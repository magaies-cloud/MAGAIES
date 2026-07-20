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
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
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

        /* Glassmorphism Containers */
        .glass-container {
            padding: 15px 22px 22px 22px;
            border-radius: 16px;
            margin-bottom: 25px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.02);
        }

        .glass-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px 0 rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.04);
        }

        #secondary-table-container {
            padding-top: 4px !important;
        }

        #secondary-table-container th {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }

        .glass-red {
            background: rgba(239, 68, 68, 0.1) !important;
            border-right: 5px solid rgba(239, 68, 68, 0.45) !important;
        }
        .glass-red:hover {
            background: rgba(239, 68, 68, 0.14) !important;
            border-right-color: rgba(239, 68, 68, 0.8) !important;
            box-shadow: 0 16px 40px 0 rgba(239, 68, 68, 0.1) !important;
        }

        .glass-green {
            background: rgba(16, 185, 129, 0.1) !important;
            border-right: 5px solid rgba(16, 185, 129, 0.45) !important;
        }
        .glass-green:hover {
            background: rgba(16, 185, 129, 0.14) !important;
            border-right-color: rgba(16, 185, 129, 0.8) !important;
            box-shadow: 0 16px 40px 0 rgba(16, 185, 129, 0.1) !important;
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
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; padding: 10px 12px; border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; transition: all 0.2s;
        }
        .form-input:focus, .form-select:focus { border-color: #f97316; background: rgba(0,0,0,0.4); outline: none; }
        
        .form-section-title { 
            font-size: 1.1rem; font-weight: 700; color: #f97316; 
            margin-bottom: 15px; display: flex; align-items: center; gap: 8px; 
            border-bottom: 1px solid rgba(249,115,22,0.2); padding-bottom: 10px; 
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

        /* Crystal Buttons */
        .crystal-btn {
            flex: 1; padding: 15px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); color: #94a3b8;
            font-size: 1.3rem; font-weight: 900; font-family: 'Tajawal', sans-serif; cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .crystal-btn::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
            transform: skewX(-20deg); transition: 0.7s;
        }
        .crystal-btn:hover::before { left: 150%; }
        .crystal-btn:hover { transform: translateY(-3px); background: rgba(255,255,255,0.08); }
        .crystal-btn.active-payment {
            background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.5); color: #fca5a5;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4), inset 0 0 15px rgba(239, 68, 68, 0.2);
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.8); transform: translateY(-2px) scale(1.02);
        }
        .crystal-btn.active-receipt {
            background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.5); color: #6ee7b7;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4), inset 0 0 15px rgba(16, 185, 129, 0.2);
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.8); transform: translateY(-2px) scale(1.02);
        }

        /* Pay Toggle Button Active (Glowing) States */
        .pay-toggle-btn.active.block-btn-cash {
            background: rgba(251, 191, 36, 0.18) !important;
            border-color: rgba(251, 191, 36, 0.6) !important;
            color: #fbbf24 !important;
            box-shadow: 0 0 12px rgba(251, 191, 36, 0.3) !important;
            transform: scale(1.03);
        }
        .pay-toggle-btn.active.block-btn-bank {
            background: rgba(59, 130, 246, 0.18) !important;
            border-color: rgba(96, 165, 250, 0.6) !important;
            color: #60a5fa !important;
            box-shadow: 0 0 12px rgba(96, 165, 250, 0.3) !important;
            transform: scale(1.03);
        }
        .pay-toggle-btn.active.block-btn-cheque {
            background: rgba(139, 92, 246, 0.18) !important;
            border-color: rgba(192, 132, 252, 0.6) !important;
            color: #c084fc !important;
            box-shadow: 0 0 12px rgba(192, 132, 252, 0.3) !important;
            transform: scale(1.03);
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
            background: rgba(0,0,0,0.4) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            color: #94a3b8 !important;
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
            background: linear-gradient(135deg, #ef4444, #b91c1c) !important;
            border: 1px solid rgba(239, 68, 68, 0.8) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5), 0 0 15px rgba(239, 68, 68, 0.3), inset 0 0 8px rgba(255,255,255,0.2) !important;
            font-weight: 900 !important;
            transform: translateY(-4px) scale(1.04) !important;
            animation: activePulse 2.5s infinite ease-in-out !important;
        }
        .voucher-type-btn.active-receipt {
            background: linear-gradient(135deg, #10b981, #047857) !important;
            border: 1px solid rgba(16, 185, 129, 0.8) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5), 0 0 15px rgba(16, 185, 129, 0.3), inset 0 0 8px rgba(255,255,255,0.2) !important;
            font-weight: 900 !important;
            transform: translateY(-4px) scale(1.04) !important;
            animation: activePulseGreen 2.5s infinite ease-in-out !important;
        }

        /* Pulsing active buttons */
        @keyframes activePulse {
            0% { box-shadow: 0 8px 20px rgba(239, 68, 68, 0.45), 0 0 10px rgba(239, 68, 68, 0.25); border-color: rgba(239, 68, 68, 0.7); }
            50% { box-shadow: 0 12px 30px rgba(239, 68, 68, 0.75), 0 0 20px rgba(239, 68, 68, 0.5); border-color: rgba(255, 255, 255, 0.9); }
            100% { box-shadow: 0 8px 20px rgba(239, 68, 68, 0.45), 0 0 10px rgba(239, 68, 68, 0.25); border-color: rgba(239, 68, 68, 0.7); }
        }
        @keyframes activePulseGreen {
            0% { box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45), 0 0 10px rgba(16, 185, 129, 0.25); border-color: rgba(16, 185, 129, 0.7); }
            50% { box-shadow: 0 12px 30px rgba(16, 185, 129, 0.75), 0 0 20px rgba(16, 185, 129, 0.5); border-color: rgba(255, 255, 255, 0.9); }
            100% { box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45), 0 0 10px rgba(16, 185, 129, 0.25); border-color: rgba(16, 185, 129, 0.7); }
        }

        /* Heartbeat for active state circles */
        @keyframes heartBeatRed {
            0% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            20% { transform: scale(1.4); background-color: #ff8787; box-shadow: 0 0 15px #ff8787; }
            40% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            60% { transform: scale(1.4); background-color: #ef4444; box-shadow: 0 0 15px #ef4444; }
            80% { transform: scale(1.2); background-color: #fca5a5; box-shadow: 0 0 12px #fca5a5; }
            100% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
        }
        @keyframes heartBeatGreen {
            0% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            20% { transform: scale(1.4); background-color: #a7f3d0; box-shadow: 0 0 15px #a7f3d0; }
            40% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
            60% { transform: scale(1.4); background-color: #10b981; box-shadow: 0 0 15px #10b981; }
            80% { transform: scale(1.2); background-color: #6ee7b7; box-shadow: 0 0 12px #6ee7b7; }
            100% { transform: scale(1); background-color: #ffffff; box-shadow: 0 0 10px #ffffff; }
        }

        .voucher-type-btn.active-payment span {
            animation: heartBeatRed 1.2s infinite ease-in-out !important;
        }
        .voucher-type-btn.active-receipt span {
            animation: heartBeatGreen 1.2s infinite ease-in-out !important;
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
                <div id="payment-methods-row" class="wave-bg" style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px; background: linear-gradient(45deg, #b91c1c, #ef4444, #991b1b, #dc2626); padding: 12px 15px; border-radius: 12px; border: 1px solid #f87171; flex-wrap: wrap; transition: all 0.4s ease; box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);">
                    
                    <!-- Title & Toggles -->
                    <div style="display: flex; align-items: center; gap: 15px; flex: none; flex-wrap: wrap;">
                        <div id="main-party-title" style="display: flex; align-items: center; gap: 8px; font-size: 1.1rem; font-weight: 700; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            <span>👤</span> بيان الطرف الرئيسي
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button id="btn-payment" class="voucher-type-btn active-payment" onclick="setVoucherType('payment')" style="padding: 8px 16px; font-size: 1rem; border-radius: 8px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Tajawal', sans-serif; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                                <span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; display: inline-block; box-shadow: 0 0 10px #ef4444;"></span> صرف مجمع
                            </button>
                            <button id="btn-receipt" class="voucher-type-btn" onclick="setVoucherType('receipt')" style="padding: 8px 16px; font-size: 1rem; border-radius: 8px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Tajawal', sans-serif; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                                <span style="width: 12px; height: 12px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 10px #10b981;"></span> استلام مجمع
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
                            <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                <input type="hidden" id="main-account">
                                <input type="text" autocomplete="off" class="form-control form-select" id="main-account-search" placeholder="ابحث عن حساب الصرف (خزينة/بنك)..." onfocus="showAccountDropdown('main-account')" onblur="hideAccountDropdownDelayed('main-account')" oninput="filterAccountDropdown('main-account')" style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                                <div class="custom-dropdown-list" id="main-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                            </div>
                        </div>

                        <div style="flex: 1; min-width: 120px;">
                            <label style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block; white-space: nowrap;">العملة</label>
                            <select id="currency" class="form-select" onchange="onCurrencyChange()">
                                <option value="SDG">جنيه (SDG)</option>
                                <option value="USD">دولار (USD)</option>
                                <option value="AED">درهم (AED)</option>
                            </select>
                        </div>
                        
                        <div style="flex: 1; min-width: 100px;">
                            <label style="color: #fbbf24; font-size: 0.85rem; margin-bottom: 5px; display: block; white-space: nowrap;">التعادل</label>
                            <input type="text" autocomplete="off" id="exchange-rate" class="form-input" value="1.0" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); typeof calcTotal === 'function' ? calcTotal() : null;" style="font-weight: bold; font-family: monospace;">
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
                                    <input type="text" autocomplete="off" class="form-input block-account-search" id="account-{ID}-search" placeholder="الحساب / الجهة المستفيدة..." onfocus="showAccountDropdown('account-{ID}')" onblur="hideAccountDropdownDelayed('account-{ID}')" oninput="filterAccountDropdown('account-{ID}')" style="font-size: 1rem; flex: 1; margin-bottom: 0;">
                                    <div class="custom-dropdown-list" id="account-{ID}-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 5px; z-index: 9999;"></div>
                                </div>
                            </div>
                            
                            <!-- Total Amount (auto-calculated) -->
                            <div style="flex: 1; min-width: 150px; display: flex; flex-direction: column; position: relative;">
                                <input type="text" readonly id="amount-{ID}" class="form-input block-total-amount" placeholder="0.00" style="font-weight: 900; font-family: monospace; font-size: 1.15rem; text-align: center; flex: 1; margin-bottom: 0; border-color: #fbbf24; box-shadow: 0 0 8px rgba(251,191,36,0.3); background: rgba(251,191,36,0.08); cursor: default; transition: all 0.4s ease;">
                            </div>
                            
                            <!-- Payment Toggles -->
                            <div style="display: flex; gap: 5px; flex: 1.5; min-width: 240px;">
                                <button type="button" id="pm-cash-{ID}" class="pay-toggle-btn block-btn-cash" data-action="toggle-pm" data-block="{ID}" data-method="cash" style="flex: 1; padding: 0 10px; border-radius: 8px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 4px; font-family: 'Tajawal', sans-serif; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; height: 100%;">💵 نقدي</button>
                                <button type="button" id="pm-bank-{ID}" class="pay-toggle-btn block-btn-bank" data-action="toggle-pm" data-block="{ID}" data-method="bank" style="flex: 1; padding: 0 10px; border-radius: 8px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 4px; font-family: 'Tajawal', sans-serif; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; height: 100%;">🏦 تحويل</button>
                                <button type="button" id="pm-cheque-{ID}" class="pay-toggle-btn block-btn-cheque" data-action="toggle-pm" data-block="{ID}" data-method="cheque" style="flex: 1; padding: 0 10px; border-radius: 8px; font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 4px; font-family: 'Tajawal', sans-serif; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; height: 100%;">✍️ شيك</button>
                            </div>
                        </div>

                        <!-- Dynamic Details Sections (aligned amounts) -->
                        <!-- Cash Details -->
                        <div id="cash-details-{ID}" class="block-cash-details" style="display: none; background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.3); border-radius: 8px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease;">
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <div style="width: 80px; font-weight: bold; color: #fbbf24; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">💵 نقدي</div>
                                <div style="width: 150px; flex-shrink: 0;">
                                    <input type="text" autocomplete="off" id="cash-amount-{ID}" class="form-input block-cash-amount block-sub-amount" data-block-id="{ID}" placeholder="مبلغ الكاش" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
                                </div>
                                <div style="flex: 1; min-width: 180px;">
                                    <select id="cash-safe-{ID}" class="form-input block-cash-safe" style="margin-bottom: 0;">
                                        <option value="">-- اختر الخزنة --</option>
                                    </select>
                                </div>
                                <div style="flex: 1.5; min-width: 180px;">
                                    <input type="text" autocomplete="off" class="form-input block-cash-desc-input" placeholder="بيان الكاش" style="margin-bottom: 0;">
                                </div>
                            </div>
                        </div>

                        <!-- Bank Details (supports multiple transfers) -->
                        <div id="bank-details-{ID}" class="block-bank-details" style="display: none; background: rgba(14, 116, 144, 0.18); border: 1.5px solid rgba(14, 116, 144, 0.45); border-radius: 8px; padding: 10px; margin-bottom: 8px; animation: rowSlideIn 0.3s ease;">
                            <div class="bank-rows-container" id="bank-rows-{ID}">
                                <div class="bank-row" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px;">
                                    <div style="width: 80px; font-weight: bold; color: #22d3ee; display: flex; align-items: center; gap: 5px; flex-shrink: 0;">🏦 تحويل</div>
                                    <div style="width: 150px; flex-shrink: 0;">
                                        <input type="text" autocomplete="off" class="form-input block-bank-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
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
                                        <input type="text" autocomplete="off" class="form-input block-cheque-amount block-sub-amount" data-block-id="{ID}" placeholder="المبلغ" style="margin-bottom: 0; font-family: monospace; font-weight: bold; text-align: center;">
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
                <div class="footer-actions" style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; width: 100%;">
                    <button type="button" class="btn crystal-btn btn-exit" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; font-family: 'Tajawal', sans-serif;">🚪 خروج</button>
                    <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()" style="background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; color: #7dd3fc; font-family: 'Tajawal', sans-serif;">✨ مستند جديد</button>
                    <button type="button" class="btn crystal-btn btn-print" onclick="directPrint()" style="background: rgba(139, 92, 246, 0.15); border: 1px solid #8b5cf6; color: #c084fc; font-family: 'Tajawal', sans-serif;">🖨️ طباعة</button>
                    <button type="button" class="btn crystal-btn btn-print-preview" onclick="printVoucher()" style="background: rgba(14, 165, 233, 0.15); border: 1px solid #0ea5e9; color: #7dd3fc; font-family: 'Tajawal', sans-serif;">👁️ معاينة</button>
                    <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()" style="background: rgba(244, 63, 94, 0.15); border: 1px solid #f43f5e; color: #fda4af; font-family: 'Tajawal', sans-serif;">🗑️ حذف المستند</button>
                    <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="saveVoucher()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; font-weight: bold; padding: 10px 25px !important; border-radius: 20px; border: none; box-shadow: 0 0 15px rgba(16,185,129,0.4); font-family: 'Tajawal', sans-serif;">✅ حفظ واعتماد السند</button>
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

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=1783054968.65812"></script>
    <script defer src="js/multi-cash-payment.js"></script>
</body>
</html>

