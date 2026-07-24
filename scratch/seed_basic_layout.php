<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=elsajana2_local", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Default 2 or Default 1 layout from DB to clone
    $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN ('layout_balances_report_layout_default2', 'balances_report_master_default2', 'layout_balances_report_layout_default1') ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute();
    $val = $stmt->fetchColumn();

    if ($val) {
        $data = $val;
    } else {
        $defaultConfigs = [
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
        $data = json_encode(['colConfigs' => $defaultConfigs, 'colOrder' => [0, 1, 2, 3, 4, 5, 6, 7, 8]], JSON_UNESCAPED_UNICODE);
    }

    $insStmt = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    $insStmt->execute(['layout_balances_report_layout_basic', $data]);
    $insStmt->execute(['balances_report_master_basic', $data]);

    echo "🔒 Master Basic layout successfully cloned and saved in MySQL DB!\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
