<?php
header('Content-Type: text/plain; charset=UTF-8');
$output = shell_exec('git add . 2>&1');
echo "ADD:\n" . $output . "\n";

$msg = "Fix report layout cross-device sync and add report-layout API endpoint";
$output = shell_exec('git commit -m ' . escapeshellarg($msg) . ' 2>&1');
echo "COMMIT:\n" . $output . "\n";

$output = shell_exec('git push origin main 2>&1');
echo "PUSH MAIN:\n" . $output . "\n";

$output = shell_exec('git push origin feature/amar-updates 2>&1');
echo "PUSH FEATURE:\n" . $output . "\n";

file_put_contents('push_log.txt', date('Y-m-d H:i:s') . " - " . $msg . "\n" . $output);
?>
