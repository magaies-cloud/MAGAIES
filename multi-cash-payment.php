<?php 
require_once 'db_bridge.php';
$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;

$SERVER_PAYMENTS_json = '[]';
$SERVER_RECEIPTS_json = '[]';

// --- AJAX save logic for num properties ---
if (isset($_POST['action']) && $_POST['action'] === 'save_num_props') {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $val = $_POST['num_props'] ?? '{}';
        $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
        
        // Save for current tenant
        $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES (?, 'multi_cash_num_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt->execute([$tId, $val, $val]);
        
        // Also save to 'default' and '1' as a permanent system-wide baseline
        $stmt->execute(['default', $val, $val]);
        if ($tId !== '1') {
            $stmt->execute(['1', $val, $val]);
        }
        
        echo "success";
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
    exit;
}

// --- AJAX save logic for OCR left column properties ---
if (isset($_POST['action']) && $_POST['action'] === 'save_ocr_props') {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $val = $_POST['ocr_props'] ?? '{}';
        $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
        
        $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES (?, 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt->execute([$tId, $val, $val]);
        $stmt->execute(['default', $val, $val]);
        if ($tId !== '1') {
            $stmt->execute(['1', $val, $val]);
        }
        echo "success";
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
    exit;
}

// --- Fetch num properties for initial load ---
$db_num_props_json = '{}';
$db_ocr_props_json = '{}';
try {
    $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
    $stmt = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id IN (?, 'default', '1') AND pref_key = 'multi_cash_num_props' ORDER BY CASE WHEN tenant_id = ? THEN 1 WHEN tenant_id = '1' THEN 2 ELSE 3 END LIMIT 1");
    $stmt->execute([$tId, $tId]);
    $res = $stmt->fetchColumn();
    if ($res) $db_num_props_json = $res;

    $stmt2 = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id IN (?, 'default', '1') AND pref_key = 'ocr_left_col_props' ORDER BY CASE WHEN tenant_id = ? THEN 1 WHEN tenant_id = '1' THEN 2 ELSE 3 END LIMIT 1");
    $stmt2->execute([$tId, $tId]);
    $res2 = $stmt2->fetchColumn();
    if ($res2) $db_ocr_props_json = $res2;
} catch(Exception $e) {}
// ------------------------------------------

try {
    // Fetch payments
    $stmt = $pdo->prepare("SELECT data FROM payments WHERE tenant_id = ? ORDER BY id ASC");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    $arrPay = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arrPay[] = json_decode($row['data'], true);
    }
    $SERVER_PAYMENTS_json = json_encode($arrPay, JSON_UNESCAPED_UNICODE);

    // Fetch receipts
    $stmt = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? ORDER BY id ASC");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    $arrRec = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arrRec[] = json_decode($row['data'], true);
    }
    $SERVER_RECEIPTS_json = json_encode($arrRec, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {}

$page_title = 'حركة مالية مجمعة'; 
?>
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
window.SERVER_PAYMENTS = <?php echo $SERVER_PAYMENTS_json; ?>;
window.SERVER_RECEIPTS = <?php echo $SERVER_RECEIPTS_json; ?>;
</script>
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حركة مالية مجمعة | مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        @keyframes flowOut {
            0% { transform: translateX(0); opacity: 0.4; }
            50% { transform: translateX(-10px); opacity: 1; filter: drop-shadow(0 0 3px #ef4444); }
            100% { transform: translateX(0); opacity: 0.4; }
        }
        .animated-arrow-left {
            display: inline-block;
            margin-right: 8px;
            color: #ef4444;
            animation: flowOut 1.5s infinite ease-in-out;
            font-size: 1.6rem;
            vertical-align: middle;
            font-weight: bold;
        }

        @keyframes flowIn {
            0% { transform: translateX(0); opacity: 0.4; }
            50% { transform: translateX(10px); opacity: 1; filter: drop-shadow(0 0 3px currentColor); }
            100% { transform: translateX(0); opacity: 0.4; }
        }
        .animated-arrow-right {
            display: inline-block;
            margin-right: 8px;
            color: inherit;
            animation: flowIn 1.5s infinite ease-in-out;
            font-size: 1.6rem;
            vertical-align: middle;
            font-weight: bold;
        }

        /* ═══ Ultra-Modern Glassmorphic Crystal Theme & Dynamic Light System ═══ */
        @keyframes lightWavePulse {
            0% { box-shadow: 0 0 0 0 var(--btn-glow, rgba(56, 189, 248, 0.6)), 0 8px 25px rgba(0,0,0,0.4); }
            50% { box-shadow: 0 0 20px 4px var(--btn-glow, rgba(56, 189, 248, 0.6)), 0 12px 35px rgba(0,0,0,0.6); }
            100% { box-shadow: 0 0 0 0 var(--btn-glow, rgba(56, 189, 248, 0.6)), 0 8px 25px rgba(0,0,0,0.4); }
        }

        @keyframes crystalBeamSweep {
            0% { transform: translateX(-120%) rotate(25deg); }
            100% { transform: translateX(220%) rotate(25deg); }
        }

        @keyframes rimGlowFlow {
            0% { border-color: rgba(255, 255, 255, 0.18); }
            50% { border-color: rgba(255, 255, 255, 0.45); }
            100% { border-color: rgba(255, 255, 255, 0.18); }
        }

        /* Glassmorphism Crystal Containers */
        .glass-container {
            padding: 22px 26px;
            border-radius: 18px;
            margin-bottom: 25px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25);
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.65), rgba(15, 23, 42, 0.85));
            position: relative;
            overflow: visible !important;
        }

        .glass-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            pointer-events: none;
        }

        .glass-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.6), inset 0 1px 2px rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.3);
        }

        #secondary-table-container {
            padding-top: 4px !important;
        }

        #secondary-table-container th {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }

        /* Crystal Red Container (المستطيل الرئيسي للصرف - منطقة من حساب) */
        .glass-red {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(110, 30, 45, 0.75), rgba(15, 23, 42, 0.85)) !important;
            border: 1.5px solid rgba(244, 63, 94, 0.5) !important;
            border-right: 6px solid #f43f5e !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(244, 63, 94, 0.35), 0 0 25px rgba(244, 63, 94, 0.25) !important;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        /* إنارة الحواف باللون الأصفر عند القدوم إلى من حساب */
        .glass-red:hover,
        .glass-red:focus-within,
        #primary-inputs-container:hover,
        #primary-inputs-container:focus-within {
            background: linear-gradient(135deg, rgba(130, 35, 50, 0.85), rgba(15, 23, 42, 0.92)) !important;
            border-color: #fbbf24 !important;
            border-right-color: #f59e0b !important;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.8), 0 0 35px rgba(251, 191, 36, 0.85), 0 0 70px rgba(245, 158, 11, 0.45), inset 0 0 20px rgba(251, 191, 36, 0.25) !important;
        }

        /* Crystal Green Container (مستطيلات الحسابات المستلمة - إلى حساب) */
        .glass-green {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(16, 60, 40, 0.85), rgba(15, 23, 42, 0.95)) !important;
            border: 1px solid rgba(39, 174, 96, 0.45) !important;
            border-right: 5px solid #27ae60 !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(39, 174, 96, 0.35), 0 0 25px rgba(39, 174, 96, 0.2) !important;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        /* إضاءة الحواف التلقائية بمجرد قدوم المؤشر إلى حساب */
        .glass-green:hover,
        .glass-green:focus-within,
        .account-block:hover,
        .account-block:focus-within {
            background: linear-gradient(135deg, rgba(20, 75, 50, 0.9), rgba(15, 23, 42, 0.98)) !important;
            border-color: #34d399 !important;
            border-right-color: #10b981 !important;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.85), 0 0 70px rgba(5, 150, 105, 0.45), inset 0 0 20px rgba(52, 211, 153, 0.25) !important;
            transform: translateY(-3px) scale(1.005) !important;
        }

        /* High Contrast Inputs, Placeholders, and Labels */
        input::placeholder,
        select::placeholder,
        textarea::placeholder,
        .form-input::placeholder,
        .form-select::placeholder,
        .multi-input::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
            opacity: 1 !important;
        }

        input, select, textarea, .form-input, .form-select, .multi-input {
            color: #ffffff !important;
        }

        .glass-container label {
            color: #e2e8f0;
        }

        /* High Contrast for Payment Method Details Inputs & Labels */
        .payment-method-box label {
            color: #e2e8f0 !important;
            opacity: 1 !important;
        }
        .payment-method-box input {
            color: #ffffff !important;
        }
        .payment-method-box input::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
            opacity: 1 !important;
        }

        /* Auto-Expanding heights for payment containers */
        #payment-panel,
        .payment-layout,
        .payment-details-card,
        .payment-method-box,
        #transfers-container,
        #cheques-container {
            height: auto !important;
            min-height: fit-content !important;
            overflow: visible !important;
        }

        .top-action-bar { background: rgba(249, 115, 22, 0.1); border-bottom: 1px solid rgba(249, 115, 22, 0.2); }
        .action-tab.active { background: #f97316; color: #fff; border-color: #f97316; }

        .form-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;
        }
        
        .form-input, .form-select {
            width: 100%; background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255,255,255,0.15);
            color: #fff; padding: 10px 14px; border-radius: 10px;
            font-family: inherit; font-size: 0.95rem; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
        }
        .form-input:focus, .form-select:focus { 
            border-color: #38bdf8; background: rgba(15, 23, 42, 0.9); outline: none;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2), 0 0 15px rgba(56, 189, 248, 0.15);
        }
        
        .form-section-title { 
            font-size: 1.1rem; font-weight: 700; color: #38bdf8; 
            margin-bottom: 15px; display: flex; align-items: center; gap: 8px; 
            border-bottom: 1px solid rgba(56,189,248,0.2); padding-bottom: 10px; 
        }

        /* Grid */
        .multi-table { width: 100%; border-collapse: separate; border-spacing: 0 5px; }
        .multi-table th { text-align: right; padding: 12px 10px; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.05); white-space: nowrap; }
        .multi-row { transition: all 0.2s; }
        .multi-row:hover { background: rgba(255,255,255,0.02); }
        .multi-row td { padding: 5px; }
        .multi-input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: #f8fafc; padding: 8px 10px; border-radius: 6px; font-size: 0.9rem; min-width: 100px;
        }
        .multi-input:focus { border-color: #f97316; outline: none; background: rgba(0,0,0,0.4); }
        
        .btn-add-row {
            background: rgba(249, 115, 22, 0.1); color: #f97316; border: 1px dashed #f97316;
            padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700;
            width: 100%; margin-top: 10px; transition: all 0.2s; font-family: 'Tajawal', sans-serif;
        }
        .btn-add-row:hover { background: rgba(249, 115, 22, 0.2); }

        .btn-save {
            background: #10b981; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; font-family: 'Tajawal', sans-serif; transition: 0.2s; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn-save:hover { background: #059669; transform: translateY(-2px); }

        .btn-del { background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-del:hover { transform: scale(1.2); }

        /* Choices custom overrides */
        .choices__inner { background-color: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff !important; border-radius: 8px !important; min-height: 42px !important; }
        .choices__list--dropdown { background-color: #1e293b !important; color: #fff !important; border: 1px solid #f97316 !important; }
        .choices__item--selectable.is-highlighted { background-color: #f97316 !important; }
        /* Toast */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; }
        .toast.hide { animation:toastOut 0.4s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        /* Context Menu */
        .context-menu {
            position: absolute; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 10px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5); z-index: 1000;
            display: none; min-width: 200px;
        }
        .context-menu.active { display: flex; flex-direction: column; gap: 8px; animation: fadeIn 0.2s; }
        .context-menu-item {
            display: flex; align-items: center; gap: 10px; color: #fff;
            padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 0.9rem;
        }
        .context-menu-item:hover { background: rgba(255,255,255,0.05); }
        .context-menu-item input[type="checkbox"] { accent-color: #f97316; width: 16px; height: 16px; cursor: pointer; }

        /* Dynamic Columns */
        .col-cost-center, .col-note, .col-check-no, .col-check-date, .col-check-due { display: none; }
        table.show-col-cost-center .col-cost-center { display: table-cell; }
        table.show-col-note .col-note { display: table-cell; }
        table.show-col-check-no .col-check-no { display: table-cell; }
        table.show-col-check-date .col-check-date { display: table-cell; }
        table.show-col-check-due .col-check-due { display: table-cell; }

        /* Sticky Footer */
        .sticky-footer { position: sticky; bottom: 0; background: rgba(15,23,42,0.9); border-top: 1px solid rgba(249,115,22,0.3); padding: 15px 25px; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between; align-items: center; z-index: 50; backdrop-filter: blur(10px); margin-top: 20px; margin-left: -25px; margin-right: -25px; margin-bottom: -25px; }

        /* ═══ MASTER DATA ULTRA-PREMIUM ICON & BUTTON DESIGN SYSTEM ═══ */

        /* 1. Highly Visible Glitter Shimmer Sparkles (From Master Data) */
        @keyframes glitterShimmer {
            0% {
                background-position: 0 0, 0 0, 0 0, 0 0, 0 0, -100% -100%;
                filter: brightness(1.3) drop-shadow(0 0 3px #ffffff);
            }
            50% {
                filter: brightness(2.4) drop-shadow(0 0 8px #ffffff);
            }
            100% {
                background-position: 55px 55px, -40px 40px, 48px -48px, -42px -42px, 30px -30px, 200% 200%;
                filter: brightness(1.3) drop-shadow(0 0 3px #ffffff);
            }
        }

        /* 2. Rear Bottom Light Ray Pulse */
        @keyframes rearRayPulse {
            0%, 100% { transform: scaleX(1) translateY(0); opacity: 0.65; }
            50% { transform: scaleX(1.35) translateY(3px); opacity: 1; filter: blur(16px) brightness(1.5); }
        }

        /* 3. Rim Glow Pulse Animation */
        @keyframes rimPulseGlow {
            0%, 100% { box-shadow: 0 0 16px var(--tab-glow-color, rgba(56, 189, 248, 0.5)), inset 0 1px 2px rgba(255, 255, 255, 0.5); }
            50% { box-shadow: 0 0 32px var(--tab-glow-color, rgba(56, 189, 248, 0.9)), inset 0 2px 5px rgba(255, 255, 255, 0.8); }
        }

        /* ═══ Base Crystal Button (Master Data Inspired) ═══ */
        .crystal-btn, .pay-toggle-btn {
            position: relative;
            background: rgba(0, 0, 0, 0.2); /* Darker background, no inner light */
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 3.5px solid var(--tab-border-color, rgba(255, 255, 255, 0.25)) !important;
            border-radius: 14px !important;
            padding: 10px 18px;
            font-size: 1rem;
            font-weight: 800;
            color: #ffffff !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            overflow: visible;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
            z-index: 1;
            font-family: 'Tajawal', sans-serif;
            user-select: none;
            height: 44px; /* Uniform height & width alignment */
            box-sizing: border-box;
        }

        .btn-text, .crystal-btn .btn-text, .btn-dock-btn .btn-text {
            color: #ffffff !important; /* الكتابة باللون الأبيض الناصع دائماً */
            font-weight: 800 !important;
            text-shadow: none !important;
        }

        /* Highly Visible Internal Glitter Sparkles Overlay (DISABLED PER USER) */
        .crystal-btn::after, .pay-toggle-btn::after {
            display: none !important;
        }

        .crystal-btn:hover::after, .pay-toggle-btn:hover::after {
            display: none !important;
        }

        /* Rear Bottom Light Ray Beam Projection: DISABLED to guarantee zero inside glow */
        .crystal-btn::before, .pay-toggle-btn::before, .btn-dock-btn::before {
            display: none !important;
        }

        /* Icon Styling (صورة الكاميرا وصورة اليد لا تعمل أي شعاع نهائياً) */
        .btn-icon, .md-tab-icon {
            font-size: 1.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: none !important; /* صورة الكاميرا وصورة اليد لا تعمل أي شعاع نهائياً */
            position: relative;
            z-index: 4;
        }

        /* Detailed Icon Rear Glow Ring */
        .btn-icon::after, .md-tab-icon::after {
            display: none !important;
        }

        /* Hover Scale + Crisp Text + Pure External Glow (الشعاع من الخارج فقط وبدون أي شعاع داخلي) */
        .crystal-btn:hover, .pay-toggle-btn:hover {
            transform: translateY(-3px) scale(1.05) !important;
            color: #ffffff !important;
            text-shadow: none !important; /* كتابة بيضاء ناصعة حادة بدون أي توهج أو ضباب داخلي */
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.75), 0 0 12px var(--tab-glow-color, rgba(56,189,248,0.7)), 0 0 24px var(--tab-glow-color, rgba(56,189,248,0.4)) !important; /* شعاع خارجي فقط بدون inset */
            background: #000000 !important;
            background-color: #000000 !important;
            background-image: none !important;
            z-index: 10;
        }

        .crystal-btn:hover .btn-icon, .pay-toggle-btn:hover .btn-icon {
            transform: scale(1.2) rotate(4deg);
            filter: none !important; /* لا شعاع نهائياً من صورة الأيقونة */
        }

        .crystal-btn:hover .btn-icon::after, .pay-toggle-btn:hover .btn-icon::after {
            display: none !important;
        }

        /* ═══ Payment Method Toggle Buttons (نقدي 💵 / تحويل 🏦 / شيك ✍️) ═══ */
        .pay-toggle-btn.block-btn-cash {
            --tab-border-color: rgba(213, 189, 175, 0.45);
            --tab-glow-color: rgba(213, 189, 175, 0.65);
            --tab-hover-bg: linear-gradient(135deg, rgba(213, 189, 175, 0.35), rgba(179, 154, 138, 0.5));
        }
        .pay-toggle-btn.active.block-btn-cash {
            --tab-border-color: #d5bdaf;
            background: linear-gradient(135deg, rgba(213, 189, 175, 0.45), rgba(179, 154, 138, 0.7)) !important;
            border-color: #d5bdaf !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(213, 189, 175, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 25px rgba(213, 189, 175, 0.5), 0 0 20px rgba(213, 189, 175, 0.3), inset 0 1px 2px rgba(255,255,255,0.6) !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ═══ Unified 4-Icon Dock & Crystal Buttons (قاعدة الأيقونات الأربعة كوحدة واحدة) ═══ */
        .crystal-unified-dock {
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.75), rgba(30, 41, 59, 0.85));
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 6px 8px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            flex-shrink: 0;
            user-select: none;
            box-sizing: border-box;
            overflow: visible;
        }

        .btn-dock-btn {
            position: relative;
            height: 38px !important;
            padding: 0 12px !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            font-family: 'Tajawal', sans-serif !important;
            border-radius: 14px !important;
            border-width: 3.5px !important;
            border-style: solid !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            white-space: nowrap !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            box-sizing: border-box !important;
            flex: 1;
            min-width: 105px;
            color: #ffffff !important; /* الكتابة باللون الأبيض الناصع لجميع الأيقونات */
            overflow: visible;
        }

        .btn-dock-btn .btn-icon {
            font-size: 1.25rem !important;
            filter: none !important; /* لا شعاع من صورة الكاميرا أو الأيقونات نهائياً */
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* عندما نأتي إليها: الحجم يزيد شوية + إنارة معتدلة من الخارج فقط + خلفية سوداء والخط أبيض ناصع بدون أي شعاع داخلي */
        .btn-dock-btn:hover {
            transform: translateY(-3px) scale(1.06) !important;
            background: #000000 !important; /* خلفية سوداء عند الوقوف بالمؤشر */
            background-image: none !important;
            color: #ffffff !important; /* الخط باللون الأبيض الناصع دائماً */
            border-width: 3.5px !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px var(--tab-glow-color), 0 0 24px var(--tab-glow-color) !important; /* شعاع خارجي معتدل فقط وبدون أي inset داخلي */
            text-shadow: none !important; /* كتابة بيضاء ناصعة حادة بدون أي توهج أو ضباب داخلي */
            z-index: 15 !important;
        }

        .btn-dock-btn:hover .btn-icon {
            transform: scale(1.25) rotate(4deg) !important;
            filter: none !important; /* لا شعاع من صورة الكاميرا نهائياً */
        }

        .btn-dock-btn::before,
        .btn-dock-btn:hover::before {
            display: none !important;
        }

        /* 1. إشعار AI (Cyan / Blue Neon) */
        .btn-dock-btn.block-btn-ai-scan {
            --tab-border-color: #06b6d4;
            --tab-glow-color: rgba(6, 182, 212, 0.85);
            border: 3.5px solid #06b6d4 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.38), rgba(14, 116, 144, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(6, 182, 212, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-ai-scan:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #22d3ee !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(6, 182, 212, 0.85), 0 0 24px rgba(6, 182, 212, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* 1.5. واتساب AI (WhatsApp Emerald Green Neon) */
        .btn-dock-btn.block-btn-ai-whatsapp {
            --tab-border-color: #25D366;
            --tab-glow-color: rgba(37, 211, 102, 0.85);
            border: 3.5px solid #25D366 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.38), rgba(18, 140, 126, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(37, 211, 102, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-ai-whatsapp:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #4ade80 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(37, 211, 102, 0.85), 0 0 24px rgba(37, 211, 102, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* 2. نماذج (Purple / Violet Neon) */
        .btn-dock-btn.block-btn-ai-trainer {
            --tab-border-color: #a855f7;
            --tab-glow-color: rgba(168, 85, 247, 0.85);
            border: 3.5px solid #a855f7 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.38), rgba(126, 34, 206, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(168, 85, 247, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-ai-trainer:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #c084fc !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(168, 85, 247, 0.85), 0 0 24px rgba(168, 85, 247, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* 3. تحويل (Amber Gold / Warm Sun) */
        .btn-dock-btn.block-btn-bank {
            --tab-border-color: #f59e0b;
            --tab-glow-color: rgba(245, 158, 11, 0.85);
            border: 3.5px solid #f59e0b !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.38), rgba(180, 83, 9, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(245, 158, 11, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-bank:hover,
        .btn-dock-btn.active.block-btn-bank:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #fbbf24 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(245, 158, 11, 0.85), 0 0 24px rgba(245, 158, 11, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }
        .btn-dock-btn.active.block-btn-bank {
            border: 3.5px solid #fbbf24 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #d97706, #fbbf24) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 18px rgba(251, 191, 36, 0.65), 0 0 16px rgba(245, 158, 11, 0.4) !important;
            transform: translateY(-1px) scale(1.03) !important;
        }

        /* 4. شيك (Emerald Mint / Neon Green) */
        .btn-dock-btn.block-btn-cheque {
            --tab-border-color: #10b981;
            --tab-glow-color: rgba(16, 185, 129, 0.85);
            border: 3.5px solid #10b981 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.38), rgba(4, 120, 87, 0.72)) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 10px rgba(16, 185, 129, 0.35) !important;
            text-shadow: none !important;
        }
        .btn-dock-btn.block-btn-cheque:hover,
        .btn-dock-btn.active.block-btn-cheque:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #34d399 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.06) !important;
        }
        .btn-dock-btn.active.block-btn-cheque {
            border: 3.5px solid #34d399 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #059669, #34d399) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 18px rgba(52, 211, 153, 0.65), 0 0 16px rgba(16, 185, 129, 0.4) !important;
            transform: translateY(-1px) scale(1.03) !important;
        }

        /* ═══ Footer Action Buttons (أزرار التحكم السفلية بالخلفية السوداء والإنارة الخارجية المعتدلة) ═══ */
        .btn-save-master {
            --tab-border-color: #34d399;
            --tab-glow-color: rgba(16, 185, 129, 0.85);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.45), rgba(4, 120, 87, 0.75)) !important;
            border: 3.5px solid #34d399 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-save-master:hover,
        .crystal-btn.btn-save-master:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #34d399 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-new {
            --tab-border-color: #38bdf8;
            --tab-glow-color: rgba(56, 189, 248, 0.85);
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.3), rgba(3, 105, 161, 0.6)) !important;
            border: 3.5px solid #38bdf8 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-new:hover,
        .crystal-btn.btn-new:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #38bdf8 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(56, 189, 248, 0.85), 0 0 24px rgba(56, 189, 248, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-print {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(168, 85, 247, 0.85);
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.6)) !important;
            border: 3.5px solid #c084fc !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-print:hover,
        .crystal-btn.btn-print:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #c084fc !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(168, 85, 247, 0.85), 0 0 24px rgba(168, 85, 247, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-print-preview {
            --tab-border-color: #22d3ee;
            --tab-glow-color: rgba(6, 182, 212, 0.85);
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.3), rgba(14, 116, 144, 0.6)) !important;
            border: 3.5px solid #22d3ee !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-print-preview:hover,
        .crystal-btn.btn-print-preview:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #22d3ee !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(6, 182, 212, 0.85), 0 0 24px rgba(6, 182, 212, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-delete {
            --tab-border-color: #fb7185;
            --tab-glow-color: rgba(244, 63, 94, 0.85);
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.3), rgba(190, 18, 60, 0.6)) !important;
            border: 3.5px solid #fb7185 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-delete:hover,
        .crystal-btn.btn-delete:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #fb7185 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(244, 63, 94, 0.85), 0 0 24px rgba(244, 63, 94, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        .btn-exit {
            --tab-border-color: #94a3b8;
            --tab-glow-color: rgba(148, 163, 184, 0.85);
            background: linear-gradient(135deg, rgba(100, 116, 139, 0.3), rgba(51, 65, 85, 0.6)) !important;
            border: 3.5px solid #94a3b8 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            text-shadow: none !important;
        }
        .btn-exit:hover,
        .crystal-btn.btn-exit:hover {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 3.5px solid #cbd5e1 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(148, 163, 184, 0.85), 0 0 24px rgba(148, 163, 184, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-4px) scale(1.06) !important;
        }

        /* ═══ Additional Controls (حواف موحدة 3.5px وخلفية سوداء ناصعة عند الوقوف) ═══ */
        .btn-add-account-block {
            border: 3.5px solid #34d399 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
        }
        .btn-add-account-block:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            transform: translateY(-3px) scale(1.03) !important;
            text-shadow: none !important;
        }
        .btn-remove-block {
            border: 3.5px solid #ef4444 !important;
        }
        .btn-remove-block:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(239, 68, 68, 0.85), 0 0 24px rgba(239, 68, 68, 0.45) !important;
            transform: scale(1.15) !important;
        }
        button[data-action="add-bank-row"] {
            border: 3.5px solid #22d3ee !important;
            border-radius: 14px !important;
            color: #ffffff !important;
        }
        button[data-action="add-bank-row"]:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #22d3ee !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(6, 182, 212, 0.85), 0 0 24px rgba(34, 211, 238, 0.45) !important;
            transform: scale(1.15) !important;
        }
        button[data-action="add-cheque-row"] {
            border: 3.5px solid #c084fc !important;
            border-radius: 14px !important;
            color: #ffffff !important;
        }
        button[data-action="add-cheque-row"]:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #c084fc !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(168, 85, 247, 0.85), 0 0 24px rgba(192, 132, 252, 0.45) !important;
            transform: scale(1.15) !important;
        }

        /* Luminous Glowing Borders on Hover for Account Blocks & Details Panels */
        .glass-container:hover, .account-block:hover {
            border-color: rgba(255, 255, 255, 0.45) !important;
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.7),
                0 0 35px var(--block-glow-color, rgba(56, 189, 248, 0.4)),
                inset 0 1px 3px rgba(255, 255, 255, 0.5) !important;
        }

        .glass-red:hover,
        .glass-red:focus-within,
        #primary-inputs-container:hover,
        #primary-inputs-container:focus-within {
            --block-glow-color: rgba(251, 191, 36, 0.85);
            border-color: #fbbf24 !important;
            border-right-color: #f59e0b !important;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.8), 0 0 35px rgba(251, 191, 36, 0.85), 0 0 70px rgba(245, 158, 11, 0.45), inset 0 0 20px rgba(251, 191, 36, 0.25) !important;
        }
        .glass-green:hover,
        .glass-green:focus-within,
        .account-block:hover,
        .account-block:focus-within {
            --block-glow-color: rgba(16, 185, 129, 0.85);
            border-color: #34d399 !important;
            border-right-color: #10b981 !important;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.8), 0 0 35px rgba(16, 185, 129, 0.85), 0 0 70px rgba(5, 150, 105, 0.45), inset 0 0 20px rgba(52, 211, 153, 0.25) !important;
        }

        .block-cash-details:hover {
            border-color: rgba(213, 189, 175, 0.75) !important;
            box-shadow: 0 0 25px rgba(213, 189, 175, 0.35), inset 0 1px 2px rgba(255,255,255,0.4) !important;
        }
        /* CSS variables for dynamic bank transfer colors */
        .block-bank-details {
            --bank-bg-start: rgba(12, 74, 110, 0.6);
            --bank-bg-end: rgba(14, 165, 233, 0.15);
            --bank-border: rgba(14, 165, 233, 0.7);
            --bank-shadow: rgba(14, 165, 233, 0.15);
            --bank-hover-border: rgba(56, 189, 248, 1);
            --bank-hover-shadow-outer: rgba(14, 165, 233, 0.9);
            --bank-hover-shadow-inner: rgba(14, 165, 233, 0.5);
            --bank-icon: #38bdf8;
            
            background: linear-gradient(135deg, var(--bank-bg-start), var(--bank-bg-end)) !important;
            border: 1px solid var(--bank-border) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px var(--bank-shadow) !important;
        }
        
        body[data-voucher-type="receipt"] .block-bank-details {
            --bank-bg-start: rgba(8, 145, 178, 0.4); /* Cyan 600 */
            --bank-bg-end: rgba(6, 182, 212, 0.15); /* Cyan 500 */
            --bank-border: rgba(6, 182, 212, 0.7);
            --bank-shadow: rgba(6, 182, 212, 0.25);
            --bank-hover-border: rgba(34, 211, 238, 1); /* Cyan 400 */
            --bank-hover-shadow-outer: rgba(6, 182, 212, 0.8);
            --bank-hover-shadow-inner: rgba(6, 182, 212, 0.5);
            --bank-icon: #22d3ee;
        }
        
        body[data-voucher-type="receipt"] .bank-icon-label {
            color: var(--bank-icon) !important;
        }

        .block-bank-details:hover {
            border-color: var(--bank-hover-border) !important;
            box-shadow: 0 0 40px var(--bank-hover-shadow-outer), inset 0 0 25px var(--bank-hover-shadow-inner) !important;
        }
        .block-cheque-details {
            background: linear-gradient(135deg, rgba(88, 28, 135, 0.6), rgba(168, 85, 247, 0.15)) !important;
            border: 1px solid rgba(168, 85, 247, 0.7) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px rgba(168, 85, 247, 0.15) !important;
        }
        .block-cheque-details:hover {
            border-color: rgba(216, 180, 254, 1) !important;
            box-shadow: 0 0 40px rgba(168, 85, 247, 0.9), inset 0 0 25px rgba(168, 85, 247, 0.5) !important;
        }
        @keyframes shimmerWave {
            0% { transform: translateX(-150%) skewX(-45deg); opacity: 0; }
            11.5% { opacity: 0.15; }
            23% { transform: translateX(150%) skewX(-45deg); opacity: 0; }
            100% { transform: translateX(150%) skewX(-45deg); opacity: 0; }
        }
        .block-bank-details, .block-cheque-details, .glass-red, .glass-green {
            position: relative;
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease, border-color 0.4s ease, background 0.4s ease;
        }
        .block-bank-details::before, .block-cheque-details::before, .glass-red::before, .glass-green::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.02) 45%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0.02) 55%, rgba(255,255,255,0) 100%);
            z-index: 1; opacity: 0; transition: opacity 0.5s ease; pointer-events: none;
        }
        .block-bank-details:hover::before, .block-cheque-details:hover::before, .glass-red:hover::before, .glass-green:hover::before { animation: shimmerWave 6.5s infinite; opacity: 1; }
        .block-bank-details:hover, .block-cheque-details:hover {
            transform: translateY(-2px) scale(1.01) !important;
            z-index: 10;
        }


        /* Advanced Layout & Animations */
        .split-layout-wrapper {
            display: flex; flex-direction: row-reverse; gap: 0; margin-top: 15px; width: 100%; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
        }
        
        /* Side Panel */
        .side-panel {
            width: 0; opacity: 0; overflow: hidden; transform: translateX(-80px) scale(0.95);
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(25px); border-radius: 16px;
            border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1); flex-shrink: 0;
            padding: 0; /* Padding is 0 when hidden */
        }
        
        /* When side panel is active */
        .split-layout-wrapper.has-sidebar { gap: 20px; }
        .split-layout-wrapper.has-sidebar .side-panel {
            width: 380px; opacity: 1; transform: translateX(0) scale(1); padding: 25px;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        /* Main Panel */
        .main-panel {
            flex-grow: 1; transition: all 0.6s cubic-bezier(0.25, 1, 0.2, 1);
            background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(12px);
            border-radius: 16px; padding: 25px; border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transform-origin: left center;
        }

        @media (max-width: 900px) {
            .split-layout-wrapper.has-sidebar { flex-direction: column; }
            .split-layout-wrapper.has-sidebar .side-panel { width: 100%; transform: translateX(0) translateY(-20px); }
        }

        /* Payment Methods Tabs */
        .payment-methods-tabs {
            display: flex; gap: 10px; background: rgba(0,0,0,0.4); padding: 8px;
            border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
        }
        .pm-tab { flex: 1; cursor: pointer; }
        .pm-tab input { display: none; }
        .pm-tab-content {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 15px 5px; gap: 10px; border-radius: 10px; transition: all 0.4s cubic-bezier(0.25, 1, 0.2, 1);
            color: #94a3b8; font-weight: 700; font-size: 0.9rem;
        }
        .pm-icon { font-size: 1.8rem; transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s; filter: grayscale(100%); opacity: 0.5; }
        
        .pm-tab input:checked + .pm-tab-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); 
            color: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            transform: translateY(-4px);
        }
        .pm-tab input:checked + .pm-tab-content .pm-icon {
            transform: scale(1.3); filter: grayscale(0%); opacity: 1;
            text-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }
        /* Custom Searchable Dropdown */
        
        /* Fix Dropdown Z-Index */
        .custom-dropdown-input-wrap { z-index: 10; }
        .custom-dropdown-input-wrap:focus-within { z-index: 999999 !important; }
        .voucher-block:focus-within { z-index: 999999 !important; }
        .custom-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            background: #0b1120 !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            max-height: 280px;
            overflow-y: auto;
            text-align: right;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            display: none;
            margin-top: 6px;
        }
        .custom-dropdown-list.show {
            display: block;
            animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .custom-dropdown-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .custom-dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .custom-dropdown-item:last-child {
            border-bottom: none;
        }

        /* ══════════════════════════════════════════════════════════════════════
           VOUCHER TYPE TOGGLE BUTTONS (صرف إلى مجموعة 📤 / استلام من مجموعة 📥)
           ستايل فاخر، متناسق ومتقن، بألوان مخففة، كتابة متناسقة، وإنارة حواف أخاذة
           ══════════════════════════════════════════════════════════════════════ */
        .voucher-type-btn {
            position: relative !important;
            min-width: 215px !important;
            height: 52px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            border-radius: 14px !important;
            cursor: pointer !important;
            font-family: 'Tajawal', sans-serif !important;
            font-size: 1.12rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.3px !important;
            white-space: nowrap !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            transition: all 0.32s cubic-bezier(0.16, 1, 0.3, 1) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            box-sizing: border-box !important;
            padding: 0 24px !important;
            text-decoration: none !important;
            overflow: hidden !important;
            z-index: 2 !important;
        }

        /* حماية خط النص والأيقونات من أي تأثير خارجي وضمان تناسق ألوان الخط */
        .voucher-type-btn .btn-text,
        .voucher-type-btn .btn-icon,
        .voucher-type-btn span,
        .voucher-type-btn i {
            color: inherit !important;
            font-family: inherit !important;
            text-decoration: none !important;
        }

        /* ─── إيماءة لمعان زجاجي سائل عند المرور بالفأرة (Kinetic Glass Sweep) ─── */
        .voucher-type-btn::after {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: -140% !important;
            width: 80% !important;
            height: 100% !important;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent) !important;
            transform: skewX(-25deg) !important;
            pointer-events: none !important;
            transition: left 0.65s cubic-bezier(0.16, 1, 0.3, 1) !important;
            z-index: 3 !important;
        }
        .voucher-type-btn:hover::after {
            left: 140% !important;
        }

        /* ─── 1. زر صرف إلى مجموعة (حالة الراحة / غير مفعل) ─── */
        #btn-payment:not(.active-payment) {
            background: linear-gradient(135deg, rgba(50, 16, 24, 0.55), rgba(20, 8, 14, 0.75)) !important;
            border: 3.5px solid rgba(244, 63, 94, 0.7) !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            transform: scale(0.98) !important;
        }
        #btn-payment:not(.active-payment):hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #fb7185 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(244, 63, 94, 0.85), 0 0 24px rgba(244, 63, 94, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.03) !important;
        }

        /* ─── زر صرف إلى مجموعة (مفعل: أحمر غامق فاخر، كتابة ناصعة متناسقة، إنارة حواف) ─── */
        .voucher-type-btn.active-payment,
        #btn-payment.active-payment {
            background: linear-gradient(135deg, #781827 0%, #4a0e19 100%) !important;
            border: 3.5px solid #fb7185 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), 0 0 12px rgba(159, 18, 57, 0.45) !important;
            transform: translateY(-2px) scale(1.03) !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8) !important;
            animation: voucherEdgePulsePayment 3.5s infinite alternate ease-in-out;
        }
        .voucher-type-btn.active-payment:hover,
        #btn-payment.active-payment:hover,
        #btn-payment:not(.active-payment):hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #fb7185 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(244, 63, 94, 0.85), 0 0 24px rgba(244, 63, 94, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ─── 2. زر استلام من مجموعة (حالة الراحة / غير مفعل) ─── */
        #btn-receipt:not(.active-receipt) {
            background: linear-gradient(135deg, rgba(14, 42, 34, 0.55), rgba(8, 24, 18, 0.75)) !important;
            border: 3.5px solid rgba(16, 185, 129, 0.7) !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            transform: scale(0.98) !important;
        }
        #btn-receipt:not(.active-receipt):hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.03) !important;
        }

        /* ─── زر استلام من مجموعة (مفعل: زمردي غامق فاخر، كتابة ناصعة متناسقة، إنارة حواف) ─── */
        .voucher-type-btn.active-receipt,
        #btn-receipt.active-receipt {
            background: linear-gradient(135deg, #0d5440 0%, #063326 100%) !important;
            border: 3.5px solid #34d399 !important;
            border-radius: 14px !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), 0 0 12px rgba(16, 185, 129, 0.45) !important;
            transform: translateY(-2px) scale(1.03) !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8) !important;
            animation: voucherEdgePulseReceipt 3.5s infinite alternate ease-in-out;
        }
        .voucher-type-btn.active-receipt:hover,
        #btn-receipt.active-receipt:hover {
            background: #000000 !important;
            background-image: none !important;
            border: 3.5px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            text-shadow: none !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ─── نبض إنارة الحواف الهادئ والمستمر لكلا الزرين المفعلين ─── */
        @keyframes voucherEdgePulsePayment {
            0% { 
                border-color: rgba(244, 63, 94, 0.7);
                box-shadow: 0 6px 20px rgba(0,0,0,0.45), 0 0 16px rgba(159, 18, 57, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25); 
            }
            100% { 
                border-color: #fb7185;
                box-shadow: 0 8px 26px rgba(0,0,0,0.5), 0 0 26px rgba(244, 63, 94, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.4); 
            }
        }
        @keyframes voucherEdgePulseReceipt {
            0% { 
                border-color: rgba(16, 185, 129, 0.7);
                box-shadow: 0 6px 20px rgba(0,0,0,0.45), 0 0 16px rgba(16, 185, 129, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.25); 
            }
            100% { 
                border-color: #34d399;
                box-shadow: 0 8px 26px rgba(0,0,0,0.5), 0 0 26px rgba(16, 185, 129, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.4); 
            }
        }

        /* ─── حركة إيماءات الأيقونة الداخلية ─── */
        .voucher-type-btn .btn-icon {
            font-size: 1.3rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease !important;
        }
        .voucher-type-btn.active-payment .btn-icon i {
            filter: none !important;
        }
        .voucher-type-btn.active-receipt .btn-icon i {
            filter: none !important;
        }
        .voucher-type-btn:hover .btn-icon {
            transform: scale(1.22) rotate(-8deg) !important;
        }

        /* ══════════════════════════════════════════════════════════════════════
           ULTRA-SUBTLE AMBIENT GLASS RIPPLES & SCREEN-WIDE LIGHT WAVES
           تموجات ولمعات زجاجية خفيفة جداً لكامل الشاشة لا تؤثر على البصر
           ══════════════════════════════════════════════════════════════════════ */
        .glass-ripple-mesh {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100vw !important; height: 100vh !important;
            pointer-events: none !important;
            z-index: 0 !important;
        }

        .glass-ripple-1 {
            background: radial-gradient(ellipse 70% 50% at 25% 25%, rgba(255, 255, 255, 0.035) 0%, rgba(56, 189, 248, 0.02) 40%, transparent 70%) !important;
            filter: blur(48px) !important;
            animation: slowGlassWave1 36s ease-in-out infinite alternate !important;
        }

        .glass-ripple-2 {
            background: radial-gradient(ellipse 65% 45% at 75% 75%, rgba(255, 255, 255, 0.03) 0%, rgba(168, 85, 247, 0.018) 40%, transparent 70%) !important;
            filter: blur(56px) !important;
            animation: slowGlassWave2 46s ease-in-out infinite alternate !important;
        }

        .glass-ripple-3 {
            background: radial-gradient(ellipse 80% 55% at 50% 50%, rgba(255, 255, 255, 0.025) 0%, rgba(16, 185, 129, 0.015) 35%, rgba(251, 191, 36, 0.012) 65%, transparent 80%) !important;
            filter: blur(65px) !important;
            animation: slowGlassWave3 54s ease-in-out infinite alternate !important;
        }

        .glass-specular-shimmer {
            position: fixed !important;
            top: 0 !important; left: -100% !important;
            width: 300% !important; height: 100vh !important;
            background: linear-gradient(115deg, transparent 0%, transparent 42%, rgba(255, 255, 255, 0.02) 50%, transparent 58%, transparent 100%) !important;
            pointer-events: none !important;
            animation: specularGlassSweep 42s linear infinite !important;
            z-index: 0 !important;
        }

        @keyframes slowGlassWave1 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 0.55; }
            50% { transform: translate(3%, 3%) scale(1.08) rotate(2deg); opacity: 0.85; }
            100% { transform: translate(-3%, 4%) scale(0.96) rotate(-2deg); opacity: 0.55; }
        }

        @keyframes slowGlassWave2 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: translate(-4%, -3%) scale(1.1) rotate(-3deg); opacity: 0.8; }
            100% { transform: translate(3%, -2%) scale(0.95) rotate(2deg); opacity: 0.5; }
        }

        @keyframes slowGlassWave3 {
            0% { transform: scale(1) translate(0, 0); opacity: 0.45; }
            50% { transform: scale(1.12) translate(2%, -3%); opacity: 0.8; }
            100% { transform: scale(0.92) translate(-2%, 2%); opacity: 0.45; }
        }

        @keyframes specularGlassSweep {
            0% { transform: translateX(-35%); }
            100% { transform: translateX(35%); }
        }

        /* لمسة ضوئية زجاجية فائقة الرقة وغير محسوسة تتهادى فوق اللوحة الرئيسية */
        #main-form-panel::before {
            content: '' !important;
            position: absolute !important;
            top: -30% !important; left: -30% !important;
            width: 160% !important; height: 160% !important;
            background: radial-gradient(ellipse 60% 40% at 50% 50%, rgba(255, 255, 255, 0.02) 0%, transparent 60%) !important;
            pointer-events: none !important;
            animation: glassSubtleDrift 28s ease-in-out infinite alternate !important;
            z-index: 0 !important;
        }

        @keyframes glassSubtleDrift {
            0% { transform: translate(0, 0); opacity: 0.4; }
            50% { transform: translate(30px, 20px); opacity: 0.75; }
            100% { transform: translate(-20px, 30px); opacity: 0.4; }
        }

        /* خط كريستالي علوي شفاف يضفي لمسة زجاجية مصقولة راقية */
        #main-form-panel,
        #payment-methods-row,
        .glass-container,
        .account-block {
            border-top: 1.5px solid rgba(255, 255, 255, 0.22) !important;
        }

        /* Color Wave Animation */
        @keyframes colorWave {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .wave-bg {
            background-size: 300% 300% !important;
            animation: colorWave 5s ease infinite !important;
        }

        /* ═══ Row Add/Delete Animations ═══ */
        @keyframes rowSlideIn {
            from { opacity: 0; transform: translateY(-15px) scale(0.97); max-height: 0; }
            to { opacity: 1; transform: translateY(0) scale(1); max-height: 100px; }
        }
        @keyframes rowSlideOut {
            from { opacity: 1; transform: translateX(0) scale(1); max-height: 100px; }
            to { opacity: 0; transform: translateX(40px) scale(0.9); max-height: 0; padding: 0; margin: 0; }
        }
        .multi-row { animation: rowSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .multi-row.removing { animation: rowSlideOut 0.3s ease forwards; pointer-events: none; }

        /* ═══ Focus Glow Effect ═══ */
        .multi-input:focus, .v-input:focus, .ctrl-input:focus {
            outline: none !important;
            border-color: rgba(56, 189, 248, 0.6) !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15), 0 0 15px rgba(56, 189, 248, 0.1) !important;
            transition: border-color 0.3s, box-shadow 0.3s !important;
        }

        /* ═══ Shake Animation for Errors ═══ */
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            10%, 50%, 90% { transform: translateX(-6px); }
            30%, 70% { transform: translateX(6px); }
        }
        .shake-error {
            animation: shakeError 0.5s ease;
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        /* ═══ Save Success Celebration ═══ */
        @keyframes saveSuccess {
            0% { transform: scale(1); }
            30% { transform: scale(1.15); }
            50% { transform: scale(0.95); }
            70% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .save-success-anim {
            animation: saveSuccess 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        /* ═══ Confetti Overlay ═══ */
        @keyframes confettiFall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .confetti-piece {
            position: fixed;
            width: 10px;
            height: 10px;
            top: -10px;
            z-index: 99999;
            border-radius: 2px;
            animation: confettiFall 2.5s linear forwards;
            pointer-events: none;
        }

        /* ═══ Number Counter Transition ═══ */
        .amount-animate {
            transition: transform 0.2s ease;
        }
        .amount-animate.changed {
            transform: scale(1.08);
            color: #fbbf24 !important;
        }

        /* ═══ Button Active Press ═══ */
        .crystal-btn:active, .btn-add-row-inline:active {
            transform: scale(0.92) !important;
            transition: transform 0.1s !important;
        }

        /* ═══ Transfer/Cheque Row Animations ═══ */
        .transfer-row, .cheque-row {
            animation: rowSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .transfer-row.removing, .cheque-row.removing {
            animation: rowSlideOut 0.3s ease forwards;
            pointer-events: none;
        }

        /* ═══ Payment Section Smooth Transition ═══ */
        #cash-section, #bank-section, #cheque-section {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ═══ Skeleton Loading ═══ */
        @keyframes skeletonShimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }
        .skeleton-loading {
            background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
            background-size: 200px 100%;
            animation: skeletonShimmer 1.5s infinite;
            border-radius: 6px;
        }

        /* ══════════════════════════════════════════════════════════════════════
           AUTOMATIC LUMINOUS EDGE ILLUMINATION (إضاءة الحواف التلقائية التفاعلية)
           تأتي الإضاءة لوحدها بمجرد وقوف المؤشر (Hover) أو الضغط والتركيز (Focus)
           ══════════════════════════════════════════════════════════════════════ */

        /* ─── 1. حقول المبالغ والأرقام: إضاءة الحواف التلقائية بمجرد الوقوف والضغط ─── */
        .transfer-amount, .cheque-amount, .block-sub-amount, .block-cash-amount, .block-bank-amount, .block-cheque-amount, .block-total-amount, .total-val, .val, .value-field, .multi-input[placeholder*="المبلغ"], .multi-input[placeholder*="القيمة"] {
            background: rgba(15, 23, 42, 0.45) !important;
            color: #ffffff !important;
            border: 3.5px solid rgba(255, 255, 255, 0.35) !important;
            border-radius: 12px !important;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .block-total-amount,
        input[id^="amount-"],
        .transfer-amount,
        .cheque-amount,
        .block-sub-amount,
        .multi-input[placeholder*="المبلغ"] {
            border: 3.5px solid #fbbf24 !important;
            border-radius: 12px !important;
        }

        /* عند وقوف المؤشر على أي حقل مبلغ (Hover) */
        .block-total-amount:hover,
        input[id^="amount-"]:hover,
        .transfer-amount:hover,
        .cheque-amount:hover,
        .block-sub-amount:hover,
        .multi-input[placeholder*="المبلغ"]:hover {
            border: 3.5px solid #fbbf24 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(251, 191, 36, 0.35) !important;
            background: rgba(251, 191, 36, 0.12) !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        /* عند الضغط على المبلغ والتركيز (Focus) */
        .transfer-amount:focus, .cheque-amount:focus, .block-sub-amount:focus, .block-total-amount:focus, .multi-input[placeholder*="المبلغ"]:focus {
            border: 3.5px solid #fbbf24 !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.95), 0 0 15px rgba(251, 191, 36, 0.4) !important;
            outline: none !important;
        }

        /* حقول مبالغ التحويلات البنكية عند الوقوف */
        .block-bank-amount {
            border: 3.5px solid #06b6d4 !important;
            border-radius: 12px !important;
        }
        .block-bank-amount:hover, .block-bank-amount:focus {
            border: 3.5px solid #22d3ee !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(34, 211, 238, 0.35) !important;
            background: rgba(6, 182, 212, 0.18) !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        /* حقول مبالغ الشيكات عند الوقوف */
        .block-cheque-amount {
            border: 3.5px solid #a855f7 !important;
            border-radius: 12px !important;
        }
        .block-cheque-amount:hover, .block-cheque-amount:focus {
            border: 3.5px solid #c084fc !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(192, 132, 252, 0.35) !important;
            background: rgba(147, 51, 234, 0.18) !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.02) !important;
        }

        /* ─── 2. منطقة الدولار (سعر التعادل): أرقام حادة بدون ظل أو هالة ─── */
        #exchange-rate {
            font-size: 1.8rem !important;
            font-weight: 900 !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            border: 3.5px solid #10b981 !important;
            border-radius: 12px !important;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        #exchange-rate:hover,
        #exchange-rate:focus,
        #exchange-rate:active {
            border: 3.5px solid #10b981 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5), 0 0 12px rgba(16, 185, 129, 0.4) !important;
            background: rgba(16, 185, 129, 0.2) !important;
            color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            transform: translateY(-2px) scale(1.04) !important;
            outline: none !important;
        }
        #label-exchange-rate {
            transition: all 0.25s ease;
        }
        #label-exchange-rate:hover {
            color: #34d399 !important;
            text-shadow: none !important;
        }
        #label-exchange-rate:hover span:last-child {
            transform: rotate(90deg) scale(1.25);
            filter: drop-shadow(0 0 4px rgba(251,191,36,0.6));
        }

        /* ─── 3. منطقة إجمالي المحلي والمعادل بالأجنبي (حواف حمراء موحدة بقوة 3.5px وخلفية بيضاء) ─── */
        #amount-local-hdr, #amount-foreign {
            font-size: 2.6rem !important;
            font-weight: 900 !important;
            color: #ef4444 !important;
            -webkit-text-fill-color: #ef4444 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            text-align: center !important;
            border: 3.5px solid #ef4444 !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            width: 100% !important;
            box-sizing: border-box !important;
            transition: all 0.25s ease !important;
        }
        #amount-local-hdr:hover, #amount-local-hdr:focus,
        #amount-foreign:hover, #amount-foreign:focus {
            border: 3.5px solid #dc2626 !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2) !important;
            color: #ef4444 !important;
            -webkit-text-fill-color: #ef4444 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            transform: scale(1.02) !important;
            outline: none !important;
        }

        /* ─── 4. إنارة الحواف في منطقة «من حساب» باللون الأصفر ─── */
        #primary-inputs-container .custom-dropdown-input-wrap {
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        #primary-inputs-container .custom-dropdown-input-wrap:hover,
        #primary-inputs-container .custom-dropdown-input-wrap:focus-within {
            box-shadow: 0 0 24px rgba(251, 191, 36, 0.85), 0 0 45px rgba(245, 158, 11, 0.45);
        }

        #main-account-search {
            border: 1.5px solid rgba(244, 63, 94, 0.6) !important;
            transition: all 0.3s ease !important;
        }
        #main-account-search:hover,
        #main-account-search:focus {
            border: 2px solid #fbbf24 !important;
            box-shadow: 0 0 28px rgba(251, 191, 36, 0.95), 0 0 50px rgba(245, 158, 11, 0.5), inset 0 0 10px rgba(251, 191, 36, 0.25) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        /* ─── 5. إنارة الحواف في منطقة «إلى حساب» التلقائية ─── */
        .block-account-search {
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .block-account-search:hover,
        .block-account-search:focus {
            border: 2px solid #34d399 !important;
            box-shadow: 0 0 28px rgba(52, 211, 153, 0.95), 0 0 50px rgba(16, 185, 129, 0.5), inset 0 0 10px rgba(52, 211, 153, 0.25) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        /* ─── 6. كل شيء عندما نأتي إليه الإضاءة تأتي لوحدها (Universal Controls) ─── */
        #currency:hover, #currency:focus {
            border: 2px solid #fbbf24 !important;
            box-shadow: 0 0 22px rgba(251, 191, 36, 0.9), 0 0 40px rgba(245, 158, 11, 0.45), inset 0 0 8px rgba(251, 191, 36, 0.25) !important;
            transform: translateY(-2px);
            outline: none !important;
        }

        #voucher-desc:hover, #voucher-desc:focus,
        input[id^="statement-"]:hover, input[id^="statement-"]:focus,
        .block-bank-desc-input:hover, .block-bank-desc-input:focus,
        .block-cheque-desc-input:hover, .block-cheque-desc-input:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 22px rgba(251, 191, 36, 0.85), inset 0 0 8px rgba(251, 191, 36, 0.25) !important;
            outline: none !important;
        }

        /* تفاصيل السند العلوية (السند، التاريخ، المرجع) */
        #payment-methods-row > div:last-child > div {
            transition: all 0.28s ease;
        }
        #payment-methods-row > div:last-child > div:hover,
        #payment-methods-row > div:last-child > div:focus-within {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 24px rgba(56, 189, 248, 0.8), inset 0 0 8px rgba(56, 189, 248, 0.25) !important;
            transform: translateY(-2px);
        }
        #voucher-ref:hover, #voucher-ref:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.85) !important;
            outline: none !important;
        }

        /* ═══ زر كشف الحساب: إنارة خضراء ساطعة بالحواف عند الوقوف عليه ═══ */
        .btn-statement-glow {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.3)) !important;
            border: 1.5px solid rgba(16, 185, 129, 0.55) !important;
            border-right: none !important;
            color: #34d399 !important;
            padding: 0 14px !important;
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: auto !important;
            font-size: 0.8rem !important;
            font-weight: bold !important;
            white-space: nowrap !important;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.2) !important;
            position: relative !important;
            overflow: hidden !important;
            user-select: none !important;
        }

        .btn-statement-glow:hover, 
        .btn-statement-glow:focus {
            background: #000000 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 1.5px solid #10b981 !important;
            border-right: none !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 12px rgba(16, 185, 129, 0.85), 0 0 24px rgba(16, 185, 129, 0.45) !important;
            transform: scale(1.03) !important;
            filter: none !important;
            text-shadow: none !important;
            z-index: 5 !important;
        }

        .btn-statement-glow:active {
            transform: scale(0.97) !important;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.7) !important;
        }
        
        /* 🌟 مقاسات الوضع الطبيعي الأساسي (حواف أرقام حادة ونقية بدون أي ظل أو هالة) */
        input.block-total-amount, input.block-bank-amount, input.block-cheque-amount, input.transfer-amount, input.cheque-amount, input.block-sub-amount {
            font-size: 3.1rem !important;
            height: 62px !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        
        input.block-transfer-no-input, input.block-cheque-no-input, input.transfer-no, input.cheque-no {
            font-size: 2.8rem !important;
            height: 62px !important;
            font-weight: 900 !important;
            letter-spacing: 1px !important;
            color: #ffffff !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        
        /* 🔍 مقاس موحد ومكبر بدقة 4.5rem لجميع حقول المبالغ والأرقام عند الضغط والتركيز والكتابة باللون الذهبي بحواف حادة */
        input.block-total-amount:focus,
        input.block-bank-amount:focus,
        input.block-cheque-amount:focus,
        input.transfer-amount:focus,
        input.cheque-amount:focus,
        input.block-sub-amount:focus {
            font-size: 4.5rem !important;
            height: 98px !important;
            width: 480px !important;
            min-width: 450px !important;
            max-width: none !important;
            position: relative !important;
            z-index: 99999 !important;
            margin-right: -60px !important;
            color: #fbbf24 !important; /* Gold / Yellow */
            -webkit-text-fill-color: #fbbf24 !important;
            caret-color: #fbbf24 !important;
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            background: rgba(15, 23, 42, 0.98) !important;
            border: 3.5px solid #fbbf24 !important;
            border-radius: 12px !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.9), 0 0 15px rgba(251, 191, 36, 0.4) !important;
        }
        
        input.block-transfer-no-input:focus,
        input.block-cheque-no-input:focus,
        input.transfer-no:focus,
        input.cheque-no:focus {
            font-size: 4.5rem !important;
            height: 98px !important;
            width: 480px !important;
            min-width: 450px !important;
            max-width: none !important;
            position: relative !important;
            z-index: 99999 !important;
            margin-right: -60px !important;
            letter-spacing: 1.5px !important;
            color: #38bdf8 !important; /* Sky Blue */
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            background: rgba(15, 23, 42, 0.98) !important;
            border: 3.5px solid #38bdf8 !important;
            border-radius: 12px !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.9), 0 0 15px rgba(56, 189, 248, 0.35) !important;
        }

        /* Larger Bank Name Font */
        input.block-bank-name-input, input.block-cheque-bank-input, input.block-bank-desc-input, input.block-cheque-desc-input {
            font-size: 1.55rem !important;
            height: 62px !important;
            font-weight: bold !important;
        }

        /* Bank Row Layout Overrides */
        .bank-row > div:nth-child(2) { flex: 1.5 !important; min-width: 140px !important; width: auto !important; }
        .bank-row > div:nth-child(3) { flex: 2 !important; min-width: 160px !important; width: auto !important; }
        .bank-row > div:nth-child(4) { flex: 1.5 !important; min-width: 120px !important; }
        .bank-row > div:nth-child(5) { flex: 3 !important; min-width: 250px !important; }

        /* Cheque Row Layout Overrides */
        .cheque-row > div:nth-child(2) { flex: 1.5 !important; min-width: 140px !important; width: auto !important; }
        .cheque-row > div:nth-child(3) { flex: 2 !important; min-width: 160px !important; width: auto !important; }
        .cheque-row > div:nth-child(4) { flex: 0.8 !important; min-width: 90px !important; } /* Cheque No (Smaller) */
        .cheque-row > div:nth-child(5) { flex: 1.2 !important; min-width: 120px !important; } /* Cheque Date */
        .cheque-row > div:nth-child(6) { flex: 2.5 !important; min-width: 210px !important; } /* Cheque Desc (Wider) */

        /* ═══════════════════════════════════════════════════════════════════════════════
           🎯 حواف أرقام ومبالغ حادة ونقية جداً بدون أي ظل أو إضاءة أو هالة (Sharp & Crisp Numbers)
           ═══════════════════════════════════════════════════════════════════════════════ */
        #amount-local-hdr,
        #amount-foreign,
        #exchange-rate,
        input.block-total-amount,
        input.block-bank-amount,
        input.block-cheque-amount,
        input.block-sub-amount,
        input.transfer-amount,
        input.cheque-amount,
        input.block-transfer-no-input,
        input.block-cheque-no-input,
        input.transfer-no,
        input.cheque-no,
        input[id^="amount-"] {
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: geometricPrecision !important;
            font-feature-settings: "tnum" 1, "zero" 1 !important;
            font-variant-numeric: tabular-nums !important;
        }

        #amount-local-hdr:focus, #amount-local-hdr:hover,
        #amount-foreign:focus, #amount-foreign:hover,
        #exchange-rate:focus, #exchange-rate:hover,
        input.block-total-amount:focus, input.block-total-amount:hover,
        input.block-bank-amount:focus, input.block-bank-amount:hover,
        input.block-cheque-amount:focus, input.block-cheque-amount:hover,
        input.block-sub-amount:focus, input.block-sub-amount:hover,
        input.transfer-amount:focus, input.transfer-amount:hover,
        input.cheque-amount:focus, input.cheque-amount:hover,
        input.block-transfer-no-input:focus, input.block-transfer-no-input:hover,
        input.block-cheque-no-input:focus, input.block-cheque-no-input:hover,
        input.transfer-no:focus, input.transfer-no:hover,
        input.cheque-no:focus, input.cheque-no:hover,
        input[id^="amount-"]:focus, input[id^="amount-"]:hover {
            text-shadow: none !important;
            -webkit-text-stroke: 0px !important;
            filter: none !important;
        }

        /* Custom Number Properties Menu (Enhanced Luxury) */
        #custom-num-menu {
            display: none;
            position: absolute;
            z-index: 100000;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.96));
            backdrop-filter: blur(14px);
            border: 1.5px solid rgba(56, 189, 248, 0.6);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 25px rgba(56, 189, 248, 0.25);
            border-radius: 14px;
            padding: 18px 20px;
            width: 320px;
            color: #e2e8f0;
            font-family: 'Tajawal', 'Inter', sans-serif;
            direction: rtl;
            user-select: none;
        }
        #custom-num-menu .props-header {
            font-weight: 800;
            color: #38bdf8;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            text-align: center;
            font-size: 1.12rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        #custom-num-menu .props-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        #custom-num-menu .props-row-label {
            color: #cbd5e1;
            font-size: 0.92rem;
            font-weight: 700;
        }
        #custom-num-menu .weight-btn {
            background: #1e293b;
            border: 1px solid #334155;
            color: #94a3b8;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        #custom-num-menu .weight-btn.active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.5));
            border-color: #fbbf24;
            color: #fbbf24;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
        }
        #custom-num-menu .color-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.15s, border-color 0.15s;
        }
        #custom-num-menu .color-dot:hover {
            transform: scale(1.25);
            border-color: #ffffff;
        }
        #custom-num-menu .color-dot.active {
            border-color: #ffffff;
            box-shadow: 0 0 10px currentColor;
            transform: scale(1.15);
        }
        #custom-num-menu .prop-action-btn {
            background: #1e293b;
            border: 1px solid #334155;
            color: #38bdf8;
            border-radius: 6px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        #custom-num-menu .prop-action-btn:hover {
            background: #334155;
            border-color: #38bdf8;
            color: #fff;
            transform: scale(1.08);
        }
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
        <div class="glass-ripple-mesh glass-ripple-1"></div>
        <div class="glass-ripple-mesh glass-ripple-2"></div>
        <div class="glass-ripple-mesh glass-ripple-3"></div>
        <div class="glass-specular-shimmer"></div>
    </div>
    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="max-width: 98%; margin: 0 auto; padding: 15px 10px; padding-bottom: 100px;">
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <input type="hidden" id="voucher-type" value="payment">

        <div class="split-layout-wrapper" id="main-layout-wrapper" style="display: block; width: 100%;">
            <!-- Main Content area (Full width) -->
            <div class="main-panel glass-panel" id="main-form-panel" style="padding: 25px; position: relative; width: 100%;">
                
                <!-- Unified Top Row (Payment Methods Row) -->
                <div id="payment-methods-row" style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px; background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.98)); padding: 14px 20px; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.18); flex-wrap: wrap; transition: all 0.4s ease; box-shadow: 0 12px 35px rgba(0, 0, 0, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.25); position: relative; overflow: hidden;">
                    
                    <!-- Title & Toggles -->
                    <div style="display: flex; align-items: center; gap: 15px; flex: none; flex-wrap: wrap;">
                        <div id="main-party-title" style="display: flex; align-items: center; gap: 8px; font-size: 1.1rem; font-weight: 700; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            <span>👤</span> بيان الطرف الرئيسي
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button id="btn-payment" class="voucher-type-btn active-payment" onclick="setVoucherType('payment')" title="الصرف من حساب واحد إلى مجموعة حسابات">
                                <span class="btn-icon"><i class="fa-solid fa-wallet"></i></span> <span class="btn-text">صرف إلى مجموعة</span>
                            </button>
                            <button id="btn-receipt" class="voucher-type-btn" onclick="setVoucherType('receipt')" title="استلام في حساب واحد من مجموعة حسابات">
                                <span class="btn-icon"><i class="fa-solid fa-sack-dollar"></i></span> <span class="btn-text">استلام من مجموعة</span>
                            </button>
                        </div>
                    </div>

                    <!-- Voucher Details (Ref, Date, No) -->
                    <div style="display: flex; align-items: center; gap: 15px; flex: 1; justify-content: flex-end; flex-wrap: wrap;">
                        
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; height: 40px; padding: 0 8px;">
                            <button type="button" onclick="navigateVoucher(-1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 1.1rem; padding: 0 5px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند السابق">◀</button>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="color: #94a3b8; font-size: 1.4rem; font-weight: bold; white-space: nowrap;">📄 السند:</span>
                                <input type="text" id="voucher-no-display" readonly style="background: transparent !important; border: none !important; color: #fff; font-weight: bold; width: 160px; text-align: center; outline: none !important; font-size: 2.4rem; box-shadow: none !important; padding: 0;" value="">
                            </div>
                            <button type="button" onclick="navigateVoucher(1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 1.1rem; padding: 0 5px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند التالي">▶</button>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; height: 50px; padding: 0 12px;">
                            <span style="color: #94a3b8; font-size: 1.4rem; font-weight: bold; white-space: nowrap;">📅 التاريخ:</span>
                            <input type="date" id="voucher-date" style="background: transparent !important; border: none !important; color: #fff; font-weight: bold; padding: 0; margin: 0; height: 100%; width: 200px; outline: none !important; box-shadow: none !important; font-size: 2.2rem; -webkit-appearance: none; appearance: none;">
                        </div>

                        <div style="display: flex; align-items: center;">
                            <input type="text" autocomplete="off" id="voucher-ref" class="form-input" placeholder="رقم المرجع (اختياري)" style="height: 40px; width: 160px; background: rgba(0,0,0,0.3); font-size: 0.9rem;">
                        </div>
                    </div>
                </div>

                <!-- Primary Inputs Glass Container -->
                <div id="primary-inputs-container" class="glass-container glass-red" style="position: relative; z-index: 15;">
                    <!-- Form Inputs Row -->
                    <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; overflow: visible; padding-bottom: 10px; margin-bottom: 15px;">

                        <div style="flex: 0 0 35%; max-width: 35%; min-width: 250px;">
                            <label id="main-account-label" style="color: #f43f5e !important; font-size: 1.25rem !important; font-weight: 800 !important; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; transition: color 0.3s; white-space: nowrap;">من حساب (صرف) <span class="animated-arrow-left" style="color: #f43f5e;">←</span></label>
                            <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative; display: flex;">
                                <input type="hidden" id="main-account">
                                <input type="text" autocomplete="off" class="form-control form-select" id="main-account-search" placeholder="ابحث عن حساب الصرف (خزينة/بنك)..." onfocus="showAccountDropdown('main-account')" onblur="hideAccountDropdownDelayed('main-account')" oninput="filterAccountDropdown('main-account')" style="background-color: var(--input-bg); color: #fbbf24 !important; font-size: 1.4rem !important; font-weight: 900 !important; border: 1px solid rgba(244, 63, 94, 0.5); flex: 1; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                <button type="button" class="btn-statement-glow" onclick="openLedgerForMainAccount()" title="دفتر الأستاذ العام"><i class="fa fa-book" style="margin-left: 4px;"></i> كشف حساب</button>
                                <div class="custom-dropdown-list" id="main-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                            </div>
                        </div>

                        <div style="flex: 0.8; min-width: 90px;">
                            <label id="currency-label" style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block; white-space: nowrap;">العملة</label>
                            <select id="currency" class="form-input system-currency-select input-glow-focus hover-glow" onchange="typeof onCurrencyChange === 'function' ? onCurrencyChange() : (typeof updateCurrency === 'function' ? updateCurrency() : null);" style="border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%; font-size: 1.1rem !important; font-weight: 800;">
                                <option value="SDG">جنيه (SDG)</option>
                                <option value="USD">دولار (USD)</option>
                                <option value="AED">درهم (AED)</option>
                            </select>
                        </div>
                        
                        <div style="flex: 0.8; min-width: 95px; position: relative;">
                            <label id="label-exchange-rate" style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: flex; align-items: center; justify-content: space-between; white-space: nowrap; cursor: pointer;" onclick="openNumPropsFor('exchange-rate', event)" title="كليك يمين أو يسار لتخصيص الخصائص">
                                <span>التعادل (سعر $)</span>
                                <span style="font-size: 0.85rem; opacity: 0.85; transition: transform 0.2s;" onmouseover="this.style.transform='rotate(45deg)'" onmouseout="this.style.transform='rotate(0deg)'" title="خصائص سعر الدولار">⚙️</span>
                            </label>
                            <input type="text" inputmode="decimal" autocomplete="off" id="exchange-rate" class="form-input" value="1" readonly style="font-weight: 900; font-family: monospace; text-align: center; cursor: pointer; background: rgba(16,185,129,0.1); color: #10b981; border-radius: 10px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.dataset.raw=r; this.value=r; if(typeof applyNumPropsToAll === 'function') applyNumPropsToAll(); if(typeof calcTotal === 'function') calcTotal(); else if(typeof recalcAll === 'function') recalcAll();});" oninput="if(typeof calcTotal === 'function') calcTotal();" title="كليك يسار لتعديل السعر | كليك يمين لتخصيص (السمك، الحجم، اللون، الخانات، الفاصلة)">
                        </div>
                        
                        <!-- إجمالي المحلي والمعادل بالأجنبي متساويين مناصفة 50% / 50% (حواف أرقام حادة بدون ظل أو هالة) -->
                        <div id="totals-equal-container" style="display: flex; gap: 15px; flex: 2.8; min-width: 380px; align-items: flex-end;">
                            <div style="flex: 1 1 50%; min-width: 0; width: 50%;">
                                <label id="label-amount-local" style="color: #10b981; font-size: 1.3rem; margin-bottom: 5px; display: block; font-weight: bold; white-space: nowrap;">💵 الإجمالي بالمحلي (SDG)</label>
                                <input type="text" id="amount-local-hdr" class="form-input" value="0.00" readonly style="font-size: 2.6rem; font-weight: 900; font-family: 'Consolas', 'Courier New', monospace; color: #ef4444 !important; -webkit-text-fill-color: #ef4444 !important; background: #ffffff !important; text-align: center; border: 3.5px solid #ef4444 !important; border-radius: 12px !important; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important; text-shadow: none !important; -webkit-text-stroke: 0px !important; filter: none !important; -webkit-font-smoothing: antialiased; text-rendering: geometricPrecision; width: 100%;">
                            </div>
                            
                            <div style="flex: 1 1 50%; min-width: 0; width: 50%;">
                                <label id="label-amount-foreign" style="color: #ef4444; font-size: 1.3rem; margin-bottom: 5px; display: block; font-weight: bold; white-space: nowrap;">💵 المعادل بالأجنبي (USD)</label>
                                <input type="text" id="amount-foreign" class="form-input" value="0.00" readonly style="font-size: 2.6rem; font-weight: 900; font-family: 'Consolas', 'Courier New', monospace; color: #ef4444 !important; -webkit-text-fill-color: #ef4444 !important; background: #ffffff !important; text-align: center; border: 3.5px solid #ef4444 !important; border-radius: 12px !important; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important; text-shadow: none !important; -webkit-text-stroke: 0px !important; filter: none !important; -webkit-font-smoothing: antialiased; text-rendering: geometricPrecision; width: 100%;">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px; background: rgba(0,0,0,0.25); padding: 12px 15px; min-height: 48px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); margin-top: 12px;">
                        <label style="color: #fbbf24; font-size: 0.95rem; font-weight: bold; margin: 0; white-space: nowrap; display: flex; align-items: center; gap: 5px;">✍️ بيان حساب الصرف:</label>
                        <input type="text" autocomplete="off" id="voucher-desc" class="form-input" placeholder="اكتب البيان التفصيلي لحساب الصرف هنا (الذي يظهر في كشف الأستاذ العام)..." style="flex: 1; margin: 0; background: rgba(15,23,42,0.4) !important; border: 1px solid rgba(255,255,255,0.1) !important; padding: 8px 12px; height: 38px !important; font-size: 0.95rem; color: #fff !important; border-radius: 6px;">
                    </div>
                </div>


                <!-- ═══ منطقة سحب ولصق إشعارات الواتساب المباشرة (WhatsApp AI Direct Hub) ═══ -->
                <div id="mcp-whatsapp-ingest-dock" style="background: linear-gradient(135deg, rgba(37,211,102,0.12), rgba(15,23,42,0.75)); border: 2.5px dashed #25D366; border-radius: 16px; padding: 16px 20px; margin-top: 15px; margin-bottom: 20px; backdrop-filter: blur(12px); box-shadow: 0 4px 20px rgba(0,0,0,0.35), 0 0 30px rgba(37,211,102,0.18); transition: all 0.25s ease;" ondragover="event.preventDefault(); this.style.borderColor='#4ade80'; this.style.background='rgba(37,211,102,0.22)';" ondragleave="this.style.borderColor='#25D366'; this.style.background='linear-gradient(135deg, rgba(37,211,102,0.12), rgba(15,23,42,0.75))';" ondrop="FinancialOCR.handleWhatsAppDirectDrop(event)">
                    <input type="file" id="mcp-whatsapp-direct-file-input" multiple accept="image/*" style="display:none;" onchange="FinancialOCR.handleWhatsAppDirectFiles(this.files)">
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #25D366, #128C7E); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #fff; box-shadow: 0 0 16px rgba(37,211,102,0.6); flex-shrink: 0;">
                                💬
                            </div>
                            <div>
                                <div style="color: #ffffff; font-weight: 900; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                                    <span>منطقة استقبال ولصق إشعارات الواتساب الذكية</span>
                                    <span style="background: rgba(37,211,102,0.2); border: 1px solid #25D366; color: #86efac; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 12px;">AI Hub</span>
                                </div>
                                <div style="color: #86efac; font-size: 0.85rem; font-weight: 700; margin-top: 2px;">
                                    انسخ الإشعار من الواتساب ثم اضغط زر اللصق بالماوس أو <kbd style="background: #1e293b; color: #fbbf24; padding: 1px 6px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2); font-family: monospace;">Ctrl + V</kbd>، أو اسحب الصور هنا لتفريغها فوراً في السند!
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" onclick="FinancialOCR.pasteFromClipboardDirect()" style="background: linear-gradient(135deg, #25D366, #128C7E); border: 1.5px solid #4ade80; color: #ffffff; padding: 10px 18px; border-radius: 10px; font-weight: 900; font-size: 0.95rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 0 18px rgba(37,211,102,0.5); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'" title="لصق صورة الإشعار المنسوخة من الحافظة وتفريغها في السند فوراً">
                                <span>📋</span> لصق الإشعار المنسوخ فوراً (Paste)
                            </button>
                            <button type="button" onclick="document.getElementById('mcp-whatsapp-direct-file-input').click()" style="background: rgba(56,189,248,0.18); border: 1.5px solid #38bdf8; color: #7dd3fc; padding: 9px 14px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.3)'" onmouseout="this.style.background='rgba(56,189,248,0.18)'">
                                <span>📂</span> اختيار صور من الجهاز / التنزيلات
                            </button>
                            <button type="button" onclick="FinancialOCR.openWhatsAppWebPopup()" style="background: linear-gradient(135deg, rgba(37,211,102,0.2), rgba(18,140,126,0.2)); border: 1.5px solid #25D366; color: #86efac; padding: 9px 14px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(37,211,102,0.35)'" onmouseout="this.style.background='rgba(37,211,102,0.2)'" title="فتح واتساب ويب في نافذة جانبية فورية لسحب الإشعارات منها">
                                <span>🌐</span> فتح واتساب ويب ↗
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ═══ حاوية الحسابات (Accounts Blocks Container) ═══ -->
                <div id="accounts-blocks-container" style="display: flex; flex-direction: column; gap: 20px; position: relative; z-index: 10;">
                    <!-- Blocks will be dynamically inserted here -->
                </div>

                <!-- ═══ زر إضافة حساب جديد ═══ -->
                <div style="display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 20px; margin-bottom: 30px; flex-wrap: wrap;">
                    <button type="button" class="btn-add-account-block" onclick="addAccountBlock()" style="padding: 12px 30px; border-radius: 14px; background: rgba(16,185,129,0.15); color: #ffffff; border: 3.5px solid #34d399 !important; font-weight: 900; font-size: 1.1rem; cursor: pointer; font-family: 'Tajawal', sans-serif; transition: all 0.3s; display: flex; align-items: center; gap: 10px;">
                        ➕ إضافة حساب مستلم / دافع آخر
                    </button>
                </div>

                <!-- ═══ قالب الكتلة المخفي (Hidden Template) ═══ -->
                <template id="account-block-template">
                    <div class="account-block glass-container glass-green" data-block-id="{ID}" style="position: relative; padding: 15px; border-radius: 12px; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding-bottom: 0px;">
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 1.25rem; font-weight: 800; color: #34d399;">
                                <span>📋</span> <span class="block-label">إلى حساب (مستلم)</span> <span class="animated-arrow-right" style="color: #34d399;">→</span>
                            </div>
                            <button type="button" class="btn-remove-block" data-action="remove-block" data-block="{ID}" style="background: rgba(239,68,68,0.15); border: 3.5px solid #ef4444; color: #ef4444; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; transition: all 0.2s;" title="حذف هذا الحساب">✖</button>
                        </div>

                        <!-- Row 1: Account, Amount, Statement, and Unified 4-Button Base -->
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 12px;">
                            <!-- Account -->
                            <div style="flex: 0 0 35%; max-width: 35%; min-width: 250px; position: relative; display: flex; flex-direction: column;">
                                <div class="custom-dropdown-input-wrap" style="position: relative; flex: 1; display: flex;">
                                    <input type="hidden" id="account-{ID}-id" class="block-account-id">
                                    <input type="hidden" id="account-{ID}" class="block-account-name">
                                    <input type="text" autocomplete="off" class="form-input block-account-search" id="account-{ID}-search" placeholder="الحساب / الجهة المستفيدة..." onfocus="showAccountDropdown('account-{ID}')" onblur="hideAccountDropdownDelayed('account-{ID}')" oninput="filterAccountDropdown('account-{ID}')" style="font-size: 1.4rem !important; font-weight: 900 !important; color: #fbbf24 !important; flex: 1; margin-bottom: 0; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <button type="button" class="btn-statement-glow" onclick="openLedgerForBlockAccount('{ID}')" title="دفتر الأستاذ العام"><i class="fa fa-book" style="margin-left: 4px;"></i> كشف حساب</button>
                                    <div class="custom-dropdown-list" id="account-{ID}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                                </div>
                            </div>
                            
                            <!-- Total Amount (auto-calculated or direct) -->
                            <div style="flex: 1.1; min-width: 140px; display: flex; flex-direction: column; position: relative;">
                                <input type="text" id="amount-{ID}" oninput="formatInput(this)" class="form-input block-total-amount" placeholder="المبلغ (جنيه)" style="font-weight: 900; font-family: 'Consolas', monospace; text-align: center; flex: 1; margin-bottom: 0; border: 1.8px solid #fbbf24; background: rgba(251,191,36,0.08); transition: all 0.25s ease; text-shadow: none !important; -webkit-text-stroke: 0px !important; letter-spacing: 0.5px;">
                            </div>
                            
                            <!-- Statement (بيان التحويل) -->
                            <div style="flex: 1.3; min-width: 140px; display: flex; flex-direction: column; position: relative;">
                                <input type="text" autocomplete="off" id="statement-{ID}" class="form-input" placeholder="بيان التحويل (اختياري)" style="font-size: 1rem; flex: 1; margin-bottom: 0; background: rgba(255,255,255,0.05); color: #fff;">
                            </div>
                            
                            <!-- ═══ Unified 5-Icon Base (قاعدة الأيقونات الموحدة) ═══ -->
                            <div class="crystal-unified-dock" style="flex: 2.2; min-width: 320px;">
                                <div style="display: flex; gap: 6px; align-items: center; width: 100%;">
                                    <button type="button" class="btn crystal-btn btn-dock-btn block-btn-ai-scan" onclick="FinancialOCR.openScanner({ target: 'multi', blockId: '{ID}' })" title="مسح وقراءة إشعار التحويل البنكي بالذكاء الاصطناعي">
                                        <span class="btn-icon">📷</span> <span class="btn-text">إشعار AI</span>
                                    </button>
                                    <button type="button" class="btn crystal-btn btn-dock-btn block-btn-ai-whatsapp" onclick="FinancialOCR.openWhatsAppIngest({ target: 'multi', blockId: '{ID}' })" title="استيراد ومسح إشعارات الواتساب (إشعار أو دفعة إشعارات) بالذكاء الاصطناعي">
                                        <span class="btn-icon">💬</span> <span class="btn-text">واتساب AI</span>
                                    </button>
                                    <button type="button" class="btn crystal-btn btn-dock-btn block-btn-ai-trainer" onclick="FinancialOCR.openTrainer()" title="نماذج الذكاء الاصطناعي">
                                        <span class="btn-icon">🧠</span> <span class="btn-text">نماذج</span>
                                    </button>
                                </div>
                                <div style="display: flex; gap: 6px; align-items: center; width: 100%;">
                                    <button type="button" id="pm-bank-{ID}" class="btn crystal-btn pay-toggle-btn btn-dock-btn block-btn-bank" data-action="toggle-pm" data-block="{ID}" data-method="bank" title="تحويل بنكي">
                                        <span class="btn-icon">🏦</span> <span class="btn-text">تحويل</span>
                                    </button>
                                    <button type="button" id="pm-cheque-{ID}" class="btn crystal-btn pay-toggle-btn btn-dock-btn block-btn-cheque" data-action="toggle-pm" data-block="{ID}" data-method="cheque" title="شيك">
                                        <span class="btn-icon">✍️</span> <span class="btn-text">شيك</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Details Sections -->
                        <!-- Bank Details (supports multiple transfers) -->
                        <div id="bank-details-{ID}" class="block-bank-details" style="display: none; border-radius: 12px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease; backdrop-filter: blur(12px);">
                            <div class="bank-rows-container" id="bank-rows-{ID}">
                                <div class="bank-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div class="bank-icon-label" style="width: 80px; font-weight: bold; display: flex; align-items: center; gap: 5px; flex-shrink: 0; transition: color 0.4s ease;">🏦 بنكي</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" style="margin-bottom: 0; font-family: 'Consolas', monospace; font-weight: 900; text-align: center; text-shadow: none !important; -webkit-text-stroke: 0px !important;">
                                    </div>
                                    <div style="flex: 1; min-width: 120px;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-name-input" placeholder="اسم البنك" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1; min-width: 120px;">
                                        <input type="text" autocomplete="off" class="form-input block-transfer-no-input" placeholder="رقم التحويلة" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1.5; min-width: 150px;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-desc-input" placeholder="بيان التحويل" style="margin-bottom: 0;">
                                    </div>
                                    <button type="button" data-action="add-bank-row" data-block="{ID}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(6, 182, 212, 0.2); border: 3.5px solid #22d3ee; color: #22d3ee; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة تحويلة أخرى">➕</button>
                                </div>
                            </div>
                        </div>

                        <!-- Cheque Details (supports multiple cheques) -->
                        <div id="cheque-details-{ID}" class="block-cheque-details" style="display: none; border-radius: 12px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease; backdrop-filter: blur(12px);">
                            <div class="cheque-rows-container" id="cheque-rows-{ID}">
                                <div class="cheque-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div style="width: 80px; font-weight: bold; color: #d8b4fe; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">✍️ شيك</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" style="margin-bottom: 0; font-family: 'Consolas', monospace; font-weight: 900; text-align: center; text-shadow: none !important; -webkit-text-stroke: 0px !important;">
                                    </div>
                                    <div style="flex: 1; min-width: 90px;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-bank-input" placeholder="اسم البنك" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1; min-width: 80px;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-no-input" placeholder="رقم الشيك" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1; min-width: 100px;">
                                        <input type="date" class="form-input block-cheque-date-input" title="تاريخ الاستحقاق" style="margin-bottom: 0;">
                                    </div>
                                    <div style="flex: 1.5; min-width: 150px;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-desc-input" placeholder="بيان الشيك" style="margin-bottom: 0;">
                                    </div>
                                    <button type="button" data-action="add-cheque-row" data-block="{ID}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(139,92,246,0.2); border: 3.5px solid #c084fc; color: #c084fc; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة شيك آخر">➕</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            <!-- Hidden radios to prevent legacy JS checks breaking -->
            <div style="display: none;">
                <input type="radio" name="payment_method" value="cash" id="radio-cash" checked>
                <input type="radio" name="payment_method" value="check" id="radio-check">
                <input type="radio" name="payment_method" value="mobile" id="radio-mobile">
                <input type="radio" name="payment_method" value="hybrid" id="radio-hybrid">
                <!-- Hidden legacy inputs -->
                <input type="text" autocomplete="off" id="mobile-transfer-no">
                <input type="text" autocomplete="off" id="mobile-note">
                <input type="text" autocomplete="off" id="check-no">
                <input type="text" autocomplete="off" id="check-branch">
                <input type="date" id="check-date">
                <input type="date" id="check-due-date">
                <table><tbody id="entries-body"></tbody></table>
            </div>

            <style>
                .footer-actions .btn { font-size: 0.85rem !important; padding: 8px 15px !important; }
                .footer-actions .btn-save-master { font-size: 0.95rem !important; padding: 8px 20px !important; font-weight: bold; }
                
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
            </style>

            <!-- Action Buttons Section Integrated Into Main Card -->
            <div style="padding: 20px 0 10px 0; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px; width: 100%;">
                <!-- Action Buttons Section -->
                <div class="footer-actions" style="display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; width: 100%;">
                    <button type="button" class="btn crystal-btn btn-exit" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                    <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()">✨ مستند جديد</button>
                    <button type="button" class="btn crystal-btn btn-print" onclick="directPrint()">🖨️ طباعة</button>
                    <button type="button" class="btn crystal-btn btn-print-preview" onclick="printVoucher()">👁️ معاينة</button>
                    <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()">🗑️ حذف المستند</button>
                    <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="saveVoucher()">✅ حفظ واعتماد السند</button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Context Menu -->
    <div class="context-menu" id="column-context-menu">
        <div style="color: #fbbf24; font-size: 0.8rem; margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;">إظهار / إخفاء الأعمدة</div>
        <label class="context-menu-item"><input type="checkbox" value="cost-center" onchange="toggleColumn('cost-center', this.checked)"> مركز التكلفة</label>
        <label class="context-menu-item"><input type="checkbox" value="note" onchange="toggleColumn('note', this.checked)"> ملحوظة</label>
        <label class="context-menu-item"><input type="checkbox" value="check-no" onchange="toggleColumn('check-no', this.checked)"> رقم الشيك</label>
        <label class="context-menu-item"><input type="checkbox" value="check-date" onchange="toggleColumn('check-date', this.checked)"> تاريخ الشيك</label>
        <label class="context-menu-item"><input type="checkbox" value="check-due" onchange="toggleColumn('check-due', this.checked)"> الاستحقاق</label>
    </div>

    <!-- Print Preview Modal -->
    <div id="print-preview-modal" class="preview-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
        <div class="preview-modal-content" style="background: #ffffff; width: 100%; max-width: 900px; height: 90vh; border-radius: 16px; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <!-- Modal Header -->
            <div style="padding: 15px 25px; background: #0f172a; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px; color: #38bdf8;">
                    <span style="font-size: 1.4rem;">👁️</span>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: bold; font-family: 'Tajawal', sans-serif;">معاينة المستند قبل الطباعة</h3>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <button onclick="window.print()" style="background: #0ea5e9; border: none; border-radius: 8px; color: #fff; padding: 8px 16px; font-weight: bold; cursor: pointer; font-family: 'Tajawal', sans-serif; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(14,165,233,0.3); transition: transform 0.2s;">🖨️ طباعة</button>
                    <button onclick="closePrintPreviewModal()" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #94a3b8; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.1rem; transition: all 0.2s;">✖</button>
                </div>
            </div>
            <!-- Scrollable Preview Area -->
            <div id="preview-modal-body" style="flex: 1; overflow-y: auto; padding: 30px; background: #f8fafc; direction: rtl;">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>
    
    <style>
        @keyframes zoomIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @media print {
            body > *:not(#print-preview-modal) {
                display: none !important;
            }
            #print-preview-modal {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: auto !important;
                background: #fff !important;
                padding: 0 !important;
                display: flex !important;
            }
            .preview-modal-content {
                box-shadow: none !important;
                height: auto !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
            #preview-modal-body {
                background: #fff !important;
                padding: 0 !important;
            }
            /* Hide modal header with print/close buttons when printing */
            #print-preview-modal > div > div:first-child {
                display: none !important;
            }
        }
    </style>

    <!-- Edit Warning Modal -->
    <div id="edit-warning-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: linear-gradient(135deg, #7f1d1d, #991b1b); border: 2px solid #f87171; border-radius: 20px; padding: 40px; width: 90%; max-width: 600px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 40px rgba(239,68,68,0.3); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="font-size: 5rem; margin-bottom: 20px; animation: pulse-glow 2s infinite;">⚠️</div>
            <h2 style="color: #ffffff; font-size: 2.2rem; font-weight: 900; margin: 0 0 15px 0; font-family: 'Tajawal', sans-serif; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">تنبيه: هل تريد التعديل؟</h2>
            <p style="color: #fecaca; font-size: 1.2rem; line-height: 1.6; margin-bottom: 35px; font-weight: 500;">أنت تقوم الآن بتعديل سند قديم تم حفظه مسبقاً.<br>حفظ التعديلات سيؤدي إلى تغيير البيانات المالية بأثر رجعي.</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="confirmEditOldVoucher()" style="background: #ef4444; color: white; font-size: 1.3rem; font-weight: 900; padding: 15px 40px; border: none; border-radius: 12px; cursor: pointer; box-shadow: 0 4px 15px rgba(239,68,68,0.4); transition: transform 0.2s;">نعم، اعتمد التعديل</button>
                <button onclick="cancelEditOldVoucher()" style="background: rgba(255,255,255,0.1); color: #ffffff; font-size: 1.3rem; font-weight: 900; padding: 15px 30px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; cursor: pointer;">تراجع ❌</button>
            </div>
        </div>
    </div>



    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?= file_exists('js/app.js') ? filemtime('js/app.js') : '1' ?>"></script>
    <script defer src="js/multi-cash-payment.js?v=<?= time() ?>"></script>
    <script>
    let hasWarnedAboutEdit = false;

    document.addEventListener('DOMContentLoaded', () => {
        // Intercept save by wrapping the original function unconditionally
        if (typeof window.saveVoucher === 'function') {
            const originalSave = window.saveVoucher;
            window.saveVoucher = function(...args) {
                let editIndex = -1;
                try {
                    if (typeof currentVoucherIndex !== 'undefined') {
                        editIndex = currentVoucherIndex;
                    }
                } catch(e) {}
                
                const isEditing = (editIndex !== -1) || (new URLSearchParams(window.location.search).get('id') != null);
                
                if (isEditing && !hasWarnedAboutEdit) {
                    showEditWarningModal();
                } else {
                    hasWarnedAboutEdit = false;
                    return originalSave.apply(this, args);
                }
            };
        }
    });

    function showEditWarningModal(e) {
        if(e && e.target && typeof e.target.blur === 'function') {
            e.target.blur();
        }
        const modal = document.getElementById('edit-warning-modal');
        if(modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.firstElementChild.style.transform = 'scale(1)';
            }, 10);
        }
    }

    function confirmEditOldVoucher() {
        hasWarnedAboutEdit = true;
        const modal = document.getElementById('edit-warning-modal');
        if(modal) {
            modal.style.opacity = '0';
            modal.firstElementChild.style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
                // Trigger the save action now that warning is accepted
                if (typeof window.saveVoucher === 'function') {
                    window.saveVoucher();
                }
            }, 300);
        }
    }

    function cancelEditOldVoucher() {
        if(document.referrer) {
            window.history.back();
        } else {
            window.location.href = 'dashboard-pro.php';
        }
    }
    </script>
    <!-- Custom Number Properties Menu (Enhanced Luxury) -->
    <div id="custom-num-menu">
        <div class="props-header">
            <span>⚙️</span> خصائص: <span id="custom-num-target-name" style="color:#fbbf24; font-weight:800; margin-right:4px;">سعر الدولار</span>
        </div>
        
        <!-- 1. حجم الخط -->
        <div class="props-row">
            <span class="props-row-label">📏 حجم الخط</span>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" class="prop-action-btn" onclick="changeNumProp('font', -1); event.stopPropagation();">−</button>
                <span id="num-prop-font-size-val" style="color: #38bdf8; width: 34px; text-align: center; font-weight: bold; font-size: 1rem;">0</span>
                <button type="button" class="prop-action-btn" onclick="changeNumProp('font', 1); event.stopPropagation();">+</button>
            </div>
        </div>

        <!-- 2. سمك الخط (Font Weight) -->
        <div class="props-row">
            <span class="props-row-label">💪 سمك الخط</span>
            <div style="display: flex; gap: 4px;" id="weight-btn-group">
                <button type="button" class="weight-btn" data-weight="400" onclick="setNumPropWeight('400'); event.stopPropagation();">عادي</button>
                <button type="button" class="weight-btn" data-weight="600" onclick="setNumPropWeight('600'); event.stopPropagation();">متوسط</button>
                <button type="button" class="weight-btn" data-weight="700" onclick="setNumPropWeight('700'); event.stopPropagation();">عريض</button>
                <button type="button" class="weight-btn" data-weight="900" onclick="setNumPropWeight('900'); event.stopPropagation();">ضخم</button>
            </div>
        </div>

        <!-- 3. لون الرقم (Color) -->
        <div class="props-row">
            <span class="props-row-label">🎨 لون الرقم</span>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span class="color-dot" data-color="#10b981" onclick="setNumPropColor('#10b981'); event.stopPropagation();" style="background:#10b981;" title="أخضر زمردي"></span>
                <span class="color-dot" data-color="#fbbf24" onclick="setNumPropColor('#fbbf24'); event.stopPropagation();" style="background:#fbbf24;" title="أصفر ذهبي"></span>
                <span class="color-dot" data-color="#38bdf8" onclick="setNumPropColor('#38bdf8'); event.stopPropagation();" style="background:#38bdf8;" title="سماوي"></span>
                <span class="color-dot" data-color="#ef4444" onclick="setNumPropColor('#ef4444'); event.stopPropagation();" style="background:#ef4444;" title="أحمر"></span>
                <span class="color-dot" data-color="#c084fc" onclick="setNumPropColor('#c084fc'); event.stopPropagation();" style="background:#c084fc;" title="بنفسجي"></span>
                <span class="color-dot" data-color="#ffffff" onclick="setNumPropColor('#ffffff'); event.stopPropagation();" style="background:#ffffff; border:1px solid #aaa;" title="أبيض"></span>
                <input type="color" id="num-prop-color-input" onchange="setNumPropColor(this.value); event.stopPropagation();" oninput="setNumPropColor(this.value); event.stopPropagation();" style="width: 26px; height: 26px; border: none; cursor: pointer; background: transparent; padding: 0; border-radius: 4px;" title="منتقي ألوان مخصص">
            </div>
        </div>

        <!-- 4. الفاصلة الألفية (Thousands Separator) -->
        <div class="props-row">
            <span class="props-row-label">🔢 الفاصلة الألفية (،)</span>
            <span id="num-prop-sep-status" onclick="toggleNumPropSep(); event.stopPropagation();" style="color: #10b981; font-weight: bold; cursor: pointer; background: rgba(16,185,129,0.15); padding: 4px 14px; border-radius: 6px; border: 1px solid #10b981; font-size: 0.9rem;">مفعل</span>
        </div>

        <!-- 5. الخانات العشرية (Decimals) -->
        <div class="props-row">
            <span class="props-row-label">✨ الخانات العشرية</span>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" class="prop-action-btn" onclick="changeNumProp('dec', -1); event.stopPropagation();">−</button>
                <span id="num-prop-dec-val" style="color: #fbbf24; width: 30px; text-align: center; font-weight: bold; font-size: 1.1rem;">0</span>
                <button type="button" class="prop-action-btn" onclick="changeNumProp('dec', 1); event.stopPropagation();">+</button>
                <button type="button" onclick="setNumPropDecimals(0); event.stopPropagation();" style="background:#1e293b; border:1px solid #334155; color:#94a3b8; border-radius:4px; padding:3px 6px; cursor:pointer; font-size:0.75rem;">.0</button>
                <button type="button" onclick="setNumPropDecimals(2); event.stopPropagation();" style="background:#1e293b; border:1px solid #334155; color:#94a3b8; border-radius:4px; padding:3px 6px; cursor:pointer; font-size:0.75rem;">.00</button>
            </div>
        </div>

        <!-- 6. نوع الخط (Font Family) -->
        <div class="props-row" style="margin-bottom: 16px;">
            <span class="props-row-label">🔤 نوع الخط</span>
            <div onclick="toggleNumPropFontFamily(); event.stopPropagation();" style="cursor: pointer; background: rgba(255,255,255,0.08); padding: 5px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.18);">
                <span id="num-prop-font-val" style="color: #fbbf24; font-weight: bold; font-size: 0.95rem;">monospace</span>
            </div>
        </div>

        <!-- 7. زر الحفظ -->
        <button type="button" id="save-num-props-btn" onclick="saveNumProps(); event.stopPropagation();" style="width: 100%; background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; padding: 11px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.35); transition: transform 0.15s, box-shadow 0.15s;">
            حفظ وتطبيق الخصائص 💾
        </button>
    </div>

    <script>
    let dbNumProps = <?php echo $db_num_props_json ?: '{}'; ?>;
    window.DB_OCR_PROPS = <?php echo $db_ocr_props_json ?: '{}'; ?>;
    let allNumProps = (dbNumProps && Object.keys(dbNumProps).length > 0) ? dbNumProps : (JSON.parse(localStorage.getItem('all-num-props')) || {});
    // Sanitize any white color on amount-local-hdr or amount-foreign (never allow white on white)
    if (!allNumProps['amount-local-hdr'] || !allNumProps['amount-local-hdr'].color || allNumProps['amount-local-hdr'].color.toLowerCase() === '#ffffff') {
        if (!allNumProps['amount-local-hdr']) allNumProps['amount-local-hdr'] = { fontSizeMod: 0, thousandsSep: true, decimals: 2, fontFamily: 'monospace', fontWeight: '900' };
        allNumProps['amount-local-hdr'].color = '#ef4444';
    }
    if (!allNumProps['amount-foreign'] || !allNumProps['amount-foreign'].color || allNumProps['amount-foreign'].color.toLowerCase() === '#ffffff') {
        if (!allNumProps['amount-foreign']) allNumProps['amount-foreign'] = { fontSizeMod: 0, thousandsSep: true, decimals: 2, fontFamily: 'monospace', fontWeight: '900' };
        allNumProps['amount-foreign'].color = '#ef4444';
    }
    try { localStorage.setItem('all-num-props', JSON.stringify(allNumProps)); } catch(e) {}
    let currentPropId = 'exchange-rate';

    const targetNames = {
        'exchange-rate': 'سعر الدولار / التعادل',
        'amount-local-hdr': 'الإجمالي بالمحلي (SDG)',
        'amount-foreign': 'المعادل بالأجنبي (USD)',
        'block-bank-amount': 'مبالغ البنك',
        'block-cheque-amount': 'مبالغ الشيكات',
        'block-total-amount': 'مبالغ الحسابات الإجمالية',
        'block-sub-amount': 'مبالغ الحسابات الفرعية',
        'default': 'الرقم المالي'
    };

    function getCanonicalPropId(elOrId) {
        if (!elOrId) return 'default';
        let el = (typeof elOrId === 'object' && elOrId !== null) ? elOrId : document.getElementById(elOrId);
        let strId = (typeof elOrId === 'string') ? elOrId : (el ? (el.id || '') : '');

        if (strId === 'exchange-rate') return 'exchange-rate';
        if (strId === 'amount-local-hdr') return 'amount-local-hdr';
        if (strId === 'amount-foreign') return 'amount-foreign';

        if (el && el.classList) {
            if (el.classList.contains('block-bank-amount') || el.classList.contains('transfer-amount')) return 'block-bank-amount';
            if (el.classList.contains('block-cheque-amount') || el.classList.contains('cheque-amount')) return 'block-cheque-amount';
            if (el.classList.contains('block-sub-amount')) return 'block-sub-amount';
            if (el.classList.contains('block-total-amount')) return 'block-total-amount';
        }

        if (strId) {
            if (strId.startsWith('amount-') || strId.startsWith('total-')) return 'block-total-amount';
            if (strId.includes('bank')) return 'block-bank-amount';
            if (strId.includes('cheque')) return 'block-cheque-amount';
            if (strId.includes('sub')) return 'block-sub-amount';
        }

        return strId || 'default';
    }

    function getActiveProp() {
        currentPropId = getCanonicalPropId(currentPropId);
        if(!allNumProps[currentPropId]) {
            if (currentPropId === 'exchange-rate') {
                allNumProps[currentPropId] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 0,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#10b981'
                };
            } else if (currentPropId === 'amount-local-hdr' || currentPropId === 'amount-foreign') {
                allNumProps[currentPropId] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#ef4444'
                };
            } else {
                allNumProps[currentPropId] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#fbbf24'
                };
            }
        }
        const p = allNumProps[currentPropId];
        if (p.fontWeight === undefined) p.fontWeight = '900';
        if (currentPropId === 'amount-local-hdr' || currentPropId === 'amount-foreign') {
            if (!p.color || p.color.toLowerCase() === '#ffffff') p.color = '#ef4444';
        } else if (p.color === undefined) {
            p.color = (currentPropId === 'exchange-rate') ? '#10b981' : '#fbbf24';
        }
        if (p.fontSizeMod === undefined) p.fontSizeMod = 0;
        if (p.thousandsSep === undefined) p.thousandsSep = true;
        if (p.decimals === undefined) p.decimals = (currentPropId === 'exchange-rate') ? 0 : 2;
        if (p.fontFamily === undefined) p.fontFamily = 'monospace';
        return p;
    }

    function getPropFor(el) {
        let id = getCanonicalPropId(el);
        if(!allNumProps[id]) {
            if (id === 'exchange-rate') {
                allNumProps[id] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 0,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#10b981'
                };
            } else if (id === 'amount-local-hdr' || id === 'amount-foreign') {
                allNumProps[id] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#ef4444'
                };
            } else {
                allNumProps[id] = {
                    fontSizeMod: 0,
                    thousandsSep: true,
                    decimals: 2,
                    fontFamily: 'monospace',
                    fontWeight: '900',
                    color: '#fbbf24'
                };
            }
        }
        const p = allNumProps[id];
        if (p.fontWeight === undefined) p.fontWeight = '900';
        if (id === 'amount-local-hdr' || id === 'amount-foreign') {
            if (!p.color || p.color.toLowerCase() === '#ffffff') p.color = '#ef4444';
        } else if (p.color === undefined) {
            p.color = (id === 'exchange-rate') ? '#10b981' : '#fbbf24';
        }
        if (p.fontSizeMod === undefined) p.fontSizeMod = 0;
        if (p.thousandsSep === undefined) p.thousandsSep = true;
        if (p.decimals === undefined) p.decimals = (id === 'exchange-rate') ? 0 : 2;
        if (p.fontFamily === undefined) p.fontFamily = 'monospace';
        return p;
    }

    const fontFamilies = ['monospace', 'Inter', 'Tajawal', 'Cairo', 'Arial', 'Tahoma'];

    function updateMenuUI() {
        const conf = getActiveProp();
        const targetTitle = document.getElementById('custom-num-target-name');
        if (targetTitle) targetTitle.textContent = targetNames[currentPropId] || 'سعر الدولار / التعادل';

        // 1. Font size mod
        const fontSizeVal = document.getElementById('num-prop-font-size-val');
        if (fontSizeVal) fontSizeVal.textContent = (conf.fontSizeMod > 0 ? '+' : '') + conf.fontSizeMod;

        // 2. Font weight
        document.querySelectorAll('#weight-btn-group .weight-btn').forEach(btn => {
            if (btn.getAttribute('data-weight') === String(conf.fontWeight)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // 3. Color
        document.querySelectorAll('#custom-num-menu .color-dot').forEach(dot => {
            if (dot.getAttribute('data-color').toLowerCase() === (conf.color || '').toLowerCase()) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
        const colorInput = document.getElementById('num-prop-color-input');
        if (colorInput && conf.color) colorInput.value = conf.color;

        // 4. Thousands sep
        const sepStatus = document.getElementById('num-prop-sep-status');
        if (sepStatus) {
            sepStatus.textContent = conf.thousandsSep ? 'مفعل (،)' : 'معطل';
            sepStatus.style.color = conf.thousandsSep ? '#10b981' : '#ef4444';
            sepStatus.style.borderColor = conf.thousandsSep ? '#10b981' : '#ef4444';
            sepStatus.style.background = conf.thousandsSep ? 'rgba(16,185,129,0.15)' : 'rgba(239,68,68,0.15)';
        }

        // 5. Decimals
        const decVal = document.getElementById('num-prop-dec-val');
        if (decVal) decVal.textContent = conf.decimals;

        // 6. Font family
        const fontVal = document.getElementById('num-prop-font-val');
        if (fontVal) fontVal.textContent = conf.fontFamily;
    }

    function setNumPropWeight(weight) {
        const conf = getActiveProp();
        conf.fontWeight = weight;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function setNumPropColor(color) {
        const conf = getActiveProp();
        conf.color = color;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function setNumPropDecimals(dec) {
        const conf = getActiveProp();
        conf.decimals = dec;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function changeNumProp(type, val) {
        const conf = getActiveProp();
        if(type === 'font') {
            conf.fontSizeMod += val;
            applyNumPropsToAll();
            updateMenuUI();
        } else if(type === 'dec') {
            conf.decimals += val;
            if(conf.decimals < 0) conf.decimals = 0;
            if(conf.decimals > 6) conf.decimals = 6;
            updateMenuUI();
            applyNumPropsToAll();
        }
    }

    function toggleNumPropSep() {
        const conf = getActiveProp();
        conf.thousandsSep = !conf.thousandsSep;
        updateMenuUI();
        applyNumPropsToAll();
    }

    function toggleNumPropFontFamily() {
        const conf = getActiveProp();
        let idx = fontFamilies.indexOf(conf.fontFamily);
        idx = (idx + 1) % fontFamilies.length;
        conf.fontFamily = fontFamilies[idx];
        updateMenuUI();
        applyNumPropsToAll();
    }

    function saveNumProps() {
        const cleanedProps = {};
        for (let k in allNumProps) {
            if (!k.startsWith('amount-1') && !k.startsWith('amount-2')) {
                cleanedProps[k] = allNumProps[k];
            }
        }
        allNumProps = cleanedProps;
        localStorage.setItem('all-num-props', JSON.stringify(allNumProps));
        
        let formData = new FormData();
        formData.append('action', 'save_num_props');
        formData.append('num_props', JSON.stringify(allNumProps));
        
        const saveBtn = document.getElementById('save-num-props-btn');
        if (saveBtn) {
            saveBtn.textContent = 'جاري الحفظ في قاعدة البيانات... ⏳';
            saveBtn.style.opacity = '0.7';
        }

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).then(r => r.text()).then(txt => {
            if (saveBtn) {
                saveBtn.textContent = 'تم الحفظ في قاعدة البيانات بنجاح ✅';
                saveBtn.style.background = 'linear-gradient(135deg, #10b981, #047857)';
                saveBtn.style.opacity = '1';
                setTimeout(() => {
                    saveBtn.textContent = 'حفظ وتطبيق الخصائص 💾';
                    const menu = document.getElementById('custom-num-menu');
                    if (menu) menu.style.display = 'none';
                }, 800);
            }
        }).catch(err => {
            if (saveBtn) {
                saveBtn.textContent = 'حفظ وتطبيق الخصائص 💾';
                saveBtn.style.opacity = '1';
            }
        });

        applyNumPropsToAll();
    }

    function formatNumberCustom(valStr, conf) {
        if(!valStr && valStr !== 0 && valStr !== "0") return "";
        let val = parseFloat(valStr.toString().replace(/,/g, ''));
        if(isNaN(val)) return valStr;
        
        let parts = val.toFixed(conf.decimals).split('.');
        if(conf.thousandsSep) {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        return parts.join('.');
    }

    function applyNumPropsToAll() {
        const targets = [
            document.getElementById('exchange-rate'),
            document.getElementById('amount-local-hdr'),
            document.getElementById('amount-foreign'),
            ...document.querySelectorAll('.block-bank-amount'),
            ...document.querySelectorAll('.block-cheque-amount'),
            ...document.querySelectorAll('.block-total-amount'),
            ...document.querySelectorAll('.block-sub-amount')
        ];
        
        targets.forEach(el => {
            if(!el) return;
            const conf = getPropFor(el);
            
            // Force razor-sharp digits with zero shadow / zero stroke
            el.style.setProperty('text-shadow', 'none', 'important');
            el.style.setProperty('-webkit-text-stroke', '0px', 'important');
            el.style.setProperty('filter', 'none', 'important');
            el.style.setProperty('-webkit-font-smoothing', 'antialiased', 'important');
            el.style.setProperty('-moz-osx-font-smoothing', 'grayscale', 'important');
            el.style.setProperty('text-rendering', 'geometricPrecision', 'important');
            
            // 1. Apply font family
            if (conf.fontFamily) {
                let fam = (conf.fontFamily === 'monospace' || !conf.fontFamily) ? "'Consolas', 'Courier New', monospace" : conf.fontFamily;
                el.style.setProperty('font-family', fam, 'important');
            }
            
            // 2. Apply font weight (السمك)
            if (conf.fontWeight) {
                el.style.setProperty('font-weight', conf.fontWeight, 'important');
            }
            
            // 3. Apply color (اللون)
            if (el === document.activeElement && (
                el.classList.contains('block-total-amount') ||
                el.classList.contains('block-bank-amount') ||
                el.classList.contains('block-cheque-amount') ||
                el.classList.contains('block-sub-amount') ||
                el.classList.contains('transfer-amount') ||
                el.classList.contains('cheque-amount')
            )) {
                el.style.setProperty('color', '#fbbf24', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#fbbf24', 'important');
                el.style.setProperty('caret-color', '#fbbf24', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
            } else if (el.id === 'amount-local-hdr' || el.id === 'amount-foreign') {
                let chosenColor = '#ef4444';
                if (conf.color && conf.color.toLowerCase() !== '#ffffff') {
                    chosenColor = conf.color;
                }
                el.style.setProperty('color', chosenColor, 'important');
                el.style.setProperty('-webkit-text-fill-color', chosenColor, 'important');
                el.style.setProperty('background', '#ffffff', 'important');
                el.style.setProperty('background-color', '#ffffff', 'important');
                el.style.setProperty('border', '3.5px solid #ef4444', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
                el.style.setProperty('opacity', '1', 'important');
                el.style.setProperty('visibility', 'visible', 'important');
            } else if (conf.color) {
                el.style.setProperty('color', conf.color, 'important');
                el.style.setProperty('-webkit-text-fill-color', conf.color, 'important');
            }
            
            // 4. Apply font size (الحجم)
            if (el !== document.activeElement) {
                let baseSize = 1.4;
                if (el.id === 'exchange-rate') baseSize = 1.6;
                else if (el.id === 'amount-local-hdr' || el.id === 'amount-foreign') baseSize = 2.6;
                else baseSize = 2.4;

                let newSize = baseSize + (conf.fontSizeMod * 0.1);
                if(newSize < 0.6) newSize = 0.6;
                el.style.setProperty('font-size', newSize + 'rem', 'important');
            }
            
            // 5. Format value
            if(el.value !== "") {
                let cleanVal = el.value.toString().replace(/,/g, '');
                if(!el.dataset.raw) el.dataset.raw = cleanVal;
                else if (cleanVal !== el.dataset.raw && !el.dataset.isFormatting) {
                     el.dataset.raw = cleanVal;
                }
                
                el.dataset.isFormatting = 'true';
                el.value = formatNumberCustom(el.dataset.raw, conf);
                el.dataset.isFormatting = '';
            }
        });
    }

    // Direct function to open the menu for any target
    window.openNumPropsFor = function(targetIdOrEl, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        let el = (typeof targetIdOrEl === 'object' && targetIdOrEl !== null) ? targetIdOrEl : document.getElementById(targetIdOrEl);
        currentPropId = getCanonicalPropId(el || targetIdOrEl);
        
        updateMenuUI();
        
        const menu = document.getElementById('custom-num-menu');
        if (!menu) return;
        
        menu.style.display = 'block';
        
        let x = (e && e.pageX) ? e.pageX : (el ? el.getBoundingClientRect().left + window.scrollX : 200);
        let y = (e && e.pageY) ? e.pageY : (el ? el.getBoundingClientRect().bottom + window.scrollY + 5 : 200);
        
        if (x + 330 > window.innerWidth) {
            x = Math.max(10, window.innerWidth - 340);
        }
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
    };

    // Right-click context menu event listener
    document.addEventListener('contextmenu', function(e) {
        let target = e.target;
        if (!target) return;
        
        let matched = null;
        if (target.id === 'exchange-rate' || target.closest('#exchange-rate') || target.closest('#label-exchange-rate') || (target.textContent && target.textContent.includes('التعادل') && target.closest('div') && target.closest('div').querySelector('#exchange-rate'))) {
            matched = document.getElementById('exchange-rate');
        } else if (target.closest) {
            matched = target.closest('#exchange-rate, #amount-local-hdr, #amount-foreign, .block-bank-amount, .block-cheque-amount, .block-total-amount, .block-sub-amount');
        }
        
        if (matched) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            openNumPropsFor(matched, e);
        }
    }, true);

    // Mousedown right-click listener (button === 2)
    document.addEventListener('mousedown', function(e) {
        if (e.button !== 2) return;
        let target = e.target;
        if (!target) return;
        
        let matched = null;
        if (target.id === 'exchange-rate' || target.closest('#exchange-rate') || target.closest('#label-exchange-rate') || (target.textContent && target.textContent.includes('التعادل') && target.closest('div') && target.closest('div').querySelector('#exchange-rate'))) {
            matched = document.getElementById('exchange-rate');
        } else if (target.closest) {
            matched = target.closest('#exchange-rate, #amount-local-hdr, #amount-foreign, .block-bank-amount, .block-cheque-amount, .block-total-amount, .block-sub-amount');
        }
        
        if (matched) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            openNumPropsFor(matched, e);
        }
    }, true);

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('custom-num-menu');
        if (menu && menu.style.display === 'block' && !menu.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    // Handle raw value tracking
    document.addEventListener('input', function(e) {
        if (e.target.id === 'exchange-rate' || 
            e.target.id === 'amount-local-hdr' || 
            e.target.id === 'amount-foreign' || 
            e.target.classList.contains('block-bank-amount') || 
            e.target.classList.contains('block-cheque-amount') ||
            e.target.classList.contains('block-total-amount') ||
            e.target.classList.contains('block-sub-amount')) {
            e.target.dataset.raw = e.target.value.replace(/,/g, '');
        }
    });

    // Initial formatting on page load
    document.addEventListener('DOMContentLoaded', function() {
        applyNumPropsToAll();
        setTimeout(applyNumPropsToAll, 400);
    });

    // 🔍 محرك التضخيم والتكبير الفوري عند التركيز والكتابة (Dynamic Focus Magnification Engine)
    document.addEventListener('focusin', function(e) {
        const el = e.target;
        if (!el || !el.classList) return;
        
        const isAmount = el.classList.contains('block-bank-amount') || 
                         el.classList.contains('block-cheque-amount') || 
                         el.classList.contains('transfer-amount') || 
                         el.classList.contains('cheque-amount') || 
                         el.classList.contains('block-total-amount') ||
                         el.classList.contains('block-sub-amount');
                         
        const isNumber = el.classList.contains('block-transfer-no-input') || 
                         el.classList.contains('block-cheque-no-input') || 
                         el.classList.contains('transfer-no') || 
                         el.classList.contains('cheque-no');
                         
        if (isAmount || isNumber) {
            el.style.setProperty('font-size', '4.5rem', 'important');
            el.style.setProperty('height', '98px', 'important');
            el.style.setProperty('min-width', '450px', 'important');
            el.style.setProperty('position', 'relative', 'important');
            el.style.setProperty('z-index', '99999', 'important');
            el.style.setProperty('margin-right', '-50px', 'important');
            el.style.setProperty('font-weight', '900', 'important');
            el.style.setProperty('background', 'rgba(15, 23, 42, 0.98)', 'important');
            el.style.setProperty('border-radius', '10px', 'important');
            el.style.setProperty('transition', 'all 0.25s cubic-bezier(0.16, 1, 0.3, 1)', 'important');
            
            if (isAmount) {
                el.style.setProperty('color', '#fbbf24', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#fbbf24', 'important');
                el.style.setProperty('caret-color', '#fbbf24', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
                el.style.setProperty('filter', 'none', 'important');
                el.style.setProperty('-webkit-font-smoothing', 'antialiased', 'important');
                el.style.setProperty('-moz-osx-font-smoothing', 'grayscale', 'important');
                el.style.setProperty('text-rendering', 'geometricPrecision', 'important');
                el.style.setProperty('font-family', "'Consolas', 'Courier New', monospace", 'important');
                el.style.setProperty('border', '2.5px solid #fbbf24', 'important');
                el.style.setProperty('box-shadow', '0 15px 40px rgba(0,0,0,0.9), 0 0 15px rgba(251, 191, 36, 0.35)', 'important');
            } else {
                el.style.setProperty('color', '#38bdf8', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#38bdf8', 'important');
                el.style.setProperty('caret-color', '#38bdf8', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                el.style.setProperty('-webkit-text-stroke', '0px', 'important');
                el.style.setProperty('filter', 'none', 'important');
                el.style.setProperty('-webkit-font-smoothing', 'antialiased', 'important');
                el.style.setProperty('-moz-osx-font-smoothing', 'grayscale', 'important');
                el.style.setProperty('text-rendering', 'geometricPrecision', 'important');
                el.style.setProperty('font-family', "'Consolas', 'Courier New', monospace", 'important');
                el.style.setProperty('border', '2.5px solid #38bdf8', 'important');
                el.style.setProperty('box-shadow', '0 15px 40px rgba(0,0,0,0.9), 0 0 15px rgba(56, 189, 248, 0.35)', 'important');
                el.style.setProperty('letter-spacing', '1.5px', 'important');
            }
            
            if (el.parentElement && el.parentElement.style) {
                el.parentElement.dataset.origZIndex = el.parentElement.style.zIndex || '';
                el.parentElement.dataset.origPosition = el.parentElement.style.position || '';
                el.parentElement.style.zIndex = '99999';
                el.parentElement.style.position = 'relative';
            }
        }
    }, true);

    document.addEventListener('focusout', function(e) {
        const el = e.target;
        if (!el || !el.classList) return;
        
        const isAmount = el.classList.contains('block-bank-amount') || 
                         el.classList.contains('block-cheque-amount') || 
                         el.classList.contains('transfer-amount') || 
                         el.classList.contains('cheque-amount') || 
                         el.classList.contains('block-total-amount') ||
                         el.classList.contains('block-sub-amount');
                         
        const isNumber = el.classList.contains('block-transfer-no-input') || 
                         el.classList.contains('block-cheque-no-input') || 
                         el.classList.contains('transfer-no') || 
                         el.classList.contains('cheque-no');
                         
        if (isAmount || isNumber) {
            el.style.removeProperty('height');
            el.style.removeProperty('min-width');
            el.style.removeProperty('position');
            el.style.removeProperty('z-index');
            el.style.removeProperty('margin-right');
            el.style.removeProperty('background');
            el.style.removeProperty('border');
            el.style.removeProperty('border-radius');
            el.style.removeProperty('box-shadow');
            el.style.removeProperty('color');
            el.style.removeProperty('-webkit-text-fill-color');
            el.style.removeProperty('caret-color');
            el.style.removeProperty('text-shadow');
            el.style.removeProperty('letter-spacing');
            
            if (isAmount) {
                el.style.removeProperty('font-size');
            } else {
                el.style.setProperty('font-size', '2.8rem', 'important');
            }
            
            if (el.parentElement && el.parentElement.style) {
                el.parentElement.style.zIndex = el.parentElement.dataset.origZIndex || '';
                el.parentElement.style.position = el.parentElement.dataset.origPosition || '';
            }
        }
        
        if (isAmount || 
            e.target.id === 'amount-local-hdr' || 
            e.target.id === 'amount-foreign' || 
            e.target.id === 'exchange-rate' ||
            e.target.classList.contains('block-bank-amount') || 
            e.target.classList.contains('block-cheque-amount') ||
            e.target.classList.contains('block-total-amount') ||
            e.target.classList.contains('block-sub-amount')) {
            applyNumPropsToAll();
        }
    }, true);
    
    // Periodically apply styles to newly added rows dynamically
    setInterval(applyNumPropsToAll, 1000);
    </script>
    <!-- Financial OCR Modal & Script -->
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
</body>
</html>











