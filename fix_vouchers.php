<?php
$f = 'single-cash-payment.php';
$c = file_get_contents($f);
$c = preg_replace('/<\?php \$page_title = \'حركة مالية\'; \?>/', "<?php \$page_title = 'حركة مالية';\n\$voucher_no = 'E' . date('ym') . str_pad(rand(10, 9999), 4, '0', STR_PAD_LEFT);\n?>", $c);
$c = preg_replace('/<span id="voucher-number" class="badge-val">000001<\/span>/', '<span id="voucher-number" class="badge-val"><?php echo $voucher_no; ?></span>', $c);
$c = preg_replace('/<span id="voucher-number" class="badge-val">0000001<\/span>/', '<span id="voucher-number" class="badge-val"><?php echo $voucher_no; ?></span>', $c);
file_put_contents($f, $c);

$f = 'single-cash-receipt.php';
$c = file_get_contents($f);
$c = preg_replace('/<\?php \$page_title = \'سند قبض\'; \?>/', "<?php \$page_title = 'سند قبض';\n\$voucher_no = 'R' . date('ym') . str_pad(rand(10, 9999), 4, '0', STR_PAD_LEFT);\n?>", $c);
$c = preg_replace('/<div class="inv-badge" id="voucher-number".*?>000001<\/div>/s', '<div class="inv-badge" id="voucher-number" style="margin:0 10px; color:#34d399; min-width:60px; text-align:center; font-size: 1.1rem;"><?php echo $voucher_no; ?></div>', $c);
file_put_contents($f, $c);

echo "Voucher numbers updated.\n";
