<?php
// =============================================================
// MAGAIEES ERP - Manage OCR Templates & Knowledge Base API
// حفظ واسترجاع النماذج والقواعد التدريبية للذكاء الاصطناعي
// =============================================================
header('Content-Type: application/json; charset=utf-8');

$storageFile = __DIR__ . '/financial_templates.json';
$guidelinesFile = __DIR__ . '/financial_ocr_guidelines.txt';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? 'get_all';

// Default templates for Sudanese & regional banks
$defaultTemplates = [
    [
        'id' => 'bankak_bok',
        'name' => 'بنكك (Bankak - بنك الخرطوم)',
        'bank' => 'بنك الخرطوم',
        'type' => 'bank_transfer',
        'rules' => 'البطاقة الخضراء أو البيضاء، رقم العملية يتكون من 8 إلى 10 أرقام (Transaction ID)، المبلغ متبوعاً بـ SDG أو USD، يظهر اسم المحول واسم المستفيد بوضوح مع علامة الصح الخضراء.'
    ],
    [
        'id' => 'fawry_fib',
        'name' => 'فوري (Fawry - بنك فيصل الإسلامي)',
        'bank' => 'بنك فيصل الإسلامي',
        'type' => 'bank_transfer',
        'rules' => 'إشعار باللون الأزرق، كود المعاملة المرجعي، المبلغ بالجنيه، اسم المستفيد، التاريخ ورقم الحساب المحول منه.'
    ],
    [
        'id' => 'ocash_onb',
        'name' => 'أوكاش (Ocash - بنك أمدرمان الوطني)',
        'bank' => 'بنك أمدرمان الوطني',
        'type' => 'bank_transfer',
        'rules' => 'إشعار تحويل بنك أمدرمان، الرقم المرجعي، المبلغ بالأرقام، اسم المحول والمستلم وتاريخ العملية.'
    ],
    [
        'id' => 'cheque_standard',
        'name' => 'الشيك المصرفي الورقي العام',
        'bank' => 'جميع البنوك',
        'type' => 'cheque',
        'rules' => 'رقم الشيك مطبوع في شريط MICR أسفل الشيك بين علامتي ⑈ أو في الزاوية العلوية، المبلغ بالأرقام وبالحروف تفقيط، اسم المستفيد بعد (ادفعوا لأمر)، تاريخ التحرير والاستحقاق.'
    ]
];

// Helper to get templates
function getTemplates($file, $defaults) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaults, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $defaults;
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return is_array($data) ? $data : $defaults;
}

if ($action === 'get_all') {
    $templates = getTemplates($storageFile, $defaultTemplates);
    $guidelines = file_exists($guidelinesFile) ? file_get_contents($guidelinesFile) : '';
    echo json_encode([
        'success' => true,
        'templates' => $templates,
        'guidelines' => $guidelines
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'save_template') {
    $template = $input['template'] ?? null;
    if (!$template || empty($template['name'])) {
        echo json_encode(['success' => false, 'error' => 'بيانات النموذج غير مكتملة']);
        exit;
    }
    $templates = getTemplates($storageFile, $defaultTemplates);
    
    // Check if updating existing or adding new
    $id = $template['id'] ?? 'tmpl_' . time();
    $template['id'] = $id;
    
    $found = false;
    foreach ($templates as $k => $t) {
        if ($t['id'] === $id) {
            $templates[$k] = $template;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $templates[] = $template;
    }
    
    file_put_contents($storageFile, json_encode($templates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'templates' => $templates, 'message' => 'تم حفظ النموذج بنجاح']);
    exit;
}

if ($action === 'delete_template') {
    $id = $input['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'معرف النموذج مفقود']);
        exit;
    }
    $templates = getTemplates($storageFile, $defaultTemplates);
    $newTemplates = array_values(array_filter($templates, fn($t) => $t['id'] !== $id));
    file_put_contents($storageFile, json_encode($newTemplates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'templates' => $newTemplates, 'message' => 'تم حذف النموذج']);
    exit;
}

if ($action === 'save_guidelines') {
    $guidelines = trim($input['guidelines'] ?? '');
    file_put_contents($guidelinesFile, $guidelines);
    echo json_encode(['success' => true, 'message' => 'تم حفظ القواعد التدريبية بنجاح']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'إجراء غير معروف']);
