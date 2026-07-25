<?php
require 'db.php';
header('Content-Type: text/plain; charset=UTF-8');

try {
    $stmt = $pdo->query("DELETE FROM global_settings WHERE setting_key LIKE 'balances_preset_%' OR setting_key LIKE 'gl_preset_%'");
    $count = $stmt->rowCount();
    echo "تم بنجاح فك القفل ومسح جميع التنسيقات المحفوظة.\n";
    echo "عدد السجلات التي تم مسحها: " . $count . "\n";
    echo "يمكنك الآن الذهاب لضبط 'افتراضي 1' من جديد.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
