<?php
// router.php - Router script for PHP built-in web server (php -S)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the requested URI is an existing static file (and not a directory), serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    // If it's a php file, include it
    if (pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
        require __DIR__ . $uri;
        exit;
    }
    return false; // let php -S serve non-php static files (images, css, js)
}

// Handle API requests (e.g. /api_index.php/auth/login)
if (strpos($uri, '/api_index.php') === 0 || strpos($uri, '/api/') === 0) {
    $_SERVER['SCRIPT_NAME'] = '/api_index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/api_index.php';
    require __DIR__ . '/api_index.php';
    exit;
}

// Default fallback to index.php
if (file_exists(__DIR__ . '/index.php')) {
    require __DIR__ . '/index.php';
    exit;
}

return false;
