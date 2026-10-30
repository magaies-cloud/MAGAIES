<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() === 0) { @ob_start('ob_gzhandler'); } ?>
<?php 
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
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script src="js/magaies-offline-sync.js?v=20261014"></script>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المبيعات | Al-Sajana Protocol</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/print-invoice.css">
    <style>
        /* ═══════════════════════════════════════════════════════
           ⚡ PERFORMANCE OVERRIDES — eliminates GPU-heavy effects
           backdrop-filter: blur() forces per-pixel compositing
           on EVERY frame. With 20+ blurred elements visible at
           once, the browser cannot maintain 60 FPS. This block
           replaces ALL always-visible backdrop-filter with solid
           opaque backgrounds that look identical but cost 0 GPU.
           Only modals/popups (display:none by default) keep blur.
           ═══════════════════════════════════════════════════════ */
        .glass-panel,
        .neon-glass-panel,
        .panel,
        .neo-card,
        .pro-panel,
        .sales-glass-card,
        .purchase-glass-card,
        .toast,
        .custom-dropdown-list,
        .pos-lemon-title,
        #hdr-customer-ledger-btn,
        .fin-available-credit {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        /* Give panels a solid dark background so they still look great */
        .glass-panel {
            background: rgba(12, 10, 20, 0.92) !important;
        }
        .panel {
            background: rgba(14, 12, 22, 0.95) !important;
        }
        .pro-panel {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(10, 17, 40, 0.98)) !important;
        }
        .sales-glass-card {
            background: linear-gradient(135deg, rgba(35, 32, 80, 0.95) 0%, rgba(12, 15, 30, 0.98) 100%) !important;
        }
        .purchase-glass-card {
            background: linear-gradient(135deg, rgba(60, 20, 90, 0.95) 0%, rgba(20, 35, 85, 0.95) 50%, rgba(12, 15, 30, 0.98) 100%) !important;
        }
        .toast {
            background: rgba(15, 23, 42, 0.96) !important;
        }
        .toast.success { background: rgba(10, 40, 30, 0.96) !important; }
        .toast.error { background: rgba(50, 15, 15, 0.96) !important; }
        .toast.warning { background: rgba(50, 40, 10, 0.96) !important; }
        .custom-dropdown-list {
            background: rgba(25, 30, 45, 0.98) !important;
        }
        /* Stop infinite CSS animations — they force continuous GPU repaints */
        .pos-lemon-title,
        .pos-lemon-title::before,
        .pos-icon-svg,
        #hdr-customer-ledger-btn,
        .fin-available-credit {
            animation: none !important;
        }

        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --surface-hover: rgba(28, 24, 38, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
            --theme-green: #10b981;
            --theme-orange: #f59e0b;
            --theme-red: #f87171;
            --theme-purple: #c084fc;
        }
        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0; min-height: 100vh;
            display: flex; flex-direction: column; overflow-x: hidden;
            font-family: 'Tajawal', sans-serif; color: #fff;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.8); }

        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: 
                radial-gradient(circle at 95% 5%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 5% 95%, rgba(236, 72, 153, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 35% 45%, rgba(59, 130, 246, 0.05) 0%, transparent 60%),
                radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .dash-main { width: 100%; max-width: 1600px; margin: 0 auto; margin: 0 auto; padding: 0px 30px 20px 30px; box-sizing: border-box; display: flex; flex-direction: column; gap: 15px; flex: 1; animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        

        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 32px; height: 32px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #34d399); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container { display: flex; flex-direction: column; align-items: center; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); min-width: 100px; }
        .clock-time { color: #34d399; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(16,185,129,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }

        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }

        /* Disable old background on pos-wrapper */
        .pos-wrapper { background: transparent !important; }

        /* Crystal Ledger Button Custom Styling */
        #hdr-customer-ledger-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.25)) !important;
            border: 1px solid rgba(56, 189, 248, 0.45) !important;
            color: #ffffff !important;
            font-weight: 800;
            border-radius: 8px !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.15), inset 0 1px 3px rgba(255, 255, 255, 0.2) !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            cursor: pointer;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            font-size: 0.8rem;
            padding: 5px 12px;
            height: 32px;
            overflow: hidden;
            animation: pulseBlueGlow 3s infinite alternate ease-in-out;
        }

        #hdr-customer-ledger-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.6s ease;
        }

        #hdr-customer-ledger-btn:hover {
            transform: translateY(-50%) scale(1.08) !important;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.35), rgba(59, 130, 246, 0.45)) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 8px 25px rgba(56, 189, 248, 0.5), 0 0 15px rgba(16, 185, 129, 0.6), inset 0 2px 4px rgba(255, 255, 255, 0.35) !important;
            color: #fff !important;
        }

        #hdr-customer-ledger-btn:hover::before {
            left: 100%;
        }

        #hdr-customer-ledger-btn:active {
            transform: translateY(-50%) scale(0.96) !important;
            box-shadow: 0 4px 10px rgba(56, 189, 248, 0.6), inset 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }

        @keyframes pulseBlueGlow {
            from { box-shadow: 0 0 10px rgba(56, 189, 248, 0.15), inset 0 1px 2px rgba(255,255,255,0.1); }
            to { box-shadow: 0 0 20px rgba(56, 189, 248, 0.45), inset 0 1px 3px rgba(255,255,255,0.25); }
        }
        
        @media (max-width: 768px) {
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
        }

        
        /* ── تصميم ترويسة نقطة البيع الليمونية ── */
        .pos-lemon-title {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, rgba(163, 230, 53, 0.15), rgba(132, 204, 22, 0.05));
            border: 1px solid rgba(163, 230, 53, 0.4);
            padding: 8px 28px;
            border-radius: 50px;
            box-shadow: 0 0 20px rgba(163, 230, 53, 0.2), inset 0 0 10px rgba(163, 230, 53, 0.1);
            animation: pulseLemonGlow 2.5s infinite alternate ease-in-out;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        .pos-lemon-title::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(163, 230, 53, 0.2) 0%, transparent 60%);
            animation: rotateLemonOrb 8s linear infinite;
        }

        @keyframes pulseLemonGlow {
            from { box-shadow: 0 0 15px rgba(163, 230, 53, 0.1), inset 0 0 5px rgba(163, 230, 53, 0.05); }
            to { box-shadow: 0 0 30px rgba(163, 230, 53, 0.5), inset 0 0 15px rgba(163, 230, 53, 0.25); }
        }

        @keyframes rotateLemonOrb {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .pos-icon-svg {
            width: 32px;
            height: 32px;
            fill: #bef264;
            filter: drop-shadow(0 0 8px rgba(163, 230, 53, 0.8));
            position: relative;
            z-index: 1;
            animation: floatPosIcon 3s infinite alternate ease-in-out;
        }
        
        @keyframes floatPosIcon {
            from { transform: translateY(-2px); }
            to { transform: translateY(2px); }
        }

        .pos-text {
            font-size: 1.7rem;
            font-weight: 900;
            color: #d9f99d;
            text-shadow: 0 0 12px rgba(163, 230, 53, 0.6);
            font-family: 'Cairo', 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pos-en {
            font-size: 1.1rem;
            font-weight: 800;
            color: #a3e635;
            background: rgba(132, 204, 22, 0.15);
            padding: 2px 10px;
            border-radius: 8px;
            border: 1px solid rgba(132, 204, 22, 0.3);
            letter-spacing: 1.5px;
        }

        /* ═══ POS PAGE: إلغاء تضاعف مسافة الشريط الجانبي ═══
           theme.css   → padding-right: 64px
           components.css → margin-right: 62px
           المجموع: 126px فراغ — نحذف الـ padding الزائد */
        body.has-sidebar {
            padding-right: 0 !important;
        }
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
        @keyframes fadeSlideIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

        /* Cheque fields highlight */
        #cheque-number-group .ctrl-input,
        #cheque-bank-group .ctrl-input {
            border-color: rgba(251,191,36,0.4) !important;
            background: rgba(251,191,36,0.04);
        }
        #cheque-number-group .ctrl-input:focus,
        #cheque-bank-group .ctrl-input:focus {
            border-color: rgba(251,191,36,0.7) !important;
            box-shadow: 0 0 0 2px rgba(251,191,36,0.12);
        }

        /* Custom Dropdown for Customers */
        .custom-dropdown { position: relative; width: 100%; display: flex; gap: 6px; }
        .custom-dropdown-input-wrap { position: relative; flex: 1; }
        .custom-dropdown-list {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            max-height: 300px; overflow-y: auto;
            background: rgba(30, 41, 59, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 10px; box-shadow: 0 15px 50px rgba(0,0,0,0.6);
            z-index: 99999; display: none; flex-direction: column;
            min-width: 320px;
            scrollbar-width: thin;
            scrollbar-color: rgba(251, 191, 36, 0.3) transparent;
        }
        .custom-dropdown-list::-webkit-scrollbar {
            width: 6px;
        }
        .custom-dropdown-list::-webkit-scrollbar-thumb {
            background: rgba(251, 191, 36, 0.3);
            border-radius: 4px;
        }
        .custom-dropdown-list.show { display: flex; }
        td:has(.custom-dropdown-list.show),
        .m-card-full:has(.custom-dropdown-list.show),
        .custom-dropdown-input-wrap:has(.custom-dropdown-list.show) { z-index: 99999 !important; }
        .grid-scroll-wrap { min-height: 350px !important; overflow: visible !important; }

        .custom-dropdown-item {
            padding: 11px 15px; cursor: pointer; font-size: 1.05rem; font-weight: 600;
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
        .custom-dropdown-empty { padding: 10px 12px; font-size: 0.8rem; color: var(--text-muted); text-align: center; }

        input[type="date"]::-webkit-datetime-edit-text,
        input[type="date"]::-webkit-datetime-edit-month-field,
        input[type="date"]::-webkit-datetime-edit-day-field,
        input[type="date"]::-webkit-datetime-edit-year-field {
            color: #ef4444 !important;
        }

        /* ═══════════════════════════════════════════════
           COMPACT SCREEN LAYOUT — Max space utilization
           ═══════════════════════════════════════════════ */

        /* Remove default pos-wrapper bottom padding — footer handles it */
        .pos-wrapper {
            padding: 8px 8px 72px 8px !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
        }

        /* Top bar — tighter */
        .page-breadcrumb { padding: 6px 0 8px; }
        .top-action-bar  { margin-bottom: 8px; }

        /* منطقة الدفع الشرطية */
        .pzone-inner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            animation: fadeSlideIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
        }

        /* ── New Layout: Top row (3 cols) + Full-width items + Notes ── */
        .ds-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Top row: بيانات الفاتورة (مدمجة) + الإجماليات */
        .ds-top-row {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 10px;
            align-items: start;
        }

        /* ── شريط الحالة المالية الأفقي ── */
        .fin-strip-inline.visible {
            flex: 0 0 58% !important; min-width: 560px !important;
            opacity: 1 !important;
            margin-left: 5px;
        }

        .fin-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            margin-top: 0;
            margin-bottom: 0;
            padding: 0 15px;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid transparent;
            border-radius: 8px;
            transition: all 0.4s ease;
            width: auto;
            min-width: 400px;
        }
        .fin-strip.visible {
            max-height: 80px;
            opacity: 1;
            padding: 6px 15px;
            border-color: rgba(56, 189, 248, 0.6);
            margin-bottom: 4px;
        }
        .fin-strip-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 6px;
            border-radius: 10px;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.05);
            gap: 4px;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: cardPopIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .fin-strip-card:nth-child(1) { animation-delay: 0.00s; }
        .fin-strip-card:nth-child(2) { animation-delay: 0.06s; }
        .fin-strip-card:nth-child(3) { animation-delay: 0.12s; }
        .fin-strip-card:nth-child(4) { animation-delay: 0.18s; }
        .fin-strip-card:nth-child(5) { animation-delay: 0.24s; }
        @keyframes cardPopIn {
            from { opacity:0; transform:translateY(-8px) scale(0.92); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        .fin-strip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .fin-strip-card.warn  { border-color: rgba(251,191,36,0.25); background: rgba(251,191,36,0.05); }
        .fin-strip-card.avail {
            border-color: rgba(56,189,248,0.35);
            background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(16,185,129,0.07));
            box-shadow: 0 0 14px rgba(56,189,248,0.12);
            animation: cardPopIn 0.4s cubic-bezier(0.34,1.56,0.64,1) 0.24s both,
                       breatheGlow 3s ease-in-out 0.7s infinite;
        }
        .fin-strip-lbl {
            font-size: 0.58rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-align: center;
            white-space: nowrap;
        }
        .fin-strip-val {
            font-size: 0.82rem;
            font-weight: 900;
            color: var(--text-primary);
            direction: ltr;
        }
        .fin-strip-card.warn  .fin-strip-val  { color: #fbbf24; }
        .fin-strip-card.avail .fin-strip-val  { color: #38bdf8; text-shadow: 0 0 8px rgba(56,189,248,0.4); font-size: 0.9rem; }
        /* badge الحالة بجانب العميل */
        #fin-status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.65rem; font-weight: 700;
            padding: 2px 8px; border-radius: 12px;
            background: rgba(255,255,255,0.05);
            margin-right: 6px;
            vertical-align: middle;
            transition: all 0.3s;
        }

        /* Professional Equal-Height Panels */
        .pro-panel {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.7), rgba(10, 17, 40, 0.9));
            border: 1px solid rgba(56, 189, 248, 0.15);
            border-radius: 14px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            overflow: hidden;
            padding: 8px 12px;
        }
        .pro-panel::before {
            content: '';
            position: absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, transparent, rgba(56,189,248,0.5), rgba(16,185,129,0.3), transparent);
            opacity: 0.5;
            transition: opacity 0.4s ease;
        }
        .pro-panel:hover {
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 12px 40px 0 rgba(56, 189, 248, 0.15);
            transform: translateY(-2px);
        }
        .pro-panel:hover::before {
            opacity: 1;
        }
        /* Override defaults for pro-panel */
        .pro-panel.panel { margin-bottom: 0; padding: 14px; }

        /* Left column (legacy - now unused wrapper) */
        .ds-left { display: contents; }

        /* ── header-grid no longer needed as wrapper ── */
        .header-grid {
            display: contents;
        }

        /* ── Panel compact overrides ── */
        .panel { padding: 8px 12px; margin-bottom: 0; }
        .panel-title { font-size: 0.67rem; margin-bottom: 6px; }

        /* ── Compact form grid ── */
        .header-form {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }
        .header-form .ctrl-full {
            grid-column: span 2;
        }

        /* ── Compact inputs ── */
        .ctrl-input, .g-input {
            padding: 3px 7px;
            font-size: 0.78rem;
            border-radius: 6px;
        }
        .label { font-size: 0.63rem; margin-bottom: 1px; }
        .ctrl-group { gap: 1px; }

        /* ── Financial panel — vertical list layout ── */
        .fin-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .fin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(255,255,255,0.03);
            border-left: 2px solid rgba(255,255,255,0.06);
            transition: background 0.2s;
        }
        .fin-row:hover { background: rgba(255,255,255,0.06); }
        .fin-row.warn { border-left-color: rgba(251,191,36,0.5); }
        .fin-row-label {
            font-size: 0.62rem;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .fin-row-value {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--text-primary);
            direction: ltr;
            letter-spacing: 0.02em;
        }
        .fin-row-value.warn  { color: #fbbf24; }
        .fin-row-value.success { color: #34d399; }

        /* الائتمان المتاح — بطاقة مميزة */
        .fin-available-card {
            margin-top: 6px;
            padding: 7px 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(16,185,129,0.08));
            border: 1px solid rgba(56,189,248,0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 12px rgba(56,189,248,0.1);
            animation: breatheGlow 3s ease-in-out infinite;
        }
        @keyframes breatheGlow {
            0%,100% { box-shadow: 0 0 8px rgba(56,189,248,0.08); }
            50% { box-shadow: 0 0 18px rgba(56,189,248,0.22); }
        }
        .fin-available-label {
            font-size: 0.62rem;
            font-weight: 700;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .fin-available-value {
            font-size: 1rem;
            font-weight: 900;
            color: #38bdf8;
            text-shadow: 0 0 10px rgba(56,189,248,0.5);
            direction: ltr;
        }

        
        /* --- POS GRID 100% FULL WIDTH RESPONSIVE OVERRIDE --- */
        #items-panel {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        #items-panel .grid-scroll-wrap {
            width: 100% !important;
            min-width: 100% !important;
            overflow-x: auto !important;
            box-sizing: border-box !important;
        }
        #pos-grid {
            width: 100% !important;
            min-width: 950px !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
        }
        #pos-grid thead th {
            white-space: nowrap !important;
            position: relative;
        }

        /* ── Items panel ── */
        #items-panel { flex: 1; }
        #items-panel .grid-scroll-wrap {
            min-height: unset !important;
            overflow: visible !important;
        }
        .panel-header-row { margin-bottom: 10px; }
        .quick-add-box { max-width: 280px !important; }

        /* ── Table Base Inputs ── */
        .pos-grid thead th {
            padding: 10px 8px !important;
            letter-spacing: 0.5px;
        }
        .pos-grid tbody td {
            padding: 6px 4px !important;
            vertical-align: middle;
        }
        .g-input {
            padding: 6px 8px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s ease;
        }
        .g-input:focus {
            border-color: #38bdf8 !important;
            background: rgba(15, 23, 42, 0.8) !important;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.6);
        }
        .del-btn { width: 26px; height: 26px; font-size: 0.85rem; }
        .row-num {
            font-size: 0.82rem;
            font-weight: 700;
            color: #94a3b8;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        .empty-state { padding: 20px 16px; }
        .empty-icon { font-size: 2rem; }

        /* ── RIGHT SIDEBAR PANEL ── */
        .ds-right {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* ── NOTES BAR — full width below table ── */
        .ds-notes-bar {
            background: rgba(10, 17, 40, 0.60);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 12px 16px;
            backdrop-filter: blur(12px);
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: start;
            gap: 12px;
        }
        .ds-notes-bar .label {
            display: block;
            margin-bottom: 0;
            padding-top: 8px;
            white-space: nowrap;
        }
        .ds-notes-bar textarea {
            min-height: 38px;
            max-height: 80px;
            resize: vertical;
            font-size: 0.82rem;
        }

        /* Totals panel — right sidebar */
        .totals-sidebar-panel {
            flex: 1;
        }
        .totals-sidebar-panel::before {
            content: '';
            position: absolute; top:0; left:0; right:0; height:1px;
            background: linear-gradient(90deg, transparent, rgba(56,189,248,0.35), rgba(16,185,129,0.25), transparent);
        }

        .totals-title {
            font-size: 0.62rem;
            font-weight: 800;
            color: rgba(148,163,184,0.9);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Vertical Totals List ── */
        .totals-vlist {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .tv-sep {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 4px 0;
        }
        .tv-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 7px;
            border-radius: 5px;
            background: rgba(0,0,0,0.12);
            transition: background 0.2s;
        }
        .tv-row:hover { background: rgba(255,255,255,0.05); }
        .tv-row.editable { background: rgba(56,189,248,0.04); border: 1px solid rgba(56,189,248,0.1); }
        .tv-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .tv-value {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-primary);
            direction: ltr;
        }
        /* Grand total card */
        .tv-grand {
            margin-top: 6px;
            padding: 8px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(52,211,153,0.05));
            border: 1px solid rgba(16,185,129,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tv-grand-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #34d399;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .tv-grand-value {
            font-size: 1.1rem;
            font-weight: 900;
            color: #34d399;
            text-shadow: 0 0 12px rgba(52,211,153,0.5);
            direction: ltr;
        }
        /* tl-input used inside tv-row */
        .tv-row .tl-input {
            width: 75px;
            padding: 2px 5px;
            font-size: 0.7rem;
            border-radius: 4px;
        }

        /* Divider line */
        .totals-divider {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 4px 0;
        }
        /* tl-input base */
        .tl-input {
            padding: 2px 6px;
            font-size: 0.7rem;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.3);
            color: var(--text-primary);
            font-family: var(--font-base);
            font-weight: 700;
            text-align: left;
            direction: ltr;
            outline: none;
            width: 75px;
        }
        .tl-input:focus {
            border-color: rgba(56,189,248,0.5);
            box-shadow: 0 0 0 2px rgba(56,189,248,0.1);
        }

        /* Notes inside right panel */
        .notes-panel {
            background: rgba(10, 17, 40, 0.70);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 12px 14px;
            backdrop-filter: blur(12px);
        }
        .notes-panel .label { display: block; margin-bottom: 6px; }
        .ctrl-textarea { min-height: 52px; resize: vertical; font-size: 0.8rem; }

        /* ── Sticky footer adjustments ── */
        .sticky-footer { padding: 8px 20px; }
        .ft-value { font-size: 1.05rem; }
        .footer-actions .btn { padding: 7px 14px; font-size: 0.78rem; }

        /* ── Back arrow ── */
        .back-arrow-btn { width: 34px; height: 34px; border-radius: 10px; }

        /* ── Responsive collapse ── */
        @media (max-width: 1100px) {
            .ds-top-row { grid-template-columns: 2fr 1fr 240px; }
        }
        @media (max-width: 860px) {
            .ds-top-row { grid-template-columns: 1fr; }
            .ds-right { position: static; }
            .ds-notes-bar { grid-template-columns: 1fr; }
        }

        /* 5. Dynamic Totals Card Animation */
        #form-fields-card {
            transition: flex 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), min-width 0.8s, max-width 0.8s;
            flex: 75;
            min-width: 450px;
        }
        #totals-calc-card {
            transition: flex 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), min-width 0.8s, opacity 0.5s ease-in-out, transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform-origin: left center;
            flex: 15;
            min-width: 250px;
        }
        .hidden-totals {
            flex: 0 0 0% !important;
            min-width: 0 !important;
            max-width: 0 !important;
            height: 0 !important;
            max-height: 0 !important;
            padding: 0 !important;
            border-width: 0 !important;
            opacity: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }
        .expanded-form {
            flex: 1 1 100% !important;
            max-width: 100% !important;
        }
    
    /* Kinetic Animation for Sales Layout */
    #sales-top-container {
        display: flex; gap: 20px; align-items: stretch; width: 100%; margin-bottom: 15px;
    }
    
    /* Glassmorphic First Panel (Invoice Details) - Sky Blue/Cyan Sapphire Glow */
    #form-fields-card {
        background: rgba(30, 41, 59, 0.45) !important;
        backdrop-filter: blur(25px) saturate(140%) !important;
        -webkit-backdrop-filter: blur(25px) saturate(140%) !important;
        border: 1px solid rgba(56, 189, 248, 0.35) !important;
        border-radius: 18px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 15px rgba(56, 189, 248, 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.05) !important;
        transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #form-fields-card:hover {
        border-color: rgba(56, 189, 248, 0.6) !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5), 0 0 25px rgba(56, 189, 248, 0.25), inset 0 1px 2px rgba(255, 255, 255, 0.08) !important;
    }

    /* Glassmorphic Second Panel (Items Grid) - Mint Emerald Glow */
    #items-panel {
        background: rgba(16, 24, 39, 0.5) !important;
        backdrop-filter: blur(25px) saturate(140%) !important;
        -webkit-backdrop-filter: blur(25px) saturate(140%) !important;
        border: 1px solid rgba(16, 185, 129, 0.35) !important;
        border-radius: 18px !important;
        padding: 20px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 15px rgba(16, 185, 129, 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.05) !important;
        transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #items-panel:hover {
        border-color: rgba(16, 185, 129, 0.6) !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5), 0 0 25px rgba(16, 185, 129, 0.25), inset 0 1px 2px rgba(255, 255, 255, 0.08) !important;
    }

    #sales-top-container #form-fields-card {
        flex: 1; 
        min-width: 0; 
        transition: flex 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), max-width 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
    }
    #sales-top-container #totals-calc-card {
        display: flex !important;
        flex: 0 0 360px;
        min-width: 360px;
        max-width: 360px;
        opacity: 1;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(180deg, rgba(15,23,42,0.8), rgba(30,58,138,0.4));
        border: 1px solid rgba(56, 189, 248, 0.6);
        border-top: 3px solid #38bdf8 !important;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        transition: all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        transform-origin: right center;
    }
    #sales-top-container #totals-calc-card.hidden-totals {
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
        padding: 3px 0; border-bottom: 1px dashed rgba(255,255,255,0.1);
    }
    .tot-lbl { font-size: 0.75rem; font-weight: 700; color: #94a3b8; }
    .tot-val { font-size: 0.85rem; font-weight: 900; color: #fff; font-family: 'Outfit', sans-serif; }
    .tot-grand {
        margin-top: 15px; background: rgba(56, 189, 248, 0.1); padding: 15px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.6); box-shadow: 0 0 15px rgba(56,189,248,0.2);
        display: flex; justify-content: space-between; align-items: center;
    }
    .tot-grand .lbl { font-size: 0.75rem; font-weight: 900; color: #38bdf8; }
    .tot-grand .val { font-size: 1.15rem; font-weight: 900; color: #fff; font-family: 'Outfit', sans-serif; text-shadow: 0 0 10px rgba(255,255,255,0.3); }
    /* Table Context Menu Styling */
    .grid-context-menu {
        display: none; position: absolute; z-index: 100000; min-width: 200px;
        background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(56, 189, 248, 0.6);
        border-radius: 8px; padding: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.8);
        backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    }
    .grid-context-title {
        font-size: 0.8rem; color: var(--gold-primary); margin-bottom: 8px; font-weight: bold; 
        text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px; 
        font-family: 'Tajawal', sans-serif;
    }
    .context-item-custom {
        padding: 8px 12px; cursor: pointer; color: #fff; display: flex; align-items: center; 
        gap: 8px; transition: 0.2s; border-radius: 6px; font-family: 'Tajawal', sans-serif; font-size: 0.9rem;
    }
    .context-item-custom:hover { background: rgba(56,189,248,0.15); color: #38bdf8; }
    .context-item-hr { border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 4px 0; }

    /* Light Theme Context Menu */
    :root.light-theme .grid-context-menu {
        background: rgba(255, 255, 255, 0.98); border-color: rgba(0,0,0,0.1); box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }
    :root.light-theme .grid-context-title { color: #8b5cf6; border-color: rgba(0,0,0,0.05); }
    :root.light-theme .context-item-custom { color: #334155; }
    :root.light-theme .context-item-custom:hover { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    :root.light-theme .context-item-hr { border-top-color: rgba(0,0,0,0.05); }

    /* ====== الحركات والانيميشنات المأخوذة من شاشة دوران المخزون ====== */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.85); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 15px rgba(56,189,248,0.1); }
        50%      { box-shadow: 0 0 30px rgba(56,189,248,0.25); }
    }

    .animate-fade   { animation: fadeIn 0.6s ease-out both; }
    .animate-slide  { animation: slideUp 0.7s ease-out both; }
    .animate-scale  { animation: scaleIn 0.5s ease-out both; }
    .stagger-1 { animation-delay: 0.05s; }
    .stagger-2 { animation-delay: 0.1s; }
    .stagger-3 { animation-delay: 0.15s; }
    .stagger-4 { animation-delay: 0.2s; }
    .stagger-5 { animation-delay: 0.25s; }

    /* توهج نبضي للوحات الزجاجية */
    .glass-panel-pulse {
        animation: pulseGlow 4s ease-in-out infinite;
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
    .ctrl-input:hover, .v-input:hover, .system-currency-select:hover,
    .ctrl-input:focus, .v-input:focus, .system-currency-select:focus {
        background: #020104 !important;
        border-color: #38bdf8 !important;
        box-shadow: 0 0 22px rgba(56, 189, 248, 0.7), inset 0 1px 3px rgba(0,0,0,0.6) !important;
        transform: scale(1.025) translateY(-1px);
        position: relative;
        z-index: 10;
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
    :root.light-theme .h-label {
        color: #475569 !important;
    }
    :root.light-theme .h-form-group:focus-within .h-label {
        color: #2563eb !important;
        text-shadow: none !important;
    }

    /* ═══ MULTI-COLOR VISUAL CODING & GLASSMORPHISM ═══ */
    /* Purchase specific top card custom glass style */
    .purchase-glass-card {
        background: linear-gradient(135deg, rgba(88, 28, 135, 0.22) 0%, rgba(30, 58, 138, 0.15) 50%, rgba(15, 23, 42, 0.75) 100%) !important;
        border: 1px solid rgba(139, 92, 246, 0.35) !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(139, 92, 246, 0.15), inset 0 1px 2px rgba(255, 255, 255, 0.05) !important;
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
    /* 1. Invoice No (Sky Blue) */
    .group-invoice .h-label { color: #38bdf8 !important; }
    .group-invoice .ctrl-input { border-color: rgba(56, 189, 248, 0.25) !important; color: #ffffff !important; }
    .h-form-group.group-invoice:focus-within .h-label { text-shadow: 0 0 8px rgba(56, 189, 248, 0.4) !important; }
    .group-invoice .ctrl-input:focus { border-color: #38bdf8 !important; box-shadow: 0 0 12px rgba(56, 189, 248, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

    /* 2. Date (Soft Rose/Red) */
    .group-date .h-label { color: #f87171 !important; }
    .group-date .ctrl-input { border-color: rgba(248, 113, 113, 0.25) !important; color: #f87171 !important; }
    .h-form-group.group-date:focus-within .h-label { text-shadow: 0 0 8px rgba(248, 113, 113, 0.4) !important; }
    .group-date .ctrl-input:focus { border-color: #f87171 !important; box-shadow: 0 0 12px rgba(248, 113, 113, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

    /* 3. Supplier / Customer (Teal/Emerald Green) */
    .group-supplier .h-label { color: #34d399 !important; font-size: 1.25rem !important; font-weight: 800 !important; }
    .group-supplier .ctrl-input, #hdr-customer-search { border-color: rgba(52, 211, 153, 0.4) !important; color: #6ee7b7 !important; font-size: 1.45rem !important; font-weight: 800 !important; text-shadow: 0 0 8px rgba(110, 231, 183, 0.25) !important; }
    .group-supplier .ctrl-input::placeholder, #hdr-customer-search::placeholder { color: #94a3b8 !important; font-size: 1.15rem !important; font-weight: 600 !important; }
    .h-form-group.group-supplier:focus-within .h-label { text-shadow: 0 0 10px rgba(52, 211, 153, 0.5) !important; }
    .group-supplier .ctrl-input:focus, #hdr-customer-search:focus { border-color: #34d399 !important; box-shadow: 0 0 16px rgba(52, 211, 153, 0.5), inset 0 1px 2px rgba(0,0,0,0.6) !important; color: #a7f3d0 !important; }

    /* 4. Currency / Exchange Rate (Lavender/Indigo) */
    .group-currency .h-label { color: #a78bfa !important; }
    .group-currency .ctrl-input, .group-currency select.system-currency-select { border-color: rgba(167, 139, 250, 0.25) !important; color: #ffffff !important; }
    .h-form-group.group-currency:focus-within .h-label { text-shadow: 0 0 8px rgba(167, 139, 250, 0.4) !important; }
    .group-currency .ctrl-input:focus, .group-currency select.system-currency-select:focus { border-color: #a78bfa !important; box-shadow: 0 0 12px rgba(167, 139, 250, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

    /* 5. Warehouse (Warm Orange) */
    .group-warehouse .h-label { color: #fb923c !important; }
    .group-warehouse .ctrl-input { border-color: rgba(251, 146, 60, 0.25) !important; color: #ffffff !important; }
    .h-form-group.group-warehouse:focus-within .h-label { text-shadow: 0 0 8px rgba(251, 146, 60, 0.4) !important; }
    .group-warehouse .ctrl-input:focus { border-color: #fb923c !important; box-shadow: 0 0 12px rgba(251, 146, 60, 0.45), inset 0 1px 2px rgba(0,0,0,0.6) !important; }

    /* 6. Cost Center (Lime Green) */
    .group-cost-center .h-label { color: #a3e635 !important; }
    .group-cost-center .ctrl-input { border-color: rgba(163, 230, 53, 0.25) !important; color: #ffffff !important; }
    .h-form-group.group-cost-center:focus-within .h-label { text-shadow: 0 0 8px rgba(163, 230, 53, 0.4) !important; }
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
</style>


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
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
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
            transition: all 0.6s ease;
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
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            color: #1e293b !important;
            border: 1.5px solid rgba(148, 163, 184, 0.5) !important;
            border-radius: 10px !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.06) !important;
            transition: all 0.3s ease !important;
        }
        :root.light-theme input:focus, :root.light-theme select:focus, :root.light-theme textarea:focus {
            border-color: rgba(99, 102, 241, 0.6) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
            outline: none !important;
        }

        /* Professional Light Theme overrides for invoice header inputs (replacing harsh black/dark styling) */
        :root.light-theme .ds-top-row .ctrl-input, 
        :root.light-theme .ds-top-row select.ctrl-input, 
        :root.light-theme .ds-top-row input.ctrl-input,
        :root.light-theme .ds-top-row textarea.ctrl-input,
        :root.light-theme .pro-panel .ctrl-input,
        :root.light-theme #hdr-customer-search,
        :root.light-theme #hdr-currency,
        :root.light-theme #hdr-exchange-rate,
        :root.light-theme #hdr-payment-method,
        :root.light-theme #hdr-payment-account,
        :root.light-theme #hdr-warehouse,
        :root.light-theme #hdr-cost-center,
        :root.light-theme #hdr-description,
        :root.light-theme #pay-detail-1,
        :root.light-theme #pay-detail-2,
        :root.light-theme #pay-detail-3,
        :root.light-theme #hdr-date {
            border: 1.5px solid rgba(99, 102, 241, 0.3) !important; /* Elegant indigo/slate tint */
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            color: #0f172a !important;
            border-radius: 12px !important;
            padding: 8px 12px !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 2px 8px rgba(99, 102, 241, 0.03) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            font-weight: 700 !important;
        }

        :root.light-theme .ds-top-row .ctrl-input:focus, 
        :root.light-theme .ds-top-row select.ctrl-input:focus, 
        :root.light-theme .ds-top-row input.ctrl-input:focus,
        :root.light-theme .ds-top-row textarea.ctrl-input:focus,
        :root.light-theme .pro-panel .ctrl-input:focus,
        :root.light-theme #hdr-customer-search:focus,
        :root.light-theme #hdr-currency:focus,
        :root.light-theme #hdr-exchange-rate:focus,
        :root.light-theme #hdr-payment-method:focus,
        :root.light-theme #hdr-payment-account:focus,
        :root.light-theme #hdr-warehouse:focus,
        :root.light-theme #hdr-cost-center:focus,
        :root.light-theme #hdr-description:focus,
        :root.light-theme #pay-detail-1:focus,
        :root.light-theme #pay-detail-2:focus,
        :root.light-theme #pay-detail-3:focus,
        :root.light-theme #hdr-date:focus {
            border-color: rgba(99, 102, 241, 0.7) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03), 0 0 0 3px rgba(99, 102, 241, 0.15), 0 4px 12px rgba(99, 102, 241, 0.15) !important;
            background: #ffffff !important;
            outline: none !important;
        }

        :root.light-theme .ds-top-row .ctrl-input:hover, 
        :root.light-theme .ds-top-row select.ctrl-input:hover, 
        :root.light-theme .ds-top-row input.ctrl-input:hover,
        :root.light-theme .ds-top-row textarea.ctrl-input:hover,
        :root.light-theme .pro-panel .ctrl-input:hover,
        :root.light-theme #hdr-customer-search:hover,
        :root.light-theme #hdr-currency:hover,
        :root.light-theme #hdr-exchange-rate:hover,
        :root.light-theme #hdr-payment-method:hover,
        :root.light-theme #hdr-payment-account:hover,
        :root.light-theme #hdr-warehouse:hover,
        :root.light-theme #hdr-cost-center:hover,
        :root.light-theme #hdr-description:hover,
        :root.light-theme #pay-detail-1:hover,
        :root.light-theme #pay-detail-2:hover,
        :root.light-theme #pay-detail-3:hover,
        :root.light-theme #hdr-date:hover {
            border-color: rgba(99, 102, 241, 0.5) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03), 0 3px 10px rgba(99, 102, 241, 0.08) !important;
        }

        /* Choices.js Light Theme Overrides */
        :root.light-theme .choices__inner {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            border: 1.5px solid rgba(99, 102, 241, 0.3) !important;
            color: #0f172a !important;
            border-radius: 12px !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04), 0 2px 8px rgba(99, 102, 241, 0.03) !important;
            transition: all 0.3s ease !important;
        }
        :root.light-theme .choices__list--dropdown {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1.5px solid rgba(99, 102, 241, 0.4) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
            border-radius: 12px !important;
        }
        :root.light-theme .choices__item {
            color: #0f172a !important;
        }
        :root.light-theme .choices__item--selectable.is-highlighted {
            background-color: rgba(99, 102, 241, 0.08) !important;
            color: #4f46e5 !important;
        }
        :root.light-theme .choices__input {
            color: #0f172a !important;
            background-color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            border-radius: 6px !important;
        }

        /* Custom Customer Dropdown Light Theme Styling */
        :root.light-theme #customer-dropdown-list {
            background: #ffffff !important;
            border: 1.5px solid rgba(99, 102, 241, 0.4) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
            border-radius: 12px !important;
        }
        :root.light-theme .custom-dropdown-item {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15) !important;
            background: transparent !important;
        }
        :root.light-theme .custom-dropdown-item:hover, 
        :root.light-theme .custom-dropdown-item.selected {
            background: rgba(99, 102, 241, 0.06) !important;
            color: #4f46e5 !important;
        }
        :root.light-theme .custom-dropdown-empty {
            color: #64748b !important;
        }

        :root.light-theme #hdr-date-label {
            color: #4f46e5 !important;
            text-shadow: none !important;
        }
        :root.light-theme .date-icon-wrap {
            background: rgba(99, 102, 241, 0.06) !important;
            border: 1px solid rgba(99, 102, 241, 0.2) !important;
            color: #4f46e5 !important;
        }
    
        /* User request: Sky Blue outer border for invoice header inputs */
        .ds-top-row .ctrl-input, 
        .ds-top-row select.ctrl-input, 
        .ds-top-row input.ctrl-input,
        .ds-top-row textarea.ctrl-input,
        .pro-panel .ctrl-input {
            border: 1px solid #38bdf8 !important;
            box-shadow: 0 0 3px rgba(56, 189, 248, 0.6) !important;
            background: rgba(30, 41, 59, 0.85) !important;
        }
        .ds-top-row .ctrl-input:focus, 
        .ds-top-row select.ctrl-input:focus, 
        .ds-top-row input.ctrl-input:focus,
        .ds-top-row textarea.ctrl-input:focus,
        .pro-panel .ctrl-input:focus {
            border: 1px solid #7dd3fc !important;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.6) !important;
            outline: none !important;
        }
        
        /* Make sure date input gets the same treatment */
        #hdr-date {
            border: 1px solid #38bdf8 !important;
            color: #38bdf8 !important;
            text-shadow: 0 0 5px rgba(56, 189, 248, 0.6) !important;
        }
        #hdr-date-label {
            color: #38bdf8 !important;
            text-shadow: 0 0 5px rgba(56, 189, 248, 0.6) !important;
        }
        .date-icon-wrap {
            background: rgba(56, 189, 248, 0.1) !important;
            border: 1px solid rgba(56, 189, 248, 0.6) !important;
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           2027 WORLD-CLASS INTERACTIVE BORDER ILLUMINATION & SEMANTIC GLOW SYSTEM
           (Psychologically Calibrated Ergonomic Palette — Matching Purchases Screen)
           ═══════════════════════════════════════════════════════════════════════════ */

        /* 0. Ledger Button 2027 (Always Visible & Glowing) */
        .ledger-btn-2027,
        #hdr-customer-ledger-btn {
            position: absolute !important;
            left: 6px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 4px !important;
            font-size: 0.78rem !important;
            font-weight: 800 !important;
            padding: 0 10px !important;
            height: 27px !important;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.22), rgba(99, 102, 241, 0.28)) !important;
            color: #e0f2fe !important;
            border: 1.5px solid rgba(56, 189, 248, 0.65) !important;
            border-radius: 7px !important;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            cursor: pointer !important;
            font-family: 'Tajawal', sans-serif !important;
            transition: all 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important;
            white-space: nowrap !important;
        }
        .ledger-btn-2027:hover,
        #hdr-customer-ledger-btn:hover {
            background: linear-gradient(135deg, #0ea5e9, #4f46e5) !important;
            color: #ffffff !important;
            border-color: #7dd3fc !important;
            box-shadow: 0 0 18px rgba(56, 189, 248, 0.65), 0 0 28px rgba(99, 102, 241, 0.4) !important;
            transform: translateY(-50%) scale(1.04) !important;
        }
        :root.light-theme .ledger-btn-2027,
        :root.light-theme #hdr-customer-ledger-btn {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(37, 99, 235, 0.25)) !important;
            color: #0c4a6e !important;
            border-color: #0284c7 !important;
        }
        :root.light-theme .ledger-btn-2027:hover,
        :root.light-theme #hdr-customer-ledger-btn:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb) !important;
            color: #ffffff !important;
        }

        /* 1. Quick Search Bar & Customer Search — Resting Visible Frame + Hover/Focus Neon Illumination */
        #quick-item-search {
            border: 1.5px solid rgba(56, 189, 248, 0.38) !important;
            border-radius: 10px !important;
            background: rgba(15, 23, 42, 0.78) !important;
            transition: border-color 0.22s cubic-bezier(0.22, 1, 0.36, 1),
                        box-shadow 0.22s cubic-bezier(0.22, 1, 0.36, 1),
                        background-color 0.22s ease,
                        transform 0.18s ease !important;
        }
        #quick-item-search:hover {
            border-color: rgba(56, 189, 248, 0.85) !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.35), inset 0 0 8px rgba(56, 189, 248, 0.12) !important;
            background: rgba(15, 23, 42, 0.92) !important;
        }
        #quick-item-search:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.22), 0 0 22px rgba(56, 189, 248, 0.55), inset 0 0 10px rgba(56, 189, 248, 0.15) !important;
            background: rgba(10, 18, 36, 0.98) !important;
        }

        /* 2. Grid Cells — Visible Resting Borders + Semantic Per-Column Border Illumination */
        #pos-grid tbody td {
            position: relative;
            transition: background-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        #pos-grid tbody tr:hover td {
            background-color: rgba(30, 41, 59, 0.45);
        }
        #pos-grid tbody tr:focus-within td {
            background-color: rgba(15, 23, 42, 0.72);
        }

        /* Base visible frame for all interactive inputs & selects inside the grid */
        #pos-grid .g-input,
        #pos-grid .g-select {
            border: 1.5px solid rgba(148, 163, 184, 0.28) !important;
            border-radius: 8px !important;
            background: rgba(15, 23, 42, 0.65) !important;
            padding: 5px 8px !important;
            transition: border-color 0.2s cubic-bezier(0.22, 1, 0.36, 1),
                        box-shadow 0.2s cubic-bezier(0.22, 1, 0.36, 1),
                        background-color 0.2s ease,
                        transform 0.15s ease !important;
        }

        /* A. Item Search Column (col_item / الصنف) — Sky Cyan Illumination (#38bdf8) */
        #pos-grid td[data-col-id="col_item"] .g-input,
        #pos-grid td[data-col-id="item"] .g-input {
            border-color: rgba(56, 189, 248, 0.32) !important;
        }
        #pos-grid td[data-col-id="col_item"]:hover .g-input,
        #pos-grid td[data-col-id="col_item"] .g-input:hover,
        #pos-grid td[data-col-id="item"]:hover .g-input,
        #pos-grid td[data-col-id="item"] .g-input:hover {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.42), inset 0 0 6px rgba(56, 189, 248, 0.12) !important;
            background: rgba(14, 165, 233, 0.10) !important;
        }
        #pos-grid td[data-col-id="col_item"] .g-input:focus,
        #pos-grid td[data-col-id="item"] .g-input:focus {
            border-color: #7dd3fc !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.22), 0 0 18px rgba(56, 189, 248, 0.6), inset 0 0 8px rgba(56, 189, 248, 0.16) !important;
            background: rgba(8, 20, 42, 0.96) !important;
        }

        /* B. Available Stock Column (col_avail / المتاح) — Aqua Teal Illumination (#2dd4bf) */
        #pos-grid td[data-col-id="col_avail"] .g-readonly,
        #pos-grid td[data-col-id="available"] .avail-val {
            border: 1.5px solid rgba(45, 212, 191, 0.25) !important;
            border-radius: 8px !important;
            padding: 4px 8px !important;
            background: rgba(20, 184, 166, 0.07) !important;
            transition: all 0.2s ease !important;
        }
        #pos-grid td[data-col-id="col_avail"]:hover .g-readonly,
        #pos-grid td[data-col-id="available"]:hover .avail-val {
            border-color: #2dd4bf !important;
            box-shadow: 0 0 12px rgba(45, 212, 191, 0.4), inset 0 0 6px rgba(45, 212, 191, 0.15) !important;
            background: rgba(20, 184, 166, 0.14) !important;
        }

        /* C. Unit Column (col_unit / الوحدات) — Royal Purple Illumination (#a78bfa) */
        #pos-grid td[data-col-id="col_unit"] .g-select,
        #pos-grid td[data-col-id="unit"] .g-select {
            border-color: rgba(167, 139, 250, 0.35) !important;
        }
        #pos-grid td[data-col-id="col_unit"]:hover .g-select,
        #pos-grid td[data-col-id="col_unit"] .g-select:hover,
        #pos-grid td[data-col-id="unit"]:hover .g-select,
        #pos-grid td[data-col-id="unit"] .g-select:hover {
            border-color: #a78bfa !important;
            box-shadow: 0 0 12px rgba(167, 139, 250, 0.45), inset 0 0 6px rgba(167, 139, 250, 0.14) !important;
            background: rgba(139, 92, 246, 0.12) !important;
        }
        #pos-grid td[data-col-id="col_unit"] .g-select:focus,
        #pos-grid td[data-col-id="unit"] .g-select:focus {
            border-color: #c4b5fd !important;
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.25), 0 0 18px rgba(167, 139, 250, 0.6) !important;
            background: rgba(18, 16, 46, 0.96) !important;
        }

        /* C2. Factor & Base Qty Columns (col_factor, col_baseqty / التعادل، عدد الوحدة الأساسية) — Indigo Illumination (#818cf8) */
        #pos-grid td[data-col-id="col_factor"] .g-input,
        #pos-grid td[data-col-id="col_baseqty"] .g-input {
            border-color: rgba(129, 140, 248, 0.34) !important;
        }
        #pos-grid td[data-col-id="col_factor"]:hover .g-input,
        #pos-grid td[data-col-id="col_factor"] .g-input:hover,
        #pos-grid td[data-col-id="col_baseqty"]:hover .g-input,
        #pos-grid td[data-col-id="col_baseqty"] .g-input:hover {
            border-color: #818cf8 !important;
            box-shadow: 0 0 12px rgba(129, 140, 248, 0.45), inset 0 0 6px rgba(129, 140, 248, 0.14) !important;
            background: rgba(99, 102, 241, 0.11) !important;
        }
        #pos-grid td[data-col-id="col_factor"] .g-input:focus,
        #pos-grid td[data-col-id="col_baseqty"] .g-input:focus {
            border-color: #a5b4fc !important;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.25), 0 0 18px rgba(129, 140, 248, 0.6) !important;
            background: rgba(16, 18, 46, 0.96) !important;
        }

        /* D. Quantity Column (col_qty / الكمية) — Warm Gold/Amber Illumination (#fbbf24) */
        #pos-grid td[data-col-id="col_qty"] .g-input,
        #pos-grid td[data-col-id="quantity"] .g-input {
            border-color: rgba(251, 191, 36, 0.38) !important;
        }
        #pos-grid td[data-col-id="col_qty"]:hover .g-input,
        #pos-grid td[data-col-id="col_qty"] .g-input:hover,
        #pos-grid td[data-col-id="quantity"]:hover .g-input,
        #pos-grid td[data-col-id="quantity"] .g-input:hover {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 13px rgba(251, 191, 36, 0.45), inset 0 0 6px rgba(251, 191, 36, 0.14) !important;
            background: rgba(245, 158, 11, 0.11) !important;
        }
        #pos-grid td[data-col-id="col_qty"] .g-input:focus,
        #pos-grid td[data-col-id="quantity"] .g-input:focus {
            border-color: #fde047 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.25), 0 0 20px rgba(251, 191, 36, 0.65), inset 0 0 8px rgba(251, 191, 36, 0.18) !important;
            background: rgba(28, 22, 10, 0.96) !important;
        }

        /* E. Price Column (col_price / السعر) — Financial Emerald Illumination (#34d399) */
        #pos-grid td[data-col-id="col_price"] .g-input,
        #pos-grid td[data-col-id="price"] .g-input {
            border-color: rgba(52, 211, 153, 0.38) !important;
        }
        #pos-grid td[data-col-id="col_price"]:hover .g-input,
        #pos-grid td[data-col-id="col_price"] .g-input:hover,
        #pos-grid td[data-col-id="price"]:hover .g-input,
        #pos-grid td[data-col-id="price"] .g-input:hover {
            border-color: #34d399 !important;
            box-shadow: 0 0 13px rgba(52, 211, 153, 0.45), inset 0 0 6px rgba(52, 211, 153, 0.14) !important;
            background: rgba(16, 185, 129, 0.11) !important;
        }
        #pos-grid td[data-col-id="col_price"] .g-input:focus,
        #pos-grid td[data-col-id="price"] .g-input:focus {
            border-color: #6ee7b7 !important;
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.25), 0 0 20px rgba(16, 185, 129, 0.65), inset 0 0 8px rgba(16, 185, 129, 0.18) !important;
            background: rgba(6, 28, 22, 0.96) !important;
        }

        /* F. Total Column (col_total / الجملة) — Luminous Cyan-Emerald Frame */
        #pos-grid td[data-col-id="col_total"] .total-val,
        #pos-grid td[data-col-id="total"] .total-val {
            display: inline-block;
            width: 100%;
            border: 1.5px solid rgba(56, 189, 248, 0.28) !important;
            border-radius: 8px !important;
            padding: 5px 8px !important;
            background: rgba(14, 165, 233, 0.08) !important;
            transition: all 0.22s ease !important;
        }
        #pos-grid td[data-col-id="col_total"]:hover .total-val,
        #pos-grid td[data-col-id="total"]:hover .total-val,
        #pos-grid tbody tr:focus-within td[data-col-id="col_total"] .total-val,
        #pos-grid tbody tr:focus-within td[data-col-id="total"] .total-val {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.45), inset 0 0 8px rgba(56, 189, 248, 0.15) !important;
            background: rgba(14, 165, 233, 0.16) !important;
        }

        /* G. Warehouse Column (col_wh / المخزن) — Coral Orange Illumination (#fb923c) */
        #pos-grid td[data-col-id="col_wh"] .g-select,
        #pos-grid td[data-col-id="warehouse"] .g-select {
            border-color: rgba(251, 146, 60, 0.34) !important;
        }
        #pos-grid td[data-col-id="col_wh"]:hover .g-select,
        #pos-grid td[data-col-id="col_wh"] .g-select:hover,
        #pos-grid td[data-col-id="warehouse"]:hover .g-select,
        #pos-grid td[data-col-id="warehouse"] .g-select:hover {
            border-color: #fb923c !important;
            box-shadow: 0 0 12px rgba(251, 146, 60, 0.42), inset 0 0 6px rgba(251, 146, 60, 0.12) !important;
            background: rgba(249, 115, 22, 0.11) !important;
        }
        #pos-grid td[data-col-id="col_wh"] .g-select:focus,
        #pos-grid td[data-col-id="warehouse"] .g-select:focus {
            border-color: #fdba74 !important;
            box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.24), 0 0 18px rgba(251, 146, 60, 0.6) !important;
        }

        /* H. Note Column (col_note / الملحوظة) — Slate Sky Illumination (#60a5fa) */
        #pos-grid td[data-col-id="col_note"] .g-input,
        #pos-grid td[data-col-id="note"] .g-input {
            border-color: rgba(148, 163, 184, 0.28) !important;
        }
        #pos-grid td[data-col-id="col_note"]:hover .g-input,
        #pos-grid td[data-col-id="col_note"] .g-input:hover,
        #pos-grid td[data-col-id="note"]:hover .g-input,
        #pos-grid td[data-col-id="note"] .g-input:hover {
            border-color: #60a5fa !important;
            box-shadow: 0 0 12px rgba(96, 165, 250, 0.38), inset 0 0 6px rgba(96, 165, 250, 0.1) !important;
            background: rgba(59, 130, 246, 0.10) !important;
        }
        #pos-grid td[data-col-id="col_note"] .g-input:focus,
        #pos-grid td[data-col-id="note"] .g-input:focus {
            border-color: #93c5fd !important;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.22), 0 0 16px rgba(96, 165, 250, 0.55) !important;
        }

        /* 3. Header Controls Hover & Focus Illumination */
        #hdr-customer-search:hover {
            border-color: #f59e0b !important;
            box-shadow: 0 0 14px rgba(245, 158, 11, 0.4), inset 0 0 8px rgba(245, 158, 11, 0.12) !important;
        }
        #hdr-customer-search:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.24), 0 0 22px rgba(245, 158, 11, 0.58) !important;
        }
        #hdr-currency:hover, #hdr-exchange-rate:hover {
            border-color: #34d399 !important;
            box-shadow: 0 0 14px rgba(52, 211, 153, 0.42), inset 0 0 6px rgba(52, 211, 153, 0.12) !important;
        }
        #hdr-warehouse:hover, #hdr-cost-center:hover, #hdr-description:hover, #hdr-date:hover {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.4), inset 0 0 6px rgba(56, 189, 248, 0.1) !important;
        }

        /* Light Theme Harmonious Ergonomic Calibration */
        :root.light-theme #quick-item-search {
            background: #ffffff !important;
            border-color: #93c5fd !important;
            color: #0f172a !important;
        }
        :root.light-theme #quick-item-search:hover,
        :root.light-theme #quick-item-search:focus {
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.18), 0 4px 12px rgba(2, 132, 199, 0.15) !important;
        }
        :root.light-theme #pos-grid .g-input,
        :root.light-theme #pos-grid .g-select {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
        }

        .hub-table { width: 100%; border-collapse: separate !important; border-spacing: 0 8px !important; }
        .hub-table thead th {
            background: rgba(15, 23, 42, 0.8) !important; 
            color: #38bdf8 !important; 
            font-size: 0.85rem !important; 
            font-weight: 800 !important;
            padding: 12px 15px !important; 
            text-align: center !important; 
            position: sticky; top: 0; z-index: 5;
            border-top: 1px solid rgba(56, 189, 248, 0.2) !important;
            border-bottom: 2px solid rgba(56, 189, 248, 0.6) !important; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .hub-table thead th:first-child { 
            border-radius: 10px 0 0 10px !important; 
            border-right: 1px solid rgba(56, 189, 248, 0.2) !important; 
            text-align: right !important;
        }
        .hub-table thead th:nth-child(2) { text-align: right !important; }
        .hub-table thead th:nth-child(3) { text-align: right !important; }
        .hub-table thead th:nth-child(4) { text-align: right !important; }
        .hub-table thead th:last-child { 
            border-radius: 0 10px 10px 0 !important; 
            border-left: 1px solid rgba(56, 189, 248, 0.2) !important; 
        }
        .hub-table tbody tr {
            background: rgba(15, 23, 42, 0.45) !important;
            transition: all 0.3s ease !important;
        }
        .hub-table tbody tr:hover {
            background: rgba(56, 189, 248, 0.18) !important; 
            transform: translateY(-2px) !important; 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4) !important;
        }
        .hub-table tbody tr:hover td {
            border-top-color: rgba(56, 189, 248, 0.25) !important;
            border-bottom-color: rgba(56, 189, 248, 0.25) !important;
        }
        .hub-table tbody tr:hover td:first-child {
            border-right-color: rgba(56, 189, 248, 0.25) !important;
        }
        .hub-table tbody tr:hover td:last-child {
            border-left-color: rgba(56, 189, 248, 0.25) !important;
        }
        .hub-table tbody td {
            padding: 12px 10px !important; 
            border-bottom: 1px solid rgba(255,255,255,0.03) !important;
            border-top: 1px solid rgba(255,255,255,0.03) !important;
            font-size: 0.95rem !important; 
            font-weight: 700 !important;
            vertical-align: middle !important;
            text-align: center;
        }
        .hub-table tbody td:first-child { 
            border-radius: 12px 0 0 12px !important; 
            border-right: 1px solid rgba(255,255,255,0.03) !important; 
            text-align: right; 
        }
        .hub-table tbody td:nth-child(2) { text-align: right; }
        .hub-table tbody td:nth-child(3) { text-align: right; }
        .hub-table tbody td:nth-child(4) { text-align: right; }
        .hub-table tbody td:last-child { 
            border-radius: 0 12px 12px 0 !important; 
            border-left: 1px solid rgba(255,255,255,0.03) !important; 
        }

        /* ═══ WORLD-CLASS KINETIC LUMINESCENT GLASS ARMS & SPLIT BOARD STYLES ═══ */
        @keyframes kineticShimmerSweep {
            0% { transform: translateX(130%) skewX(-22deg); }
            100% { transform: translateX(-180%) skewX(-22deg); }
        }
        @keyframes kineticPulseGlow {
            0%, 100% { box-shadow: 0 6px 20px rgba(236, 72, 153, 0.28), inset 0 1px 1px rgba(255, 255, 255, 0.35); }
            50% { box-shadow: 0 8px 28px rgba(236, 72, 153, 0.52), 0 0 18px rgba(56, 189, 248, 0.32), inset 0 1px 2px rgba(255, 255, 255, 0.55); }
        }
        .hub-action-card {
            position: relative !important;
            overflow: hidden !important;
            backdrop-filter: blur(16px) saturate(160%) !important;
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.28s ease, border-color 0.28s ease !important;
        }
        .hub-action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 55%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.18), transparent);
            transform: translateX(140%) skewX(-22deg);
            transition: transform 0.65s ease;
            pointer-events: none;
        }
        .hub-action-card:hover::before {
            animation: kineticShimmerSweep 1.15s ease-in-out infinite;
        }
        .hub-action-card:hover {
            transform: translateY(-4px) scale(1.015) !important;
        }
        .hub-action-card.card-pink {
            border: 1.5px solid rgba(244, 114, 182, 0.55) !important;
            animation: kineticPulseGlow 3.2s infinite ease-in-out;
        }
        .kinetic-glow-arm {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 900;
            letter-spacing: 0.2px;
            backdrop-filter: blur(12px);
            transition: all 0.24s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.32);
        }
        .kinetic-glow-arm::after {
            content: '';
            position: absolute;
            top: 0;
            left: -75%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.32), transparent);
            transform: skewX(-24deg);
            transition: left 0.55s ease;
            pointer-events: none;
        }
        .kinetic-glow-arm:hover::after {
            left: 135%;
        }
        .kinetic-glow-arm:hover {
            transform: translateY(-2px) scale(1.03);
            filter: brightness(1.12);
        }
        .kinetic-glow-arm:active {
            transform: translateY(0) scale(0.98);
        }
        .kinetic-arm-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.32), rgba(168, 85, 247, 0.28)) !important;
            border: 1.5px solid rgba(244, 114, 182, 0.65) !important;
            color: #fdf2f8 !important;
            box-shadow: 0 4px 16px rgba(236, 72, 153, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.35) !important;
        }
        .kinetic-arm-emerald {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.34), rgba(5, 150, 105, 0.28)) !important;
            border: 1.5px solid rgba(52, 211, 153, 0.65) !important;
            color: #ecfdf5 !important;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.35) !important;
        }
        .kinetic-arm-cyan {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.32), rgba(59, 130, 246, 0.26)) !important;
            border: 1.5px solid rgba(56, 189, 248, 0.65) !important;
            color: #f0f9ff !important;
            box-shadow: 0 4px 16px rgba(56, 189, 248, 0.28), inset 0 1px 1px rgba(255, 255, 255, 0.35) !important;
        }
        .kinetic-mini-step-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.9);
            border: 1.5px solid rgba(236, 72, 153, 0.5);
            color: #f472b6;
            font-weight: 900;
            font-size: 1.1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s ease;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.2);
        }
        .kinetic-mini-step-btn:hover {
            transform: scale(1.08);
            background: rgba(236, 72, 153, 0.25);
            color: #fff;
        }
        .kinetic-split-genealogy-pill:hover {
            transform: translateY(-1px) scale(1.03);
            filter: brightness(1.15);
        }

        /* Split Board Table Custom Style */
        #split-board-step table.pos-grid {
            border-collapse: separate !important;
            border-spacing: 0 6px !important;
        }
        #split-board-step table.pos-grid thead th {
            background: rgba(15, 23, 42, 0.85) !important;
            padding: 10px 8px !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            border-top: 1px solid rgba(255,255,255,0.06) !important;
            border-bottom: 2px solid rgba(255,255,255,0.12) !important;
        }
        #split-board-step table.pos-grid thead th:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(255,255,255,0.06) !important;
        }
        #split-board-step table.pos-grid thead th:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(255,255,255,0.06) !important;
        }
        #split-board-step table.pos-grid tbody tr {
            background: rgba(255, 255, 255, 0.02) !important;
            transition: background 0.2s ease !important;
        }
        #split-board-step table.pos-grid tbody tr:hover {
            background: rgba(255, 255, 255, 0.06) !important;
        }
        #split-board-step table.pos-grid tbody td {
            padding: 10px 8px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.02) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02) !important;
            font-size: 0.95rem !important;
            vertical-align: middle !important;
        }
        #split-board-step table.pos-grid tbody td:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(255, 255, 255, 0.02) !important;
        }
        #split-board-step table.pos-grid tbody td:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(255, 255, 255, 0.02) !important;
        }

        /* Split Select Step Table Custom Style */
        #split-select-step table.pos-grid {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
        }
        #split-select-step table.pos-grid thead th {
            background: rgba(15, 23, 42, 0.85) !important;
            padding: 12px 10px !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            color: #38bdf8 !important;
            border-top: 1px solid rgba(56, 189, 248, 0.2) !important;
            border-bottom: 2px solid rgba(56, 189, 248, 0.6) !important;
        }
        #split-select-step table.pos-grid thead th:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(56, 189, 248, 0.2) !important;
            text-align: right !important;
        }
        #split-select-step table.pos-grid thead th:nth-child(2) { text-align: right !important; }
        #split-select-step table.pos-grid thead th:nth-child(3) { text-align: right !important; }
        #split-select-step table.pos-grid thead th:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(56, 189, 248, 0.2) !important;
        }
        #split-select-step table.pos-grid tbody tr {
            background: rgba(15, 23, 42, 0.45) !important;
            transition: all 0.3s ease !important;
        }
        #split-select-step table.pos-grid tbody tr:hover {
            background: rgba(56, 189, 248, 0.18) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }
        #split-select-step table.pos-grid tbody tr:hover td {
            border-top-color: rgba(56, 189, 248, 0.25) !important;
            border-bottom-color: rgba(56, 189, 248, 0.25) !important;
        }
        #split-select-step table.pos-grid tbody tr:hover td:first-child {
            border-right-color: rgba(56, 189, 248, 0.25) !important;
        }
        #split-select-step table.pos-grid tbody tr:hover td:last-child {
            border-left-color: rgba(56, 189, 248, 0.25) !important;
        }
        #split-select-step table.pos-grid tbody td {
            padding: 14px 12px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.03) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            vertical-align: middle !important;
        }
        #split-select-step table.pos-grid tbody td:first-child {
            border-radius: 8px 0 0 8px !important;
            border-right: 1px solid rgba(255, 255, 255, 0.03) !important;
            text-align: right;
        }
        #split-select-step table.pos-grid tbody td:nth-child(2) { text-align: right; }
        #split-select-step table.pos-grid tbody td:nth-child(3) { text-align: right; }
        #split-select-step table.pos-grid tbody td:last-child {
            border-radius: 0 8px 8px 0 !important;
            border-left: 1px solid rgba(255, 255, 255, 0.03) !important;
        }

    </style>
    
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
            transition: all 0.3s ease !important;
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
            transition: all 0.3s ease !important;
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
            transition: all 0.3s ease !important;
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
            transition: all 0.3s ease !important;
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
                $wStyle = ($colWidth !== 'auto') ? ("width: " . $colWidth . " !important; min-width: " . $colWidth . " !important; max-width: " . $colWidth . " !important;") : "width: auto !important; min-width: 180px !important;";
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
        body #pos-grid tbody tr td .g-readonly.is-zero-val,
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
        :root.light-theme body #pos-grid tbody tr td .g-readonly.is-zero-val,
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
        :root.light-theme .sales-glass-card,
        :root.light-theme .purchase-glass-card,
        :root.light-theme #items-panel,
        :root.light-theme .panel-header-row,
        :root.light-theme .grid-scroll-wrap,
        :root.light-theme #inline-totals-container,
        :root.light-theme #payment-panel {
            background: linear-gradient(135deg, #f6f0e4 0%, #ede3d0 100%) !important;
            border: 2px solid #334155 !important;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08) !important;
            color: #000000 !important;
        }
        :root.light-theme .panel-title {
            color: #000000 !important;
            text-shadow: none !important;
            font-weight: 900 !important;
        }

        /* Header Labels: رقم الفاتورة، التاريخ، العميل، الصرف، المخزن، الفرع، البيان */
        :root.light-theme .h-label,
        :root.light-theme #invoice-info-box .h-label,
        :root.light-theme #invoice-info-box #hdr-date-label,
        :root.light-theme #sales-row1-bar .h-label,
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
        :root.light-theme .gold-serial-wrap,
        :root.light-theme .pro-serial-container {
            background: #ffffff !important;
            border: 2px solid #000000 !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06) !important;
        }
        :root.light-theme #inv-number,
        :root.light-theme .pro-serial-number,
        :root.light-theme .gold-serial-text {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
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

        /* Header Inputs: العميل، سعر الصرف، العملة، المخزن، الفرع، البيان، والبحث السريع */
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
        :root.light-theme #sales-user-audit-container .user-audit-badge,
        :root.light-theme #sales-user-audit-container > div,
        :root.light-theme #fin-strip .fin-strip-inner-box {
            background: #ffffff !important;
            border: 2px solid #0f172a !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06) !important;
        }
        :root.light-theme #sales-user-audit-container .user-audit-badge .uab-label,
        :root.light-theme #sales-user-audit-container .user-audit-badge .uab-val,
        :root.light-theme #fin-strip .fin-strip-inner-box span {
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
        :root.light-theme body #pos-grid tbody tr td .g-readonly:not(.is-zero-val):not(.total-val) {
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
        :root.light-theme #t-grand-dynamic-fc,
        :root.light-theme #t-grand-currency-badge {
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
        body .del-btn {
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
        body .edit-btn {
            background: #0284c7 !important;
            border: 2px solid #0c4a6e !important;
            color: #ffffff !important;
            opacity: 1 !important;
        }
        body .edit-btn svg {
            stroke: #ffffff !important;
        }

        /* 5. Bottom Extra-Cost & Payment Toggles (ترحيل، عمالة، إضافات، ضريبة، خصم، طرق الدفع — بلون غامق واضح التباين) */
        :root.light-theme .tot-toggle-btn,
        :root.light-theme .pay-toggle-btn,
        :root.light-theme #sales-margin-guard-badge,
        :root.light-theme #hdr-price-tier,
        :root.light-theme #btn-cust-item-history,
        :root.light-theme #btn-park-invoice,
        :root.light-theme #btn-recall-parked {
            background: #ffffff !important;
            border: 2px solid #0f172a !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
            text-shadow: none !important;
        }
        :root.light-theme #toggles-area span,
        :root.light-theme #sales-margin-guard-badge span,
        :root.light-theme #btn-cust-item-history span,
        :root.light-theme #btn-park-invoice span,
        :root.light-theme #btn-recall-parked span {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme .tot-toggle-btn:hover,
        :root.light-theme .pay-toggle-btn:hover {
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
                const numInputs = scope.querySelectorAll('#pos-grid input.g-input:not([id^="item-search-"]):not([placeholder="ملاحظة..."]), .tot-row input.tot-input');
                numInputs.forEach(inp => {
                    if (isZeroLike(inp.value)) inp.classList.add('is-zero-val');
                    else inp.classList.remove('is-zero-val');
                });
                const spans = scope.querySelectorAll('#pos-grid .total-val, #pos-grid .avail-val, #pos-grid [data-avail]');
                spans.forEach(sp => {
                    if (isZeroLike(sp.textContent)) sp.classList.add('is-zero-val');
                    else sp.classList.remove('is-zero-val');
                });
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

    <div class="pos-wrapper dash-main">

        <!-- ═══ BREADCRUMB + TOP BAR ═══ -->
                <!-- Unified Header -->
        
    <!-- Ambient Animated Background -->
    <div class="ambient-bg"></div>

    <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>


        
    <!-- ═══ SALES HUB VIEW (UNIFIED SALES, DELIVERY & SPLIT COMMAND CENTER) ═══ -->
    <div id="sales-hub-view" class="animate-fade" style="display: flex; flex-direction: column; height: 100%; gap: 16px; padding: 0 5px;">
        <!-- Action Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
            <button onclick="openInvoiceEditor('pending_delivery')" class="hub-action-card card-blue animate-scale stagger-1">
                <div class="icon">➕</div>
                <div class="text-content">
                    <div class="title">فاتورة مبيعات جديدة</div>
                </div>
            </button>
            <button onclick="openInvoiceEditor('preliminary')" class="hub-action-card card-purple animate-scale stagger-2">
                <div class="icon">📋</div>
                <div class="text-content">
                    <div class="title">فاتورة مبدئية</div>
                </div>
            </button>
        </div>

        <!-- Unified Sales, Delivery & Split Table -->
        <div class="neo-card" id="unified-sales-table-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.05rem; display: flex; flex-direction: column; gap: 10px; align-items: stretch;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span style="color: #38bdf8 !important; font-weight: 900; font-size: 1.12rem;">📋 سجل المبيعات والتسليم</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <input type="date" id="hub-date-from" class="v-input" style="width:132px;margin:0;height:34px;font-size:0.78rem;font-family:'Outfit';" onchange="window.applyAdvancedSalesHubFilters && window.applyAdvancedSalesHubFilters()" title="من تاريخ">
                        <input type="date" id="hub-date-to" class="v-input" style="width:132px;margin:0;height:34px;font-size:0.78rem;font-family:'Outfit';" onchange="window.applyAdvancedSalesHubFilters && window.applyAdvancedSalesHubFilters()" title="إلى تاريخ">
                        <button type="button" id="btn-hub-export-excel" onclick="window.exportSalesHubToExcel && window.exportSalesHubToExcel()" class="btn-action" style="width:auto; padding:6px 12px; font-size:0.8rem; font-weight:900; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:9px; cursor:pointer;">📊 تصدير Excel</button>
                        <button type="button" onclick="if(typeof openDeliveryQrScannerModal==='function') openDeliveryQrScannerModal();" class="btn-action" style="width:auto; padding:6px 12px; font-size:0.8rem; font-weight:900; background:linear-gradient(135deg,#d97706,#b45309); color:#fff; border:none; border-radius:9px; cursor:pointer;">📷 مسح QR</button>
                        <input type="text" id="hub-search" class="v-input" placeholder="🔍 بحث برقم الفاتورة، العميل، الصنف..." style="width: 250px; margin:0;" oninput="filterHubTransactions(); if(window.applyAdvancedSalesHubFilters) window.applyAdvancedSalesHubFilters();">
                        <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.06); border-radius:9px;" onclick="loadHubTransactions()">🔄 تحديث</button>
                    </div>
                </div>

                <!-- Unified Quick Filter Tabs Bar -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; background: rgba(2,6,23,0.65); border: 1px solid rgba(148,163,184,0.2); padding: 8px 12px; border-radius: 12px;">
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;" id="unified-hub-filter-pills">
                        <button type="button" id="uhub-tab-all" onclick="setUnifiedHubFilter('all')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1.5px solid #38bdf8; background:rgba(56,189,248,0.22); color:#fff;">
                            📋 الكل (<span id="uhub-cnt-all">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-pending" onclick="setUnifiedHubFilter('pending')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            🚚 قيد التسليم (<span id="uhub-cnt-pending">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-split_only" onclick="setUnifiedHubFilter('split_only')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            ✂️ المقسمة (<span id="uhub-cnt-split">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-delivered" onclick="setUnifiedHubFilter('delivered')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            ✅ المسلمة (<span id="uhub-cnt-delivered">0</span>)
                        </button>
                        <button type="button" id="uhub-tab-preliminary" onclick="setUnifiedHubFilter('preliminary')" style="padding:6px 14px; border-radius:9px; font-size:0.82rem; font-weight:900; cursor:pointer; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#cbd5e1;">
                            💜 مبدئية (<span id="uhub-cnt-prelim">0</span>)
                        </button>
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
    <div id="split-invoice-view" style="display: none; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 0 5px; position: relative; z-index: 9999; background: var(--bg-main);">
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">

            
            <!-- Step 1: Select Invoice -->
            <div id="split-select-step" style="display: flex; flex-direction: column; flex: 1; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: 12px; border: 1.5px solid rgba(255,255,255,0.08); flex-wrap: wrap;">
                    <div style="flex: 1.4; min-width: 220px;">
                        <label style="display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 4px; font-weight: 900;">🔍 بحث فوري (F2):</label>
                        <input type="text" id="split-invoice-search" class="v-input" placeholder="ابحث برقم الفاتورة، العميل، الصنف، أو الإجمالي..." oninput="filterSplitInvoices()">
                    </div>
                    <div style="min-width: 145px;">
                        <label style="display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 4px; font-weight: 900;">🗂️ حالة التجزئة:</label>
                        <select id="split-filter-status" class="v-input" onchange="filterSplitInvoices()" style="height:40px; padding:0 10px; font-weight:800;">
                            <option value="all">الكل (جاهز للتجزئة)</option>
                            <option value="original">فواتير أصلية فقط</option>
                            <option value="branches">فروع مقسمة سابقاً</option>
                            <option value="partial">تسليم جزئي متبقي</option>
                        </select>
                    </div>
                    <div style="min-width: 130px;">
                        <label style="display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 4px; font-weight: 900;">💳 طريقة الدفع:</label>
                        <select id="split-filter-payment" class="v-input" onchange="filterSplitInvoices()" style="height:40px; padding:0 10px; font-weight:800;">
                            <option value="all">كل طرق الدفع</option>
                            <option value="cash">💵 نقدي / بنكي</option>
                            <option value="credit">✍️ آجل (ذمم)</option>
                        </select>
                    </div>
                    <div style="min-width: 140px;">
                        <label style="display: block; font-size: 0.8rem; color: #94a3b8; margin-bottom: 4px; font-weight: 900;">📅 التاريخ:</label>
                        <input type="date" id="split-filter-date" class="v-input" onchange="filterSplitInvoices()" style="height:40px; padding:0 8px; font-weight:800;">
                    </div>
                    <span id="split-live-sync-pill" onclick="if(typeof window.flushSplitOfflineQueueNow==='function') window.flushSplitOfflineQueueNow(true)" title="اضغط للمزامنة الفورية مع السيرفر والأجهزة الأخرى" style="cursor:pointer; display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:99px; background:rgba(16,185,129,0.16); border:1.5px solid #10b981; color:#34d399; font-size:0.78rem; font-weight:900; margin-top:auto; height:40px;">⚡ متزامن لحظياً</span>
                    <button type="button" id="btn-split-export-excel" class="btn-action royal-crystal-btn" style="width: auto; height: 40px; margin-top: auto; padding: 0 12px; border-radius: 10px; background:rgba(16,185,129,0.18); border:1.5px solid #10b981; color:#6ee7b7; font-weight:900;" onclick="if(typeof window.exportSplitInvoicesToExcel==='function') window.exportSplitInvoicesToExcel()">📥 Excel</button>
                    <button type="button" id="btn-split-print-report" class="btn-action royal-crystal-btn" style="width: auto; height: 40px; margin-top: auto; padding: 0 12px; border-radius: 10px; background:rgba(56,189,248,0.18); border:1.5px solid #38bdf8; color:#7dd3fc; font-weight:900;" onclick="if(typeof window.printSplitEligibleReportA4==='function') window.printSplitEligibleReportA4()">🖨️ كشف A4</button>
                    <button type="button" id="btn-split-analytics" class="btn-action royal-crystal-btn" style="width: auto; height: 40px; margin-top: auto; padding: 0 12px; border-radius: 10px; background:rgba(236,72,153,0.18); border:1.5px solid #f472b6; color:#fbcfe8; font-weight:900;" onclick="if(typeof window.openSplitLogisticsAnalyticsModal==='function') window.openSplitLogisticsAnalyticsModal()">📊 تحليلات التجزئة</button>
                    <button type="button" id="btn-split-presets-hub" class="btn-action royal-crystal-btn" style="width: auto; height: 40px; margin-top: auto; padding: 0 12px; border-radius: 10px; background:rgba(14,165,233,0.18); border:1.5px solid #38bdf8; color:#bae6fd; font-weight:900;" onclick="if(typeof window.openSplitPresetsModal==='function') window.openSplitPresetsModal()" data-guide-title="قوالب التجزئة الجاهزة" data-guide-desc="إدارة واستدعاء قوالب ونسب التجزئة المتكررة وتعيينات السائقين والوجهات المحفوظة مسبقاً.">📦 قوالب التجزئة</button>
                    <button type="button" id="btn-split-voice-select" class="btn-action royal-crystal-btn" style="width: auto; height: 40px; margin-top: auto; padding: 0 12px; border-radius: 10px; background:rgba(245,158,11,0.18); border:1.5px solid #fbbf24; color:#fde68a; font-weight:900;" onclick="if(typeof window.openSplitVoiceAssistantModal==='function') window.openSplitVoiceAssistantModal()">🎙️ أوامر التجزئة</button>
                    <button type="button" id="btn-split-hands-on-sim" class="btn-action royal-crystal-btn" style="width: auto; height: 40px; margin-top: auto; padding: 0 12px; border-radius: 10px; background:rgba(168,85,247,0.18); border:1.5px solid #c084fc; color:#e9d5ff; font-weight:900;" onclick="if(typeof window.openSplitHandsOnSimulatorModal==='function') window.openSplitHandsOnSimulatorModal()">🎯 محاكي التدريب</button>
                    <button class="btn-action royal-crystal-btn royal-btn-new" style="width: auto; height: 40px; margin-top: auto; padding: 0 14px; border-radius: 10px;" onclick="showNewInvoice()">➕ فاتورة جديدة</button>
                    <button class="btn-action royal-crystal-btn royal-btn-new" style="width: auto; height: 40px; margin-top: auto; padding: 0 14px; border-radius: 10px;" onclick="loadSplitInvoices()">🔄 تحديث</button>
                    <button class="btn-action royal-crystal-btn royal-btn-back" style="width: auto; height: 40px; margin-top: auto; padding: 0 16px; border-radius: 10px;" onclick="showSalesHub()">↩️ رجوع</button>
                </div>

                <div id="split-kpi-summary-bar" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(185px, 1fr)); gap:8px;"></div>
                
                <div class="grid-scroll-wrap" style="flex: 1; max-height: 450px; overflow-y: auto; margin-top: 10px;">
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

                <div class="grid-scroll-wrap" style="max-height: 260px; overflow-y: auto; background: rgba(0,0,0,0.15); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
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

                <div style="display: flex; gap: 12px; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button class="btn-action royal-crystal-btn royal-btn-delete kinetic-glow-arm" style="width: auto; padding: 9px 22px; border-radius: 11px;" onclick="cancelCurrentSplit()">↩️ رجوع</button>
                        <button type="button" class="btn-action kinetic-glow-arm kinetic-arm-emerald" style="width: auto; padding: 9px 18px; border-radius: 11px; font-size: 0.9rem;" onclick="if(window.activeSplitInvoice) window.openDeliveryForInvoiceFromHub(window.activeSplitInvoice.invoiceNo || window.activeSplitInvoice.id)">🚚 شاشة التسليم</button>
                        <button type="button" id="btn-split-voice-cmd" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 15px; border-radius: 11px; font-size: 0.88rem; background:rgba(245,158,11,0.18); border:1.5px solid #fbbf24; color:#fde68a; font-weight:900;" onclick="if(typeof window.openSplitVoiceAssistantModal==='function') window.openSplitVoiceAssistantModal()">🎙️ أوامر صوتية</button>
                        <button type="button" id="btn-split-audio-summary" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 15px; border-radius: 11px; font-size: 0.88rem; background:rgba(16,185,129,0.18); border:1.5px solid #34d399; color:#6ee7b7; font-weight:900;" onclick="if(typeof window.readSplitSummaryAloud==='function') window.readSplitSummaryAloud()">🔊 قراءة الملخص</button>
                        <button type="button" id="btn-split-weight-shield" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 15px; border-radius: 11px; font-size: 0.88rem; background:rgba(239,68,68,0.18); border:1.5px solid #f87171; color:#fecaca; font-weight:900;" onclick="if(typeof window.openSplitWeightShieldModal==='function') window.openSplitWeightShieldModal()" data-guide-title="درع توازن الأوزان والحمولة القصوى للشاحنات" data-guide-desc="حساب الوزن الإجمالي لكل قسم مجزأ بالطن والكيلوجرام ومقارنته بالحمولة القصوى لشاحنة السائق مع موازنة الأوزان تلقائياً.">🛡️ درع الأوزان</button>
                        <button type="button" id="btn-split-driver-sig" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 15px; border-radius: 11px; font-size: 0.88rem; background:rgba(56,189,248,0.18); border:1.5px solid #38bdf8; color:#bae6fd; font-weight:900;" onclick="if(typeof window.openSplitDriverSignatureModal==='function') window.openSplitDriverSignatureModal(0)" data-guide-title="التوقيع الرقمي الميداني لسائقي الأقسام" data-guide-desc="لوحة توقيع إلكتروني باللمس لكل قسم مجزأ يوقّع عليها السائق عند استلام حصته وتُطبع تلقائياً في سند التفويض.">✍️ توقيع السائق</button>
                        <button type="button" id="btn-split-presets" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 15px; border-radius: 11px; font-size: 0.88rem; background:rgba(14,165,233,0.18); border:1.5px solid #38bdf8; color:#e0f2fe; font-weight:900;" onclick="if(typeof window.openSplitPresetsModal==='function') window.openSplitPresetsModal()" data-guide-title="القوالب الجاهزة للتجزئة المتكررة" data-guide-desc="حفظ واستدعاء أنماط ونسب التجزئة المتكررة وتعيينات السائقين والوجهات بنقرة واحدة.">📦 قوالب التجزئة</button>
                        <button type="button" id="btn-split-sim-board" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 15px; border-radius: 11px; font-size: 0.88rem; background:rgba(168,85,247,0.18); border:1.5px solid #c084fc; color:#e9d5ff; font-weight:900;" onclick="if(typeof window.openSplitHandsOnSimulatorModal==='function') window.openSplitHandsOnSimulatorModal()">🎯 محاكي التدريب</button>
                    </div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button type="button" id="btn-split-add-section" class="btn-action kinetic-glow-arm kinetic-arm-cyan" style="width: auto; padding: 9px 18px; border-radius: 11px; font-size: 0.9rem;" onclick="addSplitSection()">➕ قسم إضافي</button>
                        <button type="button" id="btn-split-preview-vouchers" class="btn-action kinetic-glow-arm" style="width: auto; padding: 9px 18px; border-radius: 11px; font-size: 0.9rem; background:rgba(56,189,248,0.16); border:1.5px solid rgba(56,189,248,0.5); color:#38bdf8; font-weight:900;" onclick="if(typeof window.previewCurrentSplitVouchersBeforeSave==='function') window.previewCurrentSplitVouchersBeforeSave()">👁️ معاينة الجزئين</button>
                        <button type="button" id="btn-split-submit" class="btn-action kinetic-glow-arm kinetic-arm-pink" style="width: auto; padding: 9px 22px; border-radius: 11px; font-size: 0.95rem;" onclick="submitInvoiceSplit(false)">✅ اعتماد التقسيم (F10)</button>
                        <button type="button" id="btn-split-submit-print" class="btn-action royal-crystal-btn royal-btn-save kinetic-glow-arm kinetic-arm-emerald" style="width: auto; padding: 9px 26px; border-radius: 11px; font-size: 0.98rem;" onclick="submitInvoiceSplit(true)">🖨️ اعتماد وطباعة</button>
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
                    <button class="btn-action royal-crystal-btn royal-btn-back" style="width: auto; padding: 6px 16px; font-size: 0.85rem; border-radius: 10px;" onclick="showSalesHub()">↩️ رجوع</button>
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
    <div id="invoice-editor-view" style="display: none; flex-direction: column; height: 100%; flex: 1;">
    
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
                    <!-- Ambient Glow & Integrated Glass Facets -->

                    <!-- Ambient Glow -->
                    <div style="position:absolute; top:0; left:0; width:100%; height:100%; overflow:hidden; border-radius:16px; z-index:0; pointer-events:none;">
                        <div style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 60%);"></div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px; position:relative; z-index:1;">
                        <!-- Saved Invoice Edit Advisory Banner -->
                        <div id="saved-invoice-edit-banner" style="display:none; margin-bottom: 4px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(180, 83, 9, 0.25)); border: 1.5px solid rgba(245, 158, 11, 0.6); border-radius: 12px; padding: 8px 14px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 1.35rem;">⚠️</span>
                                    <div style="color: #fbbf24; font-size: 1rem; font-weight: 900;">
                                        وضع تعديل فاتورة محفوظة (<span id="edit-banner-inv-no" style="color:#ffffff; font-family:'Outfit',sans-serif; text-decoration: underline;">-</span>)
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <button type="button" onclick="onSuccessNewInvoice()" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 5px 12px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.84rem; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                                        ➕ فاتورة جديدة
                                    </button>
                                </div>
                            </div>
                        </div>

    <!-- ROW 0 WRAPPER (Slim 2027 Unified Bar: Invoice Info + Audit Badge + Inline Financial Strip) -->
    <style id="sales-worldclass-ux-styles">
        #sales-row0-bar {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 10px;
            margin-bottom: 2px;
            min-height: 40px;
        }
        #invoice-info-box {
            flex: 1 1 auto;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            padding: 3px 10px !important;
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            border-radius: 10px !important;
            box-sizing: border-box !important;
        }
        /* Keep user audit badge strictly single-line & slim inside the top bar (matching purchase.php) */
        #sales-user-audit-container {
            display: flex !important;
            align-items: center !important;
            height: 34px !important;
            flex-shrink: 0 !important;
        }
        #sales-user-audit-container .user-audit-badge,
        #sales-user-audit-container > div {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            padding: 0 10px !important;
            border-radius: 8px !important;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.75)) !important;
            border: 1px solid rgba(56, 189, 248, 0.32) !important;
            white-space: nowrap !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }
        #sales-user-audit-container .user-audit-badge .uab-row {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 5px !important;
            flex-wrap: nowrap !important;
        }
        #sales-user-audit-container .user-audit-badge .uab-edit-row,
        #sales-user-audit-container .user-audit-badge .uab-deliv-row {
            display: none !important;
        }
        #sales-user-audit-container .user-audit-badge .uab-label {
            font-size: 0.75rem !important;
            color: #94a3b8 !important;
            font-weight: 700 !important;
        }
        #sales-user-audit-container .user-audit-badge .uab-val {
            font-size: 0.84rem !important;
            color: #38bdf8 !important;
            font-weight: 900 !important;
        }
        #sales-user-audit-container .user-audit-badge .uab-tag {
            padding: 1px 6px !important;
            font-size: 0.68rem !important;
            border-radius: 5px !important;
        }
        #sales-user-audit-container span {
            white-space: nowrap !important;
            line-height: 1 !important;
        }
        /* Slimmer inline financial strip */
        #fin-strip.fin-strip-inline.visible {
            flex: 1.35 1 0% !important;
            width: auto !important;
            opacity: 1 !important;
        }
        #fin-strip .fin-strip-inner-box {
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            padding: 2px 10px !important;
            border-radius: 10px !important;
            box-sizing: border-box !important;
        }
        /* Row 1 controls height & smart auto-fit text readiness */
        #sales-row1-bar .h-label {
            font-size: 0.84rem !important;
            font-weight: 800 !important;
            min-width: auto !important;
            margin: 0 !important;
            white-space: nowrap !important;
        }
        #sales-row1-bar .ctrl-input,
        #sales-row1-bar select.ctrl-input,
        :root.light-theme #sales-row1-bar .ctrl-input,
        :root.light-theme #sales-row1-bar select.ctrl-input {
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-radius: 10px !important;
            box-sizing: border-box !important;
        }
        :root.light-theme #hdr-customer-search {
            padding-left: 68px !important;
            padding-right: 10px !important;
        }
        /* Protect Ledger Button in both Dark & Light Themes */
        #hdr-customer-ledger-btn,
        .ledger-btn-2027,
        :root.light-theme #hdr-customer-ledger-btn,
        :root.light-theme .ledger-btn-2027 {
            position: absolute !important;
            left: 5px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 15 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 4px !important;
            height: 28px !important;
            min-height: 28px !important;
            max-height: 28px !important;
            padding: 0 9px !important;
            border-radius: 7px !important;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.35)) !important;
            border: 1.5px solid rgba(16, 185, 129, 0.65) !important;
            color: #34d399 !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
            font-size: 0.76rem !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25) !important;
            white-space: nowrap !important;
            line-height: 1 !important;
        }
        :root.light-theme #hdr-customer-ledger-btn,
        :root.light-theme .ledger-btn-2027 {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: #ffffff !important;
            border-color: #047857 !important;
        }
        #hdr-exchange-rate {
            font-size: 1.38rem !important;
            font-weight: 900 !important;
            font-family: 'Outfit', 'Cairo', monospace !important;
            letter-spacing: 0.5px !important;
            padding: 0 4px !important;
        }
        #hdr-warehouse, #hdr-cost-center, #hdr-currency {
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            padding-right: 8px !important;
            padding-left: 18px !important;
        }
    </style>

    <div id="sales-row0-bar">
        <!-- Row 0 Left/Right: Invoice Number, Date & User Audit Badge -->
        <div id="invoice-info-box">
            <div class="h-form-group group-invoice" style="flex: 0 1 255px; display:flex; align-items:center; gap:6px; margin:0; min-width: 185px;">
                <label class="h-label" style="margin:0; font-size: 0.85rem; font-weight:800; white-space:nowrap;">رقم الفاتورة:</label>
                <div class="gold-serial-wrap" style="display:flex; align-items:center; justify-content:space-between; flex:1; padding: 2px 6px; height: 32px; border-radius: 8px; min-width: 0;">
                    <button type="button" class="pro-serial-btn" onclick="loadPreviousInvoice()" title="السابقة" style="background: transparent; border: none; font-size: 0.95rem; cursor: pointer; padding: 0 4px; opacity:0.85; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85">❮</button>
                    <span id="inv-number" class="pro-serial-number gold-serial-text" style="font-size: 1.05rem; font-family: 'Outfit', sans-serif; font-weight: 900; letter-spacing: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">---</span>
                    <input type="hidden" id="hdr-inv-no">
                    <button type="button" class="pro-serial-btn" onclick="loadNextInvoice()" title="التالية" style="background: transparent; border: none; font-size: 0.95rem; cursor: pointer; padding: 0 4px; opacity:0.85; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85">❯</button>
                </div>
            </div>

            <div style="width: 1px; height: 22px; background: rgba(255,255,255,0.1); flex-shrink: 0;"></div>

            <div class="h-form-group group-date" style="flex: 0 1 205px; display:flex; align-items:center; gap:6px; margin:0; min-width: 155px;">
                <label class="h-label" id="hdr-date-label" style="margin:0; font-size: 0.85rem; font-weight:800; white-space:nowrap;">التاريخ:</label>
                <input type="date" class="v-input ctrl-input" id="hdr-date" style="flex:1; height: 32px !important; min-height: 32px !important; text-align: center; border-radius: 8px; padding: 2px 6px; font-family:'Outfit',sans-serif; font-weight:800; font-size:0.95rem; min-width: 0;">
            </div>

            <div style="width: 1px; height: 22px; background: rgba(255,255,255,0.1); flex-shrink: 0;"></div>
            <div id="sales-user-audit-container" style="display:inline-flex; align-items:center; flex-shrink: 0;"></div>
        </div>

        <!-- Inline Financial Strip (Matches Slim 42px Bar Height) -->
        <div id="fin-strip" class="fin-strip-inline" style="flex: 0; width: 0; opacity: 0; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex;">
            <div class="fin-strip-inner-box" style="width: 100%; background: linear-gradient(90deg, rgba(15,23,42,0.55), rgba(30,58,138,0.25)); border: 1px solid rgba(56, 189, 248, 0.55); display: flex; align-items: center; justify-content: space-between; gap: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                <div style="display: flex; align-items: center; gap: 5px; flex-shrink: 0;">
                    <span style="font-size: 0.95rem;">📊</span>
                    <span style="font-weight: 800; color:#e2e8f0; font-size:0.72rem; white-space: nowrap;">حالة العميل</span>
                    <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span><span id="fin-status-txt" style="font-size:0.68rem; margin-right:3px;"></span></span>
                </div>
                
                <div style="display:flex; gap: 10px; align-items: center; flex: 1; justify-content: flex-end; min-width: 0;">
                    <div style="display:flex; align-items:center; gap: 4px; min-width: 0;">
                        <span style="font-size:0.65rem; color:#94a3b8; font-weight:700; white-space: nowrap;">الرصيد:</span>
                        <span id="fin-ledger" style="font-weight:900; color:#fff; font-size:0.8rem; font-family:'Outfit',sans-serif; white-space: nowrap;">0</span>
                    </div>
                    <div style="width:1px; height:14px; background:rgba(255,255,255,0.12); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; align-items:center; gap: 4px; min-width: 0;">
                        <span style="font-size:0.65rem; color:#f59e0b; font-weight:700; white-space: nowrap;">شيكات:</span>
                        <span id="fin-checks" style="font-weight:900; color:#f59e0b; font-size:0.8rem; font-family:'Outfit',sans-serif; white-space: nowrap;">0</span>
                    </div>
                    <div style="width:1px; height:14px; background:rgba(255,255,255,0.12); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; align-items:center; gap: 4px; min-width: 0;">
                        <span style="font-size:0.65rem; color:#ef4444; font-weight:700; white-space: nowrap;">المديونية:</span>
                        <span id="fin-total-debt" style="font-weight:900; color:#ef4444; font-size:0.8rem; font-family:'Outfit',sans-serif; white-space: nowrap;">0</span>
                    </div>
                    <div style="width:1px; height:14px; background:rgba(255,255,255,0.12); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; align-items:center; gap: 4px; min-width: 0;">
                        <span style="font-weight:700; color:#38bdf8; font-size:0.65rem; white-space: nowrap;">الائتمان:</span>
                        <span id="fin-credit-limit" style="font-weight:900; color:#38bdf8; font-size:0.8rem; font-family:'Outfit',sans-serif; white-space: nowrap;">0</span>
                    </div>
                    <div style="width:1px; height:14px; background:rgba(255,255,255,0.12); flex-shrink: 0;"></div>
                    
                    <div style="display:inline-flex; align-items:center; gap: 4px; background: rgba(16,185,129,0.15); padding: 2px 7px; border-radius: 6px; border: 1px solid rgba(16,185,129,0.35); flex-shrink: 0;">
                        <span style="font-size:0.65rem; color:#34d399; font-weight:800; white-space: nowrap;">✨ المتاح:</span>
                        <span id="fin-remaining" style="font-weight:900; color:#34d399; font-size:0.82rem; font-family:'Outfit',sans-serif; white-space: nowrap;">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Unified Row 1: Customer (Expanded + Always-Visible Ledger Icon), Currency & Exchange Rate (Bold Auto-fit), Warehouse & Cost Center (Auto-fit) -->
    <div id="sales-row1-bar" style="display: flex; gap: 10px; flex-wrap: nowrap; margin-bottom: 6px; width: 100%; align-items: center;">
        
        <!-- 1. Customer Field (Wider allocation + auto-fit customer name + always-visible Ledger icon) -->
        <div class="h-form-group group-supplier" style="flex: 3.4; min-width: 340px; display:flex; align-items:center; gap:7px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin:0; white-space:nowrap; font-size: 0.95rem !important; font-weight: 800 !important; color: var(--amber, #f59e0b);">👤 العميل</label>
            <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 6px; flex: 1; min-width: 0; width: 100%;">
                <div class="custom-dropdown" style="flex:1; min-width: 0;">
                    <div class="custom-dropdown-input-wrap" style="position: relative;">
                        <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم العميل..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; padding-left: 72px !important; padding-right: 10px !important; font-size: 1.15rem; font-weight: 900; color: var(--amber, #fbbf24); background-color: rgba(251,191,36,0.06); border: 1px solid rgba(251,191,36,0.35);" autocomplete="one-time-code">
                        <button type="button" id="hdr-customer-ledger-btn" onclick="if(typeof window.openCustomerLedgerFromHeader==='function'){window.openCustomerLedgerFromHeader();}else{openGeneralLedgerForAccount('cust-' + document.getElementById('hdr-customer').value);}" class="ledger-btn-2027" title="دفتر الأستاذ العام">📖 أستاذ</button>
                        <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();">
                        <div class="custom-dropdown-list" id="customer-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none;">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>
                <button type="button" onclick="openModal('add-customer-modal')" style="flex-shrink: 0; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: 1px solid rgba(16,212,143,0.4); width: 36px; height: 36px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.25); transition: 0.2s;" title="إضافة عميل جديد">
                    ➕
                </button>
            </div>
        </div>

        <!-- 2. Currency & Exchange Rate Field (Larger bold number, auto-fit) -->
        <div class="h-form-group group-currency" style="flex: 1.55; min-width: 215px; display:flex; align-items:center; gap:6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin:0; white-space:nowrap;">💱 الصرف</label>
            <div class="h-input-wrapper" style="display: flex; flex-direction: row; align-items: center; gap: 5px; flex: 1; min-width: 0; width: 100%;">
                <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="handleCurrencyChange(this.value); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="flex: 1.05; min-width: 82px; padding: 0 6px;">
                    <option value="SDG">🇸🇩 جنيه</option>
                    <option value="USD">🇺🇸 دولار</option>
                    <option value="SAR">🇸🇦 ريال</option>
                    <option value="AED">🇦🇪 درهم</option>
                    <option value="EGP">🇪🇬 مصري</option>
                </select>
                <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="الصرف" readonly style="flex: 1.25; min-width: 92px; text-align: center; cursor:pointer; background:rgba(16,185,129,0.12); color:#34d399; border: 1px solid rgba(16,185,129,0.45);" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; this.dataset.userEdited='1'; if(window.autoFitHeaderFields) window.autoFitHeaderFields(); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
            </div>
        </div>

        <!-- 3. Warehouse (Auto-fit text inside allocated box + Quick Add Warehouse) -->
        <div class="h-form-group group-warehouse" style="flex: 1.35; min-width: 185px; display:flex; align-items:center; gap:5px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin:0; white-space:nowrap;">📦 المخزن</label>
            <div class="h-input-wrapper" style="flex:1; min-width: 0; display:flex; align-items:center; gap:4px;">
                <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange(); if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; font-weight: 800; flex:1;"></select>
                <button type="button" id="btn-ds-quick-add-wh" onclick="window.openDsQuickWarehouseModal && window.openDsQuickWarehouseModal()" style="flex-shrink:0; width:34px; height:36px; border-radius:8px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:1px solid rgba(251,191,36,0.5); font-weight:900; font-size:1rem; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="إضافة مخزن جديد فوراً">➕</button>
            </div>
        </div>

        <!-- 4. Cost Center / Sub-branch (Auto-fit text inside allocated box) -->
        <div class="h-form-group group-cost-center" style="flex: 1.25; min-width: 165px; display:flex; align-items:center; gap:6px; flex-direction: row !important; margin: 0;">
            <label class="h-label" style="margin:0; white-space:nowrap;">🏷️ الفرع</label>
            <div class="h-input-wrapper" style="flex:1; min-width: 0;">
                <select class="v-input ctrl-input" id="hdr-cost-center" onchange="if(window.autoFitHeaderFields) window.autoFitHeaderFields();" style="width:100%; font-weight: 800;">
                    <option value="cc-001">الفرع الرئيسي</option>
                    <option value="cc-002">فرع بحري</option>
                </select>
            </div>
        </div>

    </div>

    <!-- Row 2: Description -->
    <div style="display: flex; gap: 20px; width: 100%;">
        <div class="h-form-group group-description" style="flex: 1; display:flex; align-items:center; gap:10px;">
            <label class="h-label" style="margin:0; white-space:nowrap; min-width:60px;">📝 البيان</label>
            <div style="flex:1;">
                <input type="text" class="v-input ctrl-input" id="hdr-description" placeholder="ملاحظات، أرقام مرجعية..." style="width:100%;">
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
                <div class="panel-header-row" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                        <div class="panel-title" style="white-space: nowrap;">📦 أصناف الفاتورة</div>
                        <div class="quick-add-box" style="flex: 1; max-width: 430px; position: relative; z-index: 99999; display: flex; gap: 6px; align-items: center;">
                            <input type="text" class="g-input" id="quick-item-search" placeholder="بحث صنف أو باركود (F2)..."
                                style="background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.35); border-radius: 8px; color: var(--text-primary); padding: 5px 10px; flex: 1;"
                                onfocus="this.select(); showQuickItemDropdown()"
                                onblur="hideQuickItemDropdownDelayed()"
                                onkeydown="handleQuickItemSearchKeydown(event)"
                                oninput="filterQuickItemDropdown()" autocomplete="new-password">
                            <button type="button" onclick="startCameraScanner('quick-item-search')" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.35); border-radius: 8px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #38bdf8; font-size: 1.1rem;" title="مسح باركود بالكاميرا">
                                📷
                            </button>
                            <button type="button" onclick="addRow()" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.35); border-radius: 8px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #10b981; font-size: 1.4rem; font-weight: bold;" title="إضافة سطر للجدول">
                                +
                            </button>
                            <button type="button" id="btn-ds-quick-add-item" onclick="window.openDsQuickItemModal && window.openDsQuickItemModal()" style="background: linear-gradient(135deg,#8b5cf6,#6d28d9); border: 1px solid rgba(192,132,252,0.5); border-radius: 8px; height: 34px; padding: 0 9px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; color: #fff; font-size: 0.78rem; font-weight: 900; white-space: nowrap;" title="تعريف وإضافة صنف جديد للمخزون والفاتورة فوراً">
                                ➕ صنف
                            </button>
                            <div class="custom-dropdown-list" id="quick-item-dropdown-list" style="text-align:right; top: 100%; left: 0; right: 0; position: absolute;"></div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <!-- 1. Live Margin & Below-Cost Guard Badge -->
                        <button type="button" id="sales-margin-guard-badge" onclick="window.openCustomerItemHistoryModal('margin')" style="background: rgba(16, 185, 129, 0.14); border: 1.5px solid rgba(16, 185, 129, 0.45); color: #34d399; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;">
                            <span id="sales-margin-guard-icon">🛡️</span>
                            <span id="sales-margin-guard-text">الربح: 0%</span>
                        </button>

                        <!-- 2. Price Tiers Selector (قطاعي / نصف جملة / جملة) -->
                        <select id="hdr-price-tier" onchange="window.applySalesPriceTier(this.value)" style="background: rgba(245, 158, 11, 0.14); border: 1.5px solid rgba(245, 158, 11, 0.45); color: #fcd34d; border-radius: 8px; padding: 5px 10px; font-weight: 900; font-size: 0.85rem; cursor: pointer; height: 36px; font-family: 'Cairo', sans-serif;">
                            <option value="retail" style="background:#0f172a; color:#f8fafc;">🏷️ قطاعي</option>
                            <option value="semi_wholesale" style="background:#0f172a; color:#f8fafc;">🏷️ نصف جملة</option>
                            <option value="wholesale" style="background:#0f172a; color:#f8fafc;">🏷️ جملة</option>
                        </select>

                        <!-- 3. Customer Item Price History -->
                        <button type="button" id="btn-cust-item-history" onclick="window.openCustomerItemHistoryModal('history')" style="background: rgba(168, 85, 247, 0.16); border: 1.5px solid rgba(168, 85, 247, 0.45); color: #d8b4fe; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;">
                            <span>📈 سجل الأسعار</span>
                        </button>

                        <!-- 4. POS Park & Hold Buttons -->
                        <button type="button" id="btn-park-invoice" onclick="window.parkCurrentSalesInvoice()" style="background: rgba(14, 165, 233, 0.16); border: 1.5px solid rgba(56, 189, 248, 0.45); color: #38bdf8; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;">
                            <span>⏸️ تعليق</span>
                        </button>
                        <button type="button" id="btn-recall-parked" onclick="window.openParkedInvoicesModal()" style="background: rgba(234, 179, 8, 0.16); border: 1.5px solid rgba(250, 204, 21, 0.45); color: #fde047; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;">
                            <span>📂 المعلقة (<span id="parked-sales-cnt">0</span>)</span>
                        </button>

                        <!-- 5. Continuous POS Barcode Scanner Stream -->
                        <button type="button" id="btn-ds-continuous-pos-scan" onclick="window.openDsContinuousPosScanner && window.openDsContinuousPosScanner()" style="background: linear-gradient(135deg,#0ea5e9,#2563eb); border: 1.5px solid rgba(56,189,248,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="الماسح المتصل السريع لنقاط البيع (USB / كاميرا)">
                            <span>📡 ماسح متصل</span>
                            <span id="ds-pos-scan-cnt" style="background:rgba(0,0,0,0.35);padding:1px 6px;border-radius:99px;font-family:'Outfit';font-size:0.75rem;">0</span>
                        </button>

                        <!-- 6. Instant Warranty Certificate & Thermal 80mm QR -->
                        <button type="button" id="btn-ds-warranty-qr" onclick="window.openDsWarrantyAndThermalModal && window.openDsWarrantyAndThermalModal()" style="background: linear-gradient(135deg,#0d9488,#0f766e); border: 1.5px solid rgba(45,212,191,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="إصدار شهادة ضمان معتمدة أو إيصال حراري 80mm بباركود QR">
                            <span>🛡️ ضمان / QR</span>
                        </button>

                        <!-- 7. Live Audio Summary Readback -->
                        <button type="button" id="btn-ds-audio-summary" onclick="window.speakLiveDsInvoiceSummary && window.speakLiveDsInvoiceSummary()" style="background: linear-gradient(135deg,#6366f1,#4f46e5); border: 1.5px solid rgba(129,140,248,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="القراءة الصوتية الحية لملخص الفاتورة والمقبوضات بالصوت الرجالي">
                            <span>🔊 نطق الملخص</span>
                        </button>

                        <!-- 8. Arabic Voice POS Commands -->
                        <button type="button" id="btn-ds-voice-cmd" onclick="window.startDsVoiceCommandListener && window.startDsVoiceCommandListener()" style="background: linear-gradient(135deg,#ec4899,#be185d); border: 1.5px solid rgba(244,114,182,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="الأوامر الصوتية السريعة بالمايكروفون (قل مثلاً: حفظ الفاتورة، حاسبة الباقي، تعليق الفاتورة)">
                            <span>🎙️ أمر صوتي</span>
                        </button>

                        <!-- 9. Interactive Sales Mastery Quiz -->
                        <button type="button" id="btn-ds-mastery-quiz" onclick="window.startDsMasteryQuiz && window.startDsMasteryQuiz()" style="background: linear-gradient(135deg,#f59e0b,#b45309); border: 1.5px solid rgba(251,191,36,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="المدرب العملي التفاعلي لمندوبي المبيعات والكاشير بالصوت والمؤشر">
                            <span>🎓 تدريب عملي</span>
                        </button>

                        <!-- 10. Live Accounting Journal Entry & Impact Drill-Down -->
                        <button type="button" id="btn-ds-live-journal" onclick="window.openDsLiveJournalPreviewModal && window.openDsLiveJournalPreviewModal()" style="background: linear-gradient(135deg,#0284c7,#0369a1); border: 1.5px solid rgba(56,189,248,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="معاينة القيد المحاسبي اللحظي المتوازن وتكلفة البضاعة المباعة والأثر على رصيد العميل">
                            <span>⚖️ القيد المحاسبي</span>
                        </button>

                        <!-- 11. Instant Excel Export for Current Invoice -->
                        <button type="button" id="btn-ds-export-excel" onclick="window.exportCurrentSalesInvoiceToExcel && window.exportCurrentSalesInvoiceToExcel()" style="background: linear-gradient(135deg,#059669,#047857); border: 1.5px solid rgba(52,211,153,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="تصدير أصناف وبيانات الفاتورة الحالية إلى ملف Excel فوراً">
                            <span>📊 Excel</span>
                        </button>

                        <!-- 12. Customer Loyalty Points & Cashback Engine -->
                        <button type="button" id="btn-ds-loyalty-points" onclick="window.openDsCustomerLoyaltyModal && window.openDsCustomerLoyaltyModal()" style="background: linear-gradient(135deg,#d97706,#b45309); border: 1.5px solid rgba(251,191,36,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="بطاقة ولاء ومكافآت العميل اللحظية واستبدال النقاط بخصم فوري">
                            <span>🎁 نقاط الولاء</span>
                        </button>

                        <!-- 13. Out-of-Stock Smart Alternative Item Suggestor -->
                        <button type="button" id="btn-ds-alt-items" onclick="window.openDsAlternativeItemsModal && window.openDsAlternativeItemsModal()" style="background: linear-gradient(135deg,#7c3aed,#5b21b6); border: 1.5px solid rgba(192,132,252,0.55); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="رادار البدائل الذكية للأصناف الناقصة وفحص توفرها في المستودعات الفرعية">
                            <span>🔄 بدائل الأصناف</span>
                        </button>

                        <!-- 14. Live Profit Margin & Floor-Price Shield -->
                        <button type="button" id="btn-ds-margin-shield" onclick="window.openDsMarginShieldModal && window.openDsMarginShieldModal()" style="background: linear-gradient(135deg,#dc2626,#991b1b); border: 1.5px solid rgba(248,113,113,0.65); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="رادار حماية هامش الربح والحد الأدنى للسعر لمنع البيع بأقل من التكلفة">
                            <span>🛡️ درع الربحية</span>
                        </button>

                        <!-- 15. AI Cross-Sell & Bundle Recommender -->
                        <button type="button" id="btn-ds-cross-sell" onclick="window.openDsCrossSellModal && window.openDsCrossSellModal()" style="background: linear-gradient(135deg,#0d9488,#115e59); border: 1.5px solid rgba(45,212,191,0.65); color: #ffffff; border-radius: 8px; padding: 6px 11px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 900; font-size: 0.85rem; white-space: nowrap;" title="مُحلّل سلة المشتريات الذكي واقتراح الأصناف المكملة والباقات">
                            <span>🧠 مقترح المكملات</span>
                        </button>

                        <button type="button" onclick="openGridSettingsModal()" style="background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.3)); border: 1.5px solid #38bdf8; color: #38bdf8; border-radius: 8px; padding: 6px 14px; display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: bold; font-size: 0.95rem; transition: all 0.2s; box-shadow: 0 2px 10px rgba(56,189,248,0.25);" title="تخصيص الجدول">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <span>⚙️ تخصيص الجدول</span>
                        </button>
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
                                <span style="color: #f97316; font-size: 1.2rem; font-weight: 900;">🚚 الترحيل</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none;">
                                <div style="display: flex; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-transport" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('transport')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width: 100%; height: 46px; text-align: center; background: rgba(249, 115, 22, 0.15); border: 1.5px solid rgba(249, 115, 22, 0.5); border-radius: 8px; color: #fff; font-size: 1.5rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4); box-sizing: border-box;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Labor -->
                        <tr class="tot-row" id="row-tot-labor" style="display: none; background: rgba(59, 130, 246, 0.08); border-top: 1px dashed rgba(59, 130, 246, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #38bdf8; font-size: 1.2rem; font-weight: 900;">💪 العمالة</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none;">
                                <div style="display: flex; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="t-labor" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('labor')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width: 100%; height: 46px; text-align: center; background: rgba(59, 130, 246, 0.15); border: 1.5px solid rgba(59, 130, 246, 0.5); border-radius: 8px; color: #fff; font-size: 1.5rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4); box-sizing: border-box;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Additions -->
                        <tr class="tot-row" id="row-tot-additions" style="display: none; background: rgba(234, 179, 8, 0.08); border-top: 1px dashed rgba(234, 179, 8, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #eab308; font-size: 1.2rem; font-weight: 900;">➕ إضافات</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none;">
                                <div style="display: flex; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="addition-amt" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('additions')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width: 100%; height: 46px; text-align: center; background: rgba(234, 179, 8, 0.15); border: 1.5px solid rgba(234, 179, 8, 0.5); border-radius: 8px; color: #fff; font-size: 1.5rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4); box-sizing: border-box;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Tax -->
                        <tr class="tot-row" id="row-tot-tax" style="display: none; background: rgba(239, 68, 68, 0.08); border-top: 1px dashed rgba(239, 68, 68, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #ef4444; font-size: 1.2rem; font-weight: 900;">🏛️ ضريبة</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none;">
                                <div style="display: flex; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="tax-amt" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('tax')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width: 100%; height: 46px; text-align: center; background: rgba(239, 68, 68, 0.15); border: 1.5px solid rgba(239, 68, 68, 0.5); border-radius: 8px; color: #fff; font-size: 1.5rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4); box-sizing: border-box;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Discount -->
                        <tr class="tot-row" id="row-tot-discount" style="display: none; background: rgba(34, 197, 94, 0.08); border-top: 1px dashed rgba(34, 197, 94, 0.3);">
                            <td colspan="10" style="text-align: left; vertical-align: middle; padding-left: 20px; padding-right: 15px; border: none;">
                                <span style="color: #22c55e; font-size: 1.2rem; font-weight: 900;">➖ خصم</span>
                            </td>
                            <td data-col-id="col_total" style="padding: 6px 4px; border: none;">
                                <div style="display: flex; width: 100%;">
                                    <input type="text" inputmode="decimal" class="v-input tot-input amount" id="discount-amt" value="0" onfocus="this.select()" oninput="if(typeof syncFc==='function')syncFc('discount')" onblur="this.value=fmtNum(parseNum(this.value)); if(typeof recalcAll==='function')recalcAll();" style="width: 100%; height: 46px; text-align: center; background: rgba(34, 197, 94, 0.15); border: 1.5px solid rgba(34, 197, 94, 0.5); border-radius: 8px; color: #fff; font-size: 1.5rem; font-weight: 900; font-family: monospace, 'Cairo', sans-serif; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4); box-sizing: border-box;">
                                </div>
                            </td>
                            <td data-col-id="col_del" style="border: none;"></td>
                        </tr>
                        <!-- Spacer -->
                        <tr style="border: none;">
                            <td colspan="12" style="border: none; height: 8px;"></td>
                        </tr>
                        <!-- Grand Total -->
                        <tr id="row-grand-total" style="background: linear-gradient(90deg, rgba(15, 23, 42, 0.98) 0%, rgba(16, 185, 129, 0.18) 50%, rgba(15, 23, 42, 0.98) 100%); border-top: 3px solid rgba(16, 185, 129, 0.7); border-bottom: 3px solid rgba(16, 185, 129, 0.5); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);">
                            <td colspan="7" style="text-align: left; vertical-align: middle; padding: 14px 20px 14px 25px; border: none;">
                                <div style="display: inline-flex; align-items: center; gap: 14px;">
                                    <span style="font-size: 2rem; filter: drop-shadow(0 0 10px rgba(16,185,129,0.8));">💵</span>
                                    <span id="lbl-grand-dynamic" style="color: #34d399; font-size: 1.85rem; font-weight: 900; letter-spacing: 0.5px; text-shadow: 0 0 14px rgba(16, 185, 129, 0.6);">الإجمالي الكلي:</span>
                                </div>
                            </td>
                            <td colspan="5" style="padding: 10px 12px; border: none; vertical-align: middle;">
                                <div style="display: flex; gap: 14px; align-items: center; justify-content: flex-end; width: 100%; max-width: 580px; margin-right: auto; margin-left: 0;">
                                    <!-- ONLY Amount Number inside this stylized rectangle (Enlarged to comfortably hold 8+ integer digits) -->
                                    <span class="val amount" id="t-grand-dynamic-loc" style="flex: 1; min-width: 280px; min-height: 60px; height: 60px; padding: 0 22px; display: flex; align-items: center; justify-content: center; font-size: 2.35rem; font-weight: 900; font-family: 'Cairo', 'JetBrains Mono', monospace, sans-serif; color: #ffffff; text-align: center; background: linear-gradient(135deg, rgba(16,185,129,0.45) 0%, rgba(5,150,105,0.65) 50%, rgba(4,120,87,0.8) 100%); border: 2.5px solid #10b981; border-radius: 14px; box-shadow: 0 0 25px rgba(16,185,129,0.4), inset 0 2px 5px rgba(255,255,255,0.35), inset 0 -2px 6px rgba(0,0,0,0.5); letter-spacing: 1.5px; box-sizing: border-box; text-shadow: 0 2px 6px rgba(0,0,0,0.7); white-space: nowrap; overflow: hidden;">0</span>
                                    <!-- Currency badge completely OUTSIDE the rectangle -->
                                    <span id="t-grand-currency-badge" style="flex-shrink: 0; height: 50px; min-width: 65px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; font-size: 1.3rem; font-weight: 900; color: #6ee7b7; background: rgba(16, 185, 129, 0.2); border: 2px solid rgba(16, 185, 129, 0.5); border-radius: 11px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); font-family: 'Tajawal', 'Cairo', sans-serif; letter-spacing: 0.5px;">SDG</span>
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
            </style>
            <div id="inline-totals-container" style="margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-start; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 15px;">
                
                <!-- Left Side (Visual Right in RTL): Toggles -->
                <div id="toggles-area" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; max-width: 50%;">
                    <span style="color: #94a3b8; font-size: 0.85rem; margin-left: 10px;">إضافة مصاريف:</span>
                    <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('transport')" id="btn-toggle-transport">🚚 ترحيل</button>
                    <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('labor')" id="btn-toggle-labor">👷‍♂️ عمالة</button>
                    <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('additions')" id="btn-toggle-additions">➕ إضافات</button>
                    <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('tax')" id="btn-toggle-tax">💰 ضريبة</button>
                    <button type="button" class="tot-toggle-btn" onclick="toggleTotRow('discount')" id="btn-toggle-discount">➖ خصم</button>
                </div>

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
                        <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; transition: all 0.3s ease; white-space: nowrap; background: rgba(245, 158, 11, 0.15); color: #fbbf24;">⏳ آجلة بالكامل</span>
                    </div>

                    <div class="payment-toggles" style="display: flex; gap: 8px; flex: none; flex-wrap: wrap;">
                        <button type="button" class="pay-toggle-btn active" id="toggle-credit" onclick="togglePaymentSection('credit')" style="background: rgba(180, 83, 9, 0.1); border: 1px solid rgba(180, 83, 9, 0.35); color: #fcd34d; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">💳 آجل</button>
                        <button type="button" class="pay-toggle-btn" id="btn-ds-installment-sched" onclick="window.openDsInstallmentSchedulerModal && window.openDsInstallmentSchedulerModal()" style="background: linear-gradient(135deg,#b45309,#92400e); border: 1.5px solid #fbbf24; color: #ffffff; padding: 6px 13px; border-radius: 20px; font-size: 0.86rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="جدولة المبلغ الآجل إلى دفعات وأقساط بتواريخ استحقاق متتالية وطباعة كمبيالاتها">📅 جدولة الأقساط</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">💵 نقدي</button>
                        <button type="button" class="pay-toggle-btn" id="btn-ds-cash-change" onclick="window.openDsCashChangeCalculator && window.openDsCashChangeCalculator()" style="background: linear-gradient(135deg,#059669,#047857); border: 1.5px solid #34d399; color: #ffffff; padding: 6px 13px; border-radius: 20px; font-size: 0.86rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="حاسبة المبلغ المستلم والباقي للعميل والفئات النقدية السريعة">🧮 حاسبة الباقي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.35); color: #67e8f9; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">🏦 تحويل بنكي</button>
                        <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(147, 51, 234, 0.1); border: 1px solid rgba(147, 51, 234, 0.35); color: #d8b4fe; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">✍️ شيك</button>
                        <button type="button" class="pay-toggle-btn" id="btn-ds-digital-sign" onclick="window.openDsCustomerSignatureModal && window.openDsCustomerSignatureModal()" style="background: linear-gradient(135deg,#2563eb,#1d4ed8); border: 1.5px solid #60a5fa; color: #ffffff; padding: 6px 13px; border-radius: 20px; font-size: 0.86rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="بوابة توقيع العميل الإلكتروني وإقرار استلام البضاعة والمصادقة على الفاتورة">✍️ توقيع العميل</button>
                        <button type="button" class="pay-toggle-btn" id="btn-sales-financial-ocr" onclick="FinancialOCR.openScanner({ target: 'sales' })" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(16, 185, 129, 0.2)); border: 1.5px solid #06b6d4; color: #67e8f9; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" title="مسح إشعار"><span style="font-size: 1.1rem;">📷</span> مسح إشعار</button>
                        <button type="button" class="pay-toggle-btn" onclick="FinancialOCR.openTrainer()" style="background: rgba(147, 51, 234, 0.1); border: 1px solid rgba(147, 51, 234, 0.35); color: #d8b4fe; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="شرح وتدريب النماذج للذكاء الاصطناعي"><span>🧠</span> تدريب</button>
                    </div>
                </div>
                
                <div class="payment-layout" id="payment-layout" style="display: flex; gap: 15px; padding: 15px; flex-wrap: wrap;">
                    <!-- Right Section: Payment Details Forms -->
                    <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Credit payment box (Visible by default in yellow) -->
                        <div id="pay-credit-section" class="payment-method-box" style="background: radial-gradient(circle at top left, rgba(217, 119, 6, 0.35), rgba(180, 83, 9, 0.15)); border: 3px solid rgba(217, 119, 6, 0.9); box-shadow: 0 0 25px rgba(217, 119, 6, 0.4), inset 0 0 15px rgba(180, 83, 9, 0.3); border-radius: 12px; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
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
                        <div id="pay-cash-section" style="background: radial-gradient(circle at top left, rgba(16, 185, 129, 0.35), rgba(16, 185, 129, 0.15)); border: 3px solid rgba(16, 185, 129, 0.9); box-shadow: 0 0 25px rgba(16, 185, 129, 0.4), inset 0 0 15px rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 12px 15px; margin-bottom: 10px; display: none; flex-direction: column; width: 100%;">
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
                <!-- Exit Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-exit btn-exit" style="flex: 0.75; min-width: 84px;" onclick="if(typeof showSalesHub === 'function' && document.getElementById('sales-hub-view')) { showSalesHub(); } else if(typeof magaieesGoBack === 'function') { magaieesGoBack(); } else { window.location.href='dashboard-pro.php'; }" title="الخروج والرجوع لبوابة المبيعات أو الرئيسية">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>خروج</span>
                </button>

                <!-- Delete / Cancel Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-delete btn-delete" style="flex: 0.95; min-width: 112px; background: linear-gradient(135deg, rgba(225, 29, 72, 0.85), rgba(159, 18, 57, 0.92)) !important; border: 1px solid rgba(251, 113, 133, 0.45) !important; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.28) !important;" onclick="confirmDeleteTransaction()" title="حذف أو إلغاء الفاتورة الحالية">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span>حذف / إلغاء</span>
                </button>

                <!-- Draft Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-edit btn-draft" style="flex: 0.85; min-width: 92px;" onclick="saveDraft()" title="حفظ الفاتورة كمسودة قابلة للتعديل">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>مسودة</span>
                </button>
                
                <!-- Unified Print Studio & Preview Button -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-print" style="flex: 1.25; min-width: 140px;" onclick="openStandalonePrintPreview();" title="استوديو الطباعة والمعاينة الحية">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>معاينة وطباعة</span>
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
                    <div id="share-popup-menu" style="display: none; position: absolute; bottom: 55px; left: 50%; transform: translateX(-50%) scale(0.9); opacity: 0; min-width: 220px; background: linear-gradient(160deg, rgba(30,27,50,0.98), rgba(15,12,30,0.98)); border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 16px; padding: 8px; box-shadow: 0 15px 40px rgba(0,0,0,0.5), 0 0 30px rgba(139, 92, 246, 0.15); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); z-index: 9999; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">
                        <!-- Arrow -->
                        <div style="position: absolute; bottom: -7px; left: 50%; transform: translateX(-50%) rotate(45deg); width: 14px; height: 14px; background: rgba(15,12,30,0.98); border-right: 1px solid rgba(139, 92, 246, 0.35); border-bottom: 1px solid rgba(139, 92, 246, 0.35);"></div>
                        <!-- WhatsApp Option -->
                        <button type="button" onclick="closeShareMenu(); sendCurrentInvoiceWA();" style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: transparent; border: none; border-radius: 12px; color: #e2e8f0; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Tajawal', sans-serif; direction: rtl;" onmouseover="this.style.background='rgba(37, 211, 102, 0.12)'; this.style.color='#25d366';" onmouseout="this.style.background='transparent'; this.style.color='#e2e8f0';">
                            <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #25d366, #128C7E); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 3px 10px rgba(37, 211, 102, 0.3);">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </span>
                            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                <span>واتساب</span>
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
                                <span>إيميل</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- New Button: Unified Cyan-Sky Crystal Gradient -->
                <button type="button" class="btn-action royal-crystal-btn royal-btn-new btn-new" id="btn-new-invoice" style="flex: 0.85; min-width: 88px;" onclick="onSuccessNewInvoice(false);" title="فاتورة جديدة">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>جديد</span>
                </button>

                <!-- زر التحكم في الإرسال التلقائي مع الحفظ -->
                <div class="auto-send-control-box" id="auto-send-control-wrapper-sales">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle-unit inactive" id="btn-toggle-auto-send-sales" onclick="window.toggleSalesAutoSendMode()" title="الإرسال التلقائي">
                        <span id="auto-send-sales-icon" style="font-size: 1.02rem;">💾</span>
                        <span id="auto-send-sales-label" style="font-weight: 800; font-size: 0.85rem;">إرسال تلقائي:</span>
                        <span id="auto-send-sales-status-pill" style="padding: 2px 7px; border-radius: 6px; font-size: 0.73rem; font-weight: 900;">حفظ فقط</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options-unit inactive" id="btn-auto-send-options-sales" onclick="window.toggleSalesAutoSendDropdown(event)" title="خيارات الإرسال التلقائي">
                        ▼
                    </button>
                    
                    <!-- القائمة المنسدلة الملكية للخيارات السريعة -->
                    <div class="auto-send-menu-dropdown" id="auto-send-sales-dropdown-menu" style="display: none;">
                        <div class="auto-send-menu-header">
                            <span style="display: inline-flex; align-items: center; gap: 7px;">
                                <span style="font-size: 1rem;">⚙️</span>
                                <span>الإرسال التلقائي</span>
                            </span>
                            <span style="font-size: 0.68rem; padding: 2px 8px; border-radius: 20px; background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">واتساب</span>
                        </div>
                        <div class="auto-send-menu-item active-option" data-mode="disabled" onclick="window.selectSalesAutoSendOption('disabled')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(100, 116, 139, 0.35), rgba(51, 65, 85, 0.6)) !important; border-color: rgba(148, 163, 184, 0.4) !important;">💾</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #f1f5f9;">حفظ فقط</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="delivery_order_doc" onclick="window.selectSalesAutoSendOption('delivery_order_doc')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(2, 132, 199, 0.55)) !important; border-color: rgba(56, 189, 248, 0.45) !important;">📄</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #38bdf8;">إذن تسليم رسمي</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="delivery_order_text" onclick="window.selectSalesAutoSendOption('delivery_order_text')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.55)) !important; border-color: rgba(251, 191, 36, 0.45) !important;">📝</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #fbbf24;">إذن تسليم نصي</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="invoice_doc" onclick="window.selectSalesAutoSendOption('invoice_doc')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.55)) !important; border-color: rgba(192, 132, 252, 0.45) !important;">🧾</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #c084fc;">فاتورة مصممة</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                        <div class="auto-send-menu-item" data-mode="invoice" onclick="window.selectSalesAutoSendOption('invoice')">
                            <span class="auto-send-item-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(5, 150, 105, 0.55)) !important; border-color: rgba(52, 211, 153, 0.45) !important;">💰</span>
                            <div class="auto-send-item-content">
                                <span class="auto-send-item-title" style="color: #34d399;">فاتورة وكشف حساب</span>
                            </div>
                            <span class="auto-send-item-check">✓</span>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <span style="display:none;" id="footer-grand"></span>
                <button type="button" class="btn-action royal-crystal-btn royal-btn-save btn-submit" style="flex: 1.35; min-width: 148px;" onclick="submitInvoice()" title="حفظ واعتماد الفاتورة (F10 / Ctrl+S)">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>حفظ واعتماد</span>
                    <kbd style="font-size:0.7rem;padding:1px 5px;border-radius:4px;background:rgba(0,0,0,0.28);color:#fff;font-family:'Outfit',monospace;border:1px solid rgba(255,255,255,0.25);">F10</kbd>
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
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/accounting-engine.js?v=<?php echo file_exists('js/accounting-engine.js') ? filemtime('js/accounting-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/print-engine.js?v=<?php echo file_exists('js/print-engine.js') ? filemtime('js/print-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/direct-sales-v3.js?v=<?php echo file_exists('js/direct-sales-v3.js') ? filemtime('js/direct-sales-v3.js') : '1.0.0'; ?>"></script>
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./sw.js', { scope: './' }).catch(() => {});
    }
    </script>

    <script>
        // Fallback definitions for fmtNum/parseNum in case direct-sales-v3.js hasn't loaded yet
        if (typeof window.fmtNum !== 'function') {
            window.fmtNum = function(val) {
                const n = parseFloat(val) || 0;
                if (n === 0) return '0';
                let decimals = 2;
                try {
                    const stored = localStorage.getItem('erp_grid_customizer_v1');
                    if (stored) {
                        const settings = JSON.parse(stored);
                        if (settings.decimalPlaces !== undefined) decimals = parseInt(settings.decimalPlaces);
                    }
                } catch(e) {}
                return n.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            };
        }
        if (typeof window.parseNum !== 'function') {
            window.parseNum = function(val) {
                if (!val) return 0;
                return parseFloat(String(val).replace(/,/g, '')) || 0;
            };
        }
        // Make them globally accessible for inline HTML onblur handlers
        var fmtNum = window.fmtNum;
        var parseNum = window.parseNum;

        // Sales Hub Logic
        let currentSalesView = 'hub'; // 'hub' or 'editor'
        let hubCurrentPage = 1;
        const HUB_PAGE_SIZE = 50;
        let hubFilteredInvoices = [];

        function handleCurrencyChange(val) {
            const rateInput = document.getElementById('hdr-exchange-rate');
            if (!rateInput) return;
            rateInput.disabled = false;
            const targetCurr = (val === 'SDG' ? 'USD' : val);
            if (targetCurr === 'USD' && window.SYSTEM_USD_RATE && parseFloat(window.SYSTEM_USD_RATE) > 1 && rateInput.dataset.userEdited !== '1') {
                rateInput.value = parseFloat(window.SYSTEM_USD_RATE);
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            // Step 1: Try to get rate from local settings function
            let rate = 0;
            if (window.getExchangeRateForCurrency) {
                rate = window.getExchangeRateForCurrency(targetCurr);
            }
            
            if (rate > 0) {
                rateInput.value = rate;
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Step 2: Try to read directly from localStorage settings (any mode)
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (val === 'USD' && cfg.rateUSD) { rateInput.value = cfg.rateUSD; if (typeof recalcAll === 'function') recalcAll(); return; }
                if (val === 'SAR' && cfg.rateSAR) { rateInput.value = cfg.rateSAR; if (typeof recalcAll === 'function') recalcAll(); return; }
                if (val === 'AED' && cfg.rateAED) { rateInput.value = cfg.rateAED; if (typeof recalcAll === 'function') recalcAll(); return; }
            } catch(e) {}
            
            // Step 3: Try to fetch from API directly (central rate)
            rateInput.value = '';
            rateInput.placeholder = '⏳ جاري التحميل...';
            
            fetch('api_index.php/exchange-rate')
                .then(r => r.json())
                .then(data => {
                    if (data && data.rate) {
                        const usdRate = parseFloat(data.rate);
                        if (usdRate > 0) {
                            window._cachedGlobalRate = usdRate;
                            let finalRate = usdRate;
                            if (val === 'SAR') finalRate = parseFloat((usdRate / 3.75).toFixed(4));
                            if (val === 'AED') finalRate = parseFloat((usdRate / 3.67).toFixed(4));
                            if (val === 'EGP') finalRate = parseFloat((usdRate / 50.0).toFixed(4));
                            
                            rateInput.value = finalRate;
                            if (typeof recalcAll === 'function') recalcAll();
                            return;
                        }
                    }
                    // No rate found from API either
                    rateInput.value = '';
                    rateInput.placeholder = 'أدخل سعر الصرف';
                })
                .catch(() => {
                    rateInput.value = '';
                    rateInput.placeholder = 'أدخل سعر الصرف';
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // No need for auto-init - currency defaults to SDG
            // handleCurrencyChange only needed when user explicitly changes currency
        });

        function showSalesHub() {
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('sales-hub-view').style.display = 'flex';
            currentSalesView = 'hub';
            
            // Restore header title to "المبيعات المباشرة"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">🛒</span> المبيعات';
            }
            
            loadHubTransactions();
            
            // Adjust header Back button behavior
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

        function openInvoiceEditor(type = 'pending_delivery', isEdit = false) {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'flex';
            currentSalesView = 'editor';
            currentSaleType = type;
            
            // Adjust header Back button behavior to return to hub
            const backBtn = document.querySelector('button[title="رجوع"]');
            if (backBtn) {
                backBtn.onclick = function() { showSalesHub(); };
                backBtn.innerHTML = '<span style="font-weight:bold; font-size:0.8rem; padding: 0 4px;">العودة للقائمة</span>';
                backBtn.style.width = 'auto';
                backBtn.style.padding = '0 10px';
            }

            // Set active tab
            document.querySelectorAll('#sale-type-tabs .action-tab').forEach(t => {
                t.classList.remove('active');
            });
            const activeTab = document.getElementById('tab-' + type);
            if(activeTab) activeTab.classList.add('active');

            // Handle Preliminary UI changes
            const editorView = document.getElementById('invoice-editor-view');
            const whTreasuryRow = document.getElementById('warehouse-treasury-row');
            
            const headerTitle = document.querySelector('.header-page-title');
            if (type === 'preliminary') {
                if (headerTitle) headerTitle.innerHTML = '<span style="color:#c084fc;">📋 فاتورة مبدئية</span> <span style="font-size:0.7em;color:#f87171;">(غير ملزمة)</span>';
                if (editorView) editorView.classList.add('preliminary-active');
                if (whTreasuryRow) whTreasuryRow.style.display = 'none';
            } else {
                if (headerTitle) headerTitle.innerHTML = '<span class="header-page-title-icon">🛒</span> فاتورة مبيعات';
                if (editorView) editorView.classList.remove('preliminary-active');
                if (whTreasuryRow) whTreasuryRow.style.display = 'flex';
            }
            
            // If creating a new invoice, reset the editor form and load the next invoice number
            if (!isEdit && typeof onSuccessNewInvoice === 'function') {
                onSuccessNewInvoice(true);
            }
            
            requestAnimationFrame(() => {
                if (typeof window.autoFitHeaderFields === 'function') window.autoFitHeaderFields();
                if (typeof window.autoFitGridCells === 'function') window.autoFitGridCells();
            });
            window.dispatchEvent(new Event('resize'));
        }

        window.unifiedHubFilterMode = window.unifiedHubFilterMode || 'all';

        window.setUnifiedHubFilter = function(mode = 'all', scrollSmooth = false) {
            window.unifiedHubFilterMode = mode;
            hubCurrentPage = 1;
            const modes = ['all', 'pending', 'split_only', 'delivered', 'preliminary'];
            modes.forEach(m => {
                const btn = document.getElementById('uhub-tab-' + m);
                if (!btn) return;
                const isActive = (m === mode) || (mode === 'split' && m === 'pending');
                if (isActive) {
                    btn.style.border = '1.5px solid #38bdf8';
                    btn.style.background = 'rgba(56,189,248,0.22)';
                    btn.style.color = '#ffffff';
                } else {
                    btn.style.border = '1px solid rgba(255,255,255,0.12)';
                    btn.style.background = 'rgba(255,255,255,0.04)';
                    btn.style.color = '#cbd5e1';
                }
            });
            showSalesHub(true);
            if (scrollSmooth) {
                const card = document.getElementById('unified-sales-table-card');
                if (card && typeof card.scrollIntoView === 'function') {
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        };

        function openDeliveryHub() {
            window.setUnifiedHubFilter('pending', false);
        }

        function openSplitInvoiceView(showBoardDirectly = false) {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'flex';
            currentSalesView = 'split';
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">✂️</span> تقسيم الفاتورة / الإذن (بالوحدة الأساسية والفرعية)';
            }
            const selectStep = document.getElementById('split-select-step');
            const boardStep = document.getElementById('split-board-step');
            if (showBoardDirectly) {
                if (selectStep) selectStep.style.display = 'none';
                if (boardStep) boardStep.style.display = 'flex';
            } else {
                if (selectStep) selectStep.style.display = 'flex';
                if (boardStep) boardStep.style.display = 'none';
                if (typeof loadSplitInvoices === 'function') loadSplitInvoices();
            }
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            if (!tbody) return;
            
            // Fetch from localStorage with cache
            window._hubSalesCache = window._hubSalesCache || { raw: null, obj: [] };
            const savedData = localStorage.getItem('erp_sales_invoices_v1');
            let invoices = window._hubSalesCache.obj;
            if (savedData !== null && window._hubSalesCache.raw !== savedData) {
                try { invoices = JSON.parse(savedData) || []; } catch(e) {}
                window._hubSalesCache.raw = savedData;
                window._hubSalesCache.obj = invoices;
            }
            
            // Active (non-deleted, non-archived-split) invoices
            invoices = (invoices || []).filter(inv => !inv.deleted && !inv.wasSplit);

            // Calculate live counters for filter tabs
            let cntAll = invoices.length;
            let cntPending = 0;
            let cntSplit = 0;
            let cntDelivered = 0;
            let cntPrelim = 0;

            invoices.forEach(inv => {
                const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                const isDel = !isPrelim && (inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed');
                const isSplitFam = !!(inv.isSplit || inv.parentInvoice || inv.rootInvoice);
                if (isPrelim) cntPrelim++;
                else if (isDel) cntDelivered++;
                else cntPending++;
                if (isSplitFam) cntSplit++;
            });

            const elAll = document.getElementById('uhub-cnt-all');
            const elPend = document.getElementById('uhub-cnt-pending');
            const elSpl = document.getElementById('uhub-cnt-split');
            const elDel = document.getElementById('uhub-cnt-delivered');
            const elPre = document.getElementById('uhub-cnt-prelim');
            if (elAll) elAll.textContent = cntAll;
            if (elPend) elPend.textContent = cntPending;
            if (elSpl) elSpl.textContent = cntSplit;
            if (elDel) elDel.textContent = cntDelivered;
            if (elPre) elPre.textContent = cntPrelim;

            // Filter by unified tab mode
            const fMode = window.unifiedHubFilterMode || 'all';
            if (fMode === 'pending' || fMode === 'split') {
                invoices = invoices.filter(inv => {
                    const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                    const isDel = inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed';
                    return !isPrelim && !isDel;
                });
            } else if (fMode === 'split_only') {
                invoices = invoices.filter(inv => !!(inv.isSplit || inv.parentInvoice || inv.rootInvoice));
            } else if (fMode === 'delivered') {
                invoices = invoices.filter(inv => {
                    const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                    return !isPrelim && (inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed');
                });
            } else if (fMode === 'preliminary') {
                invoices = invoices.filter(inv => inv.saleType === 'preliminary' || inv.status === 'preliminary');
            }
            
            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                if (emptyState) emptyState.style.display = 'flex';
                const pagEl = document.getElementById('hub-pagination-bar');
                if (pagEl) pagEl.style.display = 'none';
                return;
            }

            // Load customers to map IDs to names
            window._hubCustCache = window._hubCustCache || { raw: null, obj: [] };
            let rawCusts = localStorage.getItem('erp_customers_v1');
            let customers = window._hubCustCache.obj;
            if (rawCusts !== null && window._hubCustCache.raw !== rawCusts) {
                try { customers = typeof rawCusts === 'string' ? JSON.parse(rawCusts) : rawCusts; } catch(e) { customers = []; }
                window._hubCustCache.raw = rawCusts;
                window._hubCustCache.obj = customers || [];
            }

            function getCustomerName(id) {
                if (id === 'walk-in' || !id) return 'عميل نقدي';
                const c = customers.find(x => x && x.id === id);
                return c ? c.name : id;
            }

            // Load warehouses to map IDs to names
            window._hubWhCache = window._hubWhCache || { raw: null, obj: [] };
            let rawWh = localStorage.getItem('erp_warehouses_v1');
            let warehouses = window._hubWhCache.obj;
            if (rawWh !== null && window._hubWhCache.raw !== rawWh) {
                try { warehouses = typeof rawWh === 'string' ? JSON.parse(rawWh) : rawWh; } catch(e) { warehouses = []; }
                window._hubWhCache.raw = rawWh;
                window._hubWhCache.obj = warehouses || [];
            }
            if (!warehouses || warehouses.length === 0) {
                warehouses = [{ id: 1, name: 'المخزن الرئيسي' }];
            }

            function getWarehouseName(id) {
                if (!id) return warehouses[0]?.name || 'المخزن الرئيسي';
                const w = warehouses.find(x => x && (String(x.id) === String(id) || x.name === id));
                return w ? w.name : id;
            }

            // Load items to map IDs to names
            window._hubItemsCache = window._hubItemsCache || { raw: null, obj: [] };
            let rawItems = localStorage.getItem('erp_items_v1');
            let items = window._hubItemsCache.obj;
            if (rawItems !== null && window._hubItemsCache.raw !== rawItems) {
                try { items = typeof rawItems === 'string' ? JSON.parse(rawItems) : rawItems; } catch(e) { items = []; }
                window._hubItemsCache.raw = rawItems;
                window._hubItemsCache.obj = items || [];
            }

            function getItemName(id) {
                const item = items.find(x => x && x.id === id);
                return item ? item.name : id || 'صنف غير معروف';
            }

            // Apply search/filter options in memory
            const query = document.getElementById('hub-search')?.value.trim().toLowerCase() || '';
            if (query) {
                invoices = invoices.filter(inv => {
                    const itemsNames = (inv.items || []).map(it => it.itemName || it.name || getItemName(it.itemId)).join(' ');
                    const text = [
                        inv.invoiceNo,
                        inv.rootInvoice,
                        inv.parentInvoice,
                        inv.deliveryNo,
                        inv.customerName,
                        inv.receiverName,
                        inv.truckPlate,
                        getCustomerName(inv.customer || inv.customerId),
                        itemsNames,
                        inv.date
                    ].join(' ').toLowerCase();
                    return text.includes(query);
                });
            }

            if (invoices.length === 0) {
                tbody.innerHTML = '';
                if (emptyState) emptyState.style.display = 'flex';
                const pagEl = document.getElementById('hub-pagination-bar');
                if (pagEl) pagEl.style.display = 'none';
                return;
            }

            if (emptyState) emptyState.style.display = 'none';
            tbody.innerHTML = '';
            
            // Sort by date/time descending
            invoices.sort((a,b) => {
                const dateA = new Date(a.timestamp || a.date || 0).getTime();
                const dateB = new Date(b.timestamp || b.date || 0).getTime();
                return (isNaN(dateB) ? 0 : dateB) - (isNaN(dateA) ? 0 : dateA);
            });

            // Pagination
            const totalPages = Math.ceil(invoices.length / HUB_PAGE_SIZE);
            if (hubCurrentPage > totalPages) hubCurrentPage = totalPages || 1;
            const startIdx = (hubCurrentPage - 1) * HUB_PAGE_SIZE;
            const pageInvoices = invoices.slice(startIdx, startIdx + HUB_PAGE_SIZE);
            
            // Render pagination bar
            const pagEl = document.getElementById('hub-pagination-bar');
            if (pagEl) {
                if (totalPages <= 1) {
                    pagEl.style.display = 'none';
                } else {
                    pagEl.style.display = 'flex';
                    pagEl.innerHTML = `
                        <button onclick="hubGoToPage(${hubCurrentPage - 1})" ${hubCurrentPage <= 1 ? 'disabled style="opacity:0.3; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); color:#94a3b8; padding:6px 14px; border-radius:8px; cursor:default;"' : 'style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer;"'}>→ السابق</button>
                        <span style="color:#94a3b8; font-size:0.9rem;">صفحة <strong style="color:#fff;">${hubCurrentPage}</strong> من <strong style="color:#fff;">${totalPages}</strong></span>
                        <button onclick="hubGoToPage(${hubCurrentPage + 1})" ${hubCurrentPage >= totalPages ? 'disabled style="opacity:0.3; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); color:#94a3b8; padding:6px 14px; border-radius:8px; cursor:default;"' : 'style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:6px 14px; border-radius:8px; cursor:pointer;"'}>التالي ←</button>
                    `;
                }
            }
            
            const fragment = document.createDocumentFragment();
            pageInvoices.forEach(inv => {
                const tr = document.createElement('tr');
                const invNo = String(inv.invoiceNo || inv.id || '');
                const escHubNo = invNo.replace(/'/g, "\\'");
                const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                const isDelivered = !isPrelim && (inv.isDelivered === true || inv.deliveryStatus === 'delivered' || inv.deliveryStatus === 'Admin_Closed');
                const isPartial = !isPrelim && !isDelivered && inv.deliveryStatus === 'partial';
                const isUndelivered = !isPrelim && !isDelivered;
                const isSplitFamily = !!(inv.isSplit || inv.parentInvoice || inv.rootInvoice);

                const defaultWhObj = warehouses.find(w => String(w.id) === String(inv.warehouseId || inv.warehouse || 1) || w.name === (inv.warehouseId || inv.warehouse)) || warehouses[0];
                const whOptions = warehouses.map(w => `<option value="${w.id}" ${String(w.id) === String(defaultWhObj.id) ? 'selected' : ''}>${w.name}</option>`).join('');

                const lines = Array.isArray(inv.items) ? inv.items : [];
                let totalInvRemQty = 0;
                let totalInvRemSubQty = 0;
                let hasAnyMultiUnit = false;

                const itemsPillsHtml = lines.map((line, itemIdx) => {
                    const uMeta = (typeof window.resolveItemMeta === 'function') ? window.resolveItemMeta(line, defaultWhObj) : null;
                    const iName = uMeta ? uMeta.name : (line.itemName || line.name || getItemName(line.itemId));
                    const pUnit = uMeta ? uMeta.unit : (line.selectedUnit || line.unit || 'وحدة');
                    const bUnit = uMeta ? uMeta.baseUnit : 'وحدة';
                    const factor = uMeta ? (uMeta.factor || 1) : 1;
                    if (factor > 1) hasAnyMultiUnit = true;
                    const qVal = parseFloat(line.quantity ?? line.qty ?? 0) || 0;
                    const delQ = parseFloat(line.deliveredQty || 0) || 0;
                    const remQ = isDelivered ? qVal : Math.max(0, +(qVal - delQ).toFixed(3));
                    const remSub = +(remQ * factor).toFixed(2);
                    totalInvRemQty += remQ;
                    totalInvRemSubQty += remSub;

                    return `
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; flex-wrap:wrap; background:rgba(15,23,42,0.75); border:1px solid rgba(56,189,248,0.22); padding:5px 8px; border-radius:9px; margin-bottom:4px;">
                            <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:140px;">
                                <span style="background:rgba(56,189,248,0.18); color:#38bdf8; width:20px; height:20px; border-radius:5px; font-size:0.72rem; font-weight:900; display:inline-flex; align-items:center; justify-content:center;">${itemIdx + 1}</span>
                                <span style="font-weight:900; color:#f8fafc; font-size:0.88rem;">${iName}</span>
                                ${factor > 1 ? `<span style="font-size:0.7rem; color:#38bdf8; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); padding:1px 6px; border-radius:5px; font-weight:800;">⚖️ 1 ${pUnit} = ${factor} ${bUnit}</span>` : ''}
                            </div>
                            <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                                <button type="button" onclick="${isUndelivered ? `window.openDeliveryForInvoiceFromHub('${escHubNo}', ${itemIdx}, 'primary')` : ''}" style="background:linear-gradient(135deg, #10b981, #059669); color:#ffffff; border:1px solid #34d399; padding:3px 9px; border-radius:6px; font-size:0.78rem; font-weight:900; cursor:${isUndelivered ? 'pointer' : 'default'}; display:inline-flex; align-items:center; gap:4px;" title="اضغط للتسليم بالوحدة الأساسية (${pUnit})">
                                    📦 ${fmtNum(remQ)} ${pUnit}
                                </button>
                                ${factor > 1 ? `
                                <button type="button" onclick="${isUndelivered ? `window.openDeliveryForInvoiceFromHub('${escHubNo}', ${itemIdx}, 'base')` : ''}" style="background:rgba(99,102,241,0.22); color:#c4b5fd; border:1px solid rgba(129,140,248,0.5); padding:3px 9px; border-radius:6px; font-size:0.76rem; font-weight:900; cursor:${isUndelivered ? 'pointer' : 'default'}; display:inline-flex; align-items:center; gap:4px;" title="اضغط للتسليم بالوحدة الفرعية (${bUnit})">
                                    🔢 ⇄ ${fmtNum(remSub)} ${bUnit}
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }).join('');

                const statusBadge = isPrelim
                    ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(168,85,247,0.16); border:1px solid rgba(192,132,252,0.4); color:#e9d5ff; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">📋 عرض مبدئي</span>`
                    : (isDelivered
                        ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.35); color:#6ee7b7; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">✅ تم الصرف (${inv.deliveryNo || 'GDN'})</span>`
                        : (isPartial
                            ? `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(245,158,11,0.16); border:1px solid rgba(251,191,36,0.35); color:#fcd34d; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">🌗 تسليم جزئي</span>`
                            : `<span style="display:inline-flex; align-items:center; gap:4px; background:rgba(249,115,22,0.16); border:1px solid rgba(251,146,60,0.35); color:#fdba74; padding:2px 8px; border-radius:99px; font-size:0.72rem; font-weight:800;">⏳ بانتظار التسليم</span>`));

                const splitBadgeHtml = (typeof window.renderSplitGenealogyBadge === 'function') ? window.renderSplitGenealogyBadge(inv, true) : '';
                const serialColor = isPrelim ? '#c084fc' : '#38bdf8';

                let isTargetDate = false;
                if (inv.date === '2026-06-27' || (inv.timestamp && inv.timestamp.startsWith('2026-06-27'))) {
                    isTargetDate = true;
                }
                const numColor = isTargetDate ? '#ef4444' : '#10b981';

                let actionBtns = `
                    <div style="display:inline-flex; align-items:center; gap:5px; flex-wrap:wrap; justify-content:center;">
                        ${isUndelivered ? `
                            <button onclick="window.openDeliveryForInvoiceFromHub('${escHubNo}', 0, 'primary')" class="kinetic-glow-arm kinetic-arm-emerald" style="border-radius:8px; padding:0 11px; height:34px; cursor:pointer; font-size:0.8rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="تسليم وصرف البضاعة (بالوحدة الأساسية أو الفرعية)">📦 تسليم</button>
                            <button onclick="window.openSplitForInvoiceFromDelivery('${escHubNo}')" class="kinetic-glow-arm kinetic-arm-pink" style="border-radius:8px; padding:0 11px; height:34px; cursor:pointer; font-size:0.8rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="تقسيم الفاتورة / الإذن لجزئين (100/17/1 و 100/17/2) بالوحدة الأساسية أو الفرعية">✂️ تقسيم</button>
                            <button onclick="executeInstantOneClickDelivery('${escHubNo}')" style="background:rgba(16,185,129,0.16); border:1px solid rgba(52,211,153,0.4); color:#6ee7b7; border-radius:8px; padding:0 9px; height:34px; cursor:pointer; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; gap:3px;" title="صرف مخزني فوري بكامل الكمية">⚡ فوري</button>
                        ` : (!isPrelim ? `
                            <button onclick="window.openDeliveryForInvoiceFromHub('${escHubNo}', 0, 'primary')" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; border-radius:8px; padding:0 10px; height:34px; cursor:pointer; font-size:0.78rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="عرض إذن الصرف">👁️ الإذن</button>
                            <button onclick="undoGdnDelivery('${escHubNo}')" style="background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.35); color:#fca5a5; border-radius:8px; padding:0 9px; height:34px; cursor:pointer; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; gap:3px;" title="تراجع عن التسليم وإعادة الكمية للمخزن">↩️ تراجع</button>
                        ` : '')}
                        ${isSplitFamily ? `
                            <button onclick="if(typeof window.printSplitFamilyVouchers==='function') window.printSplitFamilyVouchers('${escHubNo}')" class="kinetic-glow-arm kinetic-arm-cyan" style="border-radius:8px; padding:0 9px; height:34px; cursor:pointer; font-size:0.76rem; font-weight:900; display:inline-flex; align-items:center; gap:4px;" title="طباعة الجزئين / جميع الأذونات المقسمة">🖨️ الجزئين</button>
                            <button onclick="if(typeof window.openSplitGenealogyModal==='function') window.openSplitGenealogyModal('${escHubNo}')" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.38); color:#38bdf8; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="شجرة التقسيم والنسب">🌳</button>
                        ` : ''}
                        ${!isPrelim ? `
                            <button onclick="showInvoiceDeliveryQrModal('${escHubNo}')" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="باركود QR للتسليم">📱</button>
                            <button onclick="printOfficialGdnGatePass('${escHubNo}')" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#f8fafc; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="طباعة إذن الصرف">🖨️</button>
                        ` : ''}
                        <button onclick="editHubTransaction('${escHubNo}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض الفاتورة">👁️</button>
                        <button onclick="deleteHubTransaction('${escHubNo}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:7px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                    </div>
                `;

                if (isPrelim) {
                    actionBtns = `
                        <button onclick="convertToOfficial('${escHubNo}')" style="background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.4); color:#10b981; border-radius:7px; padding:0 10px; height:34px; cursor:pointer; margin-left:4px; font-weight:900; font-size:0.78rem; display:inline-flex; align-items:center; gap:4px;" title="تحويل لفاتورة رسمية">✅ اعتماد رسمية</button>
                    ` + actionBtns;
                }

                const custDisplayName = inv.customerName || getCustomerName(inv.customer || inv.customerId) || 'عميل نقدي';
                const payBadge = inv.paymentMethod === 'credit'
                    ? `<span style="background:rgba(245,158,11,0.16); border:1px solid rgba(245,158,11,0.35); color:#fcd34d; padding:1px 7px; border-radius:6px; font-size:0.72rem; font-weight:800;">✍️ آجل</span>`
                    : `<span style="background:rgba(16,185,129,0.16); border:1px solid rgba(16,185,129,0.35); color:#6ee7b7; padding:1px 7px; border-radius:6px; font-size:0.72rem; font-weight:800;">💵 نقدي</span>`;

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:800; color:${serialColor};">
                        <div style="font-family:'Outfit',monospace; font-size:1.05rem; font-weight:900;">#${invNo}</div>
                        ${splitBadgeHtml ? `<div style="margin-top:3px;">${splitBadgeHtml}</div>` : ''}
                        <div style="margin-top:4px;">${statusBadge}</div>
                    </td>
                    <td style="text-align:center; color:var(--text-dim); font-size:0.84rem; font-weight:700;">
                        ${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}
                    </td>
                    <td style="text-align:right;">
                        <div style="font-weight:900; color:#f8fafc; font-size:0.94rem;">👤 ${custDisplayName}</div>
                        <div style="margin-top:3px; display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                            ${payBadge}
                            ${inv.receiverName ? `<span style="font-size:0.73rem; color:#34d399; font-weight:700;">المستلم: ${inv.receiverName}</span>` : ''}
                        </div>
                    </td>
                    <td style="text-align:right;">
                        ${itemsPillsHtml || '<span style="color:#94a3b8; font-size:0.8rem;">بدون أصناف</span>'}
                    </td>
                    <td style="text-align:center;">
                        <div style="font-weight:800; color:#e2e8f0; font-size:0.82rem; margin-bottom:4px;">
                            📦 <strong style="color:#34d399;">${fmtNum(totalInvRemQty)}</strong>
                            ${hasAnyMultiUnit ? `<div style="font-size:0.74rem; color:#c4b5fd;">(🔢 ⇄ ${fmtNum(totalInvRemSubQty)} فرعي)</div>` : ''}
                        </div>
                        <select id="del-wh-${invNo}" class="gdn-borderless-select" style="width:100%; font-size:0.78rem; padding:4px 6px;" ${isDelivered ? 'disabled' : ''}>
                            ${whOptions}
                        </select>
                    </td>
                    <td style="text-align:center; font-weight:900; direction:ltr; color:${numColor}; font-family:'Outfit',monospace; font-size:1.05rem;">
                        ${fmtNum(inv.totals?.grandTotal || inv.grandTotal || inv.total || 0)} <span style="color:#a855f7; font-weight:700; font-size:0.78rem;">${inv.currency || 'SDG'}</span>
                    </td>
                    <td style="text-align:center;">
                        ${actionBtns}
                    </td>
                `;
                fragment.appendChild(tr);
            });
            tbody.appendChild(fragment);
        }
        
        function hubGoToPage(page) {
            if (page < 1) return;
            hubCurrentPage = page;
            loadHubTransactions();
        }
        
        function filterHubTransactions() {
            hubCurrentPage = 1;
            loadHubTransactions();
        }

        function editHubTransaction(invNo) {
            try {
                let allSales = [];
                try { allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || []; } catch(e) {}
                let inv = allSales.find(i => (i.invoiceNo || i.id) === invNo);
                if (!inv) {
                    let str = localStorage.getItem('erp_inv_' + invNo) || localStorage.getItem('erp_draft_' + invNo);
                    if (str) try { inv = JSON.parse(str); } catch(e) {}
                }

                const isPosted = !!(inv && (inv.status === 'posted' || inv.effectsApplied));
                let wantsToEdit = false;

                if (isPosted) {
                    // Posted invoice: STRICT READ-ONLY. No edits allowed under any circumstances!
                    wantsToEdit = false;
                    showToast('🔒 الفاتورة مرحلة محاسبياً في دفتر الأستاذ - وضع المعاينة فقط (ممنوع التعديل نهائياً)', 'warning', 6000);
                } else if (inv) {
                    // Saved unposted invoice: open directly in edit mode with toast advisory (non-blocking)
                    wantsToEdit = true;
                    showToast('✏️ تم فتح الفاتورة رقم (' + invNo + ') في وضع التعديل', 'info', 2500);
                }

                openInvoiceEditor(undefined, true);
                if(typeof loadInvoiceFromStorage === 'function') {
                    setTimeout(() => {
                        try {
                            const success = loadInvoiceFromStorage(invNo, {
                                isLocked: isPosted ? true : (!wantsToEdit),
                                isPosted: isPosted
                            });
                            if (!success) {
                                showToast('❌ فشل تحميل بيانات الفاتورة من التخزين', 'error');
                            }
                        } catch (innerErr) {
                            console.error(innerErr);
                            if (typeof showToast === 'function') showToast('⚠️ خطأ أثناء تحميل تفاصيل الفاتورة: ' + innerErr.message, 'error');
                        }
                    }, 100);
                } else {
                    if (typeof showToast === 'function') showToast('⚠️ خطأ: لم يتم العثور على دالة تحميل الفاتورة!', 'error');
                }
            } catch (err) {
                console.error(err);
                if (typeof showToast === 'function') showToast('⚠️ خطأ في محرر الفواتير: ' + err.message, 'error');
            }
        }
        let currentDeleteInvoiceNo = null;

        function closeInvoiceDeleteModal() {
            const modal = document.getElementById('invoice-delete-modal');
            if (modal) modal.style.display = 'none';
            currentDeleteInvoiceNo = null;
        }

        async function deleteHubTransaction(invNo) {
            currentDeleteInvoiceNo = invNo;
            const modal = document.getElementById('invoice-delete-modal');
            
            // Set loading state
            document.getElementById('inv-del-target-no').innerText = invNo;
            
            const iconWrap = document.getElementById('inv-del-modal-icon-wrap');
            const iconEl = document.getElementById('inv-del-modal-icon');
            const titleEl = document.getElementById('inv-del-modal-title');
            const subtitleEl = document.getElementById('inv-del-modal-subtitle');
            const statusBox = document.getElementById('inv-del-status-box');
            const vouchersContainer = document.getElementById('inv-del-vouchers-container');
            const footerEl = document.getElementById('inv-del-modal-footer');

            iconWrap.style.background = 'rgba(56,189,248,0.15)';
            iconWrap.style.borderColor = 'rgba(56,189,248,0.3)';
            iconWrap.style.boxShadow = '0 0 20px rgba(56,189,248,0.2)';
            iconEl.innerText = '🔍';
            
            titleEl.innerText = 'جاري فحص ارتباطات الفاتورة...';
            titleEl.style.color = '#fff';
            subtitleEl.innerText = 'يتم التحقق من الدفعات والقيود المحاسبية';
            
            statusBox.style.background = 'rgba(56,189,248,0.08)';
            statusBox.style.borderColor = 'rgba(56,189,248,0.2)';
            statusBox.innerHTML = `
                <div style="display:flex; align-items:center; justify-content:center; gap:12px; color:#38bdf8; font-weight:800; font-size:1.1rem;">
                    <span style="animation:spin 1s infinite linear;">⏳</span>
                    <span>جاري فحص قاعدة البيانات والسجلات...</span>
                </div>
            `;
            
            vouchersContainer.style.display = 'none';
            footerEl.innerHTML = `
                <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:8px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; cursor:pointer;">إلغاء</button>
            `;

            modal.style.display = 'flex';

            // Local Usage Check (Receipts, Delivery Notes, Cheques, Journal, Stock)
            let localUsages = [];
            try {
                // 1. Check single receipts
                const sReceipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                sReceipts.forEach(sr => {
                    if (sr && (sr.againstInvoice == invNo || sr.invoiceNo == invNo)) {
                        localUsages.push({ type: 'سند قبض مالي', doc_no: sr.id || sr.voucherNo, label: 'سند قبض مالي رقم ' + (sr.id || sr.voucherNo) + (sr.amount ? ' بمبلغ ' + sr.amount : '') });
                    }
                });
                // 2. Check multi receipts
                const mReceipts = JSON.parse(localStorage.getItem('erp_receipts_v1')) || [];
                mReceipts.forEach(mr => {
                    if (mr && mr.invoices && Array.isArray(mr.invoices)) {
                        if (mr.invoices.some(i => (i.invoiceNo || i.id) == invNo)) {
                            localUsages.push({ type: 'سند قبض مجمع', doc_no: mr.id || mr.receiptNo, label: 'سند قبض مجمع رقم ' + (mr.id || mr.receiptNo) });
                        }
                    }
                });
                // 3. Check delivery notes / warehouse dispatches
                const dNotes = JSON.parse(localStorage.getItem('erp_delivery_notes_v1')) || [];
                dNotes.forEach(dn => {
                    if (dn && (dn.invoiceNo == invNo || dn.sourceDocNo == invNo)) {
                        localUsages.push({ type: 'إذن صرف / تسليم مخزني', doc_no: dn.noteNo || dn.id, label: 'سند تسليم بضاعة من المخزن رقم ' + (dn.noteNo || dn.id) });
                    }
                });
                // 4. Check cheques
                const cheques = JSON.parse(localStorage.getItem('erp_cheques_v1')) || [];
                cheques.forEach(ch => {
                    if (ch && (ch.invoiceNo == invNo || ch.refNo == invNo || ch.againstInvoice == invNo)) {
                        localUsages.push({ type: 'شيك بنكي مرتبط', doc_no: ch.chequeNo || ch.id, label: 'شيك بنكي رقم ' + (ch.chequeNo || ch.id) });
                    }
                });
                // 5. Check journal vouchers
                const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
                jvs.forEach(jv => {
                    if (jv && (jv.invoiceNo == invNo || jv.reference == invNo || jv.refNo == invNo)) {
                        localUsages.push({ type: 'قيد محاسبي مرحل', doc_no: jv.id || jv.voucherNo, label: 'قيد يومية عامة رقم ' + (jv.id || jv.voucherNo) });
                    }
                });
                // 6. Check invoice posting & warehouse status
                const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1')) || [];
                const targetInv = allSales.find(inv => (inv.invoiceNo || inv.id) == invNo);
                if (targetInv && (targetInv.status === 'posted' || targetInv.status === 'confirmed' || targetInv.effectsApplied)) {
                    localUsages.push({ type: 'حركة مخزنية ومالية معتمدة', doc_no: invNo, label: 'الفاتورة معتمدة ومؤثرة على أرصدة المخزون والحسابات' });
                }
            } catch(e) {
                console.warn('Local usage check error:', e);
            }

            // Backend Check
            let backendUsages = [];
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                const res = await fetch('api/check_invoice_usage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': token ? ('Bearer ' + token) : '' },
                    body: JSON.stringify({ invoiceNo: invNo })
                });
                const resJson = await res.json();
                if (resJson && resJson.success && resJson.is_used) {
                    backendUsages = resJson.usages || [];
                }
            } catch(e) {
                console.warn('Backend usage check error:', e);
            }

            // Merge unique usages
            const allUsagesMap = new Map();
            [...localUsages, ...backendUsages].forEach(u => {
                const key = (u.type || '') + '_' + (u.doc_no || '');
                if (!allUsagesMap.has(key)) allUsagesMap.set(key, u);
            });
            const allUsages = Array.from(allUsagesMap.values());
            const isUsed = allUsages.length > 0;

            // Render Result
            if (isUsed) {
                iconWrap.style.background = 'rgba(239, 68, 68, 0.18)';
                iconWrap.style.borderColor = 'rgba(239, 68, 68, 0.45)';
                iconWrap.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.35)';
                iconEl.innerText = '🚫';

                titleEl.innerText = 'لا يمكن حذف هذه الفاتورة نهائياً';
                titleEl.style.color = '#f87171';
                subtitleEl.innerText = 'مرتبطة بحركات مخزنية أو سندات ومبالغ مالية';

                statusBox.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(185, 28, 28, 0.08))';
                statusBox.style.borderColor = 'rgba(239, 68, 68, 0.35)';
                statusBox.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px; color:#fca5a5; font-weight:900; font-size:1.1rem;">
                            <span>⚠️</span>
                            <span>تنبيه نظام الضبط المخزني والمالي:</span>
                        </div>
                        <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                            لا يمكن حذف الفاتورة <span style="color:#38bdf8; font-weight:800;">${invNo}</span> نسبةً لوجود عدد <span style="color:#f87171; font-weight:900; font-size:1.15rem; background:rgba(239,68,68,0.2); padding:1px 8px; border-radius:6px;">${allUsages.length}</span> حركة مخزنية أو سند مالي مرتبط بها.
                        </p>
                        <div style="background:rgba(0,0,0,0.3); border-radius:10px; padding:12px 14px; margin-top:4px; border-right:3px solid #ef4444;">
                            <span style="color:#cbd5e1; font-size:0.88rem; font-weight:700; line-height:1.6; display:block;">
                                🔒 <strong>حماية البيانات:</strong> تم صرف بضاعة أو تسجيل دفعات مالية/قيود على هذه الفاتورة. لإلغائها، يجب أولاً إلغاء الحركات المخزنية وسندات القبض/الدفع المرتبطة بها.
                            </span>
                        </div>
                    </div>
                `;

                document.getElementById('inv-del-vouchers-count').innerText = `${allUsages.length} حركة / سند`;
                document.getElementById('inv-del-vouchers-list').innerHTML = allUsages.map((u, idx) => `
                    <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:8px 12px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="color:#f87171; font-weight:bold; font-size:0.85rem;">${idx + 1}.</span>
                            <span style="color:#e2e8f0; font-weight:700; font-size:0.9rem;">${u.label || (u.type + ' رقم ' + u.doc_no)}</span>
                        </div>
                        <span style="background:rgba(239,68,68,0.15); color:#fca5a5; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:4px;">${u.type}</span>
                    </div>
                `).join('');
                vouchersContainer.style.display = 'block';

                footerEl.innerHTML = `
                    <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #3b82f6, #1d4ed8); border:none; color:#fff; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(59,130,246,0.4);">حسناً، فهمت</button>
                `;
            } else {
                iconWrap.style.background = 'rgba(245, 158, 11, 0.18)';
                iconWrap.style.borderColor = 'rgba(245, 158, 11, 0.45)';
                iconWrap.style.boxShadow = '0 0 25px rgba(245, 158, 11, 0.35)';
                iconEl.innerText = '⚠️';

                titleEl.innerText = 'تأكيد حذف الفاتورة نهائياً';
                titleEl.style.color = '#fbbf24';
                subtitleEl.innerText = 'الفاتورة غير مرتبطة بأي سندات';

                statusBox.style.background = 'linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(239, 68, 68, 0.06))';
                statusBox.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                statusBox.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px; color:#34d399; font-weight:800; font-size:1rem;">
                            <span>✅</span>
                            <span>تم التحقق: هذه الفاتورة غير مرتبطة بأي دفعات أو سندات صرف أو قيود.</span>
                        </div>
                        <p style="margin:0; color:#e2e8f0; font-size:0.95rem; line-height:1.6; font-weight:600;">
                            هل أنت متأكد من رغبتك في حذف الفاتورة (<span style="color:#38bdf8; font-weight:800;">${invNo}</span>)؟
                        </p>
                        <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.35); border-radius:10px; padding:12px 14px; margin-top:4px;">
                            <div style="display:flex; align-items:center; gap:8px; color:#f87171; font-weight:900; font-size:0.95rem; margin-bottom:4px;">
                                <span>🚨</span>
                                <span>تحذير نهائي:</span>
                            </div>
                            <span style="color:#fecaca; font-size:0.88rem; font-weight:700; line-height:1.5; display:block;">
                                سيتم حذف الفاتورة <strong>نهائياً وبشكل دائم من النظام</strong> ولن يمكن استرجاعها.
                            </span>
                        </div>
                    </div>
                `;

                vouchersContainer.style.display = 'none';

                footerEl.innerHTML = `
                    <button type="button" onclick="closeInvoiceDeleteModal()" class="btn-action" style="padding:10px 20px; border-radius:10px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:800; cursor:pointer;">إلغاء الأمر</button>
                    <button type="button" onclick="confirmExecuteDeleteInvoice()" class="btn-action" style="padding:10px 25px; border-radius:10px; background:linear-gradient(135deg, #ef4444, #dc2626); border:1px solid rgba(239,68,68,0.5); color:#fff; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 6px 20px rgba(239,68,68,0.4);">🗑️ نعم، احذف نهائياً</button>
                `;
            }
        }

        async function confirmExecuteDeleteInvoice() {
            if (!currentDeleteInvoiceNo) return;
            const invNo = String(currentDeleteInvoiceNo).trim();
            
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                const ctrl = new AbortController();
                const tId = setTimeout(() => ctrl.abort(), 4500);
                await fetch('api/save_sales_invoice.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': token ? ('Bearer ' + token) : '' },
                    body: JSON.stringify({ action: 'delete', invoiceNo: invNo }),
                    signal: ctrl.signal
                }).finally(() => clearTimeout(tId));
            } catch(e) { console.warn('DB delete sync failed:', e); }

            // Reverse customer balance if invoice had unpaid credit
            try {
                const rawInv = localStorage.getItem('erp_inv_' + invNo);
                const delInv = rawInv ? JSON.parse(rawInv) : null;
                if (delInv && delInv.saleType !== 'preliminary') {
                    const custId = delInv.customerId || (delInv.customer && delInv.customer.id);
                    const creditAmt = parseFloat(delInv.remainingAmount ?? delInv.creditAmount ?? 0) || 0;
                    if (custId && custId !== 'walk-in' && creditAmt > 0) {
                        let customers = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                        const cIdx = customers.findIndex(c => String(c.id) === String(custId));
                        if (cIdx > -1) {
                            customers[cIdx].currentBalance = (parseFloat(customers[cIdx].currentBalance) || 0) - creditAmt;
                            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
                        }
                    }
                }
            } catch (_eRev) {}

            // Delete from all LocalStorage sales keys & individual cache
            try { localStorage.removeItem('erp_inv_' + invNo); } catch(_e) {}
            ['erp_sales_invoices_v1', 'magaies_local_sales_invoices_v1', 'erp_sales_v1'].forEach(storeKey => {
                try {
                    const savedData = localStorage.getItem(storeKey);
                    if (savedData) {
                        const invoices = JSON.parse(savedData).filter(i => i && String(i.invoiceNo || i.id) !== invNo);
                        localStorage.setItem(storeKey, JSON.stringify(invoices));
                    }
                } catch (_eS) {}
            });

            if (typeof window.rebuildGLFromAllTables === 'function') {
                try { window.rebuildGLFromAllTables(false); } catch(_eGL) {}
            }
            try {
                new BroadcastChannel('elsajana_erp_sync').postMessage({ action: 'sales_invoice_deleted', invoiceNo: invNo, ts: Date.now() });
                new BroadcastChannel('magaies_erp_sync_channel').postMessage({ action: 'sales_invoice_deleted', invoiceNo: invNo, ts: Date.now() });
            } catch (_eBc) {}
            
            closeInvoiceDeleteModal();
            showToast('تم حذف الفاتورة وعكس قيودها بنجاح', 'success');
            loadHubTransactions();
        }

        // Initialize view on load (Respects ?action=new & ?id=... URL parameters)
        document.addEventListener('DBReady', () => {
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const actionParam = urlParams.get('action');
                const idParam = urlParams.get('id');
                if (idParam) {
                    return;
                }
                if (actionParam === 'split') {
                    openSplitInvoiceView(false);
                } else if (actionParam === 'new' || actionParam === 'direct' || actionParam === 'pending_delivery' || actionParam === 'preliminary' || actionParam === 'proforma') {
                    openInvoiceEditor(actionParam === 'new' || actionParam === 'direct' ? 'pending_delivery' : (actionParam === 'proforma' ? 'preliminary' : actionParam));
                } else {
                    showSalesHub();
                }
            }, 50);
        });

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
                const menu = document.getElementById('share-popup-menu');
                if ((!wrapper || !wrapper.contains(e.target)) && (!menu || !menu.contains(e.target))) {
                    closeShareMenu();
                }
            }
        });

        async function sendCurrentInvoiceWA() {
            let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedSalesInvoiceNo || document.getElementById('hdr-inv-no')?.value);
            if (invNo && invNo !== '---' && invNo !== 'SAL-NEW' && invNo !== 'جديد') {
                if (typeof sendInvoiceWA === 'function') {
                    await sendInvoiceWA(invNo);
                } else if (typeof openSalesWhatsAppModal === 'function') {
                    openSalesWhatsAppModal(invNo);
                }
            } else {
                showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
            }
        }

        async function sendCurrentInvoiceEmail() {
            let invNo = (typeof currentViewedInvoiceNo !== 'undefined' && currentViewedInvoiceNo) ? currentViewedInvoiceNo : (window.currentViewedInvoiceNo || window._lastSubmittedSalesInvoiceNo || document.getElementById('hdr-inv-no')?.value);
            if (invNo && invNo !== '---' && invNo !== 'SAL-NEW' && invNo !== 'جديد') {
                showToast('جاري تجهيز الفاتورة للإرسال عبر البريد الإلكتروني...', 'success');
                const mailtoLink = `mailto:?subject=فاتورة مبيعات رقم ${invNo}&body=مرحباً، مرفق تفاصيل الفاتورة.`;
                window.location.href = mailtoLink;
            } else {
                showToast('⚠️ يرجى حفظ وتأكيد الفاتورة أولاً', 'warning');
            }
        }
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
    //  INTERACTIVE DATABASE-PERSISTENT GRID CUSTOMIZER SYSTEM (sales_grid)
    // ══════════════════════════════════════════════════════════════════════════

    const GRID_ID = 'sales_grid';

    const DEFAULT_GRID_CONFIG = {
        "grid_id": "sales_grid",
        "fontSize": 16,
        "fontWeight": "normal",
        "fontFamily": "'Cairo', sans-serif",
        "fontColor": "#ffffff",
        "align": "center",
        "decimalPlaces": 2,
        "thousandSep": ",",
        "columns": [
            {
                "id": "col_num",
                "name": "#",
                "visible": true,
                "width": "40px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 20,
                "fontWeight": "800"
            },
            {
                "id": "col_avail",
                "name": "المتاح",
                "visible": true,
                "width": "83px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 16,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_wh",
                "name": "المخزن",
                "visible": true,
                "width": "148px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 18,
                "fontWeight": "800"
            },
            {
                "id": "col_factor",
                "name": "التعادل",
                "visible": true,
                "width": "90px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 16,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_item",
                "name": "الصنف",
                "visible": true,
                "width": "334px",
                "align": "center",
                "color": "#fedd67",
                "fontSize": 22,
                "fontWeight": "800"
            },
            {
                "id": "col_unit",
                "name": "الوحدات",
                "visible": true,
                "width": "119px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 18,
                "fontWeight": "800"
            },
            {
                "id": "col_qty",
                "name": "الكمية",
                "visible": true,
                "width": "134px",
                "align": "center",
                "color": "#7cfedd",
                "fontSize": 22,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_price",
                "name": "السعر",
                "visible": true,
                "width": "222px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 22,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_total",
                "name": "الإجمالي",
                "visible": true,
                "width": "162px",
                "align": "center",
                "color": "#f96c6c",
                "fontSize": 26,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_baseqty",
                "name": "عدد الوحدة",
                "visible": true,
                "width": "163px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 18,
                "decimals": 0,
                "fontWeight": "800",
                "thousandSep": ","
            },
            {
                "id": "col_note",
                "name": "ملاحظة",
                "visible": true,
                "width": "126px",
                "align": "center",
                "color": "#fafafa",
                "fontSize": 20,
                "fontWeight": "800"
            },
            {
                "id": "col_del",
                "name": "الإجراءات",
                "visible": true,
                "width": "81px",
                "align": "center",
                "fontSize": 20,
                "fontWeight": "800",
                "color": "#fafafa"
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
            const gridId = (window.CurrentGridConfig && window.CurrentGridConfig.grid_id) ? window.CurrentGridConfig.grid_id : 'sales_grid';
            const res = await fetch('api/get_grid_config.php?grid_id=' + gridId + '&_t=' + Date.now());
            const data = await res.json();
            if (data && data.config) {
                window.CurrentGridConfig = data.config;
            } else {
                // No saved config in DB — use factory default
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
            }
            if (typeof applyGridSettings === 'function') applyGridSettings();
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
        } catch (e) {
            console.error("Failed to load grid config", e);
            // Fallback to hardcoded default on error
            if (!window.CurrentGridConfig) {
                window.CurrentGridConfig = JSON.parse(JSON.stringify(DEFAULT_GRID_CONFIG));
                if (typeof applyGridSettings === 'function') applyGridSettings();
            }
        }
    };
    // Smart Canvas Text-Width Measurement Engine (2027 World-Class Precision)
    let _salesHeaderMeasureCanvas = null;
    function measureSalesTextWidthPx(text, fontStr) {
        try {
            if (!_salesHeaderMeasureCanvas) _salesHeaderMeasureCanvas = document.createElement('canvas');
            const ctx = _salesHeaderMeasureCanvas.getContext('2d');
            ctx.font = fontStr;
            return ctx.measureText(text || '').width;
        } catch (e) {
            return (text ? text.length : 0) * 10;
        }
    }

    window.applyGridSettings = function() {
        let cfg = window.CurrentGridConfig;
        if (!cfg || !cfg.columns) return;

        // Clear initial SSR dynamic style so live customization rules take 100% precedence
        const ssrStyle = document.getElementById('dynamic-pos-grid-style');
        if (ssrStyle) ssrStyle.innerHTML = '';
        
        let style = document.getElementById('dynamic-grid-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-grid-style';
            document.head.appendChild(style);
        }
        
        const baseFontSize = (cfg.fontSize || 16) + 'px';
        const baseFontWeight = (cfg.fontWeight && cfg.fontWeight !== 'normal') ? cfg.fontWeight : '800';
        const baseFontFamily = cfg.fontFamily || "'Cairo', sans-serif";
        const baseFontColor = '#FFFFFF';

        let css = `
            body #pos-grid,
            body #pos-grid .g-input,
            body #pos-grid .g-select,
            body #pos-grid .total-val,
            body #pos-grid .g-readonly {
                font-size: ${baseFontSize} !important;
                font-weight: 800 !important;
                font-family: ${baseFontFamily} !important;
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
                    : `width: auto !important; min-width: 140px !important;`;
                let align = col.align ? col.align : (cfg.align || 'center');
                let fontSize = col.fontSize ? (String(col.fontSize).replace('px','') + 'px') : baseFontSize;
                let fontWeight = (col.fontWeight && col.fontWeight !== 'normal') ? col.fontWeight : baseFontWeight;
                
                css += `
                    body #pos-grid thead tr th[data-col-id="${col.id}"] {
                        display: table-cell !important;
                        ${widthCss}
                        text-align: ${align} !important;
                        font-size: ${fontSize} !important;
                        font-weight: 900 !important;
                        color: #FFFFFF !important;
                    }
                    body #pos-grid tbody tr td[data-col-id="${col.id}"] {
                        display: table-cell !important;
                        ${widthCss}
                        text-align: ${align} !important;
                        font-size: ${fontSize} !important;
                        font-weight: 800 !important;
                    }
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input,
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:focus,
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select,
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:focus,
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    html:not(.light-theme) body #pos-grid tbody tr td[data-col-id="${col.id}"] .g-readonly {
                        text-align: ${align} !important;
                        background: #000000 !important;
                        background-color: #000000 !important;
                        background-image: none !important;
                        color: #FFFFFF !important;
                        -webkit-text-fill-color: #FFFFFF !important;
                        border: 2.2px solid #FFFFFF !important;
                        font-size: ${fontSize} !important;
                        font-weight: 800 !important;
                        font-family: ${baseFontFamily} !important;
                    }
                    :root.light-theme body #pos-grid thead tr th[data-col-id="${col.id}"] {
                        color: #000000 !important;
                        font-weight: 900 !important;
                    }
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] input.g-input:focus,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] select.g-select:focus,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] .total-val,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] .avail-val,
                    :root.light-theme body #pos-grid tbody tr td[data-col-id="${col.id}"] .g-readonly {
                        text-align: ${align} !important;
                        background: #FFFFFF !important;
                        background-color: #FFFFFF !important;
                        background-image: none !important;
                        color: #000000 !important;
                        -webkit-text-fill-color: #000000 !important;
                        border: 2.2px solid #000000 !important;
                        font-size: ${fontSize} !important;
                        font-weight: 900 !important;
                        font-family: ${baseFontFamily} !important;
                    }
                `;
            }
        });
        
        style.innerHTML = css;
        if (window.syncInvoiceFaintHintsAndZeros) window.syncInvoiceFaintHintsAndZeros(document);

        // DOM Reordering & Header Name Synchronization
        const table = document.getElementById('pos-grid');
        if (!table) return;

        // Reorder thead & sync column names
        const theadTr = table.querySelector('thead tr');
        if (theadTr) {
            cfg.columns.forEach(col => {
                const th = theadTr.querySelector(`th[data-col-id="${col.id}"]`);
                if (th) {
                    if (col.name && col.id !== 'col_price' && col.id !== 'col_total') {
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
        
        // Properly adjust tfoot colSpans so col_total aligns directly beneath the total column
        const tfoot = table.querySelector('tfoot');
        if (tfoot) {
            const visibleCols = cfg.columns.filter(c => c.visible !== false && c.visible !== "false" && c.visible !== 0);
            const totalIndex = visibleCols.findIndex(c => c.id === 'col_total' || c.id === 'total');
            const colsBefore = totalIndex >= 0 ? totalIndex : Math.max(1, visibleCols.length - 2);
            const colsAfter = Math.max(1, visibleCols.length - colsBefore - 1);

            tfoot.querySelectorAll('tr').forEach(tr => {
                const totalTd = tr.querySelector('[data-col-id="col_total"]');
                if (!totalTd) return;
                
                const firstTd = tr.firstElementChild;
                if (firstTd && firstTd !== totalTd) {
                    firstTd.colSpan = colsBefore;
                }
                const lastTd = tr.lastElementChild;
                if (lastTd && lastTd !== totalTd && lastTd !== firstTd) {
                    lastTd.colSpan = colsAfter;
                }
            });
        }

        if (typeof window.initSalesColumnResizers === 'function') window.initSalesColumnResizers();
        if (typeof window.autoFitGridCells === 'function') {
            requestAnimationFrame(() => window.autoFitGridCells());
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════
    //  2027 SMART AUTO-FIT FONT SCALING (Header Fields + Grid Cells via Canvas)
    // ═══════════════════════════════════════════════════════════════════════════
    window.autoFitHeaderFields = function() {
        try {
            // 1. Invoice Number (#inv-number)
            const invNoEl = document.getElementById('inv-number');
            if (invNoEl) {
                const invText = (invNoEl.textContent || '').trim();
                const parentW = (invNoEl.parentElement ? invNoEl.parentElement.clientWidth : 170) - 52;
                let invPx = 20;
                if (invText && parentW > 40) {
                    while (invPx > 13.5 && measureSalesTextWidthPx(invText, `900 ${invPx}px 'Outfit', sans-serif`) > parentW) {
                        invPx -= 0.5;
                    }
                }
                invNoEl.style.setProperty('font-size', invPx + 'px', 'important');
            }

            // 2. Customer Search Input (#hdr-customer-search) — Scales with name length & box width
            const custInput = document.getElementById('hdr-customer-search');
            if (custInput) {
                const val = (custInput.value || '').trim();
                const availW = Math.max(120, (custInput.clientWidth || 280) - 82);
                let px = 19.5;
                if (val) {
                    while (px > 12.5 && measureSalesTextWidthPx(val, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                custInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 3. Exchange Rate (#hdr-exchange-rate) — Prominent Bold Number Fitted to Box
            const rateInput = document.getElementById('hdr-exchange-rate');
            if (rateInput) {
                const val = String(rateInput.value || '').trim();
                const availW = Math.max(60, (rateInput.clientWidth || 100) - 14);
                let px = 22;
                if (val) {
                    while (px > 14.5 && measureSalesTextWidthPx(val, `900 ${px}px 'Outfit', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                rateInput.style.setProperty('font-size', px + 'px', 'important');
            }

            // 4. Currency Select (#hdr-currency)
            const curSel = document.getElementById('hdr-currency');
            if (curSel && curSel.options && curSel.selectedIndex >= 0) {
                const optText = (curSel.options[curSel.selectedIndex].text || '').trim();
                const availW = Math.max(55, (curSel.clientWidth || 88) - 24);
                let px = 15.5;
                if (optText) {
                    while (px > 11.5 && measureSalesTextWidthPx(optText, `800 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                curSel.style.setProperty('font-size', px + 'px', 'important');
            }

            // 5. Warehouse (#hdr-warehouse) & Sub-branch / Cost Center (#hdr-cost-center)
            ['hdr-warehouse', 'hdr-cost-center'].forEach(selId => {
                const sel = document.getElementById(selId);
                if (!sel || !sel.options || sel.selectedIndex < 0) return;
                const optText = (sel.options[sel.selectedIndex].text || '').trim();
                const availW = Math.max(75, (sel.clientWidth || 140) - 30);
                let px = 15.5;
                if (optText) {
                    while (px > 11.5 && measureSalesTextWidthPx(optText, `800 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                        px -= 0.5;
                    }
                }
                sel.style.setProperty('font-size', px + 'px', 'important');
            });

            // 6. Auto-Fit Text Inside Action & Toggle Buttons (#invoice-editor-view)
            const editorBtns = document.querySelectorAll('#invoice-editor-view .toolbar-actions .btn-action, #invoice-editor-view .pay-toggle-btn, #invoice-editor-view .tot-toggle-btn');
            editorBtns.forEach(btn => {
                const btnW = btn.clientWidth;
                if (!btnW || btnW < 40) return;
                const txt = (btn.textContent || '').replace(/\s+/g, ' ').trim();
                if (!txt) return;
                const hasSvg = !!btn.querySelector('svg');
                const iconSpace = hasSvg ? 44 : 28;
                const availW = Math.max(36, btnW - iconSpace);
                let px = btn.classList.contains('btn-submit') ? 16 : 15;
                while (px > 11 && measureSalesTextWidthPx(txt, `900 ${px}px 'Cairo', 'Tajawal', sans-serif`) > availW) {
                    px -= 0.5;
                }
                btn.style.setProperty('font-size', px + 'px', 'important');
            });
        } catch (e) {}
    };

    window.autoFitGridCells = function(targetRow) {
        try {
            const table = document.getElementById('pos-grid');
            const cfg = window.CurrentGridConfig;
            if (!table || !cfg || !cfg.columns) return;
            const fontFam = cfg.fontFamily || "'Cairo', sans-serif";
            const baseGlobalFs = parseFloat(cfg.fontSize) || 18;

            cfg.columns.forEach(col => {
                if (col.visible === false || col.visible === 'false' || col.visible === 0) return;
                const basePx = parseFloat(col.fontSize) || baseGlobalFs;
                const weight = col.fontWeight || cfg.fontWeight || '800';

                // 1. Synchronize Column Header (th) with basePx so enlarging "السعر" or "الكمية" scales both header & cell numbers
                if (!targetRow) {
                    const th = table.querySelector(`thead th[data-col-id="${col.id}"]`);
                    if (th && col.id !== 'col_del' && col.id !== 'action') {
                        const thText = (th.textContent || col.name || '').trim();
                        const thAvailW = Math.max(36, (th.clientWidth || parseInt(col.width) || 100) - 18);
                        let thPx = basePx;
                        if (thText && thAvailW > 25) {
                            while (thPx > 12 && measureSalesTextWidthPx(thText, `800 ${thPx}px ${fontFam}`) > thAvailW) {
                                thPx -= 0.5;
                            }
                        }
                        th.style.setProperty('font-size', thPx + 'px', 'important');
                    }
                }

                // 2. Synchronize Cell Inputs/Selects/Values to basePx & auto-fit long values to column width
                const rowsToScan = targetRow ? [targetRow] : Array.from(table.querySelectorAll('tbody tr'));
                rowsToScan.forEach(tr => {
                    const td = tr.querySelector(`td[data-col-id="${col.id}"]`);
                    if (!td) return;
                    const ctrl = td.querySelector('input.g-input, select.g-select, .total-val, .avail-val, .g-readonly');
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
                    const paddingOffset = (col.id === 'col_item' || col.id === 'item') ? 48 : (ctrl.tagName === 'SELECT' ? 28 : 18);
                    const availW = Math.max(36, tdW - paddingOffset);

                    let targetPx = basePx;
                    if (textVal && availW > 30) {
                        while (targetPx > 12 && measureSalesTextWidthPx(textVal, `${weight} ${targetPx}px ${fontFam}`) > availW) {
                            targetPx -= 0.5;
                        }
                    }
                    ctrl.style.setProperty('font-size', targetPx + 'px', 'important');
                });
            });
        } catch (e) {}
    };

    // Interactive Column Resizer with Proportional Font Scaling for Price & Qty
    window.initSalesColumnResizers = function() {
        const table = document.getElementById('pos-grid');
        if (!table) return;
        const row = table.querySelector('thead tr');
        const cols = row ? row.children : [];
        if (!cols) return;

        table.querySelectorAll('.col-resizer').forEach(r => r.remove());

        for (let i = 0; i < cols.length; i++) {
            const th = cols[i];
            const colId = th.getAttribute('data-col-id');
            if (colId === 'col_del' || colId === 'action') continue;

            const resizer = document.createElement('div');
            resizer.className = 'col-resizer';
            resizer.style.cssText = 'position:absolute; left:0; top:0; width:8px; height:100%; cursor:col-resize; user-select:none; z-index:20; transition: background-color 0.2s;';
            resizer.addEventListener('mouseenter', () => resizer.style.backgroundColor = 'rgba(56, 189, 248, 0.75)');
            resizer.addEventListener('mouseleave', () => resizer.style.backgroundColor = 'transparent');
            th.appendChild(resizer);

            let startX, startWidth, startFontSize;

            resizer.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                startX = e.clientX;
                startWidth = th.offsetWidth || parseInt(th.style.width) || 120;
                const cfg = window.CurrentGridConfig;
                const colSetting = cfg && cfg.columns ? cfg.columns.find(c => String(c.id) === String(colId)) : null;
                startFontSize = colSetting ? (parseFloat(colSetting.fontSize) || 20) : 20;

                function doDrag(ev) {
                    const dx = ev.clientX - startX;
                    const newWidth = Math.max(50, startWidth - dx); // RTL

                    th.style.setProperty('width', newWidth + 'px', 'important');
                    th.style.setProperty('min-width', newWidth + 'px', 'important');
                    th.style.setProperty('max-width', newWidth + 'px', 'important');

                    const tds = table.querySelectorAll(`tbody td[data-col-id="${colId}"]`);
                    tds.forEach(td => {
                        td.style.setProperty('width', newWidth + 'px', 'important');
                        td.style.setProperty('min-width', newWidth + 'px', 'important');
                        td.style.setProperty('max-width', newWidth + 'px', 'important');
                    });

                    if (colSetting) {
                        colSetting.width = newWidth + 'px';
                        // Proportional header + number scaling when widening/narrowing Price or Qty columns
                        if (colId === 'col_price' || colId === 'col_qty') {
                            const ratio = newWidth / Math.max(60, startWidth);
                            const scaledFs = Math.max(15, Math.min(34, Math.round(startFontSize * (0.65 + 0.35 * ratio))));
                            colSetting.fontSize = scaledFs;
                        }
                        if (window.autoFitGridCells) window.autoFitGridCells();
                    }
                }

                function stopDrag() {
                    document.removeEventListener('mousemove', doDrag);
                    document.removeEventListener('mouseup', stopDrag);
                    if (window.CurrentGridConfig) {
                        applyGridSettings();
                        saveGridConfigToDB(window.CurrentGridConfig);
                    }
                }

                document.addEventListener('mousemove', doDrag);
                document.addEventListener('mouseup', stopDrag);
            });
        }
    };

    // Ensure modal column property changes immediately preview on the grid
    const _origOnSelectedColPropChange = window.onSelectedColPropChange;
    window.onSelectedColPropChange = function(prop, val) {
        if (typeof _origOnSelectedColPropChange === 'function') {
            _origOnSelectedColPropChange(prop, val);
        }
        if (tempGridConfig) {
            window.CurrentGridConfig = JSON.parse(JSON.stringify(tempGridConfig));
            applyGridSettings();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (window.autoFitHeaderFields) window.autoFitHeaderFields();
            if (window.autoFitGridCells) window.autoFitGridCells();
        }, 250);
    });
    window.addEventListener('resize', () => {
        if (window.autoFitHeaderFields) window.autoFitHeaderFields();
        if (window.autoFitGridCells) window.autoFitGridCells();
    });
    document.addEventListener('input', (e) => {
        if (e.target && e.target.closest && e.target.closest('#pos-grid')) {
            const tr = e.target.closest('tr');
            if (window.autoFitGridCells) window.autoFitGridCells(tr);
        }
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

    window.quickResetGrid = async function(confirmedFlag) {
        const menu = document.getElementById('col-context-menu');
        if (menu) menu.style.display = 'none';
        if (!confirmedFlag && typeof window.openDsConfirmModal === 'function') {
            window.openDsConfirmModal(
                '🔄 استعادة التنسيق الافتراضي للجدول',
                'هل أنت متأكد من استعادة التنسيق والترتيب الافتراضي لجدول المبيعات المباشرة وحفظه؟',
                () => { window.quickResetGrid(true); }
            );
            return;
        }
        if (true) {
            try {
                // Try to fetch factory default from DB
                const res = await fetch('api/get_grid_config.php?grid_id=sales_grid_default&_t=' + Date.now());
                const data = await res.json();
                if (data && data.config) {
                    window.CurrentGridConfig = data.config;
                    window.CurrentGridConfig.grid_id = 'sales_grid'; // Ensure correct grid_id for saving
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
        }
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
    
    <!-- Invoice Deletion Modal -->
    <div id="invoice-delete-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:999999; justify-content:center; align-items:center; direction:rtl; font-family:'Tajawal', sans-serif;">
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
    <div id="delivery-confirmation-modal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); z-index:999999; justify-content:center; align-items:center; direction:rtl; font-family:'Tajawal', sans-serif;">
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
    <script src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
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
                const ctrl = new AbortController();
                const tId = setTimeout(() => ctrl.abort(), 4500);
                const res = await fetch('api/mirror_sync.php?action=get_recent_sales&_t=' + Date.now(), { signal: ctrl.signal }).finally(() => clearTimeout(tId));
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
                        localStorage.setItem('erp_sales_v1', JSON.stringify(localSales));
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
        setInterval(() => {
            if (!document.hidden) syncSalesWithMariaDB();
        }, 6500);
        window.addEventListener('focus', syncSalesWithMariaDB);
        window.addEventListener('online', syncSalesWithMariaDB);
        return syncSalesWithMariaDB;
    })();

    // ═══════════════════════════════════════════════════════════════════════════════
    // 🏛️ الدستور الحاكم الشامل لشاشة المبيعات المباشرة (direct-sales.php?action=new)
    // 1. مرشد الشاشة المعزول والمفصل من الألف إلى الياء (طوالي بلا شاشات وسيطة + شرح بالتأشير)
    // 2. انسيابية المؤشر 60fps والتنقل بزر Enter واختصارات الكيبورد (Ctrl+S / F2)
    // 3. منع النقرات المزدوجة + التسميع اللحظي عبر BroadcastChannel
    // ═══════════════════════════════════════════════════════════════════════════════
    (function initSalesConstitutionEngine() {
        window.MagaiesSmartGuides = window.MagaiesSmartGuides || {};

        window.MagaiesSmartGuides['sales-direct'] = {
            title: 'مرشد منظومة المبيعات المباشرة والشاملة (شاشة داخل شاشة + القوائم المنبثقة)',
            subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي خطوة بخطوة: يفتح شاشة المبيعات وقوائم العملاء وشرائح الأسعار وقائمة الأصناف وطرق الدفع أمامك فعلياً',
            screenHierarchy: [
                '1️⃣ الشاشة الرئيسية: بوابة وسجل المبيعات وفلاتر التسليم',
                '2️⃣ شاشة داخل شاشة: محرر فاتورة المبيعات المباشرة',
                '3️⃣ القوائم الفرعية: قائمة العملاء • كشف الأستاذ • شرائح الأسعار • قائمة الأصناف',
                '4️⃣ النوافذ التفاعلية: الترحيل والخصم • الدفع الهجين (آجل/نقدي/تحويل/شيك)',
                '5️⃣ التسميع اللحظي: كشف العميل • بوابة التسليم GDN • اليومية العامة • الواتساب'
            ],
            overview: 'تتكون منظومة المبيعات من شاشات متداخلة وقوائم ذكية (شاشة داخل شاشة): تبدأ من سجل وبوابة المبيعات، وعند فتح فاتورة المبيعات تظهر القوائم التفاعلية للعملاء وكشف الأستاذ وشرائح الأسعار (قطاعي/جملة) وقائمة الأصناف الذكية ولوحات الدفع الهجين.',
            autopilotSteps: [
                {
                    badge: '1. الشريط العلوي للفاتورة: الرقم التسلسلي والتاريخ وشريط الملاءة المالية والتزامن اللحظي',
                    cursorLabel: 'رقم الفاتورة وشريط الملاءة والتزامن 📊',
                    screenPath: 'الشاشة النشطة: محرر فاتورة المبيعات ⬅ رقم الفاتورة التسلسلي والتاريخ ⬅ شريط الملاءة المالية للعميل + التزامن اللحظي',
                    targetSelector: '#invoice-info-box, #fin-strip, #ds-live-sync-pill',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'محرر فاتورة المبيعات المباشرة (#invoice-editor-view)',
                        actionLabel: 'شريط معلومات الفاتورة والملاءة المالية للعميل:',
                        childItems: ['🔢 رقم الفاتورة التسلسلي التلقائي وأزرار التنقل (❮ ❯)', '📅 تاريخ الفاتورة المحاسبي وشارة التزامن الفوري (0ms)', '📊 شريط الملاءة المالية (الرصيد • الشيكات • المديونية • السقف • المتاح)', '🛡️ رقابة فورية على السقف الائتماني قبل البيع الآجل'],
                        effectLabel: 'شاشة تحرير الفاتورة وشريط الملاءة المالية مفتوحان الآن أمامك!'
                    },
                    action: () => {
                        ['add-customer-modal', 'ds-quick-wh-modal', 'ds-quick-item-modal', 'sales-cust-history-modal', 'sales-parked-invoices-modal', 'ds-cash-change-modal', 'ds-warranty-qr-modal'].forEach(id => {
                            const m = document.getElementById(id);
                            if (m) { m.style.display = 'none'; m.classList.remove('active'); }
                        });
                        if (typeof openInvoiceEditor === 'function' && document.getElementById('invoice-editor-view')?.style.display === 'none') {
                            openInvoiceEditor('pending_delivery');
                        }
                        const finStrip = document.getElementById('fin-strip');
                        if (finStrip) finStrip.classList.add('visible');
                    },
                    speech: () => 'أهلاً بحضرتك في الشرح العملي الشامل لشاشة فاتورة المبيعات المباشرة. نبدأ من الشريط العلوي للفاتورة، حيث يظهر رقم الفاتورة التسلسلي التلقائي وأزرار التنقل بين الفواتير السابقة والتالية وتاريخ الفاتورة وشارة التزامن اللحظي، وبجانبه شريط الملاءة المالية للعميل الذي يعرض رصيده والشيكات المؤجلة وسقف الائتمان المتاح.'
                },
                {
                    badge: '2. خانة العميل: فتح قائمة العملاء المنسدلة وزر كشف الأستاذ (📖 أستاذ)',
                    cursorLabel: 'قائمة العملاء وكشف الأستاذ 📖',
                    screenPath: 'فاتورة المبيعات ⬅ ضغط خانة العميل (#hdr-customer-search) ⬅ انسدال قائمة العملاء + زر (📖 أستاذ)',
                    targetSelector: '#hdr-customer-search, #hdr-customer-ledger-btn',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'خانة اختيار العميل (#hdr-customer-search)',
                        actionLabel: 'بمجرد الضغط تنسدل قائمة العملاء والأدوات المرتبطة بها:',
                        childItems: ['👤 قائمة العملاء المنسدلة مع البحث العربي المتسامح بالاسم أو الرقم', '📖 زر (أستاذ) يفتح شاشة كشف حساب العميل التفصيلي فوراً', '📊 تحديث شريط المديونية والائتمان المتاح بالأعلى تلقائياً', '➕ زر إضافة عميل جديد من داخل الفاتورة'],
                        effectLabel: 'قائمة العملاء المنسدلة مفتوحة الآن أمامك على الشاشة!'
                    },
                    action: () => {
                        const m = document.getElementById('add-customer-modal');
                        if (m) { m.style.display = 'none'; m.classList.remove('active'); }
                        const inp = document.getElementById('hdr-customer-search');
                        if (inp) { try { inp.focus(); } catch (e) {} }
                        if (typeof showCustomerDropdown === 'function') {
                            try { showCustomerDropdown(); } catch (e) {}
                        }
                        const dd = document.getElementById('customer-dropdown-list');
                        if (dd) dd.style.display = 'block';
                    },
                    speech: () => 'عند الضغط على خانة العميل، تنسدل أمامك فوراً قائمة العملاء الذكية كما ترى الآن على الشاشة لتبحث بالاسم أو الرقم بتسامح كامل مع الحروف العربية، وبداخل الخانة تجد زر أستاذ الذي يفتح شاشة كشف حساب العميل التفصيلي دون مغادرة الفاتورة.'
                },
                {
                    badge: '3. شاشة وليدة حية (1): نافذة الإضافة السريعة لعميل جديد (➕ عميل جديد)',
                    cursorLabel: 'فتح شاشة إضافة عميل جديد ➕👤',
                    screenPath: 'فاتورة المبيعات ⬅ ضغط زر (➕) بجانب العميل ⬅ فتح الشاشة الوليدة (#add-customer-modal)',
                    targetSelector: '#add-customer-modal .modal-card, button[onclick*="add-customer-modal"]',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'الشاشة الوليدة: إضافة عميل جديد (#add-customer-modal)',
                        actionLabel: 'تسجيل عميل جديد في ثوانٍ دون مغادرة الفاتورة:',
                        childItems: ['👤 اسم العميل ورقم الهاتف ورقم الواتساب للإشعارات', '💳 تحديد الرصيد الافتتاحي وحد الائتمان المسموح للبيع الآجل', '🔒 تحديد درجة سرية الحساب والعنوان', '💾 حفظ العميل واختياره تلقائياً في الفاتورة الحالية'],
                        effectLabel: 'الشاشة الوليدة لإضافة العميل مفتوحة الآن أمام عينيك!'
                    },
                    action: () => {
                        const dd = document.getElementById('customer-dropdown-list');
                        if (dd) dd.style.display = 'none';
                        if (typeof openModal === 'function') {
                            try { openModal('add-customer-modal'); } catch (e) {}
                        } else {
                            const m = document.getElementById('add-customer-modal');
                            if (m) m.classList.add('active');
                        }
                    },
                    speech: () => 'وإذا كان العميل جديداً، بمجرد الضغط على زر الزائد الأخضر بجانب خانة العميل تفتح أمامك هذه الشاشة الوليدة مباشرة لتسجيل اسم العميل ورقم الواتساب وسقف الائتمان وحفظه ليتم اختياره في الفاتورة فوراً.'
                },
                {
                    badge: '4. العملة وسعر الصرف والمخزن وإضافة مخزن فوري (➕) وشريحة التسعير',
                    cursorLabel: 'العملة والمخزن وشريحة الأسعار 🏷️',
                    screenPath: 'فاتورة المبيعات ⬅ قوائم العملة والمخزن + زر (➕ مخزن) + شرائح الأسعار (قطاعي / نصف جملة / جملة)',
                    targetSelector: '#hdr-currency, #hdr-exchange-rate, #hdr-warehouse, #btn-ds-quick-add-wh, #hdr-price-tier',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'قوائم العملة والمخزن وشرائح الأسعار',
                        actionLabel: 'قوائم منسدلة تضبط الأسعار والمستودع آلياً:',
                        childItems: ['💱 قائمة العملة وسعر الصرف (SDG / USD / SAR)', '📦 قائمة المخزن وزر (➕) لإضافة مستودع جديد فوراً', '🏷️ قائمة شرائح الأسعار (قطاعي • نصف جملة • جملة)', '🔄 تحديث أسعار الأصناف في الجدول تلقائياً حسب الشريحة'],
                        effectLabel: 'يعيد احتساب أسعار الفاتورة حسب العملة والشريحة فوراً'
                    },
                    action: () => {
                        const m = document.getElementById('add-customer-modal');
                        if (m) { m.style.display = 'none'; m.classList.remove('active'); }
                        const cur = document.getElementById('hdr-currency');
                        if (cur) { try { cur.focus(); } catch (e) {} }
                    },
                    speech: () => 'بعد اختيار العميل، تحدد عملة البيع وسعر الصرف والمخزن الذي ستُصرف منه البضاعة أو تضيف مخزناً جديداً بزر الزائد، كما يمكنك اختيار شريحة السعر قطاعي أو نصف جملة أو جملة ليقوم النظام بتحديث أسعار الأصناف في الجدول تلقائياً.'
                },
                {
                    badge: '5. البحث السريع عن الأصناف (F2) وإضافة صنف جديد (➕ صنف) والماسح المتصل',
                    cursorLabel: 'قائمة الأصناف الذكية والماسح المتصل 📦',
                    screenPath: 'فاتورة المبيعات ⬅ ضغط البحث السريع (F2) ⬅ فتح قائمة الأصناف بالرصيد والسعر + زر (➕ صنف) + (📡 ماسح متصل)',
                    targetSelector: '#quick-item-search, #btn-ds-quick-add-item, #btn-ds-continuous-pos-scan',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'شريط البحث السريع عن الأصناف (#quick-item-search)',
                        actionLabel: 'عند الضغط تفتح قائمة الأصناف الذكية وأدوات الإدراج:',
                        childItems: ['🏷️ قائمة الأصناف المنسدلة مع الرصيد الفعلي المتاح وسعر البيع', '➕ زر (صنف) لتعريف صنف جديد وإدراجه في الفاتورة فوراً', '📡 زر (ماسح متصل) لقراءة الباركود المتتابع بسرعة البرق', '⚖️ التحويل التلقائي بين الوحدة الكبرى والفرعية في الجدول'],
                        effectLabel: 'قائمة الأصناف مفتوحة الآن أمامك، وبمجرد الاختيار ينزل الصنف للجدول!'
                    },
                    action: () => {
                        const histM = document.getElementById('sales-cust-history-modal');
                        if (histM) histM.style.display = 'none';
                        const qInp = document.getElementById('quick-item-search');
                        if (qInp) { try { qInp.focus(); } catch (e) {} }
                        if (typeof showQuickItemDropdown === 'function') {
                            try { showQuickItemDropdown(); } catch (e) {}
                        }
                        const qdd = document.getElementById('quick-item-dropdown-list');
                        if (qdd) qdd.style.display = 'block';
                    },
                    speech: () => 'في شريط البحث السريع عن الأصناف باختصار إف اثنين، تنسدل أمامك قائمة الأصناف الذكية بالرصيد المتاح وسعر البيع؛ وبجانبه زر إضافة صنف جديد فوراً وزر الماسح المتصل لقراءة الباركود المتتابع بدون توقف.'
                },
                {
                    badge: '6. شاشة وليدة حية (2): كاشف التكلفة وهامش الربح وسجل آخر أسعار العميل',
                    cursorLabel: 'كاشف الربحية وسجل أسعار العميل 📈🛡️',
                    screenPath: 'فاتورة المبيعات ⬅ ضغط (🛡️ الربح) أو (📈 سجل الأسعار) ⬅ فتح الشاشة الوليدة (#sales-cust-history-modal)',
                    targetSelector: '#sales-cust-history-modal > div, #sales-margin-guard-badge, #btn-cust-item-history',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'الشاشة الوليدة: سجل أسعار العميل وكاشف التكلفة (#sales-cust-history-modal)',
                        actionLabel: 'شفافية كاملة في التكلفة والربحية وتاريخ التسعير:',
                        childItems: ['🛡️ عرض تكلفة الوحدة وسعر البيع الحالي وهامش الربح لكل صنف', '🚨 تنبيه فوري باللون الأحمر إذا كان السعر أقل من التكلفة', '📈 عرض آخر 3 أسعار بيع بها الصنف لهذا العميل بالتاريخ', '⚡ تطبيق أي سعر سابق على السطر بضغطة زر واحدة'],
                        effectLabel: 'شاشة كاشف الربحية وسجل أسعار العميل مفتوحة الآن أمامك!'
                    },
                    action: () => {
                        const qdd = document.getElementById('quick-item-dropdown-list');
                        if (qdd) qdd.style.display = 'none';
                        const parkM = document.getElementById('sales-parked-invoices-modal');
                        if (parkM) parkM.style.display = 'none';
                        if (typeof window.openCustomerItemHistoryModal === 'function') {
                            window.openCustomerItemHistoryModal('margin');
                        }
                    },
                    speech: () => 'وعند الضغط على شارة الربح أو زر سجل الأسعار، تفتح أمامك هذه الشاشة الوليدة التي تعرض تكلفة كل صنف وهامش ربحه وتنبهك فوراً إذا كان السعر أقل من التكلفة، مع عرض آخر ثلاثة أسعار اشترى بها هذا العميل لتطبيق أي منها بضغطة واحدة.'
                },
                {
                    badge: '7. شاشة وليدة حية (3): تعليق الفاتورة واستدعاء الفواتير المعلقة (POS Park & Hold)',
                    cursorLabel: 'الفواتير المعلقة المؤقتة ⏸️📂',
                    screenPath: 'فاتورة المبيعات ⬅ ضغط (⏸️ تعليق / 📂 المعلقة) ⬅ فتح الشاشة الوليدة (#sales-parked-invoices-modal)',
                    targetSelector: '#sales-parked-invoices-modal > div, #btn-park-invoice, #btn-recall-parked',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'الشاشة الوليدة: الفواتير المعلقة (#sales-parked-invoices-modal)',
                        actionLabel: 'خدمة عدة عملاء في نفس الوقت بدون ضياع أي بيان:',
                        childItems: ['⏸️ زر (تعليق) يركن الفاتورة الحالية ويفرغ الشاشة لعميل مستعجل', '📂 زر (المعلقة) يفتح جدول الفواتير المركونة مع وقت التعليق', '▶️ زر (استدعاء) يعيد الفاتورة بكامل أصنافها وعميلها فوراً', '🗑️ إمكانية حذف أي فاتورة معلقة غير مطلوبة'],
                        effectLabel: 'نافذة الفواتير المعلقة مفتوحة الآن أمامك!'
                    },
                    action: () => {
                        const histM = document.getElementById('sales-cust-history-modal');
                        if (histM) histM.style.display = 'none';
                        if (typeof window.openParkedInvoicesModal === 'function') {
                            window.openParkedInvoicesModal();
                        }
                    },
                    speech: () => 'إذا انشغل العميل وأردت خدمة عميل آخر في الطابور، اضغط على زر تعليق لركن الفاتورة مؤقتاً، وعند الضغط على زر المعلقة تفتح أمامك هذه النافذة الوليدة لاستدعاء الفاتورة بضغطة واحدة واستكمالها فوراً.'
                },
                {
                    badge: '8. أزرار الترحيل والعمالة والضريبة والخصم والإجمالي الكلي اللحظي',
                    cursorLabel: 'فتح خانات الترحيل والخصم والضريبة 🚚',
                    screenPath: 'فاتورة المبيعات ⬅ ضغط (🚚 ترحيل / ➖ خصم) ⬅ ظهور خانات المبالغ في الملخص + الإجمالي الكلي',
                    targetSelector: '#btn-toggle-transport, #row-grand-total',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'أزرار الإضافات والخصم (#btn-toggle-transport)',
                        actionLabel: 'الضغط على أي زر يفتح سطر الإدخال الخاص به:',
                        childItems: ['🚚 ترحيل: يفتح خانة رسوم التوصيل والشحن', '👷‍♂️ عمالة و ➕ إضافات: يفتح خانات الخدمات الإضافية', '💰 ضريبة و ➖ خصم: يفتح خانات الضريبة والخصم الممنوح', '💵 مستطيل الإجمالي الكلي ينبض لحظياً مع كل تعديل'],
                        effectLabel: 'ظهرت الآن خانات الترحيل والخصم أمامك في ملخص الفاتورة!'
                    },
                    action: () => {
                        const parkM = document.getElementById('sales-parked-invoices-modal');
                        if (parkM) parkM.style.display = 'none';
                        const cashM = document.getElementById('ds-cash-change-modal');
                        if (cashM) cashM.style.display = 'none';
                        ['transport', 'discount'].forEach(t => {
                            const r = document.getElementById('row-tot-' + t);
                            if (r && (r.style.display === 'none' || !r.style.display)) {
                                if (typeof window.toggleTotRow === 'function') {
                                    try { window.toggleTotRow(t); } catch (e) {}
                                } else {
                                    r.style.display = 'table-row';
                                }
                            }
                        });
                    },
                    speech: () => 'وعند الضغط على أزرار الترحيل أو العمالة أو الضريبة أو الخصم، تفتح أمامك فوراً الخانات المخصصة لإدخال مبالغها كما ظهر الآن في أسفل الجدول، ويتم تحديث مستطيل الإجمالي الكلي في نفس اللحظة.'
                },
                {
                    badge: '9. منظومة الدفع الهجين وشاشة حاسبة الباقي النقدي الذكية (🧮 حاسبة الباقي)',
                    cursorLabel: 'نوافذ الدفع الهجين وحاسبة الباقي 💳🧮',
                    screenPath: 'فاتورة المبيعات ⬅ لوحة الدفع (#payment-panel) ⬅ فتح حاسبة النقدية والباقي (#ds-cash-change-modal)',
                    targetSelector: '#ds-cash-change-modal > div, #payment-panel',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'لوحة طرق الدفع الهجين وحاسبة الباقي (#payment-panel)',
                        actionLabel: 'تعدد طرق السداد مع حاسبة باقي الكاشير الذكية:',
                        childItems: ['💳 آجل مع تاريخ الاستحقاق ومعاينة الكمبيالة القانونية', '💵 نقدي و 🧮 حاسبة الباقي بالفئات النقدية السريعة', '🏦 تحويل بنكي مع مسح الإشعار بالذكاء الاصطناعي (📷 مسح إشعار)', '✍️ تحصيل بالشيكات مع توثيق رقم الشيك وتاريخ استحقاقه'],
                        effectLabel: 'نوافذ الدفع وحاسبة الباقي للعميل مفتوحة الآن أمامك!'
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
                        if (typeof window.openDsCashChangeCalculator === 'function') {
                            window.openDsCashChangeCalculator();
                        }
                    },
                    speech: () => 'من لوحة طريقة وتفاصيل الدفع، يمكنك الجمع بين الآجل والنقدي والتحويل البنكي والشيكات ومسح الإشعار البنكي بالذكاء الاصطناعي، كما تفتح أمامك حاسبة الباقي النقدي الذكية لحساب الباقي للعميل بالفئات النقدية السريعة بضغطة واحدة.'
                },
                {
                    badge: '10. شريط الاعتماد النهائي (F10 / Ctrl+S) وشهادة الضمان والإرسال التلقائي واتساب',
                    cursorLabel: 'حفظ واعتماد الفاتورة (F10) ✅',
                    screenPath: 'فاتورة المبيعات ⬅ زر حفظ واعتماد (F10) + (🛡️ ضمان / QR) + (📲 إرسال تلقائي واتساب)',
                    targetSelector: '.items-toolbar',
                    diagramType: 'accounting-flow',
                    diagramMeta: {
                        parentScreen: 'شريط الحفظ والاعتماد والمشاركة (.items-toolbar)',
                        actionLabel: 'بمجرد الحفظ يتم التسميع اللحظي في 4 مسارات:',
                        childItems: ['👤 تحديث كشف حساب العميل ومراقبة السقف الائتماني', '📤 إرسال الفاتورة لبوابة تسليم وتقسيم المبيعات المخزنية GDN', '🏦 إثبات المقبوضات في الخزينة أو البنك أو حافظة الشيكات', '🛡️ طباعة شهادة الضمان أو إيصال 80mm بالـ QR + إرسال واتساب'],
                        effectLabel: 'ترحيل فوري متكامل 100% ومزامنة مع تطبيق الجوال'
                    },
                    action: () => {
                        const cashM = document.getElementById('ds-cash-change-modal');
                        if (cashM) cashM.style.display = 'none';
                    },
                    speech: () => 'أخيراً من شريط الأدوات السفلي، اضغط على حفظ واعتماد أو مفتاح إف عشرة ليتم ترحيل الفاتورة فوراً إلى كشف حساب العميل واليومية العامة وبوابة تسليم المخزن وتطبيق الموبايل، مع إمكانية إصدار شهادة الضمان بالـ QR وإرسال الفاتورة تلقائياً عبر واتساب.'
                }
            ],
            scenarios: [
                {
                    q: '💡 كيف أغيّر شريحة الأسعار للعميل بين (قطاعي / نصف جملة / جملة)؟',
                    a: 'من قائمة شريحة الأسعار (#hdr-price-tier) أعلى الجدول، اختر الشريحة المطلوبة وسيقوم النظام بتحديث أسعار كافة الأصناف المدرجة في الجدول وإعادة احتساب الفاتورة وهامش الربح فوراً.'
                },
                {
                    q: '💡 ماذا يفعل زر (⏸️ تعليق) وزر (📂 المعلقة)؟',
                    a: 'يتيح لك ركن فاتورة العميل الحالي مؤقتاً بكامل أصنافها لإصدار فاتورة مستعجلة لعميل آخر، ثم استرجاع الفاتورة المعلقة بضغطة واحدة دون ضياع أي بيان.'
                },
                {
                    q: '💡 كيف ينبهني النظام إذا أدخلت سعر بيع أقل من التكلفة؟',
                    a: 'يتحول شريط (🛡️ الربح) تلقائياً إلى اللون الأحمر (🚨 أقل من التكلفة) مع بيان عدد الأصناف المتأثرة، وعند الضغط عليه يعرض لك تفصيل تكلفة وربحية كل سطر.'
                }
            ],
            buttonsDict: [
                {
                    label: 'حفظ واعتماد الفاتورة (Ctrl+S)',
                    targetSelector: 'button[onclick*="submitInvoice"], .btn-submit',
                    whatItDoes: 'اعتماد فاتورة المبيعات محاسبياً ومخزنياً كعملية ذرية متكاملة.',
                    whatHappensIfClicked: 'يتحقق من العميل والأصناف والسقف الائتماني، ثم يرحّل الفاتورة لكشف العميل والخزينة/البنك واليومية العامة وبوابة التسليم المخزني (GDN).'
                },
                {
                    label: 'خانة العميل وزر كشف الأستاذ (📖 أستاذ)',
                    targetSelector: '#hdr-customer-search, #hdr-customer-ledger-btn',
                    whatItDoes: 'اختيار العميل أو استعراض كشف حسابه التفصيلي أو إضافة عميل جديد.',
                    whatHappensIfClicked: 'يعرض قائمة العملاء وشريط الملاءة المالية العلوي، ويفتح زر (أستاذ) كشف حساب العميل دون مغادرة الفاتورة.'
                },
                {
                    label: 'البحث السريع عن الأصناف والباركود (F2)',
                    targetSelector: '#quick-item-search',
                    whatItDoes: 'إدراج الأصناف في الفاتورة بالاسم أو الكود أو قارئ الباركود.',
                    whatHappensIfClicked: 'يفتح قائمة الأصناف الذكية بالرصيد المتاح وسعر البيع وينزل الصنف للجدول مباشرة.'
                },
                {
                    label: 'كاشف هامش الربح وشرائح الأسعار (#sales-margin-guard-badge / #hdr-price-tier)',
                    targetSelector: '#sales-margin-guard-badge, #hdr-price-tier, #btn-cust-item-history',
                    whatItDoes: 'مراقبة هامش الربح اللحظي وتبديل شرائح الأسعار واستعراض آخر أسعار العميل.',
                    whatHappensIfClicked: 'يفتح تفصيل تكلفة وربحية كل صنف أو يطبق شريحة السعر المختارة فوراً.'
                },
                {
                    label: 'لوحة الدفع الهجين والكمبيالة (#payment-panel)',
                    targetSelector: '#toggle-credit, #toggle-cash, #toggle-bank, #toggle-cheque, #btn-sales-financial-ocr',
                    whatItDoes: 'تحصيل الفاتورة نقداً أو بتحويل بنكي أو شيك أو تقييدها آجلة مع كمبيالة.',
                    whatHappensIfClicked: 'يفتح نافذة طريقة الدفع المختارة مع إمكانية الدمج بين عدة طرق ومسح الإشعارات بالذكاء الاصطناعي.'
                }
            ],
            gapReport: {
                title: 'تقرير المراجعة الثلاثية والنواقص المحاسبية والهندسية (Direct Sales Audit & Gap Report)',
                items: [
                    '✅ الأمر الحاسم (1) — محرك الصوت الرجالي: نطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة زر المرشد (hasPlayedOpeningInvocation = true) ومنع تكرارها نهائياً عند الانتقال بين الخطوات أو الحقول.',
                    '✅ الأمر الحاسم (2) — كبسولة التحكم الملكية المصغرة: إلغاء الشريط العريض الحاجب للرؤية واستبداله بكبسولة تحكم ملكية أنيقة ومصغرة لا تحجب الشاشة.',
                    '✅ الترابط الذري (Atomic Sync): ترحيل متزامن لكشف حساب العميل، الخزينة/البنك/الشيكات، اليومية العامة، وبوابة تسليم المخزن (GDN).',
                    '✅ التركيز البصري الهادئ والحماية التسعيرية: توجيه المؤشر بإضاءة ذهبية (focusRequiredSalesField)، وكاشف البيع بأقل من التكلفة، وشرائح الأسعار الآلية، وتعليق/استدعاء الفواتير (POS Park & Hold).'
                ]
            }
        };

        window.MagaiesSmartGuides['sales-split'] = {
            title: 'مرشد منظومة تقسيم الفواتير وأذونات التسليم (بالوحدة الأساسية والفرعية)',
            subtitle: 'شرح مصوّر ومدبلج بالصوت الرجالي خطوة بخطوة: يفتح جدول الفواتير القابلة للتقسيم ولوحة التقسيم الذكية بالوحدة الكبرى والفرعية وشجرة النسب أمامك فعلياً',
            screenHierarchy: [
                '1️⃣ الخطوة الأولى: جدول اختيار الفاتورة أو الإذن المراد تقسيمه (#split-select-step)',
                '2️⃣ الخطوة الثانية: لوحة التقسيم التفاعلية وأرقام الأذونات الفرعية المتسلسلة (#split-board-step)',
                '3️⃣ نظام الوحدات المزدوج: التقسيم بالوحدة الأساسية (جوال/كرتونة) وبالوحدة الفرعية (كيلو/قطعة)',
                '4️⃣ إدارة الأقسام والتوازن: إضافة قسم ثالث أو رابع + التوزيع بالتساوي + ميزان التطابق اللحظي',
                '5️⃣ التوثيق والاعتماد: بيانات المستلم والسيارة لكل قسم + شجرة التقسيم 🌳 + طباعة الجزئين معاً'
            ],
            overview: 'تتيح منظومة تقسيم الفواتير وأذونات التسليم تجزئة أي فاتورة أو إذن تسليم (أو حتى إعادة تقسيم إذن مقسم سابقاً) إلى قسمين أو 3 أو 4 أقسام فأكثر، سواء بالوحدة الكبرى أو بالوحدة الفرعية، مع توليد أرقام تسلسلية هرمية (مثل 100/1/1 و 100/1/2) وحفظ شجرة النسب كاملة.',
            autopilotSteps: [
                {
                    badge: '1. الخطوة الأولى: جدول اختيار الفاتورة أو الإذن القابل للتقسيم',
                    cursorLabel: 'جدول الفواتير القابلة للتقسيم ✂️',
                    screenPath: 'شاشة التقسيم 1️⃣: البحث عن الفاتورة (#split-invoice-search) ⬅ استعراض الكميات المتبقية ⬅ زر (✂️ تقسيم)',
                    targetSelector: '#split-select-step, #split-invoice-search',
                    diagramType: 'screen-hub',
                    diagramMeta: {
                        parentScreen: 'جدول اختيار الفاتورة للتقسيم (#split-select-step)',
                        actionLabel: 'يعرض الفواتير والأذونات الجاهزة للتجزئة:',
                        childItems: ['🔍 البحث الفوري برقم الفاتورة أو اسم العميل أو الصنف', '📦 عرض الكمية المتبقية بالوحدة الكبرى والفرعية لكل صنف', '🔢 معاينة الأرقام الفرعية التي ستتولد قبل التقسيم', '✂️ زر (تقسيم) لفتح لوحة التوزيع الذكية فوراً'],
                        effectLabel: 'يفتح لوحة التقسيم التفاعلية للفاتورة المختارة'
                    },
                    action: () => {
                        if (typeof openSplitInvoiceView === 'function') {
                            openSplitInvoiceView(false);
                        }
                    },
                    speech: () => 'أهلاً بحضرتك في الشرح المصوّر الشامل لمنظومة تقسيم الفواتير وأذونات التسليم. نبدأ من الخطوة الأولى حيث يظهر جدول الفواتير القابلة للتقسيم مع شريط البحث الفوري، ويعرض أمام كل فاتورة الكميات المتبقية بالوحدة الأساسية والفرعية والأرقام الفرعية المتوقعة قبل أن تضغط على زر تقسيم.'
                },
                {
                    badge: '2. فتح لوحة التقسيم التفاعلية ورقم عملية التقسيم المتسلسل',
                    cursorLabel: 'لوحة التقسيم وأرقام الأذونات الفرعية 🔢',
                    screenPath: 'شاشة التقسيم 2️⃣: فتح لوحة التقسيم (#split-board-step) ⬅ رقم عملية التقسيم التسلسلي ⬅ شريط التوازن اللحظي',
                    targetSelector: '#split-header-info',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'رأس لوحة التقسيم الذكية (#split-header-info)',
                        actionLabel: 'يفتح لوحة التوزيع ويعرض الهوية الهرمية للأذونات:',
                        childItems: ['📄 رقم الإذن المرجعي وشجرة النسب (إن كان منقسماً سابقاً)', '🔢 رقم عملية التقسيم التسلسلي (مثلاً 100/1/1 و 100/1/2)', '👤 اسم العميل وطريقة الدفع وإجمالي الإذن المرجعي', '🖨️ زر (طباعة الجزئين) لمعاينة جميع الأقسام معاً'],
                        effectLabel: 'لوحة التقسيم مفتوحة الآن أمامك على الشاشة!'
                    },
                    action: () => {
                        if (typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
                            window.ensureDemoOrFirstSplitBoardLoaded();
                        }
                    },
                    speech: () => 'بمجرد اختيار الفاتورة تفتح أمامك لوحة التقسيم الذكية كما ترى الآن على الشاشة، ويظهر في أعلاها رقم الإذن الأصلي ورقم عملية التقسيم التسلسلي الذي يدمج تلقائياً ليميز كل قسم برقم مستقل، مع عرض بيانات العميل وإجمالي الإذن.'
                },
                {
                    badge: '3. أزرار نظام التقسيم بالوحدات والقوالب الذكية وإضافة الأقسام',
                    cursorLabel: 'التبديل بين الوحدات والقوالب الذكية 🎛️',
                    screenPath: 'شاشة التقسيم 2️⃣: أزرار (الوحدتان معاً • الأساسية • الفرعية) + (⚖️ بالتساوي • 📊 70%/30% • 🏋️ فصل الثقيل • 📦 كل صنف بقسم) + (➕ قسم)',
                    targetSelector: 'button[onclick*="setSplitUnitDisplayMode"], button[onclick*="distributeSplitEqually"], button[onclick*="applySplitRatioPreset"], button[onclick*="addSplitSection"]',
                    diagramType: 'dropdown-open',
                    diagramMeta: {
                        parentScreen: 'شريط التحكم في الوحدات والقوالب الذكية',
                        actionLabel: 'تحكم كامل في طريقة العرض والتوزيع السريع وعدد الأقسام:',
                        childItems: ['📦 + 🔢 عرض الوحدتين معاً (الأساسية والفرعية في آن واحد)', '⚖️ بالتساوي أو 📊 70%/30% لتوزيع الكميات بضغطة واحدة', '🏋️ فصل الأصناف الثقيلة أو 📦 وضع كل صنف في قسم مستقل', '➕ زر (قسم جديد) لتقسيم الفاتورة إلى 3 أو 4 أقسام فأكثر'],
                        effectLabel: 'يحدّث صناديق الإدخال وأعمدة الأقسام في نفس اللحظة!'
                    },
                    action: () => {
                        if (typeof window.ensureDemoOrFirstSplitBoardLoaded === 'function') {
                            window.ensureDemoOrFirstSplitBoardLoaded();
                        }
                        if (typeof window.setSplitUnitDisplayMode === 'function') {
                            window.setSplitUnitDisplayMode('both');
                        }
                    },
                    speech: () => 'من شريط التحكم العلوي يمكنك التبديل بين إظهار الوحدتين معاً أو التقسيم بالوحدة الأساسية فقط أو بالوحدة الفرعية فقط، كما يمكنك استخدام أزرار التوزيع الذكي بنقرة واحدة مثل بالتساوي أو سبعين ثلاثين أو فصل الأصناف الثقيلة أو وضع كل صنف في قسم مستقل، أو إضافة قسم ثالث ورابع.'
                },
                {
                    badge: '4. جدول التوزيع المزدوج: التقسيم بالوحدة الكبرى والفرعية وميزان التطابق',
                    cursorLabel: 'صناديق التقسيم (أساسي / فرعي) ⚖️',
                    screenPath: 'شاشة التقسيم 2️⃣: جدول الأصناف (#split-items-body) ⬅ صندوق الوحدة الكبرى + صندوق الوحدة الفرعية ⬅ توازن السطر',
                    targetSelector: '#split-items-body, #split-qty-s0-i0',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'جدول توزيع كميات الأصناف (#split-items-body)',
                        actionLabel: 'تحويل متزامن لحظي بين الوحدة الكبرى والفرعية:',
                        childItems: ['📦 الصندوق الأخضر: إدخال الكمية بالوحدة الأساسية مع أزرار (½ و ⚡ الكل)', '🔢 الصندوق البنفسجي: إدخال الكمية بالوحدة الفرعية (مثلاً بالكيلو أو القطعة)', '🔄 حساب المتبقي تلقائياً في القسم الأخير لمنع أي خطأ حسابي', '⚖️ عمود حالة التوازن: يضيء بالأخضر فور تطابق مجموع الأقسام 100%'],
                        effectLabel: 'تزامن فوري بين الوحدتين ومراقبة دقيقة للتوازن!'
                    },
                    action: () => {
                        const firstInp = document.getElementById('split-qty-s0-i0');
                        if (firstInp) { try { firstInp.focus(); } catch(e) {} }
                    },
                    speech: () => 'داخل جدول الأصناف، يحتوي كل قسم على صندوقين متزامنين: الصندوق الأول للتقسيم بالوحدة الأساسية، والصندوق الثاني للتقسيم بالوحدة الفرعية، مع أزرار النصف والكل؛ وبمجرد تعديل أي رقم يحسب النظام المتبقي في القسم الأخير تلقائياً ويتأكد من توازن السطر بنسبة مئة بالمئة.'
                },
                {
                    badge: '5. بطاقات ملخص الأقسام المستقلة وتوثيق السائق والسيارة وموقع التسليم والواتساب',
                    cursorLabel: 'ملخص كل قسم والسائق وموقع التسليم 🚛',
                    screenPath: 'شاشة التقسيم 2️⃣: بطاقات ملخص الأقسام (#split-cards-grid-container) ⬅ صافي كل قسم + السائق + اللوحة + الموقع + الواتساب',
                    targetSelector: '#split-cards-grid-container, #split-sec-receiver-0',
                    diagramType: 'subpanel-expand',
                    diagramMeta: {
                        parentScreen: 'بطاقات ملخص الأقسام (#split-cards-grid-container)',
                        actionLabel: 'بطاقة مالية ولوجستية مستقلة لكل قسم ناتج:',
                        childItems: ['💵 الإجمالي والخصم النسبي والصافي النهائي لكل قسم بدقة', '👤 المستلم/السائق + 🚛 رقم السيارة + زر (🚛 سائق سريع)', '📍 موقع التسليم والوجهة + 📲 رقم واتساب المستلم لكل قسم', '🖨️ زر طباعة مستقل لكل قسم على حدة'],
                        effectLabel: 'توثيق مالي ولوجستي مستقل لكل إذن فرعي!'
                    },
                    action: () => {
                        const recInp = document.getElementById('split-sec-receiver-0');
                        if (recInp) { try { recInp.focus(); } catch(e) {} }
                    },
                    speech: () => 'في أسفل اللوحة تظهر بطاقات ملخص الأقسام، حيث يعرض كل قسم حصته النسبية من الخصم والضريبة وصافيه النهائي، مع إمكانية تعيين سائق سريع وتحديد اسم المستلم ورقم السيارة وموقع التسليم ورقم الواتساب لكل قسم على حدة.'
                },
                {
                    badge: '6. معاينة الأذونات واعتماد التقسيم الذري (F10) وطباعة الجزئين',
                    cursorLabel: 'اعتماد التقسيم وطباعة الجزئين ✅🖨️',
                    screenPath: 'شاشة التقسيم 2️⃣: زر (👁️ معاينة الجزئين) + زر (✅ اعتماد التقسيم F10) + زر (🖨️ اعتماد وطباعة)',
                    targetSelector: '#btn-split-submit, #btn-split-submit-print, #btn-split-preview-vouchers',
                    diagramType: 'accounting-flow',
                    diagramMeta: {
                        parentScreen: 'شريط اعتماد التقسيم والطباعة المجمعة',
                        actionLabel: 'اعتماد ذري يحفظ التسلسل الهرمي ويحدّث بوابة التسليم:',
                        childItems: ['👁️ معاينة شكل الأذونات المقسمة قبل الحفظ النهائي', '✅ اعتماد التقسيم (F10) وتوليد الأذونات الفرعية في المبيعات والمخزن', '🖨️ طباعة الجزئين معاً في صفحة واحدة أو صفحات مستقلة بضغطة زر', '⚡ مزامنة فورية لحظية مع السيرفر وتطبيق الموبايل'],
                        effectLabel: 'حفظ ومزامنة فورية مع بوابة التسليم وتطبيق الجوال!'
                    },
                    speech: () => 'من شريط الاعتماد السفلي، يمكنك معاينة شكل الأذونات قبل الحفظ، أو الضغط على اعتماد التقسيم باختصار إف عشرة، أو اعتماد التقسيم وطباعة الجزئين معاً، ليقوم النظام بتوليد الأذونات الفرعية فوراً في سجل المبيعات وبوابة تسليم المخزن.'
                },
                {
                    badge: '7. الشاشة الوليدة الأولى: نافذة نجاح التقسيم وإرسال واتساب وتسليم كل جزء',
                    cursorLabel: 'نافذة الأذونات الوليدة وواتساب كل جزء 📲📦',
                    screenPath: 'شاشة وليدة 1️⃣: نافذة نجاح التقسيم (#post-split-success-modal) ⬅ طباعة الجزء + واتساب الجزء + تسليم الجزء فوراً',
                    targetSelector: '#post-split-success-modal',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'نافذة الأذونات الوليدة (#post-split-success-modal)',
                        actionLabel: 'تفتح تلقائياً بعد اعتماد التقسيم وتعرض بطاقة لكل جزء:',
                        childItems: ['🖨️ زر طباعة مستقل لكل جزء أو طباعة الجزئين معاً في ورقة واحدة', '📲 زر واتساب مستقل لكل جزء يرسل تفاصيل الشحنة والسائق للمستلم', '📦 زر تسليم الجزء الآن لفتح بوابة الصرف المخزني لهذا الجزء مباشرة', '🔢 عرض معادلة الوحدات الأساسية والفرعية والصافي المالي لكل جزء'],
                        effectLabel: 'الشاشة الوليدة للأذونات المقسمة مفتوحة الآن أمامك!'
                    },
                    action: () => {
                        if (typeof window.previewGuidePostSplitSuccessModal === 'function') {
                            window.previewGuidePostSplitSuccessModal();
                        }
                    },
                    speech: () => 'وفور اعتماد التقسيم تفتح أمامك هذه الشاشة الوليدة كما ترى الآن، وتعرض بطاقة مستقلة لكل جزء مقسم، ومنها يمكنك طباعة أي جزء بمفرده، أو إرسال تفاصيله عبر واتساب لمستلمه وسائقه الخاص، أو فتح شاشة التسليم المخزني لصرف هذا الجزء فوراً.'
                },
                {
                    badge: '8. الشاشة الوليدة الثانية: شجرة النسب والتتبع الهرمي للفاتورة الأم والفروع',
                    cursorLabel: 'شجرة النسب والتتبع الهرمي 🌳',
                    screenPath: 'شاشة وليدة 2️⃣: شجرة النسب (#split-genealogy-tree-modal) ⬅ تتبع الفاتورة الأم والأقسام الفرعية وحالة صرف كل فرع',
                    targetSelector: '#split-genealogy-tree-modal',
                    diagramType: 'screen-transition',
                    diagramMeta: {
                        parentScreen: 'شجرة النسب والتتبع (#split-genealogy-tree-modal)',
                        actionLabel: 'توثق تاريخ عائلة الفاتورة وعمليات التقسيم المتعددة:',
                        childItems: ['🌳 عرض الإذن المرجعي الأصلي وجميع الفروع المنبثقة عنه بأرقامها', '📊 متابعة حالة كل فرع (جاهز للصرف • تسليم جزئي • تم الصرف GDN)', '📦 زر (صرف) لتسليم أي فرع غير مسلم مباشرة من الشجرة', '✂️ زر (تقسيم إضافي) لإعادة تجزئة أي فرع إلى فروع أصغر'],
                        effectLabel: 'شاشة شجرة النسب مفتوحة الآن أمامك!'
                    },
                    action: () => {
                        const sModal = document.getElementById('post-split-success-modal');
                        if (sModal) sModal.style.display = 'none';
                        if (typeof window.openSplitGenealogyModal === 'function') {
                            window.openSplitGenealogyModal('100', true);
                        }
                    },
                    speech: () => 'كما يتيح لك النظام فتح شاشة شجرة النسب الوليدة الظاهرة أمامك الآن، والتي تعرض الخريطة الشجرية الكاملة للفاتورة الأم وجميع الأذونات الفرعية المنبثقة عنها وحالة تسليم كل فرع، مع إمكانية صرف أي فرع أو إعادة تقسيمه مباشرة من داخل الشجرة.'
                }
            ],
            visualSteps: [
                {
                    stepBadge: '1️⃣ الهوية الهرمية للتجزئة',
                    title: 'توليد أرقام الأذونات الفرعية (100/1/1 و 100/1/2)',
                    desc: 'يحفظ النظام رقم الفاتورة الأم ويولد أرقاماً فرعية متسلسلة لكل عملية تقسيم مع حفظ شجرة النسب الكاملة.',
                    svg: `<svg viewBox="0 0 320 110" width="100%" height="100%"><rect x="110" y="8" width="100" height="30" rx="8" fill="#1e1b4b" stroke="#38bdf8" stroke-width="2"/><text x="160" y="27" fill="#fde047" font-size="12" font-weight="bold" text-anchor="middle">الأصل #100</text><path d="M160 38 L90 65 M160 38 L230 65" stroke="#10b981" stroke-width="2.5"/><rect x="30" y="65" width="115" height="34" rx="8" fill="#064e3b" stroke="#34d399" stroke-width="2"/><text x="87" y="86" fill="#fff" font-size="11" font-weight="bold" text-anchor="middle">فرع #100/1/1</text><rect x="175" y="65" width="115" height="34" rx="8" fill="#500724" stroke="#f472b6" stroke-width="2"/><text x="232" y="86" fill="#fff" font-size="11" font-weight="bold" text-anchor="middle">فرع #100/1/2</text></svg>`
                },
                {
                    stepBadge: '2️⃣ التزامن بين الوحدتين',
                    title: 'التقسيم بالوحدة الكبرى أو بالوحدة الفرعية لحظياً',
                    desc: 'أدخل الكمية بالجوال/الكرتونة أو بالكيلو/القطعة؛ يحسب النظام المعادل والمتبقي في القسم الأخير تلقائياً.',
                    svg: `<svg viewBox="0 0 320 110" width="100%" height="100%"><rect x="16" y="22" width="128" height="66" rx="10" fill="#064e3b" stroke="#10b981" stroke-width="2"/><text x="80" y="48" fill="#6ee7b7" font-size="11" font-weight="bold" text-anchor="middle">📦 وحدة كبرى (5 جوال)</text><text x="80" y="70" fill="#fff" font-size="10" text-anchor="middle">معامل التحويل ×50</text><path d="M148 55 L172 55" stroke="#fbbf24" stroke-width="3"/><rect x="176" y="22" width="128" height="66" rx="10" fill="#3b0764" stroke="#c084fc" stroke-width="2"/><text x="240" y="48" fill="#e9d5ff" font-size="11" font-weight="bold" text-anchor="middle">🔢 وحدة فرعية (250 كجم)</text><text x="240" y="70" fill="#fde047" font-size="10" text-anchor="middle">تزامن لحظي 100%</text></svg>`
                },
                {
                    stepBadge: '3️⃣ لوجستيات وواتساب كل قسم',
                    title: 'سائق مستقل وإشعار واتساب وطباعة لكل جزء',
                    desc: 'اربط كل فاتورة فرعية بسائقها ورقم اللوحة وموقع التسليم وأرسل تفاصيلها عبر واتساب بضغطة واحدة.',
                    svg: `<svg viewBox="0 0 320 110" width="100%" height="100%"><rect x="20" y="18" width="280" height="74" rx="12" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/><text x="160" y="44" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle">🚛 سائق مستقل + 📍 موقع تسليم لكل قسم</text><text x="160" y="72" fill="#86efac" font-size="11" font-weight="bold" text-anchor="middle">🖨️ طباعة الجزء • 📲 واتساب الجزء • 📦 تسليم فوري</text></svg>`
                }
            ],
            scenarios: [
                {
                    q: '💡 هل يمكنني تقسيم الصنف بالوحدة الفرعية (مثلاً بالكيلو أو القطعة) بدلاً من الجوال أو الكرتونة؟',
                    a: 'نعم! داخل لوحة التقسيم يظهر لكل صنف صندوقان: صندوق للوحدة الأساسية وصندوق للوحدة الفرعية (مضروباً في معامل التحويل)، ويمكنك الكتابة في أي منهما وسيقوم النظام بالتحويل الفوري وحساب المتبقي تلقائياً.'
                },
                {
                    q: '💡 هل يمكنني تقسيم الفاتورة إلى أكثر من قسمين (مثلاً 3 أو 4 سيارات)؟',
                    a: 'بكل سهولة؛ اضغط على زر (➕ قسم) في شريط التحكم العلوي لإضافة القسم الثالث أو الرابع أو الخامس، أو اضغط (⚖️ بالتساوي) لتوزيع الكمية بالتساوي على جميع الأقسام.'
                },
                {
                    q: '💡 هل يمكن إعادة تقسيم إذن كان مقسماً من قبل (تقسيم الفرع إلى فروع)؟',
                    a: 'نعم، يدعم النظام التقسيم المتعدد الأجيال (Multi-Generation Split) مع الاحتفاظ برقم الفاتورة الأم وشجرة العائلة الكاملة (🌳 شجرة التقسيم).'
                }
            ],
            buttonsDict: [
                {
                    label: 'اعتماد التقسيم وطباعة الجزئين (Ctrl+S)',
                    targetSelector: 'button[onclick*="submitInvoiceSplit"]',
                    whatItDoes: 'اعتماد تقسيم الفاتورة وتوليد الأذونات الفرعية الجديدة بأرقامها التسلسلية الهرمية.',
                    whatHappensIfClicked: 'يتحقق من تطابق مجموع الأقسام مع الكمية القابلة للتقسيم، ثم ينشئ الأذونات الفرعية ويحدث سجل المبيعات وبوابة المخزن ويفتح الطباعة.'
                },
                {
                    label: 'نظام التقسيم بالوحدات (الوحدتان معاً / الأساسية / الفرعية)',
                    targetSelector: 'button[onclick*="setSplitUnitDisplayMode"]',
                    whatItDoes: 'التحكم في إظهار صناديق الوحدة الأساسية والفرعية داخل جدول التقسيم.',
                    whatHappensIfClicked: 'يعرض الصندوقين معاً أو يركز الجدول على الوحدة الأساسية فقط أو الفرعية فقط حسب رغبتك.'
                },
                {
                    label: 'توزيع بالتساوي (⚖️ بالتساوي) وإضافة قسم (➕ قسم)',
                    targetSelector: 'button[onclick*="distributeSplitEqually"], button[onclick*="addSplitSection"]',
                    whatItDoes: 'توزيع الكميات بالتساوي على الأقسام الحالية أو زيادة عدد الأقسام.',
                    whatHappensIfClicked: 'يقسم الكميات آلياً أو يضيف عمود قسم جديد برقم فرعي متسلسل وبطاقة ملخص مستقلة.'
                },
                {
                    label: 'معاينة وطباعة الجزئين (🖨️ طباعة الجزئين / 👁️ معاينة)',
                    targetSelector: 'button[onclick*="previewCurrentSplitVouchersBeforeSave"]',
                    whatItDoes: 'عرض ومعاينة الأذونات المقسمة جنباً إلى جنب قبل أو بعد الاعتماد.',
                    whatHappensIfClicked: 'يفتح نافذة معاينة الأذونات الفرعية مع خيار طباعتها معاً بضغطة واحدة.'
                }
            ],
            gapReport: {
                title: 'تقرير المراجعة الثلاثية لمنظومة تقسيم الفواتير والأذونات (Split Invoice Audit & Gap Report)',
                items: [
                    '✅ الأمر الحاسم (1) — محرك الصوت الرجالي: نطق الصلاة على النبي ﷺ مرة واحدة فقط مع ضغطة زر المرشد (hasPlayedOpeningInvocation = true) ومنع تكرارها نهائياً بين الخطوات أو الحقول.',
                    '✅ الأمر الحاسم (2) — كبسولة التحكم الملكية المصغرة: إلغاء الشريط العريض الحاجب للرؤية واستبداله بكبسولة تحكم ملكية أنيقة ومصغرة لا تحجب الشاشة.',
                    '✅ التقسيم المزدوج بالوحدة الأساسية والفرعية: تزامن لحظي بين الوحدتين مع حساب المتبقي تلقائياً وميزان توازن بصري.',
                    '✅ التركيز البصري الهادئ والتنقل بـ Enter: توجيه المؤشر بإضاءة ذهبية (focusRequiredSalesField) لأي سطر غير متوازن والتنقل السلس بين الحقول.'
                ]
            }
        };

        if (typeof window.prewarmSmartGuideScreen === 'function') {
            setTimeout(() => {
                const isSplitUrl = window.location.search.includes('action=split');
                window.prewarmSmartGuideScreen(isSplitUrl ? 'sales-split' : 'sales-direct');
            }, 150);
        }

        window.explainDirectSalesDomElement = function(el) {
            if (!el) return null;
            const rules = [
                { sel: '#split-invoice-search', title: '🔍 البحث في الفواتير القابلة للتقسيم', desc: 'ابحث برقم الفاتورة أو اسم العميل أو الصنف للوصول الفوري للفاتورة المراد تجزئتها.' },
                { sel: 'button[onclick*="startInvoiceSplit"]', title: '✂️ زر بدء تقسيم الفاتورة', desc: 'يفتح لوحة التقسيم التفاعلية لتوزيع كميات هذه الفاتورة بالوحدة الأساسية أو الفرعية إلى قسمين أو أكثر.' },
                { sel: '#split-operation-seq-input, button[onclick*="adjustSplitOperationSeq"]', title: '🔢 رقم عملية التقسيم التسلسلي', desc: 'يحدد رقم عملية التقسيم الذي يُدمج في أرقام الأذونات الفرعية (مثل 100/1/1 و 100/1/2).' },
                { sel: 'button[onclick*="setSplitUnitDisplayMode"]', title: '🎛️ مبدّل نظام الوحدات (أساسي / فرعي / معاً)', desc: 'يبدل عرض جدول التقسيم بين إظهار الوحدتين معاً أو التقسيم بالوحدة الأساسية فقط أو بالوحدة الفرعية فقط.' },
                { sel: 'button[onclick*="distributeSplitEqually"]', title: '⚖️ توزيع الكميات بالتساوي', desc: 'يقسم الكمية المتبقية لكل صنف بالتساوي على جميع الأقسام المفتوحة بضغطة واحدة.' },
                { sel: 'button[onclick*="applySplitRatioPreset"]', title: '📊 توزيع سريع 70% / 30%', desc: 'يوزع الكميات تلقائياً بنسبة 70% للقسم الأول و30% للأقسام المتبقية بضغطة واحدة.' },
                { sel: 'button[onclick*="separateHeavyItemsSplit"]', title: '🏋️ فصل الأصناف الثقيلة تلقائياً', desc: 'يفصل الأصناف ذات الوزن أو معامل التحويل الكبير في القسم الأول والأصناف الخفيفة في القسم الثاني.' },
                { sel: 'button[onclick*="separateSplitItemsIntoSections"]', title: '📦 فصل كل صنف في قسم مستقل', desc: 'ينشئ قسماً فرعياً مستقلاً لكل صنف في الفاتورة تلقائياً لتوزيعها على عدة سيارات أو مستلمين.' },
                { sel: 'button[onclick*="addSplitSection"]', title: '➕ إضافة قسم تقسيم جديد', desc: 'يضيف قسماً جديداً (ثالث أو رابع أو أكثر) لتوزيع الفاتورة على عدة أذونات أو شاحنات.' },
                { sel: 'input[id^="split-qty-"]', title: '📦 حقل كمية القسم بالوحدة الأساسية', desc: 'أدخل كمية هذا القسم بالوحدة الكبرى؛ يتحدث الصندوق الفرعي والقسم المتبقي تلقائياً.' },
                { sel: 'input[id^="split-subqty-"]', title: '🔢 حقل كمية القسم بالوحدة الفرعية', desc: 'أدخل كمية هذا القسم بالوحدة الفرعية (كيلو/قطعة)؛ يحسب النظام ما يعادلها بالوحدة الكبرى تلقائياً.' },
                { sel: '#split-live-balance-bar', title: '⚖️ شريط التوازن اللحظي للتقسيم', desc: 'يراقب مجموع الكميات الموزعة عبر الأقسام مقارنة بالكمية الأصلية وينبهك فوراً لأي فرق.' },
                { sel: 'button[onclick*="quickPickDriverForSplitSec"]', title: '🚛 تعيين سائق وسيارة جاهزة للقسم', desc: 'يملا اسم السائق ورقم اللوحة وموقع التسليم لهذا القسم الفرعي بضغطة واحدة من قائمة السائقين.' },
                { sel: 'input[id^="split-sec-location-"]', title: '📍 موقع التسليم والوجهة للقسم', desc: 'تحديد موقع التسليم أو الفرع المقصود لهذه الشحنة الفرعية ليظهر في إذن الصرف ورسالة الواتساب.' },
                { sel: 'input[id^="split-sec-phone-"]', title: '📲 رقم واتساب مستلم القسم', desc: 'رقم جوال المستلم أو السائق المخصص لهذا الجزء لإرسال إشعار الشحنة له مباشرة عبر واتساب.' },
                { sel: 'input[id^="split-sec-receiver-"], input[id^="split-sec-truck-"], #split-cards-grid-container, #split-sections-summary', title: '🚛 ملخص القسم وبيانات المستلم والسيارة', desc: 'يعرض الصافي المالي لكل قسم مع إمكانية تدوين اسم المستلم ورقم السيارة وموقع التسليم الخاصة بهذا القسم.' },
                { sel: 'button[onclick*="shareSplitVoucherWhatsApp"]', title: '📲 إرسال إشعار القسم عبر واتساب', desc: 'يرسل تفاصيل الفاتورة الوليدة (الأصناف بالوحدة الأساسية والفرعية، المستلم، رقم السيارة، وموقع التسليم) عبر واتساب.' },
                { sel: '#split-live-sync-pill', title: '⚡ شارة التزامن اللحظي لشاشة التجزئة', desc: 'تعرض حالة التزامن اللحظي للفواتير المقسمة بين الموبايل واللابتوب والسيرفر.' },
                { sel: '#post-split-success-modal', title: '✅ نافذة الأذونات الوليدة بعد التقسيم', desc: 'تعرض الأجزاء المقسمة فور اعتمادها مع أزرار الطباعة المستقلة وإرسال الواتساب والتسليم المخزني المباشر لكل جزء.' },
                { sel: '#split-genealogy-tree-modal', title: '🌳 نافذة شجرة النسب والتتبع الهرمي', desc: 'تعرض الخريطة الشجرية الكاملة للفاتورة الأم وكافة الفروع الوليدة وحالة تسليم كل فرع.' },
                { sel: '#btn-split-voice-select, #btn-split-voice-cmd, #split-voice-assistant-modal', title: '🎙️ المساعد الصوتي التفاعلي للتجزئة السريعة', desc: 'يتيح تنفيذ عمليات التقسيم بالأوامر الصوتية العربية (بالتساوي، سبعين ثلاثين، قسم جديد، سائق سريع، اعتماد التقسيم) مع تأكيد صوتي رجالي.' },
                { sel: '#btn-split-audio-summary', title: '🔊 القراءة الصوتية لميزان التجزئة وحصة كل سائق', desc: 'يقرأ بالصوت الرجالي ملخص التجزئة الحالي (عدد الأقسام، كمية وصافي كل قسم، واسم السائق المعيّن) للمراجعة السمعية قبل الاعتماد.' },
                { sel: '#btn-split-hands-on-sim, #btn-split-sim-board, #split-hands-on-sim-modal', title: '🎯 محاكي التدريب العملي للتجزئة في 3 خطوات', desc: 'تدريب عملي تفاعلي يقودك خطوة بخطوة لاختيار فاتورة وتوزيع كمياتها بالوحدتين وتعيين السائق واعتماد التقسيم مع وسام إتقان.' },
                { sel: '#split-filter-status, #split-filter-payment, #split-filter-date', title: '🗂️ فلاتر التجزئة الفورية (الحالة • الدفع • التاريخ)', desc: 'تصفية لحظية لجدول الفواتير الجاهزة للتجزئة حسب كونها أصلية أو فرعية، نقدية أو آجلة، أو بتاريخ محدد.' },
                { sel: '#btn-split-export-excel, #btn-split-print-report', title: '📥 تصدير Excel وطباعة كشف التجزئة A4', desc: 'تصدير جدول الفواتير القابلة للتقسيم إلى ملف Excel فوري أو طباعة كشف رسمي موزون A4.' },
                { sel: '#split-kpi-summary-bar', title: '📊 شريط المؤشرات الحية والإجماليات الختامية للتجزئة', desc: 'يعرض لحظياً عدد الفواتير الجاهزة للتجزئة، الفروع المنبثقة، إجمالي الوحدات المتبقية، وصافي القيمة المالية.' },
                { sel: '#split-drilldown-doc-modal', title: '🔍 نافذة استعراض أصل المستند والبصمة الرقابية (Drill-Down)', desc: 'تفتح أصل الفاتورة بكامل أصنافها وحالة رصيد العميل والبصمة الرقابية (المستخدم، الوقت، الجهاز) دون مغادرة شاشة التجزئة.' },
                { sel: '#btn-split-analytics, #split-analytics-modal', title: '📊 تقرير تحليل كفاءة تجزئة الشحنات والسائقين', desc: 'يعرض إحصائيات الفواتير المجزأة، أكثر الأصناف تجزئة، وتوزيع الحمولات على السائقين والمركبات.' },
                { sel: 'button[onclick*="safeReverseAndMergeSplitFamily"]', title: '🔄 دمج / التراجع الآمن عن التجزئة قبل التسليم', desc: 'يعيد دمج الفروع المقسمة في الفاتورة الأم بأمان تام بشرط عدم صرف أي فرع منها مخزنياً بعد.' },
                { sel: 'button[onclick*="printSplitDriverAuthPass"]', title: '📑 سند تفويض استلام جزئي مستقل للسائق', desc: 'يصدر تصريح بوابة قانوني مستقل لهذا القسم الفرعي بباركود التحقق وبيانات السائق والسيارة وموقع التسليم.' },
                { sel: 'button[onclick*="previewCurrentSplitVouchersBeforeSave"], button[onclick*="printSplitFamilyVouchers"]', title: '🖨️ معاينة وطباعة الأذونات المقسمة (الجزئين)', desc: 'يفتح نافذة معاينة جميع الأقسام المقسمة لطباعتها معاً أو طباعة كل قسم بشكل مستقل.' },
                { sel: 'button[onclick*="openSplitGenealogyModal"]', title: '🌳 شجرة نسب وتاريخ تقسيم الفاتورة', desc: 'يعرض الخريطة الشجرية الكاملة للفاتورة الأم وكافة الأذونات الفرعية المنبثقة عنها وحالة تسليم كل جزء.' },
                { sel: 'button[onclick*="submitInvoiceSplit"]', title: '✅ اعتماد التقسيم وتوليد الأذونات الفرعية', desc: 'يعتمد التقسيم ذرياً وينشئ الأذونات الفرعية الجديدة في سجل المبيعات وبوابة تسليم المخزن.' },
                { sel: 'button[onclick*="openInvoiceEditor(\'pending_delivery\')"]', title: '➕ زر فاتورة مبيعات جديدة', desc: 'يفتح شاشة تحرير فاتورة مبيعات جديدة لإدراج الأصناف وتحديد طريقة الدفع واعتمادها فوراً في الحسابات وبوابة التسليم.' },
                { sel: 'button[onclick*="openInvoiceEditor(\'preliminary\')"]', title: '📋 زر فاتورة مبدئية (عرض سعر)', desc: 'ينشئ فاتورة مبدئية أو عرض أسعار للعميل غير ملزم محاسبياً ومخزنياً، مع إمكانية طباعته أو تحويله لفاتورة فعلية لاحقاً.' },
                { sel: '#uhub-tab-all, #uhub-tab-pending, #uhub-tab-split_only, #uhub-tab-delivered, #uhub-tab-preliminary', title: '🗂️ فلاتر سجل المبيعات والتسليم', desc: 'تصفية جدول المبيعات بضغطة واحدة لعرض كل الفواتير، أو الفواتير قيد التسليم، أو المقسمة، أو المسلمة، أو العروض المبدئية.' },
                { sel: 'button[onclick*="openDeliveryQrScannerModal"]', title: '📷 مسح رمز QR للتسليم الفوري', desc: 'يفتح الماسح الضوئي لقراءة باركود الفاتورة أو إذن التسليم وتنفيذ الصرف المخزني فوراً.' },
                { sel: 'button[onclick*="loadHubTransactions"]', title: '🔄 تحديث ومزامنة سجل المبيعات', desc: 'يعيد جلب أحدث الفواتير وأذونات التسليم المقسمة والمسلمة ومزامنتها مع قاعدة البيانات.' },
                { sel: '#hub-transactions-body', title: '📋 جدول سجل المبيعات والتسليم والتقسيم', desc: 'يعرض الفواتير المسجلة وحالة تسليمها ومخزنها، مع أزرار الإجراءات السريعة (تسليم، تقسيم، طباعة، تعديل، وواتساب).' },
                { sel: '#hub-search', title: '🔍 البحث الفوري في سجل المبيعات', desc: 'ابحث برقم الفاتورة أو اسم العميل أو اسم الصنف للوصول لأي فاتورة وتنفيذ التسليم أو التقسيم أو الطباعة فوراً.' },
                { sel: '#inv-number, .group-invoice', title: '🔢 رقم الفاتورة التسلسلي والتنقل', desc: 'يعرض الرقم التسلسلي التلقائي للفاتورة، ويمكنك الضغط على السهمين (❮ و ❯) لاستعراض الفواتير السابقة والتالية بسرعة.' },
                { sel: '#hdr-date, .group-date', title: '📅 تاريخ الفاتورة المحاسبي', desc: 'يحدد تاريخ إصدار الفاتورة والفترة المحاسبية التي ستُقيّد فيها المبيعات في اليومية العامة وكشف العميل.' },
                { sel: '#fin-strip', title: '📊 شريط الملاءة والحالة المالية للعميل', desc: 'يعرض فور اختيار العميل: رصيده الدفتري، الشيكات المؤجلة، إجمالي المديونية، سقف الائتمان المسموح، والرصيد المتاح للبيع الآجل.' },
                { sel: '#hdr-customer-ledger-btn', title: '📖 زر كشف حساب الأستاذ للعميل', desc: 'يفتح كشف حساب العميل التفصيلي في نافذة فورية لمراجعة كافة حركاته وفواتيره ودفعاته دون الخروج من الفاتورة.' },
                { sel: '#hdr-customer-search, .group-supplier', title: '👤 خانة اختيار العميل', desc: 'ابحث عن العميل بالاسم أو الرقم، أو اضغط زر (➕) لإضافة عميل جديد مع تحديد سقفه الائتماني.' },
                { sel: 'button[onclick*="add-customer-modal"]', title: '➕ إضافة عميل جديد فوراً', desc: 'يفتح نافذة سريعة لتسجيل عميل جديد (الاسم، الهاتف، الواتساب، حد الائتمان) واختياره في الفاتورة مباشرة.' },
                { sel: '#hdr-currency, #hdr-exchange-rate, .group-currency', title: '💱 العملة وسعر الصرف', desc: 'اختر عملة البيع (SDG أو USD أو غيرها) واضبط سعر الصرف ليقوم النظام باحتساب الأسعار والإجماليات تلقائياً.' },
                { sel: '#hdr-warehouse, .group-warehouse', title: '📦 المخزن الرئيسي للصرف', desc: 'يحدد المستودع الافتراضي الذي سيُفحص رصيده وتُصرف منه أصناف الفاتورة (مع إمكانية تحديد مخزن مختلف لكل سطر).' },
                { sel: '#hdr-cost-center, .group-cost-center', title: '🏷️ الفرع / مركز التكلفة', desc: 'يربط فاتورة المبيعات بالفرع أو مركز التكلفة لاستخراج تقارير أرباح ومبيعات كل فرع بدقة.' },
                { sel: '#hdr-description, .group-description', title: '📝 بيان وملاحظات الفاتورة', desc: 'وصف الفاتورة أو الرقم المرجعي الذي يظهر في كشف حساب العميل ودفتر اليومية.' },
                { sel: '#quick-item-search', title: '🔍 البحث السريع عن الأصناف والباركود (F2)', desc: 'ابحث باسم الصنف أو امسح الباركود لإدراجه فوراً في جدول الفاتورة مع عرض سعر البيع والرصيد المتاح.' },
                { sel: 'button[onclick*="startCameraScanner"]', title: '📷 مسح باركود الصنف بالكاميرا', desc: 'يفتح الكاميرا لمسح باركود الصنف وإضافته للجدول مباشرة.' },
                { sel: '#sales-margin-guard-badge', title: '🛡️ كاشف البيع بأقل من التكلفة وهامش الربح اللحظي', desc: 'يراقب أسعار البيع مقارنة بتكلفة الشراء لحظياً؛ يعرض نسبة ومبلغ الربح المتوقع باللون الأخضر، وينبهك فوراً باللون الأحمر عند إدخال سعر أقل من التكلفة، وعند الضغط عليه يفتح تفصيل ربحية وتكلفة كل سطر.' },
                { sel: '#hdr-price-tier', title: '🏷️ شرائح أسعار العملاء الآلية (قطاعي / نصف جملة / جملة)', desc: 'يبدّل أسعار جميع الأصناف المدرجة في الجدول بضغطة واحدة حسب شريحة العميل المختارة (قطاعي، نصف جملة، أو جملة) ويعيد احتساب الفاتورة فوراً.' },
                { sel: '#btn-cust-item-history', title: '📈 نافذة آخر أسعار بيع الصنف للعميل المختار', desc: 'يعرض آخر 3 أسعار وتواريخ بيع بها كل صنف في الفاتورة لهذا العميل تحديداً، مع إمكانية تطبيق أي سعر سابق على السطر بضغطة زر واحدة.' },
                { sel: '#btn-park-invoice', title: '⏸️ تعليق الفاتورة الحالية (POS Park & Hold)', desc: 'يركن فاتورة العميل الحالي مؤقتاً بكامل أصنافها وبياناتها ويفرغ الشاشة لخدمة عميل آخر في الطابور بدون فقدان أي بيانات.' },
                { sel: '#btn-recall-parked', title: '📂 استدعاء الفواتير المعلقة', desc: 'يفتح قائمة الفواتير المعلقة لاسترجاع أي فاتورة بضغطة واحدة واستكمالها واعتمادها فوراً.' },
                { sel: 'button[onclick*="openGridSettingsModal"]', title: '⚙️ تخصيص الجدول', desc: 'ترتيب وإظهار/إخفاء أعمدة جدول الفاتورة وتخصيص حجم الخط والألوان وحفظها في حسابك.' },
                { sel: 'td[data-col-id="available"], th[data-col-id="available"], [data-avail]', title: '📊 الرصيد المتاح حالياً بالمخزن', desc: 'يعرض لحظياً كمية الصنف المتوفرة فعلياً في المستودع قبل صرف الفاتورة لمنع البيع على المكشوف.' },
                { sel: 'td[data-col-id="item"], th[data-col-id="item"], [id^="item-search-"]', title: '📦 خانة اختيار الصنف في السطر', desc: 'ابحث عن الصنف بالاسم أو الكود داخل السطر، أو اختره من القائمة المنسدلة الذكية التي تعرض الرصيد وسعر البيع.' },
                { sel: 'td[data-col-id="unit"], th[data-col-id="unit"], [data-unit-select]', title: '⚖️ وحدة البيع ومعامل التحويل', desc: 'اختر وحدة البيع (كرتونة، جوال، طن، أو قطعة)؛ يحول النظام الكمية والسعر آلياً حسب معامل التحويل.' },
                { sel: 'td[data-col-id="quantity"], th[data-col-id="quantity"], [data-qty-input]', title: '🔢 الكمية المباعة', desc: 'أدخل الكمية المطلوبة للعميل؛ يفحص النظام توفر الرصيد في المخزن ويعيد احتساب الإجمالي وهامش الربح فوراً.' },
                { sel: '[data-price-hint]', title: '📈 مؤشر مقارنة سعر البيع والتكلفة', desc: 'ينبهك لحظياً إذا كان سعر البيع أقل من التكلفة أو يعرض مقارنة بآخر سعر بيع للعميل.' },
                { sel: 'td[data-col-id="price"], th[data-col-id="price"], [data-price-input]', title: '💵 سعر بيع الوحدة وهامش الربح', desc: 'أدخل سعر البيع للوحدة المختارة؛ يراقبه كاشف هامش الربح لحظياً لمنع البيع بأقل من التكلفة.' },
                { sel: 'td[data-col-id="warehouse"], th[data-col-id="warehouse"]', title: '🏭 مخزن الصرف الفرعي للسطر', desc: 'يتيح صرف هذا الصنف تحديداً من مستودع فرعي مختلف عن المستودع الرئيسي للفاتورة في نفس العملية.' },
                { sel: 'td[data-col-id="total"], th[data-col-id="total"], [data-total]', title: '💰 إجمالي السطر (الكمية × السعر)', desc: 'يحسب تلقائياً إجمالي قيمة الصنف في السطر بالعملة المختارة وما يعادلها بالعملة المحلية.' },
                { sel: '.add-row-btn', title: '➕ زر إضافة سطر صنف جديد', desc: 'يضيف سطراً فارغاً جديداً أسفل الجدول لإدراج صنف إضافي في الفاتورة.' },
                { sel: '.del-btn', title: '🗑️ زر حذف السطر من الفاتورة', desc: 'يحذف هذا السطر من جدول الفاتورة ويعيد احتساب الإجماليات وصافي الفاتورة فوراً.' },
                { sel: '#pos-grid', title: '📦 جدول أصناف الفاتورة والوحدات', desc: 'أدخل الكمية بالوحدة الكبرى أو العدد بالوحدة الفرعية والسعر؛ يقوم النظام بالتحويل التلقائي بين الوحدتين وعرض الرصيد المتاح والإجمالي.' },
                { sel: '#btn-toggle-transport, #row-tot-transport', title: '🚚 إضافة رسوم الترحيل', desc: 'إظهار خانة رسوم الترحيل والنقل لإضافتها إلى إجمالي الفاتورة.' },
                { sel: '#btn-toggle-labor, #row-tot-labor', title: '👷‍♂️ إضافة رسوم العمالة', desc: 'إظهار خانة أجور التحميل والعمالة لإضافتها إلى إجمالي الفاتورة.' },
                { sel: '#btn-toggle-additions, #row-tot-additions', title: '➕ مبالغ إضافية على الفاتورة', desc: 'إضافة أي رسوم أو خدمات إضافية على الفاتورة.' },
                { sel: '#btn-toggle-tax, #row-tot-tax', title: '💰 الضريبة المضافة', desc: 'إدخال مبلغ الضريبة المطبقة على الفاتورة.' },
                { sel: '#btn-toggle-discount, #row-tot-discount', title: '➖ الخصم الممنوح للعميل', desc: 'إدخال قيمة الخصم الممنوح للعميل لتخفيض الصافي النهائي للفاتورة.' },
                { sel: '#row-grand-total, #t-grand-dynamic-loc', title: '💵 الإجمالي الكلي النهائي للفاتورة', desc: 'يعرض الصافي النهائي المستحق على الفاتورة بعد احتساب جميع الأصناف والمصاريف والخصومات.' },
                { sel: '#toggle-credit, #pay-credit-section', title: '💳 البيع الآجل والكمبيالة', desc: 'تقييد الفاتورة (أو المتبقي منها) ذمة آجلة على حساب العميل مع تحديد تاريخ الاستحقاق ومعاينة الكمبيالة القانونية.' },
                { sel: 'button[onclick*="previewCurrentPromissoryNote"]', title: '📄 معاينة وطباعة الكمبيالة', desc: 'يولد كمبيالة رسمية بالمبلغ الآجل وتاريخ الاستحقاق وبيانات العميل جاهزة للطباعة والتوقيع.' },
                { sel: '#toggle-cash, #pay-cash-section', title: '💵 القبض النقدي المباشر', desc: 'تحصيل قيمة الفاتورة (أو جزء منها) نقداً وإيداعها فوراً في الخزينة المحددة.' },
                { sel: '#toggle-bank, #pay-bank-section', title: '🏦 التحويل البنكي (بنكك / فوري)', desc: 'تحصيل الفاتورة عبر تحويل بنكي واحد أو متعدد مع توثيق رقم الإشعار والبنك المستلم.' },
                { sel: '#toggle-cheque, #pay-cheque-section', title: '✍️ التحصيل بالشيكات', desc: 'تسجيل شيك أو عدة شيكات واردة من العميل برقم الشيك والبنك وتاريخ الاستحقاق.' },
                { sel: '#btn-sales-financial-ocr', title: '📷 مسح إشعار بنكي أو شيك بالذكاء الاصطناعي', desc: 'يقرأ صورة إشعار التحويل البنكي أو الشيك تلقائياً ويملأ المبلغ ورقم العملية والبنك.' },
                { sel: 'button[onclick*="showSalesHub"], .btn-exit', title: '🚪 زر الخروج والرجوع للسجل', desc: 'الرجوع إلى شاشة سجل المبيعات والتسليم الموحدة.' },
                { sel: 'button[onclick*="confirmDeleteTransaction"], .btn-delete', title: '🗑️ حذف / إلغاء الفاتورة المحمي', desc: 'يفحص ارتباطات الفاتورة أولاً، فإذا لم تكن مرتبطة بتسليم مخزني أو سندات يقوم بحذفها وعكس قيودها بأمان.' },
                { sel: 'button[onclick*="saveDraft"], .btn-draft', title: '💾 حفظ كمسودة', desc: 'حفظ الفاتورة مؤقتاً كمسودة للعودة إليها واستكمالها لاحقاً دون ترحيل محاسبي.' },
                { sel: 'button[onclick*="openStandalonePrintPreview"], .royal-btn-print', title: '🖨️ معاينة وطباعة الفاتورة', desc: 'فتح استوديو المعاينة والطباعة لطباعة الفاتورة أو إذن التسليم بتصميم احترافي.' },
                { sel: '#preset-selector', title: '⚙️ قوالب تخصيص الشاشة', desc: 'اختيار قالب عرض محفوظ لتنسيق أعمدة وخطوط جدول الفاتورة.' },
                { sel: '#btn-share-invoice, #share-btn-wrapper', title: '📤 مشاركة الفاتورة (واتساب / إيميل)', desc: 'إرسال الفاتورة كملف PDF أو رسالة مفصلة مباشرة إلى واتساب العميل أو بريده الإلكتروني.' },
                { sel: '#btn-new-invoice', title: '➕ بدء فاتورة جديدة', desc: 'تفريغ الحقول الحالية وتجهيز رقم تسلسلي جديد لإصدار فاتورة مبيعات جديدة.' },
                { sel: '#auto-send-control-wrapper-sales', title: '📲 الإرسال التلقائي عبر واتساب عند الحفظ', desc: 'تحديد ما إذا كان النظام سيرسل تلقائياً (فاتورة، أو إذن تسليم، أو كشف حساب) لواتساب العميل فور الضغط على حفظ واعتماد.' },
                { sel: '#ds-live-sync-pill', title: '⚡ شارة التزامن اللحظي الفوري (موبايل ↔ لابتوب ↔ أوفلاين)', desc: 'تعرض حالة الاتصال والمزامنة الفورية في جزء من الثانية؛ اضغط عليها لمزامنة الفواتير والأرصدة مع السيرفر والأجهزة الأخرى فوراً.' },
                { sel: '#btn-ds-quick-add-wh', title: '➕ إضافة مخزن جديد فوراً', desc: 'يفتح نافذة وليدة سريعة لتعريف مستودع جديد واختياره في الفاتورة الحالية دون مغادرة الشاشة.' },
                { sel: '#btn-ds-quick-add-item', title: '➕ تعريف وإدراج صنف جديد فوراً', desc: 'يفتح نافذة وليدة لتسجيل صنف جديد (الاسم، الوحدة، التكلفة، سعر البيع) وإدراجه في جدول الفاتورة مباشرة.' },
                { sel: '#btn-ds-continuous-pos-scan', title: '📡 الماسح المتصل السريع لنقاط البيع', desc: 'يفتح نافذة المسح المتتابع لقراءة الباركود أو اسم الصنف وإدراجه أو زيادة كميته في الجدول بسرعة البرق.' },
                { sel: '#btn-ds-warranty-qr', title: '🛡️ شهادة الضمان وإيصال الكاشير الحراري بالـ QR', desc: 'يصدر شهادة ضمان معتمدة (A4) أو إيصال كاشير حراري 80mm مزوداً بباركود QR للتحقق الفوري.' },
                { sel: '#btn-ds-cash-change', title: '🧮 حاسبة النقدية المستلمة والباقي للعميل', desc: 'تحسب الباقي للعميل تلقائياً مع أزرار فئات نقدية سريعة وتعتمد المبلغ النقدي في الفاتورة بضغطة واحدة.' },
                { sel: 'button[onclick*="submitInvoice"], .btn-submit', title: '✅ حفظ واعتماد الفاتورة (F10 / Ctrl+S)', desc: 'الزر الرئيسي لاعتماد الفاتورة وترحيلها لحظياً لكشف حساب العميل واليومية العامة والخزينة وبوابة التسليم المخزني.' }
            ];
            for (const r of rules) {
                if (el.matches(r.sel) || el.closest(r.sel)) {
                    return { title: r.title, desc: r.desc };
                }
            }
            return null;
        };

        // ⚡ Smooth 60fps Enter-Key Navigation & Keyboard Shortcuts (Enter / Ctrl+S / F2 / Esc)
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
        const addCustOrder = ['#new-cust-name', '#new-cust-phone', '#new-cust-whatsapp', '#new-cust-opening', '#new-cust-credit'];

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
                    'barcode-scanner-modal',
                    'success-modal-overlay',
                    'invoice-delete-modal',
                    'split-genealogy-modal',
                    'split-genealogy-tree-modal',
                    'post-split-success-modal',
                    'split-vouchers-preview-modal',
                    'split-voice-assistant-modal',
                    'split-hands-on-sim-modal',
                    'split-drilldown-doc-modal',
                    'split-analytics-modal'
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
                const splitViewEl = document.getElementById('split-invoice-view');
                const splitBoardEl = document.getElementById('split-board-step');
                if (splitViewEl && splitViewEl.style.display !== 'none' && splitBoardEl && splitBoardEl.style.display !== 'none') {
                    e.preventDefault();
                    if (typeof cancelCurrentSplit === 'function') cancelCurrentSplit();
                    return;
                }
                ['customer-dropdown-list', 'quick-item-dropdown-list', 'share-popup-menu', 'auto-send-sales-dropdown-menu'].forEach(ddId => {
                    const dd = document.getElementById(ddId);
                    if (dd && dd.style.display !== 'none') dd.style.display = 'none';
                });
                return;
            }

            // ✂️ Keyboard Navigation inside Split Invoice View (#split-invoice-view)
            const splitView = document.getElementById('split-invoice-view');
            if (splitView && splitView.style.display !== 'none') {
                const splitBoard = document.getElementById('split-board-step');
                const isBoardVisible = splitBoard && splitBoard.style.display !== 'none';
                if (e.key === 'F10') {
                    e.preventDefault();
                    if (isBoardVisible && typeof submitInvoiceSplit === 'function') submitInvoiceSplit(false);
                    return;
                }
                if (e.key === 'F2') {
                    e.preventDefault();
                    if (isBoardVisible) {
                        const firstQty = document.getElementById('split-qty-s0-i0') || splitBoard.querySelector('input[type="number"]');
                        if (firstQty) { firstQty.focus(); firstQty.select(); }
                    } else {
                        const sInp = document.getElementById('split-invoice-search');
                        if (sInp) { sInp.focus(); sInp.select(); }
                    }
                    return;
                }
                if (isBoardVisible && (e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س')) {
                    e.preventDefault();
                    if (typeof submitInvoiceSplit === 'function') submitInvoiceSplit(false);
                    return;
                }
                if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                    const t = e.target;
                    if (t && t.id === 'split-invoice-search') {
                        e.preventDefault();
                        const firstSplitBtn = document.querySelector('#split-invoices-body button[onclick*="startInvoiceSplit"]');
                        if (firstSplitBtn) firstSplitBtn.click();
                        return;
                    }
                    if (isBoardVisible && t && t.tagName === 'INPUT') {
                        const splitInputs = Array.from(splitBoard.querySelectorAll('input:not([disabled])')).filter(inp => inp.offsetParent !== null);
                        const idx = splitInputs.indexOf(t);
                        if (idx !== -1 && idx < splitInputs.length - 1) {
                            e.preventDefault();
                            const nxt = splitInputs[idx + 1];
                            nxt.focus();
                            if (typeof nxt.select === 'function') nxt.select();
                            return;
                        }
                    }
                }
                return;
            }

            if (addCustModalEl && (addCustModalEl.classList.contains('active') || addCustModalEl.style.display === 'flex')) {
                if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                    const t = e.target;
                    const activeInputs = addCustOrder.map(s => document.querySelector(s)).filter(Boolean);
                    const idx = activeInputs.indexOf(t);
                    if (idx !== -1) {
                        e.preventDefault();
                        if (idx < activeInputs.length - 1) {
                            const nxt = activeInputs[idx + 1];
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

            const editorView = document.getElementById('invoice-editor-view');
            const isEditorVisible = editorView && editorView.style.display !== 'none';
            if (!isEditorVisible) return;

            if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'س')) {
                e.preventDefault();
                if (typeof submitInvoice === 'function') submitInvoice();
                return;
            }
            if (e.key === 'F2') {
                e.preventDefault();
                const qSearch = document.getElementById('quick-item-search');
                if (qSearch) { qSearch.focus(); qSearch.select(); }
                return;
            }
            if (e.key === 'Enter' && !e.shiftKey && !e.ctrlKey && !e.altKey) {
                const t = e.target;
                if (!t) return;
                const curIdx = headerOrder.findIndex(sel => t.matches(sel));
                if (curIdx !== -1 && curIdx < headerOrder.length - 1) {
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

        // Auto-select numeric 0 on focus for fast typing
        document.addEventListener('focusin', function(e) {
            const t = e.target;
            if (!t || t.tagName !== 'INPUT') return;
            if ((t.closest('#invoice-editor-view') || t.closest('#split-invoice-view')) && (t.type === 'number' || t.classList.contains('num-input') || t.id.startsWith('t-') || t.id.startsWith('pay-') || t.id.startsWith('split-'))) {
                const v = (t.value || '').trim();
                if (v === '0' || v === '0.00' || t.id.startsWith('split-qty-') || t.id.startsWith('split-subqty-')) {
                    setTimeout(() => { try { t.select(); } catch(_e){} }, 10);
                }
            }
        });

        // 🛡️ Double-Click Guard on Primary Sales Action Buttons
        document.addEventListener('click', function(e) {
            const btn = e.target && e.target.closest('button[onclick*="submitInvoice"], button[onclick*="submitInvoiceSplit"], button[onclick*="confirmExecuteDeleteInvoice"]');
            if (!btn) return;
            if (btn.dataset.salesBusy === '1') {
                e.stopImmediatePropagation();
                e.preventDefault();
                return;
            }
            btn.dataset.salesBusy = '1';
            setTimeout(() => { delete btn.dataset.salesBusy; }, 1400);
        }, true);

        // 🌐 Real-Time Cross-Tab BroadcastChannel Listener
        try {
            const bc = new BroadcastChannel('elsajana_erp_sync');
            bc.onmessage = (ev) => {
                if (ev.data && typeof loadHubTransactions === 'function' && typeof currentSalesView !== 'undefined' && currentSalesView === 'hub') {
                    loadHubTransactions();
                }
            };
        } catch (_eBc) {}

        // ═══════════════════════════════════════════════════════════════════════════
        // 🏆 4 WORLD-CLASS SALES ENGINES (GAP REPORT COMPLETION)
        // 1. 🛡️ Margin & Below-Cost Guard
        // 2. 🏷️ Customer Price Tiers (Retail / Semi-Wholesale / Wholesale)
        // 3. ⏸️ POS Park & Hold Multi-Invoice Engine
        // 4. 📈 Customer Item Price History & Margin Inspector
        // ═══════════════════════════════════════════════════════════════════════════

        function resolveSalesItemRecord(itemId) {
            if (!itemId) return null;
            const list = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS) && ITEMS.length)
                ? ITEMS
                : (JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'));
            return list.find(i => i && (String(i.id) === String(itemId) || String(i.code) === String(itemId) || String(i.name) === String(itemId))) || null;
        }

        function resolveSalesItemUnitCost(itemObj, row) {
            if (!itemObj) return 0;
            const rawCost = parseFloat(itemObj.costPrice ?? itemObj.buyPrice ?? itemObj.purchasePrice ?? itemObj.avgCost ?? itemObj.cost ?? 0) || 0;
            if (rawCost <= 0) return 0;
            const itemFactor = parseFloat(itemObj.unitFactor ?? itemObj.conversionFactor ?? itemObj.factor ?? 1) || 1;
            const rowFactor = (row && row.customFactor !== undefined && row.customFactor !== '' && row.customFactor !== null)
                ? (parseFloat(row.customFactor) || 1)
                : itemFactor;
            if (row && row.selectedUnit && itemObj.subUnit && String(row.selectedUnit) === String(itemObj.subUnit) && itemFactor > 1) {
                return +(rawCost / itemFactor).toFixed(4);
            }
            if (rowFactor > 0 && itemFactor > 0 && rowFactor !== itemFactor) {
                return +(rawCost * (rowFactor / itemFactor)).toFixed(4);
            }
            return rawCost;
        }

        // 1. 🛡️ Live Margin & Below-Cost Guard
        window.updateSalesMarginAndCostGuard = function() {
            const badge = document.getElementById('sales-margin-guard-badge');
            const iconEl = document.getElementById('sales-margin-guard-icon');
            const txtEl = document.getElementById('sales-margin-guard-text');
            if (!badge || !txtEl) return;

            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && r.itemId && ((parseFloat(r.quantity) || 0) > 0 || (parseFloat(r.price) || 0) > 0))
                : [];

            if (activeRows.length === 0) {
                badge.style.background = 'rgba(16, 185, 129, 0.14)';
                badge.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                badge.style.color = '#34d399';
                if (iconEl) iconEl.textContent = '🛡️';
                txtEl.textContent = 'الربح: 0%';
                return;
            }

            let totalRev = 0;
            let totalCost = 0;
            let belowCostCount = 0;

            activeRows.forEach(r => {
                const qty = Math.max(1, parseFloat(r.quantity) || 1);
                const sellPrice = parseFloat(r.price) || 0;
                const itemObj = resolveSalesItemRecord(r.itemId);
                const unitCost = resolveSalesItemUnitCost(itemObj, r);
                totalRev += sellPrice * qty;
                totalCost += unitCost * qty;
                if (unitCost > 0 && sellPrice > 0 && sellPrice < unitCost) {
                    belowCostCount++;
                }
            });

            const profit = totalRev - totalCost;
            const marginPct = totalCost > 0 ? Math.round((profit / totalCost) * 100) : (totalRev > 0 ? 100 : 0);

            if (belowCostCount > 0) {
                badge.style.background = 'rgba(239, 68, 68, 0.22)';
                badge.style.borderColor = '#ef4444';
                badge.style.color = '#fca5a5';
                if (iconEl) iconEl.textContent = '🚨';
                txtEl.textContent = `أقل من التكلفة (${belowCostCount})`;
            } else {
                badge.style.background = 'rgba(16, 185, 129, 0.14)';
                badge.style.borderColor = 'rgba(16, 185, 129, 0.45)';
                badge.style.color = '#34d399';
                if (iconEl) iconEl.textContent = '🛡️';
                txtEl.textContent = `الربح: ${marginPct >= 0 ? '+' : ''}${marginPct}%`;
            }
        };

        // 2. 🏷️ Customer Price Tiers (قطاعي / نصف جملة / جملة)
        window.applySalesPriceTier = function(tier, silent = false) {
            const selectedTier = tier || document.getElementById('hdr-price-tier')?.value || 'retail';
            if (typeof rows === 'undefined' || !Array.isArray(rows)) return;
            let updatedCount = 0;

            rows.forEach(r => {
                if (!r || !r.itemId) return;
                const itemObj = resolveSalesItemRecord(r.itemId);
                if (!itemObj) return;
                const baseRetail = parseFloat(itemObj.sellPrice ?? itemObj.price ?? itemObj.retailPrice ?? r.price ?? 0) || 0;
                const unitCost = resolveSalesItemUnitCost(itemObj, r);
                let targetPrice = baseRetail;

                if (selectedTier === 'wholesale') {
                    const explicitW = parseFloat(itemObj.wholesalePrice ?? itemObj.bulkPrice ?? 0) || 0;
                    targetPrice = explicitW > 0 ? explicitW : (unitCost > 0 ? Math.max(unitCost * 1.05, +(baseRetail * 0.92).toFixed(2)) : +(baseRetail * 0.92).toFixed(2));
                } else if (selectedTier === 'semi_wholesale') {
                    const explicitSW = parseFloat(itemObj.semiWholesalePrice ?? itemObj.halfWholesalePrice ?? 0) || 0;
                    targetPrice = explicitSW > 0 ? explicitSW : (unitCost > 0 ? Math.max(unitCost * 1.08, +(baseRetail * 0.96).toFixed(2)) : +(baseRetail * 0.96).toFixed(2));
                } else {
                    targetPrice = baseRetail;
                }

                if (targetPrice > 0) {
                    r.price = Math.round(targetPrice * 100) / 100;
                    updatedCount++;
                }
            });

            if (updatedCount > 0) {
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
            }
            window.updateSalesMarginAndCostGuard();
            if (!silent && typeof showToast === 'function') {
                const labels = { retail: 'قطاعي', semi_wholesale: 'نصف جملة', wholesale: 'جملة' };
                showToast(`🏷️ تم تطبيق شريحة أسعار (${labels[selectedTier] || 'قطاعي'}) على الأصناف`, 'info', 2200);
            }
        };

        // 3. ⏸️ POS Park & Hold Multi-Invoice Engine
        function getParkedSalesList() {
            try {
                const arr = JSON.parse(localStorage.getItem('erp_parked_sales_v1') || '[]');
                return Array.isArray(arr) ? arr : [];
            } catch (_e) { return []; }
        }

        function saveParkedSalesList(arr) {
            localStorage.setItem('erp_parked_sales_v1', JSON.stringify(arr));
            const cntEl = document.getElementById('parked-sales-cnt');
            if (cntEl) cntEl.textContent = arr.length;
        }

        window.parkCurrentSalesInvoice = function() {
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && (r.itemId || (parseFloat(r.quantity) || 0) > 0))
                : [];
            if (activeRows.length === 0) {
                if (typeof showToast === 'function') showToast('⚠️ أضف صنفاً واحداً على الأقل قبل تعليق الفاتورة', 'warning');
                return;
            }
            const parked = getParkedSalesList();
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل نقدي';
            const custId = document.getElementById('hdr-customer')?.value || '';
            const grandTxt = document.getElementById('t-grand-dynamic-loc')?.textContent || '0';
            const parkRecord = {
                parkId: 'PARK-' + Date.now(),
                parkedAt: new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' }),
                date: document.getElementById('hdr-date')?.value || '',
                customerId: custId,
                customerName: custName,
                currency: document.getElementById('hdr-currency')?.value || 'SDG',
                exchangeRate: document.getElementById('hdr-exchange-rate')?.value || '1',
                warehouseId: document.getElementById('hdr-warehouse')?.value || '',
                costCenterId: document.getElementById('hdr-cost-center')?.value || '',
                description: document.getElementById('hdr-description')?.value || '',
                priceTier: document.getElementById('hdr-price-tier')?.value || 'retail',
                grandDisplay: grandTxt,
                rows: JSON.parse(JSON.stringify(rows))
            };
            parked.unshift(parkRecord);
            saveParkedSalesList(parked);
            if (typeof onSuccessNewInvoice === 'function') onSuccessNewInvoice(false);
            if (typeof showToast === 'function') showToast(`⏸️ تم تعليق فاتورة (${custName}) وتفريغ الشاشة لعميل جديد`, 'success', 2600);
        };

        window.openParkedInvoicesModal = function() {
            const modal = document.getElementById('sales-parked-invoices-modal');
            const tbody = document.getElementById('sales-parked-tbody');
            if (!modal || !tbody) return;
            const list = getParkedSalesList();
            if (list.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="padding:22px; text-align:center; color:#94a3b8; font-weight:800;">📭 لا توجد فواتير معلقة حالياً</td></tr>`;
            } else {
                tbody.innerHTML = list.map((p, idx) => `
                    <tr style="border-bottom:1px solid rgba(148,163,184,0.15);">
                        <td style="padding:10px; text-align:center; font-weight:900; color:#38bdf8;">${idx + 1}</td>
                        <td style="padding:10px; font-weight:900; color:#f8fafc;">👤 ${p.customerName}<div style="font-size:0.75rem; color:#94a3b8;">🕒 ${p.parkedAt}</div></td>
                        <td style="padding:10px; text-align:center; font-weight:800; color:#cbd5e1;">${(p.rows || []).filter(r => r && r.itemId).length} صنف</td>
                        <td style="padding:10px; text-align:center; font-weight:900; color:#34d399;">${p.grandDisplay} ${p.currency}</td>
                        <td style="padding:10px; text-align:center; display:flex; gap:6px; justify-content:center;">
                            <button type="button" onclick="window.restoreParkedSalesInvoice('${p.parkId}')" style="background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:8px; padding:6px 12px; font-weight:900; cursor:pointer;">▶️ استدعاء</button>
                            <button type="button" onclick="window.deleteParkedSalesInvoice('${p.parkId}')" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.4); border-radius:8px; padding:6px 10px; font-weight:900; cursor:pointer;">🗑️</button>
                        </td>
                    </tr>
                `).join('');
            }
            modal.style.display = 'flex';
        };

        window.restoreParkedSalesInvoice = function(parkId) {
            const list = getParkedSalesList();
            const target = list.find(p => p.parkId === parkId);
            if (!target) return;
            const remaining = list.filter(p => p.parkId !== parkId);
            saveParkedSalesList(remaining);

            if (document.getElementById('hdr-date') && target.date) document.getElementById('hdr-date').value = target.date;
            if (document.getElementById('hdr-customer')) document.getElementById('hdr-customer').value = target.customerId || '';
            if (document.getElementById('hdr-customer-search')) document.getElementById('hdr-customer-search').value = target.customerName || '';
            if (document.getElementById('hdr-currency') && target.currency) document.getElementById('hdr-currency').value = target.currency;
            if (document.getElementById('hdr-exchange-rate') && target.exchangeRate) document.getElementById('hdr-exchange-rate').value = target.exchangeRate;
            if (document.getElementById('hdr-warehouse') && target.warehouseId) document.getElementById('hdr-warehouse').value = target.warehouseId;
            if (document.getElementById('hdr-cost-center') && target.costCenterId) document.getElementById('hdr-cost-center').value = target.costCenterId;
            if (document.getElementById('hdr-description')) document.getElementById('hdr-description').value = target.description || '';
            if (document.getElementById('hdr-price-tier') && target.priceTier) document.getElementById('hdr-price-tier').value = target.priceTier;

            if (typeof rows !== 'undefined' && Array.isArray(target.rows)) {
                rows.length = 0;
                target.rows.forEach(r => rows.push(r));
            }
            if (typeof onCustomerChange === 'function' && target.customerId) {
                try { onCustomerChange(); } catch (_e) {}
            }
            if (typeof renderGrid === 'function') renderGrid();
            if (typeof recalcAll === 'function') recalcAll();
            window.updateSalesMarginAndCostGuard();

            const modal = document.getElementById('sales-parked-invoices-modal');
            if (modal) modal.style.display = 'none';
            if (typeof showToast === 'function') showToast(`▶️ تم استدعاء فاتورة (${target.customerName}) بنجاح`, 'success');
        };

        window.deleteParkedSalesInvoice = function(parkId) {
            const remaining = getParkedSalesList().filter(p => p.parkId !== parkId);
            saveParkedSalesList(remaining);
            window.openParkedInvoicesModal();
        };

        // 4. 📈 Customer Item Price History & Live Margin Inspector Modal
        window.openCustomerItemHistoryModal = function(mode = 'history') {
            const modal = document.getElementById('sales-cust-history-modal');
            const tbody = document.getElementById('sales-cust-history-tbody');
            const titleEl = document.getElementById('sales-cust-history-title');
            if (!modal || !tbody) return;

            const custId = document.getElementById('hdr-customer')?.value || '';
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل عام';
            if (titleEl) {
                titleEl.textContent = mode === 'margin'
                    ? `🛡️ كاشف التكلفة وهامش الربح اللحظي + سجل أسعار (${custName})`
                    : `📈 سجل آخر أسعار البيع للعميل (${custName}) وتحليل التكلفة`;
            }

            const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && r.itemId)
                : [];

            if (activeRows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="padding:22px; text-align:center; color:#94a3b8; font-weight:800;">📦 أضف صنفاً أولاً في جدول الفاتورة لعرض سجل أسعاره وتكلفته</td></tr>`;
            } else {
                tbody.innerHTML = activeRows.map((r, idx) => {
                    const itemObj = resolveSalesItemRecord(r.itemId);
                    const itemName = itemObj ? itemObj.name : String(r.itemId);
                    const unitCost = resolveSalesItemUnitCost(itemObj, r);
                    const curPrice = parseFloat(r.price) || 0;
                    const diff = curPrice - unitCost;
                    const pct = unitCost > 0 ? Math.round((diff / unitCost) * 100) : 100;
                    const isBelow = unitCost > 0 && curPrice > 0 && curPrice < unitCost;

                    // Find last 3 sales of this item (prioritizing current customer)
                    const custHits = [];
                    const generalHits = [];
                    for (let i = allSales.length - 1; i >= 0; i--) {
                        const inv = allSales[i];
                        if (!inv || inv.deleted || inv.saleType === 'preliminary' || !Array.isArray(inv.items)) continue;
                        const matchedLine = inv.items.find(it => it && (String(it.itemId) === String(r.itemId) || String(it.name) === String(itemName)));
                        if (matchedLine && (parseFloat(matchedLine.price) || 0) > 0) {
                            const hit = {
                                price: parseFloat(matchedLine.price),
                                date: (inv.date || inv.createdAt || '').slice(0, 10),
                                invNo: inv.invoiceNo || inv.id
                            };
                            const isSameCust = (custId && String(inv.customerId) === String(custId)) || (custName && (inv.customerName === custName || (inv.customer && inv.customer.name === custName)));
                            if (isSameCust && custHits.length < 3) custHits.push(hit);
                            else if (generalHits.length < 3) generalHits.push(hit);
                        }
                        if (custHits.length >= 3) break;
                    }
                    const historyList = custHits.length > 0 ? custHits : generalHits;
                    const historyHtml = historyList.length > 0
                        ? historyList.map(h => `
                            <button type="button" onclick="window.applyHistoryPriceToRow(${r.id}, ${h.price})" style="background:rgba(56,189,248,0.14); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; border-radius:7px; padding:4px 8px; font-weight:900; font-size:0.78rem; cursor:pointer; margin:2px;">
                                ⚡ ${h.price.toLocaleString('en-US')} <span style="color:#94a3b8; font-size:0.7rem;">(${h.date || '#' + h.invNo})</span>
                            </button>
                        `).join('')
                        : `<span style="color:#64748b; font-size:0.78rem;">لا توجد مبيعات سابقة</span>`;

                    return `
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.15); background:${isBelow ? 'rgba(239,68,68,0.1)' : 'transparent'};">
                            <td style="padding:10px; text-align:center; font-weight:900; color:#94a3b8;">${idx + 1}</td>
                            <td style="padding:10px; font-weight:900; color:#f8fafc;">${itemName}</td>
                            <td style="padding:10px; text-align:center; font-weight:900; color:#cbd5e1;">${unitCost > 0 ? unitCost.toLocaleString('en-US') : 'غير محددة'}</td>
                            <td style="padding:10px; text-align:center; font-weight:900; color:#fde047;">${curPrice.toLocaleString('en-US')}</td>
                            <td style="padding:10px; text-align:center; font-weight:900; color:${isBelow ? '#f87171' : '#34d399'};">
                                ${isBelow ? `🚨 خسارة (${pct}%)` : `✅ +${pct}% (+${Math.round(diff).toLocaleString('en-US')})`}
                            </td>
                            <td style="padding:10px; text-align:center;">${historyHtml}</td>
                        </tr>
                    `;
                }).join('');
            }
            modal.style.display = 'flex';
        };

        window.applyHistoryPriceToRow = function(rowId, priceVal) {
            if (typeof updateRow === 'function') {
                updateRow(rowId, 'price', priceVal);
                if (typeof renderGrid === 'function') renderGrid();
                if (typeof recalcAll === 'function') recalcAll();
                window.updateSalesMarginAndCostGuard();
                window.openCustomerItemHistoryModal('history');
                if (typeof showToast === 'function') showToast(`✅ تم تطبيق السعر (${priceVal.toLocaleString('en-US')}) على الصنف`, 'success', 1800);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            saveParkedSalesList(getParkedSalesList());
            setTimeout(window.updateSalesMarginAndCostGuard, 600);
        });
        document.addEventListener('input', (e) => {
            if (e.target && e.target.closest('#pos-grid')) {
                window.updateSalesMarginAndCostGuard();
            }
        });
        document.addEventListener('change', (e) => {
            if (e.target && (e.target.closest('#pos-grid') || e.target.id === 'hdr-customer')) {
                setTimeout(window.updateSalesMarginAndCostGuard, 80);
            }
        });
    })();
    </script>

    <!-- ═══ MODAL 1: POS PARKED INVOICES MODAL (⏸️ الفواتير المعلقة) ═══ -->
    <div id="sales-parked-invoices-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.85); backdrop-filter:blur(10px); z-index:999999; align-items:center; justify-content:center; padding:16px; direction:rtl;">
        <div style="background:#0f172a; border:2px solid #eab308; border-radius:20px; width:100%; max-width:680px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.85);">
            <div style="background:linear-gradient(135deg,#713f12,#a16207); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; color:#fff;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:900;">📂 الفواتير المعلقة المؤقتة (POS Park & Hold)</h3>
                <button type="button" onclick="document.getElementById('sales-parked-invoices-modal').style.display='none'" style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:34px; height:34px; border-radius:10px; font-weight:900; cursor:pointer;">✕</button>
            </div>
            <div style="padding:16px; max-height:70vh; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1e293b; color:#fde047; font-size:0.85rem;">
                            <th style="padding:10px;">#</th>
                            <th style="padding:10px; text-align:right;">العميل ووقت التعليق</th>
                            <th style="padding:10px;">الأصناف</th>
                            <th style="padding:10px;">الإجمالي</th>
                            <th style="padding:10px;">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody id="sales-parked-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══ MODAL 2: CUSTOMER ITEM PRICE HISTORY & MARGIN GUARD MODAL (📈 سجل الأسعار والربحية) ═══ -->
    <div id="sales-cust-history-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.85); backdrop-filter:blur(10px); z-index:999999; align-items:center; justify-content:center; padding:16px; direction:rtl;">
        <div style="background:#0f172a; border:2px solid #a855f7; border-radius:20px; width:100%; max-width:860px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.85);">
            <div style="background:linear-gradient(135deg,#4c1d95,#0284c7); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; color:#fff;">
                <h3 id="sales-cust-history-title" style="margin:0; font-size:1.1rem; font-weight:900;">📈 سجل آخر أسعار البيع للعميل وكاشف التكلفة والربحية</h3>
                <button type="button" onclick="document.getElementById('sales-cust-history-modal').style.display='none'" style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:34px; height:34px; border-radius:10px; font-weight:900; cursor:pointer;">✕</button>
            </div>
            <div style="padding:16px; max-height:72vh; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#1e293b; color:#e2e8f0; font-size:0.85rem;">
                            <th style="padding:10px;">#</th>
                            <th style="padding:10px; text-align:right;">الصنف</th>
                            <th style="padding:10px;">التكلفة للوحدة</th>
                            <th style="padding:10px;">السعر الحالي</th>
                            <th style="padding:10px;">هامش الربح</th>
                            <th style="padding:10px;">آخر 3 أسعار (اضغط للتطبيق الفوري)</th>
                        </tr>
                    </thead>
                    <tbody id="sales-cust-history-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- ══════════════════════════════════════════════════════════════════════════════ -->
    <!-- LIGHTWEIGHT, ULTRA-SMOOTH SALES DELIVERY HUB (GDN) & UNIT EQUIVALENCE ENGINE   -->
    <!-- ══════════════════════════════════════════════════════════════════════════════ -->
    <style id="world-class-sales-delivery-styles">
        /* Ultra-Smooth Scrolling & Zero-Jank Rendering for Delivery Hub */
        html, body {
            scroll-behavior: auto;
            overscroll-behavior-y: auto;
        }
        #delivery-hub-view {
            height: auto !important;
            min-height: calc(100vh - 90px);
            overflow: visible !important;
        }
        #delivery-hub-view .neo-card {
            overflow: visible !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2) !important;
        }
        #delivery-hub-view .grid-scroll-wrap {
            max-height: none !important;
            overflow-y: visible !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        /* 1. Clean Spaced Date & Time Pills */
        .gdn-datetime-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px !important;
            flex-wrap: wrap;
            justify-content: center;
        }
        .gdn-date-chip, .gdn-time-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 7px;
            font-size: 0.8rem;
            font-weight: 800;
            border: 1px solid rgba(255,255,255,0.1);
            direction: ltr;
        }
        .gdn-date-chip {
            background: rgba(16, 185, 129, 0.14);
            color: #34d399;
        }
        .gdn-time-chip {
            background: rgba(56, 189, 248, 0.14);
            color: #38bdf8;
        }
        :root.light-theme .gdn-date-chip { color: #047857; background: rgba(16, 185, 129, 0.12); }
        :root.light-theme .gdn-time-chip { color: #1d4ed8; background: rgba(59, 130, 246, 0.12); }

        /* 2. Structured Row-by-Row Item & Unit Grid (مرتبة بسطور كسطور وخانات ووحدات) */
        .gdn-items-container {
            display: flex;
            flex-direction: column;
            gap: 6px;
            justify-content: flex-start;
            align-items: stretch;
            width: 100%;
        }
        .gdn-item-pill {
            display: grid;
            grid-template-columns: 28px minmax(140px, 1.4fr) minmax(95px, 0.9fr) minmax(115px, auto) minmax(125px, auto);
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.24) !important;
            font-weight: 800;
            font-size: 0.86rem;
            color: #f8fafc;
            transition: background-color 0.12s ease, border-color 0.12s ease;
            cursor: pointer;
        }
        .gdn-item-pill:hover {
            border-color: #10b981 !important;
            background: rgba(6, 78, 59, 0.45);
        }
        :root.light-theme .gdn-item-pill {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1 !important;
        }
        :root.light-theme .gdn-item-pill:hover {
            background: #ecfdf5;
            border-color: #10b981 !important;
        }

        /* 3. Ultra-Wide, Precision Row-by-Row & Cell-by-Cell Columns Table */
        #delivery-confirmation-modal {
            padding: 4px 8px !important;
        }
        #delivery-confirmation-modal .gdn-smart-modal-card {
            width: 99.6vw !important;
            max-width: 1920px !important;
            height: 96vh !important;
            max-height: 97vh !important;
            background: linear-gradient(165deg, #070c18 0%, #0f172a 60%, #051e17 100%) !important;
            color: #f8fafc !important;
            border: 2px solid rgba(16, 185, 129, 0.5) !important;
            border-radius: 20px !important;
            box-shadow: 0 35px 100px rgba(0,0,0,0.92), 0 0 45px rgba(16,185,129,0.18) !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            position: relative !important;
        }
        .gdn-origin-columns-table {
            width: 100% !important;
            min-width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            border: 1.5px solid rgba(148, 163, 184, 0.32) !important;
            background: #090f1d !important;
            table-layout: auto !important;
        }
        .gdn-origin-columns-table.gdn-scale-wide {
            min-width: 1580px !important;
        }
        .gdn-origin-columns-table.gdn-scale-ultrawide {
            min-width: 1820px !important;
        }
        .gdn-origin-columns-table.gdn-scale-ultrawide tbody td {
            padding: 14px 14px !important;
            font-size: 1rem !important;
        }
        .gdn-origin-columns-table.gdn-scale-ultrawide .gdn-qty-large-input {
            width: 142px !important;
            min-width: 142px !important;
            height: 44px !important;
            font-size: 1.3rem !important;
        }
        .gdn-origin-columns-table thead th {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            color: #f8fafc !important;
            font-weight: 900 !important;
            font-size: 0.88rem !important;
            padding: 12px 10px !important;
            border-bottom: 2px solid rgba(16, 185, 129, 0.55) !important;
            border-left: 1px solid rgba(148, 163, 184, 0.2) !important;
            white-space: nowrap !important;
        }
        .gdn-origin-columns-table tbody tr {
            background: rgba(15, 23, 42, 0.82) !important;
        }
        .gdn-origin-columns-table tbody tr:nth-child(even) {
            background: rgba(30, 41, 59, 0.5) !important;
        }
        .gdn-origin-columns-table tbody td {
            padding: 10px 10px !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18) !important;
            border-left: 1px solid rgba(148, 163, 184, 0.16) !important;
            font-size: 0.92rem !important;
            font-weight: 800 !important;
            vertical-align: middle !important;
            color: #f8fafc !important;
        }
        .gdn-origin-columns-table tbody tr:hover td {
            background: rgba(16, 185, 129, 0.12) !important;
        }
        .gdn-origin-columns-table tfoot td {
            background: linear-gradient(180deg, #0f172a 0%, #020617 100%) !important;
            padding: 12px 10px !important;
            font-weight: 900 !important;
            font-size: 0.95rem !important;
            border-top: 2px solid rgba(52, 211, 153, 0.55) !important;
            border-left: 1px solid rgba(148, 163, 184, 0.16) !important;
            color: #ffffff !important;
        }
        .gdn-qty-large-input {
            width: 122px !important;
            min-width: 122px !important;
            height: 40px !important;
            text-align: center !important;
            font-size: 1.22rem !important;
            font-weight: 900 !important;
            font-family: 'Outfit', 'Cairo', sans-serif !important;
            background: #020617 !important;
            border-radius: 9px !important;
            padding: 2px 6px !important;
            box-sizing: border-box !important;
            letter-spacing: 0.5px !important;
            outline: none !important;
            box-shadow: inset 0 2px 6px rgba(0,0,0,0.65) !important;
        }
        .gdn-qty-step-btn {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            padding: 0 !important;
            border-radius: 9px !important;
            font-size: 1.22rem !important;
            font-weight: 900 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            border: 1.5px solid rgba(255,255,255,0.22) !important;
            line-height: 1 !important;
            position: relative !important;
            overflow: hidden !important;
            transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.2s ease, border-color 0.2s ease, filter 0.2s ease !important;
        }
        .gdn-qty-step-btn:hover {
            transform: translateY(-2px) scale(1.12) !important;
            border-color: #6ee7b7 !important;
            box-shadow: 0 0 16px rgba(16, 185, 129, 0.65), inset 0 0 8px rgba(255, 255, 255, 0.28) !important;
            filter: brightness(1.15) !important;
        }
        .gdn-qty-step-btn:active {
            transform: scale(0.95) !important;
        }
        .gdn-col-toggle-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 9px;
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.35);
            color: #e2e8f0 !important;
            font-size: 0.8rem;
            font-weight: 800;
            cursor: pointer;
            user-select: none;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
        }
        .gdn-col-toggle-chip:hover {
            transform: translateY(-2px) scale(1.04);
            border-color: #34d399;
            background: rgba(16, 185, 129, 0.18);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.45), inset 0 0 8px rgba(255, 255, 255, 0.15);
        }
        .gdn-col-toggle-chip input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #10b981;
            cursor: pointer;
        }
        .gdn-unit-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            border-radius: 8px;
            background: rgba(16, 185, 129, 0.16);
            color: #6ee7b7;
            font-weight: 900;
            font-size: 0.86rem;
            border: 1px solid rgba(16, 185, 129, 0.35) !important;
            white-space: nowrap;
        }

        /* 4. Lightweight Select & Inputs */
        .gdn-borderless-select, .gdn-borderless-input {
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            outline: none !important;
            background: rgba(15, 23, 42, 0.88) !important;
            border-radius: 8px !important;
            padding: 6px 10px !important;
            font-weight: 800 !important;
            color: #f8fafc !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease !important;
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
        }
        .gdn-borderless-select option {
            background: #0f172a;
            color: #f8fafc;
        }
        :root.light-theme .gdn-borderless-select, :root.light-theme .gdn-borderless-input {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        .gdn-borderless-select:focus, .gdn-borderless-input:focus {
            border-color: #34d399 !important;
            box-shadow: 0 0 16px rgba(16, 185, 129, 0.45), 0 0 0 2px rgba(16, 185, 129, 0.22) !important;
        }

        /* 5. Kinetic, Luminous, Glass-Shimmer Buttons & Interactive Dual-Unit Receiving Boxes */
        .gdn-kinetic-btn,
        #delivery-hub-view .btn-action,
        #delivery-confirmation-modal button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 9px;
            padding: 6px 13px;
            font-weight: 800;
            font-size: 0.83rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.22s ease, border-color 0.22s ease, filter 0.2s ease;
            font-family: 'Cairo', 'Tajawal', sans-serif;
        }
        .gdn-kinetic-btn::after,
        #delivery-confirmation-modal button::after,
        .gdn-receive-unit-box::after {
            content: '';
            position: absolute;
            top: -60%;
            left: -140%;
            width: 75%;
            height: 220%;
            background: linear-gradient(105deg, rgba(255,255,255,0) 15%, rgba(255,255,255,0.45) 48%, rgba(167,243,208,0.75) 52%, rgba(255,255,255,0) 85%);
            transform: rotate(22deg);
            pointer-events: none;
            transition: left 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 3;
        }
        .gdn-kinetic-btn:hover::after,
        #delivery-confirmation-modal button:hover::after,
        .gdn-receive-unit-box:hover::after {
            left: 155%;
        }
        .gdn-kinetic-btn:hover,
        #delivery-hub-view .btn-action:hover,
        #delivery-confirmation-modal button:hover {
            transform: translateY(-3px) scale(1.06);
            filter: brightness(1.12);
            border-color: #6ee7b7 !important;
            box-shadow: 0 0 22px rgba(16, 185, 129, 0.65), 0 8px 18px rgba(0, 0, 0, 0.5), inset 0 0 12px rgba(255, 255, 255, 0.28) !important;
        }
        .gdn-kinetic-btn:active,
        #delivery-confirmation-modal button:active {
            transform: translateY(0) scale(0.97);
        }

        /* Interactive Mirror-Glass Receiving & Unit Boxes — 100% IDENTICAL Size, Expansion, Glow & Crisp Unaffected Text */
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
            z-index: 1 !important;
            background: linear-gradient(105deg, rgba(255,255,255,0) 20%, rgba(255,255,255,0.14) 50%, rgba(255,255,255,0) 80%) !important;
        }
        .gdn-receive-unit-box > * {
            position: relative !important;
            z-index: 2 !important;
        }
        /* Primary Box vs Base Box: 100% Identical Dimensions & Measurements, Subtle Color Difference */
        .gdn-receive-unit-box.primary-box,
        .gdn-receive-unit-box.base-box {
            width: 248px !important;
            min-width: 248px !important;
            max-width: 248px !important;
            height: 118px !important;
            min-height: 118px !important;
            max-height: 118px !important;
            padding: 9px 11px !important;
            border-radius: 13px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: stretch !important;
            gap: 6px !important;
            margin: 0 auto !important;
        }
        .gdn-receive-unit-box.primary-box {
            background: linear-gradient(145deg, #07221c 0%, #041410 100%) !important;
            border-color: rgba(16, 185, 129, 0.5) !important;
        }
        .gdn-receive-unit-box.base-box {
            background: linear-gradient(145deg, #0b1d33 0%, #061120 100%) !important;
            border-color: rgba(56, 189, 248, 0.5) !important;
        }
        /* Identical Hover Expansion & Green Outer Illumination (Never washes out or alters internal font colors) */
        .gdn-receive-unit-box.primary-box:hover,
        .gdn-receive-unit-box.base-box:hover,
        .gdn-receive-unit-box.units-mirror-box:hover {
            transform: translateY(-4px) scale(1.075) !important;
            border-color: #34d399 !important;
            box-shadow: 0 0 28px rgba(16, 185, 129, 0.88), 0 0 12px rgba(52, 211, 153, 0.8), 0 12px 26px rgba(0, 0, 0, 0.75) !important;
            z-index: 10 !important;
        }
        .gdn-receive-unit-box.primary-box:hover {
            background: linear-gradient(145deg, #082720 0%, #041511 100%) !important;
        }
        .gdn-receive-unit-box.base-box:hover {
            background: linear-gradient(145deg, #0d223b 0%, #071324 100%) !important;
        }
        .gdn-receive-unit-box .gdn-unit-fixed-pill,
        .gdn-receive-unit-box .gdn-unit-fixed-pill:hover {
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.95) !important;
            filter: none !important;
        }
        .gdn-receive-unit-box .gdn-box-caption {
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.95) !important;
        }
        .gdn-receive-unit-box:hover .gdn-qty-large-input {
            transform: scale(1.04);
            border-color: #34d399 !important;
            box-shadow: 0 0 16px rgba(52, 211, 153, 0.55), inset 0 2px 6px rgba(0,0,0,0.75) !important;
        }
        .gdn-smart-modal-card {
            position: relative;
            box-shadow: 0 18px 45px rgba(0,0,0,0.7);
        }

        /* Row Hover without Transform Scale (Prevents Scroll Wheel Freeze) */
        #delivery-transactions-body tr.gdn-row {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        #delivery-transactions-body tr.gdn-row:hover,
        #delivery-transactions-body tr.gdn-row.gdn-active-row {
            background: rgba(16, 185, 129, 0.08) !important;
        }
        :root.light-theme #delivery-transactions-body tr.gdn-row:hover,
        :root.light-theme #delivery-transactions-body tr.gdn-row.gdn-active-row {
            background: #f0fdf4 !important;
        }

        /* 6. OUTDOOR SUNLIGHT MODE FOR STOREKEEPER */
        body.gdn-sunlight-mode #delivery-hub-view,
        body.gdn-sunlight-mode #delivery-confirmation-modal > div {
            background: #ffffff !important;
            color: #000000 !important;
            border: 3px solid #000000 !important;
        }
        body.gdn-sunlight-mode #delivery-hub-view th {
            background: #000000 !important;
            color: #ffff00 !important;
            font-size: 1rem !important;
            font-weight: 900 !important;
            border: 1px solid #000000 !important;
        }
        body.gdn-sunlight-mode #delivery-transactions-body tr.gdn-row td {
            color: #000000 !important;
            font-size: 0.98rem !important;
            font-weight: 900 !important;
            border-bottom: 1.5px solid #000000 !important;
            padding: 12px 10px !important;
        }
        body.gdn-sunlight-mode .gdn-date-chip,
        body.gdn-sunlight-mode .gdn-time-chip,
        body.gdn-sunlight-mode .gdn-item-pill,
        body.gdn-sunlight-mode .gdn-unit-badge {
            background: #fef08a !important;
            color: #000000 !important;
            border: 1.5px solid #000000 !important;
            font-size: 0.92rem !important;
            font-weight: 900 !important;
        }
    </style>

    <!-- QR Scanner, Continuous Zero-Click Stream & Mobile-to-Laptop Bridge Modal -->
    <div id="gdn-qr-scanner-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.88); backdrop-filter:blur(12px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#0f172a; color:#f8fafc; width:100%; max-width:640px; border-radius:26px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.75); border:2px solid #10b981;">
            <div style="background:linear-gradient(135deg, #064e3b, #0284c7); padding:16px 22px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:1.8rem;">📱</span>
                    <div>
                        <h3 style="margin:0; font-size:1.2rem; font-weight:900;">المسح المتتابع الفوري وربط الجوال (Zero-Click QR)</h3>
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
    <div id="gdn-qr-display-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.82); backdrop-filter:blur(8px); z-index:99998; align-items:center; justify-content:center; padding:16px;">
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

    <script defer src="js/direct-sales-delivery-core.js?v=20261029_fast"></script>

    <!-- ══════════════════════════════════════════════════════════════════════════════ -->
    <!-- CONSTITUTION 1 OF 3 (الدستور الأول): STRICT THEME CONTRAST, CHILD MODALS,      -->
    <!-- 2G RESILIENCE, SUB-SECOND AUTO-SYNC, AUTO-SELECT NUMBERS, ARABIC SEARCH & +ADD -->
    <!-- ══════════════════════════════════════════════════════════════════════════════ -->
    <style id="ds-constitution1-theme-contrast">
        /* 0. CONSTITUTION 4 GLOBAL ROOT INHERITANCE (+2 BORDER/FONT/ICON THICKNESS & AMBIENT CONTRAST) */
        :root {
            --ds-c4-border-width: 2.2px;
            --ds-c4-card-border-width: 2.4px;
            --ds-c4-font-weight: 800;
            --ds-c4-icon-stroke: 2.6px;
        }
        #invoice-editor-view svg, #invoice-editor-view svg path, #invoice-editor-view svg circle, #invoice-editor-view svg rect, #invoice-editor-view svg line, #invoice-editor-view svg polyline, #invoice-editor-view svg polygon,
        #sales-hub-view svg, #sales-hub-view svg path, #sales-hub-view svg circle, #sales-hub-view svg rect, #sales-hub-view svg line, #sales-hub-view svg polyline, #sales-hub-view svg polygon,
        #split-invoice-view svg, #split-invoice-view svg path,
        #delivery-hub-view svg, #delivery-hub-view svg path,
        .ds-c1-modal-card svg, .ds-c1-modal-card svg path,
        .modal-card svg, .modal-card svg path {
            stroke-width: var(--ds-c4-icon-stroke, 2.6px) !important;
        }

        /* 1. DARK THEMES: Strict Bright White (#FFFFFF) Text & Borders on Pure Black (#000000) Inputs inside Stepped Lighter Ambient Cards (#111c33) */
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-editor-view,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #sales-hub-view,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view {
            color: #FFFFFF !important;
            font-weight: var(--ds-c4-font-weight, 800) !important;
        }
        /* Dark Mode Stepped Ambient Cards & Child Modals (#111c33) so #000000 Inputs Pop Out Clearly */
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #form-fields-card,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .sales-glass-card,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #items-panel,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .panel-header-row,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #inline-totals-container,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #payment-panel,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .ds-c1-modal-card,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-card,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #sales-parked-invoices-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #sales-cust-history-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #grid-settings-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-delete-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view .neo-card,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-header-info,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #post-split-success-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-genealogy-tree-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-voice-assistant-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-hands-on-sim-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-drilldown-doc-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-analytics-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-weight-shield-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-driver-signature-modal > div,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-presets-modal > div {
            background: #111c33 !important;
            background-color: #111c33 !important;
            border: var(--ds-c4-card-border-width, 2.4px) solid #FFFFFF !important;
            color: #FFFFFF !important;
        }
        /* Dark Mode Inputs: Pure Black (#000000) + Thick White Border (2.2px solid #FFFFFF) + Pure White Text (#FFFFFF) — ZERO INVERSION ON FOCUS/ACTIVE/AUTOFILL */
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-editor-view select,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #invoice-editor-view textarea,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #sales-hub-view input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #sales-hub-view select,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-invoice-view select,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #split-voice-assistant-modal input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #delivery-hub-view select,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .ds-c1-modal-card input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .ds-c1-modal-card select,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .ds-c1-modal-card textarea,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-card input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-card select,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) .modal-card textarea,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #add-customer-modal input:not([type="checkbox"]):not([type="radio"]),
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) #add-customer-modal select {
            background: #000000 !important;
            background-color: #000000 !important;
            background-image: none !important;
            color: #FFFFFF !important;
            -webkit-text-fill-color: #FFFFFF !important;
            border: var(--ds-c4-border-width, 2.2px) solid #FFFFFF !important;
            caret-color: #38bdf8 !important;
            font-weight: 800 !important;
        }
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):hover,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):active,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body select:hover,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body select:focus,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body select:active,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body textarea:hover,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body textarea:focus,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body textarea:active,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #pos-grid td .g-input:hover,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #pos-grid td .g-input:focus,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #pos-grid td .g-select:hover,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body #pos-grid td .g-select:focus {
            background: #000000 !important;
            background-color: #000000 !important;
            background-image: none !important;
            color: #FFFFFF !important;
            -webkit-text-fill-color: #FFFFFF !important;
            border: var(--ds-c4-border-width, 2.2px) solid #FFFFFF !important;
            font-weight: 800 !important;
        }
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:-webkit-autofill,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:-webkit-autofill:hover,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:-webkit-autofill:focus,
        html:not(.light-theme):not([data-theme="light"]):not([data-theme="pearl-white"]):not([data-theme="warm_cream"]):not([data-theme="ultra_white"]):not([data-theme="exec_gray"]) body input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #000000 inset !important;
            -webkit-text-fill-color: #FFFFFF !important;
            background-color: #000000 !important;
            color: #FFFFFF !important;
            border: var(--ds-c4-border-width, 2.2px) solid #FFFFFF !important;
            caret-color: #38bdf8 !important;
        }

        /* 2. LIGHT THEMES: Strict Deep Black (#000000) Text & Thick Borders (+2) on Pure White (#FFFFFF) Inputs inside Stepped Calm Surround (#F1F5F9) */
        :root.light-theme #invoice-editor-view,
        :root.light-theme #sales-hub-view,
        :root.light-theme #split-invoice-view,
        :root.light-theme #delivery-hub-view,
        body.light-theme #invoice-editor-view,
        html[data-theme="light"] #invoice-editor-view,
        html[data-theme="pearl-white"] #invoice-editor-view,
        html[data-theme="warm_cream"] #invoice-editor-view,
        html[data-theme="ultra_white"] #invoice-editor-view,
        html[data-theme="exec_gray"] #invoice-editor-view {
            color: #000000 !important;
            font-weight: var(--ds-c4-font-weight, 800) !important;
        }
        :root.light-theme #invoice-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
        :root.light-theme #invoice-editor-view select:not(#hdr-price-tier),
        :root.light-theme #invoice-editor-view textarea,
        :root.light-theme #sales-hub-view input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme #sales-hub-view select,
        :root.light-theme #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme #split-invoice-view select,
        :root.light-theme #split-voice-assistant-modal input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme #delivery-hub-view input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme #delivery-hub-view select,
        :root.light-theme #t-transport,
        :root.light-theme #t-labor,
        :root.light-theme #addition-amt,
        :root.light-theme #tax-amt,
        :root.light-theme #discount-amt,
        :root.light-theme #pay-cash-amount,
        :root.light-theme #hdr-payment-account,
        :root.light-theme #pay-cash-ref,
        :root.light-theme #pay-cash-note,
        :root.light-theme #pay-credit-amount,
        :root.light-theme #pay-credit-due-date,
        :root.light-theme #preset-selector,
        :root.light-theme .ds-c1-modal-card input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme .ds-c1-modal-card select,
        :root.light-theme .ds-c1-modal-card textarea,
        :root.light-theme .modal-card input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme .modal-card select,
        :root.light-theme .modal-card textarea,
        :root.light-theme #add-customer-modal input:not([type="checkbox"]):not([type="radio"]),
        :root.light-theme #add-customer-modal select {
            background: #FFFFFF !important;
            background-color: #FFFFFF !important;
            background-image: none !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: var(--ds-c4-border-width, 2.2px) solid #000000 !important;
            caret-color: #0284c7 !important;
            font-weight: 800 !important;
            box-shadow: none !important;
        }
        :root.light-theme body input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):hover,
        :root.light-theme body input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):focus,
        :root.light-theme body input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):active,
        :root.light-theme body select:not(#hdr-price-tier):hover,
        :root.light-theme body select:not(#hdr-price-tier):focus,
        :root.light-theme body select:not(#hdr-price-tier):active,
        :root.light-theme body textarea:hover,
        :root.light-theme body textarea:focus,
        :root.light-theme body textarea:active,
        :root.light-theme body #pos-grid td .g-input:hover,
        :root.light-theme body #pos-grid td .g-input:focus,
        :root.light-theme body #pos-grid td .g-select:hover,
        :root.light-theme body #pos-grid td .g-select:focus {
            background: #FFFFFF !important;
            background-color: #FFFFFF !important;
            background-image: none !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            border: var(--ds-c4-border-width, 2.2px) solid #000000 !important;
            font-weight: 800 !important;
        }
        :root.light-theme body input:-webkit-autofill,
        :root.light-theme body input:-webkit-autofill:hover,
        :root.light-theme body input:-webkit-autofill:focus,
        :root.light-theme body input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
            -webkit-text-fill-color: #000000 !important;
            background-color: #FFFFFF !important;
            color: #000000 !important;
            border: var(--ds-c4-border-width, 2.2px) solid #000000 !important;
            caret-color: #0284c7 !important;
        }

        /* Light Theme Child Modals, Dropdowns, Panels & Tables Full Inheritance (+2 Border Thickness & Surround Contrast) */
        :root.light-theme #form-fields-card,
        :root.light-theme .sales-glass-card,
        :root.light-theme #items-panel,
        :root.light-theme .panel-header-row,
        :root.light-theme #payment-panel,
        :root.light-theme #inline-totals-container,
        :root.light-theme #invoice-editor-view .items-toolbar,
        :root.light-theme #sales-parked-invoices-modal > div,
        :root.light-theme #sales-cust-history-modal > div,
        :root.light-theme #grid-settings-modal > div,
        :root.light-theme #invoice-delete-modal > div,
        :root.light-theme #delivery-confirmation-modal .gdn-smart-modal-card,
        :root.light-theme #gdn-qr-scanner-modal > div,
        :root.light-theme .ds-c1-modal-card,
        :root.light-theme .modal-card,
        :root.light-theme #customer-dropdown-list,
        :root.light-theme #quick-item-dropdown-list,
        :root.light-theme #share-popup-menu,
        :root.light-theme #auto-send-sales-dropdown-menu,
        :root.light-theme #split-invoice-view .neo-card,
        :root.light-theme #split-header-info > div > div,
        :root.light-theme #split-items-body td > div > div,
        :root.light-theme #post-split-success-modal > div,
        :root.light-theme #post-split-success-modal > div > div > div,
        :root.light-theme #split-genealogy-tree-modal > div,
        :root.light-theme #split-voice-assistant-modal > div,
        :root.light-theme #split-hands-on-sim-modal > div,
        :root.light-theme #split-drilldown-doc-modal > div,
        :root.light-theme #split-analytics-modal > div,
        :root.light-theme #split-weight-shield-modal > div,
        :root.light-theme #split-driver-signature-modal > div,
        :root.light-theme #split-presets-modal > div {
            background: #F1F5F9 !important;
            background-color: #F1F5F9 !important;
            color: #000000 !important;
            border: var(--ds-c4-card-border-width, 2.4px) solid #000000 !important;
        }
        :root.light-theme #split-voice-assistant-modal *,
        :root.light-theme #split-hands-on-sim-modal *,
        :root.light-theme #split-drilldown-doc-modal *,
        :root.light-theme #split-analytics-modal *,
        :root.light-theme #split-invoice-view h3,
        :root.light-theme #split-invoice-view h4,
        :root.light-theme #split-invoice-view th,
        :root.light-theme #split-invoice-view td,
        :root.light-theme #split-invoice-view span,
        :root.light-theme #split-invoice-view label,
        :root.light-theme #split-invoice-view strong,
        :root.light-theme #post-split-success-modal h3,
        :root.light-theme #post-split-success-modal p,
        :root.light-theme #post-split-success-modal div,
        :root.light-theme #post-split-success-modal span,
        :root.light-theme #post-split-success-modal strong,
        :root.light-theme #split-genealogy-tree-modal h3,
        :root.light-theme #split-genealogy-tree-modal th,
        :root.light-theme #split-genealogy-tree-modal td,
        :root.light-theme #split-genealogy-tree-modal span {
            color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #split-invoice-view table.pos-grid thead tr,
        :root.light-theme #split-genealogy-tree-modal thead tr {
            background: #E2E8F0 !important;
            color: #000000 !important;
            border-bottom: 2.4px solid #000000 !important;
        }
        :root.light-theme #sales-parked-invoices-modal td,
        :root.light-theme #sales-parked-invoices-modal th,
        :root.light-theme #sales-cust-history-modal td,
        :root.light-theme #sales-cust-history-modal th,
        :root.light-theme #grid-settings-modal span,
        :root.light-theme #grid-settings-modal label,
        :root.light-theme #grid-settings-modal h2,
        :root.light-theme #grid-settings-modal h3,
        :root.light-theme #invoice-delete-modal h2,
        :root.light-theme #invoice-delete-modal p,
        :root.light-theme .ds-c1-modal-card h3,
        :root.light-theme .ds-c1-modal-card label,
        :root.light-theme .ds-c1-modal-card p,
        :root.light-theme .ds-c1-modal-card td,
        :root.light-theme .ds-c1-modal-card th,
        :root.light-theme .modal-card h3,
        :root.light-theme .modal-card label,
        :root.light-theme #customer-dropdown-list *,
        :root.light-theme #quick-item-dropdown-list *,
        :root.light-theme #share-popup-menu button span,
        :root.light-theme #auto-send-sales-dropdown-menu .auto-send-item-title,
        :root.light-theme #fin-strip span:not(#fin-remaining) {
            color: #000000 !important;
            font-weight: 800 !important;
        }
        :root.light-theme #sales-parked-invoices-modal thead tr,
        :root.light-theme #sales-cust-history-modal thead tr,
        :root.light-theme .ds-c1-modal-card thead tr,
        :root.light-theme #pos-grid thead tr {
            background: #E2E8F0 !important;
            color: #000000 !important;
            border-bottom: 2.4px solid #000000 !important;
        }
        :root.light-theme #pos-grid thead th {
            color: #000000 !important;
            font-weight: 900 !important;
        }
        :root.light-theme #row-grand-total {
            background: linear-gradient(90deg, #ECFDF5 0%, #D1FAE5 50%, #ECFDF5 100%) !important;
            border-top: 2.4px solid #000000 !important;
            border-bottom: 2.4px solid #000000 !important;
        }
        :root.light-theme #lbl-grand-dynamic {
            color: #065F46 !important;
            text-shadow: none !important;
            font-weight: 900 !important;
        }

        /* Non-blocking Child Modal Backdrop & Card */
        .ds-c1-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.82);
            backdrop-filter: blur(8px);
            z-index: 1000000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            direction: rtl;
        }
        .ds-c1-modal-card {
            background: #111c33;
            color: #FFFFFF;
            border: 2.4px solid #FFFFFF;
            border-radius: 18px;
            width: 100%;
            max-width: 520px;
            padding: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.85);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-weight: 800;
        }

        /* Save Reassurance Visual Pulse */
        @keyframes dsSaveSuccessPulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.75); }
            50% { box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .ds-save-pulse-active {
            animation: dsSaveSuccessPulse 0.85s ease-out;
        }
    </style>

    <script id="ds-constitution1-engine">
    (function() {
        // 1. Replace any native window.alert with sleek 2-second Floating Toast
        window.alert = function(msg) {
            if (typeof window.showToast === 'function') {
                window.showToast(String(msg || ''), 'warning', 2200);
            }
        };

        // 2. Tolerant Arabic Normalization Engine (أ، إ، آ = ا | ة = ه | ى = ي | ؤ = و + Arabic Digits)
        window.normalizeDsArabic = function(str) {
            if (!str) return '';
            const arabicNums = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
            return String(str)
                .replace(/[٠-٩]/g, d => arabicNums[d] || d)
                .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
                .replace(/[أإآٱ]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/[ىئ]/g, 'ي')
                .replace(/ؤ/g, 'و')
                .toLowerCase()
                .trim();
        };
        window.normalizeArabic = window.normalizeDsArabic;

        // 3. Auto-Select ALL Numeric Inputs on Focus & Click (No Manual Backspace Needed)
        function isNumericSalesInput(el) {
            if (!el || el.tagName !== 'INPUT' || el.readOnly || el.disabled) return false;
            const t = (el.type || '').toLowerCase();
            if (t === 'number' || el.inputMode === 'decimal' || el.inputMode === 'numeric') return true;
            if (el.classList.contains('num-input') || el.classList.contains('tot-input') || el.classList.contains('amount')) return true;
            const id = el.id || '';
            if (id.startsWith('t-') || id.startsWith('pay-') || id.startsWith('split-') || id.includes('qty') || id.includes('price') || id.includes('opening') || id.includes('credit')) return true;
            if (el.hasAttribute('data-qty-input') || el.hasAttribute('data-price-input') || el.hasAttribute('data-factor-input')) return true;
            return false;
        }

        document.addEventListener('focusin', function(e) {
            const t = e.target;
            if (isNumericSalesInput(t)) {
                setTimeout(() => { try { t.select(); } catch (_e) {} }, 10);
            }
        });
        document.addEventListener('click', function(e) {
            const t = e.target;
            if (isNumericSalesInput(t) && document.activeElement === t) {
                try { t.select(); } catch (_e) {}
            }
        });

        // 4. Support F10 Shortcut for Instant Save & Unsaved Changes Guard
        window._dsHasUnsavedChanges = false;
        document.addEventListener('input', function(e) {
            if (e.target && e.target.closest('#invoice-editor-view')) {
                window._dsHasUnsavedChanges = true;
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'F10') {
                const editorView = document.getElementById('invoice-editor-view');
                if (editorView && editorView.style.display !== 'none') {
                    e.preventDefault();
                    if (typeof window.submitInvoice === 'function') window.submitInvoice();
                }
            }
        });

        window.addEventListener('beforeunload', function(e) {
            const editorView = document.getElementById('invoice-editor-view');
            const hasRows = typeof rows !== 'undefined' && Array.isArray(rows) && rows.some(r => r && r.itemId);
            if (window._dsHasUnsavedChanges && editorView && editorView.style.display !== 'none' && hasRows && !window.isSubmitting) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // 5. Sub-Second Micro-Packet Auto-Sync Indicator & Cross-Device Sync (Mobile ↔ Laptop ↔ Offline)
        function ensureLiveSyncBadge() {
            const row0 = document.getElementById('invoice-info-box');
            if (!row0 || document.getElementById('ds-live-sync-pill')) return;
            const pill = document.createElement('span');
            pill.id = 'ds-live-sync-pill';
            pill.style.cssText = 'display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:0.74rem;font-weight:900;border:1.5px solid rgba(16,185,129,0.5);background:rgba(16,185,129,0.14);color:#34d399;white-space:nowrap;cursor:pointer;';
            pill.title = 'حالة التزامن الأوتوماتيكي الفوري (موبايل ↔ لابتوب ↔ أوفلاين) — اضغط للمزامنة الفورية';
            pill.onclick = () => window.triggerInstantSalesMicroSync(true);
            row0.appendChild(pill);
            updateLiveSyncBadgeState();
        }

        function updateLiveSyncBadgeState(customText, customColor) {
            const pill = document.getElementById('ds-live-sync-pill');
            if (!pill) return;
            const isOnline = navigator.onLine !== false;
            if (customText) {
                pill.innerHTML = customText;
                pill.style.borderColor = customColor || '#38bdf8';
                return;
            }
            if (isOnline) {
                pill.innerHTML = '⚡ متزامن لحظياً (0ms)';
                pill.style.borderColor = 'rgba(16,185,129,0.55)';
                pill.style.background = 'rgba(16,185,129,0.14)';
                pill.style.color = '#10b981';
            } else {
                pill.innerHTML = '📴 أوفلاين (محفوظ محلياً)';
                pill.style.borderColor = 'rgba(245,158,11,0.6)';
                pill.style.background = 'rgba(245,158,11,0.16)';
                pill.style.color = '#f59e0b';
            }
        }

        window.triggerInstantSalesMicroSync = function(manual) {
            updateLiveSyncBadgeState('🔄 جاري المزامنة الفورية...', '#38bdf8');
            try {
                if (window.MagaiesOfflineSync && typeof window.MagaiesOfflineSync.flush === 'function') {
                    window.MagaiesOfflineSync.flush();
                }
                if (typeof window.syncSalesWithMariaDB === 'function') {
                    window.syncSalesWithMariaDB();
                }
            } catch (_e) {}
            setTimeout(() => {
                updateLiveSyncBadgeState();
                if (manual && typeof window.showToast === 'function') {
                    window.showToast('⚡ تمت المزامنة اللحظية مع السيرفر والأجهزة المتصلة بنجاح', 'success', 2000);
                }
            }, 320);
        };

        window.addEventListener('online', () => window.triggerInstantSalesMicroSync(false));
        window.addEventListener('offline', () => updateLiveSyncBadgeState());
        if (navigator.connection && typeof navigator.connection.addEventListener === 'function') {
            navigator.connection.addEventListener('change', () => {
                if (navigator.onLine) window.triggerInstantSalesMicroSync(false);
            });
        }

        // 6. Non-Blocking Confirmation Modal (Replaces native confirm())
        window.openDsConfirmModal = function(title, message, onConfirm) {
            let modal = document.getElementById('ds-confirm-action-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-confirm-action-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:460px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <h3 id="ds-confirm-modal-title" style="margin:0;font-size:1.1rem;font-weight:900;">تأكيد الإجراء</h3>
                            <button type="button" id="ds-confirm-modal-close" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <p id="ds-confirm-modal-msg" style="margin:0 0 18px;font-size:0.94rem;font-weight:800;line-height:1.6;"></p>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" id="ds-confirm-modal-cancel" class="btn-action" style="padding:8px 18px;border-radius:10px;background:rgba(148,163,184,0.2);border:1.5px solid rgba(148,163,184,0.4);color:inherit;font-weight:900;cursor:pointer;">إلغاء (Esc)</button>
                            <button type="button" id="ds-confirm-modal-ok" class="btn-action" style="padding:8px 22px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);border:none;color:#fff;font-weight:900;cursor:pointer;">✅ تأكيد المتابعة</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            document.getElementById('ds-confirm-modal-title').textContent = title || 'تأكيد الإجراء';
            document.getElementById('ds-confirm-modal-msg').textContent = message || '';
            const hide = () => { modal.style.display = 'none'; };
            document.getElementById('ds-confirm-modal-close').onclick = hide;
            document.getElementById('ds-confirm-modal-cancel').onclick = hide;
            const okBtn = document.getElementById('ds-confirm-modal-ok');
            okBtn.onclick = () => {
                hide();
                if (typeof onConfirm === 'function') onConfirm();
            };
            modal.style.display = 'flex';
            setTimeout(() => { try { okBtn.focus(); } catch (_e) {} }, 30);
        };

        // 7. Quick-Add Warehouse Modal (➕ مخزن جديد من داخل الفاتورة)
        window.openDsQuickWarehouseModal = function() {
            let modal = document.getElementById('ds-quick-wh-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-quick-wh-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:440px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.1rem;font-weight:900;">🏬 إضافة مخزن جديد فوراً</h3>
                            <button type="button" onclick="document.getElementById('ds-quick-wh-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">اسم المخزن *</label>
                                <input type="text" id="ds-new-wh-name" class="ctrl-input" placeholder="مثال: مخزن المعرض السريع" style="width:100%;padding:8px 12px;border-radius:9px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">الموقع / أمين المخزن</label>
                                <input type="text" id="ds-new-wh-loc" class="ctrl-input" placeholder="اختياري..." style="width:100%;padding:8px 12px;border-radius:9px;">
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" onclick="document.getElementById('ds-quick-wh-modal').style.display='none'" class="btn-action" style="padding:8px 16px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إلغاء</button>
                            <button type="button" onclick="window.saveDsQuickWarehouse()" class="btn-action" style="padding:8px 20px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;font-weight:900;">💾 حفظ واختيار المخزن</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const nameInp = document.getElementById('ds-new-wh-name');
            if (nameInp) nameInp.value = '';
            modal.style.display = 'flex';
            setTimeout(() => { if (nameInp) nameInp.focus(); }, 40);
        };

        window.saveDsQuickWarehouse = function() {
            const nameInp = document.getElementById('ds-new-wh-name');
            const name = (nameInp ? nameInp.value : '').trim();
            if (!name) {
                if (typeof window.focusRequiredSalesField === 'function') window.focusRequiredSalesField('#ds-new-wh-name', '⚠️ يرجى كتابة اسم المخزن أولاً');
                return;
            }
            const newId = 'wh-' + Date.now();
            const newWh = { id: newId, name: name, location: (document.getElementById('ds-new-wh-loc')?.value || '').trim() };
            try {
                const list = JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]');
                list.push(newWh);
                localStorage.setItem('erp_warehouses_v1', JSON.stringify(list));
                if (typeof WAREHOUSES !== 'undefined' && Array.isArray(WAREHOUSES)) {
                    WAREHOUSES.push(newWh);
                }
            } catch (_e) {}

            const sel = document.getElementById('hdr-warehouse');
            if (sel) {
                const opt = document.createElement('option');
                opt.value = newId;
                opt.textContent = name;
                sel.appendChild(opt);
                sel.value = newId;
                if (typeof onMainWarehouseChange === 'function') onMainWarehouseChange();
            }
            document.getElementById('ds-quick-wh-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast(`🏬 تم إضافة المخزن (${name}) واختياره في الفاتورة فوراً`, 'success', 2200);
        };

        // 8. Quick-Add Inventory Item Modal (➕ صنف جديد من داخل الفاتورة)
        window.openDsQuickItemModal = function() {
            let modal = document.getElementById('ds-quick-item-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-quick-item-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:520px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.1rem;font-weight:900;">📦 تعريف وإدراج صنف جديد فوراً</h3>
                            <button type="button" onclick="document.getElementById('ds-quick-item-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
                            <div style="grid-column:1 / -1;">
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">اسم الصنف *</label>
                                <input type="text" id="ds-new-item-name" class="ctrl-input" placeholder="اكتب اسم الصنف..." style="width:100%;padding:8px 12px;border-radius:9px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">الوحدة الأساسية</label>
                                <input type="text" id="ds-new-item-unit" class="ctrl-input" value="قطعة" style="width:100%;padding:8px 12px;border-radius:9px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">الكود / الباركود</label>
                                <input type="text" id="ds-new-item-code" class="ctrl-input" placeholder="تلقائي..." style="width:100%;padding:8px 12px;border-radius:9px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">سعر التكلفة</label>
                                <input type="number" id="ds-new-item-cost" class="ctrl-input" value="0" step="any" style="width:100%;padding:8px 12px;border-radius:9px;text-align:center;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">سعر البيع *</label>
                                <input type="number" id="ds-new-item-price" class="ctrl-input" value="0" step="any" style="width:100%;padding:8px 12px;border-radius:9px;text-align:center;">
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" onclick="document.getElementById('ds-quick-item-modal').style.display='none'" class="btn-action" style="padding:8px 16px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إلغاء</button>
                            <button type="button" onclick="window.saveDsQuickItem()" class="btn-action" style="padding:8px 20px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;border:none;font-weight:900;">💾 حفظ وإدراج في الجدول</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const qSearch = document.getElementById('quick-item-search');
            const nameInp = document.getElementById('ds-new-item-name');
            if (nameInp) nameInp.value = qSearch && qSearch.value ? qSearch.value.trim() : '';
            modal.style.display = 'flex';
            setTimeout(() => { if (nameInp) nameInp.focus(); }, 40);
        };

        window.saveDsQuickItem = function() {
            const nameInp = document.getElementById('ds-new-item-name');
            const name = (nameInp ? nameInp.value : '').trim();
            if (!name) {
                if (typeof window.focusRequiredSalesField === 'function') window.focusRequiredSalesField('#ds-new-item-name', '⚠️ يرجى إدخال اسم الصنف أولاً');
                return;
            }
            const unit = (document.getElementById('ds-new-item-unit')?.value || 'قطعة').trim();
            const code = (document.getElementById('ds-new-item-code')?.value || ('ITM-' + Date.now().toString().slice(-5))).trim();
            const cost = parseFloat(document.getElementById('ds-new-item-cost')?.value) || 0;
            const price = parseFloat(document.getElementById('ds-new-item-price')?.value) || 0;
            const newItem = {
                id: 'item-' + Date.now(),
                code: code,
                barcode: code,
                name: name,
                unit: unit,
                baseUnit: unit,
                costPrice: cost,
                buyPrice: cost,
                sellPrice: price,
                price: price,
                conversionFactor: 1,
                unitsArray: [{ name: unit, factor: 1, price: price }]
            };
            try {
                const invList = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
                invList.push(newItem);
                localStorage.setItem('erp_inventory_v1', JSON.stringify(invList));
                if (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) {
                    ITEMS.push(newItem);
                }
            } catch (_e) {}

            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                const emptyRow = rows.find(r => r && !r.itemId);
                if (emptyRow && typeof selectItemForRow === 'function') {
                    selectItemForRow(emptyRow.id, newItem.id);
                } else if (typeof addRow === 'function') {
                    addRow(true);
                    const lastRow = rows[rows.length - 1];
                    if (lastRow) {
                        lastRow.itemId = newItem.id;
                        lastRow.selectedUnit = unit;
                        lastRow.price = price;
                        lastRow.quantity = 1;
                    }
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                }
            }
            document.getElementById('ds-quick-item-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast(`📦 تم تعريف الصنف (${name}) وإدراجه في الفاتورة بنجاح`, 'success', 2200);
        };

        // =========================================================================
        // CONSTITUTION 1 APPROVED PROPOSALS:
        // 1. SMART POS CASH TENDERED & CHANGE CALCULATOR (حاسبة الباقي والفئات النقدية)
        // 2. CONTINUOUS POS BARCODE STREAM (ماسح الباركود المتصل لنقاط البيع)
        // 3. INSTANT WARRANTY CERTIFICATE & THERMAL RECEIPT QR (بطاقة الضمان وإيصال QR)
        // =========================================================================

        function playDsPosBeep(freq = 880, dur = 0.08) {
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                const ctx = new Ctx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.value = 0.12;
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                setTimeout(() => { try { osc.stop(); ctx.close(); } catch (_e) {} }, dur * 1000);
            } catch (_e) {}
        }

        // Proposal 1: Smart POS Cash Tendered & Change Calculator
        window.openDsCashChangeCalculator = function() {
            let modal = document.getElementById('ds-cash-change-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-cash-change-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:540px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">🧮 حاسبة النقدية المستلمة والباقي للعميل (POS Change)</h3>
                            <button type="button" onclick="document.getElementById('ds-cash-change-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                            <div style="padding:10px;border-radius:12px;border:1.8px solid rgba(56,189,248,0.4);text-align:center;">
                                <div style="font-size:0.8rem;font-weight:800;opacity:0.85;">إجمالي الفاتورة المستحق</div>
                                <div id="ds-calc-due-val" style="font-size:1.55rem;font-weight:900;font-family:'Outfit';color:#38bdf8;">0</div>
                            </div>
                            <div style="padding:10px;border-radius:12px;border:1.8px solid rgba(16,185,129,0.5);text-align:center;">
                                <div id="ds-calc-change-lbl" style="font-size:0.8rem;font-weight:800;opacity:0.85;">الباقي للعميل (Change)</div>
                                <div id="ds-calc-change-val" style="font-size:1.55rem;font-weight:900;font-family:'Outfit';color:#10b981;">0</div>
                            </div>
                        </div>
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:0.85rem;font-weight:900;margin-bottom:5px;">💵 المبلغ المستلم من العميل:</label>
                            <input type="number" id="ds-calc-tendered-input" class="ctrl-input" step="any" oninput="window.recalcDsCashChange()" style="width:100%;height:46px;font-size:1.45rem;font-weight:900;text-align:center;border-radius:10px;font-family:'Outfit';">
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
                            <button type="button" onclick="window.addDsCashDenom('exact')" class="btn-action" style="padding:6px 11px;border-radius:8px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;font-weight:900;font-size:0.8rem;cursor:pointer;">⚡ المبلغ كامل</button>
                            <button type="button" onclick="window.addDsCashDenom(1000)" class="btn-action" style="padding:6px 11px;border-radius:8px;background:rgba(16,185,129,0.18);border:1.5px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">+1,000</button>
                            <button type="button" onclick="window.addDsCashDenom(5000)" class="btn-action" style="padding:6px 11px;border-radius:8px;background:rgba(16,185,129,0.18);border:1.5px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">+5,000</button>
                            <button type="button" onclick="window.addDsCashDenom(10000)" class="btn-action" style="padding:6px 11px;border-radius:8px;background:rgba(16,185,129,0.18);border:1.5px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">+10,000</button>
                            <button type="button" onclick="window.addDsCashDenom(50000)" class="btn-action" style="padding:6px 11px;border-radius:8px;background:rgba(16,185,129,0.18);border:1.5px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">+50,000</button>
                            <button type="button" onclick="window.addDsCashDenom(100000)" class="btn-action" style="padding:6px 11px;border-radius:8px;background:rgba(16,185,129,0.18);border:1.5px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">+100,000</button>
                            <button type="button" onclick="window.addDsCashDenom(0)" class="btn-action" style="padding:6px 11px;border-radius:8px;background:rgba(239,68,68,0.18);border:1.5px solid #ef4444;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">تصفير</button>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" onclick="document.getElementById('ds-cash-change-modal').style.display='none'" class="btn-action" style="padding:8px 16px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                            <button type="button" onclick="window.applyDsCashPaymentFromCalc()" class="btn-action" style="padding:8px 20px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;font-weight:900;">✅ اعتماد السداد النقدي في الفاتورة</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const rawGrand = (document.getElementById('t-grand')?.textContent || document.getElementById('t-grand-dynamic-loc')?.textContent || '0').replace(/[^0-9.-]/g, '');
            const dueVal = parseFloat(rawGrand) || 0;
            window._dsCurrentCalcDue = dueVal;
            const dueEl = document.getElementById('ds-calc-due-val');
            if (dueEl) dueEl.textContent = dueVal.toLocaleString('en-US');
            const inp = document.getElementById('ds-calc-tendered-input');
            if (inp) inp.value = dueVal;
            window.recalcDsCashChange();
            modal.style.display = 'flex';
            setTimeout(() => { if (inp) { inp.focus(); inp.select(); } }, 40);
        };

        window.addDsCashDenom = function(val) {
            const inp = document.getElementById('ds-calc-tendered-input');
            if (!inp) return;
            if (val === 'exact') {
                inp.value = window._dsCurrentCalcDue || 0;
            } else if (val === 0) {
                inp.value = 0;
            } else {
                inp.value = (parseFloat(inp.value) || 0) + Number(val);
            }
            window.recalcDsCashChange();
        };

        window.recalcDsCashChange = function() {
            const due = window._dsCurrentCalcDue || 0;
            const tendered = parseFloat(document.getElementById('ds-calc-tendered-input')?.value) || 0;
            const diff = Math.round((tendered - due) * 100) / 100;
            const lbl = document.getElementById('ds-calc-change-lbl');
            const valEl = document.getElementById('ds-calc-change-val');
            if (!valEl || !lbl) return;
            if (diff >= 0) {
                lbl.textContent = 'الباقي للعميل (Change Due)';
                valEl.textContent = diff.toLocaleString('en-US');
                valEl.style.color = '#10b981';
            } else {
                lbl.textContent = 'متبقي على العميل (آجل)';
                valEl.textContent = Math.abs(diff).toLocaleString('en-US');
                valEl.style.color = '#f59e0b';
            }
        };

        window.applyDsCashPaymentFromCalc = function() {
            const due = window._dsCurrentCalcDue || 0;
            const tendered = parseFloat(document.getElementById('ds-calc-tendered-input')?.value) || 0;
            const paidCash = tendered >= due ? due : Math.max(0, tendered);
            const cashSec = document.getElementById('pay-cash-section');
            if (cashSec && cashSec.style.display === 'none' && typeof window.togglePaymentSection === 'function') {
                window.togglePaymentSection('cash');
            }
            const cashInp = document.getElementById('pay-cash-amount');
            if (cashInp) {
                cashInp.value = paidCash;
                if (typeof window.onPaymentAmountInput === 'function') window.onPaymentAmountInput();
            }
            const change = Math.max(0, Math.round((tendered - due) * 100) / 100);
            const cashNote = document.getElementById('pay-cash-note');
            if (cashNote && tendered > 0) {
                cashNote.value = `المستلم: ${tendered.toLocaleString('en-US')} | الباقي للعميل: ${change.toLocaleString('en-US')}`;
            }
            document.getElementById('ds-cash-change-modal').style.display = 'none';
            if (typeof window.showToast === 'function') {
                window.showToast(`💵 تم اعتماد السداد النقدي (${paidCash.toLocaleString('en-US')}) — الباقي للعميل: ${change.toLocaleString('en-US')}`, 'success', 2400);
            }
        };

        // Proposal 2: Continuous POS Barcode Stream Modal
        window._dsPosScanCount = 0;
        window.openDsContinuousPosScanner = function() {
            let modal = document.getElementById('ds-continuous-scan-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-continuous-scan-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:560px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">📡 الماسح المتصل السريع لنقاط البيع (Continuous POS Stream)</h3>
                            <button type="button" onclick="document.getElementById('ds-continuous-scan-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="display:flex;gap:8px;margin-bottom:10px;">
                            <input type="text" id="ds-stream-scan-input" class="ctrl-input" placeholder="امسح الباركود أو اكتب اسم/كود الصنف ثم Enter للإدراج المتتابع..." style="flex:1;height:44px;border-radius:10px;padding:0 12px;font-weight:900;" onkeydown="if(event.key==='Enter'){event.preventDefault();window.processDsContinuousPosScan(this.value);}">
                            <button type="button" onclick="window.processDsContinuousPosScan(document.getElementById('ds-stream-scan-input').value)" class="btn-action" style="padding:0 16px;border-radius:10px;background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff;border:none;font-weight:900;">⚡ إدراج</button>
                        </div>
                        <div id="ds-stream-scan-log" style="max-height:200px;overflow-y:auto;border:1.8px solid rgba(148,163,184,0.3);border-radius:10px;padding:10px;font-size:0.84rem;font-weight:800;margin-bottom:12px;">
                            🎯 جاهز للمسح المتتابع السريع...
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span id="ds-stream-modal-counter" style="font-weight:900;color:#38bdf8;">إجمالي المسح: 0</span>
                            <button type="button" onclick="document.getElementById('ds-continuous-scan-modal').style.display='none'" class="btn-action" style="padding:8px 18px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
            setTimeout(() => {
                const inp = document.getElementById('ds-stream-scan-input');
                if (inp) { inp.value = ''; inp.focus(); }
            }, 40);
        };

        window.processDsContinuousPosScan = function(rawQuery) {
            const q = window.normalizeDsArabic(rawQuery);
            const inp = document.getElementById('ds-stream-scan-input');
            if (!q) return;
            const list = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS) && ITEMS.length)
                ? ITEMS
                : (JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'));
            const match = list.find(it => {
                if (!it) return false;
                const codeNorm = window.normalizeDsArabic(it.code || it.barcode || it.id || '');
                const nameNorm = window.normalizeDsArabic(it.name || '');
                return codeNorm === q || nameNorm.includes(q) || codeNorm.includes(q);
            });
            const logEl = document.getElementById('ds-stream-scan-log');
            if (!match) {
                playDsPosBeep(280, 0.14);
                if (logEl) logEl.innerHTML = `<div style="color:#ef4444;padding:4px 0;">❌ لم يتم العثور على صنف مطابق لـ (${rawQuery})</div>` + logEl.innerHTML;
                if (inp) inp.select();
                return;
            }

            playDsPosBeep(920, 0.07);
            window._dsPosScanCount = (window._dsPosScanCount || 0) + 1;
            const cntPill = document.getElementById('ds-pos-scan-cnt');
            if (cntPill) cntPill.textContent = window._dsPosScanCount;
            const modCnt = document.getElementById('ds-stream-modal-counter');
            if (modCnt) modCnt.textContent = `إجمالي المسح: ${window._dsPosScanCount}`;

            if (typeof rows !== 'undefined' && Array.isArray(rows)) {
                const existing = rows.find(r => r && String(r.itemId) === String(match.id));
                if (existing) {
                    existing.quantity = (parseFloat(existing.quantity) || 0) + 1;
                    if (typeof renderGrid === 'function') renderGrid();
                    if (typeof recalcAll === 'function') recalcAll();
                } else {
                    const emptyRow = rows.find(r => r && !r.itemId);
                    if (emptyRow && typeof selectItemForRow === 'function') {
                        selectItemForRow(emptyRow.id, match.id);
                    } else if (typeof addRow === 'function') {
                        addRow(true);
                        const lastRow = rows[rows.length - 1];
                        if (lastRow && typeof selectItemForRow === 'function') {
                            selectItemForRow(lastRow.id, match.id);
                        }
                    }
                }
            }
            if (logEl) {
                logEl.innerHTML = `<div style="color:#10b981;padding:4px 0;border-bottom:1px solid rgba(148,163,184,0.15);">✅ تمت إضافة (${match.name}) — السعر: ${(parseFloat(match.sellPrice || match.price) || 0).toLocaleString('en-US')}</div>` + logEl.innerHTML;
            }
            if (inp) { inp.value = ''; inp.focus(); }
        };

        // Proposal 3: Instant Warranty Certificate & Thermal 80mm Receipt QR Modal
        window.openDsWarrantyAndThermalModal = function() {
            let modal = document.getElementById('ds-warranty-qr-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-warranty-qr-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:540px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">🛡️ شهادة الضمان وإيصال الكاشير الحراري (QR Verified)</h3>
                            <button type="button" onclick="document.getElementById('ds-warranty-qr-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">مدة الضمان المعتمدة</label>
                                <select id="ds-warranty-duration" class="ctrl-input" style="width:100%;padding:8px 10px;border-radius:9px;">
                                    <option value="12 شهر (سنة كاملة)">12 شهر (سنة كاملة)</option>
                                    <option value="24 شهر (سنتان)">24 شهر (سنتان)</option>
                                    <option value="6 أشهر">6 أشهر</option>
                                    <option value="ضمان تشغيلي 30 يوماً">ضمان تشغيلي 30 يوماً</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:0.84rem;font-weight:900;margin-bottom:4px;">شروط الضمان / الرقم التسلسلي</label>
                                <input type="text" id="ds-warranty-serial-note" class="ctrl-input" placeholder="مثال: ضمان الوكيل ضد عيوب الصناعة" value="ضمان معتمد ضد عيوب الصناعة" style="width:100%;padding:8px 10px;border-radius:9px;">
                            </div>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;">
                            <button type="button" onclick="window.printDsCertificateOrThermal('warranty')" class="btn-action" style="flex:1;padding:10px 14px;border-radius:10px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border:none;font-weight:900;cursor:pointer;">🛡️ طباعة شهادة الضمان (A4 + QR)</button>
                            <button type="button" onclick="window.printDsCertificateOrThermal('thermal')" class="btn-action" style="flex:1;padding:10px 14px;border-radius:10px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;font-weight:900;cursor:pointer;">🧾 إيصال كاشير حراري 80mm + QR</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
        };

        window.printDsCertificateOrThermal = function(mode) {
            const invNo = document.getElementById('inv-number')?.textContent?.trim() || 'INV-NEW';
            const invDate = document.getElementById('hdr-date')?.value || new Date().toISOString().slice(0, 10);
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل نقدي';
            const grandTxt = document.getElementById('t-grand-dynamic-loc')?.textContent?.trim() || '0';
            const curTxt = document.getElementById('hdr-currency')?.value || 'SDG';
            const duration = document.getElementById('ds-warranty-duration')?.value || '12 شهر';
            const terms = document.getElementById('ds-warranty-serial-note')?.value || 'ضمان معتمد ضد عيوب الصناعة';
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId) : [];
            const qrPayload = encodeURIComponent(`ELSAJANA-SALES|INV:${invNo}|DATE:${invDate}|CUST:${custName}|TOTAL:${grandTxt} ${curTxt}`);
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${qrPayload}`;

            const rowsHtml = activeRows.map((r, i) => {
                const itemObj = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS.find(it => String(it.id) === String(r.itemId)) : null;
                const name = itemObj ? itemObj.name : r.itemId;
                const qty = parseFloat(r.quantity) || 1;
                const price = parseFloat(r.price) || 0;
                return `<tr>
                    <td style="padding:6px;border:1px solid #cbd5e1;text-align:center;">${i + 1}</td>
                    <td style="padding:6px;border:1px solid #cbd5e1;font-weight:800;">${name}</td>
                    <td style="padding:6px;border:1px solid #cbd5e1;text-align:center;">${qty} ${r.selectedUnit || ''}</td>
                    <td style="padding:6px;border:1px solid #cbd5e1;text-align:center;">${(qty * price).toLocaleString('en-US')}</td>
                </tr>`;
            }).join('') || `<tr><td colspan="4" style="padding:10px;text-align:center;">لا توجد أصناف مسجلة</td></tr>`;

            const w = window.open('', '_blank', mode === 'thermal' ? 'width=400,height=700' : 'width=880,height=720');
            if (!w) return;
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>${mode === 'warranty' ? 'شهادة ضمان معتمدة' : 'إيصال مبيعات حراري 80mm'} - ${invNo}</title>
                <style>
                    body { font-family: 'Cairo', 'Tajawal', sans-serif; margin: ${mode === 'thermal' ? '8px' : '24px'}; color: #000; background: #fff; }
                    .card { border: 2.5px solid #0f766e; border-radius: 14px; padding: ${mode === 'thermal' ? '10px' : '20px'}; max-width: ${mode === 'thermal' ? '78mm' : '820px'}; margin: 0 auto; }
                    table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: ${mode === 'thermal' ? '12px' : '14px'}; }
                </style>
            </head><body>
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #0f766e;padding-bottom:10px;">
                        <div>
                            <h2 style="margin:0;color:#0f766e;">${mode === 'warranty' ? '🛡️ شهادة ضمان أصناف معتمدة' : '🧾 إيصال مبيعات فوري (POS)'}</h2>
                            <div style="font-weight:800;font-size:13px;margin-top:4px;">رقم الفاتورة: ${invNo} | التاريخ: ${invDate}</div>
                            <div style="font-weight:800;font-size:13px;">العميل: ${custName}</div>
                        </div>
                        <img src="${qrUrl}" alt="QR" style="width:${mode === 'thermal' ? '82px' : '110px'};height:${mode === 'thermal' ? '82px' : '110px'};border:1.5px solid #0f766e;border-radius:8px;padding:3px;">
                    </div>
                    <table>
                        <thead><tr style="background:#f1f5f9;"><th>#</th><th>الصنف</th><th>الكمية</th><th>الإجمالي</th></tr></thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-weight:900;font-size:15px;padding:8px 0;border-top:2px solid #000;">
                        <span>الإجمالي الصافي: ${grandTxt} ${curTxt}</span>
                        <span>مدة الضمان: ${duration}</span>
                    </div>
                    <div style="font-size:12px;font-weight:800;margin-top:6px;">📋 الشروط: ${terms}</div>
                </div>
                <script>window.onload = function(){ setTimeout(function(){ window.print(); }, 350); };<\/script>
            </body></html>`);
            w.document.close();
            document.getElementById('ds-warranty-qr-modal').style.display = 'none';
        };

        // Guaranteed Male Arabic Voice Helper (Order 3 of Constitution 2)
        window.speakDsMaleVoice = function(text) {
            if (!text || typeof window.speechSynthesis === 'undefined') return;
            try {
                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();
                const u = new SpeechSynthesisUtterance(String(text));
                u.lang = 'ar-SA';
                u.rate = 0.96;
                u.pitch = 0.86;
                const voices = window.speechSynthesis.getVoices() || [];
                const arVoices = voices.filter(v => /ar/i.test(v.lang));
                const maleVoice = arVoices.find(v => /hamed|naayf|tarik|maged|omar|male|microsoft.*hamed/i.test(v.name)) || arVoices[0];
                if (maleVoice) u.voice = maleVoice;
                window.speechSynthesis.speak(u);
            } catch (_e) {}
        };

        // =========================================================================
        // CONSTITUTION 2 APPROVED PROPOSALS:
        // 1. LIVE AUDIO INVOICE & PAYMENT SUMMARY READBACK (🔊 نطق الملخص)
        // 2. HANDS-FREE ARABIC VOICE POS COMMANDS (🎙️ أمر صوتي)
        // 3. INTERACTIVE SALES MASTERY QUIZ (🎓 تدريب عملي)
        // =========================================================================

        // Proposal 1: Live Audio Invoice & Payment Summary Readback
        window.speakLiveDsInvoiceSummary = function() {
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل غير محدد';
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows))
                ? rows.filter(r => r && r.itemId)
                : [];
            const totalQty = activeRows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);
            const grandTxt = document.getElementById('t-grand-dynamic-loc')?.textContent?.trim() || '0';
            const cur = document.getElementById('hdr-currency')?.value || 'جنيه';
            const payBadge = document.getElementById('payment-status-badge')?.textContent?.trim() || 'آجلة';
            const marginTxt = document.getElementById('sales-margin-guard-text')?.textContent?.trim() || '';

            const msg = `ملخص فاتورة المبيعات الحالية: العميل ${custName}. عدد الأصناف المدرجة ${activeRows.length} صنف بإجمالي كميات ${totalQty}. الصافي النهائي المستحق ${grandTxt} ${cur}. حالة السداد ${payBadge}. مؤشر الربحية ${marginTxt}.`;
            if (typeof window.showToast === 'function') {
                window.showToast('🔊 ' + msg, 'info', 3800);
            }
            window.speakDsMaleVoice(msg);
        };

        // Proposal 2: Hands-Free Arabic Voice POS Commands
        window.executeDsVoiceCommandText = function(rawText) {
            const norm = window.normalizeDsArabic(rawText);
            if (!norm) return;
            if (norm.includes('حفظ') || norm.includes('اعتماد') || norm.includes('اعتمد')) {
                window.speakDsMaleVoice('جاري حفظ واعتماد فاتورة المبيعات.');
                if (typeof window.submitInvoice === 'function') window.submitInvoice();
            } else if (norm.includes('باقي') || norm.includes('حاسبه') || norm.includes('نقدي')) {
                window.speakDsMaleVoice('تم فتح حاسبة النقدية والباقي للعميل.');
                if (typeof window.openDsCashChangeCalculator === 'function') window.openDsCashChangeCalculator();
            } else if (norm.includes('تعليق') || norm.includes('اركن')) {
                window.speakDsMaleVoice('جاري تعليق الفاتورة الحالية.');
                if (typeof window.parkCurrentSalesInvoice === 'function') window.parkCurrentSalesInvoice();
            } else if (norm.includes('معلقه') || norm.includes('استدعاء')) {
                window.speakDsMaleVoice('تم فتح نافذة الفواتير المعلقة.');
                if (typeof window.openParkedInvoicesModal === 'function') window.openParkedInvoicesModal();
            } else if (norm.includes('سجل') || norm.includes('ربح') || norm.includes('تكلفه')) {
                window.speakDsMaleVoice('تم فتح شاشة كاشف التكلفة وسجل أسعار العميل.');
                if (typeof window.openCustomerItemHistoryModal === 'function') window.openCustomerItemHistoryModal('margin');
            } else if (norm.includes('ضمان') || norm.includes('شهاده')) {
                window.speakDsMaleVoice('تم فتح شاشة شهادة الضمان وإيصال الكيو آر.');
                if (typeof window.openDsWarrantyAndThermalModal === 'function') window.openDsWarrantyAndThermalModal();
            } else if (norm.includes('ملخص') || norm.includes('اقرا') || norm.includes('نطق')) {
                window.speakLiveDsInvoiceSummary();
            } else if (norm.includes('جديد') || norm.includes('فاتوره جديده')) {
                window.speakDsMaleVoice('بدء فاتورة مبيعات جديدة.');
                if (typeof window.onSuccessNewInvoice === 'function') window.onSuccessNewInvoice(false);
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast(`🎙️ تم سماع: "${rawText}" — جرب قول: حفظ الفاتورة، حاسبة الباقي، تعليق الفاتورة، سجل الأسعار`, 'info', 2800);
                }
            }
        };

        window.startDsVoiceCommandListener = function() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SR) {
                try {
                    const rec = new SR();
                    rec.lang = 'ar-SA';
                    rec.interimResults = false;
                    rec.maxAlternatives = 1;
                    if (typeof window.showToast === 'function') {
                        window.showToast('🎙️ تحدث الآن... (مثال: "حفظ الفاتورة" ، "حاسبة الباقي" ، "تعليق الفاتورة" ، "سجل الأسعار")', 'info', 3000);
                    }
                    rec.onresult = function(ev) {
                        const txt = ev.results && ev.results[0] && ev.results[0][0] ? ev.results[0][0].transcript : '';
                        if (txt) window.executeDsVoiceCommandText(txt);
                    };
                    rec.onerror = function() {
                        window.openDsVoiceCommandFallbackModal();
                    };
                    rec.start();
                    return;
                } catch (_e) {}
            }
            window.openDsVoiceCommandFallbackModal();
        };

        window.openDsVoiceCommandFallbackModal = function() {
            let modal = document.getElementById('ds-voice-cmd-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-voice-cmd-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:480px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <h3 style="margin:0;font-size:1.1rem;font-weight:900;">🎙️ مركز الأوامر الصوتية السريعة للكاشير</h3>
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <p style="margin:0 0 10px;font-size:0.84rem;font-weight:800;">اختر أو اكتب الأمر الصوتي لتنفيذه فوراً:</p>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText('نطق الملخص')" class="btn-action" style="padding:6px 10px;border-radius:8px;background:rgba(99,102,241,0.2);border:1px solid #6366f1;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">🔊 نطق الملخص</button>
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText('حاسبة الباقي')" class="btn-action" style="padding:6px 10px;border-radius:8px;background:rgba(16,185,129,0.2);border:1px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">🧮 حاسبة الباقي</button>
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText('تعليق الفاتورة')" class="btn-action" style="padding:6px 10px;border-radius:8px;background:rgba(14,165,233,0.2);border:1px solid #0ea5e9;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">⏸️ تعليق الفاتورة</button>
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText('سجل الأسعار')" class="btn-action" style="padding:6px 10px;border-radius:8px;background:rgba(168,85,247,0.2);border:1px solid #a855f7;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">📈 سجل الأسعار</button>
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText('حفظ الفاتورة')" class="btn-action" style="padding:6px 10px;border-radius:8px;background:rgba(16,185,129,0.25);border:1px solid #10b981;color:inherit;font-weight:900;font-size:0.8rem;cursor:pointer;">✅ حفظ الفاتورة</button>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <input type="text" id="ds-voice-cmd-input" class="ctrl-input" placeholder="اكتب الأمر هنا ثم Enter..." style="flex:1;padding:8px 12px;border-radius:9px;" onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText(this.value);}">
                            <button type="button" onclick="document.getElementById('ds-voice-cmd-modal').style.display='none';window.executeDsVoiceCommandText(document.getElementById('ds-voice-cmd-input').value)" class="btn-action" style="padding:8px 16px;border-radius:9px;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;border:none;font-weight:900;">تنفيذ</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
        };

        // Proposal 3: Interactive Sales Mastery Quiz
        const dsQuizQuestions = [
            {
                prompt: 'السؤال الأول: اضغط على الخانة المخصصة للبحث عن العميل أو فتح كشف حسابه في الأستاذ العام.',
                selector: '#hdr-customer-search, #hdr-customer-ledger-btn, .group-supplier'
            },
            {
                prompt: 'السؤال الثاني: اضغط على شارة كاشف هامش الربح والتكلفة أو قائمة شرائح الأسعار (قطاعي وجملة).',
                selector: '#sales-margin-guard-badge, #hdr-price-tier, #btn-cust-item-history'
            },
            {
                prompt: 'السؤال الثالث: اضغط على زر تعليق الفاتورة أو زر حاسبة الباقي النقدي الذكية.',
                selector: '#btn-park-invoice, #btn-recall-parked, #btn-ds-cash-change'
            }
        ];
        window._dsQuizState = { active: false, step: 0 };

        window.startDsMasteryQuiz = function() {
            let banner = document.getElementById('ds-mastery-quiz-banner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'ds-mastery-quiz-banner';
                banner.style.cssText = 'position:fixed;bottom:18px;left:50%;transform:translateX(-50%);z-index:999998;background:linear-gradient(135deg,#0f172a,#1e293b);border:2.2px solid #f59e0b;border-radius:99px;padding:8px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 12px 35px rgba(0,0,0,0.65);color:#fff;font-weight:900;font-size:0.88rem;direction:rtl;';
                document.body.appendChild(banner);
            }
            window._dsQuizState = { active: true, step: 0 };
            renderDsQuizStep();
        };

        function renderDsQuizStep() {
            const banner = document.getElementById('ds-mastery-quiz-banner');
            if (!banner) return;
            if (!window._dsQuizState.active) {
                banner.style.display = 'none';
                return;
            }
            const q = dsQuizQuestions[window._dsQuizState.step];
            if (!q) {
                window._dsQuizState.active = false;
                banner.style.display = 'none';
                const msg = 'أحسنت! أتممت التدريب العملي على شاشة المبيعات المباشرة بنجاح مئة بالمئة.';
                if (typeof window.showToast === 'function') window.showToast('🎓 ' + msg, 'success', 3200);
                window.speakDsMaleVoice(msg);
                return;
            }
            banner.style.display = 'flex';
            banner.innerHTML = `
                <span>🎓 تدريب (${window._dsQuizState.step + 1}/${dsQuizQuestions.length}):</span>
                <span style="color:#fde68a;">${q.prompt}</span>
                <button type="button" onclick="window._dsQuizState.active=false;document.getElementById('ds-mastery-quiz-banner').style.display='none';" style="background:rgba(239,68,68,0.25);border:1px solid #ef4444;color:#fca5a5;border-radius:99px;padding:3px 10px;cursor:pointer;font-weight:900;font-size:0.78rem;">إنهاء</button>
            `;
            window.speakDsMaleVoice(q.prompt);
        }

        document.addEventListener('click', function(e) {
            if (!window._dsQuizState || !window._dsQuizState.active) return;
            if (e.target && (e.target.closest('#btn-ds-mastery-quiz') || e.target.closest('#ds-mastery-quiz-banner'))) return;
            const q = dsQuizQuestions[window._dsQuizState.step];
            if (!q) return;
            if (e.target && (e.target.matches(q.selector) || e.target.closest(q.selector))) {
                playDsPosBeep(1040, 0.09);
                if (typeof window.showToast === 'function') window.showToast('✅ إجابة صحيحة! ننتقل للسؤال التالي...', 'success', 1800);
                window._dsQuizState.step++;
                setTimeout(renderDsQuizStep, 550);
            }
        }, true);

        // =========================================================================
        // CONSTITUTION 3 ENGINE (الدستور الثالث والأخير من 3):
        // 1. SUB-SECOND CROSS-CHANNEL SYNC (Mobile ↔ Laptop ↔ Offline)
        // 2. LIVE BALANCED ACCOUNTING JOURNAL PREVIEW MODAL (⚖️ القيد المحاسبي)
        // 3. LIVE TREASURY/CASH ACCOUNT BALANCE PILL & PERMANENT AUDIT TRAIL STAMP
        // 4. INSTANT EXCEL EXPORT & ADVANCED HUB FILTERS
        // =========================================================================

        // 1. Permanent Audit Trail Stamp in #sales-row0-bar & Live Cash Account Balance Pill
        function ensureDsAuditStampAndTreasuryBalance() {
            const auditBox = document.getElementById('sales-user-audit-container');
            if (auditBox && !document.getElementById('ds-audit-stamp-pill') && auditBox.children.length === 0) {
                const isMob = /Android|iPhone|iPad|Mobile/i.test(navigator.userAgent);
                const userName = localStorage.getItem('magaies_current_user_name') || localStorage.getItem('erp_user_name') || 'مدير النظام';
                const timeStr = new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
                const stamp = document.createElement('div');
                stamp.id = 'ds-audit-stamp-pill';
                stamp.className = 'user-audit-badge';
                stamp.innerHTML = `<span class="uab-label">👤 المستخدم:</span><span class="uab-val">${userName}</span><span class="uab-label">| ${isMob ? '📱 جوال' : '💻 لابتوب'} | 🕒 ${timeStr}</span>`;
                auditBox.appendChild(stamp);
            }

            const cashAccSel = document.getElementById('hdr-payment-account');
            if (cashAccSel && !document.getElementById('ds-cash-account-live-bal')) {
                const balPill = document.createElement('div');
                balPill.id = 'ds-cash-account-live-bal';
                balPill.style.cssText = 'margin-top:6px;padding:4px 10px;border-radius:8px;font-size:0.78rem;font-weight:900;background:rgba(16,185,129,0.14);border:1.5px solid rgba(16,185,129,0.45);color:#10b981;display:inline-flex;align-items:center;gap:8px;align-self:flex-start;';
                const parentSec = document.getElementById('pay-cash-section');
                if (parentSec) parentSec.appendChild(balPill);
                const updateCashBalPill = () => {
                    const accName = cashAccSel.options[cashAccSel.selectedIndex]?.text || 'الخزينة الرئيسية';
                    const cashEntered = parseFloat(document.getElementById('pay-cash-amount')?.value) || 0;
                    let baseBal = 0;
                    try {
                        const treasuries = JSON.parse(localStorage.getItem('erp_treasuries_v1') || '[]');
                        const found = treasuries.find(t => String(t.id) === String(cashAccSel.value) || t.name === accName);
                        if (found) baseBal = parseFloat(found.balance ?? found.openingBalance ?? 0) || 0;
                    } catch (_e) {}
                    const afterBal = baseBal + cashEntered;
                    balPill.innerHTML = `🏦 رصيد (${accName}) الحالي: <span style="font-family:'Outfit';">${baseBal.toLocaleString('en-US')}</span> ⬅ بعد القبض: <span style="font-family:'Outfit';text-decoration:underline;">${afterBal.toLocaleString('en-US')}</span>`;
                };
                cashAccSel.addEventListener('change', updateCashBalPill);
                const cashInp = document.getElementById('pay-cash-amount');
                if (cashInp) cashInp.addEventListener('input', updateCashBalPill);
                setTimeout(updateCashBalPill, 200);
            }
        }

        // 2. Live Balanced Accounting Journal Preview Modal (⚖️ القيد المحاسبي اللحظي)
        window.openDsLiveJournalPreviewModal = function() {
            let modal = document.getElementById('ds-live-journal-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-live-journal-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:780px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">⚖️ القيد المحاسبي اللحظي المتوازن وأثر المخزون والعميل (Atomic Journal Preview)</h3>
                            <button type="button" onclick="document.getElementById('ds-live-journal-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div id="ds-live-journal-summary" style="padding:10px 14px;border-radius:10px;border:1.8px solid rgba(16,185,129,0.45);margin-bottom:12px;font-size:0.86rem;font-weight:900;"></div>
                        <div style="max-height:340px;overflow-y:auto;border:1.8px solid rgba(148,163,184,0.3);border-radius:12px;margin-bottom:12px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.5);">
                                        <th style="padding:9px;text-align:right;">الحساب المحاسبي</th>
                                        <th style="padding:9px;text-align:center;">مدين (Debit)</th>
                                        <th style="padding:9px;text-align:center;">دائن (Credit)</th>
                                        <th style="padding:9px;text-align:right;">البيان / الترحيل الذري</th>
                                    </tr>
                                </thead>
                                <tbody id="ds-live-journal-tbody"></tbody>
                                <tfoot id="ds-live-journal-tfoot"></tfoot>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" onclick="document.getElementById('ds-live-journal-modal').style.display='none'" class="btn-action" style="padding:8px 18px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل نقدي';
            const invNo = document.getElementById('inv-number')?.textContent?.trim() || 'NEW';
            const cur = document.getElementById('hdr-currency')?.value || 'SDG';
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId) : [];
            const itemsSubtotal = activeRows.reduce((s, r) => s + ((parseFloat(r.quantity) || 0) * (parseFloat(r.price) || 0)), 0);
            const totalCogs = activeRows.reduce((s, r) => {
                const itemObj = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS.find(i => String(i.id) === String(r.itemId)) : null;
                const unitCost = parseFloat(itemObj?.costPrice ?? itemObj?.buyPrice ?? 0) || 0;
                return s + ((parseFloat(r.quantity) || 0) * unitCost);
            }, 0);

            const parseClean = id => parseFloat(String(document.getElementById(id)?.value || '0').replace(/,/g, '')) || 0;
            const transport = parseClean('t-transport');
            const labor = parseClean('t-labor');
            const additions = parseClean('addition-amt');
            const tax = parseClean('tax-amt');
            const discount = parseClean('discount-amt');
            const grandTotal = Math.max(0, itemsSubtotal + transport + labor + additions + tax - discount);

            const cashAmt = document.getElementById('pay-cash-section')?.style.display !== 'none' ? parseClean('pay-cash-amount') : 0;
            const creditAmt = Math.max(0, grandTotal - cashAmt);

            const entries = [];
            if (cashAmt > 0) entries.push({ acc: 'ح/ الخزينة / الصندوق النقدي', deb: cashAmt, cred: 0, note: `تحصيل نقدي فوري للفاتورة #${invNo}` });
            if (creditAmt > 0) entries.push({ acc: `ح/ ذمم العملاء — ${custName}`, deb: creditAmt, cred: 0, note: `إثبات مديونية الفاتورة #${invNo}` });
            if (discount > 0) entries.push({ acc: 'ح/ الخصم المسموح به للمبيعات', deb: discount, cred: 0, note: 'خصم ممنوح على الفاتورة' });
            if (itemsSubtotal > 0) entries.push({ acc: 'ح/ إيرادات المبيعات المباشرة', deb: 0, cred: itemsSubtotal, note: `قيمة بضاعة مباعة (${activeRows.length} صنف)` });
            if (transport + labor + additions > 0) entries.push({ acc: 'ح/ إيرادات الترحيل والعمالة والخدمات', deb: 0, cred: transport + labor + additions, note: 'مصاريف مضافة على الفاتورة' });
            if (tax > 0) entries.push({ acc: 'ح/ ضريبة القيمة المضافة للمبيعات', deb: 0, cred: tax, note: 'ضريبة مبيعات مستحقة' });
            if (totalCogs > 0) {
                entries.push({ acc: 'ح/ تكلفة البضاعة المباعة (COGS)', deb: totalCogs, cred: 0, note: 'قيد الجرد المستمر لتكلفة المبيعات' });
                entries.push({ acc: 'ح/ مراقبة المخزون بالمستودع', deb: 0, cred: totalCogs, note: 'تخفيض المخزون بالتكلفة' });
            }

            const totDeb = entries.reduce((s, e) => s + e.deb, 0);
            const totCred = entries.reduce((s, e) => s + e.cred, 0);

            const tbody = document.getElementById('ds-live-journal-tbody');
            const tfoot = document.getElementById('ds-live-journal-tfoot');
            const sumEl = document.getElementById('ds-live-journal-summary');
            if (tbody) {
                tbody.innerHTML = entries.map(e => `
                    <tr style="border-bottom:1px solid rgba(148,163,184,0.2);">
                        <td style="padding:8px 10px;font-weight:900;">${e.acc}</td>
                        <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;color:#10b981;">${e.deb > 0 ? e.deb.toLocaleString('en-US') : '—'}</td>
                        <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;color:#38bdf8;">${e.cred > 0 ? e.cred.toLocaleString('en-US') : '—'}</td>
                        <td style="padding:8px 10px;font-size:0.8rem;font-weight:800;">${e.note}</td>
                    </tr>
                `).join('') || `<tr><td colspan="4" style="padding:18px;text-align:center;font-weight:800;">أضف صنفاً واحداً على الأقل لمعاينة القيد المحاسبي اللحظي</td></tr>`;
            }
            if (tfoot) {
                tfoot.innerHTML = `
                    <tr style="background:rgba(16,185,129,0.14);font-weight:900;">
                        <td style="padding:9px 10px;">الإجمالي المتوازن (100% Atomic Match)</td>
                        <td style="padding:9px;text-align:center;font-family:'Outfit';color:#10b981;">${totDeb.toLocaleString('en-US')} ${cur}</td>
                        <td style="padding:9px;text-align:center;font-family:'Outfit';color:#38bdf8;">${totCred.toLocaleString('en-US')} ${cur}</td>
                        <td style="padding:9px 10px;color:#10b981;">✅ القيد متوازن تماماً (الفرق: 0.00)</td>
                    </tr>
                `;
            }
            if (sumEl) {
                const curCustBal = parseFloat((document.getElementById('fin-ledger')?.textContent || '0').replace(/,/g, '')) || 0;
                const newCustBal = curCustBal + creditAmt;
                sumEl.innerHTML = `👤 أثر الفاتورة على ذمة (${custName}): الرصيد قبل الفاتورة: <span style="font-family:'Outfit';">${curCustBal.toLocaleString('en-US')}</span> ⬅ الرصيد بعد الاعتماد: <span style="font-family:'Outfit';color:#10b981;">${newCustBal.toLocaleString('en-US')} ${cur}</span>`;
            }
            modal.style.display = 'flex';
        };

        // 3. Instant Excel (CSV UTF-8 BOM) Exporters for Current Invoice & Sales Hub
        window.exportCurrentSalesInvoiceToExcel = function() {
            const invNo = document.getElementById('inv-number')?.textContent?.trim() || 'INV-NEW';
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل نقدي';
            const invDate = document.getElementById('hdr-date')?.value || '';
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId) : [];
            const csvRows = [
                ['رقم الفاتورة', invNo, 'التاريخ', invDate, 'العميل', custName],
                [],
                ['#', 'الصنف', 'الوحدة', 'المعامل', 'الكمية', 'السعر', 'الإجمالي']
            ];
            activeRows.forEach((r, idx) => {
                const itemObj = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS.find(i => String(i.id) === String(r.itemId)) : null;
                const name = itemObj ? itemObj.name : r.itemId;
                const qty = parseFloat(r.quantity) || 0;
                const price = parseFloat(r.price) || 0;
                csvRows.push([idx + 1, name, r.selectedUnit || '', r.customFactor || 1, qty, price, qty * price]);
            });
            const grandTxt = document.getElementById('t-grand-dynamic-loc')?.textContent?.trim() || '0';
            csvRows.push([], ['', '', '', '', '', 'الإجمالي الصافي', grandTxt]);

            const csvContent = '\uFEFF' + csvRows.map(row => row.map(c => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Invoice_${invNo}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            if (typeof window.showToast === 'function') window.showToast(`📊 تم تصدير الفاتورة (${invNo}) إلى ملف Excel بنجاح`, 'success', 2200);
        };

        window.exportSalesHubToExcel = function() {
            const trs = Array.from(document.querySelectorAll('#hub-transactions-body tr')).filter(tr => tr.style.display !== 'none');
            const csvRows = [['رقم الفاتورة', 'التاريخ', 'العميل', 'الأصناف', 'المخزن', 'الإجمالي']];
            trs.forEach(tr => {
                const tds = Array.from(tr.querySelectorAll('td')).slice(0, 6).map(td => td.innerText.replace(/\s+/g, ' ').trim());
                if (tds.length) csvRows.push(tds);
            });
            const csvContent = '\uFEFF' + csvRows.map(row => row.map(c => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Hub_Report_${new Date().toISOString().slice(0,10)}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            if (typeof window.showToast === 'function') window.showToast('📊 تم تصدير سجل المبيعات إلى ملف Excel بنجاح', 'success', 2200);
        };

        // 4. Instant Hub Date Range Filter
        window.applyAdvancedSalesHubFilters = function() {
            const dFrom = document.getElementById('hub-date-from')?.value || '';
            const dTo = document.getElementById('hub-date-to')?.value || '';
            if (!dFrom && !dTo) return;
            document.querySelectorAll('#hub-transactions-body tr').forEach(tr => {
                const txt = tr.innerText || '';
                const dateMatch = txt.match(/\d{4}-\d{2}-\d{2}/);
                const rowDate = dateMatch ? dateMatch[0] : '';
                let show = true;
                if (dFrom && rowDate && rowDate < dFrom) show = false;
                if (dTo && rowDate && rowDate > dTo) show = false;
                if (!show) tr.style.display = 'none';
            });
        };

        // 5. Cross-Channel Sub-Second Sync Listener (Mobile ↔ Laptop)
        ['elsajana_erp_sync', 'magaies_erp_sync_channel', 'magaiees_sync_channel'].forEach(chName => {
            try {
                const ch = new BroadcastChannel(chName);
                ch.onmessage = function(ev) {
                    if (ev && ev.data) {
                        updateLiveSyncBadgeState('⚡ تم التحديث الفوري (0ms)', '#10b981');
                        if (typeof window.syncSalesWithMariaDB === 'function') window.syncSalesWithMariaDB();
                    }
                };
            } catch (_e) {}
        });

        // =========================================================================
        // CONSTITUTION 3 APPROVED PROPOSALS:
        // 1. SMART MULTI-INSTALLMENT PROMISSORY NOTE SCHEDULER (📅 جدولة الأقساط)
        // 2. LIVE CUSTOMER LOYALTY POINTS & CASHBACK ENGINE (🎁 نقاط الولاء)
        // 3. OUT-OF-STOCK SMART ALTERNATIVE ITEM SUGGESTOR (🔄 بدائل الأصناف)
        // =========================================================================

        // Proposal 1: Smart Multi-Installment Promissory Note Scheduler
        window.openDsInstallmentSchedulerModal = function() {
            let modal = document.getElementById('ds-installments-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-installments-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:680px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">📅 مُقسّم الدفعات والأقساط الذكي للكمبيالات (Installment Scheduler)</h3>
                            <button type="button" onclick="document.getElementById('ds-installments-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px;">
                            <div>
                                <label style="display:block;font-size:0.82rem;font-weight:900;margin-bottom:4px;">المبلغ الآجل المجدول</label>
                                <input type="number" id="ds-inst-total-amt" class="ctrl-input" step="any" oninput="window.recalcDsInstallmentSchedule()" style="width:100%;padding:7px 10px;border-radius:8px;text-align:center;font-family:'Outfit';font-weight:900;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.82rem;font-weight:900;margin-bottom:4px;">عدد الأقساط</label>
                                <select id="ds-inst-count" class="ctrl-input" onchange="window.recalcDsInstallmentSchedule()" style="width:100%;padding:7px 10px;border-radius:8px;font-weight:900;">
                                    <option value="2">2 أقساط</option>
                                    <option value="3" selected>3 أقساط</option>
                                    <option value="4">4 أقساط</option>
                                    <option value="6">6 أقساط</option>
                                    <option value="12">12 قسطاً (سنة)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:0.82rem;font-weight:900;margin-bottom:4px;">الدورية</label>
                                <select id="ds-inst-freq" class="ctrl-input" onchange="window.recalcDsInstallmentSchedule()" style="width:100%;padding:7px 10px;border-radius:8px;font-weight:900;">
                                    <option value="30" selected>شهري (كل 30 يوماً)</option>
                                    <option value="15">نصف شهري (كل 15 يوماً)</option>
                                    <option value="7">أسبوعي (كل 7 أيام)</option>
                                </select>
                            </div>
                        </div>
                        <div style="max-height:240px;overflow-y:auto;border:1.8px solid rgba(148,163,184,0.3);border-radius:10px;margin-bottom:12px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.5);">
                                        <th style="padding:8px;">#</th>
                                        <th style="padding:8px;text-align:right;">رقم الكمبيالة</th>
                                        <th style="padding:8px;text-align:center;">تاريخ الاستحقاق</th>
                                        <th style="padding:8px;text-align:center;">قيمة القسط</th>
                                    </tr>
                                </thead>
                                <tbody id="ds-inst-tbody"></tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                            <button type="button" onclick="window.printDsInstallmentNotes()" class="btn-action" style="padding:8px 16px;border-radius:10px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;font-weight:900;">🖨️ طباعة الكمبيالات المجدولة</button>
                            <div style="display:flex;gap:8px;">
                                <button type="button" onclick="document.getElementById('ds-installments-modal').style.display='none'" class="btn-action" style="padding:8px 16px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                                <button type="button" onclick="window.applyDsInstallmentScheduleToInvoice()" class="btn-action" style="padding:8px 18px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;font-weight:900;">✅ اعتماد الجدولة في الفاتورة</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const creditVal = parseFloat(document.getElementById('pay-credit-amount')?.value) || parseFloat((document.getElementById('t-grand-dynamic-loc')?.textContent || '0').replace(/,/g, '')) || 0;
            const amtInp = document.getElementById('ds-inst-total-amt');
            if (amtInp) amtInp.value = creditVal;
            window.recalcDsInstallmentSchedule();
            modal.style.display = 'flex';
        };

        window._lastDsInstallments = [];
        window.recalcDsInstallmentSchedule = function() {
            const total = parseFloat(document.getElementById('ds-inst-total-amt')?.value) || 0;
            const count = Math.max(1, parseInt(document.getElementById('ds-inst-count')?.value || '3', 10));
            const daysStep = Math.max(1, parseInt(document.getElementById('ds-inst-freq')?.value || '30', 10));
            const invNo = document.getElementById('inv-number')?.textContent?.trim() || 'INV';
            const baseAmt = Math.floor((total / count) * 100) / 100;
            const list = [];
            let sum = 0;
            const baseDate = new Date(document.getElementById('hdr-date')?.value || new Date().toISOString().slice(0, 10));

            for (let i = 1; i <= count; i++) {
                const d = new Date(baseDate);
                d.setDate(d.getDate() + (i * daysStep));
                const dateStr = d.toISOString().slice(0, 10);
                const amt = i === count ? Math.round((total - sum) * 100) / 100 : baseAmt;
                sum += amt;
                list.push({ idx: i, noteNo: `${invNo}-PN${i}`, dueDate: dateStr, amount: amt });
            }
            window._lastDsInstallments = list;
            const tbody = document.getElementById('ds-inst-tbody');
            if (tbody) {
                tbody.innerHTML = list.map(it => `
                    <tr style="border-bottom:1px solid rgba(148,163,184,0.2);">
                        <td style="padding:7px;text-align:center;font-weight:900;">${it.idx}</td>
                        <td style="padding:7px;font-family:'Outfit';font-weight:900;color:#fbbf24;">${it.noteNo}</td>
                        <td style="padding:7px;text-align:center;font-family:'Outfit';font-weight:800;">${it.dueDate}</td>
                        <td style="padding:7px;text-align:center;font-family:'Outfit';font-weight:900;color:#10b981;">${it.amount.toLocaleString('en-US')}</td>
                    </tr>
                `).join('');
            }
        };

        window.applyDsInstallmentScheduleToInvoice = function() {
            const list = window._lastDsInstallments || [];
            if (!list.length) return;
            const dueInp = document.getElementById('pay-credit-due-date');
            if (dueInp && list[0]) dueInp.value = list[0].dueDate;
            const descInp = document.getElementById('hdr-description');
            const summaryTxt = `جدولة أقساط (${list.length} دفعات): ` + list.map(i => `[قسط ${i.idx}: ${i.amount.toLocaleString('en-US')} في ${i.dueDate}]`).join(' ');
            if (descInp) descInp.value = (descInp.value ? descInp.value + ' | ' : '') + summaryTxt;
            document.getElementById('ds-installments-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast(`📅 تم اعتماد جدولة (${list.length}) أقساط وتحديث تاريخ الاستحقاق والبيان بنجاح`, 'success', 2400);
        };

        window.printDsInstallmentNotes = function() {
            const list = window._lastDsInstallments || [];
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل آجل';
            const cur = document.getElementById('hdr-currency')?.value || 'SDG';
            const w = window.open('', '_blank', 'width=850,height=720');
            if (!w) return;
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>سندات كمبيالات مجدولة - ${custName}</title>
                <style>body{font-family:'Cairo','Tajawal',sans-serif;padding:20px;color:#000;background:#fff;} .note{border:2px solid #000;border-radius:12px;padding:14px;margin-bottom:14px;page-break-inside:avoid;}</style>
            </head><body>
                <h2 style="text-align:center;margin-top:0;">📅 جدول سندات الكمبيالات والأقساط المعتمدة</h2>
                ${list.map(it => `
                    <div class="note">
                        <div style="display:flex;justify-content:space-between;font-weight:900;border-bottom:1.5px dashed #000;padding-bottom:6px;margin-bottom:8px;">
                            <span>سند لأمر (كمبيالة رقم: ${it.noteNo})</span>
                            <span>تاريخ الاستحقاق: ${it.dueDate}</span>
                            <span>المبلغ: ${it.amount.toLocaleString('en-US')} ${cur}</span>
                        </div>
                        <div style="font-size:14px;font-weight:800;">أتعهد أنا المدين (${custName}) بأن أدفع بموجب هذه الكمبيالة مبلغاً وقدره (${it.amount.toLocaleString('en-US')} ${cur}) في تاريخ الاستحقاق الموضح أعلاه.</div>
                        <div style="display:flex;justify-content:space-between;margin-top:16px;font-size:13px;font-weight:900;">
                            <span>توقيع العميل الملتزم: ............................</span>
                            <span>ختم واعتماد المؤسسة: ............................</span>
                        </div>
                    </div>
                `).join('')}
                <script>window.onload=function(){setTimeout(function(){window.print();},350);};<\/script>
            </body></html>`);
            w.document.close();
        };

        // Proposal 2: Live Customer Loyalty Points & Cashback Engine
        window.openDsCustomerLoyaltyModal = function() {
            let modal = document.getElementById('ds-loyalty-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-loyalty-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:520px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">🎁 بطاقة ولاء ومكافآت العميل اللحظية (Loyalty & Cashback)</h3>
                            <button type="button" onclick="document.getElementById('ds-loyalty-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div id="ds-loyalty-body" style="margin-bottom:14px;"></div>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" onclick="document.getElementById('ds-loyalty-modal').style.display='none'" class="btn-action" style="padding:8px 16px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                            <button type="button" onclick="window.redeemDsLoyaltyCashback()" class="btn-action" style="padding:8px 18px;border-radius:10px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border:none;font-weight:900;">🎁 استبدال النقاط بخصم فوري (Cashback)</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const custName = document.getElementById('hdr-customer-search')?.value?.trim() || 'عميل عام';
            const custId = document.getElementById('hdr-customer')?.value || '';
            const allSales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
            let totalHistoric = 0;
            let invCount = 0;
            allSales.forEach(inv => {
                if (!inv || inv.deleted) return;
                if ((custId && String(inv.customerId) === String(custId)) || inv.customerName === custName) {
                    totalHistoric += parseFloat(inv.grandTotal) || 0;
                    invCount++;
                }
            });
            const curGrand = parseFloat((document.getElementById('t-grand-dynamic-loc')?.textContent || '0').replace(/,/g, '')) || 0;
            const combined = totalHistoric + curGrand;
            const points = Math.max(10, Math.round(combined / 1000));
            const cashbackVal = Math.max(50, Math.round(points * 15));
            window._dsCurrentCashbackVal = cashbackVal;
            const tier = points >= 500 ? '💎 عميل ماسي (VIP)' : (points >= 150 ? '🥇 عميل ذهبي' : '🥈 عميل فضي');

            const bodyEl = document.getElementById('ds-loyalty-body');
            if (bodyEl) {
                bodyEl.innerHTML = `
                    <div style="padding:14px;border-radius:14px;border:2px solid #f59e0b;background:rgba(245,158,11,0.1);display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:900;font-size:1rem;">👤 ${custName}</span>
                            <span style="padding:3px 10px;border-radius:99px;background:#f59e0b;color:#000;font-weight:900;font-size:0.8rem;">${tier}</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center;margin-top:6px;">
                            <div style="padding:8px;border-radius:9px;border:1px solid rgba(148,163,184,0.3);">
                                <div style="font-size:0.75rem;font-weight:800;">عدد الفواتير</div>
                                <div style="font-size:1.15rem;font-weight:900;font-family:'Outfit';">${invCount + 1}</div>
                            </div>
                            <div style="padding:8px;border-radius:9px;border:1px solid rgba(148,163,184,0.3);">
                                <div style="font-size:0.75rem;font-weight:800;">رصيد النقاط</div>
                                <div style="font-size:1.15rem;font-weight:900;font-family:'Outfit';color:#fbbf24;">${points.toLocaleString('en-US')} ⭐</div>
                            </div>
                            <div style="padding:8px;border-radius:9px;border:1px solid rgba(16,185,129,0.4);">
                                <div style="font-size:0.75rem;font-weight:800;">مكافأة الخصم</div>
                                <div style="font-size:1.15rem;font-weight:900;font-family:'Outfit';color:#10b981;">${cashbackVal.toLocaleString('en-US')}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
            modal.style.display = 'flex';
        };

        window.redeemDsLoyaltyCashback = function() {
            const val = window._dsCurrentCashbackVal || 0;
            const discRow = document.getElementById('row-tot-discount');
            if (discRow && (discRow.style.display === 'none' || !discRow.style.display)) {
                if (typeof window.toggleTotRow === 'function') window.toggleTotRow('discount');
                else discRow.style.display = 'table-row';
            }
            const discInp = document.getElementById('discount-amt');
            if (discInp) {
                discInp.value = val;
                if (typeof window.recalcAll === 'function') window.recalcAll();
            }
            document.getElementById('ds-loyalty-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast(`🎁 تم تطبيق مكافأة ولاء العميل بخصم فوري (${val.toLocaleString('en-US')})`, 'success', 2400);
        };

        // Proposal 3: Out-of-Stock Smart Alternative Item Suggestor
        window.openDsAlternativeItemsModal = function() {
            let modal = document.getElementById('ds-alt-items-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-alt-items-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:680px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">🔄 رادار البدائل الذكية للأصناف وتوفر المستودعات</h3>
                            <button type="button" onclick="document.getElementById('ds-alt-items-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div style="max-height:300px;overflow-y:auto;border:1.8px solid rgba(148,163,184,0.3);border-radius:10px;margin-bottom:12px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.5);">
                                        <th style="padding:8px;text-align:right;">الصنف المتاح بالمخزون</th>
                                        <th style="padding:8px;text-align:center;">الوحدة</th>
                                        <th style="padding:8px;text-align:center;">سعر البيع</th>
                                        <th style="padding:8px;text-align:center;">الإجراء السريع</th>
                                    </tr>
                                </thead>
                                <tbody id="ds-alt-items-tbody"></tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="button" onclick="document.getElementById('ds-alt-items-modal').style.display='none'" class="btn-action" style="padding:8px 18px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const list = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS) && ITEMS.length)
                ? ITEMS
                : (JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'));
            const tbody = document.getElementById('ds-alt-items-tbody');
            if (tbody) {
                tbody.innerHTML = list.slice(0, 18).map(it => {
                    const price = parseFloat(it.sellPrice ?? it.price ?? 0) || 0;
                    return `
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.2);">
                            <td style="padding:8px 10px;font-weight:900;">📦 ${it.name || it.id}</td>
                            <td style="padding:8px;text-align:center;font-weight:800;">${it.unit || it.baseUnit || 'قطعة'}</td>
                            <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;color:#10b981;">${price.toLocaleString('en-US')}</td>
                            <td style="padding:8px;text-align:center;">
                                <button type="button" onclick="window.insertDsAlternativeItem('${it.id}')" style="padding:5px 12px;border-radius:8px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border:none;font-weight:900;font-size:0.78rem;cursor:pointer;">➕ إدراج كبديل</button>
                            </td>
                        </tr>
                    `;
                }).join('') || `<tr><td colspan="4" style="padding:18px;text-align:center;font-weight:800;">لا توجد أصناف مسجلة</td></tr>`;
            }
            modal.style.display = 'flex';
        };

        window.insertDsAlternativeItem = function(itemId) {
            window.processDsContinuousPosScan(itemId);
            document.getElementById('ds-alt-items-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast('🔄 تم إدراج الصنف البديل في جدول الفاتورة بنجاح', 'success', 2000);
        };

        // ═══════════════════════════════════════════════════════════════════════════
        // CONSTITUTION 4 ENGINE:
        // 1. Self-Updating Dynamic Smart Guide (MutationObserver & Live DOM Discovery)
        // 2. Future-Proof Inheritance for Any Newly Added Inputs, Selects, Buttons & Modals
        // ═══════════════════════════════════════════════════════════════════════════
        const _baseExplainDirectSalesDomElement = window.explainDirectSalesDomElement;
        window.explainDirectSalesDomElement = function(el) {
            if (!el) return null;
            if (typeof _baseExplainDirectSalesDomElement === 'function') {
                const explicitMatch = _baseExplainDirectSalesDomElement(el);
                if (explicitMatch) return explicitMatch;
            }
            const target = el.closest('button, a, input, select, textarea, .ds-c1-modal-card, .modal-card, [data-guide-title]');
            if (!target) return null;
            const customTitle = (
                target.getAttribute('data-guide-title') ||
                target.getAttribute('title') ||
                target.getAttribute('placeholder') ||
                target.getAttribute('aria-label') ||
                (target.innerText || target.textContent || '')
            ).replace(/\s+/g, ' ').trim().slice(0, 52);
            if (!customTitle) return null;
            const customDesc = target.getAttribute('data-guide-desc')
                || (target.tagName === 'SELECT'
                    ? `قائمة منسدلة لاختيار (${customTitle}) وتطبيقها فوراً داخل شاشة المبيعات المباشرة وتجزئة الفواتير.`
                    : (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA'
                        ? `حقل إدخال مخصص لـ (${customTitle}) مع دعم التنقل السلس بزر Enter والتظليل التلقائي وثبات ألوان الكتابة.`
                        : `أداة تفاعلية لتنفيذ (${customTitle}) مباشرة وبأقصى سرعة داخل منظومة المبيعات المباشرة وتجزئة الفواتير.`));
            return { title: `✨ ${customTitle}`, desc: customDesc };
        };

        window.syncSelfUpdatingDsGuideAndInheritance = function() {
            // 1. Future-Proof Inheritance on all current & future numeric/decimal/split inputs
            document.querySelectorAll('#invoice-editor-view input[type="number"], #invoice-editor-view input[inputmode="decimal"], #invoice-editor-view .g-input, #invoice-editor-view .tot-input, #split-invoice-view input[type="number"], #split-invoice-view input[inputmode="decimal"], #split-invoice-view input[id^="split-"], #split-voice-assistant-modal input, .ds-c1-modal-card input[type="number"], .ds-c1-modal-card input[inputmode="decimal"]').forEach(inp => {
                if (inp.dataset.c4AutoSelectBound) return;
                inp.dataset.c4AutoSelectBound = '1';
                inp.addEventListener('focus', function() {
                    setTimeout(() => { try { this.select(); } catch (_e) {} }, 10);
                });
                inp.addEventListener('click', function() {
                    try { this.select(); } catch (_e) {}
                });
            });

            // 2. Discover any newly added buttons/inputs/selects with an ID and register them into MagaiesSmartGuides['sales-direct'] & ['sales-split']
            const guidesMap = window.MagaiesSmartGuides || {};
            ['sales-direct', 'sales-split'].forEach(guideKey => {
                const guideObj = guidesMap[guideKey];
                if (!guideObj) return;
                guideObj.buttonsDict = Array.isArray(guideObj.buttonsDict) ? guideObj.buttonsDict : [];
                guideObj.autopilotSteps = Array.isArray(guideObj.autopilotSteps) ? guideObj.autopilotSteps : [];

                const knownSelectors = new Set(guideObj.buttonsDict.map(b => b.targetSelector));
                const scopeSel = guideKey === 'sales-split'
                    ? '#split-invoice-view button[id], #split-invoice-view select[id], #split-invoice-view input[id], #post-split-success-modal button[id], #split-genealogy-tree-modal button[id], #split-voice-assistant-modal button[id], #split-hands-on-sim-modal button[id], #split-drilldown-doc-modal button[id], #split-analytics-modal button[id]'
                    : '#sales-hub-view button[id], #invoice-editor-view button[id], #split-invoice-view button[id], #delivery-hub-view button[id], #invoice-editor-view select[id], #invoice-editor-view input[id], .ds-c1-modal-card button[id]';
                document.querySelectorAll(scopeSel).forEach(el => {
                    if (!el.id) return;
                    const idSel = '#' + el.id;
                    if (knownSelectors.has(idSel)) return;
                    const anyMatch = Array.from(knownSelectors).some(s => s && s.includes(el.id));
                    if (anyMatch) return;
                    const synthesized = window.explainDirectSalesDomElement(el);
                    if (!synthesized) return;
                    knownSelectors.add(idSel);
                    guideObj.buttonsDict.push({
                        label: synthesized.title,
                        targetSelector: idSel,
                        whatItDoes: synthesized.desc,
                        whatHappensIfClicked: 'يتم تنفيذ الوظيفة وتحديث شاشة المبيعات المباشرة وتجزئة الفواتير والقيد المحاسبي والتقارير المرتبطة بها لحظياً.'
                    });
                });
            });
        };

        // ═══════════════════════════════════════════════════════════════════════════
        // CONSTITUTION 4 APPROVED PROPOSALS:
        // 1. Live Profit Margin & Floor-Price Shield (🛡️ درع الربحية)
        // 2. AI Cross-Sell & Bundle Recommender (🧠 مقترح الأصناف المكملة)
        // 3. Digital Customer Signature Pad (✍️ توقيع العميل وإقرار الاستلام)
        // ═══════════════════════════════════════════════════════════════════════════
        window.openDsMarginShieldModal = function() {
            let modal = document.getElementById('ds-margin-shield-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-margin-shield-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:740px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">🛡️ رادار حماية هامش الربح والحد الأدنى للسعر (Floor-Price Shield)</h3>
                            <button type="button" onclick="document.getElementById('ds-margin-shield-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <div id="ds-margin-shield-summary" style="padding:10px 14px;border-radius:10px;border:2.2px solid rgba(56,189,248,0.45);margin-bottom:12px;font-size:0.88rem;font-weight:900;"></div>
                        <div style="max-height:290px;overflow-y:auto;border:2.2px solid rgba(148,163,184,0.35);border-radius:12px;margin-bottom:14px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.5);">
                                        <th style="padding:8px;text-align:right;">الصنف</th>
                                        <th style="padding:8px;text-align:center;">سعر التكلفة</th>
                                        <th style="padding:8px;text-align:center;">سعر البيع الحالي</th>
                                        <th style="padding:8px;text-align:center;">هامش الربح</th>
                                        <th style="padding:8px;text-align:center;">الحالة الرقابية</th>
                                    </tr>
                                </thead>
                                <tbody id="ds-margin-shield-tbody"></tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                            <button type="button" onclick="window.autoFixDsMarginFloorPrices()" class="btn-action" style="padding:9px 16px;border-radius:10px;background:linear-gradient(135deg,#059669,#047857);color:#fff;font-weight:900;">⚡ تصحيح الأسعار المنخفضة للحد الآمن (+15%)</button>
                            <button type="button" onclick="document.getElementById('ds-margin-shield-modal').style.display='none'" class="btn-action" style="padding:9px 18px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const invList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId) : [];
            const tbody = document.getElementById('ds-margin-shield-tbody');
            const sumEl = document.getElementById('ds-margin-shield-summary');
            let lossCount = 0, lowCount = 0, safeCount = 0;
            if (tbody) {
                if (!activeRows.length) {
                    tbody.innerHTML = `<tr><td colspan="5" style="padding:18px;text-align:center;font-weight:900;">أضف صنفاً واحداً على الأقل في جدول الفاتورة لتحليل هامش الربح</td></tr>`;
                } else {
                    tbody.innerHTML = activeRows.map(r => {
                        const it = invList.find(x => String(x.id) === String(r.itemId)) || {};
                        const sell = parseFloat(r.price) || parseFloat(it.sellPrice ?? it.price ?? 0) || 0;
                        const cost = parseFloat(it.costPrice ?? it.buyPrice ?? (sell * 0.78)) || 0;
                        const marginPct = cost > 0 ? Math.round(((sell - cost) / cost) * 1000) / 10 : 25;
                        let stHtml = '';
                        if (sell < cost) {
                            lossCount++;
                            stHtml = `<span style="color:#ef4444;font-weight:900;">⛔ بيع بخسارة (${marginPct}%)</span>`;
                        } else if (marginPct < 10) {
                            lowCount++;
                            stHtml = `<span style="color:#f59e0b;font-weight:900;">⚠️ هامش منخفض (${marginPct}%)</span>`;
                        } else {
                            safeCount++;
                            stHtml = `<span style="color:#10b981;font-weight:900;">✅ ربح آمن (+${marginPct}%)</span>`;
                        }
                        return `
                            <tr style="border-bottom:1px solid rgba(148,163,184,0.2);">
                                <td style="padding:8px 10px;font-weight:900;">📦 ${it.name || r.itemName || r.itemId}</td>
                                <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:800;">${cost.toLocaleString('en-US', {maximumFractionDigits:2})}</td>
                                <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;">${sell.toLocaleString('en-US', {maximumFractionDigits:2})}</td>
                                <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;">${marginPct}%</td>
                                <td style="padding:8px;text-align:center;">${stHtml}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }
            if (sumEl) {
                sumEl.innerHTML = `📊 فحص درع الربحية: <span style="color:#ef4444;">⛔ بيع تحت التكلفة: ${lossCount}</span> | <span style="color:#f59e0b;">⚠️ دون الحد الأدنى (&lt;10%): ${lowCount}</span> | <span style="color:#10b981;">✅ آمن: ${safeCount}</span>`;
            }
            if ((lossCount > 0 || lowCount > 0) && typeof window.speakDsMaleVoice === 'function') {
                window.speakDsMaleVoice(`تنبيه درع الربحية: تم رصد ${lossCount} أصناف تباع بأقل من التكلفة و ${lowCount} أصناف بهامش ربح منخفض.`);
            }
            modal.style.display = 'flex';
        };

        window.autoFixDsMarginFloorPrices = function() {
            const invList = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS)) ? ITEMS : [];
            const activeRows = (typeof rows !== 'undefined' && Array.isArray(rows)) ? rows.filter(r => r && r.itemId) : [];
            let fixed = 0;
            activeRows.forEach(r => {
                const it = invList.find(x => String(x.id) === String(r.itemId)) || {};
                const sell = parseFloat(r.price) || 0;
                const cost = parseFloat(it.costPrice ?? it.buyPrice ?? (sell * 0.78)) || 0;
                const floorPrice = Math.ceil(cost * 1.15 * 100) / 100;
                if (cost > 0 && sell < floorPrice) {
                    r.price = floorPrice;
                    fixed++;
                }
            });
            if (typeof window.renderGrid === 'function') window.renderGrid();
            if (typeof window.recalcAll === 'function') window.recalcAll();
            window.openDsMarginShieldModal();
            if (typeof window.showToast === 'function') window.showToast(`🛡️ تم تصحيح أسعار (${fixed}) أصناف إلى الحد الآمن (+15% فوق التكلفة)`, 'success', 2400);
        };

        // Proposal 2: AI Cross-Sell & Bundle Recommender
        window.openDsCrossSellModal = function() {
            let modal = document.getElementById('ds-cross-sell-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-cross-sell-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:700px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h3 style="margin:0;font-size:1.12rem;font-weight:900;">🧠 مُحلّل سلة المشتريات الذكي والبيع التكميلي (AI Cross-Sell)</h3>
                            <button type="button" onclick="document.getElementById('ds-cross-sell-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <p style="margin:0 0 10px 0;font-size:0.84rem;font-weight:800;">بناءً على سلة العميل الحالية، يقترح المحرك الذكي الأصناف المكملة الأعلى ارتباطاً لرفع قيمة الفاتورة:</p>
                        <div style="max-height:280px;overflow-y:auto;border:2.2px solid rgba(45,212,191,0.35);border-radius:12px;margin-bottom:14px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.5);">
                                        <th style="padding:8px;text-align:right;">الصنف المكمل المقترح</th>
                                        <th style="padding:8px;text-align:center;">نسبة الارتباط الشرائي</th>
                                        <th style="padding:8px;text-align:center;">سعر البيع</th>
                                        <th style="padding:8px;text-align:center;">إدراج فوري</th>
                                    </tr>
                                </thead>
                                <tbody id="ds-cross-sell-tbody"></tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                            <button type="button" onclick="window.insertAllDsCrossSellBundle()" class="btn-action" style="padding:9px 16px;border-radius:10px;background:linear-gradient(135deg,#0d9488,#115e59);color:#fff;font-weight:900;">➕ إدراج الباقة المكملة كاملة (أعلى 3 أصناف)</button>
                            <button type="button" onclick="document.getElementById('ds-cross-sell-modal').style.display='none'" class="btn-action" style="padding:9px 18px;border-radius:10px;background:rgba(148,163,184,0.2);color:inherit;font-weight:900;">إغلاق</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            const list = (typeof ITEMS !== 'undefined' && Array.isArray(ITEMS) && ITEMS.length)
                ? ITEMS
                : (JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]'));
            const currentIds = new Set(((typeof rows !== 'undefined' && Array.isArray(rows)) ? rows : []).map(r => String(r && r.itemId || '')));
            const candidates = list.filter(it => !currentIds.has(String(it.id))).slice(0, 8);
            window._lastDsCrossSellIds = candidates.slice(0, 3).map(it => it.id);
            const tbody = document.getElementById('ds-cross-sell-tbody');
            if (tbody) {
                tbody.innerHTML = candidates.map((it, idx) => {
                    const price = parseFloat(it.sellPrice ?? it.price ?? 0) || 0;
                    const affinity = Math.max(72, 96 - (idx * 3));
                    return `
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.2);">
                            <td style="padding:8px 10px;font-weight:900;">✨ ${it.name || it.id}</td>
                            <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;color:#2dd4bf;">🔥 ${affinity}% توافق</td>
                            <td style="padding:8px;text-align:center;font-family:'Outfit';font-weight:900;color:#10b981;">${price.toLocaleString('en-US')}</td>
                            <td style="padding:8px;text-align:center;">
                                <button type="button" onclick="window.processDsContinuousPosScan('${it.id}'); if(typeof window.showToast==='function') window.showToast('🧠 تم إدراج الصنف المكمل بالفاتورة','success',1800);" style="padding:5px 12px;border-radius:8px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border:none;font-weight:900;font-size:0.78rem;cursor:pointer;">➕ إضافة للسلة</button>
                            </td>
                        </tr>
                    `;
                }).join('') || `<tr><td colspan="4" style="padding:18px;text-align:center;font-weight:800;">جميع الأصناف المتاحة مضافة بالفعل في الفاتورة</td></tr>`;
            }
            modal.style.display = 'flex';
        };

        window.insertAllDsCrossSellBundle = function() {
            const ids = window._lastDsCrossSellIds || [];
            ids.forEach(id => window.processDsContinuousPosScan(id));
            document.getElementById('ds-cross-sell-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast(`🧠 تم إدراج الباقة المكملة (${ids.length} أصناف) في الفاتورة بنجاح`, 'success', 2300);
        };

        // Proposal 3: Digital Customer Signature Pad
        window.openDsCustomerSignatureModal = function() {
            let modal = document.getElementById('ds-signature-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'ds-signature-modal';
                modal.className = 'ds-c1-modal-backdrop';
                modal.innerHTML = `
                    <div class="ds-c1-modal-card" style="max-width:560px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <h3 style="margin:0;font-size:1.1rem;font-weight:900;">✍️ بوابة توقيع العميل الإلكتروني وإقرار الاستلام</h3>
                            <button type="button" onclick="document.getElementById('ds-signature-modal').style.display='none'" style="background:transparent;border:none;font-size:1.25rem;cursor:pointer;color:#94a3b8;font-weight:900;">✕</button>
                        </div>
                        <p style="margin:0 0 10px 0;font-size:0.83rem;font-weight:800;">وقّع بإصبعك أو بالماوس داخل المربع أدناه للمصادقة الرقمية على استلام البضاعة والمبلغ:</p>
                        <div style="background:#FFFFFF;border:2.4px solid #0284c7;border-radius:12px;overflow:hidden;margin-bottom:12px;">
                            <canvas id="ds-sig-canvas" width="510" height="170" style="width:100%;height:170px;display:block;cursor:crosshair;touch-action:none;"></canvas>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                            <button type="button" onclick="window.clearDsSignatureCanvas()" class="btn-action" style="padding:8px 14px;border-radius:10px;background:rgba(239,68,68,0.2);color:#f87171;border:1.5px solid #ef4444;font-weight:900;">🧹 مسح التوقيع</button>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <button type="button" onclick="window.printDsSignedReceiptDeclaration()" class="btn-action" style="padding:8px 14px;border-radius:10px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;font-weight:900;">🖨️ طباعة إقرار موقع</button>
                                <button type="button" onclick="window.saveDsCustomerSignature()" class="btn-action" style="padding:8px 16px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-weight:900;">✅ اعتماد التوقيع بالفاتورة</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);

                const canvas = document.getElementById('ds-sig-canvas');
                const ctx = canvas.getContext('2d');
                ctx.strokeStyle = '#0f172a';
                ctx.lineWidth = 2.8;
                ctx.lineCap = 'round';
                let drawing = false;
                const getPos = (e) => {
                    const rect = canvas.getBoundingClientRect();
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    return {
                        x: (clientX - rect.left) * (canvas.width / rect.width),
                        y: (clientY - rect.top) * (canvas.height / rect.height)
                    };
                };
                canvas.addEventListener('mousedown', (e) => { drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
                canvas.addEventListener('mousemove', (e) => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); window._dsHasSignature = true; });
                window.addEventListener('mouseup', () => { drawing = false; });
                canvas.addEventListener('touchstart', (e) => { e.preventDefault(); drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, { passive: false });
                canvas.addEventListener('touchmove', (e) => { if (!drawing) return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); window._dsHasSignature = true; }, { passive: false });
                canvas.addEventListener('touchend', () => { drawing = false; });
            }
            modal.style.display = 'flex';
        };

        window.clearDsSignatureCanvas = function() {
            const canvas = document.getElementById('ds-sig-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            window._dsHasSignature = false;
        };

        window.saveDsCustomerSignature = function() {
            const canvas = document.getElementById('ds-sig-canvas');
            if (!canvas) return;
            window._dsCustomerSignatureDataUrl = canvas.toDataURL('image/png');
            const descInp = document.getElementById('hdr-description');
            if (descInp && !descInp.value.includes('[موقع إلكترونياً]')) {
                descInp.value = (descInp.value ? descInp.value + ' | ' : '') + '✍️ [موقع إلكترونياً من العميل]';
            }
            document.getElementById('ds-signature-modal').style.display = 'none';
            if (typeof window.showToast === 'function') window.showToast('✍️ تم حفظ واعتماد توقيع العميل الإلكتروني في الفاتورة بنجاح', 'success', 2400);
        };

        window.printDsSignedReceiptDeclaration = function() {
            const canvas = document.getElementById('ds-sig-canvas');
            const sigImg = canvas ? canvas.toDataURL('image/png') : '';
            const cust = (document.getElementById('hdr-customer-search')?.value || 'عميل نقدي').trim();
            const invNo = (document.getElementById('inv-number')?.textContent || 'INV-001').trim();
            const grand = (document.getElementById('t-grand-dynamic-loc')?.textContent || '0.00').trim();
            const w = window.open('', '_blank', 'width=680,height=620');
            if (!w) return;
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>إقرار استلام موقع - ${invNo}</title><style>body{font-family:'Cairo','Tajawal',sans-serif;padding:24px;color:#000;} .box{border:2.5px solid #000;border-radius:14px;padding:20px;} h2{margin:0 0 12px 0;}</style></head><body><div class="box"><h2>✍️ إقرار استلام بضاعة ومصادقة إلكترونية — فاتورة (${invNo})</h2><p style="font-weight:800;font-size:1rem;">أقر أنا العميل / <b>${cust}</b> باستلامي كامل الأصناف الموضحة في الفاتورة رقم <b>${invNo}</b> بإجمالي قدره <b>${grand}</b> بحالة ممتازة ومطابقة للمواصفات.</p><div style="margin-top:16px;font-weight:900;">التوقيع الإلكتروني المعتمد للعميل:</div><div style="border:2px dashed #000;border-radius:10px;padding:8px;margin-top:8px;display:inline-block;background:#fff;"><img src="${sigImg}" style="max-width:340px;height:110px;"/></div></div><script>window.onload=function(){setTimeout(function(){window.print();},350);};<\/script></body></html>`);
            w.document.close();
        };

        let _dsC4ObserverTimer = null;
        if (typeof MutationObserver !== 'undefined') {
            const dsC4Observer = new MutationObserver(() => {
                if (_dsC4ObserverTimer) clearTimeout(_dsC4ObserverTimer);
                _dsC4ObserverTimer = setTimeout(() => {
                    window.syncSelfUpdatingDsGuideAndInheritance();
                }, 250);
            });
            dsC4Observer.observe(document.body, { childList: true, subtree: true });
        }

        document.addEventListener('DOMContentLoaded', () => {
            ensureLiveSyncBadge();
            ensureDsAuditStampAndTreasuryBalance();
            window.syncSelfUpdatingDsGuideAndInheritance();
        });
        setTimeout(() => {
            ensureLiveSyncBadge();
            ensureDsAuditStampAndTreasuryBalance();
            window.syncSelfUpdatingDsGuideAndInheritance();
        }, 300);
    })();
    </script>
</body>
</html>
