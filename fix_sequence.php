<?php
function updateFileSequence($file, $table, $prefix, $title) {
    $c = file_get_contents($file);
    $newPHP = "<?php
require_once 'api/db_bridge.php';
global \$pdo, \$GLOBAL_TENANT_ID;
\$prefix = '$prefix' . date('ym');
\$maxSeq = 0;
try {
    \$stmt = \$pdo->prepare(\"SELECT data FROM $table WHERE tenant_id = ?\");
    \$stmt->execute([\$GLOBAL_TENANT_ID]);
    while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
        \$d = json_decode(\$row['data'], true);
        \$id = isset(\$d['id']) ? (string)\$d['id'] : '';
        if (strpos(\$id, \$prefix) === 0) {
            \$seq = (int)substr(\$id, strlen(\$prefix));
            if (\$seq > \$maxSeq) \$maxSeq = \$seq;
        }
    }
} catch (Exception \$e) {
    // fallback
}
\$voucher_no = \$prefix . str_pad(\$maxSeq + 1, 4, '0', STR_PAD_LEFT);
\$page_title = '$title';
?>";

    $c = preg_replace('/<\?php.*?\$voucher_no = .*?\?>/s', $newPHP, $c, 1);
    file_put_contents($file, $c);
}

updateFileSequence('single-cash-payment.php', 'single_payments', 'E', 'حركة مالية');
updateFileSequence('single-cash-receipt.php', 'single_receipts', 'R', 'سند قبض');
updateFileSequence('sales-invoice.php', 'sales_invoices', 'INV', 'فاتورة مبيعات');

echo "Sequences updated.\n";
