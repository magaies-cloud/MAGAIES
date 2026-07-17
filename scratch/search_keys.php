<?php
function searchDir($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'node_modules' || $file === '.git') continue;
        $path = "$dir/$file";
        if (is_dir($path)) {
            searchDir($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php' || pathinfo($path, PATHINFO_EXTENSION) === 'js') {
            $content = file_get_contents($path);
            if (stripos($content, 'GEMINI_API_KEY') !== false || stripos($content, 'OPENAI_API_KEY') !== false) {
                echo "$path contains GEMINI/OPENAI key reference\n";
            }
        }
    }
}
searchDir('c:/ELSAJANA/magaies');
?>
