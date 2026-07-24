<?php
try {
    $dbName = 'elsajana2_local';
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbName", "root", "");
    } catch(Exception $e) {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=magaees", "root", "");
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS global_settings (
        setting_key varchar(100) NOT NULL PRIMARY KEY,
        setting_value json DEFAULT NULL,
        updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $colConfigs = [
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
    $colOrder = [0, 1, 2, 3, 4, 5, 6, 7, 8];

    $layoutData = json_encode(['colConfigs' => $colConfigs, 'colOrder' => $colOrder], JSON_UNESCAPED_UNICODE);

    $key = 'layout_balances_report_layout_default1';
    $stmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    $stmt->execute([$key, $layoutData]);

    echo "✅ Default 1 report layout permanently initialized in MySQL DB under key '$key'!\n";
} catch (Exception $e) {
    echo "❌ DB Error: " . $e->getMessage() . "\n";
}
