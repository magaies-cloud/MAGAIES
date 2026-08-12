<?php
header('Content-Type: text/plain; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate");
putenv("PATH=C:\\Program Files\\Git\\cmd;C:\\Program Files\\Git\\bin;" . getenv("PATH"));

$cmd = 'cd /d "C:\\xampp\\htdocs\\ELSAJANA2\\magaies" && git merge --abort 2>&1 & git rebase --abort 2>&1 & git config user.name "Magaiees Developer" && git config user.email "dev@magaiees.com" && git pull origin main --no-edit -X ours 2>&1';

$output = shell_exec($cmd);

@unlink('check_users.php');
@unlink('test_login.php');

shell_exec('cd /d "C:\\xampp\\htdocs\\ELSAJANA2\\magaies" && git rm --ignore-unmatch check_users.php test_login.php 2>&1');
shell_exec('cd /d "C:\\xampp\\htdocs\\ELSAJANA2\\magaies" && git add . 2>&1');
shell_exec('cd /d "C:\\xampp\\htdocs\\ELSAJANA2\\magaies" && git commit -m "Update local database setup and push latest changes to GitHub" 2>&1');

$pushOutput = shell_exec('cd /d "C:\\xampp\\htdocs\\ELSAJANA2\\magaies" && git push origin main 2>&1 && git push origin feature/amar-updates 2>&1');

$totalOutput = "PULL:\n" . $output . "\n\nPUSH:\n" . $pushOutput;
echo "EXECUTION OUTPUT:\n" . $totalOutput;
file_put_contents('push_log.txt', date('Y-m-d H:i:s') . "\n" . $totalOutput);
?>
