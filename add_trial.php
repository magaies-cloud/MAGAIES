<?php
$f = 'c:/xampp/htdocs/magaees/saas_data/plans.json';
$d = json_decode(file_get_contents($f), true);
if (!$d) $d = [];

// Check if a trial plan already exists
$hasTrial = false;
foreach ($d as $plan) {
    if (!empty($plan['isTrial']) && $plan['isTrial'] === true) {
        $hasTrial = true;
        break;
    }
}

if (!$hasTrial) {
    $d[] = [
        'id' => time(),
        'name' => 'باقة تجريبية (مجانية)',
        'price' => '0',
        'isTrial' => true,
        'storage' => '5',
        'duration' => '30',
        'maxUsers' => '2'
    ];
    file_put_contents($f, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo "Trial plan added successfully.";
} else {
    echo "Trial plan already exists.";
}
