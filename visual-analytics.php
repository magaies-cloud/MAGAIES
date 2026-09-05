<?php
// visual-analytics.php
// Unified Dashboard for Inventory Charts & Visual Analytics
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحليلات المرئية للمخزون - ELSAJANA</title>
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-blue: #38bdf8;
            --accent-green: #34d399;
            --accent-orange: #fbbf24;
            --accent-red: #f87171;
            --accent-purple: #c084fc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            padding: 20px;
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(192, 132, 252, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 15px 25px;
            border-radius: 16px;
            backdrop-filter: blur(12px);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            background: rgba(255,255,255,0.05);
            color: var(--text-main);
            border: 1px solid var(--glass-border);
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--accent-blue);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .chart-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }

        .chart-card.full-width {
            grid-column: 1 / -1;
        }

        .chart-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 12px;
        }

        .chart-icon {
            font-size: 1.8rem;
            background: rgba(255,255,255,0.05);
            padding: 10px;
            border-radius: 12px;
        }

        .chart-title h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #f8fafc;
        }

        .chart-title p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .chart-container.large {
            height: 400px;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(30,41,59,0.8), rgba(15,23,42,0.8));
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            color: var(--accent-blue);
            margin: 10px 0;
            text-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1><span>📈</span> المنصة التحليلية الذكية (Visual Analytics)</h1>
        <a href="inventory-report.php" class="btn-back"><span>↩</span> العودة للتقرير</a>
    </div>

    <!-- KPI Summary -->
    <div class="stat-cards" id="kpi-container">
        <div class="stat-card">
            <div class="stat-label">إجمالي قيمة المخزون (SDG)</div>
            <div class="stat-value" id="kpi-total-value">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">إجمالي الأصناف</div>
            <div class="stat-value" id="kpi-total-items" style="color: var(--accent-purple);">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">الأصناف تحت الخطر (نواقص)</div>
            <div class="stat-value" id="kpi-danger-items" style="color: var(--accent-red);">0</div>
        </div>
    </div>

    <div class="dashboard-grid">
        
        <!-- Pie Chart -->
        <div class="chart-card" id="pie">
            <div class="chart-header">
                <div class="chart-icon">🍕</div>
                <div class="chart-title">
                    <h2>توزيع قيمة المخزون على المخازن</h2>
                    <p>مخطط دائري (Pie Chart) يوضح النسبة المئوية للأموال المستثمرة في كل مخزن</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="warehousePieChart"></canvas>
            </div>
        </div>

        <!-- Donut Chart -->
        <div class="chart-card" id="donut">
            <div class="chart-header">
                <div class="chart-icon">🍩</div>
                <div class="chart-title">
                    <h2>حالة الأرصدة والمخاطر</h2>
                    <p>كعكة مفرغة (Donut Chart) توضح حالة التوفر والنواقص للأصناف</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="statusDonutChart"></canvas>
            </div>
        </div>

        <!-- Bar Chart -->
        <div class="chart-card full-width" id="bar">
            <div class="chart-header">
                <div class="chart-icon">📊</div>
                <div class="chart-title">
                    <h2>أعلى 10 أصناف من حيث القيمة المالية</h2>
                    <p>مخطط أعمدة (Bar Chart) مصمت يوضح أين يتركز رأس المال</p>
                </div>
            </div>
            <div class="chart-container large">
                <canvas id="topItemsBarChart"></canvas>
            </div>
        </div>

    </div>

    <script>
        // Set Chart.js Defaults for Dark Theme
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
        Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
        Chart.defaults.plugins.tooltip.bodyColor = '#e2e8f0';
        Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.1)';
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        const colors = {
            blue: '#38bdf8',
            green: '#34d399',
            orange: '#fbbf24',
            red: '#f87171',
            purple: '#c084fc',
            pink: '#f472b6',
            yellow: '#fde047',
            teal: '#2dd4bf'
        };
        const colorPalette = Object.values(colors);

        // Core logic to fetch data exactly like inventory-report.php
        function getInventoryData() {
            let items = [];
            let inventory = [];
            try {
                items = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
                inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            } catch(e) { console.error('Error loading data', e); }

            // Aggregate data
            let reportData = [];
            
            items.forEach(item => {
                const itemMoves = inventory.filter(m => m.itemId === item.id);
                
                // Group by warehouse
                const whGroups = {};
                itemMoves.forEach(m => {
                    const wh = m.warehouse || 'المخزن الرئيسي';
                    if (!whGroups[wh]) whGroups[wh] = { in:0, out:0, res:0 };
                    
                    if (m.type === 'PURCHASE' || m.type === 'TRANSFER_IN' || m.type === 'OPENING_BALANCE' || m.type === 'ADJUSTMENT_ADD') {
                        whGroups[wh].in += Number(m.qty || 0);
                    }
                    if (m.type === 'SALE' || m.type === 'TRANSFER_OUT' || m.type === 'ADJUSTMENT_SUB') {
                        whGroups[wh].out += Number(m.qty || 0);
                    }
                    if (m.type === 'RESERVATION') {
                        whGroups[wh].res += Number(m.qty || 0);
                    }
                });

                // Default warehouse if no moves
                if (Object.keys(whGroups).length === 0) {
                    whGroups['المخزن الرئيسي'] = { in:0, out:0, res:0 };
                }

                for (const wh in whGroups) {
                    const actualQty = whGroups[wh].in - whGroups[wh].out;
                    const reservedQty = whGroups[wh].res;
                    const availQty = actualQty - reservedQty;
                    const val = actualQty * Number(item.costPrice || 0);

                    // Status Logic
                    let status = 'OK';
                    if (actualQty <= 0) status = 'DANGER';
                    else if (actualQty <= Number(item.reorderPoint || 0)) status = 'WARNING';

                    reportData.push({
                        itemId: item.id,
                        itemName: item.name,
                        warehouse: wh,
                        actualQty: actualQty,
                        value: val,
                        status: status
                    });
                }
            });

            return reportData;
        }

        function initDashboard() {
            const data = getInventoryData();
            if (!data || data.length === 0) {
                console.warn("No data available for charts.");
                return;
            }

            // 1. Calculate KPIs
            const totalValue = data.reduce((sum, row) => sum + Math.max(0, row.value), 0);
            const dangerCount = data.filter(row => row.status === 'DANGER').length;
            const uniqueItems = new Set(data.map(d => d.itemId)).size;

            document.getElementById('kpi-total-value').innerText = totalValue.toLocaleString();
            document.getElementById('kpi-total-items').innerText = uniqueItems;
            document.getElementById('kpi-danger-items').innerText = dangerCount;

            // 2. Prepare Data for Warehouse Pie Chart
            const whTotals = {};
            data.forEach(row => {
                if(row.actualQty > 0) {
                    whTotals[row.warehouse] = (whTotals[row.warehouse] || 0) + row.value;
                }
            });
            const pieLabels = Object.keys(whTotals);
            const pieValues = Object.values(whTotals);

            new Chart(document.getElementById('warehousePieChart'), {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieValues,
                        backgroundColor: colorPalette.slice(0, pieLabels.length),
                        borderColor: 'rgba(15, 23, 42, 0.8)',
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { padding: 20 } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let val = context.raw || 0;
                                    return label + ': ' + val.toLocaleString() + ' SDG';
                                }
                            }
                        }
                    }
                }
            });

            // 3. Prepare Data for Status Donut Chart
            const statusCounts = { 'OK': 0, 'WARNING': 0, 'DANGER': 0 };
            data.forEach(row => { statusCounts[row.status]++; });

            new Chart(document.getElementById('statusDonutChart'), {
                type: 'doughnut',
                data: {
                    labels: ['رصيد آمن (OK)', 'تحت حد الطلب (Warning)', 'نافذ/نواقص (Danger)'],
                    datasets: [{
                        data: [statusCounts.OK, statusCounts.WARNING, statusCounts.DANGER],
                        backgroundColor: [colors.green, colors.orange, colors.red],
                        borderColor: 'rgba(15, 23, 42, 0.8)',
                        borderWidth: 2,
                        hoverOffset: 10,
                        cutout: '70%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });

            // 4. Prepare Data for Top Items Bar Chart (By Value)
            const itemTotals = {};
            data.forEach(row => {
                if(row.actualQty > 0) {
                    if(!itemTotals[row.itemName]) itemTotals[row.itemName] = 0;
                    itemTotals[row.itemName] += row.value;
                }
            });

            const sortedItems = Object.entries(itemTotals)
                .map(([name, val]) => ({ name, val }))
                .sort((a, b) => b.val - a.val)
                .slice(0, 10);

            const barLabels = sortedItems.map(item => item.name);
            const barValues = sortedItems.map(item => item.val);

            new Chart(document.getElementById('topItemsBarChart'), {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'إجمالي القيمة (SDG)',
                        data: barValues,
                        backgroundColor: 'rgba(56, 189, 248, 0.7)',
                        borderColor: 'rgba(56, 189, 248, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        barThickness: 'flex',
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: {
                                callback: function(value) {
                                    if(value >= 1000000) return (value/1000000).toFixed(1) + 'M';
                                    if(value >= 1000) return (value/1000).toFixed(1) + 'K';
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw.toLocaleString() + ' SDG';
                                }
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', initDashboard);
    </script>

<script src="js/magaiees_back.js?v=2026_back_v1" defer></script>
</body>
</html>
