<?php
$token = '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';

// Get the latest run ID
$url = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=1';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
$runId = $data['workflow_runs'][0]['id'] ?? null;

if (!$runId) {
    echo "No run found\n";
    exit;
}

echo "Run ID: $runId\n";

// Get jobs for this run
$url2 = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/$runId/jobs";
$ch = curl_init($url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$response2 = curl_exec($ch);
curl_close($ch);
$jobs = json_decode($response2, true);

if (isset($jobs['jobs'])) {
    foreach ($jobs['jobs'] as $job) {
        echo "Job: " . $job['name'] . "\n";
        echo "  Status: " . $job['status'] . "\n";
        echo "  Conclusion: " . ($job['conclusion'] ?? 'N/A') . "\n";
        if (isset($job['steps'])) {
            foreach ($job['steps'] as $step) {
                $icon = $step['conclusion'] === 'success' ? '✓' : '✗';
                echo "  $icon Step: " . $step['name'] . " => " . ($step['conclusion'] ?? 'N/A') . "\n";
            }
        }
    }
}

// Get logs
$url3 = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/$runId/logs";
$ch = curl_init($url3);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$logData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nLog download HTTP code: $httpCode\n";
if ($httpCode == 200) {
    file_put_contents('action_logs.zip', $logData);
    echo "Logs saved to action_logs.zip\n";
}
