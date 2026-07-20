<?php
$token = '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';

// Get latest run
$url = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=1';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);
$runId = $data['workflow_runs'][0]['id'];
echo "Run ID: $runId\n\n";

// Get jobs
$url2 = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/$runId/attempts/1/jobs";
$ch2 = curl_init($url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$res2 = curl_exec($ch2);
curl_close($ch2);
$jobs = json_decode($res2, true);

if (isset($jobs['jobs']) && count($jobs['jobs']) > 0) {
    foreach ($jobs['jobs'] as $job) {
        echo "Job: " . $job['name'] . " | " . $job['conclusion'] . "\n";
        if (isset($job['steps'])) {
            foreach ($job['steps'] as $step) {
                $icon = ($step['conclusion'] === 'success') ? '✓' : '✗';
                echo "  $icon " . $step['name'] . " => " . ($step['conclusion'] ?? 'N/A') . "\n";
            }
        }
        echo "\n";

        // Get job log
        $jobId = $job['id'];
        $logUrl = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/jobs/$jobId/logs";
        $ch3 = curl_init($logUrl);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch3, CURLOPT_HTTPHEADER, [
            "Authorization: token $token",
            "User-Agent: PHP-Script",
            "Accept: application/vnd.github.v3+json"
        ]);
        $logContent = curl_exec($ch3);
        $httpCode = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
        curl_close($ch3);

        echo "Log HTTP: $httpCode\n";
        if ($httpCode == 200 && strlen($logContent) > 0) {
            // Show last 2000 chars of log
            echo "--- Log (last 2000 chars) ---\n";
            echo substr($logContent, -2000);
        }
    }
} else {
    echo "No jobs found. Raw response:\n";
    echo substr($res2, 0, 1000);
}
