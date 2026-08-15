<?php
// api_saas_health.php - Server Health Monitoring API
// === CORS & Headers ===
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// === Authentication ===
$token = '';
// Try multiple header sources for maximum compatibility
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
} else {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
}
if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
}
// Fallback: query parameter
if (empty($token) && isset($_GET['token'])) {
    $token = $_GET['token'];
}

require_once 'api/db.php';
// Match the same fallback as api_index.php and api_saas_auth.php
$saasSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : (getenv('SAAS_ADMIN_TOKEN') ?: (isset($_ENV['SAAS_ADMIN_TOKEN']) ? $_ENV['SAAS_ADMIN_TOKEN'] : 'SUPER_ADMIN_SECRET_2026'));

if ($token !== $saasSecret) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$isWindows = stristr(PHP_OS, 'WIN');

// === CPU Load ===
$cpuLoad = 0;
if ($isWindows) {
    // Try wmic first, fallback to PowerShell if wmic is deprecated/missing
    $output = @shell_exec('wmic cpu get loadpercentage /value 2>&1');
    if ($output && preg_match('/LoadPercentage=(\d+)/', $output, $m)) {
        $cpuLoad = intval($m[1]);
    } else {
        // PowerShell fallback for Windows 11+ where wmic is removed
        $psOutput = @shell_exec('powershell -NoProfile -Command "(Get-CimInstance Win32_Processor).LoadPercentage" 2>&1');
        if ($psOutput !== null && is_numeric(trim($psOutput))) {
            $cpuLoad = intval(trim($psOutput));
        }
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
    $parsed = false;
    if ($output) {
        preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $totalMatch);
        preg_match('/FreePhysicalMemory=(\d+)/', $output, $freeMatch);
        if (!empty($totalMatch[1]) && !empty($freeMatch[1])) {
            $ramTotal = intval($totalMatch[1]) * 1024;
            $ramFree = intval($freeMatch[1]) * 1024;
            $ramUsed = $ramTotal - $ramFree;
            $ramPercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 1) : 0;
            $parsed = true;
        }
    }
    // PowerShell fallback
    if (!$parsed) {
        $psOutput = @shell_exec('powershell -NoProfile -Command "$os = Get-CimInstance Win32_OperatingSystem; Write-Output ($os.TotalVisibleMemorySize.ToString() + \",\" + $os.FreePhysicalMemory.ToString())" 2>&1');
        if ($psOutput && strpos($psOutput, ',') !== false) {
            list($totalKB, $freeKB) = explode(',', trim($psOutput));
            $ramTotal = intval($totalKB) * 1024;
            $ramFree = intval($freeKB) * 1024;
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
$diskFree = @disk_free_space(".") ?: 0;
$diskTotal = @disk_total_space(".") ?: 0;
$diskUsed = $diskTotal - $diskFree;
$diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

// === Database Size & Table Count ===
$dbSize = 0;
$dbTablesCount = 0;
try {
    $stmt = $pdo->query("SELECT COALESCE(SUM(data_length + index_length), 0) AS size, COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema = DATABASE()");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbSize = floatval($row['size'] ?? 0);
    $dbTablesCount = intval($row['tables_count'] ?? 0);
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

// === Uptime (Use MySQL uptime - reliable cross-platform, cross-locale) ===
$uptime = 'N/A';
try {
    $stmt = $pdo->query("SHOW STATUS WHERE `variable_name` = 'Uptime'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['Value'])) {
        $seconds = intval($row['Value']);
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $parts = [];
        if ($days > 0) $parts[] = $days . ' يوم';
        if ($hours > 0) $parts[] = $hours . ' ساعة';
        if ($minutes > 0) $parts[] = $minutes . ' دقيقة';
        $uptime = !empty($parts) ? implode(' و ', $parts) : 'أقل من دقيقة';
    }
} catch (Exception $e) {}

// === Network Usage ===
$rxBytes = 0;
$txBytes = 0;
if ($isWindows) {
    $output = @shell_exec('netstat -e 2>&1');
    if ($output && preg_match('/Bytes\s+(\d+)\s+(\d+)/i', $output, $m)) {
        $rxBytes = intval($m[1]);
        $txBytes = intval($m[2]);
    }
} else {
    $dev = @file_get_contents('/proc/net/dev');
    if ($dev) {
        preg_match_all('/([a-z0-9]+):\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/i', $dev, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ($match[1] !== 'lo') {
                $rxBytes += intval($match[2]);
                $txBytes += intval($match[3]);
            }
        }
    }
}

// === Processes ===
$processes = 0;
if ($isWindows) {
    $output = @shell_exec('tasklist /NH | find /C /V "" 2>&1');
    $processes = intval(trim($output));
} else {
    $output = @shell_exec('ps aux | wc -l 2>/dev/null');
    $processes = max(0, intval(trim($output)) - 1);
}

// === Helper: Format Bytes (safe for 32-bit) ===
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max(floatval($bytes), 0);
    if ($bytes == 0) return '0 B';
    $pow = floor(log($bytes) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow); // Use pow() instead of bit shift for 32-bit safety
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// === Calculate Overall Status ===
$overallStatus = 'healthy'; // healthy, warning, critical
$statusMessage = 'الخادم يعمل بشكل طبيعي';
if ($cpuLoad > 90 || $ramPercent > 90 || $diskPercent > 95) {
    $overallStatus = 'critical';
    $alerts = [];
    if ($cpuLoad > 90) $alerts[] = 'المعالج ' . $cpuLoad . '%';
    if ($ramPercent > 90) $alerts[] = 'الذاكرة ' . $ramPercent . '%';
    if ($diskPercent > 95) $alerts[] = 'التخزين ' . $diskPercent . '%';
    $statusMessage = 'حمل حرج: ' . implode(' | ', $alerts);
} elseif ($cpuLoad > 70 || $ramPercent > 75 || $diskPercent > 85) {
    $overallStatus = 'warning';
    $alerts = [];
    if ($cpuLoad > 70) $alerts[] = 'المعالج ' . $cpuLoad . '%';
    if ($ramPercent > 75) $alerts[] = 'الذاكرة ' . $ramPercent . '%';
    if ($diskPercent > 85) $alerts[] = 'التخزين ' . $diskPercent . '%';
    $statusMessage = 'حمل عالٍ: ' . implode(' | ', $alerts);
}

// === Active Users (Last 5 mins) ===
$activeUsers = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE last_active >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $activeUsers = intval($stmt->fetchColumn());
} catch (Exception $e) {}

// === Billing Info ===
$billingInfo = [
    'provider' => 'DigitalOcean',
    'cost' => '45.00',
    'renewal_date' => date('Y-m-d', strtotime('+1 month')),
    'plan' => 'Premium AMD 4GB'
];
try {
    $stmt = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'saas_billing'");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dbBilling = json_decode($row['setting_value'], true);
        if (is_array($dbBilling)) {
            $billingInfo = array_merge($billingInfo, $dbBilling);
        }
    }
} catch (Exception $e) {}

echo json_encode([
    'success' => true,
    'overall_status' => $overallStatus,
    'status_message' => $statusMessage,
    'php_version' => phpversion(),
    'mysql_version' => $mysqlVersion,
    'os' => PHP_OS,
    'cpu_load_1m' => $cpuLoad,
    'memory_usage' => formatBytes($ramUsed),
    'memory_total' => formatBytes($ramTotal),
    'memory_percent' => $ramPercent,
    'memory_peak' => formatBytes($ramUsed),
    'disk_free' => formatBytes($diskFree),
    'disk_total' => formatBytes($diskTotal),
    'disk_used_percent' => $diskPercent,
    'db_size' => formatBytes($dbSize),
    'db_tables_count' => $dbTablesCount,
    'active_connections' => $activeConnections,
    'active_users' => $activeUsers,
    'uptime' => $uptime,
    'network_rx' => formatBytes($rxBytes),
    'network_tx' => formatBytes($txBytes),
    'processes' => $processes,
    'billing' => $billingInfo,
    'time' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
