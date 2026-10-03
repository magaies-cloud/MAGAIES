<?php
/**
 * ⚡ ULTRA-FAST NETWORK HEARTBEAT & CONNECTIVITY PROBE
 * ═════════════════════════════════════════════════════════════════
 * Returns an instant 1-byte response with no database load,
 * enabling immediate real-time network presence detection on mobile devices
 * even over high-latency 2G/3G/EDGE connections.
 */
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('X-Magaiees-Heartbeat: 1');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

echo '1';
exit;
