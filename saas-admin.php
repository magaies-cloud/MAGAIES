<?php $page_title = 'SaaS Super Admin - مقاييس (MAGAIEES)'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" href="manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#050505">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SaaS Super Admin - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260619_3">
    <link rel="stylesheet" href="css/theme.css?v=20260619_3">
    <link rel="stylesheet" href="css/components.css?v=20260619_3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-color: #050505;
            --surface-color: rgba(20, 20, 22, 0.8);
            --primary-accent: #f59e0b;
            --secondary-accent: #f97316;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #eab308;
            --text-primary: #fdfdfd;
            --text-secondary: #a1a1aa;
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        body { background-color: var(--bg-color); color: var(--text-primary); font-family: 'Inter', 'Tajawal', sans-serif; margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden; }
        .ambient-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; overflow: hidden; z-index: -1; pointer-events: none; background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%); }
        .orb { position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15; animation: floatOrb 20s infinite alternate ease-in-out; }
        .orb-1 { width: 500px; height: 500px; background: #f59e0b; top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: #a855f7; bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: #ea580c; top: 30%; left: 30%; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 50% { transform: translate(30px, -30px) scale(1.05); } 100% { transform: translate(0, 0) scale(1); } }

        .dash-main { width: 100%; max-width: 1600px; margin: 0 auto; padding: 0px 30px 20px 30px; box-sizing: border-box; flex-direction: column; }
        .premium-header {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(168, 85, 247, 0.15));
            backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 15px 25px;
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon { width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #ea580c); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #fff; }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #cbd5e1; }
        
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .glass-panel { background: var(--surface-color); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px; transition: all 0.3s; }
        .glass-panel:hover { border-color: rgba(255,255,255,0.15); box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        .kpi-card { padding: 10px 15px !important; border-radius: 12px !important; }
        .kpi-title { font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; display: flex; justify-content: space-between; margin-bottom: 5px; }
        .kpi-value { font-size: 1.4rem; font-weight: 700; }
        .kpi-1 { border-top: 3px solid #3b82f6; } .kpi-1 .kpi-value { color: #3b82f6; }
        .kpi-2 { border-top: 3px solid #10b981; } .kpi-2 .kpi-value { color: #10b981; }
        .kpi-3 { border-top: 3px solid #f59e0b; } .kpi-3 .kpi-value { color: #f59e0b; }
        .kpi-4 { border-top: 3px solid #a855f7; } .kpi-4 .kpi-value { color: #a855f7; }

        .tabs-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-secondary);
            padding: 12px 8px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            text-align: center;
            font-size: 0.85rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tab-btn.active { background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(234,88,12,0.2)); border-color: rgba(245,158,11,0.5); color: #fff; }
        .tab-content { display: none; animation: fadeUp 0.3s ease forwards; }
        .tab-content.active { display: block; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        .islamic-ticker-container { overflow: hidden; white-space: nowrap; width: 350px; margin-left: 20px; position: relative; display: flex; align-items: center; border-radius: 8px; background: rgba(0,0,0,0.2); padding: 5px 10px; border: 1px solid rgba(255,255,255,0.05); }
        .islamic-ticker {
            display: inline-block; font-weight: 700; font-size: 0.95rem; color: transparent;
            background: linear-gradient(90deg, #f59e0b, #10b981, #3b82f6, #a855f7, #f59e0b);
            background-size: 200% auto; -webkit-background-clip: text; background-clip: text;
            animation: gradientMove 3s linear infinite, scrollTicker 20s linear infinite;
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.2); white-space: nowrap; padding-left: 100%;
        }
        @keyframes scrollTicker { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        @keyframes gradientMove { 0% { background-position: 0% center; } 100% { background-position: 200% center; } }

        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 14px 16px; text-align: right; border-bottom: 1px solid var(--glass-border); }
        th { color: var(--text-secondary); font-size: 0.9rem; background: rgba(0,0,0,0.2); }
        .table-responsive { width: 100%; overflow-x: auto; border-radius: 12px; border: 1px solid var(--glass-border); }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-block; }
        .badge-active { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-suspended { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        .btn-primary { background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)); color: #fff; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.3); padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .btn-outline { background: transparent; border: 1px solid var(--text-secondary); color: var(--text-secondary); padding: 10px 20px; border-radius: 10px; cursor: pointer; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.3); color: white; box-sizing: border-box; }

        #login-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(15px); }
        .login-box { width: 90%; max-width: 400px; text-align: center; padding: 40px 35px; }
        .login-box input { width: 100%; padding: 14px; border-radius: 10px; background: rgba(255,255,255,0.07); color: #fff; border: 1px solid var(--glass-border); font-size: 1.1rem; box-sizing: border-box; margin-bottom: 15px; font-family: 'Tajawal',sans-serif; }
        .login-box input:focus { outline: none; border-color: var(--primary-accent); }
        
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-content { width: 90%; max-width: 500px; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        
        /* Dropdown */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; right: 0; background: rgba(30,41,59,0.95); min-width: 140px; box-shadow: 0 8px 30px rgba(0,0,0,0.6); border-radius: 12px; border: 1px solid var(--glass-border); z-index: 100; }
        .dropdown-content button { color: #fff; padding: 10px 15px; text-decoration: none; display: block; width: 100%; text-align: right; background: transparent; border: none; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .dropdown-content button:hover { background: rgba(255,255,255,0.1); }
        .dropdown.show .dropdown-content { display: block; }

        /* تخصيص Flatpickr ليتناسب مع التصميم الداكن الفخم */
        .flatpickr-calendar {
            background: rgba(20, 20, 22, 0.98) !important;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7) !important;
            font-family: 'Tajawal', 'Inter', sans-serif !important;
            border-radius: 16px !important;
            direction: rtl !important;
        }
        .flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after {
            border-bottom-color: rgba(20, 20, 22, 0.98) !important;
        }
        .flatpickr-calendar.arrowBottom:before, .flatpickr-calendar.arrowBottom:after {
            border-top-color: rgba(20, 20, 22, 0.98) !important;
        }
        .flatpickr-months .flatpickr-month {
            color: var(--text-primary) !important;
            fill: var(--text-primary) !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowUp:after {
            border-bottom-color: var(--text-primary) !important;
        }
        .flatpickr-current-month .numInputWrapper span.arrowDown:after {
            border-top-color: var(--text-primary) !important;
        }
        .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
            color: var(--text-primary) !important;
            fill: var(--text-primary) !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover, .flatpickr-months .flatpickr-next-month:hover {
            color: var(--primary-accent) !important;
        }
        span.flatpickr-weekday {
            color: var(--text-secondary) !important;
            font-weight: 600;
        }
        .flatpickr-day {
            color: var(--text-primary) !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }
        .flatpickr-day:hover, .flatpickr-day:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: transparent !important;
        }
        .flatpickr-day.today {
            border-color: var(--primary-accent) !important;
            color: var(--primary-accent) !important;
        }
        .flatpickr-day.today:hover {
            background: rgba(245, 158, 11, 0.15) !important;
            color: #fff !important;
        }
        .flatpickr-day.selected, .flatpickr-day.selected:hover, .flatpickr-day.selected:focus {
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent)) !important;
            border-color: transparent !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3) !important;
        }
        .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
            color: rgba(255, 255, 255, 0.15) !important;
        }
        .flatpickr-time {
            border-top: 1px solid var(--glass-border) !important;
            color: var(--text-primary) !important;
        }
        .flatpickr-time input {
            color: var(--text-primary) !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 1.1rem !important;
        }
        .flatpickr-time input:hover, .flatpickr-time input:focus {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        .flatpickr-time .flatpickr-am-pm {
            color: var(--text-primary) !important;
            font-family: 'Tajawal', sans-serif !important;
        }
        .flatpickr-time .numInputWrapper span.arrowUp:after {
            border-bottom-color: var(--text-secondary) !important;
        }
        .flatpickr-time .numInputWrapper span.arrowDown:after {
            border-top-color: var(--text-secondary) !important;
        }
        /* أيقونة التقويم داخل حقل الإدخال */
        .form-group .flatpickr-input {
            background-color: rgba(0, 0, 0, 0.3) !important;
            color: #fff !important;
            border: 1px solid var(--glass-border) !important;
            font-family: 'Tajawal', 'Inter', sans-serif !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23a1a1aa'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' /%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: left 12px center !important;
            background-size: 20px !important;
            padding-left: 40px !important;
        }
    </style>
</head>
<body>
    <div class="ambient-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>

    <div id="login-modal">
        <div class="glass-panel login-box">
            <div style="margin-bottom: 25px;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #ea580c); border-radius: 20px; margin: 0 auto 15px auto; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; box-shadow: 0 10px 25px rgba(245,158,11,0.4);">🛡️</div>
                <h2 style="color: #fff; font-weight: 900; margin: 0;">SaaS Super Admin</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 5px;">أدخل رمز المشرف للمتابعة</p>
            </div>
            <input type="password" id="saas-pass" placeholder="Master Token">
            <button class="btn-primary" onclick="loginSaas()" style="width: 100%; font-family:'Tajawal',sans-serif;">تسجيل الدخول</button>
        </div>
    </div>

    <div id="dashboard" class="dash-main" style="display: none;">
        <div class="premium-header">
            <div class="header-brand">
                <div class="brand-icon">👑</div>
                <div class="brand-text">
                    <h1>SaaS Super Admin</h1>
                    <p>إدارة الشركات والباقات والمستخدمين</p>
                </div>
            </div>
            <div class="header-controls" style="display:flex; align-items:center; gap:15px;">
                <div id="wa-status-widget" onclick="openAdminWhatsAppModal()" style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; color:#d1d5db; cursor:pointer;">
                    <span id="wa-status-indicator" style="width:10px; height:10px; border-radius:50%; background:#ef4444; display:inline-block; box-shadow:0 0 8px #ef4444;"></span>
                    <span id="wa-status-text" style="font-weight:bold;">خادم الواتساب: فحص...</span>
                </div>
                <div class="islamic-ticker-container">
                    <div class="islamic-ticker">
                        سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
                    </div>
                </div>
                <button onclick="logoutSaas()" class="btn-danger">تسجيل الخروج</button>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="glass-panel kpi-card kpi-1">
                <div class="kpi-title"><span>إجمالي المشتركين</span> 🏢</div>
                <div class="kpi-value" id="kpi-total-tenants">0</div>
            </div>
            <div class="glass-panel kpi-card kpi-2">
                <div class="kpi-title"><span>حسابات نشطة</span> 🟢</div>
                <div class="kpi-value" id="kpi-active-tenants">0</div>
            </div>
            <div class="glass-panel kpi-card kpi-3">
                <div class="kpi-title"><span>حسابات موقوفة</span> ⚠️</div>
                <div class="kpi-value" id="kpi-suspended-tenants">0</div>
            </div>
            <div class="glass-panel kpi-card kpi-4">
                <div class="kpi-title"><span>إجمالي الإيرادات (شهري)</span> 💰</div>
                <div class="kpi-value" id="kpi-revenue">$0</div>
            </div>
        </div>

        <div class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('tab-analytics', this)">📊 التحليلات والإحصائيات</button>
            <button class="tab-btn" onclick="switchTab('tab-tenants', this)">🏢 المشتركون</button>
            <button class="tab-btn" onclick="switchTab('tab-users', this)">👥 إدارة المستخدمين</button>
            <button class="tab-btn" onclick="switchTab('tab-plans', this)">💎 الباقات (Plans)</button>
            <button class="tab-btn" onclick="switchTab('tab-partners', this)">🤝 الشركاء والعملاء</button>
            <button class="tab-btn" onclick="switchTab('tab-broadcasts', this)">📣 رسائل النظام</button>
            <button class="tab-btn" onclick="switchTab('tab-ads', this)">🌟 الإعلانات المدفوعة</button>
            <button class="tab-btn" onclick="switchTab('tab-audit', this)">🛡️ سجل العمليات</button>
            <button class="tab-btn" onclick="switchTab('tab-backup-saas', this)">💾 نسخة SaaS الكاملة</button>
            <button class="tab-btn" onclick="switchTab('tab-backup-tenants', this)">👥 نسخة المشتركين المحددة</button>
            <button class="tab-btn" onclick="switchTab('tab-settings', this)">⚙️ الإعدادات</button>
        </div>

        <!-- TAB: ANALYTICS -->
        <div id="tab-analytics" class="tab-content active">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="glass-panel" style="padding: 20px; min-height: 350px;">
                    <h3 style="margin-top:0; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">📈 وتيرة تسجيل المشتركين الجدد</h3>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chart-registrations"></canvas>
                    </div>
                </div>
                <div class="glass-panel" style="padding: 20px; min-height: 350px;">
                    <h3 style="margin-top:0; margin-bottom: 20px; display:flex; align-items:center; gap:8px;">💾 أكبر 5 شركات استهلاكاً للمساحة التخزينية</h3>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chart-storage"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: AUDIT LOGS -->
        <div id="tab-audit" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>🛡️ سجل العمليات البرمجية والإدارية</h3>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="audit-search" placeholder="بحث في سجل العمليات..." style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 250px;" onkeyup="loadAuditLogs(true)">
                        <button class="btn-primary" onclick="loadAuditLogs(true)" style="padding: 10px 20px; font-weight: bold;">تحديث</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العملية الإدارية</th>
                                <th>تفاصيل العملية</th>
                                <th>عنوان الـ IP</th>
                                <th>التاريخ والوقت</th>
                            </tr>
                        </thead>
                        <tbody id="audit-logs-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="flex-between" style="margin-top:20px;">
                    <button class="btn-primary" id="btn-audit-prev" onclick="changeAuditPage(-1)" style="padding: 8px 16px;">السابق</button>
                    <span id="audit-page-indicator" style="color: var(--text-secondary); font-weight: bold;">الصفحة 1 من 1</span>
                    <button class="btn-primary" id="btn-audit-next" onclick="changeAuditPage(1)" style="padding: 8px 16px;">التالي</button>
                </div>
            </div>
        </div>

        <!-- TAB: DATABASE BACKUPS (SAAS FULL) -->
        <div id="tab-backup-saas" class="tab-content">
            <div class="glass-panel" style="text-align: center; padding: 40px 20px;">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(37,99,235,0.2)); border-radius: 50%; border: 2px dashed rgba(59,130,246,0.5); display: flex; justify-content: center; align-items: center; font-size: 3.5rem; margin: 0 auto 20px auto; color: #3b82f6;">💾</div>
                <h2 style="color: #fff; font-weight: 800; margin-bottom: 10px;">النسخ الاحتياطي الكامل لـ SaaS</h2>
                <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 30px auto; line-height: 1.6;">
                    تتيح لك هذه الميزة تنزيل نسخة احتياطية كاملة ومباشرة من قاعدة بيانات الـ SaaS الإجمالية (بما في ذلك جداول النظام، بيانات المشتركين، السجلات المالية، الإعلانات، وبنية الجداول بالكامل) بصيغة ملف SQL القياسي للاحتفاظ بها كنسخة أمان خارجية.
                </p>
                <button class="btn-primary" onclick="downloadDatabaseBackup()" style="padding: 15px 40px; font-size: 1.1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; box-shadow: 0 4px 15px rgba(37,99,235,0.4);">
                    <span>تنزيل نسخة احتياطية كاملة (.SQL)</span>
                </button>
            </div>
        </div>

        <!-- TAB: TENANTS DATABASE BACKUPS (CUSTOM FILTERED) -->
        <div id="tab-backup-tenants" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h3 style="margin: 0 0 5px 0;">النسخ الاحتياطي المخصص للمشتركين</h3>
                        <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem; line-height: 1.5;">
                            حدد المشتركين من الجدول لتصدير وتنزيل نسخة احتياطية مخصصة ومفلترة لبياناتهم في ملف SQL واحد مدمج.
                        </p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="backup-tenant-search" placeholder="بحث باسم المشترك، المالك، الهاتف..." onkeyup="renderBackupTenantsTable()" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 280px; font-family: Tajawal;">
                        <button class="btn-primary" onclick="downloadSelectedTenantsBackup()" style="padding: 10px 24px; font-weight: 700; background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); display: inline-flex; align-items: center; gap: 8px;">
                            <span>تصدير المشتركين المحددين (.SQL)</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive" style="margin-top: 15px; border-radius: 12px; border: 1px solid var(--glass-border); overflow: hidden; background: rgba(0,0,0,0.15);">
                    <table class="table" style="width: 100%; border-collapse: collapse; text-align: right;">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--glass-border);">
                                <th style="width: 50px; text-align: center; padding: 15px;"><input type="checkbox" id="backup-select-all-header" onchange="selectAllBackupTenants(this.checked)" style="transform: scale(1.25); cursor: pointer;"></th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">اسم المشترك والشركة</th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">المالك ورقم الهاتف</th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">المساحة المستهلكة</th>
                                <th style="font-size: 0.95rem; font-weight: bold; color: var(--text-secondary); padding: 15px;">الباقة الحالية</th>
                            </tr>
                        </thead>
                        <tbody id="backup-tenants-table-body">
                            <!-- Populated dynamically via renderBackupTenantsTable() -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: TENANTS -->
        <div id="tab-tenants" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>سجل المشتركين</h3>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="tenant-search" placeholder="بحث ذكي (الشركة، النطاق، المالك، المستخدم، الباقة...)" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 350px;" onkeyup="filterTenants()">
                        <select id="tenant-filter-status" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border);" onchange="filterTenants()">
                            <option value="all">الكل</option>
                            <option value="active">نشط</option>
                            <option value="pending">قيد المراجعة</option>
                            <option value="suspended">موقوف</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>الشركة / النطاق</th>
                                <th>اسم المستخدم</th>
                                <th>المالك</th>
                                <th>الباقة</th>
                                <th>المساحة المستهلكة</th>
                                <th>الحالة</th>
                                <th>تاريخ الانتهاء</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="tenants-body">
                            <tr><td colspan="8" style="text-align:center;">جاري التحميل...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: USERS -->
        <div id="tab-users" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>مستخدمو المشتركين</h3>
                    <button class="btn-primary" onclick="openAddUserModal()">+ مستخدم جديد</button>
                </div>
                <div class="form-group" style="max-width: 400px;">
                    <label>الشركة</label>
                    <select id="users-tenant-select" onchange="loadTenantUsers()">
                        <option value="">اختر شركة</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>المستخدم</th>
                                <th>الاسم الكامل</th>
                                <th>الدور</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="tenant-users-body">
                            <tr><td colspan="4" style="text-align:center;">اختر شركة לעرض مستخدميها</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: PLANS -->
        <div id="tab-plans" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>الباقات (Plans)</h3>
                    <button class="btn-primary" onclick="openPlanModal()">+ باقة جديدة</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>السعر ($)</th>
                                <th>المدة</th>
                                <th>الحد الأقصى للمستخدمين</th>
                                <th>المساحة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="plans-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <!-- TAB: PARTNERS -->
        <div id="tab-partners" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>الشركاء والعملاء</h3>
                    <button class="btn-primary" onclick="openPartnerModal()">+ شريك جديد</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>اسم الشريك</th>
                                <th>الشعار</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="partners-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: BROADCASTS -->
        <!-- TAB: BROADCASTS -->
        <div id="tab-broadcasts" class="tab-content">
            <!-- Panel 1: WhatsApp Broadcasting -->
            <div class="glass-panel" style="margin-bottom: 25px;">
                <h3 style="margin-top:0; margin-bottom: 15px; display:flex; align-items:center; gap:8px;">💬 إرسال رسائل واتساب للمشتركين (WhatsApp Broadcast)</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 20px;">
                    يمكنك إرسال رسالة واتساب فورية إما لمشترك محدد منفرد أو لجميع المشتركين معاً (رسالة جماعية) للتنبيه بانتهاء الاشتراك أو إرسال تحديثات عامة.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label style="color:#fff; font-weight:bold;">المستلم (الشركة/المشترك)</label>
                        <select id="wa-target-tenant" onchange="applyWaTemplate(document.getElementById('wa-message-template').value)" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 100%; font-family: Tajawal;">
                            <option value="all">📢 إرسال لجميع المشتركين (رسالة جماعية)</option>
                            <!-- Will be populated dynamically with tenants -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="color:#fff; font-weight:bold;">قالب الرسائل الجاهزة (اختياري)</label>
                        <select id="wa-message-template" onchange="applyWaTemplate(this.value)" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 100%; font-family: Tajawal;">
                            <option value="">-- اختر قالباً جاهزاً لتعبئة الرسالة --</option>
                            <option value="expiry">⚠️ تنبيه بقرب انتهاء الاشتراك</option>
                            <option value="welcome">🌸 ترحيب بمشترك جديد</option>
                            <option value="maintenance">⚙️ تنبيه صيانة مجدولة للنظام</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="color:#fff; font-weight:bold;">نص الرسالة</label>
                    <textarea id="wa-message-body" rows="4" placeholder="اكتب نص الرسالة هنا... يمكنك استخدام المتغيرات {owner} للاسم و {company} للشركة في الرسائل الجماعية" style="padding: 12px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 100%; font-family: Tajawal; resize: vertical;"></textarea>
                </div>
                
                <button class="btn-primary" onclick="sendWhatsAppMessage()" style="padding: 12px 35px; font-weight: 700; background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); display: inline-flex; align-items: center; gap: 8px;">
                    <span>🚀 إرسال الرسالة عبر واتساب</span>
                </button>
            </div>

            <!-- Panel 2: Dashboard System Messages -->
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>رسائل النظام في لوحة تحكم المشتركين</h3>
                    <button class="btn-primary" onclick="openMessageModal()">+ رسالة لوحة تحكم جديدة</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>الجمهور</th>
                                <th>اللون</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="messages-body">
                            <tr><td colspan="4" style="text-align:center;">جاري التحميل...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <!-- TAB: ADS -->
        <div id="tab-ads" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>إدارة الإعلانات (الصفحة الرئيسية)</h3>
                    <button class="btn-primary" onclick="openAdModal()">+ إضافة إعلان جديد</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>صورة الإعلان</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="ads-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: SETTINGS -->
        <div id="tab-settings" class="tab-content">
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <div class="glass-panel" style="flex:1; min-width:300px;">
                    <h3 style="color:var(--warning-color); margin-top:0; display:flex; align-items:center; gap:8px;">💱 إعدادات الصرف</h3>
                    <div class="form-group">
                        <label>💵 سعر الصرف (الدولار مقابل العملة المحلية)</label>
                        <input type="number" id="global-exchange-rate" step="0.01">
                    </div>
                    <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" id="global-exchange-visible">
                        <label style="margin:0;">👁️ إظهار السعر للمشتركين</label>
                    </div>
                    <button class="btn-primary" onclick="saveGlobalSettings()" style="width:100%; margin-top: 15px;">💾 حفظ إعدادات الصرف</button>
                </div>
                
                <div class="glass-panel" style="flex:1; min-width:300px;">
                    <h3 style="color:#3b82f6; margin-top:0; display:flex; align-items:center; gap:8px;">⏱️ إعدادات التفعيل</h3>
                    <div class="form-group">
                        <label>زمن التفعيل المتوقع (بالساعات)</label>
                        <input type="number" id="global-activation-delay" value="12">
                        <div style="font-size: 0.85em; color: #a1a1aa; margin-top: 8px; line-height: 1.5;">
                            <strong>💡 ماذا يعني هذا الرقم؟</strong><br>
                            عندما يسجل مستخدم جديد شركته، تكون حالته "قيد المراجعة". هذا الرقم سيظهر للمستخدم في رسالة الانتظار كالتالي: <br>
                            <span style="color:#fbbf24;">"طلبك قيد المراجعة. سيتم تفعيل حسابك خلال [الرقم الذي تدخله هنا] ساعة كحد أقصى."</span>
                        </div>
                    </div>
                    <button class="btn-primary" onclick="saveGlobalSettings()" style="width:100%; margin-top: 15px;">💾 حفظ زمن التفعيل</button>
                </div>

                <div class="glass-panel" style="flex:1; min-width:300px;">
                    <h3 style="color:#10b981; margin-top:0; display:flex; align-items:center; gap:8px;">🔒 الخروج التلقائي للأمان</h3>
                    <div class="form-group">
                        <label>مدة عدم النشاط قبل الخروج</label>
                        <div style="display:flex; gap:10px;">
                            <input type="number" id="global-auto-logout-value" value="30" style="flex:2;">
                            <select id="global-auto-logout-unit" style="flex:1;">
                                <option value="minutes">دقائق</option>
                                <option value="seconds">ثواني</option>
                            </select>
                        </div>
                        <div style="font-size: 0.85em; color: #a1a1aa; margin-top: 8px;">
                            سيتم تسجيل الخروج تلقائياً من لوحة التحكم هذه إذا لم تقم بأي نشاط لمدة التحديد (اكتب 0 لإلغاء الميزة).
                        </div>
                    </div>
                    <button class="btn-primary" onclick="saveGlobalSettings()" style="width:100%; margin-top: 15px;">💾 حفظ إعدادات الأمان</button>
                </div>
                <div class="glass-panel" style="flex:1; min-width:300px;">
                    <h3 style="color:var(--danger-color); margin-top:0; display:flex; align-items:center; gap:8px;">🔑 رمز المشرف العام</h3>
                    <div style="font-size: 0.85em; color: #a1a1aa; margin-bottom: 15px;">هذا الرمز يُستخدم للدخول إلى لوحة التحكم الحالية (إدارة الشركات SaaS).</div>
                    <div class="form-group">
                        <label>الرمز الحالي</label>
                        <div style="position: relative;">
                            <input type="password" id="old-master-token" style="padding-left: 40px; width: 100%; box-sizing: border-box;">
                            <span onclick="togglePasswordVisibility('old-master-token', this)" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a1a1aa; font-size: 1.2em; user-select: none;">👁️</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>الرمز الجديد</label>
                        <div style="position: relative;">
                            <input type="password" id="new-master-token" style="padding-left: 40px; width: 100%; box-sizing: border-box;">
                            <span onclick="togglePasswordVisibility('new-master-token', this)" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a1a1aa; font-size: 1.2em; user-select: none;">👁️</span>
                        </div>
                    </div>
                    <button class="btn-danger" onclick="saveMasterToken()" style="width:100%;">🔄 تغيير الرمز</button>
                </div>
                <div class="glass-panel" style="flex:1; min-width:300px;">
                    <h3 style="color:#a855f7; margin-top:0; display:flex; align-items:center; gap:8px;">🕵️‍♂️ حساب الدعم الفني (Backdoor)</h3>
                    <div style="font-size: 0.85em; color: #a1a1aa; margin-bottom: 15px;">حساب خفي (Super Admin) يتيح لك الدخول لأي شركة مسجلة في النظام بصلاحيات مدير لحل مشاكلهم، دون أن يظهر اسمك في قائمة مستخدمي الشركة.</div>
                    <div class="form-group"><label>اسم المستخدم السري</label><input type="text" id="backdoor-username" placeholder="مثال: MasterSupport"></div>
                    <div class="form-group">
                        <label>كلمة المرور السرية</label>
                        <div style="position: relative;">
                            <input type="password" id="backdoor-password" placeholder="أدخل كلمة مرور معقدة" style="padding-left: 40px; width: 100%; box-sizing: border-box;">
                            <span onclick="togglePasswordVisibility('backdoor-password', this)" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #a1a1aa; font-size: 1.2em; user-select: none;">👁️</span>
                        </div>
                    </div>
                    <button class="btn-primary" onclick="saveBackdoorCredentials()" style="width:100%; background:#a855f7; border-color:#a855f7;">✨ حفظ وتفعيل</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Plan Modal -->
    <div id="plan-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إدارة باقة</h3><button class="close-btn" onclick="closeModal('plan-modal')">&times;</button></div>
            <input type="hidden" id="plan-id">
            <div class="form-group"><label>اسم الباقة</label><input type="text" id="plan-name"></div>
            <div class="form-group"><label>السعر الشهري ($)</label><input type="number" id="plan-price"></div>
            <div class="form-group"><label>مدة الباقة (بالأيام)</label><input type="number" id="plan-duration" value="30" placeholder="مثال: 30"></div>
            <div class="form-group"><label>الحد الأقصى للمستخدمين</label><input type="number" id="plan-users"></div>
            <div class="form-group"><label>المساحة (GB)</label><input type="number" id="plan-storage"></div>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="plan-is-trial" style="width: auto;">
                <label for="plan-is-trial" style="margin: 0; color: var(--primary-accent);">تحديد كباقة تجريبية مجانية</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" style="flex:1;" onclick="savePlan()">حفظ</button>
            </div>
        </div>
    </div>

    
    <!-- Partner Modal -->
    <div id="partner-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إدارة شريك</h3><button class="close-btn" onclick="closeModal('partner-modal')">&times;</button></div>
            <input type="hidden" id="partner-id">
            <input type="hidden" id="partner-existing-logo">
            <div class="form-group"><label>الاسم</label><input type="text" id="partner-name"></div>
            <div class="form-group">
                <label>صورة الشعار (اختيار ملف)</label>
                <input type="file" id="partner-logo-upload" accept="image/*">
                <div id="partner-logo-preview" style="display:none; margin-top:10px; text-align:center;">
                    <img src="" style="max-height:60px; border-radius:8px;">
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="partner-active" checked>
                <label for="partner-active" style="margin:0; font-size:0.9rem; color:var(--text-secondary);">تفعيل العرض في الشاشة الرئيسية</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" id="save-partner-btn" style="flex:1;" onclick="savePartner()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- User Modal (Add User / Reset Pass) -->
    <div id="user-action-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3 id="user-modal-title">تغيير كلمة المرور للمستخدم</h3><button class="close-btn" onclick="closeModal('user-action-modal')">&times;</button></div>
            <input type="hidden" id="target-user-id">
            <div class="form-group"><label>كلمة المرور الجديدة</label><input type="password" id="target-user-pass"></div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveUserPassword()">تغيير كلمة المرور</button></div>
        </div>
    </div>
    
    <div id="add-user-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إضافة مستخدم جديد لشركة</h3><button class="close-btn" onclick="closeModal('add-user-modal')">&times;</button></div>
            <div class="form-group"><label>اسم المستخدم (للدخول)</label><input type="text" id="new-user-username"></div>
            <div class="form-group"><label>الاسم الكامل</label><input type="text" id="new-user-fullname"></div>
            <div class="form-group"><label>كلمة المرور</label><input type="password" id="new-user-pass"></div>
            <div class="form-group"><label>الدور</label>
                <select id="new-user-role"><option value="user">مستخدم (User)</option><option value="admin">مدير (Admin)</option></select>
            </div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveNewUser()">حفظ المستخدم</button></div>
        </div>
    </div>

    <!-- Edit Tenant Modal -->
    <div id="edit-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>تعديل حالة المشترك</h3><button class="close-btn" onclick="closeModal('edit-modal')">&times;</button></div>
            <input type="hidden" id="edit-tenant-id">
            <div class="form-group"><label>الباقة</label><select id="edit-tenant-plan" onchange="updateMaxUsersFromPlan()"></select></div>
            <div class="form-group"><label>الحد الأقصى للمستخدمين (مخصص)</label><input type="number" id="edit-tenant-max-users" placeholder="يتبع الباقة افتراضياً..."></div>
            <div class="form-group"><label>الحد الأقصى للمساحة (جيجابايت) (مخصص)</label><input type="number" id="edit-tenant-max-storage" placeholder="يتبع الباقة افتراضياً..."></div>
            <div class="form-group">
                <label>حالة الحساب</label>
                <select id="edit-status"><option value="active">نشط</option><option value="suspended">موقوف</option></select>
            </div>
            <div class="form-group"><label>تاريخ النهاية</label><input type="text" id="edit-valid-until" placeholder="اختر التاريخ والوقت..."></div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveTenantUpdate()">حفظ</button></div>
        </div>
    </div>
    
    <!-- Msg Modal -->
    <div id="message-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إنشاء رسالة</h3><button class="close-btn" onclick="closeModal('message-modal')">&times;</button></div>
            <input type="hidden" id="msg-id" value="0">
            <div class="form-group"><label>العنوان</label><input type="text" id="msg-title"></div>
            <div class="form-group"><label>المحتوى</label><textarea id="msg-body" rows="3"></textarea></div>
            <div class="form-group"><label>اللون</label><select id="msg-theme"><option value="info">أزرق</option><option value="success">أخضر</option><option value="warning">برتقالي</option><option value="danger">أحمر</option></select></div>
            
            <div class="form-group">
                <label>الجمهور المستهدف</label>
                <select id="msg-target-type" onchange="toggleMsgTargetTenantsList()" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.3); color:#fff; border: 1px solid var(--glass-border); width: 100%; font-family: Tajawal;">
                    <option value="all">الجميع (كافة المشتركين)</option>
                    <option value="custom">مشتركين محددين</option>
                </select>
            </div>
            
            <div class="form-group" id="msg-target-tenants-group" style="display: none; margin-top: 10px;">
                <label>حدد المشتركين المستهدفين:</label>
                <div id="msg-target-tenants-list" style="max-height: 120px; overflow-y: auto; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 8px; padding: 10px; text-align: right; margin-top: 5px;">
                    <!-- Will be populated dynamically -->
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px;"><input type="checkbox" id="msg-show-once" checked> <label style="display:inline; margin-right: 5px;">عرض مرة واحدة</label></div>
            <div class="modal-actions"><button class="btn-primary" style="flex:1;" onclick="saveMessage()">حفظ</button></div>
        </div>
    </div>

    
    <!-- Ads Modal -->
    <div id="ad-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إضافة إعلان</h3><button class="close-btn" onclick="closeModal('ad-modal')">&times;</button></div>
            <div class="form-group">
                <label>صورة الإعلان</label>
                <input type="file" id="ad-image-upload" accept="image/*" style="background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; width: 100%; color: #fff;">
                <div id="ad-image-preview" style="margin-top:10px; max-height:100px; overflow:hidden; display:none; border-radius:8px;">
                    <img src="" style="width:100%; object-fit:cover;">
                </div>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                <input type="checkbox" id="ad-active" checked> <label style="margin:0;">تفعيل الإعلان وعرضه للزوار</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" style="flex:1;" onclick="saveAd()" id="ad-save-btn">حفظ الإعلان</button>
            </div>
            <input type="hidden" id="ad-id">
            <input type="hidden" id="ad-existing-image">
            <div class="form-group">
                <label>اسم الشركة المعلنة (التفاصيل)</label>
                <input type="text" id="ad-company" placeholder="مثال: مصنع الأسعد للحديد">
            </div>
        </div>
    </div>
    
    <!-- Admin WhatsApp Modal -->
    <div id="admin-wa-modal" class="modal-overlay">
        <div class="glass-panel modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header">
                <h3 style="color:#25d366; margin: 0;">📱 ربط واتساب الإدارة العامة</h3>
                <button class="close-btn" onclick="closeModal('admin-wa-modal')">&times;</button>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <p id="admin-wa-status-text" style="font-weight: bold; color: #fff; margin-bottom: 10px;">جاري فحص الاتصال...</p>
                <div id="admin-wa-qr-container" style="background: #fff; padding: 15px; border-radius: 12px; display: inline-block; min-width: 200px; min-height: 200px; margin-top: 10px;">
                    <img id="admin-wa-qr-image" src="" alt="QR Code" style="width: 100%; display: none;" />
                    <div id="admin-wa-loading" style="color: #000; margin-top: 80px;">⏳ جاري التوليد...</div>
                </div>
                
                <!-- Success Animation Container (Hidden by default) -->
                <div id="admin-wa-success-animation" style="display: none; padding: 20px; flex-direction: column; align-items: center; justify-content: center; margin-top: 10px;">
                    <div style="position: relative; width: 80px; height: 80px; margin: 0 auto; animation: pulse 2s infinite;">
                        <svg viewBox="0 0 24 24" fill="#25d366" style="width: 80px; height: 80px; drop-shadow: 0 4px 10px rgba(37, 211, 102, 0.4);">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                        </svg>
                        <div style="position: absolute; top: -5px; right: -5px; background: #fff; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            <span style="color: #25d366; font-size: 20px; font-weight: bold;">✓</span>
                        </div>
                    </div>
                </div>

                <p id="admin-wa-instruction-text" style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 15px; line-height: 1.5;">
                    امسح هذا الرمز باستخدام تطبيق واتساب في هاتفك لربط حساب الإدارة العامة بالنظام لإرسال الـ OTP والتنبيهات.
                </p>
            </div>
            <div class="modal-actions" style="margin-top: 20px; justify-content: center; gap: 10px; flex-wrap: wrap;">
                <button class="btn-danger" id="admin-wa-logout-btn" style="display: none; padding: 10px 20px; background: #ef4444; border: none; font-weight: bold; color: #ffffff; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px;" onclick="logoutAdminWhatsApp()">إلغاء الربط</button>
                <button class="btn-outline" id="admin-wa-refresh-btn" style="padding: 10px 20px; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px;" onclick="checkAdminWhatsAppConnection()">تحديث الحالة</button>
                <button class="btn-primary" id="admin-wa-close-btn" style="display: none; padding: 10px 20px; background: #25d366; border: none; font-weight: bold; color: #ffffff; border-radius: 8px; cursor: pointer; flex: 1; min-width: 120px;" onclick="closeModal('admin-wa-modal')">إغلاق النافذة</button>
            </div>
        </div>
    </div>
    
    <script>
        // Register PWA service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
                .then(function(reg) { console.log('Service Worker registered:', reg.scope); })
                .catch(function(err) { console.error('Service Worker registration failed:', err); });
        }

        let SAAS_TOKEN = localStorage.getItem('saas_token');
        if (SAAS_TOKEN === 'null' || SAAS_TOKEN === 'undefined' || !SAAS_TOKEN) {
            SAAS_TOKEN = null;
        }

        // Global Fetch Interceptor to handle expired or invalid tokens (401 Unauthorized)
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            try {
                const res = await originalFetch(...args);
                if (res.status === 401 && SAAS_TOKEN) {
                    localStorage.removeItem('saas_token');
                    alert('انتهت صلاحية الجلسة أو الرمز غير صحيح. يرجى تسجيل الدخول مرة أخرى.');
                    location.reload();
                }
                return res;
            } catch (err) {
                throw err;
            }
        };

        let allTenants = [];
        let allMessagesData = [];
        let allPlans = [];
        let allPartners = [];
        let editValidUntilPicker;
        
        document.addEventListener("DOMContentLoaded", function() {
            editValidUntilPicker = flatpickr("#edit-valid-until", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                locale: "ar",
                time_24hr: false,
                altInput: true,
                altFormat: "Y/m/d h:i K",
                disableMobile: "true"
            });
        });
        
        // Must be declared before initDashboard() is called (Nginx: PATH_INFO not supported)
        let useQueryParams = true;
        
        

        if (SAAS_TOKEN) {
            document.getElementById('login-modal').style.display = 'none';
            document.getElementById('dashboard').style.display = 'flex';
            initDashboard();
        } else {
            document.getElementById('login-modal').style.display = 'flex';
        }

        async function loginSaas() {
            const pass = document.getElementById('saas-pass').value.trim();
            if (!pass) return alert('الرجاء إدخال الرمز');
            
            const btn = document.querySelector('.login-box button');
            const oldText = btn.innerText;
            btn.innerText = 'جاري التحقق...';
            btn.disabled = true;

            try {
                const res = await fetch('api_saas_auth.php?_=' + Date.now(), {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pass })
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    alert('استجابة غير مفهومة من الخادم: ' + text.substring(0, 60));
                    throw e;
                }
                
                if (data.success) {
                    SAAS_TOKEN = data.token;
                    localStorage.setItem('saas_token', SAAS_TOKEN);
                    alert("تم التحقق بنجاح! جاري تحويلك للوحة التحكم...");
                    location.reload();
                } else {
                    alert(data.error || 'رمز خاطئ');
                }
            } catch (e) { 
                console.error(e);
            } finally {
                btn.innerText = oldText;
                btn.disabled = false;
            }
        }

        function logoutSaas() { localStorage.removeItem('saas_token'); location.reload(); }

        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
            if (tabId === 'tab-audit') {
                loadAuditLogs(true);
            }
        }

        function escapeHtml(str) {
            if(!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        let auditCurrentPage = 1;
        const auditPageSize = 10;
        let auditTotal = 0;

        async function loadAuditLogs(resetPage = false) {
            if (resetPage) auditCurrentPage = 1;
            const search = document.getElementById('audit-search') ? document.getElementById('audit-search').value : '';
            const offset = (auditCurrentPage - 1) * auditPageSize;
            
            try {
                const url = apiUrl('saas', 'audit-logs') + `&limit=${auditPageSize}&offset=${offset}&search=${encodeURIComponent(search)}`;
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                
                if (data.success) {
                    auditTotal = data.total;
                    renderAuditLogs(data.logs);
                }
            } catch(e) {
                console.error("Failed to load audit logs", e);
            }
        }

        function renderAuditLogs(logs) {
            const tbody = document.getElementById('audit-logs-table-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            if (logs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-secondary);">لا توجد عمليات مسجلة حالياً.</td></tr>`;
                return;
            }
            
            logs.forEach((log, index) => {
                const globalIndex = (auditCurrentPage - 1) * auditPageSize + index + 1;
                tbody.innerHTML += `
                    <tr>
                        <td>${globalIndex}</td>
                        <td style="font-weight: bold; color: #f59e0b;">${escapeHtml(log.action)}</td>
                        <td>${escapeHtml(log.details)}</td>
                        <td><code style="background:rgba(255,255,255,0.05); padding: 2px 6px; border-radius:4px; font-size:0.85rem;">${escapeHtml(log.ip_address)}</code></td>
                        <td style="color: var(--text-secondary); font-size: 0.9rem;">${escapeHtml(log.created_at)}</td>
                    </tr>
                `;
            });
            
            const totalPages = Math.ceil(auditTotal / auditPageSize) || 1;
            document.getElementById('audit-page-indicator').innerText = `الصفحة ${auditCurrentPage} من ${totalPages}`;
            document.getElementById('btn-audit-prev').disabled = (auditCurrentPage === 1);
            document.getElementById('btn-audit-next').disabled = (auditCurrentPage === totalPages);
        }

        function changeAuditPage(direction) {
            const totalPages = Math.ceil(auditTotal / auditPageSize) || 1;
            const newPage = auditCurrentPage + direction;
            if (newPage >= 1 && newPage <= totalPages) {
                auditCurrentPage = newPage;
                loadAuditLogs();
            }
        }

        function downloadDatabaseBackup() {
            if (!confirm("هل ترغب بتنزيل نسخة احتياطية كاملة من قاعدة بيانات SaaS الإجمالية الآن؟")) return;
            const url = apiUrl('saas', 'settings', 'backup-db') + `&token=${encodeURIComponent(SAAS_TOKEN)}`;
            window.open(url, '_blank');
        }

        function renderBackupTenantsTable() {
            const tbody = document.getElementById('backup-tenants-table-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            const search = (document.getElementById('backup-tenant-search').value || '').toLowerCase();
            
            const filtered = allTenants.filter(t => {
                const companyName = (t.company_name || '').toLowerCase();
                const subdomain   = (t.subdomain || '').toLowerCase();
                const ownerName   = (t.owner_name || '').toLowerCase();
                const ownerPhone  = (t.owner_phone || '').toLowerCase();
                const phone2      = (t.phone2 || '').toLowerCase();
                
                return search === '' || 
                       companyName.includes(search) || 
                       subdomain.includes(search) || 
                       ownerName.includes(search) || 
                       ownerPhone.includes(search) ||
                       phone2.includes(search);
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-secondary); padding: 20px;">لا يوجد نتائج تطابق البحث</td></tr>`;
                return;
            }

            filtered.forEach(t => {
                let planId = t.plan_id || 1;
                let plan = allPlans.find(p => p.id == planId) || allPlans[0] || {name: 'غير محدد', storage: '-'};
                let maxStorage = t.max_storage !== null ? t.max_storage : (plan.storage || '-');

                let storageBytes = parseInt(t.storage_used) || 0;
                let storageStr = storageBytes + ' B';
                if (storageBytes >= 1073741824) storageStr = (storageBytes / 1073741824).toFixed(2) + ' GB';
                else if (storageBytes >= 1048576) storageStr = (storageBytes / 1048576).toFixed(2) + ' MB';
                else if (storageBytes >= 1024) storageStr = (storageBytes / 1024).toFixed(2) + ' KB';

                tbody.innerHTML += `
                    <tr style="cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05);" onclick="toggleBackupRowCheckbox(${t.id}, event)">
                        <td style="text-align: center; padding: 12px 15px;" onclick="event.stopPropagation();">
                            <input type="checkbox" value="${t.id}" class="backup-tenant-checkbox" id="backup-cb-${t.id}" style="transform: scale(1.25); cursor: pointer;" onchange="updateBackupSelectAllHeaderState()">
                        </td>
                        <td style="padding: 12px 15px;">
                            <strong style="font-size: 1.15rem; color:#fff; display:block; margin-bottom: 2px;">${t.company_name || '—'}</strong>
                            <small style="color:var(--primary-accent); font-weight: 600;">${t.subdomain || '—'}.elsajana.com</small>
                        </td>
                        <td style="padding: 12px 15px;">
                            <span style="font-weight: 600; display:block; margin-bottom: 2px;">${t.owner_name || '-'}</span>
                            <small style="color:var(--text-secondary);">${t.owner_phone || ''} ${t.phone2 ? ' - ' + t.phone2 : ''}</small>
                        </td>
                        <td style="direction: ltr; font-weight: bold; color: #cbd5e1; padding: 12px 15px;">
                            ${storageStr} <br>
                            <small style="color:var(--text-secondary); font-weight: normal;">الحد الأقصى: ${maxStorage} GB</small>
                        </td>
                        <td style="padding: 12px 15px;">
                            <span class="badge badge-plan" style="padding: 6px 12px; font-weight: bold;">${plan.name}</span>
                        </td>
                    </tr>
                `;
            });
        }

        function toggleBackupRowCheckbox(id, event) {
            if (event.target.tagName === 'INPUT') return;
            const cb = document.getElementById('backup-cb-' + id);
            if (cb) {
                cb.checked = !cb.checked;
                updateBackupSelectAllHeaderState();
            }
        }

        function selectAllBackupTenants(checked) {
            document.querySelectorAll('.backup-tenant-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            const headerCb = document.getElementById('backup-select-all-header');
            if (headerCb) headerCb.checked = checked;
        }

        function updateBackupSelectAllHeaderState() {
            const allCbs = document.querySelectorAll('.backup-tenant-checkbox');
            const checkedCbs = document.querySelectorAll('.backup-tenant-checkbox:checked');
            const headerCb = document.getElementById('backup-select-all-header');
            if (headerCb && allCbs.length > 0) {
                headerCb.checked = (allCbs.length === checkedCbs.length);
            }
        }

        function downloadSelectedTenantsBackup() {
            const selectedCbs = document.querySelectorAll('.backup-tenant-checkbox:checked');
            if (selectedCbs.length === 0) {
                alert('الرجاء اختيار مشترك واحد على الأقل لتنزيل النسخة الاحتياطية!');
                return;
            }
            const ids = Array.from(selectedCbs).map(cb => cb.value).join(',');
            const url = apiUrl('saas', 'settings', 'backup-db') + `&tenant_ids=${encodeURIComponent(ids)}&token=${encodeURIComponent(SAAS_TOKEN)}`;
            window.open(url, '_blank');
        }

        function populateWaTenantSelect() {
            const select = document.getElementById('wa-target-tenant');
            if (!select) return;
            select.innerHTML = '<option value="all">📢 إرسال لجميع المشتركين (رسالة جماعية)</option>';
            allTenants.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.dataset.phone = t.owner_phone;
                opt.dataset.owner = t.owner_name;
                opt.dataset.company = t.company_name;
                opt.innerText = `${t.company_name || '—'} (${t.owner_name || '—'} - ${t.owner_phone || '—'})`;
                select.appendChild(opt);
            });
        }

        function applyWaTemplate(val) {
            const textarea = document.getElementById('wa-message-body');
            if (!textarea) return;
            
            const selectTenant = document.getElementById('wa-target-tenant');
            let ownerName = "عميلنا الكريم";
            let companyName = "";
            if (selectTenant && selectTenant.value !== 'all') {
                const opt = selectTenant.options[selectTenant.selectedIndex];
                ownerName = opt.dataset.owner || "عميلنا الكريم";
                companyName = opt.dataset.company || "";
            }
            
            let msg = '';
            if (val === 'expiry') {
                msg = `السلام عليكم ورحمة الله وبركاته، أستاذ ${ownerName}.\n\nنود تذكيركم بأن اشتراك شركتكم ${companyName ? `(${companyName})` : ''} في نظام السجّانة ERP قارب على الانتهاء.\n\nيرجى التواصل معنا لتجديد الاشتراك لضمان استمرارية الخدمة دون انقطاع.\n\nشكراً لثقتكم بنا 🌸`;
            } else if (val === 'welcome') {
                msg = `أهلاً بك أستاذ ${ownerName} في عائلة السجّانة ERP! 🎉\n\nتم تفعيل حساب شركتكم ${companyName ? `(${companyName})` : ''} بنجاح.\n\nيمكنكم الآن البدء في إدارة حساباتكم ومخازنكم بكل سهولة.\n\nإذا واجهتك أي استفسارات، نحن هنا دائماً لخدمتك 🌸`;
            } else if (val === 'maintenance') {
                msg = `تنبيه هام لكل عملائنا الكرام ⚙️\n\nنود إخطاركم بأن هناك أعمال صيانة وتحديثات مجدولة لنظام السجّانة ERP ستبدأ اليوم في تمام الساعة 11:00 مساءً ولمدة ساعة واحدة.\n\nنشكر لكم حسن تعاونكم وتفهمكم 🌸`;
            }
            textarea.value = msg;
        }

        async function sendWhatsAppMessage() {
            const target = document.getElementById('wa-target-tenant').value;
            const message = document.getElementById('wa-message-body').value.trim();
            
            if (!message) {
                alert('الرجاء كتابة نص الرسالة أولاً!');
                return;
            }
            
            // Check if WhatsApp is connected first (using our state check)
            const indicator = document.getElementById('wa-status-indicator');
            if (indicator && indicator.style.backgroundColor === 'rgb(239, 68, 68)') { // reddish #ef4444
                alert('خادم الواتساب غير نشط حالياً! يرجى التأكد من تشغيل خادم الواتساب أولاً.');
                return;
            }
            
            const host = window.location.hostname;
            const sendBtn = document.querySelector('button[onclick="sendWhatsAppMessage()"]');
            const originalText = sendBtn.innerHTML;
            sendBtn.innerHTML = '<span>⏳ جاري الإرسال...</span>';
            sendBtn.disabled = true;
            
            try {
                if (target === 'all') {
                    // Send to all tenants
                    if (!confirm('هل أنت متأكد من رغبتك في إرسال هذه الرسالة الجماعية لجميع المشتركين؟')) {
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                        return;
                    }
                    
                    let successCount = 0;
                    let failCount = 0;
                    
                    for (let t of allTenants) {
                        if (!t.owner_phone) continue;
                        try {
                            const res = await fetch(apiUrl('saas', 'wa-proxy', 'send'), {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + SAAS_TOKEN 
                                },
                                body: JSON.stringify({
                                    userId: 'default-saas-admin',
                                    phone: t.owner_phone,
                                    message: message.replace(/{owner}/g, t.owner_name || 'عميلنا الكريم').replace(/{company}/g, t.company_name || '')
                                })
                            });
                            if (res.ok) successCount++;
                            else failCount++;
                        } catch(e) {
                            failCount++;
                        }
                    }
                    alert(`تمت عملية الإرسال الجماعي بنجاح!\nالمرسل لهم بنجاح: ${successCount}\nالفشل: ${failCount}`);
                    document.getElementById('wa-message-body').value = '';
                } else {
                    // Send to single tenant
                    const select = document.getElementById('wa-target-tenant');
                    const opt = select.options[select.selectedIndex];
                    const phone = opt.dataset.phone;
                    if (!phone) {
                        alert('هذا المشترك لا يملك رقم هاتف مسجل!');
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                        return;
                    }
                    
                    const res = await fetch(apiUrl('saas', 'wa-proxy', 'send'), {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + SAAS_TOKEN 
                        },
                        body: JSON.stringify({
                            userId: 'default-saas-admin',
                            phone: phone,
                            message: message
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert('تم إرسال رسالة الواتساب بنجاح!');
                        document.getElementById('wa-message-body').value = '';
                    } else {
                        alert('فشل الإرسال: ' + (data.error || 'خطأ غير معروف'));
                    }
                }
            } catch(err) {
                alert('فشل الاتصال بخادم الواتساب: ' + err.message);
            } finally {
                sendBtn.innerHTML = originalText;
                sendBtn.disabled = false;
            }
        }

        async function checkWhatsAppStatus() {
            const indicator = document.getElementById('wa-status-indicator');
            const text = document.getElementById('wa-status-text');
            if(!indicator || !text) return;
            try {
                const host = window.location.hostname;
                const res = await fetch(apiUrl('saas', 'wa-proxy', 'status'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                
                if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') {
                    indicator.style.background = '#10b981'; // Green
                    indicator.style.boxShadow = '0 0 8px #10b981';
                    text.innerText = 'خادم الواتساب: نشط (متصل)';
                } else {
                    indicator.style.background = '#eab308'; // Orange/Yellow
                    indicator.style.boxShadow = '0 0 8px #eab308';
                    text.innerText = 'خادم الواتساب: نشط (غير مرتبط)';
                }
            } catch(e) {
                indicator.style.background = '#ef4444'; // Red
                indicator.style.boxShadow = '0 0 8px #ef4444';
                text.innerText = 'خادم الواتساب: متوقف';
            }
        }

        async function openAdminWhatsAppModal() {
            document.getElementById('admin-wa-modal').style.display = 'flex';
            checkAdminWhatsAppConnection();
        }

        async function checkAdminWhatsAppConnection(isSilent = false) {
            const statusText = document.getElementById('admin-wa-status-text');
            const qrImg = document.getElementById('admin-wa-qr-image');
            const spinner = document.getElementById('admin-wa-loading');
            const logoutBtn = document.getElementById('admin-wa-logout-btn');
            
            if (!isSilent) {
                statusText.innerText = 'جاري الفحص...';
                statusText.style.color = '#fff';
                spinner.style.display = 'block';
                qrImg.style.display = 'none';
                logoutBtn.style.display = 'none';
            }
            
            try {
                const res = await fetch(apiUrl('saas', 'wa-proxy', 'status'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                let data;
                try {
                    data = await res.json();
                } catch(pe) {
                    throw new Error('HTTP error ' + res.status);
                }
                if (data && data.success === false) {
                    throw new Error(data.error || 'Server error');
                }
                if (!res.ok) {
                    throw new Error('HTTP error ' + res.status);
                }
                
                if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED') {
                    let msg = '✅ متصل بالواتساب بنجاح!';
                    if (data.phoneNumber) {
                        msg += `<br><span style="font-size: 0.95rem; font-weight: normal; color: #94a3b8; display: block; margin-top: 5px;">📞 متصل برقم الواتساب <strong style="color: #38bdf8; font-family: sans-serif;">[${data.phoneNumber}]</strong> بنجاح</span>`;
                    }
                    statusText.innerHTML = msg;
                    statusText.style.color = '#10b981';
                    
                    // Hide QR container and show success animation
                    document.getElementById('admin-wa-qr-container').style.display = 'none';
                    document.getElementById('admin-wa-success-animation').style.display = 'flex';
                    document.getElementById('admin-wa-instruction-text').style.display = 'none';
                    
                    // Update buttons
                    logoutBtn.style.display = 'inline-block';
                    document.getElementById('admin-wa-refresh-btn').style.display = 'none';
                    document.getElementById('admin-wa-close-btn').style.display = 'inline-block';
                } else {
                    if (!isSilent) {
                        statusText.innerText = 'يرجى مسح الرمز أدناه للربط:';
                        statusText.style.color = '#fbbf24';
                        
                        // Show QR container and hide success animation
                        document.getElementById('admin-wa-qr-container').style.display = 'inline-block';
                        document.getElementById('admin-wa-success-animation').style.display = 'none';
                        document.getElementById('admin-wa-instruction-text').style.display = 'block';
                        
                        logoutBtn.style.display = 'none';
                        document.getElementById('admin-wa-refresh-btn').style.display = 'inline-block';
                        document.getElementById('admin-wa-close-btn').style.display = 'none';
                        
                        fetchAdminWhatsAppQR();
                    } else {
                        // Silent polling: retry checking status silently in 5s
                        setTimeout(() => {
                            const modal = document.getElementById('admin-wa-modal');
                            if (modal && modal.style.display === 'flex') {
                                checkAdminWhatsAppConnection(true);
                            }
                        }, 5000);
                    }
                }
                
                // Keep the header widget state in sync
                checkWhatsAppStatus();
            } catch(e) {
                if (!isSilent) {
                    statusText.innerText = '❌ خادم الواتساب متوقف أو تعذر الاتصال به: ' + e.message;
                    statusText.style.color = '#ef4444';
                    spinner.style.display = 'none';
                    checkWhatsAppStatus();
                }
            }
        }

        async function fetchAdminWhatsAppQR() {
            const qrImg = document.getElementById('admin-wa-qr-image');
            const spinner = document.getElementById('admin-wa-loading');
            const statusText = document.getElementById('admin-wa-status-text');
            try {
                const res = await fetch(apiUrl('saas', 'wa-proxy', 'qr'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                let data;
                try {
                    data = await res.json();
                } catch(pe) {
                    throw new Error('HTTP error ' + res.status);
                }
                if (data && data.success === false) {
                    throw new Error(data.error || 'Server error');
                }
                if (!res.ok) {
                    throw new Error('HTTP error ' + res.status);
                }
                
                if (data.qrCode) {
                    qrImg.src = data.qrCode;
                    qrImg.style.display = 'block';
                    spinner.style.display = 'none';
                    statusText.innerText = 'يرجى مسح الرمز أدناه للربط:';
                    statusText.style.color = '#fbbf24';
                    
                    // Poll status silently every 5 seconds
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            checkAdminWhatsAppConnection(true);
                        }
                    }, 5000);
                } else {
                    // Node.js is initializing the client, wait and try again
                    statusText.innerText = 'جاري تحضير خادم الواتساب... ⏳';
                    setTimeout(() => {
                        const modal = document.getElementById('admin-wa-modal');
                        if (modal && modal.style.display === 'flex') {
                            fetchAdminWhatsAppQR();
                        }
                    }, 3000);
                }
            } catch(e) {
                statusText.innerText = '❌ فشل جلب رمز الـ QR أو الخدمة غير متوفرة: ' + e.message;
                statusText.style.color = '#ef4444';
                spinner.style.display = 'none';
            }
        }

        async function logoutAdminWhatsApp() {
            if (!confirm('هل ترغب بإلغاء ربط حساب الواتساب الحالي؟')) return;
            try {
                const host = window.location.hostname;
                await fetch(apiUrl('saas', 'wa-proxy', 'logout'), { 
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                checkAdminWhatsAppConnection();
            } catch(e) {
                alert('فشل تسجيل الخروج');
            }
        }

        let chartRegistrations = null;
        let chartStorage = null;

        function initAnalyticsCharts() {
            if (!allTenants || allTenants.length === 0) return;

            // 1. Group registrations by month
            const monthlyRegs = {};
            allTenants.forEach(tenant => {
                if (tenant.created_at) {
                    const date = new Date(tenant.created_at);
                    if (!isNaN(date)) {
                        const month = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
                        monthlyRegs[month] = (monthlyRegs[month] || 0) + 1;
                    }
                }
            });

            const sortedMonths = Object.keys(monthlyRegs).sort();
            const regCounts = sortedMonths.map(m => monthlyRegs[m]);

            // 2. Group top 5 storage consumers
            const sortedStorageTenants = [...allTenants]
                .sort((a, b) => (b.storage_used || 0) - (a.storage_used || 0))
                .slice(0, 5);

            const storageLabels = sortedStorageTenants.map(t => t.company_name);
            const storageValues = sortedStorageTenants.map(t => ((t.storage_used || 0) / (1024 * 1024)).toFixed(2)); // in MB

            // Registrations Chart
            const ctxReg = document.getElementById('chart-registrations');
            if (ctxReg) {
                if (chartRegistrations) chartRegistrations.destroy();
                chartRegistrations = new Chart(ctxReg.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: sortedMonths,
                        datasets: [{
                            label: 'عدد الشركات المسجلة',
                            data: regCounts,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            },
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            }
                        }
                    }
                });
            }

            // Storage Chart
            const ctxStore = document.getElementById('chart-storage');
            if (ctxStore) {
                if (chartStorage) chartStorage.destroy();
                chartStorage = new Chart(ctxStore.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: storageLabels,
                        datasets: [{
                            label: 'المساحة المستخدمة (MB)',
                            data: storageValues,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(139, 92, 246, 0.7)',
                                'rgba(236, 72, 153, 0.7)'
                            ],
                            borderWidth: 0,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            },
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#9ca3af' }
                            }
                        }
                    }
                });
            }
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        
        function toggleDropdown(id) {
            const el = document.getElementById('drop-' + id);
            if(el.classList.contains('show')) el.classList.remove('show');
            else {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('show'));
                el.classList.add('show');
            }
        }
        
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.btn-outline')) {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('show'));
            }
        });

        async function initDashboard() {
            try {
                const resPub = await fetch('api_index.php?r=public');
                const dataPub = await resPub.json();
                allPlans = dataPub.plans || [];
                allPartners = dataPub.partners || [];
                allAds = dataPub.ads || [];
            } catch(e) {}

            await loadTenants();
            loadGlobalSettings();
            loadBackdoorCredentials();
            loadMessages();
            
            renderPlans();
            renderPartners();
            renderAds();
            
            // Check WhatsApp service status on startup and every 30 seconds
            checkWhatsAppStatus();
            setInterval(checkWhatsAppStatus, 30000);
        }

        // Helper: build API URL using query params (Nginx doesn't support PHP PATH_INFO)
        function apiUrl(resource, action, subAction) {
            let url = `api_index.php?r=${resource}&a=${action}`;
            if (subAction) url += `&a2=${subAction}`;
            return url;
        }

        async function loadTenants() {
            const tbody = document.getElementById('tenants-body');
            try {
                const url = apiUrl('saas', 'tenants');
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                console.log('Tenants API response:', res.status, data);

                if (Array.isArray(data)) {
                    allTenants = data;
                    renderTenantsTable();
                    populateUserTenantSelect();
                    renderBackupTenantsTable();
                    populateWaTenantSelect();
                    updateKPIs();
                    initAnalyticsCharts();
                } else {
                    const msg = data.error || JSON.stringify(data);
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ خطأ: ${msg}</td></tr>`;
                    console.error('API error:', data);
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#ef4444;">❌ فشل الاتصال: ${e.message}</td></tr>`;
                console.error('Fetch error:', e);
            }
        }

        function updateKPIs() {
            let active = 0, suspended = 0, expiring = 0, revenue = 0;
            const now = new Date();
            const next30 = new Date(); next30.setDate(now.getDate() + 30);
            
            allTenants.forEach(t => {
                if(t.status === 'active') active++;
                if(t.status === 'suspended') suspended++;
                if(t.valid_until && new Date(t.valid_until) < next30 && new Date(t.valid_until) > now) expiring++;
                
                // Revenue calculation based on real plan
                let planId = t.plan_id || 1;
                let plan = allPlans.find(p => p.id == planId);
                if(plan && t.status === 'active') revenue += parseInt(plan.price || 0);
            });
            
            document.getElementById('kpi-total-tenants').innerText = allTenants.length;
            document.getElementById('kpi-active-tenants').innerText = active;
            document.getElementById('kpi-suspended-tenants').innerText = suspended;
            document.getElementById('kpi-revenue').innerText = '$' + revenue;
        }

        function filterTenants() { renderTenantsTable(); }

        function renderTenantsTable() {
            const tbody = document.getElementById('tenants-body');
            tbody.innerHTML = '';
            const search = (document.getElementById('tenant-search').value || '').toLowerCase();
            const filterEl = document.getElementById('tenant-filter-status');
            const statusFilter = filterEl ? filterEl.value : 'all';

            const filtered = allTenants.filter(t => {
                const companyName = (t.company_name || '').toLowerCase();
                const subdomain   = (t.subdomain || '').toLowerCase();
                const ownerName   = (t.owner_name || '').toLowerCase();
                const ownerPhone  = (t.owner_phone || '').toLowerCase();
                const adminUser   = (t.admin_username || '').toLowerCase();
                
                let planId = t.plan_id || 1;
                let plan = allPlans.find(p => p.id == planId) || allPlans[0] || {name: ''};
                const planName = (plan.name || '').toLowerCase();

                const mSearch = search === '' || 
                             companyName.includes(search) || 
                             subdomain.includes(search) || 
                             ownerName.includes(search) || 
                             ownerPhone.includes(search) || 
                             adminUser.includes(search) || 
                             planName.includes(search);

                const mStatus = statusFilter === 'all' || t.status === statusFilter;
                return mSearch && mStatus;
            });

            filtered.forEach(t => {
                try {
                    let statusColor = '#94a3b8'; // Default
                    if(t.status === 'active') statusColor = '#10b981'; // Green
                    if(t.status === 'pending') statusColor = '#f59e0b'; // Orange
                    if(t.status === 'suspended') statusColor = '#ef4444'; // Red

                    const statusBadge = `
                        <select onchange="updateTenantStatus(${t.id}, this.value, this)" style="padding: 6px 12px; border-radius: 20px; background: rgba(0,0,0,0.3); color: ${statusColor}; border: 1px solid ${statusColor}; font-family: Tajawal; cursor: pointer; outline: none; width: 130px; font-weight: bold; text-align: center;">
                            <option value="active" ${t.status === 'active' ? 'selected' : ''} style="color:#10b981;">نشط</option>
                            <option value="pending" ${t.status === 'pending' ? 'selected' : ''} style="color:#f59e0b;">قيد المراجعة</option>
                            <option value="suspended" ${t.status === 'suspended' ? 'selected' : ''} style="color:#ef4444;">موقوف</option>
                        </select>
                    `;
                    let validStr = '<span style="color:#ef4444">غير محدد</span>';
                    if(t.valid_until) {
                        const validDate = new Date(t.valid_until);
                        validStr = `<span style="color:${validDate < new Date() ? '#ef4444' : '#10b981'}; direction:ltr; display:inline-block;">${validDate.toLocaleDateString()}</span>`;
                    }

                    let planId = t.plan_id || 1;
                    let plan = allPlans.find(p => p.id == planId) || allPlans[0] || {name: 'غير محدد', maxUsers: '-', storage: '-'};
                    let maxUsers = t.max_users || plan.maxUsers || '-';
                    let maxStorage = t.max_storage !== null ? t.max_storage : (plan.storage || '-');

                    let storageBytes = parseInt(t.storage_used) || 0;
                    let storageStr = storageBytes + ' B';
                    if (storageBytes >= 1073741824) storageStr = (storageBytes / 1073741824).toFixed(2) + ' GB';
                    else if (storageBytes >= 1048576) storageStr = (storageBytes / 1048576).toFixed(2) + ' MB';
                    else if (storageBytes >= 1024) storageStr = (storageBytes / 1024).toFixed(2) + ' KB';

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${t.company_name || '—'}</strong><br><small style="color:var(--text-secondary)">${t.subdomain || '—'}.elsajana.com</small></td>
                            <td><span style="color:var(--primary-accent); font-weight: bold;">${t.admin_username || 'غير متوفر'}</span></td>
                            <td>${t.owner_name || '-'}<br><small>${t.owner_phone || ''}</small>${t.phone2 ? '<br><small>'+t.phone2+'</small>' : ''}</td>
                            <td><span class="badge badge-plan">${plan.name}</span><br><small style="color:var(--text-secondary)">الحد: ${maxUsers} مستخدم</small></td>
                            <td style="direction: ltr;">${storageStr} <br><small style="color:var(--text-secondary)">الحد: ${maxStorage} GB</small></td>
                            <td>${statusBadge}</td>
                            <td>${validStr}</td>
                            <td>
                                <div class="dropdown" id="drop-${t.id}">
                                    <button class="btn-outline" onclick="toggleDropdown(${t.id}); event.stopPropagation();">إجراءات ▼</button>
                                    <div class="dropdown-content">
                                        ${t.status === 'pending' ? `<button onclick="activateTenant(${t.id})" style="color:var(--success-color)">✅ تفعيل الحساب</button>` : ''}
                                        ${t.receipt_path ? `<a href="${t.receipt_path}" target="_blank" style="display:block; padding:8px 12px; color:var(--primary-accent); text-decoration:none;">📄 عرض الإشعار</a>` : ''}
                                        <button onclick="openEditModal(${t.id})">⚙️ تعديل الصلاحية والباقة</button>
                                        <button onclick="deleteTenant(${t.id})" style="color:var(--danger-color)">🗑️ حذف</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                } catch(rowErr) {
                    console.error('Error rendering tenant row:', t, rowErr);
                }
            });
            if(filtered.length === 0) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">لا يوجد بيانات</td></tr>';
        }

        // Add User & Reset Pass Logic (Simulated for SaaS)
        function populateUserTenantSelect() {
            const s = document.getElementById('users-tenant-select');
            s.innerHTML = '<option value="">-- اختر شركة --</option>';
            allTenants.forEach(t => {
                s.innerHTML += `<option value="${t.id}">${t.company_name}</option>`;
            });
        }
        
        async function loadTenantUsers() {
            const tenantId = document.getElementById('users-tenant-select').value;
            const tbody = document.getElementById('tenant-users-body');
            if(!tenantId) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">اختر شركة לעرض مستخدميها</td></tr>'; return; }
            
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">جاري التحميل...</td></tr>';
            try {
                const url = apiUrl('saas', 'users') + '&tenant_id=' + tenantId;
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                
                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#ef4444;">${data.error}</td></tr>`;
                    return;
                }
                
                const users = Array.isArray(data) ? data : [];
                tbody.innerHTML = '';
                if(users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">لا يوجد مستخدمين</td></tr>';
                } else {
                    users.forEach(u => {
                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${u.username}</strong></td>
                                <td>${u.fullName || ''}</td>
                                <td><span class="badge ${u.role==='admin'?'badge-plan':'badge-active'}">${u.role}</span></td>
                                <td><button class="btn-primary" style="padding:5px 10px; font-size:0.8rem;" onclick="openResetPassModal(${tenantId}, ${u.id})">إعادة تعيين كلمة السر</button></td>
                            </tr>
                        `;
                    });
                }
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#ef4444;">حدث خطأ في جلب المستخدمين</td></tr>';
            }
        }

        function openAddUserModal() {
            if(!document.getElementById('users-tenant-select').value) return alert('الرجاء اختيار الشركة أولاً من قائمة المستخدمين');
            document.getElementById('new-user-username').value = '';
            document.getElementById('new-user-fullname').value = '';
            document.getElementById('new-user-pass').value = '';
            document.getElementById('add-user-modal').style.display = 'flex';
        }
        
        async function saveNewUser() {
            const tenantId = document.getElementById('users-tenant-select').value;
            const username = document.getElementById('new-user-username').value;
            const fullName = document.getElementById('new-user-fullname').value;
            const password = document.getElementById('new-user-pass').value;
            const role = document.getElementById('new-user-role').value;
            
            if(!tenantId || !username || !password) return alert('أكمل البيانات الأساسية');
            
            try {
                const res = await fetch(apiUrl('saas', 'users', 'add'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ tenant_id: tenantId, username, fullName, password, role })
                });
                const data = await res.json();
                if(data.success) {
                    closeModal('add-user-modal');
                    loadTenantUsers();
                    alert('تمت إضافة المستخدم للشركة بنجاح!');
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch (e) {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        }

        function openResetPassModal(tenantId, userId) {
            document.getElementById('target-user-id').value = userId;
            document.getElementById('target-user-pass').value = '';
            document.getElementById('user-action-modal').style.display = 'flex';
        }
        async function saveUserPassword() {
            const userId = document.getElementById('target-user-id').value;
            const password = document.getElementById('target-user-pass').value;
            if(!password) return alert('أدخل كلمة المرور');
            
            try {
                const res = await fetch(apiUrl('saas', 'users', 'password'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ user_id: userId, password })
                });
                const data = await res.json();
                if(data.success) {
                    alert('تم تغيير كلمة المرور بنجاح!');
                    closeModal('user-action-modal');
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch (e) {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        }

        // Plans Logic
        function renderPlans() {
            const tbody = document.getElementById('plans-body');
            tbody.innerHTML = '';
            allPlans.forEach(p => {
                let badge = p.isTrial ? '<span style="background:var(--primary-accent);color:#000;padding:2px 6px;border-radius:4px;font-size:0.7rem; margin-right:5px;">تجريبية</span>' : '';
                let statusBadge = p.status === 'suspended' ? '<span style="background:#ef4444;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.7rem;">موقوفة</span>' : '<span style="background:#10b981;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.7rem;">نشطة</span>';
                let durationTxt = p.duration ? `${p.duration} يوم` : 'غير محدد';
                let actionBtnText = p.status === 'suspended' ? '✅ تفعيل' : '⏸️ إيقاف';
                let actionBtnColor = p.status === 'suspended' ? '#10b981' : '#f59e0b';
                
                tbody.innerHTML += `
                    <tr>
                        <td><strong style="color:var(--primary-accent)">${p.name}</strong><br>${badge} ${statusBadge}</td>
                        <td>$${p.price}</td>
                        <td>${durationTxt}</td>
                        <td>${p.maxUsers} مستخدم</td>
                        <td>${p.storage} GB</td>
                        <td>
                            <button class="btn-primary" style="padding:5px 10px; font-size:0.8rem; margin-bottom:5px;" onclick="editPlan(${p.id})">⚙️ تعديل</button>
                            <button class="btn-outline" style="padding:5px 10px; font-size:0.8rem; color:${actionBtnColor}; border-color:${actionBtnColor}; margin-bottom:5px;" onclick="togglePlanStatus(${p.id})">${actionBtnText}</button>
                            <button class="btn-danger" style="padding:5px 10px; font-size:0.8rem; margin-bottom:5px;" onclick="deletePlan(${p.id})">🗑️ حذف</button>
                        </td>
                    </tr>
                `;
            });
            
            const pSelect = document.getElementById('edit-tenant-plan');
            if(pSelect) {
                pSelect.innerHTML = '';
                allPlans.forEach(p => { pSelect.innerHTML += `<option value="${p.id}">${p.name}</option>`; });
            }
        }
        
        function openPlanModal() {
            document.getElementById('plan-id').value = '';
            document.getElementById('plan-name').value = '';
            document.getElementById('plan-price').value = '';
            document.getElementById('plan-duration').value = '30';
            document.getElementById('plan-users').value = '';
            document.getElementById('plan-storage').value = '';
            document.getElementById('plan-is-trial').checked = false;
            document.getElementById('plan-modal').style.display = 'flex';
        }
        function editPlan(id) {
            let p = allPlans.find(x => x.id === id);
            document.getElementById('plan-id').value = p.id;
            document.getElementById('plan-name').value = p.name;
            document.getElementById('plan-price').value = p.price;
            document.getElementById('plan-duration').value = p.duration || 30;
            document.getElementById('plan-users').value = p.maxUsers;
            document.getElementById('plan-storage').value = p.storage;
            document.getElementById('plan-is-trial').checked = !!p.isTrial;
            document.getElementById('plan-modal').style.display = 'flex';
        }
        async function savePlan() {
            let id = document.getElementById('plan-id').value;
            let existingPlan = id ? allPlans.find(x => x.id == id) : {};
            let p = {
                id: id ? parseInt(id) : Date.now(),
                name: document.getElementById('plan-name').value,
                price: document.getElementById('plan-price').value,
                duration: document.getElementById('plan-duration').value,
                maxUsers: document.getElementById('plan-users').value,
                storage: document.getElementById('plan-storage').value,
                isTrial: document.getElementById('plan-is-trial').checked,
                status: existingPlan.status || 'active'
            };
            if(id) {
                let idx = allPlans.findIndex(x => x.id == id);
                allPlans[idx] = p;
            } else { allPlans.push(p); }
            try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
            closeModal('plan-modal');
            renderPlans();
            updateKPIs();
        }

        async function deletePlan(id) {
            if(!confirm('هل أنت متأكد من حذف هذه الباقة نهائياً؟')) return;
            allPlans = allPlans.filter(x => x.id !== id);
            try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
            renderPlans();
            updateKPIs();
        }

        async function togglePlanStatus(id) {
            let idx = allPlans.findIndex(x => x.id === id);
            if(idx > -1) {
                allPlans[idx].status = (allPlans[idx].status === 'suspended') ? 'active' : 'suspended';
                try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}
                renderPlans();
            }
        }

        function updateMaxUsersFromPlan() {
            let planId = document.getElementById('edit-tenant-plan').value;
            let plan = allPlans.find(p => p.id == planId);
            if(plan) {
                document.getElementById('edit-tenant-max-users').value = plan.maxUsers;
                document.getElementById('edit-tenant-max-storage').value = plan.storage;
            }
        }

        
        function renderPartners() {
            const tbody = document.getElementById('partners-body');
            tbody.innerHTML = '';
            allPartners.forEach(p => {
                const statusHtml = (p.active !== false) ? '<span style="color:#10b981;">نشط</span>' : '<span style="color:#ef4444;">غير نشط</span>';
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${p.name}</strong></td>
                        <td><img src="${p.logo}" height="30" alt="logo" style="border-radius:4px; max-height:40px;"></td>
                        <td>${statusHtml}</td>
                        <td>
                            <button class="btn-primary" style="padding:4px 8px; font-size:0.8rem;" onclick="editPartner(${p.id})">تعديل</button>
                            <button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deletePartner(${p.id})">حذف</button>
                        </td>
                    </tr>
                `;
            });
        }
        function openPartnerModal() {
            document.getElementById('partner-id').value = '';
            document.getElementById('partner-name').value = '';
            document.getElementById('partner-existing-logo').value = '';
            document.getElementById('partner-logo-upload').value = '';
            document.getElementById('partner-logo-preview').style.display = 'none';
            document.getElementById('partner-active').checked = true;
            document.getElementById('partner-modal').style.display = 'flex';
        }
        function editPartner(id) {
            let p = allPartners.find(x => x.id === id);
            if(!p) return;
            document.getElementById('partner-id').value = p.id;
            document.getElementById('partner-name').value = p.name || '';
            document.getElementById('partner-existing-logo').value = p.logo || '';
            document.getElementById('partner-logo-upload').value = '';
            document.getElementById('partner-active').checked = (p.active !== false);
            
            const preview = document.getElementById('partner-logo-preview');
            if (p.logo) {
                preview.style.display = 'block';
                preview.querySelector('img').src = p.logo;
            } else {
                preview.style.display = 'none';
            }
            
            document.getElementById('partner-modal').style.display = 'flex';
        }
        async function savePartner() {
            const btn = document.getElementById('save-partner-btn');
            btn.innerText = 'جاري الحفظ...';
            btn.disabled = true;

            let id = document.getElementById('partner-id').value;
            let name = document.getElementById('partner-name').value;
            let active = document.getElementById('partner-active').checked;
            let existingLogo = document.getElementById('partner-existing-logo').value;
            let fileInput = document.getElementById('partner-logo-upload');
            
            let finalLogo = existingLogo;

            try {
                if (fileInput.files.length > 0) {
                    const formData = new FormData();
                    formData.append('image', fileInput.files[0]);
                    const uploadRes = await fetch(apiUrl('saas', 'upload-partner'), {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN },
                        body: formData
                    });
                    const uploadData = await uploadRes.json();
                    if (uploadData.success) {
                        finalLogo = uploadData.url;
                    } else {
                        alert('خطأ في رفع الصورة: ' + (uploadData.error || ''));
                        btn.innerText = 'حفظ';
                        btn.disabled = false;
                        return;
                    }
                }

                let p = {
                    id: id ? parseInt(id) : Date.now(),
                    name: name,
                    logo: finalLogo,
                    active: active
                };

                if(id) {
                    let idx = allPartners.findIndex(x => x.id == id);
                    if(idx !== -1) allPartners[idx] = p;
                } else { 
                    allPartners.push(p); 
                }
                
                await fetch(apiUrl('saas', 'settings', 'partners'), { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                    body: JSON.stringify({ partners: allPartners }) 
                });
                
                closeModal('partner-modal');
                renderPartners();
            } catch(e) {
                console.error(e);
                alert('حدث خطأ أثناء الحفظ');
            } finally {
                btn.innerText = 'حفظ';
                btn.disabled = false;
            }
        }
        async function deletePartner(id) {
            if(!confirm('هل أنت متأكد من الحذف؟')) return;
            allPartners = allPartners.filter(x => x.id !== id);
            try { await fetch(apiUrl('saas', 'settings', 'partners'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ partners: allPartners }) }); } catch(e){}
            renderPartners();
        }

        // Original API calls ported
        function openEditModal(id) {
            const t = allTenants.find(x => x.id == id);
            if(t) {
                document.getElementById('edit-tenant-id').value = t.id;
                document.getElementById('edit-status').value = t.status || 'active';
                let planId = t.plan_id || 1;
                document.getElementById('edit-tenant-plan').value = planId;
                let plan = allPlans.find(p => p.id == planId) || allPlans[0];
                document.getElementById('edit-tenant-max-users').value = t.max_users || plan.maxUsers;
                document.getElementById('edit-tenant-max-storage').value = t.max_storage !== null ? t.max_storage : (plan.storage || '');
                if(t.valid_until) {
                    const d = new Date(t.valid_until.replace(/-/g, "/"));
                    if (editValidUntilPicker) {
                        editValidUntilPicker.setDate(d);
                    } else {
                        document.getElementById('edit-valid-until').value = t.valid_until;
                    }
                } else {
                    if (editValidUntilPicker) {
                        editValidUntilPicker.clear();
                    } else {
                        document.getElementById('edit-valid-until').value = '';
                    }
                }
                document.getElementById('edit-modal').style.display = 'flex';
            }
        }

        async function saveTenantUpdate() {
            const id = document.getElementById('edit-tenant-id').value;
            const status = document.getElementById('edit-status').value;
            let valid_until = '';
            if (editValidUntilPicker && editValidUntilPicker.selectedDates.length > 0) {
                const selectedDate = editValidUntilPicker.selectedDates[0];
                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const day = String(selectedDate.getDate()).padStart(2, '0');
                const hours = String(selectedDate.getHours()).padStart(2, '0');
                const minutes = String(selectedDate.getMinutes()).padStart(2, '0');
                const seconds = String(selectedDate.getSeconds()).padStart(2, '0');
                valid_until = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            } else {
                valid_until = document.getElementById('edit-valid-until').value;
                if(valid_until) valid_until = new Date(valid_until).toISOString().slice(0, 19).replace('T', ' ');
            }
            
            const maxUsers = document.getElementById('edit-tenant-max-users').value;
            const maxStorage = document.getElementById('edit-tenant-max-storage').value;
            const planId = document.getElementById('edit-tenant-plan').value;

            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'update'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, status, valid_until, max_users: maxUsers, max_storage: maxStorage, plan_id: planId })
                });
                const data = await res.json();
                if(data.success) { closeModal('edit-modal'); loadTenants(); }
                else alert(data.error);
            } catch(e) { alert('فشل الاتصال'); }
        }

        async function deleteTenant(id) {
            if(!confirm('هل أنت متأكد من الحذف النهائي؟')) return;
            try {
                const url = apiUrl('saas', 'tenants', id);
                const res = await fetch(url, { 
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } 
                });
                const data = await res.json();
                if(data.success) { alert('تم الحذف'); loadTenants(); }
                else alert(data.error || 'فشل حذف المشترك');
            } catch(e) { alert('فشل الاتصال'); }
        }

        async function activateTenant(id) {
            if(!confirm('هل تأكدت من وصول الدفعة وتود تفعيل الحساب؟')) return;
            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'activate'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id })
                });
                const data = await res.json();
                if(data.success) { alert('تم التفعيل بنجاح'); loadTenants(); }
            } catch(e) {}
        }

        async function updateTenantStatus(id, newStatus, selectEl) {
            if (selectEl) {
                let color = newStatus === 'active' ? '#10b981' : (newStatus === 'pending' ? '#f59e0b' : '#ef4444');
                selectEl.style.color = color;
                selectEl.style.borderColor = color;
            }
            try {
                const res = await fetch(apiUrl('saas', 'tenants', 'update'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, status: newStatus })
                });
                const data = await res.json();
                if(!data.success) {
                    alert('حدث خطأ أثناء تحديث الحالة');
                    loadTenants(); // Revert
                }
            } catch(e) {
                alert('فشل الاتصال');
                loadTenants(); // Revert
            }
        }

        let autoLogoutTimer;
        let autoLogoutValue = 30;
        let autoLogoutUnit = 'minutes';

        function resetAutoLogout() {
            clearTimeout(autoLogoutTimer);
            if (autoLogoutValue > 0 && SAAS_TOKEN) {
                let multiplier = autoLogoutUnit === 'minutes' ? 60 * 1000 : 1000;
                autoLogoutTimer = setTimeout(() => {
                    alert('تم تسجيل الخروج تلقائياً بسبب عدم النشاط للحفاظ على الأمان.');
                    logoutSaas();
                }, autoLogoutValue * multiplier);
            }
        }

        // Detect user activity to reset the logout timer
        ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt => {
            document.addEventListener(evt, resetAutoLogout);
        });

        function togglePasswordVisibility(inputId, iconEl) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconEl.innerText = "🙈";
                iconEl.style.opacity = "0.7";
            } else {
                input.type = "password";
                iconEl.innerText = "👁️";
                iconEl.style.opacity = "1";
            }
        }

        async function loadGlobalSettings() {
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'exchange-rate'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }});
                const data = await res.json();
                if(data) {
                    document.getElementById('global-exchange-rate').value = data.rate || 1.0;
                    document.getElementById('global-exchange-visible').checked = data.visible === true || data.visible === 1;
                    if(data.activation_delay_hours !== undefined) {
                        document.getElementById('global-activation-delay').value = data.activation_delay_hours;
                    }
                    if(data.auto_logout_value !== undefined) {
                        document.getElementById('global-auto-logout-value').value = data.auto_logout_value;
                        autoLogoutValue = parseInt(data.auto_logout_value);
                    }
                    if(data.auto_logout_unit !== undefined) {
                        document.getElementById('global-auto-logout-unit').value = data.auto_logout_unit;
                        autoLogoutUnit = data.auto_logout_unit;
                    }
                    resetAutoLogout();
                }
            } catch(e) {}
        }

        async function loadBackdoorCredentials() {
            try {
                const res = await fetch(apiUrl('saas', 'backdoor-credentials'), {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                if (data.username) {
                    document.getElementById('backdoor-username').value = data.username;
                }
            } catch(e) {}
        }

        async function saveGlobalSettings() {
            const rate = document.getElementById('global-exchange-rate').value;
            const visible = document.getElementById('global-exchange-visible').checked;
            const activation_delay_hours = document.getElementById('global-activation-delay').value;
            const auto_logout_value = document.getElementById('global-auto-logout-value').value;
            const auto_logout_unit = document.getElementById('global-auto-logout-unit').value;
            
            try {
                const res = await fetch(apiUrl('saas', 'settings', 'exchange-rate'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ rate, visible, activation_delay_hours, auto_logout_value, auto_logout_unit })
                });
                autoLogoutValue = parseInt(auto_logout_value);
                autoLogoutUnit = auto_logout_unit;
                resetAutoLogout();
                alert('تم حفظ الإعدادات');
            } catch(e) {}
        }

        async function saveMasterToken() {
            const oldToken = document.getElementById('old-master-token').value;
            const token = document.getElementById('new-master-token').value;
            if(!oldToken || !token) return alert('أدخل الرمزين');
            try {
                const res = await fetch(apiUrl('saas', 'master-token'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ oldToken, token })
                });
                const data = await res.json();
                if(data.success) { alert('تم التغيير! سجل الدخول مجددا'); logoutSaas(); }
            } catch(e) {}
        }

        async function loadBackdoorUser() {
            try {
                const res = await fetch(apiUrl('saas', 'backdoor-credentials'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                if(data.username) document.getElementById('backdoor-username').value = data.username;
            } catch(e) {}
        }

        async function saveBackdoorCredentials() {
            const username = document.getElementById('backdoor-username').value;
            const password = document.getElementById('backdoor-password').value;
            if(!username || !password) return alert('أدخل اسم المستخدم وكلمة المرور للدعم الفني');
            try {
                const res = await fetch(apiUrl('saas', 'backdoor-credentials'), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ username, password })
                });
                const data = await res.json();
                if(data.success) {
                    alert('تم حفظ وتفعيل حساب الدعم الفني بنجاح!');
                    document.getElementById('backdoor-password').value = '';
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch(e) {
                alert('فشل الاتصال');
            }
        }

        async function loadMessages() {
            try {
                const res = await fetch(apiUrl('saas', 'messages'), { headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN } });
                const data = await res.json();
                
                const tbody = document.getElementById('messages-body');
                tbody.innerHTML = '';
                
                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:red;">خطأ: ${data.error}</td></tr>`;
                    return;
                }
                
                allMessagesData = Array.isArray(data) ? data : [];
                
                if(allMessagesData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">لا توجد رسائل نظام</td></tr>';
                } else {
                    allMessagesData.forEach(m => {
                        let targetText = 'الجميع';
                        let targets = [];
                        try { targets = JSON.parse(m.target_tenants || '[]'); } catch(e){}
                        if (!targets.includes('all') && targets.length > 0) {
                            const companyNames = [];
                            targets.forEach(tid => {
                                const tenant = allTenants.find(t => t.id == tid);
                                if (tenant) companyNames.push(tenant.company_name);
                            });
                            targetText = companyNames.length > 0 ? companyNames.join('، ') : 'مشتركين محددين';
                        }

                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${m.title || 'بدون عنوان'}</strong><br><small style="color:var(--text-secondary);">${m.message || ''}</small></td>
                                <td><span style="color:var(--primary-accent); font-weight:bold;">${targetText}</span></td>
                                <td><span class="badge" style="background: rgba(255,255,255,0.05); color: #fff;">${m.theme || 'info'}</span></td>
                                <td>
                                    <button class="btn-outline" style="padding:4px 8px; font-size:0.8rem; margin-left: 5px; color: var(--primary-accent); border-color: var(--primary-accent);" onclick="openMessageModal(${m.id})">تعديل</button>
                                    <button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deleteMessage(${m.id})">حذف</button>
                                </td>
                            </tr>`;
                    });
                }
            } catch (e) {
                console.error("Error loading messages:", e);
                const tbody = document.getElementById('messages-body');
                if (tbody) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red;">حدث خطأ في جلب الرسائل.</td></tr>';
            }
        }

        function populateMsgTargetTenantsList() {
            const container = document.getElementById('msg-target-tenants-list');
            if (!container) return;
            container.innerHTML = '';
            if (!allTenants || allTenants.length === 0) {
                container.innerHTML = '<span style="color: var(--text-secondary);">لا يوجد مشتركين مسجلين</span>';
                return;
            }
            allTenants.forEach(t => {
                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.gap = '8px';
                label.style.color = '#fff';
                label.style.cursor = 'pointer';
                label.style.fontSize = '0.9rem';
                label.style.margin = '4px 0';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = t.id;
                checkbox.className = 'msg-target-checkbox';
                checkbox.style.cursor = 'pointer';
                
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(t.company_name + ` (${t.subdomain})`));
                container.appendChild(label);
            });
        }

        function toggleMsgTargetTenantsList() {
            const val = document.getElementById('msg-target-type').value;
            document.getElementById('msg-target-tenants-group').style.display = (val === 'custom') ? 'block' : 'none';
        }

        function openMessageModal(id) {
            populateMsgTargetTenantsList();
            const modal = document.getElementById('message-modal');
            const titleEl = document.querySelector('#message-modal .modal-header h3');
            
            if (id) {
                const m = allMessagesData.find(msg => msg.id == id);
                if (m) {
                    document.getElementById('msg-id').value = m.id;
                    document.getElementById('msg-title').value = m.title || '';
                    document.getElementById('msg-body').value = m.message || '';
                    document.getElementById('msg-theme').value = m.theme || 'info';
                    document.getElementById('msg-show-once').checked = (m.show_once == 1);
                    
                    let targets = [];
                    try { targets = JSON.parse(m.target_tenants || '[]'); } catch(e){}
                    
                    if (targets.includes('all') || targets.length === 0) {
                        document.getElementById('msg-target-type').value = 'all';
                        document.getElementById('msg-target-tenants-group').style.display = 'none';
                    } else {
                        document.getElementById('msg-target-type').value = 'custom';
                        document.getElementById('msg-target-tenants-group').style.display = 'block';
                        
                        document.querySelectorAll('.msg-target-checkbox').forEach(cb => {
                            cb.checked = targets.includes(parseInt(cb.value)) || targets.includes(cb.value);
                        });
                    }
                    
                    if (titleEl) titleEl.innerText = 'تعديل رسالة النظام';
                }
            } else {
                document.getElementById('msg-id').value = '0';
                document.getElementById('msg-title').value = '';
                document.getElementById('msg-body').value = '';
                document.getElementById('msg-theme').value = 'info';
                document.getElementById('msg-show-once').checked = true;
                document.getElementById('msg-target-type').value = 'all';
                document.getElementById('msg-target-tenants-group').style.display = 'none';
                
                document.querySelectorAll('.msg-target-checkbox').forEach(cb => cb.checked = false);
                if (titleEl) titleEl.innerText = 'إنشاء رسالة نظامية';
            }
            
            modal.style.display = 'flex';
        }

        async function saveMessage() {
            const id = parseInt(document.getElementById('msg-id').value) || 0;
            const title = document.getElementById('msg-title').value.trim();
            const body = document.getElementById('msg-body').value.trim();
            const theme = document.getElementById('msg-theme').value;
            const showOnce = document.getElementById('msg-show-once').checked ? 1 : 0;
            
            let targets = ['all'];
            if (document.getElementById('msg-target-type').value === 'custom') {
                const selectedCbs = document.querySelectorAll('.msg-target-checkbox:checked');
                targets = Array.from(selectedCbs).map(cb => parseInt(cb.value));
                if (targets.length === 0) {
                    alert('الرجاء اختيار مشترك واحد على الأقل أو جعل الجمهور "الجميع"!');
                    return;
                }
            }
            
            if (!title || !body) {
                alert('الرجاء إدخال عنوان ومحتوى الرسالة!');
                return;
            }
            
            try {
                const res = await fetch(apiUrl('saas', 'messages'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id, title, message: body, target_tenants: targets, theme, show_once: showOnce })
                });
                const data = await res.json();
                if (data.success) {
                    closeModal('message-modal');
                    loadMessages();
                } else {
                    alert(data.error || 'فشل حفظ الرسالة');
                }
            } catch(e) {
                alert('فشل الاتصال بالخادم');
            }
        }

        async function deleteMessage(id) {
            if(!confirm('هل أنت متأكد من حذف هذه الرسالة النظامية؟')) return;
            try {
                const res = await fetch(apiUrl('saas', 'messages'), { 
                    method: 'DELETE', 
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                    body: JSON.stringify({ id }) 
                });
                const data = await res.json();
                if (data.success) {
                    loadMessages();
                } else {
                    alert(data.error || 'فشل حذف الرسالة');
                }
            } catch(e) {
                alert('فشل الاتصال');
            }
        }
    
        // ==========================================
        // ADS MANAGEMENT
        // ==========================================
        let allAds = [];

        function renderAds() {
            const tbody = document.getElementById('ads-body');
            tbody.innerHTML = '';
            if (allAds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">لا توجد إعلانات</td></tr>';
                return;
            }
            allAds.forEach(ad => {
                const statusHtml = ad.active ? '<span style="color:#10b981;">نشط</span>' : '<span style="color:#ef4444;">غير نشط</span>';
                tbody.innerHTML += `
                    <tr>
                        <td><img src="${ad.image}" style="max-height: 40px; border-radius: 4px;"></td>
                        <td><strong>${ad.companyName || 'بدون اسم'}</strong></td>
                        <td>${statusHtml}</td>
                        <td>
                            <button class="btn-primary" style="padding:4px 8px; font-size:0.8rem;" onclick="editAd(${ad.id})">تعديل</button>
                            <button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deleteAd(${ad.id})">حذف</button>
                        </td>
                    </tr>
                `;
            });
        }

        function openAdModal() {
            document.getElementById('ad-id').value = '';
            document.getElementById('ad-image-upload').value = '';
            document.getElementById('ad-active').checked = true;
            document.getElementById('ad-existing-image').value = '';
            document.getElementById('ad-company').value = '';
            document.getElementById('ad-image-preview').style.display = 'none';
            document.getElementById('ad-modal').style.display = 'flex';
        }

        function editAd(id) {
            const ad = allAds.find(a => a.id == id);
            if (!ad) return;
            document.getElementById('ad-id').value = ad.id;
            document.getElementById('ad-image-upload').value = ''; 
            document.getElementById('ad-active').checked = ad.active;
            document.getElementById('ad-existing-image').value = ad.image;
            document.getElementById('ad-company').value = ad.companyName || '';
            
            const preview = document.getElementById('ad-image-preview');
            preview.style.display = 'block';
            preview.querySelector('img').src = ad.image;
            
            document.getElementById('ad-modal').style.display = 'flex';
        }

        async function saveAd() {
            const id = document.getElementById('ad-id').value;
            const fileInput = document.getElementById('ad-image-upload');
            const active = document.getElementById('ad-active').checked;
            const companyName = document.getElementById('ad-company').value;
            let imageUrl = document.getElementById('ad-existing-image').value;

            const btn = document.getElementById('ad-save-btn');
            btn.disabled = true;
            btn.innerText = 'جاري الحفظ...';

            try {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('image', file);

                    const uploadRes = await fetch(apiUrl('saas', 'upload-ad'), {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN },
                        body: formData
                    });
                    const uploadData = await uploadRes.json();
                    if (uploadData.success) {
                        imageUrl = uploadData.url;
                    } else {
                        throw new Error(uploadData.error || 'فشل رفع الصورة');
                    }
                }

                if (!imageUrl) throw new Error('يجب اختيار صورة للإعلان');

                if (id) {
                    const index = allAds.findIndex(a => a.id == id);
                    if (index !== -1) {
                        allAds[index].image = imageUrl;
                        allAds[index].active = active;
                        allAds[index].companyName = companyName;
                    }
                } else {
                    allAds.push({
                        id: Date.now(),
                        image: imageUrl,
                        active: active,
                        companyName: companyName
                    });
                }

                const saveRes = await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                const saveData = await saveRes.json();
                if (!saveRes.ok || !saveData.success) {
                    throw new Error(saveData.error || 'فشل حفظ الإعلان في قاعدة البيانات');
                }

                // Reload ads from server to ensure consistency
                try {
                    const resPub = await fetch('api_index.php?r=public');
                    const dataPub = await resPub.json();
                    allAds = dataPub.ads || [];
                } catch(reloadErr) {
                    console.warn('Failed to reload ads after save:', reloadErr);
                }

                closeModal('ad-modal');
                renderAds();
            } catch (e) {
                alert('خطأ: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerText = 'حفظ الإعلان';
            }
        }

        async function deleteAd(id) {
            if (!confirm('هل أنت متأكد من الحذف؟')) return;
            allAds = allAds.filter(a => a.id != id);
            try {
                await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                renderAds();
            } catch (e) {}
        }
    </script>
</body>
</html>

