<?php
require 'db.php';
header('Content-Type: text/plain; charset=UTF-8');

try {
    $tenantId = resolveCurrentTenantId($pdo);
    if ($tenantId <= 0) { http_response_code(401); exit('Unauthorized'); }
    $prefix = 'tenant_' . $tenantId . '_';
    $stmt = $pdo->prepare("DELETE FROM global_settings WHERE setting_key LIKE ? OR setting_key LIKE ?");
    $stmt->execute([$prefix . '%balances_report%', $prefix . 'balances_preset_%']);
    $count = $stmt->rowCount();
    echo "تم بنجاح فك القفل ومسح جميع التنسيقات المحفوظة.\n";
    echo "عدد السجلات التي تم مسحها: " . $count . "\n";
    echo "يمكنك الآن الذهاب لضبط 'افتراضي 1' من جديد.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
