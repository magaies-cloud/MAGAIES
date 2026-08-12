<?php
$token = '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';

// Check billing/usage
$urls = [
    'Billing' => 'https://api.github.com/users/magaies-cloud/settings/billing/actions',
    'Rate Limit' => 'https://api.github.com/rate_limit',
];

foreach ($urls as $name => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "User-Agent: PHP-Script",
        "Accept: application/vnd.github.v3+json"
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "=== $name (HTTP $code) ===\n";
    echo json_encode(json_decode($res), JSON_PRETTY_PRINT) . "\n\n";
}

// Check last successful run's details
echo "=== Last Successful Run (#94) ===\n";
$url2 = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=50&status=success';
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
if (isset($data2['workflow_runs'][0])) {
    $r = $data2['workflow_runs'][0];
    echo "Run #" . $r['run_number'] . " | " . $r['created_at'] . "\n";
    echo "Commit: " . $r['head_commit']['message'] . "\n";
}

// Check first failing run after #94
echo "\n=== First Failing Run After Success ===\n";
$url3 = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=50';
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
$prevWasSuccess = false;
if (isset($data3['workflow_runs'])) {
    // Reverse to find first failure after last success
    $runs = array_reverse($data3['workflow_runs']);
    foreach ($runs as $run) {
        if ($run['conclusion'] === 'success') {
            $prevWasSuccess = true;
            continue;
        }
        if ($prevWasSuccess && $run['conclusion'] === 'failure') {
            echo "First failure: Run #" . $run['run_number'] . " | " . $run['created_at'] . "\n";
            echo "Commit: " . ($run['head_commit']['message'] ?? 'N/A') . "\n";
            break;
        }
    }
}
