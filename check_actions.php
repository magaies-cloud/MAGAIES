<?php
$token = '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';
$url = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=5';
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
if (isset($data['workflow_runs'])) {
    foreach ($data['workflow_runs'] as $run) {
        echo "Run #" . $run['run_number'] . "\n";
        echo "  Status: " . $run['status'] . "\n";
        echo "  Conclusion: " . ($run['conclusion'] ?? 'N/A') . "\n";
        echo "  Created: " . $run['created_at'] . "\n";
        echo "  Commit: " . ($run['head_commit']['message'] ?? 'N/A') . "\n";
        echo "---\n";
    }
} else {
    echo "No runs found or error:\n";
    echo substr($response, 0, 500);
}
