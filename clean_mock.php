<?php
$files = glob('*.php');
$count = 0;
foreach ($files as $f) {
    if (in_array($f, ['api_index.php', 'app.js', 'index.php'])) continue;
    $c = file_get_contents($f);
    
    // Regex to match "if (empty($var)) { $var = [... mock data ...]; }" or similar
    // We look for if(empty(...)) or if(count(...)==0) followed by a block {} containing Arabic text (mock data)
    $pattern = '/if\s*\(\s*(?:empty|!\$db_connected|!isset|count.*?==\s*0).*?\)\s*\{[^{}]*(?:[\x{0600}-\x{06FF}][^{}]*)+\}/su';
    
    // To handle nested arrays like $result = [ [..], [..] ], the above regex might stop at the first }.
    // A better approach is to match the specific mock data assignment blocks.
    // Let's use a more robust regex that matches the if block up to the closing brace that is at the same indentation level, 
    // or we can just match `if (empty($result)) { ... }` where it contains "النيل" or "محلات" or "المقاولات".
    
    $c_new = preg_replace_callback('/if\s*\(\s*(?:empty|!\$db_connected|!isset|count.*?==\s*0).*?\)\s*\{.*?\}/su', function($m) {
        if (preg_match('/(?:شركة النيل|النيل|معدل|محلات|المقاولات|سيراميك|السجانة|مواد بناء|حديد التسليح|توسعة|توريد|تحديث|دفعة|شيك|فواتير|استثمار)/ui', $m[0])) {
            return '// Mock data fallback removed for SaaS environment';
        }
        return $m[0];
    }, $c, -1, $replacements);
    
    if ($replacements > 0) {
        // Handle specific variable assignments outside of if(empty) that might be mock data
        $c_new = preg_replace('/\$is_mock_data\s*=\s*.*?;/s', '$is_mock_data = false;', $c_new);
        
        file_put_contents($f, $c_new);
        echo "Cleaned $replacements mock blocks from $f\n";
        $count++;
    }
}
echo "Total files cleaned: $count\n";
