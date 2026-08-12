<?php 
require_once 'db_bridge.php';
$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;

$SERVER_PAYMENTS_json = '[]';
$SERVER_RECEIPTS_json = '[]';
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

        /* Crystal Red Container (المستطيل الرئيسي للصرف) */
        .glass-red {
            background: linear-gradient(135deg, rgba(46, 28, 30, 0.85), rgba(15, 23, 42, 0.95)) !important;
            border: 1px solid rgba(212, 122, 126, 0.35) !important;
            border-right: 5px solid #d47a7e !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(212, 122, 126, 0.25), 0 0 25px rgba(212, 122, 126, 0.12) !important;
        }
        .glass-red:hover {
            background: linear-gradient(135deg, rgba(56, 32, 34, 0.9), rgba(15, 23, 42, 0.98)) !important;
            border-color: rgba(212, 122, 126, 0.6) !important;
            border-right-color: #d47a7e !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.4), 0 0 35px rgba(212, 122, 126, 0.25) !important;
        }

        /* Crystal Green Container (مستطيلات الحسابات المستلمة) */
        .glass-green {
            background: linear-gradient(135deg, rgba(16, 42, 28, 0.85), rgba(15, 23, 42, 0.95)) !important;
            border: 1px solid rgba(39, 174, 96, 0.35) !important;
            border-right: 5px solid #27ae60 !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), inset 0 1px 2px rgba(39, 174, 96, 0.25), 0 0 25px rgba(39, 174, 96, 0.12) !important;
        }
        .glass-green:hover {
            background: linear-gradient(135deg, rgba(20, 52, 34, 0.9), rgba(15, 23, 42, 0.98)) !important;
            border-color: rgba(39, 174, 96, 0.6) !important;
            border-right-color: #2ecc71 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.4), 0 0 35px rgba(39, 174, 96, 0.25) !important;
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
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 3.8px solid var(--tab-border-color, rgba(255, 255, 255, 0.25)) !important;
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

        /* Highly Visible Internal Glitter Sparkles Overlay */
        .crystal-btn::after, .voucher-type-btn::after, .pay-toggle-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 10px;
            background-image: 
                radial-gradient(circle at 15% 25%, rgba(255, 255, 255, 1) 2px, transparent 2px),
                radial-gradient(circle at 75% 15%, rgba(255, 255, 255, 0.95) 1.8px, transparent 1.8px),
                radial-gradient(circle at 35% 75%, rgba(255, 255, 255, 1) 2px, transparent 2px),
                radial-gradient(circle at 85% 80%, rgba(255, 255, 255, 0.9) 1.5px, transparent 1.5px),
                radial-gradient(circle at 50% 40%, rgba(255, 255, 255, 0.85) 1.5px, transparent 1.5px),
                linear-gradient(135deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
            background-size: 50px 50px, 38px 38px, 45px 45px, 40px 40px, 35px 35px, 200% 200%;
            background-repeat: repeat, repeat, repeat, repeat, repeat, no-repeat;
            opacity: 0.85; /* Crystal clear glitter visibility */
            mix-blend-mode: overlay;
            pointer-events: none;
            z-index: 2;
            animation: glitterShimmer 3s infinite linear;
        }

        .crystal-btn:hover::after, .voucher-type-btn:hover::after, .pay-toggle-btn:hover::after {
            opacity: 1;
            animation: glitterShimmer 1.8s infinite linear;
        }

        /* Rear Bottom Light Ray Beam Projection (الإضاءة الخلفية من الأسفل فقط) */
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
            opacity: 0.65;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
            z-index: -1;
            animation: rearRayPulse 3s infinite ease-in-out;
        }

        .crystal-btn:hover::before, .voucher-type-btn:hover::before, .pay-toggle-btn:hover::before {
            opacity: 1 !important;
            transform: scaleX(1.4) translateY(5px) !important;
            filter: blur(18px) brightness(1.6) !important;
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
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: var(--tab-glow-color, rgba(255, 255, 255, 0.6));
            filter: blur(10px);
            opacity: 0.4;
            z-index: -1;
            transition: all 0.35s ease;
        }

        /* Hover Scale + Light Glow + Text Contrast Protection */
        .crystal-btn:hover, .voucher-type-btn:hover, .pay-toggle-btn:hover {
            transform: translateY(-5px) scale(1.08) !important;
            color: #ffffff !important;
            text-shadow: 0 0 12px var(--tab-glow-color, rgba(255,255,255,0.95)), 0 2px 5px rgba(0,0,0,0.9) !important;
            border-color: var(--tab-border-color) !important;
            box-shadow: 0 16px 36px var(--tab-glow-color, rgba(56,189,248,0.55)), 0 0 28px var(--tab-glow-color, rgba(56,189,248,0.45)), inset 0 1px 3px rgba(255,255,255,0.7) !important;
            background: var(--tab-hover-bg, rgba(255,255,255,0.12)) !important;
            z-index: 10;
        }

        .crystal-btn:hover .btn-icon, .voucher-type-btn:hover .btn-icon, .pay-toggle-btn:hover .btn-icon {
            transform: scale(1.38) rotate(8deg);
            filter: drop-shadow(0 0 10px #ffffff);
        }

        .crystal-btn:hover .btn-icon::after, .voucher-type-btn:hover .btn-icon::after, .pay-toggle-btn:hover .btn-icon::after {
            opacity: 0.95 !important;
            transform: scale(1.6) !important;
            filter: blur(14px) brightness(1.8) !important;
        }

        /* ═══ Voucher Type Buttons (صرف مجمع 📤 vs استلام مجمع 📥) ═══ */
        .voucher-type-btn.active-payment, #btn-payment.active-payment {
            --tab-border-color: #d47a7e;
            --tab-glow-color: rgba(212, 122, 126, 0.7);
            --tab-hover-bg: linear-gradient(135deg, #d47a7e, #a3585c);
            background: linear-gradient(135deg, rgba(212, 122, 126, 0.25), rgba(163, 88, 92, 0.4)) !important;
            border-color: #d47a7e !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 30px rgba(212, 122, 126, 0.65), 0 0 25px rgba(212, 122, 126, 0.4), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05) !important;
            /* animation removed */
        }

        .voucher-type-btn.active-receipt, #btn-receipt.active-receipt {
            --tab-border-color: #81b29a;
            --tab-glow-color: rgba(129, 178, 154, 0.7);
            --tab-hover-bg: linear-gradient(135deg, #81b29a, #5e8773);
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.25), rgba(39, 174, 96, 0.4)) !important;
            border-color: #81b29a !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 30px rgba(129, 178, 154, 0.65), 0 0 25px rgba(129, 178, 154, 0.4), inset 0 2px 4px rgba(255,255,255,0.5) !important;
            transform: translateY(-3px) scale(1.05) !important;
            /* animation removed */
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
            --tab-border-color: rgba(52, 152, 219, 0.45);
            --tab-glow-color: rgba(52, 152, 219, 0.65);
            --tab-hover-bg: linear-gradient(135deg, rgba(52, 152, 219, 0.35), rgba(41, 128, 185, 0.5));
        }
        .pay-toggle-btn.active.block-btn-bank {
            --tab-border-color: #3498db;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.45), rgba(41, 128, 185, 0.7)) !important;
            border-color: #3498db !important;
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(52, 152, 219, 0.95), 0 2px 4px rgba(0,0,0,0.8) !important;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.5), 0 0 20px rgba(52, 152, 219, 0.3), inset 0 1px 2px rgba(255,255,255,0.6) !important;
            transform: translateY(-3px) scale(1.05) !important;
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
        .block-bank-details:hover {
            border-color: rgba(52, 152, 219, 0.75) !important;
            box-shadow: 0 0 25px rgba(52, 152, 219, 0.35), inset 0 1px 2px rgba(255,255,255,0.4) !important;
        }
        .block-cheque-details:hover {
            border-color: rgba(155, 89, 182, 0.75) !important;
            box-shadow: 0 0 25px rgba(155, 89, 182, 0.35), inset 0 1px 2px rgba(255,255,255,0.4) !important;
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

        /* Voucher Type Toggle Buttons (صرف/استلام مجمع) */
        /* Voucher Type Toggle Buttons (صرف/استلام مجمع) */
        .voucher-type-btn {
            /* background removed */
            /* border removed */
            /* color removed */
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.6) !important;
            min-width: 175px !important;
            height: 45px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            transform: translateY(0) scale(0.97) !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }
        .voucher-type-btn:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #ffffff !important;
            border-color: rgba(255,255,255,0.2) !important;
            transform: translateY(-2px) scale(1) !important;
        }
        .voucher-type-btn.active-payment {
            background: linear-gradient(135deg, rgba(212, 122, 126, 0.25), rgba(163, 88, 92, 0.4)) !important;
            border: 1px solid rgba(212, 122, 126, 0.8) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 25px rgba(212, 122, 126, 0.5), 0 0 15px rgba(212, 122, 126, 0.3), inset 0 0 8px rgba(255,255,255,0.2) !important;
            font-weight: 900 !important;
            transform: translateY(-4px) scale(1.04) !important;
            /* animation removed */
        }
        .voucher-type-btn.active-receipt {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.25), rgba(39, 174, 96, 0.4)) !important;
            border: 1px solid rgba(39, 174, 96, 0.8) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.5), 0 0 15px rgba(39, 174, 96, 0.3), inset 0 0 8px rgba(255,255,255,0.2) !important;
            font-weight: 900 !important;
            transform: translateY(-4px) scale(1.04) !important;
            /* animation removed */
        }

        /* Pulsing active buttons */
        @keyframes activePulse {
            0% { box-shadow: 0 8px 20px rgba(212, 122, 126, 0.45), 0 0 10px rgba(212, 122, 126, 0.25); border-color: rgba(212, 122, 126, 0.7); }
            50% { box-shadow: 0 12px 30px rgba(212, 122, 126, 0.75), 0 0 20px rgba(212, 122, 126, 0.5); border-color: rgba(255, 255, 255, 0.9); }
            100% { box-shadow: 0 8px 20px rgba(212, 122, 126, 0.45), 0 0 10px rgba(212, 122, 126, 0.25); border-color: rgba(212, 122, 126, 0.7); }
        }
        @keyframes activePulseGreen {
            0% { box-shadow: 0 8px 20px rgba(39, 174, 96, 0.45), 0 0 10px rgba(39, 174, 96, 0.25); border-color: rgba(39, 174, 96, 0.7); }
            50% { box-shadow: 0 12px 30px rgba(39, 174, 96, 0.75), 0 0 20px rgba(39, 174, 96, 0.5); border-color: rgba(255, 255, 255, 0.9); }
            100% { box-shadow: 0 8px 20px rgba(39, 174, 96, 0.45), 0 0 10px rgba(39, 174, 96, 0.25); border-color: rgba(39, 174, 96, 0.7); }
        }

        /* Heartbeat for active state circles */
        @keyframes heartBeatRed {
            0% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            20% { transform: scale(1.4); background-color: #e8a5a7; box-shadow: 0 0 15px #e8a5a7; }
            40% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            60% { transform: scale(1.4); background-color: #d47a7e; box-shadow: 0 0 15px #d47a7e; }
            80% { transform: scale(1.2); background-color: #ebbabc; box-shadow: 0 0 12px #ebbabc; }
            100% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
        }
        @keyframes heartBeatGreen {
            0% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            20% { transform: scale(1.4); background-color: #abebc6; box-shadow: 0 0 15px #abebc6; }
            40% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            60% { transform: scale(1.4); background-color: #27ae60; box-shadow: 0 0 15px #27ae60; }
            80% { transform: scale(1.2); background-color: #58d68d; box-shadow: 0 0 12px #58d68d; }
            100% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
        }

        .voucher-type-btn.active-payment span {
            /* animation removed */
        }
        .voucher-type-btn.active-receipt span {
            /* animation removed */
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
            background: #ffffff !important;
            color: #047857 !important; /* Rich readable dark green */
            border: 1.5px solid #cbd5e1 !important;
        }
        .transfer-amount:focus, .cheque-amount:focus, .block-sub-amount:focus, .block-total-amount:focus, .multi-input[placeholder*="المبلغ"]:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25) !important;
        }
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
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
                            <button id="btn-payment" class="btn crystal-btn voucher-type-btn active-payment" onclick="setVoucherType('payment')">
                                <span class="btn-icon">💸📤</span> <span class="btn-text">صرف مجمع</span>
                            </button>
                            <button id="btn-receipt" class="btn crystal-btn voucher-type-btn" onclick="setVoucherType('receipt')">
                                <span class="btn-icon">💰📥</span> <span class="btn-text">استلام مجمع</span>
                            </button>
                        </div>
                    </div>

                    <!-- Voucher Details (Ref, Date, No) -->
                    <div style="display: flex; align-items: center; gap: 15px; flex: 1; justify-content: flex-end; flex-wrap: wrap;">
                        
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; height: 40px; padding: 0 8px;">
                            <button type="button" onclick="navigateVoucher(-1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 1.1rem; padding: 0 5px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند السابق">◀</button>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; white-space: nowrap;">📄 السند:</span>
                                <input type="text" id="voucher-no-display" readonly style="background: transparent !important; border: none !important; color: #fff; font-weight: bold; width: 140px; text-align: center; outline: none !important; font-size:0.95rem; box-shadow: none !important; padding: 0;" value="">
                            </div>
                            <button type="button" onclick="navigateVoucher(1)" style="background: transparent; border: none; color: #38bdf8; cursor: pointer; font-size: 1.1rem; padding: 0 5px; outline: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'" title="السند التالي">▶</button>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; height: 40px; padding: 0 12px;">
                            <span style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; white-space: nowrap;">📅 التاريخ:</span>
                            <input type="date" id="voucher-date" style="background: transparent !important; border: none !important; color: #fff; font-weight: bold; padding: 0; margin: 0; height: 100%; width: 130px; outline: none !important; box-shadow: none !important; font-size: 0.9rem; -webkit-appearance: none; appearance: none;">
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

                        <div style="flex: 2; min-width: 250px;">
                            <label id="main-account-label" style="color: #ef4444 !important; font-size: 1.2rem; margin-bottom: 5px; display: block; font-weight: bold; transition: color 0.3s; white-space: nowrap;">من حساب (صرف) <span class="animated-arrow-left">←</span></label>
                            <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative; display: flex;">
                                <input type="hidden" id="main-account">
                                <input type="text" autocomplete="off" class="form-control form-select" id="main-account-search" placeholder="ابحث عن حساب الصرف (خزينة/بنك)..." onfocus="showAccountDropdown('main-account')" onblur="hideAccountDropdownDelayed('main-account')" oninput="filterAccountDropdown('main-account')" style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); flex: 1; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                <button type="button" onclick="openLedgerForMainAccount()" title="دفتر الأستاذ العام" style="background: linear-gradient(135deg, rgba(52,211,153,0.15), rgba(16,185,129,0.25)); border: 1px solid var(--border-color); border-right: none; color: #34d399; padding: 0 10px; border-top-left-radius: 8px; border-bottom-left-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; height: auto; font-size: 0.75rem; font-weight: bold; white-space: nowrap; box-shadow: inset 0 1px 3px rgba(255,255,255,0.1);" onmouseover="this.style.background='linear-gradient(135deg, rgba(52,211,153,0.3), rgba(16,185,129,0.5))'; this.style.color='#fff';" onmouseout="this.style.background='linear-gradient(135deg, rgba(52,211,153,0.15), rgba(16,185,129,0.25))'; this.style.color='#34d399';"><i class="fa fa-book" style="margin-left: 4px;"></i> كشف حساب</button>
                                <div class="custom-dropdown-list" id="main-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                            </div>
                        </div>

                        <div style="flex: 1; min-width: 120px;">
                            <label style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block; white-space: nowrap;">العملة</label>
                            <select id="currency" class="form-input system-currency-select input-glow-focus hover-glow" onchange="typeof onCurrencyChange === 'function' ? onCurrencyChange() : (typeof updateCurrency === 'function' ? updateCurrency() : null);" style="border-radius: 10px; flex-grow: 1; min-width: 0; width: 100%; font-size: 1.1rem !important; font-weight: 800;">
                                <option value="SDG">جنيه (SDG)</option>
                                <option value="USD">دولار (USD)</option>
                                <option value="AED">درهم (AED)</option>
                            </select>
                        </div>
                        
                        <div style="flex: 1; min-width: 100px;">
                            <label style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block; white-space: nowrap;">التعادل</label>
                            <input type="number" autocomplete="off" id="exchange-rate" class="form-input" value="1.0" readonly style="font-weight: bold; font-family: monospace; text-align: center; cursor:pointer; background:rgba(16,185,129,0.1); color:#10b981; border-radius: 10px;" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=r; if(typeof calcTotal === 'function') calcTotal(); else if(typeof recalcAll === 'function') recalcAll();});">
                        </div>
                        
                        <div style="flex: 1; min-width: 150px;">
                            <label style="color: #10b981; font-size: 0.85rem; margin-bottom: 5px; display: block; font-weight: bold; white-space: nowrap;">💵 الإجمالي بالمحلي</label>
                            <input type="text" id="amount-local-hdr" class="form-input" value="0.00" readonly style="font-weight: 900; font-family: monospace; color: #10b981; background: rgba(255,255,255,0.05);">
                        </div>
                        
                        <div style="flex: 1; min-width: 150px;">
                            <label style="color: #ef4444; font-size: 0.85rem; margin-bottom: 5px; display: block; font-weight: bold; white-space: nowrap;">💵 الإجمالي بالأجنبي</label>
                            <input type="text" id="amount-foreign" class="form-input" value="0.00" readonly style="font-weight: 900; font-family: monospace; color: #ef4444; background: rgba(255,255,255,0.05);">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px; background: rgba(0,0,0,0.25); padding: 12px 15px; min-height: 48px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); margin-top: 12px;">
                        <label style="color: #fbbf24; font-size: 0.95rem; font-weight: bold; margin: 0; white-space: nowrap; display: flex; align-items: center; gap: 5px;">✍️ بيان حساب الصرف:</label>
                        <input type="text" autocomplete="off" id="voucher-desc" class="form-input" placeholder="اكتب البيان التفصيلي لحساب الصرف هنا (الذي يظهر في كشف الأستاذ العام)..." style="flex: 1; margin: 0; background: rgba(15,23,42,0.4) !important; border: 1px solid rgba(255,255,255,0.1) !important; padding: 8px 12px; height: 38px !important; font-size: 0.95rem; color: #fff !important; border-radius: 6px;">
                    </div>
                </div>


                <!-- ═══ حاوية الحسابات (Accounts Blocks Container) ═══ -->
                <div id="accounts-blocks-container" style="display: flex; flex-direction: column; gap: 20px; position: relative; z-index: 10;">
                    <!-- Blocks will be dynamically inserted here -->
                </div>

                <!-- ═══ زر إضافة حساب جديد ═══ -->
                <div style="display: flex; justify-content: center; margin-top: 20px; margin-bottom: 30px;">
                    <button type="button" onclick="addAccountBlock()" style="padding: 12px 30px; border-radius: 30px; background: rgba(16,185,129,0.15); color: #34d399; border: 1px dashed #34d399; font-weight: 900; font-size: 1.1rem; cursor: pointer; font-family: 'Tajawal', sans-serif; transition: all 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.background='rgba(16,185,129,0.3)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(16,185,129,0.15)'; this.style.transform='translateY(0)';">
                        ➕ إضافة حساب مستلم / دافع آخر
                    </button>
                </div>

                <!-- ═══ قالب الكتلة المخفي (Hidden Template) ═══ -->
                <template id="account-block-template">
                    <div class="account-block glass-container glass-green" data-block-id="{ID}" style="position: relative; padding: 15px; border-radius: 12px; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; padding-bottom: 0px;">
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 1.2rem; font-weight: 800; color: #34d399;">
                                <span>📋</span> <span class="block-label">إلى حساب (مستلم)</span> <span class="animated-arrow-right" style="color: #34d399;">→</span>
                            </div>
                            <button type="button" class="btn-remove-block" data-action="remove-block" data-block="{ID}" style="background: rgba(239,68,68,0.15); border: none; color: #ef4444; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; transition: all 0.2s;" title="حذف هذا الحساب">✖</button>
                        </div>

                        <!-- Row 1: Account, Amount, and Method Toggles -->
                        <div style="display: flex; gap: 10px; align-items: stretch; flex-wrap: wrap; margin-bottom: 12px;">
                            <!-- Account -->
                            <div style="flex: 2; min-width: 250px; position: relative; display: flex; flex-direction: column;">
                                <div class="custom-dropdown-input-wrap" style="position: relative; flex: 1; display: flex;">
                                    <input type="hidden" id="account-{ID}-id" class="block-account-id">
                                    <input type="hidden" id="account-{ID}" class="block-account-name">
                                    <input type="text" autocomplete="off" class="form-input block-account-search" id="account-{ID}-search" placeholder="الحساب / الجهة المستفيدة..." onfocus="showAccountDropdown('account-{ID}')" onblur="hideAccountDropdownDelayed('account-{ID}')" oninput="filterAccountDropdown('account-{ID}')" style="font-size: 1.25rem; font-weight: bold; flex: 1; margin-bottom: 0; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <button type="button" onclick="openLedgerForBlockAccount('{ID}')" title="دفتر الأستاذ العام" style="background: linear-gradient(135deg, rgba(52,211,153,0.15), rgba(16,185,129,0.25)); border: 1px solid rgba(255,255,255,0.1); border-right: none; color: #34d399; padding: 0 10px; border-top-left-radius: 8px; border-bottom-left-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; height: auto; font-size: 0.75rem; font-weight: bold; white-space: nowrap; box-shadow: inset 0 1px 3px rgba(255,255,255,0.1);" onmouseover="this.style.background='linear-gradient(135deg, rgba(52,211,153,0.3), rgba(16,185,129,0.5))'; this.style.color='#fff';" onmouseout="this.style.background='linear-gradient(135deg, rgba(52,211,153,0.15), rgba(16,185,129,0.25))'; this.style.color='#34d399';"><i class="fa fa-book" style="margin-left: 4px;"></i> كشف حساب</button>
                                    <div class="custom-dropdown-list" id="account-{ID}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                                </div>
                            </div>
                            
                            <!-- Total Amount (auto-calculated or direct) -->
                            <div style="flex: 0.75; min-width: 110px; display: flex; flex-direction: column; position: relative;">
                                <input type="text" id="amount-{ID}" oninput="formatInput(this)" class="form-input block-total-amount" placeholder="المبلغ" style="font-weight: 900; font-family: monospace; font-size: 1.5rem; text-align: center; flex: 1; margin-bottom: 0; border-color: #fbbf24; box-shadow: 0 0 8px rgba(251,191,36,0.3); background: rgba(251,191,36,0.08); transition: all 0.4s ease; -webkit-text-stroke: 1px currentColor; letter-spacing: 1px;">
                            </div>
                            
                            <!-- Statement (بيان التحويل) -->
                            <div style="flex: 1.5; min-width: 150px; display: flex; flex-direction: column; position: relative;">
                                <input type="text" autocomplete="off" id="statement-{ID}" class="form-input" placeholder="بيان التحويل (اختياري)" style="font-size: 1rem; flex: 1; margin-bottom: 0; background: rgba(255,255,255,0.05); color: #fff;">
                            </div>
                            
                            <!-- Payment Toggles (No Cash) -->
                            <div style="display: flex; gap: 8px; flex: 1; min-width: 150px; align-items: stretch;">
                                <button type="button" id="pm-bank-{ID}" class="btn crystal-btn pay-toggle-btn block-btn-bank" data-action="toggle-pm" data-block="{ID}" data-method="bank"><span class="btn-icon">🏦</span> <span class="btn-text">تحويل</span></button>
                                <button type="button" id="pm-cheque-{ID}" class="btn crystal-btn pay-toggle-btn block-btn-cheque" data-action="toggle-pm" data-block="{ID}" data-method="cheque"><span class="btn-icon">✍️</span> <span class="btn-text">شيك</span></button>
                            </div>
                        </div>

                        <!-- Dynamic Details Sections -->
                        <!-- Bank Details (supports multiple transfers) -->
                        <div id="bank-details-{ID}" class="block-bank-details" style="display: none; background: rgba(110, 125, 115, 0.25); border: 1.5px solid rgba(110, 125, 115, 0.6); border-radius: 8px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease;">
                            <div class="bank-rows-container" id="bank-rows-{ID}">
                                <div class="bank-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div style="width: 80px; font-weight: bold; color: #22d3ee; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">🏦 بنكي</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" oninput="if(window.formatWhileTyping) window.formatWhileTyping(this);" style="margin-bottom: 0; font-family: monospace; font-weight: 900; font-size: 1.25rem; text-align: center; -webkit-text-stroke: 0.5px currentColor;">
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
                                    <button type="button" data-action="add-bank-row" data-block="{ID}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(6, 182, 212, 0.2); border: 1px solid #22d3ee; color: #22d3ee; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة تحويلة أخرى">➕</button>
                                </div>
                            </div>
                        </div>

                        <!-- Cheque Details (supports multiple cheques) -->
                        <div id="cheque-details-{ID}" class="block-cheque-details" style="display: none; background: rgba(147, 51, 234, 0.22); border: 1.5px solid rgba(147, 51, 234, 0.65); border-radius: 8px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease; box-shadow: inset 0 0 15px rgba(147, 51, 234, 0.1);">
                            <div class="cheque-rows-container" id="cheque-rows-{ID}">
                                <div class="cheque-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div style="width: 80px; font-weight: bold; color: #d8b4fe; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">✍️ شيك</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" oninput="if(window.formatWhileTyping) window.formatWhileTyping(this);" style="margin-bottom: 0; font-family: monospace; font-weight: 900; font-size: 1.25rem; text-align: center; -webkit-text-stroke: 0.5px currentColor;">
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
                                    <button type="button" data-action="add-cheque-row" data-block="{ID}" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(139,92,246,0.2); border: 1px solid #c084fc; color: #c084fc; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;" title="إضافة شيك آخر">➕</button>
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
                
                
                
                /* Enhanced Typography & UI overrides - V2 */
                body, .form-input, .btn, .context-menu-item, p, span, div {
                    font-size: 1.2rem;
                }
                h3 { font-size: 1.4rem !important; }
                .form-label, .block-label { font-size: 1.3rem !important; font-weight: bold; }
                
                /* Yellow Names */
                .block-account-search, #main-account-search, .block-bank-name-input, .block-cheque-bank-input {
                    font-size: 1.4rem !important;
                    font-weight: 900 !important;
                    color: #fbbf24 !important;
                }
                
                /* Large Totals & Rates */
                #amount-local-hdr, #amount-foreign, #exchange-rate {
                    font-size: 2.4rem !important;
                    font-weight: 900 !important;
                }
                
                /* Large Amounts in blocks */
                .block-total-amount, .block-bank-amount, .block-cheque-amount {
                    font-size: 2.4rem !important;
                    font-weight: 900 !important;
                }
                
                /* Large Transfer & Cheque Numbers */
                .block-transfer-no-input, .block-cheque-no-input {
                    font-size: 1.9rem !important;
                    font-weight: bold !important;
                    letter-spacing: 2px !important;
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
    <script defer src="js/app.js?v=<?= time() ?>"></script>
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
</body>
</html>











