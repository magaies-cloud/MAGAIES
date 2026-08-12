<?php
$files = glob('*.php');
$count = 0;
foreach ($files as $f) {
    if (in_array($f, ['api_index.php', 'app.js', 'index.php'])) continue;
    $c = file_get_contents($f);
    // Find blocks like if (empty($xxx)) { ... }
    if (preg_match_all('/if\s*\(\s*(?:empty|!\$db_connected|!isset|count.*?==\s*0).*?\)\s*\{.*?[\x{0600}-\x{06FF}].*?\}/su', $c, $matches)) {
        foreach ($matches[0] as $match) {
            // Check if it's setting an array of fake data
            if (strpos($match, '[') !== false && strpos($match, '=>') !== false) {
                echo "File $f has mock data fallback.\n";
                $count++;
                break; // Just print once per file
            }
        }
    }
}
echo "Total files with mock data: $count\n";
