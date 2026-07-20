<?php
$f = 'sales-invoice.php';
$c = file_get_contents($f);
$c = preg_replace('/<\?php \$page_title = \'فاتورة مبيعات\'; \?>/', "<?php \$page_title = 'فاتورة مبيعات';\n\$voucher_no = 'INV' . date('ym') . str_pad(rand(10, 9999), 4, '0', STR_PAD_LEFT);\n?>", $c);
$c = preg_replace('/<div class="inv-badge" id="inv-number" style="margin:0 5px;">000001<\/div>/', '<div class="inv-badge" id="inv-number" style="margin:0 5px;"><?php echo $voucher_no; ?></div>', $c);
file_put_contents($f, $c);
echo "Sales invoice updated.\n";
