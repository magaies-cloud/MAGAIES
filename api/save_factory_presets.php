<?php
header('Content-Type: application/json; charset=utf-8');

$configDir = __DIR__ . '/factory_config';

$type = isset($_GET['type']) ? $_GET['type'] : 'ledger';
if ($type === 'balances') {
    $configFile = $configDir . '/balances_factory_presets.json';
} else {
    $configFile = $configDir . '/ledger_factory_presets.json';
}

// تأكد من وجود المجلد
if (!is_dir($configDir)) {
    if (!mkdir($configDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'فشل في إنشاء مجلد حفظ إعدادات المصنع.']);
        exit;
    }
}

// قراءة الملف الحالي إن وجد
$existingPresets = [];
if (file_exists($configFile)) {
    $existingRaw = file_get_contents($configFile);
    $parsed = json_decode($existingRaw, true);
    if (is_array($parsed)) {
        $existingPresets = $parsed;
    }
}

// قراءة البيانات القادمة من الواجهة
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['preset']) || !isset($input['index'])) {
    echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة (لم يتم إرسال التصميم أو رقم المؤشر).']);
    exit;
}

$index = (int) $input['index'];
$newPreset = $input['preset'];

if ($index < 0 || $index > 3) {
    echo json_encode(['success' => false, 'message' => 'يمكنك قفل الأزرار الأربعة الأولى فقط كـ "ضبط مصنع".']);
    exit;
}

// القفل الأمني الفردي: إذا كان هذا الزر بالذات محفوظاً ومقنعاً مسبقاً، ارفض الحفظ
if (isset($existingPresets[$index]) && isset($existingPresets[$index]['isFactoryDefault']) && $existingPresets[$index]['isFactoryDefault'] === true) {
    echo json_encode([
        'success' => false, 
        'message' => 'تم حفظ وقفل الزر رقم (' . ($index + 1) . ') كـ "ضبط مصنع" مسبقاً! لا يمكن تعديله. لتغييره يجب حذف الملف يدوياً من السيرفر.'
    ]);
    exit;
}

// تجهيز المصفوفة (تأكد من وجود عناصر كافية إذا كان المؤشر متأخراً)
for ($i = 0; $i <= $index; $i++) {
    if (!isset($existingPresets[$i])) {
        $existingPresets[$i] = null;
    }
}

// وضع التصميم في مكانه وإغلاقه
$newPreset['isFactoryDefault'] = true;
$existingPresets[$index] = $newPreset;

// حفظ الملف بشكل نهائي
if (file_put_contents($configFile, json_encode($existingPresets, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true, 
        'message' => 'تم حفظ التصميم الحالي في الزر رقم (' . ($index + 1) . ') وقُفل بنجاح كـ "ضبط مصنع".'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء كتابة الملف على السيرفر.']);
}
?>
