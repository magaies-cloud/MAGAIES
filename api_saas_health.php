<?php
// api_saas_health.php - Server Health Monitoring API
header('Content-Type: application/json');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = '';
if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
}

require_once 'api/db.php';
$saasSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : (getenv('SAAS_ADMIN_TOKEN') ?: 'super');

if ($token !== $saasSecret) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$isWindows = stristr(PHP_OS, 'WIN');

// === CPU Load ===
$cpuLoad = 0;
if ($isWindows) {
    $output = @shell_exec('wmic cpu get loadpercentage /value 2>&1');
    if ($output && preg_match('/LoadPercentage=(\d+)/', $output, $m)) {
        $cpuLoad = intval($m[1]);
    }
} else {
    $load = @sys_getloadavg();
    if ($load) {
        $cores = intval(@shell_exec('nproc 2>/dev/null') ?: 1);
        $cpuLoad = round(($load[0] / max($cores, 1)) * 100, 1);
    }
}

// === RAM (System-level) ===
$ramTotal = 0;
$ramUsed = 0;
$ramPercent = 0;
if ($isWindows) {
    $output = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value 2>&1');
    if ($output) {
        preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $totalMatch);
        preg_match('/FreePhysicalMemory=(\d+)/', $output, $freeMatch);
        if (!empty($totalMatch[1]) && !empty($freeMatch[1])) {
            $ramTotal = intval($totalMatch[1]) * 1024; // Convert KB to Bytes
            $ramFree = intval($freeMatch[1]) * 1024;
            $ramUsed = $ramTotal - $ramFree;
            $ramPercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : 0;
        }
    }
} else {
    $meminfo = @file_get_contents('/proc/meminfo');
    if ($meminfo) {
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availMatch);
        if (!empty($totalMatch[1]) && !empty($availMatch[1])) {
            $ramTotal = intval($totalMatch[1]) * 1024;
            $ramFree = intval($availMatch[1]) * 1024;
            $ramUsed = $ramTotal - $ramFree;
            $ramPercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : 0;
        }
    }
}

// === Disk Space ===
$diskFree = disk_free_space(".");
$diskTotal = disk_total_space(".");
$diskUsed = $diskTotal - $diskFree;
$diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

// === Database Size ===
$dbSize = 0;
try {
    $stmt = $pdo->query("SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = DATABASE()");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbSize = $row['size'] ?? 0;
} catch (Exception $e) {}

// === MySQL Version ===
$mysqlVersion = 'N/A';
try {
    $mysqlVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
} catch (Exception $e) {}

// === Active Connections ===
$activeConnections = 0;
try {
    $stmt = $pdo->query("SHOW STATUS WHERE `variable_name` = 'Threads_connected'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $activeConnections = $row['Value'] ?? 0;
} catch (Exception $e) {}

// === Uptime ===
$uptime = 'N/A';
if ($isWindows) {
    $output = @shell_exec('net stats workstation 2>&1');
    if ($output && preg_match('/since (.+)/i', $output, $m)) {
        $uptime = trim($m[1]);
    }
} else {
    $output = @shell_exec('uptime -p 2>/dev/null');
    $uptime = $output ? trim($output) : 'N/A';
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

echo json_encode([
    'success' => true,
    'php_version' => phpversion(),
    'mysql_version' => $mysqlVersion,
    'os' => PHP_OS,
    'cpu_load_1m' => $cpuLoad,
    'memory_usage' => formatBytes($ramUsed),
    'memory_total' => formatBytes($ramTotal),
    'memory_percent' => $ramPercent,
    'memory_peak' => formatBytes($ramUsed), // Same as used for system-level
    'disk_free' => formatBytes($diskFree),
    'disk_total' => formatBytes($diskTotal),
    'disk_used_percent' => $diskPercent,
    'db_size' => formatBytes($dbSize),
    'active_connections' => $activeConnections,
    'uptime' => $uptime,
    'time' => date('Y-m-d H:i:s')
]);
