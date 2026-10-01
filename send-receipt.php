<?php
// =============================================================
// MAGAIEES ERP 2026 - Customer Inbound Bankak Receipt Submission Portal
// بوابة العميل لإرسال إشعار تحويل بنكك وتأكيد السداد للشركة
// =============================================================
require_once 'db_bridge.php';
$pdo = getDBConnection();

$tenantId = $_GET['tenant_id'] ?? $_GET['t'] ?? '1';
$companyName = 'مقاييس لإدارة الأعمال والحلول الذكية';
try {
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT name FROM tenants WHERE id = ?");
        $stmt->execute([$tenantId]);
        $cName = $stmt->fetchColumn();
        if ($cName) $companyName = $cName;
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>بوابة إرسال إشعار الدفع - <?= htmlspecialchars($companyName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <script src="js/magaiees_back.js?v=2026_back_universal" defer></script>
    <style>
        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.85);
            --primary: #10b981;
            --accent: #38bdf8;
            --border: rgba(255, 255, 255, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-color);
            font-family: 'Tajawal', sans-serif;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            direction: rtl;
        }
        .container {
            width: 100%;
            max-width: 520px;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 2px solid rgba(16, 185, 129, 0.45);
            border-radius: 24px;
            padding: 24px 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.85), 0 0 35px rgba(16, 185, 129, 0.25);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1.5px solid var(--border);
            padding-bottom: 16px;
        }
        .logo-badge {
            width: 65px;
            height: 65px;
            margin: 0 auto 10px auto;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(56, 189, 248, 0.3));
            border: 2px solid #34d399;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.45);
        }
        .form-group {
            margin-bottom: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label {
            font-size: 0.88rem;
            font-weight: 800;
            color: #cbd5e1;
        }
        input, select, textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            color: #fff;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.96rem;
            outline: none;
            transition: all 0.25s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
            background: rgba(0, 0, 0, 0.6);
        }
        .file-upload-box {
            border: 2px dashed rgba(56, 189, 248, 0.5);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            background: rgba(56, 189, 248, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        .file-upload-box:hover {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: 2px solid #34d399;
            color: #fff;
            font-family: 'Tajawal', sans-serif;
            font-size: 1.15rem;
            font-weight: 900;
            padding: 14px;
            border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5);
            transition: all 0.25s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.7);
        }
        .success-card {
            display: none;
            text-align: center;
            padding: 30px 10px;
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Header -->
        <div class="header">
            <div class="logo-badge">📥</div>
            <h2 style="font-size: 1.35rem; font-weight: 900; color: #f8fafc; margin-bottom: 4px;">
                بوابة إرسال إشعار السداد البنكي
            </h2>
            <p style="font-size: 0.85rem; color: #94a3b8; font-weight: 700;">
                إلى: <strong style="color: #38bdf8;"><?= htmlspecialchars($companyName) ?></strong>
            </p>
        </div>

        <!-- Submission Form -->
        <form id="receiptForm" onsubmit="submitReceipt(event)">
            <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($tenantId) ?>">

            <div class="form-group">
                <label>اسم العميل / المؤسسة: <span style="color:#ef4444;">*</span></label>
                <input type="text" id="custName" name="customer_name" placeholder="مثال: شركة البركة للمقاولات" required>
            </div>

            <div class="form-group">
                <label>رقم هاتف الواتساب: <span style="color:#ef4444;">*</span></label>
                <input type="tel" id="custPhone" name="customer_phone" placeholder="مثال: 0912345678" required style="font-family:'Consolas',monospace; direction:ltr; text-align:right;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label>البنك المحول منه:</label>
                    <select name="bank_name" id="bankName">
                        <option value="بنك الخرطوم (بنكك)" selected>بنك الخرطوم (بنكك)</option>
                        <option value="بنك فيصل الإسلامي (فوري)">بنك فيصل (فوري)</option>
                        <option value="بنك أمدرمان الوطني (أوكاش)">أمدرمان (أوكاش)</option>
                        <option value="بنك النيل (سريع)">بنك النيل (سريع)</option>
                        <option value="بنك آخر">بنك آخر</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>المبلغ المحول (SDG): <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="any" id="amount" name="amount" placeholder="مثال: 150000" required style="font-family:'Consolas',monospace;">
                </div>
            </div>

            <div class="form-group">
                <label>رقم المعاملة / العملية المصرفية: <span style="color:#ef4444;">*</span></label>
                <input type="text" id="transferNo" name="transfer_no" placeholder="مثال: 84920184" required style="font-family:'Consolas',monospace; letter-spacing:1px;">
            </div>

            <div class="form-group">
                <label>صورة إشعار التحويل (لقطة الشاشة):</label>
                <div class="file-upload-box" onclick="document.getElementById('receiptFile').click()">
                    <input type="file" id="receiptFile" name="receipt_file" accept="image/*" style="display:none;" onchange="previewFile(this)">
                    <div id="uploadPlaceholder">
                        <span style="font-size: 2.2rem; display: block; margin-bottom: 6px;">📷</span>
                        <strong style="color: #7dd3fc; font-size: 0.92rem; display: block;">انقر لاختيار أو تصوير إشعار بنكك</strong>
                        <span style="font-size: 0.76rem; color: #94a3b8;">يدعم صور PNG, JPG, WEBP</span>
                    </div>
                    <img id="filePreview" style="display:none; max-height:160px; max-width:100%; border-radius:10px; margin:0 auto; object-fit:contain;">
                </div>
            </div>

            <button type="submit" id="btnSubmit" class="btn-submit">
                <span>🚀 إرسال الإشعار وتأكيد السداد</span>
            </button>
        </form>

        <!-- Success Result Card -->
        <div id="successCard" class="success-card">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(16, 185, 129, 0.2); border: 2.5px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 2.6rem; margin: 0 auto 16px auto; box-shadow: 0 0 35px rgba(16, 185, 129, 0.5);">
                ✅
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 900; color: #f8fafc; margin-bottom: 8px;">
                تم استلام إشعارك بنجاح!
            </h3>
            <p style="font-size: 0.90rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
                وصل إشعار التحويل المالي إلى إدارة الحسابات في <strong style="color:#38bdf8;"><?= htmlspecialchars($companyName) ?></strong>، وجاري الفحص والمطابقة الآلية.
            </p>
            <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px; font-size: 0.85rem; margin-bottom: 20px; text-align: right;">
                <div style="margin-bottom: 4px;">رقم المعاملة: <strong id="resTransferNo" style="color:#fef08a; font-family:'Consolas',monospace;"></strong></div>
                <div>المبلغ: <strong id="resAmount" style="color:#38bdf8; font-family:'Consolas',monospace;"></strong> SDG</div>
            </div>
            <button type="button" onclick="location.reload()" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 10px 24px; border-radius: 10px; font-weight: 800; cursor: pointer;">
                إرسال إشعار آخر ➕
            </button>
        </div>

    </div>

    <script>
    function previewFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('uploadPlaceholder').style.display = 'none';
                const img = document.getElementById('filePreview');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    async function submitReceipt(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.innerHTML = '<span>🔄 جاري الإرسال والمطابقة...</span>';

        const form = document.getElementById('receiptForm');
        const formData = new FormData(form);
        formData.append('action', 'upload');

        try {
            const res = await fetch('api/inbound_receipts.php?action=upload', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                form.style.display = 'none';
                document.getElementById('resTransferNo').textContent = document.getElementById('transferNo').value;
                document.getElementById('resAmount').textContent = Number(document.getElementById('amount').value).toLocaleString();
                document.getElementById('successCard').style.display = 'block';
            } else {
                alert('خطأ: ' + (data.error || 'تعذر إرسال الإشعار'));
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = '<span>🚀 إرسال الإشعار وتأكيد السداد</span>';
            }
        } catch(err) {
            alert('حدث خطأ في الاتصال بالخادم: ' + err.message);
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = '<span>🚀 إرسال الإشعار وتأكيد السداد</span>';
        }
    }
    </script>
</body>
</html>
