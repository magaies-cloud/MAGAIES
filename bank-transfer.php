<?php $page_title = 'تحويل بين البنوك'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحويل بين البنوك | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <style>
        .transfer-container { width: 95%; max-width: 1600px; margin: 30px auto; background: rgba(18,15,25,0.65); backdrop-filter: blur(20px); border-radius: 20px; border: 1px solid rgba(168,85,247,0.2); padding: 20px 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.5); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-bottom: 25px; }
        .t-label { display: block; margin-bottom: 8px; color: #a855f7; font-weight: 700; font-size: 0.9rem; }
        .t-input, .t-select { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.3); border: 1px solid rgba(168,85,247,0.3); border-radius: 12px; color: #fff; font-family: inherit; font-size: 1rem; transition: 0.3s; box-sizing: border-box; }
        .t-input:focus, .t-select:focus { border-color: #a855f7; box-shadow: 0 0 0 3px rgba(168,85,247,0.1); outline: none; }
        .t-amount { font-size: 1.5rem; font-weight: 900; color: #34d399; text-align: center; }
        .btn-confirm { background: linear-gradient(135deg, #a855f7, #7e22ce); border: none; color: white; padding: 15px 30px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; cursor: pointer; transition: 0.3s; width: 100%; font-family: inherit; box-shadow: 0 5px 15px rgba(168,85,247,0.4); }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(168,85,247,0.6); }
    </style>
</head>
<body style="background:var(--bg-color); color:var(--text-main); font-family:'Tajawal', sans-serif;">

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1" style="background:radial-gradient(circle, rgba(168,85,247,0.2) 0%, transparent 70%);"></div>
        <div class="orb orb-2" style="background:radial-gradient(circle, rgba(56,189,248,0.2) 0%, transparent 70%);"></div>
    </div>

    <div class="transfer-container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="width:50px; height:50px; border-radius:15px; background:linear-gradient(135deg,#a855f7,#7e22ce); display:flex; align-items:center; justify-content:center; font-size:1.5rem; box-shadow: 0 5px 15px rgba(168,85,247,0.4);">🏦</div>
                <div>
                    <h2 style="margin:0; font-weight:900; color:#fff; font-size:1.5rem;">تحويل بين البنوك</h2>
                    <span style="color:var(--text-dim); font-size:0.85rem;">نقل الأموال بين الحسابات البنكية</span>
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
                <label class="t-label">من البنك (المحول منه)</label>
                <select class="t-select" id="t-from">
                    <option value="">-- اختر البنك --</option>
                    <option value="b1">بنك الخرطوم - حساب جاري</option>
                    <option value="b2">بنك فيصل الإسلامي</option>
                    <option value="b3">بنك أمدرمان الوطني</option>
                </select>
            </div>
            <div>
                <label class="t-label">إلى البنك (المحول إليه)</label>
                <select class="t-select" id="t-to">
                    <option value="">-- اختر البنك --</option>
                    <option value="b1">بنك الخرطوم - حساب جاري</option>
                    <option value="b2">بنك فيصل الإسلامي</option>
                    <option value="b3">بنك أمدرمان الوطني</option>
                </select>
            </div>

            <div>
                <label class="t-label">رقم العملية / الشيك / التحويلة</label>
                <input type="text" class="t-input" placeholder="مثال: TRX-982374" id="t-ref">
            </div>
            <div>
                <label class="t-label">تاريخ التحويل</label>
                <input type="date" class="t-input" id="t-date">
            </div>

            <div class="full-width">
                <label class="t-label">البيان / الملاحظات</label>
                <input type="text" class="t-input" placeholder="سبب التحويل..." id="t-notes">
            </div>
        </div>

        <button class="btn-confirm" onclick="confirmTransfer()">تأكيد عملية التحويل البنكي</button>
    </div>

    <script>
        document.getElementById('t-date').valueAsDate = new Date();
        function confirmTransfer() {
            const amt = document.getElementById('t-amount').value;
            const ref = document.getElementById('t-ref').value;
            if(!amt || amt <= 0) return alert('الرجاء إدخال مبلغ صحيح');
            if(!ref) return alert('الرجاء إدخال رقم العملية أو الشيك');
            alert('تم تأكيد التحويل بنجاح!');
            window.location.href='vouchers-hub.php';
        }
    </script>
</body>
</html>
