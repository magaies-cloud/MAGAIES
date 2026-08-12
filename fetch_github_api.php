<?php
$token = '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';
$url = 'https://api.github.com/repos/magaies-cloud/magaies/contents/balances-report.php';

$options = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token\r\n" .
                    "User-Agent: PHP-Script\r\n" .
                    "Accept: application/vnd.github.v3+json\r\n"
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "Failed to fetch from GitHub API.";
} else {
    $data = json_decode($response, true);
    if (isset($data['content']) && $data['encoding'] === 'base64') {
        $content = base64_decode($data['content']);
        file_put_contents('balances-report.php', $content);
        echo "Successfully fetched and saved balances-report.php from GitHub using API.";
    } else {
        echo "Failed to decode GitHub API response.";
    }
}
?>
