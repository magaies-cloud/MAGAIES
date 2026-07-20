<?php
$token = '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';

// Trigger the workflow manually using workflow_dispatch
$url = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/workflows/deploy.yml/dispatches';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['ref' => 'main']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json",
    "Content-Type: application/json"
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Dispatch HTTP: $code\n";
echo "Response: " . substr($res, 0, 500) . "\n";

// Also check - what was the LAST successful run?
echo "\n--- Last successful deployment ---\n";
$url2 = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=20&status=success';
$ch2 = curl_init($url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$res2 = curl_exec($ch2);
curl_close($ch2);
$data2 = json_decode($res2, true);

if (isset($data2['workflow_runs']) && count($data2['workflow_runs']) > 0) {
    $lastSuccess = $data2['workflow_runs'][0];
    echo "Run #" . $lastSuccess['run_number'] . "\n";
    echo "Created: " . $lastSuccess['created_at'] . "\n";
    echo "Commit: " . ($lastSuccess['head_commit']['message'] ?? 'N/A') . "\n";
} else {
    echo "No successful runs found!\n";
    echo "Total runs checked: " . ($data2['total_count'] ?? 0) . "\n";
}

// Check ALL recent runs to see when it started failing
echo "\n--- Recent 10 runs ---\n";
$url3 = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=10';
$ch3 = curl_init($url3);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$res3 = curl_exec($ch3);
curl_close($ch3);
$data3 = json_decode($res3, true);

if (isset($data3['workflow_runs'])) {
    foreach ($data3['workflow_runs'] as $run) {
        $icon = $run['conclusion'] === 'success' ? '✓' : '✗';
        echo "$icon #{$run['run_number']} | {$run['conclusion']} | {$run['created_at']} | " . substr($run['head_commit']['message'] ?? '', 0, 60) . "\n";
    }
}
