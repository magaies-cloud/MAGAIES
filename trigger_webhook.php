<?php
header('Content-Type: text/plain; charset=UTF-8');
set_time_limit(120);
chdir(__DIR__);

$tStart = microtime(true);

// Support both CLI (--push "msg") and HTTP (?sync_all=1&msg=...)
$cliPush = (PHP_SAPI === 'cli' && in_array('--push', $argv ?? [], true));
$cliMsg = 'Sync updates from local';
if ($cliPush) {
    $idx = array_search('--push', $argv, true);
    if ($idx !== false && !empty($argv[$idx + 1])) {
        $cliMsg = $argv[$idx + 1];
    }
}

if (!empty($_GET['sync_all']) || $cliPush) {
    $msg = $_GET['msg'] ?? $cliMsg;
    echo "=== Git Add, Commit & Push ===\n";
    echo shell_exec('git add -u 2>&1');
    $status = trim((string)shell_exec('git status --porcelain -uno 2>&1'));
    if ($status !== '') {
        echo shell_exec('git commit -m ' . escapeshellarg($msg) . ' 2>&1') . "\n";
    } else {
        echo "Working tree already committed.\n";
    }
    echo shell_exec('git push origin main 2>&1') . "\n";
}

$localHead = trim((string)shell_exec('git rev-parse HEAD 2>&1'));
$payload = json_encode(['ref' => 'refs/heads/main', 'head_commit' => ['message' => 'Deploy ' . substr($localHead, 0, 7)]]);
$secret = 'magaees_deploy_2026_secure';
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

$url = 'https://c.elsajana.com/deploy_webhook.php';
echo "Triggering: $url\n";
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-Hub-Signature-256: ' . $signature,
        'Connection: keep-alive'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT        => 35,
]);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($result === false || $httpCode >= 400) {
    echo "Failed for $url (HTTP $httpCode): $err $result\n";
} else {
    echo "Success for $url (HTTP $httpCode): $result\n";
}

$localHeadAfter = trim((string)shell_exec('git rev-parse HEAD 2>&1'));
$remoteHeadAfter = trim((string)shell_exec('git rev-parse origin/main 2>&1'));
$gitStatusClean = trim((string)shell_exec('git status -s -uno 2>&1'));
$elapsed = round(microtime(true) - $tStart, 2);

echo "\n=== 3-Way Parity Check ({$elapsed}s) ===\n";
echo "Local HEAD : $localHeadAfter\n";
echo "GitHub HEAD: $remoteHeadAfter\n";
echo "Git Status : " . ($gitStatusClean === '' ? 'CLEAN (100%)' : $gitStatusClean) . "\n";
