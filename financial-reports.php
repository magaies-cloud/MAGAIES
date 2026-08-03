<?php $page_title = 'القوائم الختامية والمحاسبية'; ?>
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
</script>
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>القوائم الختامية والمحاسبية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        :root {
            --bg-color: #050308;
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        :root.light-theme {
            --bg-color: #f1f5f9;
            --glass-border: rgba(0, 0, 0, 0.08);
        }
        :root.light-theme body {
            background-color: var(--bg-color);
            color: #000000;
        }
        :root.light-theme .ambient-bg {
            background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #cbd5e1 100%) !important;
        }
        :root.light-theme .section-heading {
            color: #000000 !important;
        }
        :root.light-theme .section-heading .heading-icon {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04) !important;
        }
        :root.light-theme .bento-card {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #000000 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05) !important;
        }
        :root.light-theme .bento-card:hover {
            background: #f8fafc !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .bento-card .card-title {
            color: #000000 !important;
        }
        :root.light-theme .bento-card .card-desc {
            color: #475569 !important;
        }
        :root.light-theme .bento-card .card-icon-wrapper {
            background: rgba(0, 0, 0, 0.04) !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        :root.light-theme .bento-card .badge {
            background: rgba(37, 99, 235, 0.1) !important;
            color: #2563eb !important;
            border-color: rgba(37, 99, 235, 0.2) !important;
        }

        body {
            background-color: var(--bg-color);
            color: #f1f5f9;
            font-family: 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            overflow-x: hidden;
        }

        body.has-sidebar { padding-right: 0 !important; }

        /* Ambient Animated Background */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            z-index: -1; overflow: hidden; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px);
            animation: floatOrb 20s infinite alternate ease-in-out; opacity: 0.15;
        }
        .orb-1 { width: 600px; height: 600px; background: #38bdf8; top: -10%; right: -10%; }
        .orb-2 { width: 500px; height: 500px; background: #c084fc; bottom: -10%; left: -10%; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: #34d399; top: 40%; left: 40%; animation-delay: -10s; opacity: 0.1;}

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        .hub-container {
            max-width: 1600px; margin: 0 auto; padding: 0px 30px 20px 30px;
        }


        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #a855f7, #6366f1);
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #94a3b8; font-weight: 500; }
        
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.6rem; font-weight: 900; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }

        .header-controls { display: flex; align-items: center; gap: 15px; }
        .clock-widget { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); padding: 8px 20px; border-radius: 14px; text-align: center; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }
        .clock-widget #header-clock { color: #e2e8f0; font-size: 1.25rem; font-weight: 800; letter-spacing: 1.5px; font-family: 'Outfit', sans-serif; }
        .clock-widget #header-date-mini { color: #64748b; font-size: 0.7rem; display: block; margin-top: 2px; font-weight: 600; }
        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        /* Section Headings */
        .section-heading {
            font-size: 1.6rem; font-weight: 900; color: #fff; margin: 0 0 20px 0;
            display: flex; align-items: center; gap: 12px;
            animation: fadeIn 0.8s ease backwards;
        }
        .heading-icon {
            display: flex; align-items: center; justify-content: center;
            width: 45px; height: 45px; border-radius: 12px; font-size: 1.4rem;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        }

        /* Bento Grid Layout */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-auto-flow: dense;
            gap: 20px;
            margin-bottom: 50px;
            animation: fadeIn 1s ease backwards;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Staggered Animations */
        .section-1 { animation-delay: 0.1s; }
        .grid-1 { animation-delay: 0.2s; }
        .section-2 { animation-delay: 0.3s; }
        .grid-2 { animation-delay: 0.4s; }
        .section-3 { animation-delay: 0.5s; }
        .grid-3 { animation-delay: 0.6s; }
        .section-4 { animation-delay: 0.7s; }
        .grid-4 { animation-delay: 0.8s; }

        /* Bento Card Ultra Premium */
        .bento-card {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            padding: 25px;
            text-decoration: none;
            color: #fff;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 15px;
            transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
            transform-style: preserve-3d;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .bento-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 100%;
            background: radial-gradient(circle at top left, var(--glow-color), transparent 60%);
            opacity: 0.05; transition: opacity 0.5s ease;
        }
        
        .bento-card::after {
            content: ''; position: absolute; inset: 0;
            border-radius: 24px; padding: 2px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent 50%, var(--glow-color));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            opacity: 0; transition: opacity 0.5s ease;
        }

        .bento-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 20px var(--glow-color);
            background: rgba(15, 23, 42, 0.7);
            z-index: 10;
        }

        .bento-card:hover::before { opacity: 0.15; }
        .bento-card:hover::after { opacity: 1; }

        .card-icon-wrapper {
            width: 65px; height: 65px; border-radius: 18px;
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative; z-index: 2;
        }

        .bento-card:hover .card-icon-wrapper {
            transform: scale(1.15) rotate(5deg);
            background: var(--glow-color);
            border-color: transparent;
            box-shadow: 0 10px 25px var(--glow-color);
        }

        .card-content { position: relative; z-index: 2; }
        .card-title { font-size: 1.35rem; font-weight: 800; margin: 0 0 8px 0; color: #f8fafc; letter-spacing: -0.3px; }
        .card-desc { font-size: 0.9rem; color: #94a3b8; line-height: 1.6; font-weight: 500; margin: 0; }
        
        .bento-large {
            grid-column: span 2;
            flex-direction: row;
            align-items: center;
            gap: 25px;
        }
        
        /* Status Badges */
        .badge {
            position: absolute; top: 20px; left: 20px; z-index: 3;
            font-size: 0.7rem; font-weight: 800; padding: 4px 12px; border-radius: 20px;
            background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3);
            letter-spacing: 0.5px; opacity: 0.8; transition: 0.3s;
        }
        .badge-new { background: rgba(56,189,248,0.15); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); }
        .bento-card:hover .badge { opacity: 1; transform: scale(1.05); }

        @media (max-width: 900px) {
            .bento-large { grid-column: span 1; flex-direction: column; align-items: flex-start; }
            .bento-grid { grid-template-columns: 1fr; }
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
\n</style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="hub-container">
        
        <?php $header_theme_class = 'header-theme-reports'; include 'includes/header.php'; ?>

        <div style="margin-top: 15px; margin-bottom: 10px;">
            <a href="analytics.php" onclick="window.location.href='analytics.php'; return false;" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(251, 191, 36, 0.1); border: 1.5px solid #fbbf24; color: #fbbf24; padding: 8px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px; transition: all 0.3s; box-shadow: 0 0 15px rgba(251, 191, 36, 0.2);" onmouseover="this.style.background='#fbbf24'; this.style.color='#000';" onmouseout="this.style.background='rgba(251, 191, 36, 0.1)'; this.style.color='#fbbf24';">
                <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع للشاشة السابقة (التقارير الرئيسية)
            </a>
        </div>

        <!-- 4. Final Statements Grid -->
        <h2 class="section-heading section-4"><span class="heading-icon" style="color:#fbbf24; box-shadow:0 0 15px rgba(251,191,36,0.2);">🏛️</span> القوائم الختامية والمحاسبية</h2>
        <div class="bento-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <!-- 1. ميزان المراجعة -->
            <a data-href="trial-balance.php" onclick="window.location.href='trial-balance.php'" class="bento-card" style="--glow-color: rgba(251, 191, 36, 0.65);">
                <span class="badge badge-new">متقدم</span>
                <div class="card-icon-wrapper">⚖️</div>
                <div class="card-content">
                    <h3 class="card-title">ميزان المراجعة</h3>
                    <p class="card-desc">كشف بأرصدة الحسابات ومجاميعها لضمان التوازن المحاسبي الكامل.</p>
                </div>
            </a>

            <!-- 2. قائمة الأرباح والخسائر -->
            <a data-href="pl-statement.php" onclick="window.location.href='pl-statement.php'" class="bento-card" style="--glow-color: rgba(251, 191, 36, 0.65);">
                <span class="badge badge-new">متقدم</span>
                <div class="card-icon-wrapper">📊</div>
                <div class="card-content">
                    <h3 class="card-title">قائمة الأرباح والخسائر</h3>
                    <p class="card-desc">تحليل دقيق للإيرادات والمصروفات لاستخراج صافي الدخل.</p>
                </div>
            </a>

            <!-- 3. قيود اليومية -->
            <a data-href="journal-voucher.php" onclick="window.location.href='journal-voucher.php'" class="bento-card" style="--glow-color: rgba(251, 191, 36, 0.65);">
                <span class="badge">مفعل</span>
                <div class="card-icon-wrapper">✍️</div>
                <div class="card-content">
                    <h3 class="card-title">قيود اليومية</h3>
                    <p class="card-desc">مراجعة كافة قيود التسوية والقيود اليدوية والآلية (القيد المزدوج).</p>
                </div>
            </a>

            <!-- 4. تقرير الجرد العام لنهاية السنة والأصول الثابتة وحساب الزكاة الشرعية (ممتد بعرض الشاشة طولياً) -->
            <a data-href="annual_inventory_zakat_report.php" onclick="window.location.href='annual_inventory_zakat_report.php'" class="bento-card bento-large" style="grid-column: 1 / -1; width: 100%; --glow-color: rgba(16, 185, 129, 0.75); background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(2, 132, 199, 0.15)); border: 2.5px solid #10b981;">
                <span class="badge badge-new" style="background: #10b981; color: #ffffff; border: none; font-weight: 900;"><i class="fa-solid fa-star"></i> التقرير الشامل والمحدث 2026</span>
                <div class="card-icon-wrapper" style="background: rgba(16, 185, 129, 0.2); font-size: 2.2rem;">⚖️</div>
                <div class="card-content" style="flex: 1;">
                    <h3 class="card-title" style="font-size: 1.35rem; color: #10b981;">تقرير الجرد العام لنهاية السنة والأصول الثابتة وحساب الزكاة الشرعية</h3>
                    <p class="card-desc" style="font-size: 0.95rem; opacity: 0.95;">يتضمن جرد وتقييم مخزون عروض التجارة، مديونيات العملاء والموردين المجمعة (له وعليه)، الأصول الثابتة والإهلاكات، وحاسبة الزكاة الإسلامية بنسبة 2.5%.</p>
                </div>
            </a>
        </div>

    </div>

    <script defer src="js/app.js?v=20260803.64462"></script>
    <script>
        // Clock functionality for header
        function updateClock() {
            const now = new Date();
            const timeElement = document.getElementById('header-clock');
            const dateElement = document.getElementById('header-date-mini');
            if(timeElement) timeElement.textContent = now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12: true});
            if(dateElement) dateElement.textContent = now.toLocaleDateString('ar-EG');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // High Performance 3D Parallax effect on Bento Cards
        document.querySelectorAll('.bento-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left; 
                const y = e.clientY - rect.top;  
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                // Calculate rotation (max 4 degrees for subtle premium feel)
                const rotateX = ((y - centerY) / centerY) * -4;
                const rotateY = ((x - centerX) / centerX) * 4;
                
                card.style.transform = `translateY(-8px) scale(1.02) perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = `translateY(0) scale(1) perspective(1000px) rotateX(0deg) rotateY(0deg)`;
            });
        });
    </script>
</body>
</html>
