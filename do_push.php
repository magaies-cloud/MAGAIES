<?php
$log = "Adding files...\n";
$log .= shell_exec('git add . 2>&1');
$log .= "\nCommitting...\n";
$log .= shell_exec('git commit -m "Add preset saving feature with progress bar for balances report" 2>&1');
$log .= "\nPushing to GitHub...\n";
$log .= shell_exec('git push origin main 2>&1');
file_put_contents(__DIR__ . '/push_log.txt', $log);
echo "Done";
