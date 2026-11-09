<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$storageFile = __DIR__ . '/../uploads/market_broker_offers.json';
if (!is_dir(dirname($storageFile))) {
    @mkdir(dirname($storageFile), 0777, true);
}

$today = date('Y-m-d');
$nowTs = time();
$defaultItems = [
    [
        'id' => 'mk_101',
        'deal_code' => '101',
        'type' => 'offer',
        'privacy' => 'public',
        'sender_name' => 'مهدي للحديد والأسمنت',
        'phone' => '249123005622',
        'text' => 'سيخ 3 لينية: 415,000 ج.س | 4 لينية: 420,000 ج.س | 5 لينية: 418,000 ج.س | أسمنت عطبرة: 170,000 ج.س',
        'time' => date('H:i', $nowTs - 7200),
        'ts' => $nowTs - 7200,
        'date' => $today
    ],
    [
        'id' => 'mk_102',
        'deal_code' => '102',
        'type' => 'order',
        'privacy' => 'masked',
        'sender_name' => 'تاجر معتمد',
        'real_name' => 'شركة النيل للمقاولات',
        'phone' => '249123005622',
        'text' => 'مطلوب شراء عاجل: 35 طن سيخ 4 لينية (12مم) + 50 طن أسمنت عطبرة تسليم الخرطوم نقداً',
        'time' => date('H:i', $nowTs - 14400),
        'ts' => $nowTs - 14400,
        'date' => $today
    ],
    [
        'id' => 'mk_103',
        'deal_code' => '103',
        'type' => 'offer',
        'privacy' => 'masked',
        'sender_name' => 'مورد معتمد',
        'real_name' => 'مغلق البركة التجاري',
        'phone' => '249123005622',
        'text' => 'سيخ 4 لينية: 419,000 ج.س | 6 لينية: 417,000 ج.س | سلك رباط مجلفن: 48,000 ج.س',
        'time' => date('H:i', $nowTs - 90000),
        'ts' => $nowTs - 90000,
        'date' => $today
    ],
    [
        'id' => 'mk_104',
        'deal_code' => '104',
        'type' => 'order',
        'privacy' => 'public',
        'sender_name' => 'مؤسسة البنيان الحديثة',
        'phone' => '249123005622',
        'text' => 'مطلوب شراء: 20 طن كمر حديد ومواسير تسليح + 15 طن سيخ 3 لينية الدفع بنكك فوري',
        'time' => date('H:i', $nowTs - 93600),
        'ts' => $nowTs - 93600,
        'date' => $today
    ]
];

$items = [];
if (file_exists($storageFile)) {
    $raw = @json_decode(@file_get_contents($storageFile), true);
    if (is_array($raw)) {
        $items = array_values(array_filter($raw, function ($it) use ($nowTs) {
            $itemTs = isset($it['ts']) ? (int)$it['ts'] : strtotime(($it['date'] ?? date('Y-m-d')) . ' ' . ($it['time'] ?? '12:00'));
            return ($nowTs - $itemTs) < (7 * 86400);
        }));
    }
}
if (empty($items)) {
    $items = $defaultItems;
    @file_put_contents($storageFile, json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$action = $_REQUEST['action'] ?? 'list';
$prefsFile = __DIR__ . '/../uploads/market_broker_prefs.json';
$defaultPrefs = [
    'fontStep' => 2,
    'offersSec' => 160,
    'ordersSec' => 200,
    'maxAgeHours' => 24,
    'hideNames' => false,
    'myItemsFilter' => true,
    'dockMode' => 'top',
    'offersLocked' => false,
    'ordersLocked' => false,
    'hiddenIds' => []
];
$filePrefs = file_exists($prefsFile) ? (@json_decode(@file_get_contents($prefsFile), true) ?: []) : [];
$savedPrefs = $defaultPrefs;
try {
    require_once __DIR__ . '/db.php';
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmtP = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'market_broker_prefs' LIMIT 1");
        $stmtP->execute();
        $dbJson = $stmtP->fetchColumn();
        if ($dbJson) {
            $dbArr = @json_decode($dbJson, true);
            if (is_array($dbArr)) $savedPrefs = array_merge($savedPrefs, $dbArr);
        }
    }
} catch (Throwable $e) {}
$savedPrefs = array_merge($savedPrefs, $filePrefs);
$blacklistFile = __DIR__ . '/../uploads/market_broker_blacklist.json';
$blacklist = file_exists($blacklistFile) ? (@json_decode(@file_get_contents($blacklistFile), true) ?: ['senders' => [], 'items' => []]) : ['senders' => [], 'items' => []];

if ($action === 'save_prefs') {
    $inPrefs = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    if (is_array($inPrefs)) {
        $savedPrefs = array_merge($savedPrefs, $inPrefs);
        @file_put_contents($prefsFile, json_encode($savedPrefs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        try {
            if (isset($pdo) && $pdo instanceof PDO) {
                $jsonVal = json_encode($savedPrefs, JSON_UNESCAPED_UNICODE);
                $up = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('market_broker_prefs', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $up->execute([$jsonVal]);
            }
        } catch (Throwable $e) {}
    }
    echo json_encode(['success' => true, 'prefs' => $savedPrefs], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'report') {
    $inRep = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $itemId = trim((string)($inRep['id'] ?? ''));
    $senderKey = trim((string)($inRep['phone'] ?? $inRep['sender_name'] ?? 'unknown'));
    $reporter = trim((string)($inRep['reporter'] ?? 'تاجر معتمد'));
    if ($itemId !== '') {
        $blacklist['items'][$itemId] = (int)($blacklist['items'][$itemId] ?? 0) + 1;
    }
    if ($senderKey !== '') {
        $blacklist['senders'][$senderKey] = (int)($blacklist['senders'][$senderKey] ?? 0) + 1;
    }
    foreach ($items as &$itRef) {
        if (($itRef['id'] ?? '') === $itemId || ($itRef['deal_code'] ?? '') === $itemId) {
            $sKey = $itRef['phone'] ?? $itRef['sender_name'] ?? $senderKey;
            $pts = max((int)($blacklist['items'][$itemId] ?? 1), (int)($blacklist['senders'][$sKey] ?? 1));
            $blacklist['senders'][$sKey] = $pts;
            $itRef['black_points'] = $pts;
        }
    }
    unset($itRef);
    @file_put_contents($blacklistFile, json_encode($blacklist, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    @file_put_contents($storageFile, json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $curPts = (int)($blacklist['senders'][$senderKey] ?? ($blacklist['items'][$itemId] ?? 1));
    echo json_encode(['success' => true, 'black_points' => $curPts, 'banned' => ($curPts >= 3)], JSON_UNESCAPED_UNICODE);
    exit;
}

$items = array_values(array_filter($items, function ($it) use ($blacklist) {
    $id = $it['id'] ?? '';
    $sKey = $it['phone'] ?? ($it['sender_name'] ?? '');
    $pts = max((int)($it['black_points'] ?? 0), (int)($blacklist['items'][$id] ?? 0), (int)($blacklist['senders'][$sKey] ?? 0));
    return $pts < 3;
}));

if ($action === 'add' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $rawText = trim((string)($input['text'] ?? $input['message'] ?? ''));
    $senderName = trim((string)($input['sender_name'] ?? $input['pushName'] ?? ''));
    $phone = preg_replace('/[^0-9]/', '', (string)($input['phone'] ?? ''));
    $type = trim((string)($input['type'] ?? ''));
    $privacy = trim((string)($input['privacy'] ?? ''));

    if ($rawText !== '') {
        $cleanCmd = mb_strtolower($rawText, 'UTF-8');

        // 1. استعلام عن عروض الأسعار (ع / أسعار / 1)
        if (in_array($cleanCmd, ['ع', 'اسعار', 'أسعار', 'عروض', 'الاسعار', 'الأسعار', '1'], true)) {
            $offers = array_values(array_filter($items, fn($x) => ($x['type'] ?? 'offer') === 'offer'));
            $lines = ["📊 *بورصة عروض الأسعار اليومية بالسوق (" . count($offers) . " عروض):*"];
            foreach ($offers as $idx => $o) {
                $isMasked = ($o['privacy'] ?? 'public') === 'masked';
                $code = $o['deal_code'] ?? ($idx + 101);
                $contactInfo = $isMasked ? "🔒 كود الدلالة: *#{$code}* (أرسل *تواصل {$code}* للشراء)" : "📞 " . ($o['phone'] ?: 'مباشر');
                $lines[] = ($idx + 1) . ". 🟢 *" . $o['sender_name'] . "* [{$contactInfo}]:\n   " . $o['text'] . " ⏱️ " . $o['time'];
            }
            $lines[] = "\n💡 *طريقة النشر بالبورصة:*\n• لنشر عرض باسمك: *عرض اسمك: أسعارك*\n• لنشر عرض سري بدون اسمك ورقمك: *عرض سري: أسعارك*\n• لعرض طلبات الشراء أرسل الرمز: *ط*";
            echo json_encode(['success' => true, 'mode' => 'query_offers', 'reply' => implode("\n\n", $lines), 'items' => $offers], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. استعلام عن طلبات الشراء (ط / طلبات / 2)
        if (in_array($cleanCmd, ['ط', 'طلبات', 'طلبيات', 'الطلبات', 'مشتريات', '2'], true)) {
            $orders = array_values(array_filter($items, fn($x) => ($x['type'] ?? '') === 'order'));
            $lines = ["🛒 *بورصة طلبات الشراء المفتوحة اليوم (" . count($orders) . " طلبات):*"];
            foreach ($orders as $idx => $o) {
                $isMasked = ($o['privacy'] ?? 'public') === 'masked';
                $code = $o['deal_code'] ?? ($idx + 101);
                $contactInfo = $isMasked ? "🔒 كود الدلالة: *#{$code}* (أرسل *تواصل {$code}* للتوريد)" : "📞 " . ($o['phone'] ?: 'مباشر');
                $lines[] = ($idx + 1) . ". 🟠 *" . $o['sender_name'] . "* [{$contactInfo}]:\n   " . $o['text'] . " ⏱️ " . $o['time'];
            }
            $lines[] = "\n💡 *طريقة نشر طلب شراء:*\n• باسمك ورقمك: *طلب اسمك: تفاصيل طلبيتك*\n• بدون إظهار اسمك ورقمك: *طلب سري: تفاصيل طلبيتك*\n• لعرض الأسعار أرسل الرمز: *ع*";
            echo json_encode(['success' => true, 'mode' => 'query_orders', 'reply' => implode("\n\n", $lines), 'items' => $orders], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 3. طلب تواصل على صفقة سرية (تواصل 102 / شراء 102 / صفقة 102)
        if (preg_match('/^(?:تواصل|شراء|توريد|صفقة|صفقه|كود)\s*#?(\d{2,5})$/u', $cleanCmd, $mDeal)) {
            $targetCode = $mDeal[1];
            $found = null;
            foreach ($items as $it) {
                if ((string)($it['deal_code'] ?? '') === (string)$targetCode) {
                    $found = $it;
                    break;
                }
            }
            if ($found) {
                $reply = "🤝 *تم استلام طلبك للصفقة رقم (#{$targetCode}):*\n"
                    . "📋 التفاصيل: {$found['text']}\n"
                    . "✅ تم إشعار صاحب الصفقة برقمك فوراً عبر دلالة المنصة لترتيب البيع والتسليم.";
                echo json_encode([
                    'success' => true,
                    'mode' => 'deal_match',
                    'reply' => $reply,
                    'owner_phone' => $found['phone'] ?? '',
                    'owner_notify' => "🔔 *إشعار صفقة من بورصة السجانة (#{$targetCode}):*\nلديك عميل جاد يرغب في تنفيذ عرضك/طلبك:\n({$found['text']})\n📞 رقم العميل للتواصل المباشر: +" . ($phone ?: 'عبر المنصة')
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        // 4. نشر عرض أو طلب جديد (مع دعم الوضع السري بدون اسم/رقم أو الوضع العام)
        if ($type === '') {
            $type = preg_match('/^(طلب|مطلوب|شراء|محتاج)/u', $rawText) ? 'order' : 'offer';
        }
        $dealCode = (string)(101 + count($items));
        $wantsMasked = ($privacy === 'masked') || preg_match('/^(?:عرض|طلب|مطلوب)?\s*(?:سري|خاص|مخفي|بدون\s*اسم|بدون\s*رقم|دلالة|سمسار)\b/u', $rawText);

        $explicitName = '';
        if (preg_match('/^(?:عرض|طلب|مطلوب)?\s*(?:باسم|التاجر|المورد|شركة|مغلق|مؤسسة)\s+([^:：\-]{2,25})\s*[:：\-]\s*(.+)$/u', $rawText, $m)) {
            $explicitName = trim($m[1]);
            $rawText = trim($m[2]);
        } else {
            $rawText = trim(preg_replace('/^(?:عرض\s*اسعار|عرض\s*أسعار|عروض|عرض|طلب\s*شراء|طلبات|طلب|مطلوب\s*شراء|مطلوب|دلالة|سمسار|بورصة|للبيع)\s*(?:سري|خاص|مخفي|بدون\s*اسم|بدون\s*رقم)?\s*[:：\-]?\s*/u', '', $rawText));
        }
        if ($rawText === '') {
            $rawText = trim((string)($input['text'] ?? $input['message'] ?? ''));
        }

        $finalPrivacy = $wantsMasked ? 'masked' : ($privacy ?: 'public');
        $displaySender = $finalPrivacy === 'masked'
            ? ($type === 'order' ? 'مشتري معتمد' : 'مورد معتمد')
            : ($explicitName ?: ($senderName ?: 'تاجر بالسوق'));

        $newItem = [
            'id' => 'mk_' . time() . '_' . rand(100, 999),
            'deal_code' => $dealCode,
            'type' => $type,
            'privacy' => $finalPrivacy,
            'sender_name' => $displaySender,
            'real_name' => $explicitName ?: ($senderName ?: 'تاجر بالسوق'),
            'phone' => $phone ?: '249123005622',
            'text' => $rawText,
            'time' => date('H:i'),
            'ts' => time(),
            'date' => $today
        ];
        array_unshift($items, $newItem);
        $items = array_slice($items, 0, 60);
        @file_put_contents($storageFile, json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $privacyNote = $finalPrivacy === 'masked'
            ? "🔒 *حماية الخصوصية مفعلة:* تم إخفاء اسمك ورقمك ونشر الصفقة بالكود السري (*#{$dealCode}*). عندما يطلبها مشتري بـ (*تواصل {$dealCode}*) سنرسل لك رقمه فوراً!"
            : "🌐 تم النشر بالاسم والرقم المباشر (*{$displaySender}*) ليظهر للجميع في الشريط والواتساب.";

        $replyMsg = ($type === 'order' ? "✅ *تم نشر طلب الشراء في بورصة السوق!*\n" : "✅ *تم نشر عرض الأسعار في بورصة السوق!*\n")
            . $privacyNote . "\n📊 للأسعار أرسل: *ع* | 🛒 للطلبات أرسل: *ط*";

        echo json_encode(['success' => true, 'mode' => 'added', 'item' => $newItem, 'reply' => $replyMsg, 'items' => $items], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(['success' => true, 'items' => $items, 'prefs' => $savedPrefs], JSON_UNESCAPED_UNICODE);
