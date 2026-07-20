<?php
$token = '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';

// Get list of all runs, including their conclusion and error details
$url = 'https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs?per_page=1&status=failure';
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
if (isset($data['workflow_runs'][0])) {
    $run = $data['workflow_runs'][0];
    echo "Run #" . $run['run_number'] . " (ID: " . $run['id'] . ")\n";
    echo "Status: " . $run['status'] . "\n";
    echo "Conclusion: " . ($run['conclusion'] ?? 'N/A') . "\n";
    echo "Created: " . $run['created_at'] . "\n";
    echo "Updated: " . $run['updated_at'] . "\n";
    echo "Trigger: " . $run['event'] . "\n";

    // Now try to re-run this workflow
    echo "\n--- Attempting to re-run workflow ---\n";
    $rerunUrl = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/" . $run['id'] . "/rerun";
    $ch2 = curl_init($rerunUrl);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "User-Agent: PHP-Script",
        "Accept: application/vnd.github.v3+json"
    ]);
    $res2 = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    echo "Rerun HTTP: $code2\n";
    echo "Response: " . substr($res2, 0, 500) . "\n";
}
