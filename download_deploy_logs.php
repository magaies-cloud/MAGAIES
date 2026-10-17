<?php
$token = getenv('GITHUB_TOKEN') ?: '';
$runId = 29733820517;

// Try downloading the full logs as ZIP
$logUrl = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/$runId/logs";
$ch = curl_init($logUrl);
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

echo "HTTP: $httpCode, Size: " . strlen($logData) . "\n";

if ($httpCode == 200 && strlen($logData) > 0) {
    file_put_contents('deploy_logs.zip', $logData);
    echo "Saved to deploy_logs.zip\n";
    
    // Try to extract
    $zip = new ZipArchive();
    if ($zip->open('deploy_logs.zip') === true) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            echo "\nFile: $name\n";
            $content = $zip->getFromIndex($i);
            // Show last 1500 chars
            echo substr($content, -1500) . "\n";
        }
        $zip->close();
    }
} else {
    echo "Could not download logs\n";
    echo substr($logData, 0, 500) . "\n";
}

// Also check the run details directly
$runUrl = "https://api.github.com/repos/magaies-cloud/MAGAIES/actions/runs/$runId";
$ch2 = curl_init($runUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "Authorization: token $token",
    "User-Agent: PHP-Script",
    "Accept: application/vnd.github.v3+json"
]);
$runData = curl_exec($ch2);
curl_close($ch2);
$run = json_decode($runData, true);
echo "\n--- Run Details ---\n";
echo "Status: " . ($run['status'] ?? 'N/A') . "\n";
echo "Conclusion: " . ($run['conclusion'] ?? 'N/A') . "\n";
echo "Run started: " . ($run['run_started_at'] ?? 'N/A') . "\n";
echo "Updated: " . ($run['updated_at'] ?? 'N/A') . "\n";
