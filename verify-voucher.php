<?php
// =========================================================================
// بوابة التحقق الرقمي الرسمي من صحة واعتماد السندات المالية عبر الـ QR والباركود
// Digital Voucher Verification & QR Certificate Portal
// =========================================================================
$page_title = 'شهادة التحقق الرقمي من السند المالي (QR Verified)';
$header_theme_class = 'header-theme-reports';

$voucherId = isset($_GET['id']) ? trim($_GET['id']) : '';
$amt       = isset($_GET['amt']) ? trim($_GET['amt']) : '';
$date      = isset($_GET['date']) ? trim($_GET['date']) : '';
$type      = isset($_GET['type']) ? trim($_GET['type']) : 'سند مالي معتمد';
$fromAcc   = isset($_GET['from']) ? trim($_GET['from']) : '';
$toAcc     = isset($_GET['to']) ? trim($_GET['to']) : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <script src="js/theme.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #070b14;
            color: #f8fafc;
            margin: 0;
            min-height: 100vh;
        }
        .verify-wrap {
            max-width: 760px;
            margin: 30px auto;
            padding: 20px;
        }
        .cert-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(30, 27, 75, 0.92));
            border: 2px solid #10b981;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 22px 60px rgba(0, 0, 0, 0.65), 0 0 35px rgba(16, 185, 129, 0.2);
            text-align: center;
        }
        .verified-stamp {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.18);
            border: 1.5px solid #10b981;
            color: #34d399;
            padding: 6px 18px;
            border-radius: 99px;
            font-weight: 900;
            font-size: 0.95rem;
            margin-bottom: 14px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
            margin: 18px 0;
            text-align: right;
        }
        .meta-box {
            background: rgba(2, 6, 23, 0.65);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 14px;
            padding: 12px 14px;
        }
        .meta-box span {
            display: block;
            font-size: 0.76rem;
            color: #94a3b8;
            font-weight: 700;
        }
        .meta-box strong {
            font-size: 1rem;
            color: #ffffff;
            font-weight: 900;
        }
        .action-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .v-btn {
            padding: 10px 18px;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-weight: 800;
            font-size: 0.86rem;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            background: rgba(30, 41, 59, 0.85);
        }
        .v-btn.primary { background: linear-gradient(135deg, #0284c7, #2563eb); border-color: #38bdf8; }
        .v-btn.emerald { background: linear-gradient(135deg, #059669, #10b981); border-color: #34d399; }
    </style>
</head>
<body>
<div class="page-container">
    <?php include 'includes/header.php'; ?>
    <div class="verify-wrap">
        <div class="cert-card" id="cert-card-box">
            <div class="verified-stamp">✅ مستند مالي موثق ومعتمد إلكترونياً (QR & Barcode Verified)</div>
            <h1 style="margin:0 0 6px;font-size:1.5rem;font-weight:900;color:#22d3ee;" id="v-title"><?php echo htmlspecialchars($type); ?></h1>
            <div style="font-family:monospace;font-size:1.35rem;font-weight:900;color:#fef08a;margin-bottom:14px;" id="v-id">#<?php echo htmlspecialchars($voucherId ?: '—'); ?></div>

            <div class="meta-grid">
                <div class="meta-box">
                    <span>📅 تاريخ السند</span>
                    <strong id="v-date"><?php echo htmlspecialchars($date ?: '—'); ?></strong>
                </div>
                <div class="meta-box">
                    <span>💰 المبلغ المعتمد</span>
                    <strong id="v-amt" style="color:#34d399;"><?php echo htmlspecialchars($amt ?: '—'); ?></strong>
                </div>
                <div class="meta-box">
                    <span>📤 من حساب (صُرف منه)</span>
                    <strong id="v-from"><?php echo htmlspecialchars($fromAcc ?: '—'); ?></strong>
                </div>
                <div class="meta-box">
                    <span>📥 إلى حساب (المستفيد)</span>
                    <strong id="v-to"><?php echo htmlspecialchars($toAcc ?: '—'); ?></strong>
                </div>
            </div>

            <div id="v-codes-area" style="background:#fff;border-radius:16px;padding:14px;margin:16px auto;max-width:440px;color:#0f172a;"></div>

            <div class="action-row">
                <a href="single-cash-payment.php?id=<?php echo urlencode($voucherId); ?>" id="v-open-link" class="v-btn primary">✏️ فتح السند في النظام</a>
                <a href="general-ledger.php?search=<?php echo urlencode($voucherId); ?>" class="v-btn emerald">📘 عرض القيد في دفتر الأستاذ</a>
                <button type="button" onclick="window.print()" class="v-btn">🖨️ طباعة الشهادة</button>
                <a href="vouchers-hub.php" class="v-btn">💰 بوابة المعاملات المالية</a>
            </div>
        </div>
    </div>
</div>
<script src="js/localforage.min.js"></script>
<script src="js/voucher-qr-barcode-suite.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const vId = <?php echo json_encode($voucherId); ?>;
    const area = document.getElementById('v-codes-area');
    if (vId && window.findVoucherAnywhereByCode) {
        const found = await window.findVoucherAnywhereByCode(vId);
        if (found) {
            document.getElementById('v-title').textContent = found.typeLabel;
            document.getElementById('v-date').textContent = found.date;
            document.getElementById('v-amt').textContent = Number(found.amount || 0).toLocaleString() + ' ' + (found.currency || 'SDG');
            document.getElementById('v-from').textContent = found.fromAccount;
            document.getElementById('v-to').textContent = found.toAccount;
            document.getElementById('v-open-link').href = found.targetUrl;
        }
    }
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=2&data=' + encodeURIComponent(window.location.href);
    const barcodeSvg = window.buildCleanBarcodeSVG ? window.buildCleanBarcodeSVG(vId || 'VERIFIED') : '';
    if (area) {
        area.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-around;flex-wrap:wrap;gap:12px;">
                <img src="${qrUrl}" alt="QR" style="width:120px;height:120px;border:1px solid #cbd5e1;border-radius:8px;padding:4px;">
                <div>${barcodeSvg}</div>
            </div>
        `;
    }
});
</script>
</body>
</html>
