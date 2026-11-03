<?php
// تم دمج مراقب سقوف الائتمان والمديونية العامة في منظومة أعمار الديون والحقوق وسقوف الائتمان الموحدة 360°
$query = $_SERVER['QUERY_STRING'] ?? '';
$target = 'aging-report.php?tab=credit' . ($query ? '&' . $query : '');
header("Location: " . $target);
exit;
?>
