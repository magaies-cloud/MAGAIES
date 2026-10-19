<?php
// ============================================================
// Webhook للنشر التلقائي الفوري - فائق السرعة (Ultra-Fast Deploy)
// ============================================================

$WEBHOOK_SECRET = 'magaees_deploy_2026_secure';

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');

if (!empty($WEBHOOK_SECRET)) {
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $WEBHOOK_SECRET);
    if (!hash_equals($expected, $signature)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid signature']));
    }
}

$data = json_decode($payload, true);
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/main') {
    http_response_code(200);
    die(json_encode(['status' => 'skipped', 'reason' => 'Not main branch']));
}

$logFile = '/var/www/magaees/deploy_webhook.log';
$timestamp = date('Y-m-d H:i:s');
$commitMsg = $data['head_commit']['message'] ?? 'unknown';

@file_put_contents($logFile, "[$timestamp] Deploying: $commitMsg\n", FILE_APPEND);

// Prevent concurrent webhook runs (e.g. GitHub auto-webhook + manual trigger_webhook) from colliding on .git/index.lock
$lockFp = @fopen('/tmp/magaees_deploy.lock', 'c+');
if ($lockFp) {
    @flock($lockFp, LOCK_EX);
}

// Fast selective deployment:
// 1. Fetch & reset to origin/main without scanning node_modules or uploads
// 2. Only run migrate_ui_preferences.php if it changed
// 3. Only restart whatsapp-service if whatsapp-service.mjs or server.js changed
$commands = [
    'rm -f /var/www/magaees/.git/index.lock 2>&1',
    'cd /var/www/magaees',
    'git config core.autocrlf false 2>/dev/null || true',
    'git config core.fileMode false 2>/dev/null || true',
    'git fetch origin main --depth=5 2>&1',
    'CHANGED_FILES=$(git diff --name-only HEAD origin/main 2>/dev/null || echo "")',
    '(git update-index --refresh -q 2>/dev/null || true) && (git reset --hard origin/main 2>&1 || (git checkout -f origin/main 2>&1 && git reset --hard origin/main 2>&1))',
    '(find /var/www/magaees -maxdepth 2 -not -path "*/.*" -not -path "/var/www/magaees/node_modules*" -not -path "/var/www/magaees/uploads*" -not -path "/var/www/magaees/baileys_auth*" -exec chown www-data:www-data {} + 2>/dev/null || true)',
    'if echo "$CHANGED_FILES" | grep -q "migrate_ui_preferences.php"; then php /var/www/magaees/api/migrate_ui_preferences.php 2>&1; fi',
    'if echo "$CHANGED_FILES" | grep -qE "(whatsapp-service\.mjs|server\.js)"; then (pm2 restart all 2>/dev/null || systemctl restart whatsapp-service 2>/dev/null || pkill -f "whatsapp-service" 2>/dev/null || true); fi',
];

$result = shell_exec(implode(' && ', $commands) . ' 2>&1');

if ($lockFp) {
    @flock($lockFp, LOCK_UN);
    @fclose($lockFp);
}

@file_put_contents($logFile, "[$timestamp] Result: $result\n", FILE_APPEND);

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'commit' => $commitMsg,
    'output' => $result,
    'timestamp' => $timestamp
]);
