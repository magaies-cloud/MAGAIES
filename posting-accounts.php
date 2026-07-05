<?php $page_title = 'ترحيل الحسابات'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ترحيل الحسابات | السجانة ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --border: rgba(255, 255, 255, 0.08);
            --primary-accent: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        html, body {
            height: 100vh;
            height: 100dvh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
            background: #050308;
            font-family: 'Tajawal', sans-serif;
            color: #f8fafc;
        }

        .page-wrapper {
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            padding: 15px;
            box-sizing: border-box;
            overflow: hidden;
            width: 100%;
        }

        /* Ambient Background */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15; animation: floatOrb 20s infinite alternate ease-in-out; }
        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--success); bottom: -10%; left: -5%; animation-delay: -7s; }
        @keyframes floatOrb { 0% { transform: translate(0, 0) scale(1); } 50% { transform: translate(30px, -50px) scale(1.1); } 100% { transform: translate(0, 0) scale(1); } }
        
        .premium-header { margin: 0 0 10px 0 !important; }

        /* Filters */
        .filters-wrapper { 
            background: var(--surface); 
            padding: 15px 20px; 
            border-radius: 16px;
            border: 1px solid var(--border); 
            backdrop-filter: blur(20px);
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
            margin-bottom: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.05);
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

        .filters-top { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        
        .status-toggle { display: inline-flex; background: rgba(0,0,0,0.4); border-radius: 12px; padding: 4px; border: 1px solid rgba(255,255,255,0.05); }
        .status-btn { background: transparent; color: #94a3b8; border: none; padding: 8px 24px; border-radius: 8px; font-weight: 800; cursor: pointer; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); font-family: 'Tajawal', sans-serif; font-size: 0.95rem; }
        .status-btn.active.unposted { background: linear-gradient(135deg, var(--warning), #d97706); color: #fff; box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
        .status-btn.active.posted { background: linear-gradient(135deg, var(--success), #059669); color: #fff; box-shadow: 0 4px 15px rgba(16,185,129,0.3); }

        .search-box {
            position: relative; flex: 1; min-width: 250px; max-width: 400px;
        }
        .search-box input {
            width: 100%; padding: 10px 40px 10px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); color: #fff; font-size: 0.95rem; outline: none; font-family: 'Tajawal', sans-serif; transition: 0.3s; box-sizing: border-box;
        }
        .search-box input:focus { border-color: var(--primary-accent); box-shadow: 0 0 0 2px rgba(59,130,246,0.2); background: rgba(0,0,0,0.5); }
        .search-box .icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; pointer-events: none; opacity: 0.7; }

        .date-filters { display: flex; align-items: center; gap: 10px; }
        .date-input { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); padding: 8px 12px; border-radius: 10px; color: #fff; font-family: 'Tajawal', sans-serif; outline: none; transition: 0.3s; font-size: 0.9rem;}
        .date-input:focus { border-color: var(--primary-accent); }

        .type-filters { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 2px; }
        .type-chip { background: rgba(255,255,255,0.03); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); white-space: nowrap; font-family: 'Tajawal', sans-serif; }
        .type-chip:hover { background: rgba(255,255,255,0.1); transform: translateY(-2px); }
        .type-chip.active { background: linear-gradient(135deg, var(--primary-accent), #2563eb); color: #fff; border-color: transparent; box-shadow: 0 4px 15px rgba(59,130,246,0.4); }

        /* Split View Layout */
        .split-layout { flex: 1; display: flex; gap: 15px; overflow: hidden; min-height: 0; }
        
        /* Master Pane (Right) */
        .master-pane { flex: 0 0 62%; background: var(--surface); border: 1px solid var(--border); border-radius: 16px; display: flex; flex-direction: column; position: relative; backdrop-filter: blur(20px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; animation: fadeIn 0.6s ease-out forwards; min-height: 0; }
        @keyframes fadeIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }

        .table-scroll { flex: 1; overflow-y: auto; overflow-x: auto; padding: 0; }
        
        .erp-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .erp-table thead { position: sticky; top: 0; background: rgba(15,23,42,0.95); backdrop-filter: blur(10px); z-index: 10; border-bottom: 1px solid var(--border); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .erp-table th { padding: 15px 12px; text-align: right; color: #94a3b8; font-size: 0.9rem; font-weight: 800; white-space: nowrap; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .main-row { cursor: pointer; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); background: transparent; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .main-row:hover { background: rgba(255,255,255,0.03); transform: scale(1.002); }
        .main-row.selected { background: rgba(59,130,246,0.1); border-right: 4px solid var(--primary-accent); }
        
        .main-row td { padding: 12px; color: #f8fafc; font-size: 0.95rem; vertical-align: middle; }
        
        .amount-col { font-family: 'Inter', monospace; font-weight: 800; color: var(--success); font-size: 1.05rem; }
        .ref-col { font-family: 'Inter', monospace; color: #cbd5e1; font-weight: 700; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px; font-size: 0.85rem;}
        .date-col { color: #94a3b8; font-size: 0.9rem; }
        .customer-col { font-weight: bold; color: #e2e8f0; }
        
        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 800; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .type-sales { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .type-purchases { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .type-receipt { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
        .type-payment { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .type-journal { background: rgba(168,85,247,0.15); color: #c084fc; border: 1px solid rgba(168,85,247,0.3); }

        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary-accent); }

        /* Detail Pane (Left) */
        .detail-pane { flex: 1; background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow-y: auto; padding: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.3); display: flex; flex-direction: column; backdrop-filter: blur(20px); position: relative; animation: fadeIn 0.8s ease-out forwards; min-height: 0; }
        
        .empty-detail { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #64748b; text-align: center; padding: 20px; }
        .empty-detail-icon { font-size: 5rem; opacity: 0.2; margin-bottom: 20px; filter: grayscale(100%); transition: 0.3s; }
        .empty-detail:hover .empty-detail-icon { filter: grayscale(0%); opacity: 0.5; transform: scale(1.1); }
        
        .detail-inner { padding: 20px; display: flex; flex-direction: column; height: 100%; animation: slideIn 0.4s cubic-bezier(0.16,1,0.3,1); }
        @keyframes slideIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        .detail-header { border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px; }
        .detail-title { font-size: 1.3rem; font-weight: 900; color: #fff; display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }
        .detail-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.03); }
        .meta-item { display: flex; flex-direction: column; gap: 5px; }
        .meta-label { font-size: 0.8rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; }
        .meta-val { font-size: 1rem; color: #f8fafc; font-weight: 700; }
        
        .detail-section { background: rgba(0,0,0,0.25); border-radius: 14px; padding: 20px; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden; }
        .detail-section::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; background: var(--primary-accent); border-radius: 0 14px 14px 0; }
        .section-title { font-size: 1.05rem; font-weight: 900; color: #fff; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .section-title span { background: rgba(255,255,255,0.1); width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 1.1rem; }

        .detail-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .detail-table th { text-align: right; color: #94a3b8; padding: 10px 8px; border-bottom: 1px solid rgba(255,255,255,0.08); font-weight: 800;}
        .detail-table td { padding: 12px 8px; color: #e2e8f0; border-bottom: 1px dashed rgba(255,255,255,0.03); }
        .detail-table tr:last-child td { border-bottom: none; }
        
        .dr-text { color: var(--success); font-weight: 800; font-family: 'Inter', monospace; background: rgba(16,185,129,0.1); padding: 4px 8px; border-radius: 6px; }
        .cr-text { color: var(--danger); font-weight: 800; font-family: 'Inter', monospace; background: rgba(239,68,68,0.1); padding: 4px 8px; border-radius: 6px; }

        .flow-diagram { display: flex; align-items: center; justify-content: center; gap: 20px; background: rgba(0,0,0,0.3); padding: 25px; border-radius: 12px; border: 1px dashed rgba(255,255,255,0.1); margin-top: 10px; }
        .flow-box { flex: 1; text-align: center; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 10px; font-weight: 800; color: #fff; font-size: 0.95rem; border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;}
        .flow-box.dr { border-bottom: 3px solid var(--success); }
        .flow-box.cr { border-bottom: 3px solid var(--danger); }
        .flow-box.dr::after { content: ''; position: absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(16,185,129,0.1), transparent); pointer-events: none;}
        .flow-box.cr::after { content: ''; position: absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(to top, rgba(239,68,68,0.1), transparent); pointer-events: none;}
        .flow-arrow { display: flex; flex-direction: column; align-items: center; color: #94a3b8; font-weight: 900; gap: 5px; }

        .btn-post-single { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 900; font-size: 1.1rem; font-family: 'Tajawal', sans-serif; cursor: pointer; box-shadow: 0 4px 15px rgba(16,185,129,0.3); transition: all 0.3s cubic-bezier(0.16,1,0.3,1); width: 100%; margin-top: auto; display: flex; justify-content: center; align-items: center; gap: 10px; }
        .btn-post-single:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(16,185,129,0.5); }

        /* Sticky Action Bar for Batch */
        .sticky-action-bar { position: absolute; bottom: -100px; left: 50%; transform: translateX(-50%); background: rgba(15,23,42,0.95); border: 1px solid var(--primary-accent); border-radius: 16px; padding: 15px 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.8), 0 0 20px rgba(59,130,246,0.2); display: flex; align-items: center; gap: 25px; z-index: 1000; transition: bottom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); backdrop-filter: blur(10px); }
        .sticky-action-bar.show { bottom: 25px; }
        .selected-count { color: var(--primary-accent); font-size: 1.4rem; font-weight: 900; font-family: 'Inter', monospace; background: rgba(59,130,246,0.1); padding: 4px 12px; border-radius: 8px;}
        .btn-post-batch { background: linear-gradient(135deg, var(--primary-accent), #2563eb); color: #fff; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 900; font-family: 'Tajawal', sans-serif; font-size: 1.05rem; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(59,130,246,0.4); display: flex; align-items: center; gap: 8px;}
        .btn-post-batch:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59,130,246,0.6); }

        /* Toasts */
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:15px 25px; border-radius:12px; font-weight:800; font-size:1rem; display:flex; align-items:center; gap:12px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 10px 40px rgba(0,0,0,0.6); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; border: 1px solid rgba(255,255,255,0.1);}
        .toast.success { background:rgba(16,185,129,0.9); color:#fff; }
        .toast.error { background:rgba(239,68,68,0.9); color:#fff; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
    </style>
</head>
<body>

    <div class="toast-container" id="toast-container"></div>

    <!-- Ambient Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="page-wrapper">
        <!-- Unified Premium Header -->
        <?php include 'includes/header.php'; ?>

        <!-- Filters -->
        <div class="filters-wrapper">
            <div class="filters-top">
                <div class="status-toggle">
                    <button class="status-btn active unposted" id="status-unposted" onclick="setStatus('unposted')">⏳ معلقة للترحيل</button>
                    <button class="status-btn posted" id="status-posted" onclick="setStatus('posted')">✅ مكتملة الترحيل</button>
                </div>
                
                <div class="search-box">
                    <span class="icon">🔍</span>
                    <input type="text" id="search-input" placeholder="ابحث بالمرجع، الاسم، أو المبلغ..." onkeyup="handleFilterChange()">
                </div>
                
                <div class="date-filters">
                    <label style="color:#94a3b8; font-size:0.85rem; font-weight:bold;">من:</label>
                    <input type="date" id="date-from" class="date-input" onchange="handleFilterChange()">
                    <label style="color:#94a3b8; font-size:0.85rem; font-weight:bold;">إلى:</label>
                    <input type="date" id="date-to" class="date-input" onchange="handleFilterChange()">
                </div>
            </div>
            <div class="type-filters" id="type-filters">
                <button class="type-chip active" id="type-all" onclick="setType('all')">الكل</button>
                <button class="type-chip" id="type-sales" onclick="setType('sales')">مبيعات</button>
                <button class="type-chip" id="type-purchases" onclick="setType('purchases')">مشتريات</button>
                <button class="type-chip" id="type-receipt" onclick="setType('receipt')">سندات قبض</button>
                <button class="type-chip" id="type-payment" onclick="setType('payment')">سندات صرف</button>
                <button class="type-chip" id="type-journal" onclick="setType('journal')">قيود يومية</button>
            </div>
        </div>

        <!-- Split Layout -->
        <div class="split-layout">
            
            <!-- Master Pane (Right) -->
            <div class="master-pane">
                <div class="table-scroll">
                    <table class="erp-table" id="data-table" style="display: none;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="check-all-rows" onchange="toggleAllRows(this)" title="تحديد الكل">
                                </th>
                                <th style="width: 100px;">النوع</th>
                                <th style="width: 100px;">التاريخ</th>
                                <th style="width: 130px;">رقم المرجع</th>
                                <th>العميل / المورد / البيان</th>
                                <th style="width: 110px;">الاستحقاق</th>
                                <th style="width: 110px;">طريقة الدفع</th>
                                <th style="width: 140px; text-align: left;">المبلغ الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Rows injected via JS -->
                        </tbody>
                    </table>

                    <div id="no-data-msg" style="display: none; text-align: center; padding: 60px 20px; color: #94a3b8; background: rgba(255,255,255,0.02); height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;">📭</div>
                        <h2 style="color: #fff; font-size: 1.3rem; margin: 0 0 10px 0;">لا توجد عمليات مطابقة!</h2>
                        <p style="font-size: 0.95rem; margin: 0 0 25px 0;">لم يتم العثور على أي عمليات تتطابق مع فلاتر البحث الحالية.</p>
                        <button onclick="injectDummyData()" style="background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); padding: 10px 20px; border-radius: 10px; cursor: pointer; font-family: 'Tajawal', sans-serif; font-weight: bold; font-size: 0.95rem; transition: 0.3s;" onmouseover="this.style.background='rgba(59,130,246,0.3)'" onmouseout="this.style.background='rgba(59,130,246,0.15)'">
                            ➕ إضافة بيانات تجريبية (للاختبار)
                        </button>
                    </div>
                </div>

                <!-- Sticky Action Bar for Master Pane -->
                <div class="sticky-action-bar" id="sticky-bar">
                    <div style="color: #cbd5e1; font-size: 1rem; font-weight: bold; display: flex; align-items: center; gap: 10px;">
                        تم تحديد <span class="selected-count" id="selected-count">0</span> عمليات
                    </div>
                    <button class="btn-post-batch" onclick="postAllChecked()">
                        <span>✔️</span> ترحيل الدفعة المحددة واعتمادها
                    </button>
                </div>
            </div>

            <!-- Detail Pane (Left) -->
            <div class="detail-pane" id="detail-pane">
                <div class="empty-detail" id="empty-detail">
                    <div class="empty-detail-icon">📑</div>
                    <h3 style="color:#f8fafc; margin-bottom:10px; font-size: 1.4rem;">لم يتم تحديد أي عملية</h3>
                    <p style="font-size:0.95rem; line-height: 1.6;">اضغط على أي سطر من قائمة العمليات لعرض التفاصيل الكاملة والقيد المحاسبي التلقائي الخاص بها هنا للمراجعة.</p>
                </div>
                
                <div id="detail-content" class="detail-inner" style="display: none;">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <script src="js/localforage.min.js"></script>
    <script src="js/app.js?v=<?php echo time(); ?>"></script>
    <script src="js/posting-accounts.js?v=2026"></script>
    <script>
        function injectDummyData() {
            const dummySales = [{
                id: 'INV-1001', invoiceNo: 'INV-1001', date: new Date().toISOString().split('T')[0],
                customerId: 'CUST-001', customerName: 'شركة الآفاق الحديثة',
                totalAmount: 12500, paymentMethod: 'credit', paymentAccountId: '',
                dueDate: '2026-07-15', description: 'مبيعات آجلة - توريد أجهزة مكتبية', isPosted: false,
                items: [
                    { itemName: 'حاسب آلي مكتبي', qty: 2, price: 4000, total: 8000 },
                    { itemName: 'طابعة ليزر', qty: 3, price: 1500, total: 4500 }
                ]
            }];
            const dummyPayment = [{
                id: 'PAY-205', voucherNo: 'PAY-205', date: new Date().toISOString().split('T')[0],
                toAccount: 'exp-rent', fromAccount: 'bank-002',
                amountLocal: 4000, toStatement: 'دفع إيجار المقر بشيك', isPosted: false,
                paymentMethod: 'cheque', dueDate: '2026-06-30',
                description: 'سداد الإيجار الشهري عبر شيك بنكي'
            }];
            localStorage.setItem('erp_sales_invoices_v1', JSON.stringify(dummySales));
            localStorage.setItem('erp_single_payments_v1', JSON.stringify(dummyPayment));
            alert('تم حقن الفاتورة (الآجلة) والسند (الشيك) بنجاح!');
            location.reload();
        }
    </script>
</body>
</html>
