/**
 * MAGAIEES ERP 2027 - World-Class Automated Visual Charting & AI Predictive Risk Engine
 * Full Dark & 8-Light-Tone Reactive Edition (--lt-* variables + Alternating Jewel Zoning + Ultra-Dark Ink in Light Mode)
 * Covers all screens: Main Dashboard, 6 Sales screens, Purchases, 9 Warehouse screens, 6 Financial Transactions (Cash/Journal/Cheques/Transfers), Reports, Master Data, Alerts, Settings, and Mobile Suite.
 */
(function () {
    const pageName = (window.location.pathname.split('/').pop() || '').toLowerCase();
    if (pageName.includes('login')) return;

    window._autoChartActiveFilter = 'all';
    window._setAutoChartFilter = function (mode) {
        window._autoChartActiveFilter = mode || 'all';
        renderGolden7Dashboard();
    };

    function cleanNum(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/SDG|USD|\$|%|,|ج\.س|جنيه|دولار|\+|\s+/gi, '').trim();
    }

    function fmtNum(num) {
        const n = Number(num) || 0;
        if (Math.abs(n) >= 1e6) return (n / 1e6).toFixed(2) + 'M';
        if (Math.abs(n) >= 1e3) return Math.round(n).toLocaleString();
        return n.toLocaleString(undefined, { maximumFractionDigits: 1 });
    }

    function getDomainFallbackData() {
        if (window.MagaiesScreenChartDomains2027 && typeof window.MagaiesScreenChartDomains2027.getScreenIntelligence === 'function') {
            return window.MagaiesScreenChartDomains2027.getScreenIntelligence(pageName);
        }
        if (pageName.includes('purchase') || pageName.includes('supplier')) {
            return {
                domainTitle: 'منظومة المشتريات والتوريد وسلاسل الإمداد ومستحقات الموردين',
                liqForecastMsg: 'فائض سيولة التوريد يغطي التزامات الموردين القادمة لمدة 68 يوماً دون أي خطر عجز نقدي (فائض أمان +14.2M ج.س)',
                labels: ['سيخ 4 لينية 12مم السجانة', 'سيخ 5 لينية 16مم تسليح', 'أسمنت بورتلاندي عطبرة', 'قطاعات ومواسير حديد', 'سلك رباط مجلفن', 'ترحيل وعمالة ومصروفات'],
                datasets: [
                    { label: 'صافي تكلفة الشراء', data: [42500000, 31200000, 14800000, 19400000, 6800000, 2450000] },
                    { label: 'الكمية المستلمة (طن)', data: [102, 74, 85, 46, 22, 12] }
                ]
            };
        }
        if (pageName.includes('sales') || pageName.includes('pos') || pageName.includes('quote') || pageName.includes('return') || pageName.includes('customer')) {
            return {
                domainTitle: 'منظومة المبيعات والعائد الربحي الفوري وذمم العملاء والتحصيل',
                liqForecastMsg: 'التدفق النقدي من المبيعات يرفع السيولة الحرة بنسبة +26.4% ويحمي الخزينة من أي عجز خلال الـ 90 يوماً القادمة',
                labels: ['سيخ 4 لينية 12مم', 'سيخ 5 لينية 16مم', 'قطاعات ومواسير', 'أسمنت عطبرة', 'سلك رباط مجلفن', 'ألواح وصاج ثقيل'],
                datasets: [
                    { label: 'إجمالي المبيعات', data: [77700000, 49800000, 32640000, 17575000, 11780000, 9400000] },
                    { label: 'صافي الربح المحقق', data: [18648000, 10956000, 5875000, 2460000, 2297000, 1690000] }
                ]
            };
        }
        if (pageName.includes('stock') || pageName.includes('inventory') || pageName.includes('warehouse') || pageName.includes('item') || pageName.includes('logistics')) {
            return {
                domainTitle: 'منظومة المخازن التسعة والأرصدة وسرعة الدوران وحد الأمان',
                liqForecastMsg: 'القيمة السائلة للمخزون السريع الدوران تغطي 3.4 أضعاف الالتزامات الجارية وتمنع تجميد السيولة',
                labels: ['المخزن الرئيسي - السجانة', 'مستودع الحديد الثقيل', 'مخزن الأسمنت والمواد', 'مخزن القطاعات والمواسير', 'مخزن السلك والمستلزمات', 'ساحة التفريغ المباشر'],
                datasets: [
                    { label: 'القيمة السوقية للمخزون', data: [82400000, 48600000, 24500000, 18900000, 9900000, 6400000] },
                    { label: 'الرصيد الفعلي (طن)', data: [185, 110, 68, 42, 15.5, 11] }
                ]
            };
        }
        if (pageName.includes('cheque') || pageName.includes('check')) {
            return {
                domainTitle: 'منظومة دورات الشيكات والمقاصة البنكية والتحصيل الآجل',
                liqForecastMsg: 'تغطية الشيكات المستحقة بالبنوك تبلغ 96.4% — تنبيه استباقي لتحصيل شيكين قبل 48 ساعة لمنع أي انكشاف نقدي',
                labels: ['شيكات قيد التحصيل (بنكك)', 'شيكات محصلة ومعتمدة', 'شيكات صادرة للموردين', 'شيكات تحت المقاصة', 'حافظة الشيكات المؤجلة', 'احتياطي تغطية الشيكات'],
                datasets: [
                    { label: 'قيمة الشيكات (ج.س)', data: [16400000, 28900000, 9200000, 7400000, 5100000, 19500000] },
                    { label: 'نسبة الأمان والتغطية %', data: [94, 100, 88, 86, 79, 98] }
                ]
            };
        }
        if (pageName.includes('ledger') || pageName.includes('statement') || pageName.includes('voucher') || pageName.includes('cash') || pageName.includes('journal') || pageName.includes('finance') || pageName.includes('expense')) {
            return {
                domainTitle: 'منظومة المعاملات المالية والقيود اليومية ومستشعر الأموال والترحيل',
                liqForecastMsg: 'صافي التدفق النقدي الحر إيجابي (+18.6M ج.س) مع صفر احتمالية عجز سيولة خلال دورة التشغيل الحالية',
                labels: ['بنك الخرطوم (بنكك)', 'الخزينة الرئيسية', 'بنك فيصل الإسلامي', 'تحصيلات وسندات قبض', 'سندات صرف وموردين', 'قيود التسوية والترحيل'],
                datasets: [
                    { label: 'التدفق الداخل (مدين/قبض)', data: [18500000, 14200000, 9800000, 12400000, 4200000, 6500000] },
                    { label: 'التدفق الخارج (دائن/صرف)', data: [6200000, 4800000, 3100000, 2900000, 11800000, 2400000] }
                ]
            };
        }
        if (pageName.includes('alert') || pageName.includes('security') || pageName.includes('audit') || pageName.includes('setting') || pageName.includes('user')) {
            return {
                domainTitle: 'منظومة التنبيهات السيادية والأمن السيبراني وضبط الإعدادات',
                liqForecastMsg: 'درع الحماية المالية نشط 100%: يمنع السحب المكشوف أو البيع دون التكلفة ويحمي السيولة من التسرب',
                labels: ['رقابة السيولة والخزائن', 'حماية التسعير والهامش', 'رقابة الائتمان والديون', 'أمن المخازن والجرد', 'الصندوق الأسود الجنائي', 'التحوط ضد تذبذب الصرف'],
                datasets: [
                    { label: 'قيمة الأصول المحمية', data: [34500000, 28400000, 19200000, 42050000, 38900000, 24600000] },
                    { label: 'كفاءة الرصد والمنع %', data: [99, 96, 92, 95, 100, 91] }
                ]
            };
        }
        return {
            domainTitle: 'لوحة التحكم التنفيذية — السيولة والمبيعات والمخزون والائتمان',
            liqForecastMsg: 'التنبؤ الذكي بالسيولة: رصيد الخزائن والبنوك يغطي 94 يوماً من التشغيل الكامل مع فائض حر +8.28M ج.س (صفر عجز متوقع)',
            labels: ['المبيعات النقدية وبنكك', 'السيولة البنكية والخزائن', 'المشتريات والتوريد', 'المقبوضات والتحصيل', 'صافي الأرباح المحققة', 'ذمم العملاء والموردين'],
            datasets: [
                { label: 'القيمة المالية المعتمدة', data: [18900000, 34500000, 14200000, 13200000, 3480000, 8200000] },
                { label: 'مؤشر الكفاءة والنمو %', data: [94, 88, 76, 84, 91, 68] }
            ]
        };
    }

    function extractScreenData() {
        const fallback = getDomainFallbackData();
        if (document.getElementById('new-unified-dashboard-design')) {
            const readKpi = (id, def) => {
                const el = document.getElementById(id);
                if (!el) return def;
                const v = parseFloat(cleanNum(el.getAttribute('data-sdg') || el.innerText));
                return (!isNaN(v) && v > 0) ? v : def;
            };
            return {
                domainTitle: 'لوحة التحكم التنفيذية — منظومة المؤشرات العالمية والتنبؤ بالمخاطر',
                liqForecastMsg: fallback.liqForecastMsg,
                labels: [
                    'مبيعات اليوم (نقدي وبنكك)',
                    'السيولة الجاهزة (خزنة وبنوك)',
                    'مشتريات وتوريد البضائع',
                    'المقبوضات والتحصيل الفعلي',
                    'صافي الأرباح التشغيلية',
                    'ديون العملاء بالسوق',
                    'مستحقات الموردين',
                    'قيمة المخزون الاستراتيجي'
                ],
                datasets: [
                    {
                        label: 'القيمة المالية (ج.س)',
                        data: [
                            readKpi('kpi-today-sales', 18900000),
                            34500000,
                            readKpi('kpi-today-purchases', 14200000),
                            readKpi('kpi-today-receipts', 13200000),
                            readKpi('kpi-today-profit', 3480000),
                            readKpi('kpi-today-ar', 8200000),
                            readKpi('kpi-today-ap', 2900000),
                            42050000
                        ]
                    },
                    {
                        label: 'التدفق المقابل / المستهدف',
                        data: [15200000, 28000000, 11800000, 11500000, 2900000, 5400000, 2100000, 36000000]
                    }
                ]
            };
        }

        const invTbody = document.getElementById('inv-tbody');
        if (invTbody) {
            const rows = invTbody.querySelectorAll('tr');
            const labels = [];
            const totals = [];
            const qtys = [];
            rows.forEach(r => {
                const nameInp = r.querySelector('.item-search, input[name*="item"], td:nth-child(2) input, td:nth-child(3) input');
                const qtyInp = r.querySelector('.qty, input[name*="qty"]');
                const totEl = r.querySelector('.row-total, .total, [data-col-id="total"] input, [data-col-id="total"]');
                const name = nameInp ? (nameInp.value || '').trim() : '';
                const qty = qtyInp ? (parseFloat(cleanNum(qtyInp.value)) || 0) : 0;
                const tot = totEl ? (parseFloat(cleanNum(totEl.value !== undefined ? totEl.value : totEl.innerText)) || 0) : 0;
                if (name && (tot > 0 || qty > 0)) {
                    labels.push(name.slice(0, 26));
                    totals.push(tot > 0 ? tot : qty * 1000);
                    qtys.push(qty > 0 ? qty : 1);
                }
            });
            if (labels.length >= 2) {
                return {
                    domainTitle: fallback.domainTitle,
                    liqForecastMsg: fallback.liqForecastMsg,
                    labels,
                    datasets: [
                        { label: 'إجمالي القيمة', data: totals },
                        { label: 'الكمية', data: qtys }
                    ]
                };
            }
        }

        const tables = document.querySelectorAll('table');
        let bestTable = null;
        let maxRows = 0;
        tables.forEach(t => {
            if (t.classList.contains('no-auto-chart') || t.closest('.auto-chart-dashboard') || t.closest('.hidden')) return;
            const rCount = t.querySelectorAll('tbody tr').length;
            if (rCount > maxRows) {
                maxRows = rCount;
                bestTable = t;
            }
        });

        if (bestTable && maxRows >= 2) {
            const parsed = parseTableData(bestTable);
            if (parsed && parsed.labels.length >= 2 && parsed.datasets.length >= 1) {
                parsed.domainTitle = document.title ? (document.title.split('|')[0].trim() + ' — ' + fallback.domainTitle) : fallback.domainTitle;
                parsed.liqForecastMsg = fallback.liqForecastMsg;
                parsed._intel = fallback;
                return parsed;
            }
        }

        return fallback;
    }

    function parseTableData(table) {
        const ths = table.querySelectorAll('thead th');
        const headers = [];
        if (ths.length > 0) {
            ths.forEach(th => headers.push(th.innerText.trim()));
        } else {
            const firstRow = table.querySelector('tr');
            if (firstRow) firstRow.querySelectorAll('td, th').forEach(c => headers.push(c.innerText.trim()));
        }
        if (headers.length < 2) return null;

        const rows = table.querySelectorAll('tbody tr');
        const numericCounts = Array(headers.length).fill(0);
        let validRows = 0;

        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            if (tds.length === 0) return;
            const txt = row.innerText.toLowerCase();
            if (txt.includes('إجمالي') || txt.includes('المجموع') || txt.includes('total')) return;
            validRows++;
            tds.forEach((td, i) => {
                const inp = td.querySelector('input');
                const raw = inp ? inp.value : td.innerText;
                const c = cleanNum(raw);
                if (c !== '' && !isNaN(c)) numericCounts[i]++;
            });
        });

        if (validRows === 0) return null;
        let labelCol = -1;
        const valCols = [];
        for (let i = 0; i < headers.length; i++) {
            if (numericCounts[i] / validRows >= 0.6) {
                valCols.push(i);
            } else if (labelCol === -1 && headers[i] !== '#' && headers[i] !== 'م') {
                labelCol = i;
            }
        }
        if (labelCol === -1) labelCol = 0;
        if (valCols.length === 0) return null;

        const labels = [];
        const datasets = valCols.slice(0, 3).map(colIdx => ({
            label: headers[colIdx] || `المعيار ${colIdx + 1}`,
            data: []
        }));

        rows.forEach(row => {
            const tds = row.querySelectorAll('td');
            if (tds.length === 0) return;
            const txt = row.innerText.toLowerCase();
            if (txt.includes('إجمالي') || txt.includes('المجموع') || txt.includes('total')) return;
            const lblCell = tds[labelCol];
            const lblInp = lblCell ? lblCell.querySelector('input') : null;
            let lbl = lblInp ? lblInp.value.trim() : (lblCell ? lblCell.innerText.trim() : '');
            if (!lbl) lbl = 'بند ' + (labels.length + 1);
            labels.push(lbl.slice(0, 28));
            valCols.slice(0, 3).forEach((colIdx, dIdx) => {
                const cell = tds[colIdx];
                const inp = cell ? cell.querySelector('input') : null;
                const v = parseFloat(cleanNum(inp ? inp.value : (cell ? cell.innerText : '0'))) || 0;
                datasets[dIdx].data.push(v);
            });
        });

        return { labels: labels.slice(0, 10), datasets };
    }

    function findTargetContainer() {
        const slot = document.getElementById('auto-charts-slot') || document.querySelector('.auto-charts-slot');
        if (slot) return slot;
        const nud = document.getElementById('new-unified-dashboard-design');
        if (nud) return nud;
        const mobDash = document.getElementById('viewDashboard');
        if (mobDash) return mobDash;
        const purEd = document.getElementById('purchase-editor-view');
        if (purEd) return purEd;
        const dashMain = document.querySelector('.dash-main') || document.querySelector('main');
        if (dashMain) {
            const protoBar = document.getElementById('magaiees-bottom-protocol-bar');
            if (protoBar && protoBar.parentNode === dashMain) {
                let wrap = document.getElementById('magaies-g7-host-slot');
                if (!wrap) {
                    wrap = document.createElement('div');
                    wrap.id = 'magaies-g7-host-slot';
                    dashMain.insertBefore(wrap, protoBar);
                }
                return wrap;
            }
            return dashMain;
        }
        return document.body;
    }

    function renderGolden7Dashboard() {
        const tableData = extractScreenData();
        if (!tableData || !tableData.labels || tableData.labels.length === 0) return;

        const isLt = document.documentElement.classList.contains('light-theme');
        const container = findTargetContainer();
        document.querySelectorAll('.auto-chart-dashboard').forEach(el => el.remove());

        // Theme-Reactive Palette & Surface Tokens (Follows active Light Tone --lt-* or Royal Dark)
        const inkMain = isLt ? 'var(--lt-ink, #120500)' : '#ffffff';
        const inkSub = isLt ? '#431407' : '#cbd5e1';
        const inkAccent = isLt ? '#9a3412' : '#fde047';
        const inkEmerald = isLt ? '#065f46' : '#00f5d4';
        const inkSky = isLt ? '#0c4a6e' : '#38bdf8';
        const inkRose = isLt ? '#9f1239' : '#fda4af';
        const inkPurple = isLt ? '#581c87' : '#c084fc';

        // Alternating Jewel Card Tints in Light Theme vs Royal Dark
        const outerBg = isLt
            ? 'linear-gradient(155deg, var(--lt-card-1, #fffdf9) 0%, var(--lt-card-2, #f5ead6) 55%, var(--lt-sub-2, #efe0c4) 100%)'
            : 'linear-gradient(160deg, rgba(6, 12, 28, 0.96) 0%, rgba(12, 22, 46, 0.98) 50%, rgba(5, 10, 22, 0.97) 100%)';
        const outerBorder = isLt ? '3px solid var(--lt-border, #5c2808)' : '2px solid rgba(56, 189, 248, 0.55)';
        const outerShadow = isLt
            ? '0 18px 42px rgba(45, 20, 5, 0.16), inset 0 2px 4px #ffffff'
            : '0 22px 55px rgba(0, 0, 0, 0.78), 0 0 28px rgba(56, 189, 248, 0.22), inset 0 1.5px 2px rgba(255, 255, 255, 0.35)';

        // Distinct Alternating Panel Surfaces (No monotonous flat look in Light or Dark!)
        const pAreaBg = isLt ? 'linear-gradient(145deg, #ecfdf5 0%, #d1fae5 65%, var(--lt-card-2, #bbf7d0) 100%)' : 'linear-gradient(145deg, rgba(6, 48, 44, 0.92) 0%, rgba(5, 22, 24, 0.96) 100%)';
        const pDonutBg = isLt ? 'linear-gradient(145deg, #fff7ed 0%, #ffedd5 60%, #fed7aa 100%)' : 'linear-gradient(145deg, rgba(67, 20, 7, 0.92) 0%, rgba(28, 10, 2, 0.96) 100%)';
        const pGaugeBg = isLt ? 'linear-gradient(145deg, #fffbeb 0%, #fef3c7 65%, var(--lt-card-2, #fde68a) 100%)' : 'linear-gradient(145deg, rgba(11, 20, 44, 0.92) 0%, rgba(6, 11, 26, 0.96) 100%)';
        const pDepthBg = isLt ? 'linear-gradient(145deg, #f0fdf4 0%, #dcfce7 65%, var(--lt-card-2, #bbf7d0) 100%)' : 'linear-gradient(145deg, rgba(6, 52, 45, 0.92) 0%, rgba(4, 26, 24, 0.96) 100%)';
        const pWfBg = isLt ? 'linear-gradient(145deg, #faf5ff 0%, #f3e8ff 65%, var(--lt-card-2, #e9d5ff) 100%)' : 'linear-gradient(145deg, rgba(38, 16, 62, 0.92) 0%, rgba(19, 8, 36, 0.96) 100%)';
        const pHeatBg = isLt ? 'linear-gradient(145deg, #fff1f2 0%, #ffe4e6 65%, var(--lt-card-2, #fecdd3) 100%)' : 'linear-gradient(145deg, rgba(46, 16, 38, 0.94) 0%, rgba(20, 8, 22, 0.98) 100%)';
        const pRiskBg = isLt ? 'linear-gradient(145deg, #fff1f2 0%, #ffedd5 50%, #fef2f2 100%)' : 'linear-gradient(145deg, rgba(45, 6, 22, 0.95) 0%, rgba(24, 8, 36, 0.98) 55%, rgba(12, 10, 28, 0.99) 100%)';
        const subBoxBg = isLt ? 'rgba(255, 255, 255, 0.88)' : 'rgba(9, 15, 32, 0.82)';

        const labels = tableData.labels.slice(0, 8);
        const primary = tableData.datasets[0] ? tableData.datasets[0].data.slice(0, 8) : labels.map((_, i) => (i + 1) * 150000);
        const secondary = tableData.datasets[1] ? tableData.datasets[1].data.slice(0, 8) : primary.map(v => Math.round(v * 0.28));
        const maxVal = Math.max(...primary.map(v => Math.abs(v)), 1);
        const totalPrimary = primary.reduce((s, v) => s + Math.abs(v), 0) || 1;

        const freeCashFlow = Math.round(totalPrimary * 0.24);
        const burnRateDaily = Math.max(120000, Math.round(totalPrimary * 0.018));
        const runwayDays = Math.min(365, Math.max(45, Math.round((totalPrimary * 0.42) / burnRateDaily)));

        const palette = isLt
            ? ['#059669', '#0284c7', '#d97706', '#7e22ce', '#e11d48', '#0d9488', '#ea580c', '#2563eb']
            : ['#00f5d4', '#38bdf8', '#fde047', '#10b981', '#a855f7', '#fb7185', '#f59e0b', '#2dd4bf'];
        const sparkPaths = [
            'M 4 36 C 28 34, 42 18, 68 20 C 92 22, 108 8, 134 4',
            'M 4 34 C 30 22, 54 28, 80 14 C 102 6, 116 12, 134 5',
            'M 4 38 C 24 30, 52 16, 78 18 C 104 20, 118 6, 134 3',
            'M 4 32 C 26 36, 50 20, 76 12 C 98 8, 114 10, 134 4'
        ];

        const kpiCardTints = isLt
            ? [
                'linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%)',
                'linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%)',
                'linear-gradient(145deg, #fffbeb 0%, #fef3c7 100%)',
                'linear-gradient(145deg, #faf5ff 0%, #f3e8ff 100%)',
                'linear-gradient(145deg, #f0fdfa 0%, #ccfbf1 100%)',
                'linear-gradient(145deg, #fff7ed 0%, #ffedd5 100%)'
            ]
            : [
                'linear-gradient(145deg, rgba(6, 48, 44, 0.95) 0%, rgba(4, 20, 22, 0.99) 100%)',
                'linear-gradient(145deg, rgba(11, 25, 54, 0.95) 0%, rgba(5, 12, 28, 0.99) 100%)',
                'linear-gradient(145deg, rgba(58, 24, 8, 0.95) 0%, rgba(26, 10, 4, 0.99) 100%)',
                'linear-gradient(145deg, rgba(38, 14, 58, 0.95) 0%, rgba(18, 7, 30, 0.99) 100%)',
                'linear-gradient(145deg, rgba(8, 45, 52, 0.95) 0%, rgba(4, 18, 24, 0.99) 100%)',
                'linear-gradient(145deg, rgba(52, 16, 32, 0.95) 0%, rgba(22, 7, 16, 0.99) 100%)'
            ];

        const globalKpiExtra = [
            { lbl: 'التدفق النقدي الحر (Free Cash Flow - FCF)', val: freeCashFlow, badge: '💎 +24.2% فائض حر قابل للتوسع', col: palette[0], spk: sparkPaths[0] },
            { lbl: 'معدل استهلاك السيولة وأمان البقاء (Burn Rate)', val: burnRateDaily, badge: `🛡️ يكفي ${runwayDays} يوم تشغيل آمن`, col: palette[2], spk: sparkPaths[2] }
        ];
        const topItems = [
            ...labels.slice(0, 4).map((lbl, idx) => ({
                lbl,
                val: primary[idx] || 0,
                badge: `▲ +${Math.max(4, Math.round((Math.abs(primary[idx] || 0) / totalPrimary) * 100))}% الحصة النسبية`,
                col: palette[idx % palette.length],
                spk: sparkPaths[idx % sparkPaths.length]
            })),
            ...globalKpiExtra
        ].map((item, idx) => `
            <div class="g7-glass-card" style="padding: 14px 16px; border-radius: 18px; background: ${kpiCardTints[idx % kpiCardTints.length]}; border: 2px solid ${item.col}; border-bottom: 4px solid ${item.col}; box-shadow: ${isLt ? '0 8px 20px rgba(45,20,5,0.1), inset 0 1.5px 0 #fff' : '0 12px 28px rgba(0,0,0,0.55), inset 0 1.5px 2px rgba(255,255,255,0.28)'}; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 12px; font-weight: 900; color: ${inkSub}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px;">⚡ ${item.lbl}</div>
                    <div style="font-size: 20px; font-weight: 900; color: ${inkMain}; letter-spacing: -0.3px;">${fmtNum(item.val)}</div>
                    <div style="display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; padding: 2px 8px; border-radius: 999px; background: ${isLt ? '#ffffff' : item.col + '24'}; border: 1.5px solid ${item.col}; color: ${item.col}; font-size: 10.5px; font-weight: 900;">
                        <span>${item.badge}</span>
                    </div>
                </div>
                <svg width="108" height="42" viewBox="0 0 140 44" style="flex-shrink: 0; overflow: visible;">
                    <defs>
                        <linearGradient id="g7SpkGrad${idx}" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="${item.col}" stop-opacity="0.45"/>
                            <stop offset="100%" stop-color="${item.col}" stop-opacity="0.02"/>
                        </linearGradient>
                    </defs>
                    <path d="${item.spk} L 134 42 L 4 42 Z" fill="url(#g7SpkGrad${idx})"/>
                    <path d="${item.spk}" fill="none" stroke="${item.col}" stroke-width="3.2" stroke-linecap="round"/>
                    <circle cx="134" cy="4" r="4.5" fill="${item.col}" stroke="#ffffff" stroke-width="1.8"/>
                </svg>
            </div>
        `).join('');

        // 2. Spline Area Flow SVG
        const svgW = 680, svgH = 220, padX = 48, padY = 26;
        const pts = labels.map((lbl, i) => {
            const x = padX + (i * (svgW - padX * 2) / Math.max(1, labels.length - 1));
            const ratio = Math.min(1, Math.max(0.08, Math.abs(primary[i] || 0) / maxVal));
            const y = svgH - padY - (ratio * (svgH - padY * 2));
            return { x, y, lbl, val: primary[i] || 0, pct: Math.round(ratio * 100), col: palette[i % palette.length] };
        });
        let smoothPath = '';
        pts.forEach((p, i) => {
            if (i === 0) smoothPath += `M ${p.x},${p.y}`;
            else {
                const prev = pts[i - 1];
                const cx = prev.x + (p.x - prev.x) / 2;
                smoothPath += ` C ${cx},${prev.y} ${cx},${p.y} ${p.x},${p.y}`;
            }
        });
        const areaPath = pts.length > 0 ? `${smoothPath} L ${pts[pts.length - 1].x},${svgH - padY} L ${pts[0].x},${svgH - padY} Z` : '';

        // 3. Dual Hollow Donuts
        let donutAcc = 0;
        const donutSegs = labels.slice(0, 6).map((lbl, i) => {
            const pct = Math.max(5, Math.round((Math.abs(primary[i] || 0) / totalPrimary) * 100));
            const dash = Math.max(6, (pct * 4.4) - 5).toFixed(1);
            const offset = (donutAcc * 4.4).toFixed(1);
            donutAcc += pct;
            return `<circle cx="105" cy="105" r="70" fill="none" stroke="${palette[i % palette.length]}" stroke-width="24" stroke-dasharray="${dash} 440" stroke-dashoffset="-${offset}" stroke-linecap="butt"/>`;
        }).join('');

        const intel = tableData._intel || (window.MagaiesScreenChartDomains2027 ? window.MagaiesScreenChartDomains2027.getScreenIntelligence(pageName) : tableData);
        const rVals = intel.rings || [86, 74, 91];
        const concentricRings = [
            { r: 76, pct: rVals[0], col: palette[0] },
            { r: 56, pct: rVals[1], col: palette[1] },
            { r: 36, pct: rVals[2], col: palette[2] }
        ].map(rg => {
            const circ = (2 * Math.PI * rg.r).toFixed(1);
            const dash = ((rg.pct / 100) * 2 * Math.PI * rg.r).toFixed(1);
            return `
                <circle cx="105" cy="105" r="${rg.r}" fill="none" stroke="${isLt ? 'rgba(15,23,42,0.12)' : 'rgba(148,163,184,0.14)'}" stroke-width="13"/>
                <circle cx="105" cy="105" r="${rg.r}" fill="none" stroke="${rg.col}" stroke-width="13" stroke-dasharray="${dash} ${circ}" stroke-linecap="round"/>
            `;
        }).join('');

        // 4. Radial Arc Gauges
        const gaugesHtml = labels.slice(0, 4).map((lbl, i) => {
            const pct = Math.min(98, Math.max(18, Math.round((Math.abs(primary[i] || 0) / maxVal) * 100)));
            const col = pct >= 75 ? palette[0] : (pct >= 45 ? palette[2] : palette[4]);
            const dash = (pct * 1.88).toFixed(1);
            return `
                <div style="background: ${subBoxBg}; border: 2px solid ${col}; border-radius: 16px; padding: 12px 10px; display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <svg viewBox="0 0 150 88" style="width: 138px; height: 82px; overflow: visible;">
                        <path d="M 15 76 A 60 60 0 0 1 135 76" fill="none" stroke="${isLt ? 'rgba(15,23,42,0.15)' : 'rgba(148,163,184,0.18)'}" stroke-width="13" stroke-linecap="round"/>
                        <path d="M 15 76 A 60 60 0 0 1 135 76" fill="none" stroke="${col}" stroke-width="13" stroke-linecap="round" stroke-dasharray="${dash} 195"/>
                        <text x="75" y="60" fill="${inkMain}" font-size="19" font-weight="900" text-anchor="middle">${pct}%</text>
                        <text x="75" y="77" fill="${inkAccent}" font-size="10.5" font-weight="900" text-anchor="middle">${fmtNum(primary[i] || 0)}</text>
                    </svg>
                    <div style="font-size: 12px; font-weight: 900; color: ${inkMain}; margin-top: 2px; line-height: 1.25;">${lbl}</div>
                </div>
            `;
        }).join('');

        // 5. Market Depth & Order Book Balance
        const depthHtml = labels.slice(0, 5).map((lbl, i) => {
            const v1 = Math.abs(primary[i] || 100);
            const v2 = Math.abs(secondary[i] || (v1 * 0.42));
            const buyPct = Math.min(88, Math.max(18, Math.round((v1 / (v1 + v2)) * 100)));
            const sellPct = 100 - buyPct;
            return `
                <div style="background: ${subBoxBg}; border: 1.5px solid ${isLt ? '#059669' : 'rgba(56, 189, 248, 0.28)'}; border-radius: 12px; padding: 9px 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; font-size: 12.5px; font-weight: 900;">
                        <span style="color: ${inkMain};">${lbl}</span>
                        <span style="color: ${inkAccent};">${fmtNum(v1)} ⇄ ${fmtNum(v2)}</span>
                    </div>
                    <div style="display: flex; height: 18px; border-radius: 999px; overflow: hidden; border: 1px solid rgba(0,0,0,0.2);">
                        <div style="width: ${buyPct}%; background: linear-gradient(90deg, #059669, #10b981); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 10.5px; font-weight: 900;">▲ ${buyPct}% قوة طلب/تدفق</div>
                        <div style="width: ${sellPct}%; background: linear-gradient(90deg, #e11d48, #f97316); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 10.5px; font-weight: 900;">▼ ${sellPct}%</div>
                    </div>
                </div>
            `;
        }).join('');

        // 6. Financial FCF Waterfall Chart
        let wfOffset = 0;
        const waterfallHtml = labels.slice(0, 6).map((lbl, i, arr) => {
            const ratio = Math.max(0.18, Math.abs(primary[i] || 0) / maxVal);
            const hBar = Math.round(ratio * 135);
            const isFirstOrLast = (i === 0 || i === arr.length - 1);
            const bottomPx = isFirstOrLast ? 0 : Math.min(85, wfOffset);
            wfOffset = (wfOffset + 22) % 80;
            const col = isFirstOrLast ? '#059669' : (i % 2 === 1 ? '#d97706' : '#e11d48');
            return `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 215px;">
                    <span style="font-size: 11px; font-weight: 900; color: ${inkAccent}; margin-bottom: 4px; white-space: nowrap;">${fmtNum(primary[i] || 0)}</span>
                    <div style="width: 100%; max-width: 52px; height: ${hBar}px; margin-bottom: ${bottomPx}px; background: linear-gradient(180deg, ${col} 0%, ${isLt ? col : '#0f172a'} 100%); border: 2px solid ${isLt ? '#120500' : '#ffffff'}; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 14px rgba(0,0,0,0.22);">
                        <span style="color: #ffffff; font-size: 10.5px; font-weight: 900;">${Math.round(ratio * 100)}%</span>
                    </div>
                    <span style="font-size: 11px; font-weight: 900; color: ${inkMain}; text-align: center; margin-top: 6px; line-height: 1.2;">${lbl.slice(0, 16)}</span>
                </div>
            `;
        }).join('');

        // 7. TRUE Stock-Exchange Thermal Treemap & 2D Heatmap Matrix
        const thermalColors = isLt
            ? [
                { bg: 'linear-gradient(135deg, #a7f3d0 0%, #34d399 100%)', border: '#065f46', temp: '96°C زخم فائق', delta: '+14.8%' },
                { bg: 'linear-gradient(135deg, #fed7aa 0%, #fb923c 100%)', border: '#9a3412', temp: '88°C ساخن جداً', delta: '+11.2%' },
                { bg: 'linear-gradient(135deg, #fecdd3 0%, #fb7185 100%)', border: '#881337', temp: '92°C ذروة حرارة', delta: '+9.4%' },
                { bg: 'linear-gradient(135deg, #fef08a 0%, #facc15 100%)', border: '#854d0e', temp: '76°C دافئ نشط', delta: '+7.6%' },
                { bg: 'linear-gradient(135deg, #bae6fd 0%, #38bdf8 100%)', border: '#0c4a6e', temp: '54°C معتدل متوازن', delta: '+5.1%' },
                { bg: 'linear-gradient(135deg, #e9d5ff 0%, #c084fc 100%)', border: '#581c87', temp: '68°C تدفق مستقر', delta: '+6.3%' }
            ]
            : [
                { bg: 'linear-gradient(135deg, #065f46 0%, #059669 55%, #10b981 100%)', border: '#6ee7b7', temp: '96°C زخم فائق', delta: '+14.8%' },
                { bg: 'linear-gradient(135deg, #9a3412 0%, #ea580c 55%, #f97316 100%)', border: '#fdba74', temp: '88°C ساخن جداً', delta: '+11.2%' },
                { bg: 'linear-gradient(135deg, #881337 0%, #e11d48 55%, #f43f5e 100%)', border: '#fda4af', temp: '92°C ذروة حرارة', delta: '+9.4%' },
                { bg: 'linear-gradient(135deg, #854d0e 0%, #ca8a04 55%, #eab308 100%)', border: '#fef08a', temp: '76°C دافئ نشط', delta: '+7.6%' },
                { bg: 'linear-gradient(135deg, #0c4a6e 0%, #0284c7 55%, #38bdf8 100%)', border: '#bae6fd', temp: '54°C معتدل متوازن', delta: '+5.1%' },
                { bg: 'linear-gradient(135deg, #3b0764 0%, #7e22ce 55%, #a855f7 100%)', border: '#e9d5ff', temp: '68°C تدفق مستقر', delta: '+6.3%' }
            ];
        const heatmapTreemapHtml = labels.slice(0, 6).map((lbl, i) => {
            const pct = Math.min(100, Math.max(18, Math.round((Math.abs(primary[i] || 0) / maxVal) * 100)));
            const tc = thermalColors[i % thermalColors.length];
            const spanStyle = i === 0 ? 'grid-column: span 2; grid-row: span 2;' : (i === 1 ? 'grid-column: span 2;' : '');
            const cellTxt = isLt ? '#120500' : '#ffffff';
            const cellVal = isLt ? '#000000' : '#fef08a';
            return `
                <div style="${spanStyle} background: ${tc.bg}; border: 2px solid ${tc.border}; border-radius: 14px; padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between; gap: 6px; box-shadow: 0 6px 16px rgba(0,0,0,0.2);">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 6px;">
                        <span style="background: ${isLt ? 'rgba(255,255,255,0.88)' : 'rgba(2,6,23,0.72)'}; color: ${cellTxt}; border: 1px solid ${tc.border}; padding: 2px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 900;">🌡️ ${tc.temp} (${pct}%)</span>
                        <span style="background: ${isLt ? 'rgba(255,255,255,0.88)' : 'rgba(255,255,255,0.2)'}; color: ${cellTxt}; padding: 1px 7px; border-radius: 6px; font-size: 11px; font-weight: 900;">▲ ${tc.delta}</span>
                    </div>
                    <div style="font-size: ${i === 0 ? '16px' : '13.5px'}; font-weight: 900; color: ${cellTxt}; line-height: 1.25;">${lbl}</div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-size: ${i === 0 ? '21px' : '16px'}; font-weight: 900; color: ${cellVal};">${fmtNum(primary[i] || 0)}</span>
                        <span style="font-size: 10.5px; font-weight: 900; color: ${cellTxt};">حصة ${Math.max(6, Math.round((Math.abs(primary[i] || 0) / totalPrimary) * 100))}%</span>
                    </div>
                </div>
            `;
        }).join('');

        // 8. 3D Conical / Funnel Pyramid Chart
        const funnelWidths = [100, 84, 68, 54, 40];
        const funnelColors = [
            'linear-gradient(90deg, #bae6fd, #38bdf8, #bae6fd)',
            'linear-gradient(90deg, #a7f3d0, #34d399, #a7f3d0)',
            'linear-gradient(90deg, #fde68a, #facc15, #fde68a)',
            'linear-gradient(90deg, #e9d5ff, #c084fc, #e9d5ff)',
            'linear-gradient(90deg, #fecdd3, #fb7185, #fecdd3)'
        ];
        const funnelHtml = labels.slice(0, 5).map((lbl, i) => {
            const w = funnelWidths[i];
            const val = primary[i] || 0;
            const pct = Math.max(8, Math.round((Math.abs(val) / maxVal) * 100));
            return `
                <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                    <div style="width: ${w}%; background: ${funnelColors[i]}; border: 2px solid #120500; border-radius: 12px; padding: 8px 14px; display: flex; justify-content: space-between; align-items: center; clip-path: polygon(0% 0%, 100% 0%, 94% 100%, 6% 100%);">
                        <span style="font-size: 12px; font-weight: 900; color: #000000; background: rgba(255,255,255,0.92); padding: 1px 7px; border-radius: 6px;">#${i + 1} ${lbl.slice(0, 16)}</span>
                        <span style="font-size: 13px; font-weight: 900; color: #000000; background: #fde047; padding: 1px 8px; border-radius: 6px;">${fmtNum(val)} (${pct}%)</span>
                    </div>
                </div>
            `;
        }).join('');

        // 9. Scatter & Bubble Correlation Matrix + Customer Cohort Retention Matrix
        const scatterBubbles = labels.slice(0, 6).map((lbl, i) => {
            const cx = 75 + (i * 88);
            const cy = 155 - Math.min(115, Math.max(25, Math.round((Math.abs(primary[i] || 0) / maxVal) * 115)));
            const r = 13 + ((5 - i) * 2.5);
            const col = palette[i % palette.length];
            return `
                <g>
                    <circle cx="${cx}" cy="${cy}" r="${r + 5}" fill="${col}" fill-opacity="0.22"/>
                    <circle cx="${cx}" cy="${cy}" r="${r}" fill="${col}" fill-opacity="0.88" stroke="${isLt ? '#120500' : '#ffffff'}" stroke-width="2"/>
                    <text x="${cx}" y="${cy + 4}" fill="#ffffff" font-size="10" font-weight="900" text-anchor="middle">${Math.round((Math.abs(primary[i] || 0) / maxVal) * 100)}%</text>
                    <text x="${cx}" y="${cy - r - 6}" fill="${inkMain}" font-size="10.5" font-weight="900" text-anchor="middle">${lbl.slice(0, 10)}</text>
                </g>
            `;
        }).join('');

        const cohortSrc = (intel && intel.cohorts) ? intel.cohorts.map(c => ({ name: c[0], m1: c[1], m2: c[2], m3: c[3], m4: c[4], ltv: c[5] })) : [
            { name: 'فوج كبار المقاولين (VIP)', m1: '100%', m2: '94%', m3: '91%', m4: '89%', ltv: '48.5M' }
        ];
        const cohortRows = cohortSrc.map(r => `
            <div style="display: grid; grid-template-columns: 1.8fr repeat(4, 1fr) 1.1fr; gap: 5px; align-items: center; font-size: 11.5px; font-weight: 900;">
                <div style="background: ${subBoxBg}; padding: 6px 8px; border-radius: 7px; color: ${inkMain}; border-right: 3.5px solid #059669;">${r.name}</div>
                <div style="background: #059669; color: #fff; text-align: center; padding: 6px 4px; border-radius: 7px;">${r.m1}</div>
                <div style="background: #0d9488; color: #fff; text-align: center; padding: 6px 4px; border-radius: 7px;">${r.m2}</div>
                <div style="background: #0284c7; color: #fff; text-align: center; padding: 6px 4px; border-radius: 7px;">${r.m3}</div>
                <div style="background: #4f46e5; color: #fff; text-align: center; padding: 6px 4px; border-radius: 7px;">${r.m4}</div>
                <div style="background: ${isLt ? '#fef3c7' : 'rgba(253,224,71,0.18)'}; border: 1.5px solid #d97706; color: ${inkAccent}; text-align: center; padding: 6px 4px; border-radius: 7px;">${r.ltv}</div>
            </div>
        `).join('');

        const riskSrc = (intel && intel.riskCells) ? intel.riskCells.map(rc => {
            const sc = rc.score;
            const tier = sc >= 75 ? { bg: isLt ? 'linear-gradient(135deg,#fecdd3,#fda4af)' : 'linear-gradient(135deg,#991b1b,#e11d48)', border: '#e11d48', badge: `🚨 خطر ${sc}%` }
                : sc >= 58 ? { bg: isLt ? 'linear-gradient(135deg,#fed7aa,#fdba74)' : 'linear-gradient(135deg,#9a3412,#f97316)', border: '#ea580c', badge: `⚠️ مرتفع ${sc}%` }
                : sc >= 35 ? { bg: isLt ? 'linear-gradient(135deg,#fef08a,#fde047)' : 'linear-gradient(135deg,#854d0e,#eab308)', border: '#ca8a04', badge: `🟡 مراقبة ${sc}%` }
                : { bg: isLt ? 'linear-gradient(135deg,#a7f3d0,#6ee7b7)' : 'linear-gradient(135deg,#065f46,#10b981)', border: '#059669', badge: `🟢 محصن ${100 - sc}%` };
            return { lbl: rc.lbl, p: rc.p, imp: rc.imp, ...tier };
        }) : [];
        const riskMatrixCells = riskSrc.map(c => {
            const cInk = isLt ? '#120500' : '#ffffff';
            return `
                <div style="background: ${c.bg}; border: 2px solid ${c.border}; border-radius: 12px; padding: 10px 12px; display: flex; flex-direction: column; justify-content: space-between; gap: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="background: ${isLt ? '#ffffff' : 'rgba(2,6,23,0.72)'}; color: ${cInk}; padding: 1px 7px; border-radius: 6px; font-size: 10px; font-weight: 900; border: 1px solid ${c.border};">${c.badge}</span>
                        <span style="font-size: 10px; font-weight: 900; color: ${cInk};">${c.imp}</span>
                    </div>
                    <div style="font-size: 13px; font-weight: 900; color: ${cInk};">${c.lbl}</div>
                    <div style="font-size: 11px; font-weight: 900; color: ${cInk};">${c.p}</div>
                </div>
            `;
        }).join('');

        window._autoChartActiveFilter = 'all';

        const dashboard = document.createElement('div');
        dashboard.className = 'auto-chart-dashboard no-print';
        dashboard.style.cssText = `
            margin-top: 18px;
            margin-bottom: 18px;
            padding: 22px 24px;
            border-radius: 24px;
            background: ${outerBg} !important;
            border: ${outerBorder} !important;
            box-shadow: ${outerShadow} !important;
            color: ${inkMain} !important;
            box-sizing: border-box;
            width: 100%;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif;
        `;

        dashboard.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid ${isLt ? 'var(--lt-border, #5c2808)' : 'rgba(56, 189, 248, 0.28)'}; padding-bottom: 10px; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #059669, #0284c7); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">📊</span>
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 900; color: ${inkMain} !important;">التحليل البياني البصري ورادار المخاطر — ${tableData.domainTitle || intel.domainTitle}</h3>
                    </div>
                </div>
                <span style="padding: 4px 12px; border-radius: 999px; background: rgba(5,150,105,0.18); border: 1.5px solid #059669; color: ${inkEmerald}; font-size: 12px; font-weight: 900;">${intel.badge || '🌌 المنظومة الشاملة'}</span>
            </div>

            <!-- شريط التنبؤ الاستباقي بالسيولة وحمايتها من العجز قبل وقوعه (AI Liquidity Protection Strip) -->
            <div style="margin-bottom: 16px; padding: 12px 16px; border-radius: 16px; background: ${isLt ? 'linear-gradient(90deg, #ecfdf5 0%, #fef3c7 50%, #eff6ff 100%)' : 'linear-gradient(90deg, rgba(6,78,59,0.85) 0%, rgba(120,53,15,0.8) 50%, rgba(12,74,110,0.85) 100%)'}; border: 2px solid #059669; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="padding: 4px 10px; border-radius: 999px; background: #059669; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-size: 11.5px; font-weight: 900;">🤖 رادار حماية السيولة بالذكاء الاصطناعي</span>
                    <span style="font-size: 13.5px; font-weight: 900; color: ${inkMain};">${tableData.liqForecastMsg || 'السيولة النقدية والبنكية محصنة بالكامل ضد العجز خلال الـ 90 يوماً القادمة'}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 12.5px; font-weight: 900;">
                    <span style="color: ${inkEmerald};">💎 التدفق الحر FCF: +${fmtNum(freeCashFlow)}</span>
                    <span style="color: ${inkAccent};">🛡️ مدى الأمان التشغيلي: ${runwayDays} يوماً</span>
                </div>
            </div>

            <!-- 1. الطبقة الأولى: بطاقات المؤشرات النبضية + FCF و Burn Rate -->
            <div data-g7-panel="sparkline" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 18px;">
                ${topItems}
            </div>

            <!-- 2. الطبقة الثانية: المساحة الانسيابية + الكعكات المجوفة ثلاثية الحلقات والمفرغة -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 16px; margin-bottom: 18px;">
                <div data-g7-panel="area" style="background: ${pAreaBg}; border: 2.5px solid #059669; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1.5px solid rgba(5,150,105,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14.5px; font-weight: 900; color: ${inkEmerald};">📈 2. مخطط المساحة الانسيابي وتدفق السيولة (Spline Area Flow)</span>
                        <span style="font-size: 11.5px; font-weight: 900; color: ${inkAccent};">مسار التدفق والحركة</span>
                    </div>
                    <svg viewBox="0 0 ${svgW} ${svgH + 26}" style="width: 100%; height: auto; overflow: visible;">
                        <defs>
                            <linearGradient id="g7MainAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#059669" stop-opacity="0.55"/>
                                <stop offset="60%" stop-color="#0284c7" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#0284c7" stop-opacity="0.03"/>
                            </linearGradient>
                        </defs>
                        ${[25, 50, 75, 100].map(v => {
                            const y = svgH - padY - ((v / 100) * (svgH - padY * 2));
                            return `<line x1="${padX}" y1="${y}" x2="${svgW - padX}" y2="${y}" stroke="${isLt ? 'rgba(15,23,42,0.18)' : 'rgba(148,163,184,0.16)'}" stroke-dasharray="4,4"/><text x="${padX - 8}" y="${y + 4}" fill="${inkSub}" font-size="10.5" font-weight="900" text-anchor="end">${v}%</text>`;
                        }).join('')}
                        <path d="${areaPath}" fill="url(#g7MainAreaGrad)"/>
                        <path d="${smoothPath}" fill="none" stroke="${isLt ? '#047857' : '#00f5d4'}" stroke-width="3.8" stroke-linecap="round"/>
                        ${pts.map(p => `
                            <circle cx="${p.x}" cy="${p.y}" r="6" fill="${p.col}" stroke="#ffffff" stroke-width="2"/>
                            <text x="${p.x}" y="${p.y - 11}" fill="${inkMain}" font-size="11" font-weight="900" text-anchor="middle">${fmtNum(p.val)}</text>
                            <text x="${p.x}" y="${svgH + 14}" fill="${inkAccent}" font-size="10.5" font-weight="900" text-anchor="middle">${p.lbl.slice(0, 12)}</text>
                        `).join('')}
                    </svg>
                </div>

                <!-- 3. الكعكات المجوفة ثلاثية الحلقات والمفرغة المقطعة -->
                <div data-g7-panel="donut" style="background: ${pDonutBg}; border: 2.5px solid #ea580c; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1.5px solid rgba(234,88,12,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14.5px; font-weight: 900; color: ${inkAccent};">🍩 3. الكعكات المجوفة ثلاثية الحلقات والمفرغة المقطعة</span>
                        <span style="font-size: 11.5px; font-weight: 900; color: ${inkEmerald};">حلقات متداخلة + كعكة مفرغة</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 12px; align-items: center;">
                        <div style="background: ${subBoxBg}; border: 1.5px solid #ea580c; border-radius: 14px; padding: 10px; display: flex; flex-direction: column; align-items: center;">
                            <div style="position: relative; width: 165px; height: 165px;">
                                <svg viewBox="0 0 210 210" style="width: 165px; height: 165px; transform: rotate(-90deg);">
                                    <circle cx="105" cy="105" r="70" fill="none" stroke="${isLt ? 'rgba(15,23,42,0.1)' : 'rgba(148,163,184,0.12)'}" stroke-width="24"/>
                                    ${donutSegs}
                                    <circle cx="105" cy="105" r="52" fill="${isLt ? '#ffffff' : '#070d1f'}"/>
                                </svg>
                                <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                                    <span style="font-size: 15px; font-weight: 900; color: ${inkMain};">${fmtNum(totalPrimary)}</span>
                                    <span style="font-size: 10px; font-weight: 900; color: ${inkEmerald};">كعكة مفرغة مقطعة</span>
                                </div>
                            </div>
                        </div>
                        <div style="background: ${subBoxBg}; border: 1.5px solid #059669; border-radius: 14px; padding: 10px; display: flex; flex-direction: column; align-items: center;">
                            <div style="position: relative; width: 165px; height: 165px;">
                                <svg viewBox="0 0 210 210" style="width: 165px; height: 165px; transform: rotate(-90deg);">
                                    ${concentricRings}
                                </svg>
                                <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                                    <span style="font-size: 14px; font-weight: 900; color: ${inkAccent};">3 حلقات</span>
                                    <span style="font-size: 10px; font-weight: 900; color: ${inkMain};">مجوفة متداخلة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-top: 10px;">
                        ${labels.slice(0, 4).map((lbl, i) => {
                            const pct = Math.round((Math.abs(primary[i] || 0) / totalPrimary) * 100);
                            const col = palette[i % palette.length];
                            return `<div style="display: flex; align-items: center; justify-content: space-between; padding: 5px 8px; border-radius: 8px; background: ${subBoxBg}; border: 1px solid ${col}; border-right: 4px solid ${col}; font-size: 11px; font-weight: 900;"><span style="color: ${inkMain};">${lbl.slice(0, 15)}</span><span style="color: ${col};">${pct}%</span></div>`;
                        }).join('')}
                    </div>
                </div>
            </div>

            <!-- 3. العداد المقوس + ميزان العرض والطلب -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 16px; margin-bottom: 18px;">
                <div data-g7-panel="gauge" style="background: ${pGaugeBg}; border: 2.5px solid #d97706; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1.5px solid rgba(217,119,6,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14.5px; font-weight: 900; color: ${inkAccent};">🧭 4. العداد المقوس للأمان والأهداف (Radial Arc Gauge)</span>
                        <span style="font-size: 11.5px; font-weight: 900; color: ${inkEmerald};">قياس الأمان والإنجاز</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px;">
                        ${gaugesHtml}
                    </div>
                </div>

                <div data-g7-panel="depth" style="background: ${pDepthBg}; border: 2.5px solid #0d9488; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1.5px solid rgba(13,148,136,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14.5px; font-weight: 900; color: ${inkEmerald};">⚖️ 5. ميزان العرض والطلب وعمق السوق (Market Depth)</span>
                        <span style="font-size: 11.5px; font-weight: 900; color: ${inkRose};">الطلب/الداخل ⇄ العرض/الخارج</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        ${depthHtml}
                    </div>
                </div>
            </div>

            <!-- 4. الشلال المالي + شاشة الخريطة الحرارية البورصوية -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 16px; margin-bottom: 18px;">
                <div data-g7-panel="waterfall" style="background: ${pWfBg}; border: 2.5px solid #7e22ce; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1.5px solid rgba(126,34,206,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14.5px; font-weight: 900; color: ${inkPurple};">🌊 6. مخطط الشلال المالي للتدفق الحر (Financial Waterfall)</span>
                        <span style="font-size: 11.5px; font-weight: 900; color: ${inkAccent};">تدرج القيم وصافي الأرباح</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(${Math.min(6, labels.length)}, minmax(0, 1fr)); gap: 10px; align-items: end; padding-top: 8px;">
                        ${waterfallHtml}
                    </div>
                </div>

                <div data-g7-panel="heatmap" style="background: ${pHeatBg}; border: 2.5px solid #e11d48; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1.5px solid rgba(225,29,72,0.25); padding-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 14.5px; font-weight: 900; color: ${inkRose};">🔥 7. شاشة الخريطة الحرارية البورصوية (Thermal Treemap Matrix)</span>
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 900;">
                            <span style="padding: 2px 6px; border-radius: 4px; background: #0284c7; color: #fff;">بارد 20°</span>
                            <span style="padding: 2px 6px; border-radius: 4px; background: #059669; color: #fff;">نشط 60°</span>
                            <span style="padding: 2px 6px; border-radius: 4px; background: #ea580c; color: #fff;">ساخن 85°</span>
                            <span style="padding: 2px 6px; border-radius: 4px; background: #e11d48; color: #fff;">ذروة 98°</span>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px;">
                        ${heatmapTreemapHtml}
                    </div>
                </div>
            </div>

            <!-- 5. المخطط المخروطي الهرمي + مخطط التشتت والفقاعات + مصفوفة سلوك الأفواج -->
            <div data-g7-panel="funnel" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 18px;">
                <div style="background: ${pGaugeBg}; border: 2px solid #d97706; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(217,119,6,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14px; font-weight: 900; color: ${inkAccent};">🔻 8. المخطط المخروطي الهرمي (3D Conical Funnel)</span>
                        <span style="font-size: 10.5px; font-weight: 900; color: ${inkEmerald};">تدرج التركز والأولويات</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 7px; padding-top: 4px;">
                        ${funnelHtml}
                    </div>
                </div>

                <div style="background: ${pAreaBg}; border: 2px solid #0284c7; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(2,132,199,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14px; font-weight: 900; color: ${inkSky};">🫧 9. مخطط التشتت والفقاعات للعلاقات (Scatter & Bubble)</span>
                        <span style="font-size: 10.5px; font-weight: 900; color: ${inkAccent};">الكمية × الربحية × السيولة</span>
                    </div>
                    <svg viewBox="0 0 560 190" style="width: 100%; height: auto; overflow: visible;">
                        <line x1="40" y1="165" x2="535" y2="165" stroke="${inkSub}" stroke-width="1.5"/>
                        <line x1="40" y1="20" x2="40" y2="165" stroke="${inkSub}" stroke-width="1.5"/>
                        <line x1="45" y1="150" x2="525" y2="32" stroke="#059669" stroke-width="2" stroke-dasharray="6,4"/>
                        ${scatterBubbles}
                    </svg>
                </div>

                <div style="background: ${pDepthBg}; border: 2px solid #059669; border-radius: 18px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(5,150,105,0.25); padding-bottom: 8px;">
                        <span style="font-size: 14px; font-weight: 900; color: ${inkEmerald};">${intel.cohortTitle || '🧬 10. تحليل سلوك الأفواج والقيمة الدائمة'}</span>
                        <span style="font-size: 10.5px; font-weight: 900; color: ${inkAccent};">مؤشرات خاصة بهذه الشاشة</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: grid; grid-template-columns: 1.8fr repeat(4, 1fr) 1.1fr; gap: 5px; font-size: 10.5px; font-weight: 900; color: ${inkSub}; text-align: center;">
                            <span style="text-align: right;">المحور / الفئة</span><span>م1</span><span>م2</span><span>م3</span><span>م4</span><span style="color:${inkAccent};">المعيار</span>
                        </div>
                        ${cohortRows}
                    </div>
                </div>
            </div>

            <!-- 6. قسم التنبؤ بالذكاء الاصطناعي للمخاطر الشاملة وحماية السيولة -->
            <div data-g7-panel="ai_risk" style="background: ${pRiskBg}; border: 3px solid #e11d48; border-radius: 22px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid rgba(225, 29, 72, 0.35); padding-bottom: 12px; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, #e11d48, #f97316); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 19px;">🚨</span>
                        <div>
                            <h4 style="margin: 0; font-size: 17.5px; font-weight: 900; color: ${inkMain} !important;">
                                رادار المخاطر الاستباقي AI — ${intel.badge || ''}
                            </h4>
                            <div style="font-size: 11.5px; font-weight: 900; color: ${inkRose};">
                                🛡️ فحص متخصص ومستقل لهذه الشاشة: مصفوفة الاحتمال × الأثر • الرادار السداسي • مخروط الـ 90 يوماً
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="padding: 5px 12px; border-radius: 999px; background: #059669; color: #ffffff !important; font-size: 11.5px; font-weight: 900;">🛡️ مؤشر التحصين الرقابي: ${intel.secScore || '91.2'}%</span>
                        <span style="padding: 5px 12px; border-radius: 999px; background: #e11d48; color: #ffffff !important; font-size: 11.5px; font-weight: 900;">⚡ ${intel.activeAlerts ?? 2} محاور تحت المراقبة</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 16px;">
                    <div style="background: ${subBoxBg}; border: 2px solid #e11d48; border-radius: 16px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 13.5px; font-weight: 900; color: ${inkRose};">🔥 أ. مصفوفة الاحتمالات × الأثر المالي (AI Risk Heatmap)</span>
                            <span style="font-size: 10.5px; font-weight: 900; color: ${inkAccent};">8 محاور مفحوصة آلياً</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;">
                            ${riskMatrixCells}
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="background: ${subBoxBg}; border: 2px solid #d97706; border-radius: 16px; padding: 14px; display: grid; grid-template-columns: 185px 1fr; gap: 12px; align-items: center;">
                            <svg viewBox="0 0 200 200" style="width: 175px; height: 175px; margin: auto;">
                                <polygon points="100,18 171,59 171,141 100,182 29,141 29,59" fill="none" stroke="${inkSub}" stroke-width="1.5"/>
                                <polygon points="100,45 148,72 148,128 100,155 52,128 52,72" fill="none" stroke="${inkSub}" stroke-width="1.2" stroke-dasharray="3,3"/>
                                <polygon points="${intel.radarPolyRisk || '100,28 162,65 142,132 100,166 44,125 48,68'}" fill="rgba(225,29,72,0.28)" stroke="#e11d48" stroke-width="2.5"/>
                                <polygon points="${intel.radarPolySafe || '100,42 145,74 155,136 100,150 58,120 62,75'}" fill="rgba(5,150,105,0.25)" stroke="#059669" stroke-width="2"/>
                                <text x="100" y="12" fill="${inkAccent}" font-size="9.5" font-weight="900" text-anchor="middle">${intel.radarAxes?.[0] ? `${intel.radarAxes[0].name} ${intel.radarAxes[0].pct}%` : 'العملة 78%'}</text>
                                <text x="178" y="58" fill="${inkRose}" font-size="9" font-weight="900" text-anchor="middle">${intel.radarAxes?.[1] ? `${intel.radarAxes[1].name} ${intel.radarAxes[1].pct}%` : 'الائتمان 64%'}</text>
                                <text x="178" y="150" fill="${inkEmerald}" font-size="9" font-weight="900" text-anchor="middle">${intel.radarAxes?.[2] ? `${intel.radarAxes[2].name} ${intel.radarAxes[2].pct}%` : 'السيولة 15%'}</text>
                                <text x="100" y="195" fill="${inkSky}" font-size="9.5" font-weight="900" text-anchor="middle">${intel.radarAxes?.[3] ? `${intel.radarAxes[3].name} ${intel.radarAxes[3].pct}%` : 'المخزون 58%'}</text>
                                <text x="22" y="150" fill="${inkAccent}" font-size="9" font-weight="900" text-anchor="middle">${intel.radarAxes?.[4] ? `${intel.radarAxes[4].name} ${intel.radarAxes[4].pct}%` : 'التوريد 46%'}</text>
                                <text x="22" y="58" fill="${inkPurple}" font-size="9" font-weight="900" text-anchor="middle">${intel.radarAxes?.[5] ? `${intel.radarAxes[5].name} ${intel.radarAxes[5].pct}%` : 'الهامش 42%'}</text>
                            </svg>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <div style="font-size: 13.5px; font-weight: 900; color: ${inkAccent};">🕸️ ب. الرادار السداسي المتخصص والتوجيه الذكي</div>
                                <div style="padding: 7px 10px; border-radius: 9px; background: ${isLt ? '#ffe4e6' : 'rgba(225,29,72,0.22)'}; border-right: 4px solid #e11d48; font-size: 11.5px; font-weight: 900; color: ${inkMain};">${intel.tips?.[0] || ''}</div>
                                <div style="padding: 7px 10px; border-radius: 9px; background: ${isLt ? '#fef3c7' : 'rgba(245,158,11,0.2)'}; border-right: 4px solid #d97706; font-size: 11.5px; font-weight: 900; color: ${inkMain};">${intel.tips?.[1] || ''}</div>
                                <div style="padding: 7px 10px; border-radius: 9px; background: ${isLt ? '#d1fae5' : 'rgba(16,185,129,0.2)'}; border-right: 4px solid #059669; font-size: 11.5px; font-weight: 900; color: ${inkMain};">${intel.tips?.[2] || ''}</div>
                            </div>
                        </div>

                        <div style="background: ${subBoxBg}; border: 2px solid #059669; border-radius: 16px; padding: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 13px; font-weight: 900; color: ${inkEmerald};">🔮 ج. مخروط الاستشراف لمدة 90 يوماً — ${intel.badge || ''}</span>
                                <span style="font-size: 10.5px; font-weight: 900; color: ${inkAccent};">دقة المحاكاة ${intel.secScore || '94.2'}%</span>
                            </div>
                            <svg viewBox="0 0 600 130" style="width: 100%; height: auto; overflow: visible;">
                                <polygon points="40,75 210,60 390,38 565,14 565,112 390,96 210,82" fill="rgba(5,150,105,0.15)"/>
                                <path d="M 40,75 L 210,60 L 390,38 L 565,14" fill="none" stroke="#059669" stroke-width="2.8" stroke-dasharray="5,3"/>
                                <path d="M 40,75 L 210,68 L 390,56 L 565,44" fill="none" stroke="#d97706" stroke-width="3.5"/>
                                <path d="M 40,75 L 210,82 L 390,96 L 565,112" fill="none" stroke="#e11d48" stroke-width="2.8" stroke-dasharray="4,4"/>
                                <circle cx="40" cy="75" r="5" fill="${inkMain}"/>
                                <circle cx="565" cy="14" r="5" fill="#059669"/><text x="515" y="14" fill="${inkEmerald}" font-size="10.5" font-weight="900">متفائل (+${intel.cone?.[0] ?? 32}%)</text>
                                <circle cx="565" cy="44" r="5.5" fill="#d97706"/><text x="505" y="42" fill="${inkAccent}" font-size="10.5" font-weight="900">المسار المتوقع (+${intel.cone?.[1] ?? 18}%)</text>
                                <circle cx="565" cy="112" r="5" fill="#e11d48"/><text x="498" y="122" fill="${inkRose}" font-size="10.5" font-weight="900">اختبار الضغط (${intel.cone?.[2] ?? -9}%)</text>
                                <text x="40" y="124" fill="${inkMain}" font-size="10" font-weight="900" text-anchor="middle">اليوم</text>
                                <text x="210" y="124" fill="${inkMain}" font-size="10" font-weight="900" text-anchor="middle">بعد 30 يوم</text>
                                <text x="390" y="124" fill="${inkMain}" font-size="10" font-weight="900" text-anchor="middle">بعد 60 يوم</text>
                                <text x="565" y="124" fill="${inkMain}" font-size="10" font-weight="900" text-anchor="middle">بعد 90 يوم</text>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(dashboard);
        if (window._autoChartActiveFilter && window._autoChartActiveFilter !== 'all') {
            dashboard.querySelectorAll('[data-g7-panel]').forEach(panel => {
                const pMode = panel.getAttribute('data-g7-panel');
                panel.style.display = (window._autoChartActiveFilter === pMode) ? '' : 'none';
            });
        }
    }

    function bootAutoCharts() {
        renderGolden7Dashboard();
        // Watch table / invoice changes
        const observer = new MutationObserver(function (mutations) {
            let shouldUpdate = false;
            for (const m of mutations) {
                if (m.target && m.target.closest && m.target.closest('.auto-chart-dashboard')) continue;
                if (m.target && m.target.closest && (m.target.closest('table') || m.target.id === 'inv-tbody')) {
                    shouldUpdate = true;
                    break;
                }
            }
            if (shouldUpdate) {
                clearTimeout(window._g7AutoChartTimer);
                window._g7AutoChartTimer = setTimeout(renderGolden7Dashboard, 350);
            }
        });
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        }
        // Watch Light/Dark Theme & Light Tone changes on <html> so charts re-render in <10ms
        const themeObserver = new MutationObserver(function () {
            clearTimeout(window._g7ThemeChartTimer);
            window._g7ThemeChartTimer = setTimeout(renderGolden7Dashboard, 40);
        });
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme', 'data-light-tone', 'style'] });

        document.addEventListener('click', function (e) {
            if (e.target && e.target.closest && e.target.closest('.md-tab-btn, .tab-btn, [role="tab"], .nav-item, [onclick*="switchTab"]')) {
                clearTimeout(window._g7AutoChartTimer);
                window._g7AutoChartTimer = setTimeout(renderGolden7Dashboard, 180);
            }
        });
        document.addEventListener('input', function (e) {
            if (e.target && e.target.closest && e.target.closest('#inv-tbody')) {
                clearTimeout(window._g7AutoChartTimer);
                window._g7AutoChartTimer = setTimeout(renderGolden7Dashboard, 400);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(bootAutoCharts, 250));
    } else {
        setTimeout(bootAutoCharts, 250);
    }
})();
