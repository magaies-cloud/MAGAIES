<?php
require __DIR__ . '/api/db.php';

echo "بدء عملية تحسين قاعدة البيانات وإضافة الأعمدة الافتراضية...\n";

// Array of tables and the JSON fields we want to extract for fast searching
$optimizations = [
    'users' => [
        ['col' => 'search_name', 'path' => '$.fullName', 'type' => 'VARCHAR(255)']
    ],
    'suppliers' => [
        ['col' => 'search_name', 'path' => '$.name', 'type' => 'VARCHAR(255)'],
        ['col' => 'search_phone', 'path' => '$.phone', 'type' => 'VARCHAR(255)']
    ]
    // Add more tables (like customers, items) as needed...
];

foreach ($optimizations as $table => $columns) {
    echo "تحسين الجدول: $table\n";
    foreach ($columns as $col) {
        $colName = $col['col'];
        $path = $col['path'];
        $type = $col['type'];
        
        try {
            // Check if column exists
            $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$colName'")->fetch();
            if (!$check) {
                $sql = "ALTER TABLE `$table` ADD COLUMN `$colName` $type GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$path'))) VIRTUAL";
                $pdo->exec($sql);
                
                // Add index
                $pdo->exec("CREATE INDEX `idx_{$table}_{$colName}` ON `$table` (`$colName`)");
                
                echo "  تمت إضافة العمود الافتراضي $colName بنجاح مع الفهرسة.\n";
            } else {
                echo "  العمود $colName موجود مسبقاً.\n";
            }
        } catch (PDOException $e) {
            echo "  خطأ أثناء تحسين $table: " . $e->getMessage() . "\n";
        }
    }
}

echo "انتهت عملية التحسين بنجاح!\n";
