<?php 
$db_sales_grid_config = null;
$preloadedUnitsJson = '[]';
$preloadedItemsJson = '[]';
try {
    require_once __DIR__ . '/api/db.php';
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'grid_config_sales_grid'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['setting_value'])) {
            $db_sales_grid_config = json_decode($row['setting_value'], true);
        }

        $stmtUnits = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_global_units_v1'");
        $uVal = $stmtUnits ? $stmtUnits->fetchColumn() : null;
        if ($uVal) $preloadedUnitsJson = $uVal;

        $stmtItems = $pdo->query("SELECT data FROM items WHERE is_deleted = 0 OR is_deleted IS NULL");
        $pItems = [];
        while ($r = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($r['data'])) {
                $d = json_decode($r['data'], true);
                if ($d && empty($d['deleted'])) $pItems[] = $d;
            }
        }
        $preloadedItemsJson = json_encode($pItems, JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {}
?>
<?php $page_title = 'المبيعات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
// CRITICAL: Unregister ALL Service Workers and clear ALL caches immediately
// This runs BEFORE any other JS to ensure fresh files
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(regs) {
        regs.forEach(function(reg) { reg.unregister(); });
        if (regs.length > 0) console.log('[SW] Unregistered ' + regs.length + ' service workers');
    });
}
if (typeof caches !== 'undefined') {
    caches.keys().then(function(names) {
        names.forEach(function(name) { caches.delete(name); });
        if (names.length > 0) console.log('[Cache] Cleared ' + names.length + ' caches');
    });
}
</script>
<script>
window._PRELOADED_UNITS = <?= $preloadedUnitsJson ?: '[]' ?>;
window._PRELOADED_ITEMS = <?= $preloadedItemsJson ?: '[]' ?>;
try {
    if (Array.isArray(window._PRELOADED_UNITS) && window._PRELOADED_UNITS.length > 0) {
        localStorage.setItem('erp_global_units_v1', JSON.stringify(window._PRELOADED_UNITS));
    }
    if (Array.isArray(window._PRELOADED_ITEMS) && window._PRELOADED_ITEMS.length > 0) {
        localStorage.setItem('erp_items_v1', JSON.stringify(window._PRELOADED_ITEMS));
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المبيعات | Al-Sajana Protocol</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css?v=20261008_v21themes_elegant">
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
    .ctrl-input:focus, .v-input:focus, .system-currency-select:focus {
        background: #020104 !important;
        border-color: #38bdf8 !important;
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.4), inset 0 1px 2px rgba(0,0,0,0.6) !important;
        transform: translateY(-1px);
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
        $fColor = $cfg['fontColor'] ?? '#ffffff';
        $tAlign = $cfg['align'] ?? 'center';
        
        echo "#pos-grid, #pos-grid *, #pos-grid thead th, #pos-grid tbody td, #pos-grid tbody td input, #pos-grid tbody td select, #pos-grid tbody td span { font-family: " . $fFamily . " !important; }\n";
        echo "#pos-grid thead th { font-size: " . $fSize . " !important; text-align: " . $tAlign . " !important; }\n";
        echo "#pos-grid tbody td { font-size: " . $fSize . " !important; font-weight: " . $fWeight . " !important; color: " . $fColor . " !important; text-align: " . $tAlign . " !important; }\n";
        echo "#pos-grid tbody td input, #pos-grid tbody td select, #pos-grid tbody td span:not(.btn-icon), #pos-grid tbody td div:not(.custom-dropdown-list), #pos-grid tbody td .g-input, #pos-grid tbody td .g-select, #pos-grid tbody td .g-readonly, #pos-grid tbody td .total-val { font-size: " . $fSize . " !important; font-weight: " . $fWeight . " !important; color: " . $fColor . " !important; text-align: " . $tAlign . " !important; }\n";
        
        foreach ($cfg['columns'] as $col) {
            $colId = $col['id'];
            $colVisible = !isset($col['visible']) || $col['visible'];
            $colColor = $col['color'] ?? $fColor;
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


        
    <!-- ═══ SALES HUB VIEW ═══ -->
    <div id="sales-hub-view" class="animate-fade" style="display: flex; flex-direction: column; height: 100%; gap: 20px; padding: 0 5px;">
        <!-- Action Cards (فاتورة جديدة, تقسيم الفاتورة, تسليم, فاتورة مبدئية) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <button onclick="openInvoiceEditor('pending_delivery')" class="hub-action-card card-blue animate-scale stagger-1">
                <div class="icon">➕</div>
                <div class="text-content">
                    <div class="title">فاتورة جديدة</div>
                    <div class="subtitle">إنشاء فاتورة مبيعات (بدون خصم مخزون)</div>
                </div>
            </button>
            <button onclick="openSplitInvoiceView()" class="hub-action-card card-pink animate-scale stagger-2">
                <div class="icon">✂️</div>
                <div class="text-content">
                    <div class="title">تقسيم الفاتورة</div>
                    <div class="subtitle">تقسيم فاتورة مبيعات إلى فاتورتين مستقلتين</div>
                </div>
            </button>
            <button onclick="openDeliveryHub()" class="hub-action-card card-green animate-scale stagger-3">
                <div class="icon">🚚</div>
                <div class="text-content">
                    <div class="title">تسليم</div>
                    <div class="subtitle">تسليم فواتير المبيعات (خصم من المخزون)</div>
                </div>
            </button>
            <button onclick="openInvoiceEditor('preliminary')" class="hub-action-card card-purple">
                <div class="icon">📋</div>
                <div class="text-content">
                    <div class="title">فاتورة مبدئية</div>
                    <div class="subtitle">عرض أسعار / غير ملزمة</div>
                </div>
            </button>
        </div>

        <!-- Recent Transactions Table -->
        <div class="neo-card" style="flex: 1; display: flex; flex-direction: column; overflow: visible; margin-bottom: 15px;">
            <div class="neo-card-title" style="font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #ef4444 !important; font-weight: 800;">📋 عمليات المبيعات الأخيرة</span>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="hub-search" class="v-input" placeholder="بحث برقم الفاتورة أو العميل..." style="width: 250px;" oninput="filterHubTransactions()">
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.05);" onclick="loadHubTransactions()">🔄 تحديث</button>
                </div>
            </div>
            
            <div class="grid-scroll-wrap" style="flex: 1; margin-top: 10px;">
                <table class="pos-grid hub-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align:right;">رقم الفاتورة</th>
                            <th style="text-align:right;">التاريخ</th>
                            <th style="text-align:right;">العميل</th>
                            <th style="text-align:right;">الصنف</th>
                            <th style="text-align:center;">الكمية</th>
                            <th style="text-align:center;">السعر</th>
                            <th style="text-align:center;">الإجمالي</th>
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
                    <h3>لا توجد عمليات مسجلة</h3>
                    <p>اضغط على "فاتورة جديدة" للبدء.</p>
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
            <div id="split-select-step" style="display: flex; flex-direction: column; flex: 1; gap: 15px;">
                <div style="display: flex; gap: 15px; align-items: center; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 5px; font-weight: bold;">البحث عن فاتورة نشطة للتقسيم:</label>
                        <input type="text" id="split-invoice-search" class="v-input" placeholder="ابحث برقم الفاتورة، اسم العميل، أو الإجمالي..." oninput="filterSplitInvoices()">
                    </div>
                    <button class="btn-action" style="width: auto; height: 42px; margin-top: auto; background: rgba(255,255,255,0.05);" onclick="loadSplitInvoices()">🔄 تحديث القائمة</button>
                    <button class="btn-action" style="width: auto; height: 42px; margin-top: auto; background: rgba(239,68,68,0.15); color: #f87171;" onclick="showSalesHub()">رجوع</button>
                </div>
                
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

                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 5px;">
                    <button class="btn-action" style="width: auto; padding: 8px 20px; background: rgba(255,255,255,0.05);" onclick="cancelCurrentSplit()">إلغاء وتراجع</button>
                    <button class="btn-action" style="width: auto; padding: 8px 30px; background: linear-gradient(135deg, #ec4899, #8b5cf6); color: white; font-weight: bold; border: none; box-shadow: 0 4px 15px rgba(236,72,153,0.3);" onclick="submitInvoiceSplit()">تأكيد واعتماد التقسيم ✂️</button>
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
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.05);" onclick="loadDeliveryTransactions()">🔄 تحديث</button>
                    <button class="btn-action" style="width: auto; padding: 6px 12px; font-size: 0.8rem; background: rgba(239,68,68,0.1); color: #ef4444;" onclick="showSalesHub()">رجوع</button>
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
                    padding: 25px; 
                    border-radius: 18px; 
                    position: relative;
                    overflow: visible;
                    width: 100%;
                ">
                    <!-- Ambient Glow & Integrated Glass Facets -->

                    <!-- Ambient Glow -->
                    <div style="position:absolute; top:0; left:0; width:100%; height:100%; overflow:hidden; border-radius:18px; z-index:0; pointer-events:none;">
                        <div style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 60%);"></div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 18px; position:relative; z-index:1;">
                        <!-- Saved Invoice Edit Advisory Banner -->
                        <div id="saved-invoice-edit-banner" style="display:none; margin-bottom: 8px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(180, 83, 9, 0.25)); border: 1.5px solid rgba(245, 158, 11, 0.6); border-radius: 12px; padding: 12px 18px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);">
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

    <!-- ROW 1 WRAPPER (Invoice Info & Financial Strip) -->
    <div style="display: flex; width: 100%; gap: 15px; margin-bottom: 12px; transition: all 0.4s ease;">
        
        <!-- Row 1: Invoice Number & Date -->
        <div id="invoice-info-box" style="flex: 1; display: flex; gap: 12px; width: 100%; padding: 4px 10px; border-radius: 12px; align-items: center; justify-content: space-between;">
            <div class="h-form-group group-invoice" style="flex: 1.1; display:flex; align-items:center; gap:8px; margin:0; min-width: 0;">
                <label class="h-label" style="margin:0; font-size: 0.95rem; font-weight:bold; white-space:nowrap;">رقم الفاتورة:</label>
                <div class="gold-serial-wrap" style="display:flex; align-items:center; justify-content:space-between; flex:1; padding: 3px 8px; border-radius: 9px; min-width: 0;">
                    <button class="pro-serial-btn" onclick="loadPreviousInvoice()" title="السابقة" style="background: transparent; border: none; font-size: 1.1rem; cursor: pointer; padding: 0 5px; opacity:0.85; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85">❮</button>
                    <span id="inv-number" class="pro-serial-number gold-serial-text" style="font-size: 1.1rem; font-family: 'Outfit', sans-serif; font-weight: 900; letter-spacing: 1.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">---</span>
                    <input type="hidden" id="hdr-inv-no">
                    <button class="pro-serial-btn" onclick="loadNextInvoice()" title="التالية" style="background: transparent; border: none; font-size: 1.1rem; cursor: pointer; padding: 0 5px; opacity:0.85; transition:0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85">❯</button>
                </div>
            </div>

            <div style="width: 1px; height: 28px; background: rgba(255,255,255,0.1); margin: 0 2px;"></div>

            <div class="h-form-group group-date" style="flex: 0.9; display:flex; align-items:center; gap:8px; margin:0; min-width: 0;">
                <label class="h-label" id="hdr-date-label" style="margin:0; font-size: 0.95rem; font-weight:bold; white-space:nowrap;">التاريخ:</label>
                <input type="date" class="v-input ctrl-input" id="hdr-date" style="flex:1; text-align: center; border-radius: 8px; padding:3px 6px; font-family:'Outfit',sans-serif; font-weight:bold; font-size:1.05rem; min-width: 0;">
            </div>

            <div style="width: 1px; height: 28px; background: rgba(255,255,255,0.1); margin: 0 2px;"></div>
            <div id="sales-user-audit-container" style="display:flex; align-items:center;"></div>
        </div>

        <!-- RIGHT: Financial Strip -->
        <div id="fin-strip" class="fin-strip-inline" style="flex: 0; width: 0; opacity: 0; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: flex;">
            <div style="width: 100%; background: linear-gradient(90deg, rgba(15,23,42,0.4), rgba(30,58,138,0.2)); border: 1px solid rgba(56, 189, 248, 0.6); border-radius: 12px; padding: 4px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2), inset 0 2px 5px rgba(255,255,255,0.05); min-width: auto;">
                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                    <span style="font-size: 1.1rem;">📊</span>
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight: 800; color:#fff; font-size:0.75rem; white-space: nowrap;">الحالة المالية للعميل</span>
                        <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span></span>
                    </div>
                </div>
                
                <div style="display:flex; gap: 12px; align-items: center; flex: 1; justify-content: flex-end; min-width: 0;">
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#94a3b8; font-weight:700; white-space: nowrap;">الرصيد الدفتري</span>
                        <span id="fin-ledger" style="font-weight:800; color:#fff; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#f59e0b; font-weight:700; white-space: nowrap;">شيكات معلقة</span>
                        <span id="fin-checks" style="font-weight:800; color:#f59e0b; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-size:0.6rem; color:#ef4444; font-weight:700; white-space: nowrap;">المديونية</span>
                        <span id="fin-total-debt" style="font-weight:800; color:#ef4444; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 0;">
                        <span style="font-weight:800; color:#38bdf8; font-size:0.75rem; white-space: nowrap;">حد الائتمان</span>
                        <span id="fin-credit-limit" style="font-weight:800; color:#38bdf8; font-size:0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">0</span>
                    </div>
                    <div style="width:1px; height:15px; background:rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                    
                    <div style="display:flex; flex-direction:column; align-items:center; background: rgba(16,185,129,0.15); padding: 3px 8px; border-radius: 8px; border: 1px solid rgba(16,185,129,0.3); flex-shrink: 0; text-align: center;">
                        <span style="font-size:0.65rem; color:#34d399; font-weight:800; white-space: nowrap;">✨ المتاح</span>
                        <span id="fin-remaining" style="font-weight:900; color:#34d399; font-size:0.85rem; white-space: nowrap;">0</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Unified Row 1: Customer, Currency & Exchange Rate, Warehouse, Cost Center -->
    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 12px; width: 100%; align-items: center;">
        
        <!-- 1. Customer Field -->
        <div class="h-form-group group-supplier" style="flex: 2.8; min-width: 325px; display:flex; align-items:center; gap:10px;">
            <label class="h-label" style="margin:0; white-space:nowrap; font-size: 1.25rem !important; font-weight: 800 !important; color: #34d399;">👤 العميل <span id="fin-status-badge" style="display:none;"><span class="status-dot neutral"></span></span></label>
            <div style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex: 1; width: 100%;">
                <div class="custom-dropdown" style="flex:1; min-width: 0;">
                    <div class="custom-dropdown-input-wrap" style="position: relative;">
                        <input type="text" class="ctrl-input v-input" id="hdr-customer-search" placeholder="ابحث عن اسم العميل..." onfocus="showCustomerDropdown()" onblur="hideCustomerDropdownDelayed()" oninput="filterCustomerDropdown()" style="width:100%; padding-left: 95px !important; font-size: 1.45rem !important; font-weight: 800 !important; color: #6ee7b7 !important;" autocomplete="one-time-code">
                        <button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount('cust-' + document.getElementById('hdr-customer').value)" style="display: none;" title="دفتر الأستاذ العام">📖 أستاذ</button>
                        <input type="hidden" id="hdr-customer" onchange="if(typeof onCustomerChange === 'function') onCustomerChange()">
                        <div class="custom-dropdown-list" id="customer-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none;">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>
                <button onclick="openModal('add-customer-modal')" style="flex-shrink: 0; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: 1px solid rgba(16,212,143,0.3); width: 38px; height: 38px; border-radius: 8px; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2); transition: 0.2s;" title="إضافة عميل">
                    ➕
                </button>
            </div>
        </div>

        <!-- 2. Exchange Rate Field -->
        <div class="h-form-group group-currency" style="flex: 1.6; min-width: 200px; display:flex; align-items:center; gap:10px;">
            <label class="h-label" style="margin:0; white-space:nowrap;">💱 الصرف</label>
            <div style="display: flex; flex-direction: row; align-items: center; gap: 5px; flex: 1; width: 100%;">
                <select class="v-input ctrl-input system-currency-select" id="hdr-currency" onchange="handleCurrencyChange(this.value)" style="flex: 1.2; min-width:0;">
                    <option value="SDG">🇸🇩 جنيه</option>
                    <option value="USD">🇺🇸 دولار</option>
                    <option value="SAR">🇸🇦 ريال</option>
                    <option value="AED">🇦🇪 درهم</option>
                    <option value="EGP">🇪🇬 مصري</option>
                </select>
                <input type="number" class="v-input ctrl-input" id="hdr-exchange-rate" value="1" placeholder="الصرف" readonly style="flex: 1.2; text-align: center; cursor:pointer; background:rgba(16,185,129,0.1); color:#10b981; font-weight:bold;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
            </div>
        </div>

        <!-- 3. Warehouse -->
        <div class="h-form-group group-warehouse" style="flex: 1.3; min-width: 170px; display:flex; align-items:center; gap:10px;">
            <label class="h-label" style="margin:0; white-space:nowrap; min-width:60px;">📦 المخزن</label>
            <div style="flex:1;">
                <select class="v-input ctrl-input" id="hdr-warehouse" onchange="onMainWarehouseChange()" style="width:100%;"></select>
            </div>
        </div>

        <!-- 4. Cost Center -->
        <div class="h-form-group group-cost-center" style="flex: 1.3; min-width: 170px; display:flex; align-items:center; gap:10px;">
            <label class="h-label" style="margin:0; white-space:nowrap; min-width:75px;">🏷️ مركز التكلفة</label>
            <div style="flex:1;">
                <select class="v-input ctrl-input" id="hdr-cost-center" style="width:100%;">
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
                        <div class="quick-add-box" style="flex: 1; max-width: 380px; position: relative; z-index: 99999; display: flex; gap: 6px; align-items: center;">
                            <input type="text" class="g-input" id="quick-item-search" placeholder="بحث صنف أو باركود..."
                                style="background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.35); border-radius: 8px; color: var(--text-primary); padding: 5px 10px; flex: 1;"
                                onfocus="this.select(); showQuickItemDropdown()"
                                onblur="hideQuickItemDropdownDelayed()"
                                onkeydown="handleQuickItemSearchKeydown(event)"
                                oninput="filterQuickItemDropdown()" autocomplete="new-password">
                            <button type="button" onclick="startCameraScanner('quick-item-search')" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.35); border-radius: 8px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #38bdf8; font-size: 1.1rem;" title="مسح باركود بالكاميرا">
                                📷
                            </button>
                            <button type="button" onclick="addRow()" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.35); border-radius: 8px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #10b981; font-size: 1.4rem; font-weight: bold;" title="إضافة سطر">
                                +
                            </button>
                            <div class="custom-dropdown-list" id="quick-item-dropdown-list" style="text-align:right; top: 100%; left: 0; right: 0; position: absolute;"></div>
                        </div>
                    </div>
                    <div>
                        <button type="button" onclick="openGridSettingsModal()" style="background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.3)); border: 1.5px solid #38bdf8; color: #38bdf8; border-radius: 8px; padding: 6px 14px; display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: bold; font-size: 0.95rem; transition: all 0.2s; box-shadow: 0 2px 10px rgba(56,189,248,0.25);" title="تخصيص وترتيب الأعمدة والخطوط والألوان وحفظها نهائياً في قاعدة البيانات">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <span>⚙️ تخصيص وتنسيق الجدول</span>
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
                                    <span class="val amount" id="t-grand-dynamic-loc" style="flex: 1; min-width: 280px; min-height: 60px; height: 60px; padding: 0 22px; display: flex; align-items: center; justify-content: center; font-size: 2.35rem; font-weight: 900; font-family: 'Cairo', 'JetBrains Mono', monospace, sans-serif; color: #ffffff !important; text-align: center; background: linear-gradient(135deg, rgba(16,185,129,0.45) 0%, rgba(5,150,105,0.65) 50%, rgba(4,120,87,0.8) 100%); border: 2.5px solid #10b981; border-radius: 14px; box-shadow: 0 0 25px rgba(16,185,129,0.4), inset 0 2px 5px rgba(255,255,255,0.35), inset 0 -2px 6px rgba(0,0,0,0.5); letter-spacing: 1.5px; box-sizing: border-box; text-shadow: 0 2px 6px rgba(0,0,0,0.7); white-space: nowrap; overflow: hidden;">0</span>
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
            <div class="panel" id="payment-panel" style="display: block; z-index: 40; margin-top: 15px; padding: 0 !important; background: var(--surface); border: 1px solid var(--border-color);">
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


        <div class="items-toolbar" style="display: flex; align-items: center; justify-content: center; padding: 15px; border-radius: 12px; background: rgba(18, 15, 25, 0.8); border: 1px solid rgba(255,255,255,0.05); margin-top: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <div class="toolbar-actions" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                <button class="btn-action btn-draft" style="flex: 1; height: 45px; background: #f97316; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(249, 115, 22, 0.3);" onmouseover="this.style.background='#ea580c'; this.style.boxShadow='0 4px 15px rgba(249, 115, 22, 0.5)';" onmouseout="this.style.background='#f97316'; this.style.boxShadow='0 2px 10px rgba(249, 115, 22, 0.3)';" onclick="saveDraft()">💾 مسودة</button>
                
                <button class="btn-action btn-delete" style="flex: 1; height: 45px; background: #ef4444; border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.5)';" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 2px 10px rgba(239, 68, 68, 0.3)';" onclick="confirmDeleteTransaction()">❌ إلغاء</button>
                
                <!-- Unified Print Studio & Preview Button -->
                <button type="button" class="btn-action" style="flex: 2; height: 45px; background: linear-gradient(135deg, #2563eb, #0ea5e9); border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(37,99,235,0.4); display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.boxShadow='0 6px 20px rgba(37,99,235,0.6)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 4px 15px rgba(37,99,235,0.4)'; this.style.transform='none';" onclick="openStandalonePrintPreview();" title="استوديو الطباعة والمعاينة الحية">
                    🎨👁️ استوديو المعاينة والطباعة
                </button>
                <div class="preset-selector-container" style="flex: 1.5; display: flex; gap: 5px;">
                    <select id="preset-selector" class="ctrl-input" style="height: 45px; margin: 0; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 8px; color: #fff; font-weight: bold; flex: 1;" onchange="loadPreset(this.value)">
                        <option value="">⚙️ التخصيصات (0)</option>
                    </select>
                </div>
                
                <!-- Share Button: Professional with dropdown (نفس زر المشاركة في المشتريات) -->
                <div style="position: relative; flex: 1.2;" id="share-btn-wrapper">
                    <button type="button" class="btn-action btn-share" id="btn-share-invoice" style="width: 100%; height: 45px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border: none; color: #fff; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 10px rgba(139, 92, 246, 0.3); display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='linear-gradient(135deg, #7c3aed, #5b21b6)'; this.style.boxShadow='0 4px 18px rgba(139, 92, 246, 0.5)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #8b5cf6, #6d28d9)'; this.style.boxShadow='0 2px 10px rgba(139, 92, 246, 0.3)'; this.style.transform='none';" onclick="toggleShareMenu(event)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span>مشاركة</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.7;"><polyline points="6 9 12 15 18 9"/></svg>
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

                <!-- New Button: Solid Green Gradient (مثل المشتريات) -->
                <button type="button" class="btn-action btn-new" id="btn-new-invoice" style="flex: 1; height: 45px; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.4); background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.6)';" onmouseout="this.style.boxShadow='0 2px 10px rgba(16, 185, 129, 0.4)';" onclick="onSuccessNewInvoice(false);" title="بدء فاتورة مبيعات جديدة وتفريغ الحقول">✨ جديد</button>

                <!-- زر التحكم في الإرسال التلقائي مع الحفظ -->
                <div class="auto-send-control-box" id="auto-send-control-wrapper-sales">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle-unit inactive" id="btn-toggle-auto-send-sales" onclick="window.toggleSalesAutoSendMode()" title="التحكم في الإرسال التلقائي: انقر للتبديل بين الحفظ مع الإرسال، أو الحفظ فقط بدون إرسال">
                        <span id="auto-send-sales-icon" style="font-size: 1.1rem;">💾</span>
                        <span id="auto-send-sales-label" style="font-weight: 800;">إرسال تلقائي:</span>
                        <span id="auto-send-sales-status-pill" style="padding: 2px 8px; border-radius: 7px; font-size: 0.76rem; font-weight: 900;">معطّل (حفظ فقط)</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options-unit inactive" id="btn-auto-send-options-sales" onclick="window.toggleSalesAutoSendDropdown(event)" title="خيارات نوع الإرسال التلقائي (إذن استلام، فاتورة وكشف)">
                        ▼
                    </button>
                    
                    <!-- القائمة المنسدلة للخيارات السريعة -->
                    <div class="auto-send-menu-dropdown" id="auto-send-sales-dropdown-menu">
                        <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 800; padding: 5px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <span>⚙️ نوع الإرسال التلقائي عند الحفظ:</span>
                        </div>
                        <div class="auto-send-menu-item" onclick="window.selectSalesAutoSendOption('disabled')">
                            <span>💾</span> <span>حفظ فقط (بدون إرسال)</span>
                        </div>
                        <div class="auto-send-menu-item" onclick="window.selectSalesAutoSendOption('delivery_order_doc')" style="color: #38bdf8;">
                            <span>📄</span> <span>إذن استلام وتسليم رسمي (كميات فقط)</span>
                        </div>
                        <div class="auto-send-menu-item" onclick="window.selectSalesAutoSendOption('delivery_order_text')" style="color: #f59e0b;">
                            <span>📝</span> <span>إذن استلام كتابة كمعلومة (نص سريع)</span>
                        </div>
                        <div class="auto-send-menu-item" onclick="window.selectSalesAutoSendOption('invoice_doc')" style="color: #ca8a04;">
                            <span>🧾</span> <span>فاتورة مبيعات مصممة (تصميم رسمي)</span>
                        </div>
                        <div class="auto-send-menu-item" onclick="window.selectSalesAutoSendOption('invoice')" style="color: #10b981;">
                            <span>💰</span> <span>نص الفاتورة وكشف الحساب</span>
                        </div>
                    </div>
                </div>
                
                <span style="display:none;" id="footer-grand"></span>
                <button class="btn-action btn-submit" style="flex: 1.5; height: 45px; box-shadow: 0 2px 10px rgba(56, 189, 248, 0.4); background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #fff; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.boxShadow='0 6px 20px rgba(56, 189, 248, 0.6)';" onmouseout="this.style.boxShadow='0 2px 10px rgba(56, 189, 248, 0.4)';" onclick="submitInvoice()">✅ تأكيد وحفظ</button>
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
    <script src="js/direct-sales-studio.js?v=<?php echo time(); ?>"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=<?php echo time(); ?>"></script>
    <script defer src="js/erp-whatsapp-suite.js?v=<?php echo time(); ?>"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1.0.0'; ?>"></script>
    <script defer src="js/accounting-engine.js?v=<?php echo file_exists('js/accounting-engine.js') ? filemtime('js/accounting-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/print-engine.js?v=<?php echo file_exists('js/print-engine.js') ? filemtime('js/print-engine.js') : '1.0.0'; ?>"></script>
    <script defer src="js/direct-sales-v3.js?v=<?php echo time(); ?>"></script>
    <script>
    // Force Service Worker update + clear old caches
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(regs => {
            regs.forEach(reg => reg.update());
        });
        if (caches) {
            caches.keys().then(names => {
                names.filter(n => n.includes('static-v1') || n.includes('magaies-static-v2')).forEach(n => caches.delete(n));
            });
        }
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
            
            if (val === 'SDG') {
                rateInput.value = '1';
                rateInput.disabled = true;
                if (typeof recalcAll === 'function') recalcAll();
                return;
            }
            
            // Always enable editing for foreign currencies
            rateInput.disabled = false;
            
            // Step 1: Try to get rate from local settings function
            let rate = 0;
            if (window.getExchangeRateForCurrency) {
                rate = window.getExchangeRateForCurrency(val);
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
                onSuccessNewInvoice(false);
            }
            
            window.dispatchEvent(new Event('resize'));
        }

        function openDeliveryHub() {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'flex';
            currentSalesView = 'delivery';
            
            // Change header title to "تسليم مبيعات"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">🚚</span> تسليم مبيعات';
            }
            
            loadDeliveryTransactions();
        }

        function openSplitInvoiceView() {
            document.getElementById('sales-hub-view').style.display = 'none';
            document.getElementById('invoice-editor-view').style.display = 'none';
            document.getElementById('delivery-hub-view').style.display = 'none';
            document.getElementById('split-invoice-view').style.display = 'flex';
            currentSalesView = 'split';
            
            // Change header title to "تقسيم الفواتير"
            const headerTitle = document.querySelector('.header-page-title');
            if (headerTitle) {
                headerTitle.innerHTML = '<span class="header-page-title-icon">✂️</span> تقسيم الفواتير';
            }
            
            if (typeof loadSplitInvoices === 'function') {
                loadSplitInvoices();
            }
        }

        function loadHubTransactions() {
            const tbody = document.getElementById('hub-transactions-body');
            const emptyState = document.getElementById('hub-empty-state');
            
            // Fetch from localStorage with cache
            window._hubSalesCache = window._hubSalesCache || { raw: null, obj: [] };
            const savedData = localStorage.getItem('erp_sales_invoices_v1');
            let invoices = window._hubSalesCache.obj;
            if (savedData !== null && window._hubSalesCache.raw !== savedData) {
                try { invoices = JSON.parse(savedData) || []; } catch(e) {}
                window._hubSalesCache.raw = savedData;
                window._hubSalesCache.obj = invoices;
            }
            
            // Create a copy so we can sort without affecting the cache
            invoices = [...invoices];
            
            if (!invoices || invoices.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'flex';
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

            function getWarehouseName(id) {
                if (!id) return '—';
                const w = warehouses.find(x => x && x.id === id);
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
            const query = document.getElementById('hub-search')?.value.toLowerCase() || '';
            if (query) {
                invoices = invoices.filter(inv => {
                    const text = [
                        inv.invoiceNo,
                        inv.customerName,
                        getCustomerName(inv.customer || inv.customerId),
                        inv.date
                    ].join(' ').toLowerCase();
                    return text.includes(query);
                });
            }

            if (invoices.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'flex';
                const pagEl = document.getElementById('hub-pagination-bar');
                if (pagEl) pagEl.style.display = 'none';
                return;
            }

            emptyState.style.display = 'none';
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
                
                let itemText = 'بدون أصناف';
                let qtyText = '0';
                let priceText = '0';
                
                const lines = inv.items || [];
                if (lines.length === 1) {
                    const line = lines[0];
                    itemText = line.itemName || line.name || getItemName(line.itemId);
                    qtyText = fmtNum(line.quantity || 0);
                    priceText = fmtNum(line.price || 0) + ' SDG';
                } else if (lines.length > 1) {
                    itemText = lines.length + ' أصناف';
                    let totalQty = 0;
                    lines.forEach(l => {
                        totalQty += parseFloat(l.quantity) || 0;
                    });
                    qtyText = fmtNum(totalQty);
                    priceText = '—';
                }

                // Check if the invoice is from 27/6/2026 to highlight numbers in red
                let isTargetDate = false;
                if (inv.date === '2026-06-27' || (inv.timestamp && inv.timestamp.startsWith('2026-06-27'))) {
                    isTargetDate = true;
                }
                const numColor = isTargetDate ? '#ef4444' : '#fff';

                const isPrelim = inv.saleType === 'preliminary' || inv.status === 'preliminary';
                const serialColor = isPrelim ? '#c084fc' : 'var(--gold-primary)';

                let actionBtns = `
                    <button onclick="editHubTransaction('${inv.invoiceNo}')" style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تعديل / عرض">👁️</button>
                    <button onclick="deleteHubTransaction('${inv.invoiceNo}')" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; border-radius:6px; width:34px; height:34px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;" title="حذف">🗑️</button>
                `;

                if (inv.status === 'preliminary') {
                    actionBtns = `
                        <button onclick="convertToOfficial('${inv.invoiceNo}')" style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; border-radius:6px; width:34px; height:34px; cursor:pointer; margin-left:4px; display:inline-flex; align-items:center; justify-content:center;" title="تحويل لرسمية">✅</button>
                    ` + actionBtns;
                }

                tr.innerHTML = `
                    <td style="text-align:right; font-weight:800; color:${serialColor};">${inv.invoiceNo}</td>
                    <td style="text-align:right; color:var(--text-dim);">${new Date(inv.timestamp || inv.date).toLocaleString('ar-EG-u-nu-latn')}</td>
                    <td style="text-align:right; font-weight:600;">${inv.customerName || getCustomerName(inv.customer || inv.customerId) || 'عميل غير محدد'}</td>
                    <td style="text-align:right; color:var(--text-dim);">${itemText}</td>
                    <td style="text-align:center; color:var(--text-dim);">${qtyText}</td>
                    <td style="text-align:center; color:var(--text-dim);">${priceText}</td>
                    <td style="text-align:center; font-weight:900; direction:ltr; color:${numColor};">${fmtNum(inv.totals?.grandTotal || inv.grandTotal || 0)} <span style="color:#a855f7; font-weight:700;">SDG</span></td>
                    <td style="text-align:center; font-weight:600; color:var(--text-dim);">${getWarehouseName(inv.warehouse)}</td>
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
                    // Saved unposted invoice: Ask user if they want to edit
                    wantsToEdit = confirm('⚠️ تنبيه:\n\nهذه الفاتورة (رقم: ' + invNo + ') تم حفظها مسبقاً في النظام.\n\nهل تريد تعديل بيانات هذه الفاتورة؟\n\n- اضغط [موافق / OK] لفتح الفاتورة وتعديلها\n- اضغط [إلغاء / Cancel] للمعاينة والقراءة فقط بدون تعديل');
                    if (!wantsToEdit) {
                        showToast('👁️ وضع المعاينة فقط - الحقول مغلقة ضد التعديل', 'info', 4000);
                    }
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
                            alert('خطأ أثناء تحميل تفاصيل الفاتورة: ' + innerErr.message + '\n' + innerErr.stack);
                        }
                    }, 100);
                } else {
                    alert('خطأ: لم يتم العثور على دالة تحميل الفاتورة!');
                }
            } catch (err) {
                console.error(err);
                alert('خطأ في محرر الفواتير: ' + err.message);
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
            const invNo = currentDeleteInvoiceNo;
            
            // Delete from Database if API exists for it (usually handled by save_sales_invoice.php with action=delete or similar).
            // Currently the system uses localStorage mostly, but we'll try to sync deletion to DB if there's an endpoint.
            try {
                const token = localStorage.getItem('authToken') || localStorage.getItem('token') || '';
                // Since this system uses save_sales_invoice.php or generic save APIs, let's just mark it soft deleted or hard deleted
                // The current code just deletes it from localStorage. Let's do that + DB if possible.
                await fetch('api/save_sales_invoice.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': token ? ('Bearer ' + token) : '' },
                    body: JSON.stringify({ action: 'delete', invoiceNo: invNo })
                });
            } catch(e) { console.warn('DB delete sync failed:', e); }

            // Delete from LocalStorage
            let savedData = localStorage.getItem('erp_sales_invoices_v1');
            if (savedData) {
                let invoices = JSON.parse(savedData);
                invoices = invoices.filter(i => i.invoiceNo !== invNo);
                localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(invoices));
            }
            
            closeInvoiceDeleteModal();
            showToast('تم حذف الفاتورة نهائياً بنجاح', 'success');
            loadHubTransactions();
        }

        // Initialize view on load
        document.addEventListener('DBReady', () => {
            // Wait a brief moment for other scripts to initialize
            setTimeout(() => {
                showSalesHub();
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
    window.applyGridSettings = function() {
        let cfg = window.CurrentGridConfig;
        if (!cfg || !cfg.columns) return;
        
        let style = document.getElementById('dynamic-grid-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-grid-style';
            document.head.appendChild(style);
        }
        
        let css = `
            #pos-grid {
                font-size: ${cfg.fontSize || 16}px !important;
                font-weight: ${cfg.fontWeight || 'normal'} !important;
                font-family: ${cfg.fontFamily || "'Cairo', sans-serif"} !important;
                color: ${cfg.fontColor || '#ffffff'} !important;
            }
        `;
        
        cfg.columns.forEach(col => {
            if (col.visible === false || col.visible === "false" || col.visible === 0) {
                css += `
                    #pos-grid th[data-col-id="${col.id}"],
                    #pos-grid td[data-col-id="${col.id}"] {
                        display: none !important;
                    }
                `;
            } else {
                let width = col.width ? col.width : 'auto';
                let align = col.align ? col.align : (cfg.align || 'center');
                let fontSize = col.fontSize ? col.fontSize + 'px' : 'inherit';
                let color = col.color ? col.color : 'inherit';
                let fontWeight = col.fontWeight ? col.fontWeight : 'inherit';
                
                css += `
                    #pos-grid th[data-col-id="${col.id}"],
                    #pos-grid td[data-col-id="${col.id}"] {
                        width: ${width} !important;
                        min-width: ${width} !important;
                        max-width: ${width} !important;
                        text-align: ${align} !important;
                        color: ${color} !important;
                        font-size: ${fontSize} !important;
                        font-weight: ${fontWeight} !important;
                    }
                `;
            }
        });
        
        style.innerHTML = css;

        // DOM Reordering
        const table = document.getElementById('pos-grid');
        if (!table) return;

        // Reorder thead
        const theadTr = table.querySelector('thead tr');
        if (theadTr) {
            cfg.columns.forEach(col => {
                const th = theadTr.querySelector(`th[data-col-id="${col.id}"]`);
                if (th) {
                    theadTr.appendChild(th);
                }
            });
        }

        // Reorder tbody
        const tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach(tr => {
            // skip rows that span across (e.g. empty state)
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
    };





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
        if (confirm("هل أنت متأكد من استعادة التنسيق والترتيب الافتراضي لجدول المبيعات المباشرة وحفظه في قاعدة البيانات؟")) {
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
</body>
</html>