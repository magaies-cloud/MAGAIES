<?php $page_title = 'تصفير بيانات التطبيق'; ?>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تصفير بيانات التطبيق | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; padding:20px; }
        .reset-card { max-width:600px; width:100%; background:rgba(15,23,42,0.9); border:1px solid rgba(239,68,68,0.3); border-radius:20px; padding:35px; box-shadow:0 20px 60px rgba(0,0,0,0.7); }
        .reset-title { color:#f87171; font-size:1.8rem; font-weight:900; margin-bottom:8px; }
        .reset-subtitle { color:#94a3b8; font-size:0.9rem; margin-bottom:30px; }
        .data-item { display:flex; justify-content:space-between; align-items:center; padding:12px 15px; background:rgba(0,0,0,0.2); border-radius:10px; margin-bottom:8px; border:1px solid rgba(255,255,255,0.05); }
        .data-item label { display:flex; align-items:center; gap:10px; color:#e2e8f0; font-weight:600; cursor:pointer; }
        .data-item .count-badge { font-size:0.8rem; color:#64748b; background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:6px; }
        .checkbox { width:18px; height:18px; accent-color:#ef4444; cursor:pointer; }
        .btn-reset { width:100%; padding:15px; border:none; border-radius:12px; font-size:1.1rem; font-weight:800; cursor:pointer; transition:all 0.3s; font-family:inherit; margin-top:20px; }
        .btn-do-reset { background:linear-gradient(135deg,#ef4444,#b91c1c); color:#fff; box-shadow:0 6px 20px rgba(239,68,68,0.5); }
        .btn-do-reset:hover { box-shadow:0 8px 30px rgba(239,68,68,0.8); transform:translateY(-2px); }
        .btn-cancel { background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1) !important; margin-top:10px; }
        .btn-cancel:hover { background:rgba(255,255,255,0.1); color:#f8fafc; }
        .warning-box { background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.3); border-radius:10px; padding:12px 16px; margin-bottom:20px; color:#fca5a5; font-size:0.88rem; line-height:1.6; }
        .select-all-row { display:flex; gap:15px; margin-bottom:15px; }
        .btn-select { background:none; border:1px solid rgba(255,255,255,0.1); color:#94a3b8; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:0.82rem; font-family:inherit; transition:all 0.2s; }
        .btn-select:hover { border-color:rgba(239,68,68,0.4); color:#f87171; }
        .result-toast { display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.5); color:#4ade80; padding:12px 25px; border-radius:12px; font-weight:700; z-index:999; }
    
        
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

<div class="result-toast" id="result-toast">تم مسح البيانات بنجاح!</div>

<div class="reset-card">
    <div style="text-align:center; margin-bottom:20px; font-size:3rem;">🗑️</div>
    <div class="reset-title" style="text-align:center;">مركز مسح البيانات الشامل</div>
    <div class="reset-subtitle" style="text-align:center;">حدد الجداول التي تريد مسحها نهائياً من النظام</div>

    <div class="warning-box">
        ⚠️ <strong>تحذير:</strong> هذه العملية <strong>لا يمكن التراجع عنها</strong>. سيتم حذف البيانات المحددة نهائياً من مساحة التخزين المحلية.
    </div>

    <div class="select-all-row">
        <button class="btn-select" onclick="selectAll(true)">✓ تحديد الكل</button>
        <button class="btn-select" onclick="selectAll(false)">✗ إلغاء الكل</button>
    </div>

    <div id="items-list"></div>

    <button class="btn-reset btn-do-reset" onclick="doReset()">مسح كل البيانات المحددة</button>
    <button class="btn-reset btn-cancel" onclick="history.back()">لا أريد، رجوع للوراء</button>
</div>

<script>
const DATA_KEYS = [
    { key: 'erp_customers_v1',              label: 'العملاء',                       icon: '👥' },
    { key: 'erp_suppliers_v1',              label: 'الموردون',                      icon: '🏢' },
    { key: 'erp_items_v1',                  label: 'المنتجات والمخزون',             icon: '📦' },
    { key: 'erp_accounts_v1',               label: 'الحسابات المالية',              icon: '💼' },
    { key: 'erp_sales_invoices_v1',         label: 'فواتير المبيعات',               icon: '📄' },
    { key: 'erp_purchase_invoices_v1',      label: 'فواتير المشتريات',              icon: '🛒' },
    { key: 'erp_single_receipts_v1',        label: 'سندات قبض مفردة',               icon: '💵' },
    { key: 'erp_single_payments_v1',        label: 'سندات صرف مفردة',               icon: '💸' },
    { key: 'erp_journal_vouchers_v1',       label: 'قيود اليومية',                  icon: '📓' },
    { key: 'erp_comprehensive_receipts_v1', label: 'سندات قبض مجمعة',               icon: '💰' },
    { key: 'erp_comprehensive_payments_v1', label: 'سندات صرف مجمعة',               icon: '💳' },
    { key: 'erp_inventory_v1',              label: 'أرصدة المخزون الفعلي',          icon: '🔄' },
    { key: 'erp_payroll_v1',                label: 'كشوفات الرواتب',                icon: '👨‍💼' },
    // Sequences
    { key: 'erp_invoice_seq',               label: 'عداد المبيعات (تسلسل)',         icon: '🔢' },
    { key: 'erp_purchase_seq',              label: 'عداد المشتريات (تسلسل)',        icon: '🔢' },
    { key: 'erp_single_receipt_seq',        label: 'عداد قبض مفرد',                 icon: '🔢' },
    { key: 'erp_single_payment_seq',        label: 'عداد صرف مفرد',                 icon: '🔢' },
    { key: 'erp_receipt_seq',               label: 'عداد قبض عام',                  icon: '🔢' },
    { key: 'erp_payment_seq',               label: 'عداد صرف عام',                  icon: '🔢' },
    { key: 'erp_jv_seq',                    label: 'عداد قيود اليومية',             icon: '🔢' },
];

function getCount(key) {
    try {
        const raw = localStorage.getItem(key);
        if (!raw) return '—';
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) return parsed.length + ' سجل';
        if (typeof parsed === 'number') return 'تسلسل: ' + parsed;
        return 'موجود';
    } catch { return 'موجود'; }
}

function renderList() {
    const container = document.getElementById('items-list');
    container.innerHTML = DATA_KEYS.map(item => {
        const count = getCount(item.key);
        const exists = localStorage.getItem(item.key) !== null;
        return `
        <div class="data-item" style="${!exists ? 'opacity:0.4' : ''}">
            <label>
                <input type="checkbox" class="checkbox data-checkbox" data-key="${item.key}" ${exists ? 'checked' : 'disabled'}>
                <span>${item.icon}</span>
                <span>${item.label}</span>
            </label>
            <span class="count-badge">${count}</span>
        </div>`;
    }).join('');
}

function selectAll(val) {
    document.querySelectorAll('.data-checkbox:not(:disabled)').forEach(cb => cb.checked = val);
}

function doReset() {
    const selected = Array.from(document.querySelectorAll('.data-checkbox:checked')).map(cb => cb.dataset.key);
    if (selected.length === 0) { alert('قم بتحديد أي جدول أولاً!'); return; }

    if (!confirm(`هل أنت متأكد تماماً من مسح ${selected.length} جداول محددة؟\n\nهذه العملية لا يمكن التراجع عنها!`)) return;

    selected.forEach(key => localStorage.removeItem(key));

    const toast = document.getElementById('result-toast');
    toast.textContent = `✓ تم مسح ${selected.length} جداول بنجاح!`;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; renderList(); }, 3000);
}

renderList();
</script>

<script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
