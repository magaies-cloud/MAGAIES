<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=elsajana2_local", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $originalConfigs = [
        ['name' => 'اسم الحساب', 'alias' => 'اسم الحساب', 'id' => 'name', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'right', 'textAlign' => 'right'],
        ['name' => 'رقم الهاتف', 'alias' => 'رقم الهاتف', 'id' => 'phone', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'الرقم الضريبي', 'alias' => 'الرقم الضريبي', 'id' => 'taxNo', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'العملة', 'alias' => 'العملة', 'id' => 'currency', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'عليه عملة', 'alias' => 'عليه عملة', 'id' => 'debitF', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'له عملة', 'alias' => 'له عملة', 'id' => 'creditF', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'عليه', 'alias' => 'عليه', 'id' => 'debit', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'له', 'alias' => 'له', 'id' => 'credit', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'center', 'textAlign' => 'center'],
        ['name' => 'ملحوظة', 'alias' => 'ملحوظة', 'id' => 'note', 'bold' => false, 'fontWeight' => 'normal', 'fs' => 1.0, 'size' => '1rem', 'decimals' => 2, 'dec' => 2, 'showDecimals' => true, 'thousands' => true, 'thousandsSep' => ',', 'decSep' => '.', 'roundMode' => 'round', 'color' => '', 'bg' => '', 'visible' => true, 'align' => 'right', 'textAlign' => 'right']
    ];
    $originalOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8];

    $data = json_encode(['colConfigs' => $originalConfigs, 'colOrder' => $originalOrder], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    $stmt->execute(['layout_balances_report_layout_default1', $data]);
    $stmt->execute(['balances_report_master_default1', $data]);

    echo "✅ Original report design restored in MySQL DB successfully!\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
