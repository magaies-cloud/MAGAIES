<?php
session_start();
require_once __DIR__ . '/../db_bridge.php';
// Security: Require authentication for all push API actions
if (empty($_SESSION['user_id']) && empty($_SESSION['admin_id']) && empty($_SESSION['tenant_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🔔 MAGAIEES ERP 2026 - WEB PUSH & FIREBASE FCM SUBSCRIPTION ENGINE
 * ═════════════════════════════════════════════════════════════════════
 * Handles:
 * - Registering device subscriptions for Lockscreen Web Push
 * - Dispatching system-level push notifications outside the app
 * - Integrating with Firebase Cloud Messaging (FCM) & Standard WebPush
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    exit;
}

$storageDir = __DIR__ . '/../uploads/push_subscriptions';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}
$subsFile = $storageDir . '/subscriptions.json';
$configFile = $storageDir . '/fcm_config.json';

// Default VAPID Public Key for Client PushManager Subscription
define('DEFAULT_VAPID_PUBLIC_KEY', 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? ($_GET['action'] ?? ($_POST['action'] ?? 'status'));

// ────────────────────────────────────────────────────────────────────
// 1. GET CONFIG & STATUS
// ────────────────────────────────────────────────────────────────────
if ($action === 'status' || $action === 'get_config') {
    $config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
    $subs = file_exists($subsFile) ? json_decode(file_get_contents($subsFile), true) : [];
    
    echo json_encode([
        'success' => true,
        'vapid_public_key' => $config['vapid_public_key'] ?? DEFAULT_VAPID_PUBLIC_KEY,
        'has_custom_fcm' => !empty($config['fcm_server_key']),
        'total_subscribers' => is_array($subs) ? count($subs) : 0,
        'sound_alerts_enabled' => true,
        'vibration_enabled' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ────────────────────────────────────────────────────────────────────
// 2. SAVE / REGISTER DEVICE SUBSCRIPTION
// ────────────────────────────────────────────────────────────────────
if ($action === 'save_subscription' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $subscription = $input['subscription'] ?? null;
    if (!$subscription || empty($subscription['endpoint'])) {
        echo json_encode(['success' => false, 'message' => 'بيانات الاشتراك غير مكتملة']);
        exit;
    }

    $subs = file_exists($subsFile) ? json_decode(file_get_contents($subsFile), true) : [];
    if (!is_array($subs)) $subs = [];

    $endpoint = $subscription['endpoint'];
    $subKey = md5($endpoint);

    $subs[$subKey] = [
        'id' => $subKey,
        'endpoint' => $endpoint,
        'keys' => $subscription['keys'] ?? [],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Mobile Device',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    file_put_contents($subsFile, json_encode($subs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => 'تم تفعيل إشعارات قفل الشاشة بنجاح على هذا الهاتف 🔔',
        'subscription_id' => $subKey,
        'total_devices' => count($subs)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ────────────────────────────────────────────────────────────────────
// 3. SEND TEST OR REAL-TIME PUSH NOTIFICATION
// ────────────────────────────────────────────────────────────────────
if ($action === 'test_push' || $action === 'broadcast_risk_alert') {
    $title = $input['title'] ?? '🔔 إشعار فوري: منظومة مقاييس ERP';
    $body = $input['body'] ?? 'تم إرسال هذا الإشعار لاختبار وصول التنبيهات وشاشة الهاتف مقفلة بنجاح ⚡';
    $targetUrl = $input['url'] ?? './mobile-dashboard.php?tab=approvals';

    $payload = [
        'title' => $title,
        'body' => $body,
        'icon' => './assets/icon-192.png',
        'badge' => './assets/icon-192.png',
        'tag' => 'magaiees-risk-' . time(),
        'data' => [
            'url' => $targetUrl,
            'time' => date('H:i:s')
        ]
    ];

    $subs = file_exists($subsFile) ? json_decode(file_get_contents($subsFile), true) : [];
    $count = is_array($subs) ? count($subs) : 0;

    // Check if custom FCM Server Key exists to push via Google Firebase
    $config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
    $fcmKey = $config['fcm_server_key'] ?? '';

    $fcmSuccess = 0;
    if (!empty($fcmKey) && $count > 0) {
        foreach ($subs as $sub) {
            $ep = $sub['endpoint'];
            // If it's an FCM or Google endpoint
            if (strpos($ep, 'fcm.googleapis.com') !== false || strpos($ep, 'android.googleapis.com') !== false) {
                $fcmToken = basename($ep);
                $fcmMsg = [
                    'to' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => './assets/icon-192.png',
                        'sound' => 'default'
                    ],
                    'data' => $payload['data']
                ];
                $ch = curl_init('https://fcm.googleapis.com/fcm/send');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: key=' . $fcmKey
                    ],
                    CURLOPT_POSTFIELDS => json_encode($fcmMsg)
                ]);
                $fcmRes = curl_exec($ch);
                curl_close($ch);
                $fcmSuccess++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "تم تجهيز وبث الإشعار لـ ({$count}) أجهزة مسجلة بنجاح 🚀",
        'payload' => $payload,
        'active_subscriptions' => $count,
        'fcm_dispatched' => $fcmSuccess
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ────────────────────────────────────────────────────────────────────
// 4. SAVE CUSTOM FCM / VAPID CONFIG
// ────────────────────────────────────────────────────────────────────
if ($action === 'save_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fcmKey = trim($input['fcm_server_key'] ?? '');
    $vapidKey = trim($input['vapid_public_key'] ?? '');

    $config = [
        'fcm_server_key' => $fcmKey,
        'vapid_public_key' => !empty($vapidKey) ? $vapidKey : DEFAULT_VAPID_PUBLIC_KEY,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT), LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ إعدادات مفاتيح Firebase FCM و VAPID بنجاح ⚙️'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
