<?php $page_title = 'الإعدادات'; ?>
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس 2026 - الإعدادات</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
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
        }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex; flex-direction: column;
            overflow-x: hidden;
            font-family: 'Tajawal', sans-serif;
            color: #fff;
        }

        /* Ambient Animated Background Orbs */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15;
            animation: floatOrb 20s infinite alternate ease-in-out;
        }

        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Layout Container */
        .settings-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 25px 25px 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 30px;
            flex: 1;
        }


        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #94a3b8; font-weight: 500; }

        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.6rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }

        .header-controls { display: flex; align-items: center; gap: 15px; }
        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 46px; height: 46px;
            border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s ease;
        }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .save-btn-main {
            background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399;
            padding: 10px 24px; border-radius: 14px; font-size: 1rem; font-weight: 800; cursor: pointer;
            transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-family: 'Tajawal', sans-serif;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.1);
        }
        .save-btn-main:hover { background: rgba(16, 185, 129, 0.25); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.5); color: #fff;}

        /* Save Badge */
        .save-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
            color: #34d399; padding: 10px 20px; border-radius: 20px;
            font-size: 0.95rem; font-weight: 800; opacity: 0; transition: opacity 0.4s;
            position: fixed; top: 110px; left: 50%; transform: translateX(-50%); z-index: 1000;
            backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.5); pointer-events: none;
        }
        .save-badge.show { opacity: 1; pointer-events: all; }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }

        /* Glass Panel Settings Card */
        .glass-panel {
            background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 24px; padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.4s ease; display: flex; flex-direction: column;
            animation: fadeUpIn 0.6s ease-out both;
        }
        .glass-panel:nth-child(1) { animation-delay: 0.1s; }
        .glass-panel:nth-child(2) { animation-delay: 0.2s; }
        .glass-panel:nth-child(3) { animation-delay: 0.3s; }
        .glass-panel:nth-child(4) { animation-delay: 0.4s; }
        .glass-panel:nth-child(5) { animation-delay: 0.5s; }
        
        .glass-panel:hover { border-color: var(--border-highlight); box-shadow: 0 20px 45px rgba(0,0,0,0.4); transform: translateY(-5px); }

        .setting-title {
            font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 25px;
            padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; gap: 10px;
        }

        /* Form Controls */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem; color: #94a3b8; }
        
        .form-input, .form-select {
            width: 100%; padding: 14px 16px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3);
            color: #fff; box-sizing: border-box; font-family: 'Tajawal', sans-serif;
            font-size: 1rem; transition: all 0.3s;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: var(--primary-accent); background: rgba(0,0,0,0.5);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        .form-select option { background: #0c0812; color: #fff; }

        .setting-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.05); }
        .setting-row:last-child { border-bottom: none; margin-bottom: 0; }

        /* Premium Toggle Switch */
        .switch { position: relative; display: inline-block; width: 54px; height: 28px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid rgba(255,255,255,0.05); }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 3px; background-color: #94a3b8; transition: .4s cubic-bezier(0.68, -0.55, 0.265, 1.55); border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        
        input:checked + .slider { background-color: #10b981; border-color: #059669; }
        input:checked + .slider:before { transform: translateX(26px); background-color: #ffffff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); }

        
        /* Sub-Tabs Navigation */
        .sub-tabs-container {
            display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; overflow-x: auto;
        }
        .sub-tab-btn {
            background: transparent; border: none; color: #94a3b8; font-family: 'Tajawal', sans-serif;
            font-size: 0.95rem; font-weight: 600; padding: 10px 20px; cursor: pointer; border-radius: 8px;
            transition: all 0.3s; white-space: nowrap;
        }
        .sub-tab-btn:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sub-tab-btn.active { background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
        .sub-tab-content { display: none; animation: fadeIn 0.3s ease-out; }
        .sub-tab-content.active { display: block; }

        /* Action Buttons */
        .action-btn {
            width: 100%; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 1rem;
            border: none; cursor: pointer; margin-top: 10px; transition: all 0.3s;
            font-family: 'Tajawal', sans-serif; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-users { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .btn-users:hover { background: rgba(56, 189, 248, 0.2); color: #fff; transform: translateY(-2px); }
        
        .btn-backup { background: rgba(168, 85, 247, 0.1); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); margin-bottom: 10px; }
        .btn-backup:hover { background: rgba(168, 85, 247, 0.2); color: #fff; transform: translateY(-2px); }
        
        .btn-restore { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .btn-restore:hover { background: rgba(245, 158, 11, 0.2); color: #fff; transform: translateY(-2px); }

        @keyframes fadeUpIn { from { opacity: 0; transform: translateY(40px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        @media (max-width: 768px) {
            .settings-grid { grid-template-columns: 1fr; }
        }
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }

        /* Light Theme Overrides for Typography */
        :root.light-theme .setting-title {
            color: #0f172a !important;
            border-bottom-color: rgba(0,0,0,0.1) !important;
        }
        :root.light-theme .form-label {
            color: #475569 !important;
        }
        :root.light-theme .setting-row {
            border-bottom-color: rgba(0,0,0,0.1) !important;
            color: #1e293b !important;
        }
        :root.light-theme .setting-row span,
        :root.light-theme .glass-panel p,
        :root.light-theme .setting-row label {
            color: #1e293b !important;
        }
        :root.light-theme .btn-users:hover,
        :root.light-theme .btn-backup:hover,
        :root.light-theme .btn-restore:hover {
            color: #0f172a !important;
        }
        :root.light-theme .form-input,
        :root.light-theme .form-select {
            color: #0f172a !important;
        }
        :root.light-theme .slider {
            background-color: rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        :root.light-theme .slider:before {
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
\n
        /* Horizontal Tabs Styling */
        .settings-layout {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Reduced gap between nav and panels */
        }
        .tabs-nav {
            display: flex;
            flex-wrap: wrap; /* Wrap tabs into multiple lines */
            justify-content: center; /* Center the wrapped tabs */
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 8px;
            gap: 8px;
            backdrop-filter: blur(10px);
            margin-bottom: 5px; /* Reduced margin below the tabs */
        }
        
        :root.light-theme .tabs-nav {
            background: #e2e8f0 !important; /* Silver background in Light Mode */
            border-color: rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .tab-btn {
            background: transparent;
            color: #94a3b8;
            border: 1px solid transparent;
            padding: 10px 18px; /* Slightly adjusted padding */
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Smoother transition */
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px); /* Professional lift effect */
        }
        :root.light-theme .tab-btn { color: #64748b; }
        :root.light-theme .tab-btn:hover { 
            color: #0f172a; 
            background: rgba(0,0,0,0.03); 
            border-color: rgba(0,0,0,0.1);
        }

        .tab-btn.active {
            background: #38bdf8;
            color: #0f172a;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3);
        }
        :root.light-theme .tab-btn.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3);
        }
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease-out forwards;
        }
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes alertPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .pulse-alert {
            animation: alertPulse 1.5s infinite !important;
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: white !important;
            border: none !important;
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

<div class="settings-container">

    <div id="save-badge" class="save-badge">✅ تم حفظ الإعدادات بنجاح</div>

    <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-settings'; ?>
        <?php include 'includes/header.php'; ?>

    <!-- Tabs Navigation -->
    <div class="tabs-nav">
        <button class="tab-btn active" onclick="switchTab('company')" data-i18n="settings.companyData">🏢 بيانات الشركة</button>
        <button class="tab-btn" onclick="switchTab('finance')" data-i18n="settings.financeTax">💰 المالية والضرائب</button>
        <button class="tab-btn" onclick="switchTab('inventory')" data-i18n="settings.warehouses">📦 المخازن</button>
        <button class="tab-btn" onclick="switchTab('printing')" data-i18n="settings.printing">🖨️ إعدادات الطباعة</button>
        <button class="tab-btn" onclick="switchTab('email')" data-i18n="settings.smtp">📧 إعدادات البريد SMTP</button>

        <button class="tab-btn" onclick="switchTab('users')" data-i18n="settings.users">🛡️ المستخدمين</button>
        <button class="tab-btn" onclick="switchTab('ui')" data-i18n="settings.langUI">🎨 اللغة والواجهة</button>
        <button class="tab-btn" onclick="switchTab('system')" data-i18n="settings.system">💾 النظام والنسخ</button>
    </div>

    <div class="settings-layout">
        
        <!-- Tab: Company -->
        <div class="tab-content active" id="tab-company">
            <div class="glass-panel" id="panel-company">
                <div class="setting-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <span>🏢 بيانات الشركة والاشتراك</span>
                    <span id="subscription-status" style="font-size: 0.9rem; padding: 5px 12px; border-radius: 20px; background: rgba(255,255,255,0.1); color: #fff; display:inline-flex; align-items:center; gap:5px; font-weight:600;">
                        ⏳ جاري فحص الاشتراك...
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <!-- Row 1: Logo and Preview -->
                    <div class="form-group" style="grid-column: span 2; display: flex; align-items: center; gap: 20px; margin:0;">
                        <div style="flex: 1;">
                            <label class="form-label">شعار الشركة (اللوقو)</label>
                            <input type="file" id="cfg-logo" accept="image/*" class="form-input" style="padding: 10px;">
                        </div>
                        <div id="logo-preview-container" style="width:120px; height:80px; border:1px dashed rgba(255,255,255,0.3); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:rgba(0,0,0,0.2); margin-top: 25px;">
                            <img id="logo-preview" src="" alt="شعار الشركة" style="display:none; max-height:100%; max-width:100%; object-fit:contain;">
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="form-group" style="margin: 0;"><label class="form-label">اسم الشركة المطبوع على الفواتير</label><input type="text" class="form-input" id="cfg-company-name" placeholder="مثال: مؤسسة مقاييس للتجارة"></div>
                    <div class="form-group" style="margin: 0;"><label class="form-label">الرقم الضريبي (VAT Number)</label><input type="text" class="form-input" id="cfg-tax-number" placeholder="الرقم الضريبي للشركة"></div>

                    <!-- Row 3 -->
                    <div class="form-group" style="margin: 0;"><label class="form-label">رقم الواتساب (WhatsApp)</label><input type="tel" class="form-input" id="cfg-whatsapp" placeholder="+249..." dir="ltr" style="text-align: left;" oninput="this.value = this.value.replace(/[^0-9+\\- ]/g, '')"></div>
                    <div class="form-group" style="margin: 0;"><label class="form-label">رقم الهاتف للتواصل</label><input type="tel" class="form-input" id="cfg-phone" placeholder="+249..." dir="ltr" style="text-align: left;" oninput="this.value = this.value.replace(/[^0-9+\\- ]/g, '')"></div>

                    <!-- Row 4 -->
                    <div class="form-group" style="margin: 0;"><label class="form-label">العنوان</label><input type="text" class="form-input" id="cfg-address" placeholder="العنوان الكامل للطباعة"></div>
                    <div class="form-group" style="margin: 0;"><label class="form-label">رابط الموقع / الخرائط</label><input type="url" class="form-input" id="cfg-location" placeholder="رابط Google Maps أو وصف الموقع"></div>
                </div>
            </div>
        </div>

        <!-- Tab: Finance -->
        <div class="tab-content" id="tab-finance">
            <div class="glass-panel" id="panel-finance">
                <div class="setting-title">💰 الإعدادات المالية والضرائب</div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <!-- Row 1 -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">العملة الافتراضية للنظام (المحلية)</label>
                        <select class="form-select" id="cfg-currency" disabled>
                            <option value="SDG" selected>الجنيه السوداني (SDG)</option>
                        </select>
                        <small style="color:var(--text-secondary); display:block; margin-top:5px;">النظام مبرمج ليعتمد الجنيه السوداني كعملة أساسية ومحلية.</small>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">العملات الأجنبية النشطة في النظام</label>
                        <div class="checkbox-group" id="cfg-active-currencies" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 5px; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px;">
                            <label style="display:flex; align-items:center; gap:5px; opacity:0.6; cursor:not-allowed;"><input type="checkbox" value="SDG" checked disabled> الجنيه السوداني (SDG)</label>
                            <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" value="USD" class="active-currency-cb" checked> الدولار الأمريكي (USD)</label>
                            <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" value="SAR" class="active-currency-cb" checked> الريال السعودي (SAR)</label>
                            <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" value="AED" class="active-currency-cb" checked> الدرهم الإماراتي (AED)</label>
                        </div>
                    </div>
                </div>

                <!-- Exchange Rates Panel -->
                <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed rgba(255,255,255,0.2);">
                    <div class="setting-title" style="font-size: 1.1rem; color: #f59e0b; margin-bottom: 10px;">💱 أسعار الصرف اليومية (مقابل الجنيه السوداني)</div>
                    
                    <div style="margin-bottom: 15px;">
                        <label class="form-label" style="display:inline-block; margin-left: 10px;">مصدر تسعير العملات الأجنبية:</label>
                        <select class="form-select" id="cfg-exchange-mode" style="display:inline-block; width:auto; padding: 5px 15px;">
                            <option value="global">الاعتماد على السعر المركزي (لوحة المشتركين)</option>
                            <option value="manual">إدخال يدوي مخصص</option>
                        </select>
                    </div>

                    <div id="exchange-rates-inputs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px;">
                        <div class="form-group rate-group-usd" style="margin:0;">
                            <label class="form-label" style="color:#10b981;">دولار أمريكي (USD)</label>
                            <input type="number" class="form-input rate-input" id="cfg-rate-usd" placeholder="سعر الدولار..." step="0.01">
                        </div>
                        <div class="form-group rate-group-sar" style="margin:0;">
                            <label class="form-label" style="color:#38bdf8;">ريال سعودي (SAR)</label>
                            <input type="number" class="form-input rate-input" id="cfg-rate-sar" placeholder="سعر الريال..." step="0.01">
                        </div>
                        <div class="form-group rate-group-aed" style="margin:0;">
                            <label class="form-label" style="color:#a855f7;">درهم إماراتي (AED)</label>
                            <input type="number" class="form-input rate-input" id="cfg-rate-aed" placeholder="سعر الدرهم..." step="0.01">
                        </div>
                    </div>
                    <div id="exchange-rate-warning" style="display:none; color: #ef4444; font-size:0.9rem; margin-top:10px; font-weight:bold;">
                        ⚠️ ليس لديك الصلاحية لتعديل أسعار الصرف (للقراءة فقط).
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <!-- Tax and Decimals -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">نسبة ضريبة القيمة المضافة (VAT %)</label>
                        <input type="number" class="form-input" id="cfg-vat" value="0" min="0" max="100" step="0.5">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">عدد الأرقام العشرية في الأسعار</label>
                        <select class="form-select" id="cfg-decimal-places">
                            <option value="0">0 (بدون كسور)</option>
                            <option value="2" selected>2 (مثال: 0.00)</option>
                            <option value="3">3 (مثال: 0.000)</option>
                        </select>
                    </div>

                    <!-- Fiscal Year -->
                    <div class="form-group" style="margin: 0; grid-column: span 2;">
                        <label class="form-label">بداية السنة المالية</label>
                        <select class="form-select" id="cfg-fiscal-year">
                            <option value="1">1 يناير</option>
                            <option value="4">1 أبريل</option>
                            <option value="7">1 يوليو</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Inventory -->
        <div class="tab-content" id="tab-inventory">
            <div class="glass-panel" id="panel-inventory-rules">
                <div class="setting-title">📦 قواعد المخزون والعمليات</div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">السماح بالبيع بالسالب (تجاوز رصيد المخزون)</span>
                    <label class="switch"><input type="checkbox" id="cfg-allow-negative-stock"><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل دورة البيع الثنائية (فاتورة ثم إذن صرف)</span>
                    <label class="switch"><input type="checkbox" id="cfg-two-step-sale" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">إلزامية تحديد اسم العميل في كل فاتورة</span>
                    <label class="switch"><input type="checkbox" id="cfg-require-customer"><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">منع الموظفين من البيع بأقل من سعر التكلفة</span>
                    <label class="switch"><input type="checkbox" id="cfg-prevent-below-cost"><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل تنبيهات انخفاض المخزون (الحد الأدنى)</span>
                    <label class="switch"><input type="checkbox" id="cfg-low-stock-alerts" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">اعتماد سياسة الصرف: الوارد أولاً يصرف أولاً (FIFO)</span>
                    <label class="switch"><input type="checkbox" id="cfg-fifo-policy" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">منع بيع أو صرف المواد منتهية الصلاحية تماماً</span>
                    <label class="switch"><input type="checkbox" id="cfg-prevent-expired-sale" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل استخدام قارئ الباركود في شاشات المبيعات والمشتريات</span>
                    <label class="switch"><input type="checkbox" id="cfg-enable-barcode" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">الترحيل التلقائي للفواتير والحركات إلى الحسابات العامة</span>
                    <label class="switch"><input type="checkbox" id="cfg-auto-post-gl" checked><span class="slider"></span></label>
                </div>
            </div>
        </div>

        <!-- Tab: Printing -->
        <div class="tab-content" id="tab-printing">
            <div class="glass-panel" id="panel-printing">
                <div class="setting-title">🖨️ الطباعة والفواتير</div>
                
                <div class="sub-tabs-container">
                    <button class="sub-tab-btn active" onclick="switchPrintTab('print-sales', this)">المبيعات</button>
                    <button class="sub-tab-btn" onclick="switchPrintTab('print-purchases', this)">المشتريات</button>
                    <button class="sub-tab-btn" onclick="switchPrintTab('print-quotes', this)">عروض الأسعار</button>
                    <button class="sub-tab-btn" onclick="switchPrintTab('print-reports', this)">التقارير</button>
                </div>

                <!-- Sales Printing -->
                <div class="sub-tab-content active" id="print-sales">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">مقاس ورق الفاتورة</label>
                            <select class="form-select" id="cfg-print-sales-size">
                                <option value="A4">ورق عادي (A4)</option>
                                <option value="A5">ورق نصف عادي (A5)</option>
                                <option value="80mm">طابعة إيصالات حرارية (80mm)</option>
                                <option value="58mm">طابعة إيصالات حرارية (58mm)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">طباعة رمز الاستجابة السريعة (QR Code)</label>
                            <select class="form-select" id="cfg-print-sales-qr">
                                <option value="true">تفعيل (ZATCA / الفوترة الإلكترونية)</option>
                                <option value="false">تعطيل</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="setting-row" style="margin-top: 15px;">
                        <span class="form-label" style="margin:0;">طباعة شعار الشركة (Logo) أعلى الفاتورة</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-sales-logo" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة مربعات التوقيعات (توقيع البائع - توقيع المشتري)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-sales-signatures"><span class="slider"></span></label>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label">تذييل فاتورة المبيعات (نص أسفل الفاتورة)</label>
                        <textarea class="form-input" id="cfg-print-sales-footer" rows="2" placeholder="مثال: البضاعة المباعة لا ترد ولا تستبدل إلا خلال 3 أيام..."></textarea>
                    </div>
                </div>

                <!-- Purchases Printing -->
                <div class="sub-tab-content" id="print-purchases">
                    <div class="form-group">
                        <label class="form-label">مقاس ورق فاتورة المشتريات / أمر الشراء</label>
                        <select class="form-select" id="cfg-print-purchases-size">
                            <option value="A4">ورق عادي (A4)</option>
                            <option value="A5">ورق نصف عادي (A5)</option>
                            <option value="80mm">طابعة إيصالات حرارية (80mm)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة شعار الشركة (Logo) أعلى الفاتورة</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-purchases-logo" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة مربعات التوقيعات (أمين المستودع - المورد)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-purchases-signatures" checked><span class="slider"></span></label>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label">تذييل أمر الشراء / فاتورة المشتريات</label>
                        <textarea class="form-input" id="cfg-print-purchases-footer" rows="2" placeholder="ملاحظات مطبوعة للمورد..."></textarea>
                    </div>
                </div>

                <!-- Quotes Printing -->
                <div class="sub-tab-content" id="print-quotes">
                    <div class="form-group">
                        <label class="form-label">مقاس ورق عرض السعر</label>
                        <select class="form-select" id="cfg-print-quotes-size">
                            <option value="A4">ورق عادي (A4)</option>
                            <option value="A5">ورق نصف عادي (A5)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة شعار الشركة (Logo) أعلى العرض</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-quotes-logo" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة مربع التوقيع (توقيع وختم الشركة)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-quotes-signatures" checked><span class="slider"></span></label>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label">الشروط والأحكام (Terms & Conditions)</label>
                        <textarea class="form-input" id="cfg-print-quotes-terms" rows="3" placeholder="الشروط والأحكام لعروض الأسعار والفواتير المبدئية (مثال: مدة صلاحية العرض 30 يوماً)..."></textarea>
                    </div>
                </div>

                <!-- Reports Printing -->
                <div class="sub-tab-content" id="print-reports">
                    <div class="form-group">
                        <label class="form-label">مقاس ورق التقارير (كشوفات الحساب، المخزون، إلخ)</label>
                        <select class="form-select" id="cfg-print-reports-size">
                            <option value="A4">ورق عادي (A4)</option>
                            <option value="A5">ورق نصف عادي (A5)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة الترويسة الرسمية (اللوجو، اسم الشركة، الأرقام الضريبية)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-reports-header" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">إظهار وقت وتاريخ الطباعة واسم المستخدم</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-reports-timestamp" checked><span class="slider"></span></label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Email/SMTP -->
        <div class="tab-content" id="tab-email">
            <div class="glass-panel" id="panel-email">
                <div class="setting-title">📧 إعدادات البريد الإلكتروني (SMTP)</div>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">تستخدم لإرسال الفواتير وكشوفات الحساب للعملاء مباشرة عبر البريد الإلكتروني.</p>
                <div class="form-group"><label class="form-label">خادم البريد (SMTP Server)</label><input type="text" class="form-input" id="cfg-smtp-server" placeholder="مثال: smtp.gmail.com"></div>
                <div class="form-group"><label class="form-label">المنفذ (Port)</label><input type="number" class="form-input" id="cfg-smtp-port" placeholder="مثال: 587 أو 465"></div>
                <div class="form-group"><label class="form-label">بريد المرسل (Email Username)</label><input type="email" class="form-input" id="cfg-smtp-user" placeholder="billing@yourcompany.com"></div>
                <div class="form-group"><label class="form-label">كلمة المرور (App Password)</label><input type="password" class="form-input" id="cfg-smtp-pass" placeholder="كلمة المرور أو App Password"></div>
            </div>
        </div>

        <!-- Tab: Users -->
        <div class="tab-content" id="tab-users">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                
                <div class="glass-panel" id="panel-users" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">🛡️ إدارة المستخدمين</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">إدارة حسابات الموظفين، تحديد الأدوار (أدمن، محاسب، بائع)، وتخصيص الصلاحيات.</p>
                    <button class="action-btn btn-users" onclick="window.location.href='users-roles.php'" style="margin-top: 15px;">⚙️ فتح إدارة المستخدمين</button>
                </div>

                <div class="glass-panel" id="panel-whatsapp" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">💬 ربط الواتساب</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">إدارة جلسة الواتساب ومسح الـ QR لإرسال الفواتير والتنبيهات للعملاء مباشرة.</p>
                    <button class="action-btn" style="background: rgba(37,211,102,0.1); color: #25d366; border: 1px solid rgba(37,211,102,0.3); margin-top: 15px;" onclick="openWAModal()">📱 إعداد الواتساب</button>
                </div>

                <div class="glass-panel" id="panel-audit" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">⬛ الصندوق الأسود</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">سجل شامل لجميع حركات النظام لتتبع ومراقبة العمليات والتدقيق الأمني.</p>
                    <button class="action-btn" style="background: rgba(15,23,42,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.2); margin-top: 15px;" onclick="window.location.href='audit-log.php'">🔍 فتح الصندوق الأسود</button>
                </div>
                
            </div>
        </div>

        <!-- Tab: UI -->
        <div class="tab-content" id="tab-ui">
            <div class="glass-panel" id="panel-ui">
                <!-- Language Section -->
                <div class="setting-title" data-i18n="settings.langTitle">🌐 اللغة</div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" data-i18n="settings.selectLang">اختر لغة واجهة النظام</label>
                    <div style="display: flex; gap: 15px; margin-top: 8px;">
                        <button type="button" id="lang-btn-ar" onclick="switchLanguage('ar')" class="action-btn" style="flex:1; padding: 14px 20px; font-size: 1.05rem; font-weight: 800; border-radius: 14px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s; cursor: pointer;">
                            🇸🇦 العربية
                        </button>
                        <button type="button" id="lang-btn-en" onclick="switchLanguage('en')" class="action-btn" style="flex:1; padding: 14px 20px; font-size: 1.05rem; font-weight: 800; border-radius: 14px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s; cursor: pointer;">
                            🇬🇧 English
                        </button>
                    </div>
                </div>

                <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">

                <div class="setting-title" data-i18n="settings.uiTitle">🎨 واجهة المستخدم والمظهر</div>
                <div class="form-group">
                    <label class="form-label" data-i18n="settings.fontSize">حجم الخط الرئيسي للنظام (Font Size)</label>
                    <select class="form-select" id="cfg-font-size" onchange="previewFontSize(this.value)">
                        <option value="14" data-i18n="settings.fontVerySmall">صغير جداً (14px)</option>
                        <option value="16" selected data-i18n="settings.fontMedium">متوسط (16px - افتراضي)</option>
                        <option value="18" data-i18n="settings.fontLarge">كبير (18px)</option>
                        <option value="20" data-i18n="settings.fontVeryLarge">كبير جداً (20px)</option>
                    </select>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;" data-i18n="settings.autoDark">تفعيل الوضع التلقائي الغامق</span>
                    <label class="switch"><input type="checkbox" id="cfg-auto-dark" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row" style="margin-top: 15px;">
                    <span class="form-label" style="margin:0;" data-i18n="settings.showShapes">إظهار الأشكال في الترويسة العليا</span>
                    <label class="switch"><input type="checkbox" id="cfg-show-athkar" checked><span class="slider"></span></label>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label class="form-label" data-i18n="settings.autoLogout">مدة تسجيل الخروج التلقائي عند عدم النشاط</label>
                    <select class="form-select" id="cfg-auto-logout">
                        <option value="0" selected>بدون (معطل)</option>
                        <option value="30">30 ثانية</option>
                        <option value="60">1 دقيقة</option>
                        <option value="300">5 دقائق</option>
                        <option value="600">10 دقائق</option>
                        <option value="1800">30 دقيقة</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tab: System Backup -->
        <div class="tab-content" id="tab-system">
            <div class="glass-panel" id="panel-backup">
                <div class="setting-title">💾 النظام والنسخ الاحتياطي</div>
                <p style="color: #94a3b8; font-size: 0.9rem; margin: 0 0 20px; line-height: 1.6;">تنزيل نسخة احتياطية آمنة من جميع بيانات التخزين المحلي (JSON) لحمايتها من الفقدان.</p>
                <button class="action-btn btn-backup" onclick="backupData()">⬇️ تنزيل نسخة احتياطية للبيانات</button>
                <button class="action-btn btn-restore" onclick="document.getElementById('restore-file').click()">⬆️ استعادة من ملف احتياطي سابق</button>
                <input type="file" id="restore-file" accept=".json" style="display:none" onchange="restoreData(this)">
                
                <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
                <h4 style="color: #f87171; margin-bottom: 10px;">⚠️ منطقة الخطر</h4>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0;">القيام بتهيئة وإفراغ جميع جداول قواعد البيانات لمسح الحركات التجريبية والبدء من جديد.</p>
                <button class="action-btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3);" onclick="window.location.href='data-reset.php'">🗑️ تصفير بيانات النظام</button>
            </div>
        </div>

        <!-- Action Bar (Static Center) -->
        <div style="display: flex; justify-content: center; margin-top: 25px; margin-bottom: 35px; width: 100%;">
            <button id="save-settings-btn" class="save-btn-main" onclick="saveSettings()" style="display: none; border-radius: 25px; padding: 10px 30px; font-size: 1.05rem; font-weight: bold; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.1); cursor: pointer;">
                💾 حفظ التعديلات
            </button>
        </div>

    </div>

</div>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/app.js?v=20260719_update"></script>
<script defer src="js/whatsapp-integration.js"></script>
<script>
// Language switcher logic for settings page
function switchLanguage(lang) {
    setLang(lang);
    highlightLangBtn(lang);
    setTimeout(() => window.location.reload(), 300);
}
function highlightLangBtn(lang) {
    const arBtn = document.getElementById('lang-btn-ar');
    const enBtn = document.getElementById('lang-btn-en');
    if (!arBtn || !enBtn) return;
    arBtn.style.background = 'rgba(255,255,255,0.05)';
    arBtn.style.borderColor = 'rgba(255,255,255,0.1)';
    arBtn.style.color = '#94a3b8';
    enBtn.style.background = 'rgba(255,255,255,0.05)';
    enBtn.style.borderColor = 'rgba(255,255,255,0.1)';
    enBtn.style.color = '#94a3b8';
    const activeBtn = lang === 'ar' ? arBtn : enBtn;
    activeBtn.style.background = 'linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2))';
    activeBtn.style.borderColor = 'rgba(99,102,241,0.6)';
    activeBtn.style.color = '#fff';
    activeBtn.style.boxShadow = '0 0 20px rgba(99,102,241,0.3)';
}
document.addEventListener('DOMContentLoaded', function() {
    highlightLangBtn(getLang());
});
</script>

<script>
    // ─── Load Settings on Page Open ───────────────────────────
    document.addEventListener('DBReady', () => {
        if (!localStorage.getItem('authToken')) {
            window.location.href = 'login.php';
            return;
        }

        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const perms = window.resolveUserPermissions ? window.resolveUserPermissions(user) : {};
        
        const isAdmin = !user.role || user.username === 'admin' || user.role === 'admin' || user.role === 'Admin' || user.role === 'مدير النظام' || user.roleId === 'admin_fallback' || (user.securityLevel && user.securityLevel >= 5);
        
        const hasSettings = isAdmin || (perms.settings && perms.settings.view);
        const hasInvRules = isAdmin || (perms.inventory_rules && perms.inventory_rules.view);
        
        if (!hasSettings && !hasInvRules) {
            window.location.href = 'dashboard-pro.php';
            return;
        }
        
        if (!hasSettings) {
            const tabsToHide = ['company', 'finance', 'ui', 'users', 'system', 'printing', 'email', 'whatsapp'];
            tabsToHide.forEach(t => {
                const btn = document.querySelector(`button[onclick*="'${t}'"]`);
                if (btn) btn.style.display = 'none';
            });
            const pTitle = document.querySelector('.page-title');
            if (pTitle) pTitle.innerText = 'قواعد المخزون والعمليات';
        }
        if (!hasInvRules && !hasSettings) {
            const invBtn = document.querySelector('button[onclick*="\'inventory\'"]');
            if (invBtn) invBtn.style.display = 'none';
        }

        const canEditSettings = isAdmin || (perms.settings && perms.settings.edit);
        const canEditInvRules = isAdmin || (perms.inventory_rules && perms.inventory_rules.edit);
        const canEditRates = isAdmin || (perms.exchange_rates && perms.exchange_rates.edit);
        
        if (!canEditSettings && !canEditInvRules) {
            document.querySelector('.save-btn-main').style.display = 'none';
        }

        // Exchange Rate Logic setup
        const modeSelect = document.getElementById('cfg-exchange-mode');
        const rateInputs = document.querySelectorAll('.rate-input');
        const usdInput = document.getElementById('cfg-rate-usd');
        const sarInput = document.getElementById('cfg-rate-sar');
        const aedInput = document.getElementById('cfg-rate-aed');
        const rateWarning = document.getElementById('exchange-rate-warning');

        if (!canEditRates) {
            modeSelect.disabled = true;
            rateInputs.forEach(i => i.disabled = true);
            if (rateWarning) rateWarning.style.display = 'block';
        }

        modeSelect.addEventListener('change', function() {
            if (this.value === 'global') {
                rateInputs.forEach(i => { i.disabled = true; i.style.opacity = '0.7'; });
                // Try to show the current central rate for reference
                fetch('api_index.php/exchange-rate')
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.visible && data.rate) {
                            const usdRate = parseFloat(data.rate);
                            if (usdInput) usdInput.value = usdRate;
                            if (sarInput) sarInput.value = (usdRate / 3.75).toFixed(2);
                            if (aedInput) aedInput.value = (usdRate / 3.67).toFixed(2);
                        }
                    }).catch(() => {});
            } else {
                if (canEditRates) {
                    rateInputs.forEach(i => { i.disabled = false; i.style.opacity = '1'; });
                }
            }
        });

        if (usdInput) {
            usdInput.addEventListener('input', function() {
                if (modeSelect.value === 'manual' && this.value && !isNaN(this.value)) {
                    const usdRate = parseFloat(this.value);
                    if (usdRate > 0) {
                        sarInput.value = (usdRate / 3.75).toFixed(2);
                        aedInput.value = (usdRate / 3.67).toFixed(2);
                    }
                }
            });
        }

        // Dynamic visibility of exchange rates based on selected active currencies
        const activeCurrenciesCbs = document.querySelectorAll('.active-currency-cb');
        function updateExchangeRateVisibility() {
            activeCurrenciesCbs.forEach(cb => {
                const group = document.querySelector('.rate-group-' + cb.value.toLowerCase());
                if (group) {
                    group.style.display = cb.checked ? 'block' : 'none';
                }
            });
        }
        activeCurrenciesCbs.forEach(cb => cb.addEventListener('change', updateExchangeRateVisibility));
        window.updateExchangeRateVisibility = updateExchangeRateVisibility; // Expose for loadSettings
        updateExchangeRateVisibility();

        loadSettings();

        // Show save button only when changes are made
        const settingsContainer = document.querySelector('.settings-container');
        if (settingsContainer) {
            function showSaveAlert(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
                    const btn = document.getElementById('save-settings-btn');
                    if (btn && btn.style.display !== 'block') {
                        btn.style.display = 'block';
                        btn.style.animation = 'none';
                        setTimeout(() => {
                            btn.style.animation = 'fadeIn 0.4s ease-out';
                        }, 10);
                        setTimeout(() => {
                            btn.classList.add('pulse-alert');
                        }, 400);
                    }
                }
            }

            // Auto open requested tab via URL query (?tab=system) or hash (#system)
            const urlParams = new URLSearchParams(window.location.search);
            const reqTab = urlParams.get('tab') || window.location.hash.replace('#', '');
            if (reqTab && document.getElementById('tab-' + reqTab)) {
                switchTab(reqTab);
            }

            settingsContainer.addEventListener('input', showSaveAlert);
            settingsContainer.addEventListener('change', showSaveAlert);
        }
    });

    function switchTab(tabId, elem) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        let targetBtn = elem;
        if (!targetBtn && typeof event !== 'undefined' && event && event.currentTarget) {
            targetBtn = event.currentTarget;
        }
        if (!targetBtn) {
            targetBtn = document.querySelector(`.tab-btn[onclick*="'${tabId}'"]`);
        }
        
        if (targetBtn) targetBtn.classList.add('active');
        
        const targetTab = document.getElementById('tab-' + tabId);
        if (targetTab) {
            targetTab.classList.add('active');
        }
    }

    function switchPrintTab(tabId, btn) {
        document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sub-tab-content').forEach(content => content.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
    window.switchTab = switchTab;
    window.switchPrintTab = switchPrintTab;

    function loadSettings() {
        const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        
        if (cfg.logoBase64) {
            const img = document.getElementById('logo-preview');
            img.src = cfg.logoBase64;
            img.style.display = 'block';
        }
        
        document.getElementById('cfg-logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    window.pendingLogoBase64 = evt.target.result;
                    const img = document.getElementById('logo-preview');
                    img.src = window.pendingLogoBase64;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Company
        const safeSet = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
        if (cfg.companyName)    safeSet('cfg-company-name', cfg.companyName);
        if (cfg.regNumber)      safeSet('cfg-reg-number', cfg.regNumber);
        if (cfg.taxNumber)      safeSet('cfg-tax-number', cfg.taxNumber);
        if (cfg.phone)          safeSet('cfg-phone', cfg.phone);
        if (cfg.whatsapp)       safeSet('cfg-whatsapp', cfg.whatsapp);
        if (cfg.address)        safeSet('cfg-address', cfg.address);
        if (cfg.location)       safeSet('cfg-location', cfg.location);

        // Subscription Status Tracking
        const userObj = JSON.parse(localStorage.getItem('user') || '{}');
        const validUntilStr = userObj.valid_until || localStorage.getItem('tenant_valid_until');
        const subStatusEl = document.getElementById('subscription-status');
        if (subStatusEl) {
            if (validUntilStr) {
                const validDate = new Date(validUntilStr);
                const now = new Date();
                const diffTime = validDate - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    let color = 'rgba(16, 185, 129, 0.2)'; // Green
                    let textColor = '#10b981';
                    if (diffDays <= 30) { color = 'rgba(245, 158, 11, 0.2)'; textColor = '#f59e0b'; } // Orange
                    if (diffDays <= 7) { color = 'rgba(239, 68, 68, 0.2)'; textColor = '#ef4444'; } // Red
                    
                    subStatusEl.innerHTML = `⏳ تنتهي في: ${validDate.toLocaleDateString('ar-SA')} (متبقي ${diffDays} يوم)`;
                    subStatusEl.style.background = color;
                    subStatusEl.style.color = textColor;
                    subStatusEl.style.border = `1px solid ${textColor}`;
                } else {
                    subStatusEl.innerHTML = '⚠️ الاشتراك منتهي!';
                    subStatusEl.style.background = 'rgba(239, 68, 68, 0.2)';
                    subStatusEl.style.color = '#ef4444';
                    subStatusEl.style.border = '1px solid #ef4444';
                }
            } else {
                subStatusEl.innerHTML = '✨ فترة تجريبية (غير محدد)';
                subStatusEl.style.background = 'rgba(56, 189, 248, 0.1)';
                subStatusEl.style.color = '#38bdf8';
                subStatusEl.style.border = '1px solid rgba(56, 189, 248, 0.3)';
            }
        }

        // Financial
        if (cfg.currency)       document.getElementById('cfg-currency').value         = cfg.currency;
        if (cfg.activeCurrencies) {
            const checkboxes = document.querySelectorAll('#cfg-active-currencies input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.checked = cfg.activeCurrencies.includes(cb.value);
            });
            if (window.updateExchangeRateVisibility) {
                window.updateExchangeRateVisibility();
            }
        }
        if (cfg.vat !== undefined) document.getElementById('cfg-vat').value           = cfg.vat;
        if (cfg.decimalPlaces !== undefined) document.getElementById('cfg-decimal-places').value = cfg.decimalPlaces;
        if (cfg.fiscalYear) document.getElementById('cfg-fiscal-year').value = cfg.fiscalYear;

        // Exchange Rates
        if (cfg.exchangeMode) {
            const modeSelect = document.getElementById('cfg-exchange-mode');
            modeSelect.value = cfg.exchangeMode;
            modeSelect.dispatchEvent(new Event('change'));
        }
        if (cfg.rateUSD != null) document.getElementById('cfg-rate-usd').value = cfg.rateUSD;
        if (cfg.rateSAR != null) document.getElementById('cfg-rate-sar').value = cfg.rateSAR;
        if (cfg.rateAED != null) document.getElementById('cfg-rate-aed').value = cfg.rateAED;

        // Inventory Rules
        document.getElementById('cfg-allow-negative-stock').checked = cfg.allowNegativeStock || false;
        document.getElementById('cfg-two-step-sale').checked         = cfg.twoStepSale !== false; 
        document.getElementById('cfg-require-customer').checked      = cfg.requireCustomer || false;
        document.getElementById('cfg-prevent-below-cost').checked    = cfg.preventBelowCost || false;
        document.getElementById('cfg-low-stock-alerts').checked      = cfg.lowStockAlerts !== false;
        document.getElementById('cfg-fifo-policy').checked           = cfg.fifoPolicy !== false;
        document.getElementById('cfg-prevent-expired-sale').checked  = cfg.preventExpiredSale !== false;
        document.getElementById('cfg-enable-barcode').checked        = cfg.enableBarcode !== false;
        document.getElementById('cfg-auto-post-gl').checked          = cfg.autoPostGl !== false;

        // Printing
        const printCfg = cfg.print || {
            sales: { size: cfg.printSize || 'A4', qr: 'true', logo: true, signatures: false, footer: cfg.invoiceFooter || '' },
            purchases: { size: 'A4', logo: true, signatures: true, footer: '' },
            quotes: { size: 'A4', logo: true, signatures: true, terms: cfg.invoiceTerms || '' },
            reports: { size: 'A4', header: true, timestamp: true }
        };
        
        // Sales
        document.getElementById('cfg-print-sales-size').value = printCfg.sales.size;
        document.getElementById('cfg-print-sales-qr').value = printCfg.sales.qr;
        document.getElementById('cfg-print-sales-logo').checked = printCfg.sales.logo;
        document.getElementById('cfg-print-sales-signatures').checked = printCfg.sales.signatures;
        document.getElementById('cfg-print-sales-footer').value = printCfg.sales.footer;
        
        // Purchases
        document.getElementById('cfg-print-purchases-size').value = printCfg.purchases.size;
        document.getElementById('cfg-print-purchases-logo').checked = printCfg.purchases.logo;
        document.getElementById('cfg-print-purchases-signatures').checked = printCfg.purchases.signatures;
        document.getElementById('cfg-print-purchases-footer').value = printCfg.purchases.footer;
        
        // Quotes
        document.getElementById('cfg-print-quotes-size').value = printCfg.quotes.size;
        document.getElementById('cfg-print-quotes-logo').checked = printCfg.quotes.logo;
        document.getElementById('cfg-print-quotes-signatures').checked = printCfg.quotes.signatures;
        document.getElementById('cfg-print-quotes-terms').value = printCfg.quotes.terms;
        
        // Reports
        document.getElementById('cfg-print-reports-size').value = printCfg.reports.size;
        document.getElementById('cfg-print-reports-header').checked = printCfg.reports.header;
        document.getElementById('cfg-print-reports-timestamp').checked = printCfg.reports.timestamp;
        
        // SMTP
        if (cfg.smtpServer) document.getElementById('cfg-smtp-server').value = cfg.smtpServer;
        if (cfg.smtpPort) document.getElementById('cfg-smtp-port').value = cfg.smtpPort;
        if (cfg.smtpUser) document.getElementById('cfg-smtp-user').value = cfg.smtpUser;
        if (cfg.smtpPass) document.getElementById('cfg-smtp-pass').value = cfg.smtpPass;

        // UI
        if (cfg.fontSize)  document.getElementById('cfg-font-size').value = cfg.fontSize;
        document.getElementById('cfg-auto-dark').checked = cfg.autoDark !== false; 
        document.getElementById('cfg-show-athkar').checked = cfg.showAthkar !== false; 
        if (cfg.autoLogoutTime !== undefined) document.getElementById('cfg-auto-logout').value = cfg.autoLogoutTime;
    }

    // ─── Save Settings ─────────────────────────────────────────
    function saveSettings() {
        const activeCurrencies = Array.from(document.querySelectorAll('#cfg-active-currencies input[type="checkbox"]:checked')).map(cb => cb.value);
        const oldCfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');

        const cfg = {
            // Company
            logoBase64:        window.pendingLogoBase64 || oldCfg.logoBase64,
            companyName:       document.getElementById('cfg-company-name')?.value.trim() || '',
            regNumber:         document.getElementById('cfg-reg-number')?.value.trim() || oldCfg.regNumber || '',
            taxNumber:         document.getElementById('cfg-tax-number')?.value.trim() || '',
            phone:             document.getElementById('cfg-phone')?.value.trim() || '',
            whatsapp:          document.getElementById('cfg-whatsapp')?.value.trim() || '',
            address:           document.getElementById('cfg-address')?.value.trim() || '',
            location:          document.getElementById('cfg-location')?.value.trim() || '',

            // Financial
            currency:          document.getElementById('cfg-currency').value,
            activeCurrencies:  activeCurrencies,
            vat:               parseFloat(document.getElementById('cfg-vat').value) || 0,
            decimalPlaces:     parseInt(document.getElementById('cfg-decimal-places').value) || 2,
            fiscalYear:        document.getElementById('cfg-fiscal-year').value,

            // Exchange Rates
            exchangeMode:      document.getElementById('cfg-exchange-mode').value,
            rateUSD:           parseFloat(document.getElementById('cfg-rate-usd').value) || oldCfg.rateUSD || null,
            rateSAR:           parseFloat(document.getElementById('cfg-rate-sar').value) || oldCfg.rateSAR || null,
            rateAED:           parseFloat(document.getElementById('cfg-rate-aed').value) || oldCfg.rateAED || null,

            // Inventory
            allowNegativeStock: document.getElementById('cfg-allow-negative-stock').checked,
            twoStepSale:       document.getElementById('cfg-two-step-sale').checked,
            requireCustomer:   document.getElementById('cfg-require-customer').checked,
            preventBelowCost:  document.getElementById('cfg-prevent-below-cost').checked,
            lowStockAlerts:    document.getElementById('cfg-low-stock-alerts').checked,
            fifoPolicy:        document.getElementById('cfg-fifo-policy').checked,
            preventExpiredSale: document.getElementById('cfg-prevent-expired-sale').checked,
            enableBarcode:     document.getElementById('cfg-enable-barcode').checked,
            autoPostGl:        document.getElementById('cfg-auto-post-gl').checked,

            // Printing
            print: {
                sales: {
                    size: document.getElementById('cfg-print-sales-size').value,
                    qr: document.getElementById('cfg-print-sales-qr').value,
                    logo: document.getElementById('cfg-print-sales-logo').checked,
                    signatures: document.getElementById('cfg-print-sales-signatures').checked,
                    footer: document.getElementById('cfg-print-sales-footer').value.trim()
                },
                purchases: {
                    size: document.getElementById('cfg-print-purchases-size').value,
                    logo: document.getElementById('cfg-print-purchases-logo').checked,
                    signatures: document.getElementById('cfg-print-purchases-signatures').checked,
                    footer: document.getElementById('cfg-print-purchases-footer').value.trim()
                },
                quotes: {
                    size: document.getElementById('cfg-print-quotes-size').value,
                    logo: document.getElementById('cfg-print-quotes-logo').checked,
                    signatures: document.getElementById('cfg-print-quotes-signatures').checked,
                    terms: document.getElementById('cfg-print-quotes-terms').value.trim()
                },
                reports: {
                    size: document.getElementById('cfg-print-reports-size').value,
                    header: document.getElementById('cfg-print-reports-header').checked,
                    timestamp: document.getElementById('cfg-print-reports-timestamp').checked
                }
            },

            // SMTP
            smtpServer:        document.getElementById('cfg-smtp-server').value.trim(),
            smtpPort:          document.getElementById('cfg-smtp-port').value.trim(),
            smtpUser:          document.getElementById('cfg-smtp-user').value.trim(),
            smtpPass:          document.getElementById('cfg-smtp-pass').value.trim(),

            // UI
            fontSize:          document.getElementById('cfg-font-size').value,
            autoDark:          document.getElementById('cfg-auto-dark').checked,
            showAthkar:        document.getElementById('cfg-show-athkar').checked,
            autoLogoutTime:    document.getElementById('cfg-auto-logout').value,

            savedAt: new Date().toISOString()
        };

        localStorage.setItem('erp_settings_v1', JSON.stringify(cfg));
        if (window.saveToDB) {
            window.saveToDB('erp_settings_v1', cfg);
        }

        document.documentElement.style.fontSize = cfg.fontSize + 'px';

        const badge = document.getElementById('save-badge');
        if (badge) {
            badge.classList.add('show');
            setTimeout(() => badge.classList.remove('show'), 3000);
        }

        if (window.logSystemAction) {
            window.logSystemAction('Settings Update', 'settings', 'تم تحديث إعدادات النظام');
        }

        // Hide save button after successful save
        const saveBtn = document.getElementById('save-settings-btn');
        if (saveBtn) {
            saveBtn.style.display = 'none';
            saveBtn.classList.remove('pulse-alert');
        }
    }

    function previewFontSize(size) {
        document.documentElement.style.fontSize = size + 'px';
    }

    // ─── Backup Data ───────────────────────────────────────────
    async function backupData() {
        try {
            const completeStore = {};
            const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';

            // 1. Gather all keys from localStorage
            const globalKeys = ['saas_token', 'user', 'erp_lang', 'magaiees_global_theme', 'authToken'];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && (prefix === '' || k.startsWith(prefix) || globalKeys.includes(k))) {
                    const val = localStorage.getItem(k);
                    if (val !== null) completeStore[k] = val;
                }
            }

            // 2. Gather all keys from localforage (async)
            if (typeof localforage !== 'undefined') {
                try {
                    const lfKeys = await localforage.keys();
                    for (let k of lfKeys) {
                        if (prefix === '' || k.startsWith(prefix)) {
                            const val = await localforage.getItem(k);
                            if (val !== null) {
                                try { completeStore[k] = JSON.parse(val); } catch(e) { completeStore[k] = val; }
                            }
                        }
                    }
                } catch(e) { console.error("Error reading localforage:", e); }
            }

            // 3. Overlay DB_STORE memory proxy
            if (window.DB_STORE) {
                for (let [k, v] of Object.entries(window.DB_STORE)) {
                    if (prefix === '' || k.startsWith(prefix)) {
                        if (v !== null && v !== undefined) {
                            completeStore[k] = v;
                        }
                    }
                }
            }

            // Clean up empty data
            const cleanStore = {};
            let recordCount = 0;
            for (let [k, v] of Object.entries(completeStore)) {
                if (v && (Array.isArray(v) ? v.length > 0 : (typeof v === 'object' ? Object.keys(v).length > 0 : true))) {
                    cleanStore[k] = v;
                    if (Array.isArray(v)) recordCount += v.length;
                }
            }

            if (Object.keys(cleanStore).length === 0) {
                alert("⚠️ لا توجد بيانات مسجلة بالنظام للتصدير حالياً.");
                return;
            }

            if (!confirm(`سيتم تجميع كافة بيانات النظام الخاصة بالشركة الحالية وتنزيلها كملف احتياطي (يحتوي على ${recordCount} سجل). هل ترغب بالاستمرار؟`)) return;

            const dateStr = new Date().toISOString().split('T')[0];
            const fileName = `MAGAIEES_ERP_Backup_${dateStr}.bak.json`;
            const backupDataStr = JSON.stringify(cleanStore, null, 2);

            const blob = new Blob([backupDataStr], { type: "application/json;charset=utf-8" });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction('Database Export', 'system', 'تصدير نسخة احتياطية للشركة الحالية');
            }
            alert(`✅ تم تنزيل النسخة الاحتياطية بنجاح! (تم تصدير ${recordCount} سجل خاص بهذه الشركة)`);
        } catch(e) {
            console.error(e);
            alert("❌ حدث خطأ أثناء تنزيل النسخة الاحتياطية: " + e.message);
        }
    }

    function restoreData(elem) {
        const file = elem.files ? elem.files[0] : null;
        if (!file) return;

        if (!confirm("⚠️ تحذير خطير: استعادة النسخة الاحتياطية ستمسح كافة البيانات الحالية وتستبدلها ببيانات الملف المرفوع. هل أنت متأكد 100% أنك تريد الاستمرار؟")) {
            elem.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = async function(e) {
            let loader = null;
            try {
                const content = e.target.result;
                
                // Check if it's a SQL file or JSON file
                if (content.trim().startsWith('--') || content.toUpperCase().includes('INSERT INTO')) {
                    if (typeof restoreFromSqlDump === 'function') {
                        restoreFromSqlDump(content);
                        return;
                    }
                }

                const data = JSON.parse(content);
                if (typeof data !== 'object' || Array.isArray(data) || data === null) {
                    throw new Error("تنسيق الملف غير صالح");
                }

                // Loader screen
                loader = document.createElement('div');
                loader.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.95); backdrop-filter:blur(15px); z-index:999999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white; font-family:Tajawal; text-align:center; padding:20px;';
                loader.innerHTML = `
                    <div style="font-size:3.5rem; margin-bottom:15px; animation:spin 2s linear infinite;">🔄</div>
                    <h2 style="font-size:1.8rem; font-weight:900; margin:0 0 10px 0; color:#38bdf8;">جاري استعادة البيانات والتهيأة...</h2>
                    <p style="color:#94a3b8; font-size:1rem; margin:0;">يرجى عدم إغلاق المتصفح حتى إكتمال العملية.</p>
                `;
                document.body.appendChild(loader);

                // 1. Preserve critical session tokens
                const sessionKeys = ['authToken', 'saas_token', 'user', 'erp_lang', 'magaiees_global_theme'];
                const preservedSession = {};
                sessionKeys.forEach(k => {
                    const val = localStorage.getItem(k);
                    if (val !== null) preservedSession[k] = val;
                });

                // 2. Clear current localforage
                const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                try {
                    if (typeof localforage !== 'undefined') {
                        const keys = await localforage.keys();
                        for (let k of keys) {
                            if (k.startsWith(prefix) || prefix === '') {
                                await localforage.removeItem(k);
                            }
                        }
                    }
                } catch(err) {}

                // 3. Clear current localStorage erp_ & tenant_ keys
                const lsKeys = [];
                for (let i = 0; i < localStorage.length; i++) {
                    lsKeys.push(localStorage.key(i));
                }
                for (let k of lsKeys) {
                    if (k && !sessionKeys.includes(k) && (k.startsWith('erp_') || k.startsWith('tenant_') || k.startsWith(prefix))) {
                        localStorage.removeItem(k);
                    }
                }

                // 4. Populate imported backup with Smart Deduplicated Merging
                let restoredCount = 0;
                const currentPrefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                const mergedStore = {};

                for (let [key, val] of Object.entries(data)) {
                    let targetKey = key;
                    if (currentPrefix && key.startsWith('tenant_')) {
                        targetKey = key.replace(/^tenant_[^_]+_/, currentPrefix);
                    } else if (currentPrefix && key.startsWith('erp_')) {
                        targetKey = currentPrefix + key;
                    }

                    // Parse double-stringified JSON if present
                    if (typeof val === 'string' && (val.startsWith('[') || val.startsWith('{'))) {
                        try { val = JSON.parse(val); } catch(e) {}
                    }

                    // Smart Merge to prevent smaller/empty company arrays from overwriting main company data
                    if (mergedStore[targetKey]) {
                        let existing = mergedStore[targetKey];
                        if (Array.isArray(val) && Array.isArray(existing)) {
                            const map = new Map();
                            existing.forEach(item => {
                                const id = item && (item.id || item.code || item.invoiceNo) ? (item.id || item.code || item.invoiceNo) : JSON.stringify(item);
                                map.set(id, item);
                            });
                            val.forEach(item => {
                                const id = item && (item.id || item.code || item.invoiceNo) ? (item.id || item.code || item.invoiceNo) : JSON.stringify(item);
                                map.set(id, item);
                            });
                            mergedStore[targetKey] = Array.from(map.values());
                        } else if (typeof val === 'object' && val !== null) {
                            mergedStore[targetKey] = Object.assign({}, existing, val);
                        } else {
                            if (val) mergedStore[targetKey] = val;
                        }
                    } else {
                        mergedStore[targetKey] = val;
                    }
                }

                // Write mergedStore to localStorage, localforage, and DB_STORE
                for (let [targetKey, val] of Object.entries(mergedStore)) {
                    const valStr = typeof val === 'string' ? val : JSON.stringify(val);
                    try { localStorage.setItem(targetKey, valStr); } catch(err) {}
                    try {
                        if (typeof localforage !== 'undefined') {
                            await localforage.setItem(targetKey, val); // Store as native Object/Array
                        }
                    } catch(err) {}
                    if (window.DB_STORE) {
                        window.DB_STORE[targetKey] = val; // Store as native Object/Array
                    }
                    restoredCount++;
                }

                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('Database Import', 'system', 'استعادة نسخة احتياطية ومعالجة البيانات بنجاح');
                }

                alert(`🎉 تم استعادة كافة البيانات بنجاح (${restoredCount} جدول/سجل معالَج بنجاح)! سيتم تحديث الصفحة الآن.`);
                window.location.reload();
            } catch(err) {
                console.error(err);
                alert("❌ خطأ: ملف النسخة الاحتياطية غير صالح أو تالف (" + err.message + ")");
                elem.value = '';
                if (loader) loader.remove();
            }
        };
        reader.readAsText(file);
    }
</script>
</body>
</html>
