<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL) {
        $info = "[SHUTDOWN ERROR] " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];
        echo "<pre style='background:#f00;color:#fff;padding:20px;font-size:16px;'>" . htmlspecialchars($info) . "</pre>";
    }
});

require_once __DIR__ . '/breakeven_analysis.php';
