<?php $page_title = 'تحويل بين الخزن'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحويل بين الخزن | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        .transfer-container { width: 95%; max-width: 1600px; margin: 30px auto; background: rgba(18,15,25,0.65); backdrop-filter: blur(20px); border-radius: 20px; border: 1px solid rgba(56,189,248,0.2); padding: 20px 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.5); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-bottom: 25px; }
        .t-label { display: block; margin-bottom: 8px; color: #38bdf8; font-weight: 700; font-size: 0.9rem; }
        .t-input, .t-select { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.3); border: 1px solid rgba(56,189,248,0.3); border-radius: 12px; color: #fff; font-family: inherit; font-size: 1rem; transition: 0.3s; box-sizing: border-box; }
        .t-input:focus, .t-select:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,0.1); outline: none; }
        .t-amount { font-size: 1.5rem; font-weight: 900; color: #34d399; text-align: center; }
        .btn-confirm { background: linear-gradient(135deg, #38bdf8, #0284c7); border: none; color: white; padding: 15px 30px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; cursor: pointer; transition: 0.3s; width: 100%; font-family: inherit; box-shadow: 0 5px 15px rgba(56,189,248,0.4); }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(56,189,248,0.6); }
    
        .custom-dropdown-list {
            display: none; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; max-height: 200px; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 1000;
        }
        .custom-dropdown-list.show { display: block; }
        .custom-dropdown-list .item {
            padding: 10px 15px; color: #fff; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.2s;
        }
        .custom-dropdown-list .item:hover { background: rgba(56, 189, 248, 0.2); }
        .custom-dropdown-list .item .bal { font-size: 0.8rem; color: #94a3b8; display: block; margin-top: 3px; font-family: monospace; }
        </style>

</head>
<body style="background:var(--bg-color); color:var(--text-main); font-family:'Tajawal', sans-serif;">

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1" style="background:radial-gradient(circle, rgba(56,189,248,0.2) 0%, transparent 70%);"></div>
        <div class="orb orb-2" style="background:radial-gradient(circle, rgba(16,185,129,0.2) 0%, transparent 70%);"></div>
    </div>

    <div class="transfer-container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="width:50px; height:50px; border-radius:15px; background:linear-gradient(135deg,#38bdf8,#0284c7); display:flex; align-items:center; justify-content:center; font-size:1.5rem; box-shadow: 0 5px 15px rgba(56,189,248,0.4);">🔄</div>
                <div>
                    <h2 style="margin:0; font-weight:900; color:#fff; font-size:1.5rem;">تحويل بين الخزن</h2>
                    <span style="color:var(--text-dim); font-size:0.85rem;">نقل النقدية من صندوق إلى آخر</span>
                </div>
            </div>
            <button onclick="window.location.href='vouchers-hub.php'" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#fff; padding:10px 20px; border-radius:10px; cursor:pointer; font-family:inherit; font-weight:700; transition:0.3s;">العودة</button>
        </div>

        <div class="form-grid">
            <div class="full-width">
                <label class="t-label">المبلغ المحول</label>
                <input type="text" inputmode="decimal" class="t-input t-amount" placeholder="0.00" id="t-amount">
            </div>

            <div>
                <label class="t-label">من الخزنة (المحول منها)</label>
                
                <input type="hidden" id="t-from">
                <div style="position:relative; z-index:100;">
                    <input type="text" class="t-input" id="t-from-search" placeholder="ابحث عن الخزينة المحول منه..." onfocus="showDropdown('from')" onblur="hideDropdownDelayed('from')" oninput="filterDropdown('from')" autocomplete="off">
                    <div class="custom-dropdown-list" id="from-dropdown-list" style="position:absolute; top:100%; left:0; right:0; margin-top:5px;"></div>
                </div>

            </div>
            <div>
                <label class="t-label">إلى الخزنة (المحول إليها)</label>
                
                <input type="hidden" id="t-to">
                <div style="position:relative; z-index:90;">
                    <input type="text" class="t-input" id="t-to-search" placeholder="ابحث عن الخزينة المحول إليه..." onfocus="showDropdown('to')" onblur="hideDropdownDelayed('to')" oninput="filterDropdown('to')" autocomplete="off">
                    <div class="custom-dropdown-list" id="to-dropdown-list" style="position:absolute; top:100%; left:0; right:0; margin-top:5px;"></div>
                </div>

            </div>

            <div class="full-width">
                <label class="t-label">تاريخ التحويل</label>
                <input type="date" class="t-input" id="t-date">
            </div>

            <div class="full-width">
                <label class="t-label">البيان / الملاحظات</label>
                <input type="text" class="t-input" placeholder="سبب التحويل..." id="t-notes">
            </div>
        </div>

        <button class="btn-confirm" onclick="confirmTransfer()">تأكيد تحويل النقدية</button>
    </div>

    <script>

    let ACCOUNTS = [];
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const data = JSON.parse(localStorage.getItem('erp_safes_v1')) || [];
            ACCOUNTS = data.filter(x => !x.deleted).map(x => ({
                id: x.id,
                name: x.name || x.safeName || x.bankName,
                balance: parseFloat(x.currentBalance || 0)
            }));
        } catch(e) {}
    });

    function renderDropdown(type, filter = '') {
        const list = document.getElementById(type + '-dropdown-list');
        const term = window.normalizeArabic ? window.normalizeArabic(filter) : filter.toLowerCase();
        
        let html = '';
        ACCOUNTS.forEach(a => {
            const nName = window.normalizeArabic ? window.normalizeArabic(a.name) : a.name.toLowerCase();
            if(!term || nName.includes(term)) {
                html += `<div class="item" onmousedown="selectAccount('${type}', '${a.id}', '${a.name}')">
                    ${a.name}
                    <span class="bal">الرصيد: ${a.balance.toLocaleString()}</span>
                </div>`;
            }
        });
        
        if(!html) html = '<div style="padding:10px; color:#94a3b8; text-align:center;">لا يوجد تطابق</div>';
        list.innerHTML = html;
    }

    function showDropdown(type) {
        renderDropdown(type, document.getElementById(type + '-search').value);
        document.getElementById(type + '-dropdown-list').classList.add('show');
    }

    function hideDropdownDelayed(type) {
        setTimeout(() => {
            document.getElementById(type + '-dropdown-list').classList.remove('show');
        }, 200);
    }

    function filterDropdown(type) {
        renderDropdown(type, document.getElementById(type + '-search').value);
    }

    window.selectAccount = function(type, id, name) {
        document.getElementById('t-' + type).value = id;
        document.getElementById('t-' + type + '-search').value = name;
        document.getElementById(type + '-dropdown-list').classList.remove('show');
    }

        document.getElementById('t-date').valueAsDate = new Date();
        function confirmTransfer() {
            const amt = document.getElementById('t-amount').value;
            const from = document.getElementById('t-from').value;
            const to = document.getElementById('t-to').value;
            if(!amt || amt <= 0) return alert('الرجاء إدخال مبلغ صحيح');
            if(from === to) return alert('لا يمكن التحويل لنفس الخزنة');
            alert('تم تأكيد التحويل بنجاح!');
            window.location.href='vouchers-hub.php';
        }
    </script>
</body>
</html>
