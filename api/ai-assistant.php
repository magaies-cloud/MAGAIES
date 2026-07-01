<?php
header('Content-Type: application/json; charset=utf-8');

// 1. Get JSON Payload
$inputData = json_decode(file_get_contents('php://input'), true);
$userMessage = $inputData['message'] ?? '';
$systemContext = $inputData['context'] ?? '';
$imageBase64 = $inputData['image'] ?? null;

if (empty($userMessage)) {
    echo json_encode(['error' => 'الرسالة فارغة']);
    exit;
}

// 2. Setup API Key and Endpoint
$apiKey = "AQ.Ab8RN6IMdWH31t8-IbFYJZgytQxgFYW1GnTMee5UwEZ2Tlpqzw"; // Use exactly what the user provided
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

// 3. Construct System Prompt
$systemPrompt = "أنت مساعد محاسبي ومحلل مالي خبير ضمن نظام ERP محلي للشركة. وظيفتك هي الإجابة عن أسئلة المستخدم بدقة واحترافية.
إذا سألك المستخدم عن معلومات متعلقة بالشركة (مثل السيولة، المبيعات، المخزون، أو رصيد عميل معين)، فاستخدم المعلومات الحقيقية المرفقة لك في الأسفل للإجابة بدقة. لا تقم باختراع أرقام من عندك إطلاقاً.
إذا كان السؤال عاماً في المحاسبة فأجب بشرح علمي دقيق ومرتب.

[البيانات الحالية للشركة]:
" . $systemContext;

// 4. Prepare Payload for Gemini
$parts = [
    ["text" => $systemPrompt . "\n\nسؤال المستخدم: " . $userMessage]
];

// If an image is provided, append it to the parts
if ($imageBase64) {
    // Basic extraction if data:image/jpeg;base64, is included
    if (strpos($imageBase64, 'base64,') !== false) {
        $partsArr = explode('base64,', $imageBase64);
        $imageBase64 = $partsArr[1];
    }
    
    $parts[] = [
        "inline_data" => [
            "mime_type" => "image/jpeg",
            "data" => $imageBase64
        ]
    ];
}

$payload = [
    "contents" => [
        [
            "parts" => $parts
        ]
    ]
];

// 5. Send Request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local dev

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => 'cURL Error: ' . $curlError]);
    exit;
}

// 6. Parse and Return Response
$responseData = json_decode($response, true);

if ($httpCode >= 400) {
    // If Google API returns an error
    $errorMsg = $responseData['error']['message'] ?? 'حدث خطأ غير معروف في الاتصال بجوجل';
    echo json_encode(['error' => 'Google API Error: ' . $errorMsg]);
    exit;
}

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiReply = $responseData['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['reply' => $aiReply]);
} else {
    echo json_encode(['error' => 'استجابة غير متوقعة من الموديل', 'raw' => $responseData]);
}
?>
