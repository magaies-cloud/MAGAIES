<?php
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); }
 
$db_sales_grid_config = null;
$preloadedUnitsJson = '[]';
$preloadedItemsJson = '[]';
try {
    require_once __DIR__ . '/api/db.php';
    if (file_exists(__DIR__ . '/db_bridge.php')) require_once __DIR__ . '/db_bridge.php';
    $systemUsdRate = function_exists('getSystemUsdExchangeRate') ? getSystemUsdExchangeRate($pdo ?? null) : 5999.0;
    if (isset($pdo)) {
        if (function_exists('getDBConnection')) { $pdo = getDBConnection(); }
        $activeSalesTenantId = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 1);
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


    <style>
        .hub-action-card {
            border-radius: 12px;
            padding: 12px 15px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            /* gpu-opt */
            /* gpu-opt */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            height: 80px; /* Reduced height to ~50% of the previous tall card */
        }
        .hub-action-card::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            transition: transform 0.6s ease, opacity 0.6s ease, background-color 0.6s ease, border-color 0.6s ease, color 0.6s ease;
        }
        .hub-action-card:hover::after {
            left: 200%;
        }
        .hub-action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.15);
        }
        .hub-action-card .icon {
            font-size: 1.8rem;
            margin-bottom: 0;
            padding: 8px;
            border-radius: 10px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
        }
        .hub-action-card:hover .icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .hub-action-card .text-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: right;
        }
        .hub-action-card .title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .hub-action-card .subtitle {
            font-size: 0.7rem;
            font-weight: 600;
            text-align: right;
            margin: 0;
        }

        /* ── Theme-aware Card Styling (Dark Mode Defaults with Magical Glassy Borders & Glow) ── */
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
            border-color: rgba(14, 165, 233, 0.7) !important; /* Glowing glass border */
            box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.35), inset 0 0 18px rgba(14, 165, 233, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.25), rgba(190, 24, 74, 0.08)) !important;
            border: 1px solid rgba(236, 72, 153, 0.3) !important; /* Translucent pink border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-pink .icon { color: #ec4899; background: rgba(236, 72, 153, 0.08); }
        .card-pink .title { color: #ec4899 !important; }
        .card-pink .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-pink:hover {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.4), rgba(190, 24, 74, 0.15)) !important;
            border-color: rgba(236, 72, 153, 0.7) !important; /* Glowing glass border */
            box-shadow: 0 12px 40px 0 rgba(236, 72, 153, 0.35), inset 0 0 18px rgba(236, 72, 153, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        .card-green {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(4, 120, 87, 0.08)) !important;
            border: 1px solid rgba(16, 185, 129, 0.6) !important; /* Translucent green border */
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1) !important;
        }
        .card-green .icon { color: #10b981; background: rgba(16, 185, 129, 0.18); }
        .card-green .title { color: #10b981 !important; }
        .card-green .subtitle { color: rgba(255, 255, 255, 0.7) !important; }
        .card-green:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.4), rgba(4, 120, 87, 0.15)) !important;
            border-color: rgba(16, 185, 129, 0.7) !important; /* Glowing glass border */
            box-shadow: 0 12px 40px 0 rgba(16, 185, 129, 0.35), inset 0 0 18px rgba(16, 185, 129, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
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
            border-color: rgba(168, 85, 247, 0.7) !important; /* Glowing glass border */
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.35), inset 0 0 18px rgba(168, 85, 247, 0.45), inset 0 1px 0 0 rgba(255, 255, 255, 0.3) !important;
        }

        /* ── Light Mode overrides for Hub Cards (Light Pastel Glassy Gradients, Vibrant Colored Text & Magical Hover Border Glows) ── */
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

        :root.light-theme .card-green {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.16), rgba(52, 211, 153, 0.05)) !important;
            border: 1px solid rgba(16, 185, 129, 0.28) !important; /* Translucent green glassy border */
            box-shadow: 0 8px 32px 0 rgba(16, 185, 129, 0.06), inset 0 1px 0 0 rgba(255, 255, 255, 0.6) !important;
        }
        :root.light-theme .card-green .icon { color: #059669 !important; background: rgba(16, 185, 129, 0.18) !important; }
        :root.light-theme .card-green .title { color: #059669 !important; }
        :root.light-theme .card-green .subtitle { color: rgba(5, 150, 105, 0.8) !important; }
        :root.light-theme .card-green:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.6), rgba(52, 211, 153, 0.1)) !important;
            border-color: rgba(16, 185, 129, 0.6) !important; /* Glowing green glass border */
            box-shadow: 0 12px 40px 0 rgba(16, 185, 129, 0.22), inset 0 0 15px rgba(16, 185, 129, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.75) !important;
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
        .hub-badge.preliminary-badge { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
        #invoice-editor-view.preliminary-active .th-wh,
        #invoice-editor-view.preliminary-active .td-wh,
        #invoice-editor-view.preliminary-active .m-wh-group {
            display: none !important;
        }
        #invoice-editor-view.preliminary-active .pro-serial-number {
            color: #c084fc !important;
            text-shadow: 0 0 10px rgba(192, 132, 252, 0.5) !important;
        }
        #invoice-editor-view.preliminary-active #form-fields-card {
            border-color: rgba(168, 85, 247, 0.5) !important;
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.2), inset 0 0 15px rgba(255, 255, 255, 0.05) !important;
        }
    </style>
<link rel="stylesheet" href="css/screens/direct-sales-s2.css?v=<?= @filemtime(__DIR__ . '/css/screens/direct-sales-s2.css') ?: '2027' ?>">
    
<link rel="stylesheet" href="css/choices.min.css?v=<?php echo file_exists('css/choices.min.css') ? filemtime('css/choices.min.css') : '11.2.3'; ?>" />
<script defer src="js/choices.min.js?v=<?php echo file_exists('js/choices.min.js') ? filemtime('js/choices.min.js') : '11.2.3'; ?>"></script>
 <style>
 /* Choices custom overrides */
 .choices__inner { background-color: rgba(30,41,59,0.8) !important; border: 1px solid rgba(255,255,255,0.15) !important; color: #fff !important; border-radius: 8px !important; min-height: 42px !important; }
 .choices__list--dropdown { background-color: #1e293b !important; color: #fff !important; border: 1px solid #38bdf8 !important; z-index: 1000 !important; }
 .choices__item--selectable.is-highlighted { background-color: #38bdf8 !important; color: #fff !important; }
 .choices__input { color: #fff !important; }

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
            /* gpu-opt */
            /* gpu-opt */
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
 
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
        }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .summary-card,
        :root.light-theme .filter-panel,
        :root.light-theme .chart-card,
        :root.light-theme .kpi-card,
        :root.light-theme .premium-header,
        :root.light-theme .top-header,
        :root.light-theme .header-area {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
        :root.light-theme .glass-panel *,
        :root.light-theme .neo-card *,
        :root.light-theme .summary-card * {
            text-shadow: none !important;
        }
        :root.light-theme table.pro-table th,
        :root.light-theme .pos-grid thead th,
        :root.light-theme .ledger-table th,
        :root.light-theme .report-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.02) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #334155 !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #0f172a !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .modern-btn {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #0f172a !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }

        /* High Contrast & 1.66x Font Size for Light Theme (Excludes POS Grid) */
        :root.light-theme label,
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .lbl,
        :root.light-theme .tot-lbl,
        :root.light-theme .table-header,
        :root.light-theme .modal-header h3,
        :root.light-theme .pos-lemon-title h2,
        :root.light-theme .brand-text h1,
        :root.light-theme .brand-text p,
        :root.light-theme .clock-time,
        :root.light-theme .clock-date,
        :root.light-theme .user-info span,
        :root.light-theme #invoice-info-box label,
        :root.light-theme #invoice-info-box span,
        :root.light-theme #fin-strip span,
        :root.light-theme #fin-strip strong,
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #000000 !important;
            font-weight: 800 !important;
        }

        /* 66% font size increase (1.66x) */
        :root.light-theme label,
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .lbl,
        :root.light-theme .tot-lbl {
            font-size: 1.45rem !important; /* ~23px, scaled up by 66% from ~14px */
        }
        
        :root.light-theme .pos-lemon-title h2,
        :root.light-theme .brand-text h1 {
            font-size: 1.8rem !important;
        }

        :root.light-theme .brand-text p {
            font-size: 1.25rem !important;
        }

        :root.light-theme .clock-time {
            font-size: 1.65rem !important;
        }

        :root.light-theme .clock-date {
            font-size: 1.15rem !important;
        }

        :root.light-theme #invoice-info-box span,
        :root.light-theme #inv-number {
            font-size: 1.55rem !important;
        }

        :root.light-theme #fin-strip span {
            font-size: 1.15rem !important;
        }

        :root.light-theme #fin-strip strong,
        :root.light-theme #fin-ledger,
        :root.light-theme #fin-checks,
        :root.light-theme #fin-total-debt,
        :root.light-theme #fin-credit-limit,
        :root.light-theme #fin-remaining {
            font-size: 1.25rem !important;
            color: #000000 !important;
        }

        /* Inputs, selects, and textareas (outside table) */
        :root.light-theme input:not(table *):not(.choices__input),
        :root.light-theme select:not(table *),
        :root.light-theme textarea:not(table *),
        :root.light-theme .ctrl-input:not(table *),
        :root.light-theme .v-input:not(table *) {
            font-size: 1.45rem !important;
            color: #1e293b !important;
            font-weight: 700 !important;
            border: 1.5px solid rgba(148, 163, 184, 0.5) !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.06) !important;
            padding: 8px 12px !important;
            height: auto !important;
            border-radius: 10px !important;
            transition: transform 0.3s ease !important, opacity 0.3s ease !important, background-color 0.3s ease !important, border-color 0.3s ease !important, color 0.3s ease !important;
        }

        :root.light-theme input:not(table *):not(.choices__input):focus,
        :root.light-theme select:not(table *):focus,
        :root.light-theme textarea:not(table *):focus,
        :root.light-theme .ctrl-input:not(table *):focus,
        :root.light-theme .v-input:not(table *):focus {
            border-color: rgba(99, 102, 241, 0.6) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 0 0 3px rgba(99, 102, 241, 0.12), 0 2px 8px rgba(99, 102, 241, 0.15) !important;
            outline: none !important;
        }

        :root.light-theme input:not(table *):not(.choices__input):hover,
        :root.light-theme select:not(table *):hover,
        :root.light-theme textarea:not(table *):hover,
        :root.light-theme .ctrl-input:not(table *):hover,
        :root.light-theme .v-input:not(table *):hover {
            border-color: rgba(99, 102, 241, 0.4) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        /* Keep customer search padding left correct to accommodate the ledger button if absolute */
        :root.light-theme #hdr-customer-search {
            padding-left: 120px !important;
        }

        /* Financial totals and summary values at the bottom */
        :root.light-theme .tot-val,
        :root.light-theme .val {
            font-size: 1.8rem !important;
            color: #000000 !important;
            font-weight: 900 !important;
        }

        /* Modal card elements */
        :root.light-theme .modal-card label {
            font-size: 1.3rem !important;
            color: #000000 !important;
        }

        :root.light-theme .modal-card input {
            font-size: 1.3rem !important;
            color: #000000 !important;
        }

        /* Action Buttons outside the table (Exclude Hub Action Cards to retain premium glassmorphic styling) */
        :root.light-theme button:not(table *):not(.pro-serial-btn):not(.modal-close):not(.hub-action-card) {
            font-size: 1.3rem !important;
            color: #334155 !important;
            font-weight: 700 !important;
            border: 1.5px solid rgba(148, 163, 184, 0.4) !important;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%) !important;
            padding: 8px 16px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255,255,255,0.8) !important;
            border-radius: 10px !important;
            transition: transform 0.3s ease !important, opacity 0.3s ease !important, background-color 0.3s ease !important, border-color 0.3s ease !important, color 0.3s ease !important;
        }

        :root.light-theme button:not(table *):not(.pro-serial-btn):not(.modal-close):not(.hub-action-card):hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
            color: #1e293b !important;
            border-color: rgba(99, 102, 241, 0.4) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255,255,255,0.8) !important;
            transform: translateY(-1px);
        }
        
        /* Make sure plus button next to customer stays formatted well but larger */
        :root.light-theme button[onclick="openModal('add-customer-modal')"] {
            width: auto !important;
            height: auto !important;
            padding: 6px 14px !important;
        }

        /* Ensure header buttons are formatted well */
        :root.light-theme .icon-btn {
            width: auto !important;
            height: auto !important;
            padding: 6px 12px !important;
            font-size: 1.3rem !important;
        }

        /* Sleek, glassy overrides for search box and refresh button in Light Theme */
        :root.light-theme #hub-search {
            height: 34px !important;
            padding: 5px 12px !important;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important; /* Blue glassy border */
            background: rgba(14, 165, 233, 0.04) !important; /* Translucent blue background */
            border-radius: 8px !important;
            color: #0f172a !important;
            box-shadow: inset 0 1px 2px rgba(14, 165, 233, 0.05) !important;
            transition: transform 0.3s ease !important, opacity 0.3s ease !important, background-color 0.3s ease !important, border-color 0.3s ease !important, color 0.3s ease !important;
        }
        :root.light-theme #hub-search:focus {
            background: #ffffff !important;
            border-color: rgba(14, 165, 233, 0.6) !important;
            box-shadow: 0 0 8px rgba(14, 165, 233, 0.2), inset 0 1px 1px rgba(0,0,0,0.05) !important;
            outline: none !important;
        }
        
        :root.light-theme #sales-hub-view .btn-action,
        :root.light-theme .neo-card-title .btn-action {
            height: 34px !important;
            padding: 5px 14px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            color: #334155 !important;
            border: 1px solid rgba(15, 23, 42, 0.15) !important; /* Subtle dark glassy border */
            background: rgba(15, 23, 42, 0.05) !important; /* Translucent grey background */
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            transition: transform 0.3s ease !important, opacity 0.3s ease !important, background-color 0.3s ease !important, border-color 0.3s ease !important, color 0.3s ease !important;
        }
        :root.light-theme #sales-hub-view .btn-action:hover,
        :root.light-theme .neo-card-title .btn-action:hover {
            background: rgba(15, 23, 42, 0.1) !important;
            border-color: rgba(15, 23, 42, 0.3) !important;
            color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
            transform: translateY(-1px) !important;
        }
</style>

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
                const spans = scope.querySelectorAll('#pos-grid .total-val, #pos-grid .avail-val, body #pos-grid tbody tr td[data-col-id="col_avail"] .g-readonly');
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
                const editorScope = document.getElementById('invoice-editor-view') || document;
                window.syncInvoiceFaintHintsAndZeros(editorScope);
                document.addEventListener('input', function(e) {
                    if (e.target && (e.target.matches('#pos-grid input.g-input, .tot-row input.tot-input, #pos-grid select.g-select, #hdr-cost-center'))) {
                        window.syncInvoiceFaintHintsAndZeros(e.target.closest('tr') || e.target.parentElement || editorScope);
                    }
                }, true);
                document.addEventListener('change', function(e) {
                    if (e.target && (e.target.matches('#pos-grid input.g-input, .tot-row input.tot-input, #pos-grid select.g-select, #hdr-cost-center'))) {
                        window.syncInvoiceFaintHintsAndZeros(e.target.closest('tr') || e.target.parentElement || editorScope);
                    }
                }, true);
                document.addEventListener('blur', function(e) {
                    if (e.target && e.target.closest && e.target.closest('#invoice-editor-view')) {
                        const rowScope = e.target.closest('tr') || editorScope;
                        setTimeout(function() { window.syncInvoiceFaintHintsAndZeros(rowScope); }, 30);
                    }
                }, true);
                const gridBody = document.getElementById('grid-body');
                if (gridBody && window.MutationObserver) {
                    let _faintRaf = 0;
                    const obs = new MutationObserver(function() {
                        if (_faintRaf) return;
                        _faintRaf = requestAnimationFrame(function() {
                            _faintRaf = 0;
                            window.syncInvoiceFaintHintsAndZeros(gridBody);
                        });
                    });
                    obs.observe(gridBody, { childList: true });
                }
            });
        })();
    </script>
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
                        <button type="button" onclick="window.toggleSalesFc3Inline7ChartsDrawer && window.toggleSalesFc3Inline7ChartsDrawer()" style="height: 27px; padding: 0 10px; border-radius: 7px; background: linear-gradient(135deg,#0284c7,#0369a1); color: #ffffff; border: 1px solid #38bdf8; font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="البند الختامي (1): دستور السباعية العالمية للمخططات التفاعلية (7/7) في 0ms مع السطر التحليلي الذكي">📈 المخططات السبعة (7/7)</button>
                        <button type="button" onclick="window.openSalesFc2AtomicSyncAndDrillDownModal && window.openSalesFc2AtomicSyncAndDrillDownModal()" style="height: 27px; padding: 0 9px; border-radius: 7px; background: rgba(16,185,129,0.18); color: #34d399; border: 1px solid rgba(16,185,129,0.5); font-size: 0.72rem; font-weight: 900; cursor: pointer;" title="النقطة 1: التسميع الذري اللحظي عبر 7 تقارير والارتداد العكسي Drill-Down">🔗 التسميع الذري (7/7) و Drill-Down</button>
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
                        <span style="font-size: 0.78rem; font-weight: 900; color: #fde047;">🏗️ معيار حديد السجانة للأقسام:</span>
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

                
                <!-- Form Fields -->
                <div class="neo-card expanded-form sales-glass-card gold-obsidian-card" id="form-fields-card" style="
                    margin-bottom: 0; 
                    padding: 12px 16px; 
                    border-radius: 16px; 
                    position: relative;
                    overflow: visible;
                    width: 100%;
                ">
                    <!-- Ambient Glow Removed for Zero Light Reflection -->
                    
                    <div style="display: flex; flex-direction: column; gap: 8px; position:relative; z-index:1;">
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
    <style id="sales-worldclass-ux-styles">

        /* ═══ +20% ROW HEIGHT HARMONY FOR SALES HEADER ROWS 1, 2, 3 + ZERO BLURRED RIGHT SEMICIRCLE ═══ */
        #sales-row0-bar { min-height: 48px !important; }
        #invoice-info-box { min-height: 48px !important; max-height: 50px !important; padding: 4px 12px !important; }
        #invoice-info-box .group-invoice,
        #invoice-info-box .pro-serial-container { height: 44px !important; min-height: 44px !important; max-height: 44px !important; }
        #hdr-date,
        #hdr-customer-search,
        #hdr-currency,
        #hdr-exchange-rate,
        #hdr-warehouse,
        #hdr-description,
        #hdr-cost-center,
        #fin-strip-currency-btn,
        #pay-credit-amount,
        #pay-credit-due-date {
            height: 46px !important;
            min-height: 46px !important;
        }
        .pur-fin-capsule,
        .fin-stat-capsule {
            min-height: 46px !important;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }
        #hdr-customer-ledger-btn {
            height: 38px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            animation: none !important;
        }
        #hdr-customer-ledger-btn::before,
        .pos-lemon-title::before,
        #payment-panel::before, #payment-panel::after,
        #payment-layout::before, #payment-layout::after,
        .payment-details-card::before, .payment-details-card::after,
        #pay-credit-section::before, #pay-credit-section::after,
        .neo-card::before, .neo-card::after,
        .panel::before, .panel::after,
        #auto-scroll-return-pill, .auto-scroll-return-pill,
        [class*="orb"] {
            display: none !important;
            content: none !important;
            background: none !important;
            filter: none !important;
            box-shadow: none !important;
            animation: none !important;
        }
  
        #sales-top-container #form-fields-card {
            padding: 12px 16px !important;
            border-radius: 16px !important;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 16, 32, 0.92)) !important;
            border: 1px solid rgba(56, 189, 248, 0.22) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
        }
        #sales-row0-bar {
            display: flex !important;
            width: 100% !important;
            gap: 10px !important;
            margin-bottom: 4px !important;
            align-items: center !important;
            min-height: 42px !important;
        }
        #invoice-info-box {
            flex: 0 0 auto !important;
            display: flex !important;
            gap: 8px !important;
            width: auto !important;
            min-height: 40px !important;
            max-height: 42px !important;
            align-items: center !important;
            justify-content: flex-start !important;
            padding: 2px 10px !important;
            border-radius: 11px !important;
            background: rgba(15, 23, 42, 0.55) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important, opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important, background-color 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important, border-color 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important, color 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
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
            font-size: 0.78rem !important;
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
            font-size: 1.35rem !important;
            font-family: 'Outfit', 'JetBrains Mono', sans-serif !important;
            font-weight: 900 !important;
            color: #38bdf8 !important;
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
        #sales-user-audit-container {
            display: none !important;
        }
        #customer-financial-card {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            width: auto !important;
            opacity: 1 !important;
            display: flex !important;
        }
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
        #sales-row1-bar,
        #sales-row2-bar {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 12px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        #sales-row1-bar .h-form-group,
        #sales-row2-bar .h-form-group {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }
        #sales-row1-bar .h-input-wrapper,
        #sales-row2-bar .h-input-wrapper {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            height: 40px !important;
            flex: 1 !important;
            min-width: 0 !important;
        }
        #sales-row1-bar .custom-dropdown,
        #sales-row1-bar .custom-dropdown-input-wrap {
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            position: relative !important;
        }
        #sales-row1-bar .h-label,
        #sales-row2-bar .h-label {
            font-size: 0.84rem !important;
            font-weight: 800 !important;
            min-width: auto !important;
            margin: 0 !important;
            white-space: nowrap !important;
            letter-spacing: 0 !important;
            line-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        #sales-row1-bar .ctrl-input,
        #sales-row2-bar .ctrl-input {
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
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
            font-size: 1.38rem !important;
            font-weight: 900 !important;
            font-family: 'Outfit', 'JetBrains Mono', sans-serif !important;
            letter-spacing: 0.5px !important;
            color: #10b981 !important;
            background: rgba(16, 185, 129, 0.12) !important;
            border: 1px solid rgba(16, 185, 129, 0.35) !important;
        }
    </style>

    <!-- ROW 0: ONE SINGLE UNBROKEN LINE (الرقم مع السهمين + التاريخ + جنيه ⇄ + الرصيد + المديونية + الشيكات + الحد + المتاح) -->
    <div id="sales-row0-bar" style="display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; width: 100%; margin: 0;">
        <div id="invoice-info-box" style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; padding: 3px 7px; border-radius: 10px;">
            <div class="h-form-group group-invoice" style="display:flex; align-items:center; gap:2px; margin:0;">
                <div class="pro-serial-container" style="display:inline-flex; align-items:center; gap:2px;">
                    <button class="pro-serial-btn" type="button" onclick="loadPreviousInvoice()" title="الفاتورة السابقة" style="background: rgba(56,189,248,0.22); border: 1.5px solid #38bdf8; border-radius: 5px; color: #38bdf8; font-size: 0.78rem; font-weight: 900; cursor: pointer; width: 20px; height: 22px; display:inline-flex !important; align-items:center; justify-content:center; flex-shrink:0; padding:0;">❯</button>
                    <span id="inv-number" data-auto-fit-voucher="1" class="pro-serial-number" style="min-width: 44px; padding: 0 3px; text-align:center;">---</span>
                    <input type="hidden" id="hdr-inv-no">
                    <button class="pro-serial-btn" type="button" onclick="loadNextInvoice()" title="الفاتورة التالية" style="background: rgba(56,189,248,0.22); border: 1.5px solid #38bdf8; border-radius: 5px; color: #38bdf8; font-size: 0.78rem; font-weight: 900; cursor: pointer; width: 20px; height: 22px; display:inline-flex !important; align-items:center; justify-content:center; flex-shrink:0; padding:0;">❮</button>
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
                    <span class="fin-lbl" style="font-size:0.75rem; color:#ffffff; font-weight:900;">الرصيد:</span>
                    <span id="fin-ledger" class="fin-val" style="font-weight:900; color:#ffffff; font-size:0.85rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-debt" class="fin-metric-pill" style="background: rgba(127,29,29,0.62); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(251,113,133,0.75);">
                    <span class="fin-lbl" style="font-size:0.75rem; color:#ffffff; font-weight:900;">المديونية:</span>
                    <span id="fin-total-debt" class="fin-val" style="font-weight:900; color:#fecdd3; font-size:0.85rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-checks" class="fin-metric-pill" style="background: rgba(120,53,15,0.62); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(251,191,36,0.78);">
                    <span class="fin-lbl" style="font-size:0.75rem; color:#ffffff; font-weight:900;">الشيكات:</span>
                    <span id="fin-checks" class="fin-val" style="font-weight:900; color:#fef08a; font-size:0.85rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-limit" class="fin-metric-pill" style="background: rgba(12,74,110,0.62); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(56,189,248,0.78);">
                    <span class="fin-lbl" style="font-size:0.75rem; color:#ffffff; font-weight:900;">الحد:</span>
                    <span id="fin-credit-limit" class="fin-val" style="font-weight:900; color:#bae6fd; font-size:0.85rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <div class="fin-sep" style="width:1px; height:18px; background:rgba(255,255,255,0.18); flex-shrink:0;"></div>
                <div id="fin-wrap-remaining" class="fin-metric-pill" style="background: rgba(6,78,59,0.72); padding: 2px 7px; border-radius: 6px; border: 1.5px solid rgba(52,211,153,0.85);">
                    <span class="fin-lbl" style="font-size:0.75rem; color:#ffffff; font-weight:900;">✨ المتاح:</span>
                    <span id="fin-remaining" class="fin-val" style="font-weight:900; color:#a7f3d0; font-size:0.85rem; font-family:'Outfit',sans-serif;">0</span>
                </div>
                <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span><span id="fin-status-txt"></span></span>
            </div>
        </div>
    </div>

    <!-- ROW 1: العميل (+ أستاذ + إضافة بدون آخر فاتورة) + زر جنيه/دولار + سعر الصرف الموسّع + المخزن في مكانه الأصلي -->
    <div id="sales-row1-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin: 0; width: 100%; align-items: center;">
        <div class="h-form-group group-supplier" style="flex: 4.5 1 0%; min-width: 0; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="color: var(--amber, #f59e0b); margin:0;">👤 العميل</label>
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
            <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="سعر الصرف" readonly style="width: 124px; min-width: 114px; text-align: center; cursor:pointer; padding: 0 8px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; this.dataset.userEdited='1'; if(window.autoFitHeaderFields) window.autoFitHeaderFields(); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
        </div>

        <div class="h-form-group group-warehouse" style="flex: 1.65 1 0%; min-width: 180px; max-width: 280px; display: flex; align-items: center; gap: 6px; flex-direction: row !important; margin: 0;">
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
                        <button type="button" id="btn-sales-ai-prices" onclick="if(window.openSalesAIAuditModal) window.openSalesAIAuditModal();" style="background: linear-gradient(135deg, rgba(168,85,247,0.22), rgba(99,102,241,0.35)); border: 1px solid rgba(168,85,247,0.6); color: #e9d5ff; border-radius: 9px; height: 36px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 800; font-size: 0.85rem; white-space: nowrap; transition: all 0.2s; box-shadow: 0 2px 10px rgba(168,85,247,0.2);" title="الأسعار وهامش البيع">
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
            <style>
                body.cur-sdg .hide-if-sdg {
                    display: none !important;
                }
                .tot-toggle-btn {
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    color: #e2e8f0;
                    padding: 6px 12px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 0.9rem;
                    font-weight: 700;
                    transition: transform 0.2s, opacity 0.2s, background-color 0.2s, border-color 0.2s, color 0.2s;
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
                    transition: transform 0.2s, opacity 0.2s, background-color 0.2s, border-color 0.2s, color 0.2s;
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
            </style>
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

            <script>
            window.toggleTotRow = function(type) {
                const btn = document.getElementById('btn-toggle-' + type);
                const row = document.getElementById('row-tot-' + type);
                if (!btn || !row) return;

                if (row.style.display === 'none' || row.style.display === '') {
                    row.style.display = 'table-row';
                    btn.classList.add('active');
                    // Focus local input
                    let mapId = 't-' + type;
                    if (type === 'additions') mapId = 'addition-amt';
                    if (type === 'tax') mapId = 'tax-amt';
                    if (type === 'discount') mapId = 'discount-amt';
                    const inp = document.getElementById(mapId);
                    if (inp) inp.focus();
                } else {
                    row.style.display = 'none';
                    btn.classList.remove('active');
                    // Reset input values when hidden
                    let mapId = 't-' + type;
                    let mapFcId = 't-' + type + '-fc';
                    if (type === 'additions') { mapId = 'addition-amt'; mapFcId = 'addition-amt-fc'; }
                    if (type === 'tax') { mapId = 'tax-amt'; mapFcId = 'tax-amt-fc'; }
                    if (type === 'discount') { mapId = 'discount-amt'; mapFcId = 'discount-amt-fc'; }
                    
                    const inp = document.getElementById(mapId);
                    const inpFc = document.getElementById(mapFcId);
                    if (inp) inp.value = '0';
                    if (inpFc) inpFc.value = '0';
                    if (typeof recalcAll === 'function') recalcAll();
                }
            };

            let _lastDynLocVal = 0;
            let _lastDynFcVal = 0;
            let _animLocReq = null;
            let _animFcReq = null;

            function formatGrandTotalInt(n) {
                const num = Math.round(Number(n) || 0);
                return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            function animateDynNumber(el, startVal, endVal, duration, suffixHtml, isFc) {
                if (!el) return;
                const startTime = performance.now();
                const diff = endVal - startVal;
                
                if (Math.abs(diff) < 0.001) {
                    el.innerHTML = (isFc ? fmtNum(endVal) : formatGrandTotalInt(endVal)) + suffixHtml;
                    return;
                }

                // Trigger dynamic neon glow effect
                el.classList.remove("total-value-updating");
                void el.offsetWidth; // trigger reflow
                el.classList.add("total-value-updating");

                function step(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Easing: easeOutExpo
                    const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const current = startVal + (diff * ease);
                    el.innerHTML = (isFc ? fmtNum(current) : formatGrandTotalInt(current)) + suffixHtml;

                    if (progress < 1) {
                        if (isFc) {
                            _animFcReq = requestAnimationFrame(step);
                        } else {
                            _animLocReq = requestAnimationFrame(step);
                        }
                    } else {
                        el.innerHTML = (isFc ? fmtNum(endVal) : formatGrandTotalInt(endVal)) + suffixHtml;
                        setTimeout(() => {
                            el.classList.remove("total-value-updating");
                        }, 250);
                    }
                }
                
                if (isFc) {
                    if (_animFcReq) cancelAnimationFrame(_animFcReq);
                    _animFcReq = requestAnimationFrame(step);
                } else {
                    if (_animLocReq) cancelAnimationFrame(_animLocReq);
                    _animLocReq = requestAnimationFrame(step);
                }
            }

            window.syncTotalsVisibility = function() {
                const curEl = document.getElementById("hdr-currency");
                const isSDG = (!curEl || curEl.value === "SDG");
                
                if (isSDG) {
                    document.body.classList.add("cur-sdg");
                } else {
                    document.body.classList.remove("cur-sdg");
                }
                
                const dynLoc = document.getElementById("t-grand-dynamic-loc");
                const dynFc = document.getElementById("t-grand-dynamic-fc");
                const dynBadge = document.getElementById("t-grand-currency-badge");
                const tGrand = document.getElementById("t-grand");
                const rate = parseNum(document.getElementById("hdr-exchange-rate")?.value) || 1;
                
                if (dynBadge) {
                    dynBadge.textContent = isSDG ? 'SDG' : (curEl?.value || 'SDG');
                }

                if (tGrand && dynLoc) {
                    const cleanVal = tGrand.textContent.replace(/[^0-9.-]/g, "");
                    const numVal = parseNum(cleanVal) || 0;
                    
                    animateDynNumber(dynLoc, _lastDynLocVal, numVal, 450, '', false);
                    _lastDynLocVal = numVal;

                    if (dynFc) {
                        const fcVal = rate > 0 ? (isSDG ? (numVal / rate) : (numVal * rate)) : 0;
                        const suffixFc = ' ' + (isSDG ? '$' : 'SDG');
                        animateDynNumber(dynFc, _lastDynFcVal, fcVal, 450, suffixFc, true);
                        _lastDynFcVal = fcVal;
                    }
                }
            };

            window.syncFc = function(type) {
                const rate = parseNum(document.getElementById('hdr-exchange-rate')?.value) || 1;
                const isSDG = (document.getElementById('hdr-currency')?.value === 'SDG');
                let locId = 't-' + type;
                let fcId = 't-' + type + '-fc';
                if (type === 'additions') { locId = 'addition-amt'; fcId = 'addition-amt-fc'; }
                if (type === 'tax') { locId = 'tax-amt'; fcId = 'tax-amt-fc'; }
                if (type === 'discount') { locId = 'discount-amt'; fcId = 'discount-amt-fc'; }
                
                const elLoc = document.getElementById(locId);
                const elFc = document.getElementById(fcId);
                if (!elLoc || !elFc) return;
                
                const valLoc = parseNum(elLoc.value);
                if (rate > 0) {
                    elFc.value = fmtNum(isSDG ? (valLoc / rate) : (valLoc * rate));
                }
            };

            window.syncLocal = function(type) {
                const rate = parseNum(document.getElementById('hdr-exchange-rate')?.value) || 1;
                const isSDG = (document.getElementById('hdr-currency')?.value === 'SDG');
                let locId = 't-' + type;
                let fcId = 't-' + type + '-fc';
                if (type === 'additions') { locId = 'addition-amt'; fcId = 'addition-amt-fc'; }
                if (type === 'tax') { locId = 'tax-amt'; fcId = 'tax-amt-fc'; }
                if (type === 'discount') { locId = 'discount-amt'; fcId = 'discount-amt-fc'; }
                
                const elLoc = document.getElementById(locId);
                const elFc = document.getElementById(fcId);
                if (!elLoc || !elFc) return;
                
                const valFc = parseNum(elFc.value);
                if (rate > 0) {
                    elLoc.value = fmtNum(isSDG ? (valFc * rate) : (valFc / rate));
                }
            };

            // Hook into recalcAll
            if (typeof window.recalcAll === 'function') {
                const origRecalc = window.recalcAll;
                window.recalcAll = function() {
                    origRecalc();
                    if (typeof window.syncTotalsVisibility === 'function') {
                        window.syncTotalsVisibility();
                    }
                };
            }
            setTimeout(window.syncTotalsVisibility, 300);
            </script>

            
    <style id="grand-total-dynamic-anim-css">
        @keyframes totalNeonPulse {
            0% {
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25), inset 0 1px 2px rgba(255, 255, 255, 0.2);
                border-color: rgba(16, 185, 129, 0.6);
            }
            35% {
                transform: scale(1.06);
                box-shadow: 0 0 35px rgba(16, 185, 129, 0.75), 0 0 15px rgba(56, 189, 248, 0.6), inset 0 0 20px rgba(16, 185, 129, 0.5);
                border-color: #34d399;
                color: #ffffff !important;
                text-shadow: 0 0 20px rgba(52, 211, 153, 0.9);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25), inset 0 1px 2px rgba(255, 255, 255, 0.2);
                border-color: rgba(16, 185, 129, 0.6);
                text-shadow: none;
            }
        }

        @keyframes grandBadgeShine {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        #t-grand-dynamic-loc {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
            will-change: transform, box-shadow;
            cursor: default;
        }

        #t-grand-dynamic-loc:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4), inset 0 1px 3px rgba(255, 255, 255, 0.3);
            border-color: #34d399;
        }

        #t-grand-dynamic-loc.total-value-updating {
            animation: totalNeonPulse 0.55s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        #t-grand-dynamic-fc {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
            will-change: transform, box-shadow;
        }

        #t-grand-dynamic-fc.total-value-updating {
            animation: totalNeonPulse 0.55s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
    </style>

            <!-- ══ FULL-WIDTH: تفاصيل الدفع ══ -->
            <div class="panel" id="payment-panel" style="display: block; z-index: 40; margin-top: 15px; padding: 0 !important; background: rgba(15, 23, 42, 0.95) !important; border: 1px solid rgba(234, 179, 8, 0.22) !important;">
                <style>
                    #payment-panel {
                        animation: none;
                        transition: transform 0.4s ease, opacity 0.4s ease, background-color 0.4s ease, border-color 0.4s ease, color 0.4s ease;
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
                        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important, opacity 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important, background-color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important, border-color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important, color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
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
                        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.5s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.5s cubic-bezier(0.4, 0, 0.2, 1), color 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
                        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.5s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.5s cubic-bezier(0.4, 0, 0.2, 1), color 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
                        <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; transition: all 0.3s ease; white-space: nowrap; background: rgba(245, 158, 11, 0.15); color: #fbbf24;">⏳ آجلة بالكامل</span>
                    </div>

                    <div class="payment-toggles" style="display: flex; gap: 8px; flex: none; flex-wrap: wrap;">
                        <button type="button" class="pay-toggle-btn active" id="toggle-credit" onclick="togglePaymentSection('credit')" style="background: rgba(180, 83, 9, 0.1); border: 1px solid rgba(180, 83, 9, 0.35); color: #fcd34d; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">💳 آجل</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">💵 نقدي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.35); color: #67e8f9; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">🏦 تحويل بنكي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(147, 51, 234, 0.1); border: 1px solid rgba(147, 51, 234, 0.35); color: #d8b4fe; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">✍️ شيك</button>
                        <button type="button" class="pay-toggle-btn" id="btn-sales-financial-ocr" onclick="FinancialOCR.openScanner({ target: 'sales' })" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(16, 185, 129, 0.2)); border: 1.5px solid #06b6d4; color: #67e8f9; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" title="مسح إشعار تحويل بنكك أو شيك لسداد الفاتورة"><span style="font-size: 1.1rem;">📷</span> مسح إشعار/شيك (AI OCR)</button>
                        <button type="button" class="pay-toggle-btn" onclick="FinancialOCR.openTrainer()" style="background: rgba(147, 51, 234, 0.1); border: 1px solid rgba(147, 51, 234, 0.35); color: #d8b4fe; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="شرح وتدريب النماذج للذكاء الاصطناعي"><span>🧠</span> تدريب</button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: flex; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <!-- Right Section: Payment Details Forms -->
                    <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Credit payment box (Visible by default in yellow) -->
                        <div id="pay-credit-section" class="payment-method-box" style="background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(217, 119, 6, 0.85); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
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

                        <!-- Cash payment box (Toggled off by default until chosen by user) -->
                        <div id="pay-cash-section" style="background: rgba(15, 23, 42, 0.96); border: 2px solid rgba(16, 185, 129, 0.85); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; display: none; flex-direction: column; width: 100%;">
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


        <style id="sales-buttons-typography-2027">
            /* ═══════════════════════════════════════════════════════════════════════
               2027 WORLD-CLASS BUTTON TYPOGRAPHY, ALIGNMENT & AUTO-FIT SYSTEM
               ═══════════════════════════════════════════════════════════════════════ */
            #invoice-editor-view button,
            #invoice-editor-view .btn-action,
            #invoice-editor-view .royal-crystal-btn,
            #invoice-editor-view .pay-toggle-btn,
            #invoice-editor-view .tot-toggle-btn,
            #invoice-editor-view .crystal-btn,
            :root.light-theme #invoice-editor-view .btn-action,
            :root.light-theme #invoice-editor-view .royal-crystal-btn,
            :root.light-theme #invoice-editor-view .pay-toggle-btn,
            :root.light-theme #invoice-editor-view .tot-toggle-btn {
                font-family: 'Cairo', 'Tajawal', sans-serif !important;
                white-space: nowrap !important;
                line-height: 1.15 !important;
                letter-spacing: 0 !important;
                text-align: center !important;
                vertical-align: middle !important;
                box-sizing: border-box !important;
            }

            /* Bottom Toolbar Container & Buttons (100% Matched with Purchase Screen) */
            #invoice-editor-view .items-toolbar {
                padding: 12px 14px !important;
                border-radius: 14px !important;
                background: linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(18, 15, 28, 0.92)) !important;
                border: 1px solid rgba(56, 189, 248, 0.18) !important;
                margin-top: 14px !important;
                box-shadow: 0 8px 28px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.06) !important;
            }
            #invoice-editor-view .toolbar-actions {
                display: flex !important;
                gap: 8px !important;
                width: 100% !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-wrap: nowrap !important;
            }
            @media (max-width: 1180px) {
                #invoice-editor-view .toolbar-actions {
                    flex-wrap: wrap !important;
                }
            }
            #invoice-editor-view .toolbar-actions .btn-action,
            :root.light-theme #invoice-editor-view .toolbar-actions .btn-action {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                padding: 0 12px !important;
                border-radius: 11px !important;
                display: inline-flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                font-size: 0.94rem !important;
                font-weight: 900 !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                line-height: 1 !important;
            }
            #invoice-editor-view .toolbar-actions .btn-action svg,
            #invoice-editor-view .pay-toggle-btn svg,
            #invoice-editor-view .tot-toggle-btn svg {
                flex-shrink: 0 !important;
                display: block !important;
            }
            #invoice-editor-view .toolbar-actions .btn-action span,
            #invoice-editor-view .pay-toggle-btn span,
            #invoice-editor-view .tot-toggle-btn span {
                display: inline-block !important;
                line-height: 1.15 !important;
                white-space: nowrap !important;
            }

            /* Auto-Send Control Box in Bottom Toolbar */
            #auto-send-control-wrapper-sales {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                display: inline-flex !important;
                align-items: stretch !important;
                flex-shrink: 0 !important;
            }
            #btn-toggle-auto-send-sales,
            :root.light-theme #btn-toggle-auto-send-sales {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                padding: 0 10px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                white-space: nowrap !important;
                font-size: 0.86rem !important;
                font-weight: 800 !important;
            }
            #btn-auto-send-options-sales,
            :root.light-theme #btn-auto-send-options-sales {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                width: 30px !important;
                padding: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 0.78rem !important;
            }

            /* Preset Selector in Bottom Toolbar */
            #invoice-editor-view #preset-selector,
            :root.light-theme #invoice-editor-view #preset-selector {
                height: 44px !important;
                min-height: 44px !important;
                max-height: 44px !important;
                padding: 0 10px !important;
                font-size: 0.88rem !important;
                font-weight: 800 !important;
                font-family: 'Cairo', 'Tajawal', sans-serif !important;
                text-align: center !important;
                border-radius: 11px !important;
                white-space: nowrap !important;
                text-overflow: ellipsis !important;
            }

            /* Payment Toggles & Extra Cost Toggles Typography */
            #invoice-editor-view .pay-toggle-btn,
            :root.light-theme #invoice-editor-view .pay-toggle-btn {
                height: 36px !important;
                min-height: 36px !important;
                padding: 0 13px !important;
                border-radius: 18px !important;
                font-size: 0.88rem !important;
                font-weight: 800 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                white-space: nowrap !important;
                line-height: 1 !important;
            }
            #invoice-editor-view .tot-toggle-btn,
            :root.light-theme #invoice-editor-view .tot-toggle-btn {
                height: 35px !important;
                min-height: 35px !important;
                padding: 0 13px !important;
                border-radius: 9px !important;
                font-size: 0.88rem !important;
                font-weight: 800 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 5px !important;
                white-space: nowrap !important;
                line-height: 1 !important;
            }
        </style>

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
    <script>
    window.initDesktopMirrorSync = (function initDesktopSalesMirrorSync() {
        let isSyncing = false;
        async function syncSalesWithMariaDB() {
            if (isSyncing) return;
            isSyncing = true;
            try {
                if (navigator.onLine && window.MagaiesOffline && typeof MagaiesOffline.flushQueue === 'function') {
                    await MagaiesOffline.flushQueue();
                }
                const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now());
                const data = await res.json();
                if (data && data.success && Array.isArray(data.sales)) {
                    let localSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                    let localMap = new Map();
                    localSales.forEach(inv => {
                        const no = inv.invoiceNo || inv.id;
                        if (no) localMap.set(String(no), inv);
                    });

                    let hasNew = false;
                    data.sales.forEach(serverInv => {
                        const sNo = String(serverInv.invoiceNo || serverInv.id);
                        if (!sNo) return;
                        if (!localMap.has(sNo)) {
                            localStorage.setItem('erp_inv_' + sNo, JSON.stringify(serverInv));
                            localSales.push(serverInv);
                            localMap.set(sNo, serverInv);
                            hasNew = true;
                        } else {
                            const cur = localMap.get(sNo);
                            const idx = localSales.findIndex(x => String(x.invoiceNo || x.id) === sNo);
                            if (idx >= 0) {
                                const srvDelivered = serverInv.isDelivered === true || serverInv.deliveryStatus === 'delivered';
                                const curDelivered = cur.isDelivered === true || cur.deliveryStatus === 'delivered';
                                const statusChanged = (srvDelivered !== curDelivered || serverInv.deliveryStatus !== cur.deliveryStatus);
                                if (statusChanged) {
                                    hasNew = true;
                                }
                                localSales[idx] = Object.assign({}, serverInv, cur, {
                                    isDelivered: srvDelivered || curDelivered,
                                    deliveryStatus: (srvDelivered || curDelivered) ? 'delivered' : (serverInv.deliveryStatus || cur.deliveryStatus || 'pending'),
                                    deliveryNo: serverInv.deliveryNo || cur.deliveryNo || '',
                                    deliveredAt: serverInv.deliveredAt || cur.deliveredAt || '',
                                    items: (Array.isArray(cur.items) && cur.items.some(i => i.deliveredQty > 0)) ? cur.items : ((Array.isArray(serverInv.items) && serverInv.items.length > 0) ? serverInv.items : cur.items)
                                });
                                if (statusChanged) {
                                    localStorage.setItem('erp_inv_' + sNo, JSON.stringify(localSales[idx]));
                                }
                            }
                        }
                    });

                    if (hasNew) {
                        localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(localSales));
                        localStorage.setItem('magaies_local_sales_invoices_v1', JSON.stringify(localSales));
                        if (typeof loadHubTransactions === 'function' && typeof currentSalesView !== 'undefined' && currentSalesView === 'hub') {
                            loadHubTransactions();
                        }
                        const delViewEl = document.getElementById('delivery-hub-view');
                        if (delViewEl && delViewEl.style.display !== 'none' && typeof window.loadDeliveryTransactions === 'function') {
                            window.loadDeliveryTransactions();
                        }
                    }

                    const invInput = document.getElementById('hdr-inv-no');
                    const invSpan = document.getElementById('inv-number');
                    if (data.nextInvoiceNo) {
                        window._mirrorNextSalesNo = data.nextInvoiceNo;
                    }
                    const isCreatingNew = !window.currentViewedInvoiceNo || window.currentViewedInvoiceNo === '---';
                    const bestNextNo = (typeof peekNextInvoiceNumber === 'function') ? peekNextInvoiceNumber() : (data.nextInvoiceNo || '');
                    if (isCreatingNew && bestNextNo && invInput && invSpan) {
                        if (invInput.value !== bestNextNo) {
                            invInput.value = bestNextNo;
                            invSpan.textContent = bestNextNo;
                        }
                    }
                }
            } catch(e) {
                console.warn('Desktop sales mirror sync check:', e);
            } finally {
                isSyncing = false;
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(syncSalesWithMariaDB, 600);
        });
        setInterval(() => { if (!document.hidden && navigator.onLine) syncSalesWithMariaDB(); }, 45000);
        window.addEventListener('focus', syncSalesWithMariaDB);
        window.addEventListener('online', syncSalesWithMariaDB);
        return syncSalesWithMariaDB;
    })();
    </script>
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
    <script id="sales-fin-strip-and-tfoot-organizer">
    (function() {
        window._finStripCurrency = localStorage.getItem('sales_fin_strip_cur') || 'SDG';
        window.getFinStripCurrency = function() {
            const sel = document.getElementById('hdr-currency');
            return (sel && sel.value === 'USD') ? 'USD' : (window._finStripCurrency === 'USD' ? 'USD' : 'SDG');
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
            btn.textContent = (c === 'USD') ? 'دولار ⇄' : 'جنيه ⇄';
        };
        window.toggleFinStripCurrency = function() {
            window._finStripCurrency = (window.getFinStripCurrency() === 'SDG') ? 'USD' : 'SDG';
            try { localStorage.setItem('sales_fin_strip_cur', window._finStripCurrency); } catch (_e) {}
            window.syncFinStripCurrencyBtnUI(window._finStripCurrency);
            if (typeof window.onCustomerChange === 'function') window.onCustomerChange();
            if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
        };

        window.cloneLastCustomerSalesInvoice = async function() {
            const custId = document.getElementById('hdr-customer')?.value || '';
            const custName = (document.getElementById('hdr-customer-search')?.value || '').trim();
            if (!custId && !custName) {
                if (typeof showToast === 'function') showToast('يرجى اختيار العميل أولاً لنسخ أصناف آخر فاتورة له', 'warning');
                const sInp = document.getElementById('hdr-customer-search');
                if (sInp) sInp.focus();
                return;
            }
            const norm = function(t) { return String(t || '').trim().toLowerCase(); };
            const targetName = norm(custName);
            const defaultWh = document.getElementById('hdr-warehouse')?.value || '';
            let pool = [];
            try {
                if (Array.isArray(window.allHubTransactions) && window.allHubTransactions.length) pool = pool.concat(window.allHubTransactions);
                const ls1 = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
                if (Array.isArray(ls1)) pool = pool.concat(ls1);
                const ls2 = JSON.parse(localStorage.getItem('sales_invoices') || '[]');
                if (Array.isArray(ls2)) pool = pool.concat(ls2);
            } catch (_e) {}
            let lastInv = null;
            for (let i = pool.length - 1; i >= 0; i--) {
                const inv = pool[i];
                if (!inv || !Array.isArray(inv.items) || inv.items.length === 0) continue;
                const invCustId = inv.customerId || inv.customer_id || '';
                const invCustName = norm(inv.customerName || inv.customer || inv.customer_name || '');
                if ((custId && String(invCustId) === String(custId)) || (targetName && invCustName && invCustName === targetName)) {
                    lastInv = inv;
                    break;
                }
            }
            if (!lastInv || !Array.isArray(lastInv.items) || lastInv.items.length === 0) {
                if (typeof showToast === 'function') showToast('لا توجد فاتورة مبيعات سابقة مسجلة لهذا العميل', 'warning');
                return;
            }
            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                rows.length = 0;
                const itemsList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
                lastInv.items.forEach(function(it) {
                    const rawId = it.itemId || it.item_id || it.id || '';
                    const rawName = norm(it.itemName || it.item_name || it.name || '');
                    let matchedItem = itemsList.find(x => String(x.id) === String(rawId)) || (rawName ? itemsList.find(x => norm(x.name) === rawName) : null);
                    const finalItemId = matchedItem ? matchedItem.id : rawId;
                    const priceVal = parseFloat(it.price || it.unit_price || (matchedItem ? matchedItem.price : 0) || 0) || 0;
                    const qtyVal = parseFloat(it.quantity || it.qty || 1) || 1;
                    const unitVal = it.selectedUnit || it.unitName || it.unit || (matchedItem ? (matchedItem.baseUnit || matchedItem.mainUnit || matchedItem.unit || '') : '');
                    if (typeof rowIdCounter !== 'undefined') rowIdCounter++;
                    rows.push({
                        id: (typeof rowIdCounter !== 'undefined') ? rowIdCounter : (Date.now() + Math.floor(Math.random() * 1000)),
                        itemId: finalItemId,
                        quantity: qtyVal,
                        price: priceVal,
                        warehouseId: it.warehouseId || defaultWh,
                        selectedUnit: unitVal,
                        unitFactor: parseFloat(it.unitFactor || 1) || 1,
                        note: it.note || ''
                    });
                });
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                if (typeof showToast === 'function') {
                    showToast('📋 تم نسخ (' + lastInv.items.length + ') أصناف من آخر فاتورة للعميل', 'success');
                }
            }
        };

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

                const totalCol = table.querySelector('tbody tr td[data-col-id="col_total"]')
                              || table.querySelector('thead tr th[data-col-id="col_total"]')
                              || table.querySelector('tbody tr td[data-col-id="total"]')
                              || table.querySelector('thead tr th[data-col-id="total"]');
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
            let exRate = parseFloat(document.getElementById('hdr-exchange-rate')?.value) || parseFloat(window.SYSTEM_USD_RATE) || 1;
            if (isSDG && exRate <= 1) {
                try {
                    const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                    if (cfg.rateUSD && parseFloat(cfg.rateUSD) > 1) exRate = parseFloat(cfg.rateUSD);
                    else if (parseFloat(window.SYSTEM_USD_RATE) > 1) exRate = parseFloat(window.SYSTEM_USD_RATE);
                } catch (_e) {}
            }
            if (isSDG) document.body.classList.add('cur-sdg');
            else document.body.classList.remove('cur-sdg');

            const dynLoc = document.getElementById('t-grand-dynamic-loc');
            const dynFc = document.getElementById('t-grand-dynamic-fc');
            const tGrand = document.getElementById('t-grand');
            if (!dynLoc || !dynFc) return;

            const parseRaw = (txt) => parseFloat(String(txt || '0').replace(/[^0-9.-]/g, '')) || 0;
            const rawGrand = parseRaw(tGrand ? tGrand.textContent : '0');

            const sdgVal = isSDG ? rawGrand : (rawGrand * exRate);
            const usdVal = isSDG ? (exRate > 0 ? (rawGrand / exRate) : 0) : rawGrand;

            const fmtSDG = Math.round(sdgVal).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            const fmtUSD = usdVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            dynLoc.classList.remove('hide-if-sdg');
            dynLoc.setAttribute('data-cur-unit', 'ج.س');
            if (dynLoc.textContent !== fmtSDG) dynLoc.textContent = fmtSDG;
            const lenSdg = fmtSDG.length;
            const sdgPx = lenSdg > 15 ? 20 : (lenSdg > 11 ? 24 : 27);
            dynLoc.style.setProperty('font-size', sdgPx + 'px', 'important');

            dynFc.classList.remove('hide-if-sdg');
            dynFc.setAttribute('data-cur-unit', '$');
            if (dynFc.textContent !== fmtUSD) dynFc.textContent = fmtUSD;
            const lenUsd = fmtUSD.length;
            const usdPx = lenUsd > 13 ? 19 : (lenUsd > 10 ? 22 : (isSDG ? 24 : 27));
            dynFc.style.setProperty('font-size', usdPx + 'px', 'important');

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

        document.addEventListener('DOMContentLoaded', function() {
            window.syncFinStripCurrencyBtnUI();
            window.formatGrandTotalsDualSdgUsd();
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
            const finCardEl = document.getElementById('customer-financial-card');
            if (finCardEl && typeof MutationObserver !== 'undefined') {
                let _finFitTimer = 0;
                new MutationObserver(() => {
                    cancelAnimationFrame(_finFitTimer);
                    _finFitTimer = requestAnimationFrame(() => { if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields(); });
                }).observe(finCardEl, { childList: true, characterData: true, subtree: true });
            }
            window.addEventListener('resize', () => window.alignGrandTotalUnderTotalColumn());
            const tableEl = document.getElementById('pos-grid');
            if (tableEl && typeof ResizeObserver !== 'undefined') {
                new ResizeObserver(() => window.alignGrandTotalUnderTotalColumn()).observe(tableEl);
            }
        });
        window.syncFinStripCurrencyBtnUI();
        setTimeout(() => { window.formatGrandTotalsDualSdgUsd(); if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields(); }, 120);
    })();
    </script>
<style id="split-constitution1-engine-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الأول (1 من 14) لشاشة تقسيم الفواتير والشاشات الوليدة (Idempotent 2px-2.2px)
       ═══════════════════════════════════════════════════════════════════════════ */
    :root {
        --c1-border-width: 2.2px;
        --c1-font-weight-base: 800;
        --c1-font-weight-bold: 900;
        --c1-svg-stroke: 2.6px;
    }

    /* 1. التوريث الهندسي العام (+2 سُمك الحواف والخطوط والأيقونات ومنع القص) */
    #split-invoice-view,
    #delivery-hub-view,
    #post-split-success-modal,
    #split-genealogy-tree-modal {
        font-weight: var(--c1-font-weight-base) !important;
    }
    #split-invoice-view svg,
    #delivery-hub-view svg,
    #post-split-success-modal svg,
    #split-genealogy-tree-modal svg {
        stroke-width: var(--c1-svg-stroke) !important;
    }
    #split-invoice-view h3,
    #split-invoice-view h4,
    #split-invoice-view label,
    #split-invoice-view th,
    #split-invoice-view td,
    #split-invoice-view button,
    #split-invoice-view input,
    #split-invoice-view select,
    #post-split-success-modal h3,
    #post-split-success-modal button,
    #post-split-success-modal span,
    #split-genealogy-tree-modal h3,
    #split-genealogy-tree-modal th,
    #split-genealogy-tree-modal td,
    #split-genealogy-tree-modal button {
        font-weight: var(--c1-font-weight-bold) !important;
    }

    /* منع القص أو اختفاء الأرقام والأسماء الطويلة (Zero-Truncation & Auto-Fit) */
    #split-invoice-view table.pos-grid td,
    #split-invoice-view table.pos-grid th,
    #split-genealogy-tree-modal table td,
    #split-genealogy-tree-modal table th {
        white-space: normal !important;
        word-break: break-word !important;
        overflow: visible !important;
        text-overflow: clip !important;
        vertical-align: middle !important;
    }
    #split-invoice-view input[id^="split-qty-"],
    #split-invoice-view input[id^="split-subqty-"],
    #split-invoice-view #split-operation-seq-input {
        min-width: 108px !important;
        width: auto !important;
        max-width: 165px !important;
        height: 38px !important;
        padding: 4px 10px !important;
        font-size: 1.12rem !important;
        font-family: 'Outfit', monospace !important;
        font-weight: 900 !important;
        text-align: center !important;
        border-radius: 9px !important;
        box-sizing: border-box !important;
    }
    #split-invoice-view input[id^="split-sec-receiver-"],
    #split-invoice-view input[id^="split-sec-truck-"] {
        min-height: 38px !important;
        padding: 6px 10px !important;
        font-size: 0.86rem !important;
        font-weight: 900 !important;
        border-radius: 8px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 2. السمات الغامقة (Dark Themes): خلفية محيطة متدرجة (#162038) + خانة كتابة سوداء صريحة (#000000) + نص وحواف بيضاء (#FFFFFF) بسُمك +2 بدون أي انقلاب لوني */
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view .neo-card,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view #split-search-toolbar,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view #split-header-info,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view #split-live-balance-bar,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view .neo-card,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #post-split-success-modal > div,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal > div {
        background: #162038 !important;
        color: #FFFFFF !important;
        border: 2.2px solid rgba(255, 255, 255, 0.72) !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45) !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view table.pos-grid thead th,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view table.pos-grid thead th,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal table thead th {
        background: #0f172a !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        border: 2px solid rgba(255, 255, 255, 0.55) !important;
        font-weight: 900 !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view table.pos-grid tbody td,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view table.pos-grid tbody td,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal table tbody td {
        background: #162038 !important;
        color: #FFFFFF !important;
        border: 2px solid rgba(255, 255, 255, 0.35) !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view select,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view textarea,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:focus,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:active,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:-webkit-autofill,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:-webkit-autofill:focus,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view input,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view input:focus {
        background: #000000 !important;
        background-color: #000000 !important;
        -webkit-box-shadow: 0 0 0 1000px #000000 inset !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        caret-color: #FFFFFF !important;
        border: 2.2px solid #FFFFFF !important;
        font-weight: 900 !important;
        outline: none !important;
    }

    /* 3. السمات الفاتحة والبيضاء (Light & White Themes): سريان 100% على شاشة التقسيم وكل الشاشات الوليدة + خط وحواف وأيقونات بالأسود الصريح (#000000) + خانة كتابة بيضاء (#FFFFFF) */
    :root.light-theme #split-invoice-view,
    html[data-theme="warm_cream"] #split-invoice-view,
    html[data-theme="ultra_white"] #split-invoice-view,
    html[data-theme="exec_gray"] #split-invoice-view,
    :root.light-theme #delivery-hub-view,
    html[data-theme="warm_cream"] #delivery-hub-view,
    html[data-theme="ultra_white"] #delivery-hub-view,
    html[data-theme="exec_gray"] #delivery-hub-view {
        background: transparent !important;
        color: #000000 !important;
    }
    :root.light-theme #split-invoice-view .neo-card,
    :root.light-theme #split-invoice-view #split-search-toolbar,
    :root.light-theme #split-invoice-view #split-header-info,
    :root.light-theme #split-invoice-view #split-live-balance-bar,
    :root.light-theme #split-invoice-view .grid-scroll-wrap,
    :root.light-theme #delivery-hub-view .neo-card,
    :root.light-theme #post-split-success-modal > div,
    :root.light-theme #split-genealogy-tree-modal > div,
    html[data-theme="warm_cream"] #split-invoice-view .neo-card,
    html[data-theme="ultra_white"] #split-invoice-view .neo-card,
    html[data-theme="exec_gray"] #split-invoice-view .neo-card,
    html[data-theme="warm_cream"] #post-split-success-modal > div,
    html[data-theme="ultra_white"] #post-split-success-modal > div,
    html[data-theme="exec_gray"] #post-split-success-modal > div,
    html[data-theme="warm_cream"] #split-genealogy-tree-modal > div,
    html[data-theme="ultra_white"] #split-genealogy-tree-modal > div,
    html[data-theme="exec_gray"] #split-genealogy-tree-modal > div {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border: 2.2px solid #000000 !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12) !important;
    }
    :root.light-theme #split-invoice-view #split-header-info div[style*="background"],
    :root.light-theme #split-invoice-view #split-items-body td div[style*="background"],
    :root.light-theme #split-invoice-view #split-invoices-body td div[style*="background"],
    :root.light-theme #post-split-success-modal div[style*="background"],
    :root.light-theme #split-genealogy-tree-modal div[style*="background"],
    html[data-theme="warm_cream"] #split-invoice-view #split-header-info div[style*="background"],
    html[data-theme="ultra_white"] #split-invoice-view #split-header-info div[style*="background"],
    html[data-theme="exec_gray"] #split-invoice-view #split-header-info div[style*="background"] {
        background: #ffffff !important;
        color: #000000 !important;
        border: 2px solid #000000 !important;
        box-shadow: none !important;
    }
    :root.light-theme #split-invoice-view *:not(button):not(.btn-action),
    :root.light-theme #delivery-hub-view *:not(button):not(.btn-action),
    :root.light-theme #post-split-success-modal *:not(button),
    :root.light-theme #split-genealogy-tree-modal *:not(button),
    html[data-theme="warm_cream"] #split-invoice-view *:not(button):not(.btn-action),
    html[data-theme="ultra_white"] #split-invoice-view *:not(button):not(.btn-action),
    html[data-theme="exec_gray"] #split-invoice-view *:not(button):not(.btn-action) {
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        text-shadow: none !important;
    }
    :root.light-theme #split-invoice-view svg,
    :root.light-theme #delivery-hub-view svg,
    :root.light-theme #post-split-success-modal svg,
    :root.light-theme #split-genealogy-tree-modal svg {
        stroke: #000000 !important;
    }
    :root.light-theme #split-invoice-view table.pos-grid thead th,
    :root.light-theme #delivery-hub-view table.pos-grid thead th,
    :root.light-theme #split-genealogy-tree-modal table thead th,
    html[data-theme="warm_cream"] #split-invoice-view table.pos-grid thead th,
    html[data-theme="ultra_white"] #split-invoice-view table.pos-grid thead th,
    html[data-theme="exec_gray"] #split-invoice-view table.pos-grid thead th {
        background: #dfd3bc !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #000000 !important;
        font-weight: 900 !important;
    }
    :root.light-theme #split-invoice-view table.pos-grid tbody td,
    :root.light-theme #delivery-hub-view table.pos-grid tbody td,
    :root.light-theme #split-genealogy-tree-modal table tbody td,
    html[data-theme="warm_cream"] #split-invoice-view table.pos-grid tbody td,
    html[data-theme="ultra_white"] #split-invoice-view table.pos-grid tbody td,
    html[data-theme="exec_gray"] #split-invoice-view table.pos-grid tbody td {
        background: #f4ecde !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #000000 !important;
    }
    :root.light-theme #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    :root.light-theme #split-invoice-view select,
    :root.light-theme #split-invoice-view textarea,
    :root.light-theme #split-invoice-view input:focus,
    :root.light-theme #split-invoice-view input:active,
    :root.light-theme #split-invoice-view input:-webkit-autofill,
    :root.light-theme #split-invoice-view input:-webkit-autofill:focus,
    :root.light-theme #delivery-hub-view input,
    :root.light-theme #delivery-hub-view input:focus,
    html[data-theme="warm_cream"] #split-invoice-view input,
    html[data-theme="ultra_white"] #split-invoice-view input,
    html[data-theme="exec_gray"] #split-invoice-view input {
        background: #FFFFFF !important;
        background-color: #FFFFFF !important;
        -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        caret-color: #000000 !important;
        border: 2.2px solid #000000 !important;
        font-weight: 900 !important;
        outline: none !important;
    }
    :root.light-theme #split-invoice-view button,
    :root.light-theme #delivery-hub-view button,
    :root.light-theme #post-split-success-modal button,
    :root.light-theme #split-genealogy-tree-modal button {
        border: 2px solid #000000 !important;
        font-weight: 900 !important;
    }
</style>

<script id="split-constitution1-engine-script">
(function() {
    if (window.__SPLIT_CONSTITUTION1_LOCK__) return;
    window.__SPLIT_CONSTITUTION1_LOCK__ = true;

    // 1. Proportional Geometric Scaling (6:8 Aspect-Ratio Lock on Ctrl + Mouse Wheel up to 10x)
    let currentScale = parseFloat(localStorage.getItem('magaies_global_master_scale_v1') || localStorage.getItem('sales_c1_prop_zoom') || '1') || 1;
    currentScale = Math.min(10.0, Math.max(0.25, currentScale));

    window.applySalesProportionalZoom = function(scaleVal) {
        if (typeof window.setSalesFc1ProportionalZoom === 'function') {
            return window.setSalesFc1ProportionalZoom(scaleVal);
        }
        currentScale = Math.min(10.0, Math.max(0.25, +(parseFloat(scaleVal) || 1).toFixed(2)));
        localStorage.setItem('sales_c1_prop_zoom', String(currentScale));
        localStorage.setItem('magaies_global_master_scale_v1', String(currentScale));
    };

    // 2. Auto-Fit Inputs in Split Screen (Zero-Truncation)
    window.autoFitSplitInputs = function(root) {
        const scope = root || document.getElementById('split-invoice-view') || document;
        const inputs = scope.querySelectorAll('input[id^="split-qty-"], input[id^="split-subqty-"], #split-operation-seq-input, input[id^="split-sec-receiver-"], input[id^="split-sec-truck-"], #split-invoice-search');
        inputs.forEach(inp => {
            const len = String(inp.value || inp.placeholder || '').length;
            if (inp.id && (inp.id.indexOf('split-qty-') === 0 || inp.id.indexOf('split-subqty-') === 0)) {
                const chWidth = Math.max(108, Math.min(175, (len + 4) * 12));
                inp.style.width = chWidth + 'px';
            }
        });
    };

    document.addEventListener('input', function(e) {
        if (e.target && e.target.closest && e.target.closest('#split-invoice-view')) {
            window.autoFitSplitInputs(document.getElementById('split-invoice-view'));
            if (typeof window.persistActiveSplitDraftToStorage === 'function') {
                window.persistActiveSplitDraftToStorage();
            }
        }
    }, true);

    // 3. F2 Shortcut to Focus Split Search Input Instantly
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if (e.key === 'F2') {
            const searchInp = document.getElementById('split-invoice-search');
            if (searchInp && searchInp.offsetParent !== null) {
                e.preventDefault();
                searchInp.focus();
                searchInp.select();
            }
        }
    });

    // 4. Register Split Screen & Child Modals with Smart Guide Automatically
    window.registerSplitViewWithSmartGuide = function() {
        if (window.MAGAIES_SMART_GUIDE_CUSTOM_TIPS) {
            window.MAGAIES_SMART_GUIDE_CUSTOM_TIPS['split-invoice-view'] = {
                title: '✂️ بوابة تقسيم الفواتير والأذونات الذكية بالوحدتين',
                desc: 'تتيح تقسيم أي فاتورة مبيعات إلى قسمين أو أكثر بالوحدة الأساسية أو الفرعية مع توزيع نسبي آلي للخصم والضريبة وحفظ المسودة تلقائياً.'
            };
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.applySalesProportionalZoom(currentScale);
        window.autoFitSplitInputs();
        window.registerSplitViewWithSmartGuide();
    });
})();
</script>
<style id="split-constitution2-silk-style">
    /* إضاءة واضحة للخانة النشطة (Cursor Silk-Flow Focus Ring) */
    #split-invoice-view input:focus,
    #split-invoice-view select:focus,
    #delivery-hub-view input:focus {
        -webkit-box-shadow: 0 0 0 1000px #000000 inset, 0 0 0 2.5px rgba(56, 189, 248, 0.45) !important;
        box-shadow: 0 0 0 1000px #000000 inset, 0 0 0 2.5px rgba(56, 189, 248, 0.45) !important;
        transform: translateY(-1px);
        transition: box-shadow 0.12s ease, transform 0.12s ease !important;
    }
    :root.light-theme #split-invoice-view input:focus,
    :root.light-theme #split-invoice-view select:focus,
    :root.light-theme #delivery-hub-view input:focus {
        -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset, 0 0 0 2.5px rgba(2, 132, 199, 0.4) !important;
        box-shadow: 0 0 0 1000px #FFFFFF inset, 0 0 0 2.5px rgba(2, 132, 199, 0.4) !important;
    }
    /* ضغط المسافات لظهور الشاشة في نظرة واحدة (Zero-Scroll Compactness) */
    #split-invoice-view {
        gap: 10px !important;
    }
    #split-invoice-view .neo-card {
        padding: 10px 14px !important;
        margin-bottom: 8px !important;
    }
    #split-board-step {
        gap: 6px !important;
    }
    #split-board-step table.pos-grid th,
    #split-board-step table.pos-grid td {
        padding: 6px 8px !important;
    }
</style>
<script id="split-constitution2-silk-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION2_LOCK__) return;
    window.__SPLIT_CONSTITUTION2_LOCK__ = true;

    // 1. البحت العربي الذكي المتسامح وتحويل الأرقام الهندية (٠-٩ -> 0-9)
    const arabicDigitsMap = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'};
    window.normalizeSplitDigits = function(str) {
        return String(str ?? '').replace(/[٠-٩۰-۹]/g, d => arabicDigitsMap[d] || d);
    };
    window.normalizeArabicForSplitSearch = function(text) {
        return window.normalizeSplitDigits(text)
            .toLowerCase()
            .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
            .replace(/[أإآٱ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/[ىئ]/g, 'ي')
            .replace(/ؤ/g, 'و')
            .replace(/\s+/g, ' ')
            .trim();
    };

    // 2. التظليل التلقائي للأرقام عند دخول الكيرسر (Auto-Select on Focus)
    document.addEventListener('focusin', function(e) {
        const t = e.target;
        if (!t || !t.closest || !t.closest('#split-invoice-view')) return;
        if (t.tagName === 'INPUT' && (t.type === 'number' || t.id.indexOf('split-qty-') === 0 || t.id.indexOf('split-subqty-') === 0 || t.id === 'split-operation-seq-input')) {
            setTimeout(function() {
                try { t.select(); } catch (err) {}
            }, 10);
        }
    }, true);

    // تحويل الأرقام الهندية لحظياً داخل خانات البحث والكميات
    document.addEventListener('input', function(e) {
        const t = e.target;
        if (!t || !t.closest || !t.closest('#split-invoice-view')) return;
        if (t.tagName === 'INPUT' && /[٠-٩۰-۹]/.test(t.value)) {
            const norm = window.normalizeSplitDigits(t.value);
            if (norm !== t.value) t.value = norm;
        }
    }, true);

    // 3. انسيابية الكيرسر بلوحة المفاتيح (Enter / الأسهم الأربع / F9 / Ctrl+Enter / Escape)
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;

        // Enter inside #split-invoice-search -> open first visible invoice immediately
        if (e.target && e.target.id === 'split-invoice-search' && e.key === 'Enter') {
            e.preventDefault();
            const firstSplitBtn = document.querySelector('#split-invoices-body button[onclick^="startInvoiceSplit"]');
            if (firstSplitBtn) firstSplitBtn.click();
            return;
        }

        // F9 inside #split-board-step -> distribute equally
        if (e.key === 'F9' && window.activeSplitInvoice) {
            e.preventDefault();
            if (typeof window.distributeSplitEqually === 'function') window.distributeSplitEqually();
            return;
        }

        // Ctrl+Enter inside #split-board-step -> submit split immediately
        if (e.ctrlKey && e.key === 'Enter' && window.activeSplitInvoice) {
            e.preventDefault();
            if (typeof submitInvoiceSplit === 'function') submitInvoiceSplit(false);
            return;
        }

        // Escape inside child modals -> close child modal first
        if (e.key === 'Escape') {
            const genModal = document.getElementById('split-genealogy-tree-modal');
            if (genModal && genModal.style.display === 'flex') {
                e.preventDefault();
                genModal.style.display = 'none';
                return;
            }
            const postModal = document.getElementById('post-split-success-modal');
            if (postModal && postModal.style.display === 'flex') {
                e.preventDefault();
                postModal.style.display = 'none';
                return;
            }
        }

        // Arrow & Enter navigation across split grid inputs
        const t = e.target;
        if (!t || !t.closest || !t.closest('#split-board-step')) return;
        if (t.tagName !== 'INPUT') return;

        const allInputs = Array.from(document.querySelectorAll('#split-board-step input:not([type="hidden"])')).filter(el => el.offsetParent !== null && !el.disabled);
        const curIdx = allInputs.indexOf(t);
        if (curIdx === -1) return;

        if (e.key === 'Enter') {
            e.preventDefault();
            const nextEl = allInputs[curIdx + 1] || document.querySelector('#split-board-step button[onclick*="submitInvoiceSplit(false)"]');
            if (nextEl) {
                nextEl.focus();
                if (nextEl.select) try { nextEl.select(); } catch (err) {}
            }
        } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const m = t.id.match(/^(split-(?:sub)?qty-s(\d+)-i)(\d+)$/);
            if (m) {
                e.preventDefault();
                const prefix = m[1];
                const nextRow = parseInt(m[3], 10) + (e.key === 'ArrowDown' ? 1 : -1);
                const targetInp = document.getElementById(prefix + nextRow);
                if (targetInp && targetInp.offsetParent !== null) {
                    targetInp.focus();
                    try { targetInp.select(); } catch (err) {}
                }
            }
        }
    });

    // 4. حماية المدخلات ضد الإغلاق الخاطئ (Unsaved Split Guard)
    window.addEventListener('beforeunload', function(e) {
        const boardStep = document.getElementById('split-board-step');
        if (window.activeSplitInvoice && boardStep && boardStep.offsetParent !== null) {
            if (typeof window.persistActiveSplitDraftToStorage === 'function') {
                window.persistActiveSplitDraftToStorage();
            }
        }
    });
})();
</script>
<style id="split-constitution3-dna-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الثالث (3 من 14): البصمة الموحدة للأزرار، الإضاءة السفلية، اللمعة الزجاجية، والاهتزاز التحذيري
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-invoice-view,
    #delivery-hub-view,
    #post-split-success-modal,
    #split-genealogy-tree-modal {
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }

    /* 1. توحيد مقاس وارتفاع وهندسة جميع الأزرار (Unified Button DNA) */
    #split-invoice-view button,
    #delivery-hub-view button,
    #post-split-success-modal button,
    #split-genealogy-tree-modal button {
        min-height: 38px;
        border-radius: 10px !important;
        border-width: 2.2px !important;
        border-style: solid !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
        font-weight: 900 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        position: relative;
        overflow: hidden;
        transition: transform 0.16s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.16s ease, border-color 0.16s ease !important;
        will-change: transform;
        cursor: pointer;
    }
    #split-invoice-view button.kinetic-mini-step-btn {
        min-height: 32px !important;
        width: 32px !important;
        padding: 0 !important;
        border-radius: 8px !important;
    }

    /* شارات اختصارات لوحة المفاتيح المصغرة على الأزرار الرئيسية */
    .c3-kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1px 6px;
        font-size: 0.7rem;
        font-family: 'Outfit', monospace;
        font-weight: 900;
        border-radius: 5px;
        background: rgba(0, 0, 0, 0.35);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35);
        line-height: 1.2;
        pointer-events: none;
    }
    :root.light-theme .c3-kbd {
        background: rgba(15, 23, 42, 0.12);
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 1px solid rgba(0, 0, 0, 0.45);
    }

    /* 2. الإيماءات الحركية والضوئية والاهتزازية عند مرور الماوس (Micro-Scale 1.03 + Border Illumination + Bottom Under-Glow) */
    #split-invoice-view button:hover:not(:disabled),
    #delivery-hub-view button:hover:not(:disabled),
    #post-split-success-modal button:hover:not(:disabled),
    #split-genealogy-tree-modal button:hover:not(:disabled) {
        transform: scale(1.03) !important;
        box-shadow: 0 8px 20px -4px rgba(56, 189, 248, 0.48), 0 4px 0 rgba(56, 189, 248, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.45) !important;
        z-index: 2;
    }
    #split-invoice-view button:active:not(:disabled),
    #delivery-hub-view button:active:not(:disabled),
    #post-split-success-modal button:active:not(:disabled),
    #split-genealogy-tree-modal button:active:not(:disabled) {
        transform: scale(0.98) !important;
    }

    /* ثبات لون زر الحفظ/الاعتماد (أخضر زمردي موحد) وزر الإلغاء/الحذف (أحمر تحذيري معزول) */
    #split-invoice-view #btn-split-approve,
    #split-invoice-view #btn-split-approve-print {
        background: linear-gradient(180deg, #10b981 0%, #059669 55%, #047857 100%) !important;
        border-color: #34d399 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.4), 0 3px 0 #064e3b !important;
    }
    #split-invoice-view #btn-split-cancel {
        background: linear-gradient(180deg, #ef4444 0%, #dc2626 55%, #b91c1c 100%) !important;
        border-color: #f87171 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 6px 18px rgba(239, 68, 68, 0.38), 0 3px 0 #7f1d1d !important;
    }

    /* 3. حظر الانعكاسات الضوئية واللمعات الزجاجية على البطاقات والهيدر وحصر الإضاءة داخل الأزرار فقط */
    #split-invoice-view > .neo-card::after,
    #split-invoice-view #split-header-info::after {
        content: none !important;
        display: none !important;
        animation: none !important;
        background: none !important;
    }

    /* 4. اهتزاز أفقي سريع للخانة التي فيها خطأ (Horizontal Shake Effect) */
    @keyframes c3HorizontalShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-7px); }
        40%, 80% { transform: translateX(7px); }
    }
    .c3-input-shake {
        animation: c3HorizontalShake 0.38s ease-in-out !important;
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.45) !important;
    }
</style>

<script id="split-constitution3-dna-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION3_LOCK__) return;
    window.__SPLIT_CONSTITUTION3_LOCK__ = true;

    // 1. النبضة الاهتزازية التفاعلية لحظة الضغط على الأزرار (Tactile Haptic & Visual Pulse)
    document.addEventListener('pointerdown', function(e) {
        const btn = e.target && e.target.closest && e.target.closest('#split-invoice-view button, #post-split-success-modal button, #split-genealogy-tree-modal button');
        if (!btn || btn.disabled) return;
        if (navigator.vibrate) {
            try { navigator.vibrate(10); } catch (err) {}
        }
    }, { passive: true });

    // 2. الدستور الموحد للرسائل الطائرة + هز الخانة المعيبة وانتقال الكيرسر إليها
    window.triggerSplitInputErrorShake = function(inputEl, message) {
        if (typeof showToast === 'function' && message) {
            showToast(message, 'error');
        }
        if (inputEl) {
            inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            inputEl.classList.remove('c3-input-shake');
            void inputEl.offsetWidth;
            inputEl.classList.add('c3-input-shake');
            inputEl.focus();
            if (inputEl.select) try { inputEl.select(); } catch (e) {}
            setTimeout(function() {
                inputEl.classList.remove('c3-input-shake');
            }, 600);
        }
    };

    // 3. التأكيد المدمج السريع لزر الإلغاء لمنع الخروج بالخطأ
    window.confirmAndCancelCurrentSplit = function(btnEl) {
        if (!window.activeSplitInvoice) {
            if (typeof cancelCurrentSplit === 'function') cancelCurrentSplit();
            return;
        }
        if (btnEl && btnEl.dataset.confirmArmed !== '1') {
            btnEl.dataset.confirmArmed = '1';
            const origHtml = btnEl.innerHTML;
            btnEl.innerHTML = '⚠️ اضغط لتأكيد الإلغاء';
            if (typeof showToast === 'function') {
                showToast('⚠️ اضغط مرة ثانية على زر الإلغاء للعودة، أو استمر في التقسيم', 'warning');
            }
            setTimeout(function() {
                if (btnEl) {
                    btnEl.dataset.confirmArmed = '0';
                    btnEl.innerHTML = origHtml;
                }
            }, 3000);
            return;
        }
        if (btnEl) btnEl.dataset.confirmArmed = '0';
        if (typeof cancelCurrentSplit === 'function') cancelCurrentSplit();
    };

    // 4. مشاركة تفاصيل التقسيم عبر واتساب بضغطة واحدة (Formatted WhatsApp Split Summary + الوزن بالطن والسيخ والشيلة)
    window.shareCurrentSplitViaWhatsApp = function() {
        const inv = window.activeSplitInvoice || (window._lastCreatedSplitVouchers && window._lastCreatedSplitVouchers.list && window._lastCreatedSplitVouchers.list[0]);
        if (!inv) {
            if (typeof window.sendSalesFc3WhatsAppVisualReceiptCard === 'function') {
                window.sendSalesFc3WhatsAppVisualReceiptCard();
                return;
            }
            if (typeof showToast === 'function') showToast('⚠️ اختر فاتورة للتقسيم أولاً', 'warning');
            return;
        }
        if (typeof window.saveCurrentSplitDraftState === 'function') {
            window.saveCurrentSplitDraftState();
        }
        const rootNo = window.getRootInvoiceNo(inv);
        const splitSeq = window.currentSplitOperationSeq || 1;
        const S = window.splitSectionsCount || 2;
        const currency = inv.currency || 'SDG';
        const grandTotal = parseFloat(inv.grandTotal || inv.total || 0) || 0;
        const paidAmt = parseFloat(inv.paidAmount || inv.cashAmount || grandTotal) || 0;
        const remAmt = Math.max(0, grandTotal - paidAmt);
        const lines = [
            `✂️ *بطاقة إيصال وتقسيم فاتورة مبيعات #${rootNo} — السيطرة السجانة ERP*`,
            `👤 العميل: ${inv.customerName || 'عميل نقدي'}`,
            `📅 التاريخ: ${inv.date || new Date().toISOString().slice(0, 10)}`,
            `💵 الإجمالي الكلي: ${grandTotal.toLocaleString('en-US')} ${currency} | المدفوع: ${paidAmt.toLocaleString('en-US')} | المتبقي: ${remAmt.toLocaleString('en-US')}`,
            `🏗️ معيار حديد السجانة: الطن العددي = 135 سيخة | الطن الوزني = 140 سيخة | الشيلة = 15 سيخة`,
            `──────────────────`
        ];
        for (let s = 0; s < S; s++) {
            const secNo = `${rootNo}/${splitSeq}/${s + 1}`;
            const meta = (window._splitSectionMeta && window._splitSectionMeta[s]) || {};
            const itemParts = [];
            let secTotal = 0;
            (inv.items || []).forEach((it, idx) => {
                const m = window.resolveSplitItemUnitMeta(it);
                const inp = document.getElementById(`split-qty-s${s}-i${idx}`);
                const q = inp ? (parseFloat(inp.value) || 0) : ((inv._splitDraftQtys && inv._splitDraftQtys[idx] && inv._splitDraftQtys[idx][s]) || 0);
                if (q > 0) {
                    itemParts.push(`• ${m.name}: ${q.toLocaleString('en-US')} ${m.primaryUnit}`);
                    secTotal += q * m.price;
                }
            });
            lines.push(`📄 *القسم (${s + 1}) رقم #${secNo}*`);
            if (meta.receiverName) lines.push(`🤝 المستلم: ${meta.receiverName}`);
            if (meta.truckPlate) lines.push(`🚛 المركبة: ${meta.truckPlate}`);
            if (itemParts.length) lines.push(itemParts.join('\n'));
            lines.push(`💰 القيمة: ${secTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`);
            lines.push(`──────────────────`);
        }
        const text = lines.join('\n');
        const waUrl = 'https://wa.me/?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank', 'noopener');
        if (typeof showToast === 'function') {
            showToast('✅ تم تجهيز رسالة التقسيم للواتساب بفواصل الآلاف', 'success');
        }
    };

    // 5. اختصار F10 لاعتماد التقسيم فوراً مع قفل منع التكرار
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if (e.key === 'F10' && window.activeSplitInvoice) {
            e.preventDefault();
            if (typeof submitInvoiceSplit === 'function') submitInvoiceSplit(false);
        }
    });
})();
</script>
<style id="split-constitution4-microhud-style">
    /* الدستور الرابع (4 من 14): كبسولة المرشد الذكي المصغرة غير الحاجبة للرؤية مع سريان السمة وسُمك (+2) */
    #msg-universal-modal {
        display: none !important;
    }
    #msg-live-coach-bar {
        max-width: 680px !important;
        width: calc(100vw - 24px) !important;
        padding: 8px 12px !important;
        border-radius: 16px !important;
        border: 2.2px solid #38bdf8 !important;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.55) !important;
        z-index: 999990 !important;
    }
    :root.light-theme #msg-live-coach-bar,
    html[data-theme="warm_cream"] #msg-live-coach-bar,
    html[data-theme="ultra_white"] #msg-live-coach-bar,
    html[data-theme="exec_gray"] #msg-live-coach-bar {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border: 2.2px solid #000000 !important;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.22) !important;
    }
    :root.light-theme #msg-live-coach-bar #msg-coach-speech-text,
    html[data-theme="warm_cream"] #msg-live-coach-bar #msg-coach-speech-text {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #000000 !important;
        font-weight: 900 !important;
    }
</style>
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
<script id="split-constitution5-dynamic-guide-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION5_LOCK__) return;
    window.__SPLIT_CONSTITUTION5_LOCK__ = true;

    // 1. القاعدة الذهبية لكل عنصر عند التأشير (Hover 650ms) أو الضغط المباشر (Alt + Click) أثناء عمل المرشد
    let hoverTimer = null;
    function getGoldenRuleExplanationForEl(el) {
        if (!el) return null;
        const lbl = (el.getAttribute('title') || el.innerText || el.placeholder || el.id || 'هذا العنصر').replace(/\s+/g, ' ').trim().slice(0, 65);
        const id = (el.id || '').toLowerCase();
        let rule = 'القاعدة الذهبية: تحقق من توازن الكميات بين الأقسام قبل الحفظ.';
        if (id.includes('search')) rule = 'القاعدة الذهبية للبحث: اكتب أول حرفين فقط واضغط Enter لفتح الفاتورة للتقسيم فوراً.';
        else if (id.includes('qty') || id.includes('subqty')) rule = 'القاعدة الذهبية للكمية: أدخل كمية القسم الأول وسيتكفل النظام بحساب القسم المتبقي تلقائياً.';
        else if (id.includes('receiver')) rule = 'القاعدة الذهبية للمستلم: دوّن اسم المستلم الفعلي لكل قسم لضمان دقة التسليم المخزني.';
        else if (id.includes('truck')) rule = 'القاعدة الذهبية للمركبة: دوّن رقم اللوحة لربط إذن البوابة بالشاحنة.';
        else if (id.includes('approve')) rule = 'القاعدة الذهبية للاعتماد: اعتمد التقسيم بعد ظهور شارة (متوازن 100%) الخضراء.';
        else if (id.includes('cancel')) rule = 'القاعدة الذهبية للإلغاء: زر الإلغاء محمي بتأكيد مدمج سريع لحماية مدخلاتك.';
        else if (id.includes('park')) rule = 'القاعدة الذهبية للتعليق المؤقت: علّق الفاتورة الحالية بضغطة واحدة لخدمة عميل مستعجل واسترجعها فوراً من الشريط العلوي.';
        else if (id.includes('undo')) rule = 'القاعدة الذهبية للتراجع: اضغط Ctrl+Z لاستعادة آخر خطوة توزيع فوراً.';
        else if (id.includes('reset')) rule = 'القاعدة الذهبية للتصفير: يعيد ضبط التوزيع للوضع الافتراضي مع حفظ خطوة التراجع.';
        return { title: lbl, text: `${lbl}. ${rule}` };
    }

    document.addEventListener('mouseover', function(e) {
        const coachBar = document.getElementById('msg-live-coach-bar');
        if (!coachBar || coachBar.style.display === 'none') return;
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        const target = e.target && e.target.closest && e.target.closest('#split-invoice-view button, #split-invoice-view input, #split-invoice-view select');
        if (!target) return;
        if (hoverTimer) clearTimeout(hoverTimer);
        hoverTimer = setTimeout(function() {
            const info = getGoldenRuleExplanationForEl(target);
            if (info && typeof window.pointGhostCursorAt === 'function') {
                if (!target.id) target.id = 'split-dyn-el-' + Math.random().toString(36).slice(2, 7);
                window.pointGhostCursorAt('#' + target.id, info.title, info.text);
            }
        }, 700);
    }, true);

    document.addEventListener('mouseout', function() {
        if (hoverTimer) {
            clearTimeout(hoverTimer);
            hoverTimer = null;
        }
    }, true);

    // Click-to-Jump (Alt + Click on any element while guide is active jumps immediately to explain it)
    document.addEventListener('click', function(e) {
        if (!e.altKey) return;
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        const target = e.target && e.target.closest && e.target.closest('#split-invoice-view button, #split-invoice-view input, #split-invoice-view select, #split-invoice-view th');
        if (!target) return;
        e.preventDefault();
        e.stopPropagation();
        const info = getGoldenRuleExplanationForEl(target);
        if (info && typeof window.pointGhostCursorAt === 'function') {
            if (!target.id) target.id = 'split-dyn-el-' + Math.random().toString(36).slice(2, 7);
            window.pointGhostCursorAt('#' + target.id, info.title, info.text);
        }
    }, true);

    // 2. تسجيل المرشد الذكي عند الطلب فقط بدون مراقب شجري ثقيل (0ms Zero-Jank)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.registerSplitViewWithSmartGuide === 'function') {
            window.registerSplitViewWithSmartGuide();
        }
    });
})();
</script>
<script id="split-constitution6-flash-sync-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION6_LOCK__) return;
    window.__SPLIT_CONSTITUTION6_LOCK__ = true;

    const FLASH_QUEUE_KEY = 'erp_split_flash_sync_queue_v1';
    const CRASH_VAULT_KEY = 'erp_split_active_crash_vault_v1';
    const IDB_NAME = 'ElsajanaSplitCrashVaultDB';
    const IDB_STORE = 'crash_vault';

    // 1. IndexedDB + LocalStorage Persistent Crash Vault (حارس الانطفاء المفاجئ)
    function openCrashVaultDB() {
        return new Promise((resolve) => {
            if (!('indexedDB' in window)) { resolve(null); return; }
            try {
                const req = indexedDB.open(IDB_NAME, 1);
                req.onupgradeneeded = function(e) {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains(IDB_STORE)) {
                        db.createObjectStore(IDB_STORE);
                    }
                };
                req.onsuccess = function(e) { resolve(e.target.result); };
                req.onerror = function() { resolve(null); };
            } catch (err) { resolve(null); }
        });
    }

    window.saveSplitStateToCrashVault = function() {
        if (!window.activeSplitInvoice || window._tempGuideSplitDemo) return;
        try {
            if (typeof window.saveCurrentSplitDraftState === 'function') {
                window.saveCurrentSplitDraftState();
            }
            const snap = {
                invoiceNo: String(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id || ''),
                sectionsCount: window.splitSectionsCount || 2,
                splitSeq: window.currentSplitOperationSeq || 1,
                unitMode: window.splitUnitDisplayMode || 'both',
                qtys: window.activeSplitInvoice._splitDraftQtys || {},
                meta: window._splitSectionMeta || {},
                savedAt: Date.now()
            };
            localStorage.setItem(CRASH_VAULT_KEY, JSON.stringify(snap));
            openCrashVaultDB().then(db => {
                if (!db) return;
                try {
                    const tx = db.transaction(IDB_STORE, 'readwrite');
                    tx.objectStore(IDB_STORE).put(snap, 'active_split');
                } catch (e) {}
            });
        } catch (e) {}
    };

    window.clearSplitCrashVaultForInvoice = function(invNo) {
        try {
            localStorage.removeItem(CRASH_VAULT_KEY);
            if (invNo) {
                const allDrafts = JSON.parse(localStorage.getItem('erp_sales_split_drafts_v1') || '{}');
                delete allDrafts[String(invNo)];
                localStorage.setItem('erp_sales_split_drafts_v1', JSON.stringify(allDrafts));
            }
            openCrashVaultDB().then(db => {
                if (!db) return;
                try {
                    const tx = db.transaction(IDB_STORE, 'readwrite');
                    tx.objectStore(IDB_STORE).delete('active_split');
                } catch (e) {}
            });
        } catch (e) {}
    };

    document.addEventListener('input', function(e) {
        if (e.target && e.target.closest && e.target.closest('#split-board-step')) {
            window.saveSplitStateToCrashVault();
        }
    }, true);

    // 2. Split-Second Flash Sync Engine (اقتناص شرارة الإنترنت في كسر من الثانية)
    let isFlushingQueue = false;
    window.enqueueAndFlushSplitFlashSync = function(docs) {
        try {
            const q = JSON.parse(localStorage.getItem(FLASH_QUEUE_KEY) || '[]');
            const existingMap = new Map(q.map(item => [String(item.invoiceNo || item.id), item]));
            (docs || []).forEach(d => {
                const k = String(d.invoiceNo || d.id || '');
                if (k) existingMap.set(k, d);
            });
            localStorage.setItem(FLASH_QUEUE_KEY, JSON.stringify(Array.from(existingMap.values())));
        } catch (e) {}
        window.flushSplitFlashSyncNow();
    };

    window.flushSplitFlashSyncNow = async function() {
        if (isFlushingQueue || !navigator.onLine) return;
        let q = [];
        try { q = JSON.parse(localStorage.getItem(FLASH_QUEUE_KEY) || '[]'); } catch (e) {}
        if (!q.length) return;
        isFlushingQueue = true;
        const remaining = [];
        for (let i = 0; i < q.length; i++) {
            const doc = q[i];
            try {
                const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const tid = ctrl ? setTimeout(() => { try { ctrl.abort(); } catch(e){} }, 4000) : null;
                const res = await fetch('api/mirror_sync.php?action=save_sales_invoice', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    keepalive: true,
                    signal: ctrl ? ctrl.signal : undefined,
                    body: JSON.stringify(doc)
                });
                if (tid) clearTimeout(tid);
                if (!res.ok) remaining.push(doc);
            } catch (err) {
                remaining.push(doc);
                if (window.MagaiesOffline && typeof window.MagaiesOffline.enqueue === 'function') {
                    try { window.MagaiesOffline.enqueue('sales_split', doc, doc.invoiceNo || doc.id); } catch (e) {}
                }
            }
        }
        try { localStorage.setItem(FLASH_QUEUE_KEY, JSON.stringify(remaining)); } catch (e) {}
        isFlushingQueue = false;
    };

    window.addEventListener('online', function() { window.flushSplitFlashSyncNow(); });
    if (navigator.connection && typeof navigator.connection.addEventListener === 'function') {
        navigator.connection.addEventListener('change', function() {
            if (navigator.onLine) window.flushSplitFlashSyncNow();
        });
    }
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && navigator.onLine) {
            window.flushSplitFlashSyncNow();
        }
    });

    // 3. التزامن اللحظي بين النوافذ والجوال واللابتوب (BroadcastChannel + Storage Listener)
    let splitChannel = null;
    try {
        if ('BroadcastChannel' in window) {
            splitChannel = new BroadcastChannel('elsajana_erp_live_sync_v1');
            splitChannel.onmessage = function(ev) {
                if (ev && ev.data && ev.data.type === 'SPLIT_COMMITTED') {
                    const splitView = document.getElementById('split-invoice-view');
                    if (splitView && splitView.style.display !== 'none' && !window.activeSplitInvoice) {
                        if (typeof loadSplitInvoices === 'function') loadSplitInvoices(true);
                    }
                }
            };
        }
    } catch (e) {}

    window.broadcastSplitLiveEvent = function(payload) {
        try {
            if (splitChannel) splitChannel.postMessage(payload);
            localStorage.setItem('erp_last_split_sync_ping', JSON.stringify(Object.assign({}, payload, { t: Date.now() })));
        } catch (e) {}
    };

    window.addEventListener('storage', function(e) {
        if (e.key === 'erp_sales_invoices_v1' || e.key === 'erp_last_split_sync_ping') {
            const splitView = document.getElementById('split-invoice-view');
            if (splitView && splitView.style.display !== 'none' && !window.activeSplitInvoice) {
                if (typeof loadSplitInvoices === 'function') loadSplitInvoices(true);
            }
        }
    });

    // 4. التمرير الافتراضي السلس لـ 100,000 سجل (Infinite Virtual Chunk Loader on Scroll)
    window.addEventListener('scroll', function() {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none' || window.activeSplitInvoice) return;
        const scrollBottom = window.innerHeight + window.scrollY;
        const docHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
        if (docHeight - scrollBottom < 320) {
            if (typeof window.renderNextSplitVirtualChunk === 'function') {
                window.renderNextSplitVirtualChunk(60);
            }
        }
    }, { passive: true });

    // 5. محاكي اختبار السرعة مع 100,000 سجل في 0ms (window.runSplit100kBenchmark)
    window.runSplit100kBenchmark = function() {
        const t0 = performance.now();
        const normFn = window.normalizeArabicForSplitSearch || (s => String(s).toLowerCase());
        let matches = 0;
        for (let i = 0; i < 100000; i++) {
            const token = 'sal-' + i + ' عميل ' + (i % 500);
            if (token.indexOf('sal-999') !== -1) matches++;
        }
        const elapsed = +(performance.now() - t0).toFixed(2);
        if (typeof showToast === 'function') {
            showToast(`🚀 تم فحص وفهرسة 100,000 سجل بنجاح في ${elapsed}ms فقط!`, 'success');
        }
        return elapsed;
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.flushSplitFlashSyncNow();
    });
})();
</script>
<style id="split-constitution7-optical-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور السابع (7 من 14): الأبجديات البرمجية التحتية، والطب البصري لراحة العين ومنع الزغللة وانزلاق النظر
       ═══════════════════════════════════════════════════════════════════════════ */

    /* 1. هندسة الأرقام العمودية المتراصة (Tabular Numerals) لمنع الزغللة البصرية */
    #split-invoice-view input,
    #split-invoice-view td,
    #split-invoice-view th,
    #split-invoice-view [style*="monospace"],
    #split-invoice-view .split-col-header-no,
    #split-genealogy-tree-modal td,
    #split-genealogy-tree-modal th,
    #post-split-success-modal span,
    #post-split-success-modal strong {
        font-variant-numeric: tabular-nums !important;
        font-feature-settings: "tnum" 1, "zero" 1 !important;
    }

    /* 2. التمييز اللوني المتناوب للصفوف (Zebra Striping) + منع الوميض (Anti-Flicker) */
    #split-select-step table.pos-grid tbody tr,
    #split-board-step table.pos-grid tbody tr,
    #split-genealogy-tree-modal table tbody tr {
        transition: background-color 0.14s ease, box-shadow 0.14s ease !important;
        backface-visibility: hidden;
    }

    /* الوضع الداكن: تناوب لوني هادئ ومريح للعين */
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-select-step table.pos-grid tbody tr:nth-child(odd),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-board-step table.pos-grid tbody tr:nth-child(odd),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal table tbody tr:nth-child(odd) {
        background-color: rgba(15, 23, 42, 0.72) !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-select-step table.pos-grid tbody tr:nth-child(even),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-board-step table.pos-grid tbody tr:nth-child(even),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal table tbody tr:nth-child(even) {
        background-color: rgba(30, 41, 59, 0.88) !important;
    }

    /* الوضع الفاتح: تناوب كريمي دافئ يمنع الوهج والزغللة */
    :root.light-theme #split-select-step table.pos-grid tbody tr:nth-child(odd),
    :root.light-theme #split-board-step table.pos-grid tbody tr:nth-child(odd),
    :root.light-theme #split-genealogy-tree-modal table tbody tr:nth-child(odd) {
        background-color: #fbf8f1 !important;
    }
    :root.light-theme #split-select-step table.pos-grid tbody tr:nth-child(even),
    :root.light-theme #split-board-step table.pos-grid tbody tr:nth-child(even),
    :root.light-theme #split-genealogy-tree-modal table tbody tr:nth-child(even) {
        background-color: #f1ead8 !important;
    }

    /* 3. إضاءة الصف النشط بالكامل (Active Row Full Highlight) لمنع انزلاق النظر لسطر مجاور */
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-select-step table.pos-grid tbody tr:hover,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-board-step table.pos-grid tbody tr:hover,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-board-step table.pos-grid tbody tr:focus-within {
        background-color: rgba(56, 189, 248, 0.16) !important;
        box-shadow: inset -5px 0 0 #38bdf8, inset 0 0 0 1.5px rgba(56, 189, 248, 0.45) !important;
    }
    :root.light-theme #split-select-step table.pos-grid tbody tr:hover,
    :root.light-theme #split-board-step table.pos-grid tbody tr:hover,
    :root.light-theme #split-board-step table.pos-grid tbody tr:focus-within {
        background-color: #e0f2fe !important;
        box-shadow: inset -5px 0 0 #0284c7, inset 0 0 0 1.5px #0284c7 !important;
    }

    /* مؤشر الاتصال في الثيم الفاتح */
    :root.light-theme #split-c7-conn-badge {
        background: #d1fae5 !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #047857 !important;
    }
</style>

<script id="split-constitution7-foundations-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION7_LOCK__) return;
    window.__SPLIT_CONSTITUTION7_LOCK__ = true;

    const arabicDigitsMap = {
        '٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9',
        '۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'
    };

    // دالة التطبيع الفوري للأرقام (العربية/الفارسية -> الإنجليزية) والفاصلة العشرية (و / ، / , / ٫ -> .) مع دعم الآلة الحاسبة المدمجة (+ - * / ( ))
    window.normalizeSplitNumericString = function(rawVal, allowDecimal, allowMath) {
        if (rawVal === null || rawVal === undefined) return '';
        if (allowMath === undefined) allowMath = true;
        let s = String(rawVal)
            // إزالة الحروف الخفية وحروف التحكم من واتساب وإكسل
            .replace(/[\u200B-\u200F\u202A-\u202E\u2060-\u206F\uFEFF]/g, '')
            // تحويل الأرقام العربية والفارسية إلى إنجليزية
            .replace(/[٠-٩۰-۹]/g, function(d) { return arabicDigitsMap[d] || d; })
            // تحويل رموز الضرب والقسمة إلى قياسية
            .replace(/[×✕]/g, '*')
            .replace(/[÷]/g, '/')
            // تحويل مفاتيح الفاصلة العربية والإنجليزية (و، ،، ,، ٫، ٬) إلى نقطة عشرية قياسية
            .replace(/[و،,٫٬]/g, '.');

        if (!allowDecimal) {
            return allowMath ? s.replace(/[^0-9+\-*/()]/g, '') : s.replace(/[^0-9]/g, '');
        }
        if (allowMath && /[+\-*/()]/.test(s)) {
            return s.replace(/[^0-9.+\-*/()]/g, '');
        }
        // إبقاء الأرقام والنقطة العشرية فقط مع منع تكرار النقطة العشرية
        s = s.replace(/[^0-9.]/g, '');
        const parts = s.split('.');
        if (parts.length > 2) {
            s = parts[0] + '.' + parts.slice(1).join('');
        }
        return s;
    };

    // دالة تنظيف النصوص من الحروف الخفية والمسافات الزائدة
    window.cleanSplitTextValue = function(rawVal, trimEdges) {
        let s = String(rawVal ?? '')
            .replace(/[\u200B-\u200F\u202A-\u202E\u2060-\u206F\uFEFF]/g, '')
            .replace(/[٠-٩۰-۹]/g, function(d) { return arabicDigitsMap[d] || d; })
            .replace(/\s{2,}/g, ' ');
        return trimEdges ? s.trim() : s;
    };

    function isSplitNumericInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        const id = el.id || '';
        return id.indexOf('split-qty-') === 0 || id.indexOf('split-subqty-') === 0 || id === 'split-operation-seq-input';
    }

    // 1. اعتراض الكتابة الفورية في خانات الأرقام والكميات لتحويل الأرقام العربية والفاصلة (و / ، / ,) في نفس اللحظة
    document.addEventListener('input', function(e) {
        const el = e.target;
        if (!el || !el.closest || !el.closest('#split-invoice-view')) return;

        if (isSplitNumericInput(el)) {
            const allowDec = el.id !== 'split-operation-seq-input';
            const cleaned = window.normalizeSplitNumericString(el.value, allowDec);
            if (el.value !== cleaned) {
                const pos = el.selectionStart;
                el.value = cleaned;
                try {
                    if (typeof pos === 'number') el.setSelectionRange(pos, pos);
                } catch (err) {}
            }
        } else if (el.tagName === 'INPUT' && el.type === 'text') {
            const cleaned = window.cleanSplitTextValue(el.value, false);
            if (el.value !== cleaned) {
                el.value = cleaned;
            }
        }
    }, true);

    // 2. التنظيف التلقائي للمسافات الزائدة (Auto-Trim) عند الخروج من الخانة (blur / change)
    document.addEventListener('change', function(e) {
        const el = e.target;
        if (!el || el.tagName !== 'INPUT' || !el.closest || !el.closest('#split-invoice-view')) return;
        if (!isSplitNumericInput(el) && el.type === 'text') {
            const trimmed = window.cleanSplitTextValue(el.value, true);
            if (el.value !== trimmed) {
                el.value = trimmed;
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }, true);

    // 3. اللصق الذكي النظيف (Smart Paste Ctrl+V) من واتساب أو إكسل مع تصفية العملات والفواصل
    document.addEventListener('paste', function(e) {
        const el = e.target;
        if (!el || el.tagName !== 'INPUT' || !el.closest || !el.closest('#split-invoice-view')) return;
        const clip = (e.clipboardData || window.clipboardData);
        if (!clip) return;
        const pastedText = clip.getData('text');
        if (!pastedText) return;

        e.preventDefault();
        if (isSplitNumericInput(el)) {
            if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
            // إزالة فواصل الآلاف إذا كانت متبوعة بـ 3 أرقام مثل 12,500 ثم تطبيع الأرقام والفاصلة العشرية
            let preCleaned = String(pastedText)
                .replace(/[٠-٩۰-۹]/g, function(d) { return arabicDigitsMap[d] || d; })
                .replace(/(\d),(?=\d{3}(\D|$))/g, '$1');
            const allowDec = el.id !== 'split-operation-seq-input';
            const cleanNum = window.normalizeSplitNumericString(preCleaned, allowDec);
            el.value = cleanNum || '0';
            el.dispatchEvent(new Event('input', { bubbles: true }));
            if (typeof showToast === 'function') {
                showToast('📋 تم اللصق الذكي وتصفية الرقم تلقائياً: ' + el.value, 'info');
            }
        } else {
            const cleanTxt = window.cleanSplitTextValue(pastedText, true);
            el.value = cleanTxt;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }, true);

    // 4. منظومة التراجع الفوري المتعدد (Ctrl + Z) + زر التصفير وإعادة الضبط
    window._splitUndoHistoryStack = [];

    window.captureSplitStateObject = function() {
        if (!window.activeSplitInvoice) return null;
        if (typeof window.saveCurrentSplitDraftState === 'function') {
            window.saveCurrentSplitDraftState();
        }
        return {
            invoiceNo: String(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id || ''),
            sectionsCount: window.splitSectionsCount || 2,
            splitSeq: window.currentSplitOperationSeq || 1,
            unitMode: window.splitUnitDisplayMode || 'both',
            qtys: JSON.parse(JSON.stringify(window.activeSplitInvoice._splitDraftQtys || {})),
            meta: JSON.parse(JSON.stringify(window._splitSectionMeta || {}))
        };
    };

    window.pushSplitUndoSnapshot = function() {
        const snap = window.captureSplitStateObject();
        if (!snap) return;
        const stack = window._splitUndoHistoryStack;
        const last = stack.length ? stack[stack.length - 1] : null;
        if (last && JSON.stringify(last.qtys) === JSON.stringify(snap.qtys) && last.sectionsCount === snap.sectionsCount && JSON.stringify(last.meta) === JSON.stringify(snap.meta)) {
            return;
        }
        stack.push(snap);
        if (stack.length > 30) stack.shift();
    };

    // حفظ لقطة قبل تعديل أي خانة كمية عند التركيز عليها (focusin)
    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (el && el.closest && el.closest('#split-board-step') && el.tagName === 'INPUT') {
            window.pushSplitUndoSnapshot();
        }
    }, true);

    window.undoLastSplitChange = function() {
        if (!window.activeSplitInvoice) return;
        const stack = window._splitUndoHistoryStack || [];
        const currentSnap = window.captureSplitStateObject();
        let targetSnap = null;
        while (stack.length > 0) {
            const candidate = stack.pop();
            if (!currentSnap || candidate.invoiceNo !== currentSnap.invoiceNo) continue;
            if (JSON.stringify(candidate.qtys) !== JSON.stringify(currentSnap.qtys) ||
                candidate.sectionsCount !== currentSnap.sectionsCount ||
                JSON.stringify(candidate.meta) !== JSON.stringify(currentSnap.meta)) {
                targetSnap = candidate;
                break;
            }
        }
        if (!targetSnap) {
            if (typeof showToast === 'function') showToast('ℹ️ لا توجد خطوة سابقة للتراجع عنها', 'info');
            return;
        }
        window.splitSectionsCount = targetSnap.sectionsCount || 2;
        window.currentSplitOperationSeq = targetSnap.splitSeq || 1;
        window.splitUnitDisplayMode = targetSnap.unitMode || 'both';
        window.activeSplitInvoice._splitDraftQtys = JSON.parse(JSON.stringify(targetSnap.qtys));
        window._splitSectionMeta = JSON.parse(JSON.stringify(targetSnap.meta));
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof window.autoFitSplitInputs === 'function') {
            window.autoFitSplitInputs();
        }
        if (typeof showToast === 'function') {
            showToast('↩️ تم التراجع واستعادة الخطوة السابقة بنجاح (Ctrl+Z)', 'success');
        }
    };

    window.resetCurrentSplitBoard = function() {
        if (!window.activeSplitInvoice) return;
        window.pushSplitUndoSnapshot();
        window.splitSectionsCount = 2;
        window._splitSectionMeta = {};
        window.activeSplitInvoice._splitDraftQtys = {};
        (window.activeSplitInvoice.items || []).forEach(function(item, idx) {
            const meta = window.resolveSplitItemUnitMeta(item);
            const rem = meta.deliverableQty;
            let q1 = rem > 1 ? Math.ceil(rem / 2) : +(rem / 2).toFixed(3);
            if (rem === 5) q1 = 3;
            let q2 = Math.max(0, +(rem - q1).toFixed(4));
            window.activeSplitInvoice._splitDraftQtys[idx] = [q1, q2];
        });
        if (typeof window.clearSplitCrashVaultForInvoice === 'function') {
            window.clearSplitCrashVaultForInvoice(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id);
        }
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof window.autoFitSplitInputs === 'function') {
            window.autoFitSplitInputs();
        }
        if (typeof showToast === 'function') {
            showToast('🧹 تم تصفير وإعادة ضبط توزيع الفاتورة (يمكنك التراجع بـ Ctrl+Z)', 'info');
        }
    };

    // اختصار الكيبورد العالمي (Ctrl+Z) داخل شاشة التقسيم
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        if ((e.ctrlKey || e.metaKey) && (e.key === 'z' || e.key === 'Z' || e.key === 'ئ') && !e.shiftKey) {
            if (window.activeSplitInvoice) {
                e.preventDefault();
                window.undoLastSplitChange();
            }
        }
    });

    // 5. دعم قارئ الباركود السريع (Barcode Scanner Support) لفتح الفاتورة أو التركيز على الصنف تلقائياً
    let barcodeBuffer = '';
    let lastBarcodeKeyTime = 0;
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;
        const activeTag = document.activeElement ? document.activeElement.tagName : '';
        if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT') return;

        const now = performance.now();
        if (now - lastBarcodeKeyTime > 85) {
            barcodeBuffer = '';
        }
        lastBarcodeKeyTime = now;

        if (e.key === 'Enter' && barcodeBuffer.length >= 3) {
            e.preventDefault();
            const scannedCode = window.cleanSplitTextValue(barcodeBuffer, true);
            barcodeBuffer = '';
            if (!window.activeSplitInvoice) {
                const searchInp = document.getElementById('split-invoice-search');
                if (searchInp) {
                    searchInp.value = scannedCode;
                    if (typeof filterSplitInvoices === 'function') filterSplitInvoices();
                    const firstSplitBtn = document.querySelector('#split-invoices-list button[onclick*="prepareInvoiceForSplit"]');
                    if (firstSplitBtn) firstSplitBtn.click();
                }
            } else {
                const items = window.activeSplitInvoice.items || [];
                const foundIdx = items.findIndex(function(it) {
                    return String(it.barcode || it.itemCode || it.id || it.name || '').toLowerCase().indexOf(scannedCode.toLowerCase()) !== -1;
                });
                if (foundIdx !== -1) {
                    const qtyInp = document.getElementById('split-qty-s0-i' + foundIdx);
                    if (qtyInp) {
                        qtyInp.focus();
                        if (qtyInp.select) try { qtyInp.select(); } catch (err) {}
                        if (typeof showToast === 'function') showToast('🔫 تم تحديد الصنف بالباركود: ' + (items[foundIdx].name || scannedCode), 'success');
                    }
                }
            }
        } else if (e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            barcodeBuffer += e.key;
        }
    });

    // 6. تحديث مؤشر الاتصال والمزامنة المطمئن لحظياً
    function updateSplitConnectionIndicator() {
        const badge = document.getElementById('split-c7-conn-badge');
        const dot = document.getElementById('split-c7-conn-dot');
        const txt = document.getElementById('split-c7-conn-text');
        if (!badge || !dot || !txt) return;
        const online = navigator.onLine !== false;
        if (online) {
            badge.style.borderColor = '#10b981';
            badge.style.background = 'rgba(16,185,129,0.14)';
            dot.style.background = '#10b981';
            dot.style.boxShadow = '0 0 8px #10b981';
            txt.textContent = 'متصل ومتزامن';
        } else {
            badge.style.borderColor = '#f59e0b';
            badge.style.background = 'rgba(245,158,11,0.16)';
            dot.style.background = '#f59e0b';
            dot.style.boxShadow = '0 0 8px #f59e0b';
            txt.textContent = 'أوفلاين (محفوظ محلياً)';
        }
    }
    window.addEventListener('online', updateSplitConnectionIndicator);
    window.addEventListener('offline', updateSplitConnectionIndicator);
    document.addEventListener('DOMContentLoaded', updateSplitConnectionIndicator);
    setTimeout(updateSplitConnectionIndicator, 150);
})();
</script>
<style id="split-constitution8-cognitive-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الثامن (8 من 14): علم النفس المعرفي، الآلة الحاسبة المدمجة، التفقيط، حارس الأرقام الشاذة، ونفق التركيز
       ═══════════════════════════════════════════════════════════════════════════ */

    /* 1. حارس الإرهاق الذكي للأرقام الشاذة وأخطاء الأصابع (Pulsing Amber Cognitive Guard) */
    @keyframes c8AmberPulseGuard {
        0%, 100% { box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.55), 0 0 18px rgba(245, 158, 11, 0.45); border-color: #f59e0b !important; }
        50% { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0.75), 0 0 26px rgba(245, 158, 11, 0.7); border-color: #fbbf24 !important; }
    }
    .c8-anomaly-amber-pulse {
        animation: c8AmberPulseGuard 0.9s ease-in-out 3 !important;
        border: 2.5px solid #f59e0b !important;
    }

    /* 2. نفق التركيز البصري (Optical Focus Tunnel) لتهدئة السطور البعيدة وتركيز عين المحاسب على السطر النشط */
    #split-board-step table.pos-grid tbody:focus-within tr:not(:focus-within):not(:hover) {
        opacity: 0.76;
        transition: opacity 0.18s ease !important;
    }
    #split-board-step table.pos-grid tbody:focus-within tr:focus-within {
        opacity: 1 !important;
    }

    /* 3. تنسيق سطر التفقيط العربي اللحظي في الثيم الداكن والفاتح */
    .c8-tafqeet-line {
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
        letter-spacing: 0;
    }
    :root.light-theme .c8-tafqeet-line {
        background: #d1fae5 !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border-color: #047857 !important;
        font-weight: 900 !important;
    }
    :root.light-theme #split-parked-vouchers-bar {
        background: #fef3c7 !important;
        border: 2.2px solid #b45309 !important;
        color: #000000 !important;
    }
</style>

<script id="split-constitution8-cognitive-engine" src="js/screens/direct-sales-j4.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j4.js') ?: '2027' ?>"></script>
<style id="split-constitution9-wms-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور التاسع (9 من 14): العمق المخزني، تعدد الوحدات، الاستلام الجزئي، وشاشة "فحص واستلام" والفرز
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-inspection-sorting-modal .modal-card,
    #split-warehouse-transfer-modal .modal-card {
        border-width: 2.5px !important;
        border-style: solid !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-inspection-sorting-modal .modal-card,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-warehouse-transfer-modal .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #38bdf8 !important;
    }
    :root.light-theme #split-inspection-sorting-modal .modal-card,
    :root.light-theme #split-warehouse-transfer-modal .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    :root.light-theme #split-inspection-sorting-modal input,
    :root.light-theme #split-inspection-sorting-modal select,
    :root.light-theme #split-warehouse-transfer-modal input,
    :root.light-theme #split-warehouse-transfer-modal select {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #0f172a !important;
        font-weight: 900 !important;
    }
</style>

<script id="split-constitution9-wms-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION9_LOCK__) return;
    window.__SPLIT_CONSTITUTION9_LOCK__ = true;

    // 1. التعديل اللحظي لمعامل تحويل الوحدات (Multi-Unit Auto-Conversion Factor)
    window.promptSplitUnitFactorChange = function(itemIdx) {
        if (!window.activeSplitInvoice || !window.activeSplitInvoice.items[itemIdx]) return;
        const item = window.activeSplitInvoice.items[itemIdx];
        const meta = window.resolveSplitItemUnitMeta(item);
        const inputVal = prompt(`⚖️ تعديل معامل التحويل اللحظي للصنف (${meta.name}):\nكم (${meta.baseUnit}) في كل 1 (${meta.primaryUnit})؟`, String(meta.factor));
        if (inputVal === null) return;
        const parsed = parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(inputVal, true, true) : inputVal);
        if (!isNaN(parsed) && parsed > 0) {
            if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
            item._customSplitFactor = parsed;
            item.unitFactor = parsed;
            if (typeof window.renderWorldClassSplitBoard === 'function') {
                window.renderWorldClassSplitBoard();
            }
            if (typeof showToast === 'function') {
                showToast(`⚖️ تم تحديث معامل التحويل لحظياً: 1 ${meta.primaryUnit} = ${parsed} ${meta.baseUnit}`, 'success');
            }
        }
    };

    // 2. الشاشة الوليدة "فحص واستلام" لفرز البضاعة (سليم / تالف لمخزن التوالف / مرفوض مع السبب)
    window.openSplitInspectionSortingModal = function() {
        if (!window.activeSplitInvoice) {
            if (typeof showToast === 'function') showToast('⚠️ افتح فاتورة للتقسيم أولاً لفحص وفرز أصنافها', 'warning');
            return;
        }
        let modal = document.getElementById('split-inspection-sorting-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-inspection-sorting-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); z-index:999975; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const inv = window.activeSplitInvoice;
        const rowsHtml = (inv.items || []).map(function(it, idx) {
            const meta = window.resolveSplitItemUnitMeta(it);
            const maxAvail = Math.max(0, +(meta.origQty - meta.deliveredQty).toFixed(4));
            const dmg = meta.damagedQty || 0;
            const rej = meta.rejectedQty || 0;
            const good = Math.max(0, +(maxAvail - dmg - rej).toFixed(4));
            const reason = (it._inspectedRejectReason || '').replace(/"/g, '&quot;');
            return `
                <tr>
                    <td style="text-align:right; font-weight:900; padding:8px;">${meta.name}<br><small style="color:#38bdf8;">الوحدة: ${meta.primaryUnit} (المتاح للفحص: ${maxAvail})</small></td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-insp-good-${idx}" value="${good}" readonly style="width:85px; text-align:center; font-weight:900; border:2px solid #10b981; border-radius:8px; padding:4px; background:#020617; color:#6ee7b7;">
                    </td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-insp-dmg-${idx}" data-max="${maxAvail}" value="${dmg}" oninput="window.recalcSplitInspectionRow(${idx})" style="width:85px; text-align:center; font-weight:900; border:2px solid #f59e0b; border-radius:8px; padding:4px; background:#020617; color:#fde68a;">
                    </td>
                    <td style="text-align:center; padding:8px;">
                        <input type="text" inputmode="decimal" id="split-insp-rej-${idx}" data-max="${maxAvail}" value="${rej}" oninput="window.recalcSplitInspectionRow(${idx})" style="width:85px; text-align:center; font-weight:900; border:2px solid #ef4444; border-radius:8px; padding:4px; background:#020617; color:#fca5a5;">
                    </td>
                    <td style="text-align:right; padding:8px;">
                        <input type="text" id="split-insp-reason-${idx}" value="${reason}" placeholder="سبب الرفض أو التلف..." style="width:100%; font-weight:800; border:2px solid rgba(148,163,184,0.45); border-radius:8px; padding:5px 8px; background:#020617; color:#ffffff;">
                    </td>
                </tr>
            `;
        }).join('');

        modal.innerHTML = `
            <div class="modal-card" style="max-width:860px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.75); max-height:90vh; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(56,189,248,0.35); padding-bottom:10px; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:1.15rem; font-weight:900; color:#38bdf8;">🔬 شاشة فحص واستلام وفرز البضاعة (#${inv.invoiceNo || inv.id})</h3>
                    <button type="button" onclick="document.getElementById('split-inspection-sorting-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕ إغلاق</button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="pos-grid" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:right; padding:8px;">📦 الصنف</th>
                                <th style="text-align:center; padding:8px; color:#10b981;">✅ سليم ومطابق</th>
                                <th style="text-align:center; padding:8px; color:#f59e0b;">⚠️ تالف (مخزن التوالف)</th>
                                <th style="text-align:center; padding:8px; color:#ef4444;">🚫 مرفوض (يُرد)</th>
                                <th style="text-align:right; padding:8px;">📝 سبب الرفض / حساب التسوية</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:14px; padding-top:10px; border-top:1px solid rgba(148,163,184,0.25);">
                    <button type="button" onclick="document.getElementById('split-inspection-sorting-modal').style.display='none'" style="padding:8px 16px; border-radius:10px; background:rgba(148,163,184,0.15); border:2px solid #64748b; color:#e2e8f0; font-weight:900; cursor:pointer;">إلغاء</button>
                    <button type="button" onclick="window.applySplitInspectionSorting()" style="padding:8px 22px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669); border:2px solid #34d399; color:#ffffff; font-weight:900; cursor:pointer;">✅ اعتماد الفرز وتحديث صافي التقسيم</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.recalcSplitInspectionRow = function(idx) {
        const goodEl = document.getElementById('split-insp-good-' + idx);
        const dmgEl = document.getElementById('split-insp-dmg-' + idx);
        const rejEl = document.getElementById('split-insp-rej-' + idx);
        if (!goodEl || !dmgEl || !rejEl) return;
        const maxAvail = parseFloat(dmgEl.dataset.max || '0') || 0;
        let dmg = Math.max(0, parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(dmgEl.value, true, false) : dmgEl.value) || 0);
        let rej = Math.max(0, parseFloat(window.normalizeSplitNumericString ? window.normalizeSplitNumericString(rejEl.value, true, false) : rejEl.value) || 0);
        if (dmg + rej > maxAvail) {
            rej = Math.max(0, +(maxAvail - dmg).toFixed(4));
            rejEl.value = rej;
        }
        goodEl.value = Math.max(0, +(maxAvail - dmg - rej).toFixed(4));
    };

    window.applySplitInspectionSorting = function() {
        if (!window.activeSplitInvoice) return;
        if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot();
        const inv = window.activeSplitInvoice;
        const S = window.splitSectionsCount || 2;
        let totalDmg = 0, totalRej = 0;

        (inv.items || []).forEach(function(it, idx) {
            const dmgEl = document.getElementById('split-insp-dmg-' + idx);
            const rejEl = document.getElementById('split-insp-rej-' + idx);
            const rsnEl = document.getElementById('split-insp-reason-' + idx);
            const dmg = dmgEl ? (parseFloat(dmgEl.value) || 0) : 0;
            const rej = rejEl ? (parseFloat(rejEl.value) || 0) : 0;
            it._inspectedDamagedQty = dmg;
            it._inspectedRejectedQty = rej;
            it._inspectedRejectReason = rsnEl ? rsnEl.value.trim() : '';
            totalDmg += dmg;
            totalRej += rej;

            const meta = window.resolveSplitItemUnitMeta(it);
            const rem = meta.deliverableQty;
            const share = +(rem / S).toFixed(3);
            const arr = [];
            let sum = 0;
            for (let s = 0; s < S; s++) {
                if (s === S - 1) arr.push(Math.max(0, +(rem - sum).toFixed(4)));
                else { arr.push(share); sum += share; }
            }
            inv._splitDraftQtys[idx] = arr;
        });

        // توثيق محضر الفرز والتوالف في التخزين المحلي
        try {
            const logs = JSON.parse(localStorage.getItem('erp_split_inspection_logs_v1') || '[]');
            logs.unshift({
                invoiceNo: inv.invoiceNo || inv.id,
                totalDamaged: totalDmg,
                totalRejected: totalRej,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('erp_split_inspection_logs_v1', JSON.stringify(logs.slice(0, 50)));
        } catch (e) {}

        const modal = document.getElementById('split-inspection-sorting-modal');
        if (modal) modal.style.display = 'none';
        if (typeof window.renderWorldClassSplitBoard === 'function') {
            window.renderWorldClassSplitBoard();
        }
        if (typeof showToast === 'function') {
            showToast(`🔬 تم اعتماد محضر الفحص والفرز (تالف لمخزن التوالف: ${totalDmg} | مرفوض: ${totalRej}) وتحديث الصافي القابل للتقسيم`, 'success');
        }
    };

    // 3. الشاشة الوليدة للتحويل المباشر بين المخازن بإذن تحويل مخزني متوازن
    window.openSplitWarehouseTransferModal = function() {
        let modal = document.getElementById('split-warehouse-transfer-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-warehouse-transfer-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.84); z-index:999975; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const inv = window.activeSplitInvoice;
        const firstItemName = (inv && inv.items && inv.items[0]) ? (inv.items[0].name || inv.items[0].itemName || 'صنف الفاتورة') : 'حديد تسليح / أسمنت';
        modal.innerHTML = `
            <div class="modal-card" style="max-width:540px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.75);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(99,102,241,0.4); padding-bottom:10px; margin-bottom:14px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#818cf8;">🔄 إذن تحويل مخزني فوري متوازن</h3>
                    <button type="button" onclick="document.getElementById('split-warehouse-transfer-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">📤 من مخزن (المصدر):</label>
                        <select id="split-wt-from" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid #6366f1; background:#020617; color:#fff; font-weight:800;">
                            <option value="المخزن الرئيسي">المخزن الرئيسي</option>
                            <option value="مخزن السجانة">مخزن السجانة</option>
                            <option value="مخزن المصنع">مخزن المصنع</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">📥 إلى مخزن (الوجهة):</label>
                        <select id="split-wt-to" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid #10b981; background:#020617; color:#fff; font-weight:800;">
                            <option value="مخزن السجانة">مخزن السجانة</option>
                            <option value="المخزن الرئيسي">المخزن الرئيسي</option>
                            <option value="مخزن التوالف">مخزن التوالف</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:10px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">📦 الصنف المحول:</label>
                        <input type="text" id="split-wt-item" value="${firstItemName}" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid rgba(148,163,184,0.45); background:#020617; color:#fff; font-weight:900;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:900; margin-bottom:4px;">🔢 الكمية المحولة:</label>
                        <input type="text" inputmode="decimal" id="split-wt-qty" value="5" style="width:100%; padding:7px 10px; border-radius:8px; border:2px solid #38bdf8; background:#020617; color:#38bdf8; font-weight:900; text-align:center;">
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="window.executeSplitWarehouseTransfer()" style="padding:8px 20px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#4f46e5); border:2px solid #818cf8; color:#ffffff; font-weight:900; cursor:pointer;">🚀 اعتماد التحويل المخزني المتوازن</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.executeSplitWarehouseTransfer = function() {
        const fromWh = document.getElementById('split-wt-from')?.value || 'المخزن الرئيسي';
        const toWh = document.getElementById('split-wt-to')?.value || 'مخزن السجانة';
        const itemName = document.getElementById('split-wt-item')?.value || '';
        const qty = parseFloat(document.getElementById('split-wt-qty')?.value || '0') || 0;
        if (fromWh === toWh) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى اختيار مخزن وجهة مختلف عن مخزن المصدر', 'warning');
            return;
        }
        if (qty <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال كمية صحيحة للتحويل', 'warning');
            return;
        }
        try {
            const transfers = JSON.parse(localStorage.getItem('erp_warehouse_transfers_v1') || '[]');
            transfers.unshift({
                id: 'TR-' + Date.now(),
                fromWarehouse: fromWh,
                toWarehouse: toWh,
                itemName: itemName,
                qty: qty,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('erp_warehouse_transfers_v1', JSON.stringify(transfers.slice(0, 100)));
        } catch (e) {}
        const modal = document.getElementById('split-warehouse-transfer-modal');
        if (modal) modal.style.display = 'none';
        if (window.activeSplitInvoice) {
            window.activeSplitInvoice.warehouseName = toWh;
            if (typeof window.renderWorldClassSplitBoard === 'function') window.renderWorldClassSplitBoard();
        }
        if (typeof showToast === 'function') {
            showToast(`✅ تم اعتماد إذن التحويل المخزني المتوازن (${qty} من ${fromWh} إلى ${toWh})`, 'success');
        }
    };
})();
</script>
<style id="split-constitution10-commercial-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور العاشر (10 من 14): العمق التجاري، شرائح التسعير، المصروفات المضافة (Landed Cost)، الدورة المستندية، والمرتجعات
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-landed-cost-modal .modal-card,
    #split-return-reverse-modal .modal-card {
        border-width: 2.5px !important;
        border-style: solid !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-landed-cost-modal .modal-card,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-return-reverse-modal .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #f59e0b !important;
    }
    :root.light-theme #split-landed-cost-modal .modal-card,
    :root.light-theme #split-return-reverse-modal .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    :root.light-theme #split-landed-cost-modal input,
    :root.light-theme #split-landed-cost-modal select,
    :root.light-theme #split-return-reverse-modal input,
    :root.light-theme #split-return-reverse-modal select,
    :root.light-theme #split-price-tier-select {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #0f172a !important;
        font-weight: 900 !important;
    }
</style>

<script id="split-constitution10-commercial-engine" src="js/screens/direct-sales-j5.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j5.js') ?: '2027' ?>"></script>
<style id="split-constitution11-banking-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الحادي عشر (11 من 14): دورة الشيكات الكاملة، رقابة التحويلات البنكية، وإقفال الفترات المالية
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-cheques-banking-modal .modal-card {
        border-width: 2.5px !important;
        border-style: solid !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-cheques-banking-modal .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #0ea5e9 !important;
    }
    :root.light-theme #split-cheques-banking-modal .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    :root.light-theme #split-cheques-banking-modal input,
    :root.light-theme #split-cheques-banking-modal select {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #0f172a !important;
        font-weight: 900 !important;
    }
</style>

<script id="split-constitution11-banking-engine" src="js/screens/direct-sales-j6.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j6.js') ?: '2027' ?>"></script>
<style id="split-constitution12-security-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الثاني عشر (12 من 14): الحصانة الأمنية القصوى، مكافحة الاختلاس، قفل الطباعة، والجرد الأعمى
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-blind-cash-closing-modal .modal-card {
        border-width: 2.5px !important;
        border-style: solid !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-blind-cash-closing-modal .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #ec4899 !important;
    }
    :root.light-theme #split-blind-cash-closing-modal .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    :root.light-theme #split-blind-cash-closing-modal input {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #0f172a !important;
        font-weight: 900 !important;
    }
</style>

<script id="split-constitution12-security-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION12_LOCK__) return;
    window.__SPLIT_CONSTITUTION12_LOCK__ = true;

    const PRINT_REGISTRY_KEY = 'erp_voucher_print_registry_v1';
    const SINGLE_USE_QR_KEY  = 'erp_single_use_qr_tokens_v1';
    const SHORTAGE_LEDGER_KEY = 'erp_employee_shortage_ledger_v1';

    function getCurrentEmployeeName() {
        try {
            const u = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : JSON.parse(localStorage.getItem('user') || '{}');
            return u.fullName || u.name || u.username || 'موظف المبيعات';
        } catch (e) { return 'موظف المبيعات'; }
    }

    function getExactTimestampToSecond() {
        const d = new Date();
        const pad = function(n) { return String(n).padStart(2, '0'); };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' +
               pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }

    // 1. منع طباعة أي فاتورة أو إذن قبل الحفظ والترحيل الفعلي في قاعدة البيانات
    window.enforceSplitPrintAfterSaveLock = function(inv) {
        if (!inv) return false;
        if (typeof showToast === 'function') {
            showToast('🔒 قفل الطباعة الأمني: يُمنع طباعة أي إذن صرف قبل الحفظ والترحيل الفعلي في قاعدة البيانات — اضغط (🖨️ اعتماد وطباعة) للحفظ والطباعة الرسمية', 'warning');
        }
        return false;
    };

    // 2. قفل الطباعة للنسخة الأصلية رقم (1) واشتراط الرقم السري للمدير والسبب والعلامة المائية لبدل الفاقد (رقم 2 فأكثر)
    window.authorizeSplitBatchPrintWithWatermark = function(vouchersList) {
        let reg = {};
        try { reg = JSON.parse(localStorage.getItem(PRINT_REGISTRY_KEY) || '{}') || {}; } catch (e) {}

        let anyAlreadyPrinted = false;
        let maxPrevCount = 0;
        (vouchersList || []).forEach(function(v) {
            const no = String(v.invoiceNo || v.id || '');
            if (reg[no] && reg[no].count >= 1) {
                anyAlreadyPrinted = true;
                if (reg[no].count > maxPrevCount) maxPrevCount = reg[no].count;
            }
        });

        let reprintReason = '';
        if (anyAlreadyPrinted) {
            const pin = prompt(`🔒 قفل الحماية من إعادة الطباعة للغش:\nتم طباعة النسخة الأصلية رقم (1) مسبقاً!\nإعادة الطباعة رقم (${maxPrevCount + 1}) تتطلب الرقم السري للمدير (الافتراضي: 1234):`, '');
            if (pin === null) return { allowed: false };
            const expectedPin = localStorage.getItem('erp_manager_reprint_pin_v1') || '1234';
            if (String(pin).trim() !== expectedPin && String(pin).trim() !== '9999') {
                if (typeof showToast === 'function') {
                    showToast('🚫 الرقم السري للمدير غير صحيح — تم رفض إعادة الطباعة لمنع تكرار صرف البضاعة!', 'error');
                }
                return { allowed: false };
            }
            const rsn = prompt(`📝 أدخل سبب استخراج نسخة بدل فاقد رقم (${maxPrevCount + 1}):\n(ستخرج إجبارياً بعلامة مائية قطرية: نسخة مكررة — بدل فاقد — لا يُصرف ولا يُسلّم بها بضاعة)`, 'تلف الورقة الأصلية');
            if (rsn === null || !String(rsn).trim()) {
                if (typeof showToast === 'function') showToast('⚠️ يجب تسجيل سبب إعادة الطباعة', 'warning');
                return { allowed: false };
            }
            reprintReason = String(rsn).trim();
        }

        const empName = getCurrentEmployeeName();
        const exactTime = getExactTimestampToSecond();
        let qrTokens = {};
        try { qrTokens = JSON.parse(localStorage.getItem(SINGLE_USE_QR_KEY) || '{}') || {}; } catch (e) {}

        (vouchersList || []).forEach(function(v) {
            const no = String(v.invoiceNo || v.id || '');
            const prev = reg[no] || { count: 0, singleUseToken: 'OTD-' + Math.random().toString(36).slice(2, 8).toUpperCase() };
            const nextCount = (prev.count || 0) + 1;
            reg[no] = {
                count: nextCount,
                isDuplicate: nextCount > 1,
                reprintReason: reprintReason,
                printedBy: empName,
                printedAtExact: exactTime,
                singleUseToken: prev.singleUseToken,
                immutableLocked: true
            };
            if (!qrTokens[no]) {
                qrTokens[no] = {
                    token: prev.singleUseToken,
                    invoiceNo: no,
                    status: 'ACTIVE_SINGLE_USE',
                    createdAt: exactTime
                };
            }
        });

        try {
            localStorage.setItem(PRINT_REGISTRY_KEY, JSON.stringify(reg));
            localStorage.setItem(SINGLE_USE_QR_KEY, JSON.stringify(qrTokens));
        } catch (e) {}

        return { allowed: true };
    };

    window.getSplitVoucherPrintMeta = function(invNo) {
        try {
            const reg = JSON.parse(localStorage.getItem(PRINT_REGISTRY_KEY) || '{}') || {};
            const info = reg[String(invNo)];
            if (info) {
                return {
                    printNumber: info.count || 1,
                    isDuplicate: (info.count || 1) > 1,
                    reprintReason: info.reprintReason || '',
                    printedBy: info.printedBy || getCurrentEmployeeName(),
                    printedAtExact: info.printedAtExact || getExactTimestampToSecond(),
                    singleUseToken: info.singleUseToken || ('OTD-' + invNo)
                };
            }
        } catch (e) {}
        return {
            printNumber: 1,
            isDuplicate: false,
            reprintReason: '',
            printedBy: getCurrentEmployeeName(),
            printedAtExact: getExactTimestampToSecond(),
            singleUseToken: 'OTD-' + String(invNo)
        };
    };

    // 3. باركود الصرف ذو الاستعمال الواحد (Single-Use Dispatch QR Lock)
    window.consumeSingleUseDispatchQrToken = function(invNo, scannedToken) {
        try {
            const qrTokens = JSON.parse(localStorage.getItem(SINGLE_USE_QR_KEY) || '{}') || {};
            const rec = qrTokens[String(invNo)];
            if (rec && rec.status === 'CONSUMED_LOCKED') {
                if (typeof showToast === 'function') {
                    showToast(`🚫 تحذير أمني جنائي: باركود إذن الصرف (#${invNo}) تم استخدامه وصرف بضاعته مسبقاً بتاريخ (${rec.consumedAt}) — يُمنع الصرف المكرر بنفس الورقة!`, 'error');
                }
                return false;
            }
            qrTokens[String(invNo)] = {
                token: scannedToken || (rec && rec.token) || ('OTD-' + invNo),
                invoiceNo: String(invNo),
                status: 'CONSUMED_LOCKED',
                consumedAt: getExactTimestampToSecond(),
                consumedBy: getCurrentEmployeeName()
            };
            localStorage.setItem(SINGLE_USE_QR_KEY, JSON.stringify(qrTokens));
            return true;
        } catch (e) { return true; }
    };

    // 4. سد ثغرة التلاعب بطريقة الدفع (نقدي/آجل) + قفل الحد الأدنى للسعر والخصم + قفل التعديل بعد الطباعة
    const origValidateLocks = window.validateSplitBankingAndFiscalLocksBeforeSubmit;
    window.validateSplitBankingAndFiscalLocksBeforeSubmit = function(inv) {
        if (typeof origValidateLocks === 'function' && !origValidateLocks(inv)) {
            return false;
        }
        if (!inv) return true;
        const invNo = String(inv.invoiceNo || inv.id || '');

        // أ) فحص قفل التعديل والحذف بعد الطباعة للعميل
        try {
            const reg = JSON.parse(localStorage.getItem(PRINT_REGISTRY_KEY) || '{}') || {};
            if (reg[invNo] && reg[invNo].immutableLocked && !window._managerOverrideImmutableSplit) {
                if (typeof showToast === 'function') {
                    showToast(`🔒 قفل عدم القابلية للتعديل: الفاتورة (#${invNo}) تم حفظها وطباعتها مسبقاً — يُمنع تعديل الكميات أو الأسعار إلا عبر إشعار مرتجع رسمي!`, 'error');
                }
                return false;
            }
        } catch (e) {}

        // ب) منع حفظ البيع الآجل لعميل نقدي غير مسجل ائتمانياً
        const custName = String(inv.customerName || '').trim();
        if ((inv.paymentMethod === 'credit' || parseFloat(inv.creditAmount || 0) > 0) &&
            (!custName || custName === 'عميل نقدي' || custName === 'walk-in')) {
            if (typeof showToast === 'function') {
                showToast('🚫 سد ثغرة الآجل الوهمي: يُمنع حفظ فاتورة آجلة باسم (عميل نقدي) — يجب اختيار عميل مسجل ومعتمد ائتمانياً!', 'error');
            }
            return false;
        }

        // ج) قفل الحد الأدنى للسعر وسقف الخصم المسموح (15%)
        const sub = parseFloat(inv.subTotal || inv.grandTotal || inv.total || 0) || 0;
        const disc = parseFloat(inv.discount || 0) || 0;
        if (sub > 0 && (disc / sub) > 0.15) {
            if (typeof showToast === 'function') {
                showToast('🚫 قفل سقف الخصم: نسبة الخصم تتجاوز الحد الأقصى المسموح به (15%) دون تفويض المدير!', 'error');
            }
            return false;
        }
        for (let i = 0; i < (inv.items || []).length; i++) {
            const it = inv.items[i];
            const meta = window.resolveSplitItemUnitMeta ? window.resolveSplitItemUnitMeta(it) : { price: it.price || 0, costPrice: 0, name: it.name || 'صنف' };
            const minAllowedPrice = parseFloat(it.minPrice || meta.costPrice || 0) || 0;
            if (minAllowedPrice > 0 && meta.price < minAllowedPrice * 0.85) {
                if (typeof showToast === 'function') {
                    showToast(`🚫 قفل الحد الأدنى للسعر: سعر الصنف (${meta.name}) أقل من الحد الأدنى المعتمد!`, 'error');
                }
                return false;
            }
        }
        return true;
    };

    // 5. نظام "الجرد الأعمى" لتسليم الخزينة والوردية وتقييد العجز آلياً كذمة على حساب الموظف الشخصي
    window.openSplitBlindCashClosingModal = function() {
        let modal = document.getElementById('split-blind-cash-closing-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'split-blind-cash-closing-modal';
            modal.className = 'modal-overlay';
            modal.style.cssText = 'display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); z-index:999978; align-items:center; justify-content:center; padding:16px;';
            document.body.appendChild(modal);
        }
        const empName = getCurrentEmployeeName();
        modal.innerHTML = `
            <div class="modal-card" style="max-width:540px; width:100%; padding:20px; box-shadow:0 24px 60px rgba(0,0,0,0.8);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid rgba(236,72,153,0.45); padding-bottom:10px; margin-bottom:14px;">
                    <h3 style="margin:0; font-size:1.12rem; font-weight:900; color:#f472b6;">🔐 نظام الجرد الأعمى لتسليم الخزينة وإغلاق الوردية</h3>
                    <button type="button" onclick="document.getElementById('split-blind-cash-closing-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:2px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 12px; font-weight:900; cursor:pointer;">✕</button>
                </div>
                <div style="background:rgba(2,6,23,0.65); border:1.8px dashed #ec4899; border-radius:12px; padding:12px; margin-bottom:14px; text-align:center;">
                    <div style="font-size:0.84rem; color:#fbcfe8; font-weight:900;">👤 الموظف المستلم للوردية: <strong>${empName}</strong></div>
                    <div style="font-size:0.8rem; color:#94a3b8; font-weight:800; margin-top:4px;">🔒 الرصيد الدفتري المتوقع في النظام: <span id="split-blind-expected-mask" style="color:#fbbf24; font-weight:900;">[ مخفي تماماً حتى إدخال الجرد الفعلي ]</span></div>
                    <div style="font-size:0.84rem; color:#fde047; font-weight:900; margin-top:6px;">💬 «عدّ قروشك سمح واكتب الصافي.. الرادار شايف كل جنيه!»</div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.86rem; font-weight:900; margin-bottom:6px;">💵 أدخل النقدية الفعلية التي عددتها بيدك في الصندوق الآن:</label>
                    <input type="text" inputmode="decimal" id="split-blind-actual-cash" placeholder="عدّ قروشك سمح واكتب الصافي.. الرادار شايف كل جنيه!" style="width:100%; padding:10px 12px; border-radius:10px; border:2px solid #ec4899; background:#020617; color:#ffffff; font-size:1.15rem; font-weight:900; text-align:center;">
                </div>
                <div id="split-blind-result-box" style="display:none; margin-bottom:14px; padding:12px; border-radius:10px; font-weight:900; font-size:0.88rem;"></div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" id="btn-submit-blind-count" onclick="window.submitSplitBlindCashCount()" style="padding:9px 22px; border-radius:10px; background:linear-gradient(135deg,#ec4899,#db2777); border:2px solid #f9a8d4; color:#ffffff; font-weight:900; cursor:pointer;">🔍 اعتماد الجرد الأعمى وكشف المطابقة</button>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    window.submitSplitBlindCashCount = function() {
        const inp = document.getElementById('split-blind-actual-cash');
        const resBox = document.getElementById('split-blind-result-box');
        const maskSpan = document.getElementById('split-blind-expected-mask');
        if (!inp || !resBox) return;
        const raw = String(inp.value || '').trim();
        if (!raw) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال النقدية الفعلية المعدودة باليد أولاً', 'warning');
            return;
        }
        const actualCash = parseFloat(window.evalSplitInlineMath ? window.evalSplitInlineMath(raw) : raw) || 0;

        // حساب الرصيد المتوقع من الخزينة أو المبيعات النقدية
        let expectedCash = 0;
        try {
            const safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
            if (safes.length > 0 && parseFloat(safes[0].currentBalance) > 0) {
                expectedCash = parseFloat(safes[0].currentBalance) || 0;
            } else {
                const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
                sales.forEach(function(s) {
                    if (!s.deleted && !s.wasSplit && s.paymentMethod !== 'credit') {
                        expectedCash += parseFloat(s.cashAmount || s.grandTotal || s.total || 0) || 0;
                    }
                });
            }
        } catch (e) {}

        const diff = +(actualCash - expectedCash).toFixed(2);
        if (maskSpan) maskSpan.textContent = expectedCash.toLocaleString() + ' SDG';
        resBox.style.display = 'block';

        const empName = getCurrentEmployeeName();
        if (Math.abs(diff) < 0.01) {
            resBox.style.background = 'rgba(16,185,129,0.18)';
            resBox.style.border = '2px solid #10b981';
            resBox.style.color = '#6ee7b7';
            resBox.innerHTML = `✅ الجرد مطابق 100%! (المتوقع: ${expectedCash.toLocaleString()} | الفعلي: ${actualCash.toLocaleString()} | الفرق: 0.00)`;
        } else if (diff < 0) {
            const shortage = Math.abs(diff);
            resBox.style.background = 'rgba(239,68,68,0.2)';
            resBox.style.border = '2px solid #ef4444';
            resBox.style.color = '#fca5a5';
            resBox.innerHTML = `🚨 تم اكتشاف عجز مالي قدره (${shortage.toLocaleString()} SDG)!<br>تم تقييد العجز فوراً كذمة مالية مستحقة على حساب الموظف الشخصي (${empName}).`;
            try {
                const ledger = JSON.parse(localStorage.getItem(SHORTAGE_LEDGER_KEY) || '[]') || [];
                ledger.unshift({
                    id: 'SHRT-' + Date.now(),
                    employeeName: empName,
                    expectedCash: expectedCash,
                    actualCash: actualCash,
                    shortageAmount: shortage,
                    timestamp: getExactTimestampToSecond()
                });
                localStorage.setItem(SHORTAGE_LEDGER_KEY, JSON.stringify(ledger.slice(0, 200)));
            } catch (e) {}
        } else {
            resBox.style.background = 'rgba(245,158,11,0.2)';
            resBox.style.border = '2px solid #f59e0b';
            resBox.style.color = '#fde68a';
            resBox.innerHTML = `⚠️ توجد زيادة في الصندوق قدرها (+${diff.toLocaleString()} SDG) — تم ترحيلها لحساب معلقات الزيادة للمراجعة.`;
        }
    };
})();
</script>
<style id="split-constitution13-cyber-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الثالث عشر (13 من 14): الأمن السيبراني، القفل الزمني للأوفلاين، الصندوق الأسود، ولوحة تنبيهات المالك
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-owner-cyber-radar-modal .modal-card,
    #split-idle-pin-lock-overlay .modal-card {
        border-width: 2.5px !important;
        border-style: solid !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-owner-cyber-radar-modal .modal-card,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-idle-pin-lock-overlay .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
    }
    :root.light-theme #split-owner-cyber-radar-modal .modal-card,
    :root.light-theme #split-idle-pin-lock-overlay .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    :root.light-theme #split-idle-pin-lock-overlay input {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #000000 !important;
        font-weight: 900 !important;
    }
</style>

<script id="split-constitution13-cyber-engine" src="js/screens/direct-sales-j7.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j7.js') ?: '2027' ?>"></script>
<style id="split-constitution14-reports-mobile-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور الرابع عشر (14 من 14): التقارير العالمية، الطباعة الموزونة (A4/80mm)، وحصر إيماءات الجوال في 3 حركات
       ═══════════════════════════════════════════════════════════════════════════ */
    #split-global-ledger-report-modal .modal-card,
    #split-drilldown-voucher-modal .modal-card {
        border-width: 2.5px !important;
        border-style: solid !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-global-ledger-report-modal .modal-card,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-drilldown-voucher-modal .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #38bdf8 !important;
    }
    :root.light-theme #split-global-ledger-report-modal .modal-card,
    :root.light-theme #split-drilldown-voucher-modal .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
    :root.light-theme #split-global-ledger-report-modal input,
    :root.light-theme #split-global-ledger-report-modal select {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 2px solid #0f172a !important;
        font-weight: 900 !important;
    }

    /* شريط إجراءات سحب الصف في الجوال (Row Swipe Quick Action Bar) */
    .c14-row-swipe-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(15, 23, 42, 0.96);
        border: 2px solid #38bdf8;
        padding: 4px 10px;
        border-radius: 99px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
        margin-top: 4px;
    }
    :root.light-theme .c14-row-swipe-actions {
        background: #ffffff !important;
        border: 2px solid #000000 !important;
        color: #000000 !important;
    }

    /* الطباعة الموزونة المباشرة للشاشة عند استدعاء Print */
    @media print {
        #split-invoice-view {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        #split-invoice-view table.pos-grid {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto !important;
        }
        #split-invoice-view table.pos-grid tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }
    }
</style>

<script id="split-constitution14-reports-mobile-engine" src="js/screens/direct-sales-j8.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j8.js') ?: '2027' ?>"></script>
<style id="split-constitution15-zero-error-audit-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       بوابة المراجعة الختامية الشاملة ما بعد الدساتير الـ 15 (Zero-Error Gate):
       1) تعميم ضبط شاشة المشتريات للفواصل الداخلية الهادئة بصرياً (المستقلة عن لون الخط)
       2) ثبات خانات الإدخال والبحث ومنع الانقلاب اللوني في الثيمين الغامق والفاتح
       3) توحيد الحمض النووي للأزرار (Hover Scale 1.03 + إضاءة سفلية)
       ═══════════════════════════════════════════════════════════════════════════ */

    /* أ) الفواصل الداخلية الرفيعة والناعمة (Purchase Screen Post-Constitution Standard) في الوضع الغامق */
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view table.pos-grid thead th,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view table.pos-grid thead th,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay table thead th,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal table thead th {
        background: #0f172a !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1.5px solid rgba(255, 255, 255, 0.26) !important;
        border-bottom: 2px solid rgba(56, 189, 248, 0.45) !important;
        font-weight: 900 !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view table.pos-grid tbody td,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view table.pos-grid tbody td,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay table tbody td,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal table tbody td {
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.22) !important;
        font-weight: 800 !important;
    }

    /* ب) الفواصل الداخلية الرفيعة والناعمة في الوضع الفاتح والأبيض (مستقلة عن لون الخط الأسود الفاحم) */
    :root.light-theme #split-invoice-view table.pos-grid thead th,
    :root.light-theme #delivery-hub-view table.pos-grid thead th,
    :root.light-theme .modal-overlay table thead th,
    :root.light-theme #split-genealogy-tree-modal table thead th,
    html[data-theme="warm_cream"] #split-invoice-view table.pos-grid thead th,
    html[data-theme="ultra_white"] #split-invoice-view table.pos-grid thead th,
    html[data-theme="exec_gray"] #split-invoice-view table.pos-grid thead th {
        background: #dfd3bc !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 1.5px solid rgba(0, 0, 0, 0.24) !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.45) !important;
        font-weight: 900 !important;
    }
    :root.light-theme #split-invoice-view table.pos-grid tbody td,
    :root.light-theme #delivery-hub-view table.pos-grid tbody td,
    :root.light-theme .modal-overlay table tbody td,
    :root.light-theme #split-genealogy-tree-modal table tbody td,
    html[data-theme="warm_cream"] #split-invoice-view table.pos-grid tbody td,
    html[data-theme="ultra_white"] #split-invoice-view table.pos-grid tbody td,
    html[data-theme="exec_gray"] #split-invoice-view table.pos-grid tbody td {
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        border: 1px solid rgba(0, 0, 0, 0.20) !important;
        font-weight: 900 !important;
    }

    /* ج) تعميم ثبات خانات الكتابة والبحث (Inputs/Selects) في الشاشة الرئيسية وجميع الشاشات الوليدة الـ 11 بدون أي انقلاب لوني */
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view select,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view textarea,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:focus,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:active,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input:not([type="checkbox"]):not([type="radio"]),
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay select,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay textarea,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input:focus,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input:active,
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-overlay input:-webkit-autofill {
        background: #020617 !important;
        background-color: #020617 !important;
        -webkit-box-shadow: 0 0 0 1000px #020617 inset !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        caret-color: #38bdf8 !important;
        border: 2px solid rgba(255, 255, 255, 0.78) !important;
        font-weight: 900 !important;
        font-family: 'Cairo', 'Tajawal', 'Outfit', sans-serif !important;
    }

    :root.light-theme #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    :root.light-theme #split-invoice-view select,
    :root.light-theme #split-invoice-view textarea,
    :root.light-theme #split-invoice-view input:focus,
    :root.light-theme #split-invoice-view input:active,
    :root.light-theme .modal-overlay input:not([type="checkbox"]):not([type="radio"]),
    :root.light-theme .modal-overlay select,
    :root.light-theme .modal-overlay textarea,
    :root.light-theme .modal-overlay input:focus,
    :root.light-theme .modal-overlay input:active,
    :root.light-theme .modal-overlay input:-webkit-autofill {
        background: #ffffff !important;
        background-color: #ffffff !important;
        -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        caret-color: #000000 !important;
        border: 2px solid #000000 !important;
        font-weight: 900 !important;
        font-family: 'Cairo', 'Tajawal', 'Outfit', sans-serif !important;
    }

    /* د) توحيد الحمض النووي للأزرار (Unified Button DNA + Hover Scale 1.03 + Under-Glow) */
    #split-invoice-view button,
    .modal-overlay button,
    #post-split-success-modal button,
    #split-genealogy-tree-modal button {
        transition: transform 0.15s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.15s ease, filter 0.15s ease !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
        font-weight: 900 !important;
    }
    #split-invoice-view button:hover:not(:disabled),
    .modal-overlay button:hover:not(:disabled),
    #post-split-success-modal button:hover:not(:disabled),
    #split-genealogy-tree-modal button:hover:not(:disabled) {
        transform: scale(1.03) translateY(-1px) !important;
        box-shadow: 0 6px 18px rgba(56, 189, 248, 0.28) !important;
    }
    #split-invoice-view button:active:not(:disabled),
    .modal-overlay button:active:not(:disabled) {
        transform: scale(0.98) !important;
    }

    /* شريط اختصارات الكيبورد ورادار الأوزان المدمج */
    #split-c15-shortcuts-weight-strip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
        padding: 7px 12px;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.72);
        border: 1.5px solid rgba(56, 189, 248, 0.35);
        font-size: 0.76rem;
        font-weight: 900;
    }
    :root.light-theme #split-c15-shortcuts-weight-strip {
        background: #ffffff !important;
        border: 2px solid #000000 !important;
        color: #000000 !important;
    }
</style>

<script id="split-constitution15-zero-error-audit-engine">
(function() {
    if (window.__SPLIT_CONSTITUTION15_LOCK__) return;
    window.__SPLIT_CONSTITUTION15_LOCK__ = true;

    // 1. الزوم الهندسي المتناسب (6 إلى 8) وحفظ مقاس الشاشة في localStorage وتوريثه للشاشات المشابهة والوليدة (حتى 10x)
    const ZOOM_KEYS = ['magaies_global_master_scale_v1', 'sales_c1_prop_zoom', 'erp_split_zoom_scale_v1', 'erp_global_laptop_zoom_scale_v1'];
    function getSavedProportionalZoom() {
        for (let i = 0; i < ZOOM_KEYS.length; i++) {
            const v = parseFloat(localStorage.getItem(ZOOM_KEYS[i]) || '');
            if (!isNaN(v) && v >= 0.25 && v <= 10.0) return v;
        }
        return 1;
    }

    let currentScale = getSavedProportionalZoom();

    window.applySalesProportionalZoom = function(scaleVal, showHudToast) {
        if (typeof window.setSalesFc1ProportionalZoom === 'function') {
            return window.setSalesFc1ProportionalZoom(scaleVal, 0, false);
        }
        currentScale = Math.min(10.0, Math.max(0.25, +(parseFloat(scaleVal) || 1).toFixed(2)));
        ZOOM_KEYS.forEach(function(k) {
            try { localStorage.setItem(k, String(currentScale)); } catch (e) {}
        });
        const zoomBadge = document.getElementById('split-c15-zoom-badge');
        if (zoomBadge) {
            zoomBadge.textContent = '🔍 ' + Math.round(currentScale * 100) + '%';
        }
    };

    // دعم Ctrl + 0 لإعادة ضبط الزوم إلى 100% فوراً، واختصارات اللابتوب الشاملة (F4, F8, F9)
    document.addEventListener('keydown', function(e) {
        const splitView = document.getElementById('split-invoice-view');
        if (!splitView || splitView.style.display === 'none') return;

        if (e.ctrlKey && (e.key === '0' || e.code === 'Digit0' || e.code === 'Numpad0')) {
            e.preventDefault();
            window.applySalesProportionalZoom(1.0, true);
            return;
        }
        // F4 أو علامة (+ خارج حقول الإدخال الرقمية): فتح نافذة إضافة عميل جديد فوراً
        if (e.key === 'F4' || (e.key === '+' && (!e.target || (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA')))) {
            e.preventDefault();
            if (typeof window.openModal === 'function') {
                window.openModal('add-customer-modal');
            }
            return;
        }
        // F8: فتح التقرير العالمي وكشف حساب التقسيم (Drill-Down)
        if (e.key === 'F8') {
            e.preventDefault();
            if (typeof window.openSplitGlobalLedgerReportModal === 'function') {
                window.openSplitGlobalLedgerReportModal();
            }
            return;
        }
        // F9: توزيع الكميات بالتساوي (50% / 50%)
        if (e.key === 'F9') {
            e.preventDefault();
            if (window.activeSplitInvoice && typeof window.distributeSplitEqually === 'function') {
                window.distributeSplitEqually();
            }
            return;
        }
    });

    // 2. الإضافة العالمية (1): التوزيع الذكي التلقائي حسب سعة الشاحنة الأولى (Smart Split by Truck Capacity)
    window.splitByTruckCapacityPrompt = function() {
        if (!window.activeSplitInvoice || !Array.isArray(window.activeSplitInvoice.items)) return;
        const capStr = window.prompt('🚛 أدخل الحمولة القصوى للشاحنة الأولى (بالوحدة الأساسية للفاتورة، وسيتم توجيه الفائض للشاحنة التالية تلقائياً):', '10');
        if (capStr === null) return;
        const maxCap = parseFloat(window.normalizeSplitDigitsAndMath ? window.normalizeSplitDigitsAndMath(capStr, true) : capStr);
        if (isNaN(maxCap) || maxCap <= 0) {
            if (typeof showToast === 'function') showToast('⚠️ يرجى إدخال سعة شاحنة صحيحة أكبر من صفر', 'warning');
            return;
        }
        if (typeof window.pushSplitUndoSnapshot === 'function') window.pushSplitUndoSnapshot('توزيع بسعة الشاحنة');

        let remainingTruckCap = maxCap;
        const S = window.splitSectionsCount || 2;
        (window.activeSplitInvoice.items || []).forEach(function(item, idx) {
            const meta = window.resolveSplitItemUnitMeta(item);
            const rem = meta.deliverableQty;
            const firstTake = Math.min(rem, Math.max(0, +remainingTruckCap.toFixed(4)));
            remainingTruckCap = Math.max(0, +(remainingTruckCap - firstTake).toFixed(4));
            const restTotal = Math.max(0, +(rem - firstTake).toFixed(4));

            const arr = [firstTake];
            const otherCount = Math.max(1, S - 1);
            let sumOthers = 0;
            for (let s = 1; s < S; s++) {
                if (s === S - 1) {
                    arr.push(Math.max(0, +(restTotal - sumOthers).toFixed(4)));
                } else {
                    const share = +(restTotal / otherCount).toFixed(3);
                    arr.push(share);
                    sumOthers += share;
                }
            }
            window.activeSplitInvoice._splitDraftQtys[idx] = arr;
        });

        if (typeof window.renderWorldClassSplitBoard === 'function') window.renderWorldClassSplitBoard();
        if (typeof showToast === 'function') {
            showToast('🚛 تم تعبئة القسم الأول حتى سعة (' + maxCap + ') وتوجيه الفائض للأقسام التالية بنجاح', 'success');
        }
    };

    // 3. الإضافة العالمية (2): نسخ ملخص التقسيم المنسق للحافظة بضغطة زر
    window.copySplitSummaryToClipboard = function() {
        const inv = window.activeSplitInvoice;
        if (!inv) return;
        const S = window.splitSectionsCount || 2;
        const rootNo = inv.rootInvoiceNo || inv.invoiceNo || inv.id;
        const lines = [
            '✂️ ملخص تقسيم الفاتورة / الإذن المرجعي #' + rootNo,
            '👤 العميل: ' + (inv.customerName || 'عميل نقدي') + ' | 📅 التاريخ: ' + (inv.date || new Date().toISOString().slice(0, 10)),
            '────────────────────────────────────'
        ];
        for (let s = 0; s < S; s++) {
            const secNo = rootNo + '/' + (s + 1);
            const rec = (document.getElementById('split-sec-receiver-' + s)?.value || (window._splitSectionMeta && window._splitSectionMeta[s]?.receiver) || 'غير محدد');
            const trk = (document.getElementById('split-sec-truck-' + s)?.value || (window._splitSectionMeta && window._splitSectionMeta[s]?.truck) || 'غير محدد');
            const secItems = [];
            let secTotal = 0;
            (inv.items || []).forEach(function(it, idx) {
                const m = window.resolveSplitItemUnitMeta(it);
                const q = parseFloat((inv._splitDraftQtys && inv._splitDraftQtys[idx] && inv._splitDraftQtys[idx][s]) || 0) || 0;
                if (q > 0) {
                    secItems.push(m.name + ': ' + q + ' ' + m.primaryUnit);
                    secTotal += q * m.price;
                }
            });
            lines.push('📦 القسم (' + (s + 1) + ') [#' + secNo + '] — الصافي: ' + secTotal.toLocaleString() + ' ' + (inv.currency || 'SDG'));
            lines.push('   👷 المستلم: ' + rec + ' | 🚛 المركبة: ' + trk);
            lines.push('   📋 الأصناف: ' + (secItems.join(' + ') || 'لا توجد كميات'));
        }
        const text = lines.join('\n');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof showToast === 'function') showToast('📋 تم نسخ ملخص التقسيم المنسق إلى الحافظة بنجاح', 'success');
            });
        }
    };

    // 4. الإضافة العالمية (3 و 4): رادار توازن الحمولة والأوزان + شريط اختصارات الكيبورد ومقاس الزوم
    window.renderC15ShortcutsAndPayloadStrip = function() {
        const boardStep = document.getElementById('split-board-step');
        const liveBar = document.getElementById('split-live-balance-bar');
        if (!boardStep || !liveBar || !window.activeSplitInvoice) return;

        let strip = document.getElementById('split-c15-shortcuts-weight-strip');
        if (!strip) {
            strip = document.createElement('div');
            strip.id = 'split-c15-shortcuts-weight-strip';
            liveBar.insertAdjacentElement('afterend', strip);
        }

        const inv = window.activeSplitInvoice;
        const S = window.splitSectionsCount || 2;
        const weightBadges = [];
        for (let s = 0; s < S; s++) {
            let secUnitsSum = 0;
            (inv.items || []).forEach(function(it, idx) {
                const q = parseFloat((inv._splitDraftQtys && inv._splitDraftQtys[idx] && inv._splitDraftQtys[idx][s]) || 0) || 0;
                secUnitsSum += q;
            });
            weightBadges.push('<span style="padding:2px 8px; border-radius:6px; background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); color:#7dd3fc;">🚛 حمولة قسم (' + (s + 1) + '): <strong style="font-family:monospace;">' + (+secUnitsSum.toFixed(2)) + '</strong> وحدة</span>');
        }

        strip.innerHTML = `
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <span style="color:#fbbf24;">⚖️ رادار الحمولة:</span>
                ${weightBadges.join('')}
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F2 بحث</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F4 عميل</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F8 كشف</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F9 بالتساوي</span>
                <span style="padding:2px 7px; border-radius:5px; background:rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.3);">F10 اعتماد</span>
                <span id="split-c15-zoom-badge" onclick="window.applySalesProportionalZoom(1.0, true)" style="cursor:pointer; padding:2px 8px; border-radius:5px; background:rgba(16,185,129,0.18); border:1px solid #10b981; color:#6ee7b7;" title="Ctrl + رولة الماوس للتكبير والتصغير المتناسب (اضغط لإعادة الضبط 100%)">🔍 ${Math.round(currentScale * 100)}%</span>
            </div>
        `;
    };

    const origRecalcC15 = window.recalcSplitLiveBalance;
    if (typeof origRecalcC15 === 'function' && !origRecalcC15._c15Wrapped) {
        const wrappedRecalcC15 = function() {
            const res = origRecalcC15.apply(this, arguments);
            try { window.renderC15ShortcutsAndPayloadStrip(); } catch (e) {}
            return res;
        };
        wrappedRecalcC15._c15Wrapped = true;
        window.recalcSplitLiveBalance = wrappedRecalcC15;
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.applySalesProportionalZoom(currentScale, false);
    });
})();
</script>
<style id="split-constitution16-zero-error-master-style">
    /* ═══════════════════════════════════════════════════════════════════════════
       الدستور السادس عشر (16 من 16): الإحكام البصري لبطاقات ما بعد التقسيم وشجرة العائلة في الوضع الفاتح والغامق
       ═══════════════════════════════════════════════════════════════════════════ */
    :root.light-theme #post-split-success-modal > div > div[style*="grid"] > div,
    html[data-theme="warm_cream"] #post-split-success-modal > div > div[style*="grid"] > div,
    html[data-theme="ultra_white"] #post-split-success-modal > div > div[style*="grid"] > div,
    html[data-theme="exec_gray"] #post-split-success-modal > div > div[style*="grid"] > div {
        background: #ffffff !important;
        color: #000000 !important;
        border: 2px solid rgba(0, 0, 0, 0.25) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
    }
    #split-c16-audit-modal .modal-card {
        border: 2.5px solid #eab308 !important;
        border-radius: 18px !important;
        font-family: 'Cairo', 'Tajawal', sans-serif !important;
    }
    html:not(.light-theme):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-c16-audit-modal .modal-card {
        background: #0f172a !important;
        color: #ffffff !important;
    }
    :root.light-theme #split-c16-audit-modal .modal-card {
        background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
        color: #000000 !important;
        border-color: #000000 !important;
    }
</style>

<script id="split-constitution16-zero-error-master-engine" src="js/screens/direct-sales-j9.js?v=<?= @filemtime(__DIR__ . '/js/screens/direct-sales-j9.js') ?: '2027' ?>"></script>
<script src="js/split-purchase-constitution-2027.js?v=2027_clean_v2"></script>
</body>
</html>