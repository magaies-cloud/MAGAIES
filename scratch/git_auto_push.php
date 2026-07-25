<?php
// 1. Kill any hanging git processes that might be blocking the server
shell_exec('taskkill /F /IM git.exe 2>&1');
shell_exec('taskkill /F /IM ssh.exe 2>&1');

// 2. Change directory and execute git commands
$dir = 'c:\xampp\htdocs\ELSAJANA2\magaies';

echo "1. Adding files...\n";
$outAdd = shell_exec("cd $dir && git add . 2>&1");
echo $outAdd . "\n";

echo "2. Committing files...\n";
$outCommit = shell_exec("cd $dir && git commit -m \"Update presets (General Ledger & Balances)\" 2>&1");
echo $outCommit . "\n";

echo "3. Pulling from GitHub (to ensure sync)...\n";
// Using GIT_TERMINAL_PROMPT=0 to prevent it from hanging if it asks for a password
$outPull = shell_exec("cd $dir && set GIT_TERMINAL_PROMPT=0 && git pull origin main 2>&1");
echo $outPull . "\n";

echo "4. Pushing to GitHub...\n";
$outPush = shell_exec("cd $dir && set GIT_TERMINAL_PROMPT=0 && git push origin main 2>&1");
echo $outPush . "\n";

echo "Done!";
?>
