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
    <script src="js/inheritance_engine.js?v=2026_v6_evidence_1788628663636"></script>

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

                .fiqh-top-nav-bar {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.98), rgba(2, 6, 23, 0.98));
            border: 2px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 14px 18px;
            box-shadow: 0 12px 35px -10px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .fiqh-nav-pill {
            padding: 11px 20px;
            border-radius: 14px;
            font-size: 0.96rem;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 9px;
            user-select: none;
            line-height: 1.4;
            border-width: 2px;
            border-style: solid;
        }

        .fiqh-pill-icon {
            font-size: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.5));
        }

        /* 1. Fatwas: Luxury Gold / Amber */
        .fiqh-pill-fatwas {
            background: rgba(245, 158, 11, 0.14);
            border-color: rgba(245, 158, 11, 0.55);
            color: #fef08a;
        }
        .fiqh-pill-fatwas:hover {
            background: rgba(245, 158, 11, 0.26);
            border-color: #fbbf24;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.45);
            transform: translateY(-1px);
        }
        .fiqh-pill-fatwas.active {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border-color: #fef08a !important;
            color: #020617 !important;
            box-shadow: 0 6px 25px rgba(245, 158, 11, 0.65) !important;
            transform: translateY(-2px);
        }

        /* 2. Quran: Noble Quranic Emerald Green */
        .fiqh-pill-quran {
            background: rgba(16, 185, 129, 0.14);
            border-color: rgba(16, 185, 129, 0.55);
            color: #a7f3d0;
        }
        .fiqh-pill-quran:hover {
            background: rgba(16, 185, 129, 0.26);
            border-color: #34d399;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.45);
            transform: translateY(-1px);
        }
        .fiqh-pill-quran.active {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            border-color: #ecfdf5 !important;
            color: #020617 !important;
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.65) !important;
            transform: translateY(-2px);
        }

        /* 3. Hadiths: Prophetic Sky Blue / Radiant Cyan */
        .fiqh-pill-hadiths {
            background: rgba(14, 165, 233, 0.14);
            border-color: rgba(14, 165, 233, 0.55);
            color: #bae6fd;
        }
        .fiqh-pill-hadiths:hover {
            background: rgba(14, 165, 233, 0.26);
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.45);
            transform: translateY(-1px);
        }
        .fiqh-pill-hadiths.active {
            background: linear-gradient(135deg, #0284c7, #0369a1) !important;
            border-color: #f0f9ff !important;
            color: #ffffff !important;
            box-shadow: 0 6px 25px rgba(14, 165, 233, 0.65) !important;
            transform: translateY(-2px);
        }

        /* 4. Books: Grand Classical Purple / Violet */
        .fiqh-pill-books {
            background: rgba(168, 85, 247, 0.14);
            border-color: rgba(168, 85, 247, 0.55);
            color: #e9d5ff;
        }
        .fiqh-pill-books:hover {
            background: rgba(168, 85, 247, 0.26);
            border-color: #c084fc;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.45);
            transform: translateY(-1px);
        }
        .fiqh-pill-books.active {
            background: linear-gradient(135deg, #9333ea, #7e22ce) !important;
            border-color: #faf5ff !important;
            color: #ffffff !important;
            box-shadow: 0 6px 25px rgba(168, 85, 247, 0.65) !important;
            transform: translateY(-2px);
        }

        /* 5. Maxims: Legal Deep Teal / Aqua */
        .fiqh-pill-maxims {
            background: rgba(20, 184, 166, 0.14);
            border-color: rgba(20, 184, 166, 0.55);
            color: #99f6e4;
        }
        .fiqh-pill-maxims:hover {
            background: rgba(20, 184, 166, 0.26);
            border-color: #2dd4bf;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(20, 184, 166, 0.45);
            transform: translateY(-1px);
        }
        .fiqh-pill-maxims.active {
            background: linear-gradient(135deg, #0d9488, #0f766e) !important;
            border-color: #f0fdfa !important;
            color: #ffffff !important;
            box-shadow: 0 6px 25px rgba(20, 184, 166, 0.65) !important;
            transform: translateY(-2px);
        }

        /* 6. Terms: Vibrant Economic Amber-Orange */
        .fiqh-pill-terms {
            background: rgba(249, 115, 22, 0.14);
            border-color: rgba(249, 115, 22, 0.55);
            color: #fed7aa;
        }
        .fiqh-pill-terms:hover {
            background: rgba(249, 115, 22, 0.26);
            border-color: #fb923c;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.45);
            transform: translateY(-1px);
        }
        .fiqh-pill-terms.active {
            background: linear-gradient(135deg, #ea580c, #c2410c) !important;
            border-color: #fff7ed !important;
            color: #ffffff !important;
            box-shadow: 0 6px 25px rgba(249, 115, 22, 0.65) !important;
            transform: translateY(-2px);
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
           UNIFIED MASTER CARD: المسائل وفتاوى المعاملات المالية الأكثر شيوعاً
           ══════════════════════════════════════════════════════════════════════ */
        .fiqh-unified-master-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.98) 0%, rgba(10, 15, 28, 0.99) 100%);
            border: 2px solid rgba(251, 191, 36, 0.45);
            border-radius: 28px;
            padding: 26px;
            box-shadow: 
                0 20px 50px -8px rgba(0, 0, 0, 0.8), 
                0 0 30px -4px rgba(245, 158, 11, 0.2), 
                inset 0 1px 2px rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(16px);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .fiqh-unified-master-card:hover {
            border-color: rgba(251, 191, 36, 0.7);
            box-shadow: 
                0 24px 60px -8px rgba(0, 0, 0, 0.9), 
                0 0 38px -2px rgba(245, 158, 11, 0.3);
        }

        .fiqh-unified-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid rgba(251, 191, 36, 0.25);
            padding-bottom: 18px;
            margin-bottom: 22px;
        }

        .fiqh-header-badge {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.15));
            border: 2px solid rgba(251, 191, 36, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fbbf24;
            font-size: 1.4rem;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.25);
        }

        .fiqh-unified-title {
            font-size: 1.55rem; /* زيادة الخط درجة واحدة للعنوان */
            font-weight: 900;
            color: #fef08a;
            letter-spacing: -0.01em;
            margin: 0;
            line-height: 1.3;
        }

        .fiqh-reset-btn {
            padding: 9px 20px;
            border-radius: 14px;
            background: rgba(244, 63, 94, 0.15);
            border: 1.5px solid rgba(244, 63, 94, 0.45);
            color: #fda4af;
            font-size: 0.94rem; /* خط أوضح وأكبر درجة */
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        .fiqh-reset-btn:hover {
            background: rgba(244, 63, 94, 0.35);
            border-color: #f43f5e;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(244, 63, 94, 0.4);
        }

        .fiqh-categories-merged-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 900px) {
            .fiqh-categories-merged-grid {
                grid-template-columns: 1fr 1fr;
                gap: 22px;
            }
        }

        .fiqh-cat-subgroup {
            background: rgba(11, 17, 32, 0.85);
            border-radius: 22px;
            padding: 20px;
            border: 1.5px solid rgba(255, 255, 255, 0.09);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 16px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fiqh-cat-cyan {
            border-color: rgba(6, 182, 212, 0.4);
            background: linear-gradient(145deg, rgba(8, 28, 48, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
        }
        .fiqh-cat-cyan:hover {
            border-color: rgba(6, 182, 212, 0.7);
            box-shadow: 0 10px 28px rgba(6, 182, 212, 0.2);
        }

        .fiqh-cat-emerald {
            border-color: rgba(16, 185, 129, 0.4);
            background: linear-gradient(145deg, rgba(6, 36, 26, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
        }
        .fiqh-cat-emerald:hover {
            border-color: rgba(16, 185, 129, 0.7);
            box-shadow: 0 10px 28px rgba(16, 185, 129, 0.2);
        }

        .fiqh-cat-amber {
            border-color: rgba(245, 158, 11, 0.4);
            background: linear-gradient(145deg, rgba(42, 28, 10, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
        }
        .fiqh-cat-amber:hover {
            border-color: rgba(245, 158, 11, 0.7);
            box-shadow: 0 10px 28px rgba(245, 158, 11, 0.2);
        }

        .fiqh-cat-purple {
            border-color: rgba(168, 85, 247, 0.4);
            background: linear-gradient(145deg, rgba(34, 16, 50, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
        }
        .fiqh-cat-purple:hover {
            border-color: rgba(168, 85, 247, 0.7);
            box-shadow: 0 10px 28px rgba(168, 85, 247, 0.2);
        }

        /* Sub-box 5 Full-Width Horizontal 2-Row Layout */
        .fiqh-cat-subgroup.fiqh-box5-full {
            grid-column: 1 / -1 !important;
            width: 100% !important;
        }

        .fiqh-box5-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            width: 100%;
        }

        .fiqh-box5-grid .fiqh-pill-btn {
            flex: none !important;
            min-width: 0 !important;
            width: 100% !important;
            min-height: 52px !important;
            padding: 8px 10px !important;
            font-size: 0.84rem !important;
            font-weight: 800 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            gap: 6px !important;
            white-space: normal !important;
            line-height: 1.3 !important;
            border-radius: 13px !important;
        }

        @media (max-width: 1199px) {
            .fiqh-box5-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .fiqh-box5-grid .fiqh-pill-btn {
                font-size: 0.88rem !important;
                padding: 10px 12px !important;
            }
        }

        @media (max-width: 640px) {
            .fiqh-box5-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .fiqh-cat-indigo {
            border-color: rgba(99, 102, 241, 0.4);
            background: linear-gradient(145deg, rgba(24, 20, 52, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
        }
        .fiqh-cat-indigo:hover {
            border-color: rgba(99, 102, 241, 0.7);
            box-shadow: 0 10px 28px rgba(99, 102, 241, 0.2);
        }

        .fiqh-cat-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 12px;
        }

        .fiqh-cat-tag {
            font-size: 1.15rem; /* زيادة الخط درجة واحدة للترويسة */
            font-weight: 900;
            color: #f8fafc;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .fiqh-cat-count {
            font-size: 0.82rem; /* زيادة الخط درجة واحدة للعداد */
            font-weight: 800;
            font-family: inherit;
            padding: 4px 12px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.5);
            border-width: 1px;
            border-style: solid;
        }

        /* 🎯 ملء الفراغات وتوازن الأزرار بدقة هندسية عالية */
        .fiqh-cat-pills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
        }

        /* The Pills */
        .fiqh-pill-btn {
            flex: 1 1 calc(50% - 10px); /* يملأ الفراغ بالتساوي في كل صف 2 في كل سطر */
            min-width: 220px;
            padding: 11px 16px; /* زيادة مساحة الخط والزر */
            border-radius: 14px;
            font-size: 0.96rem; /* زيادة الخط درجة واحدة كاملة */
            font-weight: 800;
            background: rgba(15, 23, 42, 0.92);
            color: #f1f5f9;
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 9px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.35);
            user-select: none;
            line-height: 1.35;
        }

        .fiqh-cat-indigo .fiqh-pill-btn:not(.fiqh-master-full-pill) {
            flex: 1 1 calc(25% - 10px); /* 4 في كل صف في المربع الخامس العريض لملء كامل الفراغ */
            min-width: 210px;
        }

        .fiqh-pill-btn:hover {
            transform: translateY(-2px) scale(1.015);
        }

        .fiqh-pill-cyan:hover, .fiqh-pill-cyan.active {
            border-color: #22d3ee !important;
            color: #67e8f9 !important;
            background: rgba(8, 51, 68, 0.95) !important;
            box-shadow: 0 6px 18px rgba(6, 182, 212, 0.45) !important;
        }

        .fiqh-pill-emerald:hover, .fiqh-pill-emerald.active {
            border-color: #34d399 !important;
            color: #6ee7b7 !important;
            background: rgba(6, 78, 59, 0.95) !important;
            box-shadow: 0 6px 18px rgba(168, 243, 208, 0.45) !important;
        }

        .fiqh-pill-amber:hover, .fiqh-pill-amber.active {
            border-color: #fbbf24 !important;
            color: #fef08a !important;
            background: rgba(69, 26, 3, 0.95) !important;
            box-shadow: 0 6px 18px rgba(245, 158, 11, 0.45) !important;
        }

        .fiqh-pill-purple:hover, .fiqh-pill-purple.active {
            border-color: #c084fc !important;
            color: #e9d5ff !important;
            background: rgba(59, 7, 100, 0.95) !important;
            box-shadow: 0 6px 18px rgba(168, 85, 247, 0.45) !important;
        }

        .fiqh-pill-indigo:hover, .fiqh-pill-indigo.active {
            border-color: #818cf8 !important;
            color: #c7d2fe !important;
            background: rgba(49, 46, 129, 0.95) !important;
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45) !important;
        }

        /* Master full-width gold pill in box 5 */
        .fiqh-master-full-pill {
            flex: 1 1 100% !important;
            width: 100%;
            padding: 13px 20px !important;
            font-size: 1.06rem !important; /* خط أكبر للزر الرئيسي */
            font-weight: 900 !important;
            border: 2px solid #fbbf24 !important;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.28) 0%, rgba(180, 83, 9, 0.22) 100%) !important;
            color: #fef08a !important;
            justify-content: center !important;
            box-shadow: 0 4px 18px rgba(245, 158, 11, 0.3) !important;
        }

        .fiqh-master-full-pill:hover, .fiqh-master-full-pill.active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.45) 0%, rgba(217, 119, 6, 0.35) 100%) !important;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.45), 0 0 16px rgba(251, 191, 36, 0.4) !important;
            transform: translateY(-2px) !important;
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


        /* ═════════════════════════════════════════════════════════════════════════ */
        /* 🌟 بطاقات الفتاوى العصرية: حواف دائرية فخمة وهوامش داخلية واسعة ومريحة 🌟 */
        /* ═════════════════════════════════════════════════════════════════════════ */
        .fiqh-fatwa-card {
            border-radius: 36px !important;
            padding: 2.25rem 2.5rem !important; /* هوامش واسعة جداً لمنع أي التصاق بالنصوص */
            transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1) !important;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(2, 6, 23, 0.98) 100%) !important;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5) !important;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-fatwa-card {
                padding: 1.5rem 1.25rem !important;
            }
        }
        .fiqh-fatwa-card:hover {
            transform: translateY(-6px);
            border-color: rgba(245, 158, 11, 0.75) !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65), 0 0 35px rgba(245, 158, 11, 0.2) !important;
        }

        /* ═════════════════════════════════════════════════════════════════════════ */
        /* 🌟 شريط أزرار تصفية الأحكام الشرعية التكليفية والتحكم بالخطوط 🌟 */
        /* ═════════════════════════════════════════════════════════════════════════ */
        .fiqh-ruling-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 800;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            border-width: 2px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            background-color: rgba(15, 23, 42, 0.85);
            color: #cbd5e1;
            border-color: rgba(51, 65, 85, 0.8);
            user-select: none;
        }
        .fiqh-ruling-btn:hover {
            transform: translateY(-2px);
            color: #f8fafc;
            border-color: rgba(245, 158, 11, 0.6);
        }
        .fiqh-ruling-btn.active.ruling-all {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #020617;
            border-color: #fbbf24;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.45);
        }
        .fiqh-ruling-btn.active.ruling-halal {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            border-color: #34d399;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.45);
        }
        .fiqh-ruling-btn.active.ruling-haram {
            background: linear-gradient(135deg, #f43f5e, #e11d48);
            color: #ffffff;
            border-color: #fb7185;
            box-shadow: 0 4px 15px rgba(244, 63, 94, 0.45);
        }
        .fiqh-ruling-btn.active.ruling-conditional {
            background: linear-gradient(135deg, #f59e0b, #b45309);
            color: #ffffff;
            border-color: #fcd34d;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.45);
        }
        .fiqh-ruling-btn.active.ruling-wajib {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: #ffffff;
            border-color: #67e8f9;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.45);
        }

        /* 🌟 تظليل الكلمات المبحوث عنها بلون ذهبي مشع 🌟 */
        .fiqh-highlight {
            background-color: rgba(245, 158, 11, 0.4) !important;
            color: #fef08a !important;
            padding: 0.1rem 0.4rem !important;
            border-radius: 6px !important;
            border: 1px solid rgba(251, 191, 36, 0.75) !important;
            font-weight: 900 !important;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.35) !important;
        }

        /* 🌟 إشعار النسخ المنبثق التفاعلي Toast 🌟 */
        #fiqh-toast {
            position: fixed;
            bottom: 2.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(120px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 999999;
        }
        #fiqh-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }


        /* 🌟 مربع الحديث والأثر: هوامش مريحة تمنع الالتصاق بالحواف، يزيد حجمه ويتحرك بسلاسة 🌟 */
        .fiqh-hadith-card {
            border-radius: 28px !important;
            padding: 1.75rem 2rem !important; /* هوامش مريحة ومتباعدة عن الحافة أكثر من 5 ملم */
            margin: 1.5rem 0 !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, background 0.35s ease, border-color 0.35s ease !important;
            will-change: transform, box-shadow;
            cursor: pointer;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-hadith-card {
                padding: 1.25rem 1.25rem !important;
            }
        }
        .fiqh-hadith-card:hover {
            transform: translateY(-8px) scale(1.035) !important;
            box-shadow: 0 22px 48px rgba(245, 158, 11, 0.38), 0 0 30px rgba(245, 158, 11, 0.25) !important;
            border-color: rgba(251, 191, 36, 0.95) !important;
            background: linear-gradient(135deg, rgba(69, 26, 3, 0.95) 0%, rgba(15, 23, 42, 0.98) 50%, rgba(69, 26, 3, 0.85) 100%) !important;
        }
        .fiqh-hadith-card:hover .hadith-icon-bubble {
            transform: scale(1.22) rotate(8deg) !important;
            background-color: rgba(245, 158, 11, 0.35) !important;
            border-color: rgba(251, 191, 36, 0.9) !important;
        }
        .hadith-icon-bubble {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        /* 🌟 صندوق نص الجواب والفتوى: هوامش مريحة ورحبة تمنع التكدس وتمنح النص مساحة كافية 🌟 */
        .fiqh-summary-box {
            border-radius: 26px !important;
            padding: 1.75rem 2rem !important; /* هوامش متسعة ومريحة تبعد النص عن الحواف */
            line-height: 2.1 !important;
            text-align: justify;
            text-justify: inter-word;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-summary-box {
                padding: 1.25rem 1.25rem !important;
            }
        }

        /* 🌟 بطاقات القواعد الفقهية الثلاثين: هوامش مريحة وفخمة 🌟 */
        .fiqh-maxim-card {
            border-radius: 36px !important;
            padding: 2.25rem 2.5rem !important;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-maxim-card {
                padding: 1.5rem 1.25rem !important;
            }
        }
.hadith-icon-bubble {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }


        /* ═════════════════════════════════════════════════════════════════════════ */
        /* 🌟 بطاقات الدستور القرآني: تصميم فخم مطابق لموسوعة الفتاوى بحواف وهوامش واسعة 🌟 */
        /* ═════════════════════════════════════════════════════════════════════════ */
        .fiqh-ayah-card {
            border-radius: 36px !important;
            padding: 2.25rem 2.5rem !important; /* هوامش واسعة جداً تمنع أي التصاق بالنصوص */
            transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1) !important;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(2, 6, 23, 0.98) 100%) !important;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5) !important;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-ayah-card {
                padding: 1.5rem 1.25rem !important;
            }
        }
        .fiqh-ayah-card:hover {
            transform: translateY(-6px);
            border-color: rgba(52, 211, 153, 0.8) !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65), 0 0 35px rgba(16, 185, 129, 0.22) !important;
        }

        /* 🌟 مستطيل الآية الكريمة: هوامش واسعة، يزيد حجمه ويتحرك للأعلى بمرونة عند مرور المؤشر لزيادة التركيز 🌟 */
        .fiqh-quran-verse-box {
            border-radius: 28px !important;
            padding: 1.75rem 2rem !important; /* هوامش مريحة ومتباعدة عن الحافة */
            margin: 1.5rem 0 !important;
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.45) 0%, rgba(15, 23, 42, 0.96) 50%, rgba(6, 78, 59, 0.35) 100%) !important;
            border: 2px solid rgba(16, 185, 129, 0.45) !important;
            border-right: 6px solid #10b981 !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, background 0.35s ease, border-color 0.35s ease !important;
            will-change: transform, box-shadow;
            cursor: pointer;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-quran-verse-box {
                padding: 1.25rem 1.25rem !important;
            }
        }
        .fiqh-quran-verse-box:hover {
            transform: translateY(-8px) scale(1.035) !important;
            box-shadow: 0 22px 48px rgba(16, 185, 129, 0.38), 0 0 30px rgba(245, 158, 11, 0.25) !important;
            border-color: #34d399 !important;
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.7) 0%, rgba(15, 23, 42, 0.98) 50%, rgba(6, 78, 59, 0.55) 100%) !important;
        }
        .fiqh-quran-verse-box:hover .hadith-icon-bubble {
            transform: scale(1.22) rotate(8deg) !important;
            background-color: rgba(16, 185, 129, 0.35) !important;
            border-color: #34d399 !important;
        }

        /* 🌟 صندوق الأحكام والتوجيهات الاقتصادية المستنبطة: هوامش متسعة ومريحة وخطوط واضحة 🌟 */
        .fiqh-quran-guidance-box {
            border-radius: 26px !important;
            padding: 1.75rem 2rem !important;
            line-height: 2.1 !important;
            background: rgba(15, 23, 42, 0.9) !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            text-align: justify;
            text-justify: inter-word;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-quran-guidance-box {
                padding: 1.25rem 1.25rem !important;
            }
        }


        /* 🌟 مستطيل نص الحديث النبوي الشريف في الموسوعة النبوية: هوامش واسعة ويكبر ويتحرك لزيادة التركيز 🌟 */
        .fiqh-prophetic-matn-box {
            border-radius: 28px !important;
            padding: 1.75rem 2rem !important;
            margin: 1.5rem 0 !important;
            background: linear-gradient(135deg, rgba(69, 26, 3, 0.45) 0%, rgba(15, 23, 42, 0.96) 50%, rgba(69, 26, 3, 0.35) 100%) !important;
            border: 2px solid rgba(245, 158, 11, 0.45) !important;
            border-right: 6px solid #f59e0b !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, background 0.35s ease, border-color 0.35s ease !important;
            will-change: transform, box-shadow;
            cursor: pointer;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-prophetic-matn-box {
                padding: 1.25rem 1.25rem !important;
            }
        }
        .fiqh-prophetic-matn-box:hover {
            transform: translateY(-8px) scale(1.035) !important;
            box-shadow: 0 22px 48px rgba(245, 158, 11, 0.4), 0 0 30px rgba(245, 158, 11, 0.25) !important;
            border-color: #fbbf24 !important;
            background: linear-gradient(135deg, rgba(69, 26, 3, 0.8) 0%, rgba(15, 23, 42, 0.98) 50%, rgba(69, 26, 3, 0.65) 100%) !important;
        }
        .fiqh-prophetic-matn-box:hover .hadith-icon-bubble {
            transform: scale(1.22) rotate(8deg) !important;
            background-color: rgba(245, 158, 11, 0.35) !important;
            border-color: #fbbf24 !important;
        }


        /* 🌟 مستطيل الأصل والنص الشرعي المؤسس للقاعدة الفقهية: هوامش واسعة ويكبر ويتحرك لزيادة التركيز 🌟 */
        .fiqh-maxim-evidence-box {
            border-radius: 28px !important;
            padding: 1.75rem 2rem !important;
            margin: 1.5rem 0 !important;
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.45) 0%, rgba(15, 23, 42, 0.96) 50%, rgba(6, 78, 59, 0.35) 100%) !important;
            border: 2px solid rgba(16, 185, 129, 0.45) !important;
            border-right: 6px solid #10b981 !important;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, background 0.35s ease, border-color 0.35s ease !important;
            will-change: transform, box-shadow;
            cursor: pointer;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-maxim-evidence-box {
                padding: 1.25rem 1.25rem !important;
            }
        }
        .fiqh-maxim-evidence-box:hover {
            transform: translateY(-8px) scale(1.035) !important;
            box-shadow: 0 22px 48px rgba(16, 185, 129, 0.4), 0 0 30px rgba(245, 158, 11, 0.25) !important;
            border-color: #34d399 !important;
            background: linear-gradient(135deg, rgba(6, 78, 59, 0.75) 0%, rgba(15, 23, 42, 0.98) 50%, rgba(6, 78, 59, 0.6) 100%) !important;
        }
        .fiqh-maxim-evidence-box:hover .hadith-icon-bubble {
            transform: scale(1.22) rotate(8deg) !important;
            background-color: rgba(16, 185, 129, 0.35) !important;
            border-color: #34d399 !important;
        }

        /* 🌟 صندوق المعنى الفقهي والضابط الاقتصادي للقاعدة الفقهية: هوامش رحبة 🌟 */
        .fiqh-maxim-explanation-box {
            border-radius: 26px !important;
            padding: 1.75rem 2rem !important;
            line-height: 2.1 !important;
            background: rgba(15, 23, 42, 0.9) !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            text-align: justify;
            text-justify: inter-word;
            box-sizing: border-box !important;
        }
        @media (max-width: 640px) {
            .fiqh-maxim-explanation-box {
                padding: 1.25rem 1.25rem !important;
            }
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
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-11 gap-2.5" id="suite-tabs-container">
            
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

            <!-- 8. الميراث والفرائض الشرعية (منفصلة ومميزة) -->
            <div class="suite-pill-card" style="--p-color:#10b981; --p-border:rgba(16,185,129,0.6); background:linear-gradient(180deg, rgba(16,185,129,0.06), rgba(6,78,59,0.15));" onclick="switchSuite('inheritance', this)">
                <div class="suite-pill-icon"><i class="fa-solid fa-scale-balanced text-emerald-400 text-xl"></i></div>
                <div class="suite-pill-title text-emerald-300">⚖️ الميراث الشرعي</div>
                <div class="suite-pill-sub text-emerald-400/80">قسمة وحساب التركات</div>
            </div>

            <!-- 9. حاسبة الزكاة -->
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
    </div> <!-- /tools-master-wrapper -->

<!-- INHERITANCE EVIDENCE GRAND MODAL CONTAINER (ROOT LEVEL VIEWPORT) -->
<!-- ════════════════════════════════════════════════════════════════ -->
<div id="inh-evidence-modal" style="display: none; position: fixed; inset: 0; z-index: 99999999; align-items: center; justify-content: center; padding: 1rem; background-color: rgba(3, 7, 18, 0.94); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);"></div>

</body>
</html>
