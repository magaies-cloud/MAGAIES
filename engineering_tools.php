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

                <!-- 🌟 MASTER NAVIGATION BAR: قائمة أقسام المنظومة الـ 6 المعتمدة بألوان فائقة الوضوح والجمال 🌟 -->
                <div class="fiqh-top-nav-bar mb-6">
                    <button type="button" class="fiqh-nav-pill fiqh-pill-fatwas active" onclick="switchFiqhSubTab('fatwas', this)">
                        <span class="fiqh-pill-icon">📑</span>
                        <span>موسوعة الفتاوى والأحكام (4,500 مسألة محررة لكبار الأئمة)</span>
                    </button>
                    <button type="button" class="fiqh-nav-pill fiqh-pill-quran" onclick="switchFiqhSubTab('quran', this)">
                        <span class="fiqh-pill-icon">📖</span>
                        <span>الدستور القرآني للمعاملات والمال (38 آية محكمة)</span>
                    </button>
                    <button type="button" class="fiqh-nav-pill fiqh-pill-hadiths" onclick="switchFiqhSubTab('hadiths', this)">
                        <span class="fiqh-pill-icon">📜</span>
                        <span>الموسوعة النبوية الصحيحة (100 حديث صحيح محكم)</span>
                    </button>
                    <button type="button" class="fiqh-nav-pill fiqh-pill-books" onclick="switchFiqhSubTab('books', this)">
                        <span class="fiqh-pill-icon">📚</span>
                        <span>مكتبة أمهات كتب البيوع (26 مؤلفاً وموسوعة)</span>
                    </button>
                    <button type="button" class="fiqh-nav-pill fiqh-pill-maxims" onclick="switchFiqhSubTab('maxims', this)">
                        <span class="fiqh-pill-icon">⚖️</span>
                        <span>قواعد الفقه الكلية الحاكمة للتجارة (30 قاعدة كبرى من القرآن والسنة)</span>
                    </button>
                    <button type="button" class="fiqh-nav-pill fiqh-pill-terms" onclick="switchFiqhSubTab('terms', this)">
                        <span class="fiqh-pill-icon">💡</span>
                        <span>معجم المصطلحات الاقتصادية (قاموس فقهي شامل)</span>
                    </button>
                </div>
<!-- ════════════════════════════════════════════════════════════ -->
                <!-- SUB-SECTION 1: FATWAS & RULINGS SUITE (الموسوعة الكبرى)       -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div id="fiqh-sub-fatwas" class="space-y-6">
                    
                    <!-- 🌟 شريط وصول مباشر فوري للدستور القرآني (38 آية محكمة) 🌟 -->
                    <div class="p-4 bg-gradient-to-r from-emerald-950/70 via-slate-900 to-emerald-950/50 border-2 border-emerald-500/60 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-xl cursor-pointer hover:border-emerald-400 transition transform hover:-translate-y-0.5" onclick="switchFiqhSubTab('quran')">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-2xl border border-emerald-400/50 shadow-inner flex-shrink-0">
                                📖
                            </span>
                            <div>
                                <h4 class="text-sm md:text-base font-black text-emerald-300 flex flex-wrap items-center gap-2">
                                    <span>الموسوعة القرآنية الشاملة للمعاملات المالية والديون والمواريث</span>
                                    <span class="text-xs font-black px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 rounded-full border border-emerald-400/40">38 آية محكمة ✓</span>
                                </h4>
                                <p class="text-xs text-slate-300 font-bold mt-1">
                                    تضم كامل الآيات في: البيوع، المداينة والرهن، تحريم الربا والرشوة، مصارف الزكاة، المواريث والتركات، الإجارة والشركات، أموال اليتامى، وكنز الأموال.
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="switchFiqhSubTab('quran')" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs md:text-sm rounded-xl shadow-lg transition flex items-center gap-2 flex-shrink-0">
                            <span>افتح الآيات الـ 38 الآن</span>
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                    </div>
                    
                    <!-- 3. UNIFIED MASTER BOX: فتاوى المعاملات والمسائل المالية الأكثر شيوعاً في مربع واحد مدمج وأنيق وموزون -->
                <div class="fiqh-unified-master-card mb-8">
                    
                    <!-- شريط عنوان المربع الموحد -->
                    <div class="fiqh-unified-header">
                        <div class="flex items-center gap-3.5">
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

                    <!-- شبكة المربعات الخمسة المدمجة ومرصوصة رصاً متناسقاً وموزوناً لملء الفراغات -->
                    <div class="fiqh-categories-merged-grid">
                        
                        <!-- 1. التمويل والتقسيط والديون (6 مسائل موزونة) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-cyan">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-credit-card text-cyan-400"></i>
                                    1. التمويل والتقسيط والديون
                                </span>
                                <span class="fiqh-cat-count text-cyan-400 border-cyan-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="filterByFiqhButton('installment', this)">
                                    <span>💳</span> البيع بالتقسيط وزيادة الأجل
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="filterByFiqhButton('penalties', this)">
                                    <span>⚠️</span> غرامات التأخير والشرط الجزائي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="filterByFiqhButton('murabaha', this)">
                                    <span>🏢</span> المرابحة للآمر بالشراء
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="filterByFiqhButton('tawarruq', this)">
                                    <span>🔄</span> التورق للحصول على سيولة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="filterByFiqhButton('leasing', this)">
                                    <span>🚗</span> الإيجار المنتهي بالتمليك
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-cyan" onclick="filterByFiqhButton('insolvency', this)">
                                    <span>⏳</span> إنظار المعسر والتيسير في السداد
                                </button>
                            </div>
                        </div>

                        <!-- 2. مقاولات السلع والضمان (6 مسائل موزونة) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-purple">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-trowel-bricks text-purple-400"></i>
                                    2. مقاولات السلع والضمان
                                </span>
                                <span class="fiqh-cat-count text-purple-400 border-purple-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="filterByFiqhButton('unowned_sale', this)">
                                    <span>🛒</span> بيع ما لا يملك والوساطة التجارية
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="filterByFiqhButton('istisna_supply', this)">
                                    <span>🏗️</span> عقد الاستصناع وتوريد البضائع
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="filterByFiqhButton('scrap', this)">
                                    <span>🔩</span> بيع حديد السكراب والخردة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="filterByFiqhButton('defects', this)">
                                    <span>🛡️</span> خيار العيب والضمان المصنعي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="filterByFiqhButton('workers_wages', this)">
                                    <span>👥</span> أجور العمال وحقوق الأجراء
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-purple" onclick="filterByFiqhButton('pricing', this)">
                                    <span>⚖️</span> التسعير وتطفيف الكيل والميزان
                                </button>
                            </div>
                        </div>

                        <!-- 3. الذهب والعملات والتحويلات (6 مسائل موزونة) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-amber">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-coins text-amber-400"></i>
                                    3. الذهب والعملات والتحويلات
                                </span>
                                <span class="fiqh-cat-count text-amber-400 border-amber-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="filterByFiqhButton('gold_mada', this)">
                                    <span>💎</span> شراء الذهب ببطاقات الصراف ومدى
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="filterByFiqhButton('bankak_fawry', this)">
                                    <span>📱</span> الصرف بتطبيقات بنكك وفوري
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="filterByFiqhButton('gold_exchange', this)">
                                    <span>🔄</span> بيع الذهب القديم بالجديد والمصنعية
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="filterByFiqhButton('immediate_possession', this)">
                                    <span>🤝</span> التقابض الفوري في مجلس عقد الصرف
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="filterByFiqhButton('riba_inah', this)">
                                    <span>🚫</span> حيل الربا وبيوع العِينة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-amber" onclick="filterByFiqhButton('crypto_forex', this)">
                                    <span>🪙</span> العملات الرقمية والفوركس
                                </button>
                            </div>
                        </div>

                        <!-- 4. الأوراق التجارية والمدفوعات (6 مسائل موزونة) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-emerald">
                            <div class="fiqh-cat-title-bar">
                                <span class="fiqh-cat-tag">
                                    <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                                    4. الأوراق التجارية والمدفوعات
                                </span>
                                <span class="fiqh-cat-count text-emerald-400 border-emerald-500/30">6 مسائل</span>
                            </div>
                            <div class="fiqh-cat-pills-wrap">
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="filterByFiqhButton('checks', this)">
                                    <span>📜</span> خصم الشيكات والكمبيالات الآجلة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="filterByFiqhButton('earnest_money', this)">
                                    <span>🤝</span> بيع العربون وحجز البضاعة
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="filterByFiqhButton('debt_sale', this)">
                                    <span>💼</span> بيع الدين والمقاصة المالية
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="filterByFiqhButton('iqala', this)">
                                    <span>🔄</span> إقالة النادم والفسخ الرضائي
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="filterByFiqhButton('salam', this)">
                                    <span>📦</span> بيع السلم ودفع الثمن مقدماً
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-emerald" onclick="filterByFiqhButton('contracts_terms', this)">
                                    <span>📋</span> العقود والشروط وحرية الاتفاق
                                </button>
                            </div>
                        </div>

                        <!-- 5. جميع مسائل المعاملات المالية (الموسوعة الشاملة ممتدة أفقياً على كامل العرض بسطرين موزونين 6 + 6) -->
                        <div class="fiqh-cat-subgroup fiqh-cat-indigo fiqh-box5-full">
                            
                            <!-- شريط عنوان المربع الخامس مع زر الموسوعة الشاملة الفاخر -->
                            <div class="fiqh-cat-title-bar flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="fiqh-cat-tag">
                                        <i class="fa-solid fa-scale-balanced text-indigo-400"></i>
                                        5. جميع مسائل المعاملات المالية
                                    </span>
                                    <span class="fiqh-cat-count text-indigo-400 border-indigo-500/30">الموسوعة الشاملة (4,500 فتوى موثقة)</span>
                                </div>
                                <button type="button" class="fiqh-pill-btn fiqh-master-full-pill active" onclick="showAllFiqhTransactions(this)" id="btn-show-all-fiqh" style="flex: 0 0 auto !important; min-width: auto !important; padding: 8px 18px !important; font-size: 0.88rem !important;">
                                    <span>🌟</span> عرض كافة المسائل والمعاملات (الموسوعة الكاملة لكافة الأبواب)
                                </button>
                            </div>

                            <!-- شبكة المسائل المنسقة أفقياً في سطرين كاملين متناسقين (6 في السطر الأول + 6 في السطر الثاني) -->
                            <div class="fiqh-box5-grid">
                                
                                <!-- ════════════════ السطر الأول (6 مسائل) ════════════════ -->
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('stocks', this)">
                                    <span>🏢</span> <span>الشركات والأسهم والمضاربة</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('ecommerce', this)">
                                    <span>📱</span> <span>التجارة الإلكترونية ونقاط البيع POS</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('insurance', this)">
                                    <span>🛡️</span> <span>التأمين التعاوني والتكافلي</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('guarantee', this)">
                                    <span>🤝</span> <span>الكفالة والضمان الشخصي</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('franchise', this)">
                                    <span>🏷️</span> <span>الامتياز التجاري (الفرنشايز)</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('letters_of_credit', this)">
                                    <span>🚢</span> <span>الاعتمادات والشحن الدولي</span>
                                </button>

                                <!-- ════════════════ السطر الثاني (6 مسائل) ════════════════ -->
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('zakat', this)">
                                    <span>💰</span> <span>الزكاة وعروض التجارة والديون</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('pawn', this)">
                                    <span>🏠</span> <span>الرهن الحيازي والتجاري</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('emergency', this)">
                                    <span>⚡</span> <span>الجوائح والظروف الطارئة</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('bankruptcy', this)">
                                    <span>⚖️</span> <span>التفليس وقسمة الغرماء</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('loyalty_points', this)">
                                    <span>🎁</span> <span>نقاط المكافآت وبطاقات الولاء</span>
                                </button>
                                <button type="button" class="fiqh-pill-btn fiqh-pill-indigo" onclick="filterByFiqhButton('crowdfunding', this)">
                                    <span>👥</span> <span>التمويل الجماعي والاستثماري</span>
                                </button>

                            </div>
                        </div></div>
                </div>

                    
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
                </div>
                    
                

                <div id="fiqh-sub-maxims" class="space-y-4 hidden">
                    <div class="p-5 bg-gradient-to-r from-emerald-950/60 via-slate-900 to-emerald-950/40 border-2 border-emerald-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-4 shadow-xl">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-black text-xs rounded-xl border border-emerald-400">
                                    ★ الدستور الفقهي الكلي للأسواق والتجارة
                                </span>
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400">
                                    30 قاعدة فقهية كبرى من القرآن والسنة الصحيحة 100% ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-emerald-300 flex items-center gap-2">
                                <i class="fa-solid fa-scale-balanced text-emerald-400"></i>
                                قواعد الفقه الكبرى الحاكمة للتجارة والبيوع والمعاملات المالية المعاصرة
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1 leading-relaxed">
                                القواعد والضوابط الكلية المؤسسة مباشرة على <span class="text-amber-300 font-black">نصوص القرآن الكريم المحكمة</span> و<span class="text-emerald-300 font-black">الأحاديث النبوية الصحيحة الثابتة</span> الحاكمة للشركات والمصارف وعقود التجارة.
                            </p>
                        </div>

                        <!-- Instant Maxim Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في القواعد والأصول والتطبيقات:</label>
                            <input type="text" id="maxims-search-input" oninput="filterMaxims()" placeholder="ابحث: العقد، الشروط، الضمان، الغرر، الخراج، براءة الذمة..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Maxims Category Filter Bar (6 تصنيفات فقهية) 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-emerald-500/30 flex flex-wrap items-center gap-2 shadow-lg">
                        <span class="text-xs font-black text-emerald-400 block w-full mb-1">
                            🏷️ تصفية القواعد الفقهية حسب المحور الاقتصادي والتجاري:
                        </span>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn active" data-mcat="all" onclick="setMaximCategory('all', this)">
                            🌟 جميع القواعد الفقهية الكلية (30)
                        </button>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn" data-mcat="contracts" onclick="setMaximCategory('contracts', this)">
                            📜 أصول العقود والوفاء والتراضي (6)
                        </button>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn" data-mcat="liability" onclick="setMaximCategory('liability', this)">
                            ⚖️ موازين الربح والضمان والمخاطرة (5)
                        </button>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn" data-mcat="harm" onclick="setMaximCategory('harm', this)">
                            🛡️ درء المفاسد ونفي الغرر والضرر (5)
                        </button>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn" data-mcat="integrity" onclick="setMaximCategory('integrity', this)">
                            ⚖️ النزاهة وحماية الحقوق المالية (5)
                        </button>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn" data-mcat="market" onclick="setMaximCategory('market', this)">
                            🏪 أخلاقيات السوق وحظر الاحتكار (4)
                        </button>
                        <button type="button" class="maxim-filter-btn fiqh-filter-btn" data-mcat="evidence" onclick="setMaximCategory('evidence', this)">
                            ⚖️ الإثبات واستقرار المعاملات والتيسير (5)
                        </button>
                    </div>

                    <!-- Maxims Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="maxims-grid-container">

        <!-- Maxim Card 1 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="contracts" data-maxim-search="1 «الأَصْلُ فِي الْعُقُودِ وَالشُّرُوطِ الْحِلُّ وَالإِبَاحَةُ» 📖 أصل قرآني ونبوي محكم قوله تعالى: ﴿وَأَحَلَّ اللَّهُ الْبَيْعَ﴾ [البقرة: 275]، وقوله: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ﴾ [المائدة: 1]، وحديث: «المسلمون على شروطهم إلا شرطاً حرم حلالاً أو أحل حراماً» [صحيح الترمذي وأبو داود]. الأصل في كافة المعاملات والأنشطة والعقود والاتفاقيات التجارية التي يبتكرها الناس لمصالحهم هو الصحة والجواز، ولا يحرم عقد إلا بنص شرعي قطعي يحرمه لربا أو غرر أو ظلم أو ميسر. صحة العقود والابتكارات الاستثمارية الحديثة كالتجارة الإلكترونية، الصكوك، التمويل الجماعي، والإجارة المنتهية بالتمليك دون حاجة لنص قديم يسميها بأعيانها. الإمام أحمد، شيخ الإسلام ابن تيمية، ابن القيم، ابن عثيمين الأصل في العقود والشروط الحل والإباحة العقود المستحدثة المعاملات المالية المباحة">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الأَصْلُ فِي الْعُقُودِ وَالشُّرُوطِ الْحِلُّ وَالإِبَاحَةُ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        📜 أصول العقود والتراضي
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 أصل قرآني ونبوي محكم
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَأَحَلَّ اللَّهُ الْبَيْعَ﴾ [البقرة: 275]، وقوله: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ﴾ [المائدة: 1]، وحديث: «المسلمون على شروطهم إلا شرطاً حرم حلالاً أو أحل حراماً» [صحيح الترمذي وأبو داود].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأصل في كافة المعاملات والأنشطة والعقود والاتفاقيات التجارية التي يبتكرها الناس لمصالحهم هو الصحة والجواز، ولا يحرم عقد إلا بنص شرعي قطعي يحرمه لربا أو غرر أو ظلم أو ميسر.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">صحة العقود والابتكارات الاستثمارية الحديثة كالتجارة الإلكترونية، الصكوك، التمويل الجماعي، والإجارة المنتهية بالتمليك دون حاجة لنص قديم يسميها بأعيانها.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الإمام أحمد، شيخ الإسلام ابن تيمية، ابن القيم، ابن عثيمين</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 2 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="contracts" data-maxim-search="2 «أَوْفُوا بِالْعُقُودِ · العَقْدُ شَرِيعَةُ الْمُتَعَاقِدِينَ» 📖 نص قرآني قطعي الدلالة قوله تعالى: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ﴾ [المائدة: 1]. إلزامية العقد ووجوب تنفيذ كافة الالتزامات والتعهدات التعاقدية المتفق عليها بين البائع والمشتري أو الشركاء، وحظر نكث العهود أو الفسخ الانفرادي بغير رضا أو سبب شرعي. إلزامية شروط التسليم، جداول الدفعات المالية، الاعتمادات المستندية، وبوالص الشحن، والقوة القاهرة فقط هي التي تسوغ التعديل. إجماع فقهاء المذاهب الأربعة وكافة المجامع الفقهية المعاصرة أوفوا بالعقود العقد شريعة المتعاقدين الالتزام بالعقود الوفاء بالعهود">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «أَوْفُوا بِالْعُقُودِ · العَقْدُ شَرِيعَةُ الْمُتَعَاقِدِينَ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        📜 أصول العقود والتراضي
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 نص قرآني قطعي الدلالة
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ﴾ [المائدة: 1].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    إلزامية العقد ووجوب تنفيذ كافة الالتزامات والتعهدات التعاقدية المتفق عليها بين البائع والمشتري أو الشركاء، وحظر نكث العهود أو الفسخ الانفرادي بغير رضا أو سبب شرعي.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">إلزامية شروط التسليم، جداول الدفعات المالية، الاعتمادات المستندية، وبوالص الشحن، والقوة القاهرة فقط هي التي تسوغ التعديل.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع فقهاء المذاهب الأربعة وكافة المجامع الفقهية المعاصرة</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 3 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="contracts" data-maxim-search="3 «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ إِلَّا شَرْطاً حَرَّمَ حَلَالاً أَوْ أَحَلَّ حَرَاماً» 📜 نص نبوي صحيح صريح حديث رسول الله ﷺ: «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا، أَوْ أَحَلَّ حَرَامًا» [رواه الترمذي (1352) وصححه، وأبو داود، وصححه الألباني]. حرية التراضي على الشروط الخاصة في البيع والشراء والشركات، وتعتبر ملزمة لكلا الطرفين كأصل العقد تماماً، ما لم تخالف حكماً شرعياً ثابتاً كالاشتراط الربوي. صحة الشروط الجزائية في عقود التوريد والمقاولات (دون الديون)، واشتراط خدمات الصيانة وما بعد البيع والضمانات المصنعية. عمر بن الخطاب، الإمام البخاري، الإمام أحمد، مجمع الفقه الإسلامي الدولي المسلمون على شروطهم الشروط الجزائية التوريد المقاولات حرية الاشتراط">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ إِلَّا شَرْطاً حَرَّمَ حَلَالاً أَوْ أَحَلَّ حَرَاماً»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        📜 أصول العقود والتراضي
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص نبوي صحيح صريح
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا، أَوْ أَحَلَّ حَرَامًا» [رواه الترمذي (1352) وصححه، وأبو داود، وصححه الألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    حرية التراضي على الشروط الخاصة في البيع والشراء والشركات، وتعتبر ملزمة لكلا الطرفين كأصل العقد تماماً، ما لم تخالف حكماً شرعياً ثابتاً كالاشتراط الربوي.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">صحة الشروط الجزائية في عقود التوريد والمقاولات (دون الديون)، واشتراط خدمات الصيانة وما بعد البيع والضمانات المصنعية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">عمر بن الخطاب، الإمام البخاري، الإمام أحمد، مجمع الفقه الإسلامي الدولي</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 4 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="contracts" data-maxim-search="4 «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ · الرِّضَا جَوْهَرُ الْعَقْدِ» 📖 قرآن وحديث صحيح متطابقان قوله تعالى: ﴿إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ﴾ [النساء: 29]، وحديث: «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ» [رواه ابن ماجه (2185) وابن حبان وصححه الألباني]. التراضي التام الخالي من العيوب هو روح العقد وأساس نقل الملكية؛ فإذا شاب الرضا إكراه أو تدليس أو غبن فاحش بطل العقد أو ثبت للمتضرر حق الفسخ. بطلان عقود الإذعان الجائرة، وحق المشتري الإلكتروني في فسخ الشراء عند التغرير بالمواصفات أو الضغط النفسي غير المشروع. جمهور الفقهاء والمحققين، الشافعي، مالك، أبو حنيفة، أحمد إنما البيع عن تراض تراضي الطرفين عيوب الرضا الإكراه التدليس الغبن">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ · الرِّضَا جَوْهَرُ الْعَقْدِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        📜 أصول العقود والتراضي
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 قرآن وحديث صحيح متطابقان
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ﴾ [النساء: 29]، وحديث: «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ» [رواه ابن ماجه (2185) وابن حبان وصححه الألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    التراضي التام الخالي من العيوب هو روح العقد وأساس نقل الملكية؛ فإذا شاب الرضا إكراه أو تدليس أو غبن فاحش بطل العقد أو ثبت للمتضرر حق الفسخ.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">بطلان عقود الإذعان الجائرة، وحق المشتري الإلكتروني في فسخ الشراء عند التغرير بالمواصفات أو الضغط النفسي غير المشروع.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">جمهور الفقهاء والمحققين، الشافعي، مالك، أبو حنيفة، أحمد</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 5 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="contracts" data-maxim-search="5 «الْعِبْرَةُ فِي الْعُقُودِ بِالْمَقَاصِدِ وَالْمَعَانِي لَا بِالأَلْفَاظِ وَالْمَبَانِي» 📜 مأخوذة من حديث الأعمال بالنيات حديث عمر بن الخطاب رضي الله عنه متفق عليه: «إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى» [صحيح البخاري ومسلم]. الحكم على حقيقة المعاملة وجوهرها الاقتصادي لا على التسميات والش كليات المصطنعة؛ فالحيل الربوية والممارسات الاحتيالية لا تصير حلالاً بتسميتها بيوعاً أو مرابحات صورية. إبطال بيوع العِينة والتورق المنظم الصوري الذي لا قصد فيه للسلعة، وتدقيق العمليات المصرفية وفق مقاصدها الجوهرية لا ألفاظها. الإمام مالك، الإمام أحمد، ابن تيمية، ابن القيم، مجلة الأحكام العدلية (مادة 3) العبرة في العقود بالمقاصد والمعاني لا بالألفاظ والمباني الحيل الربوية التورق الصوري النيات">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْعِبْرَةُ فِي الْعُقُودِ بِالْمَقَاصِدِ وَالْمَعَانِي لَا بِالأَلْفَاظِ وَالْمَبَانِي»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        📜 أصول العقود والتراضي
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 مأخوذة من حديث الأعمال بالنيات
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث عمر بن الخطاب رضي الله عنه متفق عليه: «إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى» [صحيح البخاري ومسلم].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الحكم على حقيقة المعاملة وجوهرها الاقتصادي لا على التسميات والش كليات المصطنعة؛ فالحيل الربوية والممارسات الاحتيالية لا تصير حلالاً بتسميتها بيوعاً أو مرابحات صورية.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">إبطال بيوع العِينة والتورق المنظم الصوري الذي لا قصد فيه للسلعة، وتدقيق العمليات المصرفية وفق مقاصدها الجوهرية لا ألفاظها.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الإمام مالك، الإمام أحمد، ابن تيمية، ابن القيم، مجلة الأحكام العدلية (مادة 3)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 6 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="contracts" data-maxim-search="6 «إِعْمَالُ الْكَلامِ أَوْلَى مِنْ إِهْمَالِهِ» 📖 مستنبطة من مقاصد القرآن وأصول البيان قوله تعالى: ﴿وَقُولُوا قَوْلًا سَدِيدًا﴾ [الأحزاب: 70] وقوله: ﴿وَقُولُوا لِلنَّاسِ حُسْنًا﴾ [البقرة: 83]. إذا دار كلام العاقدين بين حمله على معنى مفيد يصحح العقد وبين إلغائه وإهداره، وجب حمله على المعنى الصحيح النافذ احتراماً لإرادة المتعاقدين واستقراراً للسوق. تفسير بنود العقود الغامضة والملحقات الفنية بما يحقق نفاذ العقد واستمراره وحفظ حقوق الطرفين وتفادي فسخ الشراكات القائمة. الحنفية والمالكية والشافعية والحنابلة، مجلة الأحكام العدلية (مادة 60) إعمال الكلام أولى من إهماله تفسير العقود بنود الاتفاقيات استقرار المعاملات">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «إِعْمَالُ الْكَلامِ أَوْلَى مِنْ إِهْمَالِهِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        📜 أصول العقود والتراضي
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 مستنبطة من مقاصد القرآن وأصول البيان
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَقُولُوا قَوْلًا سَدِيدًا﴾ [الأحزاب: 70] وقوله: ﴿وَقُولُوا لِلنَّاسِ حُسْنًا﴾ [البقرة: 83].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    إذا دار كلام العاقدين بين حمله على معنى مفيد يصحح العقد وبين إلغائه وإهداره، وجب حمله على المعنى الصحيح النافذ احتراماً لإرادة المتعاقدين واستقراراً للسوق.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">تفسير بنود العقود الغامضة والملحقات الفنية بما يحقق نفاذ العقد واستمراره وحفظ حقوق الطرفين وتفادي فسخ الشراكات القائمة.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الحنفية والمالكية والشافعية والحنابلة، مجلة الأحكام العدلية (مادة 60)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 7 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="liability" data-maxim-search="7 «الْخَرَاجُ بِالضَّمَانِ» 📜 نص حديث نبوي صحيح بلفظه حديث عائشة رضي الله عنها: «أَنَّ النَّبِيَّ ﷺ قَضَى أَنَّ الْخَرَاجَ بِالضَّمَانِ» [رواه أبو داود (3508) والترمذي (1285) والنسائي (4490) وصححه الترمذي وابن حبان والألباني]. استحقاق غلة الأصل وأرباحه وعوائده الاستثمارية يكون لمن يتحمل مخاطرة تلفه وهلاكه وضمانه، فمن يضمن الأصل يحل له ريعه وخراجه. التفريق بين الاستثمار الحلال والربا؛ فالبنك الإسلامي يملك السلعة ويتحمل ضمانها قبل بيعها مرابحة، بينما البنك التقليدي يقرض بفائدة دون تحمل أي ضمان. الفقهاء الأربعة، ابن حزم، الشوكاني، مجلة الأحكام العدلية (مادة 85) الخراج بالضمان استحقاق الربح مخاطرة الاستثمار الفارق بين الربا والمرابحة">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْخَرَاجُ بِالضَّمَانِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        ⚖️ موازين الربح والضمان
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص حديث نبوي صحيح بلفظه
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث عائشة رضي الله عنها: «أَنَّ النَّبِيَّ ﷺ قَضَى أَنَّ الْخَرَاجَ بِالضَّمَانِ» [رواه أبو داود (3508) والترمذي (1285) والنسائي (4490) وصححه الترمذي وابن حبان والألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    استحقاق غلة الأصل وأرباحه وعوائده الاستثمارية يكون لمن يتحمل مخاطرة تلفه وهلاكه وضمانه، فمن يضمن الأصل يحل له ريعه وخراجه.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">التفريق بين الاستثمار الحلال والربا؛ فالبنك الإسلامي يملك السلعة ويتحمل ضمانها قبل بيعها مرابحة، بينما البنك التقليدي يقرض بفائدة دون تحمل أي ضمان.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الفقهاء الأربعة، ابن حزم، الشوكاني، مجلة الأحكام العدلية (مادة 85)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 8 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="liability" data-maxim-search="8 «الْغُنْمُ بِالْغُرْمِ» 📜 مستنبطة من صريح الحديث النبوي تأسست على قضاء النبي ﷺ بالخراج بالضمان، وحديث: «لا يغلق الرهن من صاحبه، له غنمه وعليه غرمه» [صحيح الدارقطني والحاكم]. المستفيد من عوائد ومغانم أي أصل أو شراكة مالية يجب أن يتحمل أعباءه وتكاليفه وخسائره ومخاطره المحتملة، ولا يجوز اشتراط الربح دون الغرم. بطلان اشتراط ضمان رأس المال في شركات العنان والمضاربة، واشتراك المساهمين في تحمل الخسائر بنسبة حصصهم في رأس المال. جمهور الفقهاء وأئمة الأصول، مجلة الأحكام العدلية (مادة 87) الغنم بالغرم تحمل التكاليف توزيع الأرباح والخسائر المضاربة المساهمة">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْغُنْمُ بِالْغُرْمِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        ⚖️ موازين الربح والضمان
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 مستنبطة من صريح الحديث النبوي
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    تأسست على قضاء النبي ﷺ بالخراج بالضمان، وحديث: «لا يغلق الرهن من صاحبه، له غنمه وعليه غرمه» [صحيح الدارقطني والحاكم].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    المستفيد من عوائد ومغانم أي أصل أو شراكة مالية يجب أن يتحمل أعباءه وتكاليفه وخسائره ومخاطره المحتملة، ولا يجوز اشتراط الربح دون الغرم.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">بطلان اشتراط ضمان رأس المال في شركات العنان والمضاربة، واشتراك المساهمين في تحمل الخسائر بنسبة حصصهم في رأس المال.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">جمهور الفقهاء وأئمة الأصول، مجلة الأحكام العدلية (مادة 87)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 9 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="liability" data-maxim-search="9 «كُلُّ قَرْضٍ جَرَّ نَفْعاً مَشْرُوطاً فَهُوَ رِبَا» 📖 أصل قرآني وإجماع قطعي قوله تعالى: ﴿وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا﴾ [البقرة: 278]، وأثر فضالة بن عبيد رضي الله عنه صحيحاً: «كل قرض جر منفعة فهو وجه من وجوه الربا» [البيهقي وصححه الألباني في الإرواء (1398)]. عقد القرض في الإسلام عقد إرفاق وإحسان وتبرع لا معاوضة واسترباح؛ فإذا اشترط الدائن على المقترض أي فائدة نقدية أو عينية أو هدية فهو ربا محرم بإجماع الأمة. تحريم فوائد السحب على المكشوف، الفوائد التأخيرية على بطاقات الائتمان، واشتراط عمولات خاصة لتقديم القروض. إجماع الصحابة والتابعين والأئمة الأربعة وكافة المجامع الفقهية المعاصرة كل قرض جر نفعا فهو ربا ربا القروض بطاقات الائتمان الفوائد التأخيرية">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «كُلُّ قَرْضٍ جَرَّ نَفْعاً مَشْرُوطاً فَهُوَ رِبَا»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        ⚖️ موازين الربح والضمان
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 أصل قرآني وإجماع قطعي
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا﴾ [البقرة: 278]، وأثر فضالة بن عبيد رضي الله عنه صحيحاً: «كل قرض جر منفعة فهو وجه من وجوه الربا» [البيهقي وصححه الألباني في الإرواء (1398)].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    عقد القرض في الإسلام عقد إرفاق وإحسان وتبرع لا معاوضة واسترباح؛ فإذا اشترط الدائن على المقترض أي فائدة نقدية أو عينية أو هدية فهو ربا محرم بإجماع الأمة.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">تحريم فوائد السحب على المكشوف، الفوائد التأخيرية على بطاقات الائتمان، واشتراط عمولات خاصة لتقديم القروض.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع الصحابة والتابعين والأئمة الأربعة وكافة المجامع الفقهية المعاصرة</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 10 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="liability" data-maxim-search="10 «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ» 📜 نص نبوي صحيح بلفظه حديث سمرة بن جندب رضي الله عنه عن النبي ﷺ قال: «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ» [رواه أبو داود والترمذي وصححه والحاكم وصححه الألباني]. كل من قبض مالاً لغيره بطريق غير مشروع (كالغصب والسرقة) أو بطريق مشروع مقيد (كالعارية والمستأجر المتعدي) فهو ضامن لرد عينه أو بدله ومثله وقيمته إن هلك. المسؤولية المدنية والتعويض عن إتلاف البضائع أثناء الشحن والنقل، وضمان الأصول المستأجرة في حال سوء الاستخدام والإهمال. الحنفية والشافعية والحنابلة والمالكية، مجلة الأحكام العدلية (مادة 763) على اليد ما أخذت حتى تؤديه الضمان الشامل المسؤولية المدنية التعويض عن التلف">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        ⚖️ موازين الربح والضمان
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص نبوي صحيح بلفظه
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث سمرة بن جندب رضي الله عنه عن النبي ﷺ قال: «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ» [رواه أبو داود والترمذي وصححه والحاكم وصححه الألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    كل من قبض مالاً لغيره بطريق غير مشروع (كالغصب والسرقة) أو بطريق مشروع مقيد (كالعارية والمستأجر المتعدي) فهو ضامن لرد عينه أو بدله ومثله وقيمته إن هلك.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">المسؤولية المدنية والتعويض عن إتلاف البضائع أثناء الشحن والنقل، وضمان الأصول المستأجرة في حال سوء الاستخدام والإهمال.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الحنفية والشافعية والحنابلة والمالكية، مجلة الأحكام العدلية (مادة 763)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 11 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="liability" data-maxim-search="11 «لَا ضَمَانَ عَلَى مُؤْتَمَنٍ إِلَّا بِتَعَدٍّ أَوْ تَفْرِيطٍ» 📜 نص نبوي صحيح المعنى حديث: «لَيْسَ عَلَى الْمُسْتَوْدَعِ غَيْرِ الْمُغِلِّ ضَمَانٌ» [رواه الدارقطني والبيهقي وصححه ابن عبد البر والألباني في الإرواء (1546)]. الأمين (كالوكيل، المستودع، الشريك، والمضارب) لا يضمن ما تحت يده من أموال وبضائع إذا تلفت بآفة سماوية أو سبب لا يد له فيه، ما لم يثبت تعديه أو إهماله الفاحش. حماية مديري المحافظ الاستثمارية وأمناء المستودعات وحسابات الأمانات من التضمين الجائر عند تقلبات الأسواق العالمية ما داموا التزموا بالحوكمة المهنية. الأئمة الأربعة وسائر فقهاء الإسلام، مجلة الأحكام العدلية (مادة 777) لا ضمان على مؤتمن إلا بتعد أو تفريط يد الأمانة الوكالة الاستثمارية الودائع">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «لَا ضَمَانَ عَلَى مُؤْتَمَنٍ إِلَّا بِتَعَدٍّ أَوْ تَفْرِيطٍ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        ⚖️ موازين الربح والضمان
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص نبوي صحيح المعنى
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث: «لَيْسَ عَلَى الْمُسْتَوْدَعِ غَيْرِ الْمُغِلِّ ضَمَانٌ» [رواه الدارقطني والبيهقي وصححه ابن عبد البر والألباني في الإرواء (1546)].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأمين (كالوكيل، المستودع، الشريك، والمضارب) لا يضمن ما تحت يده من أموال وبضائع إذا تلفت بآفة سماوية أو سبب لا يد له فيه، ما لم يثبت تعديه أو إهماله الفاحش.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">حماية مديري المحافظ الاستثمارية وأمناء المستودعات وحسابات الأمانات من التضمين الجائر عند تقلبات الأسواق العالمية ما داموا التزموا بالحوكمة المهنية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الأئمة الأربعة وسائر فقهاء الإسلام، مجلة الأحكام العدلية (مادة 777)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 12 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="harm" data-maxim-search="12 «لَا ضَرَرَ وَلَا ضِرَارَ · الضَّرَرُ يُزَالُ» 📜 نص نبوي صحيح محكم حديث رسول الله ﷺ: «لَا ضَرَرَ وَلَا ضِرَارَ» [رواه ابن ماجه (2340) وأحمد ومالك في الموطأ وصححه الحاكم والنووي والألباني]. تحريم إلحاق أي ضرر مالي أو تجاري بالآخرين ابتداءً، وتحريم مقابلة الضرر بالضرر، ووجوب رفع كل ما يضر بسلامة الأسواق والمستهلكين والشركاء. منع المنافسة غير الشريفة، منع احتكار السلع، منع تسريب البيانات الحساسة، وإلزام المنشآت الصناعية بإزالة الأضرار البيئية على الجيران. القاعدة الكبرى بالإجماع، السيوطي، ابن نجيم، مجلة الأحكام العدلية (مادة 19 و 20) لا ضرر ولا ضرار الضرر يزال حماية الأسواق المنافسة الشريفة منع الإضرار">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «لَا ضَرَرَ وَلَا ضِرَارَ · الضَّرَرُ يُزَالُ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🛡️ درء المفاسد ونفي الغرر
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص نبوي صحيح محكم
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «لَا ضَرَرَ وَلَا ضِرَارَ» [رواه ابن ماجه (2340) وأحمد ومالك في الموطأ وصححه الحاكم والنووي والألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    تحريم إلحاق أي ضرر مالي أو تجاري بالآخرين ابتداءً، وتحريم مقابلة الضرر بالضرر، ووجوب رفع كل ما يضر بسلامة الأسواق والمستهلكين والشركاء.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">منع المنافسة غير الشريفة، منع احتكار السلع، منع تسريب البيانات الحساسة، وإلزام المنشآت الصناعية بإزالة الأضرار البيئية على الجيران.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">القاعدة الكبرى بالإجماع، السيوطي، ابن نجيم، مجلة الأحكام العدلية (مادة 19 و 20)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 13 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="harm" data-maxim-search="13 «دَرْءُ الْمَفَاسِدِ مُقَدَّمٌ عَلَى جَلْبِ الْمَصَالِحِ» 📖 أصل قرآني في ميزان المنافع والمفاسد قوله تعالى: ﴿يَسْأَلُونَكَ عَنِ الْخَمْرِ وَالْمَيْسِرِ ۖ قُلْ فِيهِمَا إِثْمٌ كَبِيرٌ وَمَنَافِعُ لِلنَّاسِ وَإِثْمُهُمَا أَكْبَرُ مِن نَّفْعِهِمَا﴾ [البقرة: 219]. إذا تعارضت مصلحة ربحية مع مفسدة تجارية أو مجتمعية راجحة، قُدِّم منع المفسدة ودفعها على تحقيق المكسب المالي. حظر المشاريع الاستثمارية التي تروج للمخدرات أو الإباحية أو المقامرات المالية الرقمية مهما بلغت إيراداتها وعوائدها المالية المغرية. العز بن عبد السلام (قواعد الأحكام)، الشاطبي (الموافقات)، مجلة الأحكام العدلية (مادة 30) درء المفاسد مقدم على جلب المصالح الميسر المقامرة الاستثمار الأخلاقي">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «دَرْءُ الْمَفَاسِدِ مُقَدَّمٌ عَلَى جَلْبِ الْمَصَالِحِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🛡️ درء المفاسد ونفي الغرر
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 أصل قرآني في ميزان المنافع والمفاسد
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿يَسْأَلُونَكَ عَنِ الْخَمْرِ وَالْمَيْسِرِ ۖ قُلْ فِيهِمَا إِثْمٌ كَبِيرٌ وَمَنَافِعُ لِلنَّاسِ وَإِثْمُهُمَا أَكْبَرُ مِن نَّفْعِهِمَا﴾ [البقرة: 219].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    إذا تعارضت مصلحة ربحية مع مفسدة تجارية أو مجتمعية راجحة، قُدِّم منع المفسدة ودفعها على تحقيق المكسب المالي.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">حظر المشاريع الاستثمارية التي تروج للمخدرات أو الإباحية أو المقامرات المالية الرقمية مهما بلغت إيراداتها وعوائدها المالية المغرية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">العز بن عبد السلام (قواعد الأحكام)، الشاطبي (الموافقات)، مجلة الأحكام العدلية (مادة 30)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 14 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="harm" data-maxim-search="14 «الْغَرَرُ الْكَثِيرُ مَفْسَدَةٌ لِلْعَقْدِ دُونَ الْيَسِيرِ» 📜 صريح صحيح مسلم حديث أبي هريرة رضي الله عنه: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ» [صحيح مسلم (1513)]. الجهالة الكبيرة التي تؤدي للنزاع والمقامرة تبطل العقد المالي، أما الغرر التافه أو اليسير الذي يشق التحرز منه فيعفى عنه تيسيراً لحركة الأسواق. جواز بيع السيارات المستعملة مع وجود غرر يسير في باطن المحرك لا يمكن كشفه إلا بالتفكيك، وبطلان عقود المشتقات المالية والمقامرات المشوبة بغرر فاحش. الإمام مالك، القرافي (الفروق)، النووي، ابن رشد، مجمع الفقه الإسلامي الدولي الغرر الكثير مفسدة للعقد بيع الغرر بيع الحصاة المشتقات المالية الجهالة الفاحشة">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْغَرَرُ الْكَثِيرُ مَفْسَدَةٌ لِلْعَقْدِ دُونَ الْيَسِيرِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🛡️ درء المفاسد ونفي الغرر
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 صريح صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث أبي هريرة رضي الله عنه: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ» [صحيح مسلم (1513)].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الجهالة الكبيرة التي تؤدي للنزاع والمقامرة تبطل العقد المالي، أما الغرر التافه أو اليسير الذي يشق التحرز منه فيعفى عنه تيسيراً لحركة الأسواق.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">جواز بيع السيارات المستعملة مع وجود غرر يسير في باطن المحرك لا يمكن كشفه إلا بالتفكيك، وبطلان عقود المشتقات المالية والمقامرات المشوبة بغرر فاحش.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الإمام مالك، القرافي (الفروق)، النووي، ابن رشد، مجمع الفقه الإسلامي الدولي</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 15 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="harm" data-maxim-search="15 «الْجَوَازُ الشَّرْعِيُّ يُنَافِي الضَّمَانَ» 📖 مستنبطة من نفي السبيل عن المحسنين قوله تعالى: ﴿مَا عَلَى الْمُحْسِنِينَ مِن سَبِيلٍ﴾ [التوبة: 91]. من مارس فعلاً أذن له الشارع فيه أو كان بتفويض مشروع من صاحب العمل دون تعدٍ ولا تقصير، فلا يضمن ما قد يترتب على ذلك الفعل من ضرر غير مقصود. عدم تضمين الطبيب الحاذق أو المقاول المرخص إذا التزم بالمعايير الهندسية والطبية المعتمدة وحدث تلف خارج عن الإرادة وبلا إهمال. الفقهاء الأربعة، مجلة الأحكام العدلية (مادة 91) الجواز الشرعي ينافي الضمان ما على المحسنين من سبيل المسؤولية المهنية المقاولات">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْجَوَازُ الشَّرْعِيُّ يُنَافِي الضَّمَانَ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🛡️ درء المفاسد ونفي الغرر
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 مستنبطة من نفي السبيل عن المحسنين
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿مَا عَلَى الْمُحْسِنِينَ مِن سَبِيلٍ﴾ [التوبة: 91].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    من مارس فعلاً أذن له الشارع فيه أو كان بتفويض مشروع من صاحب العمل دون تعدٍ ولا تقصير، فلا يضمن ما قد يترتب على ذلك الفعل من ضرر غير مقصود.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">عدم تضمين الطبيب الحاذق أو المقاول المرخص إذا التزم بالمعايير الهندسية والطبية المعتمدة وحدث تلف خارج عن الإرادة وبلا إهمال.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الفقهاء الأربعة، مجلة الأحكام العدلية (مادة 91)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 16 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="harm" data-maxim-search="16 «مَنِ اسْتَعْجَلَ الشَّيْءَ قَبْلَ أَوَانِهِ عُوقِبَ بِحِرْمَانِهِ» 📜 مأخوذة من قضاء النبوة في حرمان القاتل حديث رسول الله ﷺ: «الْقَاتِلُ لَا يَرِثُ» [رواه الترمذي وابن ماجه وصححه الألباني]. كل من سلك طريقاً غير مشروع للتعجيل بالحصول على حق مالي أو صفقة قبل وقتها، عاقبه الشارع بإبطال تصرفه وحرمانه من ذلك المكسب كلياً. حرمان الموظف الذي يسرب معلومات المناقصات لصالحه، وبطلان التنازل عن الديون والحقوق تحت وطأة التحايل والتعجيل غير العادل. الفقهاء الأربعة، مجلة الأحكام العدلية (مادة 99) من استعجل الشيء قبل أوانه عوقب بحرمانه سد الذرائع تسريب المناقصات التحايل المالي">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        16
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «مَنِ اسْتَعْجَلَ الشَّيْءَ قَبْلَ أَوَانِهِ عُوقِبَ بِحِرْمَانِهِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🛡️ درء المفاسد ونفي الغرر
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 مأخوذة من قضاء النبوة في حرمان القاتل
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «الْقَاتِلُ لَا يَرِثُ» [رواه الترمذي وابن ماجه وصححه الألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    كل من سلك طريقاً غير مشروع للتعجيل بالحصول على حق مالي أو صفقة قبل وقتها، عاقبه الشارع بإبطال تصرفه وحرمانه من ذلك المكسب كلياً.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">حرمان الموظف الذي يسرب معلومات المناقصات لصالحه، وبطلان التنازل عن الديون والحقوق تحت وطأة التحايل والتعجيل غير العادل.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الفقهاء الأربعة، مجلة الأحكام العدلية (مادة 99)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 17 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="integrity" data-maxim-search="17 «تَحْرِيمُ أَكْلِ أَمْوَالِ النَّاسِ بِالْبَاطِلِ» 📖 نص قرآني قطعي متكرر قوله تعالى: ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالإِثْمِ﴾ [البقرة: 188]. الأصل العام الحاكم لكافة أموال الخلق: بطلان وتجريم كل كسب مالي لا يقابله عوض مشروع أو طيب نفس أو سبب شرعي صحيح كالرشوة والاحتيال والغصب. تجريم عمولات الشراء السرية، الرشاوى الوظيفية، النصب الإلكتروني، والتلاعب في الميزانيات المحاسبية. إجماع علماء الأمة بلا خلاف لا تأكلوا أموالكم بينكم بالباطل الرشوة أكل الحرام النصب الاحتيال الكسب غير المشروع">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        17
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «تَحْرِيمُ أَكْلِ أَمْوَالِ النَّاسِ بِالْبَاطِلِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ النزاهة وحماية الحقوق
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 نص قرآني قطعي متكرر
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالإِثْمِ﴾ [البقرة: 188].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأصل العام الحاكم لكافة أموال الخلق: بطلان وتجريم كل كسب مالي لا يقابله عوض مشروع أو طيب نفس أو سبب شرعي صحيح كالرشوة والاحتيال والغصب.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">تجريم عمولات الشراء السرية، الرشاوى الوظيفية، النصب الإلكتروني، والتلاعب في الميزانيات المحاسبية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع علماء الأمة بلا خلاف</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 18 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="integrity" data-maxim-search="18 «حُرْمَةُ مَالِ الْمُسْلِمِ كَحُرْمَةِ دَمِهِ» 📜 صريح خطبة حجة الوداع المتواترة حديث النبي ﷺ في حجة الوداع: «إِنَّ دِمَاءَكُمْ وَأَمْوَالَكُمْ وَأَعْرَاضَكُمْ عَلَيْكُمْ حَرَامٌ، كَحُرْمَةِ يَوْمِكُمْ هَذَا، فِي شَهْرِكُمْ هَذَا، فِي بَلَدِكُمْ هَذَا» [متفق عليه]. قدسية الملكية الفردية والأموال والحسابات المصرفية؛ فلا يجوز مصادرتها أو تجميدها أو الاقتطاع منها بغير حكم قضائي عادل وبسبب شرعي ثابت. حماية أموال المودعين والمستثمرين، ومنع الاستيلاء على أملاك الشركات بحجج واهية، وحق الدفاع الشرعي عن المال. إجماع كافة فقهاء الإسلام حرمة مال المسلم كحرمة دمه حجة الوداع الملكية الخاصة حماية الودائع المصادرة">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        18
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «حُرْمَةُ مَالِ الْمُسْلِمِ كَحُرْمَةِ دَمِهِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ النزاهة وحماية الحقوق
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 صريح خطبة حجة الوداع المتواترة
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث النبي ﷺ في حجة الوداع: «إِنَّ دِمَاءَكُمْ وَأَمْوَالَكُمْ وَأَعْرَاضَكُمْ عَلَيْكُمْ حَرَامٌ، كَحُرْمَةِ يَوْمِكُمْ هَذَا، فِي شَهْرِكُمْ هَذَا، فِي بَلَدِكُمْ هَذَا» [متفق عليه].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    قدسية الملكية الفردية والأموال والحسابات المصرفية؛ فلا يجوز مصادرتها أو تجميدها أو الاقتطاع منها بغير حكم قضائي عادل وبسبب شرعي ثابت.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">حماية أموال المودعين والمستثمرين، ومنع الاستيلاء على أملاك الشركات بحجج واهية، وحق الدفاع الشرعي عن المال.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع كافة فقهاء الإسلام</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 19 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="integrity" data-maxim-search="19 «لَا يَحِلُّ مَالُ امْرِئٍ مُسْلِمٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ» 📜 نص نبوي صحيح بلفظه حديث رسول الله ﷺ: «أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، إِنَّهُ لَا يَحِلُّ مَالُ امْرِئٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ» [رواه أحمد والبيهقي والدارقطني وصححه الألباني]. كل مال أُخذ بالحياء أو الإحراج أو الضغط الأدبي دون رضا باطني واختيار حر فهو سحت باطل ومردود على صاحبه شرعاً. بطلان التبرعات الإجبارية المقتطعة من رواتب الموظفين دون موافقتهم الصريحة، وبطلان فرض رسوم خدمات إضافية مخفية دون موافقة العميل. الفقهاء الأربعة وسائر أئمة المذاهب لا يحل مال امرئ إلا بطيب نفس منه ألا لا تظلموا طيب النفس الاقتطاعات الإجبارية">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        19
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «لَا يَحِلُّ مَالُ امْرِئٍ مُسْلِمٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ النزاهة وحماية الحقوق
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص نبوي صحيح بلفظه
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، إِنَّهُ لَا يَحِلُّ مَالُ امْرِئٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ» [رواه أحمد والبيهقي والدارقطني وصححه الألباني].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    كل مال أُخذ بالحياء أو الإحراج أو الضغط الأدبي دون رضا باطني واختيار حر فهو سحت باطل ومردود على صاحبه شرعاً.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">بطلان التبرعات الإجبارية المقتطعة من رواتب الموظفين دون موافقتهم الصريحة، وبطلان فرض رسوم خدمات إضافية مخفية دون موافقة العميل.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الفقهاء الأربعة وسائر أئمة المذاهب</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 20 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="integrity" data-maxim-search="20 «وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ · عَدَالَةُ التَّثْمِينِ» 📖 نص قرآني في آيات المعاملات قوله تعالى: ﴿وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تُفْسِدُوا فِي الْأَرْضِ بَعْدَ إِصْلَاحِهَا﴾ [الأعراف: 85 وسورة هود: 85 والشعراء: 183]. وجوب تقييم السلع والأصول والجهود المهنية بقيمتها العادلة، وتحريم إنقاص أثمان السلع وأجور العمال أو استغلال اضطرار البائع للشراء منه بأقل من سعر السوق. التقييم المحاسبي العادل للأصول، النزاهة في دراسات الجدوى، وإنصاف الموظفين في جداول الأجور والمكافآت. جمهور المفسرين والفقهاء ولا تبخسوا الناس أشياءهم عدالة التثمين تقييم الأصول بخس الأجور دراسات الجدوى">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        20
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ · عَدَالَةُ التَّثْمِينِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ النزاهة وحماية الحقوق
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 نص قرآني في آيات المعاملات
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تُفْسِدُوا فِي الْأَرْضِ بَعْدَ إِصْلَاحِهَا﴾ [الأعراف: 85 وسورة هود: 85 والشعراء: 183].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    وجوب تقييم السلع والأصول والجهود المهنية بقيمتها العادلة، وتحريم إنقاص أثمان السلع وأجور العمال أو استغلال اضطرار البائع للشراء منه بأقل من سعر السوق.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">التقييم المحاسبي العادل للأصول، النزاهة في دراسات الجدوى، وإنصاف الموظفين في جداول الأجور والمكافآت.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">جمهور المفسرين والفقهاء</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 21 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="integrity" data-maxim-search="21 «إِيفَاءُ الْكَيْلِ وَالْمِيزَانِ وَحُرْمَةُ التَّطْفِيفِ الْمَالِي» 📖 نص قرآني صريح في سورة المطففين قوله تعالى: ﴿وَيْلٌ لِّلْمُطَفِّفِينَ · الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ · وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ﴾ [المطففين: 1-3]. وجوب الدقة المطلقة والنزاهة في المقاييس والأوزان والكميات المبيعة، وتجريم أخذ الحق كاملاً عند الشراء وإنقاصه عند البيع والتسليم. التدقيق المحاسبي الصارم، منع التلاعب بالكميات في التعبئة والتغليف، وضبط عدادات الوقود والموازين الرقمية في الأسواق. إجماع علماء الأمة ويل للمطففين إيفاء الكيل والميزان التطفيف المالي التدقيق المحاسبي النزاهة">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        21
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «إِيفَاءُ الْكَيْلِ وَالْمِيزَانِ وَحُرْمَةُ التَّطْفِيفِ الْمَالِي»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ النزاهة وحماية الحقوق
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 نص قرآني صريح في سورة المطففين
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَيْلٌ لِّلْمُطَفِّفِينَ · الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ · وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ﴾ [المطففين: 1-3].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    وجوب الدقة المطلقة والنزاهة في المقاييس والأوزان والكميات المبيعة، وتجريم أخذ الحق كاملاً عند الشراء وإنقاصه عند البيع والتسليم.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">التدقيق المحاسبي الصارم، منع التلاعب بالكميات في التعبئة والتغليف، وضبط عدادات الوقود والموازين الرقمية في الأسواق.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع علماء الأمة</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 22 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="market" data-maxim-search="22 «حَظْرُ الِاحْتِكَارِ وَإِغْلَاءِ الأَسْعَارِ» 📜 صريح صحيح مسلم حديث رسول الله ﷺ: «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ» [صحيح مسلم (1605)]. تحريم حبس السلع التموينية والحاجات الأساسية عن السوق بهدف تقليل المعروض وإجبار الناس على شرائها بأسعار مضاعفة واستغلال الأزمات. قوانين حماية المنافسة ومنع الاحتكار ومكافحة الممارسات الاحتكارية وتخزين الأقوات والمستلزمات الطبية في أوقات الطوارئ. الفقهاء الأربعة وسائر أئمة الاقتصاد الإسلامي لا يحتكر إلا خاطئ حظر الاحتكار إغلاء الأسعار حماية المنافسة السلع الأساسية">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        22
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «حَظْرُ الِاحْتِكَارِ وَإِغْلَاءِ الأَسْعَارِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        🏪 أخلاقيات السوق والتنافس
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 صريح صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «لَا يَحْتَكِرُ إِلَّا خَاطِئٌ» [صحيح مسلم (1605)].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    تحريم حبس السلع التموينية والحاجات الأساسية عن السوق بهدف تقليل المعروض وإجبار الناس على شرائها بأسعار مضاعفة واستغلال الأزمات.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">قوانين حماية المنافسة ومنع الاحتكار ومكافحة الممارسات الاحتكارية وتخزين الأقوات والمستلزمات الطبية في أوقات الطوارئ.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الفقهاء الأربعة وسائر أئمة الاقتصاد الإسلامي</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 23 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="market" data-maxim-search="23 «شَفَافِيَّةُ السُّوقِ وَمَنْعُ اسْتِغْلَالِ جَهَالَةِ الْمُتَعَامِلِينَ» 📜 متفق عليه في الصحيحين حديث ابن عمر رضي الله عنهما: «نَهَى النَّبِيُّ ﷺ عَنِ النَّجْشِ، وَأَنْ يَبِيعَ حَاضِرٌ لِبَادٍ، وَأَنْ تُلَقَّى السِّلَعُ حَتَّى تَهْبِطَ إِلَى الأَسْوَاقِ» [متفق عليه]. وجوب إتاحة معلومات الأسعار العادلة للجميع، وحظر تلقي البضائع خارج الأسواق لشرائها بغبن من أصحابها الجاهلين بالسعر الحقيقي، وحظر المزايدات الوهمية (النجش). شفافية أسواق الأسهم والبورصات، تجريم التداول بناءً على معلومات داخلية مسربة، ومنع تزييف المزادات بالبوتات الرقمية. الأئمة الأربعة، ابن تيمية، مجلة الأحكام العدلية شفافية السوق النجش بيع حاضر لباد تلقي الركبان البورصة التداول الداخلي">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        23
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «شَفَافِيَّةُ السُّوقِ وَمَنْعُ اسْتِغْلَالِ جَهَالَةِ الْمُتَعَامِلِينَ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        🏪 أخلاقيات السوق والتنافس
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 متفق عليه في الصحيحين
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث ابن عمر رضي الله عنهما: «نَهَى النَّبِيُّ ﷺ عَنِ النَّجْشِ، وَأَنْ يَبِيعَ حَاضِرٌ لِبَادٍ، وَأَنْ تُلَقَّى السِّلَعُ حَتَّى تَهْبِطَ إِلَى الأَسْوَاقِ» [متفق عليه].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    وجوب إتاحة معلومات الأسعار العادلة للجميع، وحظر تلقي البضائع خارج الأسواق لشرائها بغبن من أصحابها الجاهلين بالسعر الحقيقي، وحظر المزايدات الوهمية (النجش).
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">شفافية أسواق الأسهم والبورصات، تجريم التداول بناءً على معلومات داخلية مسربة، ومنع تزييف المزادات بالبوتات الرقمية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">الأئمة الأربعة، ابن تيمية، مجلة الأحكام العدلية</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 24 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="market" data-maxim-search="24 «تَحْرِيمُ الْغِشِّ وَثُبُوتُ خِيَارِ الْعَيْبِ» 📜 صريح صحيح مسلم حديث رسول الله ﷺ: «مَنْ غَشَّ فَلَيْسَ مِنِّي» [صحيح مسلم (102)]. وجوب الإفصاح الكامل عن كل عيب أو نقص في البضاعة المباعة، وثبوت حق المشتري في رد السلعة واسترداد ثمنها فور اكتشاف أي عيب خفي. حقوق حماية المستهلك، سياسات استرجاع البضائع المعيبة، وتجريم التلاعب ببلد المنشأ وتواريخ الصلاحية. إجماع فقهاء الإسلام من غش فليس مني خيار العيب حماية المستهلك استرجاع البضائع الغش التجاري">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        24
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «تَحْرِيمُ الْغِشِّ وَثُبُوتُ خِيَارِ الْعَيْبِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        🏪 أخلاقيات السوق والتنافس
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 صريح صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «مَنْ غَشَّ فَلَيْسَ مِنِّي» [صحيح مسلم (102)].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    وجوب الإفصاح الكامل عن كل عيب أو نقص في البضاعة المباعة، وثبوت حق المشتري في رد السلعة واسترداد ثمنها فور اكتشاف أي عيب خفي.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">حقوق حماية المستهلك، سياسات استرجاع البضائع المعيبة، وتجريم التلاعب ببلد المنشأ وتواريخ الصلاحية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع فقهاء الإسلام</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 25 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="market" data-maxim-search="25 «دَفْعُ الضَّرَرِ عَنِ الشَّرِيكِ وَالْجَارِ بِالشُّفْعَةِ» 📜 متفق عليه في الصحيحين حديث جابر رضي الله عنه: «قَضَى رَسُولُ اللَّهِ ﷺ بِالشُّفْعَةِ فِي كُلِّ مَا لَمْ يُقْسَمْ» [صحيح البخاري ومسلم]. تشريع حق الشفعة لمنع دخول شريك أجنبي غير مرغوب فيه قد يضر ببقية الشركاء أو الجيران، بإعطاء الشريك القائم أولوية الشراء بذات الثمن المتفق عليه. أحقية الشركاء في شراء حصص الشريك المنسحب في الشركات ذات المسؤولية المحدودة قبل طرحها للغير حفاظاً على استقرار المنشأة. جمهور الفقهاء الأربعة، مجلة الأحكام العدلية (مادة 950) قضى بالشفعة في كل ما لم يقسم حق الشفعة استقرار الشركات خروج الشريك">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        25
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «دَفْعُ الضَّرَرِ عَنِ الشَّرِيكِ وَالْجَارِ بِالشُّفْعَةِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        🏪 أخلاقيات السوق والتنافس
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 متفق عليه في الصحيحين
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث جابر رضي الله عنه: «قَضَى رَسُولُ اللَّهِ ﷺ بِالشُّفْعَةِ فِي كُلِّ مَا لَمْ يُقْسَمْ» [صحيح البخاري ومسلم].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    تشريع حق الشفعة لمنع دخول شريك أجنبي غير مرغوب فيه قد يضر ببقية الشركاء أو الجيران، بإعطاء الشريك القائم أولوية الشراء بذات الثمن المتفق عليه.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">أحقية الشركاء في شراء حصص الشريك المنسحب في الشركات ذات المسؤولية المحدودة قبل طرحها للغير حفاظاً على استقرار المنشأة.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">جمهور الفقهاء الأربعة، مجلة الأحكام العدلية (مادة 950)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 26 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="evidence" data-maxim-search="26 «الْيَقِينُ لَا يَزُولُ بِالشَّكِّ · الأَصْلُ بَقَاءُ مَا كَانَ» 📜 مستنبطة من أصح الأحاديث النبوية حديث صحيح مسلم فيمن شك في الصلاة أو الحدث: «لَا يَنْصَرِفْ حَتَّى يَسْمَعَ صَوْتًا أَوْ يَجِدَ رِيحًا»، وقوله تعالى: ﴿إِنَّ الظَّنَّ لَا يُغْنِي مِنَ الْحَقِّ شَيْئًا﴾ [يونس: 36]. الأمر الثابت المتيقن لا يرتفع بمجرد الشك والتردد والظنون؛ بل يبقى الحكم على ما كان عليه حتى يثبت العكس ببرهان قاطع. بقاء العقد صحيحاً ما لم يثبت فسخه بدليل، استمرار ملكية الأصل لمالكه حتى تثبت الحوالة، وبقاء المديونية مسجلة حتى يثبت سند السداد. القاعدة الكبرى بالإجماع، الشافعي، السيوطي، ابن نجيم، مجلة الأحكام العدلية (مادة 4) اليقين لا يزول بالشك الأصل بقاء ما كان استصحاب الحال استقرار المعاملات سند السداد">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        26
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْيَقِينُ لَا يَزُولُ بِالشَّكِّ · الأَصْلُ بَقَاءُ مَا كَانَ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        ⚖️ الإثبات واستقرار المعاملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 مستنبطة من أصح الأحاديث النبوية
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث صحيح مسلم فيمن شك في الصلاة أو الحدث: «لَا يَنْصَرِفْ حَتَّى يَسْمَعَ صَوْتًا أَوْ يَجِدَ رِيحًا»، وقوله تعالى: ﴿إِنَّ الظَّنَّ لَا يُغْنِي مِنَ الْحَقِّ شَيْئًا﴾ [يونس: 36].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأمر الثابت المتيقن لا يرتفع بمجرد الشك والتردد والظنون؛ بل يبقى الحكم على ما كان عليه حتى يثبت العكس ببرهان قاطع.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">بقاء العقد صحيحاً ما لم يثبت فسخه بدليل، استمرار ملكية الأصل لمالكه حتى تثبت الحوالة، وبقاء المديونية مسجلة حتى يثبت سند السداد.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">القاعدة الكبرى بالإجماع، الشافعي، السيوطي، ابن نجيم، مجلة الأحكام العدلية (مادة 4)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 27 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="evidence" data-maxim-search="27 «الأَصْلُ بَرَاءَةُ الذِّمَّةِ» 📖 أصل قرآني في العدالة الجنائية والمالية قوله تعالى: ﴿وَلَا تَزِرُ وَازِرَةٌ وِزْرَ أُخْرَىٰ﴾ [الإسراء: 15]، وحديث: «الْبَيِّنَةُ عَلَى الْمُدَّعِي» [صحيح الترمذي]. ذمة الإنسان المالية بريئة وخالية من أي التزام أو دين أو ضمان ما لم يقم المدعي الدليل الحاسم والبينة المحاسبية المثبتة للحق. عدم جواز إلزام أي شخص أو تاجر بفاتورة أو مطالبة مالية إلا بوجود سند قبض أو عقد موقع أو مستند تحويل مصرفي معتمد. إجماع أئمة الفقه والقضاء، مجلة الأحكام العدلية (مادة 8) الأصل براءة الذمة عبء الإثبات خلو الذمة المطالبات المالية الفواتير">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        27
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الأَصْلُ بَرَاءَةُ الذِّمَّةِ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        ⚖️ الإثبات واستقرار المعاملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 أصل قرآني في العدالة الجنائية والمالية
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿وَلَا تَزِرُ وَازِرَةٌ وِزْرَ أُخْرَىٰ﴾ [الإسراء: 15]، وحديث: «الْبَيِّنَةُ عَلَى الْمُدَّعِي» [صحيح الترمذي].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    ذمة الإنسان المالية بريئة وخالية من أي التزام أو دين أو ضمان ما لم يقم المدعي الدليل الحاسم والبينة المحاسبية المثبتة للحق.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">عدم جواز إلزام أي شخص أو تاجر بفاتورة أو مطالبة مالية إلا بوجود سند قبض أو عقد موقع أو مستند تحويل مصرفي معتمد.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع أئمة الفقه والقضاء، مجلة الأحكام العدلية (مادة 8)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 28 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="evidence" data-maxim-search="28 «الْبَيِّنَةُ عَلَى الْمُدَّعِي، وَالْيَمِينُ عَلَى مَنْ أَنْكَرَ» 📜 نص نبوي صحيح صريح حديث رسول الله ﷺ: «لَوْ يُعْطَى النَّاسُ بِدَعْوَاهُمْ لَادَّعَى رِجَالٌ أَمْوَالَ قَوْمٍ وَدِمَاءَهُمْ، لَكِنَّ الْبَيِّنَةَ عَلَى الْمُدَّعِي، وَالْيَمِينَ عَلَى مَنْ أَنْكَرَ» [رواه البيهقي والترمذي وصححه النووي]. توزيع عبء الإثبات القضائي والمحاسبي العادل: على من يدعي خلاف الأصل تقديم الوثائق والفواتير والشهود، فإن عجز فالقول قول المنكر بيمينه تبرئة لذمته. أساس قانون المرافعات والإثبات التجاري والمحاسبي والتحكيم المالي في فض النزاعات بين الشركات والتجار. إجماع علماء الإسلام وكافة قوانين الإثبات في العالم الإسلامي، مجلة الأحكام العدلية (مادة 76) البينة على المدعي واليمين على من أنكر عبء الإثبات المحاكم التجارية التحكيم المالي">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        28
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْبَيِّنَةُ عَلَى الْمُدَّعِي، وَالْيَمِينُ عَلَى مَنْ أَنْكَرَ»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        ⚖️ الإثبات واستقرار المعاملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📜 نص نبوي صحيح صريح
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    حديث رسول الله ﷺ: «لَوْ يُعْطَى النَّاسُ بِدَعْوَاهُمْ لَادَّعَى رِجَالٌ أَمْوَالَ قَوْمٍ وَدِمَاءَهُمْ، لَكِنَّ الْبَيِّنَةَ عَلَى الْمُدَّعِي، وَالْيَمِينَ عَلَى مَنْ أَنْكَرَ» [رواه البيهقي والترمذي وصححه النووي].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    توزيع عبء الإثبات القضائي والمحاسبي العادل: على من يدعي خلاف الأصل تقديم الوثائق والفواتير والشهود، فإن عجز فالقول قول المنكر بيمينه تبرئة لذمته.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">أساس قانون المرافعات والإثبات التجاري والمحاسبي والتحكيم المالي في فض النزاعات بين الشركات والتجار.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">إجماع علماء الإسلام وكافة قوانين الإثبات في العالم الإسلامي، مجلة الأحكام العدلية (مادة 76)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 29 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="evidence" data-maxim-search="29 «الْعَادَةُ مُحَكَّمَةٌ · الْمَعْرُوفُ عُرْفاً كَالْمَشْرُوطِ شَرْطاً» 📖 نص قرآني وأثر ابن مسعود الصحيح قوله تعالى: ﴿خُذِ الْعَفْوَ وَأْمُرْ بِالْعُرْفِ وَأَعْرِضْ عَنِ الْجَاهِلِينَ﴾ [الأعراف: 199]، وأثر عبد الله بن مسعود رضي الله عنه: «مَا رَآهُ الْمُسْلِمُونَ حَسَنًا فَهُوَ عِنْدَ اللَّهِ حَسَنٌ» [رواه أحمد وصححه الحاكم والنووي]. الأعراف التجارية المستقرة والممارسات المهنية المعتادة في بيئة العمل تعتبر حجة ملزمة كالشروط المكتوبة تماماً ما لم تخالف نصاً شرعياً صريحاً. اعتماد أعراف الشحن والتسليم المعتمدة (Incoterms)، تحديد مواعيد تسليم البضائع بحسب عرف السوق، وتحكيم العرف في أجور الخدمات عند عدم التسمية. القاعدة الكبرى بالإجماع، السرخسي، القرافي، مجلة الأحكام العدلية (مادة 36 و 43) العادة محكمة المعروف عرفا كالمشروط شرطا الأعراف التجارية Incoterms عرف السوق">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        29
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْعَادَةُ مُحَكَّمَةٌ · الْمَعْرُوفُ عُرْفاً كَالْمَشْرُوطِ شَرْطاً»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        ⚖️ الإثبات واستقرار المعاملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 نص قرآني وأثر ابن مسعود الصحيح
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿خُذِ الْعَفْوَ وَأْمُرْ بِالْعُرْفِ وَأَعْرِضْ عَنِ الْجَاهِلِينَ﴾ [الأعراف: 199]، وأثر عبد الله بن مسعود رضي الله عنه: «مَا رَآهُ الْمُسْلِمُونَ حَسَنًا فَهُوَ عِنْدَ اللَّهِ حَسَنٌ» [رواه أحمد وصححه الحاكم والنووي].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأعراف التجارية المستقرة والممارسات المهنية المعتادة في بيئة العمل تعتبر حجة ملزمة كالشروط المكتوبة تماماً ما لم تخالف نصاً شرعياً صريحاً.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">اعتماد أعراف الشحن والتسليم المعتمدة (Incoterms)، تحديد مواعيد تسليم البضائع بحسب عرف السوق، وتحكيم العرف في أجور الخدمات عند عدم التسمية.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">القاعدة الكبرى بالإجماع، السرخسي، القرافي، مجلة الأحكام العدلية (مادة 36 و 43)</span>
                </div>
            </div>

        </div>

        <!-- Maxim Card 30 -->
        <div class="fiqh-maxim-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3.5 shadow-xl hover:border-emerald-400 transition" data-maxim-cat="evidence" data-maxim-search="30 «الْمَشَقَّةُ تَجْلِبُ التَّيْسِيرَ · وَالضَّرُورَاتُ تُبِيحُ الْمَحْظُورَاتِ بِقَدْرِهَا» 📖 نص قرآني في رفع الحرج والتيسير قوله تعالى: ﴿يُرِيدُ اللَّهُ بِكُمُ الْيُسْرَ وَلَا يُرِيدُ بِكُمُ الْعُسْرَ﴾ [البقرة: 185]، وقوله: ﴿وَمَا جَعَلَ عَلَيْكُمْ فِي الدِّينِ مِنْ حَرَجٍ﴾ [الحج: 78]، وقوله: ﴿فَمَنِ اضْطُرَّ غَيْرَ بَاغٍ وَلَا عَادٍ فَلَا إِثْمَ عَلَيْهِ﴾ [البقرة: 173]. الصعوبات والحرج والظروف الطارئة والاستثنائية تجلب التخفيف الشرعي والرخص المناسبة لإنقاذ رؤوس الأموال ومصالح المتعاقدين بقدر الحاجة دون توسع مفرط. إعادة جدولة ديون الشركات المتعثرة في الأزمات الاقتصادية، إقرار عقود التأمين عند الحاجة العامة، وإسقاط التكاليف التعجيزية بالقوة القاهرة. القاعدة الكبرى بالإجماع، الشاطبي، السيوطي، ابن نجيم، مجلة الأحكام العدلية (مادة 17 و 21) المشقة تجلب التيسير الضرورات تبيح المحظورات رفع الحرج القوة القاهرة إعادة جدولة الديون">
            
            <!-- Top Header with Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        30
                    </span>
                    <h4 class="text-base md:text-lg font-black text-emerald-300 leading-tight">
                        «الْمَشَقَّةُ تَجْلِبُ التَّيْسِيرَ · وَالضَّرُورَاتُ تُبِيحُ الْمَحْظُورَاتِ بِقَدْرِهَا»
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        ⚖️ الإثبات واستقرار المعاملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-400/50">
                        📖 نص قرآني في رفع الحرج والتيسير
                    </span>
                </div>
            </div>

            <!-- Evidence Calligraphy Box (النص المؤسس من القرآن أو الحديث) -->
            <div class="p-3.5 bg-slate-950/90 rounded-xl border border-emerald-500/30">
                <span class="text-xs text-amber-400 font-black block mb-1">📖 الأصل والنص الشرعي المؤسس للقاعدة:</span>
                <p class="text-sm md:text-base text-slate-100 font-bold leading-relaxed" style="font-family: 'Amiri', 'Cairo', serif; line-height: 1.9;">
                    قوله تعالى: ﴿يُرِيدُ اللَّهُ بِكُمُ الْيُسْرَ وَلَا يُرِيدُ بِكُمُ الْعُسْرَ﴾ [البقرة: 185]، وقوله: ﴿وَمَا جَعَلَ عَلَيْكُمْ فِي الدِّينِ مِنْ حَرَجٍ﴾ [الحج: 78]، وقوله: ﴿فَمَنِ اضْطُرَّ غَيْرَ بَاغٍ وَلَا عَادٍ فَلَا إِثْمَ عَلَيْهِ﴾ [البقرة: 173].
                </p>
            </div>

            <!-- Detailed Explanation & Economic Rationale -->
            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <span class="text-xs text-emerald-400 font-black block mb-0.5">📌 المعنى الفقهي والضابط الاقتصادي:</span>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الصعوبات والحرج والظروف الطارئة والاستثنائية تجلب التخفيف الشرعي والرخص المناسبة لإنقاذ رؤوس الأموال ومصالح المتعاقدين بقدر الحاجة دون توسع مفرط.
                </p>
            </div>

            <!-- Modern Accounting & Trade Application -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-sky-950/30 rounded-xl border border-sky-500/30">
                    <span class="text-sky-400 font-black block mb-0.5">💡 التطبيق المحاسبي والتجاري المعاصر:</span>
                    <span class="text-slate-200 font-bold leading-relaxed">إعادة جدولة ديون الشركات المتعثرة في الأزمات الاقتصادية، إقرار عقود التأمين عند الحاجة العامة، وإسقاط التكاليف التعجيزية بالقوة القاهرة.</span>
                </div>
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-amber-400 font-black block mb-0.5">🏛️ المقررون والمصادر الفقهية المعتمدة:</span>
                    <span class="text-slate-300 font-bold">القاعدة الكبرى بالإجماع، الشاطبي، السيوطي، ابن نجيم، مجلة الأحكام العدلية (مادة 17 و 21)</span>
                </div>
            </div>

        </div>

                    </div>

                    <div id="no-maxims-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد قواعد فقهية مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية القواعد.</p>
                    </div>
                </div>

                <div id="fiqh-sub-quran" class="space-y-4 hidden">
                    <div class="p-5 bg-emerald-950/40 border-2 border-emerald-500/50 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-black text-xs rounded-xl border border-emerald-400">
                                    ★ الدستور الإلهي الحاكم للمعاملات والديون والأموال
                                </span>
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded-xl border border-amber-400">
                                    38 آية قرآنية محكمة شاملة لكافة أبواب المال ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-emerald-300 flex items-center gap-2">
                                <i class="fa-solid fa-book-open-reader text-emerald-400"></i>
                                الموسوعة القرآنية الشاملة في المعاملات المالية، البيوع، الديون، الربا، الزكاة، والمواريث
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1">
                                تشمل: <span class="text-amber-300 font-black">المداينة والرهن</span> · <span class="text-sky-300 font-black">البيوع والتراضي</span> · <span class="text-rose-300 font-black">الربا والرشوة</span> · <span class="text-emerald-300 font-black">الزكاة والصدقات</span> · <span class="text-purple-300 font-black">المواريث والتركات</span> · <span class="text-cyan-300 font-black">الإجارة والشركات</span> · <span class="text-pink-300 font-black">أموال اليتامى</span> · <span class="text-yellow-300 font-black">كنز الأموال والاقتصاد الكلي</span>.
                            </p>
                        </div>

                        <!-- Instant Quranic Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في نصوص وتوجيهات الآيات:</label>
                            <input type="text" id="quran-search-input" oninput="filterQuranVerses()" placeholder="اكتب للبحث: دين، رهن، ربا، زكاة، ميراث، إجارة..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Quranic Topic Filter Bar 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-emerald-500/30 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black text-emerald-400 block w-full mb-1">
                            🏷️ تصفية الآيات حسب الموضوع المالي:
                        </span>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn active" data-qcat="all" onclick="setQuranTopic('all', this)">
                            🌟 جميع الآيات المالية (38)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="debts" onclick="setQuranTopic('debts', this)">
                            💳 الديون والتوثيق والرهن (3)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="trade" onclick="setQuranTopic('trade', this)">
                            ⚖️ البيوع والتجارة والموازين (9)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="riba" onclick="setQuranTopic('riba', this)">
                            🚫 الربا والرشوة وأكل المال بالباطل (5)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="zakat" onclick="setQuranTopic('zakat', this)">
                            💰 الزكاة المفروضة ومصارفها الثمانية (4)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="inheritance" onclick="setQuranTopic('inheritance', this)">
                            📜 المواريث وقسمة التركات (5)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="contracts" onclick="setQuranTopic('contracts', this)">
                            🤝 الإجارة والجعالة والشركات (5)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="orphans" onclick="setQuranTopic('orphans', this)">
                            🛡️ أموال اليتامى وحفظ الثروة (3)
                        </button>
                        <button type="button" class="quran-filter-btn fiqh-filter-btn" data-qcat="macro" onclick="setQuranTopic('macro', this)">
                            💎 كنز الأموال ودوران الثروة والاعتدال (4)
                        </button>
                    </div>

                    <!-- Verses Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="quran-verses-grid-container">

        <!-- Quranic Verse Card 1 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="آية المداينة الكبرى (أطول آية في القرآن الكريم - دستور توثيق الديون والالتزامات المالية) يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَاسْتَشْهِدُوا شَهِيدَيْنِ مِن رِّجَالِكُمْ ۖ فَإِن لَّمْ يَكُونَا رَجُلَيْنِ فَرَجُلٌ وَامْرَأَتَانِ مِمَّن تَرْضَوْنَ مِنَ الشُّهَدَاءِ أَن تَضِلَّ إِحْدَاهُمَا فَتُذَكِّرَ إِحْدَاهُمَا الْأُخْرَىٰ ۚ وَلَا يَأْبَ الشُّهَدَاءُ إِذَا مَا دُعُوا ۚ وَلَا تَسْأَمُوا أَن تَكْتُبُوهُ صَغِيرًا أَوْ كَبِيرًا إِلَىٰ أَجَلِهِ ۚ ذَٰلِكُمْ أَقْسَطُ عِندَ اللَّهِ وَأَقْوَمُ لِلشَّهَادَةِ وَأَدْنَىٰ أَلَّا تَرْتَابُوا ۖ إِلَّا أَن تَكُونَ تِجَارَةً حَاضِرَةً تُدِيرُونَهَا بَيْنَكُمْ فَلَيْسَ عَلَيْكُمْ جُنَاحٌ أَلَّا تَكْتُبُوهَا ۗ وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ... سورة البقرة: الآية 282 الأحكام الاقتصادية: وجوب/ندب توثيق الديون المؤجلة بالكتابة الرسمية، وتحديد أجل السداد بدقة، وإملاء المدين بنفسه اعترافاً بالحق، والإشهاد العدلي، واستثناء التجارة الحاضرة النقدية لتيسير حركة السوق وسرعة تداول السلع. الديون والتوثيق">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        آية المداينة الكبرى (أطول آية في القرآن الكريم - دستور توثيق الديون والالتزامات المالية)
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآية 282
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا تَدَايَنتُم بِدَيْنٍ إِلَىٰ أَجَلٍ مُّسَمًّى فَاكْتُبُوهُ ۚ وَلْيَكْتُب بَّيْنَكُمْ كَاتِبٌ بِالْعَدْلِ ۚ وَلَا يَأْبَ كَاتِبٌ أَن يَكْتُبَ كَمَا عَلَّمَهُ اللَّهُ ۚ فَلْيَكْتُبْ وَلْيُمْلِلِ الَّذِي عَلَيْهِ الْحَقُّ وَلْيَتَّقِ اللَّهَ رَبَّهُ وَلَا يَبْخَسْ مِنْهُ شَيْئًا... وَاسْتَشْهِدُوا شَهِيدَيْنِ مِن رِّجَالِكُمْ ۖ فَإِن لَّمْ يَكُونَا رَجُلَيْنِ فَرَجُلٌ وَامْرَأَتَانِ مِمَّن تَرْضَوْنَ مِنَ الشُّهَدَاءِ أَن تَضِلَّ إِحْدَاهُمَا فَتُذَكِّرَ إِحْدَاهُمَا الْأُخْرَىٰ ۚ وَلَا يَأْبَ الشُّهَدَاءُ إِذَا مَا دُعُوا ۚ وَلَا تَسْأَمُوا أَن تَكْتُبُوهُ صَغِيرًا أَوْ كَبِيرًا إِلَىٰ أَجَلِهِ ۚ ذَٰلِكُمْ أَقْسَطُ عِندَ اللَّهِ وَأَقْوَمُ لِلشَّهَادَةِ وَأَدْنَىٰ أَلَّا تَرْتَابُوا ۖ إِلَّا أَن تَكُونَ تِجَارَةً حَاضِرَةً تُدِيرُونَهَا بَيْنَكُمْ فَلَيْسَ عَلَيْكُمْ جُنَاحٌ أَلَّا تَكْتُبُوهَا ۗ وَأَشْهِدُوا إِذَا تَبَايَعْتُمْ...
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: وجوب/ندب توثيق الديون المؤجلة بالكتابة الرسمية، وتحديد أجل السداد بدقة، وإملاء المدين بنفسه اعترافاً بالحق، والإشهاد العدلي، واستثناء التجارة الحاضرة النقدية لتيسير حركة السوق وسرعة تداول السلع.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الديون والتوثيق</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 2 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="مشروعية الرهن الحيازي في السفر والديون وأداء الأمانات وتحريم كتمان الشهادة وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ سورة البقرة: الآية 283 الأحكام الاقتصادية: مشروعية أخذ الرهن لضمان استرداد الدين المالي عند تعذر التوثيق الكتابي، وكون الرهن مقبوضاً بيد الدائن، وتأكيد أداء الأمانة المالية للطرفين، وتحريم كتمان الشهادات المالية في المنازعات القضائية. الرهن والأمانات">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الرهن الحيازي في السفر والديون وأداء الأمانات وتحريم كتمان الشهادة
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآية 283
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَإِن كُنتُمْ عَلَىٰ سَفَرٍ وَلَمْ تَجِدُوا كَاتِبًا فَرِهَانٌ مَّقْبُوضَةٌ ۖ فَإِنْ أَمِنَ بَعْضُكُم بَعْضًا فَلْيُؤَدِّ الَّذِي اؤْتُمِنَ أَمَانَتَهُ وَلْيَتَّقِ اللَّهَ رَبَّهُ ۗ وَلَا تَكْتُمُوا الشَّهَادَةَ ۚ وَمَن يَكْتُمْهَا فَإِنَّهُ آثِمٌ قَلْبُهُ ۗ وَاللَّهُ بِمَا تَعْمَلُونَ عَلِيمٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: مشروعية أخذ الرهن لضمان استرداد الدين المالي عند تعذر التوثيق الكتابي، وكون الرهن مقبوضاً بيد الدائن، وتأكيد أداء الأمانة المالية للطرفين، وتحريم كتمان الشهادات المالية في المنازعات القضائية.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الرهن والأمانات</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 3 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="debts" data-ayah-search="وجوب إنظار المدين المعسر شرعاً وفضل التجاوز عنه بالإسقاط والصدقة وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ سورة البقرة: الآية 280 الأحكام الاقتصادية: وجوب تأجيل سداد الدين على المدين العاجز دون فرض أي غرامات تأخير أو فوائد إضافية، واستحباب التصدق بإبراء ذمة المدين كلياً أو جزئياً من باب الإحسان والمعونة الاجتماعية. إنظار المعسر">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب إنظار المدين المعسر شرعاً وفضل التجاوز عنه بالإسقاط والصدقة
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآية 280
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَإِن كَانَ ذُو عُسْرَةٍ فَنَظِرَةٌ إِلَىٰ مَيْسَرَةٍ ۚ وَأَن تَصَدَّقُوا خَيْرٌ لَّكُمْ ۖ إِن كُنتُمْ تَعْلَمُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: وجوب تأجيل سداد الدين على المدين العاجز دون فرض أي غرامات تأخير أو فوائد إضافية، واستحباب التصدق بإبراء ذمة المدين كلياً أو جزئياً من باب الإحسان والمعونة الاجتماعية.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: إنظار المعسر</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 4 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="القاعدة الكلية الكبرى: إحلال البيع والتجارة وتحريم الربا القاطع الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ سورة البقرة: الآية 275 الأحكام الاقتصادية: التفريق الجذري بين الربح الناتج عن بيع السلع والخدمات وتحمل المخاطر وتداول الأموال الحقيقية (المباح)، وبين الزيادة المشروطة على القرض والنقد بلا عوض ولا مخاطرة (المحرم قطيعاً). إحلال البيع">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        القاعدة الكلية الكبرى: إحلال البيع والتجارة وتحريم الربا القاطع
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآية 275
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                الَّذِينَ يَأْكُلُونَ الرِّبَا لَا يَقُومُونَ إِلَّا كَمَا يَقُومُ الَّذِي يَتَخَبَّطُهُ الشَّيْطَانُ مِنَ الْمَسِّ ۚ ذَٰلِكَ بِأَنَّهُمْ قَالُوا إِنَّمَا الْبَيْعُ مِثْلُ الرِّبَا ۗ وَأَحَلَّ اللَّهُ الْبَيْعَ وَحَرَّمَ الرِّبَا ۚ فَمَن جَاءَهُ مَوْعِظَةٌ مِّن رَّبِّهِ فَانتَهَىٰ فَلَهُ مَا سَلَفَ وَأَمْرُهُ إِلَى اللَّهِ ۖ وَمَنْ عَادَ فَأُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: التفريق الجذري بين الربح الناتج عن بيع السلع والخدمات وتحمل المخاطر وتداول الأموال الحقيقية (المباح)، وبين الزيادة المشروطة على القرض والنقد بلا عوض ولا مخاطرة (المحرم قطيعاً).
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: إحلال البيع</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 5 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="ركن التراضي التام في العقود وتحريم أكل أموال الناس بالباطل يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا سورة النساء: الآية 29 الأحكام الاقتصادية: بطلان أي معاملة مالية قائمة على الغصب أو التدليس أو الغرر أو الإكراه، واشتراط التراضي الحقيقي والإيجاب والقبول الصحيح بين المتبايعين لصحة انتقال الملكية. التراضي في التجارة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        ركن التراضي التام في العقود وتحريم أكل أموال الناس بالباطل
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 29
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ إِلَّا أَن تَكُونَ تِجَارَةً عَن تَرَاضٍ مِّنكُمْ ۚ وَلَا تَقْتُلُوا أَنفُسَكُمْ ۚ إِنَّ اللَّهَ كَانَ بِكُمْ رَحِيمًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: بطلان أي معاملة مالية قائمة على الغصب أو التدليس أو الغرر أو الإكراه، واشتراط التراضي الحقيقي والإيجاب والقبول الصحيح بين المتبايعين لصحة انتقال الملكية.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: التراضي في التجارة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 6 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="الأمر الإلهي القطعي بالوفاء بكافة العقود والعهود والالتزامات التجارية يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ... سورة المائدة: الآية 1 الأحكام الاقتصادية: قاعدة 'العقد شريعة المتعاقدين' في الفقه الإسلامي؛ وجوب الالتزام التام بكافة بنود وشروط عقود التوريد والمقاولات والوكالة والشراكة والبيع ما لم تحرم حلالاً أو تحل حراماً. الوفاء بالعقود">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأمر الإلهي القطعي بالوفاء بكافة العقود والعهود والالتزامات التجارية
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة المائدة: الآية 1
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَا أَيُّهَا الَّذِينَ آمَنُوا أَوْفُوا بِالْعُقُودِ ۚ أُحِلَّتْ لَكُم بَهِيمَةُ الْأَنْعَامِ إِلَّا مَا يُتْلَىٰ عَلَيْكُمْ...
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: قاعدة 'العقد شريعة المتعاقدين' في الفقه الإسلامي؛ وجوب الالتزام التام بكافة بنود وشروط عقود التوريد والمقاولات والوكالة والشراكة والبيع ما لم تحرم حلالاً أو تحل حراماً.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الوفاء بالعقود</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 7 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="إيفاء الكيل والميزان بالقسطاس المستقيم وأثره في بركة الاقتصاد وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا سورة الإسراء: الآية 35 الأحكام الاقتصادية: وجوب دقة المعايير والمكاييل والأوزان والمواصفات القياسية الهندسية والتجارية في تسليم البضائع والسلع، واعتبار العدل أساس استقرار الأسواق. الموازين والمكاييل">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إيفاء الكيل والميزان بالقسطاس المستقيم وأثره في بركة الاقتصاد
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الإسراء: الآية 35
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَأَوْفُوا الْكَيْلَ إِذَا كِلْتُمْ وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ ۚ ذَٰلِكَ خَيْرٌ وَأَحْسَنُ تَأْوِيلًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: وجوب دقة المعايير والمكاييل والأوزان والمواصفات القياسية الهندسية والتجارية في تسليم البضائع والسلع، واعتبار العدل أساس استقرار الأسواق.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الموازين والمكاييل</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 8 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="الوعيد الشديد والهلاك للمطففين الذين ينقصون في الكيل والوزن والمواصفات وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ سورة المطففين: الآيات 1-3 الأحكام الاقتصادية: تجريم الازدواجية والمخادعة في البيع والشراء؛ أن يطلب حقه كاملاً في الشراء وينقص حقوق الناس عند البيع والتسليم في الجودة أو الكمية أو الوزن. تحريم التطفيف">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الوعيد الشديد والهلاك للمطففين الذين ينقصون في الكيل والوزن والمواصفات
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة المطففين: الآيات 1-3
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَيْلٌ لِّلْمُطَفِّفِينَ * الَّذِينَ إِذَا اكْتَالُوا عَلَى النَّاسِ يَسْتَوْفُونَ * وَإِذَا كَالُوهُمْ أَو وَّزَنُوهُمْ يُخْسِرُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تجريم الازدواجية والمخادعة في البيع والشراء؛ أن يطلب حقه كاملاً في الشراء وينقص حقوق الناس عند البيع والتسليم في الجودة أو الكمية أو الوزن.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: تحريم التطفيف</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 9 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="النهي عن بخس الناس أشياءهم وبخس مجهوداتهم والفساد في الأسواق فَأَوْفُوا الْكَيْلَ وَالْمِيزَانَ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تُفْسِدُوا فِي الْأَرْضِ بَعْدَ إِصْلَاحِهَا ۚ ذَٰلِكُمْ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ سورة الأعراف: الآية 85 الأحكام الاقتصادية: تحريم التقليل المصطنع من قيمة سلع الناس أو خدماتهم أو كفاءاتهم لشرائها بأقل من قيمتها العادلة، والنهي عن الاحتكار والممارسات الاحتكارية الضارة بالسوق. عدم بخس السلع">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بخس الناس أشياءهم وبخس مجهوداتهم والفساد في الأسواق
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الأعراف: الآية 85
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                فَأَوْفُوا الْكَيْلَ وَالْمِيزَانَ وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تُفْسِدُوا فِي الْأَرْضِ بَعْدَ إِصْلَاحِهَا ۚ ذَٰلِكُمْ خَيْرٌ لَّكُمْ إِن كُنتُم مُّؤْمِنِينَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تحريم التقليل المصطنع من قيمة سلع الناس أو خدماتهم أو كفاءاتهم لشرائها بأقل من قيمتها العادلة، والنهي عن الاحتكار والممارسات الاحتكارية الضارة بالسوق.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: عدم بخس السلع</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 10 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="وجوب أداء الأمانات المالية إلى أصحابها والحكم بالعدل في المعاملات إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا سورة النساء: الآية 58 الأحكام الاقتصادية: إلزامية رد الودائع، وحفظ أموال الشركاء والمساهمين، وسداد مستحقات الموردين والعمال دون مماطلة، والعدل في الفصل في النزاعات التجارية والتحكيم المالي. أداء الأمانات">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب أداء الأمانات المالية إلى أصحابها والحكم بالعدل في المعاملات
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 58
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                إِنَّ اللَّهَ يَأْمُرُكُمْ أَن تُؤَدُّوا الْأَمَانَاتِ إِلَىٰ أَهْلِهَا وَإِذَا حَكَمْتُم بَيْنَ النَّاسِ أَن تَحْكُمُوا بِالْعَدْلِ ۚ إِنَّ اللَّهَ نِعِمَّا يَعِظُكُم بِهِ ۗ إِنَّ اللَّهَ كَانَ سَمِيعًا بَصِيرًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: إلزامية رد الودائع، وحفظ أموال الشركاء والمساهمين، وسداد مستحقات الموردين والعمال دون مماطلة، والعدل في الفصل في النزاعات التجارية والتحكيم المالي.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: أداء الأمانات</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 11 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="التعاون على البر والتقوى: الأصل القرآني للشركات التعاونية والتأمين التكافلي وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ سورة المائدة: الآية 2 الأحكام الاقتصادية: تأصيل العمل التكافلي التعاوني، وصناديق الضمان الاجتماعي، والتأمين الإسلامي القائم على التعاون والتبرع المتبادل دون ربا ولا قمار. التأمين التكافلي والتعاون">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        التعاون على البر والتقوى: الأصل القرآني للشركات التعاونية والتأمين التكافلي
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة المائدة: الآية 2
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ ۖ وَلَا تَعَاوَنُوا عَلَى الْإِثْمِ وَالْعُدْوَانِ ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تأصيل العمل التكافلي التعاوني، وصناديق الضمان الاجتماعي، والتأمين الإسلامي القائم على التعاون والتبرع المتبادل دون ربا ولا قمار.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: التأمين التكافلي والتعاون</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 12 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="trade" data-ayah-search="الأمر بإيفاء الكيل والميزان الصحيح وعدم الإفساد الاقتصادي في الأرض أَوْفُوا الْكَيْلَ وَلَا تَكُونُوا مِنَ الْمُخْسِرِينَ * وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ * وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ سورة الشعراء: الآيات 181-183 الأحكام الاقتصادية: ربط سلامة الممارسات التجارية والمعايير الفنية بالإيمان وتقوى الله، والتحذير من أن الفساد التجاري والتلاعب بالموازين يؤدي إلى هلاك الأمم وزوال النعم. الكيل الصادق">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأمر بإيفاء الكيل والميزان الصحيح وعدم الإفساد الاقتصادي في الأرض
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الشعراء: الآيات 181-183
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                أَوْفُوا الْكَيْلَ وَلَا تَكُونُوا مِنَ الْمُخْسِرِينَ * وَزِنُوا بِالْقِسْطَاسِ الْمُسْتَقِيمِ * وَلَا تَبْخَسُوا النَّاسَ أَشْيَاءَهُمْ وَلَا تَعْثَوْا فِي الْأَرْضِ مُفْسِدِينَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: ربط سلامة الممارسات التجارية والمعايير الفنية بالإيمان وتقوى الله، والتحذير من أن الفساد التجاري والتلاعب بالموازين يؤدي إلى هلاك الأمم وزوال النعم.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الكيل الصادق</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 13 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="إعلان الحرب الإلهية على أكلة الربا وحق استرداد رأس المال الأصلي فقط يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ سورة البقرة: الآيات 278-279 الأحكام الاقتصادية: أعظم تحذير مالي في الشريعة، وبطلان الزيادة الربوية في القروض، وحق الدائن التائب في استرجاع رأس ماله الأساسي فقط دون زيادة (ظلم للمدين) ودون نقصان (ظلم للدائن). تحريم الربا القاطع">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إعلان الحرب الإلهية على أكلة الربا وحق استرداد رأس المال الأصلي فقط
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآيات 278-279
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ وَذَرُوا مَا بَقِيَ مِنَ الرِّبَا إِن كُنتُم مُّؤْمِنِينَ * فَإِن لَّمْ تَفْعَلُوا فَأْذَنُوا بِحَرْبٍ مِّنَ اللَّهِ وَرَسُولِهِ ۖ وَإِن تُبْتُمْ فَلَكُمْ رُءُوسُ أَمْوَالِكُمْ لَا تَظْلِمُونَ وَلَا تُظْلَمُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: أعظم تحذير مالي في الشريعة، وبطلان الزيادة الربوية في القروض، وحق الدائن التائب في استرجاع رأس ماله الأساسي فقط دون زيادة (ظلم للمدين) ودون نقصان (ظلم للدائن).
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: تحريم الربا القاطع</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 14 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="سنة الله في محق الربا وتدمير أرباحه ونماء أموال الصدقات والبركات يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ سورة البقرة: الآية 276 الأحكام الاقتصادية: الحقيقة الاقتصادية الإلهية بزوال بركة المال الربوي وانهياره في النهاية عبر الأزمات والتضخم، ونماء الأموال الحلال المزكاة وحمايتها من التلف والجوائح. محق الربا وبركة الصدقات">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        سنة الله في محق الربا وتدمير أرباحه ونماء أموال الصدقات والبركات
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآية 276
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَمْحَقُ اللَّهُ الرِّبَا وَيُرْبِي الصَّدَقَاتِ ۗ وَاللَّهُ لَا يُحِبُّ كُلَّ كَفَّارٍ أَثِيمٍ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: الحقيقة الاقتصادية الإلهية بزوال بركة المال الربوي وانهياره في النهاية عبر الأزمات والتضخم، ونماء الأموال الحلال المزكاة وحمايتها من التلف والجوائح.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: محق الربا وبركة الصدقات</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 15 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="النهي الصريح عن ربا الديون وتراكم الفوائد أضعافاً مضاعفة يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا الرِّبَا أَضْعَافًا مُّضَاعَفَةً ۖ وَاتَّقُوا اللَّهَ لَعَلَّكُمْ تُفْلِحُونَ سورة آل عمران: الآية 130 الأحكام الاقتصادية: تحريم ربا الجاهلية المتمثل في مضاعفة الديون عند عجز المدين عن السداد في الأجل، وهو ما تطبقه البنوك الربوية المعاصرة في الفوائد التراكمية المركبة. الربا المضاعف">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي الصريح عن ربا الديون وتراكم الفوائد أضعافاً مضاعفة
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة آل عمران: الآية 130
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَا أَيُّهَا الَّذِينَ آمَنُوا لَا تَأْكُلُوا الرِّبَا أَضْعَافًا مُّضَاعَفَةً ۖ وَاتَّقُوا اللَّهَ لَعَلَّكُمْ تُفْلِحُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تحريم ربا الجاهلية المتمثل في مضاعفة الديون عند عجز المدين عن السداد في الأجل، وهو ما تطبقه البنوك الربوية المعاصرة في الفوائد التراكمية المركبة.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الربا المضاعف</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 16 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="تحريم الرشوة والوساطة الباطلة لأكل أموال الناس واستباحة الحقوق وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ سورة البقرة: الآية 188 الأحكام الاقتصادية: تجريم دفع الرشاوى والعمولات الخفية للمسؤولين أو القضاة للاستيلاء على مناقصات أو أراضٍ أو عقارات بغير وجه حق، وبطلان كل عقد استند إلى رشوة. تحريم الرشوة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        16
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الرشوة والوساطة الباطلة لأكل أموال الناس واستباحة الحقوق
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة البقرة: الآية 188
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَلَا تَأْكُلُوا أَمْوَالَكُم بَيْنَكُم بِالْبَاطِلِ وَتُدْلُوا بِهَا إِلَى الْحُكَّامِ لِتَأْكُلُوا فَرِيقًا مِّنْ أَمْوَالِ النَّاسِ بِالْإِثْمِ وَأَنتُمْ تَعْلَمُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تجريم دفع الرشاوى والعمولات الخفية للمسؤولين أو القضاة للاستيلاء على مناقصات أو أراضٍ أو عقارات بغير وجه حق، وبطلان كل عقد استند إلى رشوة.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: تحريم الرشوة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 17 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="riba" data-ayah-search="حقيقة المال الربوي في ميزان الله ومضاعفة أجر الصدقة الخالصة وَمَا آتَيْتُم مِّن رِّبًا لِّيَرْبُوَ فِي أَمْوَالِ النَّاسِ فَلَا يَرْبُو عِندَ اللَّهِ ۖ وَمَا آتَيْتُم مِّن زَكَاةٍ تُرِيدُونَ وَجْهَ اللَّهِ فَأُولَٰئِكَ هُمُ الْمُضْعِفُونَ سورة الروم: الآية 39 الأحكام الاقتصادية: بيان أن الفوائد المالية الربوية لا تحقق نمواً حقيقياً مستداماً في الثروة ولا بركة فيها، بينما الزكاة والصدقات هي التي تضاعف الثروات وتنشط الطلب الحقيقي. الربا المالي المحرم">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        17
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حقيقة المال الربوي في ميزان الله ومضاعفة أجر الصدقة الخالصة
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الروم: الآية 39
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَمَا آتَيْتُم مِّن رِّبًا لِّيَرْبُوَ فِي أَمْوَالِ النَّاسِ فَلَا يَرْبُو عِندَ اللَّهِ ۖ وَمَا آتَيْتُم مِّن زَكَاةٍ تُرِيدُونَ وَجْهَ اللَّهِ فَأُولَٰئِكَ هُمُ الْمُضْعِفُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: بيان أن الفوائد المالية الربوية لا تحقق نمواً حقيقياً مستداماً في الثروة ولا بركة فيها، بينما الزكاة والصدقات هي التي تضاعف الثروات وتنشط الطلب الحقيقي.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الربا المالي المحرم</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 18 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="zakat" data-ayah-search="دستور مصارف الزكاة الثمانية الحصرية في الشريعة الإسلامية إِنَّمَا الصَّدَقَاتُ لِلْفُقَرَاءِ وَالْمَسَاكِينِ وَالْعَامِلِينَ عَلَيْهَا وَالْمُؤَلَّفَةِ قُلُوبُهُمْ وَفِي الرِّقَابِ وَالْغَارِمِينَ وَفِي سَبِيلِ اللَّهِ وَابْنِ السَّبِيلِ ۖ فَرِيضَةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَكِيمٌ سورة التوبة: الآية 60 الأحكام الاقتصادية: حصر مصارف الزكاة المفروضة في الأصناف الثمانية بدقة تشريعية، وأبرزهم الغارمون (المدينون العاجزون عن سداد ديونهم المشروعة)، وإعادة تدوير الثروة لتأمين الحماية الاجتماعية. مصارف الزكاة الثمانية">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        18
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور مصارف الزكاة الثمانية الحصرية في الشريعة الإسلامية
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة التوبة: الآية 60
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                إِنَّمَا الصَّدَقَاتُ لِلْفُقَرَاءِ وَالْمَسَاكِينِ وَالْعَامِلِينَ عَلَيْهَا وَالْمُؤَلَّفَةِ قُلُوبُهُمْ وَفِي الرِّقَابِ وَالْغَارِمِينَ وَفِي سَبِيلِ اللَّهِ وَابْنِ السَّبِيلِ ۖ فَرِيضَةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَكِيمٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: حصر مصارف الزكاة المفروضة في الأصناف الثمانية بدقة تشريعية، وأبرزهم الغارمون (المدينون العاجزون عن سداد ديونهم المشروعة)، وإعادة تدوير الثروة لتأمين الحماية الاجتماعية.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: مصارف الزكاة الثمانية</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 19 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="zakat" data-ayah-search="وجوب جباية الزكاة وأثرها في تطهير النفوس ونماء الأموال خُذْ مِنْ أَمْوَالِهِمْ صَدَقَةً تُطَهِّرُهُمْ وَتُزَكِّيهِم بِهَا وَصَلِّ عَلَيْهِمْ ۖ إِنَّ صَلَاتَكَ سَكَنٌ لَّهُمْ ۗ وَاللَّهُ سَمِيعٌ عَلِيمٌ سورة التوبة: الآية 103 الأحكام الاقتصادية: الزكاة ليست منحة اختيارية بل حق مالي واجب تؤخذ من الأغنياء لدعم استقرار المجتمع وتطهير المال من الشبهات، ونماء المال بالبركة والتوسعة على المحتاجين. وجوب أخذ الزكاة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        19
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب جباية الزكاة وأثرها في تطهير النفوس ونماء الأموال
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة التوبة: الآية 103
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                خُذْ مِنْ أَمْوَالِهِمْ صَدَقَةً تُطَهِّرُهُمْ وَتُزَكِّيهِم بِهَا وَصَلِّ عَلَيْهِمْ ۖ إِنَّ صَلَاتَكَ سَكَنٌ لَّهُمْ ۗ وَاللَّهُ سَمِيعٌ عَلِيمٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: الزكاة ليست منحة اختيارية بل حق مالي واجب تؤخذ من الأغنياء لدعم استقرار المجتمع وتطهير المال من الشبهات، ونماء المال بالبركة والتوسعة على المحتاجين.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: وجوب أخذ الزكاة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 20 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="zakat" data-ayah-search="تقرير الحق المالي المعلوم في أموال الأغنياء للسائل والمحروم وَالَّذِينَ فِي أَمْوَالِهِمْ حَقٌّ مَّعْلُومٌ * لِّلسَّائِلِ وَالْمَحْرُومِ سورة المعارج: الآيات 24-25 الأحكام الاقتصادية: ترسيخ مبدأ 'الحق المعلوم' وهو نسبة الزكاة المحددة شرعاً (ربع العشر 2.5% في النقدين وعروض التجارة)، وحق الفقير المتعفف الذي لا يسأل الناس إلحافاً. حق معلوم في الأموال">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        20
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تقرير الحق المالي المعلوم في أموال الأغنياء للسائل والمحروم
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة المعارج: الآيات 24-25
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَالَّذِينَ فِي أَمْوَالِهِمْ حَقٌّ مَّعْلُومٌ * لِّلسَّائِلِ وَالْمَحْرُومِ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: ترسيخ مبدأ 'الحق المعلوم' وهو نسبة الزكاة المحددة شرعاً (ربع العشر 2.5% في النقدين وعروض التجارة)، وحق الفقير المتعفف الذي لا يسأل الناس إلحافاً.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: حق معلوم في الأموال</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 21 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="zakat" data-ayah-search="إيتاء زكاة الإنتاج الزراعي والثمار يوم الحصاد وجوباً وَهُوَ الَّذِي أَنشَأَ جَنَّاتٍ مَّعْرُوشَاتٍ وَغَيْرَ مَعْرُوشَاتٍ وَالنَّخْلَ وَالزَّرْعَ مُخْتَلِفًا أُكُلُهُ وَالزَّيْتُونَ وَالرُّمَّانَ مُتَشَابِهًا وَغَيْرَ مُتَشَابِهٍ ۚ كُلُوا مِن ثَمَرِهِ إِذَا أَثْمَرَ وَآتُوا حَقَّهُ يَوْمَ حَصَادِهِ ۖ وَلَا تُسْرِفُوا ۚ إِنَّهُ لَا يُحِبُّ الْمُسْرِفِينَ سورة الأنعام: الآية 141 الأحكام الاقتصادية: وجوب إخراج زكاة المحاصيل الزراعية فور الحصاد دون انتظار حولان الحول، بمقدار 10% فيما سقي بلا كلفة (ماء المطر) و5% فيما سقي بكلفة وآلات. زكاة الزروع والثمار">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        21
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إيتاء زكاة الإنتاج الزراعي والثمار يوم الحصاد وجوباً
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الأنعام: الآية 141
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَهُوَ الَّذِي أَنشَأَ جَنَّاتٍ مَّعْرُوشَاتٍ وَغَيْرَ مَعْرُوشَاتٍ وَالنَّخْلَ وَالزَّرْعَ مُخْتَلِفًا أُكُلُهُ وَالزَّيْتُونَ وَالرُّمَّانَ مُتَشَابِهًا وَغَيْرَ مُتَشَابِهٍ ۚ كُلُوا مِن ثَمَرِهِ إِذَا أَثْمَرَ وَآتُوا حَقَّهُ يَوْمَ حَصَادِهِ ۖ وَلَا تُسْرِفُوا ۚ إِنَّهُ لَا يُحِبُّ الْمُسْرِفِينَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: وجوب إخراج زكاة المحاصيل الزراعية فور الحصاد دون انتظار حولان الحول، بمقدار 10% فيما سقي بلا كلفة (ماء المطر) و5% فيما سقي بكلفة وآلات.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: زكاة الزروع والثمار</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 22 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="inheritance" data-ayah-search="دستور قسمة المواريث الأول: أنصبة الأولاد والوالدين وتقديم الدين والوصية يُوصِيكُمُ اللَّهُ فِي أَوْلَادِكُمْ ۖ لِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۚ فَإِن كُنَّ نِسَاءً فَوْقَ اثْنَتَيْنِ فَلَهُنَّ ثُلُثَا مَا تَرَكَ ۖ وَإِن كَانَتْ وَاحِدَةً فَلَهَا النِّصْفُ ۚ وَلِأَبَوَيْهِ لِكُلِّ وَاحِدٍ مِّنْهُمَا السُّدُسُ مِمَّا تَرَكَ إِن كَانَ لَهُ وَلَدٌ... مِن بَعْدِ وَصِيَّةٍ يُوصِي بِهَا أَوْ دَيْنٍ ۗ آبَاؤُكُمْ وَأَبْنَاؤُكُمْ لَا تَدْرُونَ أَيُّهُمْ أَقْرَبُ لَكُمْ نَفْعًا ۚ فَرِيضَةً مِّنَ اللَّهِ ۗ إِنَّ اللَّهَ كَانَ عَلِيمًا حَكِيمًا سورة النساء: الآية 11 الأحكام الاقتصادية: توزيع التركات بأنصبة محكمة قطعية الدلالة تمنع النزاع العائلي، وقاعدة تقديم سداد ديون المتوفى أولاً ثم تنفيذ الوصية المشروعة (في حدود ثلث التركة لغير وارث) قبل قسمة التركة. مواريث الأولاد والوالدين">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        22
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور قسمة المواريث الأول: أنصبة الأولاد والوالدين وتقديم الدين والوصية
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 11
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يُوصِيكُمُ اللَّهُ فِي أَوْلَادِكُمْ ۖ لِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۚ فَإِن كُنَّ نِسَاءً فَوْقَ اثْنَتَيْنِ فَلَهُنَّ ثُلُثَا مَا تَرَكَ ۖ وَإِن كَانَتْ وَاحِدَةً فَلَهَا النِّصْفُ ۚ وَلِأَبَوَيْهِ لِكُلِّ وَاحِدٍ مِّنْهُمَا السُّدُسُ مِمَّا تَرَكَ إِن كَانَ لَهُ وَلَدٌ... مِن بَعْدِ وَصِيَّةٍ يُوصِي بِهَا أَوْ دَيْنٍ ۗ آبَاؤُكُمْ وَأَبْنَاؤُكُمْ لَا تَدْرُونَ أَيُّهُمْ أَقْرَبُ لَكُمْ نَفْعًا ۚ فَرِيضَةً مِّنَ اللَّهِ ۗ إِنَّ اللَّهَ كَانَ عَلِيمًا حَكِيمًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: توزيع التركات بأنصبة محكمة قطعية الدلالة تمنع النزاع العائلي، وقاعدة تقديم سداد ديون المتوفى أولاً ثم تنفيذ الوصية المشروعة (في حدود ثلث التركة لغير وارث) قبل قسمة التركة.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: مواريث الأولاد والوالدين</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 23 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="inheritance" data-ayah-search="دستور قسمة المواريث الثاني: أنصبة الأزواج والزوجات والإخوة لأم والنهي عن الإضرار وَلَكُمْ نِصْفُ مَا تَرَكَ أَزْوَاجُكُمْ إِن لَّمْ يَكُن لَّهُنَّ وَلَدٌ ۚ فَإِن كَانَ لَهُنَّ وَلَدٌ فَلَكُمُ الرُّبُعُ مِمَّا تَرَكْنَ ۚ مِن بَعْدِ وَصِيَّةٍ يُوصِينَ بِهَا أَوْ دَيْنٍ ۚ وَلَهُنَّ الرُّبُعُ مِمَّا تَرَكْتُمْ إِن لَّمْ يَكُن لَّكُمْ وَلَدٌ ۚ فَإِن كَانَ لَكُمْ وَلَدٌ فَلَهُنَّ الثُّمُنُ مِمَّا تَرَكْتُم ۚ مِن بَعْدِ وَصِيَّةٍ تُوصُونَ بِهَا أَوْ دَيْنٍ... غَيْرَ مُضَارٍّ ۚ وَصِيَّةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَلِيمٌ سورة النساء: الآية 12 الأحكام الاقتصادية: تفصيل أنصبة الزوج والزوجة في التركة حال وجود الولد وحال عدمه، وتحريم المضارة في الوصية كالإقرار بديون مصطنعة أو حرمان الورثة الشرعيين. مواريث الأزواج والإخوة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        23
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور قسمة المواريث الثاني: أنصبة الأزواج والزوجات والإخوة لأم والنهي عن الإضرار
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 12
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَلَكُمْ نِصْفُ مَا تَرَكَ أَزْوَاجُكُمْ إِن لَّمْ يَكُن لَّهُنَّ وَلَدٌ ۚ فَإِن كَانَ لَهُنَّ وَلَدٌ فَلَكُمُ الرُّبُعُ مِمَّا تَرَكْنَ ۚ مِن بَعْدِ وَصِيَّةٍ يُوصِينَ بِهَا أَوْ دَيْنٍ ۚ وَلَهُنَّ الرُّبُعُ مِمَّا تَرَكْتُمْ إِن لَّمْ يَكُن لَّكُمْ وَلَدٌ ۚ فَإِن كَانَ لَكُمْ وَلَدٌ فَلَهُنَّ الثُّمُنُ مِمَّا تَرَكْتُم ۚ مِن بَعْدِ وَصِيَّةٍ تُوصُونَ بِهَا أَوْ دَيْنٍ... غَيْرَ مُضَارٍّ ۚ وَصِيَّةً مِّنَ اللَّهِ ۗ وَاللَّهُ عَلِيمٌ حَلِيمٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تفصيل أنصبة الزوج والزوجة في التركة حال وجود الولد وحال عدمه، وتحريم المضارة في الوصية كالإقرار بديون مصطنعة أو حرمان الورثة الشرعيين.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: مواريث الأزواج والإخوة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 24 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="inheritance" data-ayah-search="فرائض المواريث حدود إلهية ملزمة والوعيد الشديد لمن يعتدي عليها ويحرم الورثة تِلْكَ حُدُودُ اللَّهِ ۚ وَمَن يُطِعِ اللَّهَ وَرَسُولَهُ يُدْخِلْهُ جَنَّاتٍ تَجْرِي مِن تَحْتِهَا الْأَنْهَارُ خَالِدِينَ فِيهَا ۚ وَذَٰلِكَ الْفَوْزُ الْعَظِيمُ * وَمَن يَعْصِ اللَّهَ وَيَتَعَدَّ حُدُودَهُ يُدْخِلْهُ نَارًا خَالِدًا فِيهَا وَلَهُ عَذَابٌ مُّهِينٌ سورة النساء: الآيات 13-14 الأحكام الاقتصادية: تأكيد أن قسمة المواريث ليست خاضعة للأهواء ولا التقاليد القبلية؛ فتحريم حرمان البنات أو الزوجات أو الإخوة من حقهم المالي المفروض، والوعيد بالنار لمن يتعدى حدود القسمة. حدود الله في الميراث">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        24
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فرائض المواريث حدود إلهية ملزمة والوعيد الشديد لمن يعتدي عليها ويحرم الورثة
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآيات 13-14
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                تِلْكَ حُدُودُ اللَّهِ ۚ وَمَن يُطِعِ اللَّهَ وَرَسُولَهُ يُدْخِلْهُ جَنَّاتٍ تَجْرِي مِن تَحْتِهَا الْأَنْهَارُ خَالِدِينَ فِيهَا ۚ وَذَٰلِكَ الْفَوْزُ الْعَظِيمُ * وَمَن يَعْصِ اللَّهَ وَيَتَعَدَّ حُدُودَهُ يُدْخِلْهُ نَارًا خَالِدًا فِيهَا وَلَهُ عَذَابٌ مُّهِينٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تأكيد أن قسمة المواريث ليست خاضعة للأهواء ولا التقاليد القبلية؛ فتحريم حرمان البنات أو الزوجات أو الإخوة من حقهم المالي المفروض، والوعيد بالنار لمن يتعدى حدود القسمة.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: حدود الله في الميراث</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 25 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="inheritance" data-ayah-search="ميراث الكلالة: أحكام الإخوة والأخوات الأشقاء ولأب عند عدم الأصول والفروع يَسْتَفْتُونَكَ قُلِ اللَّهُ يُفْتِيكُمْ فِي الْكَلَالَةِ ۚ إِنِ امْرُؤٌ هَلَكَ لَيْسَ لَهُ وَلَدٌ وَلَهُ أُخْتٌ فَلَهَا نِصْفُ مَا تَرَكَ ۚ وَهُوَ يَرِثُهَا إِن لَّمْ يَكُن لَّهَا وَلَدٌ ۚ فَإِن كَانَتَا اثْنَتَيْنِ فَلَهُمَا الثُّلُثَانِ مِمَّا تَرَكَ ۚ وَإِن كَانُوا إِخْوَةً رِّجَالًا وَنِسَاءً فَلِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۗ يُبَيِّنُ اللَّهُ لَكُمْ أَن تَضِلُّوا ۗ وَاللَّهُ بِكُلِّ شَيْءٍ عَلِيمٌ سورة النساء: الآية 176 الأحكام الاقتصادية: أحكام توريث الإخوة والأخوات للمتوفى الذي ليس له ولد يرثه ولا والد (الكلالة)، واكتمال منظومة الفرائض المالية التي لا تترك مالاً ضائعاً أو مشاعاً. ميراث الكلالة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        25
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        ميراث الكلالة: أحكام الإخوة والأخوات الأشقاء ولأب عند عدم الأصول والفروع
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 176
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                يَسْتَفْتُونَكَ قُلِ اللَّهُ يُفْتِيكُمْ فِي الْكَلَالَةِ ۚ إِنِ امْرُؤٌ هَلَكَ لَيْسَ لَهُ وَلَدٌ وَلَهُ أُخْتٌ فَلَهَا نِصْفُ مَا تَرَكَ ۚ وَهُوَ يَرِثُهَا إِن لَّمْ يَكُن لَّهَا وَلَدٌ ۚ فَإِن كَانَتَا اثْنَتَيْنِ فَلَهُمَا الثُّلُثَانِ مِمَّا تَرَكَ ۚ وَإِن كَانُوا إِخْوَةً رِّجَالًا وَنِسَاءً فَلِلذَّكَرِ مِثْلُ حَظِّ الْأُنثَيَيْنِ ۗ يُبَيِّنُ اللَّهُ لَكُمْ أَن تَضِلُّوا ۗ وَاللَّهُ بِكُلِّ شَيْءٍ عَلِيمٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: أحكام توريث الإخوة والأخوات للمتوفى الذي ليس له ولد يرثه ولا والد (الكلالة)، واكتمال منظومة الفرائض المالية التي لا تترك مالاً ضائعاً أو مشاعاً.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: ميراث الكلالة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 26 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="inheritance" data-ayah-search="إثبات استقلال الذمة المالية للرجال والنساء وحقهم القطعي في التركات لِّلرِّجَالِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ وَلِلنِّسَاءِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ مِمَّا قَلَّ مِنْهُ أَوْ كَثُرَ ۚ نَصِيبًا مَّفْرُوضًا سورة النساء: الآية 7 الأحكام الاقتصادية: تقرير قاعدة العدالة المالية التامة؛ أن المال الموروث حق مقدر للرجل والمرأة مهما صغر حجم المال أو كبر، وإبطال حرمان الصغار والنساء من الثروات. حق النساء والرجال في التركة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        26
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إثبات استقلال الذمة المالية للرجال والنساء وحقهم القطعي في التركات
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 7
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                لِّلرِّجَالِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ وَلِلنِّسَاءِ نَصِيبٌ مِّمَّا تَرَكَ الْوَالِدَانِ وَالْأَقْرَبُونَ مِمَّا قَلَّ مِنْهُ أَوْ كَثُرَ ۚ نَصِيبًا مَّفْرُوضًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تقرير قاعدة العدالة المالية التامة؛ أن المال الموروث حق مقدر للرجل والمرأة مهما صغر حجم المال أو كبر، وإبطال حرمان الصغار والنساء من الثروات.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: حق النساء والرجال في التركة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 27 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="معيارا التوظيف والعمل في عقود الإجارة: الكفاءة الفنية والنزاهة والأمانة قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ سورة القصص: الآية 26 الأحكام الاقتصادية: مشروعية عقد الإجارة واستئجار الأشخاص للأعمال الهندسية والتجارية، والمعياران الحاكمان للنجاح: القوة (المهارة والكفاءة في العمل) والأمانة (الصدق وحفظ أموال المنشأة). عقد الإجارة والكفاءة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        27
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        معيارا التوظيف والعمل في عقود الإجارة: الكفاءة الفنية والنزاهة والأمانة
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة القصص: الآية 26
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                قَالَتْ إِحْدَاهُمَا يَا أَبَتِ اسْتَأْجِرْهُ ۖ إِنَّ خَيْرَ مَنِ اسْتَأْجَرْتَ الْقَوِيُّ الْأَمِينُ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: مشروعية عقد الإجارة واستئجار الأشخاص للأعمال الهندسية والتجارية، والمعياران الحاكمان للنجاح: القوة (المهارة والكفاءة في العمل) والأمانة (الصدق وحفظ أموال المنشأة).
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: عقد الإجارة والكفاءة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 28 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="عقد العمل محدد المدة ومبادلة العمل بالأجر والمهر قَالَ إِنِّي أُرِيدُ أَنْ أُنكِحَكَ إِحْدَى ابْنَتَيَّ هَاتَيْنِ عَلَىٰ أَن تَأْجُرَنِي ثَمَانِيَ حِجَجٍ ۖ فَإِنْ أَتْمَمْتَ عَشْرًا فَمِنْ عِندِكَ ۖ وَمَا أُرِيدُ أَنْ أَشُقَّ عَلَيْكَ ۚ سَتَجِدُنِي إِن شَاءَ اللَّهُ مِنَ الصَّالِحِينَ سورة القصص: الآية 27 الأحكام الاقتصادية: صحة عقد العمل المشروط بمدة محددة (8 أو 10 سنوات)، وجواز جعل العمل والمنفعة عوضاً مالياً، وحسن معاملة الأجراء وعدم تكليفهم بما يشق عليهم. تحديد مدة عقد العمل">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        28
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقد العمل محدد المدة ومبادلة العمل بالأجر والمهر
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة القصص: الآية 27
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                قَالَ إِنِّي أُرِيدُ أَنْ أُنكِحَكَ إِحْدَى ابْنَتَيَّ هَاتَيْنِ عَلَىٰ أَن تَأْجُرَنِي ثَمَانِيَ حِجَجٍ ۖ فَإِنْ أَتْمَمْتَ عَشْرًا فَمِنْ عِندِكَ ۖ وَمَا أُرِيدُ أَنْ أَشُقَّ عَلَيْكَ ۚ سَتَجِدُنِي إِن شَاءَ اللَّهُ مِنَ الصَّالِحِينَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: صحة عقد العمل المشروط بمدة محددة (8 أو 10 سنوات)، وجواز جعل العمل والمنفعة عوضاً مالياً، وحسن معاملة الأجراء وعدم تكليفهم بما يشق عليهم.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: تحديد مدة عقد العمل</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 29 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="وجوب دفع أجور العاملين فور أداء العمل والتعامل المالي بالمعروف ... فَإِنْ أَرْضَعْنَ لَكُمْ فَآتُوهُنَّ أُجُورَهُنَّ ۖ وَأْتَمِرُوا بَيْنَكُم بِمَعْرُوفٍ ۖ وَإِن تَعَاسَرْتُمْ فَسَتُرْضِعُ لَهُ أُخْرَىٰ سورة الطلاق: الآية 6 الأحكام الاقتصادية: وجوب الوفاء التام بأجور العمال والموظفين فور إنجاز العمل دون تأخير أو خصم تعسفي، وتراضي الطرفين على الأجر العادل المناسب لظروف السوق. وفاء أجور العاملين">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        29
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب دفع أجور العاملين فور أداء العمل والتعامل المالي بالمعروف
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الطلاق: الآية 6
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                ... فَإِنْ أَرْضَعْنَ لَكُمْ فَآتُوهُنَّ أُجُورَهُنَّ ۖ وَأْتَمِرُوا بَيْنَكُم بِمَعْرُوفٍ ۖ وَإِن تَعَاسَرْتُمْ فَسَتُرْضِعُ لَهُ أُخْرَىٰ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: وجوب الوفاء التام بأجور العمال والموظفين فور إنجاز العمل دون تأخير أو خصم تعسفي، وتراضي الطرفين على الأجر العادل المناسب لظروف السوق.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: وفاء أجور العاملين</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 30 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="أصل عقدي الجعالة (المكافأة المشروطة) والكفالة الشخصية والضمان المالي قَالُوا نَفْقِدُ صُوَاعَ الْمَلِكِ وَلِمَن جَاءَ بِهِ حِمْلُ بَعِيرٍ وَأَنَا بِهِ زَعِيمٌ سورة يوسف: الآية 72 الأحكام الاقتصادية: الأصل القرآني لعقد الجعالة (جائزة أو عمولة لمن ينجز مهمة محددة: 'حمل بعير')، وأصل عقد الكفالة والضمان الشخصي والمالي ('وأنا به زعيم' أي ضامن متعهد بالسداد). عقدا الجعالة والكفالة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        30
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        أصل عقدي الجعالة (المكافأة المشروطة) والكفالة الشخصية والضمان المالي
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة يوسف: الآية 72
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                قَالُوا نَفْقِدُ صُوَاعَ الْمَلِكِ وَلِمَن جَاءَ بِهِ حِمْلُ بَعِيرٍ وَأَنَا بِهِ زَعِيمٌ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: الأصل القرآني لعقد الجعالة (جائزة أو عمولة لمن ينجز مهمة محددة: 'حمل بعير')، وأصل عقد الكفالة والضمان الشخصي والمالي ('وأنا به زعيم' أي ضامن متعهد بالسداد).
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: عقدا الجعالة والكفالة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 31 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="contracts" data-ayah-search="مشروعية الشركات والمخالطة في الأموال والتحذير من بغي وخيانة الشركاء ... وَإِنَّ كَثِيرًا مِّنَ الْخُلَطَاءِ لَيَبْغِي بَعْضُهُمْ عَلَىٰ بَعْضٍ إِلَّا الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ وَقَلِيلٌ مَّا هُمْ ۗ وَظَنَّ دَاوُودُ أَنَّمَا فَتَنَّاهُ فَاسْتَغْفَرَ رَبَّهُ وَخَرَّ رَاكِعًا وَأَنَابَ سورة ص: الآية 24 الأحكام الاقتصادية: مشروعية الشراكة التجارية وخلط الأموال لتنمية الاستثمار، والتحذير الشديد من خيانة الشريك لأخيه باختلاس الأرباح أو استغلال الصلاحيات بغير عدل. عقد الشركة وحقوق الشركاء">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        31
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الشركات والمخالطة في الأموال والتحذير من بغي وخيانة الشركاء
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة ص: الآية 24
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                ... وَإِنَّ كَثِيرًا مِّنَ الْخُلَطَاءِ لَيَبْغِي بَعْضُهُمْ عَلَىٰ بَعْضٍ إِلَّا الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ وَقَلِيلٌ مَّا هُمْ ۗ وَظَنَّ دَاوُودُ أَنَّمَا فَتَنَّاهُ فَاسْتَغْفَرَ رَبَّهُ وَخَرَّ رَاكِعًا وَأَنَابَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: مشروعية الشراكة التجارية وخلط الأموال لتنمية الاستثمار، والتحذير الشديد من خيانة الشريك لأخيه باختلاس الأرباح أو استغلال الصلاحيات بغير عدل.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: عقد الشركة وحقوق الشركاء</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 32 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="orphans" data-ayah-search="اختبار الرشد المالي لليتيم وتأهيله تجارياً قبل تسليمه الثروة والأموال وَابْتَلُوا الْيَتَامَىٰ حَتَّىٰ إِذَا بَلَغُوا النِّكَاحَ فَإِنْ آنَسْتُم مِّنْهُمْ رُشْدًا فَادْفَعُوا إِلَيْهِمْ أَمْوَالَهُمْ ۖ وَلَا تَأْكُلُوهَا إِسْرَافًا وَبِدَارًا أَن يَكْبَرُوا ۚ وَمَن كَانَ غَنِيًّا فَلْيَسْتَعْفِفْ ۖ وَمَن كَانَ فَقِيرًا فَلْيَأْكُلْ بِالْمَعْرُوفِ ۚ فَإِذَا دَفَعْتُمْ إِلَيْهِمْ أَمْوَالَهُمْ فَأَشْهِدُوا عَلَيْهِمْ ۚ وَكَفَىٰ بِاللَّهِ حَسِيبًا سورة النساء: الآية 6 الأحكام الاقتصادية: قاعدة 'التأهيل المالي'؛ لا يُسلَّم المال لمن لا يحسن إدارته حتى يثبت رشده وقدرته على التجارة وحفظ الأصول، وتنظيم أتعاب الوصي أو مدير الصناديق بالمعروف. اختبار الرشد المالي">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        32
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        اختبار الرشد المالي لليتيم وتأهيله تجارياً قبل تسليمه الثروة والأموال
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 6
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَابْتَلُوا الْيَتَامَىٰ حَتَّىٰ إِذَا بَلَغُوا النِّكَاحَ فَإِنْ آنَسْتُم مِّنْهُمْ رُشْدًا فَادْفَعُوا إِلَيْهِمْ أَمْوَالَهُمْ ۖ وَلَا تَأْكُلُوهَا إِسْرَافًا وَبِدَارًا أَن يَكْبَرُوا ۚ وَمَن كَانَ غَنِيًّا فَلْيَسْتَعْفِفْ ۖ وَمَن كَانَ فَقِيرًا فَلْيَأْكُلْ بِالْمَعْرُوفِ ۚ فَإِذَا دَفَعْتُمْ إِلَيْهِمْ أَمْوَالَهُمْ فَأَشْهِدُوا عَلَيْهِمْ ۚ وَكَفَىٰ بِاللَّهِ حَسِيبًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: قاعدة 'التأهيل المالي'؛ لا يُسلَّم المال لمن لا يحسن إدارته حتى يثبت رشده وقدرته على التجارة وحفظ الأصول، وتنظيم أتعاب الوصي أو مدير الصناديق بالمعروف.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: اختبار الرشد المالي</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 33 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="orphans" data-ayah-search="مشروعية الحجر المالي على غير الرشيد لحفظ الثروة وتنميتها بالاستثمار وَلَا تُؤْتُوا السُّفَهَاءَ أَمْوَالَكُمُ الَّتِي جَعَلَ اللَّهُ لَكُمْ قِيَامًا وَارْزُقُوهُمْ فِيهَا وَاكْسُوهُمْ وَقُولُوا لَهُمْ قَوْلًا مَّعْرُوفًا سورة النساء: الآية 5 الأحكام الاقتصادية: الحجر المالي على المبذرين والسفهاء لحماية الثروات من التبدد، واستثمار أموال القاصرين في التجارة والإنفاق عليهم من الأرباح ('وارزقوهم فيها') لحفظ رأس المال الأصلي. الحجر المالي على السفيه">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        33
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الحجر المالي على غير الرشيد لحفظ الثروة وتنميتها بالاستثمار
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 5
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَلَا تُؤْتُوا السُّفَهَاءَ أَمْوَالَكُمُ الَّتِي جَعَلَ اللَّهُ لَكُمْ قِيَامًا وَارْزُقُوهُمْ فِيهَا وَاكْسُوهُمْ وَقُولُوا لَهُمْ قَوْلًا مَّعْرُوفًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: الحجر المالي على المبذرين والسفهاء لحماية الثروات من التبدد، واستثمار أموال القاصرين في التجارة والإنفاق عليهم من الأرباح ('وارزقوهم فيها') لحفظ رأس المال الأصلي.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الحجر المالي على السفيه</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 34 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="orphans" data-ayah-search="الوعيد الشديد على أكل أموال اليتامى ظلماً والاعتداء على حقوق الضعفاء إِنَّ الَّذِينَ يَأْكُلُونَ أَمْوَالَ الْيَتَامَىٰ ظُلْمًا إِنَّمَا يَأْكُلُونَ فِي بُطُونِهِمْ نَارًا ۖ وَسَيَصْلَوْنَ سَعِيرًا سورة النساء: الآية 10 الأحكام الاقتصادية: أشد تحذير في الشريعة على خيانة الأمانة المالية للوصي والوكيل، وتجريم التربح غير المشروع من أموال القاصرين ومن لا حيلة لهم. حرمة أكل أموال اليتامى">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        34
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الوعيد الشديد على أكل أموال اليتامى ظلماً والاعتداء على حقوق الضعفاء
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة النساء: الآية 10
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                إِنَّ الَّذِينَ يَأْكُلُونَ أَمْوَالَ الْيَتَامَىٰ ظُلْمًا إِنَّمَا يَأْكُلُونَ فِي بُطُونِهِمْ نَارًا ۖ وَسَيَصْلَوْنَ سَعِيرًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: أشد تحذير في الشريعة على خيانة الأمانة المالية للوصي والوكيل، وتجريم التربح غير المشروع من أموال القاصرين ومن لا حيلة لهم.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: حرمة أكل أموال اليتامى</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 35 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="macro" data-ayah-search="الوعيد الشديد على كنز الذهب والفضة والسيولة ومنعها من الدورة الاقتصادية ... وَالَّذِينَ يَكْنِزُونَ الذَّهَبَ وَالْفِضَّةَ وَلَا يُنفِقُونَهَا فِي سَبِيلِ اللَّهِ فَبَشِّرْهُم بِعَذَابٍ أَلِيمٍ * يَوْمَ يُحْمَىٰ عَلَيْهَا فِي نَارِ جَهَنَّمَ فَتُكْوَىٰ بِهَا جِبَاهُهُمْ وَجُنُوبُهُمْ وَظُهُورُهُمْ ۖ هَٰذَا مَا كَنَزْتُمْ لِأَنفُسِكُمْ فَذُوقُوا مَا كُنتُمْ تَكْنِزُونَ سورة التوبة: الآيات 34-35 الأحكام الاقتصادية: تحريم تعطيل السيولة وحبس النقود عن الاستثمار وعمارة الأسواق، وإيجاب الزكاة والإنفاق عليها لتبقى الأموال متدفقة في شرايين الاقتصاد لخدمة الإنتاج. تحريم كنز الأموال">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        35
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الوعيد الشديد على كنز الذهب والفضة والسيولة ومنعها من الدورة الاقتصادية
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة التوبة: الآيات 34-35
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                ... وَالَّذِينَ يَكْنِزُونَ الذَّهَبَ وَالْفِضَّةَ وَلَا يُنفِقُونَهَا فِي سَبِيلِ اللَّهِ فَبَشِّرْهُم بِعَذَابٍ أَلِيمٍ * يَوْمَ يُحْمَىٰ عَلَيْهَا فِي نَارِ جَهَنَّمَ فَتُكْوَىٰ بِهَا جِبَاهُهُمْ وَجُنُوبُهُمْ وَظُهُورُهُمْ ۖ هَٰذَا مَا كَنَزْتُمْ لِأَنفُسِكُمْ فَذُوقُوا مَا كُنتُمْ تَكْنِزُونَ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: تحريم تعطيل السيولة وحبس النقود عن الاستثمار وعمارة الأسواق، وإيجاب الزكاة والإنفاق عليها لتبقى الأموال متدفقة في شرايين الاقتصاد لخدمة الإنتاج.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: تحريم كنز الأموال</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 36 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="macro" data-ayah-search="قانون تداول الأموال العظيم: منع تركز الثروة في يد فئة قليلة واحتكار الأسواق ... كَيْ لَا يَكُونَ دُولَةً بَيْنَ الْأَغْنِيَاءِ مِنكُمْ ۚ وَمَا آتَاكُمُ الرَّسُولُ فَخُذُوهُ وَمَا نَهَاكُمْ عَنْهُ فَانتَهُوا ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ سورة الحشر: الآية 7 الأحكام الاقتصادية: أهم قاعدة في الاقتصاد الكلي والعدالة الاجتماعية؛ تحريم تركيز الثروة واحتكار الأسواق لدى القلة من أصحاب رؤوس الأموال، ووجوب توزيع الفرص الاقتصادية على المجتمع ككل. قانون دوران الثروة">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        36
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قانون تداول الأموال العظيم: منع تركز الثروة في يد فئة قليلة واحتكار الأسواق
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الحشر: الآية 7
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                ... كَيْ لَا يَكُونَ دُولَةً بَيْنَ الْأَغْنِيَاءِ مِنكُمْ ۚ وَمَا آتَاكُمُ الرَّسُولُ فَخُذُوهُ وَمَا نَهَاكُمْ عَنْهُ فَانتَهُوا ۚ وَاتَّقُوا اللَّهَ ۖ إِنَّ اللَّهَ شَدِيدُ الْعِقَابِ
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: أهم قاعدة في الاقتصاد الكلي والعدالة الاجتماعية؛ تحريم تركيز الثروة واحتكار الأسواق لدى القلة من أصحاب رؤوس الأموال، ووجوب توزيع الفرص الاقتصادية على المجتمع ككل.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: قانون دوران الثروة</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 37 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="macro" data-ayah-search="قاعدة إدارة الميزانية والإنفاق الرشيد: التوازن بين الإسراف والتقتير وَالَّذِينَ إِذَا أَنفَقُوا لَمْ يُسْرِفُوا وَلَمْ يَقْتُرُوا وَكَانَ بَيْنَ ذَٰلِكَ قَوَامًا سورة الفرقان: الآية 67 الأحكام الاقتصادية: التوازن المالي في الإنفاق الاستهلاكي والاستثماري؛ تحريم التبذير المهدور وتحريم البخل والشح الذي يضر بالنشاط، وتحقيق الوسطية في الإنفاق. الاعتدال المالي">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        37
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قاعدة إدارة الميزانية والإنفاق الرشيد: التوازن بين الإسراف والتقتير
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة الفرقان: الآية 67
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                وَالَّذِينَ إِذَا أَنفَقُوا لَمْ يُسْرِفُوا وَلَمْ يَقْتُرُوا وَكَانَ بَيْنَ ذَٰلِكَ قَوَامًا
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: التوازن المالي في الإنفاق الاستهلاكي والاستثماري؛ تحريم التبذير المهدور وتحريم البخل والشح الذي يضر بالنشاط، وتحقيق الوسطية في الإنفاق.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: الاعتدال المالي</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

        <!-- Quranic Verse Card 38 -->
        <div class="fiqh-ayah-card p-5 bg-slate-900/95 rounded-2xl border-2 border-emerald-500/40 space-y-3 shadow-xl hover:border-emerald-400 transition" data-ayah-topic="macro" data-ayah-search="فضل الضرب في الأرض وابتغاء فضل الله بالتجارة والكسب الشريف ... وَآخَرُونَ يَضْرِبُونَ فِي الْأَرْضِ يَبْتَغُونَ مِن فَضْلِ اللَّهِ ۙ وَآخَرُونَ يُقَاتِلُونَ فِي سَبِيلِ اللَّهِ ۖ فَاقْرَءُوا مَا تَيَسَّرَ مِنْهُ... سورة المزمل: الآية 20 الأحكام الاقتصادية: مدح السعي التجاري والضرب في الأرض لطلب الرزق الحلال وقرنه بالجهاد في سبيل الله، وتأصيل السفر والاستيراد والتصدير وتنمية الاستثمارات المشروعة. السعي والتجارة الحلال">
            
            <!-- Top Row: Title + Surah Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-300 font-mono font-black flex items-center justify-center border border-emerald-400/50 flex-shrink-0">
                        38
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل الضرب في الأرض وابتغاء فضل الله بالتجارة والكسب الشريف
                    </h4>
                </div>
                <span class="px-3 py-1 bg-slate-800 text-emerald-400 font-mono font-black text-xs rounded-xl border border-slate-700">
                    سورة المزمل: الآية 20
                </span>
            </div>

            <!-- Verse Body -->
            <div class="p-4 bg-emerald-950/20 rounded-xl border border-emerald-500/30 text-emerald-100 font-bold leading-relaxed text-sm md:text-base font-amiri shadow-inner">
                <i class="fa-solid fa-quote-right text-emerald-400/50 ml-1.5 text-xs"></i>
                ... وَآخَرُونَ يَضْرِبُونَ فِي الْأَرْضِ يَبْتَغُونَ مِن فَضْلِ اللَّهِ ۙ وَآخَرُونَ يُقَاتِلُونَ فِي سَبِيلِ اللَّهِ ۖ فَاقْرَءُوا مَا تَيَسَّرَ مِنْهُ...
                <i class="fa-solid fa-quote-left text-emerald-400/50 mr-1.5 text-xs"></i>
            </div>

            <!-- Fiqh & Economic Guidance -->
            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-400">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>الأحكام والفوائد الفقهية والاقتصادية المستنبطة:</span>
                </div>
                <p class="text-xs text-slate-300 font-bold leading-relaxed">
                    الأحكام الاقتصادية: مدح السعي التجاري والضرب في الأرض لطلب الرزق الحلال وقرنه بالجهاد في سبيل الله، وتأصيل السفر والاستيراد والتصدير وتنمية الاستثمارات المشروعة.
                </p>
            </div>

            <!-- Bottom Badge -->
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold pt-1">
                <span class="text-emerald-400 font-black">🏷️ التصنيف: السعي والتجارة الحلال</span>
                <span class="text-slate-500 font-mono">نص قطعي الثبوت والدلالة ✓</span>
            </div>

        </div>

                    </div>

                    <div id="no-quran-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد آيات مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية الآيات.</p>
                    </div>
                </div>

                <div id="fiqh-sub-hadiths" class="space-y-4 hidden">
                    <div class="p-5 bg-gradient-to-r from-amber-950/60 via-slate-900 to-amber-950/40 border-2 border-amber-500/40 rounded-2xl flex flex-wrap items-center justify-between gap-4 shadow-xl">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-3.5 py-1 bg-amber-500/20 text-amber-300 font-black text-xs rounded-xl border border-amber-400">
                                    ★ الموسوعة النبوية الشاملة للأحاديث الصحيحة في المال والمعاملات
                                </span>
                                <span class="px-3.5 py-1 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded-xl border border-emerald-400">
                                    100 حديث صحيح محكم 100% ✓
                                </span>
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-amber-300 flex items-center gap-2">
                                <i class="fa-solid fa-scroll text-amber-400"></i>
                                الموسوعة النبوية الكبرى: البيوع، الربا، الزكاة، المواريث، الديون، والأجور (الأحاديث الصحيحة فقط)
                            </h3>
                            <p class="text-xs md:text-sm text-slate-200 font-bold mt-1 leading-relaxed">
                                تشمل: <span class="text-amber-300 font-black">البيوع والتراضي (15)</span> · <span class="text-rose-300 font-black">الصرف والربا (10)</span> · <span class="text-sky-300 font-black">الديون والرهن (12)</span> · <span class="text-emerald-300 font-black">فريضة الزكاة (14)</span> · <span class="text-teal-300 font-black">الصدقات والإنفاق (10)</span> · <span class="text-purple-300 font-black">المواريث والفرائض (10)</span> · <span class="text-fuchsia-300 font-black">الإجارة والشركات (9)</span> · <span class="text-indigo-300 font-black">السلم والشفعة (8)</span> · <span class="text-cyan-300 font-black">اللقطة والأمانات (6)</span> · <span class="text-red-300 font-black">النزاهة ومحاربة الرشوة (6)</span>.
                            </p>
                        </div>

                        <!-- Instant Hadith Search Box -->
                        <div class="w-full md:w-80">
                            <label class="text-xs font-bold text-slate-300 block mb-1">🔍 ابحث في نصوص وتخريج الأحاديث:</label>
                            <input type="text" id="hadith-search-input" oninput="filterHadiths()" placeholder="ابحث: بيع، ربا، زكاة، ميراث، رهن، أجر، لقطة..." class="tool-input text-xs font-bold">
                        </div>
                    </div>

                    <!-- 🎛️ Hadiths Category Filter Bar (10 أبواب فقهية) 🎛️ -->
                    <div class="bg-slate-900/90 p-3.5 rounded-2xl border border-amber-500/30 flex flex-wrap items-center gap-2 shadow-lg">
                        <span class="text-xs font-black text-amber-400 block w-full mb-1">
                            🏷️ تصفية الأحاديث النبوية الصحيحة حسب الباب الاقتصادي والمالي (10 أبواب فقهية):
                        </span>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn active" data-hcat="all" onclick="setHadithCategory('all', this)">
                            🌟 جميع الأحاديث النبوية (100)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="sales" onclick="setHadithCategory('sales', this)">
                            🛒 البيوع والتراضي والمرابحة (15)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="riba" onclick="setHadithCategory('riba', this)">
                            🚫 الصرف والربا والعملات (10)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="debts" onclick="setHadithCategory('debts', this)">
                            💳 الديون والرهن والإفلاس (12)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="zakat" onclick="setHadithCategory('zakat', this)">
                            🌾 فريضة الزكاة وأنصبتها (14)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="charity" onclick="setHadithCategory('charity', this)">
                            💎 الصدقات والإنفاق والوقف (10)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="inheritance" onclick="setHadithCategory('inheritance', this)">
                            📜 المواريث والفرائض والوصايا (10)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="labor" onclick="setHadithCategory('labor', this)">
                            👥 أجور العمال والشركات (9)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="contracts" onclick="setHadithCategory('contracts', this)">
                            📑 السلم والشفعة والمساقاة (8)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="property" onclick="setHadithCategory('property', this)">
                            🛡️ اللقطة والودائع والأمانات (6)
                        </button>
                        <button type="button" class="hadith-filter-btn fiqh-filter-btn" data-hcat="integrity" onclick="setHadithCategory('integrity', this)">
                            ⚖️ النزاهة ومحاربة الرشوة والغلول (6)
                        </button>
                    </div>

                    <!-- Hadiths Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="hadiths-grid-container">
        <!-- Hadith Card 1 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="1 الأصل في البيوع والتجارة هو التراضي الخالص بين الطرفين عن أبي سعيد الخدري رضي الله عنه قال: قال رسول الله ﷺ: «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ» رواه ابن ماجه (2185) وابن حبان (4967) وصححه الألباني في الإرواء (1283) القاعدة العظمى في كافة العقود والمعاملات والمرابحات: بطلان أي عقد يشوبه إكراه أو غش أو تدليس أو غبن فاحش، واشتراط الرضا التام المتبادل لصحّة انتقال الملكية. البيع عن تراض الرضا الإكراه صحة البيع العقود التجارة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        1
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأصل في البيوع والتجارة هو التراضي الخالص بين الطرفين
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّمَا الْبَيْعُ عَنْ تَرَاضٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه ابن ماجه (2185) وابن حبان (4967) وصححه الألباني في الإرواء (1283)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">القاعدة العظمى في كافة العقود والمعاملات والمرابحات: بطلان أي عقد يشوبه إكراه أو غش أو تدليس أو غبن فاحش، واشتراط الرضا التام المتبادل لصحّة انتقال الملكية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 2 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="2 خيار المجلس والبركة الكبرى في الصدق والبيان وشؤم الكتمان عن حكيم بن حزام رضي الله عنه قال: قال رسول الله ﷺ: «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَذَبَا وَكَتَمَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا» صحيح البخاري (2079) وصحيح مسلم (1532) إثبات خيار المجلس لكلا العاقدين، وبيان أن أرباح التجارة لا بركة فيها إن بنيت على إخفاء عيوب السلع أو المبالغة الكاذبة، وأن الشفافية التامة هي جالبة النماء. البيعان بالخيار ما لم يتفرقا صدقا وبينا محقت بركة بيعهما خيار المجلس الصدق">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        2
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        خيار المجلس والبركة الكبرى في الصدق والبيان وشؤم الكتمان
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا، فَإِنْ صَدَقَا وَبَيَّنَا بُورِكَ لَهُمَا فِي بَيْعِهِمَا، وَإِنْ كَذَبَا وَكَتَمَا مُحِقَتْ بَرَكَةُ بَيْعِهِمَا»
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
                    <span class="text-slate-200 font-bold">إثبات خيار المجلس لكلا العاقدين، وبيان أن أرباح التجارة لا بركة فيها إن بنيت على إخفاء عيوب السلع أو المبالغة الكاذبة، وأن الشفافية التامة هي جالبة النماء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 3 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="3 النهي الصارم عن بيع السلع قبل تملكها وقبضها (لا تبع ما ليس عندك) عن حكيم بن حزام رضي الله عنه قال: سألت النبي ﷺ فقلت: يأتيني الرجل فيريد مني البيع ليس عندي أفأبتاعه له من السوق؟ فقال: «لَا تَبِعْ مَا لَيْسَ عِنْدَكَ» رواه أبو داود (3503) والترمذي (1232) والنسائي (4613) وصححه الترمذي والألباني تحريم بيع الأعيان غير المملوكة للبائع درءاً للعجز عن التسليم والغرر والمخاطرة، وهو أصل تشريعي يضبط تجارة الدروب شيبينغ المعاصرة والبيع على المكشوف (Short Selling). لا تبع ما ليس عندك حكيم بن حزام بيع المعدوم الدروب شيبينغ البيع على المكشوف">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        3
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي الصارم عن بيع السلع قبل تملكها وقبضها (لا تبع ما ليس عندك)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه قال: سألت النبي ﷺ فقلت: يأتيني الرجل فيريد مني البيع ليس عندي أفأبتاعه له من السوق؟ فقال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا تَبِعْ مَا لَيْسَ عِنْدَكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3503) والترمذي (1232) والنسائي (4613) وصححه الترمذي والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم بيع الأعيان غير المملوكة للبائع درءاً للعجز عن التسليم والغرر والمخاطرة، وهو أصل تشريعي يضبط تجارة الدروب شيبينغ المعاصرة والبيع على المكشوف (Short Selling).</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 4 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="4 النهي عن بيوع الغرر والمخاطرة والمجهولات في الأسواق عن أبي هريرة رضي الله عنه قال: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْحَصَاةِ، وَعَنْ بَيْعِ الْغَرَرِ» صحيح مسلم (1513) الأصل الجوهري في تحريم المعاملات المالية المشتملة على مجهول العاقبة، كعقود المشتقات المالية الاحتيالية والمقامرات الرقمية وبيع الصناديق العشوائية مجهولة المحتوى. نهى عن بيع الغرر بيع الحصاة الجهالة المخاطرة المشتقات المالية القمار">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        4
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بيوع الغرر والمخاطرة والمجهولات في الأسواق
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال:</span>
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
                    <span class="text-slate-200 font-bold">الأصل الجوهري في تحريم المعاملات المالية المشتملة على مجهول العاقبة، كعقود المشتقات المالية الاحتيالية والمقامرات الرقمية وبيع الصناديق العشوائية مجهولة المحتوى.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 5 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="5 اشتراط حيازة المبيع واستيفاء القبض قبل إعادة تصريفه وبيعه عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «مَنِ اشْتَرَى طَعَامًا فَلَا يَبِعْهُ حَتَّى يَسْتَوْفِيَهُ» وفي رواية: «حَتَّى يَقْبِضَهُ» صحيح البخاري (2136) وصحيح مسلم (1525) عدم جواز المتاجرة بالسلع وبيعها قبل دخولها في ضمان المشتري وحيازتها الفعلية أو الحكمية (كاستلام أوراق الشحن وبوالص التخزين الرسمية المعاصرة). من اشترى طعاما فلا يبعه حتى يستوفيه حتى يقبضه قبض المبيع الحيازة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        5
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        اشتراط حيازة المبيع واستيفاء القبض قبل إعادة تصريفه وبيعه
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنِ اشْتَرَى طَعَامًا فَلَا يَبِعْهُ حَتَّى يَسْتَوْفِيَهُ» وفي رواية: «حَتَّى يَقْبِضَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2136) وصحيح مسلم (1525)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">عدم جواز المتاجرة بالسلع وبيعها قبل دخولها في ضمان المشتري وحيازتها الفعلية أو الحكمية (كاستلام أوراق الشحن وبوالص التخزين الرسمية المعاصرة).</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 6 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="6 النهي عن البيع على بيع المسلم والنجش واحتكار السلع بالخداع عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال: «لَا يَبِعْ بَعْضُكُمْ عَلَى بَيْعِ بَعْضٍ، وَلَا تَنَاجَشُوا، وَلَا يَبِعْ حَاضِرٌ لِبَادٍ» صحيح البخاري (2139) وصحيح مسلم (1412) حماية المنافسة الشريفة ومنع المزايدات الوهمية (النجش) في المزادات العلنية والرقمية، ومنع استغلال جهل القادمين من خارج السوق بالأسعار الحقيقية. لا يبع بعضكم على بيع بعض ولا تناجشوا ولا يبع حاضر لباد المزادات النجش">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        6
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن البيع على بيع المسلم والنجش واحتكار السلع بالخداع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَبِعْ بَعْضُكُمْ عَلَى بَيْعِ بَعْضٍ، وَلَا تَنَاجَشُوا، وَلَا يَبِعْ حَاضِرٌ لِبَادٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2139) وصحيح مسلم (1412)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حماية المنافسة الشريفة ومنع المزايدات الوهمية (النجش) في المزادات العلنية والرقمية، ومنع استغلال جهل القادمين من خارج السوق بالأسعار الحقيقية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 7 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="7 النهي عن بيعتين في بيعة والصفقات المشروطة الملتوية عن أبي هريرة رضي الله عنه قال: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعَتَيْنِ فِي بَيْعَةٍ» رواه الترمذي (1231) والنسائي (4632) وأحمد (9582) وصححه الترمذي والألباني تحريم العقود المتضمنة حيل الربا (كالعينة: أبيعك نقداً بـ 100 على أن تبيعني مؤجلاً بـ 120)، أو العقود التي تترك الثمن مجهولاً بين النقد والتقسيط دون حسم عند التعاقد. نهى عن بيعتين في بيعة العينة بيع وسلف الصفقات المشروطة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        7
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بيعتين في بيعة والصفقات المشروطة الملتوية
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعَتَيْنِ فِي بَيْعَةٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1231) والنسائي (4632) وأحمد (9582) وصححه الترمذي والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم العقود المتضمنة حيل الربا (كالعينة: أبيعك نقداً بـ 100 على أن تبيعني مؤجلاً بـ 120)، أو العقود التي تترك الثمن مجهولاً بين النقد والتقسيط دون حسم عند التعاقد.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 8 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="8 النهي عن بيع الثمار والمحاصيل قبل بدو صلاحها وأمن العاهة عن أنس بن مالك رضي الله عنه أن رسول الله ﷺ: «نَهَى عَنْ بَيْعِ الثِّمَارِ حَتَّى تُزْهِيَ، قِيلَ: وَمَا تُزْهِي؟ قَالَ: حَتَّى تَحْمَرَّ، فَقَالَ: أَرَأَيْتَ إِذَا مَنَعَ اللَّهُ الثَّمَرَةَ بِمَ يَأْخُذُ أَحَدُكُمْ مَالَ أَخِيهِ؟» صحيح البخاري (2198) وصحيح مسلم (1555) قاعدة منع بيع الإنتاج الزراعي قبل وصوله للطور الصالح للاستعمال وأمنه من الآفات، منعاً للخصومة وأكل أموال المشترين بغير مقابل مشروع. نهى عن بيع الثمار حتى تزهي تحمر بم يأخذ أحدكم مال أخيه ضمان المحاصيل">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        8
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بيع الثمار والمحاصيل قبل بدو صلاحها وأمن العاهة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أنس بن مالك رضي الله عنه أن رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى عَنْ بَيْعِ الثِّمَارِ حَتَّى تُزْهِيَ، قِيلَ: وَمَا تُزْهِي؟ قَالَ: حَتَّى تَحْمَرَّ، فَقَالَ: أَرَأَيْتَ إِذَا مَنَعَ اللَّهُ الثَّمَرَةَ بِمَ يَأْخُذُ أَحَدُكُمْ مَالَ أَخِيهِ؟»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2198) وصحيح مسلم (1555)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">قاعدة منع بيع الإنتاج الزراعي قبل وصوله للطور الصالح للاستعمال وأمنه من الآفات، منعاً للخصومة وأكل أموال المشترين بغير مقابل مشروع.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 9 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="9 تشريع وضع الجوائح والكوارث الطارئة عن المشترين والمزارعين عن جابر بن عبد الله رضي الله عنهما أن النبي ﷺ قال: «لَوْ بِعْتَ مِنْ أَخِيكَ ثَمَرًا فَأَصَابَتْهُ جَائِحَةٌ، فَلَا يَحِلُّ لَكَ أَنْ تَأْخُذَ مِنْهُ شَيْئًا، بِمَ تَأْخُذُ مَالَ أَخِيكَ بِغَيْرِ حَقٍّ؟» صحيح مسلم (1554) أصل فقهي عظيم في نظرية الظروف الطارئة والقوة القاهرة المعاصرة: إسقاط التكاليف عن المشتري إذا تلفت البضاعة بآفة سماوية لا يد له فيها قبل التمكن من حيازتها. وضع الجوائح جائحة بم تأخذ مال أخيك بغير حق القوة القاهرة الكوارث">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        9
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تشريع وضع الجوائح والكوارث الطارئة عن المشترين والمزارعين
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما أن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَوْ بِعْتَ مِنْ أَخِيكَ ثَمَرًا فَأَصَابَتْهُ جَائِحَةٌ، فَلَا يَحِلُّ لَكَ أَنْ تَأْخُذَ مِنْهُ شَيْئًا، بِمَ تَأْخُذُ مَالَ أَخِيكَ بِغَيْرِ حَقٍّ؟»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1554)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أصل فقهي عظيم في نظرية الظروف الطارئة والقوة القاهرة المعاصرة: إسقاط التكاليف عن المشتري إذا تلفت البضاعة بآفة سماوية لا يد له فيها قبل التمكن من حيازتها.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 10 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="10 وجوب الوفاء بالشروط التجارية والعقود الرضائية المبرمة عن عمرو بن عوف المزني رضي الله عنه أن رسول الله ﷺ قال: «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا، أَوْ أَحَلَّ حَرَامًا» رواه الترمذي (1352) وصححه، وأبو داود (3594)، وصححه الألباني في الإرواء (1303) حرية الاشتراط والتنظيم التعاقدي في المعاملات المالية، وإلزامية مواعيد التسليم والدفعات وجداول السداد وغرامات التأخير غير الربوية ما دامت متفقة مع أحكام الشريعة. المسلمون على شروطهم الشروط العقدية الالتزامات الوفاء بالعقود">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        10
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب الوفاء بالشروط التجارية والعقود الرضائية المبرمة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عمرو بن عوف المزني رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْمُسْلِمُونَ عَلَى شُرُوطِهِمْ، إِلَّا شَرْطًا حَرَّمَ حَلَالًا، أَوْ أَحَلَّ حَرَامًا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1352) وصححه، وأبو داود (3594)، وصححه الألباني في الإرواء (1303)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حرية الاشتراط والتنظيم التعاقدي في المعاملات المالية، وإلزامية مواعيد التسليم والدفعات وجداول السداد وغرامات التأخير غير الربوية ما دامت متفقة مع أحكام الشريعة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 11 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="11 تحريم الغش والتطفيف في البضائع والمواصفات والبراءة النبوية من الغاش عن أبي هريرة رضي الله عنه أن رسول الله ﷺ مر على صُبرة طعام فأدخل يده فيها فنالت أصابعه بللاً، فقال: ما هذا يا صاحب الطعام؟ قال: أصابته السماء يا رسول الله، قال: «أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي» صحيح مسلم (102) وجوب الإفصاح والشفافية التامة عن كل عيب خفي في السلع أو الخدمات، وتحريم التلاعب بتاريخ الصلاحية أو بلد المنشأ أو إخفاء المعيب تحت السليم. من غش فليس مني صبرة طعام أصابته السماء عيوب السلع الغش التجاري">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        11
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الغش والتطفيف في البضائع والمواصفات والبراءة النبوية من الغاش
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ مر على صُبرة طعام فأدخل يده فيها فنالت أصابعه بللاً، فقال: ما هذا يا صاحب الطعام؟ قال: أصابته السماء يا رسول الله، قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَفَلَا جَعَلْتَهُ فَوْقَ الطَّعَامِ كَيْ يَرَاهُ النَّاسُ، مَنْ غَشَّ فَلَيْسَ مِنِّي»
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
                    <span class="text-slate-200 font-bold">وجوب الإفصاح والشفافية التامة عن كل عيب خفي في السلع أو الخدمات، وتحريم التلاعب بتاريخ الصلاحية أو بلد المنشأ أو إخفاء المعيب تحت السليم.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 12 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="12 فضل التاجر الصدوق الأمين ومنزلته العالية في الجنة مع النبيين والشهداء عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال: «التَّاجِرُ الصَّدُوقُ الْأَمِينُ مَعَ النَّبِيِّينَ، وَالصِّدِّيقِينَ، وَالشُّهَدَاءِ» رواه الترمذي (1209) وصححه، والدارمي (2539)، وصححه الألباني في صحيح الترغيب (1782) بيان شرف مهنة التجارة والمحاسبة والمال إذا التزم صاحبها بصدق الوصف والأمانة في الحسابات والفواتير والوفاء بالعهود دون جشع أو احتيال. التاجر الصدوق الأمين مع النبيين والصديقين والشهداء بركة التجارة الأمانة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        12
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل التاجر الصدوق الأمين ومنزلته العالية في الجنة مع النبيين والشهداء
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «التَّاجِرُ الصَّدُوقُ الْأَمِينُ مَعَ النَّبِيِّينَ، وَالصِّدِّيقِينَ، وَالشُّهَدَاءِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1209) وصححه، والدارمي (2539)، وصححه الألباني في صحيح الترغيب (1782)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">بيان شرف مهنة التجارة والمحاسبة والمال إذا التزم صاحبها بصدق الوصف والأمانة في الحسابات والفواتير والوفاء بالعهود دون جشع أو احتيال.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 13 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="13 السماحة واليسر في البيع والشراء والمطالبة بالحقوق واستجلاب رحمة الله عن جابر بن عبد الله رضي الله عنهما أن رسول الله ﷺ قال: «رَحِمَ اللَّهُ رَجُلًا سَمْحًا إِذَا بَاعَ، وَإِذَا اشْتَرَى، وَإِذَا اقْتَضَى» صحيح البخاري (2076) الحث على المرونة والتيسير وعدم المشاحة والتعنت في الأسعار والتفاوض وسداد المطالبات المالية، وهي روح الأخلاق الاقتصادية الإسلامية. رحم الله رجلا سمحا إذا باع وإذا اشترى وإذا اقتضى السماحة اليسر المعاملة الحسنة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        13
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        السماحة واليسر في البيع والشراء والمطالبة بالحقوق واستجلاب رحمة الله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما أن رسول الله ﷺ قال:</span>
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
                    <span class="text-slate-200 font-bold">الحث على المرونة والتيسير وعدم المشاحة والتعنت في الأسعار والتفاوض وسداد المطالبات المالية، وهي روح الأخلاق الاقتصادية الإسلامية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 14 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="14 فضل إقالة النادم وقبول المرتجع وفسخ الصفقة لراحة الزبون عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ» رواه أبو داود (3460) وابن ماجه (2199) وصححه ابن حبان (4931) والألباني استحباب قبول سياسة إرجاع البضائع واسترداد المشتري لماله إن ندم أو تبين عدم حاجته للسلعة، وأن جزاء ذلك تكفير الذنوب وتفريج الكربات يوم القيامة. من أقال مسلما أقاله الله عثرته إقالة النادم سياسة الاسترجاع المرتجعات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        14
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل إقالة النادم وقبول المرتجع وفسخ الصفقة لراحة الزبون
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَقَالَ مُسْلِمًا، أَقَالَهُ اللَّهُ عَثْرَتَهُ يَوْمَ الْقِيَامَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3460) وابن ماجه (2199) وصححه ابن حبان (4931) والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب قبول سياسة إرجاع البضائع واسترداد المشتري لماله إن ندم أو تبين عدم حاجته للسلعة، وأن جزاء ذلك تكفير الذنوب وتفريج الكربات يوم القيامة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 15 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="sales" data-hadith-search="15 تحريم الحلف الكاذب أو الصادق لتنفيق البضائع وترويج المبيعات عن أبي هريرة رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «الْحَلِفُ مُنَفِّقَةٌ لِلسِّلْعَةِ، مُمْحِقَةٌ لِلْبَرَكَةِ» صحيح البخاري (2087) وصحيح مسلم (1606) تحريم وتجريم الترويج الإعلاني المضلل باستخدام الأيمان والأقسام لاستدراج المشترين، والتحذير من أن المبيعات الناتجة عن ذلك تنزع البركة من رأس المال كلياً. الحلف منفقة للسلعة ممحقة للبركة الأيمان الكاذبة التسويق المضلل الإعلانات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        15
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الحلف الكاذب أو الصادق لتنفيق البضائع وترويج المبيعات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-amber-500/20 text-amber-300 border-amber-400/40">
                        🛒 بيوع ومرابحة وتجارة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: سمعت رسول الله ﷺ يقول:</span>
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
                    <span class="text-slate-200 font-bold">تحريم وتجريم الترويج الإعلاني المضلل باستخدام الأيمان والأقسام لاستدراج المشترين، والتحذير من أن المبيعات الناتجة عن ذلك تنزع البركة من رأس المال كلياً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 16 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="16 دستور الأصناف الربوية الستة وضوابط صرف العملات والسلع الأساسية عن عبادة بن الصامت رضي الله عنه قال: قال رسول الله ﷺ: «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ، فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ» صحيح مسلم (1587) القانون الأساسي في تحريم ربا الفضل وربا النسيئة، واشتراط التقابض الفوري يداً بيد (Spot Exchange) عند تحويل العملات المختلفة كالدولار والريال واليورو لمنع الوقوع في الصرف المؤجل المحرم. الذهب بالذهب والفضة بالفضة مثلا بمثل يدا بيد الأصناف الستة الصرف العملات ربا الفضل ربا النسيئة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        16
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور الأصناف الربوية الستة وضوابط صرف العملات والسلع الأساسية
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبادة بن الصامت رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الذَّهَبُ بِالذَّهَبِ، وَالْفِضَّةُ بِالْفِضَّةِ، وَالْبُرُّ بِالْبُرِّ، وَالشَّعِيرُ بِالشَّعِيرِ، وَالتَّمْرُ بِالتَّمْرِ، وَالْمِلْحُ بِالْمِلْحِ، مِثْلًا بِمِثْلٍ، سَوَاءً بِسَوَاءٍ، يَدًا بِيَدٍ، فَإِذَا اخْتَلَفَتْ هَذِهِ الْأَصْنَافُ، فَبِيعُوا كَيْفَ شِئْتُمْ إِذَا كَانَ يَدًا بِيَدٍ»
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
                    <span class="text-slate-200 font-bold">القانون الأساسي في تحريم ربا الفضل وربا النسيئة، واشتراط التقابض الفوري يداً بيد (Spot Exchange) عند تحويل العملات المختلفة كالدولار والريال واليورو لمنع الوقوع في الصرف المؤجل المحرم.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 17 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="17 تحريم التفاضل في مبادلة الجنس الواحد من الذهب والفضة وصرفهما عن أبي سعيد الخدري رضي الله عنه أن رسول الله ﷺ قال: «لَا تَبِيعُوا الذَّهَبَ بِالذَّهَبِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا الْوَرِقَ بِالْوَرِقِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا مِنْهَا غَائِبًا بِنَاجِزٍ» صحيح البخاري (2177) وصحيح مسلم (1584) منع دفع أي زيادة (تشفوا) عند استبدال ذهب قديم بذهب جديد أو مكسور بمصاغ، ووجوب التماثل التام في الوزن وزناً بوزن، أو البيع نقداً ثم الشراء نقداً. لا تبيعوا الذهب بالذهب إلا مثلا بمثل ولا تشفوا الورق غائبا بناجز مصاغ الذهب ربا الفضل">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        17
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم التفاضل في مبادلة الجنس الواحد من الذهب والفضة وصرفهما
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا تَبِيعُوا الذَّهَبَ بِالذَّهَبِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا الْوَرِقَ بِالْوَرِقِ إِلَّا مِثْلًا بِمِثْلٍ، وَلَا تُشِفُّوا بَعْضَهَا عَلَى بَعْضٍ، وَلَا تَبِيعُوا مِنْهَا غَائِبًا بِنَاجِزٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2177) وصحيح مسلم (1584)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">منع دفع أي زيادة (تشفوا) عند استبدال ذهب قديم بذهب جديد أو مكسور بمصاغ، ووجوب التماثل التام في الوزن وزناً بوزن، أو البيع نقداً ثم الشراء نقداً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 18 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="18 حديث تمر خيبر والقاعدة الذهبية في تجنب ربا الفضل عبر النقد عن أبي سعيد الخدري وأبي هريرة رضي الله عنهما أن رسول الله ﷺ استعمل رجلاً على خيبر فجاءه بتمر جَنِيب، فقال: أكلُّ تمر خيبر هكذا؟ قال: لا والله يا رسول الله! إنا لنأخذ الصاع من هذا بالصاعين، والصاعين بالثلاثة، فقال ﷺ: «أَوَّهْ! عَيْنُ الرِّبَا! لَا تَفْعَلْ، وَلَكِنْ بِعِ الْجَمْعَ بِالدَّرَاهِمِ، ثُمَّ اشْتَرِ بِالدَّرَاهِمِ جَنِيبًا» صحيح البخاري (2201) وصحيح مسلم (1593) وضع الآلية المصرفية والتجارية الشرعية للخروج من مبادلة الأعيان الربوية المتفاوتة الجودة: بيع الرديء بالنقود أولاً، ثم شراء الجيد بالنقود في صفقة مستقلة منفصلة. تمر خيبر عين الربا بع الجمع بالدراهم ثم اشتر بالدراهم جنيبا ربا الفضل المبادلة العينية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        18
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حديث تمر خيبر والقاعدة الذهبية في تجنب ربا الفضل عبر النقد
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري وأبي هريرة رضي الله عنهما أن رسول الله ﷺ استعمل رجلاً على خيبر فجاءه بتمر جَنِيب، فقال: أكلُّ تمر خيبر هكذا؟ قال: لا والله يا رسول الله! إنا لنأخذ الصاع من هذا بالصاعين، والصاعين بالثلاثة، فقال ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَوَّهْ! عَيْنُ الرِّبَا! لَا تَفْعَلْ، وَلَكِنْ بِعِ الْجَمْعَ بِالدَّرَاهِمِ، ثُمَّ اشْتَرِ بِالدَّرَاهِمِ جَنِيبًا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2201) وصحيح مسلم (1593)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وضع الآلية المصرفية والتجارية الشرعية للخروج من مبادلة الأعيان الربوية المتفاوتة الجودة: بيع الرديء بالنقود أولاً، ثم شراء الجيد بالنقود في صفقة مستقلة منفصلة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 19 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="19 لعن آكل الربا وموكله وموثقه وشاهديه واستواء الجميع في الوزر عن جابر بن عبد الله رضي الله عنهما قال: «لَعَنَ رَسُولُ اللَّهِ ﷺ آكِلَ الرِّبَا، وَمُؤْكِلَهُ، وَكَاتِبَهُ، وَشَاهِدَيْهِ، وَقَالَ: هُمْ سَوَاءٌ» صحيح مسلم (1598) تجريم كل وسيط أو محاسب أو مدقق أو موثق يتعاقد على فوائد ربوية أو يصيغ بنود الربا أو يوقع عليها، وإثبات أن الإعانة على المعاملة الربوية كأكلها تماماً. لعن آكل الربا وموكله وكاتبه وشاهديه هم سواء الإعانة على الحرام العقود الربوية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        19
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        لعن آكل الربا وموكله وموثقه وشاهديه واستواء الجميع في الوزر
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما قال:</span>
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
                    <span class="text-slate-200 font-bold">تجريم كل وسيط أو محاسب أو مدقق أو موثق يتعاقد على فوائد ربوية أو يصيغ بنود الربا أو يوقع عليها، وإثبات أن الإعانة على المعاملة الربوية كأكلها تماماً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 20 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="20 التغليظ الشديد في جرم الربا وبيان شناعته وخطورته على المجتمع عن عبد الله بن مسعود رضي الله عنه عن النبي ﷺ قال: «الرِّبَا ثَلَاثَةٌ وَسَبْعُونَ بَابًا، أَيْسَرُهَا مِثْلُ أَنْ يَنْكِحَ الرَّجُلُ أُمَّهُ» رواه الحاكم في المستدرك (2259) وصححه، ورواه ابن ماجه (2275) وصححه الألباني في صحيح الجامع (3533) تحذير المسلمين والمستثمرين من الاستهانة بفوائد القروض والربا المقنع بألفاظ حديثة، وبيان أنه من أشد المحرمات الماحقة للبركة والمفسدة للأخلاق والاقتصاد. الربا ثلاثة وسبعون بابا أيسرها خطورة الربا الفوائد البنكية القروض الربوية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        20
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        التغليظ الشديد في جرم الربا وبيان شناعته وخطورته على المجتمع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن مسعود رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الرِّبَا ثَلَاثَةٌ وَسَبْعُونَ بَابًا، أَيْسَرُهَا مِثْلُ أَنْ يَنْكِحَ الرَّجُلُ أُمَّهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الحاكم في المستدرك (2259) وصححه، ورواه ابن ماجه (2275) وصححه الألباني في صحيح الجامع (3533)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحذير المسلمين والمستثمرين من الاستهانة بفوائد القروض والربا المقنع بألفاظ حديثة، وبيان أنه من أشد المحرمات الماحقة للبركة والمفسدة للأخلاق والاقتصاد.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 21 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="21 ربا النسيئة وخطورة تأجيل تسليم البدلين في عقود الصرف عن أسامة بن زيد رضي الله عنهما أن رسول الله ﷺ قال: «إِنَّمَا الرِّبَا فِي النَّسِيئَةِ» صحيح البخاري (2178) وصحيح مسلم (1596) تأكيد أن الربا الأعظم والأخطر هو ربا النسيئة (تأجيل القبض أو زيادة المال مقابل مد أجل السداد)، وهو المعمول به في القروض الربوية والفوائد المركبة. إنما الربا في النسيئة تأخير القبض الصرف المؤجل الفوائد المركبة ربا الجاهلية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        21
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        ربا النسيئة وخطورة تأجيل تسليم البدلين في عقود الصرف
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أسامة بن زيد رضي الله عنهما أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّمَا الرِّبَا فِي النَّسِيئَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2178) وصحيح مسلم (1596)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تأكيد أن الربا الأعظم والأخطر هو ربا النسيئة (تأجيل القبض أو زيادة المال مقابل مد أجل السداد)، وهو المعمول به في القروض الربوية والفوائد المركبة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 22 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="22 النهي عن بيع الكالئ بالكالئ (بيع الدين بالدين وتوريق الديون) عن عبد الله بن عمر رضي الله عنهما قال: «نَهَى النَّبِيُّ ﷺ عَنْ بَيْعِ الْكَالِئِ بِالْكَالِئِ» [أي: الدَّيْنِ بِالدَّيْنِ] رواه الحاكم (2341) والدارقطني (3079) والنووي في المجموع ونقل الإجماع على تحريمه شيخ الإسلام ابن تيمية تحريم بيع الديون المؤجلة بديون مؤجلة أخرى، وهو أصل قطعي يبطل تداول سندات الديون وخصم الكمبيالات المؤجلة بآجال أخرى والمشتقات المالية الائتمانية المعاصرة. نهى عن بيع الكالئ بالكالئ بيع الدين بالدين توريق الديون السندات خصم الأوراق التجارية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        22
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن بيع الكالئ بالكالئ (بيع الدين بالدين وتوريق الديون)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح بمعناه إجماعاً
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى النَّبِيُّ ﷺ عَنْ بَيْعِ الْكَالِئِ بِالْكَالِئِ» [أي: الدَّيْنِ بِالدَّيْنِ]
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الحاكم (2341) والدارقطني (3079) والنووي في المجموع ونقل الإجماع على تحريمه شيخ الإسلام ابن تيمية</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحريم بيع الديون المؤجلة بديون مؤجلة أخرى، وهو أصل قطعي يبطل تداول سندات الديون وخصم الكمبيالات المؤجلة بآجال أخرى والمشتقات المالية الائتمانية المعاصرة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 23 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="23 تحريم بيع العِينة والتحايل الصوري على القروض الربوية بالسلع عن عبد الله بن عمر رضي الله عنهما قال: سمعت رسول الله ﷺ يقول: «إِذَا تَبَايَعْتُمْ بِالْعِينَةِ، وَأَخَذْتُمْ أَذْنَابَ الْبَقَرِ، وَرَضِيتُمْ بِالزَّرْعِ، وَتَرَكْتُمُ الْجِهَادَ، سَلَّطَ اللَّهُ عَلَيْكُمْ ذُلًّا لَا يَنْزِعُهُ حَتَّى تَرْجِعُوا إِلَى دِينِكُمْ» رواه أبو داود (3462) وصححه الألباني في السلسلة الصحيحة (11) تحريم صور البيع الشكلي الذي يتخذ السلعة حيلة لأخذ نقد عاجل مقابل نقد آجل أكبر (كأن يبيع سلعة بالآجل بـ 120 ثم يشتريها نقداً بـ 100 في مجلس واحد). إذا تبايعتم بالعينة الحيل الربوية التورق المنظم القروض الصوريه أخذتم أذناب البقر">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        23
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم بيع العِينة والتحايل الصوري على القروض الربوية بالسلع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال: سمعت رسول الله ﷺ يقول:</span>
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
                    <span class="text-slate-200 font-bold">تحريم صور البيع الشكلي الذي يتخذ السلعة حيلة لأخذ نقد عاجل مقابل نقد آجل أكبر (كأن يبيع سلعة بالآجل بـ 120 ثم يشتريها نقداً بـ 100 في مجلس واحد).</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 24 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="24 تحريم الجمع بين سلف وبيع واشتراط نفع تجاري في القروض عن عبد الله بن عمرو رضي الله عنهما أن رسول الله ﷺ قال: «لَا يَحِلُّ سَلَفٌ وَبَيْعٌ، وَلَا شَرْطَانِ فِي بَيْعٍ، وَلَا رِبْحُ مَا لَمْ يُضْمَنْ، وَلَا بَيْعُ مَا لَيْسَ عِنْدَكَ» رواه الترمذي (1234) وأبو داود (3504) والنسائي (4611) وصححه الترمذي والألباني قاعدة مانعة لتحويل عقود التبرع (القرض) إلى عقود معاوضة وربح (كل قرض جر نفعاً مشروطاً فهو ربا)، ومنع اشتراط الشراء أو البيع لمنحه قرضاً حسناً. لا يحل سلف وبيع شرطان في بيع ربح ما لم يضمن قرض جر نفعا ربا القروض">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        24
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الجمع بين سلف وبيع واشتراط نفع تجاري في القروض
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو رضي الله عنهما أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَحِلُّ سَلَفٌ وَبَيْعٌ، وَلَا شَرْطَانِ فِي بَيْعٍ، وَلَا رِبْحُ مَا لَمْ يُضْمَنْ، وَلَا بَيْعُ مَا لَيْسَ عِنْدَكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1234) وأبو داود (3504) والنسائي (4611) وصححه الترمذي والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">قاعدة مانعة لتحويل عقود التبرع (القرض) إلى عقود معاوضة وربح (كل قرض جر نفعاً مشروطاً فهو ربا)، ومنع اشتراط الشراء أو البيع لمنحه قرضاً حسناً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 25 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="riba" data-hadith-search="25 عقوبة المرابين في البرزخ والسباحة في نهر الدم مع الحجارة عن سمرة بن جندب رضي الله عنه في حديث الرؤيا الطويل عن النبي ﷺ قال: «فَانْطَلَقْنَا حَتَّى أَتَيْنَا عَلَى نَهَرٍ مِنْ دَمٍ فِيهِ رَجُلٌ قَائِمٌ، وَعَلَى شَطِّ النَّهَرِ رَجُلٌ بَيْنَ يَدَيْهِ حِجَارَةٌ، فَأَقْبَلَ الرَّجُلُ الَّذِي فِي النَّهَرِ، فَإِذَا أَرَادَ أَنْ يَخْرُجَ رَمَى فِي فِيهِ بِحَجَرٍ، فَرَجَعَ حَيْثُ كَانَ... فَقُلْتُ: مَنْ هَذَا؟ قَالَا: الَّذِي يَأْكُلُ الرِّبَا» صحيح البخاري (2085) تحذير إيماني قاطع من كبائر المال الحرام، وبيان الجزاء البرزخي الصارم لمن يستحل امتصاص جهود ودماء المعوزين والمقترضين بفوائد الربا. حديث الرؤيا نهر الدم آكل الربا عقوبة الربا كبائر الأموال">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        25
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة المرابين في البرزخ والسباحة في نهر الدم مع الحجارة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-rose-500/20 text-rose-300 border-rose-400/40">
                        🚫 ربا وصرف وعملات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن سمرة بن جندب رضي الله عنه في حديث الرؤيا الطويل عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فَانْطَلَقْنَا حَتَّى أَتَيْنَا عَلَى نَهَرٍ مِنْ دَمٍ فِيهِ رَجُلٌ قَائِمٌ، وَعَلَى شَطِّ النَّهَرِ رَجُلٌ بَيْنَ يَدَيْهِ حِجَارَةٌ، فَأَقْبَلَ الرَّجُلُ الَّذِي فِي النَّهَرِ، فَإِذَا أَرَادَ أَنْ يَخْرُجَ رَمَى فِي فِيهِ بِحَجَرٍ، فَرَجَعَ حَيْثُ كَانَ... فَقُلْتُ: مَنْ هَذَا؟ قَالَا: الَّذِي يَأْكُلُ الرِّبَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2085)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحذير إيماني قاطع من كبائر المال الحرام، وبيان الجزاء البرزخي الصارم لمن يستحل امتصاص جهود ودماء المعوزين والمقترضين بفوائد الربا.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 26 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="26 نية سداد الدين واستجلاب عون الله وتيسير الرزق وعقوبة الإتلاف عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: «مَنْ أَخَذَ أَمْوَالَ النَّاسِ يُرِيدُ أَدَاءَهَا أَدَّى اللَّهُ عَنْهُ، وَمَنْ أَخَذَ يُرِيدُ إِتْلَافَهَا أَتْلَفَهُ اللَّهُ» صحيح البخاري (2387) النية الصادقة في سداد الالتزامات المالية والديون تستجلب معونة الله وتيسير أبواب الكسب، ونية المماطلة وأكل أموال الدائنين سبب مباشر لمحق رأس المال والهلاك. من أخذ أموال الناس يريد أداءها أدى الله عنه إتلافها أتلفه الله نية السداد القروض">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        26
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        نية سداد الدين واستجلاب عون الله وتيسير الرزق وعقوبة الإتلاف
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه عن النبي ﷺ قال:</span>
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
                    <span class="text-slate-200 font-bold">النية الصادقة في سداد الالتزامات المالية والديون تستجلب معونة الله وتيسير أبواب الكسب، ونية المماطلة وأكل أموال الدائنين سبب مباشر لمحق رأس المال والهلاك.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 27 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="27 تحريم مَطْل الغني والمماطلة في الديون الحالة ومشروعية الحوالة المالية عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَطْلُ الْغَنِيِّ ظُلْمٌ، فَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتْبَعْ» صحيح البخاري (2287) وصحيح مسلم (1564) تحريم تأخير سداد المستحقات والفواتير من المقتدر، ومشروعية عقد الحوالة المالية المصرفية والتجارية إذا حُوِّل الدائن إلى طرف موسر قادر على الوفاء. مطل الغني ظلم إذا أتبع أحدكم على مليء فليتبع الحوالة المالية تسديد الديون المماطلة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        27
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم مَطْل الغني والمماطلة في الديون الحالة ومشروعية الحوالة المالية
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَطْلُ الْغَنِيِّ ظُلْمٌ، فَإِذَا أُتْبِعَ أَحَدُكُمْ عَلَى مَلِيءٍ فَلْيَتْبَعْ»
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
                    <span class="text-slate-200 font-bold">تحريم تأخير سداد المستحقات والفواتير من المقتدر، ومشروعية عقد الحوالة المالية المصرفية والتجارية إذا حُوِّل الدائن إلى طرف موسر قادر على الوفاء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 28 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="28 مشروعية ملاحقة المماطل الموسر قضائياً وجواز عقوبته وحبسه عن عمرو بن الشريد عن أبيه رضي الله عنه عن رسول الله ﷺ قال: «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ» رواه أبو داود (3628) والنسائي (4689) وابن ماجه (2427) وصححه الألباني في الإرواء (1434) المماطل المقتدر (الواجد) يجوز شرعاً وقانوناً رفع الشكوى ضده، والتشهير به في السجلات الائتمانية الرسمية، وفرض العقوبات المالية والإدارية وحبسه لإلزامه بالوفاء. لي الواجد يحل عرضه وعقوبته المماطل حبس المدين القضاء التنفيذي السجلات الائتمانية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        28
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية ملاحقة المماطل الموسر قضائياً وجواز عقوبته وحبسه
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عمرو بن الشريد عن أبيه رضي الله عنه عن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَيُّ الْوَاجِدِ يُحِلُّ عِرْضَهُ وَعُقُوبَتَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3628) والنسائي (4689) وابن ماجه (2427) وصححه الألباني في الإرواء (1434)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المماطل المقتدر (الواجد) يجوز شرعاً وقانوناً رفع الشكوى ضده، والتشهير به في السجلات الائتمانية الرسمية، وفرض العقوبات المالية والإدارية وحبسه لإلزامه بالوفاء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 29 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="29 خطورة الدين وتعلق نفس المؤمن وذمته به بعد الوفاة حتى يُقضى عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ» رواه الترمذي (1078) وصححه، وابن ماجه (2413)، وصححه الألباني في صحيح الجامع (6779) وجوب المبادرة فور الوفاة بتصفية تركة الميت وسداد ديونه قبل توزيع أي ميراث أو تنفيذ أي وصية تبرعية، لأن براءة الذمة مقدمة على حقوق الورثة. نفس المؤمن معلقة بدينه حتى يقضى عنه قضاء الديون تصفية التركات سداد الديون">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        29
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        خطورة الدين وتعلق نفس المؤمن وذمته به بعد الوفاة حتى يُقضى
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَفْسُ الْمُؤْمِنِ مُعَلَّقَةٌ بِدَيْنِهِ حَتَّى يُقْضَى عَنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (1078) وصححه، وابن ماجه (2413)، وصححه الألباني في صحيح الجامع (6779)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وجوب المبادرة فور الوفاة بتصفية تركة الميت وسداد ديونه قبل توزيع أي ميراث أو تنفيذ أي وصية تبرعية، لأن براءة الذمة مقدمة على حقوق الورثة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 30 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="30 قدسية حقوق العباد: الدين لا يسقط حتى بالشهادة في سبيل الله عن عبد الله بن عمرو بن العاص رضي الله عنهما أن رسول الله ﷺ قال: «يُغْفَرُ لِلشَّهِيدِ كُلُّ ذَنْبٍ إِلَّا الدَّيْنَ» صحيح مسلم (1886) التأكيد القاطع على أن حقوق العباد والديون والمعاملات المالية مبنية على المشاحة ولا تسقطها العبادات مهما عظمت، ووجوب حفظ سجلات الديون بدقة متناهية. يغفر للشهيد كل ذنب إلا الدين حقوق العباد التوثيق المحاسبي الديون والالتزامات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        30
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قدسية حقوق العباد: الدين لا يسقط حتى بالشهادة في سبيل الله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو بن العاص رضي الله عنهما أن رسول الله ﷺ قال:</span>
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
                    <span class="text-slate-200 font-bold">التأكيد القاطع على أن حقوق العباد والديون والمعاملات المالية مبنية على المشاحة ولا تسقطها العبادات مهما عظمت، ووجوب حفظ سجلات الديون بدقة متناهية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 31 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="31 فضل حسن القضاء وإعطاء الأفضل والزيادة غير المشروطة عند السداد عن أبي هريرة رضي الله عنه في قصة اقتضاء الأعرابي لبعيره من النبي ﷺ فقال ﷺ لأصحابه: اشتروا له، فطلبوا فلم يجدوا إلا سناً أفضل من سنه، فقال: «أَعْطُوهُ، فَإِنَّ مِنْ خَيْرِكُمْ أَحْسَنَكُمْ قَضَاءً» صحيح البخاري (2306) وصحيح مسلم (1601) استحباب مكافأة الدائن ورد الدين بأفضل منه صفةً أو زيادة في القدر بطيب نفس ورضا من المدين، ما لم تكن تلك الزيادة مشروطة مسبقاً أو متفقاً عليها فتصير ربا. خيركم أحسنكم قضاء حسن الوفاء رد القرض الزيادة غير المشروطة البركة في السداد">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        31
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل حسن القضاء وإعطاء الأفضل والزيادة غير المشروطة عند السداد
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه في قصة اقتضاء الأعرابي لبعيره من النبي ﷺ فقال ﷺ لأصحابه: اشتروا له، فطلبوا فلم يجدوا إلا سناً أفضل من سنه، فقال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَعْطُوهُ، فَإِنَّ مِنْ خَيْرِكُمْ أَحْسَنَكُمْ قَضَاءً»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2306) وصحيح مسلم (1601)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب مكافأة الدائن ورد الدين بأفضل منه صفةً أو زيادة في القدر بطيب نفس ورضا من المدين، ما لم تكن تلك الزيادة مشروطة مسبقاً أو متفقاً عليها فتصير ربا.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 32 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="32 مشروعية الرهن التجاري في الديون لتوثيق وضمان الحقوق المالية عن عائشة رضي الله عنها قالت: «تُوُفِّيَ رَسُولُ اللَّهِ ﷺ وَدِرْعُهُ مَرْهُونَةٌ عِنْدَ يَهُودِيٍّ بِثَلَاثِينَ صَاعًا مِنْ شَعِيرٍ» صحيح البخاري (2916) وصحيح مسلم (1603) مشروعية توثيق الديون بالضمانات العينية والرهون العقارية والتجارية لضمان استرداد الحقوق، وجواز معاملة غير المسلمين في المعاملات المالية المباحة. درعه مرهونة عند يهودي الرهن التجاري الضمانات العينية توثيق الديون">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        32
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الرهن التجاري في الديون لتوثيق وضمان الحقوق المالية
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عائشة رضي الله عنها قالت:</span>
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
                    <span class="text-slate-200 font-bold">مشروعية توثيق الديون بالضمانات العينية والرهون العقارية والتجارية لضمان استرداد الحقوق، وجواز معاملة غير المسلمين في المعاملات المالية المباحة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 33 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="33 بقاء ملكية المرهون للراهن: له نماء العين وغلّتها وعليه ضمانها عن سعيد بن المسيب عن رسول الله ﷺ أنه قال: «لَا يَغْلَقُ الرَّهْنُ مِنْ صَاحِبِهِ الَّذِي رَهَنَهُ، لَهُ غُنْمُهُ، وَعَلَيْهِ غُرْمُهُ» رواه الشافعي والدارقطني (3031) والحاكم (2371) وصححه، وصححه الألباني في الإرواء (1401) الرهن لا يسلب المالك ملكيته، وأرباح وعوائد العين المرهونة (كإيجار العقار المرهون) ملك للراهن، ولا يحق للمرتهن (الدائن) الاستيلاء على الرهن إلا عبر بيعه لسداد دينه فقط ورد الباقي. لا يغلق الرهن له غنمه وعليه غرمه ملكية المرهون عوائد الرهن حماية حقوق المدين">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        33
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        بقاء ملكية المرهون للراهن: له نماء العين وغلّتها وعليه ضمانها
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن سعيد بن المسيب عن رسول الله ﷺ أنه قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَغْلَقُ الرَّهْنُ مِنْ صَاحِبِهِ الَّذِي رَهَنَهُ، لَهُ غُنْمُهُ، وَعَلَيْهِ غُرْمُهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الشافعي والدارقطني (3031) والحاكم (2371) وصححه، وصححه الألباني في الإرواء (1401)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الرهن لا يسلب المالك ملكيته، وأرباح وعوائد العين المرهونة (كإيجار العقار المرهون) ملك للراهن، ولا يحق للمرتهن (الدائن) الاستيلاء على الرهن إلا عبر بيعه لسداد دينه فقط ورد الباقي.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 34 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="34 فضل إنظار المعسر والتجاوز عنه والاستظلال في ظل عرش الرحمن عن أبي قتادة رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «مَنْ سَرَّهُ أَنْ يُنْجِيَهُ اللَّهُ مِنْ كُرَبِ يَوْمِ الْقِيَامَةِ، فَلْيُنَفِّسْ عَنْ مُعْسِرٍ، أَوْ يَضَعْ عَنْهُ» صحيح مسلم (1563) الأجر الإيماني العظيم في تأجيل مواعيد سداد الديون للمتعثرين مالياً أو إسقاط جزء منها (التنازل والخصم)، وتفريج كربهم في مواسم الأزمات المالية. من سره أن ينجيه الله من كرب يوم القيامة فلينفس عن معسر أو يضع عنه إنظار المعسر">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        34
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل إنظار المعسر والتجاوز عنه والاستظلال في ظل عرش الرحمن
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي قتادة رضي الله عنه قال: سمعت رسول الله ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ سَرَّهُ أَنْ يُنْجِيَهُ اللَّهُ مِنْ كُرَبِ يَوْمِ الْقِيَامَةِ، فَلْيُنَفِّسْ عَنْ مُعْسِرٍ، أَوْ يَضَعْ عَنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1563)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الأجر الإيماني العظيم في تأجيل مواعيد سداد الديون للمتعثرين مالياً أو إسقاط جزء منها (التنازل والخصم)، وتفريج كربهم في مواسم الأزمات المالية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 35 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="35 مضاعفة أجر الصدقة عن كل يوم تأخير يُنظَر فيه المدين المعسر عن بريدة الأسلمي رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، ثُمَّ سَمِعْتُهُ يَقُولُ: مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ، فَقُلْتُ: سَمِعْتُكَ يَا رَسُولَ اللَّهِ تَقُولُ... فَقَالَ: لَهُ بِكُلِّ يَوْمٍ مِثْلُهُ صَدَقَةٌ قَبْلَ أَنْ يَحِلَّ الدَّيْنُ، فَإِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَاهُ صَدَقَةٌ» رواه أحمد (22998) وابن ماجه (2418) والحاكم وصححه، وصححه الألباني في السلسلة الصحيحة (86) أعظم استثمار أخروي لرأس المال: اعتبار مهلة السداد الممنوحة للمدين المعسر صدقة يومية مضاعفة تعادل أصل الدين ومثليه في الميزان. من أنظر معسرا فله بكل يوم مثله صدقة مثلاه صدقة ثواب إنظار المعسر التمويل التنموي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        35
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مضاعفة أجر الصدقة عن كل يوم تأخير يُنظَر فيه المدين المعسر
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن بريدة الأسلمي رضي الله عنه قال: سمعت رسول الله ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلِهِ صَدَقَةٌ، ثُمَّ سَمِعْتُهُ يَقُولُ: مَنْ أَنْظَرَ مُعْسِرًا فَلَهُ بِكُلِّ يَوْمٍ مِثْلَيْهِ صَدَقَةٌ، فَقُلْتُ: سَمِعْتُكَ يَا رَسُولَ اللَّهِ تَقُولُ... فَقَالَ: لَهُ بِكُلِّ يَوْمٍ مِثْلُهُ صَدَقَةٌ قَبْلَ أَنْ يَحِلَّ الدَّيْنُ، فَإِذَا حَلَّ الدَّيْنُ فَأَنْظَرَهُ فَلَهُ بِكُلِّ يَوْمٍ مِثْلَاهُ صَدَقَةٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (22998) وابن ماجه (2418) والحاكم وصححه، وصححه الألباني في السلسلة الصحيحة (86)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أعظم استثمار أخروي لرأس المال: اعتبار مهلة السداد الممنوحة للمدين المعسر صدقة يومية مضاعفة تعادل أصل الدين ومثليه في الميزان.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 36 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="36 مشروعية الكفالة والضمان المالي (الزعيم غارم وضامن للدين) عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ» رواه أبو داود (3565) والترمذي (2120) وصححه، وصححه الألباني في صحيح الجامع (4068) تأصيل مشروعية عقود الكفالة والضمان المصرفي وخطابات الضمان البنكية، والتزام الكفيل قانوناً وشرعاً بسداد الدين كاملاً إذا تعذر السداد من المدين الأصلي. الزعيم غارم الكفالة الضمان خطابات الضمان العارية مؤداة الدين مقضي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        36
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الكفالة والضمان المالي (الزعيم غارم وضامن للدين)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْعَارِيَّةُ مُؤَدَّاةٌ، وَالْمِنْحَةُ مَرْدُودَةٌ، وَالدَّيْنُ مَقْضِيٌّ، وَالزَّعِيمُ غَارِمٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3565) والترمذي (2120) وصححه، وصححه الألباني في صحيح الجامع (4068)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تأصيل مشروعية عقود الكفالة والضمان المصرفي وخطابات الضمان البنكية، والتزام الكفيل قانوناً وشرعاً بسداد الدين كاملاً إذا تعذر السداد من المدين الأصلي.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 37 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="debts" data-hadith-search="37 أحكام إفلاس المدين وحق البائع في استرداد عين سلعته قبل الغرماء عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَنْ أَدْرَكَ مَالَهُ بِعَيْنِهِ عِنْدَ رَجُلٍ قَدْ أَفْلَسَ، فَهُوَ أَحَقُّ بِهِ مِنْ غَيْرِهِ» صحيح البخاري (2402) وصحيح مسلم (1559) قاعدة حقوق الأولوية والامتياز عند إفلاس الشركات أو الأفراد: إذا وجد المورد أو التاجر بضاعته لم تتغير ولم تُبع، فله الأولوية في استردادها بعينها دون الدخول في قسمة الغرماء. من أدرك ماله بعينه عند رجل قد أفلس الإفلاس قسمة الغرماء حقوق الأولوية استرداد البضاعة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        37
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        أحكام إفلاس المدين وحق البائع في استرداد عين سلعته قبل الغرماء
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-sky-500/20 text-sky-300 border-sky-400/40">
                        💳 ديون وقروض ورهن
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَدْرَكَ مَالَهُ بِعَيْنِهِ عِنْدَ رَجُلٍ قَدْ أَفْلَسَ، فَهُوَ أَحَقُّ بِهِ مِنْ غَيْرِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2402) وصحيح مسلم (1559)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">قاعدة حقوق الأولوية والامتياز عند إفلاس الشركات أو الأفراد: إذا وجد المورد أو التاجر بضاعته لم تتغير ولم تُبع، فله الأولوية في استردادها بعينها دون الدخول في قسمة الغرماء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 38 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="38 فريضة الزكاة كركن مالي تعبدي ثالث بني عليه صرح الإسلام عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «بُنِيَ الْإِسْلَامُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلَاةِ، وَإِيتَاءِ الزَّكَاةِ، وَالْحَجِّ، وَصَوْمِ رَمَضَانَ» صحيح البخاري (8) وصحيح مسلم (16) الزكاة ركن مالي واجتماعي واقتصادي قطعي لا يقوم إسلام العبد إلا بأدائه، وهي ليست ضريبة بشرية بل فريضة إلهية تطهر النفوس وتنمي الثروة. بني الإسلام على خمس وإيتاء الزكاة ركن الإسلام فريضة الزكاة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        38
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فريضة الزكاة كركن مالي تعبدي ثالث بني عليه صرح الإسلام
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «بُنِيَ الْإِسْلَامُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلَاةِ، وَإِيتَاءِ الزَّكَاةِ، وَالْحَجِّ، وَصَوْمِ رَمَضَانَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (8) وصحيح مسلم (16)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الزكاة ركن مالي واجتماعي واقتصادي قطعي لا يقوم إسلام العبد إلا بأدائه، وهي ليست ضريبة بشرية بل فريضة إلهية تطهر النفوس وتنمي الثروة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 39 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="39 دستور الزكاة النبوي: تؤخذ من أغنياء المجتمع وترد إلى فقرائه عن عبد الله بن عباس رضي الله عنهما أن رسول الله ﷺ لما بعث معاذاً رضي الله عنه إلى اليمن قال: «فَأَعْلِمْهُمْ أَنَّ اللَّهَ افْتَرَضَ عَلَيْهِمْ صَدَقَةً فِي أَمْوَالِهِمْ، تُؤْخَذُ مِنْ أَغْنِيَائِهِمْ، وَتُرَدُّ عَلَى فُقَرَائِهِمْ» صحيح البخاري (1395) وصحيح مسلم (19) أصل التكافل المالي المحلي: أولوية توزيع حصيلة الزكاة على فقراء ومحتاجي البلد أو المنطقة التي جُمعت منها الأموال لتحقيق الاستقرار الاجتماعي ومحاربة الفقر. تؤخذ من أغنيائهم وترد على فقرائهم معاذ بن جبل فريضة الزكاة التكافل الاجتماعي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        39
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور الزكاة النبوي: تؤخذ من أغنياء المجتمع وترد إلى فقرائه
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عباس رضي الله عنهما أن رسول الله ﷺ لما بعث معاذاً رضي الله عنه إلى اليمن قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فَأَعْلِمْهُمْ أَنَّ اللَّهَ افْتَرَضَ عَلَيْهِمْ صَدَقَةً فِي أَمْوَالِهِمْ، تُؤْخَذُ مِنْ أَغْنِيَائِهِمْ، وَتُرَدُّ عَلَى فُقَرَائِهِمْ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1395) وصحيح مسلم (19)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أصل التكافل المالي المحلي: أولوية توزيع حصيلة الزكاة على فقراء ومحتاجي البلد أو المنطقة التي جُمعت منها الأموال لتحقيق الاستقرار الاجتماعي ومحاربة الفقر.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 40 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="40 تحديد أنصبة الزكاة الأساسية في النقد والمواشي والإنتاج الزراعي عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال: «لَيْسَ فِيمَا دُونَ خَمْسِ أَوَاقٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسِ ذَوْدٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسَةِ أَوْسُقٍ صَدَقَةٌ» صحيح البخاري (1405) وصحيح مسلم (979) حماية صغار الكسبة والملاك: لا تجب الزكاة إلا بعد بلوغ النصاب المحكم (5 أواق فضة = 595 غراماً، 5 جمال، 5 أوسق حبوب وثمار = حوالي 653 كغ). ليس فيما دون خمس أواق صدقة خمس ذود خمسة أوسق نصاب الزكاة الفضة الحبوب">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        40
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحديد أنصبة الزكاة الأساسية في النقد والمواشي والإنتاج الزراعي
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَيْسَ فِيمَا دُونَ خَمْسِ أَوَاقٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسِ ذَوْدٍ صَدَقَةٌ، وَلَيْسَ فِيمَا دُونَ خَمْسَةِ أَوْسُقٍ صَدَقَةٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1405) وصحيح مسلم (979)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حماية صغار الكسبة والملاك: لا تجب الزكاة إلا بعد بلوغ النصاب المحكم (5 أواق فضة = 595 غراماً، 5 جمال، 5 أوسق حبوب وثمار = حوالي 653 كغ).</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 41 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="41 تحديد النسبة المحاسبية الشرعية لزكاة الأموال والنقود (ربع العُشر 2.5%) عن أنس بن مالك رضي الله عنه أن أبا بكر الصديق رضي الله عنه كتب له كتاب فريضة الصدقة التي فرض رسول الله ﷺ وفيه: «وَفِي الرِّقَّةِ: رُبْعُ الْعُشْرِ، فَإِنْ لَمْ تَكُنْ إِلَّا تِسْعِينَ وَمِائَةً فَلَيْسَ فِيهَا شَيْءٌ إِلَّا أَنْ يَشَاءَ رَبُّهَا» صحيح البخاري (1454) المعيار المحاسبي والمالي الثابت في كافة البنوك والمؤسسات والشركات: نسبة زكاة النقد والأرصدة البنكية وعروض التجارة والأسهم الاستثمارية هي 2.5% سنوياً. وفي الرقة ربع العشر تسعين ومائة نسبة الزكاة 2.5% عروض التجارة الأسهم السيولة النقدية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        41
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحديد النسبة المحاسبية الشرعية لزكاة الأموال والنقود (ربع العُشر 2.5%)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أنس بن مالك رضي الله عنه أن أبا بكر الصديق رضي الله عنه كتب له كتاب فريضة الصدقة التي فرض رسول الله ﷺ وفيه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «وَفِي الرِّقَّةِ: رُبْعُ الْعُشْرِ، فَإِنْ لَمْ تَكُنْ إِلَّا تِسْعِينَ وَمِائَةً فَلَيْسَ فِيهَا شَيْءٌ إِلَّا أَنْ يَشَاءَ رَبُّهَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1454)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المعيار المحاسبي والمالي الثابت في كافة البنوك والمؤسسات والشركات: نسبة زكاة النقد والأرصدة البنكية وعروض التجارة والأسهم الاستثمارية هي 2.5% سنوياً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 42 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="42 تحديد نصاب الذهب بـ عشرين ديناراً (85 غراماً عيار 24) وشرط الحول عن علي بن أبي طالب رضي الله عنه عن النبي ﷺ قال: «إِذَا كَانَتْ لَكَ مِائَتَا دِرْهَمٍ وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا خَمْسَةُ دَرَاهِمَ، وَلَيْسَ عَلَيْكَ شَيْءٌ - يَعْنِي فِي الذَّهَبِ - حَتَّى يَكُونَ لَكَ عِشْرُونَ دِينَارًا، فَإِذَا كَانَتْ لَكَ عِشْرُونَ دِينَارًا وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا نِصْفُ دِينَارٍ» رواه أبو داود (1573) وصححه الألباني في صحيح أبي داود نصاب الذهب المعتمد عالمياً في الفقه الإسلامي هو 20 ديناراً ذهبياً يعادل 85 غراماً من الذهب الخالص، فإذا ملك المسلم قيمتها وحال عليها الحول وجب إخراج 2.5%. عشرون دينارا نصف دينار مائتا درهم نصاب الذهب 85 غرام عيار 24 حولان الحول">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        42
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحديد نصاب الذهب بـ عشرين ديناراً (85 غراماً عيار 24) وشرط الحول
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن علي بن أبي طالب رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِذَا كَانَتْ لَكَ مِائَتَا دِرْهَمٍ وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا خَمْسَةُ دَرَاهِمَ، وَلَيْسَ عَلَيْكَ شَيْءٌ - يَعْنِي فِي الذَّهَبِ - حَتَّى يَكُونَ لَكَ عِشْرُونَ دِينَارًا، فَإِذَا كَانَتْ لَكَ عِشْرُونَ دِينَارًا وَحَالَ عَلَيْهَا الْحَوْلُ فَفِيهَا نِصْفُ دِينَارٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (1573) وصححه الألباني في صحيح أبي داود</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">نصاب الذهب المعتمد عالمياً في الفقه الإسلامي هو 20 ديناراً ذهبياً يعادل 85 غراماً من الذهب الخالص، فإذا ملك المسلم قيمتها وحال عليها الحول وجب إخراج 2.5%.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 43 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="43 تفاوت زكاة الزروع والمحاصيل بحسب كلفة وتعب الري والتشغيل عن عبد الله بن عمر رضي الله عنهما عن النبي ﷺ قال: «فِيمَا سَقَتِ السَّمَاءُ وَالْعُيُونُ أَوْ كَانَ عَثَرِيًّا: الْعُشْرُ، وَمَا سُقِيَ بِالنَّضْحِ: نِصْفُ الْعُشْرِ» صحيح البخاري (1483) العدالة التشريعية في التكاليف الاقتصادية: المحاصيل المروية بماء المطر دون كلفة تخرج 10%، والمروية بالآبار والماكينات والمضخات المكلفة تخرج 5% تقديراً لنفقات الإنتاج. فيما سقت السماء العشر وما سقي بالنضح نصف العشر زكاة الزروع الثمار كلفة الري">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        43
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تفاوت زكاة الزروع والمحاصيل بحسب كلفة وتعب الري والتشغيل
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فِيمَا سَقَتِ السَّمَاءُ وَالْعُيُونُ أَوْ كَانَ عَثَرِيًّا: الْعُشْرُ، وَمَا سُقِيَ بِالنَّضْحِ: نِصْفُ الْعُشْرِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1483)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">العدالة التشريعية في التكاليف الاقتصادية: المحاصيل المروية بماء المطر دون كلفة تخرج 10%، والمروية بالآبار والماكينات والمضخات المكلفة تخرج 5% تقديراً لنفقات الإنتاج.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 44 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="44 فرض زكاة الفطر صاعاً من طعام وتعميمها على جميع فئات الأسرة عن عبد الله بن عمر رضي الله عنهما قال: «فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ صَاعًا مِنْ تَمْرٍ، أَوْ صَاعًا مِنْ شَعِيرٍ، عَلَى الْعَبْدِ وَالْحُرِّ، وَالذَّكَرِ وَالْأُنْثَى، وَالصَّغِيرِ وَالْكَبِيرِ مِنَ الْمُسْلِمِينَ، وَأَمَرَ بِهَا أَنْ تُؤَدَّى قَبْلَ خُرُوجِ النَّاسِ إِلَى الصَّلَاةِ» صحيح البخاري (1503) وصحيح مسلم (984) وجوب إخراج زكاة الفطر عن كل فرد يعوله المسلم قبل صلاة عيد الفطر، ومقدارها صاع (نحو 2.5 - 3 كغ) من غالب قوت البلد، لإغناء المحتاجين عن السؤال في يوم العيد. فرض رسول الله زكاة الفطر صاعا من تمر صاعا من شعير العبد والحر صلاة العيد">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        44
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فرض زكاة الفطر صاعاً من طعام وتعميمها على جميع فئات الأسرة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ صَاعًا مِنْ تَمْرٍ، أَوْ صَاعًا مِنْ شَعِيرٍ، عَلَى الْعَبْدِ وَالْحُرِّ، وَالذَّكَرِ وَالْأُنْثَى، وَالصَّغِيرِ وَالْكَبِيرِ مِنَ الْمُسْلِمِينَ، وَأَمَرَ بِهَا أَنْ تُؤَدَّى قَبْلَ خُرُوجِ النَّاسِ إِلَى الصَّلَاةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1503) وصحيح مسلم (984)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">وجوب إخراج زكاة الفطر عن كل فرد يعوله المسلم قبل صلاة عيد الفطر، ومقدارها صاع (نحو 2.5 - 3 كغ) من غالب قوت البلد، لإغناء المحتاجين عن السؤال في يوم العيد.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 45 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="45 حكمة زكاة الفطر: طهرة للصائم من اللغو والرفث وإطعام للمساكين عن عبد الله بن عباس رضي الله عنهما قال: «فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ طُهْرَةً لِلصَّائِمِ مِنَ اللَّغْوِ وَالرَّفَثِ، وَطُعْمَةً لِلْمَسَاكِينِ، مَنْ أَدَّاهَا قَبْلَ الصَّلَاةِ فَهِيَ زَكَاةٌ مَقْبُولَةٌ، وَمَنْ أَدَّاهَا بَعْدَ الصَّلَاةِ فَهِيَ صَدَقَةٌ مِنَ الصَّدَقَاتِ» رواه أبو داود (1609) وابن ماجه (1827) والحاكم وصححه، وصححه الألباني في صحيح أبي داود بيان الأثر الروحي والاجتماعي المزدوج لزكاة الفطر: تطهير عمل الصائم وسد حاجة الجائع، وتأكيد وقتها الشرعي المستحب قبل صلاة العيد. زكاة الفطر طهرة للصائم طعمة للمساكين قبل الصلاة صدقة من الصدقات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        45
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حكمة زكاة الفطر: طهرة للصائم من اللغو والرفث وإطعام للمساكين
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عباس رضي الله عنهما قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فَرَضَ رَسُولُ اللَّهِ ﷺ زَكَاةَ الْفِطْرِ طُهْرَةً لِلصَّائِمِ مِنَ اللَّغْوِ وَالرَّفَثِ، وَطُعْمَةً لِلْمَسَاكِينِ، مَنْ أَدَّاهَا قَبْلَ الصَّلَاةِ فَهِيَ زَكَاةٌ مَقْبُولَةٌ، وَمَنْ أَدَّاهَا بَعْدَ الصَّلَاةِ فَهِيَ صَدَقَةٌ مِنَ الصَّدَقَاتِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (1609) وابن ماجه (1827) والحاكم وصححه، وصححه الألباني في صحيح أبي داود</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">بيان الأثر الروحي والاجتماعي المزدوج لزكاة الفطر: تطهير عمل الصائم وسد حاجة الجائع، وتأكيد وقتها الشرعي المستحب قبل صلاة العيد.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 46 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="46 وعيد الله الشديد لمن كنز المال ومنع زكاته وتحوله لشجاع أقرع يعذبه عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «مَنْ آتَاهُ اللَّهُ مَالًا فَلَمْ يُؤَدِّ زَكَاتَهُ، مُثِّلَ لَهُ مَالُهُ يَوْمَ الْقِيَامَةِ شُجَاعًا أَقْرَعَ لَهُ زَبِيبَتَانِ يُطَوَّقُهُ يَوْمَ الْقِيَامَةِ، ثُمَّ يَأْخُذُ بِلِهْزِمَتَيْهِ - يَعْنِي بِشِدْقَيْهِ - ثُمَّ يَقُولُ: أَنَا مَالُكَ، أَنَا كَنْزُكَ» صحيح البخاري (1403) تحذير أصحاب الثروات والمستثمرين من التقاعس عن حساب وإخراج زكاة أموالهم، وأن المال غير المزكى ينقلب عذاباً وحسرة على صاحبه يوم الحساب. شجاعا أقرع له زبيبتان أنا مالك أنا كنزك عقوبة مانع الزكاة كنز الأموال">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        46
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وعيد الله الشديد لمن كنز المال ومنع زكاته وتحوله لشجاع أقرع يعذبه
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ آتَاهُ اللَّهُ مَالًا فَلَمْ يُؤَدِّ زَكَاتَهُ، مُثِّلَ لَهُ مَالُهُ يَوْمَ الْقِيَامَةِ شُجَاعًا أَقْرَعَ لَهُ زَبِيبَتَانِ يُطَوَّقُهُ يَوْمَ الْقِيَامَةِ، ثُمَّ يَأْخُذُ بِلِهْزِمَتَيْهِ - يَعْنِي بِشِدْقَيْهِ - ثُمَّ يَقُولُ: أَنَا مَالُكَ، أَنَا كَنْزُكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1403)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحذير أصحاب الثروات والمستثمرين من التقاعس عن حساب وإخراج زكاة أموالهم، وأن المال غير المزكى ينقلب عذاباً وحسرة على صاحبه يوم الحساب.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 47 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="47 تحريم أخذ أموال الزكاة للأغنياء والمكتفين والأقوياء القادرين على الكسب عن عبد الله بن عمرو رضي الله عنهما عن النبي ﷺ قال: «لَا تَحِلُّ الصَّدَقَةُ لِغَنِيٍّ، وَلَا لِذِي مِرَّةٍ سَوِيٍّ» رواه أبو داود (1634) والترمذي (652) والنسائي (2597) وصححه الألباني حصر استحقاق الزكاة في الضعفاء والعاجزين والغارمين الحقيقيين، وتحريم تحايل المقتدرين أو الأصحاء القادرين على العمل والإنتاج في طلب أموال الزكاة. لا تحل الصدقة لغني ولا لذي مرة سوي استحقاق الزكاة القادر على الكسب الغنى">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        47
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم أخذ أموال الزكاة للأغنياء والمكتفين والأقوياء القادرين على الكسب
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو رضي الله عنهما عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا تَحِلُّ الصَّدَقَةُ لِغَنِيٍّ، وَلَا لِذِي مِرَّةٍ سَوِيٍّ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (1634) والترمذي (652) والنسائي (2597) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حصر استحقاق الزكاة في الضعفاء والعاجزين والغارمين الحقيقيين، وتحريم تحايل المقتدرين أو الأصحاء القادرين على العمل والإنتاج في طلب أموال الزكاة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 48 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="48 اشتراط تمام الحول القمري (عام كامل) لوجوب زكاة الأموال والتجارة عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «لَا زَكَاةَ فِي مَالٍ حَتَّى يَحُولَ عَلَيْهِ الْحَوْلُ» رواه الترمذي (631) وأبو داود (1573) وابن ماجه (1792) وصححه الألباني بشواهده في الإرواء (787) الشرط المحاسبي الحاسم لزكاة الثروة النقدية والتجارية: مرور 354 يوماً (سنة هجرية قمرية) على بقاء المال بالغاً للنصاب، تيسيراً على الملاك والمستثمرين. لا زكاة في مال حتى يحول عليه الحول تمام الحول السنة القمرية الميزانية الزكوية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        48
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        اشتراط تمام الحول القمري (عام كامل) لوجوب زكاة الأموال والتجارة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا زَكَاةَ فِي مَالٍ حَتَّى يَحُولَ عَلَيْهِ الْحَوْلُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (631) وأبو داود (1573) وابن ماجه (1792) وصححه الألباني بشواهده في الإرواء (787)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الشرط المحاسبي الحاسم لزكاة الثروة النقدية والتجارية: مرور 354 يوماً (سنة هجرية قمرية) على بقاء المال بالغاً للنصاب، تيسيراً على الملاك والمستثمرين.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 49 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="49 مشروعية تعجيل إخراج الزكاة مقدماً قبل حلول الحول للمصلحة والأزمات عن علي بن أبي طالب رضي الله عنه: «أَنَّ الْعَبَّاسَ رَضِيَ اللَّهُ عَنْهُ سَأَلَ رَسُولَ اللَّهِ ﷺ فِي تَعْجِيلِ صَدَقَتِهِ قَبْلَ أَنْ تَحِلَّ، فَرَخَّصَ لَهُ فِي ذَلِكَ» رواه أبو داود (1624) والترمذي (678) وحسنه، وابن ماجه (1795) وصححه الحاكم والنووي والألباني جواز تقديم دفع الزكاة لعام أو عامين مقدماً في حالات الكوارث أو الحروب أو إغاثة المنكوبين ومشاريع الإغاثة العاجلة، كمرونة تمويلية إسلامية رائدة. تعجيل الصدقة قبل أن تحل العباس رضي الله عنه إخراج الزكاة مقدما الأزمات والكوارث">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        49
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية تعجيل إخراج الزكاة مقدماً قبل حلول الحول للمصلحة والأزمات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن علي بن أبي طالب رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَّ الْعَبَّاسَ رَضِيَ اللَّهُ عَنْهُ سَأَلَ رَسُولَ اللَّهِ ﷺ فِي تَعْجِيلِ صَدَقَتِهِ قَبْلَ أَنْ تَحِلَّ، فَرَخَّصَ لَهُ فِي ذَلِكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (1624) والترمذي (678) وحسنه، وابن ماجه (1795) وصححه الحاكم والنووي والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">جواز تقديم دفع الزكاة لعام أو عامين مقدماً في حالات الكوارث أو الحروب أو إغاثة المنكوبين ومشاريع الإغاثة العاجلة، كمرونة تمويلية إسلامية رائدة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 50 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="50 النهي عن أخذ نفائس وكرائم أموال الناس في جباية الزكاة والعدل مع المالك عن عبد الله بن عباس رضي الله عنهما في وصية النبي ﷺ لمعاذ لما بعثه لليمن: «فَإِيَّاكَ وَكَرَائِمَ أَمْوَالِهِمْ، وَاتَّقِ دَعْوَةَ الْمَظْلُومِ؛ فَإِنَّهُ لَيْسَ بَيْنَهَا وَبَيْنَ اللَّهِ حِجَابٌ» صحيح البخاري (1496) وصحيح مسلم (19) حظر التعسف في التقدير الزكوي أو الضريبي وأخذ خيار مال المكلف (كرائم أمواله)، بل يؤخذ الوسط العدل حماية لرأس مال التاجر والمستثمر من الجور. إياك وكرائم أموالهم دعوة المظلوم ليس بينها وبين الله حجاب جباية الزكاة العدالة المالية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        50
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن أخذ نفائس وكرائم أموال الناس في جباية الزكاة والعدل مع المالك
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عباس رضي الله عنهما في وصية النبي ﷺ لمعاذ لما بعثه لليمن:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فَإِيَّاكَ وَكَرَائِمَ أَمْوَالِهِمْ، وَاتَّقِ دَعْوَةَ الْمَظْلُومِ؛ فَإِنَّهُ لَيْسَ بَيْنَهَا وَبَيْنَ اللَّهِ حِجَابٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1496) وصحيح مسلم (19)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حظر التعسف في التقدير الزكوي أو الضريبي وأخذ خيار مال المكلف (كرائم أمواله)، بل يؤخذ الوسط العدل حماية لرأس مال التاجر والمستثمر من الجور.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 51 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="zakat" data-hadith-search="51 تحريم الحيل المحاسبية والتهرب الزكوي بتفريق المجتمع أو جمع المفترق عن أنس بن مالك رضي الله عنه في كتاب الصدقة الذي كتبه أبو بكر عن رسول الله ﷺ: «وَلَا يُجْمَعُ بَيْنَ مُتَفَرِّقٍ، وَلَا يُفَرَّقُ بَيْنَ مُجْتَمِعٍ، خَشْيَةَ الصَّدَقَةِ» صحيح البخاري (1450) التأصيل النبوي الصارم لمكافحة التهرب من الزكاة: تحريم تفريق الحسابات البنكية أو تشتيت رؤوس الأموال بين شركات وهمية صورية بقصد خفض الوعاء الزكوي. لا يجمع بين متفرق ولا يفرق بين مجتمع خشية الصدقة التهرب الزكوي الحيل المحاسبية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        51
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الحيل المحاسبية والتهرب الزكوي بتفريق المجتمع أو جمع المفترق
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-emerald-500/20 text-emerald-300 border-emerald-400/40">
                        🌾 فريضة الزكاة وأنصبتها
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أنس بن مالك رضي الله عنه في كتاب الصدقة الذي كتبه أبو بكر عن رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «وَلَا يُجْمَعُ بَيْنَ مُتَفَرِّقٍ، وَلَا يُفَرَّقُ بَيْنَ مُجْتَمِعٍ، خَشْيَةَ الصَّدَقَةِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1450)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التأصيل النبوي الصارم لمكافحة التهرب من الزكاة: تحريم تفريق الحسابات البنكية أو تشتيت رؤوس الأموال بين شركات وهمية صورية بقصد خفض الوعاء الزكوي.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 52 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="52 قانون البركة الرباني: نفي النقصان عن المال المتصدق به وزيادته حقيقة عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَا نَقَصَتْ صَدَقَةٌ مِنْ مَالٍ، وَمَا زَادَ اللَّهُ عَبْدًا بِعَفْوٍ إِلَّا عِزًّا، وَمَا تَوَاضَعَ أَحَدٌ لِلَّهِ إِلَّا رَفَعَهُ اللَّهُ» صحيح مسلم (2588) اليقين الاقتصادي الإسلامي بأن البذل والإنفاق لا ينقص رأس المال في المآل بل ينميه بالبركة ويدفع عنه الآفات والمصائب المهلكة. ما نقصت صدقة من مال بركة الصدقة نماء المال الإنفاق التواضع العفو">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        52
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قانون البركة الرباني: نفي النقصان عن المال المتصدق به وزيادته حقيقة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا نَقَصَتْ صَدَقَةٌ مِنْ مَالٍ، وَمَا زَادَ اللَّهُ عَبْدًا بِعَفْوٍ إِلَّا عِزًّا، وَمَا تَوَاضَعَ أَحَدٌ لِلَّهِ إِلَّا رَفَعَهُ اللَّهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (2588)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">اليقين الاقتصادي الإسلامي بأن البذل والإنفاق لا ينقص رأس المال في المآل بل ينميه بالبركة ويدفع عنه الآفات والمصائب المهلكة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 53 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="53 دعاء الملكين اليومي: الاستخلاف بالخلف للمنفق والهلاك والتلف للبخيل الممسك عن أبي هريرة رضي الله عنه أن النبي ﷺ قال: «مَا مِنْ يَوْمٍ يُصْبِحُ الْعِبَادُ فِيهِ، إِلَّا مَلَكَانِ يَنْزِلَانِ، فَيَقُولُ أَحَدُهُمَا: اللَّهُمَّ أَعْطِ مُنْفِقًا خَلَفًا، وَيَقُولُ الْآخَرُ: اللَّهُمَّ أَعْطِ مُمْسِكًا تَلَفًا» صحيح البخاري (1442) وصحيح مسلم (1010) قانون التدفق المالي الكوني: كثرة الإنفاق في وجوه الخير والمسؤولية المجتمعية تستجلب الخلف العاجل وتجدد رأس المال، والبخل والشح سبب لتلف الثروات. اللهم أعط منفقا خلفا أعط ممسكا تلفا دعاء الملكين البخل والشح المسؤولية الاجتماعية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        53
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دعاء الملكين اليومي: الاستخلاف بالخلف للمنفق والهلاك والتلف للبخيل الممسك
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا مِنْ يَوْمٍ يُصْبِحُ الْعِبَادُ فِيهِ، إِلَّا مَلَكَانِ يَنْزِلَانِ، فَيَقُولُ أَحَدُهُمَا: اللَّهُمَّ أَعْطِ مُنْفِقًا خَلَفًا، وَيَقُولُ الْآخَرُ: اللَّهُمَّ أَعْطِ مُمْسِكًا تَلَفًا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1442) وصحيح مسلم (1010)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">قانون التدفق المالي الكوني: كثرة الإنفاق في وجوه الخير والمسؤولية المجتمعية تستجلب الخلف العاجل وتجدد رأس المال، والبخل والشح سبب لتلف الثروات.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 54 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="54 فضل صدقة السر وإخفاء البذل المالي كأحد أسباب الظل في عرصات القيامة عن أبي هريرة رضي الله عنه في حديث السبعة الذين يظلهم الله في ظله قال ﷺ: «وَرَجُلٌ تَصَدَّقَ بِصَدَقَةٍ فَأَخْفَاهَا حَتَّى لَا تَعْلَمَ شِمَالُهُ مَا تُنْفِقُ يَمِينُهُ» صحيح البخاري (1423) وصحيح مسلم (1031) عظمة الإخلاص وتجريد النية في التبرعات المالية والأوقاف، والبعد عن الرياء والسمعة أو المن والأذى الذي يحبط أجر الصدقات ويثلم مروءة الفقراء. تصدق بصدقة فأخفاها لا تعلم شماله ما تنفق يمينه صدقة السر السبعة الذين يظلهم الله الإخلاص">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        54
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فضل صدقة السر وإخفاء البذل المالي كأحد أسباب الظل في عرصات القيامة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه في حديث السبعة الذين يظلهم الله في ظله قال ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «وَرَجُلٌ تَصَدَّقَ بِصَدَقَةٍ فَأَخْفَاهَا حَتَّى لَا تَعْلَمَ شِمَالُهُ مَا تُنْفِقُ يَمِينُهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1423) وصحيح مسلم (1031)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">عظمة الإخلاص وتجريد النية في التبرعات المالية والأوقاف، والبعد عن الرياء والسمعة أو المن والأذى الذي يحبط أجر الصدقات ويثلم مروءة الفقراء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 55 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="55 تزكية الثروة والغنى الحلال: نعم المال الصالح للرجل الصالح عن عمرو بن العاص رضي الله عنه قال: قال لي رسول الله ﷺ: «يَا عَمْرُو، نِعْمَ الْمَالُ الصَّالِحُ لِلْمَرْءِ الصَّالِحِ» رواه أحمد (17763) والبخاري في الأدب المفرد (299) وصححه ابن حبان (3210) والألباني تقرير شرعي حاسم بأن الإسلام لا يذم امتلاك الثروات ورؤوس الأموال الكبرى إذا اكتسبت من حلال وأُنفقت في حلال، بل هي قوة للمسلم وللأمة. نعم المال الصالح للمرء الصالح عمرو بن العاص الغنى الحلال كسب المال الثروة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        55
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تزكية الثروة والغنى الحلال: نعم المال الصالح للرجل الصالح
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عمرو بن العاص رضي الله عنه قال: قال لي رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «يَا عَمْرُو، نِعْمَ الْمَالُ الصَّالِحُ لِلْمَرْءِ الصَّالِحِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (17763) والبخاري في الأدب المفرد (299) وصححه ابن حبان (3210) والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تقرير شرعي حاسم بأن الإسلام لا يذم امتلاك الثروات ورؤوس الأموال الكبرى إذا اكتسبت من حلال وأُنفقت في حلال، بل هي قوة للمسلم وللأمة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 56 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="56 مشروعية الوقف الإسلامي والصدقة الجارية التي لا ينقطع أثرها بعد الموت عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «إِذَا مَاتَ الْإِنْسَانُ انْقَطَعَ عَنْهُ عَمَلُهُ إِلَّا مِنْ ثَلَاثَةٍ: إِلَّا مِنْ صَدَقَةٍ جَارِيَةٍ، أَوْ عِلْمٍ يُنْتَفَعُ بِهِ، أَوْ وَلَدٍ صَالِحٍ يَدْعُو لَهُ» صحيح مسلم (1631) التأسيس النبوي الأعظم لمنظومة الأوقاف الخيرية والاستثمارية المستدامة (المستشفيات، المدارس، آبار المياه، العقارات الوقفية) كأعظم ادخار مالي للمؤمن بعد وفاته. إذا مات الإنسان انقطع عمله إلا من ثلاثة صدقة جارية علم ينتفع به الوقف الخيري الاستدامة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        56
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية الوقف الإسلامي والصدقة الجارية التي لا ينقطع أثرها بعد الموت
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِذَا مَاتَ الْإِنْسَانُ انْقَطَعَ عَنْهُ عَمَلُهُ إِلَّا مِنْ ثَلَاثَةٍ: إِلَّا مِنْ صَدَقَةٍ جَارِيَةٍ، أَوْ عِلْمٍ يُنْتَفَعُ بِهِ، أَوْ وَلَدٍ صَالِحٍ يَدْعُو لَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (1631)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التأسيس النبوي الأعظم لمنظومة الأوقاف الخيرية والاستثمارية المستدامة (المستشفيات، المدارس، آبار المياه، العقارات الوقفية) كأعظم ادخار مالي للمؤمن بعد وفاته.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 57 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="57 فقه أولويات الموازنة المالية: أعظم الصدقات أجراً النفقة على الأهل والعيال عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «دِينَارٌ أَنْفَقْتَهُ فِي سَبِيلِ اللَّهِ، وَدِينَارٌ أَنْفَقْتَهُ فِي رَقَبَةٍ، وَدِينَارٌ تَصَدَّقْتَ بِهِ عَلَى مِسْكِينٍ، وَدِينَارٌ أَنْفَقْتَهُ عَلَى أَهْلِكَ، أَعْظَمُهَا أَجْرًا الَّذِي أَنْفَقْتَهُ عَلَى أَهْلِكَ» صحيح مسلم (995) ترتيب الأولويات في التخطيط المالي للأسرة: كفاية الأهل والأبناء والوفاء بحاجاتهم المعيشية الكريمة فريضة مقدمة في الأجر على صدقات التطوع الخارجية. دينار أنفقته في سبيل الله دينار أنفقته على أهلك أعظمها أجرا نفقة الأهل التخطيط المالي للأسرة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        57
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        فقه أولويات الموازنة المالية: أعظم الصدقات أجراً النفقة على الأهل والعيال
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح مسلم
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «دِينَارٌ أَنْفَقْتَهُ فِي سَبِيلِ اللَّهِ، وَدِينَارٌ أَنْفَقْتَهُ فِي رَقَبَةٍ، وَدِينَارٌ تَصَدَّقْتَ بِهِ عَلَى مِسْكِينٍ، وَدِينَارٌ أَنْفَقْتَهُ عَلَى أَهْلِكَ، أَعْظَمُهَا أَجْرًا الَّذِي أَنْفَقْتَهُ عَلَى أَهْلِكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح مسلم (995)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">ترتيب الأولويات في التخطيط المالي للأسرة: كفاية الأهل والأبناء والوفاء بحاجاتهم المعيشية الكريمة فريضة مقدمة في الأجر على صدقات التطوع الخارجية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 58 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="58 عزة اليد العليا المنفقة وفضل التعفف والكسب على التكفف والمسألة عن حكيم بن حزام رضي الله عنه عن النبي ﷺ قال: «الْيَدُ الْعُلْيَا خَيْرٌ مِنَ الْيَدِ السُّفْلَى، وَابْدَأْ بِمَنْ تَعُولُ، وَخَيْرُ الصَّدَقَةِ عَنْ ظَهْرِ غِنًى، وَمَنْ يَسْتَعْفِفْ يُعِفَّهُ اللَّهُ، وَمَنْ يَسْتَغْنِ يُغْنِهِ اللَّهُ» صحيح البخاري (1427) وصحيح مسلم (1053) الحث على الإنتاجية والعمل والاستقلال المالي لتكون يد المسلم معطية مانحة لا آخذة سائلة، واشتراط أن تكون الصدقة بعد استيفاء الكفاية الذاتية (عن ظهر غنى). اليد العليا خير من اليد السفلى ابدأ بمن تعول خير الصدقة عن ظهر غنى يستعفف يعفه الله">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        58
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عزة اليد العليا المنفقة وفضل التعفف والكسب على التكفف والمسألة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن حكيم بن حزام رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْيَدُ الْعُلْيَا خَيْرٌ مِنَ الْيَدِ السُّفْلَى، وَابْدَأْ بِمَنْ تَعُولُ، وَخَيْرُ الصَّدَقَةِ عَنْ ظَهْرِ غِنًى، وَمَنْ يَسْتَعْفِفْ يُعِفَّهُ اللَّهُ، وَمَنْ يَسْتَغْنِ يُغْنِهِ اللَّهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1427) وصحيح مسلم (1053)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الحث على الإنتاجية والعمل والاستقلال المالي لتكون يد المسلم معطية مانحة لا آخذة سائلة، واشتراط أن تكون الصدقة بعد استيفاء الكفاية الذاتية (عن ظهر غنى).</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 59 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="59 عدم احتقار أي قدر من البذل المالي: الوقاية من النار ولو بنصف تمرة عن عدي بن حاتم رضي الله عنه قال: سمعت النبي ﷺ يقول: «اتَّقُوا النَّارَ وَلَوْ بِشِقِّ تَمْرَةٍ، فَمَنْ لَمْ يَجِدْ فَبِكَلِمَةٍ طَيِّبَةٍ» صحيح البخاري (1417) وصحيح مسلم (1016) تشجيع ثقافة العطاء المالي الميسور للجميع دون استصغار، فكل مساهمة مالية مهما قلت تدفع الشر وتنجي صاحبها من المهالك يوم القيامة. اتقوا النار ولو بشق تمرة كلمة طيبة الصدقة القليلة ثقافة العطاء">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        59
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عدم احتقار أي قدر من البذل المالي: الوقاية من النار ولو بنصف تمرة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عدي بن حاتم رضي الله عنه قال: سمعت النبي ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «اتَّقُوا النَّارَ وَلَوْ بِشِقِّ تَمْرَةٍ، فَمَنْ لَمْ يَجِدْ فَبِكَلِمَةٍ طَيِّبَةٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1417) وصحيح مسلم (1016)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تشجيع ثقافة العطاء المالي الميسور للجميع دون استصغار، فكل مساهمة مالية مهما قلت تدفع الشر وتنجي صاحبها من المهالك يوم القيامة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 60 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="60 الصدقة تطفئ غضب الرب وتدفع ميتة السوء ومصائب المال عن أنس بن مالك رضي الله عنه قال: قال رسول الله ﷺ: «إِنَّ الصَّدَقَةَ لَتُطْفِئُ غَضَبَ الرَّبِّ، وَتَدْفَعُ مِيتَةَ السُّوءِ» رواه الترمذي (664) وحسنه، وصححه الألباني في صحيح الترغيب (864) الصدقة حصن حصين للأموال والأنفس، وهي صمام أمان لدفع تقلبات الزمان والمصائب الفجائية، وبوابة للمغفرة ورضا الله تعالى. إن الصدقة لتطفئ غضب الرب تدفع ميتة السوء حفظ الأموال دفع البلاء">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        60
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الصدقة تطفئ غضب الرب وتدفع ميتة السوء ومصائب المال
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أنس بن مالك رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ الصَّدَقَةَ لَتُطْفِئُ غَضَبَ الرَّبِّ، وَتَدْفَعُ مِيتَةَ السُّوءِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (664) وحسنه، وصححه الألباني في صحيح الترغيب (864)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">الصدقة حصن حصين للأموال والأنفس، وهي صمام أمان لدفع تقلبات الزمان والمصائب الفجائية، وبوابة للمغفرة ورضا الله تعالى.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 61 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="charity" data-hadith-search="61 الأثر النفسي للبذل والبخل: انشراح صدر المنفق وضيق صدر البخيل عن أبي هريرة رضي الله عنه أنه سمع رسول الله ﷺ يقول: «مَثَلُ الْبَخِيلِ وَالْمُنْفِقِ، كَمَثَلِ رَجُلَيْنِ عَلَيْهِمَا جُبَّتَانِ مِنْ حَدِيدٍ مِنْ ثُدِيِّهِمَا إِلَى تَرَاقِيهِمَا، فَأَمَّا الْمُنْفِقُ فَلَا يُنْفِقُ إِلَّا سَبَغَتْ أَوْ وَفَرَتْ عَلَى جِلْدِهِ... وَأَمَّا الْبَخِيلُ فَلَا يُرِيدُ أَنْ يُنْفِقَ شَيْئًا إِلَّا لَزِقَتْ كُلُّ حَلْقَةٍ مَكَانَهَا» صحيح البخاري (1443) وصحيح مسلم (1021) تصوير نبوي عبقري لراحة النفس وسعة الصدر والبركة التي يجدها منفق المال، بينما يعيش الشحيح البخيل في ضيق وهم وخوف دائم من الفقر. مثل البخيل والمنفق جبتان من حديد انشراح الصدر الشح البخل علم النفس المالي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        61
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        الأثر النفسي للبذل والبخل: انشراح صدر المنفق وضيق صدر البخيل
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-teal-500/20 text-teal-300 border-teal-400/40">
                        💎 صدقات وإنفاق ونماء
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أنه سمع رسول الله ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَثَلُ الْبَخِيلِ وَالْمُنْفِقِ، كَمَثَلِ رَجُلَيْنِ عَلَيْهِمَا جُبَّتَانِ مِنْ حَدِيدٍ مِنْ ثُدِيِّهِمَا إِلَى تَرَاقِيهِمَا، فَأَمَّا الْمُنْفِقُ فَلَا يُنْفِقُ إِلَّا سَبَغَتْ أَوْ وَفَرَتْ عَلَى جِلْدِهِ... وَأَمَّا الْبَخِيلُ فَلَا يُرِيدُ أَنْ يُنْفِقَ شَيْئًا إِلَّا لَزِقَتْ كُلُّ حَلْقَةٍ مَكَانَهَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1443) وصحيح مسلم (1021)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تصوير نبوي عبقري لراحة النفس وسعة الصدر والبركة التي يجدها منفق المال، بينما يعيش الشحيح البخيل في ضيق وهم وخوف دائم من الفقر.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 62 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="62 القانون النبوي الأساسي لقسمة التركات: ألحقوا الفرائض بأهلها عن عبد الله بن عباس رضي الله عنهما عن النبي ﷺ قال: «أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لِأَوْلَى رَجُلٍ ذَكَرٍ» صحيح البخاري (6732) وصحيح مسلم (1615) القاعدة الأم التي يبنى عليها علم المواريث والتركات بأكمله: البدء بأصحاب الفروض المقدرة في كتاب الله أولاً، ثم دفع الفائض من التركة إلى أقرب عصبة من الرجال الذكور. ألحقوا الفرائض بأهلها فما بقي فلأولى رجل ذكر قسمة التركات المواريث أصحاب الفروض العصبات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        62
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        القانون النبوي الأساسي لقسمة التركات: ألحقوا الفرائض بأهلها
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عباس رضي الله عنهما عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَلْحِقُوا الْفَرَائِضَ بِأَهْلِهَا، فَمَا بَقِيَ فَهُوَ لِأَوْلَى رَجُلٍ ذَكَرٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (6732) وصحيح مسلم (1615)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">القاعدة الأم التي يبنى عليها علم المواريث والتركات بأكمله: البدء بأصحاب الفروض المقدرة في كتاب الله أولاً، ثم دفع الفائض من التركة إلى أقرب عصبة من الرجال الذكور.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 63 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="63 تحديد سقف الوصية بالثلث كحد أقصى وأولوية إغناء الورثة وكفايتهم عن سعد بن أبي وقاص رضي الله عنه قال: قلت: يا رسول الله، أوصي بمالي كله؟ قال: لا، قلت: فالشطر؟ قال: لا، قلت: فالثلث؟ قال: «فَالثُّلُثُ، وَالثُّلُثُ كَثِيرٌ، إِنَّكَ أَنْ تَذَرَ وَرَثَتَكَ أَغْنِيَاءَ خَيْرٌ مِنْ أَنْ تَذَرَهُمْ عَالَةً يَتَكَفَّفُونَ النَّاسَ» صحيح البخاري (2742) وصحيح مسلم (1628) ضابط جوهري في إدارة الثروات العائلية: منع التبرع بالوصايا لما زاد عن ثلث التركة بعد سداد الديون، وتفضيل توريث المال للأبناء والأهل على التبرع الخارجي لحمايتهم من الفقر. الثلث والثلث كثير أن تذر ورثتك أغنياء خير من أن تذرهم عالة سعد بن أبي وقاص سقف الوصية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        63
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحديد سقف الوصية بالثلث كحد أقصى وأولوية إغناء الورثة وكفايتهم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن سعد بن أبي وقاص رضي الله عنه قال: قلت: يا رسول الله، أوصي بمالي كله؟ قال: لا، قلت: فالشطر؟ قال: لا، قلت: فالثلث؟ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «فَالثُّلُثُ، وَالثُّلُثُ كَثِيرٌ، إِنَّكَ أَنْ تَذَرَ وَرَثَتَكَ أَغْنِيَاءَ خَيْرٌ مِنْ أَنْ تَذَرَهُمْ عَالَةً يَتَكَفَّفُونَ النَّاسَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2742) وصحيح مسلم (1628)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">ضابط جوهري في إدارة الثروات العائلية: منع التبرع بالوصايا لما زاد عن ثلث التركة بعد سداد الديون، وتفضيل توريث المال للأبناء والأهل على التبرع الخارجي لحمايتهم من الفقر.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 64 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="64 تحريم الوصية لأي وارث منعاً للظلم وتغيير أنصبة الميراث الإلهية عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول في خطبته عام حجة الوداع: «إِنَّ اللَّهَ قَدْ أَعْطَى كُلَّ ذِي حَقٍّ حَقَّهُ، فَلَا وَصِيَّةَ لِوَارِثٍ» رواه أبو داود (2870) والترمذي (2120) وابن ماجه (2713) وصححه الألباني في الإرواء (1655) إبطال أي وصية مخصصة لأحد الورثة الشرعيين تميزه عن بقية إخوته أو شركائه في الإرث، حماية للروابط الأسرية من النزاع والحسد والجور. إن الله قد أعطى كل ذي حق حقه فلا وصية لوارث حجة الوداع بطلان الوصية للوارث">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        64
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الوصية لأي وارث منعاً للظلم وتغيير أنصبة الميراث الإلهية
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي أمامة الباهلي رضي الله عنه قال: سمعت رسول الله ﷺ يقول في خطبته عام حجة الوداع:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ قَدْ أَعْطَى كُلَّ ذِي حَقٍّ حَقَّهُ، فَلَا وَصِيَّةَ لِوَارِثٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (2870) والترمذي (2120) وابن ماجه (2713) وصححه الألباني في الإرواء (1655)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إبطال أي وصية مخصصة لأحد الورثة الشرعيين تميزه عن بقية إخوته أو شركائه في الإرث، حماية للروابط الأسرية من النزاع والحسد والجور.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 65 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="65 وجوب توثيق الوصايا والحقوق والديون كتابةً دون تأخير عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال: «مَا حَقُّ امْرِئٍ مُسْلِمٍ لَهُ شَيْءٌ يُوصِي فِيهِ، يَبِيتُ لَيْلَتَيْنِ إِلَّا وَوَصِيَّتُهُ مَكْتُوبَةٌ عِنْدَهُ» صحيح البخاري (2738) وصحيح مسلم (1627) أمر نبوي حاسم بتدوين المستندات المالية والوصايا والديون والأمانات والحسابات المعلقة خطياً ومحاسبياً، تحسباً للموت المفاجئ وضماناً لحقوق الدائنين والورثة. ما حق امرئ مسلم له شيء يوصي فيه يبيت ليلتين إلا ووصيته مكتوبة عنده توثيق الوصية الحقوق">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        65
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب توثيق الوصايا والحقوق والديون كتابةً دون تأخير
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا حَقُّ امْرِئٍ مُسْلِمٍ لَهُ شَيْءٌ يُوصِي فِيهِ، يَبِيتُ لَيْلَتَيْنِ إِلَّا وَوَصِيَّتُهُ مَكْتُوبَةٌ عِنْدَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2738) وصحيح مسلم (1627)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أمر نبوي حاسم بتدوين المستندات المالية والوصايا والديون والأمانات والحسابات المعلقة خطياً ومحاسبياً، تحسباً للموت المفاجئ وضماناً لحقوق الدائنين والورثة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 66 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="66 موانع الميراث الشرعية: اختلاف الدين يمنع التوارث التلقائي في التركات عن أسامة بن زيد رضي الله عنهما أن النبي ﷺ قال: «لَا يَرِثُ الْمُسْلِمُ الْكَافِرَ، وَلَا الْكَافِرُ الْمُسْلِمَ» صحيح البخاري (6764) وصحيح مسلم (1614) المانع الأول من موانع الإرث في الفقه الإسلامي: اختلاف الملة يمنع استحقاق الفريضة المقدرة في التركة، ويبقى التبرع عبر الوصية في حدود الثلث جائزاً لغير الوارث. لا يرث المسلم الكافر ولا الكافر المسلم موانع الميراث اختلاف الدين التركات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        66
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        موانع الميراث الشرعية: اختلاف الدين يمنع التوارث التلقائي في التركات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أسامة بن زيد رضي الله عنهما أن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يَرِثُ الْمُسْلِمُ الْكَافِرَ، وَلَا الْكَافِرُ الْمُسْلِمَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (6764) وصحيح مسلم (1614)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المانع الأول من موانع الإرث في الفقه الإسلامي: اختلاف الملة يمنع استحقاق الفريضة المقدرة في التركة، ويبقى التبرع عبر الوصية في حدود الثلث جائزاً لغير الوارث.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 67 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="67 موانع الميراث: حرمان القاتل من تركة مورّثه منعاً للاستعجال غير المشروع عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «الْقَاتِلُ لَا يَرِثُ» وفي رواية: «لَيْسَ لِلْقَاتِلِ مِنَ الْمِيرَاثِ شَيْءٌ» رواه الترمذي (2109) وابن ماجه (2645) وصححه الألباني في الإرواء (1672) القاعدة الفقهية الجنائية والمالية الكبرى: (من استعجل الشيء قبل أوانه عوقب بحرمانه)، فإذا قتل الوارث مورثه سقط حقه في الميراث والدية كلياً. القاتل لا يرث ليس للقاتل من الميراث شيء موانع الإرث من استعجل الشيء قبل أوانه">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        67
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        موانع الميراث: حرمان القاتل من تركة مورّثه منعاً للاستعجال غير المشروع
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْقَاتِلُ لَا يَرِثُ» وفي رواية: «لَيْسَ لِلْقَاتِلِ مِنَ الْمِيرَاثِ شَيْءٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (2109) وابن ماجه (2645) وصححه الألباني في الإرواء (1672)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">القاعدة الفقهية الجنائية والمالية الكبرى: (من استعجل الشيء قبل أوانه عوقب بحرمانه)، فإذا قتل الوارث مورثه سقط حقه في الميراث والدية كلياً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 68 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="68 قضاء النبي ﷺ بفرض السُّدس للجدة عند عدم وجود الأم عن قبيصة بن ذؤيب رضي الله عنه قال: «جَاءَتِ الْجَدَّةُ إِلَى أَبِي بَكْرٍ الصِّدِّيقِ تَسْأَلُهُ مِيرَاثَهَا، فَقَالَ: مَا لَكِ فِي كِتَابِ اللَّهِ شَيْءٌ... حَتَّى شَهِدَ الْمُغِيرَةُ بْنُ شُعْبَةَ وَمُحَمَّدُ بْنُ مَسْلَمَةَ أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَاهَا السُّدُسَ، فَأَنْفَذَهُ لَهَا أَبُو بَكْرٍ» رواه أبو داود (2894) والترمذي (2101) وصححه، وابن ماجه (2724) وصححه الألباني بيان فرض الجدة الشرعي الثابت بالسنة النبوية وإجماع الصحابة (السدس)، تقديراً للأمومة الكبرى عند فقد الأم المباشرة. ميراث الجدة السدس قبيصة بن ذؤيب المغيرة بن شعبة أصحاب الفروض">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        68
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قضاء النبي ﷺ بفرض السُّدس للجدة عند عدم وجود الأم
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن قبيصة بن ذؤيب رضي الله عنه قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «جَاءَتِ الْجَدَّةُ إِلَى أَبِي بَكْرٍ الصِّدِّيقِ تَسْأَلُهُ مِيرَاثَهَا، فَقَالَ: مَا لَكِ فِي كِتَابِ اللَّهِ شَيْءٌ... حَتَّى شَهِدَ الْمُغِيرَةُ بْنُ شُعْبَةَ وَمُحَمَّدُ بْنُ مَسْلَمَةَ أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَاهَا السُّدُسَ، فَأَنْفَذَهُ لَهَا أَبُو بَكْرٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (2894) والترمذي (2101) وصححه، وابن ماجه (2724) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">بيان فرض الجدة الشرعي الثابت بالسنة النبوية وإجماع الصحابة (السدس)، تقديراً للأمومة الكبرى عند فقد الأم المباشرة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 69 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="69 قضاء النبوة في ميراث الابنة وبنت الابن والأخت (تكملة الثلثين) عن عبد الله بن مسعود رضي الله عنه في قضائه المشهور الذي وافق قضاء رسول الله ﷺ قال: «لِلِابْنَةِ النِّصْفُ، وَلِابْنَةِ الِابْنِ السُّدُسُ تَكْمِلَةَ الثُّلُثَيْنِ، وَمَا بَقِيَ فَلِلْأُخْتِ» صحيح البخاري (6736) تطبيق حسابي فرائضي بالغ الدقة: إذا اجتمعت ابنة واحدة صلبة مع بنت ابن، فللصلبية النصف، ولبنت الابن السدس استيفاءً لحد الثلثين المخصص لإناث البنات، والباقي تعصيباً للأخت. للابنة النصف ولبنت الابن السدس تكملة الثلثين وما بقي فللأخت ابن مسعود الفرائض">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        69
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قضاء النبوة في ميراث الابنة وبنت الابن والأخت (تكملة الثلثين)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن مسعود رضي الله عنه في قضائه المشهور الذي وافق قضاء رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لِلِابْنَةِ النِّصْفُ، وَلِابْنَةِ الِابْنِ السُّدُسُ تَكْمِلَةَ الثُّلُثَيْنِ، وَمَا بَقِيَ فَلِلْأُخْتِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (6736)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تطبيق حسابي فرائضي بالغ الدقة: إذا اجتمعت ابنة واحدة صلبة مع بنت ابن، فللصلبية النصف، ولبنت الابن السدس استيفاءً لحد الثلثين المخصص لإناث البنات، والباقي تعصيباً للأخت.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 70 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="70 حرمة بيع أسباب الميراث: الولاء لحمة كلحمة النسب لا يباع ولا يوهب عن عبد الله بن عمر رضي الله عنهما قال: «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْوَلَاءِ، وَعَنْ هِبَتِهِ» صحيح البخاري (2535) وصحيح مسلم (1506) عدم جواز المتاجرة أو التنازل بمقابل مالي عن حقوق وأسباب التوارث والنسب، لأن أسباب الميراث في الشريعة صلات قرابة ثابتة بحكم الشرع لا تقبل المعاوضة التجارية. نهى عن بيع الولاء وعن هبته لحمة كلحمة النسب تجارة المواريث المتاجرة بالحقوق">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        70
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حرمة بيع أسباب الميراث: الولاء لحمة كلحمة النسب لا يباع ولا يوهب
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «نَهَى رَسُولُ اللَّهِ ﷺ عَنْ بَيْعِ الْوَلَاءِ، وَعَنْ هِبَتِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2535) وصحيح مسلم (1506)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">عدم جواز المتاجرة أو التنازل بمقابل مالي عن حقوق وأسباب التوارث والنسب، لأن أسباب الميراث في الشريعة صلات قرابة ثابتة بحكم الشرع لا تقبل المعاوضة التجارية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 71 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="inheritance" data-hadith-search="71 كفالة الدولة النبوية لديون المتوفى المعسر وتسليم صافي التركة للورثة عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «مَنْ تَرَكَ مَالًا فَلِوَرَثَتِهِ، وَمَنْ تَرَكَ كَلًّا أَوْ ضَيَاعًا فَإِلَيَّ، وَعَلَيَّ» صحيح البخاري (2298) وصحيح مسلم (1619) أصل الضمان الاجتماعي الحكومي الإسلامي: سلامة تركة المتوفى وخلوصها لورثته، وتكفل بيت مال الدولة بسداد ديون المعسرين الذين ماتوا وعجزت تركاتهم عن الوفاء. من ترك مالا فلورثته ومن ترك كلا أو ضياعا فإلي وعلي ديون الميت الضمان الاجتماعي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        71
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        كفالة الدولة النبوية لديون المتوفى المعسر وتسليم صافي التركة للورثة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-purple-500/20 text-purple-300 border-purple-400/40">
                        📜 مواريث وفرائض ووصايا
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ تَرَكَ مَالًا فَلِوَرَثَتِهِ، وَمَنْ تَرَكَ كَلًّا أَوْ ضَيَاعًا فَإِلَيَّ، وَعَلَيَّ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2298) وصحيح مسلم (1619)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أصل الضمان الاجتماعي الحكومي الإسلامي: سلامة تركة المتوفى وخلوصها لورثته، وتكفل بيت مال الدولة بسداد ديون المعسرين الذين ماتوا وعجزت تركاتهم عن الوفاء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 72 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="72 وجوب المبادرة الفورية بدفع أجور العمال والموظفين دون مماطلة عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ: «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ» رواه ابن ماجه (2443) وصححه الألباني في الإرواء (1498) إلزام أصحاب العمل والمنشآت بتحويل رواتب ومستحقات الموظفين والعمال في مواعيدها المحددة فور الانتهاء من أداء العمل، وتجريم تأخير الرواتب بحجج واهية. أعطوا الأجير أجره قبل أن يجف عرقه أجور العمال الرواتب نظام حماية الأجور حقوق العمال">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        72
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب المبادرة الفورية بدفع أجور العمال والموظفين دون مماطلة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَعْطُوا الْأَجِيرَ أَجْرَهُ قَبْلَ أَنْ يَجِفَّ عَرَقُهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه ابن ماجه (2443) وصححه الألباني في الإرواء (1498)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إلزام أصحاب العمل والمنشآت بتحويل رواتب ومستحقات الموظفين والعمال في مواعيدها المحددة فور الانتهاء من أداء العمل، وتجريم تأخير الرواتب بحجج واهية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 73 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="73 خصومة الله العظمى يوم القيامة لمن استوفى جهد العامل وبخسه حقه عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: قال الله تعالى: «ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ: رَجُلٌ أَعْطَى بِي ثُمَّ غَدَرَ، وَرَجُلٌ بَاعَ حُرًّا فَأَكَلَ ثَمَنَهُ، وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ» صحيح البخاري (2227) وعيد إلهي مرعب لأي مدير أو صاحب شركة يستغل حاجة العمال فيكلفهم بأعمال شاقة ثم ينكر مستحقاتهم أو يقتطع منها ظلماً وعدواناً. ثلاثة أنا خصمهم يوم القيامة استأجر أجيرا فاستوفى منه ولم يعطه أجره بخس الأجور">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        73
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        خصومة الله العظمى يوم القيامة لمن استوفى جهد العامل وبخسه حقه
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: قال الله تعالى:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «ثَلَاثَةٌ أَنَا خَصْمُهُمْ يَوْمَ الْقِيَامَةِ: رَجُلٌ أَعْطَى بِي ثُمَّ غَدَرَ، وَرَجُلٌ بَاعَ حُرًّا فَأَكَلَ ثَمَنَهُ، وَرَجُلٌ اسْتَأْجَرَ أَجِيرًا فَاسْتَوْفَى مِنْهُ وَلَمْ يُعْطِهِ أَجْرَهُ»
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
                    <span class="text-slate-200 font-bold">وعيد إلهي مرعب لأي مدير أو صاحب شركة يستغل حاجة العمال فيكلفهم بأعمال شاقة ثم ينكر مستحقاتهم أو يقتطع منها ظلماً وعدواناً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 74 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="74 بركة الأمانة والشفافية بين الشركاء وخروج البركة وحلول الخسارة بالخيانة عن أبي هريرة رضي الله عنه رفعه قال: إن الله يقول: «أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا» رواه أبو داود (3383) والحاكم (2322) وصححه، وصححه الألباني في غاية المرام (329) ميثاق الشراكة والمضاربة والمساهمة الاستثمارية: التوفيق الإلهي والربح يرافق الشركاء ما دامت الحسابات مكشوفة وصادقة، وتنزع البركة فور إخفاء الأرباح أو التلاعب بالدفاتر. أنا ثالث الشريكين ما لم يخن أحدهما صاحبه عقد الشراكة المضاربة الاستثمار الأمانة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        74
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        بركة الأمانة والشفافية بين الشركاء وخروج البركة وحلول الخسارة بالخيانة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه رفعه قال: إن الله يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَا ثَالِثُ الشَّرِيكَيْنِ مَا لَمْ يَخُنْ أَحَدُهُمَا صَاحِبَهُ، فَإِذَا خَانَهُ خَرَجْتُ مِنْ بَيْنِهِمَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3383) والحاكم (2322) وصححه، وصححه الألباني في غاية المرام (329)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">ميثاق الشراكة والمضاربة والمساهمة الاستثمارية: التوفيق الإلهي والربح يرافق الشركاء ما دامت الحسابات مكشوفة وصادقة، وتنزع البركة فور إخفاء الأرباح أو التلاعب بالدفاتر.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 75 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="75 إلزامية معايير الجودة والإتقان المهني والمالي في الأعمال والخدمات عن عائشة رضي الله عنها أن رسول الله ﷺ قال: «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ» رواه البيهقي في شعب الإيمان (4929) وأبو يعلى (4386) وصححه الألباني في السلسلة الصحيحة (1113) أصل الجودة وإدارة التميز التشغيلي في الفكر الإسلامي: وجوب مطابقة المواصفات الهندسية والتجارية، وتحريم الغش في المواد الخام أو إنهاء الأعمال بنواقص معيبة. إن الله يحب إذا عمل أحدكم عملا أن يتقنه معايير الجودة الإتقان المهني مواصفات العمل">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        75
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        إلزامية معايير الجودة والإتقان المهني والمالي في الأعمال والخدمات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عائشة رضي الله عنها أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ يُحِبُّ إِذَا عَمِلَ أَحَدُكُمْ عَمَلًا أَنْ يُتْقِنَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه البيهقي في شعب الإيمان (4929) وأبو يعلى (4386) وصححه الألباني في السلسلة الصحيحة (1113)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أصل الجودة وإدارة التميز التشغيلي في الفكر الإسلامي: وجوب مطابقة المواصفات الهندسية والتجارية، وتحريم الغش في المواد الخام أو إنهاء الأعمال بنواقص معيبة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 76 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="76 وجوب تحديد الراتب والمهام الوظيفية بوضوح تام قبل مباشرة العمل عن أبي سعيد الخدري رضي الله عنه أن النبي ﷺ قال: «مَنِ اسْتَأْجَرَ أَجِيرًا، فَلْيُعْلِمْهُ أَجْرَهُ» رواه أحمد (11525) والبيهقي في السنن الكبرى (12071) وصححه الألباني في الإرواء (1497) اشتراط وضوح بنود عقد العمل (توصيف الوظيفة، ساعات الدوام، الراتب الإجمالي والبدلات) لقطع النزاع والجهالة والغرر في سوق العمل والتوظيف. من استأجر أجيرا فليعلمه أجره تحديد الراتب عقد العمل التوصيف الوظيفي وضوح الأجر">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        76
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب تحديد الراتب والمهام الوظيفية بوضوح تام قبل مباشرة العمل
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه أن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنِ اسْتَأْجَرَ أَجِيرًا، فَلْيُعْلِمْهُ أَجْرَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (11525) والبيهقي في السنن الكبرى (12071) وصححه الألباني في الإرواء (1497)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">اشتراط وضوح بنود عقد العمل (توصيف الوظيفة، ساعات الدوام، الراتب الإجمالي والبدلات) لقطع النزاع والجهالة والغرر في سوق العمل والتوظيف.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 77 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="77 تحريم الاستئجار والتكسب من المهن المحرمة والأنشطة غير الأخلاقية عن أبي مسعود الأنصاري رضي الله عنه: «أَنَّ رَسُولَ اللَّهِ ﷺ نَهَى عَنْ ثَمَنِ الْكَلْبِ، وَمَهْرِ الْبَغِيِّ، وَحُلْوَانِ الْكَاهِنِ» صحيح البخاري (2237) وصحيح مسلم (1567) بطلان كافة عقود العمل والتوظيف في الأنشطة المحرمة كالرهانات وتجارة المخدرات أو المنصات الإباحية والربوية، واعتبار الدخل الناتج عنها سحتاً باطلاً. نهى عن ثمن الكلب ومهر البغي وحلوان الكاهن المهن المحرمة الكسب الحرام بطلان العقد">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        77
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم الاستئجار والتكسب من المهن المحرمة والأنشطة غير الأخلاقية
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي مسعود الأنصاري رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَّ رَسُولَ اللَّهِ ﷺ نَهَى عَنْ ثَمَنِ الْكَلْبِ، وَمَهْرِ الْبَغِيِّ، وَحُلْوَانِ الْكَاهِنِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2237) وصحيح مسلم (1567)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">بطلان كافة عقود العمل والتوظيف في الأنشطة المحرمة كالرهانات وتجارة المخدرات أو المنصات الإباحية والربوية، واعتبار الدخل الناتج عنها سحتاً باطلاً.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 78 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="78 النهي عن إجارة قفيز الطحان (أخذ الأجر من عين العمل المعرض للتلف) عن أبي سعيد الخدري رضي الله عنه: «أَنَّ النَّبِيَّ ﷺ نَهَى عَنْ عَسِيبِ الْفَحْلِ، وَعَنْ قَفِيزِ الطَّحَّانِ» رواه الدارقطني (2998) والبيهقي في السنن (11984) وصححه الألباني في الإرواء (1492) قاعدة فقهية في تحديد الأجرة: اشتراط أن تكون الأجرة معلومة ومضمونة ومستقلة، وألا تكون جزءاً مجهولاً من عين ما ينتجه العمل إن كان عرضة للتلف دون ضمان. قفيز الطحان عسيب الفحل تحديد الأجرة ضمان الأجر عقود التشغيل">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        78
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        النهي عن إجارة قفيز الطحان (أخذ الأجر من عين العمل المعرض للتلف)
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح بمجموع طرقه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَّ النَّبِيَّ ﷺ نَهَى عَنْ عَسِيبِ الْفَحْلِ، وَعَنْ قَفِيزِ الطَّحَّانِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الدارقطني (2998) والبيهقي في السنن (11984) وصححه الألباني في الإرواء (1492)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">قاعدة فقهية في تحديد الأجرة: اشتراط أن تكون الأجرة معلومة ومضمونة ومستقلة، وألا تكون جزءاً مجهولاً من عين ما ينتجه العمل إن كان عرضة للتلف دون ضمان.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 79 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="79 أعظم الكسب والرزق شرفاً وبركة: ما كسبه العبد من كدّ يده وعمله عن المقدام بن معديكرب رضي الله عنه عن النبي ﷺ قال: «مَا أَكَلَ أَحَدٌ طَعَامًا قَطُّ، خَيْرًا مِنْ أَنْ يَأْكُلَ مِنْ عَمَلِ يَدِهِ، وَإِنَّ نَبِيَّ اللَّهِ دَاوُدَ عَلَيْهِ السَّلَامُ كَانَ يَأْكُلُ مِنْ عَمَلِ يَدِهِ» صحيح البخاري (2072) التعظيم النبوي للعمل اليدوي والمهني والصناعي والحرفي، وبيان أن الاعتماد على الكسب الذاتي والشرف المهني هو منهاج الأنبياء والمرسلين. ما أكل أحد طعاما قط خيرا من أن يأكل من عمل يده نبي الله داود كسب اليد العمل الحرفي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        79
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        أعظم الكسب والرزق شرفاً وبركة: ما كسبه العبد من كدّ يده وعمله
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن المقدام بن معديكرب رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا أَكَلَ أَحَدٌ طَعَامًا قَطُّ، خَيْرًا مِنْ أَنْ يَأْكُلَ مِنْ عَمَلِ يَدِهِ، وَإِنَّ نَبِيَّ اللَّهِ دَاوُدَ عَلَيْهِ السَّلَامُ كَانَ يَأْكُلُ مِنْ عَمَلِ يَدِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2072)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التعظيم النبوي للعمل اليدوي والمهني والصناعي والحرفي، وبيان أن الاعتماد على الكسب الذاتي والشرف المهني هو منهاج الأنبياء والمرسلين.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 80 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="labor" data-hadith-search="80 شرف المهن والإنتاج: ما بعث الله نبياً إلا رعى الغنم وتكسب بجهده عن أبي هريرة رضي الله عنه عن النبي ﷺ قال: «مَا بَعَثَ اللَّهُ نَبِيًّا إِلَّا رَعَى الْغَنَمَ، فَقَالَ أَصْحَابُهُ: وَأَنْتَ؟ فَقَالَ: نَعَمْ، كُنْتُ أَرْعَاهَا عَلَى قَرَارِيطَ لِأَهْلِ مَكَّةَ» صحيح البخاري (2262) إرساء التواضع المهني ومحاربة ثقافة العيب في العمل الميداني والإنتاجي، وإثبات أن أعظم الخلق ﷺ عمل أجيراً بأجر محدد (قراريط) قبل النبوة. ما بعث الله نبيا إلا رعى الغنم قراريط لأهل مكة ثقافة العمل الميداني التواضع المهني">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        80
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        شرف المهن والإنتاج: ما بعث الله نبياً إلا رعى الغنم وتكسب بجهده
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-fuchsia-500/20 text-fuchsia-300 border-fuchsia-400/40">
                        👥 أجور وعمال وشركات
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا بَعَثَ اللَّهُ نَبِيًّا إِلَّا رَعَى الْغَنَمَ، فَقَالَ أَصْحَابُهُ: وَأَنْتَ؟ فَقَالَ: نَعَمْ، كُنْتُ أَرْعَاهَا عَلَى قَرَارِيطَ لِأَهْلِ مَكَّةَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2262)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">إرساء التواضع المهني ومحاربة ثقافة العيب في العمل الميداني والإنتاجي، وإثبات أن أعظم الخلق ﷺ عمل أجيراً بأجر محدد (قراريط) قبل النبوة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 81 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="81 دستور عقد السَّلَم والتمويل المسبق للإنتاج وضوابطه الصارمة عن عبد الله بن عباس رضي الله عنهما قال: قَدِمَ النبيُّ ﷺ المدينةَ وهُمْ يُسْلِفُونَ بالتَّمْرِ السَّنَتَيْنِ والثَّلَاثَ، فقال: «مَنْ أَسْلَفَ فِي شَيْءٍ، فَفِي كَيْلٍ مَعْلُومٍ، وَوَزْنٍ مَعْلُومٍ، إِلَى أَجَلٍ مَعْلُومٍ» صحيح البخاري (2240) وصحيح مسلم (1604) أركان عقد السلم الاستثماري المعتمد في البنوك الإسلامية: دفع الثمن نقداً بالكامل مقدماً لتوفير السيولة للجهة الصانعة أو المزارعة، مع التحديد القاطع للمواصفات والكمية وتاريخ التسليم لمنع النزاع. من أسلف في شيء ففي كيل معلوم ووزن معلوم إلى أجل معلوم عقد السلم التمويل الإسلامي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        81
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور عقد السَّلَم والتمويل المسبق للإنتاج وضوابطه الصارمة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عباس رضي الله عنهما قال: قَدِمَ النبيُّ ﷺ المدينةَ وهُمْ يُسْلِفُونَ بالتَّمْرِ السَّنَتَيْنِ والثَّلَاثَ، فقال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَسْلَفَ فِي شَيْءٍ، فَفِي كَيْلٍ مَعْلُومٍ، وَوَزْنٍ مَعْلُومٍ، إِلَى أَجَلٍ مَعْلُومٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2240) وصحيح مسلم (1604)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أركان عقد السلم الاستثماري المعتمد في البنوك الإسلامية: دفع الثمن نقداً بالكامل مقدماً لتوفير السيولة للجهة الصانعة أو المزارعة، مع التحديد القاطع للمواصفات والكمية وتاريخ التسليم لمنع النزاع.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 82 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="82 قضاء النبوة بالشفعة في العقارات والأراضي المشتركة قبل القسمة عن جابر بن عبد الله رضي الله عنهما قال: «قَضَى رَسُولُ اللَّهِ ﷺ بِالشُّفْعَةِ فِي كُلِّ مَا لَمْ يُقْسَمْ، فَإِذَا وَقَعَتِ الْحُدُودُ، وَصُرِّفَتِ الطُّرُقُ، فَلَا شُفْعَةَ» صحيح البخاري (2257) وصحيح مسلم (1608) حق الشفعة للشريك في العقار أو الأرض غير المفرزة لدرء الضرر عنه بشراء حصة شريكه بنفس الثمن الذي اتفق عليه مع المشتري الأجنبي، وسقوط الشفعة بعد الفرز وتحديد الحدود. قضى بالشفعة في كل ما لم يقسم وقعت الحدود صرفت الطرق حق الشفعة العقارات الأراضي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        82
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        قضاء النبوة بالشفعة في العقارات والأراضي المشتركة قبل القسمة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن جابر بن عبد الله رضي الله عنهما قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «قَضَى رَسُولُ اللَّهِ ﷺ بِالشُّفْعَةِ فِي كُلِّ مَا لَمْ يُقْسَمْ، فَإِذَا وَقَعَتِ الْحُدُودُ، وَصُرِّفَتِ الطُّرُقُ، فَلَا شُفْعَةَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2257) وصحيح مسلم (1608)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حق الشفعة للشريك في العقار أو الأرض غير المفرزة لدرء الضرر عنه بشراء حصة شريكه بنفس الثمن الذي اتفق عليه مع المشتري الأجنبي، وسقوط الشفعة بعد الفرز وتحديد الحدود.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 83 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="83 حق الجار الملاصق في أولوية الشراء والعرض عليه قبل الغريب عن الشريد بن سويد رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «الْجَارُ أَحَقُّ بِصَقَبِهِ مَا كَانَ» [أي: بِقُرْبِهِ وَشُفْعَتِهِ] صحيح البخاري (2258) استحباب -وفي مذهب الحنفية وجوب- عرض العقار أو الدار على الجار الملاصق قبل بيعه للأجنبي، حفاظاً على حسن الجوار ومنعاً لدخول من قد يضر بالحي أو البناء. الجار أحق بصقبه حق الجوار أولوية الشراء شفعة الجوار بيع العقارات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        83
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حق الجار الملاصق في أولوية الشراء والعرض عليه قبل الغريب
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح البخاري
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن الشريد بن سويد رضي الله عنه قال: سمعت رسول الله ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْجَارُ أَحَقُّ بِصَقَبِهِ مَا كَانَ» [أي: بِقُرْبِهِ وَشُفْعَتِهِ]
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2258)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">استحباب -وفي مذهب الحنفية وجوب- عرض العقار أو الدار على الجار الملاصق قبل بيعه للأجنبي، حفاظاً على حسن الجوار ومنعاً لدخول من قد يضر بالحي أو البناء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 84 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="84 مشروعية عقود المساقاة والمزارعة والمشاركة في المحصول بنسبة شائعة عن عبد الله بن عمر رضي الله عنهما: «أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَى خَيْبَرَ الْيَهُودَ، عَلَى أَنْ يَعْمَلُوهَا وَيَزْرَعُوهَا، وَلَهُمْ شَطْرُ مَا يَخْرُجُ مِنْهَا مِنْ ثَمَرٍ أَوْ زَرْعٍ» صحيح البخاري (2328) وصحيح مسلم (1551) تأصيل عقود الاستثمار الزراعي (المساقاة والمزارعة): تقديم الأرض والمزارع من طرف والعمل من طرف آخر، وتقاسم صافي الإنتاج بنسبة مئوية شائعة (كـ 50% أو 30%) دون تحديد أجر نقدي ثابت مشروط. أعطى خيبر على أن يعملوها ويزرعوها ولهم شطر ما يخرج منها المساقاة المزارعة الاستثمار الزراعي">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        84
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية عقود المساقاة والمزارعة والمشاركة في المحصول بنسبة شائعة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمر رضي الله عنهما:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَنَّ رَسُولَ اللَّهِ ﷺ أَعْطَى خَيْبَرَ الْيَهُودَ، عَلَى أَنْ يَعْمَلُوهَا وَيَزْرَعُوهَا، وَلَهُمْ شَطْرُ مَا يَخْرُجُ مِنْهَا مِنْ ثَمَرٍ أَوْ زَرْعٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2328) وصحيح مسلم (1551)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تأصيل عقود الاستثمار الزراعي (المساقاة والمزارعة): تقديم الأرض والمزارع من طرف والعمل من طرف آخر، وتقاسم صافي الإنتاج بنسبة مئوية شائعة (كـ 50% أو 30%) دون تحديد أجر نقدي ثابت مشروط.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 85 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="85 تشجيع الاستثمار واستصلاح الأراضي المهملة: من أحيا أرضاً ميتة فهي له عن سعيد بن زيد رضي الله عنه عن النبي ﷺ قال: «مَنْ أَحْيَا أَرْضًا مَيْتَةً فَهِيَ لَهُ، وَلَيْسَ لِعِرْقٍ ظَالِمٍ حَقٌّ» رواه أبو داود (3073) والترمذي (1378) وصححه، وصححه الألباني في الإرواء (1550) تحفيز التنمية العمرانية والزراعية: من استصلح أرضاً بواراً لا مالك لها بإذن الدولة وحفر فيها بئراً أو زرعها تملكها شرعاً، وبطلان وضع اليد الجائر الغاصب (العرق الظالم). من أحيا أرضا ميتة فهي له ليس لعرق ظالم حق إحياء الموات استصلاح الأراضي وضع اليد">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        85
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تشجيع الاستثمار واستصلاح الأراضي المهملة: من أحيا أرضاً ميتة فهي له
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن سعيد بن زيد رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ أَحْيَا أَرْضًا مَيْتَةً فَهِيَ لَهُ، وَلَيْسَ لِعِرْقٍ ظَالِمٍ حَقٌّ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3073) والترمذي (1378) وصححه، وصححه الألباني في الإرواء (1550)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تحفيز التنمية العمرانية والزراعية: من استصلح أرضاً بواراً لا مالك لها بإذن الدولة وحفر فيها بئراً أو زرعها تملكها شرعاً، وبطلان وضع اليد الجائر الغاصب (العرق الظالم).</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 86 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="86 دستور المرافق العامة والملكية المشتركة: الناس شركاء في ثلاث عن رجل من المهاجرين من أصحاب النبي ﷺ قال: قال رسول الله ﷺ: «الْمُسْلِمُونَ شُرَكَاءُ فِي ثَلَاثٍ: فِي الْمَاءِ، وَالْكَلَإِ، وَالنَّارِ» رواه أبو داود (3477) وابن ماجه (2472) وصححه الألباني في صحيح الجامع (6701) حظر احتكار الموارد الطبيعية الحيوية ومصادر الطاقة والمياه العامة والمراعي الطبيعية، ووجوب إتاحتها لعموم المواطنين كمرافق عامة دون احتكار تجاري جائر. المسلمون شركاء في ثلاث في الماء والكلإ والنار المرافق العامة مصادر الطاقة حظر الاحتكار">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        86
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور المرافق العامة والملكية المشتركة: الناس شركاء في ثلاث
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن رجل من المهاجرين من أصحاب النبي ﷺ قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «الْمُسْلِمُونَ شُرَكَاءُ فِي ثَلَاثٍ: فِي الْمَاءِ، وَالْكَلَإِ، وَالنَّارِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3477) وابن ماجه (2472) وصححه الألباني في صحيح الجامع (6701)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حظر احتكار الموارد الطبيعية الحيوية ومصادر الطاقة والمياه العامة والمراعي الطبيعية، ووجوب إتاحتها لعموم المواطنين كمرافق عامة دون احتكار تجاري جائر.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 87 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="87 تحريم بيع فضل الماء في الآبار والأنهار لمنع رعي الماشية والأراضي عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال: «لَا يُمْنَعُ فَضْلُ الْمَاءِ؛ لِيُمْنَعَ بِهِ الْكَلَأُ» صحيح البخاري (2353) وصحيح مسلم (1566) حماية الموارد المائية من التعسف في استعمال الحق، وإلزام الملاك بالسماح للآخرين بالاستفادة من الفائض من المياه للشرب والري دون أخذ مقابل تعسفي. لا يمنع فضل الماء ليمنع به الكلأ الموارد المائية التعسف في استعمال الحق">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        87
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تحريم بيع فضل الماء في الآبار والأنهار لمنع رعي الماشية والأراضي
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه أن رسول الله ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَا يُمْنَعُ فَضْلُ الْمَاءِ؛ لِيُمْنَعَ بِهِ الْكَلَأُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2353) وصحيح مسلم (1566)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">حماية الموارد المائية من التعسف في استعمال الحق، وإلزام الملاك بالسماح للآخرين بالاستفادة من الفائض من المياه للشرب والري دون أخذ مقابل تعسفي.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 88 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="contracts" data-hadith-search="88 مشروعية عقد الجِعالة واستحقاق المكافأة المالية المشروطة عند إتمام الإنجاز عن أبي سعيد الخدري رضي الله عنه في قصة رقية سيد الحي بديغ بفاتحة الكتاب واشتراط قطيع من الغنم كجُعْل ومكافأة، فقال رسول الله ﷺ مقراً لهم: «اقْسِمُوا وَاضْرِبُوا لِي مَعَكُمْ بِسَهْمٍ» صحيح البخاري (2276) وصحيح مسلم (2201) تأصيل عقود الجعالة والمكافآت المرتبطة بالنتائج ومكافآت النجاح (Success Fees) وعمولات السمسرة المشروطة بإنجاز العمل كاملاً وقبض الجعل المستحق. عقد الجعالة اقسموا واضربوا لي معكم بسهم رقية اللديغ مكافآت الإنجاز عمولات السمسرة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        88
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        مشروعية عقد الجِعالة واستحقاق المكافأة المالية المشروطة عند إتمام الإنجاز
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-indigo-500/20 text-indigo-300 border-indigo-400/40">
                        📑 سلم وشفعة ومساقاة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي سعيد الخدري رضي الله عنه في قصة رقية سيد الحي بديغ بفاتحة الكتاب واشتراط قطيع من الغنم كجُعْل ومكافأة، فقال رسول الله ﷺ مقراً لهم:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «اقْسِمُوا وَاضْرِبُوا لِي مَعَكُمْ بِسَهْمٍ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2276) وصحيح مسلم (2201)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تأصيل عقود الجعالة والمكافآت المرتبطة بالنتائج ومكافآت النجاح (Success Fees) وعمولات السمسرة المشروطة بإنجاز العمل كاملاً وقبض الجعل المستحق.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 89 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="property" data-hadith-search="89 دستور اللقطة والمال الضائع: حفظ الأوصاف والإعلان والتعريف لمدة عام عن زيد بن خالد الجهني رضي الله عنه أن رجلاً سأل رسول الله ﷺ عن اللُّقَطَةِ، فقال: «اعْرِفْ عِفَاصَهَا وَوِكَاءَهَا، ثُمَّ عَرِّفْهَا سَنَةً، فَإِنْ جَاءَ صَاحِبُهَا، وَإِلَّا فَشَأْنُكَ بِهَا» صحيح البخاري (2426) وصحيح مسلم (1722) التوثيق القانوني والشرعي للأموال والمفقودات الثمينة المعثور عليها: فحص وحفظ مواصفاتها وأغلفتها بدقة، والإعلان عنها في الوسائل المعاصرة لمدة سنة كاملة قبل التصرف فيها كوديعة مضمونة. اعرف عفاصها ووكاءها ثم عرفها سنة اللقطة الأموال الضائعة المفقودات التوثيق">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        89
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور اللقطة والمال الضائع: حفظ الأوصاف والإعلان والتعريف لمدة عام
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-cyan-500/20 text-cyan-300 border-cyan-400/40">
                        🛡️ لقطة وأمانات وحفظ مال
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن زيد بن خالد الجهني رضي الله عنه أن رجلاً سأل رسول الله ﷺ عن اللُّقَطَةِ، فقال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «اعْرِفْ عِفَاصَهَا وَوِكَاءَهَا، ثُمَّ عَرِّفْهَا سَنَةً، فَإِنْ جَاءَ صَاحِبُهَا، وَإِلَّا فَشَأْنُكَ بِهَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2426) وصحيح مسلم (1722)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التوثيق القانوني والشرعي للأموال والمفقودات الثمينة المعثور عليها: فحص وحفظ مواصفاتها وأغلفتها بدقة، والإعلان عنها في الوسائل المعاصرة لمدة سنة كاملة قبل التصرف فيها كوديعة مضمونة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 90 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="property" data-hadith-search="90 التفريق بين اللقطة المعرضة للضياع والحيوانات والممتلكات المصونة عن زيد بن خالد الجهني رضي الله عنه قال: سئل النبي ﷺ عن ضالة الغنم؟ قال: «هِيَ لَكَ أَوْ لِأَخِيكَ أَوْ لِلذِّئْبِ»، وسئل عن ضالة الإبل؟ فغضب وقال: «مَا لَكَ وَلَهَا؟ مَعَهَا حِذَاؤُهَا وَسِقَاؤُهَا، تَرِدُ الْمَاءَ، وَتَأْكُلُ الشَّجَرَ، حَتَّى يَلْقَاهَا رَبُّهَا» صحيح البخاري (2427) وصحيح مسلم (1722) المنع من حيازة والتقاط الممتلكات القادرة على حماية نفسها أو المحفوظة في أماكنها الآمنة خشية التعدي، ووجوب إنقاذ وحفظ الأموال الهشة المعرضة للتلف والضياع الفوري. ضالة الغنم لك أو لأخيك أو للذئب ضالة الإبل معها حذاؤها وسقاؤها حفظ الممتلكات">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        90
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        التفريق بين اللقطة المعرضة للضياع والحيوانات والممتلكات المصونة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-cyan-500/20 text-cyan-300 border-cyan-400/40">
                        🛡️ لقطة وأمانات وحفظ مال
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن زيد بن خالد الجهني رضي الله عنه قال: سئل النبي ﷺ عن ضالة الغنم؟ قال: «هِيَ لَكَ أَوْ لِأَخِيكَ أَوْ لِلذِّئْبِ»، وسئل عن ضالة الإبل؟ فغضب وقال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا لَكَ وَلَهَا؟ مَعَهَا حِذَاؤُهَا وَسِقَاؤُهَا، تَرِدُ الْمَاءَ، وَتَأْكُلُ الشَّجَرَ، حَتَّى يَلْقَاهَا رَبُّهَا»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2427) وصحيح مسلم (1722)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المنع من حيازة والتقاط الممتلكات القادرة على حماية نفسها أو المحفوظة في أماكنها الآمنة خشية التعدي، ووجوب إنقاذ وحفظ الأموال الهشة المعرضة للتلف والضياع الفوري.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 91 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="property" data-hadith-search="91 وجوب رد الأمانات والودائع المصرفية والتجارية وتحريم خيانة الخائن عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: «أَدِّ الْأَمَانَةَ إِلَى مَنِ ائْتَمَنَكَ، وَلَا تَخُنْ مَنْ خَانَكَ» رواه أبو داود (3535) والترمذي (1264) وصححه، وصححه الألباني في السلسلة الصحيحة (423) قدسية حسابات الأمانات والودائع المصرفية والودائع العينية؛ ووجوب ردها كاملة لمالكها عند الطلب، والنهي الصارم عن مجاراة الخيانة بخيانة مقابلة أو اختلاس أموال من غدر بك. أد الأمانة إلى من ائتمنك ولا تخن من خانك الودائع المصرفية حسابات الأمانة رد الحقوق">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        91
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        وجوب رد الأمانات والودائع المصرفية والتجارية وتحريم خيانة الخائن
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-cyan-500/20 text-cyan-300 border-cyan-400/40">
                        🛡️ لقطة وأمانات وحفظ مال
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَدِّ الْأَمَانَةَ إِلَى مَنِ ائْتَمَنَكَ، وَلَا تَخُنْ مَنْ خَانَكَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3535) والترمذي (1264) وصححه، وصححه الألباني في السلسلة الصحيحة (423)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">قدسية حسابات الأمانات والودائع المصرفية والودائع العينية؛ ووجوب ردها كاملة لمالكها عند الطلب، والنهي الصارم عن مجاراة الخيانة بخيانة مقابلة أو اختلاس أموال من غدر بك.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 92 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="property" data-hadith-search="92 المسؤولية القانونية والضمان الشامل للأموال المقبوضة تحت يد الشخص عن سمرة بن جندب رضي الله عنه عن النبي ﷺ قال: «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ» رواه أبو داود (3561) والترمذي (1266) وصححه، والنسائي وابن ماجه وصححه الحاكم والألباني القاعدة الأساسية في الضمان والتعويض والمسؤولية المدنية: كل من وضع يده على مال غيره بطريق عارية أو غصب أو استلام تجاري فهو ضامن لسلامته حتى يرجعه سالماً لمالكه الشرعي. على اليد ما أخذت حتى تؤديه قاعدة الضمان المسؤولية المدنية التعويض حيازة الأموال">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        92
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        المسؤولية القانونية والضمان الشامل للأموال المقبوضة تحت يد الشخص
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-cyan-500/20 text-cyan-300 border-cyan-400/40">
                        🛡️ لقطة وأمانات وحفظ مال
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن سمرة بن جندب رضي الله عنه عن النبي ﷺ قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «عَلَى الْيَدِ مَا أَخَذَتْ حَتَّى تُؤَدِّيَهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3561) والترمذي (1266) وصححه، والنسائي وابن ماجه وصححه الحاكم والألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">القاعدة الأساسية في الضمان والتعويض والمسؤولية المدنية: كل من وضع يده على مال غيره بطريق عارية أو غصب أو استلام تجاري فهو ضامن لسلامته حتى يرجعه سالماً لمالكه الشرعي.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 93 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="property" data-hadith-search="93 حرمة الملكية الفردية الخاصة ومشروعية الدفاع عن المال حتى الشهادة عن عبد الله بن عمرو بن العاص رضي الله عنهما قال: سمعت النبي ﷺ يقول: «مَنْ قُتِلَ دُونَ مَالِهِ فَهُوَ شَهِيدٌ» صحيح البخاري (2480) وصحيح مسلم (141) تقرير قدسية الملكية الفردية والأموال الخاصة في الإسلام، ومنح صاحب المال الحق الكامل في حماية تجارته ومستودعاته وممتلكاته من اللصوص والغاصبين. من قتل دون ماله فهو شهيد حماية الملكية الخاصة الدفاع عن المال مكافحة السطو والسرقة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        93
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حرمة الملكية الفردية الخاصة ومشروعية الدفاع عن المال حتى الشهادة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-cyan-500/20 text-cyan-300 border-cyan-400/40">
                        🛡️ لقطة وأمانات وحفظ مال
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو بن العاص رضي الله عنهما قال: سمعت النبي ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ قُتِلَ دُونَ مَالِهِ فَهُوَ شَهِيدٌ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2480) وصحيح مسلم (141)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تقرير قدسية الملكية الفردية والأموال الخاصة في الإسلام، ومنح صاحب المال الحق الكامل في حماية تجارته ومستودعاته وممتلكاته من اللصوص والغاصبين.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 94 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="property" data-hadith-search="94 عقوبة غصب العقارات وتزوير حجج الأراضي والتعدي على المخططات عن سعيد بن زيد رضي الله عنه قال: سمعت رسول الله ﷺ يقول: «مَنْ ظَلَمَ قِيدَ شِبْرٍ مِنَ الْأَرْضِ، طُوِّقَهُ مِنْ سَبْعِ أَرَضِينَ» صحيح البخاري (2452) وصحيح مسلم (1610) التغليظ الشديد في تحريم التعدي على الأراضي والعقارات وتغيير حدود المخططات أو الاستيلاء على أملاك الجيران والورثة، وبيان شناعة عقوبتها الأخروية. من ظلم قيد شبر من الأرض طوقه من سبع أرضين غصب العقارات الاستيلاء على الأراضي تزوير الحجج">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        94
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة غصب العقارات وتزوير حجج الأراضي والتعدي على المخططات
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-cyan-500/20 text-cyan-300 border-cyan-400/40">
                        🛡️ لقطة وأمانات وحفظ مال
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن سعيد بن زيد رضي الله عنه قال: سمعت رسول الله ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ ظَلَمَ قِيدَ شِبْرٍ مِنَ الْأَرْضِ، طُوِّقَهُ مِنْ سَبْعِ أَرَضِينَ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2452) وصحيح مسلم (1610)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التغليظ الشديد في تحريم التعدي على الأراضي والعقارات وتغيير حدود المخططات أو الاستيلاء على أملاك الجيران والورثة، وبيان شناعة عقوبتها الأخروية.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 95 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="integrity" data-hadith-search="95 لعن الراشي والمرتشي في المعاملات والمناقصات والقضاء والوظائف عن عبد الله بن عمرو رضي الله عنهما قال: «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِي وَالْمُرْتَشِي» رواه أبو داود (3580) والترمذي (1337) وصححه، وابن ماجه (2313) وصححه الألباني التحريم القطعي لكافة أشكال الرشاوى والإكراميات غير المشروعة لتمرير الصفقات أو الحصول على عقود أو تزييف التدقيق المالي، واستحقاق الطرد من رحمة الله للطرفين. لعن رسول الله الراشي والمرتشي الرشوة الفساد المالي المناقصات النزاهة الشفافية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        95
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        لعن الراشي والمرتشي في المعاملات والمناقصات والقضاء والوظائف
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ نزاهة ومحاربة الرشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن عمرو رضي الله عنهما قال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «لَعَنَ رَسُولُ اللَّهِ ﷺ الرَّاشِي وَالْمُرْتَشِي»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أبو داود (3580) والترمذي (1337) وصححه، وابن ماجه (2313) وصححه الألباني</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التحريم القطعي لكافة أشكال الرشاوى والإكراميات غير المشروعة لتمرير الصفقات أو الحصول على عقود أو تزييف التدقيق المالي، واستحقاق الطرد من رحمة الله للطرفين.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 96 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="integrity" data-hadith-search="96 تجريم عمولات الموظفين وهدايا المشتريات: هدايا العمال غلول وخيانة عن أبي حميد الساعدي رضي الله عنه أن رسول الله ﷺ استعمل ابن اللُّتْبِيَّة على الصدقة فجاء فقال: هذا لكم وهذا أهدي لي، فقام ﷺ خطيباً وقال: «مَا بَالُ الْعَامِلِ نَبْعَثُهُ فَيَقُولُ: هَذَا لَكُمْ وَهَذَا أُهْدِيَ لِي! أَفَلَا جَلَسَ فِي بَيْتِ أَبِيهِ أَوْ بَيْتِ أُمِّهِ فَيَنْظُرَ أَيُهْدَى إِلَيْهِ أَمْ لَا؟ وَالَّذِي نَفْسِي بِيَدِهِ لَا يَأْخُذُ أَحَدٌ مِنْهُ شَيْئًا إِلَّا جَاءَ بِهِ يَوْمَ الْقِيَامَةِ يَحْمِلُهُ عَلَى رَقَبَتِهِ» صحيح البخاري (2597) وصحيح مسلم (1832) المعيار النبوي الدقيق في مكافحة تضارب المصالح والحوكمة المؤسسية: تحريم قبول موظفي المشتريات أو العقود أو الجباية لأي هدايا أو عمولات خاصة من الموردين والعملاء. هدايا العمال غلول ابن اللتبية أفلا جلس في بيت أبيه تضارب المصالح عمولات المشتريات الحوكمة">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        96
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        تجريم عمولات الموظفين وهدايا المشتريات: هدايا العمال غلول وخيانة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ نزاهة ومحاربة الرشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي حميد الساعدي رضي الله عنه أن رسول الله ﷺ استعمل ابن اللُّتْبِيَّة على الصدقة فجاء فقال: هذا لكم وهذا أهدي لي، فقام ﷺ خطيباً وقال:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَا بَالُ الْعَامِلِ نَبْعَثُهُ فَيَقُولُ: هَذَا لَكُمْ وَهَذَا أُهْدِيَ لِي! أَفَلَا جَلَسَ فِي بَيْتِ أَبِيهِ أَوْ بَيْتِ أُمِّهِ فَيَنْظُرَ أَيُهْدَى إِلَيْهِ أَمْ لَا؟ وَالَّذِي نَفْسِي بِيَدِهِ لَا يَأْخُذُ أَحَدٌ مِنْهُ شَيْئًا إِلَّا جَاءَ بِهِ يَوْمَ الْقِيَامَةِ يَحْمِلُهُ عَلَى رَقَبَتِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2597) وصحيح مسلم (1832)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">المعيار النبوي الدقيق في مكافحة تضارب المصالح والحوكمة المؤسسية: تحريم قبول موظفي المشتريات أو العقود أو الجباية لأي هدايا أو عمولات خاصة من الموردين والعملاء.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 97 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="integrity" data-hadith-search="97 شؤم التكسب من السحت والحرام واستحقاق النار للجسد النابت منه عن كعب بن عجرة رضي الله عنه قال: قال لي رسول الله ﷺ: «يَا كَعْبُ بْنَ عُجْرَةَ، إِنَّهُ لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ» رواه الترمذي (614) وصححه، وأحمد (14441) وصححه الألباني في صحيح الترغيب (1729) التحذير الرهيب من إدخال المال الحرام أو العمولات المشبوهة في طعام الأهل والذرية، وبيان أن أكل السحت يحرم العبد من استجابة الدعاء ويجلب الأمراض ومحق البركة. لا يربو لحم نبت من سحت إلا كانت النار أولى به أكل الحرام كسب السحت إطعام الأهل">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        97
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        شؤم التكسب من السحت والحرام واستحقاق النار للجسد النابت منه
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ نزاهة ومحاربة الرشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن كعب بن عجرة رضي الله عنه قال: قال لي رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «يَا كَعْبُ بْنَ عُجْرَةَ، إِنَّهُ لَا يَرْبُو لَحْمٌ نَبَتَ مِنْ سُحْتٍ إِلَّا كَانَتِ النَّارُ أَوْلَى بِهِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه الترمذي (614) وصححه، وأحمد (14441) وصححه الألباني في صحيح الترغيب (1729)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التحذير الرهيب من إدخال المال الحرام أو العمولات المشبوهة في طعام الأهل والذرية، وبيان أن أكل السحت يحرم العبد من استجابة الدعاء ويجلب الأمراض ومحق البركة.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 98 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="integrity" data-hadith-search="98 عقوبة اقتطاع أموال الناس وابتلاع حقوقهم بالأيمان الكاذبة المغلظة عن عبد الله بن مسعود رضي الله عنه قال: قال رسول الله ﷺ: «مَنْ حَلَفَ عَلَى يَمِينِ صَبْرٍ يَقْتَطِعُ بِهَا مَالَ امْرِئٍ مُسْلِمٍ، هُوَ فِيهَا فَاجِرٌ؛ لَقِيَ اللَّهَ وَهُوَ عَلَيْهِ غَضْبَانُ» صحيح البخاري (2666) وصحيح مسلم (138) تجريم شهادة الزور والأيمان الكاذبة في المحاكم والمرافعات لنهب الشركات أو حقوق الشركاء والورثة، وبيان أنها اليمين الغموس التي تغمس صاحبها في نار جهنم. من حلف على يمين صبر يقتطع بها مال امرئ مسلم اليمين الغموس شهادة الزور نهب الأموال">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        98
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        عقوبة اقتطاع أموال الناس وابتلاع حقوقهم بالأيمان الكاذبة المغلظة
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ نزاهة ومحاربة الرشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن عبد الله بن مسعود رضي الله عنه قال: قال رسول الله ﷺ:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «مَنْ حَلَفَ عَلَى يَمِينِ صَبْرٍ يَقْتَطِعُ بِهَا مَالَ امْرِئٍ مُسْلِمٍ، هُوَ فِيهَا فَاجِرٌ؛ لَقِيَ اللَّهَ وَهُوَ عَلَيْهِ غَضْبَانُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (2666) وصحيح مسلم (138)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">تجريم شهادة الزور والأيمان الكاذبة في المحاكم والمرافعات لنهب الشركات أو حقوق الشركاء والورثة، وبيان أنها اليمين الغموس التي تغمس صاحبها في نار جهنم.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 99 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="integrity" data-hadith-search="99 حرمة مال المسلم المطلقة: لا يحل أخذ أي جزء منه إلا بطيب نفس ورضا عن أبي حرة الرقاشي عن عمه رضي الله عنه أن رسول الله ﷺ قال في خطبة أيام التشريق: «أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، إِنَّهُ لَا يَحِلُّ مَالُ امْرِئٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ» رواه أحمد (20695) والبيهقي في السنن (11325) والدارقطني وصححه الألباني في الإرواء (1459) أصل أصيل في حرمة الملكيات وحسابات الأفراد: بطلان أي استقطاع مالي أو فرض رسوم أو أخذ ممتلكات بالحياء أو الإحراج أو التحايل دون رضا قلبي تام واختيار حر. لا يحل مال امرئ إلا بطيب نفس منه ألا لا تظلموا طيب النفس الرضا التام استقطاع الأموال">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        99
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        حرمة مال المسلم المطلقة: لا يحل أخذ أي جزء منه إلا بطيب نفس ورضا
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ نزاهة ومحاربة الرشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ صحيح
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن أبي حرة الرقاشي عن عمه رضي الله عنه أن رسول الله ﷺ قال في خطبة أيام التشريق:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، أَلَا لَا تَظْلِمُوا، إِنَّهُ لَا يَحِلُّ مَالُ امْرِئٍ إِلَّا بِطِيبِ نَفْسٍ مِنْهُ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">رواه أحمد (20695) والبيهقي في السنن (11325) والدارقطني وصححه الألباني في الإرواء (1459)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">أصل أصيل في حرمة الملكيات وحسابات الأفراد: بطلان أي استقطاع مالي أو فرض رسوم أو أخذ ممتلكات بالحياء أو الإحراج أو التحايل دون رضا قلبي تام واختيار حر.</span>
                </div>
            </div>

        </div>

        <!-- Hadith Card 100 -->
        <div class="fiqh-hadith-card p-5 bg-slate-900/95 rounded-2xl border-2 border-amber-500/40 space-y-3 shadow-lg hover:border-amber-400 transition" data-hadith-cat="integrity" data-hadith-search="100 دستور الحوكمة وإدارة الثروات: النهي القاطع عن إضاعة المال والتبذير عن المغيرة بن شعبة رضي الله عنه قال: سمعت النبي ﷺ يقول: «إِنَّ اللَّهَ كَرِهَ لَكُمْ ثَلَاثًا: قِيلَ وَقَالَ، وَكَثْرَةَ السُّؤَالِ، وَإِضَاعَةَ الْمَالِ» صحيح البخاري (1477) وصحيح مسلم (593) التوجيه النبوي الأعلى في ترشيد الإنفاق وحسن التدبير المحاسبي والاستثماري: تحريم هدر الموارد المالية، ومحاربة المشاريع الخاسرة الناتجة عن الإهمال والتبذير وسوء التخطيط. إن الله كره لكم ثلاثا قيل وقال وكثرة السؤال وإضاعة المال ترشيد الإنفاق التبذير الحوكمة المالية">
            
            <!-- Top Badge & Number -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black flex items-center justify-center border border-amber-400/50 flex-shrink-0">
                        100
                    </span>
                    <h4 class="text-base md:text-lg font-black text-amber-300 leading-tight">
                        دستور الحوكمة وإدارة الثروات: النهي القاطع عن إضاعة المال والتبذير
                    </h4>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-xl border bg-red-500/20 text-red-300 border-red-400/40">
                        ⚖️ نزاهة ومحاربة الرشوة
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
                        ✓ متفق عليه
                    </span>
                </div>
            </div>

            <!-- Hadith Matn Calligraphy Box -->
            <div class="p-4 bg-slate-950/90 rounded-xl border border-slate-800">
                <span class="text-xs text-slate-400 font-bold block mb-1.5">عن المغيرة بن شعبة رضي الله عنه قال: سمعت النبي ﷺ يقول:</span>
                <p class="text-base sm:text-lg md:text-xl text-amber-200 font-black leading-relaxed hadith-text-display" style="font-family: 'Amiri', 'Cairo', serif; line-height: 2.1; letter-spacing: 0.3px;">
                    «إِنَّ اللَّهَ كَرِهَ لَكُمْ ثَلَاثًا: قِيلَ وَقَالَ، وَكَثْرَةَ السُّؤَالِ، وَإِضَاعَةَ الْمَالِ»
                </p>
            </div>

            <!-- Source and Fiqh Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-slate-950/70 rounded-xl border border-slate-800">
                    <span class="text-sky-400 font-black block mb-0.5">📖 التخريج والمصدر المعتمد:</span>
                    <span class="text-slate-300 font-bold">صحيح البخاري (1477) وصحيح مسلم (593)</span>
                </div>
                <div class="p-2.5 bg-emerald-950/30 rounded-xl border border-emerald-500/30">
                    <span class="text-emerald-400 font-black block mb-0.5">💡 الضابط والفائدة للتاجر والمحاسب:</span>
                    <span class="text-slate-200 font-bold">التوجيه النبوي الأعلى في ترشيد الإنفاق وحسن التدبير المحاسبي والاستثماري: تحريم هدر الموارد المالية، ومحاربة المشاريع الخاسرة الناتجة عن الإهمال والتبذير وسوء التخطيط.</span>
                </div>
            </div>

        </div>

                    </div>

                    <div id="no-hadiths-alert" class="p-8 text-center bg-slate-900/60 rounded-2xl border border-slate-800 hidden">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h5 class="text-lg font-black text-slate-300">لا توجد أحاديث مطابقة للبحث الحالي</h5>
                        <p class="text-xs text-slate-400 mt-1">يرجى مسح كلمة البحث أو اختيار تصنيف آخر لإظهار بقية الأحاديث.</p>
                    </div>
                </div>
                    
                

                <div id="fiqh-sub-terms" class="space-y-4 hidden">
                    <div class="p-6 bg-slate-900/90 rounded-2xl border-2 border-amber-500/30 space-y-4">
                        <div class="border-b border-slate-800 pb-3 flex flex-wrap items-center justify-between gap-4">
                            <div>
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
                            <div class="w-full md:w-80">
                                <label class="text-xs font-bold text-amber-300 block mb-1">🔍 ابحث في المصطلحات الاقتصادية:</label>
                                <input type="text" id="terms-search-input" oninput="filterTerms()" placeholder="اكتب للبحث: استصناع، سلم، عربون، مرابحة..." class="tool-input text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            
                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition" fiqh-term-card fiqh-term-card>
                                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 font-mono font-black text-xs rounded">1. عقد الاستصناع (Istisna'a)</span>
                                <h5 class="text-sm font-black text-white">طلب تصنيع سلعة بمواصفات محددة ومواد من الصانع</h5>
                                <p class="text-xs text-slate-300 font-bold">عقد على مبيع في الذمة شرط فيه العمل، كطلبيات حديد التسليح الخاص وتصنيع الهياكل المعدنية والخرسانات الجاهزة، ويجوز فيه تأجيل الثمن أو تقسيطه.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition" fiqh-term-card fiqh-term-card>
                                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 font-mono font-black text-xs rounded">2. بيع السلم (Salam Sale)</span>
                                <h5 class="text-sm font-black text-white">شراء بضاعة آجلة بثمن مدفوع مقدماً بالكامل</h5>
                                <p class="text-xs text-slate-300 font-bold">بيع موصوف في الذمة مؤجل التسليم بثمن مقبوض فوراً في مجلس العقد، وهو وسيلة شرعية ممتازة لتمويل المشروعات الزراعية والمقاولات وتوريد المواد.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition" fiqh-term-card fiqh-term-card>
                                <span class="px-2 py-0.5 bg-sky-500/20 text-sky-300 font-mono font-black text-xs rounded">3. بيع العربون (Down Payment Sale)</span>
                                <h5 class="text-sm font-black text-white">دفع جزء من الثمن لتأكيد الشراء أو اعتباره هدراً للبائع</h5>
                                <p class="text-xs text-slate-300 font-bold">أن يشتري السلعة ويدفع جزءاً مقدماً فإن أتم الشراء حُسب من الثمن وإن تراجع اختص به البائع تعويضاً عن حجز السلعة (وهو مذهب الإمام أحمد وصحيح فتاوى ابن باز والعثيمين).</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition" fiqh-term-card fiqh-term-card>
                                <span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 font-mono font-black text-xs rounded">4. بيع المرابحة للآمر بالشراء</span>
                                <h5 class="text-sm font-black text-white">شراء المؤسسة للسلعة ثم بيعها للعميل بالتقسيط بربح معلوم</h5>
                                <p class="text-xs text-slate-300 font-bold">أن يطلب العميل من البنك أو التاجر شراء معدات أو مواد محددة، فإذا تملكها وقبضها البائع باعها للعميل بالتقسيط بربح متفق عليه، بشرط خلوها من العينة.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition" fiqh-term-card fiqh-term-card>
                                <span class="px-2 py-0.5 bg-rose-500/20 text-rose-300 font-mono font-black text-xs rounded">5. بيع العِينة المحرم (Eina Sale)</span>
                                <h5 class="text-sm font-black text-white">بيع السلعة لأجل ثم شراؤها نقداً بثمن أقل</h5>
                                <p class="text-xs text-slate-300 font-bold">حيلة ربوية صريحة محرمة، وصورتها أن يبيع التاجر سلعة بـ 100 ألف لأجل ثم يشتريها في نفس المجلس نقداً بـ 80 ألفاً، فيكون القصد قرض 80 واسترداد 100.</p>
                            </div>

                            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-1.5 hover:border-amber-400/60 transition" fiqh-term-card fiqh-term-card>
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
    <!-- Master Fiqh 4,500 Fatwas Data Library (Synchronous Instant Memory Preload) -->
    <script src="js/fiqh_20000_data.js?v=4500_v2"></script>


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
        const rawQuery = (document.getElementById('hadith-search-input') ? document.getElementById('hadith-search-input').value : '').trim();
        const normQuery = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawQuery) : rawQuery.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-hadith-card').forEach(card => {
            const cat = card.getAttribute('data-hadith-cat');
            const rawSearch = card.getAttribute('data-hadith-search') || '';
            const normSearch = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawSearch) : rawSearch.toLowerCase();

            const matchCat = (activeHadithCat === 'all' || cat === activeHadithCat);
            const matchSearch = (normQuery === '' || normSearch.includes(normQuery));

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
        const rawQuery = (document.getElementById('quran-search-input') ? document.getElementById('quran-search-input').value : '').trim();
        const normQuery = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawQuery) : rawQuery.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-ayah-card').forEach(card => {
            const cat = card.getAttribute('data-ayah-topic');
            const rawSearch = card.getAttribute('data-ayah-search') || '';
            const normSearch = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawSearch) : rawSearch.toLowerCase();

            const matchCat = (activeQuranTopic === 'all' || cat === activeQuranTopic);
            const matchSearch = (normQuery === '' || normSearch.includes(normQuery) || rawSearch.toLowerCase().includes(rawQuery.toLowerCase()));

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

    let activeMaximCat = 'all';
    function setMaximCategory(cat, btn) {
        activeMaximCat = cat;
        document.querySelectorAll('.maxim-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        filterMaxims();
    }

    function filterMaxims() {
        const rawQuery = (document.getElementById('maxims-search-input') ? document.getElementById('maxims-search-input').value : '').trim();
        const normQuery = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawQuery) : rawQuery.toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.fiqh-maxim-card').forEach(card => {
            const cat = card.getAttribute('data-maxim-cat');
            const rawSearch = card.getAttribute('data-maxim-search') || '';
            const normSearch = typeof normalizeArabicText === 'function' ? normalizeArabicText(rawSearch) : rawSearch.toLowerCase();

            const matchCat = (activeMaximCat === 'all' || cat === activeMaximCat);
            const matchSearch = (normQuery === '' || normSearch.includes(normQuery));

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noAlert = document.getElementById('no-maxims-alert');
        if (noAlert) noAlert.classList.toggle('hidden', visibleCount > 0);
    }

    function filterTerms() {
        const query = (document.getElementById('terms-search-input') ? document.getElementById('terms-search-input').value : '').trim().toLowerCase();
        document.querySelectorAll('.fiqh-term-card').forEach(card => {
            const text = (card.innerText || card.textContent || '').toLowerCase();
            if (query === '' || text.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function switchFiqhSubTab(subId, el) {
        const allSubIds = ['fatwas', 'quran', 'hadiths', 'books', 'maxims', 'terms'];
        allSubIds.forEach(id => {
            const sec = document.getElementById('fiqh-sub-' + id);
            if (sec) sec.classList.add('hidden');
        });

        document.querySelectorAll('.fiqh-nav-pill').forEach(btn => btn.classList.remove('active'));

        const target = document.getElementById('fiqh-sub-' + subId);
        if (target) {
            target.classList.remove('hidden');
        }

        if (el) {
            el.classList.add('active');
        } else {
            const matchingBtn = document.querySelector(`.fiqh-nav-pill[onclick*="'${subId}'"]`);
            if (matchingBtn) matchingBtn.classList.add('active');
        }

        if (subId === 'fatwas') {
            const grid = document.getElementById('fiqh_fatwas_grid');
            if (grid && (!grid.children || grid.children.length === 0) && typeof initFiqhEngine === 'function') {
                initFiqhEngine();
            }
        }

        setTimeout(() => {
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 50);
    }

    // ════════════════════════════════════════════════════════════════════════════

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 MASTER 4,500 FATWAS ENGINE (EXACT SCOPED FILTERING & ZERO LAG) 🌟
    // ════════════════════════════════════════════════════════════════════════════
    const PAGE_SIZE = 21;
    let currentPage = 1;
    let currentScholarFilter = 'all';
    let currentTopicFilter = 'all';
    let currentFilteredList = [];
    let searchDebounceTimer = null;
    let fiqhLoadingPromise = null;
    let currentActiveButtonKey = null;
    let currentActiveButtonTitle = '';

    // 🌟 ARABIC TEXT NORMALIZER & PREFIX STRIPPER (FOR ACCURATE FREE SEARCH) 🌟
    function normalizeArabicText(text) {
        if (!text) return '';
        return text.toString()
            .replace(/[\u064B-\u065F\u0670]/g, '') // remove tashkeel
            .replace(/[إأآا]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .toLowerCase()
            .trim();
    }

    function cleanToken(t) {
        let s = t;
        if (s.startsWith('و') && s.length > 3) s = s.slice(1);
        if (s.startsWith('بال') && s.length > 4) s = s.slice(3);
        else if (s.startsWith('ال') && s.length > 3) s = s.slice(2);
        else if (s.startsWith('ب') && s.length > 3) s = s.slice(1);
        else if (s.startsWith('ل') && s.length > 3) s = s.slice(1);
        return s;
    }

    // 🌟 36 BUTTON SPECIFIC TOPIC DEFINITIONS (EXACT FILTERING FOR EACH BUTTON) 🌟
    const FIQH_BUTTON_FILTERS = {
        // 1. التمويل والتقسيط والديون
        'installment': { title: 'البيع بالتقسيط وزيادة الأجل', filter: (item) => item.topic_code === 'installments' },
        'penalties': { title: 'غرامات التأخير والشرط الجزائي', filter: (item) => item.topic_code === 'penalties' },
        'murabaha': { title: 'المرابحة للآمر بالشراء', filter: (item) => item.topic_code === 'installments' || (item._normSearchStr && item._normSearchStr.includes('مرابحه')) },
        'tawarruq': { title: 'التورق للحصول على سيولة', filter: (item) => item.topic_code === 'riba' || (item._normSearchStr && item._normSearchStr.includes('تورق')) },
        'leasing': { title: 'الإيجار المنتهي بالتمليك', filter: (item) => item.topic_code === 'leasing' },
        'insolvency': { title: 'إنظار المعسر والتيسير في السداد', filter: (item) => item.topic_code === 'bankruptcy' || (item._normSearchStr && item._normSearchStr.includes('معسر')) },

        // 2. مقاولات السلع والضمان
        'unowned_sale': { title: 'بيع ما لا يملك والوساطة التجارية', filter: (item) => item.topic_code === 'brokerage' },
        'istisna_supply': { title: 'عقد الاستصناع وتوريد البضائع', filter: (item) => item.topic_code === 'contracts' || (item._normSearchStr && (item._normSearchStr.includes('استصناع') || item._normSearchStr.includes('توريد'))) },
        'scrap': { title: 'بيع حديد السكراب والخردة', filter: (item) => item.topic_code === 'brokerage' || (item._normSearchStr && (item._normSearchStr.includes('سكراب') || item._normSearchStr.includes('خرده'))) },
        'defects': { title: 'خيار العيب والضمان المصنعي', filter: (item) => item.topic_code === 'defects' },
        'workers_wages': { title: 'أجور العمال وحقوق الأجراء', filter: (item) => item.topic_code === 'charity' },
        'pricing': { title: 'التسعير وتطفيف الكيل والميزان', filter: (item) => item.topic_code === 'pricing' },

        // 3. الذهب والعملات والتحويلات
        'gold_mada': { title: 'شراء الذهب ببطاقات الصراف ومدى', filter: (item) => item.topic_code === 'gold' },
        'bankak_fawry': { title: 'الصرف بتطبيقات بنكك وفوري', filter: (item) => (item._normSearchStr && (item._normSearchStr.includes('بنكك') || item._normSearchStr.includes('فوري'))) || (item.topic_code === 'exchange' && item._normSearchStr && (item._normSearchStr.includes('تطبيق') || item._normSearchStr.includes('محفظ') || item._normSearchStr.includes('تحويل'))) },
        'gold_exchange': { title: 'بيع الذهب القديم بالجديد والمصنعية', filter: (item) => item.topic_code === 'gold' },
        'immediate_possession': { title: 'التقابض الفوري في مجلس عقد الصرف', filter: (item) => item.topic_code === 'exchange' },
        'riba_inah': { title: 'حيل الربا وبيوع العِينة', filter: (item) => item.topic_code === 'riba' },
        'crypto_forex': { title: 'العملات الرقمية والفوركس', filter: (item) => (item._normSearchStr && (item._normSearchStr.includes('رقمي') || item._normSearchStr.includes('فوركس') || item._normSearchStr.includes('بيتكوين'))) || item.topic_code === 'digital_assets' },

        // 4. الأوراق التجارية والمدفوعات
        'checks': { title: 'خصم الشيكات والكمبيالات الآجلة', filter: (item) => item.topic_code === 'checks' },
        'earnest_money': { title: 'بيع العربون وحجز البضاعة', filter: (item) => item.topic_code === 'contracts' || (item._normSearchStr && item._normSearchStr.includes('عربون')) },
        'debt_sale': { title: 'بيع الدين والمقاصة المالية', filter: (item) => item.topic_code === 'checks' || item.topic_code === 'installments' },
        'iqala': { title: 'إقالة النادم والفسخ الرضائي', filter: (item) => item.topic_code === 'contracts' },
        'salam': { title: 'بيع السلم ودفع الثمن مقدماً', filter: (item) => item.topic_code === 'contracts' || item.topic_code === 'letters_of_credit' },
        'contracts_terms': { title: 'العقود والشروط وحرية الاتفاق', filter: (item) => item.topic_code === 'contracts' },

        // 5. جميع مسائل المعاملات المالية (الموسوعة الشاملة)
        'stocks': { title: 'الشركات والأسهم والمضاربة', filter: (item) => item.topic_code === 'companies' },
        'ecommerce': { title: 'التجارة الإلكترونية ونقاط البيع POS', filter: (item) => item.topic_code === 'ecommerce' },
        'insurance': { title: 'التأمين التعاوني والتكافلي', filter: (item) => item.topic_code === 'insurance' },
        'guarantee': { title: 'الكفالة والضمان الشخصي', filter: (item) => item.topic_code === 'guarantee' },
        'franchise': { title: 'الامتياز التجاري (الفرنشايز)', filter: (item) => item.topic_code === 'franchise' },
        'letters_of_credit': { title: 'الاعتمادات والشحن الدولي', filter: (item) => item.topic_code === 'letters_of_credit' },
        'zakat': { title: 'الزكاة وعروض التجارة والديون', filter: (item) => item.topic_code === 'zakat' },
        'pawn': { title: 'الرهن الحيازي والتجاري', filter: (item) => item.topic_code === 'pawn' },
        'emergency': { title: 'الجوائح والظروف الطارئة', filter: (item) => item.topic_code === 'emergency' },
        'bankruptcy': { title: 'التفليس وقسمة الغرماء', filter: (item) => item.topic_code === 'bankruptcy' },
        'loyalty_points': { title: 'نقاط المكافآت وبطاقات الولاء', filter: (item) => item.topic_code === 'timeshare_realestate' || (item._normSearchStr && (item._normSearchStr.includes('مكافا') || item._normSearchStr.includes('ولاء'))) },
        'crowdfunding': { title: 'التمويل الجماعي والاستثماري', filter: (item) => item.topic_code === 'crowdfunding' }
    };

    function getMasterFatwas() {
        return window.MASTER_20000_FATWAS || window.MASTER_5500_FATWAS || [];
    }

    function indexFatwasDataset() {
        const dataset = getMasterFatwas();
        if (!dataset || dataset.length === 0) return;
        for (let i = 0; i < dataset.length; i++) {
            const item = dataset[i];
            if (!item._normSearchStr) {
                item._normSearchStr = normalizeArabicText(
                    (item.question || '') + ' ' +
                    (item.summary || '') + ' ' +
                    (item.evidence || '') + ' ' +
                    (item.scholar_name || '') + ' ' +
                    (item.topic_name || '') + ' ' +
                    (item.tags || '') + ' ' +
                    (item.ruling || '') + ' ' +
                    (item.source || '')
                );
            }
        }
    }

    function ensureFiqhLoaded(callback) {
        if (window.MASTER_20000_FATWAS && Array.isArray(window.MASTER_20000_FATWAS) && window.MASTER_20000_FATWAS.length > 0) {
            indexFatwasDataset();
            if (callback) callback();
            return Promise.resolve();
        }

        if (fiqhLoadingPromise) {
            return fiqhLoadingPromise.then(() => {
                indexFatwasDataset();
                if (callback) callback();
            });
        }

        const grid = document.getElementById('fiqh_fatwas_grid');
        const badge = document.getElementById('fiqh_search_badge');
        if (badge) {
            badge.innerHTML = '<i class="fa-solid fa-cloud-arrow-down text-amber-400 animate-pulse"></i> جاري تجهيز الموسوعة (4,500 فتوى)...';
        }

        fiqhLoadingPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'js/fiqh_20000_data.js?v=4500_v2';
            script.async = true;
            script.onload = () => {
                indexFatwasDataset();
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
            const input = document.getElementById('fiqh-search-input');
            if (input && input.value.trim() !== '') {
                executeLiveFilter();
            } else {
                currentActiveButtonKey = null;
                currentActiveButtonTitle = '';
                currentFilteredList = getMasterFatwas();
                currentPage = 1;
                renderFatwaCards();
            }
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
                badge.innerHTML = `<i class="fa-solid fa-layer-group text-emerald-400"></i> إجمالي <strong>${totalAll.toLocaleString()}</strong> فتوى موثقة | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (21 فتوى في الصفحة)`;
            } else if (currentActiveButtonTitle) {
                badge.innerHTML = `<i class="fa-solid fa-filter text-amber-400"></i> عُثر على <strong>${totalMatches.toLocaleString()}</strong> فتوى تخص: <span class="text-amber-300 font-bold">${currentActiveButtonTitle}</span> | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (21 فتوى في الصفحة)`;
            } else {
                badge.innerHTML = `<i class="fa-solid fa-filter text-amber-400"></i> عُثر على <strong>${totalMatches.toLocaleString()}</strong> من أصل ${totalAll.toLocaleString()} فتوى | صفحة <strong>${currentPage.toLocaleString()}</strong> من <strong>${totalPages.toLocaleString()}</strong> (21 فتوى في الصفحة)`;
            }
        }

        if (totalMatches === 0) {
            grid.innerHTML = `
                <div class="col-span-full p-10 bg-slate-900/90 border-2 border-dashed border-amber-500/40 rounded-3xl text-center space-y-3">
                    <span class="text-4xl">🔍</span>
                    <h4 class="text-lg font-black text-amber-300">لم يتم العثور على نتائج مطابقة لبحثك في موسوعة الفتاوى</h4>
                    <p class="text-xs text-slate-300 font-bold">جرب كتابة كلمة أخرى مثل: تقسيط، شيك، ذهب، عربون، توريد، أو تصفح حسب الشيوخ الـ 12.</p>
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
                <div class="p-5 bg-slate-950/90 border-2 border-slate-800/90 hover:border-amber-500/60 rounded-2xl flex flex-col justify-between transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-amber-500/10 group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
                            <span class="px-2.5 py-1 text-[11px] font-black rounded-lg border ${rulingClass} shadow-sm">
                                ${item.ruling}
                            </span>
                            <span class="text-[11px] font-mono text-amber-400/90 font-black px-2 py-0.5 bg-amber-500/10 rounded-md border border-amber-500/20">
                                #${item.id}
                            </span>
                        </div>
                        
                        <h4 class="text-sm font-black text-white group-hover:text-amber-300 transition-colors leading-snug">
                            ${item.question}
                        </h4>
                        
                        ${hadithBox}

                        <p class="text-xs text-slate-300 leading-relaxed font-bold bg-slate-900/70 p-3 rounded-xl border border-slate-800/60">
                            ${item.summary}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400 font-bold">
                        <span class="text-amber-300/90 font-black flex items-center gap-1">
                            <i class="fa-solid fa-user-tie text-[10px]"></i> ${item.scholar_name}
                        </span>
                        <span class="text-slate-400 truncate max-w-[200px]" title="${item.source}">
                            <i class="fa-solid fa-book text-[10px]"></i> ${item.source}
                        </span>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;

        // Render Pagination Bars
        updatePaginationBarHtml('fiqh_pagination_bar_top', totalPages, totalMatches, startIndex + 1, endIndex);
        updatePaginationBarHtml('fiqh_pagination_bar_bottom', totalPages, totalMatches, startIndex + 1, endIndex);
    }

    function updatePaginationBarHtml(containerId, totalPages, totalMatches, fromItem, toItem) {
        const bar = document.getElementById(containerId);
        if (!bar) return;

        if (totalMatches === 0) {
            bar.innerHTML = '<div class="text-xs text-slate-400 font-bold w-full text-center">لا توجد صفحات للعرض</div>';
            return;
        }

        let pagesHtml = '';
        const maxButtons = 5;
        let startP = Math.max(1, currentPage - 2);
        let endP = Math.min(totalPages, startP + maxButtons - 1);
        if (endP - startP < maxButtons - 1) {
            startP = Math.max(1, endP - maxButtons + 1);
        }

        if (startP > 1) {
            pagesHtml += `<button type="button" onclick="goToFiqhPage(1)" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 hover:border-amber-500 text-slate-300 text-xs font-black transition">1</button>`;
            if (startP > 2) {
                pagesHtml += `<span class="text-slate-500 text-xs px-1">...</span>`;
            }
        }

        for (let p = startP; p <= endP; p++) {
            const isActive = (p === currentPage);
            const activeClasses = isActive 
                ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-black shadow-md shadow-amber-500/20 border-amber-400' 
                : 'bg-slate-900 border border-slate-800 hover:border-amber-500 text-slate-300 font-bold';
            pagesHtml += `<button type="button" onclick="goToFiqhPage(${p})" class="w-8 h-8 rounded-lg text-xs transition ${activeClasses}">${p}</button>`;
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) {
                pagesHtml += `<span class="text-slate-500 text-xs px-1">...</span>`;
            }
            pagesHtml += `<button type="button" onclick="goToFiqhPage(${totalPages})" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 hover:border-amber-500 text-slate-300 text-xs font-black transition">${totalPages}</button>`;
        }

        bar.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="text-xs text-amber-300 font-black">
                    عرض المسائل (${fromItem.toLocaleString()} - ${toItem.toLocaleString()}) من أصل ${totalMatches.toLocaleString()} مسألة
                </span>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                <button type="button" onclick="goToFiqhPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled class="opacity-40 cursor-not-allowed px-3 py-1.5 bg-slate-900 text-slate-400 text-xs font-black rounded-lg border border-slate-800"' : 'class="px-3 py-1.5 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-amber-300 text-xs font-black rounded-lg border border-slate-800 hover:border-amber-400 transition shadow-sm"'}>
                    <i class="fa-solid fa-chevron-right"></i> السابق
                </button>
                ${pagesHtml}
                <button type="button" onclick="goToFiqhPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled class="opacity-40 cursor-not-allowed px-3 py-1.5 bg-slate-900 text-slate-400 text-xs font-black rounded-lg border border-slate-800"' : 'class="px-3 py-1.5 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-amber-300 text-xs font-black rounded-lg border border-slate-800 hover:border-amber-400 transition shadow-sm"'}>
                    التالي <i class="fa-solid fa-chevron-left"></i>
                </button>
            </div>
        `;
    }

    function goToFiqhPage(page) {
        const totalMatches = currentFilteredList.length;
        const totalPages = Math.max(1, Math.ceil(totalMatches / PAGE_SIZE));
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderFatwaCards();
        const el = document.getElementById('fiqh_pagination_bar_top');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function filterFatwas() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            executeLiveFilter();
        }, 50); // Instant 50ms response
    }

    function executeLiveFilter() {
        ensureFiqhLoaded(() => {
            const input = document.getElementById('fiqh-search-input');
            const rawQ = input ? input.value.trim() : '';

            // If user cleared input, reset button filter
            if (!rawQ) {
                currentActiveButtonKey = null;
                currentActiveButtonTitle = '';
                const allBtn = document.getElementById('btn-show-all-fiqh');
                if (allBtn) {
                    document.querySelectorAll('.fiqh-pill-btn').forEach(b => b.classList.remove('active'));
                    allBtn.classList.add('active');
                }
            } else if (currentActiveButtonTitle && rawQ !== currentActiveButtonTitle) {
                // User is actively editing text away from clicked button title
                currentActiveButtonKey = null;
                currentActiveButtonTitle = '';
                document.querySelectorAll('.fiqh-pill-btn').forEach(b => b.classList.remove('active'));
            }

            const dataset = getMasterFatwas();
            currentPage = 1;

            if (currentActiveButtonKey && FIQH_BUTTON_FILTERS[currentActiveButtonKey]) {
                const btnDef = FIQH_BUTTON_FILTERS[currentActiveButtonKey];
                currentFilteredList = dataset.filter(item => {
                    if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                    return btnDef.filter(item);
                });
            } else if (rawQ) {
                // Free text search with smart token matching and Arabic prefix stripping
                const normQ = normalizeArabicText(rawQ);
                const rawTokens = normQ.split(/\s+/).filter(t => t.length > 0);
                const cleanTokens = rawTokens.map(cleanToken).filter(t => t.length > 1);

                // 1. Strict match on raw tokens
                let matches = dataset.filter(item => {
                    if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                    const s = item._normSearchStr || '';
                    return rawTokens.every(t => s.includes(t));
                });

                // 2. Strict match on cleaned tokens (without ال, و, بال prefixes)
                if (matches.length === 0 && cleanTokens.length > 0) {
                    matches = dataset.filter(item => {
                        if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                        const s = item._normSearchStr || '';
                        return cleanTokens.every(t => s.includes(t));
                    });
                }

                // 3. Fallback: match any significant token
                if (matches.length === 0 && cleanTokens.length > 1) {
                    const stopWords = ['في', 'من', 'عن', 'على', 'الي', 'الى', 'مع', 'او', 'و', 'ما', 'هل', 'حكم'];
                    const sig = cleanTokens.filter(t => !stopWords.includes(t) && t.length > 2);
                    if (sig.length > 0) {
                        matches = dataset.filter(item => {
                            if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                            const s = item._normSearchStr || '';
                            return sig.some(t => s.includes(t));
                        });
                    }
                }

                currentFilteredList = matches;
            } else {
                currentFilteredList = dataset.filter(item => {
                    if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) return false;
                    return true;
                });
            }

            renderFatwaCards();
        });
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 🌟 36 BUTTON CONTROLLER & SCHOLAR SELECT CONTROLLERS 🌟
    // ════════════════════════════════════════════════════════════════════════════
    function filterByFiqhButton(btnKey, el) {
        ensureFiqhLoaded(() => {
            currentActiveButtonKey = btnKey;
            const filterDef = FIQH_BUTTON_FILTERS[btnKey];
            if (!filterDef) return;

            currentActiveButtonTitle = filterDef.title;

            const input = document.getElementById('fiqh-search-input');
            if (input) {
                input.value = filterDef.title;
            }

            // Update active state on buttons
            document.querySelectorAll('.fiqh-pill-btn').forEach(btn => btn.classList.remove('active'));
            if (el) el.classList.add('active');

            const allBtn = document.getElementById('btn-show-all-fiqh');
            if (allBtn && el !== allBtn) allBtn.classList.remove('active');

            // Switch to fatwas subtab
            const fatwasPill = document.querySelector('.fiqh-nav-pill');
            if (typeof switchFiqhSubTab === 'function' && fatwasPill) {
                switchFiqhSubTab('fatwas', fatwasPill);
            }

            const dataset = getMasterFatwas();
            currentFilteredList = dataset.filter(item => {
                if (currentScholarFilter !== 'all' && item.scholar_code !== currentScholarFilter) {
                    return false;
                }
                return filterDef.filter(item);
            });

            currentPage = 1;
            renderFatwaCards();

            // 🚀 Smooth scroll directly to the fatwas cards so the user sees results immediately! 🚀
            setTimeout(() => {
                const fatwasSection = document.getElementById('fiqh-sub-fatwas');
                if (fatwasSection) {
                    fatwasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 60);
        });
    }

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

        setTimeout(() => {
            const fatwasSection = document.getElementById('fiqh-sub-fatwas');
            if (fatwasSection) {
                fatwasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 60);
    }

    function showAllFiqhTransactions(btn) {
        ensureFiqhLoaded(() => {
            currentActiveButtonKey = null;
            currentActiveButtonTitle = '';

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
            
            currentFilteredList = getMasterFatwas();
            currentPage = 1;
            renderFatwaCards();

            setTimeout(() => {
                const fatwasSection = document.getElementById('fiqh-sub-fatwas');
                if (fatwasSection) {
                    fatwasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 60);
        });
    }

    function setQuickSearch(word, el) {
        // Find if word matches any of the button filter keys or titles
        for (const [key, def] of Object.entries(FIQH_BUTTON_FILTERS)) {
            if (def.title.includes(word) || word.includes(def.title)) {
                filterByFiqhButton(key, el);
                return;
            }
        }
        const input = document.getElementById('fiqh-search-input');
        if (input) input.value = word;
        currentActiveButtonKey = null;
        currentActiveButtonTitle = '';
        executeLiveFilter();
    }

    function clearFiqhSearch() {
        const input = document.getElementById('fiqh-search-input');
        if (input) input.value = '';
        
        currentActiveButtonKey = null;
        currentActiveButtonTitle = '';
        currentScholarFilter = 'all';
        currentTopicFilter = 'all';
        
        const scholarSelect = document.getElementById('fiqh-scholar-select');
        if (scholarSelect) scholarSelect.value = 'all';

        const googleScholarSelect = document.getElementById('google-scholar-select');
        if (googleScholarSelect) googleScholarSelect.value = 'all';
        
        const allBtn = document.getElementById('btn-show-all-fiqh');
        if (allBtn) allBtn.classList.add('active');

        document.querySelectorAll('.fiqh-pill-btn').forEach(btn => {
            if (btn !== allBtn) btn.classList.remove('active');
        });

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

    document.addEventListener("DOMContentLoaded", function() {
        if (document.getElementById('fiqh_fatwas_grid')) {
            initFiqhEngine();
        }
    });
    
</script>
</body>
</html>
