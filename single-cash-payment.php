<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;
$prefix = 'E' . date('ym');
$maxSeq = 0;
try {
    $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $d = json_decode($row['data'], true);
        $id = isset($d['id']) ? (string)$d['id'] : '';
        if (strpos($id, $prefix) === 0) {
            $seq = (int)substr($id, strlen($prefix));
            if ($seq > $maxSeq) $maxSeq = $seq;
        }
    }
} catch (Exception $e) {
    // fallback
}
$voucher_no = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
$page_title = 'حركة مالية';
$SERVER_SINGLE_PAYMENTS_json = '[]';
try {
    $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
    $stmt->execute([$GLOBAL_TENANT_ID]);
    $arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = json_decode($row['data'], true);
    }
    $SERVER_SINGLE_PAYMENTS_json = json_encode($arr);
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js?v=<?php echo time(); ?>"></script>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حركة مالية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    
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

        /* Crystal Red Container (المستطيل الرئيسي للصرف) */
        .glass-red {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(110, 30, 45, 0.75), rgba(15, 23, 42, 0.85)) !important;
            border: 1px solid rgba(244, 63, 94, 0.45) !important;
            border-right: 5px solid #f43f5e !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(244, 63, 94, 0.35), 0 0 25px rgba(244, 63, 94, 0.15) !important;
        }
        .glass-red:hover {
            background: linear-gradient(135deg, rgba(130, 35, 50, 0.85), rgba(15, 23, 42, 0.92)) !important;
            border-color: rgba(244, 63, 94, 0.8) !important;
            border-right-color: #fb7185 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.6), 0 0 45px rgba(244, 63, 94, 0.6) !important;
        }

        /* Crystal Green Container (مستطيلات الحسابات المستلمة) */
        .glass-green {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(16, 60, 40, 0.85), rgba(15, 23, 42, 0.95)) !important;
            border: 1px solid rgba(39, 174, 96, 0.45) !important;
            border-right: 5px solid #27ae60 !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(39, 174, 96, 0.35), 0 0 25px rgba(39, 174, 96, 0.2) !important;
        }
        .glass-green:hover {
            background: linear-gradient(135deg, rgba(20, 75, 50, 0.9), rgba(15, 23, 42, 0.98)) !important;
            border-color: rgba(39, 174, 96, 0.8) !important;
            border-right-color: #2ecc71 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.6), 0 0 45px rgba(39, 174, 96, 0.6) !important;
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
        .crystal-btn, .voucher-type-btn, .pay-toggle-btn {
            position: relative;
            background: rgba(0, 0, 0, 0.2); /* Darker background, no inner light */
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 4.8px solid var(--tab-border-color, rgba(255, 255, 255, 0.25)) !important;
            border-radius: 14px;
            padding: 10px 18px;
            font-size: 1rem;
            font-weight: 800;
            color: #f8fafc !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            overflow: visible; /* Allows rear bottom glow projection */
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
            z-index: 1;
            font-family: 'Tajawal', sans-serif;
            user-select: none;
            height: 44px; /* Uniform height & width alignment */
            box-sizing: border-box;
        }

        /* Highly Visible Internal Glitter Sparkles Overlay (DISABLED PER USER) */
        .crystal-btn::after, .voucher-type-btn::after, .pay-toggle-btn::after {
            display: none !important;
        }

        .crystal-btn:hover::after, .voucher-type-btn:hover::after, .pay-toggle-btn:hover::after {
            display: none !important;
        }

        /* Rear Bottom Light Ray Beam Projection (Faint per user) */
        .crystal-btn::before, .voucher-type-btn::before, .pay-toggle-btn::before {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 10%;
            width: 80%;
            height: 18px;
            background: var(--tab-glow-color, rgba(56, 189, 248, 0.75));
            border-radius: 50%;
            filter: blur(14px);
            opacity: 0.15; /* Very faint */
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
            z-index: -1;
            animation: rearRayPulse 3s infinite ease-in-out;
        }

        .crystal-btn:hover::before, .voucher-type-btn:hover::before, .pay-toggle-btn:hover::before {
            opacity: 0.25 !important; /* Very faint on hover */
            transform: scaleX(1.4) translateY(5px) !important;
            filter: blur(18px) brightness(1.2) !important;
        }

        /* Icon Styling & Detailed Icon Glow Halo (إضاءة مفصلة خلفية من الأيقونة) */
        .btn-icon, .md-tab-icon {
            font-size: 1.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.6));
            position: relative;
            z-index: 4;
        }

        /* Detailed Icon Rear Glow Ring */
        .btn-icon::after, .md-tab-icon::after {
            display: none !important;
        }

        /* Hover Scale + Light Glow + Text Contrast Protection */
        .crystal-btn:hover, .voucher-type-btn:hover, .pay-toggle-btn:hover {
            transform: translateY(-5px) scale(1.08) !important;
            color: #ffffff !important;
            text-shadow: 0 0 8px rgba(0,0,0,0.8) !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.6), 0 0 12px var(--tab-glow-color, rgba(56,189,248,0.2)) !important;
            background: rgba(0,0,0,0.3) !important; /* No inner light */
            z-index: 10;
        }

        .crystal-btn:hover .btn-icon, .voucher-type-btn:hover .btn-icon, .pay-toggle-btn:hover .btn-icon {
            transform: scale(1.38) rotate(8deg);
            filter: drop-shadow(0 0 5px rgba(255,255,255,0.3));
        }

        .crystal-btn:hover .btn-icon::after, .voucher-type-btn:hover .btn-icon::after, .pay-toggle-btn:hover .btn-icon::after {
            display: none !important;
        }

        /* ═══ Voucher Type Buttons (صرف مجمع 📤 vs استلام مجمع 📥) ═══ */
        .voucher-type-btn.active-payment, #btn-payment.active-payment {
            --tab-border-color: #f43f5e;
            --tab-glow-color: rgba(244, 63, 94, 0.7);
            --tab-hover-bg: linear-gradient(135deg, #f43f5e, #be123c);
            background: linear-gradient(135deg, #e11d48, #9f1239) !important;
            border-color: #f43f5e !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 30px rgba(225, 29, 72, 0.65), 0 0 25px rgba(225, 29, 72, 0.4), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        .voucher-type-btn.active-receipt, #btn-receipt.active-receipt {
            --tab-border-color: #10b981;
            --tab-glow-color: rgba(16, 185, 129, 0.7);
            --tab-hover-bg: linear-gradient(135deg, #10b981, #047857);
            background: linear-gradient(135deg, #059669, #064e3b) !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.65), 0 0 25px rgba(16, 185, 129, 0.4), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05) !important;
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

        .pay-toggle-btn.block-btn-bank {
            --tab-border-color: #fbbf24;
            --tab-glow-color: rgba(251, 191, 36, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(217, 119, 6, 0.4), rgba(251, 191, 36, 0.6));
            --tab-active-bg: linear-gradient(135deg, #d97706, #fbbf24);
        }
        .pay-toggle-btn.block-btn-cheque {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(192, 132, 252, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(124, 58, 237, 0.4), rgba(192, 132, 252, 0.6));
            --tab-active-bg: linear-gradient(135deg, #7c3aed, #c084fc);
        }

        .pay-toggle-btn.block-btn-cheque {
            --tab-border-color: rgba(155, 89, 182, 0.45);
            --tab-glow-color: rgba(155, 89, 182, 0.65);
            --tab-hover-bg: linear-gradient(135deg, rgba(155, 89, 182, 0.35), rgba(142, 68, 173, 0.5));
        }
        .pay-toggle-btn.active.block-btn-cheque {
            --tab-border-color: #9b59b6;
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.45), rgba(142, 68, 173, 0.7)) !important;
            border-color: #9b59b6 !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(155, 89, 182, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 25px rgba(155, 89, 182, 0.5), 0 0 20px rgba(155, 89, 182, 0.3), inset 0 1px 2px rgba(255,255,255,0.6) !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ═══ Footer Action Buttons ═══ */
        .btn-save-master {
            --tab-border-color: #34d399;
            --tab-glow-color: rgba(16, 185, 129, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #10b981, #059669);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.45), rgba(4, 120, 87, 0.75)) !important;
            border-color: #34d399 !important;
            color: #ffffff !important;
            text-shadow: 0 0 12px rgba(52, 211, 153, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-new {
            --tab-border-color: #38bdf8;
            --tab-glow-color: rgba(56, 189, 248, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #0284c7, #0369a1);
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.3), rgba(3, 105, 161, 0.6)) !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-print {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(168, 85, 247, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #9333ea, #7e22ce);
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.6)) !important;
            border-color: #c084fc !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(192, 132, 252, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-print-preview {
            --tab-border-color: #22d3ee;
            --tab-glow-color: rgba(6, 182, 212, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #0891b2, #0e7490);
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.3), rgba(14, 116, 144, 0.6)) !important;
            border-color: #22d3ee !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(34, 211, 238, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-delete {
            --tab-border-color: #fb7185;
            --tab-glow-color: rgba(244, 63, 94, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #e11d48, #be123c);
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.3), rgba(190, 18, 60, 0.6)) !important;
            border-color: #fb7185 !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(251, 113, 133, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-exit {
            --tab-border-color: #94a3b8;
            --tab-glow-color: rgba(148, 163, 184, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #475569, #334155);
            background: linear-gradient(135deg, rgba(100, 116, 139, 0.3), rgba(51, 65, 85, 0.6)) !important;
            border-/* color removed */
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(148, 163, 184, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }

        /* Luminous Glowing Borders on Hover for Account Blocks & Details Panels */
        .glass-container:hover, .account-block:hover {
            border-color: rgba(255, 255, 255, 0.45) !important;
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.7),
                0 0 35px var(--block-glow-color, rgba(56, 189, 248, 0.4)),
                inset 0 1px 3px rgba(255, 255, 255, 0.5) !important;
        }

        .glass-red:hover {
            --block-glow-color: rgba(212, 122, 126, 0.5);
            border-color: rgba(212, 122, 126, 0.75) !important;
        }
        .glass-green:hover {
            --block-glow-color: rgba(39, 174, 96, 0.5);
            border-color: rgba(39, 174, 96, 0.75) !important;
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

        /* Voucher Type Toggle Buttons (صرف/استلام من مجموعة) - PREMIUM DESIGN */
        .voucher-type-btn {
            position: relative;
            min-width: 185px !important;
            height: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: rgba(255, 255, 255, 0.8) !important;
            background: rgba(15, 23, 42, 0.4) !important;
            border: 2px solid transparent !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important;
            transform: translateY(0) scale(0.97) !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            backdrop-filter: blur(8px);
        }

        .voucher-type-btn.active-payment, .voucher-type-btn[id="btn-payment"] {
            --btn-color: #f43f5e;
            --btn-glow: rgba(244, 63, 94, 0.6);
            --btn-bg-glow: rgba(244, 63, 94, 0.15);
        }
        .voucher-type-btn.active-receipt, .voucher-type-btn[id="btn-receipt"] {
            --btn-color: #3b82f6;
            --btn-glow: rgba(59, 130, 246, 0.6);
            --btn-bg-glow: rgba(59, 130, 246, 0.15);
        }

        /* Default state: colored border, slight underlying glow */
        .voucher-type-btn {
            border-color: var(--btn-color) !important;
            box-shadow: 0 4px 15px var(--btn-bg-glow) !important;
        }

        /* Hover state: intense border glow */
        .voucher-type-btn:hover {
            color: #ffffff !important;
            transform: translateY(-2px) scale(1) !important;
            background: var(--btn-bg-glow) !important;
            box-shadow: 0 0 20px var(--btn-glow), 0 5px 25px var(--btn-bg-glow) !important;
            text-shadow: 0 0 8px var(--btn-color);
        }

        /* Active State */
        .voucher-type-btn.active-payment, .voucher-type-btn.active-receipt {
            color: #ffffff !important;
            background: var(--btn-bg-glow) !important;
            border: 2px solid var(--btn-color) !important;
            box-shadow: 0 0 25px var(--btn-glow), inset 0 0 15px var(--btn-bg-glow), 0 8px 30px var(--btn-bg-glow) !important;
            transform: translateY(-3px) scale(1.05) !important;
            text-shadow: 0 0 8px var(--btn-color);
            animation: borderPulse 2s infinite alternate ease-in-out;
        }

        @keyframes borderPulse {
            0% { box-shadow: 0 0 15px var(--btn-glow), inset 0 0 10px var(--btn-bg-glow), 0 4px 20px var(--btn-bg-glow); }
            100% { box-shadow: 0 0 35px var(--btn-glow), inset 0 0 25px var(--btn-bg-glow), 0 8px 40px var(--btn-bg-glow); }
        }

        .voucher-type-btn span.btn-icon i {
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }
        .voucher-type-btn.active-payment span.btn-icon i, .voucher-type-btn.active-receipt span.btn-icon i {
            filter: drop-shadow(0 0 12px var(--btn-color));
            transform: scale(1.15);
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

        /* ═══ High legibility white background for Amount/Value fields ═══ */
        .transfer-amount, .cheque-amount, .block-sub-amount, .block-cash-amount, .block-bank-amount, .block-cheque-amount, .block-total-amount, .total-val, .val, .value-field, .multi-input[placeholder*="المبلغ"], .multi-input[placeholder*="القيمة"] {
            background: rgba(15, 23, 42, 0.45) !important;
            color: #ffffff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.2) !important;
        }
        .transfer-amount:focus, .cheque-amount:focus, .block-sub-amount:focus, .block-total-amount:focus, .multi-input[placeholder*="المبلغ"]:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25) !important;
        }

        /* Large Totals & Rates */
        /* Large Totals & Rates */
        #exchange-rate {
            font-size: 1.8rem !important;
            font-weight: 900 !important;
        }
        #amount-local-hdr, #amount-foreign {
            font-size: 2.6rem !important;
            font-weight: 900 !important;
            color: #ef4444 !important;
            background-color: #ffffff !important;
            text-align: center !important;
        }
        
        /* Normal State for Amounts */
        input.block-total-amount, input.block-bank-amount, input.block-cheque-amount, input.transfer-amount, input.cheque-amount {
            font-size: 2.4rem !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            transition: font-size 0.2s ease, color 0.2s ease, background 0.2s ease !important;
        }
        
        /* Focus State for Amounts */
        input.block-total-amount:focus, input.block-bank-amount:focus, input.block-cheque-amount:focus, input.transfer-amount:focus, input.cheque-amount:focus {
            font-size: 4.0rem !important;
            color: #fbbf24 !important; /* Gold */
            background: rgba(15, 23, 42, 0.8) !important;
        }
        
        /* Normal State for Numbers */
        input.block-transfer-no-input, input.block-cheque-no-input, input.transfer-no, input.cheque-no {
            font-size: 2.0rem !important;
            font-weight: 900 !important;
            letter-spacing: 1px !important;
            color: #ffffff !important;
            transition: font-size 0.2s ease, color 0.2s ease, background 0.2s ease !important;
        }
        
        /* Focus State for Numbers */
        input.block-transfer-no-input:focus, input.block-cheque-no-input:focus, input.transfer-no:focus, input.cheque-no:focus {
            font-size: 3.5rem !important;
            color: #22d3ee !important; /* Cyan */
            background: rgba(15, 23, 42, 0.8) !important;
        }

        /* Larger Bank Name Font */
        input.block-bank-name-input, input.block-cheque-bank-input {
            font-size: 1.35rem !important;
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
        /* Custom Number Properties Menu */
        #num-props-menu { display: none; position: absolute; z-index: 9999; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); border-radius: 12px; padding: 10px; width: 260px; color: #cbd5e1; font-family: 'Inter', sans-serif; direction: rtl; }
        #num-props-menu .props-header { font-weight: bold; color: #38bdf8; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 1.1rem; }
        #num-props-menu .props-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 5px; cursor: pointer; border-radius: 6px; transition: background 0.2s; font-size: 0.95rem; }
        #num-props-menu .props-item:hover { background: rgba(255, 255, 255, 0.1); }
        #num-props-menu button.prop-btn { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.5); color: #60a5fa; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; }
        #num-props-menu button.prop-btn:hover { background: rgba(59, 130, 246, 0.4); color: #fff; }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" style="width: 100%; max-width: 100%; margin: 0 auto; padding: 5px 40px 100px 40px;">


        <!-- NEW ADVANCED HEADER -->
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- (Moved SUB HEADER BAR inside main form) -->
        <div class="split-layout-wrapper" id="main-layout-wrapper" style="display: block; margin-top: 15px; width: 100%;">
            <!-- Main Content area (Full width) -->
            <div class="main-panel" id="main-form-panel" style="padding: 10px; margin-bottom: 15px; width: 100%;">

                <div id="edit-warning-banner" style="display: none; background: rgba(220, 38, 38, 0.15); border: 2px solid #ef4444; color: #f87171; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2); animation: pulse-glow 2s infinite;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">⚠️</span>
                        <div>
                            <strong style="font-size: 1.1rem; display: block; margin-bottom: 4px;">تنبيه هام: أنت تقوم بعرض سند قديم تم حفظه مسبقاً!</strong>
                            <span style="font-size: 0.95rem;">أي تغييرات تقوم بها سيتم تطبيقها وتعديل هذا السند. هل تريد التعديل حقاً؟</span>
                        </div>
                    </div>
                </div>

                <!-- Edit Warning Modal (شاشة تحذيرية للتعديل) -->
                <div id="edit-warning-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
                    <div style="background: linear-gradient(135deg, #7f1d1d, #991b1b); border: 2px solid #f87171; border-radius: 20px; padding: 40px; width: 90%; max-width: 600px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 40px rgba(239,68,68,0.3); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <div style="font-size: 5rem; margin-bottom: 20px; animation: pulse-glow 2s infinite;">⚠️</div>
                        <h1 style="color: #ffffff; font-size: 2.2rem; font-weight: 900; margin-bottom: 15px; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">تنبيه: أنت تقوم بتعديل سند قديم!</h1>
                        <p style="color: #fca5a5; font-size: 1.3rem; font-weight: 700; margin-bottom: 30px; line-height: 1.6;">هذا السند تم حفظه مسبقاً. أي تغييرات تقوم بها الآن ستؤدي إلى تعديل البيانات الأصلية للسند.<br><br><span style="color:#fff;">هل تريد التعديل حقاً؟</span></p>
                        <div style="display: flex; gap: 20px; justify-content: center;">
                            <button type="button" onclick="confirmEditOldVoucher()" class="btn" style="background: #ef4444; color: white; font-size: 1.3rem; font-weight: 900; padding: 15px 30px; border: 2px solid #fca5a5; border-radius: 12px; cursor: pointer; box-shadow: 0 10px 20px rgba(239,68,68,0.4);">نعم، أريد التعديل</button>
                            <button type="button" onclick="cancelEditOldVoucher()" class="btn" style="background: rgba(255,255,255,0.1); color: white; font-size: 1.3rem; font-weight: 900; padding: 15px 30px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; cursor: pointer;">لا، تراجع</button>
                        </div>
                    </div>
                </div>

                <div class="interactive-columns" style="display: block;">
                    <!-- ONE SINGLE RECTANGLE -->
                    <div class="focus-zone single-unified-form" style="padding: 25px; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        
                        <!-- Top Row: Date, Balances, Voucher Number -->
                        <div class="sub-header-bar" style="margin-top: 0; margin-bottom: 25px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);">
                            <!-- Title & Date -->
                            <div class="header-group group-primary">
                                <div class="crystal-pill date-crystal hover-scale">
                                    <div class="icon-box bg-gradient-blue">📅</div>
                                    <input type="date" id="hdr-date" class="date-input" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            
                            <!-- Balances -->
                            <div class="header-group group-balances">
                                <div class="crystal-pill bal-crystal hover-scale">
                                    <div class="bal-card cash-card">
                                        <span class="bal-icon">💵</span>
                                        <div class="bal-info">
                                            <span class="bal-label">نقدية</span>
                                            <strong id="bal-checks" class="bal-val">50</strong>
                                        </div>
                                    </div>
                                    <div class="bal-divider"></div>
                                    <div class="bal-card check-card">
                                        <span class="bal-icon">🧾</span>
                                        <div class="bal-info">
                                            <span class="bal-label">شيكات</span>
                                            <strong id="bal-debt" class="bal-val">70</strong>
                                        </div>
                                    </div>
                                    <div class="bal-divider"></div>
                                    <div class="bal-card loan-card">
                                        <span class="bal-icon">💼</span>
                                        <div class="bal-info">
                                            <span class="bal-label">عهد</span>
                                            <strong id="bal-loan" class="bal-val">120</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Next / Prev -->
                            <div class="header-group group-nav">
                                <div class="crystal-pill nav-crystal hover-scale">
                                    <button class="crystal-btn-sm btn-blue-glass crystal-btn-icon-only prev-btn" onclick="loadPreviousVoucher()" title="السند السابق">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </button>
                                    <div class="nav-badge">
                                        <span class="badge-label">رقم السند</span>
                                        <span id="voucher-number" class="badge-val"><?php echo $voucher_no; ?></span>
                                    </div>
                                    <button class="crystal-btn-sm btn-blue-glass crystal-btn-icon-only next-btn" onclick="loadNextVoucher()" title="السند التالي">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Row 1: Currency | Amount | Rate | Foreign -->
                        <div style="display: grid; grid-template-columns: 1.0fr 1.7fr 1.3fr 1.5fr; gap: 15px; margin-bottom: 25px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#fbbf24 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">💱 العملة</label>
                                <select class="form-input system-currency-select input-glow-focus hover-glow" id="currency" data-next="exchange-rate" onchange="updateCurrency(); document.getElementById('exchange-rate').focus(); document.getElementById('exchange-rate').select();" style="height: 55px; border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%; font-size: 1.3rem !important; font-weight: 800; padding-right: 5px; padding-left: 5px;">
                                    <option value="SDG">جنيه (SDG)</option>
                                    <option value="USD">دولار (USD)</option>
                                    <option value="AED">درهم (AED)</option>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; position: relative;">
                                <label id="label-box-1" class="label" style="display:flex; align-items:center; gap:5px; color:#cbd5e1 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;"><span class="vivid-icon vivid-green" style="width:24px; height:24px; font-size:1.1rem;">💰</span> المبلغ (محلي)</label>
                                <div style="position: relative; flex-grow: 1; min-width: 0; width: 100%;">
                                    <span id="amount-currency-symbol" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: var(--primary-color, #3b82f6); color: white; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.9rem; pointer-events: none; z-index: 5; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">SDG</span>
                                    <input type="text" class="form-input amount hover-scale input-glow-focus hover-glow" .2rem !important; font-weight: 900; text-align: center; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 2px; direction: ltr; border-radius: 10px; width: 100%; transition: font-size 0.2s ease, letter-spacing 0.2s ease; padding-left: 60px;">
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#fbbf24 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">⚖️ سعر الصرف</label>
                                <input type="number" class="form-input" id="exchange-rate" value="1" placeholder="الصرف" readonly style="flex: 1.5; text-align: center; cursor:pointer; background:rgba(16,185,129,0.1); color:#10b981; font-weight:900; height: 55px; border-radius: 10px; font-size: 0.95rem !important; letter-spacing: 1px; min-width: 110px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=Math.round(r); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; position: relative;">
                                <label id="label-box-2" class="label" style="display:flex; align-items:center; gap:5px; color:#cbd5e1 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">💵 القيمة (أجنبي)</label>
                                <div style="position: relative; flex-grow: 1; min-width: 0; width: 100%;">
                                    <span id="amount-foreign-currency-symbol" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.9rem; pointer-events: none; z-index: 5; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);">USD</span>
                                    <input type="text" class="form-input amount input-glow-focus hover-glow" .2rem !important; text-align: center; background: #fafafa !important; color: #ef4444 !important; font-weight: 900; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 2px; direction: ltr; border-radius: 10px; width: 100%; transition: font-size 0.2s ease, letter-spacing 0.2s ease; padding-left: 60px; cursor: not-allowed; border: 1px solid #fca5a5;">
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: من حساب | إلى حساب -->
                        <div class="from-to-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 70px; margin-bottom: 25px; position: relative;">
                            
                            <!-- Crystalline Wave Flow Connection -->
                            <svg class="money-flow-svg" width="100%" height="40" viewBox="0 0 1000 40" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="pathColorGradient" x1="100%" y1="0%" x2="0%" y2="0%">
                                        <stop offset="0%" stop-color="#ff0055" />
                                        <stop offset="50%" stop-color="#ff0055" />
                                        <stop offset="100%" stop-color="#ff0055" />
                                    </linearGradient>
                                </defs>
                                <!-- Static subtle path -->
                                <path class="flow-path-bg" d="M 720 20 C 630 5, 570 35, 500 20 C 430 5, 370 35, 280 20" fill="none" stroke="rgba(255, 255, 255, 0.05)" stroke-width="2.5" />
                                <!-- Flowing pulses -->
                                <path class="flow-path-wave-1" d="M 720 20 C 630 5, 570 35, 500 20 C 430 5, 370 35, 280 20" fill="none" stroke="url(#pathColorGradient)" stroke-width="3.5" stroke-dasharray="100 900" stroke-dashoffset="1000" />
                                <path class="flow-path-wave-2" d="M 720 20 C 630 5, 570 35, 500 20 C 430 5, 370 35, 280 20" fill="none" stroke="url(#pathColorGradient)" stroke-width="2.2" stroke-dasharray="50 950" stroke-dashoffset="1000" />
                            </svg>

                            <!-- Epic Animated Transfer Arrow (Centered Absolute) -->
                            <div class="epic-arrow-container" onclick="swapAccounts()" role="button" aria-label="عكس الحسابات" title="عكس حساب الصرف والاستلام">
                                <svg class="transfer-arrow-anim" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="red" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline class="chev chev-3" points="20 18 14 12 20 6"></polyline>
                                    <polyline class="chev chev-2" points="13 18 7 12 13 6"></polyline>
                                    <polyline class="chev chev-1" points="6 18 0 12 6 6"></polyline>
                                </svg>
                            </div>

                            <!-- من حساب (Rose Accent) -->
                            <div style="background: rgba(244, 63, 94, 0.08); padding: 8px 15px; border-radius: 12px; border: 1px solid rgba(244, 63, 94, 0.4); display: flex; align-items: center; gap: 10px;" data-swap="1">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#f43f5e !important; margin-bottom:0; font-weight:800; white-space:nowrap; flex-shrink:0; font-size: 1.5rem !important;">
                                    <span style="display:flex; align-items:center; justify-content:center; width: 24px; height: 24px; background: rgba(244, 63, 94, 0.15); border-radius: 50%;">
                                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                    </span>
                                    من حساب <span style="color:#ffffff !important;">(دائن)</span>
                                </label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="from-account">
                                    <input type="text" autocomplete="off" class="form-input input-glow-focus hover-glow" .4rem !important; border: 1px solid rgba(244, 63, 94, 0.5); background: rgba(0,0,0,0.3); color: #fbbf24 !important;">
                                    <button type="button" id="from-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('from-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                    <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none; margin-top: 5px;"></div>
                                </div>
                            </div>
                            <!-- إلى حساب (Green) -->
                            <div class="glass-container glass-green" style="position: relative; padding: 15px; border-radius: 12px; transition: all 0.3s ease;" data-swap="1">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#10b981 !important; margin-bottom:0; font-weight:800; white-space:nowrap; flex-shrink:0; font-size: 1.5rem !important;">
                                    <span style="display:flex; align-items:center; justify-content:center; width: 24px; height: 24px; background: rgba(16, 185, 129, 0.15); border-radius: 50%;">
                                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </span>
                                    إلى حساب <span style="color:#ffffff !important;">(استلام)</span>
                                </label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="to-account">
                                    <input type="text" autocomplete="off" class="form-input input-glow-focus hover-glow" .4rem !important; border: 1px solid rgba(16, 185, 129, 0.5); background: rgba(0,0,0,0.3); color: #fbbf24 !important;">
                                    <button type="button" id="to-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('to-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                    <div class="custom-dropdown-list" id="to-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none; margin-top: 5px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: المستفيد | مركز التكلفة -->
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; background: rgba(255,255,255,0.02); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <!-- Beneficiary (Right) -->
                            <div style="display: flex; align-items: center; gap: 10px; flex-grow: 1; min-width: 250px;">
                                <label class="label" style="color:#fbbf24 !important; font-weight:800; display:flex; align-items:center; gap:5px; margin-bottom:0; white-space:nowrap;">
                                    <span class="vivid-icon vivid-yellow" style="width:24px; height:24px; margin-left:0px;"><span class="icon-pulse" style="font-size:0.9rem;">👨‍💼</span></span> المستفيد
                                </label>
                                <input type="text" class="form-input input-glow-focus hover-glow" id="beneficiary-name" data-next="cost-center" placeholder="اسم المستفيد (اختياري)..." style="height: 48px; border-radius: 10px; border-color: rgba(251, 191, 36, 0.4); flex-grow: 1;">
                            </div>

                            <!-- Cost Center (Left) -->
                            <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0; min-width: 200px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; margin-bottom:0; white-space:nowrap;">
                                    <span style="font-size:1.1rem; opacity:0.8;">📊</span> مركز التكلفة
                                </label>
                                <select class="form-input" id="cost-center" data-next="to-statement" style="height: 48px; border-radius: 10px; flex-grow: 1; min-width: 150px;">
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                        </div>

                        <!-- Row 4: البيان -->
                        <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.02); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; flex-grow: 1; gap: 10px;">
                                <input type="text" class="form-input input-glow-focus hover-glow" id="to-statement" data-next="btn-save" placeholder="اكتب مبرر أو تفاصيل الدفع (البيان)..." style="height: 48px; border-radius: 10px; flex-grow: 1;">
                                <button type="button" onclick="autoCategorize()" id="btn-auto-categorize" style="background: linear-gradient(135deg, #c084fc, #9333ea); color: white; border: none; border-radius: 10px; padding: 0 15px; height: 48px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: bold; white-space: nowrap; box-shadow: 0 4px 15px rgba(147, 51, 234, 0.4); transition: transform 0.2s;" title="توجيه محاسبي ذكي">
                                    <span style="font-size: 1.2rem;">🪄</span> <span class="btn-text">توقع الحساب</span>
                                </button>
                            </div>
                            <input type="hidden" id="from-statement"> <!-- Hidden to satisfy JS -->
                        </div>

                        <!-- Hidden radios to prevent legacy JS checks breaking -->
                        <div style="display: none;">
                            <input type="radio" name="payment_method" value="cash" id="radio-cash">
                            <input type="radio" name="payment_method" value="check" id="radio-check">
                            <input type="radio" name="payment_method" value="mobile" id="radio-mobile" checked>
                            <input type="radio" name="payment_method" value="credit" id="radio-credit">
                            <input type="radio" name="payment_method" value="hybrid" id="radio-hybrid">
                        </div>

                        <!-- Unified Payment details panel styled like Sales/Purchase -->
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

        /* Crystal Red Container (المستطيل الرئيسي للصرف) */
        .glass-red {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(110, 30, 45, 0.75), rgba(15, 23, 42, 0.85)) !important;
            border: 1px solid rgba(244, 63, 94, 0.45) !important;
            border-right: 5px solid #f43f5e !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(244, 63, 94, 0.35), 0 0 25px rgba(244, 63, 94, 0.15) !important;
        }
        .glass-red:hover {
            background: linear-gradient(135deg, rgba(130, 35, 50, 0.85), rgba(15, 23, 42, 0.92)) !important;
            border-color: rgba(244, 63, 94, 0.8) !important;
            border-right-color: #fb7185 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.6), 0 0 45px rgba(244, 63, 94, 0.6) !important;
        }

        /* Crystal Green Container (مستطيلات الحسابات المستلمة) */
        .glass-green {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, rgba(16, 60, 40, 0.85), rgba(15, 23, 42, 0.95)) !important;
            border: 1px solid rgba(39, 174, 96, 0.45) !important;
            border-right: 5px solid #27ae60 !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(39, 174, 96, 0.35), 0 0 25px rgba(39, 174, 96, 0.2) !important;
        }
        .glass-green:hover {
            background: linear-gradient(135deg, rgba(20, 75, 50, 0.9), rgba(15, 23, 42, 0.98)) !important;
            border-color: rgba(39, 174, 96, 0.8) !important;
            border-right-color: #2ecc71 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), inset 0 1px 2px rgba(255, 255, 255, 0.6), 0 0 45px rgba(39, 174, 96, 0.6) !important;
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
        .crystal-btn, .voucher-type-btn, .pay-toggle-btn {
            position: relative;
            background: rgba(0, 0, 0, 0.2); /* Darker background, no inner light */
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 4.8px solid var(--tab-border-color, rgba(255, 255, 255, 0.25)) !important;
            border-radius: 14px;
            padding: 10px 18px;
            font-size: 1rem;
            font-weight: 800;
            color: #f8fafc !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            overflow: visible; /* Allows rear bottom glow projection */
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
            z-index: 1;
            font-family: 'Tajawal', sans-serif;
            user-select: none;
            height: 44px; /* Uniform height & width alignment */
            box-sizing: border-box;
        }

        /* Highly Visible Internal Glitter Sparkles Overlay (DISABLED PER USER) */
        .crystal-btn::after, .voucher-type-btn::after, .pay-toggle-btn::after {
            display: none !important;
        }

        .crystal-btn:hover::after, .voucher-type-btn:hover::after, .pay-toggle-btn:hover::after {
            display: none !important;
        }

        /* Rear Bottom Light Ray Beam Projection (Faint per user) */
        .crystal-btn::before, .voucher-type-btn::before, .pay-toggle-btn::before {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 10%;
            width: 80%;
            height: 18px;
            background: var(--tab-glow-color, rgba(56, 189, 248, 0.75));
            border-radius: 50%;
            filter: blur(14px);
            opacity: 0.15; /* Very faint */
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
            z-index: -1;
            animation: rearRayPulse 3s infinite ease-in-out;
        }

        .crystal-btn:hover::before, .voucher-type-btn:hover::before, .pay-toggle-btn:hover::before {
            opacity: 0.25 !important; /* Very faint on hover */
            transform: scaleX(1.4) translateY(5px) !important;
            filter: blur(18px) brightness(1.2) !important;
        }

        /* Icon Styling & Detailed Icon Glow Halo (إضاءة مفصلة خلفية من الأيقونة) */
        .btn-icon, .md-tab-icon {
            font-size: 1.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.6));
            position: relative;
            z-index: 4;
        }

        /* Detailed Icon Rear Glow Ring */
        .btn-icon::after, .md-tab-icon::after {
            display: none !important;
        }

        /* Hover Scale + Light Glow + Text Contrast Protection */
        .crystal-btn:hover, .voucher-type-btn:hover, .pay-toggle-btn:hover {
            transform: translateY(-5px) scale(1.08) !important;
            color: #ffffff !important;
            text-shadow: 0 0 8px rgba(0,0,0,0.8) !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.6), 0 0 12px var(--tab-glow-color, rgba(56,189,248,0.2)) !important;
            background: rgba(0,0,0,0.3) !important; /* No inner light */
            z-index: 10;
        }

        .crystal-btn:hover .btn-icon, .voucher-type-btn:hover .btn-icon, .pay-toggle-btn:hover .btn-icon {
            transform: scale(1.38) rotate(8deg);
            filter: drop-shadow(0 0 5px rgba(255,255,255,0.3));
        }

        .crystal-btn:hover .btn-icon::after, .voucher-type-btn:hover .btn-icon::after, .pay-toggle-btn:hover .btn-icon::after {
            display: none !important;
        }

        /* ═══ Voucher Type Buttons (صرف مجمع 📤 vs استلام مجمع 📥) ═══ */
        .voucher-type-btn.active-payment, #btn-payment.active-payment {
            --tab-border-color: #f43f5e;
            --tab-glow-color: rgba(244, 63, 94, 0.7);
            --tab-hover-bg: linear-gradient(135deg, #f43f5e, #be123c);
            background: linear-gradient(135deg, #e11d48, #9f1239) !important;
            border-color: #f43f5e !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 30px rgba(225, 29, 72, 0.65), 0 0 25px rgba(225, 29, 72, 0.4), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        .voucher-type-btn.active-receipt, #btn-receipt.active-receipt {
            --tab-border-color: #10b981;
            --tab-glow-color: rgba(16, 185, 129, 0.7);
            --tab-hover-bg: linear-gradient(135deg, #10b981, #047857);
            background: linear-gradient(135deg, #059669, #064e3b) !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.65), 0 0 25px rgba(16, 185, 129, 0.4), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05) !important;
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

        .pay-toggle-btn.block-btn-bank {
            --tab-border-color: #fbbf24;
            --tab-glow-color: rgba(251, 191, 36, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(217, 119, 6, 0.4), rgba(251, 191, 36, 0.6));
            --tab-active-bg: linear-gradient(135deg, #d97706, #fbbf24);
        }
        .pay-toggle-btn.block-btn-cheque {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(192, 132, 252, 0.55);
            --tab-hover-bg: linear-gradient(135deg, rgba(124, 58, 237, 0.4), rgba(192, 132, 252, 0.6));
            --tab-active-bg: linear-gradient(135deg, #7c3aed, #c084fc);
        }

        .pay-toggle-btn.block-btn-cheque {
            --tab-border-color: rgba(155, 89, 182, 0.45);
            --tab-glow-color: rgba(155, 89, 182, 0.65);
            --tab-hover-bg: linear-gradient(135deg, rgba(155, 89, 182, 0.35), rgba(142, 68, 173, 0.5));
        }
        .pay-toggle-btn.active.block-btn-cheque {
            --tab-border-color: #9b59b6;
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.45), rgba(142, 68, 173, 0.7)) !important;
            border-color: #9b59b6 !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(155, 89, 182, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 25px rgba(155, 89, 182, 0.5), 0 0 20px rgba(155, 89, 182, 0.3), inset 0 1px 2px rgba(255,255,255,0.6) !important;
            transform: translateY(-3px) scale(1.05) !important;
        }

        /* ═══ Footer Action Buttons ═══ */
        .btn-save-master {
            --tab-border-color: #34d399;
            --tab-glow-color: rgba(16, 185, 129, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #10b981, #059669);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.45), rgba(4, 120, 87, 0.75)) !important;
            border-color: #34d399 !important;
            color: #ffffff !important;
            text-shadow: 0 0 12px rgba(52, 211, 153, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-new {
            --tab-border-color: #38bdf8;
            --tab-glow-color: rgba(56, 189, 248, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #0284c7, #0369a1);
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.3), rgba(3, 105, 161, 0.6)) !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-print {
            --tab-border-color: #c084fc;
            --tab-glow-color: rgba(168, 85, 247, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #9333ea, #7e22ce);
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(126, 34, 206, 0.6)) !important;
            border-color: #c084fc !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(192, 132, 252, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-print-preview {
            --tab-border-color: #22d3ee;
            --tab-glow-color: rgba(6, 182, 212, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #0891b2, #0e7490);
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.3), rgba(14, 116, 144, 0.6)) !important;
            border-color: #22d3ee !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(34, 211, 238, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-delete {
            --tab-border-color: #fb7185;
            --tab-glow-color: rgba(244, 63, 94, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #e11d48, #be123c);
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.3), rgba(190, 18, 60, 0.6)) !important;
            border-color: #fb7185 !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(251, 113, 133, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }
        .btn-exit {
            --tab-border-color: #94a3b8;
            --tab-glow-color: rgba(148, 163, 184, 0.75);
            --tab-hover-bg: linear-gradient(135deg, #475569, #334155);
            background: linear-gradient(135deg, rgba(100, 116, 139, 0.3), rgba(51, 65, 85, 0.6)) !important;
            border-/* color removed */
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(148, 163, 184, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
        }

        /* Luminous Glowing Borders on Hover for Account Blocks & Details Panels */
        .glass-container:hover, .account-block:hover {
            border-color: rgba(255, 255, 255, 0.45) !important;
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.7),
                0 0 35px var(--block-glow-color, rgba(56, 189, 248, 0.4)),
                inset 0 1px 3px rgba(255, 255, 255, 0.5) !important;
        }

        .glass-red:hover {
            --block-glow-color: rgba(212, 122, 126, 0.5);
            border-color: rgba(212, 122, 126, 0.75) !important;
        }
        .glass-green:hover {
            --block-glow-color: rgba(39, 174, 96, 0.5);
            border-color: rgba(39, 174, 96, 0.75) !important;
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

        /* Voucher Type Toggle Buttons (صرف/استلام من مجموعة) - PREMIUM DESIGN */
        .voucher-type-btn {
            position: relative;
            min-width: 185px !important;
            height: 50px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: rgba(255, 255, 255, 0.8) !important;
            background: rgba(15, 23, 42, 0.4) !important;
            border: 2px solid transparent !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important;
            transform: translateY(0) scale(0.97) !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            backdrop-filter: blur(8px);
        }

        .voucher-type-btn.active-payment, .voucher-type-btn[id="btn-payment"] {
            --btn-color: #f43f5e;
            --btn-glow: rgba(244, 63, 94, 0.6);
            --btn-bg-glow: rgba(244, 63, 94, 0.15);
        }
        .voucher-type-btn.active-receipt, .voucher-type-btn[id="btn-receipt"] {
            --btn-color: #3b82f6;
            --btn-glow: rgba(59, 130, 246, 0.6);
            --btn-bg-glow: rgba(59, 130, 246, 0.15);
        }

        /* Default state: colored border, slight underlying glow */
        .voucher-type-btn {
            border-color: var(--btn-color) !important;
            box-shadow: 0 4px 15px var(--btn-bg-glow) !important;
        }

        /* Hover state: intense border glow */
        .voucher-type-btn:hover {
            color: #ffffff !important;
            transform: translateY(-2px) scale(1) !important;
            background: var(--btn-bg-glow) !important;
            box-shadow: 0 0 20px var(--btn-glow), 0 5px 25px var(--btn-bg-glow) !important;
            text-shadow: 0 0 8px var(--btn-color);
        }

        /* Active State */
        .voucher-type-btn.active-payment, .voucher-type-btn.active-receipt {
            color: #ffffff !important;
            background: var(--btn-bg-glow) !important;
            border: 2px solid var(--btn-color) !important;
            box-shadow: 0 0 25px var(--btn-glow), inset 0 0 15px var(--btn-bg-glow), 0 8px 30px var(--btn-bg-glow) !important;
            transform: translateY(-3px) scale(1.05) !important;
            text-shadow: 0 0 8px var(--btn-color);
            animation: borderPulse 2s infinite alternate ease-in-out;
        }

        @keyframes borderPulse {
            0% { box-shadow: 0 0 15px var(--btn-glow), inset 0 0 10px var(--btn-bg-glow), 0 4px 20px var(--btn-bg-glow); }
            100% { box-shadow: 0 0 35px var(--btn-glow), inset 0 0 25px var(--btn-bg-glow), 0 8px 40px var(--btn-bg-glow); }
        }

        .voucher-type-btn span.btn-icon i {
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }
        .voucher-type-btn.active-payment span.btn-icon i, .voucher-type-btn.active-receipt span.btn-icon i {
            filter: drop-shadow(0 0 12px var(--btn-color));
            transform: scale(1.15);
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

        /* ═══ High legibility white background for Amount/Value fields ═══ */
        .transfer-amount, .cheque-amount, .block-sub-amount, .block-cash-amount, .block-bank-amount, .block-cheque-amount, .block-total-amount, .total-val, .val, .value-field, .multi-input[placeholder*="المبلغ"], .multi-input[placeholder*="القيمة"] {
            background: rgba(15, 23, 42, 0.45) !important;
            color: #ffffff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.2) !important;
        }
        .transfer-amount:focus, .cheque-amount:focus, .block-sub-amount:focus, .block-total-amount:focus, .multi-input[placeholder*="المبلغ"]:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25) !important;
        }

        /* Large Totals & Rates */
        /* Large Totals & Rates */
        #exchange-rate {
            font-size: 1.8rem !important;
            font-weight: 900 !important;
        }
        #amount-local-hdr, #amount-foreign {
            font-size: 2.6rem !important;
            font-weight: 900 !important;
            color: #ef4444 !important;
            background-color: #ffffff !important;
            text-align: center !important;
        }
        
        /* Normal State for Amounts */
        input.block-total-amount, input.block-bank-amount, input.block-cheque-amount, input.transfer-amount, input.cheque-amount {
            font-size: 2.4rem !important;
            font-weight: 900 !important;
            color: #ffffff !important;
            transition: font-size 0.2s ease, color 0.2s ease, background 0.2s ease !important;
        }
        
        /* Focus State for Amounts */
        input.block-total-amount:focus, input.block-bank-amount:focus, input.block-cheque-amount:focus, input.transfer-amount:focus, input.cheque-amount:focus {
            font-size: 4.0rem !important;
            color: #fbbf24 !important; /* Gold */
            background: rgba(15, 23, 42, 0.8) !important;
        }
        
        /* Normal State for Numbers */
        input.block-transfer-no-input, input.block-cheque-no-input, input.transfer-no, input.cheque-no {
            font-size: 2.0rem !important;
            font-weight: 900 !important;
            letter-spacing: 1px !important;
            color: #ffffff !important;
            transition: font-size 0.2s ease, color 0.2s ease, background 0.2s ease !important;
        }
        
        /* Focus State for Numbers */
        input.block-transfer-no-input:focus, input.block-cheque-no-input:focus, input.transfer-no:focus, input.cheque-no:focus {
            font-size: 3.5rem !important;
            color: #22d3ee !important; /* Cyan */
            background: rgba(15, 23, 42, 0.8) !important;
        }

        /* Larger Bank Name Font */
        input.block-bank-name-input, input.block-cheque-bank-input {
            font-size: 1.35rem !important;
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
        /* Custom Number Properties Menu */
        #num-props-menu { display: none; position: absolute; z-index: 9999; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); border-radius: 12px; padding: 10px; width: 260px; color: #cbd5e1; font-family: 'Inter', sans-serif; direction: rtl; }
        #num-props-menu .props-header { font-weight: bold; color: #38bdf8; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 1.1rem; }
        #num-props-menu .props-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 5px; cursor: pointer; border-radius: 6px; transition: background 0.2s; font-size: 0.95rem; }
        #num-props-menu .props-item:hover { background: rgba(255, 255, 255, 0.1); }
        #num-props-menu button.prop-btn { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.5); color: #60a5fa; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; }
        #num-props-menu button.prop-btn:hover { background: rgba(59, 130, 246, 0.4); color: #fff; }
    </style>
                        <div id="payment-panel" style="display: none; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; margin-top: 20px; margin-bottom: 20px;">
                            <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 12px 20px; background: rgba(15,23,42,0.4); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                                <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.15rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0; white-space: nowrap; flex: none;">
                                    <span>💳</span> طريقة وتفاصيل الدفع للمستند المجمع
                                </div>
                                
                                <!-- Sleek Payment Progress Bar (Inline) -->
                                <div class="payment-progress-container" style="flex: 1; display: flex; align-items: center; gap: 12px; min-width: 280px; padding: 0 10px;">
                                    <div class="progress-bar-wrapper" style="height: 10px; flex: 1; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); min-width: 120px;">
                                        <div id="prog-cash" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10b981, #34d399); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(16,185,129,0.3);"></div>
                                        <div id="prog-bank" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #60a5fa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(59,130,246,0.3);"></div>
                                        <div id="prog-cheque" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #a78bfa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(139,92,246,0.3);"></div>
                                    </div>
                                    <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; transition: all 0.3s ease; white-space: nowrap; background: rgba(16, 185, 129, 0.15); color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);" data-status="fully-paid">💳 مدفوعة بالكامل</span>
                                </div>

                                <div class="payment-toggles" style="display: flex; gap: 8px; flex: none;">
                                    <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">💵 نقدي</button>
                                    <button type="button" class="pay-toggle-btn active" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">🏦 تحويل بنكي</button>
                                    <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;">✍️ شيك</button>
                                </div>
                            </div>
                            
                            <div class="payment-layout" id="payment-layout" style="display: flex; gap: 15px; padding: 15px; flex-wrap: wrap;">
                                <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">

                                    <!-- Cash section -->
                                    <div id="pay-cash-section" class="payment-method-box" style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.18); border-radius: 12px; padding: 12px 15px; display: none; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 8px; min-width: 200px; width: 200px; flex: none;">
                                            <span style="font-size: 1.2rem;">💵</span>
                                            <span style="font-weight: 800; color: #34d399; font-size: 0.95rem;">الدفع النقدي (كاش)</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 320px; flex-wrap: wrap;">
                                            <span style="color: #94a3b8; font-size: 0.9rem;">سيتم السداد نقداً بالكامل من خلال الصندوق المحدد.</span>
                                        </div>
                                    </div>

                                    <!-- Bank Transfer section -->
                                    <div id="pay-bank-section" class="payment-method-box" style="background: linear-gradient(135deg, rgba(6, 78, 59, 0.6), rgba(16, 185, 129, 0.15)); border: 1px solid rgba(16, 185, 129, 0.7); border-radius: 12px; padding: 12px 15px; display: none; flex-direction: column; gap: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px rgba(16, 185, 129, 0.15); backdrop-filter: blur(12px);">
                                        <div id="transfers-container" style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                                            <!-- Dynamic rows -->
                                        </div>
                                    </div>

                                    <!-- Cheque section -->
                                    <div id="pay-cheque-section" class="payment-method-box" style="background: linear-gradient(135deg, rgba(88, 28, 135, 0.6), rgba(168, 85, 247, 0.15)); border: 1px solid rgba(168, 85, 247, 0.7); border-radius: 12px; padding: 12px 15px; display: none; flex-direction: column; gap: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px rgba(168, 85, 247, 0.15); backdrop-filter: blur(12px);">
                                        <div id="cheques-container" style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                                            <!-- Dynamic rows -->
                                        </div>
                                    </div>

                                    <!-- Unified Total Box Redesign -->
                                    <div id="unified-total-box" style="display: none; width: 100%; margin-top: 15px; padding: 10px 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: stretch; gap: 15px; flex-wrap: wrap;">
                                            <!-- Cheques Total -->
                                            <div class="total-glass-card cheque-glow" style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.4); position: relative; overflow: hidden;">
                                                <div class="glow-orb" style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(168, 85, 247, 0.5); filter: blur(40px); border-radius: 50%; animation: pulseOrb 3s infinite alternate;"></div>
                                                <span style="color: #c084fc; font-size: 1.1rem; font-weight: 800; z-index: 2; margin-bottom: 5px; text-shadow: 0 0 10px rgba(168,85,247,0.5);">مجموع الشيكات</span>
                                                <span id="unified-cheque-total" style="color: #e9d5ff; font-size: 1.8rem; font-weight: 900; z-index: 2; text-shadow: 0 0 15px rgba(168,85,247,0.8); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                            <!-- Bank Total -->
                                            <div class="total-glass-card bank-glow" style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.4); position: relative; overflow: hidden;">
                                                <div class="glow-orb" style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(16, 185, 129, 0.5); filter: blur(40px); border-radius: 50%; animation: pulseOrb 3.5s infinite alternate-reverse;"></div>
                                                <span style="color: #34d399; font-size: 1.1rem; font-weight: 800; z-index: 2; margin-bottom: 5px; text-shadow: 0 0 10px rgba(16,185,129,0.5);">مجموع التحويلات</span>
                                                <span id="unified-bank-total" style="color: #a7f3d0; font-size: 1.8rem; font-weight: 900; z-index: 2; text-shadow: 0 0 15px rgba(16,185,129,0.8); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                            <!-- Grand Total -->
                                            <div class="total-glass-card grand-glow" style="flex: 1; min-width: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(56, 189, 248, 0.15); border: 2px solid rgba(56, 189, 248, 0.6); position: relative; overflow: hidden; transform: scale(1.02); box-shadow: 0 0 20px rgba(56,189,248,0.2);">
                                                <div class="glow-orb" style="position: absolute; top: -50px; left: -50px; width: 120px; height: 120px; background: rgba(56, 189, 248, 0.6); filter: blur(50px); border-radius: 50%; animation: pulseOrb 2.5s infinite alternate;"></div>
                                                <span style="color: #7dd3fc; font-size: 1.3rem; font-weight: 900; z-index: 2; margin-bottom: 5px; text-shadow: 0 0 15px rgba(56,189,248,0.6);">إجمالي الجملة</span>
                                                <span id="unified-grand-total" style="color: #ffffff; font-size: 2.2rem; font-weight: 900; z-index: 2; text-shadow: 0 0 20px rgba(56,189,248,1); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden legacy inputs to prevent crashes in old scripts -->
                                    <div style="display: none;">
                                        <input type="text" id="mobile-transfer-no">
                                        <input type="text" id="mobile-note">
                                        <input type="text" id="check-no">
                                        <input type="text" id="check-branch">
                                        <input type="date" id="check-date">
                                        <input type="date" id="check-due-date">
                                        <input type="text" id="check-ref">
                                        <input type="text" id="check-ref-no">
                                        <input type="checkbox" id="check-settle-local">
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY FOOTER -->
        <div class="sticky-footer" style="background: rgba(15,23,42,0.85); backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px); border-top: 1px solid rgba(56, 189, 248, 0.4); border-left: 1px solid rgba(56, 189, 248, 0.4); border-right: 1px solid rgba(56, 189, 248, 0.4); border-radius: 20px 20px 0 0; padding: 8px 15px; position: sticky; bottom: 0; z-index: 100; box-shadow: 0 -10px 40px rgba(0,0,0,0.6), inset 0 2px 15px rgba(255,255,255,0.05);">
            <div class="footer-actions" style="width: 100%; justify-content: center; gap: 10px; display: flex; flex-wrap: wrap;">
                <button type="button" class="btn crystal-btn btn-exit" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()">✨ مستند جديد</button>
                <button type="button" class="btn crystal-btn btn-print-preview" id="btn-preview-invoice" style="display: none; background: rgba(14, 165, 233, 0.15); border: 1px solid #0ea5e9; color: #7dd3fc;" onclick="previewVoucher()">👁️ معاينة</button>
                <button type="button" class="btn crystal-btn btn-print" id="btn-print-invoice" style="display: none;" onclick="printVoucher()">🖨️ طباعة</button>
                <button type="button" class="btn btn-whatsapp" id="btn-whatsapp-invoice" style="display: none; background: #25d366; color: #fff; border-color: #25d366; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold; cursor: pointer; padding: 10px 20px; border-radius: 8px; border: none;" onclick="sendCurrentVoucherWA()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" style="margin-left: 5px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>إرسال للواتساب</span>
                </button>
                <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()">🗑️ حذف السند</button>
                <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="submitVoucher()">✅ حفظ واعتماد السند</button>
            </div>
        </div>
    </div>

    <!-- Dedicated Premium Voucher Preview Modal -->
    <div id="premium-voucher-preview-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="glass-panel" style="width: 95%; max-width: 900px; height: 90vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 20px; overflow: hidden; transform: translateY(30px); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #38bdf8;">
                <h2 style="margin: 0; color: #fff; font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.8rem;">👁️</span> معاينة السند المالي
                </h2>
                <div style="display: flex; gap: 15px;">
                    <button type="button" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 10px 25px; border-radius: 10px; font-weight: 800; font-size: 1.1rem; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="printVoucher()">
                        🖨️ طباعة فورية
                    </button>
                    <button type="button" onclick="closePremiumPreview()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; width: 45px; height: 45px; border-radius: 10px; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        ❌
                    </button>
                </div>
            </div>

            <!-- Modal Body (The Actual Paper) -->
            <div style="flex: 1; overflow-y: auto; padding: 40px; background: #e2e8f0; display: flex; justify-content: center;">
                <div id="premium-voucher-paper" style="background: #ffffff; width: 100%; max-width: 800px; min-height: 1000px; padding: 50px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); position: relative; border-radius: 4px;">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    
    <script defer src="js/app.js?v=<?php echo time(); ?>"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=<?php echo time(); ?>"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          
          
      });
    </script>
    <script>
        window.SERVER_SINGLE_PAYMENTS = <?php echo $SERVER_SINGLE_PAYMENTS_json; ?>;
    </script>
    <script defer src="js/single-cash-payment.js?v=<?php echo time(); ?>"></script>
    <script>
        let lastEnterTime = 0;
        // Perfected Exact Path Enter Navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                let now = Date.now();
                if (now - lastEnterTime < 200) { e.preventDefault(); return; } // prevent rapid double jump
                lastEnterTime = now;
                
                let current = document.activeElement;
                if (current.tagName === 'TEXTAREA' && current.id !== 'from-statement') return; 

                // Determine sequence based on current focus ID
                let id = current.id;
                
                // If focus is inside a choices container, figure out which one (ignore choices inner IDs)
                if (current.closest('.choices')) {
                    let choicesDiv = current.closest('.choices');
                    if (choicesDiv.classList.contains('is-open')) return; // let choices select item
                    
                    let originalSelect = choicesDiv.querySelector('select');
                    if (originalSelect) id = originalSelect.id;
                    else {
                        let prev = choicesDiv.previousElementSibling;
                        if (prev && prev.tagName === 'SELECT') id = prev.id;
                    }
                }

                if (!id) return;
                
                let nextAction = null;

                if (id === 'amount-local') {
                    nextAction = () => { 
                        document.getElementById('to-account-search').focus();
                    };
                } else if (id === 'to-account-search' || id === 'from-account-search') {
                    nextAction = () => {
                        let type = id.split('-')[0];
                        let list = document.getElementById(type + '-account-dropdown-list');
                        if (list && list.classList.contains('show')) {
                            let firstItem = list.querySelector('.custom-dropdown-item');
                            if (firstItem && typeof firstItem.onmousedown === 'function') {
                                firstItem.onmousedown(); // This will call selectAccount and our logic
                            }
                        }
                    };
                } else if (id === 'to-statement') {
                    nextAction = () => { let n = document.getElementById('currency'); n.focus(); };
                } else if (id === 'currency') {
                    nextAction = () => { let n = document.getElementById('exchange-rate'); n.focus(); n.select(); };
                } else if (id === 'exchange-rate') {
                    nextAction = () => { let n = document.getElementById('beneficiary-name'); n.focus(); n.select(); };
                } else if (id === 'amount-foreign') {
                    nextAction = () => { let n = document.getElementById('beneficiary-name'); n.focus(); n.select(); };
                } else if (id === 'beneficiary-name') {
                    nextAction = () => {
                        const btnBank = document.getElementById('toggle-bank');
                        if (btnBank && !btnBank.classList.contains('active')) { if (typeof togglePaymentSection === 'function') togglePaymentSection('bank'); }
                        const bankSec = document.getElementById('pay-bank-section');
                        if (bankSec) bankSec.style.display = 'flex';
                        const container = document.getElementById('transfers-container');
                        if (container && container.children.length === 0 && typeof addTransferRow === 'function') { addTransferRow('', '', 0); }
                        setTimeout(() => { const amtInput = document.querySelector('.transfer-amount'); if (amtInput) { amtInput.focus(); amtInput.select(); } }, 150);
                    };
                } else if (id === 'from-account-search') {
                    nextAction = () => {
                        const btnBank = document.getElementById('toggle-bank');
                        if (btnBank && !btnBank.classList.contains('active')) { if (typeof togglePaymentSection === 'function') togglePaymentSection('bank'); }
                        const bankSec = document.getElementById('pay-bank-section');
                        if (bankSec) bankSec.style.display = 'flex';
                        const container = document.getElementById('transfers-container');
                        if (container && container.children.length === 0 && typeof addTransferRow === 'function') { addTransferRow('', '', 0); }
                        setTimeout(() => { const amtInput = document.querySelector('.transfer-amount'); if (amtInput) { amtInput.focus(); amtInput.select(); } }, 150);
                    };
                } else if (id === 'cost-center') {
                    nextAction = () => { let n = document.getElementById('from-statement'); n.focus(); n.select(); };
                } else if (id === 'from-statement') {
                    nextAction = () => { document.getElementById('btn-save').focus(); };
                }

                if (nextAction) {
                    e.preventDefault();
                    // Small timeout ensures Choices.js DOM is ready if it just opened
                    setTimeout(nextAction, 10);
                }
            }
        });

        // Sound & Progress Bar Recalculator
        const PlaySound = {
            ctx: null,
            init() {
                if (!this.ctx) {
                    this.ctx = new (window.AudioContext || window.webkitAudioContext)();
                }
            },
            success() {
                try {
                    this.init();
                    const now = this.ctx.currentTime;
                    const osc1 = this.ctx.createOscillator();
                    const osc2 = this.ctx.createOscillator();
                    const gain = this.ctx.createGain();
                    osc1.type = 'sine';
                    osc2.type = 'sine';
                    osc1.frequency.setValueAtTime(523.25, now);
                    osc1.frequency.exponentialRampToValueAtTime(880, now + 0.15);
                    osc2.frequency.setValueAtTime(659.25, now);
                    osc2.frequency.exponentialRampToValueAtTime(1046.50, now + 0.15);
                    gain.gain.setValueAtTime(0.08, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(this.ctx.destination);
                    osc1.start(now);
                    osc2.start(now);
                    osc1.stop(now + 0.25);
                    osc2.stop(now + 0.25);
                } catch(e) {}
            }
        };

        function recalcPaymentSummary() {
            const amtStr = document.getElementById('amount-local')?.value || '0';
            let amount = parseFloat(amtStr.replace(/,/g, '')) || 0;
            
            const panel = document.getElementById('payment-panel');
            if (!panel) return;
            
            panel.style.display = 'block';
            
            // Get active method
            let method = 'cash';
            const bankActive = document.getElementById('toggle-bank')?.classList.contains('active');
            const chequeActive = document.getElementById('toggle-cheque')?.classList.contains('active');
            
            if (bankActive && chequeActive) method = 'hybrid';
            else if (bankActive) method = 'bank';
            else if (chequeActive) method = 'cheque';
            else method = 'cash';
            
            let tSum = 0;
            if (bankActive) {
                document.querySelectorAll('.transfer-row .transfer-amount').forEach(inp => tSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
            }
            let cSum = 0;
            if (chequeActive) {
                document.querySelectorAll('.cheque-row .cheque-amount').forEach(inp => cSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
            }
            
            const amtElGlobal = document.getElementById('amount-local');
            
            // Auto-calculate amount-local based on rows if not cash!
            if (method !== 'cash') {
                if (amtElGlobal) amtElGlobal.readOnly = true; // Lock local amount field
                const totalSum = tSum + cSum;
                if (Math.abs(amount - totalSum) > 0.001) {
                    amount = totalSum;
                    if (amtElGlobal) {
                        // Remember original cursor position if focused on amount-local (unlikely but safe)
                        let parts = totalSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]]; // Don't add .00 if not needed
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        amtElGlobal.value = parts.join('.');
                        
                        if (typeof calcEquiv === 'function') calcEquiv('local');
                    }
                }
            } else {
                if (amtElGlobal) amtElGlobal.readOnly = false; // Unlock local amount field in cash mode
            }
            
            // Update unified total box
            const unifiedBox = document.getElementById('unified-total-box');
            if (unifiedBox) {
                if (method !== 'cash') {
                    unifiedBox.style.display = 'block';
                    const uBank = document.getElementById('unified-bank-total');
                    const uCheque = document.getElementById('unified-cheque-total');
                    const uGrand = document.getElementById('unified-grand-total');
                    
                    if (uBank) {
                        let parts = tSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]];
                        uBank.textContent = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
                    }
                    if (uCheque) {
                        let parts = cSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]];
                        uCheque.textContent = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
                    }
                    if (uGrand) {
                        let totalSum = tSum + cSum;
                        let parts = totalSum.toFixed(2).split('.');
                        if (parts[1] === '00') parts = [parts[0]];
                        uGrand.textContent = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
                    }
                } else {
                    unifiedBox.style.display = 'none';
                }
            }
            
            // Progress widths
            const elCash = document.getElementById('prog-cash');
            const elBank = document.getElementById('prog-bank');
            const elCheque = document.getElementById('prog-cheque');
            
            if (elCash) elCash.style.width = '0%';
            if (elBank) elBank.style.width = '0%';
            if (elCheque) elCheque.style.width = '0%';
            
            if (method === 'cash') {
                if (elCash) elCash.style.width = '100%';
            } else if (method === 'bank') {
                if (elBank) elBank.style.width = '100%';
            } else if (method === 'cheque') {
                if (elCheque) elCheque.style.width = '100%';
            } else if (method === 'hybrid') {
                const bankPct = amount > 0 ? Math.min(100, (tSum / amount) * 100) : 0;
                const chequePct = amount > 0 ? Math.min(100, (cSum / amount) * 100) : 0;
                
                if (elBank) elBank.style.width = bankPct + '%';
                if (elCheque) elCheque.style.width = chequePct + '%';
            }
            
            // Update status badge
            const badge = document.getElementById('payment-status-badge');
            if (badge) {
                if (method === 'hybrid') {
                    let tSum = 0;
                    document.querySelectorAll('.transfer-row .transfer-amount').forEach(inp => tSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
                    let cSum = 0;
                    document.querySelectorAll('.cheque-row .cheque-amount').forEach(inp => cSum += parseFloat(inp.value.replace(/,/g, '')) || 0);
                    
                    if (Math.abs((tSum + cSum) - amount) < 0.01) {
                        badge.textContent = '💳 مدفوعة بالكامل (تحويل وشيك)';
                        badge.style.background = 'rgba(56, 189, 248, 0.15)';
                        badge.style.color = '#38bdf8';
                        badge.style.boxShadow = '0 0 10px rgba(56, 189, 248, 0.2)';
                    } else {
                        const remaining = amount - (tSum + cSum);
                        let parts = remaining.toFixed(2).split('.');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        badge.textContent = `⏳ متبقي: ${parts.join('.')}`;
                        badge.style.background = 'rgba(245, 158, 11, 0.15)';
                        badge.style.color = '#fbbf24';
                        badge.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.2)';
                    }
                } else {
                    if (badge.dataset.status !== 'fully-paid') {
                        PlaySound.success();
                        badge.dataset.status = 'fully-paid';
                    }
                    badge.textContent = '💳 مدفوعة بالكامل';
                    badge.style.background = 'rgba(16, 185, 129, 0.15)';
                    badge.style.color = '#34d399';
                    badge.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.2)';
                }
            }
        }

        function togglePaymentSection(clickedMethod) {
            const btnCash = document.getElementById('toggle-cash');
            const btnBank = document.getElementById('toggle-bank');
            const btnCheque = document.getElementById('toggle-cheque');

            if (clickedMethod === 'cash') {
                if (btnCash) btnCash.classList.add('active');
                if (btnBank) btnBank.classList.remove('active');
                if (btnCheque) btnCheque.classList.remove('active');
            } else {
                if (btnCash) btnCash.classList.remove('active');
                
                const btn = document.getElementById('toggle-' + clickedMethod);
                if (btn) {
                    btn.classList.toggle('active');
                }
                
                // If both bank and cheque are deactivated, fallback to cash
                const bankActive = btnBank?.classList.contains('active');
                const chequeActive = btnCheque?.classList.contains('active');
                if (!bankActive && !chequeActive) {
                    if (btnCash) btnCash.classList.add('active');
                }
            }

            // Sync with radio buttons
            const bankActive = btnBank?.classList.contains('active');
            const chequeActive = btnCheque?.classList.contains('active');
            
            let radioId = 'radio-cash';
            if (bankActive && chequeActive) radioId = 'radio-hybrid';
            else if (bankActive) radioId = 'radio-mobile';
            else if (chequeActive) radioId = 'radio-check';

            const radio = document.getElementById(radioId);
            if (radio) {
                radio.checked = true;
            }

            // Update payment method boxes visibility
            const isCash = !bankActive && !chequeActive;
            document.getElementById('pay-cash-section').style.display = isCash ? 'flex' : 'none';
            document.getElementById('pay-bank-section').style.display = bankActive ? 'flex' : 'none';
            document.getElementById('pay-cheque-section').style.display = chequeActive ? 'flex' : 'none';
            
            if (document.getElementById('pay-credit-section')) {
                document.getElementById('pay-credit-section').style.display = 'none';
            }
            
            recalcPaymentSummary();
        }

        // Focus delegation to clear zero values instantly on click/focus
        document.addEventListener('focus', function(event) {
            const input = event.target;
            if (input.id === 'amount-local' || input.id === 'amount-foreign') {
                const val = parseFloat(input.value.replace(/,/g, '')) || 0;
                if (val === 0) {
                    input.value = '';
                }
            }
        }, true);

        // Blur delegation to format payment inputs to 2 decimal places with commas
        document.addEventListener('blur', function(event) {
            const input = event.target;
            if (input.id === 'amount-local' || input.id === 'amount-foreign') {
                const val = parseFloat(input.value.replace(/,/g, '')) || 0;
                if (input.value.trim() === '') {
                    input.value = '0.00';
                } else {
                    let parts = val.toFixed(2).split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    input.value = parts.join('.');
                }
                // Recalculate progress
                recalcPaymentSummary();
            }
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            // Override togglePaymentMethod to handle the inline unified boxes
            window.togglePaymentMethod = function() {
                const methodRadio = document.querySelector('input[name="payment_method"]:checked');
                const method = methodRadio ? methodRadio.value : 'cash';
                
                const btnCash = document.getElementById('toggle-cash');
                const btnBank = document.getElementById('toggle-bank');
                const btnCheque = document.getElementById('toggle-cheque');

                if (btnCash) btnCash.classList.remove('active');
                if (btnBank) btnBank.classList.remove('active');
                if (btnCheque) btnCheque.classList.remove('active');

                if (method === 'mobile') {
                    if (btnBank) btnBank.classList.add('active');
                } else if (method === 'check') {
                    if (btnCheque) btnCheque.classList.add('active');
                } else if (method === 'hybrid') {
                    if (btnBank) btnBank.classList.add('active');
                    if (btnCheque) btnCheque.classList.add('active');
                } else {
                    if (btnCash) btnCash.classList.add('active');
                }

                const bankActive = btnBank?.classList.contains('active');
                const chequeActive = btnCheque?.classList.contains('active');
                const isCash = !bankActive && !chequeActive;

                if (document.getElementById('pay-cash-section')) {
                    document.getElementById('pay-cash-section').style.display = isCash ? 'flex' : 'none';
                    document.getElementById('pay-bank-section').style.display = bankActive ? 'flex' : 'none';
                    document.getElementById('pay-cheque-section').style.display = chequeActive ? 'flex' : 'none';
                }
                
                recalcPaymentSummary();
            };
            
            // Set initial state
            setTimeout(() => {
                if (typeof updateCurrency === 'function') updateCurrency();
                window.togglePaymentMethod();
            }, 200);
        });
    </script>

    <script>
    let hasWarnedAboutEdit = false;

    document.addEventListener('DOMContentLoaded', () => {
        // Intercept save by wrapping the original function unconditionally
        if (typeof window.submitVoucher === 'function') {
            const originalSubmit = window.submitVoucher;
            window.submitVoucher = function(...args) {
                let editIndex = -1;
                try {
                    if (typeof currentViewedIndex !== 'undefined') {
                        editIndex = currentViewedIndex;
                    }
                } catch(e) {}
                
                const isEditing = (editIndex !== -1) || (new URLSearchParams(window.location.search).get('id') != null);
                
                if (isEditing && !hasWarnedAboutEdit) {
                    showEditWarningModal();
                } else {
                    hasWarnedAboutEdit = false;
                    return originalSubmit.apply(this, args);
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
                if (typeof window.submitVoucher === 'function') {
                    window.submitVoucher();
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
    
    function autoCategorize() {
        const desc = document.getElementById('to-statement').value.trim();
        if (!desc) {
            alert('الرجاء كتابة البيان أولاً لكي يتوقع الذكاء الاصطناعي الحساب.');
            return;
        }
        
        const btn = document.getElementById('btn-auto-categorize');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span style="font-size: 1.2rem;">⏳</span> <span class="btn-text">جاري التفكير...</span>';
        btn.disabled = true;

        // Gather all accounts to give Gemini context of what choices exist
        let allAccounts = [];
        try {
            allAccounts = allAccounts.concat(JSON.parse(localStorage.getItem('erp_accounts_v1') || '[]'));
            allAccounts = allAccounts.concat(JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]'));
        } catch(e){}

        // Select only relevant fields to save tokens
        let accountListStr = allAccounts.filter(a => !a.deleted).map(a => a.name).join(' | ');

        let promptMsg = `أنت موجه محاسبي آلي. لقد كتب المستخدم البيان التالي لسند صرف نقدية: "${desc}". 
المطلوب: اختار الحساب الأنسب من ضمن القائمة التالية: [${accountListStr}]. 
يجب أن يكون ردك عبارة عن اسم الحساب فقط بدون أي إضافات أو شرح. إذا لم تجد حساباً مطابقاً بدقة اختر أقرب حساب (مثلاً مصروفات عمومية أو مصروفات ضيافة).`;

        fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: promptMsg, context: '' })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            if (data.reply) {
                let suggestedName = data.reply.trim();
                // Find account ID
                let match = allAccounts.find(a => a.name === suggestedName);
                if (match) {
                    // Update UI
                    document.getElementById('to-account').value = match.id;
                    document.getElementById('to-account-search').value = match.name;
                    // Flash success
                    document.getElementById('to-account-search').style.boxShadow = '0 0 15px #10b981';
                    setTimeout(() => document.getElementById('to-account-search').style.boxShadow = '', 2000);
                } else {
                    alert('اقترح الذكاء الاصطناعي: ' + suggestedName + ' ولكن لم يتم العثور عليه في القائمة بدقة.');
                }
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('حدث خطأ في الاتصال بالمساعد الذكي.');
        });
    }
    </script>
    
    <!-- Custom Context Menu for Numbers -->
    <div id="num-props-menu">
        <div class="props-header">⚙️ خصائص الأرقام</div>
        <div class="props-item">
            <span>حجم الخط (تعديل)</span>
            <div style="display: flex; gap: 5px;">
                <button class="prop-btn" onclick="applyNumProp('font-inc'); event.stopPropagation();">➕</button>
                <button class="prop-btn" onclick="applyNumProp('font-dec'); event.stopPropagation();">➖</button>
            </div>
        </div>
        <div class="props-item" onclick="applyNumProp('toggle-comma')">
            <span>الفاصلة الألفية (,)</span>
            <span id="prop-comma-status" style="color:#10b981; font-weight:bold;">مفعل</span>
        </div>
        <div class="props-item">
            <span>الخانات العشرية</span>
            <div style="display: flex; gap: 5px; align-items: center;">
                <span id="prop-dec-val" style="color:#fbbf24; font-weight:bold;">0</span>
                <button class="prop-btn" onclick="applyNumProp('dec-inc'); event.stopPropagation();">➕</button>
                <button class="prop-btn" onclick="applyNumProp('dec-dec'); event.stopPropagation();">➖</button>
            </div>
        </div>
        <div class="props-item" onclick="applyNumProp('toggle-font')">
            <span>نوع الخط</span>
            <span id="prop-font-status" style="color:#fbbf24; font-size:0.8rem; font-weight:bold;">Inter</span>
                <div class="props-item" style="justify-content: center; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            <button id="save-props-btn" onclick="saveNumberProperties(); event.stopPropagation();" style="background: #10b981; color: #fff; border: none; padding: 6px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.2s;">💾 حفظ الخصائص</button>
        </div>
    </div>
</body>
</html>











<!-- FORCE CACHE BUST: 10001 -->
