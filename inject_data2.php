<?php
function injectServerData($phpFile, $table, $jsVar) {
    if (!file_exists($phpFile)) return;
    $c = file_get_contents($phpFile);
    if (strpos($c, "window.$jsVar =") !== false) return;
    
    $injectPHP = "\n\$" . $jsVar . "_json = '[]';\ntry {\n    \$stmt = \$pdo->prepare(\"SELECT data FROM " . $table . " WHERE tenant_id = ? ORDER BY id ASC\");\n    \$stmt->execute([\$GLOBAL_TENANT_ID]);\n    \$arr = [];\n    while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {\n        \$arr[] = json_decode(\$row['data'], true);\n    }\n    \$" . $jsVar . "_json = json_encode(\$arr);\n} catch (Exception \$e) {}\n?>\n<script>\n    window." . $jsVar . " = <?php echo \$" . $jsVar . "_json; ?>;\n</script>\n<?php\n";
    
    $c = preg_replace('/(\$voucher_no = .*?;\s*\$page_title = .*?;)\s*\?>/s', "$1$injectPHP", $c);
    file_put_contents($phpFile, $c);
}
injectServerData('single-cash-payment.php', 'single_payments', 'SERVER_SINGLE_PAYMENTS');
injectServerData('single-cash-receipt.php', 'single_receipts', 'SERVER_SINGLE_RECEIPTS');
injectServerData('sales-invoice.php', 'sales_invoices', 'SERVER_SALES_INVOICES');
echo "PHP files updated.\n";
