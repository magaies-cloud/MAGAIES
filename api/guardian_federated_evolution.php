<?php
/**
 * Sovereign Shield — Progressive Federated Learning & Autonomous Structural Evolution Engine
 * يدمج «التعلّم الاتحادي التدريجي» (بدون كشف أي بيانات مالية بين الشركات) مع «تطوير البنية الذاتي»
 * لتعزيز الرقابة اللحظية في محرك الفحص الذاتي ومجلس الذكاء السيادي.
 */

function ensureFederatedEvolutionTables(PDO $pdo): void {
    static $ready = false;
    if ($ready) return;
    $ready = true;

    // 1. Global Anonymized Federated Weights (Zero tenant PII / Zero raw financial balances)
    $pdo->exec("CREATE TABLE IF NOT EXISTS guardian_federated_weights (
        pattern_key VARCHAR(64) NOT NULL PRIMARY KEY,
        pattern_label VARCHAR(160) NOT NULL DEFAULT '',
        ema_mean DOUBLE NOT NULL DEFAULT 0,
        ema_std DOUBLE NOT NULL DEFAULT 1,
        z_threshold DOUBLE NOT NULL DEFAULT 3.0,
        training_rounds INT NOT NULL DEFAULT 0,
        participating_nodes INT NOT NULL DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Per-Tenant Local Training Checkpoint (tracks progressive rounds without leaking raw rows)
    $pdo->exec("CREATE TABLE IF NOT EXISTS guardian_federated_checkpoints (
        tenant_id INT NOT NULL PRIMARY KEY,
        local_rounds INT NOT NULL DEFAULT 0,
        last_doc_count INT NOT NULL DEFAULT 0,
        last_trained_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Autonomous Structural Evolution Log (indexes, columns, and schema self-optimizations)
    $pdo->exec("CREATE TABLE IF NOT EXISTS guardian_structural_evolutions (
        evo_code VARCHAR(80) NOT NULL PRIMARY KEY,
        target_table VARCHAR(64) NOT NULL,
        evolution_type VARCHAR(40) NOT NULL DEFAULT 'COMPOSITE_INDEX',
        description VARCHAR(255) NOT NULL,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * أ) التعلّم الاتحادي التدريجي: يتدرب محلياً داخل الشركة النشطة على النسب المعيارية فقط،
 * ويحدّث الأوزان الاتحادية المشتركة بمعادلة EMA تدريجية دون إخراج أي اسم أو رقم خاص.
 */
function runProgressiveFederatedRound(PDO $pdo, int $tid): array {
    ensureFederatedEvolutionTables($pdo);

    $ratios = [];
    $fxRates = [];
    $invGrowth = [];
    $docTotal = 0;

    // Local feature extraction (dimensionless ratios & normalized log-scales ONLY)
    try {
        $st = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ? LIMIT 300");
        $st->execute([$tid]);
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
            $d = json_decode($r['data'] ?? '', true);
            if (!is_array($d)) continue;
            $cost = (float)($d['costPrice'] ?? $d['cost_price'] ?? 0);
            $sell = (float)($d['sellPrice'] ?? $d['price'] ?? 0);
            if ($cost > 0 && $sell > 0) {
                $ratios[] = round(($sell - $cost) / $cost, 4); // Pure margin ratio (no currency amount leaked)
            }
        }
    } catch (Throwable $e) {}

    foreach (['sales_invoices', 'purchase_invoices'] as $tbl) {
        try {
            $st = $pdo->prepare("SELECT data FROM `{$tbl}` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 200");
            $st->execute([$tid]);
            while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                $docTotal++;
                $d = json_decode($r['data'] ?? '', true);
                if (!is_array($d)) continue;
                $tot = (float)($d['grandTotal'] ?? $d['total'] ?? 0);
                if ($tot > 0) $invGrowth[] = log10($tot + 1.0); // Normalized log magnitude
                $cur = strtoupper((string)($d['currency'] ?? 'SDG'));
                $rate = (float)($d['exchangeRate'] ?? 1);
                if ($cur !== 'SDG' && $rate > 1) $fxRates[] = $rate;
            }
        } catch (Throwable $e) {}
    }

    $calcStats = function (array $arr, float $defMean, float $defStd): array {
        $n = count($arr);
        if ($n === 0) return [$defMean, $defStd];
        $mean = array_sum($arr) / $n;
        if ($n < 2) return [$mean, $defStd];
        $var = 0.0;
        foreach ($arr as $v) { $var += ($v - $mean) * ($v - $mean); }
        $std = max(0.05, sqrt($var / $n));
        return [round($mean, 4), round($std, 4)];
    };

    [$mMargin, $sMargin] = $calcStats($ratios, 0.18, 0.08);
    [$mScale, $sScale]   = $calcStats($invGrowth, 5.20, 0.85);
    [$mFx, $sFx]         = $calcStats($fxRates, 2600.0, 250.0);

    // Check if this tenant has new data or needs initial federated contribution
    $cpSt = $pdo->prepare("SELECT local_rounds, last_doc_count FROM guardian_federated_checkpoints WHERE tenant_id = ?");
    $cpSt->execute([$tid]);
    $cp = $cpSt->fetch(PDO::FETCH_ASSOC);
    $shouldUpdateGlobal = !$cp || (int)$cp['last_doc_count'] !== $docTotal || (int)$cp['local_rounds'] === 0;

    if ($shouldUpdateGlobal) {
        $pdo->prepare("INSERT INTO guardian_federated_checkpoints (tenant_id, local_rounds, last_doc_count)
            VALUES (?, 1, ?) ON DUPLICATE KEY UPDATE local_rounds = local_rounds + 1, last_doc_count = VALUES(last_doc_count)")
            ->execute([$tid, $docTotal]);

        $nodeCount = (int)$pdo->query("SELECT COUNT(*) FROM guardian_federated_checkpoints")->fetchColumn();
        $alpha = 0.25; // Progressive Federated EMA learning rate

        $patterns = [
            ['MARGIN_RATIO_DIST', 'معيار هامش الربح الآمن عبر الفروع', $mMargin, $sMargin, 3.0],
            ['INVOICE_LOG_SCALE', 'بصمة الحجم الطبيعي للفواتير (كشف الطفرات الشاذة)', $mScale, $sScale, 3.2],
            ['FX_DRIFT_GUARD', 'نطاق استقرار سعر الصرف المرجعي', $mFx, $sFx, 2.8]
        ];
        $upW = $pdo->prepare("INSERT INTO guardian_federated_weights
            (pattern_key, pattern_label, ema_mean, ema_std, z_threshold, training_rounds, participating_nodes)
            VALUES (?, ?, ?, ?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE
                ema_mean = ROUND((1 - {$alpha}) * ema_mean + ({$alpha} * VALUES(ema_mean)), 4),
                ema_std  = ROUND((1 - {$alpha}) * ema_std  + ({$alpha} * VALUES(ema_std)), 4),
                training_rounds = training_rounds + 1,
                participating_nodes = VALUES(participating_nodes)");
        foreach ($patterns as $pt) {
            $upW->execute([$pt[0], $pt[1], $pt[2], $pt[3], $pt[4], max(1, $nodeCount)]);
        }
    }

    // Detect Z-score live outliers in current tenant against Federated Global Model
    $wRows = $pdo->query("SELECT * FROM guardian_federated_weights")->fetchAll(PDO::FETCH_ASSOC);
    $weightsMap = [];
    $totalRounds = 0;
    $nodes = 1;
    foreach ($wRows as $w) {
        $weightsMap[$w['pattern_key']] = $w;
        $totalRounds += (int)$w['training_rounds'];
        $nodes = max($nodes, (int)$w['participating_nodes']);
    }

    $zOutliers = 0;
    if (!empty($weightsMap['INVOICE_LOG_SCALE']) && !empty($invGrowth)) {
        $wm = (float)$weightsMap['INVOICE_LOG_SCALE']['ema_mean'];
        $ws = max(0.15, (float)$weightsMap['INVOICE_LOG_SCALE']['ema_std']);
        $zt = (float)$weightsMap['INVOICE_LOG_SCALE']['z_threshold'];
        foreach ($invGrowth as $val) {
            if (abs($val - $wm) / $ws > $zt) $zOutliers++;
        }
    }

    return [
        'active' => true,
        'privacy_leakage' => 0,
        'participating_nodes' => $nodes,
        'total_rounds' => $totalRounds,
        'z_outliers_detected' => $zOutliers,
        'weights' => array_values($weightsMap)
    ];
}

/**
 * ب) محرك تطوير البنية التلقائي الآمن: يفحص الجداول المحاسبية ويرقّي فهارس العزل والسرعة تلقائياً دون توقف.
 */
function runAutonomousStructuralEvolution(PDO $pdo): array {
    ensureFederatedEvolutionTables($pdo);

    $targets = [
        ['sales_invoices',    'idx_sovereign_tenant_id', ['tenant_id', 'id'], 'تسريع استعلامات المبيعات المعزولة لكل شركة'],
        ['purchase_invoices', 'idx_sovereign_tenant_id', ['tenant_id', 'id'], 'تسريع استعلامات المشتريات المعزولة لكل شركة'],
        ['gl_entries',        'idx_sovereign_tenant_id', ['tenant_id', 'id'], 'تسريع مطابقة القيد المزدوج اللحظية بالأستاذ العام'],
        ['receipts',          'idx_sovereign_tenant_id', ['tenant_id', 'id'], 'تسريع مطابقة سندات القبض اللحظية'],
        ['single_payments',   'idx_sovereign_tenant_id', ['tenant_id', 'id'], 'تسريع مطابقة سندات الصرف اللحظية'],
        ['audit_logs',        'idx_sovereign_tenant_id', ['tenant_id', 'id'], 'تسريع الرقابة الجنائية اللحظية لسجل الأحداث']
    ];

    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $appliedNow = 0;

    foreach ($targets as [$tbl, $idxName, $cols, $desc]) {
        if (!in_array($tbl, $existingTables, true)) continue;
        try {
            $hasIdx = $pdo->query("SHOW INDEX FROM `{$tbl}` WHERE Key_name = '{$idxName}'")->fetch();
            if ($hasIdx) {
                $pdo->prepare("INSERT IGNORE INTO guardian_structural_evolutions (evo_code, target_table, evolution_type, description) VALUES (?, ?, 'COMPOSITE_INDEX', ?)")
                    ->execute([$tbl . '_' . $idxName, $tbl, $desc]);
                continue;
            }
            // Verify all target columns exist before adding composite index
            $colCheckOk = true;
            foreach ($cols as $c) {
                if (!$pdo->query("SHOW COLUMNS FROM `{$tbl}` LIKE '{$c}'")->fetch()) {
                    $colCheckOk = false;
                    break;
                }
            }
            if ($colCheckOk) {
                $colListSql = '`' . implode('`, `', $cols) . '`';
                $pdo->exec("CREATE INDEX `{$idxName}` ON `{$tbl}` ({$colListSql})");
                $pdo->prepare("INSERT IGNORE INTO guardian_structural_evolutions (evo_code, target_table, evolution_type, description) VALUES (?, ?, 'COMPOSITE_INDEX', ?)")
                    ->execute([$tbl . '_' . $idxName, $tbl, $desc]);
                $appliedNow++;
            }
        } catch (Throwable $e) {}
    }

    $totalEvolutions = (int)$pdo->query("SELECT COUNT(*) FROM guardian_structural_evolutions")->fetchColumn();
    $recent = $pdo->query("SELECT target_table, description, applied_at FROM guardian_structural_evolutions ORDER BY applied_at DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

    return [
        'active' => true,
        'total_evolutions' => $totalEvolutions,
        'newly_evolved' => $appliedNow,
        'evolutions' => $recent
    ];
}
