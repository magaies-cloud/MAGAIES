<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
echo "<h1>WhatsApp Debugger</h1>";

// 1. Check if Node is installed
echo "<h3>1. Node.js Version Check</h3>";
$nodeVer = shell_exec('node -v 2>&1');
$nodeJsVer = shell_exec('nodejs -v 2>&1');
$npmVer = shell_exec('npm -v 2>&1');
$path = shell_exec('echo $PATH 2>&1');
$whoami = shell_exec('whoami 2>&1');
echo "Node version (node): <pre>" . htmlspecialchars(trim($nodeVer)) . "</pre>";
echo "Node version (nodejs): <pre>" . htmlspecialchars(trim($nodeJsVer)) . "</pre>";
echo "NPM version: <pre>" . htmlspecialchars(trim($npmVer)) . "</pre>";
echo "Executing user: <pre>" . htmlspecialchars(trim($whoami)) . "</pre>";
echo "PATH variable: <pre>" . htmlspecialchars(trim($path)) . "</pre>";

// 2. Check if whatsapp-service.js exists
echo "<h3>2. File Existence Check</h3>";
$filePath = __DIR__ . '/whatsapp-service.js';
echo "whatsapp-service.js path: " . htmlspecialchars($filePath) . "<br>";
echo "Exists: " . (file_exists($filePath) ? "YES" : "NO") . "<br>";

// 3. Try to check if port 3001 is open
function getWhatsAppNodeCommand() {
    $nodeCheck = shell_exec('which node 2>&1');
    if (!empty($nodeCheck) && strpos($nodeCheck, 'not found') === false && strpos($nodeCheck, 'no node') === false) {
        return 'node';
    }
    
    $nodejsCheck = shell_exec('which nodejs 2>&1');
    if (!empty($nodejsCheck) && strpos($nodejsCheck, 'not found') === false && strpos($nodejsCheck, 'no nodejs') === false) {
        return 'nodejs';
    }
    
    if (file_exists('/usr/bin/node')) return '/usr/bin/node';
    if (file_exists('/usr/bin/nodejs')) return '/usr/bin/nodejs';
    if (file_exists('/usr/local/bin/node')) return '/usr/local/bin/node';
    
    return 'node';
}

echo "<h3>3. Port 3001 Check</h3>";
$connection = @fsockopen('localhost', 3001, $errno, $errstr, 1);
if (is_resource($connection)) {
    echo "Port 3001 is OPEN!<br>";
    fclose($connection);
} else {
    echo "Port 3001 is CLOSED. (Error: $errno - $errstr)<br>";
    
    // 4. Try to start the service
    echo "<h3>4. Trying to start service...</h3>";
    $cmd = getWhatsAppNodeCommand();
    if (stristr(PHP_OS, 'WIN')) {
        echo "Executing Windows command...<br>";
        pclose(popen("start /B node \"" . __DIR__ . "\\whatsapp-service.js\"", "r"));
    } else {
        echo "Executing Linux command: <code>$cmd \"" . __DIR__ . "/whatsapp-service.js\" > wa_debug.log 2>&1 &</code><br>";
        shell_exec("$cmd \"" . __DIR__ . "/whatsapp-service.js\" > wa_debug.log 2>&1 &");
    }
    
    echo "Waiting 3 seconds...<br>";
    sleep(3);
    
    // Check again
    $connection2 = @fsockopen('localhost', 3001, $errno, $errstr, 1);
    if (is_resource($connection2)) {
        echo "<b>Port 3001 is now OPEN! Success!</b><br>";
        fclose($connection2);
    } else {
        echo "<b>Port 3001 is STILL CLOSED. (Error: $errno - $errstr)</b><br>";
        $logPath = __DIR__ . '/wa_debug.log';
        if (file_exists($logPath)) {
            echo "<h4>wa_debug.log contents:</h4>";
            echo "<pre>" . htmlspecialchars(file_get_contents($logPath)) . "</pre>";
        } else {
            echo "wa_debug.log was not created.<br>";
        }
    }
}
