<?php $page_title = 'سجل التطوير'; ?>
<!-- c:\mynewapp\frontend-pwa\audit-log.html -->
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>سجل التطوير</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
        <style>
        .audit-container { padding: 20px; padding-bottom: 100px; }
        .page-title {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filters-card {
            background: var(--surface-color);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 0.9rem; font-weight: 600; color: var(--text-secondary); }
        .filter-group select, .filter-group input {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            background: var(--bg-color);
            color: var(--text-primary);
        }

        .data-table-wrapper {
            background: var(--surface-color);
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            overflow-x: auto;
        }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: right; border-bottom: 1px solid var(--glass-border); }
        th { background: rgba(0,0,0,0.2); font-weight: 800; color: var(--text-secondary); font-size: 0.9rem; }
        [data-theme="light"] th { background: rgba(0,0,0,0.02); }
        tr:hover { background: rgba(56, 189, 248, 0.05); }
        
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 800;
        }
        
        .empty-state { text-align: center; padding: 40px; color: var(--text-secondary); font-weight: 600; }
    
        .header-brand { display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, #f472b6, #ec4899); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 15px rgba(236, 72, 153, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2); }
        .brand-text h1 { margin: 0; font-size: 1.1rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 5px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
        .header-center { text-align: center; }
        .header-controls { display: flex; align-items: center; gap: 15px; }
        .icon-btn { width: 38px; height: 38px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .icon-btn:hover { background: rgba(255,255,255,0.1); transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); }
        .icon-btn:active { transform: translateY(0); }
        :root.light-theme .premium-header { background: linear-gradient(135deg, rgba(244, 114, 182, 0.1), rgba(168, 85, 247, 0.1), rgba(99, 102, 241, 0.1), rgba(56, 189, 248, 0.1)); border-color: rgba(0,0,0,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        :root.light-theme .brand-text h1 { color: #0f172a; }
        :root.light-theme .brand-text p { color: #64748b; }
        :root.light-theme .icon-btn { background: #fff; border-color: #e2e8f0; color: #64748b; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        :root.light-theme .icon-btn:hover { background: #f8fafc; color: #0f172a; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

    
        
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

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    
    
    <!-- Unified Premium Header -->
    <?php $header_theme_class = 'header-theme-audit'; ?>
    <?php include 'includes/header.php'; ?>


    <!-- Main Content -->
    <main class="main-content audit-container" id="main-content" style="display:none;">
        

        <div class="filters-card">
            <div class="filter-group">
                <label>تصفية بالمستخدم</label>
                <select id="filter-user" onchange="renderLogs()">
                    <option value="all">الكل</option>
                </select>
            </div>
            <div class="filter-group">
                <label>تصفية بالوحدة</label>
                <select id="filter-module" onchange="renderLogs()">
                    <option value="all">الكل</option>
                    <option value="login">تسجيل الدخول</option>
                    <option value="sales">المبيعات</option>
                    <option value="purchase">المشتريات</option>
                    <option value="finance">المالية</option>
                    <option value="master_data">البيانات الأساسية</option>
                    <option value="settings">الإعدادات</option>
                </select>
            </div>
            <div class="filter-group">
                <label>نص البحث</label>
                <input type="text" id="filter-search" placeholder="ابحث في السجلات..." onkeyup="renderLogs()">
            </div>
        </div>

        <div class="data-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>تاريخ الحركة</th>
                        <th>المستخدم</th>
                        <th>الوحدة</th>
                        <th>نوع الحركة</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>
                <tbody id="logs-table-body">
                    <!-- Logs will be populated here -->
                </tbody>
            </table>
        </div>
    </main>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260803.09151"></script>
    <script>
        document.addEventListener('DBReady', () => {
            const userStr = localStorage.getItem('user');
            if (!userStr) return;
            const user = JSON.parse(userStr);
            
            // SECURITY CHECK: Only Admin or Security Level >= 8
            if (user.securityLevel < 8 && user.role !== 'admin' && user.username !== 'admin') {
                alert('عذراً، ليس لديك الصلاحية لعرض هذه الصفحة.');
                window.location.href = 'dashboard-pro.php';
                return;
            }
            
            document.getElementById('main-content').style.display = 'block';
            
            populateUserFilter();
            renderLogs();
        });

        function populateUserFilter() {
            const users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
            const select = document.getElementById('filter-user');
            users.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.fullName || u.username;
                select.appendChild(opt);
            });
        }

        function getActionBadgeColor(action) {
            const a = action.toLowerCase();
            if (a.includes('delete') || a.includes('حذف') || a.includes('reject')) return 'background:rgba(239, 68, 68, 0.1); color:#ef4444;';
            if (a.includes('add') || a.includes('create') || a.includes('إضافة') || a.includes('approve') || a.includes('login')) return 'background:rgba(16, 185, 129, 0.1); color:#10b981;';
            if (a.includes('edit') || a.includes('update') || a.includes('تعديل')) return 'background:rgba(245, 158, 11, 0.1); color:#f59e0b;';
            if (a.includes('print') || a.includes('طباعة')) return 'background:rgba(56, 189, 248, 0.1); color:#38bdf8;';
            return 'background:rgba(156, 163, 175, 0.1); color:#6b7280;';
        }

        function renderLogs() {
            const logs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');
            const tbody = document.getElementById('logs-table-body');
            tbody.innerHTML = '';

            const userFilter = document.getElementById('filter-user').value;
            const moduleFilter = document.getElementById('filter-module').value;
            const search = document.getElementById('filter-search').value.toLowerCase();

            const filtered = logs.filter(l => {
                const matchUser = userFilter === 'all' || l.userId === userFilter;
                const matchModule = moduleFilter === 'all' || l.module === moduleFilter;
                const matchSearch = l.description.toLowerCase().includes(search) || l.actionType.toLowerCase().includes(search);
                return matchUser && matchModule && matchSearch;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="empty-state">لا توجد سجلات تطابق بحثك</td></tr>`;
                return;
            }

            filtered.forEach(log => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-size:0.85rem; opacity:0.8;" dir="ltr">${new Date(log.timestamp).toLocaleString('en-GB')}</td>
                    <td style="font-weight:bold;">${log.userName}</td>
                    <td>${log.module}</td>
                    <td><span class="badge" style="${getActionBadgeColor(log.actionType)}">${log.actionType}</span></td>
                    <td style="max-width:300px; line-height:1.4;">${log.description}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    </script>
</body>
</html>

