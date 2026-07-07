<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

echo "=== STDOUT LOGS ===\n";
echo shell_exec('tail -n 100 /root/.pm2/logs/elsajana-wa-out.log 2>&1');

echo "\n\n=== STDERR LOGS ===\n";
echo shell_exec('tail -n 100 /root/.pm2/logs/elsajana-wa-error.log 2>&1');
