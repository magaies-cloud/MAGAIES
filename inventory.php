<?php $page_title = 'إدارة المخزون 2026 - مقاييس (MAGAIEES)'; ?>
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
    <title>إدارة المخزون 2026 - مقاييس (MAGAIEES)</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
        <style>
        .search-container {
            padding: 20px 30px;
            max-width: 1600px;
            margin: 0 auto;
            position: sticky;
            top: 70px;
            z-index: 90;
        }
        .search-input {
            width: 100%;
            padding: 15px 20px;
            border-radius: 15px;
            border: 1px solid var(--glass-border);
            background: var(--surface-color);
            color: var(--text-primary);
            font-size: 1.1rem;
            box-shadow: var(--shadow-sm);
            outline: none;
            transition: box-shadow 0.3s;
        }
        .search-input:focus {
            box-shadow: 0 0 10px var(--primary-accent);
        }
        .inventory-list {
            padding: 0 30px 100px 30px;
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .inventory-card {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .product-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-name {
            font-weight: 800;
            font-size: 1.2rem;
        }
        .product-sku {
            font-size: 0.8rem;
            color: var(--text-secondary);
            background: var(--bg-color);
            padding: 4px 8px;
            border-radius: 8px;
        }
        .balance-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
        }
        .balance-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .balance-val {
            font-weight: 900;
            font-size: 1.1rem;
        }
        .balance-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        .val-physical { color: var(--text-primary); }
        .val-reserved { color: var(--danger-color); }
        .val-available { color: var(--success-color); }

        /* Progress Bar for Stock */
        .stock-progress-container {
            width: 100%;
            height: 8px;
            background: var(--bg-color);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }
        .stock-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--success-color), #86efac);
            transition: width 1s ease-in-out;
        }

        .back-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-primary);
        }

        .no-results {
            text-align: center;
            padding: 50px;
            color: var(--text-secondary);
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

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


            <!-- Unified Header -->
        <?php $header_theme_class = 'header-theme-inventory'; ?>
        <?php include 'includes/header.php'; ?>

    <div class="search-container">
        <input type="text" class="search-input" placeholder="ابحث عن صنف (الاسم، الباركود...)" id="search-box">
    </div>

    <div class="inventory-list" id="inventory-container">
        <!-- Will be populated by JS -->
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DBReady', () => {
            const searchBox = document.getElementById('search-box');
            const container = document.getElementById('inventory-container');

            // Fetch function (now calling the real Backend API)
            const fetchInventory = async (query) => {
                try {
                    container.innerHTML = '<div class="no-results">جاري التحميل...</div>';
                    
                    // In a real scenario, we'd encode the query
                    const url = query 
                        ? \`http://localhost:3000/api/v1/inventory/search/\${encodeURIComponent(query)}\`
                        : \`http://localhost:3000/api/v1/inventory/search/all\`; // Assuming 'all' returns everything
                    
                    const response = await fetch(url, {
                        headers: {
                            'x-device-uuid': 'DEV-MODE-2026' // Bypass enabled in Dev Mode
                        }
                    });

                    if (!response.ok) throw new Error('Network response was not ok');
                    return await response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                    return [];
                }
            };

            const renderData = (data) => {
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = '<div class="no-results">لا توجد أصناف تطابق بحثك</div>';
                    return;
                }

                data.forEach(item => {
                    const availablePercent = (item.available / item.physical) * 100;
                    const card = document.createElement('div');
                    card.className = 'inventory-card glass-panel';
                    card.innerHTML = \`
                        <div class="product-info">
                            <div class="product-name">\${item.name}</div>
                            <div class="product-sku">\${item.sku}</div>
                        </div>
                        <div class="balance-grid">
                            <div class="balance-item">
                                <span class="balance-val val-physical">\${item.physical.toLocaleString()}</span>
                                <span class="balance-label">الفعلي</span>
                            </div>
                            <div class="balance-item">
                                <span class="balance-val val-reserved">\${item.reserved.toLocaleString()}</span>
                                <span class="balance-label">المحجوز</span>
                            </div>
                            <div class="balance-item">
                                <span class="balance-val val-available">\${item.available.toLocaleString()}</span>
                                <span class="balance-label">المتوفر</span>
                            </div>
                        </div>
                        <div class="stock-progress-container">
                            <div class="stock-progress-bar" style="width: \${availablePercent}%"></div>
                        </div>
                    \`;
                    container.appendChild(card);
                });
            };

            // Initial Load
            fetchInventory('').then(renderData);

            // Search logic
            searchBox.addEventListener('input', (e) => {
                fetchInventory(e.target.value).then(renderData);
            });
        });
    </script>
</body>
</html>

