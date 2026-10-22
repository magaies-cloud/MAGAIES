<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$bridgeDir = __DIR__ . '/../uploads/qr_bridge';
if (!is_dir($bridgeDir)) {
    @mkdir($bridgeDir, 0777, true);
}

$channel = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['channel'] ?? 'default_gate');
if ($channel === '') {
    $channel = 'default_gate';
}
$stateFile = $bridgeDir . '/bridge_' . $channel . '.json';

function readBridgeState($file) {
    if (!file_exists($file)) {
        return [
            'channel' => basename($file, '.json'),
            'mobileConnectedAt' => 0,
            'mobileDevice' => '',
            'scans' => []
        ];
    }
    $raw = @file_get_contents($file);
    $data = $raw ? @json_decode($raw, true) : null;
    if (!is_array($data)) {
        return [
            'channel' => basename($file, '.json'),
            'mobileConnectedAt' => 0,
            'mobileDevice' => '',
            'scans' => []
        ];
    }
    if (!isset($data['scans']) || !is_array($data['scans'])) {
        $data['scans'] = [];
    }
    return $data;
}

function writeBridgeState($file, $data) {
    // Keep only last 60 scans from the last 30 minutes
    $cutoff = (time() - 1800) * 1000;
    if (isset($data['scans']) && is_array($data['scans'])) {
        $data['scans'] = array_values(array_filter($data['scans'], function ($s) use ($cutoff) {
            return isset($s['timestamp']) && $s['timestamp'] >= $cutoff;
        }));
        if (count($data['scans']) > 60) {
            $data['scans'] = array_slice($data['scans'], -60);
        }
    }
    @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function detectLocalLanIp() {
    $ips = [];
    if (stripos(PHP_OS, 'WIN') !== false) {
        $out = @shell_exec('ipconfig');
        if ($out && preg_match_all('/IPv4[^:]*:\s*([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/i', $out, $matches)) {
            foreach ($matches[1] as $ip) {
                if ($ip !== '127.0.0.1' && !preg_match('/^169\.254\./', $ip)) {
                    $ips[] = $ip;
                }
            }
        }
    }
    if (empty($ips)) {
        $hostIp = @gethostbyname(gethostname());
        if ($hostIp && $hostIp !== '127.0.0.1' && filter_var($hostIp, FILTER_VALIDATE_IP)) {
            $ips[] = $hostIp;
        }
    }
    // Prefer 192.168.x.x or 10.x.x.x
    foreach ($ips as $ip) {
        if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip)) {
            return $ip;
        }
    }
    return $ips[0] ?? '127.0.0.1';
}

$action = $_REQUEST['action'] ?? 'poll';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if ($action === 'info') {
    $state = readBridgeState($stateFile);
    $nowMs = round(microtime(true) * 1000);
    $isConnected = ($nowMs - ($state['mobileConnectedAt'] ?? 0)) < 15000;
    echo json_encode([
        'ok' => true,
        'channel' => $channel,
        'lanIp' => detectLocalLanIp(),
        'mobileConnected' => $isConnected,
        'mobileConnectedAt' => $state['mobileConnectedAt'] ?? 0,
        'mobileDevice' => $state['mobileDevice'] ?? '',
        'scansCount' => count($state['scans'])
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'connect') {
    $state = readBridgeState($stateFile);
    $state['mobileConnectedAt'] = round(microtime(true) * 1000);
    $state['mobileDevice'] = substr((string)($input['device'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Mobile Scanner'), 0, 120);
    writeBridgeState($stateFile, $state);
    echo json_encode([
        'ok' => true,
        'channel' => $channel,
        'connectedAt' => $state['mobileConnectedAt']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'push_scan') {
    $rawCode = trim((string)($input['code'] ?? $_REQUEST['code'] ?? ''));
    if ($rawCode === '') {
        echo json_encode(['ok' => false, 'error' => 'empty_code']);
        exit;
    }

    $state = readBridgeState($stateFile);
    $nowMs = round(microtime(true) * 1000);
    $state['mobileConnectedAt'] = $nowMs;

    $scanId = 'scn_' . $nowMs . '_' . mt_rand(100, 999);
    $scanEntry = [
        'scanId' => $scanId,
        'rawCode' => $rawCode,
        'timestamp' => $nowMs,
        'processedByLaptop' => false,
        'resultStatus' => 'pending',
        'resultMessage' => '',
        'invoiceNo' => '',
        'customerName' => ''
    ];

    $state['scans'][] = $scanEntry;
    writeBridgeState($stateFile, $state);

    echo json_encode([
        'ok' => true,
        'scanId' => $scanId,
        'timestamp' => $nowMs
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'poll') {
    $state = readBridgeState($stateFile);
    $nowMs = round(microtime(true) * 1000);
    $isConnected = ($nowMs - ($state['mobileConnectedAt'] ?? 0)) < 15000;

    $unprocessed = [];
    foreach ($state['scans'] as $s) {
        if (empty($s['processedByLaptop'])) {
            $unprocessed[] = $s;
        }
    }

    echo json_encode([
        'ok' => true,
        'channel' => $channel,
        'mobileConnected' => $isConnected,
        'mobileConnectedAt' => $state['mobileConnectedAt'] ?? 0,
        'unprocessedScans' => $unprocessed,
        'recentScans' => array_slice($state['scans'], -20)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'ack_scan') {
    $scanId = (string)($input['scanId'] ?? $_REQUEST['scanId'] ?? '');
    $resultStatus = (string)($input['resultStatus'] ?? 'delivered');
    $resultMessage = (string)($input['resultMessage'] ?? '');
    $invoiceNo = (string)($input['invoiceNo'] ?? '');
    $customerName = (string)($input['customerName'] ?? '');

    $state = readBridgeState($stateFile);
    $found = false;
    foreach ($state['scans'] as &$s) {
        if (($s['scanId'] ?? '') === $scanId) {
            $s['processedByLaptop'] = true;
            $s['resultStatus'] = $resultStatus;
            $s['resultMessage'] = $resultMessage;
            $s['invoiceNo'] = $invoiceNo;
            $s['customerName'] = $customerName;
            $s['processedAt'] = round(microtime(true) * 1000);
            $found = true;
            break;
        }
    }
    unset($s);
    writeBridgeState($stateFile, $state);

    echo json_encode(['ok' => true, 'found' => $found], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'status_scan') {
    $scanId = (string)($_REQUEST['scanId'] ?? '');
    $state = readBridgeState($stateFile);
    $state['mobileConnectedAt'] = round(microtime(true) * 1000);
    writeBridgeState($stateFile, $state);

    foreach ($state['scans'] as $s) {
        if (($s['scanId'] ?? '') === $scanId) {
            echo json_encode(['ok' => true, 'scan' => $s], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'unknown_action'], JSON_UNESCAPED_UNICODE);
