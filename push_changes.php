<?php
$output = shell_exec('git add . 2>&1');
echo "ADD: " . $output . "\n";
$output = shell_exec('git commit -m "Update visual analytics: add waterfall, heatmap, currency formats, and increase font size" 2>&1');
echo "COMMIT: " . $output . "\n";
$output = shell_exec('git push origin main 2>&1');
echo "PUSH MAIN: " . $output . "\n";
$output = shell_exec('git push origin feature/amar-updates 2>&1');
echo "PUSH FEATURE: " . $output . "\n";
file_put_contents('git_deploy_log.txt', "DONE");
