/**
 * MAGAIEES ERP 2026 - Automated Visual Charting Engine
 * Auto-detects data tables on report screens and appends high-fidelity interactive charts at the bottom.
 */

(function () {
    // 1. Check if we should execute on this page
    const pageName = window.location.pathname.split('/').pop().toLowerCase();
    
    // Skip pages that already have custom tailored canvases or are not reports (transactional screens)
    const excludePages = [
        'next_gen_analytics', 'profitability_analysis', 'login', 'index', 
        'purchase-receipt', 'purchase-return', 'purchase', 'purchase-hub',
        'sales-invoice', 'direct-sales', 'reserved-sales', 'sales-hub',
        'cash-receipt', 'cash-payment', 'journal-voucher', 'pos', 'settings', 'users'
    ];
    if (excludePages.some(ex => pageName.includes(ex)) || pageName === '') {
        return;
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Wait a small delay to ensure dynamic content has loaded
        setTimeout(initAutoChartsEngine, 500);

        // Also observe for dynamic updates to tables (like filtering or async loads)
        const observer = new MutationObserver(function (mutations) {
            let shouldUpdate = false;
            for (let mutation of mutations) {
                // If mutation happens inside our dashboard, ignore it
                if (mutation.target.closest && mutation.target.closest('.auto-chart-dashboard')) {
                    continue;
                }
                // If mutation is inside a table or adds table rows, we should update
                if (mutation.target.closest && mutation.target.closest('table')) {
                    shouldUpdate = true;
                    break;
                }
            }
            if (shouldUpdate) {
                if (window.autoChartsTimeout) clearTimeout(window.autoChartsTimeout);
                window.autoChartsTimeout = setTimeout(initAutoChartsEngine, 300);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    function initAutoChartsEngine() {
        // Find all tables on the page
        const tables = document.querySelectorAll('table');
        if (tables.length === 0) return;

        // Find the main data table (usually the one with the most rows)
        let mainTable = null;
        let maxRows = 0;
        tables.forEach(table => {
            // Do not inject charts into transactional POS grids or editor tables
            if (table.classList.contains('pos-grid') || table.closest('.pos-grid') || table.closest('.view-container') || table.classList.contains('no-auto-chart')) {
                return;
            }

            const rows = table.querySelectorAll('tbody tr');
            if (rows.length > maxRows) {
                maxRows = rows.length;
                mainTable = table;
            }
        });

        // We need at least 3 rows to make a meaningful chart
        if (!mainTable || maxRows < 2) return;

        // Parse the table
        const tableData = parseTableData(mainTable);
        if (!tableData || tableData.labels.length === 0 || tableData.datasets.length === 0) {
            return;
        }

        // Load Chart.js dynamically if needed
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = () => {
                renderDashboard(mainTable, tableData);
            };
            document.head.appendChild(script);
        } else {
            renderDashboard(mainTable, tableData);
        }
    }

    function parseTableData(table) {
        // 1. Parse Headers
        const ths = table.querySelectorAll('thead th');
        let headers = [];
        if (ths.length > 0) {
            ths.forEach(th => headers.push(th.innerText.trim()));
        } else {
            // Fallback to first row
            const firstRow = table.querySelector('tr');
            if (firstRow) {
                const cells = firstRow.querySelectorAll('td, th');
                cells.forEach(cell => headers.push(cell.innerText.trim()));
            }
        }

        if (headers.length < 2) return null;

        // 2. Parse Rows and Detect numeric columns
        const rows = table.querySelectorAll('tbody tr');
        let numericCounts = Array(headers.length).fill(0);
        let totalRows = 0;

        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            if (tds.length === 0) return;

            // Skip total rows
            let text = row.innerText.toLowerCase();
            if (text.includes('إجمالي') || text.includes('المجموع') || text.includes('total') || row.classList.contains('total') || row.classList.contains('sum') || row.style.fontWeight === 'bold' || row.style.fontWeight === '900') {
                return;
            }

            totalRows++;
            tds.forEach((td, idx) => {
                let cleanVal = cleanNumericString(td.innerText);
                if (cleanVal !== '' && !isNaN(cleanVal)) {
                    numericCounts[idx]++;
                }
            });
        });

        if (totalRows === 0) return null;

        // 3. Identify label and value columns
        let labelColIdx = -1;
        let valColIndices = [];

        for (let i = 0; i < headers.length; i++) {
            let ratio = numericCounts[i] / totalRows;
            if (ratio > 0.7) {
                valColIndices.push(i);
            } else {
                let hText = headers[i].toLowerCase();
                if (labelColIdx === -1 && !hText.includes('#') && hText !== 'م' && hText !== 'id' && hText !== 'الرقم' && hText !== 'م.') {
                    labelColIdx = i;
                }
            }
        }

        if (labelColIdx === -1) labelColIdx = 0;
        if (valColIndices.length === 0) return null;

        // 4. Extract data
        let labels = [];
        let datasets = valColIndices.map(colIdx => ({
            label: headers[colIdx] || `المعيار ${colIdx}`,
            data: []
        }));

        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            if (tds.length === 0) return;

            // Skip total rows
            let text = row.innerText.toLowerCase();
            if (text.includes('إجمالي') || text.includes('المجموع') || text.includes('total') || row.classList.contains('total') || row.classList.contains('sum') || row.style.fontWeight === 'bold' || row.style.fontWeight === '900') {
                return;
            }

            // Get label
            let labelVal = tds[labelColIdx] ? tds[labelColIdx].innerText.trim() : '';
            if (!labelVal) labelVal = 'غير محدد';
            labels.push(labelVal);

            // Get values
            valColIndices.forEach((colIdx, dIdx) => {
                let rawVal = tds[colIdx] ? tds[colIdx].innerText.trim() : '0';
                let cleanVal = parseFloat(cleanNumericString(rawVal)) || 0;
                datasets[dIdx].data.push(cleanVal);
            });
        });

        return { labels, datasets };
    }

    function cleanNumericString(str) {
        return str
            .replace(/SDG/gi, '')
            .replace(/USD/gi, '')
            .replace(/\$/g, '')
            .replace(/%/g, '')
            .replace(/,/g, '')
            .replace(/جنيه/g, '')
            .replace(/دولار/g, '')
            .replace(/\+/g, '')
            .replace(/\s+/g, '')
            .trim();
    }

    function formatNumber(num) {
        if (Math.abs(num) >= 1e6) {
            return (num / 1e6).toFixed(1) + 'M';
        } else if (Math.abs(num) >= 1e3) {
            return (num / 1e3).toFixed(1) + 'K';
        }
        return num.toLocaleString(undefined, { maximumFractionDigits: 1 });
    }

    function renderDashboard(table, tableData) {
        // Find container to append to
        const container = table.closest('.container') || table.closest('.glass-panel') || table.closest('.pos-wrapper') || table.closest('.dash-main') || table.parentNode;

        // Remove existing dashboard if any to prevent duplicates on mutation updates
        const existing = container.querySelector('.auto-chart-dashboard');
        if (existing) {
            existing.remove();
        }

        // Build HTML
        const dashboard = document.createElement('div');
        dashboard.className = 'auto-chart-dashboard no-print';
        dashboard.style.cssText = `
            margin-top: 40px; 
            padding: 30px; 
            border-radius: 24px; 
            background: var(--surface-color, rgba(15, 23, 42, 0.45)); 
            border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.08)); 
            box-shadow: var(--shadow-md, 0 8px 32px rgba(0, 0, 0, 0.2)); 
            box-sizing: border-box;
            width: 100%;
        `;

        // Title section
        let titleHtml = `
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(128,128,128,0.1); padding-bottom: 15px; margin-bottom: 25px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; font-size: 20px; font-weight: 900; color: var(--primary-accent, #38bdf8); display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-chart-pie"></i> التحليل البياني البصري التلقائي
                </h3>
                <span style="font-size: 11px; font-weight: bold; background: var(--primary-glow, rgba(56, 189, 248, 0.15)); color: var(--primary-accent, #38bdf8); padding: 4px 12px; border-radius: 50px; border: 1px solid var(--glass-border, rgba(56, 189, 248, 0.15));">
                    ● مؤشر التقارير الذكية الموحد
                </span>
            </div>
        `;

        // Metric cards section
        let metricsHtml = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">';
        
        // Dynamic colors for metrics
        const colors = ['#38bdf8', '#34d399', '#ffa500', '#ff6384', '#a855f7', '#db2777'];
        const icons = ['fa-chart-simple', 'fa-wallet', 'fa-box', 'fa-receipt', 'fa-chart-line', 'fa-gem'];

        tableData.datasets.forEach((dataset, idx) => {
            let totalVal = dataset.data.reduce((sum, val) => sum + val, 0);
            let color = colors[idx % colors.length];
            let icon = icons[idx % icons.length];
            
            // Customize icon based on name
            let name = dataset.label.toLowerCase();
            if (name.includes('ربح') || name.includes('أرباح')) {
                icon = 'fa-sack-dollar';
                color = '#34d399';
            } else if (name.includes('مبيعات') || name.includes('إيراد')) {
                icon = 'fa-cart-shopping';
                color = '#38bdf8';
            } else if (name.includes('تكلفة') || name.includes('مصاريف') || name.includes('شيك')) {
                icon = 'fa-receipt';
                color = '#ff6384';
            } else if (name.includes('رصيد') || name.includes('أرصدة')) {
                icon = 'fa-vault';
                color = '#ffa500';
            }

            // Trend indicator
            let trendHtml = '';
            if (dataset.data.length > 1) {
                let lastVal = dataset.data[dataset.data.length - 1];
                let firstVal = dataset.data[0];
                let diff = lastVal - firstVal;
                let arrow = diff >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                let arrowColor = diff >= 0 ? '#34d399' : '#ff6384';
                trendHtml = `
                    <div style="font-size: 11px; color: ${arrowColor}; font-weight: bold; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
                        <i class="fa-solid ${arrow}"></i> الاتجاه العام: ${diff >= 0 ? '+' : ''}${formatNumber(diff)}
                    </div>
                `;
            }

            metricsHtml += `
                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.06); padding: 20px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <div>
                        <span style="font-size: 12px; color: var(--text-secondary, #94a3b8); font-weight: bold; display: block; margin-bottom: 6px;">إجمالي ${dataset.label}</span>
                        <span style="font-size: 20px; font-weight: 900; color: ${color};" class="num-en">${formatNumber(totalVal)}</span>
                        ${trendHtml}
                    </div>
                    <div style="font-size: 24px; color: ${color}; background: rgba(255,255,255,0.04); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 14px;">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                </div>
            `;
        });
        metricsHtml += '</div>';

        // Charts grid container
        let chartsGridHtml = `
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px;">
                <!-- Donut Chart Card -->
                <div style="background: rgba(0,0,0,0.15); border-radius: 20px; padding: 22px; border: 1px solid rgba(255,255,255,0.04); display: flex; flex-direction: column; align-items: center;">
                    <h4 style="margin-top: 0; margin-bottom: 20px; font-size: 15px; font-weight: 800; color: #fff; text-align: center; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                        <i class="fa-solid fa-chart-pie" style="color: var(--primary-accent, #38bdf8); margin-left: 6px;"></i> كعكة التوزيع النسبية
                    </h4>
                    <div style="position: relative; width: 100%; height: 260px; display: flex; justify-content: center; align-items: center;">
                        <canvas id="autoDonutChart" style="max-width: 250px; max-height: 250px;"></canvas>
                    </div>
                </div>
                
                <!-- Bar Chart Card -->
                <div style="background: rgba(0,0,0,0.15); border-radius: 20px; padding: 22px; border: 1px solid rgba(255,255,255,0.04); display: flex; flex-direction: column;">
                    <h4 style="margin-top: 0; margin-bottom: 20px; font-size: 15px; font-weight: 800; color: #fff; text-align: center; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                        <i class="fa-solid fa-chart-column" style="color: #34d399; margin-left: 6px;"></i> مخطط المقارنة بالأعمدة
                    </h4>
                    <div style="position: relative; width: 100%; height: 260px;">
                        <canvas id="autoBarChart"></canvas>
                    </div>
                </div>
            </div>
        `;

        dashboard.innerHTML = titleHtml + metricsHtml + chartsGridHtml;
        container.appendChild(dashboard);

        // Render Charts using Chart.js
        setTimeout(() => {
            renderCharts(tableData);
        }, 100);
    }

    function renderCharts(tableData) {
        // Group data for the donut chart if there are too many items
        let donutLabels = [...tableData.labels];
        let donutData = [...tableData.datasets[0].data];

        if (donutLabels.length > 7) {
            let sortedWithIndices = donutData.map((val, idx) => ({ val, idx }))
                .sort((a, b) => b.val - a.val);

            let topIndices = sortedWithIndices.slice(0, 6).map(x => x.idx);
            let otherValue = sortedWithIndices.slice(6).reduce((sum, x) => sum + x.val, 0);

            let newLabels = [];
            let newData = [];
            topIndices.forEach(idx => {
                newLabels.push(donutLabels[idx]);
                newData.push(donutData[idx]);
            });
            newLabels.push('أخرى (Others)');
            newData.push(otherValue);

            donutLabels = newLabels;
            donutData = newData;
        }

        // Color Palette
        const backgroundColors = [
            'rgba(56, 189, 248, 0.75)',  // sky-400
            'rgba(52, 211, 153, 0.75)',  // emerald-400
            'rgba(245, 158, 11, 0.75)',   // amber-500
            'rgba(168, 85, 247, 0.75)',  // purple-500
            'rgba(244, 114, 182, 0.75)',  // pink-400
            'rgba(20, 184, 166, 0.75)',   // teal-500
            'rgba(239, 68, 68, 0.75)'     // red-500
        ];

        const borderColors = [
            '#38bdf8', '#34d399', '#f59e0b', '#a855f7', '#f472b6', '#14b8a6', '#ef4444'
        ];

        // 1. Donut Chart
        if (window._autoDonutChart) {
            window._autoDonutChart.destroy();
        }
        const donutCtx = document.getElementById('autoDonutChart').getContext('2d');
        window._autoDonutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#e2e8f0',
                            font: { family: 'Cairo', size: 10 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let val = context.raw;
                                return ` ${context.label}: ${val.toLocaleString()}`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // 2. Bar Chart
        if (window._autoBarChart) {
            window._autoBarChart.destroy();
        }
        const barCtx = document.getElementById('autoBarChart').getContext('2d');
        // Render bar chart with all datasets if there are multiple columns
        const datasets = tableData.datasets.map((ds, idx) => ({
            label: ds.label,
            data: ds.data,
            backgroundColor: borderColors[idx % borderColors.length] + 'cc',
            borderColor: borderColors[idx % borderColors.length],
            borderWidth: 1,
            borderRadius: 5
        }));

        window._autoBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: tableData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#e2e8f0',
                            font: { family: 'Cairo', size: 11 }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { family: 'Cairo', size: 10 }
                        }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#94a3b8',
                            font: { family: 'Cairo', size: 10 }
                        }
                    }
                }
            }
        });
    }
})();
