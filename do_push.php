<?php
echo "<pre>";
echo "Adding files...\n";
echo shell_exec('git add . 2>&1');
echo "\nCommitting...\n";
echo shell_exec('git commit -m "Add preset saving feature with progress bar for balances report" 2>&1');
echo "\nPushing to GitHub...\n";
echo shell_exec('git push origin main 2>&1');
echo "</pre>";
echo "Done";
