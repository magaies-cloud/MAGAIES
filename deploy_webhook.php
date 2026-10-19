<?php
// ============================================================
// Webhook للنشر التلقائي - يتم استدعاؤه من GitHub عند كل push
// يعمل بشكل مستقل عن GitHub Actions (لا يحتاج دقائق)
// ============================================================

// Secret token for security - must match GitHub webhook secret
$WEBHOOK_SECRET = 'magaees_deploy_2026_secure';

// Verify the request is from GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');

if (!empty($WEBHOOK_SECRET)) {
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $WEBHOOK_SECRET);
    if (!hash_equals($expected, $signature)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid signature']));
    }
}

// Only deploy on push to main
$data = json_decode($payload, true);
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/main') {
    http_response_code(200);
    die(json_encode(['status' => 'skipped', 'reason' => 'Not main branch']));
}

// Log the deployment
$logFile = '/var/www/magaees/deploy_webhook.log';
$timestamp = date('Y-m-d H:i:s');
$commitMsg = $data['head_commit']['message'] ?? 'unknown';

file_put_contents($logFile, "[$timestamp] Deploying: $commitMsg\n", FILE_APPEND);

// Execute deployment
$output = [];
$commands = [
    'rm -f /var/www/magaees/.git/index.lock 2>&1',
    'cd /var/www/magaees',
    'sed -i "/insteadOf/d" /var/www/magaees/.git/config 2>&1 || true',
    'git remote set-url origin https://github.com/magaies-cloud/MAGAIES.git 2>&1',
    'git update-index --no-skip-worktree whatsapp-service.mjs 2>&1',
    'chmod +w whatsapp-service.mjs 2>&1',
    'git fetch origin main 2>&1',
    'git clean -fd 2>&1',
    'git reset --hard origin/main 2>&1',
    '(chown -R www-data:www-data /var/www/magaees 2>/dev/null || true)',
    '(chmod -R 755 /var/www/magaees 2>/dev/null || true)',
    'php /var/www/magaees/api/migrate_ui_preferences.php 2>&1',
    '(pm2 restart all 2>/dev/null || systemctl restart whatsapp-service 2>/dev/null || pkill -f "whatsapp-service" 2>/dev/null || true)',
];

$result = shell_exec(implode(' && ', $commands) . ' 2>&1');

file_put_contents($logFile, "[$timestamp] Result: $result\n", FILE_APPEND);

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'commit' => $commitMsg,
    'output' => $result,
    'timestamp' => $timestamp
]);
