<?php
// =============================================================
// MAGAIEES ERP - UI Preferences & Field Sizes Database Migration
// سكربت تهيئة وحفظ تفضيلات الشاشات ومقاسات الحقول في قاعدة البيانات
// =============================================================

$isCli = (php_sapi_name() === 'cli');
$secret = $_GET['secret'] ?? '';
$validSecret = 'magaees_deploy_2026_secure';

if (!$isCli && $secret !== $validSecret) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized migration trigger']));
}

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/db.php';
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection failed");
    }

    // 1. Ensure ui_preferences table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
        tenant_id VARCHAR(50) NOT NULL,
        pref_key VARCHAR(100) NOT NULL,
        pref_value LONGTEXT,
        PRIMARY KEY (tenant_id, pref_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Exact calibrated field properties
    $calibratedProps = [
        'date' => [
            'fontSize' => '1.25rem',
            'color' => '#ffffff',
            'fontWeight' => '900',
            'thousandsSep' => false,
            'decimals' => 0
        ],
        'ref-no' => [
            'fontSize' => '1.55rem',
            'color' => '#38bdf8',
            'fontWeight' => '900',
            'thousandsSep' => false,
            'decimals' => 0
        ],
        'amount' => [
            'fontSize' => '2.30rem',
            'color' => '#fef08a',
            'fontWeight' => '900',
            'thousandsSep' => true,
            'decimals' => 0
        ],
        'sender-acc' => [
            'fontSize' => '1.06rem',
            'color' => '#bae6fd',
            'fontWeight' => '900',
            'thousandsSep' => false,
            'decimals' => 0
        ],
        'receiver-acc' => [
            'fontSize' => '1.06rem',
            'color' => '#a7f3d0',
            'fontWeight' => '900',
            'thousandsSep' => false,
            'decimals' => 0
        ],
        'receiver' => [
            'fontSize' => '0.94rem',
            'color' => '#ffffff',
            'fontWeight' => '800',
            'thousandsSep' => false,
            'decimals' => 0
        ]
    ];

    $json = json_encode($calibratedProps, JSON_UNESCAPED_UNICODE);

    // 3. Seed into tenant 1 and default
    $tenants = ['1', 'default'];
    $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES (?, 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
    foreach ($tenants as $tId) {
        $stmt->execute([$tId, $json, $json]);
    }

    $gitCommit = trim((string)shell_exec('git rev-parse HEAD 2>&1'));
    $gitStatus = trim((string)shell_exec('git status --porcelain 2>&1'));

    $out = [
        'success' => true,
        'message' => 'ui_preferences table verified and seeded successfully',
        'live_server_git_commit' => $gitCommit,
        'live_server_git_status' => empty($gitStatus) ? 'clean' : $gitStatus,
        'calibrated_props' => $calibratedProps
    ];
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]) . "\n";
}
