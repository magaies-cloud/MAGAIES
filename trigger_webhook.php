<?php
header('Content-Type: text/plain; charset=UTF-8');
set_time_limit(180);
chdir(__DIR__);

if (!empty($_GET['sync_all'])) {
    // Clean up any leftover temp files if present
    foreach (['_sync_deploy_tmp.php', '_sync_deploy_tmp2.php', '_sync_deploy_tmp3.php', '_diag_tmp.php'] as $tmpF) {
        if (file_exists(__DIR__ . '/' . $tmpF)) {
            @unlink(__DIR__ . '/' . $tmpF);
        }
    }
    $msg = $_GET['msg'] ?? 'Sync 24/7 Cloud WhatsApp Hub, two-way live sync, and offline WhatsApp outbox';
    echo "=== Git Add, Commit & Push ===\n";
    echo shell_exec('git add -u 2>&1') . "\n";
    echo shell_exec('git commit -m ' . escapeshellarg($msg) . ' 2>&1') . "\n";
    echo shell_exec('git push origin main 2>&1') . "\n";
}

$localHead = trim(shell_exec('git rev-parse HEAD 2>&1'));
$payload = json_encode(['ref'=>'refs/heads/main','head_commit'=>['message'=>'Deploy ' . substr($localHead, 0, 7)]]);
$secret = 'magaees_deploy_2026_secure';
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

$urls = ['https://c.elsajana.com/deploy_webhook.php'];
foreach ($urls as $url) {
    echo "Triggering: $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Hub-Signature-256: ' . $signature
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($result === false || $httpCode >= 400) {
        echo "Failed for $url (HTTP $httpCode): $err $result\n";
    } else {
        echo "Success for $url (HTTP $httpCode): $result\n";
    }
}

$localHeadAfter = trim(shell_exec('git rev-parse HEAD 2>&1'));
$remoteHeadRaw = trim(shell_exec('git ls-remote origin refs/heads/main 2>&1'));
$remoteHeadAfter = trim(explode("\t", $remoteHeadRaw)[0] ?? '');
$gitStatusClean = trim(shell_exec('git status -s 2>&1'));

echo "\n=== 3-Way Parity Check ===\n";
echo "Local HEAD : $localHeadAfter\n";
echo "GitHub HEAD: $remoteHeadAfter\n";
echo "Git Status : " . ($gitStatusClean === '' ? 'CLEAN (100%)' : $gitStatusClean) . "\n";


