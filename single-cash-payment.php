<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
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
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css?v=1788466095950">
    <link rel="stylesheet" href="css/components.css?v=1788466095949">
    <link rel="stylesheet" href="css/sales-invoice.css">
    
    <style>
        html { font-size: 14.5px; } /* Slightly reduce overall scale to make everything beautifully compact */
        /* -------------------------------------------
           ✨ ADVANCED UI / UX VISUAL EFFECTS ✨
           ------------------------------------------- */
        /* Entrance Animations */
        @keyframes gl-fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes gl-slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes gl-slideFromLeft { from { opacity: 0; transform: translateX(-40px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes gl-scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        /* Apply Entrance Animations */
        body > .premium-header { animation: gl-slideFromLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards !important; }
        .main-panel { animation: gl-scaleIn 0.4s ease-out 0.1s both; }
        .side-panel { animation: gl-slideUp 0.4s ease-out 0.2s both; }
        .sticky-footer { animation: gl-slideUp 0.4s ease-out 0.3s both; }
        
        /* Glassmorphism & Blur */
        .main-panel, .side-panel, .premium-header, .sticky-footer {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        
        /* Fluid Transitions & State Changes */
        .form-input, .btn, .pm-tab-content, .choices__inner {
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1) !important;
        }
        
        /* Hover Effects & Neumorphism / Glow */
        .form-input:hover, .choices__inner:hover, textarea.form-input:hover {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3) !important;
        }
        .form-input:focus, .choices.is-focused .choices__inner {
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4) !important;
            border-color: #38bdf8 !important;
            transform: translateY(-2px);
        }
        
        /* Amount Field Premium Glow */
        #amount-local:focus {
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.6) !important;
            border-color: #ef4444 !important;
            transform: scale(1.02);
        }

        /* Micro-interactions (Buttons & Payment Tabs) */
        .btn:hover, .pm-tab input:checked + .pm-tab-content {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
        }
        .btn:active {
            transform: scale(0.95);
        }

        /* Focus Zones Subtle Glow & Blur */
        .focus-zone {
            transition: filter 0.5s ease, opacity 0.5s ease, transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.5s ease !important;
            border-radius: 16px;
            backdrop-filter: blur(8px);
        }
        .interactive-columns:focus-within .focus-zone:not(:focus-within) {
            filter: grayscale(30%) blur(0.5px) brightness(0.85);
            transform: scale(0.99);
            opacity: 0.9;
        }
        .interactive-columns:focus-within .focus-zone:focus-within {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(-4px);
            z-index: 10;
        }

        /* Continuous Shine Animation for Amount */
        @keyframes subtleShine {
            0% { text-shadow: 0 0 10px rgba(239,68,68,0.3); box-shadow: 0 0 15px rgba(239,68,68,0.1); }
            50% { text-shadow: 0 0 20px rgba(239,68,68,0.8), 0 0 30px rgba(239,68,68,0.4); box-shadow: 0 0 25px rgba(239,68,68,0.3); }
            100% { text-shadow: 0 0 10px rgba(239,68,68,0.3); box-shadow: 0 0 15px rgba(239,68,68,0.1); }
        }
        #amount-local { animation: subtleShine 3s infinite alternate; }

        /* Typography Scale for Clarity */
        .label { font-size: 1.3rem !important; font-weight: 900 !important; color: #f8fafc !important; text-shadow: 0 2px 4px rgba(0,0,0,0.9) !important; }
        .form-input, textarea.form-input { font-size: 1.25rem !important; font-weight: 800 !important; color: #ffffff !important; background: #0f172a !important; border: 1px solid #38bdf8 !important; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5); text-shadow: none !important; }
        .form-input:not(#amount-local):not(#amount-foreign) { height: 48px !important; }
        textarea.form-input { height: auto !important; min-height: 48px; }
        #to-statement, #from-statement { font-size: 0.95rem !important; }
        #amount-local { 
            font-size: 3.2rem !important; 
            font-weight: 900 !important; 
            color: #b91c1c !important; /* Medium elegant red */
            background: #d6d3d1 !important; /* Warm, calm beige/gray, not bright white */
            border: 2px solid #991b1b !important;
            text-shadow: none !important;
            box-shadow: inset 0 3px 8px rgba(0, 0, 0, 0.15) !important;
            line-height: 1 !important;
            padding-top: 5px !important;
        }
        #amount-local:focus {
            border-color: #7f1d1d !important;
            background: #e7e5e4 !important; /* Slightly lighter warm gray on focus, still not white */
            box-shadow: 0 0 15px rgba(127, 29, 29, 0.3), inset 0 3px 8px rgba(0, 0, 0, 0.1) !important;
        }
        
        .choices { margin-bottom: 0 !important; }
        .choices__inner { font-size: 1.1rem !important; font-weight: 800 !important; min-height: 48px !important; padding: 6px 12px !important; border-radius: 8px !important; background: #0f172a !important; border: 1px solid #38bdf8 !important; color: #ffffff !important; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5); text-shadow: none !important; }
        .choices__input { color: #ffffff !important; font-size: 1.1rem !important; font-weight: 700 !important; }
        .choices__list--dropdown { background: #1e293b !important; color: #fff !important; border: 1px solid rgba(255,255,255,0.1) !important; z-index: 1000 !important; }
        .choices__list--dropdown .choices__item { font-size: 1.1rem !important; font-weight: 500 !important; }
        .choices__list--dropdown .choices__item--selectable.is-highlighted { background: #38bdf8 !important; color: #fff !important; }
        /* Center Screen Premium Toast & Alert System */
        .toast-container {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 1000000 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 15px !important;
            pointer-events: none !important;
            width: auto !important;
            max-width: 92vw !important;
        }

        .toast {
            padding: 18px 32px !important;
            border-radius: 20px !important;
            font-weight: 800 !important;
            font-size: 1.25rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 14px !important;
            pointer-events: auto !important;
            backdrop-filter: blur(25px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(25px) saturate(180%) !important;
            box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.8), 0 0 35px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.25) !important;
            animation: toastCenterIn 0.38s cubic-bezier(0.175, 0.885, 0.32, 1.275) both !important;
            font-family: 'Cairo', 'Tajawal', system-ui, sans-serif !important;
            min-width: 320px !important;
            max-width: 650px !important;
            text-align: center !important;
            direction: rtl !important;
            letter-spacing: 0.3px !important;
            cursor: pointer !important;
            user-select: none !important;
            transition: transform 0.2s ease, opacity 0.3s ease !important;
        }

        .toast:hover {
            transform: scale(1.02) !important;
        }

        .toast .toast-icon {
            font-size: 1.6rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4)) !important;
        }

        .toast .toast-text {
            flex: 1 !important;
            line-height: 1.5 !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6) !important;
        }

        .toast.error, .toast.danger {
            background: linear-gradient(145deg, rgba(38, 8, 16, 0.96), rgba(80, 15, 28, 0.94)) !important;
            border: 1.5px solid rgba(248, 113, 113, 0.65) !important;
            color: #ffffff !important;
            box-shadow: 0 20px 60px rgba(239, 68, 68, 0.35), 0 0 30px rgba(239, 68, 68, 0.2), inset 0 0 15px rgba(239, 68, 68, 0.15) !important;
        }

        .toast.warning {
            background: linear-gradient(145deg, rgba(40, 24, 6, 0.96), rgba(85, 48, 12, 0.94)) !important;
            border: 1.5px solid rgba(251, 191, 36, 0.65) !important;
            color: #ffffff !important;
            box-shadow: 0 20px 60px rgba(245, 158, 11, 0.35), 0 0 30px rgba(245, 158, 11, 0.2), inset 0 0 15px rgba(245, 158, 11, 0.15) !important;
        }

        .toast.success {
            background: linear-gradient(145deg, rgba(6, 36, 24, 0.96), rgba(12, 70, 45, 0.94)) !important;
            border: 1.5px solid rgba(52, 211, 153, 0.65) !important;
            color: #ffffff !important;
            box-shadow: 0 20px 60px rgba(16, 185, 129, 0.35), 0 0 30px rgba(16, 185, 129, 0.2), inset 0 0 15px rgba(16, 185, 129, 0.15) !important;
        }

        .toast.info {
            background: linear-gradient(145deg, rgba(12, 30, 52, 0.96), rgba(20, 60, 100, 0.94)) !important;
            border: 1.5px solid rgba(56, 189, 248, 0.65) !important;
            color: #ffffff !important;
            box-shadow: 0 20px 60px rgba(56, 189, 248, 0.35), 0 0 30px rgba(56, 189, 248, 0.2), inset 0 0 15px rgba(56, 189, 248, 0.15) !important;
        }

        .toast.hide {
            animation: toastCenterOut 0.32s cubic-bezier(0.4, 0, 1, 1) forwards !important;
        }

        @keyframes toastCenterIn {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
                filter: blur(10px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
                filter: blur(0);
            }
        }

        @keyframes toastCenterOut {
            0% {
                opacity: 1;
                transform: scale(1) translateY(0);
                filter: blur(0);
            }
            100% {
                opacity: 0;
                transform: scale(0.85) translateY(-15px);
                filter: blur(8px);
            }
        }

        .top-action-bar { background: rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.2); }
        .action-tab.active { background: #ef4444; color: #fff; border-color: #ef4444; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .form-input {
            width: 100%; border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 12px; border-radius: 8px;
            font-family: inherit; transition: all 0.2s;
        }
        .form-input:focus { border-color: #ef4444; background: rgba(0,0,0,0.6) !important; outline: none; }
        .form-input.amount { color: #f87171 !important; font-weight: 900 !important; font-family: monospace; }
        
        .form-section-title { 
            font-size: 1.3rem; font-weight: 800; color: #f87171; 
            border-bottom: 2px solid rgba(239, 68, 68, 0.2);
            padding-bottom: 5px; margin-bottom: 15px; 
            display: flex; align-items: center; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .balance-summary {
            background: rgba(249, 115, 22, 0.1);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 8px;
            display: flex;
            padding: 8px 15px;
            gap: 15px;
            font-size: 0.85rem;
            color: #fdba74;
            float: left; /* Left align for RTL */
            margin-top: -5px;
        }
        .bal-item strong { color: #f97316; margin-right: 5px; font-family: monospace; font-size: 1.05rem; }
        .hover-animate { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-animate:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249,115,22,0.2) !important; }

        /* Custom Radio Buttons */
        .radio-group {
            display: flex; gap: 20px; align-items: center; padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 15px;
        }
        .radio-label {
            display: flex; align-items: center; gap: 8px; cursor: pointer;
            font-weight: 600; color: var(--text-primary);
        }
        .radio-label input[type="radio"] {
            width: 18px; height: 18px; cursor: pointer;
            accent-color: #ef4444;
        }

        .dynamic-section { display: none; background: rgba(0,0,0,0.1); padding: 15px; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.1); margin-bottom: 15px; }
        .dynamic-section.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .checkbox-label {
            display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; margin-top: 10px;
        }
        .checkbox-label input { width: 16px; height: 16px; accent-color: #ef4444; }

        /* Premium Header Styling */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .premium-header {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(249, 115, 22, 0.15), rgba(220, 38, 38, 0.15), rgba(234, 88, 12, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 12px 20px;
            display: flex; justify-content: space-between; align-items: center;
            height: 110px; box-sizing: border-box;
            margin-bottom: 0px; width: 100%;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.2), inset 0 -1px 1px rgba(0,0,0,0.3);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1), animateGradient 12s ease infinite;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 40%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.04) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: shimmer 8s cubic-bezier(0.25, 0.8, 0.25, 1) infinite;
            pointer-events: none;
        }

        @keyframes animateGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes shimmer {
            0% { left: -100%; }
            25% { left: 200%; }
            100% { left: 200%; }
        }

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #fca5a5; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 {
            margin: 0; font-size: 1.6rem; font-weight: 900;
            background: linear-gradient(to right, #fff, #fca5a5);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }
        .header-controls { display: flex; align-items: center; gap: 15px; }
        
        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
            width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;
        }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .sub-header-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 15px; 
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px; margin-bottom: 15px; margin-top: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .header-group { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        
        .crystal-pill {
            display: flex; align-items: center; gap: 10px;
            height: 48px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 0 12px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.5);
        }
        .title-crystal { border-color: rgba(239, 68, 68, 0.6); box-shadow: 0 0 20px rgba(239, 68, 68, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); }
        .date-crystal { border-color: rgba(56, 189, 248, 0.6); box-shadow: 0 0 20px rgba(56, 189, 248, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); }
        .bal-crystal { border-color: rgba(249, 115, 22, 0.6); box-shadow: 0 0 20px rgba(249, 115, 22, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); gap: 15px; }
        .nav-crystal { border-color: rgba(16, 185, 129, 0.6); box-shadow: 0 0 20px rgba(16, 185, 129, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); padding: 0 8px; }
        
        .icon-box {
            width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .bg-gradient-red { background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; }
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
        .pill-text { font-size: 1.15rem; color: #ffffff; font-weight: 800; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .date-input { 
            background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); 
            color: #ffffff; border-radius: 6px; padding: 2px 10px;
            font-family: 'Inter', sans-serif; font-size: 1.05rem; font-weight: 700; 
            outline: none; cursor: pointer; height: 32px; line-height: 1; text-align: center;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); transition: all 0.2s;
        }
        .date-input:focus { border-color: #38bdf8; background: rgba(0,0,0,0.8); }
        .date-input::-webkit-calendar-picker-indicator { 
            filter: invert(1); opacity: 0.8; cursor: pointer; 
            margin-left: 8px; width: 16px; height: 16px; padding: 0;
        }

        /* Unused balance-container replaced by crystal-pill */
        .bal-card { display: flex; align-items: center; gap: 8px; }
        .bal-icon { font-size: 1.6rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
        .bal-info { display: flex; flex-direction: column; line-height: 1.1; }
        .bal-label { font-size: 0.8rem; color: #e2e8f0; font-weight: 700; }
        .bal-val { font-size: 1.25rem; color: #f97316; font-family: 'Inter', monospace; font-weight: 900; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        .cash-card .bal-val { color: #10b981; }
        .check-card .bal-val { color: #3b82f6; }
        .loan-card .bal-val { color: #f59e0b; }
        .bal-divider { width: 1px; height: 30px; background: rgba(255,255,255,0.1); }

        /* Unused nav-pill replaced by crystal-pill */
        .nav-btn {
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            border-radius: 8px; background: transparent; border: none; color: #94a3b8; cursor: pointer; transition: all 0.2s;
        }
        .nav-btn:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .nav-badge { display: flex; flex-direction: column; align-items: center; padding: 0 15px; min-width: 90px; }
        .badge-label { font-size: 0.7rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .badge-val { font-size: 1.2rem; color: #ef4444; font-family: 'Inter', monospace; font-weight: 800; text-shadow: 0 0 10px rgba(239, 68, 68, 0.4); }

        .hover-scale { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease; }
        .hover-scale:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 25px rgba(0,0,0,0.3); z-index: 10; }
        
        /* Entrance animation */
        .premium-header { animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

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
        
        .interactive-columns {
            display: grid; grid-template-columns: 1fr 1fr; gap: 30px;
        }

        @media (max-width: 900px) {
            .interactive-columns { grid-template-columns: 1fr; }
            .split-layout-wrapper.has-sidebar { flex-direction: column; }
            .split-layout-wrapper.has-sidebar .side-panel { width: 100%; transform: translateX(0) translateY(-20px); }
        }

        /* Crystalline Glassmorphic Transfer Arrow */
        @keyframes rotateRing {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes crystalShineSweep {
            0% { transform: translate(-150%, -150%) rotate(45deg); }
            100% { transform: translate(150%, 150%) rotate(45deg); }
        }

        .epic-arrow-container {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 20; 
            display: flex; align-items: center; justify-content: center; 
            width: 72px; height: 72px; 
            /* Premium Crystalline Background: high glassmorphic translucency with colorful light */
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(255, 255, 255, 0.02) 100%);
            background-size: 200% 200%;
            border-radius: 50%; 
            /* Sharp, highly reflective, thin double border effect */
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.6), 
                inset 0 4px 18px rgba(255, 255, 255, 0.35), 
                inset 0 -4px 18px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.2), inset 0 -1px 1px rgba(0,0,0,0.3);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1), animateGradient 12s ease infinite;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        .premium-header::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 40%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.04) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: shimmer 8s cubic-bezier(0.25, 0.8, 0.25, 1) infinite;
            pointer-events: none;
        }

        @keyframes animateGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes shimmer {
            0% { left: -100%; }
            25% { left: 200%; }
            100% { left: 200%; }
        }

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #fca5a5; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 {
            margin: 0; font-size: 1.6rem; font-weight: 900;
            background: linear-gradient(to right, #fff, #fca5a5);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }
        .header-controls { display: flex; align-items: center; gap: 15px; }
        
        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
            width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;
        }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .sub-header-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 15px; 
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px; margin-bottom: 15px; margin-top: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .header-group { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        
        .crystal-pill {
            display: flex; align-items: center; gap: 10px;
            height: 42px; /* Slimmer height */
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 0 12px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.5);
        }
        .title-crystal { border-color: rgba(239, 68, 68, 0.6); box-shadow: 0 0 20px rgba(239, 68, 68, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); }
        .date-crystal { border-color: rgba(56, 189, 248, 0.6); box-shadow: 0 0 20px rgba(56, 189, 248, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); }
        .bal-crystal { border-color: rgba(249, 115, 22, 0.6); box-shadow: 0 0 20px rgba(249, 115, 22, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); gap: 15px; }
        .nav-crystal { border-color: rgba(16, 185, 129, 0.6); box-shadow: 0 0 20px rgba(16, 185, 129, 0.3), inset 0 2px 10px rgba(255,255,255,0.1); padding: 0 8px; }
        
        .icon-box {
            width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .bg-gradient-red { background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; }
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
        .pill-text { font-size: 1.15rem; color: #ffffff; font-weight: 800; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .date-input { 
            background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); 
            color: #ffffff; border-radius: 6px; padding: 2px 10px;
            font-family: 'Inter', sans-serif; font-size: 1.05rem; font-weight: 700; 
            outline: none; cursor: pointer; height: 32px; line-height: 1; text-align: center;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); transition: all 0.2s;
        }
        .date-input:focus { border-color: #38bdf8; background: rgba(0,0,0,0.8); }
        .date-input::-webkit-calendar-picker-indicator { 
            filter: invert(1); opacity: 0.8; cursor: pointer; 
            margin-left: 8px; width: 16px; height: 16px; padding: 0;
        }

        /* Unused balance-container replaced by crystal-pill */
        .bal-card { display: flex; align-items: center; gap: 8px; }
        .bal-icon { font-size: 1.3rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
        .bal-info { display: flex; flex-direction: column; line-height: 1.1; }
        .bal-label { font-size: 0.8rem; color: #e2e8f0; font-weight: 700; }
        .bal-val { font-size: 1.25rem; color: #f97316; font-family: 'Inter', monospace; font-weight: 900; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
        .cash-card .bal-val { color: #10b981; }
        .check-card .bal-val { color: #3b82f6; }
        .loan-card .bal-val { color: #f59e0b; }
        .bal-divider { width: 1px; height: 30px; background: rgba(255,255,255,0.1); }

        /* Unused nav-pill replaced by crystal-pill */
        .nav-btn {
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            border-radius: 8px; background: transparent; border: none; color: #94a3b8; cursor: pointer; transition: all 0.2s;
        }
        .nav-btn:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .nav-badge { display: flex; flex-direction: column; align-items: center; padding: 0 15px; min-width: 90px; }
        .badge-label { font-size: 0.7rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .badge-val { font-size: 1.2rem; color: #ef4444; font-family: 'Inter', monospace; font-weight: 800; text-shadow: 0 0 10px rgba(239, 68, 68, 0.4); }

        .hover-scale { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow: 0.3s ease; }
        .hover-scale:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 12px 25px rgba(0,0,0,0.3); z-index: 10; }
        
        /* Entrance animation */
        .premium-header { animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

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
        
        .interactive-columns {
            display: grid; grid-template-columns: 1fr 1fr; gap: 30px;
        }

        @media (max-width: 900px) {
            .interactive-columns { grid-template-columns: 1fr; }
            .split-layout-wrapper.has-sidebar { flex-direction: column; }
            .split-layout-wrapper.has-sidebar .side-panel { width: 100%; transform: translateX(0) translateY(-20px); }
        }

        /* Crystalline Glassmorphic Transfer Arrow */
        @keyframes rotateRing {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes crystalShineSweep {
            0% { transform: translate(-150%, -150%) rotate(45deg); }
            100% { transform: translate(150%, 150%) rotate(45deg); }
        }

        .epic-arrow-container {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 20; 
            display: flex; align-items: center; justify-content: center; 
            width: 72px; height: 72px; 
            /* Premium Crystalline Background: high glassmorphic translucency with colorful light */
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(255, 255, 255, 0.02) 100%);
            background-size: 200% 200%;
            border-radius: 50%; 
            /* Sharp, highly reflective, thin double border effect */
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.6), 
                inset 0 4px 18px rgba(255, 255, 255, 0.35), 
                inset 0 -4px 18px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px) saturate(220%);
            -webkit-backdrop-filter: blur(25px) saturate(220%);
            cursor: pointer;
            overflow: hidden; /* To clip the shine reflection */
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        /* Outer Crystalline Orbit Ring with soft red gradient path */
        .epic-arrow-container::before {
            content: '';
            position: absolute;
            top: -8px; left: -8px; right: -8px; bottom: -8px;
            border-radius: 50%;
            border: 1.5px dashed rgba(255, 255, 255, 0.25);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.15));
            animation: rotateRing 15s linear infinite;
            pointer-events: none;
            z-index: -1;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Moving Diagonal Reflection Gleam */
        .epic-arrow-container::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0) 40%,
                rgba(255, 255, 255, 0.45) 50%,
                rgba(255, 255, 255, 0) 60%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: translate(-150%, -150%) rotate(45deg);
            transition: all 0.6s ease;
            pointer-events: none;
            /* Sweep every 6 seconds automatically */
            animation: crystalShineSweep 6s infinite ease-in-out;
        }
        
        .epic-arrow-container:hover {
            transform: translate(-50%, -50%) scale(1.18);
            border-color: rgba(255, 255, 255, 0.7);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.1) 50%, rgba(255, 255, 255, 0.05) 100%);
            box-shadow: 
                0 20px 45px rgba(0, 0, 0, 0.7), 
                inset 0 6px 24px rgba(255, 255, 255, 0.4), 
                inset 0 -6px 24px rgba(0, 0, 0, 0.2),
                0 0 30px rgba(255, 255, 255, 0.3);
        }
        
        .epic-arrow-container:hover::before {
            top: -12px; left: -12px; right: -12px; bottom: -12px;
            border-color: rgba(255, 255, 255, 0.65);
            animation-duration: 6s; /* Spin faster */
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(239, 68, 68, 0.3));
        }

        .epic-arrow-container:hover::after {
            /* Trigger a faster shine sweep on hover */
            animation: crystalShineSweep 1.5s infinite ease-in-out;
        }

        .epic-arrow-container:active {
            transform: translate(-50%, -50%) scale(0.9);
            transition: all 0.1s ease;
        }

        .transfer-arrow-anim {
            display: block;
            overflow: visible;
        }

        /* Continuous flowing chevrons (Right to Left / Green to Red) */
        .chev {
            opacity: 0.15;
            stroke-width: 3.2px;
            transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .chev-3 { animation: chevFlow3 1.5s infinite; }
        .chev-2 { animation: chevFlow2 1.5s infinite; }
        .chev-1 { animation: chevFlow1 1.5s infinite; }

        @keyframes chevFlow3 {
            0%, 100% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
            15% { 
                opacity: 1; 
                stroke-width: 4.8px;
                color: #ff0055; 
                filter: drop-shadow(0 0 6px #ff0055) drop-shadow(0 0 15px #ff0055) drop-shadow(0 0 30px #ff0055); 
                transform: scale(1.22) translateX(-3px); 
            }
            40% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
        }
        @keyframes chevFlow2 {
            0%, 20%, 100% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
            35% { 
                opacity: 1; 
                stroke-width: 4.8px;
                color: #ff0055; 
                filter: drop-shadow(0 0 8px #ff0055) drop-shadow(0 0 20px #ff0055) drop-shadow(0 0 35px #ff0055); 
                transform: scale(1.25) translateX(-3px); 
            }
            60% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
        }
        @keyframes chevFlow1 {
            0%, 40%, 100% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
            55% { 
                opacity: 1; 
                stroke-width: 4.8px;
                color: #ff0055; 
                filter: drop-shadow(0 0 6px #ff0055) drop-shadow(0 0 15px #ff0055) drop-shadow(0 0 30px #ff0055); 
                transform: scale(1.22) translateX(-3px); 
            }
            80% { opacity: 0.15; stroke-width: 3.2px; color: rgba(239, 68, 68, 0.25); transform: scale(0.9); }
        }

        /* Money Flow Conduit Styles */
        .money-flow-svg {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 10;
            overflow: visible;
        }

        .flow-path-bg {
            stroke-linecap: round;
            opacity: 0.7;
        }

        .flow-path-wave-1 {
            stroke-linecap: round;
            animation: waveLaser1 2.2s linear infinite;
            filter: drop-shadow(0 0 4px #ff0055) drop-shadow(0 0 10px #ff0055);
        }

        .flow-path-wave-2 {
            stroke-linecap: round;
            animation: waveLaser2 1.4s linear infinite;
            animation-delay: 0.5s;
            filter: drop-shadow(0 0 3px #ff0055) drop-shadow(0 0 8px #ff0055);
            opacity: 0.85;
        }

        @keyframes waveLaser1 {
            0% { stroke-dashoffset: 1000; }
            100% { stroke-dashoffset: 0; }
        }

        @keyframes waveLaser2 {
            0% { stroke-dashoffset: 1000; }
            100% { stroke-dashoffset: 0; }
        }

        .focus-zone {
            background: rgba(0, 0, 0, 0.2); padding: 25px; border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.5s cubic-bezier(0.25, 1, 0.2, 1);
            transform-origin: center;
        }
        
        /* When Main Form has focus somewhere, handle siblings */
        .interactive-columns:focus-within .focus-zone {
            opacity: 0.4; transform: scale(0.96) translateY(5px); filter: grayscale(50%);
        }
        /* Elevate and light up the zone that actually contains focus */
        .interactive-columns:focus-within .focus-zone:focus-within {
            opacity: 1; transform: scale(1.02) translateY(-5px); filter: grayscale(0%);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Specialized glowing borders for active zones */
        #zone-recipient:focus-within { border-bottom: 4px solid #ef4444; }
        #zone-sender:focus-within { border-bottom: 4px solid #38bdf8; }

        /* Payment Methods Tabs (Modern Apple-like Segmented Control) */
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

        /* -------------------------------------------
           💎 CRYSTAL FOOTER BUTTONS 💎
           ------------------------------------------- */
        .crystal-btn {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.95rem !important;
            padding: 7px 18px !important;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
            z-index: 1;
        }
        .crystal-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: crystalShine 6s infinite;
            z-index: -1;
            pointer-events: none;
        }
        @keyframes crystalShine { 0% { left: -100%; } 20% { left: 200%; } 100% { left: 200%; } }
        
        @keyframes tactileBuzz {
            0% { transform: translateY(-5px) scale(1.05) rotate(0deg); }
            25% { transform: translateY(-5px) scale(1.05) rotate(-0.5deg); }
            50% { transform: translateY(-5px) scale(1.05) rotate(0.5deg); }
            75% { transform: translateY(-5px) scale(1.05) rotate(-0.5deg); }
            100% { transform: translateY(-5px) scale(1.05) rotate(0deg); }
        }

        .crystal-btn:hover {
            animation: tactileBuzz 0.1s linear infinite;
            z-index: 10;
        }
        .crystal-btn:active {
            transform: translateY(2px) scale(0.98) !important;
        }
        
        .crystal-btn i, .crystal-btn span.icon, .crystal-btn .btn-icon, .crystal-btn span:first-child {
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-block;
        }
        .crystal-btn:hover i, .crystal-btn:hover span.icon, .crystal-btn:hover .btn-icon, .crystal-btn:hover span:first-child {
            transform: scale(1.2);
        }

        .btn-exit {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(185, 28, 28, 0.4));
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-exit:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.4), rgba(185, 28, 28, 0.6));
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.6), 0 0 30px rgba(239, 68, 68, 0.3), inset 0 0 15px rgba(239, 68, 68, 0.2) !important;
            border-color: #ef4444 !important;
            color: #fff;
        }

        .btn-new {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(3, 105, 161, 0.4));
            border: 1px solid rgba(56, 189, 248, 0.5);
            color: #bae6fd;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-new:hover {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.4), rgba(3, 105, 161, 0.6));
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.6), 0 0 30px rgba(56, 189, 248, 0.3), inset 0 0 15px rgba(56, 189, 248, 0.2) !important;
            border-color: #38bdf8 !important;
            color: #fff;
        }

        .btn-delete {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(194, 65, 12, 0.4));
            border: 1px solid rgba(249, 115, 22, 0.5);
            color: #fdba74;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.4), rgba(194, 65, 12, 0.6));
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.6), 0 0 30px rgba(249, 115, 22, 0.3), inset 0 0 15px rgba(249, 115, 22, 0.2) !important;
            border-color: #f97316 !important;
            color: #fff;
        }

        .btn-print {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(107, 33, 168, 0.4));
            border: 1px solid rgba(168, 85, 247, 0.5);
            color: #d8b4fe;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-print:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.4), rgba(107, 33, 168, 0.6));
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.6), 0 0 30px rgba(168, 85, 247, 0.3), inset 0 0 15px rgba(168, 85, 247, 0.2) !important;
            border-color: #a855f7 !important;
            color: #fff;
        }

        .btn-save-master {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.6), rgba(4, 120, 87, 0.8));
            border: 1px solid rgba(52, 211, 153, 0.8);
            color: #fff;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4), inset 0 2px 10px rgba(255,255,255,0.2);
            font-size: 1.1rem !important;
            padding: 8px 24px !important;
            animation: pulseGlow 2s infinite alternate;
        }
        .btn-save-master:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.8), rgba(4, 120, 87, 1));
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.8), 0 0 40px rgba(16, 185, 129, 0.4), inset 0 0 20px rgba(16, 185, 129, 0.3) !important;
            border-color: #10b981 !important;
            animation: tactileBuzz 0.1s linear infinite !important;
        }
        @keyframes pulseGlow { 0% { box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4); } 100% { box-shadow: 0 4px 35px rgba(16, 185, 129, 0.8); } }

        /* Small Crystal Buttons */
        .crystal-btn-sm {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
            z-index: 1;
            padding: 6px 12px;
            font-size: 0.9rem;
            color: #fff;
        }
        .crystal-btn-sm::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: crystalShine 6s infinite;
            z-index: -1;
            pointer-events: none;
        }
        .crystal-btn-sm:hover {
            transform: translateY(-3px) scale(1.05) !important;
        }
        .crystal-btn-sm:active {
            transform: translateY(1px) scale(0.95) !important;
        }
        .crystal-btn-icon-only {
            padding: 6px !important;
            border-radius: 50%;
        }
        .btn-blue-glass {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(3, 105, 161, 0.4));
            border: 1px solid rgba(56, 189, 248, 0.5);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-blue-glass:hover {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.4), rgba(3, 105, 161, 0.6));
            box-shadow: 0 8px 25px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        .btn-green-glass {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(4, 120, 87, 0.4));
            border: 1px solid rgba(52, 211, 153, 0.5);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-green-glass:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.4), rgba(4, 120, 87, 0.6));
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        .btn-orange-glass {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(194, 65, 12, 0.4));
            border: 1px solid rgba(249, 115, 22, 0.5);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-orange-glass:hover {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.4), rgba(194, 65, 12, 0.6));
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        .btn-slate-glass {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.2), rgba(71, 85, 105, 0.4));
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 4px 15px rgba(148, 163, 184, 0.2), inset 0 2px 5px rgba(255,255,255,0.1);
        }
        .btn-slate-glass:hover {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.4), rgba(71, 85, 105, 0.6));
            box-shadow: 0 8px 25px rgba(148, 163, 184, 0.4), inset 0 2px 5px rgba(255,255,255,0.2);
        }
        
        /* ═══ High legibility white background for Amount/Value fields ═══ */
        #amount-local {
            background: #ffffff !important;
            color: #b91c1c !important;
            border: 2px solid #ef4444 !important;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        }
        #amount-local:focus {
            background: #ffffff !important;
            border-color: #ef4444 !important;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.3), inset 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        }
        .transfer-amount, .cheque-amount, .block-sub-amount, .block-cash-amount, .block-bank-amount, .block-cheque-amount, .block-total-amount, .total-val, .val, .value-field {
            background: #ffffff !important;
            color: #047857 !important; /* Rich readable dark green */
            border: 1.5px solid #cbd5e1 !important;
        }
        .transfer-amount:focus, .cheque-amount:focus, .block-sub-amount:focus, .block-total-amount:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }

        /* Dynamic Scaling per user request */
        .dynamic-focus-container {
            transition: width 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), flex 0.3s ease !important;
        }
        .dynamic-focus-container:focus-within {
            width: 450px !important;
            flex: none !important;
        }
        .dynamic-focus-input {
            transition: font-size 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), letter-spacing 0.3s ease, height 0.3s ease, color 0.3s ease !important;
            color: #ffffff !important;
        }
        /* 🌟 مقاسات الوضع الطبيعي الأساسي (تمت زيادتها 3 درجات إضافية لتكون الأرقام واضحة ومقروءة جداً) */
        .transfer-no, .cheque-no {
            font-size: 2.8rem !important; /* زيادة 3 درجات من 2.1rem إلى 2.8rem */
            height: 62px !important;
            font-weight: 900 !important;
            letter-spacing: 1.5px !important;
        }
        .transfer-amount, .cheque-amount {
            font-size: 3.1rem !important; /* زيادة 3 درجات من 2.4rem إلى 3.1rem */
            height: 62px !important;
            font-weight: 900 !important;
        }
        .transfer-note, .cheque-branch, .transfer-statement, .cheque-statement, .cheque-date {
            font-size: 1.55rem !important;
            height: 62px !important;
        }

        .dynamic-focus-amount:focus {
            color: #fbbf24 !important; /* Golden Yellow */
            border-color: #fbbf24 !important;
            box-shadow: 0 15px 50px rgba(0,0,0,0.9), 0 0 30px rgba(251, 191, 36, 0.7) !important;
        }
        .dynamic-focus-number:focus {
            color: #38bdf8 !important; /* Sky Blue */
            border-color: #38bdf8 !important;
            box-shadow: 0 15px 50px rgba(0,0,0,0.9), 0 0 30px rgba(56, 189, 248, 0.7) !important;
        }

        /* 🔍 مقاس موحد ومكبر بدقة 4.5rem لجميع حقول الأرقام والمبالغ أثناء التركيز والكتابة */
        .transfer-no.dynamic-focus-input:focus,
        .cheque-no.dynamic-focus-input:focus,
        .transfer-amount:focus,
        .cheque-amount:focus,
        .block-cash-amount:focus,
        .block-sub-amount:focus,
        .block-total-amount:focus,
        .dynamic-focus-input:focus {
            font-size: 4.5rem !important; /* مقاس موحد عملاق ومريح جداً للقراءة والكتابة */
            height: 98px !important;
            min-width: 480px !important;
            max-width: none !important;
            position: relative !important;
            z-index: 1000 !important;
            margin-right: -60px !important;
            letter-spacing: 2px !important;
            font-weight: 900 !important;
            background: rgba(15, 23, 42, 0.98) !important;
            border-width: 2.5px !important;
            border-style: solid !important;
            border-radius: 10px !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
            /* Custom Number Properties Menu */
        #num-props-menu { display: none; position: absolute; z-index: 9999; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); border-radius: 12px; padding: 10px; width: 260px; color: #cbd5e1; font-family: 'Inter', sans-serif; direction: rtl; }
        #num-props-menu .props-header { font-weight: bold; color: #38bdf8; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 1.1rem; }
        #num-props-menu .props-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 5px; cursor: pointer; border-radius: 6px; transition: background 0.2s; font-size: 0.95rem; }
        #num-props-menu .props-item:hover { background: rgba(255, 255, 255, 0.1); }
        #num-props-menu button.prop-btn { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.5); color: #60a5fa; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; }
        #num-props-menu button.prop-btn:hover { background: rgba(59, 130, 246, 0.4); color: #fff; }

        @keyframes pulseOrb {
            0% { transform: scale(0.8) translate(0, 0); opacity: 0.5; }
            100% { transform: scale(1.5) translate(20px, 20px); opacity: 1; }
        }
        @keyframes shimmerWave {
            0% { transform: translateX(-150%) skewX(-45deg); opacity: 0; }
            11.5% { opacity: 0.15; }
            23% { transform: translateX(150%) skewX(-45deg); opacity: 0; }
            100% { transform: translateX(150%) skewX(-45deg); opacity: 0; }
        }
        .total-glass-card {
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease, border-color 0.4s ease;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .total-glass-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 45%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0.05) 55%, rgba(255,255,255,0) 100%);
            z-index: 1; opacity: 0; transition: opacity 0.5s ease;
        }
        .total-glass-card::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
            opacity: 0; z-index: 0; pointer-events: none; transition: opacity 0.4s ease, transform 0.6s ease;
        }
        .total-glass-card:hover {
            transform: translateY(-8px) scale(1.05) !important;
            z-index: 10;
        }
        .total-glass-card:hover::before { animation: shimmerWave 6.5s infinite; opacity: 1; }
        .total-glass-card:hover::after { opacity: 1; transform: rotate(15deg); }
        .total-glass-card:hover .glow-orb { filter: blur(20px); transform: scale(2); opacity: 0.8; }
        
        .cheque-glow:hover { border-color: rgba(216, 180, 254, 1) !important; box-shadow: 0 0 30px rgba(168, 85, 247, 0.8), inset 0 0 20px rgba(168, 85, 247, 0.4) !important; }
        .bank-glow:hover { border-color: rgba(110, 231, 183, 1) !important; box-shadow: 0 0 30px rgba(16, 185, 129, 0.8), inset 0 0 20px rgba(16, 185, 129, 0.4) !important; }
        .grand-glow:hover { border-color: rgba(125, 211, 252, 1) !important; box-shadow: 0 0 40px rgba(56, 189, 248, 1), inset 0 0 25px rgba(56, 189, 248, 0.5) !important; }
    </style>
<!-- 👑 USER REQUESTED TYPOGRAPHY ENHANCEMENTS (+2 & +3 SIZES) 👑 -->
    <style id="user-requested-typography-override">
        /* 1. التاريخ (زيادة الخط درجتين) */
        .date-input, #hdr-date {
            font-size: 1.85rem !important; /* زيادة درجتين إضافيتين بناء على طلب المستخدم */
            font-weight: 900 !important;
            height: 52px !important;
            padding: 4px 16px !important;
            min-width: 195px !important;
            color: #ffffff !important;
            background: rgba(15, 23, 42, 0.9) !important;
            border: 2px solid #38bdf8 !important;
            border-radius: 10px !important;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.5) !important;
            letter-spacing: 1px !important;
        }
        .date-crystal {
            height: 58px !important;
        }
        .date-crystal .icon-box {
            font-size: 1.65rem !important;
            width: 44px !important;
            height: 44px !important;
        }
        .date-input::-webkit-calendar-picker-indicator {
            width: 22px !important;
            height: 22px !important;
        }

        /* 1.1 خانة كتابة العملة (زيادة بنسبة 25%) */
        #currency {
            height: 68px !important; /* زيادة الارتفاع بنسبة 25% من 55px */
            font-size: 1.55rem !important; /* زيادة حجم الخط بنسبة 25% */
            min-width: 140px !important;
            font-weight: 900 !important;
        }

        /* 2. النقد والشيك والوعد / العهد (زيادة الخط درجتين) */
        .bal-label {
            font-size: 1.15rem !important; /* زيادة درجتين */
            font-weight: 900 !important;
            color: #f1f5f9 !important;
            letter-spacing: 0.5px !important;
        }
        .bal-val {
            font-size: 1.65rem !important; /* زيادة درجتين */
            font-weight: 900 !important;
            letter-spacing: 1px !important;
        }
        .bal-icon {
            font-size: 1.7rem !important;
        }
        .pay-toggle-btn {
            font-size: 1.25rem !important; /* زيادة درجتين */
            font-weight: 900 !important;
            padding: 9px 20px !important;
            border-radius: 22px !important;
        }

        /* 3. رقم السند (زيادة الخط درجتين) */
        .badge-label {
            font-size: 1.05rem !important; /* زيادة درجتين */
            font-weight: 800 !important;
            color: #cbd5e1 !important;
        }
        .badge-val, #voucher-number {
            font-size: 1.85rem !important; /* زيادة درجتين مع توهج أحمر واضح */
            font-weight: 900 !important;
            color: #f87171 !important;
            text-shadow: 0 0 15px rgba(239, 68, 68, 0.7) !important;
            letter-spacing: 1.5px !important;
        }
        .crystal-pill {
            height: 52px !important; /* Slim & Tall enough to comfortably frame larger fonts */
            padding: 0 16px !important;
        }

        /* 4. في الأسفل: مجموع الشيكات، مجموع التحويلات، إجمالي الجملة (زيادة الخط 3 درجات كاملة) */
        #unified-total-box {
            display: block !important; /* Always visible at the bottom of the voucher */
            margin-top: 25px !important;
            padding: 15px 0 !important;
        }
        .cheque-glow span:first-of-type {
            font-size: 1.65rem !important; /* مجموع الشيكات: زيادة 3 درجات */
            font-weight: 900 !important;
            color: #c084fc !important;
            text-shadow: 0 0 15px rgba(168,85,247,0.7) !important;
        }
        #unified-cheque-total {
            font-size: 2.85rem !important; /* قيمة الشيكات: زيادة 3 درجات */
            font-weight: 900 !important;
            color: #f3e8ff !important;
            text-shadow: 0 0 25px rgba(168,85,247,1) !important;
        }
        .bank-glow span:first-of-type {
            font-size: 1.65rem !important; /* مجموع التحويلات: زيادة 3 درجات */
            font-weight: 900 !important;
            color: #34d399 !important;
            text-shadow: 0 0 15px rgba(16,185,129,0.7) !important;
        }
        #unified-bank-total {
            font-size: 2.85rem !important; /* قيمة التحويلات: زيادة 3 درجات */
            font-weight: 900 !important;
            color: #a7f3d0 !important;
            text-shadow: 0 0 25px rgba(16,185,129,1) !important;
        }
        .grand-glow span:first-of-type {
            font-size: 1.95rem !important; /* إجمالي الجملة: زيادة 3 درجات */
            font-weight: 900 !important;
            color: #7dd3fc !important;
            text-shadow: 0 0 20px rgba(56,189,248,0.8) !important;
        }
        #unified-grand-total {
            font-size: 3.4rem !important; /* قيمة إجمالي الجملة: زيادة 3 درجات عملاقة وواضحة جداً */
            font-weight: 900 !important;
            color: #ffffff !important;
            text-shadow: 0 0 35px rgba(56,189,248,1) !important;
        }

        /* ═══ زر الواتساب كأيقونة فقط في السطر الموحد ═══ */
        .btn-whatsapp-icon-only {
            --tab-border-color: #25d366;
            --tab-glow-color: rgba(37, 211, 102, 0.85);
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.28), rgba(18, 140, 126, 0.55)) !important;
            border: 2.2px solid #25d366 !important;
            border-radius: 12px !important;
            color: #ffffff !important;
            width: 44px !important;
            height: 42px !important;
            min-width: 44px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), 0 0 12px rgba(37, 211, 102, 0.35) !important;
            flex-shrink: 0 !important;
            transition: all 0.25s ease !important;
        }
        .btn-whatsapp-icon-only:hover,
        .crystal-btn.btn-whatsapp-icon-only:hover {
            background: #000000 !important;
            color: #25d366 !important;
            border: 2.2px solid #25d366 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.75), 0 0 16px rgba(37, 211, 102, 0.85) !important;
            transform: translateY(-3px) scale(1.06) !important;
        }

        /* ═══ Auto-Send Control Button & Options in Footer ═══ */
        #auto-send-control-wrapper,
        .footer-actions #auto-send-control-wrapper {
            display: inline-flex !important;
            align-items: center !important;
            position: relative !important;
            direction: rtl !important;
        }
        #btn-toggle-auto-send,
        .footer-actions #btn-toggle-auto-send {
            height: 42px !important;
            font-size: 0.88rem !important;
            padding: 0 14px 0 10px !important;
            border-radius: 0 12px 12px 0 !important;
            border-left: none !important;
            font-weight: 800 !important;
            cursor: pointer !important;
            white-space: nowrap !important;
            transition: all 0.25s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        #btn-auto-send-options,
        .footer-actions #btn-auto-send-options {
            height: 42px !important;
            width: 28px !important;
            min-width: 28px !important;
            padding: 0 !important;
            border-radius: 12px 0 0 12px !important;
            border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            font-size: 0.72rem !important;
            transition: all 0.25s ease !important;
        }
        .btn-auto-send-toggle.active,
        .btn-auto-send-options.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(5, 150, 105, 0.42) 100%) !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.4), 0 0 14px rgba(16, 185, 129, 0.35) !important;
        }
        .btn-auto-send-toggle.active:hover,
        .btn-auto-send-options.active:hover {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.35) 0%, rgba(5, 150, 105, 0.55) 100%) !important;
            box-shadow: 0 6px 18px rgba(0,0,0,0.5), 0 0 20px rgba(16, 185, 129, 0.55) !important;
            transform: translateY(-2px) !important;
        }
        .btn-auto-send-toggle.inactive,
        .btn-auto-send-options.inactive {
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.45) 0%, rgba(30, 41, 59, 0.6) 100%) !important;
            border-color: rgba(148, 163, 184, 0.38) !important;
            color: #cbd5e1 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important;
        }
        .btn-auto-send-toggle.inactive:hover,
        .btn-auto-send-options.inactive:hover {
            background: linear-gradient(135deg, rgba(71, 85, 105, 0.6) 0%, rgba(51, 65, 85, 0.75) 100%) !important;
            border-color: #94a3b8 !important;
            color: #ffffff !important;
            transform: translateY(-2px) !important;
        }
        .auto-send-opt-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
        }
        :root.light-theme .btn-auto-send-toggle.active,
        :root.light-theme .btn-auto-send-options.active {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
            border-color: #059669 !important;
            color: #065f46 !important;
        }
        :root.light-theme .btn-auto-send-toggle.inactive,
        :root.light-theme .btn-auto-send-options.inactive {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
        }
        :root.light-theme #auto-send-dropdown-menu {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        }
        :root.light-theme .auto-send-opt-item:hover {
            background: #f1f5f9 !important;
        }

        /* ═══ WhatsApp Promissory Dispatch Modal Styles ═══ */
        .wa-modal-resizable {
            resize: both;
            overflow: hidden;
            min-width: 380px;
            min-height: 400px;
            max-width: 96vw;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            box-shadow: 0 16px 45px rgba(0,0,0,0.7), 0 0 25px rgba(37, 211, 102, 0.25);
        }
        @media (max-width: 768px) {
            #wa-voucher-modal-card {
                width: 96vw !important;
                padding: 12px 10px !important;
            }
        }
        .wa-modal-header-draggable {
            cursor: grab;
            cursor: -webkit-grab;
            user-select: none;
            -webkit-user-select: none;
        }
        .wa-modal-header-draggable:active {
            cursor: grabbing;
            cursor: -webkit-grabbing;
        }
        .wa-modal-scroll-area {
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1 1 auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(37, 211, 102, 0.45) rgba(0,0,0,0.25);
        }
        .wa-modal-scroll-area::-webkit-scrollbar {
            width: 6px;
        }
        .wa-modal-scroll-area::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.25);
            border-radius: 4px;
        }
        .wa-modal-scroll-area::-webkit-scrollbar-thumb {
            background: rgba(37, 211, 102, 0.45);
            border-radius: 4px;
        }
        .wa-modal-scroll-area::-webkit-scrollbar-thumb:hover {
            background: rgba(37, 211, 102, 0.75);
        }
        .wa-resize-handle-corner {
            position: absolute;
            bottom: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            cursor: sw-resize;
            opacity: 0.45;
            pointer-events: none;
            color: #25d366;
            font-size: 11px;
            line-height: 1;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
        }
        .wa-btn-header-ctrl {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .wa-btn-header-ctrl:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: scale(1.05);
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 16px rgba(239, 68, 68, 0.4); }
            50% { transform: scale(1.015); box-shadow: 0 0 26px rgba(239, 68, 68, 0.85); }
            100% { transform: scale(1); box-shadow: 0 0 16px rgba(239, 68, 68, 0.4); }
        }

        .wa-luxury-badge {
            position: relative;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            user-select: none;
        }
        .wa-ripple-container {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .wa-wave-pulse {
            position: absolute;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #25d366;
            animation: waWaveRipple 2.6s infinite cubic-bezier(0, 0.2, 0.8, 1);
            pointer-events: none;
        }
        .wa-wave-pulse.wave-1 { animation-delay: 0s; }
        .wa-wave-pulse.wave-2 { animation-delay: 0.85s; }
        .wa-wave-pulse.wave-3 { animation-delay: 1.7s; }
        .wa-luxury-icon-circle {
            position: relative;
            z-index: 2;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            border: 2px solid #a7f3d0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 14px rgba(37, 211, 102, 0.65), inset 0 1px 2px rgba(255, 255, 255, 0.7);
            animation: waSphereGlow 2.5s infinite ease-in-out, waMicroMotion 3.5s infinite ease-in-out;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .wa-luxury-icon-circle svg {
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
            transition: transform 0.25s ease;
        }
        .wa-luxury-badge:hover .wa-luxury-icon-circle {
            transform: scale(1.14) rotate(8deg);
            box-shadow: 0 0 24px rgba(37, 211, 102, 1), 0 0 35px rgba(37, 211, 102, 0.6), inset 0 1px 4px #ffffff;
            border-color: #ffffff;
        }
        .wa-luxury-badge:hover .wa-luxury-icon-circle svg {
            transform: scale(1.1);
        }
        @keyframes waWaveRipple {
            0% { transform: scale(0.85); opacity: 0.95; border-color: rgba(37, 211, 102, 0.9); box-shadow: 0 0 10px rgba(37, 211, 102, 0.7); }
            50% { opacity: 0.5; border-color: rgba(52, 211, 153, 0.6); }
            100% { transform: scale(1.75); opacity: 0; border-color: rgba(16, 185, 129, 0); box-shadow: 0 0 18px rgba(37, 211, 102, 0); }
        }
        @keyframes waSphereGlow {
            0%, 100% { box-shadow: 0 0 12px rgba(37, 211, 102, 0.6), inset 0 1px 2px rgba(255, 255, 255, 0.65); filter: drop-shadow(0 0 5px rgba(37, 211, 102, 0.5)); }
            50% { box-shadow: 0 0 22px rgba(37, 211, 102, 0.95), inset 0 1px 4px rgba(255, 255, 255, 0.9); filter: drop-shadow(0 0 12px rgba(37, 211, 102, 0.9)); }
        }
        @keyframes waMicroMotion {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.05) rotate(-3deg); }
            50% { transform: scale(0.97) rotate(2deg); }
            75% { transform: scale(1.04) rotate(-1deg); }
        }
        .wa-currency-chip {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #0f172a;
            color: #fef08a;
            border: 1.5px solid #fbbf24;
            border-radius: 6px;
            font-size: 1.05rem;
            font-weight: 900;
            padding: 2px 6px 2px 20px;
            cursor: pointer;
            text-align: center;
            outline: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px 2px rgba(255,255,255,0.15);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23fbbf24' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 4px center;
            background-size: 10px;
            transition: all 0.2s ease;
            height: 32px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
            font-family: 'Consolas', monospace;
            box-sizing: border-box;
        }
        .wa-currency-chip:hover, .wa-currency-chip:focus {
            border-color: #f59e0b;
            background-color: #1e293b;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.6);
            transform: translateY(-1px);
        }
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
                                            <span class="bal-label">عهد / وعد</span>
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
                        <div style="display: grid; grid-template-columns: 1.0fr 1.9fr 1.2fr 1.9fr; gap: 15px; margin-bottom: 25px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#fbbf24 !important; font-size:1.15rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">💱 العملة</label>
                                <select class="form-input system-currency-select input-glow-focus hover-glow" id="currency" data-next="exchange-rate" onchange="updateCurrency(); document.getElementById('exchange-rate').focus(); document.getElementById('exchange-rate').select();" style="height: 68px; border-radius: 10px; flex-grow: 1; min-width: 140px; width: 100%; font-size: 1.55rem !important; font-weight: 900; padding-right: 8px; padding-left: 8px;">
                                    <option value="SDG">جنيه (SDG)</option>
                                    <option value="USD">دولار (USD)</option>
                                    <option value="AED">درهم (AED)</option>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; position: relative;">
                                <label id="label-box-1" class="label" style="display:flex; align-items:center; gap:5px; color:#cbd5e1 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;"><span class="vivid-icon vivid-green" style="width:24px; height:24px; font-size:1.1rem;">💰</span> المبلغ (محلي)</label>
                                <div style="position: relative; flex-grow: 1; min-width: 0; width: 100%;">
                                    <span id="amount-currency-symbol" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: var(--primary-color, #3b82f6); color: white; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.9rem; pointer-events: none; z-index: 5; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">SDG</span>
                                    <input type="text" class="form-input amount hover-scale input-glow-focus hover-glow" id="amount-local" data-next="from-account" autofocus value="0" inputmode="numeric" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === 'function') calcEquiv('local');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, ''); this.value = val; calcEquiv('local'); if(typeof adjustAmountFontSize === 'function') adjustAmountFontSize(this); if(this.getAttribute('data-thousands') !== 'false') { let parts = val.split('.'); parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); this.value = parts.join('.'); }" style="height: 94px !important; box-sizing: border-box !important; font-size: 2.35rem !important; font-weight: 900; text-align: center; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 2px; direction: ltr; border-radius: 10px; width: 100%; transition: font-size 0.2s ease, letter-spacing 0.2s ease; padding-left: 60px;">
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#fbbf24 !important; font-size:1.15rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">⚖️ الصرف</label>
                                <input type="number" class="form-input" id="exchange-rate" value="1" placeholder="الصرف" readonly style="flex: 2; text-align: center; cursor:pointer; background:rgba(16,185,129,0.12); color:#10b981; font-weight:900; height: 55px; border-radius: 10px; font-size: 1.65rem !important; letter-spacing: 1px; min-width: 130px; width: 100%; border: 1.5px solid rgba(16,185,129,0.4); box-shadow: 0 0 12px rgba(16,185,129,0.25);" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=Math.round(r); if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; position: relative;">
                                <label id="label-box-2" class="label" style="display:flex; align-items:center; gap:5px; color:#cbd5e1 !important; font-size:1.1rem; margin-bottom:0; white-space:nowrap; flex-shrink: 0;">💵 القيمة (أجنبي)</label>
                                <div style="position: relative; flex-grow: 1; min-width: 0; width: 100%;">
                                    <span id="amount-foreign-currency-symbol" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.9rem; pointer-events: none; z-index: 5; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);">USD</span>
                                    <input type="text" class="form-input amount input-glow-focus hover-glow" id="amount-foreign" data-next="from-account" value="0" inputmode="decimal" readonly tabindex="-1" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === 'function') calcEquiv('foreign');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, ''); this.value = val; calcEquiv('foreign'); if(typeof adjustAmountFontSize === 'function') adjustAmountFontSize(this); if(this.getAttribute('data-thousands') !== 'false') { let parts = val.split('.'); parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); this.value = parts.join('.'); }" style="height: 94px !important; box-sizing: border-box !important; font-size: 2.35rem !important; text-align: center; background: #fafafa !important; color: #ef4444 !important; font-weight: 900; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 2px; direction: ltr; border-radius: 10px; width: 100%; transition: font-size 0.2s ease, letter-spacing 0.2s ease; padding-left: 60px; cursor: not-allowed; border: 1px solid #fca5a5;">
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
                                    من حساب <span style="color:#ffffff !important;">(صرف)</span>
                                </label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="from-account">
                                    <input type="text" autocomplete="off" class="form-input input-glow-focus hover-glow" id="from-account-search" placeholder="ابحث عن حساب الصرف..." onfocus="showAccountDropdown('from')" onblur="hideAccountDropdownDelayed('from')" oninput="filterAccountDropdown('from')" style="width: 100%; padding: 12px 15px 12px 95px; border-radius: 8px; font-weight: bold; font-size: 1.55rem !important; border: 1px solid rgba(244, 63, 94, 0.5); background: rgba(0,0,0,0.3); color: #fbbf24 !important;">
                                    <button type="button" id="from-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('from-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.8rem; padding: 4px 8px; height: 32px; display: none;" title="دفتر الأستاذ العام">📖 دفتر الأستاذ</button>
                                    <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 250px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none; margin-top: 5px;"></div>
                                </div>
                            </div>
                            <!-- إلى حساب (Green) -->
                            <div style="background: rgba(16, 185, 129, 0.05); padding: 8px 15px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; gap: 10px;" data-swap="1">
                                <label class="label" style="display:flex; align-items:center; gap:5px; color:#10b981 !important; margin-bottom:0; font-weight:800; white-space:nowrap; flex-shrink:0; font-size: 1.5rem !important;">
                                    <span style="display:flex; align-items:center; justify-content:center; width: 24px; height: 24px; background: rgba(16, 185, 129, 0.15); border-radius: 50%;">
                                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </span>
                                    إلى حساب <span style="color:#ffffff !important;">(استلام)</span>
                                </label>
                                <div class="custom-dropdown-input-wrap" style="flex-grow: 1; width: 100%; min-width: 0; position: relative;">
                                    <input type="hidden" id="to-account">
                                    <input type="text" autocomplete="off" class="form-input input-glow-focus hover-glow" id="to-account-search" placeholder="ابحث عن حساب الاستلام..." onfocus="showAccountDropdown('to')" onblur="hideAccountDropdownDelayed('to')" oninput="filterAccountDropdown('to')" style="width: 100%; padding: 12px 15px 12px 95px; border-radius: 8px; font-weight: bold; font-size: 1.55rem !important; border: 1px solid rgba(16, 185, 129, 0.5); background: rgba(0,0,0,0.3); color: #fbbf24 !important;">
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
                            .pay-toggle-btn {
                                background: rgba(255, 255, 255, 0.05);
                                border: 1px solid rgba(255, 255, 255, 0.1);
                                color: #94a3b8;
                                padding: 6px 14px;
                                border-radius: 20px;
                                font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;
                            }
                            .pay-toggle-btn.active {
                                background: rgba(56, 189, 248, 0.2) !important;
                                border-color: #38bdf8 !important;
                                color: #38bdf8 !important;
                                box-shadow: 0 0 12px rgba(56, 189, 248, 0.3);
                            }
                            #toggle-credit.active {
                                background: rgba(245, 158, 11, 0.2) !important;
                                border-color: #fbbf24 !important;
                                color: #fbbf24 !important;
                                box-shadow: 0 0 12px rgba(245, 158, 11, 0.3);
                            }
                            #toggle-cash.active {
                                background: rgba(16, 185, 129, 0.2) !important;
                                border-color: #34d399 !important;
                                color: #34d399 !important;
                                box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
                            }
                            #toggle-bank.active {
                                background: rgba(59, 130, 246, 0.2) !important;
                                border-color: #60a5fa !important;
                                color: #60a5fa !important;
                                box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
                            }
                            #toggle-cheque.active {
                                background: rgba(139, 92, 246, 0.2) !important;
                                border-color: #c084fc !important;
                                color: #c084fc !important;
                                box-shadow: 0 0 12px rgba(139, 92, 246, 0.3);
                            }
                            .payment-method-box {
                                transition: all 0.3s ease;
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
                                /* Custom Number Properties Menu */
        #num-props-menu { display: none; position: absolute; z-index: 9999; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); border-radius: 12px; padding: 10px; width: 260px; color: #cbd5e1; font-family: 'Inter', sans-serif; direction: rtl; }
        #num-props-menu .props-header { font-weight: bold; color: #38bdf8; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 1.1rem; }
        #num-props-menu .props-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 5px; cursor: pointer; border-radius: 6px; transition: background 0.2s; font-size: 0.95rem; }
        #num-props-menu .props-item:hover { background: rgba(255, 255, 255, 0.1); }
        #num-props-menu button.prop-btn { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.5); color: #60a5fa; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; }
        #num-props-menu button.prop-btn:hover { background: rgba(59, 130, 246, 0.4); color: #fff; }
    </style>
                        <div id="payment-panel" style="display: block; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; margin-top: 20px; margin-bottom: 20px;">
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

                                <div class="payment-toggles" style="display: flex; gap: 8px; flex: none; flex-wrap: wrap;">
                                    <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">💵 نقدي</button>
                                    <button type="button" class="pay-toggle-btn active" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">🏦 تحويل بنكي</button>
                                    <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">✍️ شيك</button>
                                    <button type="button" class="pay-toggle-btn" id="btn-financial-ocr" onclick="FinancialOCR.openScanner({ target: 'single' })" style="background: linear-gradient(135deg, rgba(6,182,212,0.2), rgba(16,185,129,0.2)); border: 1.5px solid #22d3ee; color: #22d3ee; border-radius: 20px; font-size: 1.15rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(6,182,212,0.25);" title="تصوير وقراءة إشعار بنكك أو الشيك بالذكاء الاصطناعي">
                                        <span style="font-size: 1.3rem;">📷</span> مسح إشعار / شيك (AI OCR)
                                    </button>
                                    <button type="button" class="pay-toggle-btn" onclick="FinancialOCR.openTrainer()" style="background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.4); color: #d8b4fe; padding: 8px 14px; border-radius: 20px; font-size: 1.05rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="شرح وتدريب النماذج للذكاء الاصطناعي">
                                        <span>🧠</span> تدريب النماذج
                                    </button>
                                </div>
                            </div>
                            
                            <div class="payment-layout" id="payment-layout" style="display: flex; gap: 15px; padding: 15px; flex-wrap: wrap;">
                                <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">

                                    <!-- Cash section -->
                                    <div id="pay-cash-section" class="payment-method-box" style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.18); border-radius: 12px; padding: 12px 15px; display: none; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 8px; min-width: 200px; width: 200px; flex: none;">
                                            <span style="font-size: 1.2rem;">💵</span>
                                            <span style="font-weight: 900; color: #34d399; font-size: 1.25rem;">الدفع النقدي (كاش)</span>
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
                                    <div id="unified-total-box" style="display: block !important; width: 100%; margin-top: 15px; padding: 10px 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: stretch; gap: 15px; flex-wrap: wrap;">
                                            <!-- Cheques Total -->
                                            <div class="total-glass-card cheque-glow" style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.4); position: relative; overflow: hidden;">
                                                <div class="glow-orb" style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(168, 85, 247, 0.5); filter: blur(40px); border-radius: 50%; animation: pulseOrb 3s infinite alternate;"></div>
                                                <span style="color: #c084fc; font-size: 1.55rem; font-weight: 900; z-index: 2; margin-bottom: 6px; text-shadow: 0 0 15px rgba(168,85,247,0.6);">مجموع الشيكات</span>
                                                <span id="unified-cheque-total" style="color: #e9d5ff; font-size: 2.5rem; font-weight: 900; z-index: 2; text-shadow: 0 0 20px rgba(168,85,247,0.9); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                            <!-- Bank Total -->
                                            <div class="total-glass-card bank-glow" style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.4); position: relative; overflow: hidden;">
                                                <div class="glow-orb" style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(16, 185, 129, 0.5); filter: blur(40px); border-radius: 50%; animation: pulseOrb 3.5s infinite alternate-reverse;"></div>
                                                <span style="color: #34d399; font-size: 1.55rem; font-weight: 900; z-index: 2; margin-bottom: 6px; text-shadow: 0 0 15px rgba(16,185,129,0.6);">مجموع التحويلات</span>
                                                <span id="unified-bank-total" style="color: #a7f3d0; font-size: 2.5rem; font-weight: 900; z-index: 2; text-shadow: 0 0 20px rgba(16,185,129,0.9); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                            <!-- Grand Total -->
                                            <div class="total-glass-card grand-glow" style="flex: 1; min-width: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(56, 189, 248, 0.15); border: 2px solid rgba(56, 189, 248, 0.6); position: relative; overflow: hidden; transform: scale(1.02); box-shadow: 0 0 20px rgba(56,189,248,0.2);">
                                                <div class="glow-orb" style="position: absolute; top: -50px; left: -50px; width: 120px; height: 120px; background: rgba(56, 189, 248, 0.6); filter: blur(50px); border-radius: 50%; animation: pulseOrb 2.5s infinite alternate;"></div>
                                                <span style="color: #7dd3fc; font-size: 1.85rem; font-weight: 900; z-index: 2; margin-bottom: 6px; text-shadow: 0 0 20px rgba(56,189,248,0.8);">إجمالي الجملة</span>
                                                <span id="unified-grand-total" style="color: #ffffff; font-size: 3.1rem; font-weight: 900; z-index: 2; text-shadow: 0 0 30px rgba(56,189,248,1); font-variant-numeric: tabular-nums;">0</span>
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
            <div class="footer-actions unified-footer-bar" style="width: 100%; justify-content: center; gap: 10px; display: flex; flex-wrap: wrap; align-items: center;">
                <button type="button" class="btn crystal-btn btn-exit" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='dashboard-pro.php'; }" title="الرجوع للشاشة السابقة">🚪 خروج</button>
                <button type="button" class="btn crystal-btn btn-new" onclick="resetVoucher()">✨ مستند جديد</button>
                <button type="button" class="btn crystal-btn btn-print-preview" id="btn-preview-invoice" style="display: none; background: rgba(14, 165, 233, 0.15); border: 1px solid #0ea5e9; color: #7dd3fc;" onclick="previewVoucher()">👁️ معاينة</button>
                <button type="button" class="btn crystal-btn btn-print" id="btn-print-invoice" style="display: none;" onclick="printVoucher()">🖨️ طباعة</button>
                
                <!-- زر الواتساب - يظهر كشعار واتساب رسمي احترافي -->
                <button type="button" class="btn crystal-btn btn-whatsapp-icon-only" id="btn-whatsapp-master" onclick="openMasterWhatsAppDispatchModal()" title="إرسال كمبيالة وإشعار السند عبر واتساب">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="display: block;">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C5.373 0 0 5.373 0 12c0 2.115.552 4.103 1.516 5.832L.057 24l6.34-1.662A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z" fill="#25D366"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="#ffffff"/>
                    </svg>
                </button>
                <button type="button" id="btn-whatsapp-invoice" style="display: none !important;"></button>

                <button type="button" class="btn crystal-btn btn-delete" onclick="confirmDelete()">🗑️ حذف السند</button>

                <!-- ═══ زر التحكم في الإرسال التلقائي مع الحفظ ═══ -->
                <div id="auto-send-control-wrapper">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle inactive" id="btn-toggle-auto-send" onclick="window.toggleAutoSendMode()" title="التحكم في الإرسال التلقائي: انقر للتبديل بين الحفظ مع الإرسال، أو الحفظ فقط بدون إرسال">
                        <span id="auto-send-icon" style="font-size: 1.1rem;">💾</span>
                        <span id="auto-send-label" style="font-weight: 800;">إرسال تلقائي:</span>
                        <span id="auto-send-status-pill" style="padding: 2px 8px; border-radius: 7px; font-size: 0.76rem; font-weight: 900;">معطّل (حفظ فقط)</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options inactive" id="btn-auto-send-options" onclick="window.toggleAutoSendDropdown(event)" title="خيارات وجهة الإرسال التلقائي (المستلم / الصارف / كلاهما)">
                        ▼
                    </button>
                    
                    <!-- القائمة المنسدلة للخيارات السريعة -->
                    <div id="auto-send-dropdown-menu" style="display: none; position: absolute; bottom: 50px; left: 0; background: #0f172a; border: 1.5px solid #334155; border-radius: 12px; padding: 6px; min-width: 260px; box-shadow: 0 12px 30px rgba(0,0,0,0.7), 0 0 20px rgba(0,0,0,0.5); z-index: 9999; direction: rtl; text-align: right; backdrop-filter: blur(12px);">
                        <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 800; padding: 5px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <span>⚙️ وضع الحفظ والإرسال:</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(false)" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #cbd5e1; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                            <span>💾</span> <span>حفظ فقط (بدون إرسال)</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'both')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #34d399; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                            <span>⚡👥</span> <span>إرسال تلقائي للطرفين (المستلم + الصارف)</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'debtor')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #60a5fa; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                            <span>📥</span> <span>إرسال تلقائي للطرف المستلم فقط</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'creditor')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #f472b6; display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
                            <span>📤</span> <span>إرسال تلقائي للطرف الصارف فقط</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn crystal-btn btn-save-master" id="btn-save" onclick="submitVoucher()" title="حفظ واعتماد وترحيل السند المالي">
                    <span>✅</span> <span>حفظ واعتماد السند</span>
                </button>
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

    <!-- ═══ WhatsApp Enterprise Voucher Dispatch Modal (نافذة إرسال الواتساب الذكي والكمبيالة المعتمدة) ═══ -->
    <div id="wa-voucher-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); z-index: 99999; align-items: center; justify-content: center;">
        <div id="wa-voucher-modal-card" class="modal-card glass-container wa-modal-resizable" style="width: 96vw; max-width: 1450px; height: 95vh; max-height: 96vh; border: 2.5px solid #25d366; padding: 12px 18px; border-radius: 14px; background: #0b1120; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.9), 0 0 35px rgba(37,211,102,0.35); display: flex; flex-direction: column; box-sizing: border-box; overflow: hidden;">
            
            <!-- Header (Draggable + Window Controls) -->
            <div id="wa-voucher-modal-header" class="wa-modal-header-draggable" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid rgba(37, 211, 102, 0.4); padding-bottom: 8px; margin-bottom: 8px; flex-shrink: 0;" title="اضغط مع السحب لتحريك النافذة يميناً ويساراً">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.3rem; color: #94a3b8; cursor: grab;" title="اضغط مع السحب لتحريك النافذة">⠿</span>
                    <span style="font-size: 1.5rem;">📱</span>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h3 style="margin: 0; color: #25d366; font-size: 1.30rem; font-weight: 900; line-height: 1.2; text-shadow: 0 0 10px rgba(37,211,102,0.35);">إرسال واعتماد إشعار السند المالي عبر واتساب</h3>
                        <span id="wa-modal-subtitle" style="background: rgba(251, 191, 36, 0.16); border: 1.5px solid rgba(251, 191, 36, 0.6); color: #fef08a; font-size: 0.86rem; font-weight: 800; padding: 3px 12px; border-radius: 6px;">📜 ورقة كمبيالة رسمية مؤطرة (تشمل رصيد دفتر الأستاذ العام)</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- زر التكبير / الاستعادة -->
                    <button type="button" id="btn-wa-modal-maximize" class="wa-btn-header-ctrl" onclick="toggleWAModalMaximize()" title="تكبير / استعادة الحجم" style="width: 32px; height: 32px; font-size: 1.05rem;">🗖</button>
                    <!-- زر الإغلاق -->
                    <button type="button" onclick="closeWAVoucherModal()" class="wa-btn-header-ctrl" style="width: 32px; height: 32px; font-size: 1.05rem; background: rgba(239, 68, 68, 0.25); border-color: #ef4444; color: #ef4444;" title="إغلاق">✕</button>
                </div>
            </div>

            <!-- ═══ شبكة التقسيم الرئيسية ═══ -->
            <div id="wa-voucher-modal-body" style="display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr); gap: 14px; flex: 1; min-height: 0; overflow: hidden;">
                <input type="hidden" id="wa-modal-block-id">
                <input type="hidden" id="wa-modal-target-mode" value="both">
                <input type="hidden" id="wa-modal-format-type" value="promissory_note">
                <input type="hidden" id="wa-both-active-tab" value="debtor">

                <!-- ════════ العمود الأيمن الموسع (لوحة أطراف السند والمعلومات وقنوات الإرسال) ════════ -->
                <div style="display: flex; flex-direction: column; justify-content: space-between; gap: 7px; overflow-y: auto; padding-right: 2px;" class="wa-modal-scroll-area">
                    
                    <!-- 1. شريط وجهة العملية والأطراف واختيار العملة الموحدة -->
                    <div style="background: #0f172a; border: 1.5px solid rgba(37,211,102,0.4); border-radius: 9px; padding: 7px 12px; display: flex; flex-direction: column; gap: 6px; box-shadow: 0 3px 10px rgba(0,0,0,0.4);">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 0.92rem; font-weight: 900; color: #fbbf24; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span style="display: flex; align-items: center; gap: 6px;"><span>🎯</span> <span>وجهة إشعار السند:</span></span>
                                <div style="display: flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.45); padding: 2px 8px; border-radius: 6px; border: 1.5px solid #fbbf24;">
                                    <span style="font-size: 1.05rem;">💱</span>
                                    <span style="font-size: 0.86rem; color: #fbbf24; font-weight: 900; white-space: nowrap;">العملة الموحدة:</span>
                                    <select id="wa-modal-currency-select" onchange="window.setWAModalCurrency(this.value)" style="background: #1e293b; border: 1.5px solid #fbbf24; color: #fde047; font-weight: 900; font-size: 0.88rem; padding: 2px 6px; border-radius: 5px; cursor: pointer; outline: none;">
                                        <option value="SDG">جنيه سوداني (SDG)</option>
                                        <option value="USD">دولار أمريكي (USD)</option>
                                        <option value="SAR">ريال سعودي (SAR)</option>
                                        <option value="AED">درهم إماراتي (AED)</option>
                                        <option value="EGP">جنيه مصري (EGP)</option>
                                    </select>
                                </div>
                            </div>
                            <span id="wa-target-status-badge" style="font-size: 0.84rem; font-weight: 900; padding: 3px 12px; border-radius: 6px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(239, 68, 68, 0.3) 100%); color: #fef08a; border: 1.5px solid rgba(251, 191, 36, 0.6);">
                                محدد: إرسال لكليهما معاً (إلى + من) 👥
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: space-between;">
                            <!-- زر الطرف المدين (المستلم) -->
                            <button type="button" id="btn-wa-target-debtor" class="wa-target-btn" onclick="setWATargetMode('debtor')" style="flex: 1; height: 42px; padding: 4px 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #022c22; border: 2px solid #10b981; color: #a7f3d0; font-size: 1.08rem; font-weight: 900; white-space: nowrap;">
                                <span style="font-size: 1.25rem;">📥</span> <span id="wa-debtor-role-title">إلى</span>: <b id="wa-debtor-acc-name" style="color: #fef08a; font-size: 1.15rem; font-weight: 900;">-</b>
                            </button>
                            <!-- سهم التبديل الأوسط -->
                            <button type="button" id="btn-wa-swap-parties" onclick="swapWATargetParties()" title="تبديل الإرسال بين الأطراف" style="background: #1e293b; border: 2px solid #fbbf24; color: #fbbf24; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; flex-shrink: 0; transition: all 0.25s; box-shadow: 0 2px 6px rgba(0,0,0,0.5);">
                                ⇄
                            </button>
                            <!-- زر الطرف الدائن (الصارف) -->
                            <button type="button" id="btn-wa-target-creditor" class="wa-target-btn" onclick="setWATargetMode('creditor')" style="flex: 1; height: 42px; padding: 4px 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #2a080c; border: 2px solid #ef4444; color: #fecaca; font-size: 1.08rem; font-weight: 900; white-space: nowrap;">
                                <span style="font-size: 1.25rem;">📤</span> <span id="wa-creditor-role-title">من</span>: <b id="wa-creditor-acc-name" style="color: #fef08a; font-size: 1.15rem; font-weight: 900;">-</b>
                            </button>
                            <!-- زر كليهما معاً (مصغر وأنيق) -->
                            <button type="button" id="btn-wa-target-both" class="wa-target-btn active" onclick="setWATargetMode('both')" style="flex: 0 0 auto; height: 42px; padding: 4px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: linear-gradient(135deg, #047857 0%, #1d4ed8 50%, #b91c1c 100%); border: 2px solid #fbbf24; color: #ffffff; box-shadow: 0 0 12px rgba(251, 191, 36, 0.45); font-size: 1.02rem; font-weight: 900; white-space: nowrap;" title="إرسال لكليهما معاً">
                                <span style="font-size: 1.15rem;">👥</span> <span>كليهما معاً ⚡</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. بطاقات الأطراف في وضع كليهما معاً -->
                    <div id="wa-both-parties-view" style="display: flex; flex-direction: column; gap: 8px;">
                        <!-- بطاقة الطرف المدين (المستلم) -->
                        <div id="wa-both-debtor-card" style="background: #061f18; border: 2px solid #10b981; border-right: 8px solid #10b981; border-radius: 12px; padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.5); box-sizing: border-box;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                    <span style="font-size: 1.35rem; flex-shrink: 0;">📥</span>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                                        <span style="background: rgba(16, 185, 129, 0.25); border: 1.5px solid #10b981; color: #86efac; font-size: 1.15rem; font-weight: 900; padding: 3px 10px; border-radius: 6px; white-space: nowrap; min-width: 40px; text-align: center; display: inline-block; box-sizing: border-box;" id="wa-both-debtor-role-label">إلى</span>:
                                        <b id="wa-both-debtor-name" style="color: #ffffff; font-weight: 900; font-size: 1.32rem; letter-spacing: 0.3px; line-height: 1.3; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">-</b>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span id="wa-debtor-status-tag" style="background: #1e293b; border: 1.5px solid #475569; color: #cbd5e1; font-size: 0.98rem; font-weight: 800; padding: 4px 12px; border-radius: 6px; white-space: nowrap; min-width: 110px; text-align: center; display: inline-block; box-sizing: border-box;">⏳ بانتظار الإرسال</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; justify-content: space-between;">
                                <div id="wa-both-debtor-balance" style="background: #022c22; border: 2px solid #10b981; padding: 4px 10px; border-radius: 8px; white-space: nowrap; flex: 0 0 auto; width: auto; height: 44px; box-sizing: border-box; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.45);">
                                    <span style="color: #6ee7b7; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد المستلم:</span>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <b style="color: #ffffff; background: #064e3b; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #10b981; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">-</b>
                                        <select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                            <option value="SDG">SDG</option>
                                            <option value="USD">USD</option>
                                            <option value="SAR">SAR</option>
                                            <option value="AED">AED</option>
                                            <option value="EGP">EGP</option>
                                        </select>
                                        <span style="background: #10b981; color: #ffffff; border: 1.5px solid #34d399; padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">-</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                        <div class="wa-ripple-container">
                                            <span class="wa-wave-pulse wave-1"></span>
                                            <span class="wa-wave-pulse wave-2"></span>
                                            <span class="wa-wave-pulse wave-3"></span>
                                        </div>
                                        <div class="wa-luxury-icon-circle">
                                            <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="tel" id="wa-both-debtor-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone('debtor', this.value)" placeholder="أدخل رقم الواتساب للمستلم هنا..." style="flex: 1; min-width: 0; width: 100%; height: 44px; background: #021a14; border: 2px solid #10b981; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; border-radius: 8px; box-sizing: border-box; transition: all 0.25s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);">
                                </div>
                                <button type="button" class="btn crystal-btn" onclick="sendSinglePartyDirectly('debtor')" style="height: 44px; min-width: 105px; flex-shrink: 0; background: linear-gradient(135deg, #059669 0%, #047857 100%); border: 2px solid #34d399; color: #ffffff; font-weight: 900; font-size: 1.10rem; padding: 4px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; box-shadow: 0 3px 10px rgba(16,185,129,0.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;" title="إرسال إشعار هذا الطرف فقط">
                                    <span>⚡ إرسال</span>
                                </button>
                            </div>
                        </div>

                        <!-- بطاقة الطرف الدائن (الصارف) -->
                        <div id="wa-both-creditor-card" style="background: #200a0f; border: 2px solid #ef4444; border-right: 8px solid #ef4444; border-radius: 12px; padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.5); box-sizing: border-box;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                    <span style="font-size: 1.35rem; flex-shrink: 0;">📤</span>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                                        <span style="background: rgba(239, 68, 68, 0.25); border: 1.5px solid #ef4444; color: #fca5a5; font-size: 1.15rem; font-weight: 900; padding: 3px 10px; border-radius: 6px; white-space: nowrap; min-width: 40px; text-align: center; display: inline-block; box-sizing: border-box;" id="wa-both-creditor-role-label">من</span>:
                                        <b id="wa-both-creditor-name" style="color: #ffffff; font-weight: 900; font-size: 1.32rem; letter-spacing: 0.3px; line-height: 1.3; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">-</b>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span id="wa-creditor-status-tag" style="background: #1e293b; border: 1.5px solid #475569; color: #cbd5e1; font-size: 0.98rem; font-weight: 800; padding: 4px 12px; border-radius: 6px; white-space: nowrap; min-width: 110px; text-align: center; display: inline-block; box-sizing: border-box;">⏳ بانتظار الإرسال</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; justify-content: space-between;">
                                <div id="wa-both-creditor-balance" style="background: #2a080c; border: 2px solid #ef4444; padding: 4px 10px; border-radius: 8px; white-space: nowrap; flex: 0 0 auto; width: auto; height: 44px; box-sizing: border-box; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.45);">
                                    <span style="color: #fca5a5; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد الصرف:</span>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <b style="color: #ffffff; background: #7f1d1d; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #ef4444; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">-</b>
                                        <select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                            <option value="SDG">SDG</option>
                                            <option value="USD">USD</option>
                                            <option value="SAR">SAR</option>
                                            <option value="AED">AED</option>
                                            <option value="EGP">EGP</option>
                                        </select>
                                        <span style="background: #ef4444; color: #ffffff; border: 1.5px solid #f87171; padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">-</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                        <div class="wa-ripple-container">
                                            <span class="wa-wave-pulse wave-1"></span>
                                            <span class="wa-wave-pulse wave-2"></span>
                                            <span class="wa-wave-pulse wave-3"></span>
                                        </div>
                                        <div class="wa-luxury-icon-circle">
                                            <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="tel" id="wa-both-creditor-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone('creditor', this.value)" placeholder="أدخل رقم الواتساب لحساب الصرف هنا..." style="flex: 1; min-width: 0; width: 100%; height: 44px; background: #1f070b; border: 2px solid #ef4444; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; border-radius: 8px; box-sizing: border-box; transition: all 0.25s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);">
                                </div>
                                <button type="button" class="btn crystal-btn" onclick="sendSinglePartyDirectly('creditor')" style="height: 44px; min-width: 105px; flex-shrink: 0; background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); border: 2px solid #f87171; color: #ffffff; font-weight: 900; font-size: 1.10rem; padding: 4px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; box-shadow: 0 3px 10px rgba(239,68,68,0.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;" title="إرسال إشعار هذا الطرف فقط">
                                    <span>⚡ إرسال</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. بطاقة الطرف الفردي في وضع التحديد المفرد -->
                    <div id="wa-single-party-view" style="display: none; flex-direction: column; gap: 8px; background: #0f172a; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 10px 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.4);">
                        <div>
                            <label id="wa-label-recipient-name" style="color: #34d399; font-size: 1.15rem; font-weight: 900; display: block; margin-bottom: 5px;">👤 الطرف المحدد:</label>
                            <input type="text" id="wa-modal-recipient-name" class="form-input" readonly style="background: #1e293b; color: #ffffff; font-weight: 900; font-size: 1.30rem; padding: 3px 12px; height: 44px; border-radius: 8px; border: 1.5px solid #475569;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div id="wa-modal-balance-container" style="flex: 0 0 auto; width: auto; display: inline-flex; align-items: center; justify-content: space-between; gap: 8px; background: #082f49; border: 2px solid #38bdf8; border-radius: 8px; padding: 4px 12px; height: 44px; box-sizing: border-box;">
                                <span style="color: #bae6fd; font-size: 1.05rem; font-weight: 900; white-space: nowrap;">📊 رصيد الأستاذ:</span>
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <span id="wa-modal-live-balance" style="color: #ffffff; background: #0369a1; padding: 3px 8px; border-radius: 6px; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900;">0.00</span>
                                    <select id="wa-modal-single-currency-chip" class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                        <option value="SDG">SDG</option>
                                        <option value="USD">USD</option>
                                        <option value="SAR">SAR</option>
                                        <option value="AED">AED</option>
                                        <option value="EGP">EGP</option>
                                    </select>
                                    <span id="wa-modal-live-balance-tag" style="font-size: 1.02rem; font-weight: 900; padding: 3px 8px; border-radius: 6px; background: #38bdf8; color: #082f49;">-</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                    <div class="wa-ripple-container">
                                        <span class="wa-wave-pulse wave-1"></span>
                                        <span class="wa-wave-pulse wave-2"></span>
                                        <span class="wa-wave-pulse wave-3"></span>
                                    </div>
                                    <div class="wa-luxury-icon-circle">
                                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </div>
                                </div>
                                <input type="tel" id="wa-modal-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone(document.getElementById('wa-modal-target-mode')?.value||'debtor', this.value)" placeholder="أدخل رقم الواتساب هنا..." style="background: #021a14; border: 2px solid #10b981; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; height: 44px; border-radius: 8px; flex: 1; min-width: 0; width: 100%; box-sizing: border-box;">
                            </div>
                        </div>
                    </div>

                    <!-- 4. زر الإرسال الرئيسي الشامل لكلا الطرفين (المستلم + الصارف) -->
                    <button type="button" id="btn-wa-send-both-action" onclick="executeWASend('gateway')" class="btn crystal-btn" style="width: 100%; height: 48px; padding: 4px 16px; background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%); border: 2px solid #34d399; border-radius: 10px; color: #ffffff; font-weight: 900; font-size: 1.22rem; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.45); cursor: pointer; transition: all 0.2s ease;" title="إرسال كمبيالة السند مباشرة عبر خادم الواتساب المربوط">
                        <span style="font-size: 1.45rem;">🚀</span>
                        <span id="btn-wa-send-both-action-text">إرسال إلى كل الأطراف عبر الواتساب ⚡</span>
                    </button>

                    <!-- بانر التسليم الذكي للمرحلة الثانية (Handover Panel) -->
                    <div id="wa-modal-handover-panel" style="display: none; background: #0f172a; border: 2px solid #10b981; border-radius: 9px; padding: 6px 12px; box-shadow: 0 0 16px rgba(16, 185, 129, 0.4); animation: fadeIn 0.2s ease;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                            <span style="color: #86efac; font-weight: 900; font-size: 0.90rem; display: flex; align-items: center; gap: 6px;">
                                <span>✅</span> <span id="wa-handover-status-text">تم إرسال إشعار المستلم بنجاح!</span>
                            </span>
                            <span id="wa-handover-subtext" style="color: #cbd5e1; font-weight: 700; font-size: 0.82rem; width: 100%;">اضغط الزر لإرسال إشعار الصارف فوراً:</span>
                            <span id="wa-handover-countdown-badge" style="display: none;"><b id="wa-handover-timer"></b></span>
                            <div style="display: flex; gap: 8px; align-items: center; width: 100%; margin-top: 3px;">
                                <button type="button" id="btn-wa-handover-next" onclick="triggerHandoverNextParty()" style="flex: 1; height: 36px; padding: 4px 12px; background: linear-gradient(135deg, #10b981, #059669); border: 1.5px solid #34d399; border-radius: 7px; color: #fff; font-size: 0.88rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                                    <span>📤</span> <span id="btn-wa-handover-label">إرسال إشعار الطرف الصارف الآن ➔</span>
                                </button>
                                <button type="button" onclick="closeWAVoucherModal()" style="height: 36px; padding: 4px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 7px; color: #cbd5e1; font-size: 0.82rem; font-weight: 800; cursor: pointer;">
                                    إغلاق
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. شريط أدوات تصوير ونسخ صورة السند الفوري (Image & Capture Tools) -->
                    <div style="background: #0f172a; border: 1.5px dashed #fbbf24; border-radius: 9px; padding: 5px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <span style="color: #fbbf24; font-size: 0.90rem; font-weight: 900; display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 1.15rem;">📸</span> <span>تصوير وحفظ السند:</span>
                        </span>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" onclick="downloadWAVoucherImage()" class="btn crystal-btn" style="height: 34px; background: #78350f; border: 1.5px solid #fbbf24; color: #fef08a; font-size: 0.86rem; font-weight: 900; padding: 4px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="تنزيل كارت السند المصور عالي الدقة كصورة PNG">
                                <span>💾</span> حفظ كارت السند (PNG)
                            </button>
                            <button type="button" onclick="copyWAVoucherImageToClipboard()" class="btn crystal-btn" style="height: 34px; background: #075985; border: 1.5px solid #38bdf8; color: #e0f2fe; font-size: 0.86rem; font-weight: 900; padding: 4px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="نسخ صورة السند للحافظة للصقها مباشرة في واتساب">
                                <span>📋</span> نسخ الصورة للحافظة
                            </button>
                        </div>
                    </div>

                    <!-- 6. قناة الإرسال الموحدة عبر خادم الواتساب المربوط -->
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" id="btn-wa-send-gateway" class="btn crystal-btn" onclick="executeWASend('gateway')" style="width: 100%; height: 48px; background: linear-gradient(135deg, #15803d 0%, #047857 100%); border: 2px solid #4ade80; color: #ffffff; font-weight: 900; font-size: 1.18rem; padding: 4px 16px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 16px rgba(21,128,61,0.45); cursor: pointer;" title="إرسال كمبيالة السند كملف PDF معتمد وموثق عبر الواتساب">
                            <span style="font-size: 1.45rem;">📄⚡</span> <span id="btn-wa-send-gateway-text">إرسال كمبيالة السند المعتمدة (ملف PDF) عبر الواتساب</span>
                        </button>
                    </div>

                    <!-- 7. شريط الإعدادات والإلغاء السفلي -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1.5px solid rgba(255,255,255,0.1); padding-top: 6px;">
                        <button type="button" class="btn crystal-btn btn-ghost" onclick="closeWAVoucherModal()" style="font-size: 0.85rem; font-weight: 800; padding: 4px 14px;">إلغاء</button>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #86efac; font-size: 0.84rem; font-weight: 900; margin: 0; background: rgba(37,211,102,0.1); border: 1.5px dashed rgba(37,211,102,0.4); padding: 3px 10px; border-radius: 6px;">
                            <input type="checkbox" id="wa-modal-auto-dispatch-sync" onchange="toggleWAAutoDispatch(this.checked)" style="width: 15px; height: 15px; cursor: pointer; accent-color: #25d366;">
                            <span>⚡ إرسال تلقائي دائم فور حفظ السند</span>
                        </label>
                    </div>

                    <div id="wa-modal-status-alert" style="display: none; padding: 5px 8px; border-radius: 6px; font-size: 0.84rem; font-weight: 800;"></div>
                </div>

                <!-- ════════ العمود الأيسر (لوحة كمبيالة السند المعتمدة الكاملة - Full Promissory Note Document) ════════ -->
                <div style="display: flex; flex-direction: column; background: #070d18; border: 1.5px solid rgba(251, 191, 36, 0.4); border-radius: 10px; padding: 8px 10px; overflow: hidden; box-shadow: inset 0 0 30px rgba(0,0,0,0.65);">
                    
                    <!-- ترويسة لوحة الكمبيالة والتبويبات المدمجة -->
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid rgba(255,255,255,0.12); padding-bottom: 5px; margin-bottom: 5px; flex-shrink: 0; flex-wrap: wrap; gap: 5px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: #fbbf24; font-size: 0.86rem; font-weight: 900; display: flex; align-items: center; gap: 5px;">
                                <span>📜</span> <span>المعاينة المعتمدة:</span>
                            </span>
                        </div>
                        
                        <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                            <!-- تبويب المستلم والصارف في وضع كليهما معاً -->
                            <div id="wa-both-tabs-toolbar" style="display: flex; gap: 4px;">
                                <button type="button" id="btn-wa-both-tab-debtor" onclick="switchWABothPreviewTab('debtor')" style="background: rgba(16, 185, 129, 0.35); border: 1.5px solid #10b981; color: #34d399; font-size: 0.78rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;">
                                    📥 المستلم
                                </button>
                                <button type="button" id="btn-wa-both-tab-creditor" onclick="switchWABothPreviewTab('creditor')" style="background: rgba(255, 255, 255, 0.08); border: 1.5px solid rgba(255, 255, 255, 0.25); color: #cbd5e1; font-size: 0.78rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;">
                                    📤 الصارف
                                </button>
                            </div>
                            <!-- زر تحميل PDF الفوري -->
                            <button type="button" onclick="downloadPromissoryPDF()" class="btn crystal-btn" style="background: linear-gradient(135deg, #b45309, #78350f); border: 1px solid #fbbf24; color: #fef08a; font-size: 0.76rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;" title="تنزيل كمبيالة السند كملف PDF معتمد">
                                📥 PDF
                            </button>
                            <!-- زر الطباعة المباشرة -->
                            <button type="button" onclick="printPromissoryNote()" class="btn crystal-btn" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25); color: #f1f5f9; font-size: 0.76rem; font-weight: 800; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="طباعة كمبيالة السند">
                                🖨️ طباعة
                            </button>
                            <!-- زر نسخ النص بالكامل -->
                            <button type="button" onclick="copyFullTextMessage()" class="btn crystal-btn" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25); color: #f1f5f9; font-size: 0.76rem; font-weight: 800; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="نسخ نص الكمبيالة بالكامل إلى الحافظة">
                                📋 نسخ
                            </button>
                            <!-- زر التبديل بين كمبيالة PDF والنص -->
                            <button type="button" id="btn-wa-toggle-preview-mode" onclick="toggleWAPreviewMode()" class="btn crystal-btn" style="background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); color: #38bdf8; font-size: 0.76rem; font-weight: 900; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="تبديل العرض بين كمبيالة PDF والنص">
                                📄 PDF / 📝 نص
                            </button>
                        </div>
                    </div>

                    <!-- مساحة عرض كمبيالة السند الكاملة (Certified PDF Document & Monospace Area) -->
                    <div style="flex: 1; min-height: 0; display: flex; flex-direction: column; position: relative;">
                        <!-- حاوية المعاينة الحية لكمبيالة السند الرسمية المعتمدة (PDF Document Live Preview) -->
                        <div id="wa-promissory-preview-wrapper" style="flex: 1; min-height: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; background: #0c1424; border-radius: 8px; border: 1.5px solid rgba(251, 191, 36, 0.35); padding: 8px; box-sizing: border-box;">
                            <div id="wa-promissory-note-container" style="background: #ffffff; color: #0f172a; border-radius: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); font-family: 'Tajawal', sans-serif; direction: rtl; width: 100%;">
                            </div>
                        </div>

                        <!-- صندوق نص كمبيالة كليهما معاً (Hidden by default, available when user toggles text mode) -->
                        <div id="wa-preview-both-box" style="display: none; flex: 1; min-height: 0; flex-direction: column;">
                            <textarea id="wa-both-preview-message" class="form-input" oninput="saveWABothPreviewEdit(this.value)" style="width: 100%; height: 100%; flex: 1; background: #050b14; border: 1.5px solid rgba(251, 191, 36, 0.35); color: #f8fafc; font-size: 0.84rem; line-height: 1.55; font-weight: 600; border-radius: 7px; resize: none; padding: 8px 10px; font-family: 'Consolas', 'Courier New', monospace; box-sizing: border-box; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); white-space: pre-wrap; word-break: break-word;"></textarea>
                        </div>

                        <!-- صندوق نص كمبيالة الطرف الفردي -->
                        <div id="wa-preview-single-box" style="display: none; flex: 1; min-height: 0; flex-direction: column;">
                            <textarea id="wa-modal-message" class="form-input" style="width: 100%; height: 100%; flex: 1; background: #050b14; border: 1.5px solid rgba(251, 191, 36, 0.35); color: #f8fafc; font-size: 0.84rem; line-height: 1.55; font-weight: 600; border-radius: 7px; resize: none; padding: 8px 10px; font-family: 'Consolas', 'Courier New', monospace; box-sizing: border-box; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); white-space: pre-wrap; word-break: break-word;"></textarea>
                        </div>

                        <!-- حاوية كارت السند المصور اللحظي (Graphic Preview Container) -->
                        <div id="wa-modal-image-preview-container" style="display: none; flex: 1; min-height: 0; align-items: center; justify-content: center; background: rgba(0,0,0,0.65); border: 1.5px solid rgba(251,191,36,0.35); border-radius: 7px; overflow: hidden; padding: 6px; position: relative;">
                            <img id="wa-modal-image-preview" src="" alt="كارت السند المصور" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.85);">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.74rem; font-weight: 800; color: #94a3b8;">
                        <span>💡 ورقة كمبيالة معتمدة ومقيدة</span>
                        <span style="color: #fbbf24;">⚡ قيد آلي رقمي</span>
                    </div>
                </div>

            </div>

            <!-- Visual corner resize indicator -->
            <div class="wa-resize-handle-corner" title="اسحب لتغيير الحجم">◢</div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/promissory-flight-animation.js?v=<?php echo time(); ?>"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=<?php echo time(); ?>"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          setTimeout(() => {
              const fromSearch = document.getElementById('from-account-search');
              if (fromSearch) {
                  fromSearch.focus();
                  fromSearch.select();
              }
          }, 300);
      });
    </script>
    <script>
        window.SERVER_SINGLE_PAYMENTS = <?php echo $SERVER_SINGLE_PAYMENTS_json; ?>;
    </script>
    <script defer src="js/master-banks-helper.js?v=<?php echo file_exists('js/master-banks-helper.js') ? filemtime('js/master-banks-helper.js') : '1'; ?>"></script>
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

                let nextAction = null;

                if (id === 'from-account-search' || id === 'to-account-search') {
                    nextAction = () => {
                        let type = id.split('-')[0];
                        let list = document.getElementById(type + '-account-dropdown-list');
                        if (list && list.classList.contains('show')) {
                            let firstItem = list.querySelector('.custom-dropdown-item');
                            if (firstItem && typeof firstItem.onmousedown === 'function') {
                                firstItem.onmousedown(); // This calls selectAccount and auto-focuses
                            }
                        } else {
                            if (id === 'from-account-search') {
                                const toSearch = document.getElementById('to-account-search');
                                if (toSearch) { toSearch.focus(); toSearch.select(); }
                            } else {
                                const amts = document.querySelectorAll('.transfer-amount');
                                if (amts.length > 0) { amts[0].focus(); amts[0].select(); }
                            }
                        }
                    };
                } else if (current.classList.contains('transfer-amount')) {
                    nextAction = () => {
                        const row = current.closest('.transfer-row');
                        if (row) {
                            const bank = row.querySelector('.transfer-note');
                            if (bank) { bank.focus(); bank.select(); }
                        }
                    };
                } else if (current.classList.contains('transfer-note')) {
                    nextAction = () => {
                        const row = current.closest('.transfer-row');
                        if (row) {
                            const no = row.querySelector('.transfer-no');
                            if (no) { no.focus(); no.select(); }
                        }
                    };
                } else if (current.classList.contains('transfer-no')) {
                    nextAction = () => {
                        const row = current.closest('.transfer-row');
                        if (row) {
                            const statement = row.querySelector('.transfer-statement');
                            if (statement) { statement.focus(); statement.select(); }
                        }
                    };
                } else if (current.classList.contains('transfer-statement')) {
                    nextAction = () => {
                        if (typeof addTransferRow === 'function') {
                            addTransferRow('', '', 0);
                            setTimeout(() => {
                                const amts = document.querySelectorAll('.transfer-amount');
                                if (amts.length > 0) {
                                    const lastAmt = amts[amts.length - 1];
                                    lastAmt.focus();
                                    lastAmt.select();
                                }
                            }, 100);
                        }
                    };
                } else if (!id) {
                    return; // Ignore other inputs without IDs
                } else if (id === 'amount-local') {
                    nextAction = () => { 
                        document.getElementById('to-account-search').focus();
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
                const container = document.getElementById('transfers-container');
                if (container && container.children.length === 0 && typeof addTransferRow === 'function') {
                    addTransferRow('', '', 0);
                }
                window.togglePaymentMethod();
            }, 50);
        });
    </script>

    <script>
    let hasWarnedAboutEdit = false;

    document.addEventListener('DOMContentLoaded', () => {
        // Intercept save by wrapping the original function unconditionally
        if (typeof window.submitVoucher === 'function') {
            const originalSubmit = window.submitVoucher;
            window.submitVoucher = function(...args) {
                // Check financial limits for voucher amount
                const voucherAmt = parseFloat(document.getElementById('amount')?.value) || 0;
                if (window.MagaieesPermissions && typeof window.MagaieesPermissions.checkFinancialLimits === 'function') {
                    const check = window.MagaieesPermissions.checkFinancialLimits('voucher_amount', voucherAmt);
                    if (!check.allowed) {
                        window.MagaieesPermissions.requestManagerApproval({
                            title: 'تجاوز سقف الصرف المالي',
                            description: check.message + ' يرجى إدخال رمز المشرف لاعتماد صرف هذا المبلغ الاستثنائي.',
                            requiredLevel: 6,
                            onSuccess: () => {
                                hasWarnedAboutEdit = true;
                                return originalSubmit.apply(this, args);
                            }
                        });
                        return;
                    }
                }

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
        </div>
        <div class="props-item" style="justify-content: center; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            <button id="save-props-btn" onclick="saveNumberProperties(); event.stopPropagation();" style="background: #10b981; color: #fff; border: none; padding: 6px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.2s;">💾 حفظ الخصائص</button>
        </div>
    </div>
    <!-- Financial OCR Modal & Script -->
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
</body>
</html>











<!-- FORCE CACHE BUST: 10001 -->
