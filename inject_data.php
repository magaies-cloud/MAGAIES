<?php
function injectServerData($phpFile, $table, $jsVar) {
    if (!file_exists($phpFile)) return;
    $c = file_get_contents($phpFile);
    
    // Check if already injected
    if (strpos($c, "window.$jsVar =") !== false) return;
    
    $injectPHP = "
\$${jsVar}_json = '[]';
try {
    \$stmt = \$pdo->prepare(\"SELECT data FROM $table WHERE tenant_id = ? ORDER BY id ASC\");
    \$stmt->execute([\$GLOBAL_TENANT_ID]);
    \$arr = [];
    while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
        \$arr[] = json_decode(\$row['data'], true);
    }
    \$${jsVar}_json = json_encode(\$arr);
} catch (Exception \$e) {}
?>
<script>
    window.$jsVar = <?php echo \$${jsVar}_json; ?>;
</script>
<?php
";
    // Inject just before ?>
    $c = preg_replace('/(\$voucher_no = .*?;\s*\$page_title = .*?;)\s*\?>/s', "$1$injectPHP", $c);
    file_put_contents($phpFile, $c);
}

injectServerData('single-cash-payment.php', 'single_payments', 'SERVER_SINGLE_PAYMENTS');
injectServerData('single-cash-receipt.php', 'single_receipts', 'SERVER_SINGLE_RECEIPTS');
injectServerData('sales-invoice.php', 'sales_invoices', 'SERVER_SALES_INVOICES');

echo "PHP files updated.\n";
