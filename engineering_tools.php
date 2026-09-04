<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - حزمة المساعدات ومفاتيح البنوك والموسوعة الكبرى لفتاوى البيوع V12
// كوكبة الأئمة الـ 12 + مكتبة أمهات الكتب + القواعد الفقهية الـ 5 + معجم المصطلحات + محرك البحث الذكي
// ====================================================================
$page_title = 'الأدوات المساعدة والحاسبات ومفاتيح تحويلات البنوك وفتاوى البيوع';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مفاتيح تحويلات البنوك وموسوعة فتاوى البيوع | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800;900&family=Cairo:wght@300;400;600;700;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <!-- Fiqh Encyclopedia is Lazy-Loaded on demand for ultra-fast page rendering -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 10, 26, 0.85);
            --border-neon: rgba(236, 72, 153, 0.25);
            --text-main: #ffffff;
            --text-muted: #94a3b8;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --panel-bg: #ffffff;
            --border-neon: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            overflow-x: hidden;
        }

        .tools-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 10px 14px 60px 14px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── أزرار التبويبات العشرة البارزة ثلاثية الأبعاد ── */
        .suite-pill-card {
            padding: 14px 8px;
            border-radius: 20px;
            min-height: 120px;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 2px solid var(--p-border, rgba(255, 255, 255, 0.12));
            background: linear-gradient(145deg, rgba(20, 27, 45, 0.92), rgba(10, 15, 28, 0.98));
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.45), inset 0 1px 2px rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(15px);
        }

        .suite-pill-card:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: var(--p-color, #ec4899);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6), 0 0 20px var(--p-color);
        }

        .suite-pill-card.active {
            background: linear-gradient(145deg, rgba(236, 72, 153, 0.35), rgba(168, 85, 247, 0.45)) !important;
            border-color: #ec4899 !important;
            box-shadow: 0 12px 30px rgba(236, 72, 153, 0.5), 0 0 25px #ec4899 !important;
            transform: translateY(-4px) scale(1.03);
        }

        .suite-pill-icon {
            font-size: 1.8rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 3px 8px var(--p-color, #ec4899));
            transition: all 0.3s ease;
        }

        .suite-pill-title {
            font-size: 1.05rem;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.3px;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.8);
            margin: 2px 0;
            line-height: 1.2;
        }

        .suite-pill-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 700;
            white-space: nowrap;
        }

        .glass-panel-v3 {
            background: var(--panel-bg);
            border: 2px solid var(--border-color, rgba(236, 72, 153, 0.3));
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
            position: relative;
        }

        :root.light-theme .glass-panel-v3 {
            background: #ffffff !important;
            border: 2px solid #cbd5e1 !important;
            color: #000000 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }

        .tool-input {
            background: rgba(15, 23, 42, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1rem;
            outline: none;
            width: 100%;
            transition: all 0.2s;
        }

        :root.light-theme .tool-input {
            background: #f8fafc;
            border: 2px solid #94a3b8;
            color: #000000;
        }

        .tool-input:focus {
            border-color: #ec4899;
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.35);
        }

        .calc-result-box {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.12), rgba(168, 85, 247, 0.12));
            border: 2px solid rgba(236, 72, 153, 0.3);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        :root.light-theme .calc-result-box {
            background: #fdf2f8;
            border: 2px solid #db2777;
        }

        /* ── بطاقات البنوك الفخمة والمفاتيح البارزة ── */
        .bban-bank-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(6, 10, 20, 0.99));
            border: 2px solid rgba(56, 189, 248, 0.3);
            border-radius: 22px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.12);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
        }

        .bban-bank-card:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: #38bdf8;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7), 0 0 25px rgba(56, 189, 248, 0.35);
        }

        .bban-code-box {
            background: #000000;
            border: 2px solid rgba(56, 189, 248, 0.6);
            border-radius: 14px;
            padding: 14px 16px;
            text-align: center;
            font-family: 'Outfit', 'JetBrains Mono', 'Courier New', monospace;
            font-size: 1.45rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: #38bdf8;
            box-shadow: inset 0 3px 10px rgba(0, 0, 0, 0.9), 0 0 20px rgba(56, 189, 248, 0.2);
            direction: ltr;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }

        /* ── المنصة الرئيسية العليا لتوليد الـ BBAN ── */
        .master-bban-hero-box {
            background: linear-gradient(145deg, rgba(8, 14, 28, 0.98), rgba(2, 6, 15, 0.99));
            border: 3px solid #f59e0b;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.85), 0 0 40px rgba(245, 158, 11, 0.35);
            position: relative;
            overflow: hidden;
        }

        .master-bban-display-screen {
            background: #000000;
            border: 3px solid #10b981;
            border-radius: 22px;
            padding: 22px 26px;
            text-align: center;
            font-family: 'Outfit', 'JetBrains Mono', 'Courier New', monospace;
            font-size: 2.6rem;
            font-weight: 900;
            letter-spacing: 4px;
            color: #38bdf8;
            box-shadow: inset 0 4px 18px rgba(0, 0, 0, 0.95), 0 0 35px rgba(16, 185, 129, 0.45);
            direction: ltr;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .master-bban-display-screen {
                font-size: 1.6rem;
                padding: 14px;
                letter-spacing: 1.5px;
            }
        }

        /* ── فتاوى البيوع الفخمة ── */
        .fiqh-master-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(6, 10, 20, 0.98));
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 22px;
            padding: 22px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        }

        .fiqh-master-card:hover {
            border-color: #fbbf24;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.7), 0 0 25px rgba(251, 191, 36, 0.3);
            transform: translateY(-3px);
        }

        .fiqh-filter-btn {
            padding: 7px 13px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 800;
            background: rgba(15, 23, 42, 0.9);
            color: #cbd5e1;
            border: 2px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .fiqh-filter-btn:hover {
            border-color: #fbbf24;
            color: #fbbf24;
            transform: translateY(-2px);
        }

        .fiqh-filter-btn.active {
            background: linear-gradient(135deg, #d97706, #b45309) !important;
            border-color: #fbbf24 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
        }

        .fiqh-chip {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            background: rgba(30, 41, 59, 0.8);
            color: #93c5fd;
            border: 1px solid rgba(147, 197, 253, 0.3);
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .fiqh-chip:hover {
            background: #0284c7;
            color: #ffffff;
            border-color: #38bdf8;
        }

        .fiqh-nav-pill {
            padding: 10px 18px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 900;
            background: rgba(15, 23, 42, 0.9);
            color: #94a3b8;
            border: 2px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fiqh-nav-pill:hover {
            border-color: #fbbf24;
            color: #fbbf24;
        }

        .fiqh-nav-pill.active {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border-color: #fbbf24 !important;
            color: #020617 !important;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim-suite { animation: fadeInUp 0.35s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .tools-master-wrapper { padding: 0 !important; }
            .glass-panel-v3 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    
        /* 📱 ULTRA-MODERN MOBILE-FIRST RESPONSIVE DESIGN 📱 */
        @media (max-width: 768px) {
            .tools-master-wrapper {
                padding: 10px !important;
            }
            .glass-panel-v3 {
                padding: 14px !important;
                border-radius: 18px !important;
            }
            #suite-tabs-container {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }
            .suite-pill-card {
                padding: 10px 8px !important;
                border-radius: 14px !important;
            }
            .suite-pill-icon {
                font-size: 1.1rem !important;
            }
            .suite-pill-title {
                font-size: 0.78rem !important;
            }
            .suite-pill-sub {
                display: none !important;
            }
            #master_bban_display {
                font-size: 1.25rem !important;
                letter-spacing: 1px !important;
                padding: 12px 6px !important;
            }
            .fiqh-nav-pill {
                font-size: 0.72rem !important;
                padding: 6px 10px !important;
            }
            .fiqh-chip {
                font-size: 0.7rem !important;
                padding: 4px 8px !important;
            }
        }

    
        /* 🌟 MODERN CATEGORY PILL BUTTONS (LARGE & CLEAR) 🌟 */
        .category-pill-btn {
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 800;
            background: rgba(15, 23, 42, 0.95);
            color: #f1f5f9;
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .category-pill-btn:hover {
            border-color: #fbbf24;
            color: #fbbf24;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            background: rgba(30, 41, 59, 0.95);
        }

    
        /* 🌟 LUXURY SEARCH CONSOLE & HARMONIZED BUTTONS 🌟 */
        .fiqh-luxury-search-wrapper {
            position: relative;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(10, 15, 28, 0.99) 100%);
            border: 2px solid rgba(251, 191, 36, 0.5);
            border-radius: 24px;
            padding: 20px 24px;
            box-shadow: 
                0 14px 34px -6px rgba(0, 0, 0, 0.65), 
                0 0 25px -4px rgba(245, 158, 11, 0.2), 
                inset 0 1px 2px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            transition: all 0.3s ease;
        }

        .fiqh-luxury-search-wrapper:hover {
            border-color: rgba(251, 191, 36, 0.7);
            box-shadow: 
                0 18px 42px -6px rgba(0, 0, 0, 0.75), 
                0 0 32px -2px rgba(245, 158, 11, 0.28), 
                inset 0 1px 2px rgba(255, 255, 255, 0.12);
        }

        .fiqh-luxury-search-inner {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        @media (min-width: 768px) {
            .fiqh-luxury-search-inner {
                flex-direction: row;
                align-items: center;
                gap: 16px;
            }
        }

        /* The Input Box */
        .fiqh-luxury-input {
            width: 100%;
            height: 56px;
            padding: 10px 20px 10px 52px;
            background: rgba(8, 12, 22, 0.96) !important;
            border: 2px solid rgba(251, 191, 36, 0.45) !important;
            color: #fbbf24 !important;
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            border-radius: 16px !important;
            box-shadow: inset 0 3px 10px rgba(0, 0, 0, 0.7) !important;
            outline: none !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .fiqh-luxury-input:focus {
            border-color: #f59e0b !important;
            background: rgba(6, 10, 18, 0.98) !important;
            box-shadow: 
                inset 0 3px 10px rgba(0, 0, 0, 0.8), 
                0 0 24px rgba(245, 158, 11, 0.38) !important;
        }

        .fiqh-luxury-clear-btn {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #94a3b8;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fiqh-luxury-clear-btn:hover {
            color: #ffffff;
            background: rgba(239, 68, 68, 0.85);
            border-color: #ef4444;
            transform: translateY(-50%) scale(1.1);
        }

        /* Modern Elegant Buttons with Dual Harmony */
        .fiqh-btn-glow {
            position: relative;
            height: 56px;
            padding: 0 26px;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
            overflow: hidden;
        }

        .fiqh-btn-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: transform 0.25s ease;
        }

        .fiqh-btn-glow:hover .fiqh-btn-icon-wrap {
            transform: scale(1.18);
        }

        /* زر البحث داخل البرنامج - Golden Amber Luxury */
        .fiqh-btn-program-glow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 60%, #b45309 100%);
            color: #0f172a;
            border: 1.5px solid #fde68a;
            box-shadow: 
                0 6px 20px rgba(217, 119, 6, 0.35), 
                inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .fiqh-btn-program-glow:hover {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 60%, #d97706 100%);
            color: #020617;
            transform: translateY(-2px);
            box-shadow: 
                0 12px 28px rgba(245, 158, 11, 0.55), 
                0 0 20px rgba(251, 191, 36, 0.4);
            border-color: #ffffff;
        }

        .fiqh-btn-program-glow:active {
            transform: translateY(1px);
            box-shadow: 0 3px 10px rgba(217, 119, 6, 0.4);
        }

        /* زر بحث جوجل - Royal Sky/Blue Google Luxury */
        .fiqh-btn-google-glow {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 60%, #0369a1 100%);
            color: #ffffff;
            border: 1.5px solid #7dd3fc;
            box-shadow: 
                0 6px 20px rgba(2, 132, 199, 0.35), 
                inset 0 1px 1px rgba(255, 255, 255, 0.3);
        }

        .fiqh-btn-google-glow:hover {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 60%, #0284c7 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 
                0 12px 28px rgba(2, 132, 199, 0.55), 
                0 0 20px rgba(56, 189, 248, 0.4);
            border-color: #ffffff;
        }

        .fiqh-btn-google-glow:active {
            transform: translateY(1px);
            box-shadow: 0 3px 10px rgba(2, 132, 199, 0.4);
        }


        /* ══════════════════════════════════════════════════════════════════════
           UNIFIED MASTER CARD: المسائل والفتاوى الأكثر شيوعاً في مربع واحد مدمج
           ══════════════════════════════════════════════════════════════════════ */
        .fiqh-unified-master-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.98) 0%, rgba(10, 15, 28, 0.99) 100%);
            border: 2px solid rgba(251, 191, 36, 0.4);
            border-radius: 26px;
            padding: 24px;
            box-shadow: 
                0 18px 45px -8px rgba(0, 0, 0, 0.75), 
                0 0 28px -4px rgba(245, 158, 11, 0.18), 
                inset 0 1px 2px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .fiqh-unified-master-card:hover {
            border-color: rgba(251, 191, 36, 0.65);
            box-shadow: 
                0 22px 55px -8px rgba(0, 0, 0, 0.85), 
                0 0 35px -2px rgba(245, 158, 11, 0.28);
        }

        .fiqh-unified-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid rgba(251, 191, 36, 0.25);
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .fiqh-header-badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.12));
            border: 1.5px solid rgba(251, 191, 36, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fbbf24;
            font-size: 1.25rem;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.22);
        }

        .fiqh-unified-title {
            font-size: 1.35rem;
            font-weight: 900;
            color: #fef08a;
            letter-spacing: -0.01em;
            margin: 0;
            line-height: 1.2;
        }

        .fiqh-reset-btn {
            padding: 8px 18px;
            border-radius: 12px;
            background: rgba(244, 63, 94, 0.14);
            border: 1.5px solid rgba(244, 63, 94, 0.4);
            color: #fda4af;
            font-size: 0.85rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        .fiqh-reset-btn:hover {
            background: rgba(244, 63, 94, 0.3);
            border-color: #f43f5e;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(244, 63, 94, 0.35);
        }

        .fiqh-categories-merged-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 900px) {
            .fiqh-categories-merged-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
        }

        .fiqh-cat-subgroup {
            background: rgba(11, 17, 32, 0.8);
            border-radius: 20px;
            padding: 18px;
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            gap: 14px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fiqh-cat-cyan {
            border-color: rgba(6, 182, 212, 0.35);
            background: linear-gradient(145deg, rgba(8, 25, 42, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        }
        .fiqh-cat-cyan:hover {
            border-color: rgba(6, 182, 212, 0.65);
            box-shadow: 0 8px 24px rgba(6, 182, 212, 0.18);
        }

        .fiqh-cat-emerald {
            border-color: rgba(16, 185, 129, 0.35);
            background: linear-gradient(145deg, rgba(6, 32, 24, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        }
        .fiqh-cat-emerald:hover {
            border-color: rgba(16, 185, 129, 0.65);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.18);
        }

        .fiqh-cat-amber {
            border-color: rgba(245, 158, 11, 0.35);
            background: linear-gradient(145deg, rgba(38, 26, 10, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        }
        .fiqh-cat-amber:hover {
            border-color: rgba(245, 158, 11, 0.65);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.18);
        }

        .fiqh-cat-purple {
            border-color: rgba(168, 85, 247, 0.35);
            background: linear-gradient(145deg, rgba(30, 16, 44, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        }
        .fiqh-cat-purple:hover {
            border-color: rgba(168, 85, 247, 0.65);
            box-shadow: 0 8px 24px rgba(168, 85, 247, 0.18);
        }

        .fiqh-cat-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 10px;
        }

        .fiqh-cat-tag {
            font-size: 0.98rem;
            font-weight: 900;
            color: #f8fafc;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .fiqh-cat-count {
            font-size: 0.72rem;
            font-weight: 800;
            font-family: monospace;
            padding: 3px 10px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.45);
            border-width: 1px;
            border-style: solid;
        }

        .fiqh-cat-pills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* The Pills */
        .fiqh-pill-btn {
            padding: 9px 15px;
            border-radius: 13px;
            font-size: 0.86rem;
            font-weight: 800;
            background: rgba(15, 23, 42, 0.92);
            color: #e2e8f0;
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.35);
            user-select: none;
        }

        .fiqh-pill-btn:hover {
            transform: translateY(-2px) scale(1.02);
        }

        .fiqh-pill-cyan:hover {
            border-color: #22d3ee;
            color: #22d3ee;
            background: rgba(8, 51, 68, 0.95);
            box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
        }

        .fiqh-pill-emerald:hover {
            border-color: #34d399;
            color: #34d399;
            background: rgba(6, 78, 59, 0.95);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .fiqh-pill-amber:hover {
            border-color: #fbbf24;
            color: #fbbf24;
            background: rgba(69, 26, 3, 0.95);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        .fiqh-pill-purple:hover {
            border-color: #c084fc;
            color: #c084fc;
            background: rgba(59, 7, 100, 0.95);
            box-shadow: 0 6px 16px rgba(168, 85, 247, 0.4);
        }

        .fiqh-pill-btn:active {
            transform: translateY(1px);
        }


        /* Master All-Transactions Button in Common Fiqh Box */
        .fiqh-all-transactions-master-btn {
            width: 100%;
            padding: 14px 22px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(180, 83, 9, 0.12) 100%);
            border: 2px solid rgba(251, 191, 36, 0.45);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35);
            user-select: none;
        }

        .fiqh-all-transactions-master-btn:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.28) 0%, rgba(217, 119, 6, 0.22) 100%);
            border-color: #fbbf24;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .fiqh-all-transactions-master-btn.active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.35) 0%, rgba(217, 119, 6, 0.28) 100%);
            border-color: #fde047;
            box-shadow: 0 0 24px rgba(245, 158, 11, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.25);
        }


        .fiqh-pill-btn.active {
            border-color: #22d3ee !important;
            color: #ffffff !important;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.45) 0%, rgba(8, 51, 68, 0.95) 100%) !important;
            box-shadow: 0 0 18px rgba(6, 182, 212, 0.5) !important;
            transform: translateY(-2px);
        }


        /* Scholar Dropdown Selector in Search Console */
        .fiqh-scholar-dropdown-wrap {
            position: relative;
            min-width: 230px;
        }

        .fiqh-luxury-select-scholar {
            width: 100%;
            height: 56px;
            padding: 0 18px 0 42px !important; /* RTL: left padding for chevron */
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(20, 30, 48, 0.98) 100%) !important;
            border: 1.5px solid rgba(251, 191, 36, 0.5) !important;
            color: #fef08a !important;
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            border-radius: 16px !important;
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.4), 
                inset 0 1px 1px rgba(255, 255, 255, 0.1) !important;
            outline: none !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .fiqh-luxury-select-scholar:hover {
            border-color: #fbbf24 !important;
            box-shadow: 
                0 8px 24px rgba(245, 158, 11, 0.28), 
                0 0 16px rgba(251, 191, 36, 0.25) !important;
            transform: translateY(-1px);
        }

        .fiqh-luxury-select-scholar:focus {
            border-color: #f59e0b !important;
            box-shadow: 
                0 0 20px rgba(245, 158, 11, 0.45), 
                inset 0 1px 2px rgba(255, 255, 255, 0.15) !important;
        }

        .fiqh-luxury-select-scholar option {
            background: #0f172a !important;
            color: #f8fafc !important;
            font-weight: 700 !important;
            padding: 10px !important;
        }

        .fiqh-select-chevron {
            position: absolute;
            left: 16px; /* RTL position */
            top: 50%;
            transform: translateY(-50%);
            color: #fbbf24;
            font-size: 0.85rem;
            pointer-events: none;
            transition: transform 0.2s ease;
        }

        .fiqh-scholar-dropdown-wrap:hover .fiqh-select-chevron {
            transform: translateY(-50%) scale(1.2);
        }


        .fiqh-cat-indigo {
            border-color: rgba(99, 102, 241, 0.35);
            background: linear-gradient(145deg, rgba(16, 22, 50, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
        }
        .fiqh-cat-indigo:hover {
            border-color: rgba(99, 102, 241, 0.65);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.2);
        }
        .fiqh-pill-indigo:hover {
            border-color: #818cf8;
            color: #818cf8;
            background: rgba(30, 27, 75, 0.95);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }

</style>
</head>

<body class="no-page-transition">

    <div class="tools-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/85 border-2 border-pink-500/40 p-5 rounded-2xl shadow-2xl backdrop-blur-2xl">
            <div>
                <a href="magaiees_unified_dashboard.php" class="inline-flex items-center gap-2 text-pink-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع للوحة القيادة الموحدة (Unified Dashboard)
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-toolbox text-pink-400"></i>
                    مركز الأدوات والمساعدات ومفاتيح تحويلات البنوك (BBAN)
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">
                    حاسبات أوزان الحديد، الخرسانات، الطاقة والمضخات، الجمارك، مفاتيح التحويلات البنكية السودانية (BBAN)، فتاوى البيوع، والزكاة.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.print();" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-black rounded-xl text-xs transition flex items-center gap-1.5 border border-slate-700 shadow-md">
                    <i class="fa-solid fa-print"></i> طباعة التقرير / الحسابات
                </button>
            </div>
        </div>

        <!-- 🎛️ 10 PROMINENT SUITE TABS (3D CENTERED NEUMORPHIC) 🎛️ -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-10 gap-2.5" id="suite-tabs-container">
            
            <!-- 1. الحديد -->
            <div class="suite-pill-card active" style="--p-color:#ec4899; --p-border:rgba(236,72,153,0.5);" onclick="switchSuite('steel', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-cubes-stacked text-pink-400"></i></div>
                <div class="suite-pill-title">🔩 الحديد</div>
                <div class="suite-pill-sub">الأوزان والقطع</div>
            </div>

            <!-- 2. الخرسانات -->
            <div class="suite-pill-card" style="--p-color:#a78bfa; --p-border:rgba(167,139,250,0.5);" onclick="switchSuite('concrete', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-building text-purple-400"></i></div>
                <div class="suite-pill-title">🏗️ الخرسانة</div>
                <div class="suite-pill-sub">الأسقف والمونة</div>
            </div>

            <!-- 3. الطاقة الشمسية -->
            <div class="suite-pill-card" style="--p-color:#f59e0b; --p-border:rgba(245,158,11,0.5);" onclick="switchSuite('solar', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-solar-panel text-amber-400"></i></div>
                <div class="suite-pill-title">☀️ الطاقة</div>
                <div class="suite-pill-sub">المضخات والألواح</div>
            </div>

            <!-- 4. الجمارك والاستيراد -->
            <div class="suite-pill-card" style="--p-color:#38bdf8; --p-border:rgba(56,189,248,0.5);" onclick="switchSuite('freight', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-ship text-sky-400"></i></div>
                <div class="suite-pill-title">🚢 الجمارك</div>
                <div class="suite-pill-sub">الواصل والحاويات</div>
            </div>

            <!-- 5. التحويلات الهندسية -->
            <div class="suite-pill-card" style="--p-color:#34d399; --p-border:rgba(52,211,153,0.5);" onclick="switchSuite('units', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-calculator text-emerald-400"></i></div>
                <div class="suite-pill-title">📐 التحويلات</div>
                <div class="suite-pill-sub">الأراضي والقياسات</div>
            </div>

            <!-- 6. مفاتيح التحويلات البنكية BBAN -->
            <div class="suite-pill-card" style="--p-color:#22d3ee; --p-border:rgba(34,211,238,0.5);" onclick="switchSuite('banks', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-money-bill-transfer text-cyan-400"></i></div>
                <div class="suite-pill-title">🏦 مفاتيح التحويلات</div>
                <div class="suite-pill-sub">صيغ BBAN للبنوك (16)</div>
            </div>

            <!-- 7. فتاوى البيوع -->
            <div class="suite-pill-card" style="--p-color:#fbbf24; --p-border:rgba(251,191,36,0.5);" onclick="switchSuite('fiqh', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-book-quran text-amber-400"></i></div>
                <div class="suite-pill-title">📜 فتاوى البيع والمعاملات</div>
                <div class="suite-pill-sub">12 إماماً وموسوعة</div>
            </div>

            <!-- 8. حاسبة الزكاة -->
            <div class="suite-pill-card" style="--p-color:#10b981; --p-border:rgba(167,243,208,0.5);" onclick="switchSuite('zakat', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-hand-holding-dollar text-emerald-400"></i></div>
                <div class="suite-pill-title">⚖️ الزكاة</div>
                <div class="suite-pill-sub">عروض التجارة</div>
            </div>

            <!-- 9. الجدوى والأرباح -->
            <div class="suite-pill-card" style="--p-color:#f43f5e; --p-border:rgba(244,63,94,0.5);" onclick="switchSuite('feasibility', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-chart-line text-rose-400"></i></div>
                <div class="suite-pill-title">📊 الجدوى</div>
                <div class="suite-pill-sub">التعادل والهامش</div>
            </div>

            <!-- 10. نهاية الخدمة -->
            <div class="suite-pill-card" style="--p-color:#e879f9; --p-border:rgba(232,121,249,0.5);" onclick="switchSuite('hr_gratuity', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-user-tie text-fuchsia-400"></i></div>
                <div class="suite-pill-title">👥 نهاية الخدمة</div>
                <div class="suite-pill-sub">قوانين العمل والرواتب</div>
            </div>

        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 1. SUITE: STEEL & METALS WEIGHT & PIECE CALCULATOR               -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-steel" class="suite-section anim-suite space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(236, 72, 153, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-cubes-stacked text-pink-400"></i>
                        حاسبة أوزان وقطع حديد التسليح والمواسير والعلب والزنك الأمريكي
                    </h3>
                    <span class="text-xs font-bold text-pink-400 bg-pink-500/10 px-3 py-1 rounded-lg border border-pink-500/20">
                        معادلات التصنيع القياسية (ISO / DIN)
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">نوع القطاع / المنتج:</label>
                                <select id="steel_product_type" onchange="calculateSteel()" class="tool-input">
                                    <option value="rebar">حديد تسليح مباني (Rebar Bars)</option>
                                    <option value="pipe_rect">مواسير وعلب مستطيلة/مربعة (4×8، 2×4، 3×6 سم)</option>
                                    <option value="pipe_round">مواسير دائرية (Round Pipes)</option>
                                    <option value="sheet_zinc">صاج وزنك أمريكي مضلع وألواح مسطحة</option>
                                    <option value="beam_ipe">كمارات وقنوات (IPE / HEA / UPN)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المقاس المعتمد / السُمك:</label>
                                <select id="steel_preset_size" onchange="calculateSteel()" class="tool-input">
                                    <option value="rebar_8mm">تسليح 8 مم (لينة وربع - 12 متر)</option>
                                    <option value="rebar_10mm">تسليح 10 مم (3 لينة - 12 متر)</option>
                                    <option value="rebar_12mm" selected>تسليح 12 مم (4 لينة - 12 متر)</option>
                                    <option value="rebar_16mm">تسليح 16 مم (5 لينة - 12 متر)</option>
                                    <option value="rebar_20mm">تسليح 20 مم (6 لينة - 12 متر)</option>
                                    <option value="rebar_25mm">تسليح 25 مم (8 لينة - 12 متر)</option>
                                    <option value="rebar_32mm">تسليح 32 مم (10 لينة - 12 متر)</option>
                                    <option value="4x8_1.5">ماسورة علبة 4 × 8 سم (سُمك 1.5 مم - 6م)</option>
                                    <option value="4x8_2.0">ماسورة علبة 4 × 8 سم (سُمك 2.0 مم - 6م)</option>
                                    <option value="2x4_1.5">ماسورة علبة 2 × 4 سم (سُمك 1.5 مم - 6م)</option>
                                    <option value="2x4_2.0">ماسورة علبة 2 × 4 سم (سُمك 2.0 مم - 6م)</option>
                                    <option value="zinc_0.3">زنك أمريكي 0.30 مم (3.0م × 0.9م)</option>
                                    <option value="zinc_0.4">زنك أمريكي 0.40 مم (3.0م × 0.9م)</option>
                                    <option value="custom">مقاس وسُمك مخصص (Custom)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">القطر / العرض (مم):</label>
                                <input type="number" id="steel_width_mm" value="12" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الارتفاع (مم):</label>
                                <input type="number" id="steel_height_mm" value="12" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">السُمك (مم):</label>
                                <input type="number" step="0.1" id="steel_thickness_mm" value="12" oninput="calculateSteel()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الطول (متر):</label>
                                <input type="number" step="0.5" id="steel_length_m" value="12.0" oninput="calculateSteel()" class="tool-input">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-300 block mb-1">الكمية المطلوبة (طن أو حبة):</label>
                            <div class="flex items-center gap-3">
                                <input type="number" id="steel_target_qty" value="1.0" oninput="calculateSteel()" class="tool-input font-bold text-lg text-pink-400">
                                <select id="steel_target_unit" onchange="calculateSteel()" class="tool-input w-40">
                                    <option value="ton">طن حديد (Ton)</option>
                                    <option value="pcs">عدد الحبات / الأسياخ (Pcs)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">📦 عدد الأسياخ / القطع في الطن الواحد</span>
                            <span class="text-4xl font-black text-pink-400 mt-1 block font-mono" id="res_pieces_per_ton">0</span>
                            <span class="text-xs text-pink-300/80 font-bold mt-1 block">سيخ / حبة في الطن المتري</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">⚖️ وزن السيخ / القطعة الواحدة</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_weight_per_piece">0.00 kg</span>
                            <span class="text-xs text-sky-300/80 font-bold mt-1 block">كيلوجرام للقطعة</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">📊 ملخص الكمية المطلوبة</span>
                            <span class="text-xl font-black text-emerald-400 mt-1 block font-mono" id="res_total_summary">0 قطعة = 0.00 طن</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 2. SUITE: CONCRETE, SLABS & FINISHING CALCULATOR                 -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-concrete" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(167, 139, 250, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-building text-purple-400"></i>
                        حاسبة كميات الخرسانة، الأسقف، البلوك، والتشطيبات والسيراميك
                    </h3>
                    <span class="text-xs font-bold text-purple-400 bg-purple-500/10 px-3 py-1 rounded-lg border border-purple-500/20">
                        تقدير دقيق للمواد الخام والمقاولات
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">نوع العنصر الإنشائي / السقف:</label>
                                <select id="concrete_type" onchange="calculateConcrete()" class="tool-input">
                                    <option value="beam_slab">سقف كمري عادي (Beam Slab)</option>
                                    <option value="hordy">سقف هوردي (Hollow Block / Joist Slab)</option>
                                    <option value="flat_slab">سقف فلات سلاب (Flat Slab)</option>
                                    <option value="building_skeleton">هيكل كامل (قواعد + أعمدة + سملات + أسقف)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">مساحة المسطح ($m^2$):</label>
                                <input type="number" id="concrete_area" value="150" oninput="calculateConcrete()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">سُمك البلاطة ($cm$):</label>
                                <input type="number" id="concrete_thickness" value="15" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">عدد الطوابق:</label>
                                <input type="number" id="concrete_floors" value="1" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">نسبة التسليح ($kg/m^3$):</label>
                                <input type="number" id="concrete_rebar_ratio" value="110" oninput="calculateConcrete()" class="tool-input">
                            </div>
                        </div>

                        <!-- Finishing Extractor -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-purple-950/20 p-4 rounded-xl border border-purple-500/30">
                            <div>
                                <label class="text-xs font-bold text-purple-300 block mb-1">🧱 جدران البلوك (مساحة الجدران $m^2$):</label>
                                <input type="number" id="wall_area_m2" value="200" oninput="calculateConcrete()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-purple-300 block mb-1">🏺 أرضيات السيراميك ($m^2$):</label>
                                <input type="number" id="tiles_area_m2" value="150" oninput="calculateConcrete()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Concrete Results -->
                    <div class="space-y-2.5">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">🏗️ حجم الخرسانة المسلحة المطلوبة</span>
                            <span class="text-3xl font-black text-purple-400 mt-1 block font-mono" id="res_concrete_m3">0.00 m³</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">🔩 إجمالي حديد التسليح المطلوب</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_rebar_tons">0.00 طن</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-bold text-center">
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">أكياس الأسمنت:</span>
                                <span class="text-amber-400 text-lg block font-mono" id="res_cement_bags">0</span>
                            </div>
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">طوب / بلوك الجدران:</span>
                                <span class="text-pink-400 text-lg block font-mono" id="res_wall_blocks">0 حبة</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-bold text-center">
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">كراتين السيراميك (1.44m²):</span>
                                <span class="text-emerald-400 text-base block font-mono" id="res_tiles_boxes">0 كرتونة</span>
                            </div>
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800">
                                <span class="text-slate-400 block">أكياس غراء السيراميك:</span>
                                <span class="text-cyan-400 text-base block font-mono" id="res_tiles_glue">0 كيس</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 3. SUITE: SOLAR & DEEP WELL PUMPS CALCULATOR                     -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-solar" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(245, 158, 11, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-solar-panel text-amber-400"></i>
                        حاسبة منظومات الطاقة الشمسية، البطاريات، ومضخات الآبار والري الغاطسة
                    </h3>
                    <span class="text-xs font-bold text-amber-400 bg-amber-500/10 px-3 py-1 rounded-lg border border-amber-500/20">
                        للمنازل والمزارع والمشاريع الإنتاجية
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <!-- Mode selector -->
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-bold text-slate-300">نوع النظام:</label>
                            <select id="solar_sys_mode" onchange="toggleSolarMode()" class="tool-input flex-1">
                                <option value="home">منظومة منازل وشركات (إنفرتر + ألواح + بطاريات)</option>
                                <option value="pump">منظومة مضخة ري آبار غاطسة زراعية (Solar VFD Pumping)</option>
                            </select>
                        </div>

                        <!-- Home inputs -->
                        <div id="solar_home_inputs" class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">مكيفات فريون (1.5 طن):</label>
                                <input type="number" id="solar_ac_count" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">ثلاجات وفريزرات:</label>
                                <input type="number" id="solar_fridge_count" value="1" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">إنارة ومراوح وشاشات (W):</label>
                                <input type="number" id="solar_lights_watt" value="500" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">ساعات تشغيل البطاريات ليلاً:</label>
                                <input type="number" id="solar_night_hours" value="8" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">قدرة اللوح الشمسي (وات):</label>
                                <input type="number" id="solar_panel_watt" value="550" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">نوع البطارية المفضل:</label>
                                <select id="solar_bat_type" onchange="calculateSolar()" class="tool-input">
                                    <option value="lithium">ليثيوم LifePO4 (48V)</option>
                                    <option value="gel">جل / أسيد Tubular (48V)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pump inputs -->
                        <div id="solar_pump_inputs" class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-amber-950/20 p-4 rounded-xl border border-amber-500/30 hidden">
                            <div>
                                <label class="text-xs font-bold text-amber-300 block mb-1">قدرة المضخة الغاطسة (حصان HP):</label>
                                <input type="number" id="solar_pump_hp_val" value="10" oninput="calculateSolar()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-amber-300 block mb-1">عمق البئر الإجمالي (متر):</label>
                                <input type="number" id="solar_well_depth_m" value="70" oninput="calculateSolar()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-amber-300 block mb-1">قدرة اللوح الشمسي (وات):</label>
                                <input type="number" id="solar_pump_panel_w" value="550" oninput="calculateSolar()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Solar Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">⚡ قدرة الإنفرتر الموصى به (Inverter / VFD)</span>
                            <span class="text-3xl font-black text-amber-400 mt-1 block font-mono" id="res_inverter_kw">0 kW</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">☀️ عدد ألواح الطاقة الشمسية</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_panel_count">0 لوح</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">🔋 سعة بنك البطاريات المطلوب</span>
                            <span class="text-2xl font-black text-sky-400 mt-1 block font-mono" id="res_battery_ah">0 Ah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 4. SUITE: FREIGHT, LANDED COST & CONTAINER PLANNER              -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-freight" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(56, 189, 248, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-ship text-sky-400"></i>
                        حاسبة تكاليف الاستيراد والجمارك والشحن والواصل وسعة الحاويات (Landed Cost)
                    </h3>
                    <span class="text-xs font-bold text-sky-400 bg-sky-500/10 px-3 py-1 rounded-lg border border-sky-500/20">
                        حساب دقيق لتكلفة الصنف الواصل للمخزن
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">قيمة فاتورة المصنع FOB ($):</label>
                                <input type="number" id="freight_fob_usd" value="50000" oninput="calculateFreight()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">تكلفة الشحن البحري والنولون ($):</label>
                                <input type="number" id="freight_sea_usd" value="3500" oninput="calculateFreight()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الرسوم الجمركية (%):</label>
                                <input type="number" id="freight_customs_pct" value="15" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">الموانئ والتخليص ($):</label>
                                <input type="number" id="freight_clearance_usd" value="1200" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">النقل البري الداخلي ($):</label>
                                <input type="number" id="freight_inland_usd" value="800" oninput="calculateFreight()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 block mb-1">سعر صرف الدولار (SDG):</label>
                                <input type="number" id="freight_fx_rate" value="600" oninput="calculateFreight()" class="tool-input font-bold">
                            </div>
                        </div>

                        <!-- Container Planner Quick -->
                        <div class="bg-sky-950/20 p-4 rounded-xl border border-sky-500/30 space-y-2">
                            <span class="text-xs font-bold text-sky-300 block">📦 سعة الحاوية البحرية (Container Capacity Planner):</span>
                            <div class="grid grid-cols-3 gap-3 text-xs">
                                <div class="bg-slate-900/80 p-2 rounded-xl text-center">
                                    <span class="text-slate-400 block">حاوية 20 قدم:</span>
                                    <span class="text-sky-400 font-bold font-mono">33.2 CBM (~21.7 طن)</span>
                                </div>
                                <div class="bg-slate-900/80 p-2 rounded-xl text-center">
                                    <span class="text-slate-400 block">حاوية 40 قدم:</span>
                                    <span class="text-sky-400 font-bold font-mono">67.7 CBM (~26.5 طن)</span>
                                </div>
                                <div class="bg-slate-900/80 p-2 rounded-xl text-center">
                                    <span class="text-slate-400 block">حاوية 40 HQ:</span>
                                    <span class="text-sky-400 font-bold font-mono">76.3 CBM (~26.5 طن)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Freight Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box">
                            <span class="text-xs text-slate-400 font-bold block">💵 التكلفة الإجمالية الواصلة بالدولار</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_landed_usd">$0</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">💰 التكلفة الإجمالية بالعملة المحلية</span>
                            <span class="text-2xl font-black text-emerald-400 mt-1 block font-mono" id="res_landed_sdg">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(245, 158, 11, 0.3);">
                            <span class="text-xs text-slate-400 font-bold block">📈 نسبة المصاريف المضافة إلى الشراء</span>
                            <span class="text-2xl font-black text-amber-400 mt-1 block font-mono" id="res_overhead_pct">%0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 5. SUITE: UNIVERSAL UNIT & LAND CONVERTER                        -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-units" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(52, 211, 153, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-emerald-400"></i>
                        حاسبة التحويلات الهندسية ومقاييس الأراضي والمساحات والأوزان الشاملة
                    </h3>
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                        تحويلات سريعة ومباشرة
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Land Area -->
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-emerald-400 block">🌾 مساحات الأراضي (متر مربع ⬅️ فدان)</span>
                        <label class="text-[11px] text-slate-400 block">متر مربع ($m^2$):</label>
                        <input type="number" id="unit_sqm" value="4200" oninput="convertUnits('sqm')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">فدان سوداني (4200 متر مربع):</label>
                        <input type="number" id="unit_feddan" value="1" oninput="convertUnits('feddan')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">قيراط (24 قيراط = 1 فدان):</label>
                        <input type="number" id="unit_qirat" value="24" oninput="convertUnits('qirat')" class="tool-input">
                    </div>

                    <!-- Inches to mm -->
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-pink-400 block">📏 الأطوال والمقاسات (بوصة ⬅️ مليمتر)</span>
                        <label class="text-[11px] text-slate-400 block">بوصة (Inch):</label>
                        <input type="number" id="unit_inch" value="4" oninput="convertUnits('inch')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">مليمتر (mm):</label>
                        <input type="number" id="unit_mm" value="101.6" oninput="convertUnits('mm')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">قدم (Foot = 12 Inch):</label>
                        <input type="number" id="unit_foot" value="0.333" oninput="convertUnits('foot')" class="tool-input">
                    </div>

                    <!-- Weight Units -->
                    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <span class="text-xs font-bold text-sky-400 block">⚖️ الأوزان والمحاصيل (كجم ⬅️ طن ⬅️ قنطار)</span>
                        <label class="text-[11px] text-slate-400 block">كيلوجرام (kg):</label>
                        <input type="number" id="unit_kg" value="1000" oninput="convertUnits('kg')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">طن متري (Ton = 1000 kg):</label>
                        <input type="number" id="unit_ton" value="1" oninput="convertUnits('ton')" class="tool-input">
                        <label class="text-[11px] text-slate-400 block mt-2">قنطار سمسم/صمغ (100 رطل = 45.36 kg):</label>
                        <input type="number" id="unit_qantar" value="22.04" oninput="convertUnits('qantar')" class="tool-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 6. SUITE: BBAN SUDANESE BANK ROUTING & TRANSFER GUIDE           -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-banks" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(34, 211, 238, 0.4);">
                
                <!-- 👑👑 MASTER LUXURY HERO CARD: THE 3 CORE ELEMENTS IN GOLDEN YELLOW & EXTRA LARGE FONTS 👑👑 -->
                <div class="master-bban-hero-box mb-8">
                    
                    <!-- Header of the Hero Section -->
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-amber-500/40 pb-5 mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="px-4 py-1.5 bg-amber-500/20 text-amber-300 rounded-xl text-sm font-mono font-black border-2 border-amber-400 shadow-md">
                                    ★ المنظومة الموحدة للتحويلات البنكية (BBAN Engine)
                                </span>
                                <span class="px-3.5 py-1.5 bg-emerald-500/20 text-emerald-300 rounded-xl text-sm font-black border-2 border-emerald-400 shadow-md">
                                    14 خانة معتمدة ✓
                                </span>
                            </div>
                            <h2 class="text-2xl md:text-4xl font-black text-white flex items-center gap-3">
                                <i class="fa-solid fa-building-columns text-amber-400"></i>
                                المنصة الذكية لتوليد وتركيب أرقام الـ BBAN للتحويل المباشر
                            </h2>
                            <p class="text-sm md:text-base font-bold text-slate-300 mt-1.5">
                                الخطوات الثلاث الرئيسية: <span class="text-amber-300 font-black">1) اختيار اسم البنك</span> ⬅️ <span class="text-amber-300 font-black">2) كتابة رقم الحساب</span> ⬅️ <span class="text-emerald-300 font-black">3) نسخ صيغة الـ BBAN المكتملة</span>
                            </p>
                        </div>

                        <!-- Search Bar -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">بحث سريع في دليل البنوك:</label>
                            <input type="text" id="bank-search-input" oninput="searchBanks(this.value)" placeholder="🔍 ابحث بالاسم أو التطبيق..." class="tool-input text-base font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ 1 & 2: TWO MASSIVE INPUT SELECTORS WITH GOLDEN YELLOW TYPOGRAPHY 🎛️ -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        
                        <!-- 1. اختيار اسم البنك المستفيد -->
                        <div class="bg-slate-900/95 p-6 rounded-3xl border-3 border-amber-500/50 shadow-xl space-y-3">
                            <label class="text-xl md:text-2xl font-black text-amber-400 flex items-center gap-2">
                                <span class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 font-black flex items-center justify-center text-lg shadow-md">1</span>
                                🏛️ اختيار اسم البنك المستفيد:
                            </label>
                            <select id="bban_bank_selector" onchange="generateUserBBAN()" style="color: #fbbf24; font-size: 1.55rem; font-weight: 900;" class="tool-input font-black py-4 px-5 bg-slate-950 border-3 border-amber-400 rounded-2xl cursor-pointer">
                                <option value="040|XXXXXXX|0001">1. بنك الخرطوم</option>
                                <option value="240|XXXXXXX|0001">2. بنك أم درمان الوطني</option>
                                <option value="02|XXXXXXXX|0001">3. بنك فيصل الإسلامي</option>
                                <option value="150000|XXXXXXXX|">4. بنك النيل الأزرق المشرق</option>
                                <option value="14000|XXXX|00001">5. البنك الإسلامي السوداني</option>
                                <option value="sahel">6. بنك النيل</option>
                                <option value="222|XXXXXXXXXXX|">7. بنك المزارع التجاري</option>
                                <option value="110|XXXXXX|00001">8. البنك الفرنسي السوداني</option>
                                <option value="03|XXXXXXX|01001">9. البنك الزراعي السوداني</option>
                                <option value="180|XXXXXX|00001">10. بنك السلام السوداني</option>
                                <option value="070|XXXXXX|00001">11. بنك البركة السوداني</option>
                                <option value="090|XXXXXX|00001">12. بنك الثروة الحيوانية</option>
                                <option value="120|XXXXXX|00001">13. بنك العمال الوطني</option>
                                <option value="160|XXXXXX|00001">14. البنك العقاري التجاري</option>
                                <option value="130|XXXXXX|00001">15. بنك التنمية الصناعية</option>
                                <option value="170|XXXXXX|00001">16. بنك المال المتحد</option>
                            </select>
                            <span class="text-xs text-slate-400 font-bold block mt-1" id="selected_bank_app_badge">
                                📱 التطبيق المعتمد: بنكك (Bank of Khartoum)
                            </span>
                        </div>

                        <!-- 2. كتابة رقم الحساب -->
                        <div class="bg-slate-900/95 p-6 rounded-3xl border-3 border-amber-500/50 shadow-xl space-y-3">
                            <label class="text-xl md:text-2xl font-black text-amber-400 flex items-center gap-2">
                                <span class="w-9 h-9 rounded-full bg-amber-400 text-slate-950 font-black flex items-center justify-center text-lg shadow-md">2</span>
                                🔢 كتابة رقم الحساب (بدون الصفر):
                            </label>
                            <input type="text" id="bban_raw_account" placeholder="اكتب رقم الحساب هنا" oninput="generateUserBBAN()" style="color: #fbbf24; font-size: 2.2rem; font-weight: 900; letter-spacing: 4px;" class="tool-input font-mono font-black py-4 px-6 bg-slate-950 border-3 border-amber-400 text-center rounded-2xl placeholder-slate-700 shadow-inner">
                            <div class="flex items-center justify-between text-xs font-black mt-1">
                                <span class="text-amber-300 font-bold" id="bban_strip_feedback">✓ يحذف الصفر في بداية الحساب تلقائياً</span>
                                <span class="text-rose-400 font-bold">⚠️ تنبيه: لا تكتب صفر في البداية</span>
                            </div>
                        </div>

                    </div>

                    <!-- 🌟 3. GRAND HERO DISPLAY: صيغة رقم الـ BBAN النهائي المكتمل 🌟 -->
                    <div class="bg-slate-950/95 p-6 md:p-8 rounded-3xl border-3 border-emerald-500/60 shadow-2xl space-y-4">
                        
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
                            <label class="text-lg md:text-2xl font-black text-emerald-400 flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xl shadow-md">3</span>
                                👑 صيغة رقم الـ BBAN النهائي المكتمل (جاهز للتحويل المباشر):
                            </label>
                            <span class="px-4 py-1.5 bg-emerald-500/20 text-emerald-300 border-2 border-emerald-400 rounded-xl font-mono font-black text-sm md:text-base" id="bban_len_counter">
                                عدد الخانات: 14 رقماً ✓
                            </span>
                        </div>

                        <!-- 🖥️ THE GIGANTIC DIGITAL SCREEN 🖥️ -->
                        <div class="master-bban-display-screen" id="generated_bban_display_wrapper">
                            <span id="generated_bban_display">040-------0001</span>
                        </div>

                        <!-- Huge Copy Button -->
                        <button type="button" onclick="copyGeneratedBBAN()" class="w-full py-4 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400 hover:from-emerald-400 hover:to-cyan-300 text-slate-950 font-black rounded-2xl text-base md:text-lg transition flex items-center justify-center gap-3 shadow-2xl transform active:scale-95">
                            <i class="fa-regular fa-copy text-xl"></i>
                            اضغط هنا لنسخ رقم الـ BBAN المكتمل الآن (جاهز للصق في تطبيق البنك)
                        </button>

                    </div>

                </div>

                <!-- ⚠️ CRITICAL NOTICE BANNER ⚠️ -->
                <div class="p-4 bg-amber-500/15 border-2 border-amber-500/50 rounded-2xl flex items-center gap-4 text-amber-200 font-bold shadow-xl mb-6">
                    <span class="text-3xl">💡</span>
                    <div>
                        <h4 class="text-base md:text-lg font-black text-amber-300">قاعدة ذهبية لجميع التحويلات: حذف الصفر في بداية رقم الحساب</h4>
                        <p class="text-xs md:text-sm text-slate-200 mt-0.5">
                            عند تركيب رقم الحساب داخل صيغة الـ BBAN، <span class="text-rose-400 font-black underline">يتم حذف أي صفر في بداية رقم الحساب الأساسي</span> حتى تنضبط خانات الـ BBAN وتكتمل الـ 14 خانة بنجاح دون أي زيادة أو خطأ في التحويل.
                        </p>
                    </div>
                </div>

                <!-- 📋 Full 16 Banks Directory Grid 📋 -->
                <div class="mb-4">
                    <h3 class="text-xl md:text-2xl font-black text-white flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-cyan-400"></i>
                        دليل وصيغ الـ BBAN لجميع البنوك الـ 16 (للمراجعة والنسخ الفردي):
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="banks-grid-container">

                    <!-- Bank 1: بنك الخرطوم -->
                    <div class="bban-bank-card" data-bank-search="بنك الخرطوم تطبيق بنكك (bankak) 040xxxxxxx0001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    1
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/25 text-emerald-300 border-emerald-400 shadow-sm">
                                    📱 تطبيق بنكك (Bankak)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك الخرطوم
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 040 + 7 أرقام حساب (بدون صفر البداية) + اللاحقة 0001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">040</span><span class="text-white font-black px-1">XXXXXXX</span><span class="text-emerald-400 font-black">0001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('040XXXXXXX0001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 2: بنك أم درمان الوطني -->
                    <div class="bban-bank-card" data-bank-search="بنك أم درمان الوطني تطبيق أوكاش (o-cash) 240xxxxxxx0001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    2
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-sky-500/25 text-sky-300 border-sky-400 shadow-sm">
                                    📱 تطبيق أوكاش (O-Cash)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك أم درمان الوطني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 240 + 7 أرقام حساب (بدون صفر البداية) + اللاحقة 0001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">240</span><span class="text-white font-black px-1">XXXXXXX</span><span class="text-emerald-400 font-black">0001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('240XXXXXXX0001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 3: بنك فيصل الإسلامي -->
                    <div class="bban-bank-card" data-bank-search="بنك فيصل الإسلامي تطبيق فوري (fawry) 02xxxxxxxx0001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    3
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-400 shadow-sm">
                                    📱 تطبيق فوري (Fawry)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك فيصل الإسلامي
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 02 + 8 أرقام حساب (بدون صفر البداية) + اللاحقة 0001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">02</span><span class="text-white font-black px-1">XXXXXXXX</span><span class="text-emerald-400 font-black">0001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('02XXXXXXXX0001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 4: بنك النيل الأزرق المشرق -->
                    <div class="bban-bank-card" data-bank-search="بنك النيل الأزرق المشرق تطبيق مشرق (mashreq) 150000xxxxxxxx">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    4
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-yellow-500/25 text-yellow-300 border-yellow-400 shadow-sm">
                                    📱 تطبيق مشرق (Mashreq)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك النيل الأزرق المشرق
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 150000 + 8 أرقام حساب (بدون صفر البداية)
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">150000</span><span class="text-white font-black px-1">XXXXXXXX</span><span class="text-emerald-400 font-black"></span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('150000XXXXXXXX', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 5: البنك الإسلامي السوداني -->
                    <div class="bban-bank-card" data-bank-search="البنك الإسلامي السوداني تطبيق آمن بلس (amen plus) 14000xxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    5
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-purple-500/25 text-purple-300 border-purple-400 shadow-sm">
                                    📱 تطبيق آمن بلس (Amen Plus)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك الإسلامي السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 14000 + 4 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">14000</span><span class="text-white font-black px-1">XXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('14000XXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 6: بنك النيل -->
                    <div class="bban-bank-card" data-bank-search="بنك النيل تطبيق ساهل (sahel) يستخرج من داخل التطبيق">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    6
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-cyan-500/25 text-cyan-300 border-cyan-400 shadow-sm">
                                    📱 تطبيق ساهل (Sahel)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك النيل
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                يتم نسخ رمز الـ BBAN مباشرة من الملف الشخصي داخل تطبيق ساهل
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-300 font-bold text-lg md:text-xl tracking-normal">يستخرج من داخل التطبيق</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('يستخرج من داخل التطبيق', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 7: بنك المزارع التجاري -->
                    <div class="bban-bank-card" data-bank-search="بنك المزارع التجاري تطبيق مصرفك (masrafak) 222xxxxxxxxxxx">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    7
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/25 text-emerald-300 border-emerald-400 shadow-sm">
                                    📱 تطبيق مصرفك (Masrafak)
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك المزارع التجاري
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 222 + 11 رقم حساب (بدون صفر البداية)
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">222</span><span class="text-white font-black px-1">XXXXXXXXXXX</span><span class="text-emerald-400 font-black"></span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('222XXXXXXXXXXX', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 8: البنك الفرنسي السوداني -->
                    <div class="bban-bank-card" data-bank-search="البنك الفرنسي السوداني الفرنسي موبايل 110xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    8
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-sky-500/25 text-sky-300 border-sky-400 shadow-sm">
                                    📱 الفرنسي موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك الفرنسي السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 110 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">110</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('110XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 9: البنك الزراعي السوداني -->
                    <div class="bban-bank-card" data-bank-search="البنك الزراعي السوداني الزراعي موبايل 03xxxxxxx01001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    9
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-lime-500/25 text-lime-300 border-lime-400 shadow-sm">
                                    📱 الزراعي موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك الزراعي السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 03 + 7 أرقام حساب + اللاحقة 01001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">03</span><span class="text-white font-black px-1">XXXXXXX</span><span class="text-emerald-400 font-black">01001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('03XXXXXXX01001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 10: بنك السلام السوداني -->
                    <div class="bban-bank-card" data-bank-search="بنك السلام السوداني السلام موبايل 180xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    10
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-400 shadow-sm">
                                    📱 السلام موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك السلام السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 180 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">180</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('180XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 11: بنك البركة السوداني -->
                    <div class="bban-bank-card" data-bank-search="بنك البركة السوداني البركة موبايل 070xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    11
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-rose-500/25 text-rose-300 border-rose-400 shadow-sm">
                                    📱 البركة موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك البركة السوداني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 070 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">070</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('070XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 12: بنك الثروة الحيوانية -->
                    <div class="bban-bank-card" data-bank-search="بنك الثروة الحيوانية الثروة كاش 090xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    12
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-emerald-500/25 text-emerald-300 border-emerald-400 shadow-sm">
                                    📱 الثروة كاش
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك الثروة الحيوانية
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 090 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">090</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('090XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 13: بنك العمال الوطني -->
                    <div class="bban-bank-card" data-bank-search="بنك العمال الوطني العمال موبايل 120xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    13
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-sky-500/25 text-sky-300 border-sky-400 shadow-sm">
                                    📱 العمال موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك العمال الوطني
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 120 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">120</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('120XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 14: البنك العقاري التجاري -->
                    <div class="bban-bank-card" data-bank-search="البنك العقاري التجاري العقاري كاش 160xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    14
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-purple-500/25 text-purple-300 border-purple-400 shadow-sm">
                                    📱 العقاري كاش
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                البنك العقاري التجاري
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 160 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">160</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('160XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 15: بنك التنمية الصناعية -->
                    <div class="bban-bank-card" data-bank-search="بنك التنمية الصناعية الصناعي موبايل 130xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    15
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-amber-500/25 text-amber-300 border-amber-400 shadow-sm">
                                    📱 الصناعي موبايل
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك التنمية الصناعية
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 130 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">130</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('130XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                    <!-- Bank 16: بنك المال المتحد -->
                    <div class="bban-bank-card" data-bank-search="بنك المال المتحد المال كاش 170xxxxxx00001">
                        <div class="space-y-2">
                            <!-- Top Row: Badge + Number -->
                            <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2.5">
                                <span class="w-10 h-10 rounded-xl bg-slate-950 text-cyan-400 font-black text-lg flex items-center justify-center font-mono border-2 border-cyan-500/40 shadow-inner">
                                    16
                                </span>
                                <span class="text-xs md:text-sm font-black px-3.5 py-1.5 rounded-xl border-2 bg-cyan-500/25 text-cyan-300 border-cyan-400 shadow-sm">
                                    📱 المال كاش
                                </span>
                            </div>

                            <!-- Bank Name Prominent Large -->
                            <h4 class="text-xl md:text-2xl font-black text-white pt-1">
                                بنك المال المتحد
                            </h4>
                            <p class="text-xs text-slate-300 font-bold leading-relaxed">
                                المفتاح 170 + 6 أرقام حساب + اللاحقة 00001
                            </p>
                        </div>

                        <!-- 🌟 PROMINENT BIG CODE BOX (EXTRA LARGE TYPOGRAPHY) 🌟 -->
                        <div class="my-2 bg-slate-950/90 p-3 rounded-2xl border border-slate-800">
                            <div class="flex items-center justify-between mb-1.5 text-xs font-bold text-slate-400">
                                <span>صيغة الـ BBAN الرسمية:</span>
                                <span class="text-rose-400 text-[11px] font-black">⚠️ يُحذف الصفر بالبداية</span>
                            </div>
                            <div class="bban-code-box">
                                <span class="text-amber-400 font-black">170</span><span class="text-white font-black px-1">XXXXXX</span><span class="text-emerald-400 font-black">00001</span>
                            </div>
                        </div>

                        <!-- Copy Button -->
                        <button type="button" onclick="copyBBANCode('170XXXXXX00001', this)" class="w-full py-3 bg-slate-900 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border-2 border-cyan-500/50 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 shadow-lg">
                            <i class="fa-regular fa-copy text-base"></i> نسخ صيغة المفتاح
                        </button>
                    </div>
    
                </div>

                <!-- 💡 Smart Instructions from Card 💡 -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-800">
                    <div class="p-4 bg-slate-950/80 border-2 border-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-300 text-2xl flex items-center justify-center flex-shrink-0">
                            🔍
                        </div>
                        <div>
                            <h5 class="text-sm md:text-base font-black text-white">راجع رقم BBAN بعناية</h5>
                            <p class="text-xs text-slate-300 font-bold">تأكد من مطابقة عدد الخانات (14 رقماً) وحذف الصفر في بداية الحساب.</p>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-950/80 border-2 border-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-300 text-2xl flex items-center justify-center flex-shrink-0">
                            👤
                        </div>
                        <div>
                            <h5 class="text-sm md:text-base font-black text-white">تطابق اسم المستفيد</h5>
                            <p class="text-xs text-slate-300 font-bold">لا تُكمل التحويل إلا بعد ظهور ومطابقة اسم المستفيد الرباعي على الشاشة.</p>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-950/80 border-2 border-slate-800 rounded-2xl flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-300 text-2xl flex items-center justify-center flex-shrink-0">
                            💵
                        </div>
                        <div>
                            <h5 class="text-sm md:text-base font-black text-white">رسوم التحويل بين البنوك</h5>
                            <p class="text-xs text-slate-300 font-bold">تختلف الرسوم بين التطبيقات وغالباً ما تكون بين 400 - 500 جنيه.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 7. SUITE: MEGA FIQH PLATFORM (12 SCHOLARS, BOOKS, MAXIMS, TERMS)-->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-fiqh" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(251, 191, 36, 0.45);">
                
                <!-- 1. SECTION MASTER HEADER (العنوان الصريح النظيف مع زيادة المسافة بمقدار 1.5) -->
                <div class="pb-4 mb-9">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-amber-300 flex items-center gap-3 tracking-wide drop-shadow-md">
                            <span class="w-11 h-11 rounded-2xl bg-amber-500/15 border border-amber-400/40 flex items-center justify-center text-amber-400 text-2xl shadow-inner">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </span>
                            <span>فتاوى البيع والمعاملات</span>
                        </h2>
                        <div class="hidden sm:flex items-center gap-2 text-xs font-mono font-bold text-slate-400 px-3 py-1.5 bg-slate-900/80 rounded-xl border border-slate-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>4,500 مسألة فقهية موثقة</span>
                        </div>
                    </div>
                    <!-- Decorative Golden Gradient Line -->
                    <div class="mt-4 h-[2px] w-full bg-gradient-to-r from-amber-500/60 via-amber-400/30 to-transparent rounded-full"></div>
                </div>

                <!-- 2. LUXURY CLEAN SEARCH CONSOLE (مربع بحث أنيق فاخر مع زري البحث المباشر وبحث جوجل المتناسقين) -->
                <div class="fiqh-luxury-search-wrapper mb-8">
                    <div class="fiqh-luxury-search-inner">
                        
                        <!-- Input Box with Inset Luxury Style -->
                        <div class="relative flex-1 min-w-[280px]">
                            <input type="text" id="fiqh-search-input" 
                                   oninput="filterFatwas()" 
                                   onkeydown="if(event.key==='Enter'){executeInternalFiqhSearch();}" 
                                   placeholder="" 
                                   autocomplete="off"
                                   spellcheck="false"
                                   class="fiqh-luxury-input">
                            <button type="button" onclick="clearFiqhInput()" class="fiqh-luxury-clear-btn" title="تفريغ خانة البحث">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Buttons Group: Perfectly Harmonized & Symmetrical -->
                        <div class="flex items-center gap-3 flex-shrink-0">
                            
                            <!-- الزر 1: البحث داخل البرنامج -->
                            <button type="button" onclick="executeInternalFiqhSearch()" class="fiqh-btn-glow fiqh-btn-program-glow">
                                <div class="fiqh-btn-icon-wrap">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>
                                <span>البحث داخل البرنامج</span>
                            </button>

                            <!-- الزر 2: بحث جوجل المشروط بالـ 12 -->
                            <button type="button" onclick="executeScopedGoogle12Search()" class="fiqh-btn-glow fiqh-btn-google-glow" title="بحث جوجل مقيد بالشيوخ الـ 12 المعتمدين والمواقع الشرعية">
                                <div class="fiqh-btn-icon-wrap">
                                    <i class="fa-brands fa-google"></i>
                                </div>
                                <span>بحث جوجل</span>
                            </button>

                            <!-- الزر 3 / القائمة المنسدلة: جميع الشيوخ الـ 12 المعتمدين -->
                            <div class="relative fiqh-scholar-dropdown-wrap">
                                <select id="fiqh-scholar-select" onchange="onScholarSelectChange(this.value)" class="fiqh-luxury-select-scholar" title="تصفية فتاوى الشيوخ والعلماء الـ 12 المعتمدين">
                                    <option value="all">🌟 جميع الشيوخ الـ 12</option>
                                    <option value="ibn_baz">📜 الإمام عبد العزيز بن باز</option>
                                    <option value="ibn_uthaymeen">💡 الإمام محمد بن صالح العثيمين</option>
                                    <option value="al_fawzan">⚖️ معالي الشيخ د. صالح الفوزان</option>
                                    <option value="ibn_taymiyyah">📖 شيخ الإسلام ابن تيمية</option>
                                    <option value="ibn_al_qayyim">✨ الإمام ابن قيم الجوزية</option>
                                    <option value="imam_ahmad">👑 الإمام أحمد بن حنبل</option>
                                    <option value="al_nawawi">📜 الإمام محيي الدين النووي</option>
                                    <option value="al_dhahabi">⚖️ الإمام الحافظ الذهبي</option>
                                    <option value="muhammad_bin_abdulwahhab">📚 الإمام محمد بن عبد الوهاب</option>
                                    <option value="al_saadi">💡 العلامة عبد الرحمن السعدي</option>
                                    <option value="al_shinqiti">📖 العلامة محمد الأمين الشنقيطي</option>
                                    <option value="al_albani">📚 الإمام محمد ناصر الدين الألباني</option>
                                </select>
                                <div class="fiqh-select-chevron">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- 3. UNIFIED MASTER BOX: المسائل وفتاوى المعاملات المالية الأكثر شيوعاً في مربع واحد مدمج وأنيق -->
                <div class="fiqh-unified-master-card mb-8">
                    
                    <!-- شريط عنوان المربع الموحد -->
                    <div class="fiqh-unified-header">
                        <div class="flex items-center gap-3">
                            <div class="fiqh-header-badge">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <h3 class="fiqh-unified-title">فتاوى المعاملات والمسائل المالية الأكثر شيوعاً</h3>
                            </div>
                        </div>
                        <button type="button" onclick="clearFiqhSearch()" class="fiqh-reset-btn" title="إعادة تعيين وتفريغ البحث">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>تفريغ البحث</span>
                        </button>
                    </div>

                    <!-- شبكة المربعات الخمسة المدمجة ومرصوصة رصاً أنيقاً خالية من التكرار -->
                    <div class="fiqh-categories-merged-grid">
                        
                        <!-- 1. التمويل والتقسيط والدين -->
                        <div class="fiqh-cat-subgroup fiqh-cat-cyan">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-credit-card text-cyan-400"></i>
                                    1. التمويل والتقسيط والديون
                                </span>
                                <span class="fiqh-cat-count text-cyan-400 border-cyan-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="setQuickSearch('التقسيط', this)">
                                    <span>💳</span> البيع بالتقسيط وزيادة الأجل
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="setQuickSearch('غرامات التأخير', this)">
                                    <span>⚠️</span> غرامات التأخير والشرط الجزائي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="setQuickSearch('المرابحة', this)">
                                    <span>🏢</span> المرابحة للآمر بالشراء
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="setQuickSearch('التورق', this)">
                                    <span>🔄</span> التورق للحصول على سيولة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="setQuickSearch('الإيجار المنتهي بالتمليك', this)">
                                    <span>🚗</span> الإيجار المنتهي بالتمليك
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="setQuickSearch('إنظار المعسر', this)">
                                    <span>⏳</span> إنظار المعسر والتيسير
                                </button>
                            </div>
                        </div>

                        <!-- 2. الأوراق التجارية والمدفوعات -->
                        <div class="fiqh-cat-subgroup fiqh-cat-emerald">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                                    2. الأوراق التجارية والمدفوعات
                                </span>
                                <span class="fiqh-cat-count text-emerald-400 border-emerald-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="setQuickSearch('خصم الشيكات', this)">
                                    <span>📜</span> خصم الشيكات والكمبيالات الآجلة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="setQuickSearch('العربون', this)">
                                    <span>🤝</span> بيع العربون وحجز البضاعة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="setQuickSearch('بيع الدين', this)">
                                    <span>💼</span> بيع الدين والمقاصة المالية
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="setQuickSearch('إقالة النادم', this)">
                                    <span>🔄</span> إقالة النادم والفسخ الرضائي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="setQuickSearch('السلم', this)">
                                    <span>📦</span> بيع السلم ودفع الثمن مقدماً
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="setQuickSearch('العقود والشروط', this)">
                                    <span>📋</span> العقود والشروط وحرية الاتفاق
                                </button>
                            </div>
                        </div>

                        <!-- 3. مقاولات السلع والضمان -->
                        <div class="fiqh-cat-subgroup fiqh-cat-purple">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-trowel-bricks text-purple-400"></i>
                                    3. مقاولات السلع والضمان
                                </span>
                                <span class="fiqh-cat-count text-purple-400 border-purple-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="setQuickSearch('بيع ما لا يملك', this)">
                                    <span>🛒</span> بيع ما لا يملك والوساطة التجارية
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="setQuickSearch('الاستصناع', this)">
                                    <span>🏗️</span> عقد الاستصناع وتوريد الحديد
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="setQuickSearch('السكراب', this)">
                                    <span>🔩</span> بيع حديد السكراب والخردة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="setQuickSearch('ضمان العيوب', this)">
                                    <span>🛡️</span> خيار العيب والضمان المصنعي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="setQuickSearch('حقوق العمال', this)">
                                    <span>👥</span> أجور العمال وحقوق الأجراء
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="setQuickSearch('التسعير', this)">
                                    <span>⚖️</span> التسعير وتطفيف الكيل والميزان
                                </button>
                            </div>
                        </div>

                        <!-- 4. الذهب والعملات والتمويل (والتحويلات) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-amber">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-coins text-amber-400"></i>
                                    4. الذهب والعملات والتحويلات
                                </span>
                                <span class="fiqh-cat-count text-amber-400 border-amber-500/30">4 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="setQuickSearch('الذهب', this)">
                                    <span>💎</span> شراء الذهب ببطاقات الصراف ومدى
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="setQuickSearch('الصرف بالموبايل', this)">
                                    <span>📱</span> الصرف بتطبيقات بنكك وفوري
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="setQuickSearch('العِينة', this)">
                                    <span>🚫</span> حيل الربا وبيوع العِينة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="setQuickSearch('العملات المشفرة', this)">
                                    <span>🪙</span> العملات الرقمية والفوركس
                                </button>
                            </div>
                        </div>

                        <!-- 5. جميع مسائل المعاملات المالية (العقود والشركات والخدمات الحديثة) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-indigo md:col-span-2">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-scale-balanced text-indigo-400"></i>
                                    5. جميع مسائل المعاملات المالية
                                </span>
                                <span class="fiqh-cat-count text-indigo-400 border-indigo-500/30">الموسوعة الشاملة (3,000+ مسألة)</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber active" onclick="showAllFiqhTransactions(this)" id="btn-show-all-fiqh" style="border-color: #fbbf24; background: rgba(245, 158, 11, 0.2); color: #fef08a;">
                                    <span>🌟</span> عرض كافة المسائل والمعاملات (الموسوعة الكاملة)
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الشركات والأسهم', this)">
                                    <span>🏢</span> الشركات والأسهم والمضاربة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('التجارة الإلكترونية', this)">
                                    <span>📱</span> التجارة الإلكترونية ونقاط البيع POS
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('التأمين', this)">
                                    <span>🛡️</span> التأمين التعاوني والتكافلي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الكفالة والضمان', this)">
                                    <span>🤝</span> الكفالة والضمان الشخصي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الفرنشايز', this)">
                                    <span>🏢</span> الامتياز التجاري (الفرنشايز)
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الاعتمادات', this)">
                                    <span>🚢</span> الاعتمادات والشحن الدولي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الزكاة وعروض التجارة', this)">
                                    <span>💰</span> الزكاة وعروض التجارة والديون
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الرهن', this)">
                                    <span>🏠</span> الرهن الحيازي والتجاري
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('الجوائح والظروف الطارئة', this)">
                                    <span>⚡</span> الجوائح والظروف الطارئة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('التفليس وقسمة الغرماء', this)">
                                    <span>⚖️</span> التفليس وقسمة الغرماء
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('نقاط المكافآت', this)">
                                    <span>🎁</span> نقاط المكافآت وبطاقات الولاء
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="setQuickSearch('التمويل الجماعي', this)">
                                    <span>👥</span> التمويل الجماعي والاستثماري
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 4. SUB-TABS NAVIGATION BAR (أقسام المنظومة الـ 6 في صندوق مستقل ومرتب) -->
                <div class="p-3 bg-slate-950/95 rounded-2xl border-2 border-slate-800 flex flex-wrap items-center gap-2 mb-6">
                    <button type="button" class="fiqh-nav-pill active" onclick="switchFiqhSubTab('fatwas', this)">
                        📑 موسوعة الفتاوى والأحكام (3,000 مسألة محررة لكبار الأئمة الـ 12)
                    </button>
                    <button type="button" class="fiqh-nav-pill bg-gradient-to-r from-sky-500/20 to-blue-600/30 text-sky-300 border-sky-400/60 hover:border-sky-300" onclick="switchFiqhSubTab('google', this)">
                        🌐 محرك بحث قوقل للشيوخ الـ 12 (المواقع العالمية)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('quran', this)">
                        📖 الدستور القرآني للمعاملات (15 آية محكمة)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('hadiths', this)">
                        📜 الموسوعة النبوية الصحيحة (32 حديثاً ثابتاً)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('books', this)">
                        📚 مكتبة أمهات كتب البيوع (26 مؤلفاً وموسوعة)
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('maxims', this)">
                        ⚖️ القواعد الفقهية الكبرى الـ 5 الحاكمة للتجارة
                    </button>
                    <button type="button" class="fiqh-nav-pill" onclick="switchFiqhSubTab('terms', this)">
                        📖 معجم المصطلحات الاقتصادية (6 مصطلحات)
                    </button>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 1: 80 EXTENDED FATWAS SUITE                     -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-fatwas" class="space-y-4">
                    
                    <!-- 👁️ VISIBILITY NOTIFICATION BANNER 👁️ -->
                    <div id="fatwas-visibility-banner" class="p-3.5 bg-slate-950/90 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-md">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">🌟</span>
                            <div>
                                <span class="text-xs sm:text-sm font-black text-amber-300 block">الموسوعة الكبرى المحررة لفقه المعاملات والبيوع الإسلامية:</span>
                                <span class="text-xs text-slate-300 font-bold">موسوعة محررة متكاملة بدون أي تكرار، مدعمة بالأحاديث النبوية الصحيحة والأدلة الشرعية لكبار الأئمة الـ 12 المعتمدين (21 مسألة في الصفحة).</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1.5 bg-emerald-500/20 text-emerald-300 font-black text-xs rounded-xl border border-emerald-500/40 flex items-center gap-1">
                                <i class="fa-solid fa-check-circle"></i> صفر تكرار (مسائل فريدة 100%)
                            </span>
                            <span class="px-3 py-1.5 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-500/40 flex items-center gap-1">
                                <i class="fa-solid fa-book-quran"></i> مدعمة بالأحاديث الصحيحة
                            </span>
                        </div>
                    </div>

                    <!-- 📑 1. TOP PROMINENT PAGINATION & PAGE CHANGER BAR 📑 -->
                    <div id="fiqh_pagination_bar_top" class="p-4 bg-slate-950/95 border-2 border-amber-500/60 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-xl mb-4">
                        <!-- Rendered by JavaScript -->
                    </div>

                    <!-- 🗂️ THE 21 FATWAS GRID (عرض 21 فتوى بالضبط في كل صفحة) 🗂️ -->
                    <div id="fiqh_fatwas_grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 min-h-[400px]">
                        <!-- Dynamic 21 Cards Rendered via JavaScript -->
                    </div>

                    <!-- 📑 2. BOTTOM PROMINENT PAGINATION & PAGE CHANGER BAR 📑 -->
                    <div id="fiqh_pagination_bar_bottom" class="p-4 bg-slate-950/95 border-2 border-amber-500/60 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-xl mt-4">
                        <!-- Rendered by JavaScript -->
                    </div>
                </div>

                <div id="fiqh-sub-google" class="space-y-4 hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-sky-950/40 to-slate-900 border-2 border-sky-500/50 rounded-2xl shadow-2xl space-y-4">
                        
                        <!-- Header -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="px-3 py-1 bg-sky-500/20 text-sky-300 font-mono font-black text-xs rounded-xl border border-sky-400">
                                        ★ محرك بحث قوقل الذكي الموثق (Google Search Engine)
                                    </span>
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                        حصر النتائج في الشيوخ الـ 12 والمواقع الرسمية فقط ✓
                                    </span>
                                </div>
                                <h3 class="text-xl md:text-2xl font-black text-sky-300 flex items-center gap-2">
                                    <i class="fa-brands fa-google text-sky-400"></i>
                                    البحث العالمي المباشر عن الفتاوى عبر قوقل للشيوخ الـ 12 المعتمدين
                                </h3>
                                <p class="text-xs md:text-sm text-slate-300 font-bold mt-1">
                                    يبحث هذا المحرك الذكي في شبكة الإنترنت العالمية مع <strong class="text-amber-300 font-black">إلزام محرك قوقل بعدم إظهار أي فتوى إلا إذا كانت صادرة عن أحد الأئمة الـ 12 المعتمدين حصراً</strong> ومن المواقع والمكتبات الإسلامية الرسمية والمحققة.
                                </p>
                            </div>
                            <span class="text-4xl hidden md:block">🌐</span>
                        </div>

                        <!-- Google Live Search Form -->
                        <div class="p-5 bg-slate-950/90 rounded-2xl border border-sky-500/30 space-y-4 shadow-inner">
                            
                            <!-- Search Input -->
                            <div>
                                <label class="text-xs md:text-sm font-black text-amber-300 block mb-1.5">
                                    🔍 اكتب سؤالك أو مسألتك الفقهية للبحث عنها في قوقل:
                                </label>
                                <div class="relative">
                                    <input type="text" id="google-fiqh-query" placeholder="مثال: حكم شراء سيارة أو معدات بالمرابحة البنكية، بيع سكراب، تأخير تسليم حديد..." class="tool-input text-sm md:text-base font-bold pr-4 pl-12 py-3 bg-slate-900 border-2 border-sky-500/40 focus:border-sky-400 text-white rounded-xl w-full">
                                    <button type="button" onclick="executeGoogleScholarSearch()" class="absolute left-2 top-2 px-4 py-1.5 bg-sky-500 hover:bg-sky-400 text-slate-950 font-black text-xs rounded-lg shadow transition">
                                        بحث ↗
                                    </button>
                                </div>
                            </div>

                            <!-- Filter Grid: Scholar + Target Domain -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                
                                <!-- Scholar Filter Selector -->
                                <div>
                                    <label class="text-xs font-black text-slate-300 block mb-1">
                                        👤 تخصيص الشيخ أو الإمام في قوقل:
                                    </label>
                                    <select id="google-scholar-select" class="tool-input text-xs font-black bg-slate-900 border border-slate-700 text-amber-300 py-2.5 rounded-xl w-full">
                                        <option value="all">🌟 جميع الأئمة والعلماء الـ 12 معاً (تلقائي)</option>
                                        <option value="ibn_baz">📜 الإمام عبد العزيز بن باز (رحمه الله)</option>
                                        <option value="ibn_uthaymeen">💡 الإمام محمد بن صالح العثيمين (رحمه الله)</option>
                                        <option value="al_fawzan">⚖️ معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</option>
                                        <option value="ibn_taymiyyah">📖 شيخ الإسلام ابن تيمية (رحمه الله)</option>
                                        <option value="ibn_al_qayyim">✨ الإمام ابن قيم الجوزية (رحمه الله)</option>
                                        <option value="imam_ahmad">👑 الإمام أحمد بن حنبل (رحمه الله)</option>
                                        <option value="al_nawawi">📜 الإمام محيي الدين النووي (رحمه الله)</option>
                                        <option value="al_dhahabi">⚖️ الإمام الحافظ الذهبي (رحمه الله)</option>
                                        <option value="muhammad_bin_abdulwahhab">📚 الإمام محمد بن عبد الوهاب (رحمه الله)</option>
                                        <option value="al_saadi">💡 العلامة الشيخ عبد الرحمن السعدي (رحمه الله)</option>
                                        <option value="al_shinqiti">📖 العلامة محمد الأمين الشنقيطي (رحمه الله)</option>
                                        <option value="al_albani">📚 الإمام محمد ناصر الدين الألباني (رحمه الله)</option>
                                    </select>
                                </div>

                                <!-- Target Sites Selector -->
                                <div>
                                    <label class="text-xs font-black text-slate-300 block mb-1">
                                        🏛️ نطاق المواقع والمكتبات العالمية المعتمدة:
                                    </label>
                                    <select id="google-sites-select" class="tool-input text-xs font-black bg-slate-900 border border-slate-700 text-emerald-300 py-2.5 rounded-xl w-full">
                                        <option value="all_approved">🌐 كافة المواقع الرسمية والمكتبة الشاملة والدرر السنية وإسلام ويب</option>
                                        <option value="official_only">🏛️ المواقع الرسمية للشيوخ فقط (ابن باز، ابن عثيمين، الفوزان، الألباني)</option>
                                        <option value="shamela">📚 المكتبة الشاملة الحديثة (shamela.ws)</option>
                                        <option value="binbaz">📜 موقع الشيخ ابن باز الرسمي (binbaz.org.sa)</option>
                                        <option value="uthaymeen">💡 موقع الشيخ ابن عثيمين الرسمي (ibnothaimeen.net)</option>
                                        <option value="fawzan">⚖️ موقع الشيخ صالح الفوزان الرسمي (alfawzan.af.org.sa)</option>
                                        <option value="albany">📚 موقع الشيخ الألباني الرسمي (alalbany.org)</option>
                                        <option value="dorar">💎 موسوعة الدرر السنية (dorar.net)</option>
                                        <option value="islamweb">📖 شبكة إسلام ويب (islamweb.net)</option>
                                        <option value="alukah">📄 شبكة الألوكة الفقهية (alukah.net)</option>
                                    </select>
                                </div>

                            </div>

                            <!-- Main Search Execution Button -->
                            <div class="pt-2">
                                <button type="button" onclick="executeGoogleScholarSearch()" class="w-full py-3.5 px-6 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 hover:from-sky-400 hover:via-blue-500 hover:to-indigo-500 text-white font-black text-sm md:text-base rounded-xl shadow-xl flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 border border-sky-300/40">
                                    <i class="fa-brands fa-google text-lg"></i>
                                    <span>افتح نتائج البحث الموثق في محرك قوقل العالمي الآن ↗</span>
                                </button>
                            </div>

                        </div>

                        <!-- 10 Instant Topic Search Chips -->
                        <div class="space-y-2 pt-2">
                            <span class="text-xs font-black text-amber-300 block">
                                ⚡ نماذج أسئلة وفتاوى تجارية جاهزة للبحث الفوري في قوقل (اضغط للبحث مباشرة):
                            </span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم شراء سيارات أو معدات بالمرابحة البنكية للآمر بالشراء')">🚗 المرابحة البنكية للسيارات</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم الشرط الجزائي وغرامات التأخير في عقود المقاولات والتوريد')">🏗️ الشرط الجزائي للمقاولات</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم بيع حديد السكراب والخردة وضوابط وزن البسكول')">🔩 بيع حديد السكراب</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم التورق الفردي وسداد الديون للحصول على السيولة')">💳 التورق للحصول على سيولة</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم خصم الشيكات والكمبيالات الآجلة بنقد حال')">📜 حكم خصم الشيكات</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم شراء الذهب والفضة عبر بطاقات الصراف ومدى ونقاط البيع')">💎 شراء الذهب بالبطاقة</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم الإيجار المنتهي بالتمليك وضوابطه الشرعية الصحيحة')">🏢 الإيجار المنتهي بالتمليك</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم بيع الاسم التجاري والعلامات التجارية والفرنشايز')">🏷️ بيع العلامة التجارية</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم التأمين التعاوني التكافلي للبضائع والشحن البحري')">🚢 التأمين التكافلي للبضائع</button>
                                <button type="button" class="quick-chip" onclick="quickGoogleSearch('حكم الصرف الفوري عبر تطبيقات الموبايل بنك والقبض الحكمي')">📱 الصرف عبر الموبايل بنك</button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SUB-SECTION 2: GRAND CLASSICAL & CONTEMPORARY BOOKS LIBRARY  -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-books" class="space-y-4 hidden">
                    <div class="p-5 bg-amber-500/15 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                    ★ المكتبة الفقهية الشاملة للبيوع والمعاملات
                                </span>
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded-xl border border-emerald-400">
                                    26 كتاباً وموسوعة محققة ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-amber-300 flex items-center gap-2">
                                <i class="fa-solid fa-book-bookmark text-amber-400"></i>
                                مكتبة أمهات كتب ومؤلفات ومجلدات البيوع لكبار أئمة وعلماء السنة (12 إماماً)
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                الفهرس والمحتوى العلمي الدقيق لأمهات كتب ومؤلفات: <span class="text-amber-300 font-black">أحمد بن حنبل</span> · <span class="text-amber-300 font-black">ابن تيمية</span> · <span class="text-amber-300 font-black">ابن القيم</span> · <span class="text-amber-300 font-black">النووي</span> · <span class="text-amber-300 font-black">الذهبي</span> · <span class="text-amber-300 font-black">محمد بن عبد الوهاب</span> · <span class="text-amber-300 font-black">السعدي</span> · <span class="text-amber-300 font-black">الشنقيطي</span> · <span class="text-amber-300 font-black">الألباني</span> · <span class="text-amber-300 font-black">ابن باز</span> · <span class="text-amber-300 font-black">ابن عثيمين</span> · <span class="text-amber-300 font-black">الفوزان</span>.
                            </p>
                        </div>

                        <!-- Instant Books Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في أسماء ومجلدات الكتب:</label>
                            <input type="text" id="book-search-input" oninput="filterBooksLibrary()" placeholder="اكتب للبحث: مجموع الفتاوى، الممتع، إعلام الموقعين..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Filter Books by Scholar Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-amber-500/30 flex flex-wrap items-center gap-1.5">
                        <span class="text-xs font-black text-amber-400 block w-full mb-1">
                            👤 تصفية الكتب حسب الإمام والمؤلف:
                        </span>
                        <button type="button" class="book-filter-btn fiqh-filter-btn active" data-bscholar="all" onclick="setBookScholar('all', this)">🌟 جميع الكتب (26)</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="imam_ahmad" onclick="setBookScholar('imam_ahmad', this)">👑 أحمد بن حنبل</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_taymiyyah" onclick="setBookScholar('ibn_taymiyyah', this)">📖 ابن تيمية</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_al_qayyim" onclick="setBookScholar('ibn_al_qayyim', this)">✨ ابن القيم</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_nawawi" onclick="setBookScholar('al_nawawi', this)">📜 النووي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_dhahabi" onclick="setBookScholar('al_dhahabi', this)">⚖️ الذهبي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="muhammad_bin_abdulwahhab" onclick="setBookScholar('muhammad_bin_abdulwahhab', this)">📚 محمد بن عبد الوهاب</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_saadi" onclick="setBookScholar('al_saadi', this)">💡 السعدي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_shinqiti" onclick="setBookScholar('al_shinqiti', this)">📖 الشنقيطي</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_albani" onclick="setBookScholar('al_albani', this)">📚 الألباني</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_baz" onclick="setBookScholar('ibn_baz', this)">📜 ابن باز</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="ibn_uthaymeen" onclick="setBookScholar('ibn_uthaymeen', this)">💡 ابن عثيمين</button>
                        <button type="button" class="book-filter-btn fiqh-filter-btn" data-bscholar="al_fawzan" onclick="setBookScholar('al_fawzan', this)">⚖️ الفوزان</button>
                    </div>

                    <!-- Books Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="books-grid-container">

        <!-- Book Card 1 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="imam_ahmad" data-book-search="مسائل الإمام أحمد في البيوع والتجارة (رواية عبد الله والكوسج والمغني) الإمام المبجل أحمد بن حنبل (ت 241 هـ) خيار المجلس · خيار الشرط · السَّلَم · الرهن · القرض وتأجيل الديون · الشفعة الأصل العظيم في تأسيس مذهب أهل السنة بأن الأصل في العقود والشروط الإباحة والصحة، وثبوت خيار المجلس والشرط للتراضي التام، وجواز تأجيل سداد القرض بالتراضي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #1
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلدات وروايات متعددة
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8505/1800" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مسائل الإمام أحمد في البيوع والتجارة (رواية عبد الله والكوسج والمغني) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام المبجل أحمد بن حنبل (ت 241 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">خيار المجلس · خيار الشرط · السَّلَم · الرهن · القرض وتأجيل الديون · الشفعة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    الأصل العظيم في تأسيس مذهب أهل السنة بأن الأصل في العقود والشروط الإباحة والصحة، وثبوت خيار المجلس والشرط للتراضي التام، وجواز تأجيل سداد القرض بالتراضي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفقهي الأول للمذهب الحنبلي وأهل الحديث في المعاملات المالية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8505/1800" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (المغني وشرح مسائل الإمام أحمد)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 2 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="imam_ahmad" data-book-search="كتاب الورع للإمام أحمد (أبواب الورع في المكاسب والتجارة والأسواق) الإمام المبجل أحمد بن حنبل (ت 241 هـ) الحلال والحرام · اجتناب الشبهات · الورع في المماكسة · النزاهة المالية بيان دقيق لضوابط الورع في التجارة، وكيفية تطهير المكاسب من الشبهات، والتحذير من أكل أموال الناس بالباطل والمماطلة في الديون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #2
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد واحد مفرد
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7454" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 كتاب الورع للإمام أحمد (أبواب الورع في المكاسب والتجارة والأسواق) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام المبجل أحمد بن حنبل (ت 241 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الحلال والحرام · اجتناب الشبهات · الورع في المماكسة · النزاهة المالية</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    بيان دقيق لضوابط الورع في التجارة، وكيفية تطهير المكاسب من الشبهات، والتحذير من أكل أموال الناس بالباطل والمماطلة في الديون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">الدستور الأخلاقي والسلوكي للتاجر والمحاسب المسلم.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7454" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (كتاب الورع للإمام أحمد)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 3 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="imam_ahmad" data-book-search="الجامع لعلوم الإمام أحمد (فقه المعاملات المالية المروية) الإمام المبجل أحمد بن حنبل (ت 241 هـ) القواعد الفقهية · تصحيح شروط التجار · التيسير في المعاملات جمع شامل لكافة نصوص وفتاوى الإمام أحمد في البيوع والشركات والمضاربة والإجارات والرهونات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #3
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 موسوعة جامعة (قسم البيوع)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/97356" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الجامع لعلوم الإمام أحمد (فقه المعاملات المالية المروية) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام المبجل أحمد بن حنبل (ت 241 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">القواعد الفقهية · تصحيح شروط التجار · التيسير في المعاملات</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    جمع شامل لكافة نصوص وفتاوى الإمام أحمد في البيوع والشركات والمضاربة والإجارات والرهونات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">مرجع للمقارنة بين الروايات الفقهية واختيارات كبار الأئمة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/97356" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الجامع لعلوم الإمام أحمد)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 4 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_taymiyyah" data-book-search="مجموع الفتاوى (المجلدان 29 و 30 - كتاب البيوع، العقود، الشروط، والربا) شيخ الإسلام ابن تيمية (ت 728 هـ) الأصل في العقود الإباحة · إبطال حيل الربا · بيع المعدوم · تصحيح شروط التجار · المقاصة أعظم وأشمل تحرير فقهي لقواعد المعاملات والعقود والشروط في التاريخ الإسلامي. قرر فيه حرية العقود ما لم تخالف نصاً، وأبطل الحيل الربوية وبيع العينة، وأصّل لقواعد المقاصة وحوالة الديون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #4
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 29 و 30 (أكثر من 1000 صفحة)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7289/13768" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مجموع الفتاوى (المجلدان 29 و 30 - كتاب البيوع، العقود، الشروط، والربا) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: شيخ الإسلام ابن تيمية (ت 728 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الأصل في العقود الإباحة · إبطال حيل الربا · بيع المعدوم · تصحيح شروط التجار · المقاصة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    أعظم وأشمل تحرير فقهي لقواعد المعاملات والعقود والشروط في التاريخ الإسلامي. قرر فيه حرية العقود ما لم تخالف نصاً، وأبطل الحيل الربوية وبيع العينة، وأصّل لقواعد المقاصة وحوالة الديون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">العمدة الأولى والأساس الذي انطلقت منه الفتاوى الاقتصادية المعاصرة وهيئات الرقابة الشرعية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7289/13768" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (مجموع الفتاوى - المجلد 29)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 5 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_taymiyyah" data-book-search="الحسبة في الإسلام (وظيفة ولاية السوق ومراقبة المكاييل ومنع الاحتكار) شيخ الإسلام ابن تيمية (ت 728 هـ) مراقبة الأسواق · منع الغش والتدليس · التسعير العادل الجبري · تحريم التواطؤ الاحتكاري تنظيم السوق التجاري، واجبات ولي الأمر في مراقبة الموازين والمكاييل ومنع الغش، وبيان ضوابط التسعير العادل عند احتكار السلع والأقوات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #5
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 كتاب مفرد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21703" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الحسبة في الإسلام (وظيفة ولاية السوق ومراقبة المكاييل ومنع الاحتكار) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: شيخ الإسلام ابن تيمية (ت 728 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">مراقبة الأسواق · منع الغش والتدليس · التسعير العادل الجبري · تحريم التواطؤ الاحتكاري</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تنظيم السوق التجاري، واجبات ولي الأمر في مراقبة الموازين والمكاييل ومنع الغش، وبيان ضوابط التسعير العادل عند احتكار السلع والأقوات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الكلاسيكي الأول في الاقتصاد الكلي وتنظيم الأسواق الحرة في الإسلام.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21703" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الحسبة في الإسلام)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 6 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_taymiyyah" data-book-search="القواعد النورانية الفقهية (كتاب المعاملات المالية والعقود) شيخ الإسلام ابن تيمية (ت 728 هـ) القواعد الكلية · استحقاق الربح بالضمان والعمل · نفي الغرر · بيع العربون تأصيل القواعد الكلية الجامعة للمعاملات المالية، وتفصيل قاعدة (الغنم بالغرم والخراج بالضمان)، وصحة بيع العربون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #6
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7288" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 القواعد النورانية الفقهية (كتاب المعاملات المالية والعقود) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: شيخ الإسلام ابن تيمية (ت 728 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">القواعد الكلية · استحقاق الربح بالضمان والعمل · نفي الغرر · بيع العربون</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تأصيل القواعد الكلية الجامعة للمعاملات المالية، وتفصيل قاعدة (الغنم بالغرم والخراج بالضمان)، وصحة بيع العربون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">تأصيل نظريات العقد والضمان في الفقه الإسلامي المقارن.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7288" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (القواعد النورانية الفقهية)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 7 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_al_qayyim" data-book-search="إعلام الموقعين عن رب العالمين (أبواب الربا، الحيل، وعقود البيوع) الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ) المقاصد والمعاني · إبطال حيل الربا · عقد الاستصناع والمقاولات · التورق المنضبط تأسيس قاعدة أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني، وكشف وإبطال حيل الربا كبيع العينة، وإثبات صحة عقد الاستصناع والصناعة بالطلب.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #7
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات 3 و 4
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21609" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 إعلام الموقعين عن رب العالمين (أبواب الربا، الحيل، وعقود البيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">المقاصد والمعاني · إبطال حيل الربا · عقد الاستصناع والمقاولات · التورق المنضبط</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تأسيس قاعدة أن العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني، وكشف وإبطال حيل الربا كبيع العينة، وإثبات صحة عقد الاستصناع والصناعة بالطلب.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفقهي الأهم في فقه المقاصد المالية ومكافحة التحايل المالي.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21609" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (إعلام الموقعين)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 8 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_al_qayyim" data-book-search="زاد المعاد في هدي خير العباد (المجلد الخامس - فصول هَديه ﷺ في البيوع) الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ) المعاملات النبوية · وضع الجوائح والتلف · السمسرة · خيار العيب · تضمين الصناع بيان شامل للتطبيقات النبوية في البيوع، ووجوب وضع الجوائح وإسقاط الثمن عن المشتري عند تلف البضاعة قبل التسليم، وضوابط أجور السمسرة.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #8
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الخامس
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/7440/1715" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 زاد المعاد في هدي خير العباد (المجلد الخامس - فصول هَديه ﷺ في البيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">المعاملات النبوية · وضع الجوائح والتلف · السمسرة · خيار العيب · تضمين الصناع</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    بيان شامل للتطبيقات النبوية في البيوع، ووجوب وضع الجوائح وإسقاط الثمن عن المشتري عند تلف البضاعة قبل التسليم، وضوابط أجور السمسرة.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">التطبيق العملي للسنة النبوية الشريفة في إدارة التجارة والأعمال.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/7440/1715" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (زاد المعاد - المجلد الخامس)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 9 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_al_qayyim" data-book-search="إغاثة اللهفان من مصائد الشيطان (أبواب الحيل في المعاملات) الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ) مفسدات العقود · التحذير من التلاعب بالألفاظ · حماية المعاملات من الفساد تفصيل دقيق لكيفية حماية العقود والمعاملات التجارية من الشبهات والربا الخفي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #9
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الثاني
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21604" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 إغاثة اللهفان من مصائد الشيطان (أبواب الحيل في المعاملات) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام شمس الدين ابن قيم الجوزية (ت 751 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">مفسدات العقود · التحذير من التلاعب بالألفاظ · حماية المعاملات من الفساد</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تفصيل دقيق لكيفية حماية العقود والمعاملات التجارية من الشبهات والربا الخفي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">حماية النزاهة المالية للمؤسسات والشركات.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21604" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (إغاثة اللهفان)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 10 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_nawawi" data-book-search="المجموع شرح المهذب (المجلدات 9 و 10 و 11 - كتاب البيوع والربا والسلم) الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ) أركان البيع · نفي الغرر والجهالة · البيع بالمعاطاة · السلم · الإجارة · الشركات الموسوعة الفقهية المقارنة الكبرى. تفصيل دقيق لشروط العوضين، نفي الغرر، صحة البيع بالمعاطاة والبيع الآلي ونقاط البيع السريعة، وأحكام الإجارة والجعالة والشركات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #10
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات 9 و 10 و 11 (أكثر من 1500 صفحة)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/2186/3890" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 المجموع شرح المهذب (المجلدات 9 و 10 و 11 - كتاب البيوع والربا والسلم) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أركان البيع · نفي الغرر والجهالة · البيع بالمعاطاة · السلم · الإجارة · الشركات</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    الموسوعة الفقهية المقارنة الكبرى. تفصيل دقيق لشروط العوضين، نفي الغرر، صحة البيع بالمعاطاة والبيع الآلي ونقاط البيع السريعة، وأحكام الإجارة والجعالة والشركات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">أوسع مرجع فقهي مقارن بين المذاهب الأربعة في تحرير شروط البيوع والربا.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/2186/3890" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (المجموع للنووي - المجلد التاسع)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 11 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_nawawi" data-book-search="منهاج الطالبين وعمدة المفتين (قسم المعاملات المالية والبيوع) الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ) خيارات البيع · الرهن · الضمان · الحوالة · الشفعة · القراض المتن الفقهي الأكثر ضبطاً واعتماداً في تحرير مسائل البيوع، وضبط خيار الرؤية وخيار العيب، وتوثيق الديون بالرهن والضمان.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #11
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 متن فقهي عمدة
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/11818" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 منهاج الطالبين وعمدة المفتين (قسم المعاملات المالية والبيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">خيارات البيع · الرهن · الضمان · الحوالة · الشفعة · القراض</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    المتن الفقهي الأكثر ضبطاً واعتماداً في تحرير مسائل البيوع، وضبط خيار الرؤية وخيار العيب، وتوثيق الديون بالرهن والضمان.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المتن المعياري المعتمد لدى المجامع الفقهية في الصياغة القانونية للمعاملات.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/11818" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (منهاج الطالبين)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 12 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_nawawi" data-book-search="شرح صحيح مسلم (كتاب البيوع، المساقاة، والربا) الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ) أحاديث البيوع · تحريم بيع الحصاة · الربويات الستة · الصرف والتقابض شرح وتأصيل الأحاديث النبوية الجامعة في تحريم الغرر والربا والنجش والاحتكار، واشتراط التقابض في صرف العملات والذهب.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #12
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 10 و 11
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/1711/4060" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 شرح صحيح مسلم (كتاب البيوع، المساقاة، والربا) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محيي الدين يحيى بن شرف النووي (ت 676 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أحاديث البيوع · تحريم بيع الحصاة · الربويات الستة · الصرف والتقابض</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    شرح وتأصيل الأحاديث النبوية الجامعة في تحريم الغرر والربا والنجش والاحتكار، واشتراط التقابض في صرف العملات والذهب.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">تأصيل فقه الحديث في المعاملات المالية المعاصرة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/1711/4060" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (شرح صحيح مسلم للنووي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 13 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_dhahabi" data-book-search="كتاب الكبائر (كبائر المعاملات، الربا، الغش، والتطفيف) الإمام الحافظ شمس الدين الذهبي (ت 748 هـ) أكل الربا · التطفيف في الميزان · اليمين الغموس لترويج السلع · الغش وتزوير المنشأ بيان الكبائر والموبقات المالية: التطفيف في الميزان والكيل، الغش في بلد المنشأ والمواصفات، واليمين الكاذبة، والرشوة وأكل أموال الناس بالباطل.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #13
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 كتاب مفرد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/10543" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 كتاب الكبائر (كبائر المعاملات، الربا، الغش، والتطفيف) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام الحافظ شمس الدين الذهبي (ت 748 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أكل الربا · التطفيف في الميزان · اليمين الغموس لترويج السلع · الغش وتزوير المنشأ</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    بيان الكبائر والموبقات المالية: التطفيف في الميزان والكيل، الغش في بلد المنشأ والمواصفات، واليمين الكاذبة، والرشوة وأكل أموال الناس بالباطل.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">التحذير والضبط الرادع للجرائم المالية والغش التجاري.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/10543" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (كتاب الكبائر للذهبي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 14 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="muhammad_bin_abdulwahhab" data-book-search="مجموع مؤلفات الشيخ (القسم الثاني: الفقه - كتاب البيوع والديون) الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ) شروط البيع · تحريم الربا · توثيق الديون · الشفعة · منع الاحتكار تحرير أحكام البيوع والربا والديون بالسند والدليل من الكتاب والسنة، والتأكيد على وجوب كتابة الديون والصدق في الأسعار.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #14
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 القسم الثاني (الفقه)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8381" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مجموع مؤلفات الشيخ (القسم الثاني: الفقه - كتاب البيوع والديون) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">شروط البيع · تحريم الربا · توثيق الديون · الشفعة · منع الاحتكار</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تحرير أحكام البيوع والربا والديون بالسند والدليل من الكتاب والسنة، والتأكيد على وجوب كتابة الديون والصدق في الأسعار.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المنهج الفقهي الأصيل المجرد بالدليل الشرعي المباشر.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8381" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (مؤلفات الشيخ محمد بن عبد الوهاب)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 15 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="muhammad_bin_abdulwahhab" data-book-search="مختصر الإنصاف والشرح الكبير في الفقه (قسم المعاملات) الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ) تهذيب الأحكام · فقه المعاملات الحنبلية المحررة تهذيب وتلخيص أدلة الراجح في الخلاف الفقهي في البيوع والإجارات والشركات والرهن.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #15
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8382" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مختصر الإنصاف والشرح الكبير في الفقه (قسم المعاملات) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام الشيخ محمد بن عبد الوهاب (ت 1206 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تهذيب الأحكام · فقه المعاملات الحنبلية المحررة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تهذيب وتلخيص أدلة الراجح في الخلاف الفقهي في البيوع والإجارات والشركات والرهن.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">خلاصة فقه المعاملات المالية المعتمدة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8382" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (مختصر الإنصاف والشرح الكبير)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 16 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_saadi" data-book-search="القواعد والأصول الجامعة والفروق والتقاسيم البديعة النافعة في المعاملات العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ) الأصل في المعاملات الإباحة · العرف مطرد كالمشروط · الخراج بالضمان · التعاقد الإلكتروني تأصيل بديع لقواعد التيسير ورفع الحرج في المعاملات المالية، واعتبار العرف التجاري، وصحة التعاقد بالمراسلات والوسائل الحديثة.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #16
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 كتاب مفرد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8122" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 القواعد والأصول الجامعة والفروق والتقاسيم البديعة النافعة في المعاملات ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الأصل في المعاملات الإباحة · العرف مطرد كالمشروط · الخراج بالضمان · التعاقد الإلكتروني</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تأصيل بديع لقواعد التيسير ورفع الحرج في المعاملات المالية، واعتبار العرف التجاري، وصحة التعاقد بالمراسلات والوسائل الحديثة.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">أعظم كتاب معاصر في تبسيط وتطبيق القواعد الفقهية على التجارة الحديثة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8122" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (القواعد والأصول الجامعة للسعدي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 17 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_saadi" data-book-search="الفتاوى السعدية في المعاملات المالية المستجدة والشركات العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ) الأسهم · التأمين التعاوني · الوكالات الحصرية · المضاربة والنسب الشائعة · المعاملات البنكية فتاوى عصرية رائدة في قسمة أرباح الشركات بالنسب المئوية، جواز الوكالات التجارية، وضوابط المعاملات البنكية الخالية من الربا.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #17
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد الفتاوى (ص 240 - 320)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8120" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الفتاوى السعدية في المعاملات المالية المستجدة والشركات ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">الأسهم · التأمين التعاوني · الوكالات الحصرية · المضاربة والنسب الشائعة · المعاملات البنكية</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى عصرية رائدة في قسمة أرباح الشركات بالنسب المئوية، جواز الوكالات التجارية، وضوابط المعاملات البنكية الخالية من الربا.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">الريادة في الإفتاء المالي المعاصر والتيسير المنضبط.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8120" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الفتاوى السعدية)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 18 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_saadi" data-book-search="بهجة قلوب الأبرار (شرح جوامع الأخبار في البيوع والتجارة) العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ) شرح أحاديث التجارة · بركة الصدق · نفي الغرر · بيع ما لا يملك شرح أحاديث البيع المبرور، خيار المجلس، النهي عن الغش، وفضل إنظار المعسرين.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #18
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/8119" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 بهجة قلوب الأبرار (شرح جوامع الأخبار في البيوع والتجارة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ عبد الرحمن بن ناصر السعدي (ت 1376 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">شرح أحاديث التجارة · بركة الصدق · نفي الغرر · بيع ما لا يملك</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    شرح أحاديث البيع المبرور، خيار المجلس، النهي عن الغش، وفضل إنظار المعسرين.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">شرح حديثي فقهي تربوي لكل تاجر ورجل أعمال.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/8119" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (بهجة قلوب الأبرار للسعدي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 19 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_shinqiti" data-book-search="أضواء البيان في إيضاح القرآن بالقرآن (تفسير آيات البيوع والربا والمداينة) العلامة الشيخ محمد الأمين الشنقيطي (ت 1393 هـ) آية المداينة · تحريم الربا والتأمين التجاري · جواز التأمين التعاوني · عقد السَّلَم · المزارعة تحرير فقهي وتفسيري بديع لآيات سورة البقرة في المداينة والربا، وسورة المائدة في العقود، وتفصيل الفارق الجوهري بين التأمين التجاري الربوي والتأمين التعاوني التكافلي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #19
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات 1 و 2 و 3
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/21626" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 أضواء البيان في إيضاح القرآن بالقرآن (تفسير آيات البيوع والربا والمداينة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: العلامة الشيخ محمد الأمين الشنقيطي (ت 1393 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">آية المداينة · تحريم الربا والتأمين التجاري · جواز التأمين التعاوني · عقد السَّلَم · المزارعة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تحرير فقهي وتفسيري بديع لآيات سورة البقرة في المداينة والربا، وسورة المائدة في العقود، وتفصيل الفارق الجوهري بين التأمين التجاري الربوي والتأمين التعاوني التكافلي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع التفسيري والفقهي الأعمق في استنباط أحكام المعاملات المالية من القرآن الكريم.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/21626" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (أضواء البيان للشنقيطي)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 20 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_albani" data-book-search="سلسلة الأحاديث الصحيحة والضعيفة (موسوعة أحاديث التجارة والبيوع) الإمام محمد ناصر الدين الألباني (ت 1420 هـ) تحقيق أحاديث البيوع · تحريم بيع العينة · خيار العيب · بركة الصدق · نفي الغش التحقيق الحديثي الصارم لأحاديث التجارة والبيوع، وتصحيح أحاديث وضع الجوائح والبركة في الصدق، وتضعيف الأحاديث الواهية في المعاملات.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #20
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلدات متعددة
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/9442" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 سلسلة الأحاديث الصحيحة والضعيفة (موسوعة أحاديث التجارة والبيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد ناصر الدين الألباني (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تحقيق أحاديث البيوع · تحريم بيع العينة · خيار العيب · بركة الصدق · نفي الغش</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    التحقيق الحديثي الصارم لأحاديث التجارة والبيوع، وتصحيح أحاديث وضع الجوائح والبركة في الصدق، وتضعيف الأحاديث الواهية في المعاملات.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الحديثي الأول لتمييز الأحاديث الصحيحة من الضعيفة في البيوع.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/9442" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (سلسلة الأحاديث الصحيحة للألباني)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 21 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_albani" data-book-search="إرواء الغليل في تخريج أحاديث منار السبيل (المجلد الخامس - كتاب البيوع) الإمام محمد ناصر الدين الألباني (ت 1420 هـ) تخريج أحاديث العقود · السلم · الرهن · الإجارة · الشركات · الغصب تخريج وتحقيق موسع لكافة أحاديث المعاملات المالية والعقود الفقهية.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #21
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الخامس
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/12063" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 إرواء الغليل في تخريج أحاديث منار السبيل (المجلد الخامس - كتاب البيوع) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد ناصر الدين الألباني (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تخريج أحاديث العقود · السلم · الرهن · الإجارة · الشركات · الغصب</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    تخريج وتحقيق موسع لكافة أحاديث المعاملات المالية والعقود الفقهية.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">الموسوعة التخريجية الكبرى لأحاديث الفقه المالي.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/12063" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (إرواء الغليل - المجلد الخامس)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 22 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_albani" data-book-search="فتاوى المعاملات المالية والاقتصادية للشيخ الألباني الإمام محمد ناصر الدين الألباني (ت 1420 هـ) تحريم التسويق الهرمي والشبكي · أجور الحوالات المصرفية · الصرف والذهب فتاوى معاصرة في إبطال التسويق الشبكي لجمعه بين الربا والغرر، وجواز أخذ أجر مقطوع على خدمات التحويل البنكي.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #22
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 تسجيلات ورسائل محررة
                        </span>
                    </div>
                    <a href="https://alalbany.org" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 فتاوى المعاملات المالية والاقتصادية للشيخ الألباني ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد ناصر الدين الألباني (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">تحريم التسويق الهرمي والشبكي · أجور الحوالات المصرفية · الصرف والذهب</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى معاصرة في إبطال التسويق الشبكي لجمعه بين الربا والغرر، وجواز أخذ أجر مقطوع على خدمات التحويل البنكي.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">فتاوى حديثية معاصرة في النوازل الاقتصادية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://alalbany.org" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي للإمام الألباني (الفتاوى الصوتية والمحررة)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 23 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_baz" data-book-search="مجموع فتاوى ومقالات متنوعة (المجلدان 19 و 20 - البيوع والمعاملات المعاصرة) الإمام عبد العزيز بن باز (ت 1420 هـ) البيع بالتقسيط · خصم الشيكات · التورق · تداول الأسهم النقية · بطاقات الائتمان · الصرافة المرجع الفقهي اليومي الأول لمسائل البيوع المعاصرة. تحريم خصم الشيكات الآجلة (صريح الربا)، تحريم بيع ما لا يملك، وضوابط الاستثمار في الأسهم النقية الخالية من الربا.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #23
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 19 و 20 (أكثر من 900 صفحة)
                        </span>
                    </div>
                    <a href="https://binbaz.org.sa/categories/fiqhi/16" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 مجموع فتاوى ومقالات متنوعة (المجلدان 19 و 20 - البيوع والمعاملات المعاصرة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام عبد العزيز بن باز (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">البيع بالتقسيط · خصم الشيكات · التورق · تداول الأسهم النقية · بطاقات الائتمان · الصرافة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    المرجع الفقهي اليومي الأول لمسائل البيوع المعاصرة. تحريم خصم الشيكات الآجلة (صريح الربا)، تحريم بيع ما لا يملك، وضوابط الاستثمار في الأسهم النقية الخالية من الربا.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفتوى الأكثر انتشاراً وثقة في العالم الإسلامي في المعاملات التجارية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://binbaz.org.sa/categories/fiqhi/16" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لسماحة الشيخ ابن باز (فقه المعاملات المالية)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 24 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_baz" data-book-search="فتاوى نور على الدرب (قسم المعاملات والبيوع والربا والديون) الإمام عبد العزيز بن باز (ت 1420 هـ) أجوبة التجار · قضايا الديون والرهن · إنظار المعسر · الصرف بالموبايل إجابات مباشرة وميسرة لآلاف النوازل والأسئلة التجارية اليومية للتجار والمحاسبين.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #24
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدات الاقتصادية
                        </span>
                    </div>
                    <a href="https://binbaz.org.sa/fatwas/kind/1" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 فتاوى نور على الدرب (قسم المعاملات والبيوع والربا والديون) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام عبد العزيز بن باز (ت 1420 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أجوبة التجار · قضايا الديون والرهن · إنظار المعسر · الصرف بالموبايل</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    إجابات مباشرة وميسرة لآلاف النوازل والأسئلة التجارية اليومية للتجار والمحاسبين.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">سهولة التطبيق وحل الإشكالات المالية السريعة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://binbaz.org.sa/fatwas/kind/1" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لسماحة الشيخ ابن باز (فتاوى نور على الدرب)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 25 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_uthaymeen" data-book-search="الشرح الممتع على زاد المستقنع (المجلدات 8 و 9 - كتاب البيع والربا والسلم والإجارة) الإمام محمد بن صالح العثيمين (ت 1421 هـ) البيع بالتقسيط · الشرط الجزائي · الكوبونات والديسكاونت · الإيجار المنتهي بالتمليك · الضمان المصنعي أوسع وأوضح شرح تطبيقي معاصر لفقه البيوع. ربط القواعد الفقهية بالمعاملات الاقتصادية الحديثة، وإجازة البيع بالتقسيط، وفصل عقد الإيجار المنتهي بالتمليك، والشرط الجزائي في المقاولات والتوريد.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #25
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 8 و 9 (أكثر من 1100 صفحة)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/23746/2800" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الشرح الممتع على زاد المستقنع (المجلدات 8 و 9 - كتاب البيع والربا والسلم والإجارة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد بن صالح العثيمين (ت 1421 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">البيع بالتقسيط · الشرط الجزائي · الكوبونات والديسكاونت · الإيجار المنتهي بالتمليك · الضمان المصنعي</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    أوسع وأوضح شرح تطبيقي معاصر لفقه البيوع. ربط القواعد الفقهية بالمعاملات الاقتصادية الحديثة، وإجازة البيع بالتقسيط، وفصل عقد الإيجار المنتهي بالتمليك، والشرط الجزائي في المقاولات والتوريد.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الأسهل والأشمل للفقهاء والمحاسبين ورجال الأعمال في العصر الحديث.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/23746/2800" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الشرح الممتع للشيخ ابن عثيمين - كتاب البيوع)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 26 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="ibn_uthaymeen" data-book-search="فقه المعاملات المالية المعاصرة ومجموع الفتاوى والرسائل (المجلدان 18 و 19) الإمام محمد بن صالح العثيمين (ت 1421 هـ) بطاقات الائتمان · الصرف الإلكتروني · نقاط الولاء المجانية · المقاصة · حماية المستهلك فتاوى محررة بالأدلة المقارنة في النوازل المالية، وتأصيل حكم بطاقات الخصومات المجانية والكفالة المصنعية وخدمات ما بعد البيع.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #26
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلدان 18 و 19
                        </span>
                    </div>
                    <a href="https://ibnothaimeen.net/fatawa" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 فقه المعاملات المالية المعاصرة ومجموع الفتاوى والرسائل (المجلدان 18 و 19) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: الإمام محمد بن صالح العثيمين (ت 1421 هـ)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">بطاقات الائتمان · الصرف الإلكتروني · نقاط الولاء المجانية · المقاصة · حماية المستهلك</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى محررة بالأدلة المقارنة في النوازل المالية، وتأصيل حكم بطاقات الخصومات المجانية والكفالة المصنعية وخدمات ما بعد البيع.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الأساسي في فقه النوازل التجارية والمصرفية المعاصرة.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://ibnothaimeen.net/fatawa" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لفضيلة الشيخ ابن عثيمين (فتاوى المعاملات)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 27 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_fawzan" data-book-search="الملخص الفقهي (المجلد الثاني - كتاب المعاملات والبيوع والشركات والديون) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) شروط البيع · الربا · السلم · القرض والرهن · الإجارة · الوكالة · الشركات الدليل الفقهي الميسر والمحرر لكل مسلم وتاجر ومحاسب في ضبط كافة بنود العقود المالية والديون.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #27
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 المجلد الثاني (قسم المعاملات)
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/11440" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 الملخص الفقهي (المجلد الثاني - كتاب المعاملات والبيوع والشركات والديون) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">شروط البيع · الربا · السلم · القرض والرهن · الإجارة · الوكالة · الشركات</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    الدليل الفقهي الميسر والمحرر لكل مسلم وتاجر ومحاسب في ضبط كافة بنود العقود المالية والديون.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المنهاج المعتمد لتعليم فقه المعاملات في الجامعات والمؤسسات المالية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/11440" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (الملخص الفقهي للشيخ الفوزان)</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 28 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_fawzan" data-book-search="المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية والبيوع المعاصرة) معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) التحويلات عبر الموبايل بنك · نقاط البيع pos · المرابحة البنكية · تحريم البيتكوين · الصناديق العقارية فتاوى مصرفية عصرية في اعتبار التحويل الإلكتروني قبضاً حكمياً صحيحاً، ضوابط المرابحة للآمر بالشراء، والتحذير من العملات الرقمية المشفرة والمخاطرة والغرر.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #28
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلدات متعددة
                        </span>
                    </div>
                    <a href="https://alfawzan.af.org.sa/ar/node/2065" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 المنتقى من فتاوى الفوزان (فتاوى المعاملات المصرفية والبيوع المعاصرة) ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">التحويلات عبر الموبايل بنك · نقاط البيع POS · المرابحة البنكية · تحريم البيتكوين · الصناديق العقارية</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    فتاوى مصرفية عصرية في اعتبار التحويل الإلكتروني قبضاً حكمياً صحيحاً، ضوابط المرابحة للآمر بالشراء، والتحذير من العملات الرقمية المشفرة والمخاطرة والغرر.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">المرجع الفتوى الأحدث لكبار العلماء المعاصرين في المعاملات الرقمية والبنكية.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://alfawzan.af.org.sa/ar/node/2065" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: الموقع الرسمي لمعالي الشيخ صالح الفوزان</span>
                </a>
            </div>

        </div>
    
        <!-- Book Card 29 -->
        <div class="fiqh-book-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3.5 shadow-xl hover:border-amber-400 transition flex flex-col justify-between" data-book-scholar="al_fawzan" data-book-search="شرح كتاب البيوع من بلوغ المرام من أدلة الأحكام معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله) أحاديث الأحكام المالية · الخيارات · الربا والصرف · السلم · الإجارة شرح حديثي فقهي رصين لأحاديث البيوع وضوابط الحلال والحرام في المكاسب.">
            
            <div class="space-y-3">
                <!-- Header: Title & Scholar -->
                <div class="border-b border-slate-800 pb-3 space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400/50">
                            كتاب #29
                        </span>
                        <span class="text-xs text-sky-400 font-black px-2.5 py-1 rounded-xl bg-sky-950/80 border border-sky-500/40 font-mono">
                            📖 مجلد محقق
                        </span>
                    </div>
                    <a href="https://shamela.ws/book/37575" target="_blank" rel="noopener noreferrer" class="text-base md:text-lg font-black text-amber-300 hover:text-amber-200 leading-tight block hover:underline transition">
                        📚 شرح كتاب البيوع من بلوغ المرام من أدلة الأحكام ↗
                    </a>
                    <div class="flex items-center gap-1.5 text-xs text-slate-300 font-bold">
                        <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                        <span>المؤلف: معالي الشيخ د. صالح بن فوزان الفوزان (حفظه الله)</span>
                    </div>
                </div>

                <!-- Topics Chips -->
                <div class="p-2.5 bg-slate-950/80 rounded-xl border border-slate-800">
                    <span class="text-[11px] text-slate-400 font-bold block mb-1">🏷️ أهم الأبواب والموضوعات المعالجة:</span>
                    <span class="text-xs text-emerald-300 font-black leading-relaxed block">أحاديث الأحكام المالية · الخيارات · الربا والصرف · السلم · الإجارة</span>
                </div>

                <!-- Description -->
                <p class="text-xs md:text-sm text-slate-200 font-bold leading-relaxed">
                    شرح حديثي فقهي رصين لأحاديث البيوع وضوابط الحلال والحرام في المكاسب.
                </p>

                <!-- Importance Box -->
                <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                    <span class="text-emerald-400 font-black block mb-0.5">🌟 القيمة والأهمية الفقهية:</span>
                    <span class="text-slate-300 font-bold">تأصيل أدلة الأحكام في الفقه المالي المعاصر.</span>
                </div>
            </div>

            <!-- Direct Online Link Button -->
            <div class="pt-2 border-t border-slate-800/80">
                <a href="https://shamela.ws/book/37575" target="_blank" rel="noopener noreferrer" class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500/20 to-amber-600/30 hover:from-amber-500/30 hover:to-amber-600/40 border-2 border-amber-400/60 rounded-xl text-amber-300 hover:text-amber-200 font-black text-xs flex items-center justify-center gap-2 shadow-md transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>تصفح وقراءة الكتاب كاملاً أونلاين عبر: المكتبة الشاملة (شرح بلوغ المرام - كتاب البيوع)</span>
                </a>
            </div>

        </div>
    
                    </div>

                    <div id="no-books-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد كتب مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار إمام آخر لإظهار بقية المؤلفات.</p>
                    </div>
                    
                

                id="fiqh-sub-maxims" class="space-y-4 hidden">
                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl">
                        <h3 class="text-lg font-black text-emerald-400 flex items-center gap-2">
                            <i class="fa-solid fa-scale-balanced"></i>
                            القواعد الفقهية الكبرى الـ 5 الحاكمة للبيوع والتجارة في الإسلام
                        </h3>
                        <p class="text-xs text-slate-300 font-bold mt-1">
                            القواعد الكلية التي تفرعت عنها أحكام التجارة والشركات والمصارف.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-emerald-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-emerald-300">1. «الأصل في العقود والشروط الحل والإباحة»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg">قاعدة عامة</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                كل عقد أو شرط تجاري ابتكره الناس لتحقيق مصالحهم فهو جائز ومباح شرعاً، إلا إذا تضمن ربا، أو غشاً، أو ظلماً، أو ميسراً، أو غرراً فاحشاً.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">المقررون: الإمام أحمد، ابن تيمية، ابن القيم، السعدي، وابن عثيمين.</span>
                        </div>

                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-amber-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-amber-300">2. «الخَرَاجُ بِالضَّمَانِ · والغُنْمُ بِالغُرْمِ»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-amber-500/20 text-amber-300 rounded-lg">حديث نبوي صحيح</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                لا يستحق التاجر أو الشريك الربح إلا إذا كان يتحمل مخاطرة الضمان وتلف البضاعة وخسارتها. أما ضمان رأس المال مع اشتراط فائدة مؤكدة فهو عين الربا المحرم.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">التطبيق: تحريم فوائد الودائع البنكية المضمونة مع ربح ثابت، وصحة المضاربة بالمخاطرة.</span>
                        </div>

                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-sky-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-sky-300">3. «لا ضَرَرَ وَلا ضِرَارَ»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-sky-500/20 text-sky-300 rounded-lg">حديث نبوي صحيح</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                تحريم كل تصرف تجاري يلحق ضرراً بالمتعاملين أو بالأسواق، كاحتكار السلع الأساسية، التلاعب بالأسعار (النجش)، أو بيع السلع المعيبة دون بيان.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">التطبيق: جواز التسعير الجبري عند احتكار أقوات الناس، وثبوت خيار العيب للمشتري.</span>
                        </div>

                        <div class="p-5 bg-slate-900/90 rounded-2xl border-2 border-purple-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <h4 class="text-base font-black text-purple-300">4. «العَادَةُ مُحَكَّمَةٌ · والمَعْرُوفُ عُرْفاً كَالمَشْرُوطِ شَرْطاً»</h4>
                                <span class="text-xs font-black px-2.5 py-1 bg-purple-500/20 text-purple-300 rounded-lg">قاعدة أصولية</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed font-bold">
                                ما تعارف عليه التجار وأهل السوق من مواعيد التسليم، طرق التغليف، مواعيد السداد، والخصومات، يُعد ملزماً كأنه منصوص عليه في صلب العقد.
                            </p>
                            <span class="text-[11px] text-slate-400 block font-bold">التطبيق: تطبيق أعراف التجارة الإلكترونية والشحن والتسليم.</span>
                        </div>

                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                
                <!-- ════════════════════════════════════════════════════════════ -->
                
                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 6: QURANIC VERSES OF DEBTS & TRANSACTIONS        -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-quran" class="space-y-4 hidden">
                    <div class="p-5 bg-emerald-950/40 border-2 border-emerald-500/50 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-black text-xs rounded-xl border border-emerald-400">
                                    ★ الدستور الإلهي الحاكم للمعاملات والديون
                                </span>
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400">
                                    15 آية قرآنية محكمة ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-emerald-300 flex items-center gap-2">
                                <i class="fa-solid fa-book-open-reader text-emerald-400"></i>
                                الآيات القرآنية في الديون، القروض، توثيق العقود، إحلال البيع، وتحريم الربا
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                تشمل: <span class="text-amber-300 font-black">آية المداينة (أطول آية)</span> · <span class="text-sky-300 font-black">آيات إنظار المعسر والرهن</span> · <span class="text-rose-300 font-black">تحريم الربا والرشوة</span> · <span class="text-emerald-300 font-black">ضبط الموازين والمكاييل</span> · <span class="text-purple-300 font-black">التراضي وأداء الأمانات</span>.
                            </p>
                        </div>

                        <!-- Instant Quranic Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في نصوص وتوجيهات الآيات:</label>
                            <input type="text" id="quran-search-input" oninput="filterQuranVerses()" placeholder="اكتب للبحث: دين، رهن، ربا، ميزان، تراضي..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Quranic Topic Filter Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-emerald-500/30 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black text-emerald-400 block">
                            🏷️ تصفية الآيات حسب الموضوع:
                        </span>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn active" data-qcat="all" onclick="setQuranTopic('all', this)">
                            🌟 جميع الآيات القرآنية (15)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="debts" onclick="setQuranTopic('debts', this)">
                            💳 الديون والمداينة والرهن وإنظار المعسر
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="riba" onclick="setQuranTopic('riba', this)">
                            🚫 إحلال البيع وتحريم الربا والرشوة
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="contracts" onclick="setQuranTopic('contracts', this)">
                            🤝 التراضي والوفاء بالعقود والتكافل
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="weights" onclick="setQuranTopic('weights', this)">
                            ⚖️ إيفاء المكيال والميزان ونفي التطفيف
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="trust" onclick="setQuranTopic('trust', this)">
                            👥 أداء الأمانات والكفاءة في العمل
                        </button>
                    </div>

                    <!-- Verses Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="quran-verses-grid-container">

        <!-- Quranic Verse Card 1 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="آية المداينة (أطول آية في القرآن الكريم - دستور التوثيق المالي والديون) ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ ۚ وَلَا يُضَارَّ كَاتِبٌ وَلَا شَهِيدٌ﴾ سورة البقرة: الآية 282 التوجيه المحاسبي والتجاري: وجوب وتأكيد توثيق كافة المعاملات الآجلة والفواتير كتابياً، وتحديد الأجل والمقدار بدقة، والعدالة التامة في القيود المحاسبية لقطع النزاع وحفظ الحقوق.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        آية المداينة (أطول آية في القرآن الكريم - دستور التوثيق المالي والديون)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 الديون وتوثيق العقود
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 282
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ ۚ وَلَا يُضَارَّ كَاتِبٌ وَلَا شَهِيدٌ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: وجوب وتأكيد توثيق كافة المعاملات الآجلة والفواتير كتابياً، وتحديد الأجل والمقدار بدقة، والعدالة التامة في القيود المحاسبية لقطع النزاع وحفظ الحقوق.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 2 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="مشروعية الرهن المقبوض في السفر والديون وأداء الأمانات ﴿وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ﴾ سورة البقرة: الآية 283 التوجيه المحاسبي والتجاري: مشروعية أخذ الرهونات والضمانات العينية لتوثيق الديون عند تعذر التوثيق الكتابي، ووجوب أداء الأمانات والشهادات دون كتمان.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الرهن المقبوض في السفر والديون وأداء الأمانات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 الرهن والضمانات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 283
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: مشروعية أخذ الرهونات والضمانات العينية لتوثيق الديون عند تعذر التوثيق الكتابي، ووجوب أداء الأمانات والشهادات دون كتمان.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 3 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="وجوب إنظار المعسر والتجاوز عنه بالصدقة والإبراء ﴿وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ﴾ سورة البقرة: الآية 280 التوجيه المحاسبي والتجاري: وجوب إمهال المدين المعسر شرعاً دون فرض أي غرامات تأخير أو فوائد، واستحباب التنازل عن الدين أو إسقاط جزء منه كصدقة لوجه الله.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب إنظار المعسر والتجاوز عنه بالصدقة والإبراء
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 إنظار المعسر والتيسير
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 280
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: وجوب إمهال المدين المعسر شرعاً دون فرض أي غرامات تأخير أو فوائد، واستحباب التنازل عن الدين أو إسقاط جزء منه كصدقة لوجه الله.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 4 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="إحلال البيع والتجارة وتحريم الربا القاطع ﴿الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ﴾ سورة البقرة: الآية 275 التوجيه المحاسبي والتجاري: الأصل الشرعي هو إباحة البيع والتجارة وتحقيق الأرباح العادلة، والتحريم القاطع للربا والزيادات المشروطة على الديون والقروض.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إحلال البيع والتجارة وتحريم الربا القاطع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 إحلال البيع وتحريم الربا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 275
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: الأصل الشرعي هو إباحة البيع والتجارة وتحقيق الأرباح العادلة، والتحريم القاطع للربا والزيادات المشروطة على الديون والقروض.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 5 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="محق الربا وبركة الصدقات ونماء المال الحلال ﴿يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ﴾ سورة البقرة: الآية 276 التوجيه المحاسبي والتجاري: زوال البركة والهلاك الحتمي للأموال الربوية وإن كثرت أرقامها، ونماء وبركة التجارة الصادقة المصحوبة بإخراج الزكاة والصدقات.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        محق الربا وبركة الصدقات ونماء المال الحلال
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 محق الربا ونماء الصدقات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 276
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: زوال البركة والهلاك الحتمي للأموال الربوية وإن كثرت أرقامها، ونماء وبركة التجارة الصادقة المصحوبة بإخراج الزكاة والصدقات.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 6 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="إعلان الحرب الإلهية على من لم يذر الربا وحق استرداد رأس المال فقط ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ﴾ سورة البقرة: الآيات 278-279 التوجيه المحاسبي والتجاري: تحريم تقاضي أي زيادة فوق مبلغ الدين الأصلي، وحق الدائن محصور في استرداد رأس ماله الفعلي فقط دون ظلم أو مظلمة.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إعلان الحرب الإلهية على من لم يذر الربا وحق استرداد رأس المال فقط
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 وجوب ترك الربا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآيات 278-279
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم تقاضي أي زيادة فوق مبلغ الدين الأصلي، وحق الدائن محصور في استرداد رأس ماله الفعلي فقط دون ظلم أو مظلمة.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 7 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="ركن التراضي في التجارة وتحريم أكل أموال الناس بالباطل ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا﴾ سورة النساء: الآية 29 التوجيه المحاسبي والتجاري: اشتراط التراضي التام ونفي الإكراه والغرر في عقود البيع، وتحريم كافة صور أكل أموال الناس بالحيل والسرقة والغش والاحتيال.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        ركن التراضي في التجارة وتحريم أكل أموال الناس بالباطل
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🤝 التراضي والعقود
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة النساء: الآية 29
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: اشتراط التراضي التام ونفي الإكراه والغرر في عقود البيع، وتحريم كافة صور أكل أموال الناس بالحيل والسرقة والغش والاحتيال.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 8 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="الأمر الإلهي القاطع بالوفاء بكافة العقود والشروط ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ...﴾ سورة المائدة: الآية 1 التوجيه المحاسبي والتجاري: الإلزام الشرعي التام بالوفاء بكافة بنود العقود المبرمة (عقود التوريد، المقاولات، الشراكات، والتوكيلات التجارية).">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأمر الإلهي القاطع بالوفاء بكافة العقود والشروط
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🤝 الوفاء بالعقود
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة المائدة: الآية 1
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ...﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: الإلزام الشرعي التام بالوفاء بكافة بنود العقود المبرمة (عقود التوريد، المقاولات، الشراكات، والتوكيلات التجارية).
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 9 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="weights" data-ayah-search="إيفاء الكيل والميزان بالقسطاس المستقيم ﴿وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا﴾ سورة الإسراء: الآية 35 التوجيه المحاسبي والتجاري: الأمر الرباني بالمعايرة الدقيقة للموازين الرقمية والمكاييل وتطابق وزن بضائع التسليح والمخازن مع الفواتير الصادرة.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إيفاء الكيل والميزان بالقسطاس المستقيم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        ⚖️ ضبط الموازين والمكاييل
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة الإسراء: الآية 35
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: الأمر الرباني بالمعايرة الدقيقة للموازين الرقمية والمكاييل وتطابق وزن بضائع التسليح والمخازن مع الفواتير الصادرة.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 10 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="weights" data-ayah-search="الوعيد الشديد والهلاك للمطففين في الكيل والوزن ﴿وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ * أَلَا يَظُنُّ أُولَٰئِكَ أَنَّهُم مَّبْعُوثُونَ * لِيَوْمٍ عَظِيمٍ﴾ سورة المطففين: الآيات 1-5 التوجيه المحاسبي والتجاري: تحريم إنقاص أوزان الحديد أو الخرسانة أو أمتار الأقمشة والأراضي عند البيع للزبائن، وبيان أن التطفيف من كبائر الذنوب.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الوعيد الشديد والهلاك للمطففين في الكيل والوزن
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        ⚖️ تحريم التطفيف
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة المطففين: الآيات 1-5
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ * أَلَا يَظُنُّ أُولَٰئِكَ أَنَّهُم مَّبْعُوثُونَ * لِيَوْمٍ عَظِيمٍ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم إنقاص أوزان الحديد أو الخرسانة أو أمتار الأقمشة والأراضي عند البيع للزبائن، وبيان أن التطفيف من كبائر الذنوب.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 11 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trust" data-ayah-search="وجوب أداء الأمانات إلى أهلها والعدل في المعاملات ﴿إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا﴾ سورة النساء: الآية 58 التوجيه المحاسبي والتجاري: وجوب رد الودائع، حفظ أموال الشركاء، أداء مستحقات الموردين والعملاء، وإقامة العدل في الحسابات.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب أداء الأمانات إلى أهلها والعدل في المعاملات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        🛡️ أداء الأمانات والعدل
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة النساء: الآية 58
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: وجوب رد الودائع، حفظ أموال الشركاء، أداء مستحقات الموردين والعملاء، وإقامة العدل في الحسابات.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 12 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="تحريم الرشوة والوساطة غير المشروعة لأكل أموال الناس ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ﴾ سورة البقرة: الآية 188 التوجيه المحاسبي والتجاري: تحريم دفع الرشاوى والعمولات الخفية للمسؤولين أو موظفي المشتريات لكسب المناقصات أو تجاوز اللوائح بالباطل.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الرشوة والوساطة غير المشروعة لأكل أموال الناس
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 تحريم الرشوة والفساد
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة البقرة: الآية 188
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم دفع الرشاوى والعمولات الخفية للمسؤولين أو موظفي المشتريات لكسب المناقصات أو تجاوز اللوائح بالباطل.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 13 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="التعاون على البر والتقوى في الشراكات والتأمين التكافلي ﴿وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ﴾ سورة المائدة: الآية 2 التوجيه المحاسبي والتجاري: مشروعية وتأصيل عقود التأمين التعاوني الإسلامي وصناديق التكافل والشركات الإنتاجية، وتحريم الشراكة في الحرام.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        التعاون على البر والتقوى في الشراكات والتأمين التكافلي
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🤝 التعاون والتكافل التجاري
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة المائدة: الآية 2
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: مشروعية وتأصيل عقود التأمين التعاوني الإسلامي وصناديق التكافل والشركات الإنتاجية، وتحريم الشراكة في الحرام.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 14 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="weights" data-ayah-search="إتمام الكيل والعدل وعدم بخس الناس أشياءهم ﴿وَيَا قَوْمِ أَوْفُوا الْمِكْيَالَ وَالْمِيزَانَ بِالْقِسْطِ ۖ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ * بَقِيَّتُ اللَّهِ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ ۚ وَمَا أَنَا عَلَيْكُم بِحَفِيظٍ﴾ سورة هود: الآيات 85-86 التوجيه المحاسبي والتجاري: تحريم التقليل من قيمة وجودة سلع الموردين أو البائعين ظلماً، والتأكيد على أن الربح الحلال القليل خير وأبقى من الحرام الكثير.">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إتمام الكيل والعدل وعدم بخس الناس أشياءهم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        ⚖️ عدم بخس سلع الناس
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة هود: الآيات 85-86
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿وَيَا قَوْمِ أَوْفُوا الْمِكْيَالَ وَالْمِيزَانَ بِالْقِسْطِ ۖ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ * بَقِيَّتُ اللَّهِ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ ۚ وَمَا أَنَا عَلَيْكُم بِحَفِيظٍ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: تحريم التقليل من قيمة وجودة سلع الموردين أو البائعين ظلماً، والتأكيد على أن الربح الحلال القليل خير وأبقى من الحرام الكثير.
                </span>
            </div>

        </div>
    
        <!-- Quranic Verse Card 15 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trust" data-ayah-search="معيار الكفاءة والأمانة في التوظيف وعقود العمل والمقاولات ﴿قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ﴾ سورة القصص: الآية 26 التوجيه المحاسبي والتجاري: ركنا النجاح والبركة في إدارة الأعمال والمؤسسات: الكفاءة الفنية والمهنية (القوة) والصدق والنزاهة المحاسبية (الأمانة).">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        معيار الكفاءة والأمانة في التوظيف وعقود العمل والمقاولات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        👥 الأمانة والكفاءة في العمل
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        📖 سورة القصص: الآية 26
                    </span>
                </div>
            </div>

            <!-- Ayah Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <p class="text-lg md:text-xl text-emerald-200 font-black leading-loose text-center" style="font-family: 'Amiri', serif; line-height: 2.1;">
                    ﴿قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ﴾
                </p>
            </div>

            <!-- Guidance & Accounting Impact -->
            <div class="p-3 bg-emerald-950/30 rounded-xl border border-emerald-500/30 text-xs">
                <span class="text-emerald-400 font-black block mb-1">
                    💡 التوجيه المحاسبي والتجاري: ركنا النجاح والبركة في إدارة الأعمال والمؤسسات: الكفاءة الفنية والمهنية (القوة) والصدق والنزاهة المحاسبية (الأمانة).
                </span>
            </div>

        </div>
    
                    </div>

                    <div id="no-quran-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد آيات مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية الآيات.</p>
                    </div>
                </div>

                <!-- SUB-SECTION 5: AUTHENTIC HADITHS OF DEBTS & ALL TRANSACTIONS -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-hadiths" class="space-y-4 hidden">
                    <div class="p-5 bg-amber-500/15 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                    ★ الموسوعة النبوية الشاملة للمعاملات المالية
                                </span>
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded-xl border border-emerald-400">
                                    32 حديثاً صحيحاً 100% ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-amber-300 flex items-center gap-2">
                                <i class="fa-solid fa-scroll text-amber-400"></i>
                                أحاديث البيوع، الديون، القروض، الرهن، والأجور والمعاملات المالية (الصحيحة فقط)
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                تشمل: <span class="text-sky-300 font-black">أحكام الديون وسدادها</span> · <span class="text-emerald-300 font-black">إنظار المعسرين والرهن</span> · <span class="text-fuchsia-300 font-black">أجور العمال والشركات</span> · <span class="text-amber-300 font-black">البيوع والصدق والتسعير</span> · <span class="text-rose-300 font-black">تحريم الربا والرشوة والغلول</span>.
                            </p>
                        </div>

                        <!-- Instant Hadith Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في نصوص وتخريج الأحاديث:</label>
                            <input type="text" id="hadith-search-input" oninput="filterHadiths()" placeholder="اكتب للبحث: دين، رهن، أجر، ربا، رشوة..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Hadiths Category Filter Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-amber-500/30 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black text-amber-400 block">
                            🏷️ تصفية الأحاديث حسب الباب المالي:
                        </span>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn active" data-hcat="all" onclick="setHadithCategory('all', this)">
                            🌟 جميع الأحاديث النبوية (32)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="debts" onclick="setHadithCategory('debts', this)">
                            💳 أحاديث الديون والقروض والرهن والإنظار (10)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="sales" onclick="setHadithCategory('sales', this)">
                            🛒 أحاديث البيوع والصدق والتسعير والغش (12)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="labor" onclick="setHadithCategory('labor', this)">
                            👥 أحاديث الأجور والعمال والشركات (4)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="prohibitions" onclick="setHadithCategory('prohibitions', this)">
                            🚫 أحاديث تحريم الربا والصرف والرشوة والغلول (6)
                        </button>
                    </div>

                    <!-- Hadiths Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="hadiths-grid-container">

        <!-- Hadith Card 1 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="نية سداد الدين وعون الله للمدين الصادق وعقوبة المتلف «مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ» صحيح البخاري (2387) النية الصادقة في سداد الديون تستجلب معونة الله وتيسير الرزق، ونية المماطلة وأكل أموال الناس سبب لهلاك المال وصاحبه.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        نية سداد الدين وعون الله للمدين الصادق وعقوبة المتلف
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2387)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">النية الصادقة في سداد الديون تستجلب معونة الله وتيسير الرزق، ونية المماطلة وأكل أموال الناس سبب لهلاك المال وصاحبه.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 2 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="تحريم مَطْل الغني والمماطلة في سداد الديون الحالة «مَطْلُ الْغَنِيِّ ظُلْمٌ، وَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتَّبِعْ» صحيح البخاري (2287) وصحيح مسلم (1564) تأخير سداد الدين مع القدرة المالية ظلم صريح وكبيرة، وجواز الحوالة المالية على الشخص القادر المليء.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم مَطْل الغني والمماطلة في سداد الديون الحالة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَطْلُ الْغَنِيِّ ظُلْمٌ، وَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتَّبِعْ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2287) وصحيح مسلم (1564)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تأخير سداد الدين مع القدرة المالية ظلم صريح وكبيرة، وجواز الحوالة المالية على الشخص القادر المليء.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 3 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="خطورة الدين وتعلق نفس المؤمن به بعد الموت «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ» رواه الترمذي (1078) وابن ماجه وصححه الألباني وجوب المبادرة بقضاء الديون وتوثيقها وكتابة الوصية بها لإبراء ذمة المتوفى قبل تقسيم التركة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        خطورة الدين وتعلق نفس المؤمن به بعد الموت
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1078) وابن ماجه وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وجوب المبادرة بقضاء الديون وتوثيقها وكتابة الوصية بها لإبراء ذمة المتوفى قبل تقسيم التركة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 4 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="الدين لا يسقط حتى بالشهادة في سبيل الله «يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ» صحيح مسلم (1886) حقوق العباد والديون مبنية على المشاحة ولا تسقط إلا بالأداء أو الإبراء من صاحب الحق.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الدين لا يسقط حتى بالشهادة في سبيل الله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو بن العاص رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1886)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حقوق العباد والديون مبنية على المشاحة ولا تسقط إلا بالأداء أو الإبراء من صاحب الحق.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 5 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="فضل الوفاء وحسن القضاء والزيادة غير المشروطة عند السداد «إِنَّ خِيَارَكُمْ أَحْسَنُكُمْ قَضَاءً» صحيح البخاري (2305) وصحيح مسلم (1601) استحباب رد الدين بأحسن منه في الوصف أو زيادة طيبة من غير شرط ولا تواطؤ مسبق إكراماً للمقرض.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل الوفاء وحسن القضاء والزيادة غير المشروطة عند السداد
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ خِيَارَكُمْ أَحْسَنُكُمْ قَضَاءً»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2305) وصحيح مسلم (1601)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب رد الدين بأحسن منه في الوصف أو زيادة طيبة من غير شرط ولا تواطؤ مسبق إكراماً للمقرض.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 6 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="مشروعية الرهن التجاري في الديون لتوثيق الحقوق «تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ» صحيح البخاري (2916) وصحيح مسلم (1603) جواز رهن الأصول والضمانات العينية لتوثيق الديون والمشتريات الآجلة حفظاً للحقوق المالية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الرهن التجاري في الديون لتوثيق الحقوق
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عائشة رضي الله عنها:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2916) وصحيح مسلم (1603)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">جواز رهن الأصول والضمانات العينية لتوثيق الديون والمشتريات الآجلة حفظاً للحقوق المالية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 7 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="فضل إنظار المعسر ومضاعفة أجر الصدقة عن كل يوم تأخير «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، حَتَّى إِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ» رواه أحمد (23046) وابن ماجه وصححه الألباني في السلسلة الصحيحة (860) الأجر المضاعف للتاجر الذي يمهل المدين المعسر عن تاريخ الاستحقاق دون احتساب أي فوائد ربوية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل إنظار المعسر ومضاعفة أجر الصدقة عن كل يوم تأخير
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن بريدة بن الحصيب رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، حَتَّى إِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (23046) وابن ماجه وصححه الألباني في السلسلة الصحيحة (860)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الأجر المضاعف للتاجر الذي يمهل المدين المعسر عن تاريخ الاستحقاق دون احتساب أي فوائد ربوية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 8 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="تجاوز الله ومغفرته لمن كان يتجاوز عن المعسرين في الأسواق «كَانَ رَجُلٌ يُدَايِنُ النَّاسَ، فَكَانَ يَقُولُ لِفَتَاهُ: إِذَا أَتَيْتَ مُعْسِرًا فَتَجَاوَزْ عَنْهُ، لَعَلَّ اللَّهَ أَنْ يَتَجَاوَزَ عَنَّا، فَلَقِيَ اللَّهَ فَتَجَاوَزَ عَنْهُ» صحيح البخاري (2078) وصحيح مسلم (1562) التيسير على المدينين وإسقاط جزء من الدين أو المسامحة فيه سبب مباشر لمغفرة الله يوم القيامة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تجاوز الله ومغفرته لمن كان يتجاوز عن المعسرين في الأسواق
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حذيفة وأبي هريرة رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «كَانَ رَجُلٌ يُدَايِنُ النَّاسَ، فَكَانَ يَقُولُ لِفَتَاهُ: إِذَا أَتَيْتَ مُعْسِرًا فَتَجَاوَزْ عَنْهُ، لَعَلَّ اللَّهَ أَنْ يَتَجَاوَزَ عَنَّا، فَلَقِيَ اللَّهَ فَتَجَاوَزَ عَنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2078) وصحيح مسلم (1562)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التيسير على المدينين وإسقاط جزء من الدين أو المسامحة فيه سبب مباشر لمغفرة الله يوم القيامة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 9 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="مشروعية الكفالة والضمان المالي (الزعيم غارم) «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ» رواه الترمذي (1265) وأبو داود (3565) وصححه الألباني التزام الكفيل والغارم بسداد الدين عند تعذر المدين الأصلي، وثبوت أحكام الكفالة والضمان في المعاملات.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الكفالة والضمان المالي (الزعيم غارم)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي أمامة الباهلي رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1265) وأبو داود (3565) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التزام الكفيل والغارم بسداد الدين عند تعذر المدين الأصلي، وثبوت أحكام الكفالة والضمان في المعاملات.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 10 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="عقوبة المماطل المقتدر وجواز شكايته وحبسه شرعاً «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ» رواه أبو داود (3628) والنسائي وصححه الألباني، وعلقه البخاري بصيغة الجزم المماطلة في السداد مع القدرة المالية تجيز للدائن رفع أمره للقضاء وللحاكم تعزيره وإلزامه بالتنفيذ الجبري.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة المماطل المقتدر وجواز شكايته وحبسه شرعاً
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن الشريد بن سويد رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3628) والنسائي وصححه الألباني، وعلقه البخاري بصيغة الجزم</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المماطلة في السداد مع القدرة المالية تجيز للدائن رفع أمره للقضاء وللحاكم تعزيره وإلزامه بالتنفيذ الجبري.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 11 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="وجوب دفع أجر العامل والأجير فور الفراغ من عمله «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ» رواه ابن ماجه (2443) وصححه الألباني في صحيح الجامع (1055) تحريم تأخير صرف رواتب ومستحقات العمال والموظفين والمقاولين بعد إتمام المهام المطلوبة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب دفع أجر العامل والأجير فور الفراغ من عمله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه ابن ماجه (2443) وصححه الألباني في صحيح الجامع (1055)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم تأخير صرف رواتب ومستحقات العمال والموظفين والمقاولين بعد إتمام المهام المطلوبة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 12 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="وعيد الله الشديد لمن استأجر أجيراً ولم يوفه أجره كاملاً «قَالَ اللَّهُ تَعَالَى: ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ... وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ» صحيح البخاري (2227) إنقاص مستحقات العامل أو بخسه حقه بعد أداء عمله من أعظم الظلم الذي يجعل الله خصماً لصاحب العمل.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وعيد الله الشديد لمن استأجر أجيراً ولم يوفه أجره كاملاً
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «قَالَ اللَّهُ تَعَالَى: ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ... وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2227)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إنقاص مستحقات العامل أو بخسه حقه بعد أداء عمله من أعظم الظلم الذي يجعل الله خصماً لصاحب العمل.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 13 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="بركة الشراكة والأمانة بين الشركاء وخروج البركة بالخيانة «إِنَّ اللَّهَ يَقُولُ: أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا» رواه أبو داود (3383) وصححه الحاكم ووافقه الذهبي الأمانة والشفافية المحاسبية التامة بين الشركاء سبب توفيق واستثمار ونماء الشركة، والخيانة تمحق المال.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        بركة الشراكة والأمانة بين الشركاء وخروج البركة بالخيانة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ يَقُولُ: أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3383) وصححه الحاكم ووافقه الذهبي</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الأمانة والشفافية المحاسبية التامة بين الشركاء سبب توفيق واستثمار ونماء الشركة، والخيانة تمحق المال.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 14 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="وجوب الإتقان والجودة في الأعمال والتصنيع والخدمات «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ» رواه البيهقي في شعب الإيمان وصححه الألباني في السلسلة الصحيحة (1113) معيار الجودة والإتقان في الإنتاج الصناعي والمقاولات والخدمات واجب شرعي وأخلاقي في الإسلام.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب الإتقان والجودة في الأعمال والتصنيع والخدمات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشراكة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عائشة رضي الله عنها:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه البيهقي في شعب الإيمان وصححه الألباني في السلسلة الصحيحة (1113)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">معيار الجودة والإتقان في الإنتاج الصناعي والمقاولات والخدمات واجب شرعي وأخلاقي في الإسلام.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 15 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="لعن آكل الربا وموكله وكاتبه وشاهديه ومساواتهم في الإثم «لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ» صحيح مسلم (1598) تحريم التعامل بالربا أو توثيقه أو برمجته أو قيده في السجلات المحاسبية والأنظمة المالية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        لعن آكل الربا وموكله وكاتبه وشاهديه ومساواتهم في الإثم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1598)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم التعامل بالربا أو توثيقه أو برمجته أو قيده في السجلات المحاسبية والأنظمة المالية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 16 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="الأصناف الربوية وشرط التقابض الفوري في صرف العملات «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ» صحيح مسلم (1587) اشتراط التقابض الفوري الحقيقي أو الحكمي (التحويل البنكي الفوري) عند مبادلة العملات المختلفة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        16
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأصناف الربوية وشرط التقابض الفوري في صرف العملات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبادة بن الصامت رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1587)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">اشتراط التقابض الفوري الحقيقي أو الحكمي (التحويل البنكي الفوري) عند مبادلة العملات المختلفة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 17 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="لعن الراشي والمرتشي في المعاملات والوظائف والمناقصات «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِيَ وَالْمُرْتَشِيَ (وفي رواية: وَالرَّائِشَ)» رواه الترمذي (1337) وأبو داود (3580) وصححه الألباني تحريم دفع أو قبول أي مبالغ أو عمولات خفية للتأثير على المناقصات أو العقود أو الإجراءات الرسمية.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        17
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        لعن الراشي والمرتشي في المعاملات والوظائف والمناقصات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو وأبي هريرة رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِيَ وَالْمُرْتَشِيَ (وفي رواية: وَالرَّائِشَ)»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1337) وأبو داود (3580) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم دفع أو قبول أي مبالغ أو عمولات خفية للتأثير على المناقصات أو العقود أو الإجراءات الرسمية.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 18 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="تحريم هدايا وعمولات الموظفين والمسؤولين والمشترين (الغلول) «هَدَايَا الْعُمَّالِ غُلُولٌ» رواه أحمد (23604) والبيهقي وصححه الألباني في صحيح الجامع (7021) تحريم الهدايا والعمولات الخاصة التي يأخذها مسؤول المشتريات أو الموظف من الموردين دون إذن صاحب المنشأة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        18
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم هدايا وعمولات الموظفين والمسؤولين والمشترين (الغلول)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي حميد الساعدي رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «هَدَايَا الْعُمَّالِ غُلُولٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (23604) والبيهقي وصححه الألباني في صحيح الجامع (7021)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم الهدايا والعمولات الخاصة التي يأخذها مسؤول المشتريات أو الموظف من الموردين دون إذن صاحب المنشأة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 19 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="شؤم المال الحرام واستحقاق النار للجسد النابت من السحت «لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ» رواه الترمذي (614) وصححه الألباني في صحيح سنن الترمذي تطهير التجارة من أي مكاسب خبيثة لأن أكل الحرام يحبط العمل ويمنع إجابة الدعاء ويمحق البركة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        19
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        شؤم المال الحرام واستحقاق النار للجسد النابت من السحت
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن كعب بن عجرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (614) وصححه الألباني في صحيح سنن الترمذي</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تطهير التجارة من أي مكاسب خبيثة لأن أكل الحرام يحبط العمل ويمنع إجابة الدعاء ويمحق البركة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 20 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="prohibitions" data-hadith-search="عقوبة اقتطاع أموال الناس وحقوقهم بالأيمان الكاذبة «مَنِ اقْتَطَعَ حَقَّ امْرِئٍ مُسْلِمٍ بِيَمِينِهِ، فَقَدْ أَوْجَبَ اللَّهُ لَهُ النَّارَ، وَحَرَّمَ عَلَيْهِ الْجَنَّةَ، فَقَالَ رَجُلٌ: وَإِنْ كَانَ شَيْئًا يَسِيرًا يَا رَسُولَ اللَّهِ؟ قَالَ: وَإِنْ قَضِيبًا مِنْ أَرَاكٍ» صحيح مسلم (137) حرمة أكل أموال الناس بالباطل أو إنكار الديون أو الفواتير المستحقة مهما كانت قيمتها ضئيلة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        20
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة اقتطاع أموال الناس وحقوقهم بالأيمان الكاذبة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف ورشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي أمامة إياس بن ثعلبة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنِ اقْتَطَعَ حَقَّ امْرِئٍ مُسْلِمٍ بِيَمِينِهِ، فَقَدْ أَوْجَبَ اللَّهُ لَهُ النَّارَ، وَحَرَّمَ عَلَيْهِ الْجَنَّةَ، فَقَالَ رَجُلٌ: وَإِنْ كَانَ شَيْئًا يَسِيرًا يَا رَسُولَ اللَّهِ؟ قَالَ: وَإِنْ قَضِيبًا مِنْ أَرَاكٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (137)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حرمة أكل أموال الناس بالباطل أو إنكار الديون أو الفواتير المستحقة مهما كانت قيمتها ضئيلة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 21 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="البركة في الصدق والبيان وشؤم الكتمان والكذب «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَتَمَا وَكَذَبَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا» صحيح البخاري (2079) وصحيح مسلم (1532) الصدق في بيان تكلفة وسعر السلعة وعيوبها هو مفتاح البركة والنماء، والكتمان والكذب يمحق الأرباح.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        21
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        البركة في الصدق والبيان وشؤم الكتمان والكذب
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَتَمَا وَكَذَبَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2079) وصحيح مسلم (1532)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الصدق في بيان تكلفة وسعر السلعة وعيوبها هو مفتاح البركة والنماء، والكتمان والكذب يمحق الأرباح.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 22 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="فضل التاجر الصدوق الأمين ومنزلته مع النبيين والشهداء «التَّاجِرُ الصَّدُوقُ الأَمِينُ مَعَ النَّبِيِّينَ وَالصِّدِّيقِينَ وَالشُّهَدَاءِ» رواه الترمذي (1209) وصححه الألباني في صحيح الترغيب (1782) بيان شرف مهنة التجارة إذا اقترنت بالأمانة والصدق والبعد عن الغش والخيانة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        22
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل التاجر الصدوق الأمين ومنزلته مع النبيين والشهداء
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «التَّاجِرُ الصَّدُوقُ الأَمِينُ مَعَ النَّبِيِّينَ وَالصِّدِّيقِينَ وَالشُّهَدَاءِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1209) وصححه الألباني في صحيح الترغيب (1782)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">بيان شرف مهنة التجارة إذا اقترنت بالأمانة والصدق والبعد عن الغش والخيانة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 23 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="السماحة واليسر في البيع والشراء والقضاء والمطالبة «رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى» صحيح البخاري (2076) الدعاء بالرحمة النبوية لكل من يتساهل ولا يشدد على الناس في المماكسة وسداد الديون.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        23
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        السماحة واليسر في البيع والشراء والقضاء والمطالبة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2076)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الدعاء بالرحمة النبوية لكل من يتساهل ولا يشدد على الناس في المماكسة وسداد الديون.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 24 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="النهي الصريح عن بيع ما لا يملك التاجر (بيع ما ليس عندك) «قُلْتُ: يَا رَسُولَ اللَّهِ، يَأْتِينِي الرَّجُلُ فَيُرِيدُ مِنِّي الْبَيْعَ لَيْسَ عِنْدِي، أَفَأَبْتَاعُهُ لَهُ مِنَ السُّوقِ؟ فَقَالَ: لَا تَبِعْ مَا لَيْسَ عِنْدَكَ» رواه أبو داود (3503) والترمذي (1232) وصححه الألباني تحريم إبرام عقد بيع باتّ على سلعة معينة قبل شرائها وقبضها ودخولها في ملك وضمان التاجر.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        24
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي الصريح عن بيع ما لا يملك التاجر (بيع ما ليس عندك)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «قُلْتُ: يَا رَسُولَ اللَّهِ، يَأْتِينِي الرَّجُلُ فَيُرِيدُ مِنِّي الْبَيْعَ لَيْسَ عِنْدِي، أَفَأَبْتَاعُهُ لَهُ مِنَ السُّوقِ؟ فَقَالَ: لَا تَبِعْ مَا لَيْسَ عِنْدَكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3503) والترمذي (1232) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم إبرام عقد بيع باتّ على سلعة معينة قبل شرائها وقبضها ودخولها في ملك وضمان التاجر.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 25 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="النهي عن بيوع الغرر والمخاطرة والجهالة في السلع «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ» صحيح مسلم (1513) وجوب نفي الجهالة وتحديد مواصفات وأبعاد وأوزان وأسعار السلع بدقة بالفاتورة لقطع النزاع.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        25
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بيوع الغرر والمخاطرة والجهالة في السلع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1513)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وجوب نفي الجهالة وتحديد مواصفات وأبعاد وأوزان وأسعار السلع بدقة بالفاتورة لقطع النزاع.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 26 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="وجوب الوفاء بالشروط التجارية والعقود المبرمة «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا أَوْ أَحَلَّ حَرَامًا» رواه الترمذي (1352) وصححه الألباني في الإرواء (1303) إلزامية كافة الشروط التجارية المتفق عليها (مواعيد التسليم، الدفعات، المواصفات) ما لم تخالف الشرع.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        26
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب الوفاء بالشروط التجارية والعقود المبرمة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عمرو بن عوف المزني رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا أَوْ أَحَلَّ حَرَامًا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1352) وصححه الألباني في الإرواء (1303)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إلزامية كافة الشروط التجارية المتفق عليها (مواعيد التسليم، الدفعات، المواصفات) ما لم تخالف الشرع.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 27 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم الغش والتطفيف والبراءة النبوية من الغاش «أَنَّ رَسُولَ اللَّهِ ﷺ مَرَّ عَلَى صُبْرَةِ طَعَامٍ فَأَدْخَلَ يَدَهُ فِيهَا، فَنَالَتْ أَصَابِعُهُ بَلَلًا، فَقَالَ: مَا هَذَا يَا صَاحِبَ الطَّعَامِ؟ قَالَ: أَصَابَتْهُ السَّمَاءُ يَا رَسُولَ اللَّهِ، قَالَ: أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي» صحيح مسلم (102) تحريم إخفاء البضاعة التالفة تحت السليمة أو تغيير المواصفات، ووجوب إظهار العيوب للمشتري.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        27
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الغش والتطفيف والبراءة النبوية من الغاش
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَّ رَسُولَ اللَّهِ ﷺ مَرَّ عَلَى صُبْرَةِ طَعَامٍ فَأَدْخَلَ يَدَهُ فِيهَا، فَنَالَتْ أَصَابِعُهُ بَلَلًا، فَقَالَ: مَا هَذَا يَا صَاحِبَ الطَّعَامِ؟ قَالَ: أَصَابَتْهُ السَّمَاءُ يَا رَسُولَ اللَّهِ، قَالَ: أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (102)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم إخفاء البضاعة التالفة تحت السليمة أو تغيير المواصفات، ووجوب إظهار العيوب للمشتري.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 28 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="فضل إقالة النادم وفسخ البيع برضا وقبول المرتجع «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ» رواه أبو داود (3460) وابن ماجه (2199) وصححه الألباني استحباب قبول إرجاع البضاعة واسترداد الثمن للزبون النادم الذي لم يعد بحاجة للسلعة وتفريج كربته.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        28
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل إقالة النادم وفسخ البيع برضا وقبول المرتجع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3460) وابن ماجه (2199) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب قبول إرجاع البضاعة واسترداد الثمن للزبون النادم الذي لم يعد بحاجة للسلعة وتفريج كربته.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 29 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="النهي والتحذير الشديد من الحلف الكاذب لترويج البضائع «الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ» صحيح البخاري (2087) وصحيح مسلم (1606) تحريم الإكثار من الحلف بالله في الأسواق لإقناع المشتري بالسعر أو جودة البضاعة.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        29
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي والتحذير الشديد من الحلف الكاذب لترويج البضائع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2087) وصحيح مسلم (1606)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم الإكثار من الحلف بالله في الأسواق لإقناع المشتري بالسعر أو جودة البضاعة.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 30 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم الحيل الربوية وبيع العِينة «إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ» رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11) تحريم التحايل على القرض الربوي بصور بيع صورية، كبيع السلعة بالآجل ثم شراؤها نقداً بأقل.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        30
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الحيل الربوية وبيع العِينة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن ابن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم التحايل على القرض الربوي بصور بيع صورية، كبيع السلعة بالآجل ثم شراؤها نقداً بأقل.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 31 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم النَّجْش (المزايدة الكاذبة في الأسواق والمزادات) «نَهَى النَّبِيُّ ﷺ عَنِ النَّجْشِ» صحيح البخاري (2142) وصحيح مسلم (1516) تحريم أن يرفع شخص سعر السلعة في المزاد أو السوق وهو لا يريد شراءها بل ليغر المشتري ويرفع السعر عليه.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        31
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم النَّجْش (المزايدة الكاذبة في الأسواق والمزادات)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى النَّبِيُّ ﷺ عَنِ النَّجْشِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2142) وصحيح مسلم (1516)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم أن يرفع شخص سعر السلعة في المزاد أو السوق وهو لا يريد شراءها بل ليغر المشتري ويرفع السعر عليه.</span>
                </div>
            </div>

        </div>
    
        <!-- Hadith Card 32 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="تحريم الاحتكار وحبس السلع الأساسية لرفع أسعارها «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ» صحيح مسلم (1605) تحريم شراء أقوات وحاجات الناس وتخزينها لحبسها عن الأسواق بغرض إغلاء الأسعار واستغلال الأزمات.">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        32
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الاحتكار وحبس السلع الأساسية لرفع أسعارها
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع وصدق وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن معمر بن عبد الله رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1605)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم شراء أقوات وحاجات الناس وتخزينها لحبسها عن الأسواق بغرض إغلاء الأسعار واستغلال الأزمات.</span>
                </div>
            </div>

        </div>
    
                    </div>

                    <div id="no-hadiths-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد أحاديث مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية الأحاديث.</p>
                    </div>
                    
                

                id="fiqh-sub-terms" class="space-y-4 hidden">
                    <div class="p-6 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-4">
                        <div class="border-b border-slate-800 pb-3">
                            <span class="px-3 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                📖 معجم المصطلحات الفقهية والاقتصادية
                            </span>
                            <h3 class="text-xl font-black text-white mt-2">
                                قاموس مصطلحات البيوع والمعاملات للتاجر المسلم
                            </h3>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                تعريفات موجزة ومحررة لأهم المصطلحات الفقهية المتداولة في الأسواق وعقود التجارة الحديثة.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            
                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded">1. عقد الاستصناع (Istisna'a)</span>
                                <h5 class="text-sm font-black text-white">طلب تصنيع سلعة بمواصفات محددة ومواد من الصانع</h5>
                                <p class="text-xs text-slate-300 font-bold">عقد على مبيع في الذمة شرط فيه العمل، كطلبيات حديد التسليح الخاص وتصنيع الهياكل المعدنية والخرسانات الجاهزة، ويجوز فيه تأجيل الثمن أو تقسيطه.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded">2. بيع السلم (Salam Sale)</span>
                                <h5 class="text-sm font-black text-white">شراء بضاعة آجلة بثمن مدفوع مقدماً بالكامل</h5>
                                <p class="text-xs text-slate-300 font-bold">بيع موصوف في الذمة مؤجل التسليم بثمن مقبوض فوراً في مجلس العقد، وهو وسيلة شرعية ممتازة لتمويل المشروعات الزراعية والمقاولات وتوريد المواد.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-sky-500/20 text-sky-300 font-mono font-black text-xs rounded">3. بيع العربون (Down Payment Sale)</span>
                                <h5 class="text-sm font-black text-white">دفع جزء من الثمن لتأكيد الشراء أو اعتباره هدراً للبائع</h5>
                                <p class="text-xs text-slate-300 font-bold">أن يشتري السلعة ويدفع جزءاً مقدماً فإن أتم الشراء حُسب من الثمن وإن تراجع اختص به البائع تعويضاً عن حجز السلعة (وهو مذهب الإمام أحمد وصحيح فتاوى ابن باز والعثيمين).</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 font-mono font-black text-xs rounded">4. بيع المرابحة للآمر بالشراء</span>
                                <h5 class="text-sm font-black text-white">شراء المؤسسة للسلعة ثم بيعها للعميل بالتقسيط بربح معلوم</h5>
                                <p class="text-xs text-slate-300 font-bold">أن يطلب العميل من البنك أو التاجر شراء معدات أو مواد محددة، فإذا تملكها وقبضها البائع باعها للعميل بالتقسيط بربح متفق عليه، بشرط خلوها من العينة.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-rose-500/20 text-rose-300 font-mono font-black text-xs rounded">5. بيع العِينة المحرم (Eina Sale)</span>
                                <h5 class="text-sm font-black text-white">بيع السلعة لأجل ثم شراؤها نقداً بثمن أقل</h5>
                                <p class="text-xs text-slate-300 font-bold">حيلة ربوية صريحة محرمة، وصورتها أن يبيع التاجر سلعة بـ 100 ألف لأجل ثم يشتريها في نفس المجلس نقداً بـ 80 ألفاً، فيكون القصد قرض 80 واسترداد 100.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition">
                                <span class="px-2 py-0.5 bg-cyan-500/20 text-cyan-300 font-mono font-black text-xs rounded">6. عقد المقاصة المالية (Settlement)</span>
                                <h5 class="text-sm font-black text-white">إسقاط دين مطلوب بدين مقابل متساوٍ</h5>
                                <p class="text-xs text-slate-300 font-bold">براءة ذمة كل من الطرفين بما عليه لصاحبه من التزامات مالية متقابلة متماثلة، وهي جائزة شرعاً وتوفر سيولة هائلة وتسرع تسوية الديون التجارية.</p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 8. SUITE: ENTERPRISE ZAKAT CALCULATOR                            -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-zakat" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(16, 185, 129, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        حاسبة الزكاة الشرعية الذكية للمؤسسات وعروض التجارة والسيولة
                    </h3>
                    <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                        نسبة ربع العشر (2.5%) عند حولان الحول
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">1. بضاعة عروض التجارة بالمخازن (بسعر السوق الجاري):</label>
                                <input type="number" id="zakat_inventory_val" value="10000000" oninput="calculateZakat()" class="tool-input text-lg font-bold text-emerald-400">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">2. النقدية والسيولة بالخزائن والحسابات البنكية:</label>
                                <input type="number" id="zakat_cash_val" value="3500000" oninput="calculateZakat()" class="tool-input text-lg font-bold text-emerald-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">3. الديون الجيدة المرجوة السداد على العملاء:</label>
                                <input type="number" id="zakat_debts_in" value="2000000" oninput="calculateZakat()" class="tool-input">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-rose-300 block mb-1">4. يُخصم: الديون والالتزامات الحالة على المنشأة:</label>
                                <input type="number" id="zakat_debts_out" value="1500000" oninput="calculateZakat()" class="tool-input text-rose-400">
                            </div>
                        </div>

                        <div class="bg-emerald-950/20 p-4 rounded-xl border border-emerald-500/30 flex items-center justify-between text-xs">
                            <span class="text-slate-300 font-bold">نصاب الذهب الشرعي (85 جرام عيار 24):</span>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">سعر جرام الذهب:</span>
                                <input type="number" id="gold_gram_price" value="80000" oninput="calculateZakat()" class="w-24 tool-input text-center py-1">
                                <span class="text-slate-400">SDG</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Zakat Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">📊 إجمالي الوعاء الزكوي الخاضع للزكاة</span>
                            <span class="text-3xl font-black text-sky-400 mt-1 block font-mono" id="res_zakat_pool">0 SDG</span>
                            <span class="text-xs text-sky-300/80 font-bold mt-1 block" id="res_nisab_status">بلغ النصاب الشرعي ✓</span>
                        </div>

                        <div class="calc-result-box" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.3)); border-color: #10b981;">
                            <span class="text-xs text-emerald-300 font-bold block">⚖️ مقدار الزكاة الواجب إخراجها فوراً (2.5%)</span>
                            <span class="text-4xl font-black text-emerald-400 mt-1 block font-mono" id="res_zakat_due">0 SDG</span>
                            <span class="text-xs text-emerald-200/90 font-bold mt-1 block">ربع العشر من وعاء التجارة والسيولة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 9. SUITE: FEASIBILITY, MARGIN & BREAK-EVEN CALCULATOR            -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-feasibility" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(244, 63, 94, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-rose-400 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line"></i>
                        حاسبة الجدوى، نقطة التعادل (Break-Even)، وهامش الربح مقابل الإضافة
                    </h3>
                    <span class="text-xs font-bold text-rose-400 bg-rose-500/10 px-3 py-1 rounded-lg border border-rose-500/20">
                        Margin vs Markup & ROI
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المصاريف الثابتة الشهرية (إيجار + رواتب + كهرباء):</label>
                                <input type="number" id="fe_fixed_costs" value="500000" oninput="calculateFeasibility()" class="tool-input text-lg font-bold">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">متوسط سعر بيع الوحدة / القطعة:</label>
                                <input type="number" id="fe_unit_price" value="1000" oninput="calculateFeasibility()" class="tool-input text-lg font-bold text-emerald-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">التكلفة المتغيرة للوحدة (سعر الشراء):</label>
                                <input type="number" id="fe_unit_cost" value="750" oninput="calculateFeasibility()" class="tool-input text-rose-400">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">المبيعات الشهرية المتوقعة (عدد القطع):</label>
                                <input type="number" id="fe_expected_qty" value="3000" oninput="calculateFeasibility()" class="tool-input text-sky-400">
                            </div>
                        </div>
                    </div>

                    <!-- Feasibility Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box" style="border-color: rgba(245, 158, 11, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">🎯 نقطة التعادل (Break-Even Units)</span>
                            <span class="text-3xl font-black text-amber-400 mt-1 block font-mono" id="res_breakeven_units">0 قطعة</span>
                            <span class="text-xs text-amber-300/80 font-bold mt-1 block" id="res_breakeven_revenue">0 SDG مبيعات للتعادل</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">💵 صافي الأرباح الشهرية المتوقعة</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_net_profit_month">0 SDG</span>
                            <span class="text-xs text-emerald-300/80 font-bold mt-1 block" id="res_margin_markup_info">هامش الربح: %0 | الإضافة: %0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 10. SUITE: PAYROLL & GRATUITY CALCULATOR                         -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="suite-sec-hr_gratuity" class="suite-section anim-suite hidden space-y-6">
            <div class="glass-panel-v3" style="--border-color: rgba(232, 121, 249, 0.4);">
                <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-black text-fuchsia-400 flex items-center gap-2">
                        <i class="fa-solid fa-user-tie"></i>
                        حاسبة مكافأة نهاية الخدمة، مستحقات الإجازات، وقوانين العمل
                    </h3>
                    <span class="text-xs font-bold text-fuchsia-400 bg-fuchsia-500/10 px-3 py-1 rounded-lg border border-fuchsia-500/20">
                        وفق معايير وقوانين العمل المعتمدة
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4 lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">الراتب الأساسي الأخير للموظف:</label>
                                <input type="number" id="hr_basic_salary" value="200000" oninput="calculateGratuity()" class="tool-input text-lg font-bold text-fuchsia-400">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">سنوات الخدمة في المنشأة:</label>
                                <input type="number" step="0.5" id="hr_service_years" value="6.5" oninput="calculateGratuity()" class="tool-input text-lg font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">سبب انتهاء عقد العمل:</label>
                                <select id="hr_end_reason" onchange="calculateGratuity()" class="tool-input">
                                    <option value="contract_end">انتهاء العقد / فصل تعسفي / تقاعد (مستحقات 100%)</option>
                                    <option value="resignation">استقالة الموظف بمحض إرادته</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 block mb-1">رصيد الإجازات غير المستنفذة (أيام):</label>
                                <input type="number" id="hr_leave_days" value="21" oninput="calculateGratuity()" class="tool-input">
                            </div>
                        </div>
                    </div>

                    <!-- Gratuity Results -->
                    <div class="space-y-3">
                        <div class="calc-result-box" style="border-color: rgba(232, 121, 249, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">💼 مكافأة نهاية الخدمة المستحقة</span>
                            <span class="text-3xl font-black text-fuchsia-400 mt-1 block font-mono" id="res_gratuity_amount">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(56, 189, 248, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">🏖️ بدل الإجازات السنوية المستحق</span>
                            <span class="text-2xl font-black text-sky-400 mt-1 block font-mono" id="res_leave_amount">0 SDG</span>
                        </div>

                        <div class="calc-result-box" style="border-color: rgba(16, 185, 129, 0.4);">
                            <span class="text-xs text-slate-400 font-bold block">💵 إجمالي المستحقات النهائية للموظف</span>
                            <span class="text-3xl font-black text-emerald-400 mt-1 block font-mono" id="res_total_hr_due">0 SDG</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            نظام Al-Sajana Smart ERP 2026 · منصة المساعدات والحاسبات الهندسية والتجارية ومفاتيح البنوك
        </div>

    </div>

    <!-- JavaScript Controllers -->
    <script>
    let activeScholarFilter = 'all';
    let activeTopicFilter = 'all';

    
    // 🚀 AUTO-ACTIVATE TAB FROM URL HASH OR PARAMETER 🚀
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
        
        if (tabParam) {
            const targetPill = document.querySelector('[onclick*="switchSuite(\'' + tabParam + '\'"]');
            if (targetPill) {
                switchSuite(tabParam, targetPill);
            }
        }
    });

    // ════════════════════════════════════════════════════════════════════════════
    // 🏦 REAL-TIME AUTOMATED SUDANESE BBAN & IBAN GENERATOR 🏦
    // ════════════════════════════════════════════════════════════════════════════
    function generateUserBBAN() {
        const bankSelect = document.getElementById('bban_bank_selector');
        const accInput = document.getElementById('bban_raw_account');
        const displaySpan = document.getElementById('generated_bban_display');
        const lenBadge = document.getElementById('bban_len_counter');
        const stripFeedback = document.getElementById('bban_strip_feedback');

        if (!bankSelect || !accInput || !displaySpan) return;

        let bankPrefix = bankSelect.value.trim() || '170';
        if (bankPrefix.includes('|')) bankPrefix = bankPrefix.split('|')[0];
        
        let rawAcc = accInput.value.replace(/\D/g, '');
        let cleanAcc = rawAcc.replace(/^0+/, '');

        // Auto-clean leading zero from input box
        if (rawAcc !== cleanAcc && accInput.value.startsWith('0')) {
            accInput.value = cleanAcc;
            if (stripFeedback) {
                stripFeedback.textContent = '✓ تم حذف الصفر الأول تلقائياً بنجاح!';
                stripFeedback.className = 'text-emerald-400 font-bold';
            }
        }

        const branchCode = '00001';

        if (!cleanAcc) {
            displaySpan.innerHTML = `<span class="text-amber-400 font-mono">${bankPrefix}</span><span class="text-slate-600 font-mono">-------</span><span class="text-emerald-400 font-mono">${branchCode}</span>`;
            if (lenBadge) {
                lenBadge.innerHTML = `عدد الخانات: <span class="text-amber-300 font-black">اكتب رقم الحساب لتوليد الآيبان</span>`;
            }
            return;
        }

        // Format to at least 6 digits
        let formattedAcc = cleanAcc;
        while (formattedAcc.length < 6) {
            formattedAcc = '0' + formattedAcc;
        }

        const fullBBAN = bankPrefix + formattedAcc + branchCode;

        // Visual distinction: Bank Prefix (Gold) + Account (Cyan) + Branch (Emerald)
        displaySpan.innerHTML = `<span class="text-amber-400 font-black font-mono">${bankPrefix}</span><span class="text-cyan-300 font-black font-mono tracking-widest">${formattedAcc}</span><span class="text-emerald-400 font-black font-mono">${branchCode}</span>`;
        
        if (lenBadge) {
            lenBadge.innerHTML = `عدد الخانات: <strong class="text-emerald-400 font-mono font-black">${fullBBAN.length} رقماً (مكتمل وجاهز للتحويل ✓)</strong>`;
        }
    }

    function copyGeneratedBBAN() {
        const accInput = document.getElementById('bban_raw_account');
        if (!accInput || !accInput.value.trim()) {
            alert('الرجاء كتابة رقم الحساب أولاً ليتم توليد ونسخ الآيبان.');
            if (accInput) accInput.focus();
            return;
        }

        const displaySpan = document.getElementById('generated_bban_display');
        const fullBBAN = displaySpan ? displaySpan.textContent.replace(/\s+/g, '') : '';
        
        if (!fullBBAN || fullBBAN.includes('-')) {
            alert('الرجاء التأكد من كتابة رقم الحساب.');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(fullBBAN).then(() => {
                alert('✓ تم نسخ رقم الـ BBAN بنجاح:\n' + fullBBAN + '\n(جاهز للصق في تطبيق البنك للتحويل الفوري).');
            }).catch(() => {
                fallbackCopyText(fullBBAN);
            });
        } else {
            fallbackCopyText(fullBBAN);
        }
    }

    function fallbackCopyText(text) {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        alert('✓ تم نسخ رقم الـ BBAN بنجاح:\n' + text);
    }

    function searchBanks(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.bban-bank-card');
        cards.forEach(card => {
            const text = (card.textContent || '').toLowerCase();
            if (!q || text.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function quickSelectBank(code) {
        const sel = document.getElementById('bban_bank_selector');
        if (sel) {
            sel.value = code;
            generateUserBBAN();
            sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function switchSuite(suiteId, el) {
        document.querySelectorAll('.suite-section').forEach(sec => sec.classList.add('hidden'));
        document.querySelectorAll('.suite-pill-card').forEach(btn => btn.classList.remove('active'));

        const targetSec = document.getElementById('suite-sec-' + suiteId);
        if (targetSec) targetSec.classList.remove('hidden');
        if (el) el.classList.add('active');

        if (suiteId === 'fiqh') {
            if (typeof initFiqhEngine === 'function') {
                initFiqhEngine();
            }
        }
    }

    // ── Fiqh Sub-Tabs Controller ──
    
    let activeHadithCat = 'all';
    function setHadithCategory(cat, btn) {
        activeHadithCat = cat;
        document.querySelectorAll('.hadith-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterHadiths();
    }

    function filterHadiths() {
        const query = (document.getElementById('hadith-search-input') ? document.getElementById('hadith-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-hadith-card').forEach(card => {
            const cat = card.getAttribute('data-hadith-cat');
            const search = card.getAttribute('data-hadith-search') || '';

            const matchCat = (activeHadithCat === 'all' || cat === activeHadithCat);
            const matchSearch = (query === '' || search.includes(query));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-hadiths-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    let activeQuranTopic = 'all';
    function setQuranTopic(cat, btn) {
        activeQuranTopic = cat;
        document.querySelectorAll('.quran-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterQuranVerses();
    }

    function filterQuranVerses() {
        const query = (document.getElementById('quran-search-input') ? document.getElementById('quran-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-ayah-card').forEach(card => {
            const cat = card.getAttribute('data-ayah-topic');
            const search = card.getAttribute('data-ayah-search') || '';

            const matchCat = (activeQuranTopic === 'all' || cat === activeQuranTopic);
            const matchSearch = (query === '' || search.includes(query));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-quran-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    let activeBookScholar = 'all';
    function setBookScholar(scholar, btn) {
        activeBookScholar = scholar;
        document.querySelectorAll('.book-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterBooksLibrary();
    }

    function filterBooksLibrary() {
        const query = (document.getElementById('book-search-input') ? document.getElementById('book-search-input').value : '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-book-card').forEach(card => {
            const scholar = card.getAttribute('data-book-scholar');
            const search = card.getAttribute('data-book-search') || '';

            const matchScholar = (activeBookScholar === 'all' || scholar === activeBookScholar);
            const matchSearch = (query === '' || search.includes(query));

            if (matchScholar && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-books-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    
    // 🌐 LIVE GOOGLE SCHOLAR SEARCH ENGINE CONTROLLER 🌐
    const googleScholarNamesMap = {
        'all': '("ابن باز" OR "ابن عثيمين" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني" OR "صالح الفوزان")',
        'ibn_baz': '"ابن باز"',
        'ibn_uthaymeen': '"ابن عثيمين"',
        'al_fawzan': '"صالح الفوزان"',
        'ibn_taymiyyah': '"ابن تيمية"',
        'ibn_al_qayyim': '"ابن القيم"',
        'imam_ahmad': '"أحمد بن حنبل"',
        'al_nawawi': '"النووي"',
        'al_dhahabi': '"الذهبي"',
        'muhammad_bin_abdulwahhab': '"محمد بن عبد الوهاب"',
        'al_saadi': '"السعدي"',
        'al_shinqiti': '"الشنقيطي"',
        'al_albani': '"الألباني"'
    };

    const googleSitesMap = {
        'all_approved': '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)',
        'official_only': '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org)',
        'shamela': 'site:shamela.ws',
        'binbaz': 'site:binbaz.org.sa',
        'uthaymeen': 'site:ibnothaimeen.net',
        'fawzan': 'site:alfawzan.af.org.sa',
        'albany': 'site:alalbany.org',
        'dorar': 'site:dorar.net',
        'islamweb': 'site:islamweb.net',
        'alukah': 'site:alukah.net'
    };

    
    // 🌟 FIQH CLEAN SEARCH CONTROLLERS 🌟
    function executeInternalFiqhSearch() {
        switchFiqhSubTab('fatwas');
        executeLiveFilter();
        const fatwasContainer = document.getElementById('fiqh-sub-fatwas');
        if (fatwasContainer) {
            fatwasContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function executeScopedGoogle12Search() {
        const input = document.getElementById('fiqh-search-input');
        const q = input ? input.value.trim() : '';
        const query = q !== '' ? q : 'فتاوى البيوع والمعاملات المالية';
        const scholars = '("ابن باز" OR "ابن عثيمين" OR "صالح الفوزان" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني")';
        const sites = '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)';
        const fullQuery = query + ' ' + scholars + ' ' + sites;
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullQuery);
        window.open(googleUrl, '_blank');
    }

    function clearFiqhInput() {
        const input = document.getElementById('fiqh-search-input');
        if (input) {
            input.value = '';
            input.focus();
        }
        executeLiveFilter();
    }

    function executeHeaderGoogleSearch() {
        const q = (document.getElementById('google_quick_input') ? document.getElementById('google_quick_input').value : '').trim();
        if (!q) {
            alert('يرجى كتابة مسألتك أو سؤالك أولاً للبحث في قوقل.');
            if (document.getElementById('google_quick_input')) document.getElementById('google_quick_input').focus();
            return;
        }
        const scholars = '("ابن باز" OR "ابن عثيمين" OR "صالح الفوزان" OR "ابن تيمية" OR "ابن القيم" OR "أحمد بن حنبل" OR "النووي" OR "الذهبي" OR "محمد بن عبد الوهاب" OR "السعدي" OR "الشنقيطي" OR "الألباني")';
        const sites = '(site:binbaz.org.sa OR site:ibnothaimeen.net OR site:alfawzan.af.org.sa OR site:alalbany.org OR site:shamela.ws OR site:islamweb.net OR site:alukah.net OR site:dorar.net)';
        const fullQuery = q + ' ' + scholars + ' ' + sites;
        window.open('https://www.google.com/search?q=' + encodeURIComponent(fullQuery), '_blank');
    }

    function executeGoogleScholarSearch() {
        const queryInput = (document.getElementById('google-fiqh-query') ? document.getElementById('google-fiqh-query').value : '').trim();
        if (!queryInput) {
            alert('يرجى كتابة كلمة البحث أو السؤال الفقهي أولاً للبحث في قوقل.');
            if (document.getElementById('google-fiqh-query')) document.getElementById('google-fiqh-query').focus();
            return;
        }

        const scholarKey = document.getElementById('google-scholar-select') ? document.getElementById('google-scholar-select').value : 'all';
        const sitesKey = document.getElementById('google-sites-select') ? document.getElementById('google-sites-select').value : 'all_approved';

        const scholarDork = googleScholarNamesMap[scholarKey] || googleScholarNamesMap['all'];
        const sitesDork = googleSitesMap[sitesKey] || googleSitesMap['all_approved'];

        const fullSearchQuery = queryInput + ' ' + scholarDork + ' ' + sitesDork;
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullSearchQuery);
        window.open(googleUrl, '_blank');
    }

    function quickGoogleSearch(phrase) {
        if (document.getElementById('google-fiqh-query')) {
            document.getElementById('google-fiqh-query').value = phrase;
        }
        executeGoogleScholarSearch();
    }

    function executeQuickGoogleSearch() {
        const queryInput = (document.getElementById('fiqh-search-input') ? document.getElementById('fiqh-search-input').value : '').trim();
        const baseQuery = queryInput !== '' ? queryInput : 'فتاوى البيوع والمعاملات المالية';
        const fullSearchQuery = baseQuery + ' ' + googleScholarNamesMap['all'] + ' ' + googleSitesMap['all_approved'];
        const googleUrl = 'https://www.google.com/search?q=' + encodeURIComponent(fullSearchQuery);
        window.open(googleUrl, '_blank');
    }

    function switchFiqhSubTab(subId, el) {
        document.getElementById('fiqh-sub-fatwas').classList.add('hidden');
        document.getElementById('fiqh-sub-books').classList.add('hidden');
        document.getElementById('fiqh-sub-maxims').classList.add('hidden');
        document.getElementById('fiqh-sub-terms').classList.add('hidden');
        if (document.getElementById('fiqh-sub-quran')) document.getElementById('fiqh-sub-quran').classList.add('hidden');
        if (document.getElementById('fiqh-sub-google')) document.getElementById('fiqh-sub-google').classList.add('hidden');
        if (document.getElementById('fiqh-sub-hadiths')) document.getElementById('fiqh-sub-hadiths').classList.add('hidden');

        document.querySelectorAll('.fiqh-nav-pill').forEach(btn => btn.classList.remove('active'));

        const target = document.getElementById('fiqh-sub-' + subId);
        if (target) target.classList.remove('hidden');
        if (el) el.classList.add('active');

        if (subId === 'fatwas' && typeof initFiqhEngine === 'function') {
            initFiqhEngine();
        }
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 MASTER 4,500 FATWAS ENGINE (ON-DEMAND LAZY LOAD + PRE-INDEXED SEARCH) 🌟
    // ════════════════════════════════════════════════════════════════════════════
    let currentScholarFilter = 'all';
    let currentTopicFilter = 'all';
    const PAGE_SIZE = 21; // Exactly 21 fatwas per page requested
    let currentPage = 1;
    let currentFilteredList = [];
    let fiqhLoadingPromise = null;

    function getMasterFatwas() {
        return window.MASTER_20000_FATWAS || window.MASTER_5500_FATWAS || [];
    }

    function ensureFiqhLoaded(callback) {
        if (window.MASTER_20000_FATWAS && Array.isArray(window.MASTER_20000_FATWAS) && window.MASTER_20000_FATWAS.length > 0) {
            if (callback) callback();
            return Promise.resolve();
        }

        if (fiqhLoadingPromise) {
            return fiqhLoadingPromise.then(() => {
                if (callback) callback();
            });
        }

        const grid = document.getElementById('fiqh_fatwas_grid');
        const badge = document.getElementById('fiqh_search_badge');
        if (badge) {
            badge.innerHTML = '<i class="fa-solid fa-cloud-arrow-down text-amber-400 animate-pulse"></i> جاري تجهيز الموسوعة (4,500 فتوى)...';
        }
        if (grid && (!grid.children || grid.children.length === 0)) {
            grid.innerHTML = `
                <div class="col-span-full py-16 px-6 text-center bg-slate-900/90 border-2 border-amber-500/50 rounded-2xl shadow-2xl space-y-4">
                    <div class="inline-flex p-4 bg-amber-500/10 rounded-full border border-amber-400/40 animate-pulse">
                        <i class="fa-solid fa-book-quran text-4xl text-amber-400"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-white">جاري تحميل موسوعة الـ 4,500 فتوى موثقة...</h3>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
                        يتم الآن تجهيز الموسوعة وتحميلها بخفة فائقة لتعمل مباشرة من ذاكرة المتصفح بسرعة خيالية ودون استهلاك للإنترنت في الزيارات القادمة.
                    </p>
                    <div class="w-48 sm:w-64 h-2 bg-slate-800 rounded-full mx-auto overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-500 via-emerald-400 to-amber-500 animate-pulse w-full"></div>
                    </div>
                </div>
            `;
        }

        fiqhLoadingPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'js/fiqh_20000_data.js?v=4500_v1';
            script.async = true;
            script.onload = () => {
                // Pre-index search tokens once for ultra-fast zero-freeze searching
                const dataset = getMasterFatwas();
                for (let i = 0; i < dataset.length; i++) {
                    const item = dataset[i];
                    item._searchStr = (
                        (item.question || '') + ' ' +
                        (item.summary || '') + ' ' +
                        (item.evidence || '') + ' ' +
                        (item.scholar_name || '') + ' ' +
                        (item.topic_name || '') + ' ' +
                        (item.tags || '') + ' ' +
                        (item.ruling || '') + ' ' +
                        (item.source || '')
                    ).toLowerCase();
                }
                resolve();
            };
            script.onerror = (err) => {
                console.error('Failed to load fiqh dataset', err);
                if (grid) {
                    grid.innerHTML = `
                        <div class="col-span-full py-10 px-4 text-center bg-rose-950/60 border border-rose-500/50 rounded-xl space-y-2">
                            <i class="fa-solid fa-triangle-exclamation text-3xl text-rose-400"></i>
                            <div class="text-white font-black">تعذر تحميل بيانات الفتاوى، يرجى التحقق من الاتصال بالإنترنت والمحاولة مجدداً.</div>
                            <button type="button" onclick="ensureFiqhLoaded(() => initFiqhEngine())" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-black">إعادة المحاولة</button>
                        </div>
                    `;
                }
                reject(err);
            };
            document.body.appendChild(script);
        }).then(() => {
            if (callback) callback();
        });

        return fiqhLoadingPromise;
    }

    function initFiqhEngine() {
        ensureFiqhLoaded(() => {
            currentFilteredList = getMasterFatwas();
            currentPage = 1;
            renderFatwaCards();
        });
    }

    function renderFatwaCards() {
        const grid = document.getElementById('fiqh_fatwas_grid');
        const badge = document.getElementById('fiqh_search_badge');
        if (!grid) return;

        const totalAll = getMasterFatwas().length || 4500;
        const totalMatches = currentFilteredList.length;
        const totalPages = Math.max(1, Math.ceil(totalMatches / PAGE_SIZE));

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        if (badge) {
            if (totalMatches === totalAll) {
                badge.innerHTML = `<i class="fa-solid fa-layer-group text-emerald-400"></i> إجمالي <strong>${totalAll.toLocaleString()}</strong> فتوى | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (21 فتوى في الصفحة)`;
            } else {
                badge.innerHTML = `<i class="fa-solid fa-filter text-amber-400"></i> عُثر على <strong>${totalMatches.toLocaleString()}</strong> من أصل ${totalAll.toLocaleString()} فتوى | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong>`;
            }
        }

        if (totalMatches === 0) {
            grid.innerHTML = `
                <div class="col-span-full p-10 bg-slate-900/90 border-2 border-dashed border-amber-500/40 rounded-3xl text-center space-y-3">
                    <span class="text-4xl">🔍</span>
                    <h4 class="text-lg font-black text-amber-300">لم يتم العثور على نتائج مطابقة لبحثك في موسوعة الفتاوى</h4>
                    <p class="text-xs text-slate-300 font-bold">جرب كتابة كلمة أخرى مثل: تقسيط، شيك، ذهب، عربون، استصناع، أو تصفح حسب الشيوخ الـ 12.</p>
                    <button type="button" onclick="clearFiqhSearch()" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-lg transition">
                        ✕ إلغاء الفلترة وتصفح كافة الفتاوى
                    </button>
                </div>
            `;
            updatePaginationBarHtml('fiqh_pagination_bar_top', totalPages, 0, 0, 0);
            updatePaginationBarHtml('fiqh_pagination_bar_bottom', totalPages, 0, 0, 0);
            return;
        }

        // Slice exactly 21 items for current page
        const startIndex = (currentPage - 1) * PAGE_SIZE;
        const endIndex = Math.min(startIndex + PAGE_SIZE, totalMatches);
        const pageItems = currentFilteredList.slice(startIndex, endIndex);

        let html = '';
        pageItems.forEach((item) => {
            let rulingClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/50';
            if (item.ruling.includes('محرم') || item.ruling.includes('باطل') || item.ruling.includes('لا يجوز') || item.ruling.includes('كبيرة') || item.ruling.includes('سحت')) {
                rulingClass = 'bg-rose-500/20 text-rose-300 border-rose-500/50';
            } else if (item.ruling.includes('ضوابط') || item.ruling.includes('مشروطة') || item.ruling.includes('مكروه') || item.ruling.includes('تحري')) {
                rulingClass = 'bg-amber-500/20 text-amber-300 border-amber-500/50';
            } else if (item.ruling.includes('واجب')) {
                rulingClass = 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50';
            } else if (item.ruling.includes('مستحب') || item.ruling.includes('مندوب')) {
                rulingClass = 'bg-teal-500/20 text-teal-300 border-teal-500/50';
            }

            const hadithBox = item.evidence ? `
                <div class="p-3 bg-gradient-to-r from-amber-950/40 via-slate-900/90 to-amber-950/30 border-r-4 border-amber-500/80 rounded-xl space-y-1.5 my-2.5 shadow-inner">
                    <div class="flex items-center gap-1.5 text-[11px] font-black text-amber-300">
                        <i class="fa-solid fa-book-quran text-amber-400"></i>
                        <span>الأثر والدليل من السنة الصحيحة والقرآن:</span>
                    </div>
                    <div class="text-[12px] text-amber-100/95 font-serif leading-relaxed pr-1 font-bold">
                        ${item.evidence}
                    </div>
                </div>
            ` : '';

            html += `
                <div class="fiqh-master-card flex flex-col justify-between" data-scholar="${item.scholar_code}" data-topic="${item.topic_code}">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                            <span class="text-xs font-black text-amber-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-graduation-cap text-amber-400"></i>
                                ${item.scholar_name}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-black border ${rulingClass}">
                                ${item.ruling}
                            </span>
                        </div>
                        <h4 class="text-sm md:text-base font-black text-white leading-snug">
                            <span class="text-amber-400/80 font-mono text-xs">#${item.id}</span> ${item.question}
                        </h4>
                        ${hadithBox}
                        <p class="text-xs text-slate-300 leading-relaxed font-bold">
                            ${item.summary}
                        </p>
                    </div>
                    <div class="mt-4 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 font-bold">
                        <span class="text-cyan-400"><i class="fa-solid fa-tag ml-1"></i>${item.topic_name}</span>
                        <span class="font-mono text-slate-500 text-[10px] truncate max-w-[180px]">${item.source}</span>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
        updatePaginationBarHtml('fiqh_pagination_bar_top', totalPages, totalMatches, startIndex + 1, endIndex);
        updatePaginationBarHtml('fiqh_pagination_bar_bottom', totalPages, totalMatches, startIndex + 1, endIndex);
    }

    function updatePaginationBarHtml(barId, totalPages, totalMatches, fromItem, toItem) {
        const bar = document.getElementById(barId);
        if (!bar) return;

        if (totalMatches === 0) {
            bar.innerHTML = '';
            return;
        }

        const prevDisabled = currentPage <= 1 ? 'disabled opacity-30 cursor-not-allowed pointer-events-none' : '';
        const nextDisabled = currentPage >= totalPages ? 'disabled opacity-30 cursor-not-allowed pointer-events-none' : '';

        let pagesHtml = `
            <!-- Main Navigation Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="goToPage(1)" class="px-3.5 py-2 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-amber-400 border border-amber-500/50 rounded-xl text-xs sm:text-sm font-black transition flex items-center gap-1 ${prevDisabled}">
                    ⏮️ الأولى
                </button>
                <button type="button" onclick="goToPage(${currentPage - 1})" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 border border-amber-400 rounded-xl text-xs sm:text-sm font-black shadow-lg transition flex items-center gap-1.5 ${prevDisabled}">
                    ◀️ الصفحة السابقة
                </button>
                <button type="button" onclick="goToPage(${currentPage + 1})" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 border border-amber-400 rounded-xl text-xs sm:text-sm font-black shadow-lg transition flex items-center gap-1.5 ${nextDisabled}">
                    الصفحة التالية ▶️
                </button>
                <button type="button" onclick="goToPage(${totalPages})" class="px-3.5 py-2 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-amber-400 border border-amber-500/50 rounded-xl text-xs sm:text-sm font-black transition flex items-center gap-1 ${nextDisabled}">
                    الأخيرة (${totalPages}) ⏭️
                </button>
            </div>

            <!-- Surrounding Page Number Buttons -->
            <div class="flex items-center gap-1.5 overflow-x-auto py-1">
        `;

        let startP = Math.max(1, currentPage - 2);
        let endP = Math.min(totalPages, startP + 4);
        if (endP - startP < 4) {
            startP = Math.max(1, endP - 4);
        }

        if (startP > 1) {
            pagesHtml += `<button type="button" onclick="goToPage(1)" class="px-3 py-1.5 bg-slate-900 text-slate-400 border border-slate-800 rounded-lg text-xs font-bold hover:border-amber-400">1</button>`;
            if (startP > 2) pagesHtml += '<span class="text-slate-500 font-black px-1">...</span>';
        }

        for (let p = startP; p <= endP; p++) {
            if (p === currentPage) {
                pagesHtml += `<button type="button" class="px-4 py-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-black rounded-xl text-xs sm:text-sm shadow-lg border-2 border-amber-300 transform scale-105">
                    ${p}
                </button>`;
            } else {
                pagesHtml += `<button type="button" onclick="goToPage(${p})" class="px-3 py-1.5 bg-slate-900 text-slate-300 hover:border-amber-400 border border-slate-700 rounded-lg text-xs sm:text-sm font-bold transition">
                    ${p}
                </button>`;
            }
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) pagesHtml += '<span class="text-slate-500 font-black px-1">...</span>';
            pagesHtml += `<button type="button" onclick="goToPage(${totalPages})" class="px-3 py-1.5 bg-slate-900 text-slate-400 border border-slate-800 rounded-lg text-xs font-bold hover:border-amber-400">${totalPages}</button>`;
        }

        pagesHtml += `
            </div>

            <!-- Direct Jump to Page Box -->
            <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-slate-300">
                <span>عرض (${fromItem.toLocaleString()} - ${toItem.toLocaleString()}) | انتقال إلى صفحة:</span>
                <input type="number" id="${barId}_jump_input" min="1" max="${totalPages}" value="${currentPage}" class="w-16 py-1.5 px-2 bg-slate-900 border-2 border-amber-500/60 focus:border-amber-400 text-white rounded-xl text-center text-xs sm:text-sm font-black shadow-inner">
                <button type="button" onclick="jumpToPageInput('${barId}_jump_input')" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow transition">
                    انتقال ↵
                </button>
            </div>
        `;

        bar.innerHTML = pagesHtml;
    }

    function goToPage(page) {
        const totalMatches = currentFilteredList.length;
        const totalPages = Math.max(1, Math.ceil(totalMatches / PAGE_SIZE));
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        renderFatwaCards();

        const grid = document.getElementById('fiqh_fatwas_grid');
        if (grid) {
            grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function jumpToPageInput(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            const val = parseInt(input.value, 10);
            if (!isNaN(val)) {
                goToPage(val);
            }
        }
    }

    // Real-time live filtering on every keystroke
    let searchDebounceTimer = null;
    function filterFatwas() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            executeLiveFilter();
        }, 50); // Instant 50ms response
    }

    function executeLiveFilter() {
        const input = document.getElementById('fiqh-search-input');
        const q = input ? input.value.trim().toLowerCase() : '';
        const queryTerms = q ? q.split(/\s+/).filter(t => t.length > 0) : [];
        const dataset = getMasterFatwas();

        currentPage = 1; // Reset to page 1 on new search query

        currentFilteredList = dataset.filter(item => {
            // Scholar filter match
            if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) {
                return false;
            }
            // Topic filter match
            if (currentTopicFilter !== 'all' && item.topic_code !== currentTopicFilter) {
                return false;
            }
            // Search query match (ultra-fast pre-indexed tokens with multi-word AND)
            if (queryTerms.length > 0) {
                const s = item._searchStr || (item.question + ' ' + item.summary).toLowerCase();
                return queryTerms.every(term => s.includes(term));
            }
            return true;
        });

        renderFatwaCards();
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 5-SUBBOX HUB & SCHOLAR SELECT CONTROLLERS 🌟
    // ════════════════════════════════════════════════════════════════════════════
    function onScholarSelectChange(val) {
        currentScholarFilter = val;
        
        const googleScholarSelect = document.getElementById('google-scholar-select');
        if (googleScholarSelect) {
            googleScholarSelect.value = val;
        }

        const fatwasPill = document.querySelector('.fiqh-nav-pill');
        if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
            switchFiqhSubTab('fatwas', fatwasPill);
        }

        executeLiveFilter();
    }

    function showAllFiqhTransactions(btn) {
        const searchInput = document.getElementById('fiqh-search-input');
        if (searchInput) searchInput.value = '';
        
        currentScholarFilter = 'all';
        currentTopicFilter = 'all';
        
        const scholarSelect = document.getElementById('fiqh-scholar-select');
        if (scholarSelect) scholarSelect.value = 'all';

        const googleScholarSelect = document.getElementById('google-scholar-select');
        if (googleScholarSelect) googleScholarSelect.value = 'all';
        
        document.querySelectorAll('.fiqh-pill-btn').forEach(el => el.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const fatwasPill = document.querySelector('.fiqh-nav-pill');
        if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
            switchFiqhSubTab('fatwas', fatwasPill);
        }
        
        executeLiveFilter();
    }

    function setQuickSearch(word, el) {
        const input = document.getElementById('fiqh-search-input');
        if (input) input.value = word;
        
        currentTopicFilter = 'all';

        const allBtn = document.getElementById('btn-show-all-fiqh');
        if (allBtn) allBtn.classList.remove('active');
        
        document.querySelectorAll('.fiqh-pill-btn').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');

        const fatwasPill = document.querySelector('.fiqh-nav-pill');
        if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
            switchFiqhSubTab('fatwas', fatwasPill);
        }
        
        executeLiveFilter();
    }

    function clearFiqhSearch() {
        const input = document.getElementById('fiqh-search-input');
        if (input) input.value = '';
        
        currentScholarFilter = 'all';
        currentTopicFilter = 'all';
        
        const scholarSelect = document.getElementById('fiqh-scholar-select');
        if (scholarSelect) scholarSelect.value = 'all';

        const googleScholarSelect = document.getElementById('google-scholar-select');
        if (googleScholarSelect) googleScholarSelect.value = 'all';
        
        const allBtn = document.getElementById('btn-show-all-fiqh');
        if (allBtn) allBtn.classList.add('active');

        document.querySelectorAll('.fiqh-pill-btn').forEach(btn => btn.classList.remove('active'));

        executeLiveFilter();
    }

    function setFiqhScholar(scholar, el) {
        currentScholarFilter = scholar;
        document.querySelectorAll('.fiqh-filter-btn[data-filter-type="scholar"]').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');
        executeLiveFilter();
    }

    function setFiqhTopic(topic, el) {
        currentTopicFilter = topic;
        document.querySelectorAll('.fiqh-filter-btn[data-filter-type="topic"]').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');
        executeLiveFilter();
    }

    // Quiet background pre-fetch during browser idle time (Low-Bandwidth Friendly)
    if (typeof window !== 'undefined') {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(() => { ensureFiqhLoaded(); }, { timeout: 4000 });
        } else {
            setTimeout(() => { ensureFiqhLoaded(); }, 2500);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (document.getElementById('fiqh_fatwas_grid')) {
            initFiqhEngine();
        }
    });
    </script>
</body>
</html>
