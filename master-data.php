<?php $page_title = 'السجانة ERP - البيانات الأساسية'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>السجانة ERP - البيانات الأساسية</title>
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
            --theme-orange: #f59e0b;
            --theme-red: #f87171;
            --theme-purple: #c084fc;
        }
        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0; min-height: 100vh;
            display: flex; flex-direction: column; overflow-x: hidden;
            font-family: 'Tajawal', sans-serif; color: #fff;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.8); }

        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15; animation: floatOrb 20s infinite alternate ease-in-out; }
        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-30px, 30px) scale(0.9); } 100% { transform: translate(0, 0) scale(1); } }

        .dash-main { width: 96%; max-width: 1600px; margin: 0 auto; padding: 20px 0; box-sizing: border-box; display: flex; flex-direction: column; gap: 15px; flex: 1; animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        
        .premium-header {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(217, 70, 239, 0.15), rgba(168, 85, 247, 0.15), rgba(192, 132, 252, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 12px 20px;
            display: flex; justify-content: space-between; align-items: center;
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
        

        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #38bdf8, #818cf8); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.2rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }
        .header-controls { display: flex; align-items: center; gap: 12px; }
        
        .clock-container { display: flex; flex-direction: column; align-items: center; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); min-width: 100px; }
        .clock-time { color: #38bdf8; font-size: 1rem; font-weight: 900; font-variant-numeric: tabular-nums; letter-spacing: 1px; text-shadow: 0 0 8px rgba(56,189,248,0.5); font-family: 'Outfit', sans-serif;}
        .clock-date { color: var(--text-secondary); font-size: 0.65rem; margin-top: 1px; }

        .icon-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 36px; height: 36px; border-radius: 10px; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 10px rgba(0,0,0,0.3); }

        .user-info { display: flex; align-items: center; gap: 8px; background: rgba(15,23,42,0.6); padding: 6px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); }

        /* Master Data Specific Styles */
        .md-tabs-sidebar {
            background: rgba(10, 8, 15, 0.5); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 18px; padding: 8px;
            display: flex; flex-direction: row; align-items: center; overflow-x: auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            gap: 10px; margin-bottom: 0;
        }
        .md-tabs-title { display: none; }
        .md-tab-btn { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); text-align: center; padding: 10px 15px; font-size: 0.9rem; font-weight: 700; color: #94a3b8; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; white-space: nowrap; border-radius: 12px; }
        .md-tab-btn:hover { background: rgba(255,255,255,0.08); color: #fff; border-color: rgba(255,255,255,0.1); transform: translateY(-2px); }
        .md-tab-btn.active { color: #fff; background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; box-shadow: 0 5px 15px rgba(56,189,248,0.2); }
        .md-tab-icon { font-size: 1.1rem; }

        .md-content-area { flex: 1; display: flex; flex-direction: column; width: 100%; box-sizing: border-box; }
        .md-tab-content { display: none; height: 100%; animation: fadeInSlideUp 0.4s ease-out forwards; }
        .md-tab-content.active { display: flex; flex-direction: column; }
        
        .md-card { background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 18px; padding: 18px; box-shadow: 0 10px 25px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03); margin-bottom: 15px; }
        .md-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; flex-wrap: wrap; gap: 10px; }
        .md-card-title { margin: 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        
        .btn-add { background: linear-gradient(135deg, #38bdf8, #818cf8); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; box-shadow: 0 4px 10px rgba(56, 189, 248, 0.3); transition: all 0.2s; font-family: 'Tajawal', sans-serif;}
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4); }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-size: 0.85rem; color: #94a3b8; font-weight: 700; }
        .input-group input, .input-group select { width: 100%; padding: 10px 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; font-size: 0.9rem; outline: none; box-sizing: border-box; font-family: 'Tajawal', sans-serif; transition: all 0.3s; }
        .input-group input:focus, .input-group select:focus { border-color: #38bdf8; background: rgba(0,0,0,0.4); box-shadow: 0 0 0 2px rgba(56,189,248,0.2); }
        
        .inner-tabs { display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; overflow-x: auto; }
        .inner-tab-btn { background: none; border: none; padding: 8px 15px; font-weight: 700; font-size: 0.9rem; color: #94a3b8; cursor: pointer; border-radius: 8px; white-space: nowrap; font-family: 'Tajawal', sans-serif; transition: all 0.3s;}
        .inner-tab-btn:hover { background: rgba(255,255,255,0.05); color: #fff;}
        .inner-tab-btn.active { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .inner-tab-content { display: none; }
        .inner-tab-content.active { display: block; animation: fadeInSlideUp 0.3s; }
        
        .toggle-sw { position:relative; display:inline-block; width:44px; height:24px; }
        .toggle-sw input { opacity:0; width:0; height:0; }
        .toggle-sl { position:absolute; cursor:pointer; inset:0; background:rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); transition:.3s; border-radius:24px; }
        .toggle-sl:before { content:""; position:absolute; height:16px; width:16px; left:4px; bottom:3px; background:#fff; transition:.3s; border-radius:50%; }
        .toggle-sw input:checked + .toggle-sl { background:#10b981; border-color:#10b981;}
        .toggle-sw input:checked + .toggle-sl:before { transform:translateX(20px); }
        
        .sec-hdr { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:10px; margin-bottom:15px; border-right:3px solid; background: rgba(255,255,255,0.02); }
        .sec-hdr h5 { margin:0; font-size:0.95rem; font-weight:800; }
        .item-section { margin-bottom:20px; }

        /* Tables override for glassmorphism */
        table.pro-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
        table.pro-table th { padding: 10px; text-align: right; color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 700; }
        table.pro-table td { padding: 10px; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.03); }
        table.pro-table tbody tr:hover td { background: rgba(255,255,255,0.02); }
        
        @media (max-width: 768px) {
            .premium-header { flex-direction: column; gap: 12px; }
            .header-controls { justify-content: center; width: 100%; flex-wrap: wrap; }
            .md-tabs-sidebar { padding: 8px; }
        }

        /* --- تحسينات الخطوط والتفاعلات المطلوبة --- */
        body { font-size: 1.05rem; }
        .input-group label { font-size: 1rem; font-weight: 600; }
        input, select { font-size: 1.05rem !important; }
        table.pro-table { font-size: 0.95rem !important; }
        
        .split-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        .split-panel {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            z-index: 1;
            border-radius: 12px;
        }
        .split-container:hover .split-panel {
            filter: blur(5px);
            opacity: 0.6;
            transform: scale(0.98);
        }
        .split-container .split-panel:hover,
        .split-container .split-panel:focus-within {
            filter: blur(0);
            opacity: 1;
            transform: scale(1.03);
            z-index: 10;
            box-shadow: 0 0 40px rgba(56, 189, 248, 0.15), inset 0 0 15px rgba(255, 255, 255, 0.05);
        }
        
        /* تأثيرات حركية وضوئية للأزرار */
        .btn-action:hover, .btn-add:hover, .hover-glow:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 10px 25px rgba(168, 85, 247, 0.5) !important;
            filter: brightness(1.2);
            animation: pulse-glow 1.5s infinite;
        }
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 15px rgba(168,85,247,0.4); }
            50% { box-shadow: 0 0 30px rgba(168,85,247,0.8); }
            100% { box-shadow: 0 0 15px rgba(168,85,247,0.4); }
        }
        /* ------------------------------------------- */
    </style>

</head>
<body>
    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="dash-main">

        <!-- Unified Premium Header -->
        <?php include 'includes/header.php'; ?>

        <aside class="md-tabs-sidebar">
            <button class="md-tab-btn active" onclick="openTab(event, 'tab-warehouses')"><span class="md-tab-icon">🏢</span> المخازن</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-customers')"><span class="md-tab-icon">👥</span> العملاء</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-suppliers')"><span class="md-tab-icon">🏭</span> الموردين</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-banks')"><span class="md-tab-icon">🏦</span> البنوك</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-safes')"><span class="md-tab-icon">💰</span> الخزن</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-items'); setTimeout(openItemForm, 50);"><span class="md-tab-icon">📦</span> الأصناف والوحدات</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-employees')"><span class="md-tab-icon">👥</span> الموظفين</button>
            <button class="md-tab-btn" onclick="openTab(event, 'tab-costcenters')"><span class="md-tab-icon">📊</span> مراكز التكلفة</button>
            <div style="flex: 1;"></div>
            <button class="md-tab-btn" onclick="goBackOrExit()" style="color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.1); font-weight: 900; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);"><span class="md-tab-icon">🚪</span> خروج / رجوع</button>
        </aside>

        <main class="md-content-area">
            
            <div id="tab-warehouses" class="md-tab-content active">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">🏢 قائمة المخازن</h3>
                        <button class="btn-add" onclick="openWarehouseForm()">+ إضافة مخزن جديد</button>
                    </div>
                    
                    <div id="warehouse-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">إضافة/تعديل مخزن</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-warehouse" onclick="cancelEditWarehouse()">إلغاء التعديل</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="warehouse-id">
                            <div class="input-group"><label>اسم المخزن</label><input type="text" id="warehouse-name" placeholder="مثال: مخزن السجانة الرئيسي"></div>
                            <div class="input-group"><label>النوع</label><select id="warehouse-type"><option value="مستودع رئيسي">مستودع رئيسي</option><option value="معرض (نقطة بيع)">معرض (نقطة بيع)</option><option value="مخزن بضاعة تالفة">مخزن بضاعة تالفة</option></select></div>
                            <div class="input-group"><label>درجة السرية</label><select id="warehouse-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveWarehouse()">💾 حفظ البيانات</button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>اسم المخزن</th><th>النوع</th><th>الإجراء</th></tr></thead>
                            <tbody id="warehouses-tbody"><!-- سيتم إضافة المخازن هنا --></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-customers" class="md-tab-content">
                <div class="md-card">
                    <div id="customer-form" class="md-card" style="display: block; margin-bottom: 25px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 1.1rem; color: var(--primary-accent); display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.2rem;">👥</span> إضافة / تعديل بيانات عميل
                            </h4>
                            <button onclick="clearCustomerForm()" class="btn-action" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">🔄 تفريغ الحقول (عميل جديد)</button>
                        </div>
                        <div class="form-grid">
                            <div class="input-group"><label>اسم العميل / الشركة</label><input type="text" id="cust-name" placeholder="مثال: أحمد للتجارة"></div>
                            <div class="input-group"><label>رقم الهاتف</label><input type="text" id="cust-phone" placeholder="0024912300675"></div>
                            <div class="input-group"><label>رقم الواتساب</label><input type="text" id="cust-whatsapp" placeholder="0024912300675"></div>
                            <div class="input-group"><label>الايميل (البريد الإلكتروني)</label><input type="email" id="cust-email" placeholder="example@email.com"></div>
                            <div class="input-group" style="grid-column: span 2;"><label>العنوان</label><input type="text" id="cust-address" placeholder="مثال: الخرطوم، السجانة"></div>
                            <div class="input-group"><label>العملة</label><select id="cust-currency"><option value="SDG">SDG</option><option value="USD">USD</option><option value="SAR">SAR</option><option value="AED">AED</option></select></div>
                            <div class="input-group"><label>الرقم الضريبي</label><input type="text" id="cust-tax" placeholder="الرقم الضريبي للعميل"></div>
                            <div class="input-group"><label>رقم الحساب</label><input type="text" id="cust-account-no" placeholder="رقم حساب العميل"></div>
                            <div class="input-group"><label>رصيد افتتاحي (عليه)</label><input type="number" id="cust-opening-debit" placeholder="0.00"></div>
                            <div class="input-group"><label>رصيد افتتاحي (له)</label><input type="number" id="cust-opening-credit" placeholder="0.00"></div>
                            <div class="input-group"><label style="color: var(--danger-color);">حد الائتمان (أقصى سقف)</label><input type="number" id="cust-limit" placeholder="المبلغ المسموح للدين"></div>
                            <div class="input-group"><label>درجة السرية</label><select id="cust-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                            <div class="input-group"><label>حالة العميل (القائمة السوداء)</label><select id="cust-status" style="font-weight: bold;"><option value="active" style="color: var(--success-color);">🟢 نشط (عادي)</option><option value="vip" style="color: var(--primary-accent);">🌟 VIP (مميز)</option><option value="blacklisted" style="color: var(--danger-color);">🔴 قائمة سوداء (محظور)</option></select></div>
                            <div class="input-group"><label>كود العميل</label><input type="text" id="cust-id" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;" placeholder="تلقائي عند الحفظ"></div>
                            <div class="input-group"><label>الشخص المسؤول</label><input type="text" id="cust-contact" placeholder="اسم المندوب / الشخص المسؤول"></div>
                            <div class="input-group" style="grid-column: 1 / -1;"><label>ملاحظات إضافية</label><textarea id="cust-notes" placeholder="أضف أي ملاحظات عن العميل هنا..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); outline: none; font-family: inherit; resize: none; height: 50px; box-sizing: border-box;"></textarea></div>

                            <!-- بيانات الحساب البنكي للعميل -->
                            <div style="grid-column: 1 / -1; margin-top: 10px; padding: 15px; border: 1px solid var(--glass-border); border-radius: 12px; background: rgba(0,0,0,0.1);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h4 style="margin: 0; color: var(--primary-accent); display:flex; align-items:center; gap:8px;"><span>🏦</span> بيانات الحسابات البنكية للعميل</h4>
                                    <button type="button" onclick="addCustomerBankAccount()" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: bold;">+ إضافة حساب آخر</button>
                                </div>
                                <div id="cust-bank-accounts-container" style="display: flex; flex-direction: column; gap: 15px;">
                                    <!-- Bank accounts will be injected here -->
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn-add" style="background: var(--success-color); padding: 10px 25px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="saveCustomer()">💾 حفظ العميل</button>
                        </div>
                    </div>

                    <!-- Customer Search Bar -->
                    <div style="margin-bottom: 15px; display: flex; align-items: center;">
                        <div class="input-group" style="width: 100%; max-width: 500px; position: relative; margin-bottom: 0;">
                            <span style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; pointer-events: none;">🔍</span>
                            <input type="text" id="customer-search-input" onkeyup="filterCustomers()" placeholder="ابحث ذكياً باسم العميل، رقم الهاتف، أو الإيميل..." style="padding-right: 45px; border-radius: 12px; border: 1px solid rgba(56,189,248,0.3); background: rgba(0,0,0,0.3); font-size: 1rem; color: #fff; width: 100%; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);">
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>اسم العميل</th><th>رقم الهاتف</th><th>الرصيد الحالي</th><th>حد الائتمان (المسموح)</th><th>الإجراء</th></tr></thead>
                            <tbody id="customers-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Suppliers Tab -->
            <div id="tab-suppliers" class="md-tab-content">
                <div class="md-card">
                    <div id="supplier-form" class="md-card" style="display: block; margin-bottom: 25px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px; margin-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 1.1rem; color: var(--primary-accent); display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.2rem;">🏭</span> إضافة / تعديل بيانات مورد
                            </h4>
                            <button onclick="clearSupplierForm()" class="btn-action" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">🔄 تفريغ الحقول (مورد جديد)</button>
                        </div>
                        <div class="form-grid">
                            <div class="input-group"><label>اسم المورد / المصنع</label><input type="text" id="sup-name" placeholder="مثال: مصنع جياد"></div>
                            <div class="input-group"><label>رقم الهاتف</label><input type="text" id="sup-phone" placeholder="0024912300675"></div>
                            <div class="input-group"><label>رقم الواتساب</label><input type="text" id="sup-whatsapp" placeholder="0024912300675"></div>
                            <div class="input-group"><label>الايميل (البريد الإلكتروني)</label><input type="email" id="sup-email" placeholder="example@email.com"></div>
                            <div class="input-group" style="grid-column: span 2;"><label>العنوان</label><input type="text" id="sup-address" placeholder="موقع المصنع / المعرض"></div>
                            <div class="input-group"><label>العملة</label><select id="sup-currency"><option value="SDG">SDG</option><option value="USD">USD</option><option value="SAR">SAR</option><option value="AED">AED</option></select></div>
                            <div class="input-group"><label>الرقم الضريبي</label><input type="text" id="sup-tax" placeholder="الرقم الضريبي للمورد"></div>
                            <div class="input-group"><label>رقم الحساب</label><input type="text" id="sup-account-no" placeholder="رقم حساب المورد"></div>
                            <div class="input-group"><label>رصيد افتتاحي (عليه)</label><input type="number" id="sup-opening-debit" placeholder="0.00"></div>
                            <div class="input-group"><label>رصيد افتتاحي (له)</label><input type="number" id="sup-opening-credit" placeholder="0.00"></div>
                            <div class="input-group"><label style="color: var(--danger-color);">حد الائتمان (أقصى سقف)</label><input type="number" id="sup-limit" placeholder="أقصى سقف"></div>
                            <div class="input-group"><label>درجة السرية</label><select id="sup-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                            <div class="input-group"><label>حالة المورد</label><select id="sup-status" style="font-weight: bold;"><option value="active" style="color: var(--success-color);">🟢 مورد نشط</option><option value="blacklisted" style="color: var(--danger-color);">🔴 قائمة سوداء</option></select></div>
                            <div class="input-group"><label>كود المورد</label><input type="text" id="sup-id" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;" placeholder="تلقائي عند الحفظ"></div>
                            <div class="input-group" style="grid-column: 1 / -1;"><label>ملاحظات إضافية</label><textarea id="sup-notes" placeholder="أي ملاحظات حول المورد..." style="width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--bg-color); color: var(--text-primary); outline: none; font-family: inherit; resize: none; height: 50px; box-sizing: border-box;"></textarea></div>
                        </div>
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="btn-add" style="background: var(--success-color); padding: 10px 25px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="saveSupplier()">💾 حفظ بيانات المورد</button>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="tab-suppliers-table">
                            <thead><tr><th>اسم المورد</th><th>الشخص المرجعي / الهاتف</th><th>الرصيد الحالي</th><th>حد الائتمان</th><th>الإجراء</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Banks Tab -->
            <div id="tab-banks" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">🏦 البنوك</h3><button class="btn-add" onclick="openBankForm()">+ إضافة حساب بنكي</button></div>
                    <div id="bank-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="bank-form-title">إضافة حساب بنكي</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء</button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="bank-code" placeholder="رقم الحساب أو الخزينة"></div>
                            <div class="input-group"><label>الاسم</label><input type="text" id="bank-name" placeholder="مثال: الخزينة الرئيسية"></div>
                            <div class="input-group" id="bank-branch-group"><label>الفرع</label><input type="text" id="bank-branch" placeholder="مثال: فرع السجانة"></div>
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="bank-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد الافتتاحي</label><input type="date" id="bank-opening-date"></div>
                            <div class="input-group"><label>رقم حساب الأستاذ (GL)</label><input type="text" id="bank-gl-account" placeholder="مثال: 110101"></div>
                            <div class="input-group bank-only-field"><label>IBAN</label><input type="text" id="bank-iban" placeholder="SA..."></div>
                            <div class="input-group bank-only-field"><label>SWIFT</label><input type="text" id="bank-swift" placeholder="BOKS..."></div>
                            
                            <div class="input-group"><label>العملة</label><select id="bank-currency" class="system-currency-select"><option value="SDG">SDG</option><option value="USD">USD</option></select></div>
                            
                            
                            <div class="input-group"><label>درجة السرية</label><select id="bank-confidentiality"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع المسموحة</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="bank-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="bank-inactive"> عدم تفعيل الحساب</label>
                            
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeBankForm()">✖ إلغاء</button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveBank()">💾 حفظ</button></div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="banks-table">
                            <thead><tr><th>الرقم</th><th>اسم الحساب</th><th>النوع</th><th>العملة</th><th>الرصيد الافتتاحي</th><th>الإجراء</th></tr></thead>
                            <tbody id="banks-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            

<div id="tab-safes" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">🏦 الخزن</h3><button class="btn-add" onclick="openSafeForm()">+ إضافة خزينة</button></div>
                    <div id="safe-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px; margin-bottom:20px;"><h4 class="neon-text-primary" style="margin:0;" id="safe-form-title">إضافة خزينة</h4><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء</button></div>
                        
                        <div class="form-grid">
                            <div class="input-group"><label>الرقم</label><input type="text" id="safe-code" placeholder="رقم الحساب أو الخزينة"></div>
                            <div class="input-group"><label>الاسم</label><input type="text" id="safe-name" placeholder="مثال: الخزينة الرئيسية"></div>
                            
                            <div class="input-group"><label>الرصيد الافتتاحي</label><input type="number" id="safe-opening-balance" placeholder="0.00"></div>
                            <div class="input-group"><label>تاريخ الرصيد الافتتاحي</label><input type="date" id="safe-opening-date"></div>
                            <div class="input-group"><label>رقم حساب الأستاذ (GL)</label><input type="text" id="safe-gl-account" placeholder="مثال: 110101"></div>
                            
                            
                            <div class="input-group" id="safe-responsible-group"><label>المسؤول</label><select id="safe-responsible"><option value="">-- اختر --</option></select></div>
                            <div class="input-group"><label>العملة</label><select id="safe-currency" class="system-currency-select"><option value="SDG">SDG</option><option value="USD">USD</option></select></div>
                            <div class="input-group" id="safe-max-limit-group"><label>الحد الأعلى</label><input type="number" id="safe-max-limit" placeholder="0.00"></div>
                            <div class="input-group" id="safe-min-limit-group"><label>الحد الأدنى</label><input type="number" id="safe-min-limit" placeholder="0.00"></div>
                            <div class="input-group"><label>درجة السرية</label><select id="safe-confidentiality"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                        </div>
                        <div style="margin-top:20px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;"><h5 style="color:var(--text-secondary); margin-top:0; margin-bottom:10px;">طرق الدفع المسموحة</h5>
                            <div style="display:flex; gap:15px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:10px; border-radius:8px;">
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="cash" checked> كاش</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="pos" checked> شبكة</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="transfer" checked> تحويل</label>
                                <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" class="safe-payment-method" value="check"> شيكات</label>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; margin-top:20px; align-items:center; flex-wrap:wrap; border-top:1px dashed rgba(255,255,255,0.1); padding-top:15px;">
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-inactive"> عدم تفعيل الحساب</label>
                            <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="safe-modifier"> المعدل</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;"><button class="btn-text" style="color:var(--danger-color);" onclick="closeSafeForm()">✖ إلغاء</button><button type="button" class="btn-add" style="background:var(--success-color); padding:8px 20px;" onclick="saveSafe()">💾 حفظ</button></div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table" id="safes-table">
                            <thead><tr><th>الرقم</th><th>اسم الخزينة</th><th>النوع</th><th>العملة</th><th>الرصيد الافتتاحي</th><th>الإجراء</th></tr></thead>
                            <tbody id="safes-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Items Tab -->
            <div id="tab-items" class="md-tab-content">
                <div class="md-card">
                    <!-- Header and redundant KPIs removed to utilize vertical space -->
                    <!-- Quick Actions moved to the item form -->

                    <!-- Main Inner Tabs removed as requested -->

                    <div id="inner-products" class="inner-tab-content active">
                        
                        <!-- Premium Item Form Modal -->
                        <div id="item-form" class="md-card" style="display: none; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                            <!-- HEADER BAR -->
                            <div style="background:linear-gradient(135deg,rgba(56,189,248,0.2),rgba(15,23,42,0.95)); padding:12px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom:1px solid rgba(56,189,248,0.3);">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <div style="width:50px; height:50px; background:linear-gradient(135deg, #38bdf8, #818cf8); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; box-shadow: 0 4px 15px rgba(56,189,248,0.4);">📦</div>
                                    <div><h3 id="form-mode-indicator" style="margin:0; color:#fff; font-size:1.4rem; font-weight:800; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">إضافة صنف جديد</h3><small style="color:#cbd5e1; font-size: 0.85rem;">إدارة بطاقة الصنف وتفاصيل التسعير والوحدات</small></div>
                                </div>
                                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items: center;">
                                    <!-- Low Stock Mini -->
                                    <div class="neon-glass-panel neon-border-danger" style="padding: 5px 12px; display: flex; align-items: center; gap: 8px; border-radius: 8px; border: 1px solid rgba(244,63,94,0.3); margin-left: 10px;">
                                        <span style="font-size:1rem; background: rgba(244,63,94,0.1); width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">⚠️</span>
                                        <div>
                                            <span style="color:var(--text-secondary); font-size:0.7rem; display: block; line-height: 1; margin-bottom: 3px;">النواقص</span>
                                            <strong id="kpi-low-stock" class="neon-text-danger" style="font-size:0.95rem; font-weight:800; line-height: 1;">0 صنف</strong>
                                        </div>
                                    </div>
                                    
                                    <button onclick="openInnerTab(event, 'inner-categories')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(168,85,247,0.2), rgba(88,28,135,0.6)); border: 1px solid rgba(168,85,247,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">🗂️</span> أنواع الأصناف
                                    </button>
                                    <button onclick="openInnerTab(event, 'inner-units')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">📐</span> الوحدات
                                    </button>
                                    <button onclick="alert('جاري التجهيز للطباعة...')" class="btn-action hover-glow" style="display:flex; align-items:center; gap:5px; justify-content:center; padding:10px 20px; font-size:0.95rem; font-weight:bold; border-radius: 8px; background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(6,78,59,0.6)); border: 1px solid rgba(52,211,153,0.5); color: #fff; cursor: pointer;">
                                        <span style="font-size:1.1rem;">🖨️</span> طباعة باركود
                                    </button>
                                    <button class="btn-add hover-glow" style="background:linear-gradient(135deg, var(--success-color), #059669); padding:10px 25px; font-size: 1rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.3); border: 1px solid rgba(16,185,129,0.5); color: #fff; cursor: pointer;" onclick="saveItem()">💾 حفظ الصنف</button>
                                    <button onclick="closeItemForm()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.5); padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:bold; transition: all 0.3s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">✖ إلغاء</button>
                                </div>
                            </div>
                            
                            <div style="padding:25px; background: rgba(0,0,0,0.2);">

                                <div class="split-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                                    
                                    <!-- SEC 1: General -->
                                    <div class="neon-glass-panel split-panel" style="padding: 20px; border-top: 3px solid #38bdf8; background: linear-gradient(to bottom, rgba(56,189,248,0.05), transparent);">
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom: 20px;">
                                            <span style="font-size:1.5rem;">📋</span><h4 style="margin:0; color:#38bdf8; font-weight: 800;">البيانات العامة والتعريف</h4>
                                        </div>
                                        
                                        <div style="display:flex; gap:20px;">
                                            <div id="item-qr-box" style="width:100px; height:100px; border:2px dashed rgba(56,189,248,0.4); border-radius:14px; display:flex; flex-direction:column; justify-content:center; align-items:center; background:rgba(0,0,0,0.4); flex-shrink:0; transition: all 0.3s;"><span style="font-size:2rem;">📱</span><span style="font-size:0.75rem; color:#38bdf8; margin-top:5px; font-weight:bold;">QR تلقائي</span></div>
                                            <div class="form-grid" style="flex:1; grid-template-columns: 1fr;">
                                                <div class="input-group"><label style="color:#fff;">اسم الصنف <span style="color:#ef4444;">*</span></label><input type="text" id="item-name-input" placeholder="مثال: سيخ 3 لنيه ماستر" style="border-color: rgba(56,189,248,0.5); background: rgba(0,0,0,0.3);"></div>
                                                <div style="display:flex; gap:10px;">
                                                    <div class="input-group" style="flex:1;"><label>كود الصنف (SKU)</label><input type="text" id="item-code" placeholder="تلقائي إذا تُرك فارغاً" style="color:#38bdf8; font-weight:bold;"></div>
                                                    <div class="input-group" style="flex:1;"><label>الاسم اللاتيني</label><input type="text" id="item-name-latin" placeholder="Rebar 3 Linia"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-grid" style="margin-top: 15px;">
                                            <div class="input-group"><label>الباركود</label><input type="text" id="item-barcode" placeholder="امسح بالليزر..."></div>
                                            <div class="input-group"><label>الماركة / المنشأ</label><input type="text" id="item-brand" placeholder="مثال: تركي، جياد"></div>
                                            <div class="input-group"><label>الفئة الرئيسية</label><div style="display:flex; gap:5px;"><select id="item-category" style="flex:1;"></select><button class="btn-text" style="color:#a855f7; background:rgba(168,85,247,0.1); border-radius:8px; padding:0 10px;" onclick="quickAddCategory()" title="إضافة فئة">➕</button></div></div>
                                            <div class="input-group"><label>الفئة الفرعية (للجرد)</label><input type="text" id="item-sub-category" placeholder="مثال: كمر 12 متر"></div>
                                            <div class="input-group"><label>طبيعة الصنف</label><select id="item-nature"><option value="مخزني">📦 مخزني (يتم جرده)</option><option value="خدمي">🔧 خدمي (لا يجرد)</option><option value="تجميعي">🏗️ تجميعي</option></select></div>
                                            <div class="input-group"><label>حالة التفعيل</label><div style="display:flex; align-items:center; gap:10px; padding:10px; background: rgba(0,0,0,0.2); border-radius: 8px;"><label class="toggle-sw"><input type="checkbox" id="item-active-toggle" checked onchange="updateToggleLabel()"><span class="toggle-sl"></span></label><span id="item-active-label" style="color:var(--success-color); font-weight:bold; font-size:0.9rem;">🟢 نشط</span></div></div>
                                        </div>
                                    </div>

                                    <!-- Right Column Stack -->
                                    <div class="split-panel" style="display: flex; flex-direction: column; gap: 20px;">
                                        
                                        <!-- SEC 2: Pricing & Tax -->
                                        <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #34d399; background: linear-gradient(to bottom, rgba(52,211,153,0.05), transparent);">
                                            <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                <span style="font-size:1.5rem;">💰</span><h4 style="margin:0; color:#34d399; font-weight: 800;">التسعير والضرائب</h4>
                                            </div>
                                            <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                                                <div class="input-group"><label>سعر البيع الافتراضي (SDG)</label><input type="number" id="item-selling-price" placeholder="0.00" style="font-weight:bold; color:var(--success-color); border-color: rgba(52,211,153,0.4); font-size: 1.1rem; background: rgba(52,211,153,0.05);"></div>
                                                <div class="input-group"><label>الحد الأقصى للخصم %</label><input type="number" id="item-max-discount" placeholder="0" max="100"></div>
                                                <div class="input-group"><label>خاضع للضريبة</label><div style="padding:10px; background:rgba(0,0,0,0.2); border-radius:8px;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;"><input type="checkbox" id="item-taxable" onchange="document.getElementById('item-tax-rate').disabled=!this.checked" style="width:18px; height:18px; accent-color:#34d399;"> نعم، خاضع</label></div></div>
                                                <div class="input-group"><label>نسبة الضريبة %</label><input type="number" id="item-tax-rate" placeholder="0" disabled></div>
                                            </div>
                                        </div>

                                        <!-- SEC 3 & 4: Costing & Warehousing in one row inside column -->
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                            <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #f59e0b; background: linear-gradient(to bottom, rgba(245,158,11,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                    <span style="font-size:1.2rem;">📊</span><h5 style="margin:0; color:#f59e0b; font-weight: 800;">التكلفة والأرصدة</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px; grid-column: 1 / -1;"><label style="color:#f59e0b;">طريقة التكلفة</label>
                                                    <div style="display:flex; gap:10px; flex-wrap:wrap; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px;">
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="lastPurchase" checked style="accent-color:#f59e0b;"> آخر شراء</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="highest" style="accent-color:#f59e0b;"> الأعلى</label>
                                                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size: 0.85rem;"><input type="radio" name="cost_method" value="opening" style="accent-color:#f59e0b;"> الافتتاحي</label>
                                                    </div>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>الكمية الافتتاحية</label><input type="number" id="item-opening-qty" placeholder="0"></div>
                                                <div class="input-group"><label>تكلفة الافتتاحي</label><input type="number" id="item-opening-value" placeholder="0.00"></div>
                                            </div>

                                            <div class="neon-glass-panel" style="padding: 20px; border-top: 3px solid #ef4444; background: linear-gradient(to bottom, rgba(239,68,68,0.05), transparent);">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                                    <span style="font-size:1.2rem;">🏭</span><h5 style="margin:0; color:#ef4444; font-weight: 800;">حدود المخزون</h5>
                                                </div>
                                                <div class="input-group" style="margin-bottom: 10px;"><label>مكان الصنف / الرف</label><input type="text" id="item-bin-location" placeholder="ممر 3، رف ب"></div>
                                                <div style="display:flex; gap:10px; margin-bottom: 10px;">
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأعلى</label><input type="number" id="item-max-limit" placeholder="500"></div>
                                                    <div class="input-group" style="flex:1;"><label style="color:#ef4444; font-size: 0.75rem;">الحد الأدنى</label><input type="number" id="item-min-limit" placeholder="20"></div>
                                                </div>
                                                <div class="input-group"><label style="color:#f59e0b;">حد إعادة الطلب</label><input type="number" id="item-reorder-level" placeholder="50"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- SEC 5: Unit Matrix (Full Width) -->
                                <div class="neon-glass-panel" style="margin-top: 20px; padding: 25px; border-top: 3px solid #a855f7; background: linear-gradient(to bottom, rgba(168,85,247,0.05), transparent); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="font-size:1.8rem; filter: drop-shadow(0 0 8px rgba(168,85,247,0.5));">📐</span><h4 style="margin:0; color:#a855f7; font-weight: 800; font-size: 1.3rem;">مصفوفة الوحدات والتحويل</h4>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:15px; background: rgba(0,0,0,0.3); padding: 10px 15px; border-radius: 12px; border: 1px solid rgba(168,85,247,0.3); box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);">
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 1rem;">الوحدة الأساسية <span style="color:#ef4444;">*</span></label>
                                            <div style="display:flex; align-items:center; gap:5px;">
                                                <select id="item-base-unit" onchange="updateBaseUnitRow()" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.1rem; padding: 5px; outline:none; width: 150px; text-align: center; transition: all 0.3s;" onfocus="this.style.borderBottomColor='#d8b4fe'">
                                                    <option value="">-- اختر الوحدة --</option>
                                                </select>
                                                <button class="btn-text" style="color:#38bdf8; background:rgba(56,189,248,0.1); border-radius:8px; padding:0 8px; font-size:0.9rem;" onclick="quickAddUnit()" title="إضافة وحدة سريعة">➕</button>
                                            </div>
                                            <label style="color:#a855f7; font-weight:bold; margin:0; font-size: 1rem; margin-right: 20px;">الشيلة</label>
                                            <input type="number" id="item-bundle-factor" placeholder="مثال: 15" value="1" min="1" style="border:none; border-bottom: 2px solid #a855f7; background:transparent; color:#fff; font-weight:bold; font-size: 1.1rem; padding: 5px; outline:none; width: 80px; text-align: center; transition: all 0.3s;" onfocus="this.style.borderBottomColor='#d8b4fe'">
                                        </div>
                                    </div>
                                    
                                    <div style="background:rgba(15,23,42,0.8); border-radius:14px; border:1px solid rgba(168,85,247,0.2); overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(168,85,247,0.05);">
                                            <span style="color:#cbd5e1; font-weight: 600;">جدول تحويل الوحدات وتسعيرها (المعادل بالوحدة الأساسية)</span>
                                            <button class="btn-text" style="color:#fff; font-weight:bold; background:linear-gradient(135deg, #a855f7, #9333ea); padding:8px 15px; border-radius:8px; box-shadow: 0 4px 10px rgba(168,85,247,0.3); border: none; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" onclick="addUnitRow()"><span style="font-size: 1.2rem;">+</span> إضافة وحدة جديدة</button>
                                        </div>
                                        <div class="table-responsive" style="overflow-x: auto;">
                                            <table class="pro-table" style="width: 100%; min-width: 1000px;">
                                                <thead><tr style="background: rgba(0,0,0,0.2);">
                                                    <th style="padding: 15px; width: 40px; text-align: center;">م</th>
                                                    <th style="padding: 15px;">اسم الوحدة</th>
                                                    <th style="padding: 15px; text-align: center;">المعادل (Factor)</th>
                                                    <th style="padding: 15px;">باركود</th>
                                                    <th style="padding: 15px; color: #34d399;">سعر نقدي</th>
                                                    <th style="padding: 15px; color: #60a5fa;">سعر آجل</th>
                                                    <th style="padding: 15px; color: #f87171;">أقل سعر</th>
                                                    <th style="padding: 15px;">وزن (Kg)</th>
                                                    <th style="padding: 15px; text-align: center;">إجراء</th>
                                                </tr></thead>
                                                <tbody id="units-matrix-body">
                                                    <tr style="background:rgba(52,211,153,0.05); border-right:4px solid var(--success-color); transition: background 0.3s;" onmouseover="this.style.background='rgba(52,211,153,0.1)'" onmouseout="this.style.background='rgba(52,211,153,0.05)'">
                                                        <td style="text-align: center; font-weight: bold; color: var(--text-secondary);">1</td>
                                                        <td><input type="text" id="base-unit-name-display" value="" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; font-size: 1.1rem; outline: none;"></td>
                                                        <td><input type="number" value="1" disabled style="width:100%; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; font-size:1.2rem; outline: none;"></td>
                                                        <td><input type="text" placeholder="باركود الوحدة..." style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px; border-radius:8px; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#34d399; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#34d399'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#60a5fa; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#60a5fa'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#f87171; padding:10px; border-radius:8px; font-weight:bold; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#f87171'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td><input type="number" placeholder="0.00" style="width:100%; background:var(--bg-color); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px; border-radius:8px; transition: border-color 0.3s; outline: none;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></td>
                                                        <td style="text-align: center;"><span style="font-size:0.8rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); border: 1px solid rgba(52,211,153,0.4); padding:6px 12px; border-radius:6px; display: inline-block; box-shadow: 0 2px 5px rgba(52,211,153,0.2);">وحدة أساسية</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEC 6: Description -->
                                <div class="neon-glass-panel" style="margin-top: 20px; padding: 20px; border-top: 3px solid #94a3b8; background: linear-gradient(to bottom, rgba(148,163,184,0.05), transparent);">
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                                        <span style="font-size:1.2rem;">📝</span><h5 style="margin:0; color:#94a3b8; font-weight: 800;">الوصف والملاحظات</h5>
                                    </div>
                                    <textarea id="item-description" placeholder="أضف وصفاً أو ملاحظات داخلية عن الصنف..." style="width:100%; min-height:100px; padding:15px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); color:#fff; font-family:inherit; resize:vertical; box-sizing:border-box; transition: border-color 0.3s; font-size: 0.95rem; outline: none;" onfocus="this.style.borderColor='#94a3b8'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- List Data Table -->
                        <div class="md-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; background:rgba(0,0,0,0.3); padding:8px 15px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); flex:1; max-width:400px;">
                                    <span>🔍</span>
                                    <input type="text" id="item-search" onkeyup="filterItemsTable()" placeholder="ابحث بالاسم أو الكود..." style="background:transparent; border:none; color:#fff; width:100%; outline:none;">
                                </div>
                            </div>
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="pro-table" id="main-items-table">
                                    <thead><tr><th>الكود</th><th>الصنف</th><th>الفئة</th><th>الوحدة الأساسية</th><th>سعر البيع</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                                    <tbody id="items-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div id="inner-categories" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-purple" style="margin-bottom:20px;">
                            <h4 style="margin-top:0; color:#a855f7;">إضافة/تعديل فئة</h4>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الفئة</label><input type="text" id="cat-name-input" placeholder="مثال: أسمنت"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:var(--success-color); padding:10px 20px; height:fit-content;" onclick="saveCategory()">💾 حفظ الفئة</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>اسم الفئة</th><th>عدد الأصناف</th><th>الإجراء</th></tr></thead>
                                <tbody id="categories-table-body">
                                    <!-- Dynamic categories -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Units Tab -->
                    <div id="inner-units" class="inner-tab-content">
                        <div class="md-card neon-glass-panel neon-border-info" style="margin-bottom:20px; border-color: rgba(56,189,248,0.3);">
                            <h4 style="margin-top:0; color:#38bdf8;">إضافة/تعديل وحدة</h4>
                            <div class="form-grid">
                                <div class="input-group"><label>اسم الوحدة</label><input type="text" id="unit-name-input" placeholder="مثال: دزينة، صندوق، حبة"></div>
                                <div style="display:flex; align-items:flex-end;">
                                    <button class="btn-add" style="background:linear-gradient(135deg, rgba(56,189,248,0.2), rgba(14,165,233,0.6)); border: 1px solid rgba(56,189,248,0.5); padding:10px 20px; height:fit-content;" onclick="saveGlobalUnit()">💾 حفظ الوحدة</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="pro-table">
                                <thead><tr><th>اسم الوحدة</th><th>الإجراء</th></tr></thead>
                                <tbody id="units-table-body">
                                    <!-- Dynamic units -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Tab -->
            <div id="tab-employees" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header"><h3 class="md-card-title">👥 الموظفين والصلاحيات</h3><button class="btn-add" onclick="openNewEmployeeForm()">+ إضافة موظف جديد</button></div>
                    <div id="employee-form" class="md-card" style="display: none;">
                        <h4 style="margin-top:0; color:var(--primary-accent);">إضافة/تعديل موظف</h4>
                        <div class="form-grid">
                            <div class="input-group"><label>الكود</label><input type="text" id="emp-id" disabled></div>
                            <div class="input-group"><label>الاسم</label><input type="text" id="emp-name"></div>
                            <div class="input-group"><label>رقم الهاتف</label><input type="text" id="emp-phone"></div>
                            <div class="input-group"><label>كلمة المرور</label><input type="text" id="emp-password"></div>
                            <div class="input-group"><label>الدور</label><select id="emp-role"><option value="admin">مدير</option><option value="cashier">كاشير</option></select></div>
                            <div class="input-group"><label>درجة السرية</label><select id="emp-security"><option value="1">1 (الأقل سرية)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 (الأعلى سرية)</option></select></div>
                            <div class="input-group"><label>الحالة</label><select id="emp-status"><option value="active">نشط</option><option value="inactive">موقوف</option></select></div>
                        </div>
                        <button type="button" class="btn-add" style="background:var(--success-color);" onclick="saveEmployee()">💾 حفظ</button>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>الاسم</th><th>الدور</th><th>الهاتف</th><th>السرية</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                            <tbody id="tab-employees-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-costcenters" class="md-tab-content">
                <div class="md-card">
                    <div class="md-card-header">
                        <h3 class="md-card-title">📊 قائمة مراكز التكلفة</h3>
                        <button class="btn-add" onclick="openCostCenterForm()">+ إضافة مركز تكلفة جديد</button>
                    </div>
                    
                    <div id="costcenter-form" class="md-card" style="display: none;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0;">إضافة/تعديل مركز تكلفة</h4>
                            <button class="btn-add" style="background: var(--danger-color); padding: 5px 15px; display: none;" id="btn-cancel-costcenter" onclick="closeCostCenterForm()">إلغاء التعديل</button>
                        </div>
                        <div class="form-grid">
                            <input type="hidden" id="costcenter-id">
                            <div class="input-group"><label>رمز/كود المركز</label><input type="text" id="costcenter-code" placeholder="تلقائي عند الحفظ" disabled style="background: rgba(0,0,0,0.05); font-weight: bold; cursor: not-allowed; border-color: transparent;"></div>
                            <div class="input-group"><label>اسم مركز التكلفة</label><input type="text" id="costcenter-name" placeholder="مثال: مشروع الخرطوم الجديد"></div>
                            <div class="input-group"><label>الوصف / ملاحظات</label><input type="text" id="costcenter-desc" placeholder="..."></div>
                        </div>
                        <button type="button" class="btn-add" style="background: var(--success-color);" onclick="saveCostCenter()">💾 حفظ البيانات</button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="pro-table">
                            <thead><tr><th>كود المركز</th><th>الاسم</th><th>الوصف</th><th>الإجراء</th></tr></thead>
                            <tbody id="costcenters-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    <script src="js/app.js?v=1783054968.4328"></script>
    <script>
        document.addEventListener('DBReady', () => {
            if (!localStorage.getItem('authToken')) window.location.href = 'login.php';
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                const tabBtn = document.querySelector(`button[onclick*="${tabId}"]`);
                if (tabBtn) tabBtn.click();
            }
            clearCustomerForm();
            clearSupplierForm();
            renderCustomers();
            renderSuppliers();
            renderEmployees();
            if(typeof renderBanks === 'function') renderBanks();
            if(typeof renderSafes === 'function') renderSafes();
            if(typeof renderItems === 'function') renderItems();
            renderWarehouses();
            if(typeof renderCostCenters === 'function') renderCostCenters();
        });

        function openTab(evt, tabId) {
            document.querySelectorAll('.md-tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.md-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function openInnerTab(evt, tabId) {
            const container = evt.currentTarget.closest('.md-card');
            container.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
            container.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function switchSupplierTab(event, tabId) {
            const form = document.getElementById('supplier-form');
            form.querySelectorAll('.inner-tab-btn').forEach(btn => btn.classList.remove('active'));
            form.querySelectorAll('.inner-tab-content').forEach(tab => tab.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        // ================= WAREHOUSES LOGIC =================
        function saveWarehouse() {
            const name = document.getElementById('warehouse-name').value;
            if(!name) { alert('الرجاء إدخال اسم المخزن'); return; }
            
            const type = document.getElementById('warehouse-type').value;
            const security = parseInt(document.getElementById('warehouse-security').value) || 1;
            const id = document.getElementById('warehouse-id').value || 'WH-' + Date.now();

            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const existingIdx = warehouses.findIndex(w => w.id === id);

            const whData = { id, name, type, securityLevel: security };

            if (existingIdx > -1) {
                warehouses[existingIdx] = whData;
            } else {
                warehouses.push(whData);
            }

            localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehouses));
            cancelEditWarehouse();
            renderWarehouses();
            
            if(typeof showToast === 'function') showToast('تم حفظ المخزن بنجاح', 'success');
        }

        function renderWarehouses() {
            const tbody = document.getElementById('warehouses-tbody');
            if(!tbody) return;
            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            tbody.innerHTML = '';
            
            if(warehouses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 15px; color: var(--text-secondary);">لا توجد مخازن مضافة بعد</td></tr>';
                return;
            }

            warehouses.forEach(w => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong>${w.name}</strong></td>
                    <td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${w.type}</span></td>
                    <td>
                        <button class="btn-text" onclick="editWarehouse('${w.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteWarehouse('${w.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editWarehouse(id) {
            let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
            const wh = warehouses.find(w => w.id === id);
            if(!wh) return;
            document.getElementById('warehouse-id').value = wh.id;
            document.getElementById('warehouse-name').value = wh.name;
            document.getElementById('warehouse-type').value = wh.type || 'مستودع رئيسي';
            document.getElementById('warehouse-security').value = wh.securityLevel || '1';
            document.getElementById('btn-cancel-warehouse').style.display = 'inline-block';
        }

        function cancelEditWarehouse() {
            document.getElementById('warehouse-form').style.display = 'none';
            document.getElementById('warehouse-id').value = '';
            document.getElementById('warehouse-name').value = '';
            document.getElementById('warehouse-type').value = 'مستودع رئيسي';
            document.getElementById('warehouse-security').value = '1';
        }

        function openWarehouseForm() {
            cancelEditWarehouse();
            document.getElementById('warehouse-form').style.display = 'block';
        }

        function deleteWarehouse(id) {
            if(confirm('هل أنت متأكد من حذف هذا المخزن؟ سيتم حذف المخزن فقط، لن يتم حذف البضائع الموجودة.')) {
                let warehouses = [];
            try {
                warehouses = JSON.parse(localStorage.getItem('erp_warehouses_v1')) || [];
                if (!Array.isArray(warehouses)) warehouses = [];
            } catch(e) {
                warehouses = [];
            }
            warehouses = warehouses.filter(w => !w.deleted);
                const idx = warehouses.findIndex(w => w.id === id); if(idx > -1) { warehouses[idx].deleted = true; warehouses[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_warehouses_v1', JSON.stringify(warehouses));
                renderWarehouses();
            }
        }

        // ================= CUSTOMERS LOGIC =================
        function saveCustomer() {
            const id = document.getElementById('cust-id').value || 'CUST-' + Date.now();
            const name = document.getElementById('cust-name').value;
            const phone = document.getElementById('cust-phone').value;
            const whatsapp = document.getElementById('cust-whatsapp').value;
            const email = document.getElementById('cust-email').value;
            const contactPerson = document.getElementById('cust-contact').value;
            const address = document.getElementById('cust-address').value;
            const openingDebit = parseFloat(document.getElementById('cust-opening-debit').value) || 0;
            const openingCredit = parseFloat(document.getElementById('cust-opening-credit').value) || 0;
            const currency = document.getElementById('cust-currency').value || 'SDG';
            const taxNumber = document.getElementById('cust-tax').value || '';
            const accountNo = document.getElementById('cust-account-no').value || '';
            const limit = parseFloat(document.getElementById('cust-limit').value) || 0;
            const security = parseInt(document.getElementById('cust-security').value) || 1;
            const status = document.getElementById('cust-status').value || 'active';
            const notes = document.getElementById('cust-notes').value;

            const bankAccounts = [];
            document.querySelectorAll('.cust-bank-row').forEach(row => {
                bankAccounts.push({
                    bankName: row.querySelector('.bank-name').value,
                    branch: row.querySelector('.bank-branch').value,
                    accountNo: row.querySelector('.bank-acc').value,
                    securityLevel: parseInt(row.querySelector('.bank-security').value) || 1,
                    isDeactivated: row.querySelector('.bank-deactive').checked
                });
            });

            if (!name) { alert('الرجاء إدخال اسم العميل'); return; }

            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            
            const existingIdx = customers.findIndex(c => c.id === id);
            
            const custData = {
                id, name, phone, whatsapp, email, contactPerson, address, 
                openingDebit, openingCredit, 
                currency, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes,
                bankAccounts: bankAccounts
            };

            if (existingIdx > -1) {
                custData.currentBalance = customers[existingIdx].currentBalance;
                customers[existingIdx] = custData;
            } else {
                customers.push(custData);
            }

            localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
            clearCustomerForm();
            renderCustomers();
        }

        function renderCustomers(query = '') {
            const tbody = document.getElementById('customers-tbody');
            if(!tbody) return;
            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);

            if (query) {
                const q = query.toLowerCase();
                customers = customers.filter(c => 
                    (c.name && c.name.toLowerCase().includes(q)) || 
                    (c.phone && c.phone.toLowerCase().includes(q)) || 
                    (c.email && c.email.toLowerCase().includes(q))
                );
            }

            tbody.innerHTML = '';
            customers.forEach(c => {
                const statusBadge = c.status === 'vip' ? '🌟 VIP' : (c.status === 'blacklisted' ? '🔴 موقوف' : '🟢 نشط');
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${c.name} <small>${statusBadge}</small></td><td>${c.phone || '-'}</td><td>${c.currentBalance} SDG</td><td>${c.creditLimit} SDG</td><td><button class="btn-text" onclick="editCustomer('${c.id}')">تعديل</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCustomer('${c.id}')">حذف</button></td></tr>`);
            });
        }

        function filterCustomers() {
            const query = document.getElementById('customer-search-input').value;
            renderCustomers(query);
        }

        function addCustomerBankAccount(data = null) {
            const container = document.getElementById('cust-bank-accounts-container');
            if(!container) return;
            const row = document.createElement('div');
            row.className = 'cust-bank-row';
            row.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; padding: 15px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; position: relative; animation: fadeIn 0.3s ease-out;';
            
            row.innerHTML = `
                <div class="input-group"><label>اسم البنك</label><input type="text" class="bank-name" placeholder="مثال: بنك الخرطوم" value="${data ? (data.bankName || '') : ''}"></div>
                <div class="input-group"><label>الفرع</label><input type="text" class="bank-branch" placeholder="مثال: فرع الرياض" value="${data ? (data.branch || '') : ''}"></div>
                <div class="input-group"><label>رقم الحساب</label><input type="text" class="bank-acc" placeholder="رقم الحساب البنكي" value="${data ? (data.accountNo || '') : ''}"></div>
                <div class="input-group"><label>درجة سرية البنك</label>
                    <select class="bank-security">
                        <option value="1" ${data && data.securityLevel == 1 ? 'selected' : ''}>1 (الأقل سرية)</option>
                        <option value="2" ${data && data.securityLevel == 2 ? 'selected' : ''}>2</option>
                        <option value="3" ${data && data.securityLevel == 3 ? 'selected' : ''}>3</option>
                        <option value="4" ${data && data.securityLevel == 4 ? 'selected' : ''}>4</option>
                        <option value="5" ${data && data.securityLevel == 5 ? 'selected' : ''}>5</option>
                        <option value="6" ${data && data.securityLevel == 6 ? 'selected' : ''}>6</option>
                        <option value="7" ${data && data.securityLevel == 7 ? 'selected' : ''}>7</option>
                        <option value="8" ${data && data.securityLevel == 8 ? 'selected' : ''}>8</option>
                        <option value="9" ${data && data.securityLevel == 9 ? 'selected' : ''}>9</option>
                        <option value="10" ${data && data.securityLevel == 10 ? 'selected' : ''}>10 (الأعلى سرية)</option>
                    </select>
                </div>
                <div class="input-group" style="display:flex; align-items:flex-end; gap: 10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:bold; color:var(--danger-color); margin-bottom:10px;">
                        <input type="checkbox" class="bank-deactive" style="width:18px; height:18px; accent-color:var(--danger-color);" ${data && data.isDeactivated ? 'checked' : ''}> إيقاف
                    </label>
                    <button type="button" onclick="this.closest('.cust-bank-row').remove()" style="background: rgba(244,63,94,0.15); color: #f43f5e; border: 1px solid rgba(244,63,94,0.3); border-radius: 6px; cursor: pointer; padding: 6px 12px; margin-bottom: 5px; margin-right: auto;" title="حذف الحساب">🗑️ حذف</button>
                </div>
            `;
            container.appendChild(row);
        }

        function clearCustomerForm() {
            document.getElementById('cust-id').value = '';
            document.getElementById('cust-name').value = '';
            document.getElementById('cust-phone').value = '';
            document.getElementById('cust-whatsapp').value = '';
            document.getElementById('cust-email').value = '';
            document.getElementById('cust-contact').value = '';
            document.getElementById('cust-address').value = '';
            document.getElementById('cust-opening-debit').value = '';
            document.getElementById('cust-opening-credit').value = '';
            document.getElementById('cust-currency').value = 'SDG';
            document.getElementById('cust-tax').value = '';
            document.getElementById('cust-account-no').value = '';
            document.getElementById('cust-limit').value = '';
            document.getElementById('cust-security').value = '1';
            document.getElementById('cust-status').value = 'active';
            document.getElementById('cust-notes').value = '';
            
            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) {
                bankContainer.innerHTML = '';
                addCustomerBankAccount();
            }
        }

        function editCustomer(id) {
            let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);
            const cust = customers.find(c => c.id === id);
            if(!cust) return;
            document.getElementById('cust-id').value = cust.id;
            document.getElementById('cust-name').value = cust.name;
            document.getElementById('cust-phone').value = cust.phone || '';
            document.getElementById('cust-whatsapp').value = cust.whatsapp || '';
            document.getElementById('cust-email').value = cust.email || '';
            document.getElementById('cust-contact').value = cust.contactPerson || '';
            document.getElementById('cust-address').value = cust.address || '';
            document.getElementById('cust-opening-debit').value = cust.openingDebit || '';
            document.getElementById('cust-opening-credit').value = cust.openingCredit || '';
            document.getElementById('cust-currency').value = cust.currency || 'SDG';
            document.getElementById('cust-tax').value = cust.taxNumber || '';
            document.getElementById('cust-account-no').value = cust.accountNo || '';
            document.getElementById('cust-limit').value = cust.creditLimit || '';
            document.getElementById('cust-security').value = cust.securityLevel || '1';
            document.getElementById('cust-status').value = cust.status || 'active';
            document.getElementById('cust-notes').value = cust.notes || '';
            
            const bankContainer = document.getElementById('cust-bank-accounts-container');
            if(bankContainer) bankContainer.innerHTML = '';
            
            if (cust.bankAccounts && cust.bankAccounts.length > 0) {
                cust.bankAccounts.forEach(acc => addCustomerBankAccount(acc));
            } else if (cust.bankDetails) {
                addCustomerBankAccount(cust.bankDetails);
            } else {
                addCustomerBankAccount();
            }
            
            document.getElementById('customer-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteCustomer(id) {
            if(confirm('هل أنت متأكد من حذف هذا العميل؟')) {
                let customers = [];
            try {
                customers = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
                if (!Array.isArray(customers)) customers = [];
            } catch(e) {
                customers = [];
            }
            customers = customers.filter(c => !c.deleted);
                const idx = customers.findIndex(c => c.id === id); if(idx > -1) { customers[idx].deleted = true; customers[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_customers_v1', JSON.stringify(customers));
                renderCustomers();
            }
        }

        // ================= SUPPLIERS LOGIC =================
        function saveSupplier() {
            const id = document.getElementById('sup-id').value || 'SUP-' + Date.now();
            const name = document.getElementById('sup-name').value;
            const phone = document.getElementById('sup-phone').value;
            const whatsapp = document.getElementById('sup-whatsapp').value;
            const email = document.getElementById('sup-email').value;
            const address = document.getElementById('sup-address').value;
            const openingDebit = parseFloat(document.getElementById('sup-opening-debit').value) || 0;
            const openingCredit = parseFloat(document.getElementById('sup-opening-credit').value) || 0;
            const currency = document.getElementById('sup-currency').value || 'SDG';
            const taxNumber = document.getElementById('sup-tax').value || '';
            const accountNo = document.getElementById('sup-account-no').value || '';
            const limit = parseFloat(document.getElementById('sup-limit').value) || 0;
            const security = parseInt(document.getElementById('sup-security').value) || 1;
            const status = document.getElementById('sup-status').value || 'active';
            const notes = document.getElementById('sup-notes').value;

            if (!name) { alert('الرجاء إدخال اسم المورد'); return; }

            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            
            const existingIdx = suppliers.findIndex(s => s.id === id);
            
            const supData = {
                id, name, phone, whatsapp, email, address, 
                openingDebit, openingCredit, 
                currency, taxNumber, accountNo,
                currentBalance: (openingDebit - openingCredit), 
                creditLimit: limit,
                securityLevel: security, status: status, notes
            };

            if (existingIdx > -1) {
                supData.currentBalance = suppliers[existingIdx].currentBalance;
                suppliers[existingIdx] = supData;
            } else {
                suppliers.push(supData);
            }

            localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
            clearSupplierForm();
            renderSuppliers();
        }

        function renderSuppliers() {
            const tbody = document.querySelector('#tab-suppliers-table tbody');
            if(!tbody) return;
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
            tbody.innerHTML = '';
            suppliers.forEach(s => {
                const statusBadge = s.status === 'blacklisted' ? '🔴 موقوف' : '🟢 نشط';
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${s.name} <small>${statusBadge}</small></td><td>${s.phone||'-'}</td><td>${s.currentBalance} SDG</td><td>${s.creditLimit} SDG</td><td><button class="btn-text" onclick="editSupplier('${s.id}')">تعديل</button><button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSupplier('${s.id}')">حذف</button></td></tr>`);
            });
        }
        
        function clearSupplierForm() {
            document.getElementById('sup-id').value = '';
            document.getElementById('sup-name').value = '';
            document.getElementById('sup-phone').value = '';
            document.getElementById('sup-whatsapp').value = '';
            document.getElementById('sup-email').value = '';
            document.getElementById('sup-address').value = '';
            document.getElementById('sup-opening-debit').value = '';
            document.getElementById('sup-opening-credit').value = '';
            document.getElementById('sup-currency').value = 'SDG';
            document.getElementById('sup-tax').value = '';
            document.getElementById('sup-account-no').value = '';
            document.getElementById('sup-limit').value = '';
            document.getElementById('sup-security').value = '1';
            document.getElementById('sup-status').value = 'active';
            document.getElementById('sup-notes').value = '';
        }
        
        function editSupplier(id) {
            let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
            const sup = suppliers.find(s => s.id === id);
            if(!sup) return;
            document.getElementById('sup-id').value = sup.id;
            document.getElementById('sup-name').value = sup.name;
            document.getElementById('sup-phone').value = sup.phone || '';
            document.getElementById('sup-whatsapp').value = sup.whatsapp || '';
            document.getElementById('sup-email').value = sup.email || '';
            document.getElementById('sup-address').value = sup.address || '';
            document.getElementById('sup-opening-debit').value = sup.openingDebit || '';
            document.getElementById('sup-opening-credit').value = sup.openingCredit || '';
            document.getElementById('sup-currency').value = sup.currency || 'SDG';
            document.getElementById('sup-tax').value = sup.taxNumber || '';
            document.getElementById('sup-account-no').value = sup.accountNo || '';
            document.getElementById('sup-limit').value = sup.creditLimit || '';
            document.getElementById('sup-security').value = sup.securityLevel || '1';
            document.getElementById('sup-status').value = sup.status || 'active';
            document.getElementById('sup-notes').value = sup.notes || '';
            
            document.getElementById('supplier-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function deleteSupplier(id) {
            if(confirm('هل أنت متأكد من حذف هذا المورد؟')) {
                let suppliers = [];
            try {
                suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                if (!Array.isArray(suppliers)) suppliers = [];
            } catch(e) {
                suppliers = [];
            }
            suppliers = suppliers.filter(s => !s.deleted);
                const idx = suppliers.findIndex(s => s.id === id); if(idx > -1) { suppliers[idx].deleted = true; suppliers[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
                renderSuppliers();
            }
        }

        function renderEmployees() {
            const tbody = document.getElementById('tab-employees-table');
            if(!tbody) return;
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            employees = employees.filter(e => !e.deleted);
            tbody.innerHTML = '';
            
            const safeSelect = document.getElementById('safe-responsible');
            if (safeSelect) {
                const currentVal = safeSelect.value;
                safeSelect.innerHTML = '<option value="">-- اختر --</option>';
                employees.forEach(emp => {
                    if (emp.status === 'active' || emp.status === 'نشط') {
                        const opt = document.createElement('option');
                        opt.value = emp.id || emp.username || emp.name;
                        opt.textContent = emp.name || emp.fullName || emp.username || 'بدون اسم';
                        safeSelect.appendChild(opt);
                    }
                });
                safeSelect.value = currentVal;
            }

            employees.forEach(emp => {
                tbody.insertAdjacentHTML('beforeend', `<tr><td>${emp.name}</td><td>${emp.role}</td><td>${emp.phone||'-'}</td><td>${emp.securityLevel}</td><td>${emp.status}</td><td><button class="btn-text" onclick="editEmployee('${emp.id}')">تعديل</button></td></tr>`);
            });
        }
        
        function openNewEmployeeForm() {
            document.getElementById('emp-id').value = '';
            document.getElementById('emp-name').value = '';
            document.getElementById('emp-phone').value = '';
            document.getElementById('emp-password').value = '';
            document.getElementById('emp-role').value = 'cashier';
            document.getElementById('emp-security').value = '1';
            document.getElementById('emp-status').value = 'active';
            document.getElementById('employee-form').style.display = 'block';
        }
        
        function saveEmployee() {
            const id = document.getElementById('emp-id').value || 'EMP-' + Date.now();
            const name = document.getElementById('emp-name').value;
            if(!name) { alert('الرجاء إدخال اسم الموظف'); return; }
            
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            
            const existingIdx = employees.findIndex(e => e.id === id);
            
            const empData = {
                id,
                name,
                phone: document.getElementById('emp-phone').value,
                password: document.getElementById('emp-password').value,
                role: document.getElementById('emp-role').value,
                securityLevel: parseInt(document.getElementById('emp-security').value) || 1,
                status: document.getElementById('emp-status').value
            };
            
            if (existingIdx > -1) {
                employees[existingIdx] = empData;
            } else {
                employees.push(empData);
            }
            
            localStorage.setItem('erp_users_v1', JSON.stringify(employees));
            document.getElementById('employee-form').style.display='none';
            renderEmployees();
        }
        
        function editEmployee(id) {
            let employees = [];
            try {
                employees = JSON.parse(localStorage.getItem('erp_users_v1')) || [];
                if (!Array.isArray(employees)) employees = [];
            } catch(e) {
                employees = [];
            }
            employees = employees.filter(e => !e.deleted);
            const emp = employees.find(e => e.id === id);
            if(!emp) return;
            document.getElementById('emp-id').value = emp.id;
            document.getElementById('emp-name').value = emp.name;
            document.getElementById('emp-phone').value = emp.phone || '';
            document.getElementById('emp-password').value = emp.password || '';
            document.getElementById('emp-role').value = emp.role || 'cashier';
            document.getElementById('emp-security').value = emp.securityLevel || '1';
            document.getElementById('emp-status').value = emp.status || 'active';
            document.getElementById('employee-form').style.display='block';
        }

    
        // ================= BANKS & SAFES LOGIC =================
        function openBankForm() {
            document.getElementById('bank-form').style.display = 'block';
            document.getElementById('bank-code').value = 'B-' + Date.now();
        }

        function closeBankForm() {
            document.getElementById('bank-form').style.display = 'none';
        }

        

        function saveBank() {
            const id = document.getElementById('bank-code').value;
            const name = document.getElementById('bank-name').value;
            if(!name) { alert('الرجاء إدخال اسم الحساب/الخزينة'); return; }

            const type = 'bank';
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            
            const existingIdx = banks.findIndex(b => b.id === id);

            const bankData = {
                id, name, type,
                branch: document.getElementById('bank-branch').value,
                openingBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('bank-opening-balance').value) || 0,
                openingDate: document.getElementById('bank-opening-date').value,
                glAccount: document.getElementById('bank-gl-account').value,
                iban: document.getElementById('bank-iban').value,
                swift: document.getElementById('bank-swift').value,
                responsible: '',
                currency: document.getElementById('bank-currency').value,
                maxLimit: 0,
                minLimit: 0,
                confidentiality: document.getElementById('bank-confidentiality').value,
                isInactive: document.getElementById('bank-inactive').checked,
                isModifier: false
            };

            if (existingIdx > -1) {
                bankData.currentBalance = banks[existingIdx].currentBalance;
                banks[existingIdx] = bankData;
            } else {
                banks.push(bankData);
            }

            localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
            closeBankForm();
            renderBanks();
        }

        function renderBanks() {
            const tbody = document.getElementById('banks-tbody');
            if(!tbody) return;
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
            tbody.innerHTML = '';
            banks.forEach(b => {
                const typeLabel = b.type === 'bank' ? '🏦 حساب بنكي' : '💰 خزينة';
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${b.id}</td>
                    <td>${b.name} <br><small>${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td>${b.currency}</td>
                    <td>${b.currentBalance}</td>
                    <td>
                        <button class="btn-text" onclick="editBank('${b.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteBank('${b.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editBank(id) {
            let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
            const bank = banks.find(b => b.id === id);
            if(!bank) return;
            document.querySelector(`input[name="bank_type"][value="${bank.type}"]`).checked = true;
            toggleBankFields();
            document.getElementById('bank-code').value = bank.id;
            document.getElementById('bank-name').value = bank.name;
            document.getElementById('bank-branch').value = bank.branch || '';
            document.getElementById('bank-opening-balance').value = bank.openingBalance || '';
            document.getElementById('bank-opening-date').value = bank.openingDate || '';
            document.getElementById('bank-gl-account').value = bank.glAccount || '';
            document.getElementById('bank-iban').value = bank.iban || '';
            document.getElementById('bank-swift').value = bank.swift || '';
            document.getElementById('bank-responsible').value = bank.responsible || '';
            document.getElementById('bank-currency').value = bank.currency || 'SDG';
            document.getElementById('safe-max-limit').value = bank.maxLimit || '';
            document.getElementById('safe-min-limit').value = bank.minLimit || '';
            document.getElementById('bank-confidentiality').value = bank.confidentiality || '1';
            document.getElementById('bank-inactive').checked = bank.isInactive || false;
            
            openBankForm();
        }

        function deleteBank(id) {
            if(confirm('هل أنت متأكد من حذف هذا الحساب؟')) {
                let banks = [];
            try {
                banks = JSON.parse(localStorage.getItem('erp_banks_v1')) || [];
                if (!Array.isArray(banks)) banks = [];
            } catch(e) {
                banks = [];
            }
            banks = banks.filter(b => !b.deleted);
                const idx = banks.findIndex(b => b.id === id); if(idx > -1) { banks[idx].deleted = true; banks[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_banks_v1', JSON.stringify(banks));
                renderBanks();
            }
        }

                // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = '🟢 نشط'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = '🔴 موقوف'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
        }

        function quickAddCategory() {
            const name = prompt('أدخل اسم الفئة الجديدة:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value;
            if(!name) { alert('الرجاء إدخال اسم الصنف'); return; }
            const baseUnit = document.getElementById('item-base-unit').value;
            if(!baseUnit) { alert('الرجاء إدخال الوحدة الأساسية'); return; }
            const id = document.getElementById('item-code').value || 'ITEM-' + Date.now();
            const units = [];
            document.querySelectorAll('#units-matrix-body tr').forEach((row) => {
                const inputs = row.querySelectorAll('input');
                if(inputs.length >= 7) {
                    units.push({ unitName: inputs[0].value || baseUnit, factor: parseFloat(inputs[1].value) || 1, barcode: inputs[2].value, cashPrice: parseFloat(inputs[3].value) || 0, creditPrice: parseFloat(inputs[4].value) || 0, minPrice: parseFloat(inputs[5].value) || 0, weight: parseFloat(inputs[6].value) || 0, stock: 0 });
                }
            });
            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = { id, name, nameLatin: document.getElementById('item-name-latin').value, barcode: document.getElementById('item-barcode').value, category: document.getElementById('item-category').value, subCategory: document.getElementById('item-sub-category').value, nature: document.getElementById('item-nature').value, brand: document.getElementById('item-brand').value, status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive', sellingPrice: parseFloat(document.getElementById('item-selling-price').value) || 0, maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0, taxable: document.getElementById('item-taxable').checked, taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0, costMethod: costRadio ? costRadio.value : 'lastPurchase', openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0, openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0, binLocation: document.getElementById('item-bin-location').value, maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0, minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0, reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0, baseUnit, bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1, units, description: document.getElementById('item-description').value };
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) { if(items[ei].units) { itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; }); } items[ei] = itemData; }
            else { itemData.units[0].stock = itemData.openingQty; items.push(itemData); }
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            closeItemForm(); renderItems();
        }

        

function openSafeForm() {
            document.getElementById('safe-form').style.display = 'block';
            document.getElementById('safe-code').value = 'B-' + Date.now();
        }

        function closeSafeForm() {
            document.getElementById('safe-form').style.display = 'none';
        }

        

        function saveSafe() {
            const id = document.getElementById('safe-code').value;
            const name = document.getElementById('safe-name').value;
            if(!name) { alert('الرجاء إدخال اسم الخزينة'); return; }

            const type = 'safe';
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            
            const isDuplicateName = safes.some(b => b.name.trim() === name.trim() && b.id !== id);
            if (isDuplicateName) {
                alert('هذا الاسم مستخدم لخزنة أخرى، الرجاء إدخال اسم مختلف.');
                return;
            }

            const existingIdx = safes.findIndex(b => b.id === id);

            const safeData = {
                id, name, type,
                branch: '',
                openingBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                currentBalance: parseFloat(document.getElementById('safe-opening-balance').value) || 0,
                openingDate: document.getElementById('safe-opening-date').value,
                glAccount: document.getElementById('safe-gl-account').value,
                iban: '',
                swift: '',
                responsible: document.getElementById('safe-responsible').value,
                currency: document.getElementById('safe-currency').value,
                maxLimit: parseFloat(document.getElementById('safe-max-limit').value) || 0,
                minLimit: parseFloat(document.getElementById('safe-min-limit').value) || 0,
                confidentiality: document.getElementById('safe-confidentiality').value,
                isInactive: document.getElementById('safe-inactive').checked,
                isModifier: document.getElementById('safe-modifier').checked
            };

            if (existingIdx > -1) {
                safeData.currentBalance = safes[existingIdx].currentBalance;
                safes[existingIdx] = safeData;
            } else {
                safes.push(safeData);
            }

            localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
            closeSafeForm();
            renderSafes();
        }

        function renderSafes() {
            const tbody = document.getElementById('safes-tbody');
            if(!tbody) return;
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            tbody.innerHTML = '';
            safes.forEach(b => {
                const typeLabel = b.type === 'safe' ? '💰 خزينة' : '💰 خزينة';
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td>${b.id}</td>
                    <td>${b.name} <br><small>${b.branch || ''}</small></td>
                    <td>${typeLabel}</td>
                    <td>${b.currency}</td>
                    <td>${b.currentBalance}</td>
                    <td>
                        <button class="btn-text" onclick="editSafe('${b.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteSafe('${b.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editSafe(id) {
            let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
            const safe = safes.find(b => b.id === id);
            if(!safe) return;
            document.querySelector(`input[name="safe_type"][value="${safe.type}"]`).checked = true;
            toggleSafeFields();
            document.getElementById('safe-code').value = safe.id;
            document.getElementById('safe-name').value = safe.name;
            
            document.getElementById('safe-opening-balance').value = safe.openingBalance || '';
            document.getElementById('safe-opening-date').value = safe.openingDate || '';
            document.getElementById('safe-gl-account').value = safe.glAccount || '';
            
            
            document.getElementById('safe-responsible').value = safe.responsible || '';
            document.getElementById('safe-currency').value = safe.currency || 'SDG';
            document.getElementById('safe-max-limit').value = safe.maxLimit || '';
            document.getElementById('safe-min-limit').value = safe.minLimit || '';
            document.getElementById('safe-confidentiality').value = safe.confidentiality || '1';
            document.getElementById('safe-inactive').checked = safe.isInactive || false;
            document.getElementById('safe-modifier').checked = safe.isModifier || false;
            openSafeForm();
        }

        function deleteSafe(id) {
            if(confirm('هل أنت متأكد من حذف هذا الحساب؟')) {
                let safes = [];
            try {
                safes = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
                if (!Array.isArray(safes)) safes = [];
            } catch(e) {
                safes = [];
            }
            safes = safes.filter(b => !b.deleted);
                const idx = safes.findIndex(b => b.id === id); if(idx > -1) { safes[idx].deleted = true; safes[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_safes_v1', JSON.stringify(safes));
                renderSafes();
            }
        }

                // ================= ITEMS LOGIC =================
        function closeItemForm() { document.getElementById('item-form').style.display = 'none'; }

        function updateToggleLabel() {
            const t = document.getElementById('item-active-toggle');
            const l = document.getElementById('item-active-label');
            if(t.checked) { l.innerText = '🟢 نشط'; l.style.color = 'var(--success-color)'; }
            else { l.innerText = '🔴 موقوف'; l.style.color = 'var(--danger-color)'; }
        }

        function updateBaseUnitRow() {
            const v = document.getElementById('item-base-unit').value;
            const d = document.getElementById('base-unit-name-display');
            if(d) d.value = v;
        }

        function quickAddCategory() {
            const name = prompt('أدخل اسم الفئة الجديدة:');
            if(!name) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            updateCategoryDropdowns();
            document.getElementById('item-category').value = name;
            renderCategoriesList();
        }

        function saveItem() {
            const name = document.getElementById('item-name-input').value;
            if(!name) { alert('الرجاء إدخال اسم الصنف'); return; }
            const baseUnit = document.getElementById('item-base-unit').value;
            if(!baseUnit) { alert('الرجاء إدخال الوحدة الأساسية'); return; }
            const id = document.getElementById('item-code').value || 'ITEM-' + Date.now();
            const units = [];
            document.querySelectorAll('#units-matrix-body tr').forEach((row) => {
                const unitNameElem = row.querySelector('input[type="text"]:first-of-type, select.unit-name-input');
                const inputs = row.querySelectorAll('input[type="number"], input[type="text"]:not(#base-unit-name-display)');
                
                // inputs now has:
                // [number factor, text barcode, number cash, number credit, number min, number weight]
                // but wait, let's just get them by specific selectors or index correctly to avoid bugs.
                
                const factorInput = row.querySelector('input[type="number"]:nth-of-type(1)'); // wait, nth-of-type(1) might be wrong if there are multiple td's.
                // It's safer to get all inputs AND selects and map them, or use specific classes/indices.
                // Let's use the td index.
                const tds = row.querySelectorAll('td');
                if (tds.length >= 8) {
                    const uName = tds[1].querySelector('input, select')?.value || baseUnit;
                    const uFactor = parseFloat(tds[2].querySelector('input')?.value) || 1;
                    const uBarcode = tds[3].querySelector('input')?.value || '';
                    const uCash = parseFloat(tds[4].querySelector('input')?.value) || 0;
                    const uCredit = parseFloat(tds[5].querySelector('input')?.value) || 0;
                    const uMin = parseFloat(tds[6].querySelector('input')?.value) || 0;
                    const uWeight = parseFloat(tds[7].querySelector('input')?.value) || 0;
                    
                    units.push({ unitName: uName, factor: uFactor, barcode: uBarcode, cashPrice: uCash, creditPrice: uCredit, minPrice: uMin, weight: uWeight, stock: 0 });
                }
            });
            const itemBarcode = document.getElementById('item-barcode').value;
            const newBarcodes = [itemBarcode, ...units.map(u => u.barcode)].filter(b => b && b.trim() !== '');

            const costRadio = document.querySelector('input[name="cost_method"]:checked');
            const itemData = { id, name, nameLatin: document.getElementById('item-name-latin').value, barcode: itemBarcode, category: document.getElementById('item-category').value, subCategory: document.getElementById('item-sub-category').value, nature: document.getElementById('item-nature').value, brand: document.getElementById('item-brand').value, status: document.getElementById('item-active-toggle').checked ? 'active' : 'inactive', sellingPrice: parseFloat(document.getElementById('item-selling-price').value) || 0, maxDiscount: parseFloat(document.getElementById('item-max-discount').value) || 0, taxable: document.getElementById('item-taxable').checked, taxRate: parseFloat(document.getElementById('item-tax-rate').value) || 0, costMethod: costRadio ? costRadio.value : 'lastPurchase', openingQty: parseFloat(document.getElementById('item-opening-qty').value) || 0, openingValue: parseFloat(document.getElementById('item-opening-value').value) || 0, binLocation: document.getElementById('item-bin-location').value, maxLimit: parseFloat(document.getElementById('item-max-limit').value) || 0, minLimit: parseFloat(document.getElementById('item-min-limit').value) || 0, reorderLevel: parseFloat(document.getElementById('item-reorder-level').value) || 0, baseUnit, bundleFactor: parseFloat(document.getElementById('item-bundle-factor').value) || 1, units, description: document.getElementById('item-description').value };
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const duplicateName = items.find(i => i.name.trim() === name.trim() && i.id !== id);
            if (duplicateName) { alert('هذا الصنف مسجل مسبقاً بنفس الاسم.'); return; }
            
            for (const b of newBarcodes) {
                const duplicateBarcode = items.find(i => i.id !== id && (i.barcode === b || (i.units && i.units.some(u => u.barcode === b))));
                if (duplicateBarcode) {
                    alert('الباركود (' + b + ') مستخدم مسبقاً في صنف آخر: ' + duplicateBarcode.name);
                    return;
                }
            }
            const ei = items.findIndex(i => i.id === id);
            if(ei > -1) { if(items[ei].units) { itemData.units.forEach((u,i) => { if(items[ei].units[i]) u.stock = items[ei].units[i].stock || 0; }); } items[ei] = itemData; }
            else { itemData.units[0].stock = itemData.openingQty; items.push(itemData); }
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            closeItemForm(); renderItems();
        }

        function renderItems() {
            const tbody = document.getElementById('items-table-body');
            if(!tbody) return;
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            
            const totalEl = document.getElementById('kpi-total-items');
            if(totalEl) totalEl.innerText = items.length + ' صنف';
            
            let lowStock = 0;
            items.forEach(i => {
                let stock = i.units && i.units[0] ? (i.units[0].stock || 0) : 0;
                if(stock <= (i.minLimit || 5)) lowStock++;
            });
            const lowEl = document.getElementById('kpi-low-stock');
            if(lowEl) lowEl.innerText = lowStock + ' صنف';
            
            if (currentItemSearchQuery) {
                items = items.filter(i => {
                    const unitBarcodes = i.units ? i.units.map(u => u.barcode || '').join(' ') : '';
                    const str = (i.id + ' ' + i.name + ' ' + (i.category||'') + ' ' + (i.barcode||'') + ' ' + unitBarcodes).toLowerCase();
                    return str.includes(currentItemSearchQuery);
                });
            }
            
            const totalPages = Math.ceil(items.length / itemsPerPage) || 1;
            if (currentPageItems > totalPages) currentPageItems = totalPages;
            const start = (currentPageItems - 1) * itemsPerPage;
            const paginatedItems = items.slice(start, start + itemsPerPage);
            
            tbody.innerHTML = '';
            paginatedItems.forEach(i => {
                let bu = i.baseUnit || (i.units && i.units[0] ? i.units[0].unitName : '-');
                let price = i.sellingPrice || (i.units && i.units[0] ? i.units[0].cashPrice : 0);
                const badge = i.status === 'inactive' ? '<span style="color:var(--danger-color); font-size:0.8rem; font-weight:bold; background:rgba(239,68,68,0.1); padding:3px 8px; border-radius:6px;">🔴 موقوف</span>' : '<span style="color:var(--success-color); font-size:0.8rem; font-weight:bold; background:rgba(52,211,153,0.1); padding:3px 8px; border-radius:6px;">🟢 نشط</span>';
                const subCat = i.subCategory ? ' <small style="color:var(--text-secondary);">(' + i.subCategory + ')</small>' : '';
                tbody.insertAdjacentHTML('beforeend', `<tr class="item-row"><td class="item-id-cell" style="font-weight:bold; color:#38bdf8;">${i.id}</td><td class="item-name-cell" style="font-weight:bold;">${i.name}${subCat}</td><td><span style="background:rgba(255,255,255,0.05); padding:3px 8px; border-radius:6px; font-size:0.85rem;">${i.category || '-'}</span></td><td>${bu}</td><td style="font-weight:bold; color:var(--success-color);">${price.toLocaleString()} SDG</td><td>${badge}</td><td><button class="btn-text" style="color:#a855f7; font-size:1.1rem; margin-left:8px;" onclick="editItem('${i.id}')" title="تعديل">✏️</button><button class="btn-text" style="color:var(--danger-color); font-size:1.1rem;" onclick="deleteItem('${i.id}')" title="حذف">🗑️</button></td></tr>`);
            });
            
            renderItemsPagination(totalPages);
            renderCategoriesList();
        }
        
        function renderItemsPagination(totalPages) {
            let pagContainer = document.getElementById('items-pagination-container');
            if (!pagContainer) {
                const tableWrap = document.querySelector('#inner-products .table-responsive');
                if (tableWrap) {
                    tableWrap.insertAdjacentHTML('afterend', '<div id="items-pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding:10px 20px; background:rgba(0,0,0,0.2); border-radius:10px;"></div>');
                    pagContainer = document.getElementById('items-pagination-container');
                }
            }
            if (pagContainer) {
                pagContainer.innerHTML = `
                    <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems>1){currentPageItems--; renderItems();}">السابق</button>
                    <span style="color:var(--text-secondary); font-weight:bold;">صفحة ${currentPageItems} من ${totalPages}</span>
                    <button class="btn-action" style="padding:5px 15px;" onclick="if(currentPageItems<${totalPages}){currentPageItems++; renderItems();}">التالي</button>
                `;
            }
        }

        let currentPageItems = 1;
        const itemsPerPage = 20;
        let currentItemSearchQuery = '';

        let itemSearchTimeout = null;
        function filterItemsTable() {
            clearTimeout(itemSearchTimeout);
            itemSearchTimeout = setTimeout(() => {
                currentItemSearchQuery = (document.getElementById('item-search').value || '').toLowerCase();
                currentPageItems = 1;
                renderItems();
            }, 300);
        }

        function quickAddUnit() {
            const name = prompt('أدخل اسم الوحدة الجديدة:');
            if(name) {
                let units = [];
                try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
                if(!units.includes(name)) {
                    units.push(name);
                    localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
                    renderGlobalUnitsList();
                    updateUnitDropdowns(name);
                }
            }
        }

        function buildUnitRow(num, u, isBase) {
            const dis = isBase ? 'disabled' : '';
            const bg = isBase ? 'style="background:rgba(52,211,153,0.08); border-right:3px solid var(--success-color);"' : '';
            const act = isBase ? '<span style="font-size:0.75rem; color:var(--success-color); font-weight:bold; background:rgba(52,211,153,0.2); padding:3px 8px; border-radius:4px;">أساسية</span>' : "<button class=\"btn-text\" style=\"color:var(--danger-color);\" onclick=\"this.closest('tr').remove()\">✖</button>";
            const idAttr = isBase ? ' id="base-unit-name-display"' : ' class="unit-name-input"';
            
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            const val = u.unitName || '';
            if (val && !units.includes(val)) units.push(val); // Ensure backward compat display
            
            let opts = `<option value="">-- اختر --</option>` + units.map(un => `<option value="${un}" ${un === val ? 'selected' : ''}>${un}</option>`).join('');
            
            let unitField = isBase ? 
                `<input type="text" id="base-unit-name-display" value="${val}" disabled style="width:100px; background:transparent; border:none; color:var(--success-color); font-weight:bold; text-align:center; padding:5px; border-radius:6px;">` : 
                `<select ${idAttr} style="width:120px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; font-weight:bold; text-align:center; padding:5px; border-radius:6px; outline:none;">${opts}</select>`;

            return '<tr ' + bg + '><td>' + num + '</td><td>' + unitField + '</td><td><input type="number" value="' + (u.factor || 1) + '" ' + dis + ' style="width:60px; background:transparent; border:' + (isBase ? 'none' : '1px solid rgba(255,255,255,0.15)') + '; color:' + (isBase ? 'var(--success-color)' : '#fff') + '; font-weight:bold; text-align:center; padding:5px; border-radius:6px;"></td><td><input type="text" value="' + (u.barcode || '') + '" placeholder="باركود..." style="width:100px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.cashPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px; font-weight:bold;"></td><td><input type="number" value="' + (u.creditPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.minPrice || '') + '" placeholder="0.00" style="width:85px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td><input type="number" value="' + (u.weight || '') + '" placeholder="0.00" style="width:60px; background:var(--bg-color); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px; border-radius:6px;"></td><td>' + act + '</td></tr>';
        }

        function editItem(id) {
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const item = items.find(i => i.id === id);
            if(!item) return;
            document.getElementById('form-mode-indicator').innerText = 'تعديل صنف: ' + item.name;
            document.getElementById('item-code').value = item.id;
            document.getElementById('item-name-input').value = item.name;
            document.getElementById('item-name-latin').value = item.nameLatin || '';
            document.getElementById('item-barcode').value = item.barcode || '';
            updateCategoryDropdowns();
            document.getElementById('item-category').value = item.category || '';
            document.getElementById('item-sub-category').value = item.subCategory || '';
            document.getElementById('item-nature').value = item.nature || 'مخزني';
            document.getElementById('item-brand').value = item.brand || '';
            document.getElementById('item-active-toggle').checked = item.status !== 'inactive';
            updateToggleLabel();
            document.getElementById('item-selling-price').value = item.sellingPrice || '';
            document.getElementById('item-max-discount').value = item.maxDiscount || '';
            document.getElementById('item-taxable').checked = item.taxable || false;
            document.getElementById('item-tax-rate').value = item.taxRate || '';
            document.getElementById('item-tax-rate').disabled = !item.taxable;
            const cm = document.querySelector('input[name="cost_method"][value="' + (item.costMethod || 'lastPurchase') + '"]');
            if(cm) cm.checked = true;
            document.getElementById('item-opening-qty').value = item.openingQty || '';
            document.getElementById('item-opening-value').value = item.openingValue || '';
            document.getElementById('item-bin-location').value = item.binLocation || '';
            document.getElementById('item-max-limit').value = item.maxLimit || '';
            document.getElementById('item-min-limit').value = item.minLimit || '';
            document.getElementById('item-reorder-level').value = item.reorderLevel || '';
            document.getElementById('item-base-unit').value = item.baseUnit || '';
            document.getElementById('item-bundle-factor').value = item.bundleFactor || 1;
            document.getElementById('item-description').value = item.description || '';
            updateBaseUnitRow();
            const tbody = document.getElementById('units-matrix-body');
            tbody.innerHTML = '';
            if(item.units && item.units.length > 0) { item.units.forEach((u, idx) => { tbody.insertAdjacentHTML('beforeend', buildUnitRow(idx + 1, u, idx === 0)); }); }
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function deleteItem(id) {
            if(confirm('هل أنت متأكد من حذف هذا الصنف؟')) {
                let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
                const idx = items.findIndex(i => i.id === id); if(idx > -1) { items[idx].deleted = true; items[idx].updatedAt = Date.now(); }
                localStorage.setItem('erp_items_v1', JSON.stringify(items));
                renderItems();
            }
        }

        function openItemForm() {
            const tabItems = document.getElementById('tab-items');
            if(tabItems) {
                tabItems.querySelectorAll('.inner-tab-content').forEach(c => c.classList.remove('active'));
                tabItems.querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
                const productsTab = document.getElementById('inner-products');
                if(productsTab) productsTab.classList.add('active');
                const productsBtn = tabItems.querySelector('button[onclick*="inner-products"]');
                if(productsBtn) productsBtn.classList.add('active');
            }
            
            document.getElementById('form-mode-indicator').innerText = 'إضافة صنف جديد';
            document.getElementById('item-code').value = '';
            document.getElementById('item-name-input').value = '';
            document.getElementById('item-name-latin').value = '';
            document.getElementById('item-barcode').value = '';
            document.getElementById('item-sub-category').value = '';
            document.getElementById('item-brand').value = '';
            document.getElementById('item-active-toggle').checked = true;
            updateToggleLabel();
            document.getElementById('item-selling-price').value = '';
            document.getElementById('item-max-discount').value = '';
            document.getElementById('item-taxable').checked = false;
            document.getElementById('item-tax-rate').value = '';
            document.getElementById('item-tax-rate').disabled = true;
            document.querySelector('input[name="cost_method"][value="lastPurchase"]').checked = true;
            document.getElementById('item-opening-qty').value = '';
            document.getElementById('item-opening-value').value = '';
            document.getElementById('item-bin-location').value = '';
            document.getElementById('item-max-limit').value = '';
            document.getElementById('item-min-limit').value = '';
            document.getElementById('item-reorder-level').value = '';
            document.getElementById('item-base-unit').value = '';
            document.getElementById('item-bundle-factor').value = '1';
            document.getElementById('item-description').value = '';
            updateCategoryDropdowns();
            document.getElementById('units-matrix-body').innerHTML = buildUnitRow(1, { unitName: '', factor: 1 }, true);
            document.getElementById('item-form').style.display = 'block';
            document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
        }

        function addUnitRow() {
            const tbody = document.getElementById('units-matrix-body');
            if(!tbody) return;
            const count = tbody.querySelectorAll('tr').length + 1;
            tbody.insertAdjacentHTML('beforeend', buildUnitRow(count, { unitName: '', factor: 1 }, false));
        }

        // ================= UNITS LOGIC =================
        function saveGlobalUnit() {
            const name = document.getElementById('unit-name-input').value;
            if(!name) { alert('أدخل اسم الوحدة'); return; }
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            if(!units.includes(name)) { 
                units.push(name); 
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units)); 
            }
            document.getElementById('unit-name-input').value = '';
            renderGlobalUnitsList();
            updateUnitDropdowns();
        }

        function renderGlobalUnitsList() {
            const tbody = document.getElementById('units-table-body');
            if(!tbody) return;
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            tbody.innerHTML = '';
            units.forEach(u => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td><strong style="color:#38bdf8;">${u}</strong></td>
                    <td><button class="btn-text" style="color:var(--danger-color);" onclick="deleteGlobalUnit('${u}')">حذف</button></td>
                </tr>`);
            });
        }

        function deleteGlobalUnit(name) {
            if(!confirm('هل أنت متأكد من حذف هذه الوحدة؟')) return;
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            units = units.filter(u => u !== name);
            localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            renderGlobalUnitsList();
            updateUnitDropdowns();
        }

        function importLegacyUnits() {
            // Import units that were manually typed in older items
            let items = [];
            try { items = JSON.parse(localStorage.getItem('erp_items_v1')) || []; } catch(e) {}
            if(items.length === 0) return;
            let units = [];
            try { units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || []; } catch(e) {}
            let added = false;
            items.forEach(i => {
                if(i.baseUnit && !units.includes(i.baseUnit)) { units.push(i.baseUnit); added = true; }
                if(i.units) {
                    i.units.forEach(u => {
                        if(u.unitName && !units.includes(u.unitName)) { units.push(u.unitName); added = true; }
                    });
                }
            });
            if(added) {
                localStorage.setItem('erp_global_units_v1', JSON.stringify(units));
            }
        }

        function updateUnitDropdowns(selectedValue = '') {
            let units = [];
            try {
                units = JSON.parse(localStorage.getItem('erp_global_units_v1')) || [];
            } catch(e) { units = []; }
            
            const options = units.map(u => `<option value="${u}">${u}</option>`).join('');
            const baseSelect = document.getElementById('item-base-unit');
            if(baseSelect) {
                const current = selectedValue || baseSelect.value;
                baseSelect.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                if(current) baseSelect.value = current;
            }
            
            // Also update any dynamic selects in the matrix
            document.querySelectorAll('.unit-name-input').forEach(sel => {
                if(sel.tagName === 'SELECT') {
                    const current = sel.value;
                    sel.innerHTML = `<option value="">-- اختر الوحدة --</option>` + options;
                    if(current) sel.value = current;
                }
            });
        }

        // ================= CATEGORY LOGIC =================
        function saveCategory() {
            const name = document.getElementById('cat-name-input').value;
            if(!name) { alert('أدخل اسم الفئة'); return; }
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            if(!cats.includes(name)) { cats.push(name); localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            document.getElementById('cat-name-input').value = '';
            renderCategoriesList();
            updateCategoryDropdowns();
        }

        function renderCategoriesList() {
            const tbody = document.getElementById('categories-table-body');
            if(!tbody) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            const kpi = document.getElementById('kpi-total-categories');
            if(kpi) kpi.innerText = cats.length + ' فئة';
            tbody.innerHTML = '';
            cats.forEach(c => {
                const count = items.filter(i => i.category === c).length;
                const subs = [...new Set(items.filter(i => i.category === c && i.subCategory).map(i => i.subCategory))];
                const subHtml = subs.length > 0 ? '<br><small style="color:#a855f7;">' + subs.join(' | ') + '</small>' : '';
                tbody.insertAdjacentHTML('beforeend', '<tr><td style="font-weight:bold; color:#a855f7;">' + c + subHtml + '</td><td><span style="background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px;">' + count + '</span></td><td><button class="btn-text" style="color:#38bdf8; margin-left:10px;" onclick="editCategory(\'' + c + '\')">✏️ تعديل</button><button class="btn-text" style="color:var(--danger-color);" onclick="deleteCategory(\'' + c + '\')">🗑️ حذف</button></td></tr>');
            });
        }

        function editCategory(oldName) {
            const newName = prompt('تعديل اسم الفئة:', oldName);
            if(!newName || newName === oldName) return;
            let cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || [];
            const idx = cats.indexOf(oldName);
            if(idx > -1) { cats[idx] = newName; localStorage.setItem('erp_categories_v1', JSON.stringify(cats)); }
            // Update all items that use this category
            let items = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                if (!Array.isArray(items)) items = [];
            } catch(e) {
                items = [];
            }
            items = items.filter(i => !i.deleted);
            items.forEach(i => { if(i.category === oldName) i.category = newName; });
            localStorage.setItem('erp_items_v1', JSON.stringify(items));
            renderCategoriesList();
            updateCategoryDropdowns();
            renderItems();
        }

        function deleteCategory(name) {
            if(confirm('هل أنت متأكد من حذف الفئة؟')) {
                let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
                cats = cats.filter(c => c !== name);
                localStorage.setItem('erp_categories_v1', JSON.stringify(cats));
                renderCategoriesList();
                updateCategoryDropdowns();
            }
        }

        function updateCategoryDropdowns() {
            const select = document.getElementById('item-category');
            if(!select) return;
            let cats = [];
            try {
                cats = JSON.parse(localStorage.getItem('erp_categories_v1')) || ['مواد بناء','أدوات صحية','كهرباء'];
                if (!Array.isArray(cats)) cats = ['مواد بناء','أدوات صحية','كهرباء'];
            } catch(e) {
                cats = ['مواد بناء','أدوات صحية','كهرباء'];
            }
            const val = select.value;
            select.innerHTML = '';
            cats.forEach(c => { select.insertAdjacentHTML('beforeend', '<option value="' + c + '">' + c + '</option>'); });
            if(cats.includes(val)) select.value = val;
        }


        // ================= COST CENTERS LOGIC =================
        function openCostCenterForm() {
            document.getElementById('costcenter-form').style.display = 'block';
            document.getElementById('btn-cancel-costcenter').style.display = 'block';
            document.getElementById('costcenter-id').value = '';
            document.getElementById('costcenter-code').value = 'CC-' + Date.now();
            document.getElementById('costcenter-name').value = '';
            document.getElementById('costcenter-desc').value = '';
        }

        function closeCostCenterForm() {
            document.getElementById('costcenter-form').style.display = 'none';
        }

        function saveCostCenter() {
            const id = document.getElementById('costcenter-id').value || document.getElementById('costcenter-code').value || 'CC-' + Date.now();
            const name = document.getElementById('costcenter-name').value;
            const desc = document.getElementById('costcenter-desc').value;
            
            if(!name) { alert('الرجاء إدخال اسم مركز التكلفة'); return; }

            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            } catch(e) { ccs = []; }
            
            const existingIdx = ccs.findIndex(c => c.id === id);
            
            const ccData = {
                id, name, desc,
                createdAt: Date.now()
            };

            if (existingIdx > -1) {
                ccs[existingIdx] = { ...ccs[existingIdx], ...ccData };
            } else {
                ccs.push(ccData);
            }

            localStorage.setItem('erp_jobs_v1', JSON.stringify(ccs));
            closeCostCenterForm();
            renderCostCenters();
        }

        function renderCostCenters() {
            const tbody = document.getElementById('costcenters-tbody');
            if(!tbody) return;
            let ccs = [];
            try {
                ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || [];
            } catch(e) { ccs = []; }
            
            tbody.innerHTML = '';
            ccs.forEach(c => {
                tbody.insertAdjacentHTML('beforeend', `<tr>
                    <td style="color:#38bdf8; font-weight:bold;">${c.id}</td>
                    <td style="font-weight:bold;">${c.name || c.title || ''}</td>
                    <td>${c.desc || ''}</td>
                    <td>
                        <button class="btn-text" onclick="editCostCenter('${c.id}')">تعديل</button>
                        <button class="btn-text" style="color:var(--danger-color); margin-right:10px;" onclick="deleteCostCenter('${c.id}')">حذف</button>
                    </td>
                </tr>`);
            });
        }

        function editCostCenter(id) {
            let ccs = [];
            try { ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) { ccs = []; }
            const cc = ccs.find(c => c.id === id);
            if(!cc) return;
            
            document.getElementById('costcenter-id').value = cc.id;
            document.getElementById('costcenter-code').value = cc.id;
            document.getElementById('costcenter-name').value = cc.name || cc.title || '';
            document.getElementById('costcenter-desc').value = cc.desc || '';
            
            document.getElementById('costcenter-form').style.display = 'block';
            document.getElementById('btn-cancel-costcenter').style.display = 'block';
            document.getElementById('costcenter-form').scrollIntoView({ behavior: 'smooth' });
        }

        function deleteCostCenter(id) {
            if(confirm('هل أنت متأكد من حذف مركز التكلفة؟')) {
                let ccs = [];
                try { ccs = JSON.parse(localStorage.getItem('erp_jobs_v1')) || []; } catch(e) { ccs = []; }
                ccs = ccs.filter(c => c.id !== id);
                localStorage.setItem('erp_jobs_v1', JSON.stringify(ccs));
                renderCostCenters();
            }
        }

        function goBackOrExit() {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                window.history.back();
            } else {
                window.location.href = 'index.php';
            }
        }
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Units Init
    importLegacyUnits();
    renderGlobalUnitsList();
    updateUnitDropdowns();
    
    setTimeout(() => {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        if (tabParam) {
            const tabId = 'tab-' + tabParam;
            const targetBtn = document.querySelector(`button[onclick*="'${tabId}'"]`);
            if (targetBtn) {
                targetBtn.click();
            }
        }
    }, 100);
});
</script>
</body>
</html>
