<?php
// توجيه موحد إلى شاشة الصرف والقبض المجمع المعتمدة - وضع القبض (multi-cash-payment.php?type=receipt)
$qs = $_GET;
if (!isset($qs['type'])) $qs['type'] = 'receipt';
header('Location: multi-cash-payment.php?' . http_build_query($qs));
exit;