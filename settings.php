<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php 
$page_title = 'الإعدادات'; 
require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;

// AJAX Endpoints for live company & subscription sync
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    // 1. Get Live Company & Subscription Data
    if ($_GET['action'] === 'get_company_subscription_data') {
        try {
            $stmtT = $pdo->prepare("SELECT id, company_name, subdomain, owner_name, owner_phone, phone2, owner_email, status, valid_until, max_users, max_storage, plan_id, base_currency, foreign_currency, created_at FROM tenants WHERE id = ?");
            $stmtT->execute([$GLOBAL_TENANT_ID]);
            $tenant = $stmtT->fetch(PDO::FETCH_ASSOC);

            $planName = 'باقة الأعمال المؤسسية';
            if (!empty($tenant['plan_id'])) {
                try {
                    $stmtP = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'saas_plans'");
                    $plansJson = $stmtP->fetchColumn();
                    if ($plansJson) {
                        $plans = json_decode($plansJson, true);
                        foreach ($plans as $p) {
                            if ($p['id'] == $tenant['plan_id']) {
                                $planName = $p['name'] ?? $planName;
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {}
            }
            if ($tenant) {
                $tenant['plan_name'] = $planName;
            }

            $settingsData = null;
            try {
                $stmtS = $pdo->prepare("SELECT data FROM settings WHERE tenant_id = ? ORDER BY id DESC LIMIT 1");
                $stmtS->execute([$GLOBAL_TENANT_ID]);
                $rawSettings = $stmtS->fetchColumn();
                $settingsData = $rawSettings ? json_decode($rawSettings, true) : null;
            } catch (Exception $e) {
                try {
                    $stmtG = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = ? LIMIT 1");
                    $stmtG->execute(['tenant_' . (int)$GLOBAL_TENANT_ID . '_erp_settings_v1']);
                    $rawSettings = $stmtG->fetchColumn();
                    $settingsData = $rawSettings ? json_decode($rawSettings, true) : null;
                } catch (Exception $e2) {}
            }

            echo json_encode([
                'success' => true,
                'tenant_id' => $GLOBAL_TENANT_ID,
                'tenant' => $tenant,
                'settings' => $settingsData
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    // 2. Save Live Company Settings
    if ($_GET['action'] === 'save_company_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            if (!$input || !is_array($input)) {
                echo json_encode(['success' => false, 'error' => 'بيانات الإعدادات غير صالحة']);
                exit;
            }
            
            // Sync to tenants table
            $companyName = trim($input['companyName'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $phone2 = trim($input['whatsapp'] ?? '');
            
            if ($companyName !== '' || $phone !== '' || $phone2 !== '') {
                $stmtUpT = $pdo->prepare("UPDATE tenants SET 
                    company_name = CASE WHEN ? != '' THEN ? ELSE company_name END,
                    owner_phone = CASE WHEN ? != '' THEN ? ELSE owner_phone END,
                    phone2 = CASE WHEN ? != '' THEN ? ELSE phone2 END
                    WHERE id = ?");
                $stmtUpT->execute([$companyName, $companyName, $phone, $phone, $phone2, $phone2, $GLOBAL_TENANT_ID]);
            }
            
            // 🛡️ Physical Tenant Logo File Isolation Barrier
            if (!empty($input['logoBase64']) && strpos($input['logoBase64'], 'data:image') === 0) {
                try {
                    $parts = explode(',', $input['logoBase64']);
                    if (count($parts) === 2) {
                        $imgData = base64_decode($parts[1]);
                        $uploadDir = __DIR__ . '/uploads/tenants/' . (int)$GLOBAL_TENANT_ID;
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }
                        $logoFilePath = $uploadDir . '/logo.png';
                        @file_put_contents($logoFilePath, $imgData);
                        $input['logoUrl'] = 'uploads/tenants/' . (int)$GLOBAL_TENANT_ID . '/logo.png?v=' . time();
                    }
                } catch (Exception $e) {}
            }
            
            // Sync full settings payload into global_settings and settings table
            $jsonStr = json_encode($input, JSON_UNESCAPED_UNICODE);
            try {
                @$pdo->exec("ALTER TABLE global_settings MODIFY COLUMN setting_value LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL");
            } catch (Throwable $e) {}
            try {
                $stmtG = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmtG->execute(['tenant_' . (int)$GLOBAL_TENANT_ID . '_erp_settings_v1', $jsonStr, $jsonStr]);
                if (!empty($input['smtpServer']) || !empty($input['smtpUser'])) {
                    $smtpJson = json_encode([
                        'smtp_server'  => trim((string)($input['smtpServer'] ?? '')),
                        'smtp_port'    => trim((string)($input['smtpPort'] ?? '587')) ?: '587',
                        'smtp_user'    => trim((string)($input['smtpUser'] ?? '')),
                        'smtp_pass'    => (!empty($input['smtpPass']) && $input['smtpPass'] !== '********') ? trim((string)$input['smtpPass']) : '',
                        'backup_email' => trim((string)($input['backupEmail'] ?? ''))
                    ], JSON_UNESCAPED_UNICODE);
                    $stmtG->execute(['smtp_config_json', $smtpJson, $smtpJson]);
                }
                foreach ([
                    'smtp_server'  => $input['smtpServer'] ?? null,
                    'smtp_port'    => $input['smtpPort'] ?? null,
                    'smtp_user'    => $input['smtpUser'] ?? null,
                    'smtp_pass'    => (!empty($input['smtpPass']) && $input['smtpPass'] !== '********') ? $input['smtpPass'] : null,
                    'backup_email' => $input['backupEmail'] ?? null
                ] as $k => $v) {
                    if ($v === null) continue;
                    $sv = trim((string)$v);
                    try {
                        $stmtG->execute([$k, $sv, $sv]);
                    } catch (Throwable $e) {
                        try {
                            $jv = json_encode($sv, JSON_UNESCAPED_UNICODE);
                            $stmtG->execute([$k, $jv, $jv]);
                        } catch (Throwable $e2) {}
                    }
                }
            } catch (Exception $e) {}

            try {
                $stmtCheck = $pdo->prepare("SELECT id FROM settings WHERE tenant_id = ? LIMIT 1");
                $stmtCheck->execute([$GLOBAL_TENANT_ID]);
                $existingId = $stmtCheck->fetchColumn();
                if ($existingId) {
                    $stmtUpS = $pdo->prepare("UPDATE settings SET data = ?, updated_at = NOW() WHERE id = ?");
                    $stmtUpS->execute([$jsonStr, $existingId]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO settings (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmtIns->execute([$GLOBAL_TENANT_ID, $jsonStr]);
                }
            } catch (Exception $e) {}
            
            // If user session exists, update company_name there too
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            if (!empty($companyName) && isset($_SESSION['user'])) {
                $_SESSION['user']['company_name'] = $companyName;
            }
            
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

// Pre-fetch for server-rendered state
$serverTenant = null;
$serverSettings = null;
try {
    if ($pdo && !empty($GLOBAL_TENANT_ID)) {
        $stmtT = $pdo->prepare("SELECT id, company_name, subdomain, owner_name, owner_phone, phone2, owner_email, status, valid_until, max_users, max_storage, plan_id, base_currency, foreign_currency, created_at FROM tenants WHERE id = ?");
        $stmtT->execute([$GLOBAL_TENANT_ID]);
        $serverTenant = $stmtT->fetch(PDO::FETCH_ASSOC);

        $planName = 'باقة الأعمال المؤسسية';
        if (!empty($serverTenant['plan_id'])) {
            try {
                $stmtP = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'saas_plans'");
                $plansJson = $stmtP->fetchColumn();
                if ($plansJson) {
                    $plans = json_decode($plansJson, true);
                    foreach ($plans as $p) {
                        if ($p['id'] == $serverTenant['plan_id']) {
                            $planName = $p['name'] ?? $planName;
                            break;
                        }
                    }
                }
            } catch (Exception $e) {}
        }
        if ($serverTenant) {
            $serverTenant['plan_name'] = $planName;
        }

        $stmtS = $pdo->prepare("SELECT data FROM settings WHERE tenant_id = ? ORDER BY id DESC LIMIT 1");
        $stmtS->execute([$GLOBAL_TENANT_ID]);
        $rawS = $stmtS->fetchColumn();
        if ($rawS) $serverSettings = json_decode($rawS, true);
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>السيطرة 2026 - الإعدادات</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/leaflet.css">
    <link rel="stylesheet" href="css/all.min.css?v=2027">
    <script>
        window.SERVER_TENANT = <?php echo json_encode($serverTenant, JSON_UNESCAPED_UNICODE); ?>;
        window.SERVER_SETTINGS = <?php echo json_encode($serverSettings, JSON_UNESCAPED_UNICODE); ?>;
        window.SERVER_WA_POLICY = <?php $serverWaPolicy = null; try { if ($pdo) { $tPolKey = 'tenant_' . (int)($tenantId ?? ($GLOBAL_TENANT_ID ?? 1)) . '_erp_whatsapp_statement_policy_v1'; $stmtWaPol = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN (?, 'erp_whatsapp_statement_policy_v1') ORDER BY (setting_key = ?) DESC LIMIT 1"); $stmtWaPol->execute([$tPolKey, $tPolKey]); $rawWaPol = $stmtWaPol->fetchColumn(); if ($rawWaPol) $serverWaPolicy = json_decode($rawWaPol, true); } } catch (Throwable $e) {} echo json_encode($serverWaPolicy, JSON_UNESCAPED_UNICODE); ?>;
    </script>
<link rel="stylesheet" href="css/screens/settings-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/settings-s1.css') ?: '2027' ?>">
</head>
<body>

<!-- Ambient Animated Background -->
<div class="ambient-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="settings-container">

    <div id="save-badge" class="save-badge">✅ تم حفظ الإعدادات بنجاح</div>

    <!-- Unified Premium Header -->
        <?php $header_theme_class = 'header-theme-settings'; ?>
        <?php include 'includes/header.php'; ?>

    <!-- شريط البحث الفوري في الإعدادات -->
    <div class="glass-panel" style="margin-bottom: 14px; padding: 10px 16px; border-radius: 14px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(8, 25, 48, 0.95)); border: 1px solid rgba(56, 189, 248, 0.35);">
        <input type="search" id="settings-omni-search" oninput="window.filterSettingsOmniSearch(this.value)" placeholder="🔍 ابحث فوراً في كل إعدادات النظام، الضرائب، الشاشة، الخطوط، الصلاحيات..." style="width: 100%; padding: 8px 14px; border-radius: 10px; background: rgba(2, 6, 23, 0.75); border: 1px solid rgba(56, 189, 248, 0.4); color: #f8fafc; font-family: 'Tajawal', sans-serif; font-size: 0.9rem; outline: none;">
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-nav">
        <button class="tab-btn active" onclick="switchTab('company', this)" data-i18n="settings.companyData">🏢 بيانات الشركة</button>
        <button class="tab-btn" onclick="switchTab('finance', this)" data-i18n="settings.financeTax">💰 المالية والضرائب</button>
        <button class="tab-btn" onclick="switchTab('inventory', this)" data-i18n="settings.warehouses">📦 المخازن</button>
        <button class="tab-btn" onclick="switchTab('printing', this)" data-i18n="settings.printing">🖨️ إعدادات الطباعة</button>
        <button class="tab-btn" onclick="switchTab('email', this)" data-i18n="settings.smtp">📧 إعدادات البريد SMTP</button>
        <button class="tab-btn" onclick="switchTab('users', this)" data-i18n="settings.users">🛡️ المستخدمين والصلاحيات</button>
        <button class="tab-btn" onclick="switchTab('cybersecurity', this)">🔐 الأمان السيبراني والحوكمة</button>
        <button class="tab-btn" onclick="switchTab('screen', this)">🖥️ إعدادات الشاشة</button>
        <button class="tab-btn" onclick="switchTab('fonts', this)">🔤 إعدادات الخطوط</button>
        <button class="tab-btn" onclick="switchTab('manager-alerts', this); if(window.loadManagerAlerts12) window.loadManagerAlerts12();" style="border-color: rgba(16,185,129,0.5); color: #34d399; font-weight: 900;">👔 إشعارات الإدارة (12 رقم)</button>
        <button class="tab-btn" onclick="switchTab('ui', this)" data-i18n="settings.langUI">🎨 اللغة والواجهة</button>
        <button class="tab-btn" onclick="switchTab('system', this)" data-i18n="settings.system">💾 النظام والنسخ</button>
    </div>

    <div class="settings-layout">
        
        <!-- Tab: Company -->
        <div class="tab-content active" id="tab-company">
            <div class="glass-panel" id="panel-company">
                <div class="setting-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <span>🏢 بيانات الشركة والاشتراك</span>
                    <span id="subscription-status" style="font-size: 0.9rem; padding: 5px 14px; border-radius: 20px; background: rgba(255,255,255,0.1); color: #fff; display:inline-flex; align-items:center; gap:6px; font-weight:700;">
                        ⏳ جاري فحص الاشتراك...
                    </span>
                </div>

                <!-- 🌟 بطاقة تفاصيل الاشتراك والترخيص السحابي 🌟 -->
                <div id="subscription-summary-box" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(30, 41, 59, 0.65) 100%); border: 1.5px solid rgba(56, 189, 248, 0.35); border-radius: 18px; padding: 16px 20px; margin-top: 15px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; ">
                    <div class="sub-card-item">
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; display:block;">📦 باقة الاشتراك الحالية</span>
                        <span id="sub-plan-name" style="font-size: 1.05rem; font-weight: 900; color: #38bdf8; margin-top: 4px; display:block;">الباقة المؤسسية المعتمدة</span>
                    </div>
                    <div class="sub-card-item">
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; display:block;">🛡️ حالة الحساب</span>
                        <span id="sub-account-status" style="font-size: 1.05rem; font-weight: 900; color: #10b981; margin-top: 4px; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-circle-check"></i> حساب نشط ومعتمد
                        </span>
                    </div>
                    <div class="sub-card-item">
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; display:block;">⏳ تاريخ انتهاء الصلاحية</span>
                        <span id="sub-expiry-date" style="font-size: 1.05rem; font-weight: 900; color: #f8fafc; margin-top: 4px; display:block;">-</span>
                    </div>
                    <div class="sub-card-item">
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; display:block;">⏱️ الأيام المتبقية</span>
                        <span id="sub-days-remaining" style="font-size: 1.05rem; font-weight: 900; color: #34d399; margin-top: 4px; display:block;">-</span>
                    </div>
                </div>
                
                <!-- 🏛️ منطقة اسم المنشأة والشعار والتوقيع / الختم المعتمد للسندات والإشعارات -->
                <div class="wa-voucher-branding-bar" style="margin-top: 5px; margin-bottom: 18px; padding: 12px 14px; border-radius: 12px; background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(250, 204, 21, 0.35); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 240px;">
                        <span style="color: #fde047; font-weight: 900; font-size: 0.85rem; white-space: nowrap;">🏢 اسم المنشأة (تحت سند رصيد):</span>
                        <input type="text" class="wa-policy-company-name" placeholder="مثال: منظومة السودان بلد الأحرار" onchange="syncAdminWaPolicyFromUI(this)" style="flex: 1; height: 36px; padding: 0 10px; border-radius: 8px; border: 1px solid rgba(250, 204, 21, 0.45); background: rgba(0, 0, 0, 0.4); color: #ffffff; font-weight: 800; font-size: 0.9rem;">
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <label style="height: 36px; background: rgba(250, 204, 21, 0.18); color: #fde047; border: 1px solid rgba(250, 204, 21, 0.5); padding: 0 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; margin: 0;">
                            <span>🖼️ رفع شعار المنشأة</span>
                            <input type="file" accept="image/*" onchange="handleWaVoucherLogoUpload(this)" style="display: none;">
                        </label>
                        <img class="wa-policy-logo-preview" src="" alt="الشعار" style="display: none; height: 36px; width: auto; max-width: 55px; object-fit: contain; border-radius: 6px; border: 1px solid rgba(250, 204, 21, 0.5); background: rgba(255,255,255,0.12); padding: 2px;">
                        <button type="button" class="wa-policy-logo-clear-btn" onclick="clearWaVoucherLogoImage()" style="display: none; height: 36px; background: rgba(239, 68, 68, 0.18); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); padding: 0 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;" title="حذف شعار المنشأة">🗑️</button>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 220px;">
                        <span style="color: #34d399; font-weight: 900; font-size: 0.85rem; white-space: nowrap;">💮 نص التوقيع/الختم:</span>
                        <input type="text" class="wa-policy-stamp-text" placeholder="ختم مصادقة مالية إلكترونية" onchange="syncAdminWaPolicyFromUI(this)" style="flex: 1; height: 36px; padding: 0 10px; border-radius: 8px; border: 1px solid rgba(52, 211, 153, 0.45); background: rgba(0, 0, 0, 0.4); color: #ffffff; font-weight: 800; font-size: 0.9rem;">
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <label style="height: 36px; background: rgba(52, 211, 153, 0.18); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.5); padding: 0 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; margin: 0;">
                            <span>✍️ رفع التوقيع / الختم</span>
                            <input type="file" accept="image/*" onchange="handleWaVoucherStampUpload(this)" style="display: none;">
                        </label>
                        <img class="wa-policy-stamp-preview" src="" alt="التوقيع" style="display: none; height: 36px; width: auto; max-width: 85px; object-fit: contain; border-radius: 6px; border: 1px solid rgba(250, 204, 21, 0.5); background: #ffffff; padding: 2px;">
                        <button type="button" class="wa-policy-stamp-clear-btn" onclick="clearWaVoucherStampImage()" style="display: none; height: 36px; background: rgba(239, 68, 68, 0.18); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); padding: 0 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;" title="حذف التوقيع / الختم">🗑️</button>
                        <button type="button" onclick="syncAdminWaPolicyFromUI(this)" style="height: 36px; background: linear-gradient(135deg, #eab308, #ca8a04); color: #04120e; border: none; padding: 0 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 900; cursor: pointer;">💾 حفظ الهوية والشعار والتوقيع</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 5px;">
                    <!-- Row 1: Logo and Preview -->
                    <div class="form-group" style="grid-column: span 2; display: flex; align-items: center; gap: 20px; margin:0;">
                        <div style="flex: 1;">
                            <label class="form-label">شعار الشركة (اللوقو المعتمد للفواتير والسندات)</label>
                            <input type="file" id="cfg-logo" accept="image/*" class="form-input" style="padding: 10px;">
                        </div>
                        <div id="logo-preview-container" style="width:120px; height:80px; border:1px dashed rgba(255,255,255,0.3); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; background:rgba(0,0,0,0.2); margin-top: 25px;">
                            <img id="logo-preview" src="" alt="شعار الشركة" style="display:none; max-height:100%; max-width:100%; object-fit:contain;">
                        </div>
                    </div>

                    <!-- Row 2: Company Name -->
                    <div class="form-group" style="grid-column: span 2; margin: 0;">
                        <label class="form-label">اسم الشركة الرسمي المطبوع على الفواتير والتقارير</label>
                        <input type="text" class="form-input" id="cfg-company-name" placeholder="مثال: مؤسسة السيطرة للتجارة والخدمات">
                    </div>

                    <!-- Row 3: Commercial Reg & Tax Number -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">رقم السجل التجاري (Commercial Registration)</label>
                        <input type="text" class="form-input" id="cfg-reg-number" placeholder="رقم السجل التجاري المعتمد">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">الرقم الضريبي (VAT / Tax Number)</label>
                        <input type="text" class="form-input" id="cfg-tax-number" placeholder="الرقم الضريبي للمؤسسة">
                    </div>

                    <!-- Row 4: Phone & WhatsApp -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">رقم الهاتف للتواصل</label>
                        <input type="tel" class="form-input" id="cfg-phone" placeholder="+249..." dir="ltr" style="text-align: left;" oninput="this.value = this.value.replace(/[^0-9+\\- ]/g, '')">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">رقم الواتساب (WhatsApp المعتمد)</label>
                        <input type="tel" class="form-input" id="cfg-whatsapp" placeholder="+249..." dir="ltr" style="text-align: left;" oninput="this.value = this.value.replace(/[^0-9+\\- ]/g, '')">
                    </div>

                    <!-- Row 5: Address & Location -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">العنوان الجغرافي والفرع الرئيسي</label>
                        <input type="text" class="form-input" id="cfg-address" placeholder="المدينة، الحي، الشارع، المبنى">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">رابط الموقع الجغرافي / خرائط Google</label>
                        <input type="url" class="form-input" id="cfg-location" placeholder="https://maps.google.com/...">
                    </div>
                </div>

                <!-- Quick In-Tab Save Action -->
                <div style="display: flex; justify-content: flex-end; margin-top: 25px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.08);">
                    <button type="button" onclick="saveSettings()" class="save-btn-main" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; padding: 12px 30px; border-radius: 12px; font-weight: 800; font-size: 1rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);">
                        <i class="fa-solid fa-floppy-disk"></i> حفظ بيانات الشركة والاشتراك
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab: Finance -->
        <div class="tab-content" id="tab-finance">


            <!-- Panel 1: Currency Configuration & Exchange Rates -->
            <div class="glass-panel" id="panel-finance" style="margin-bottom: 20px;">
                <div class="setting-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <span>💰 منظومة العملات وسعر الصرف</span>
                    <span id="cfg-lock-badge" class="badge" style="display:none; background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:4px 12px; border-radius:20px; font-size:0.85rem;">🔒 العملات الرئيسية معتمدة ومثبتة</span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <!-- Base Local Currency -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>العملة الافتراضية المحلية (Base Currency)</span>
                            <span id="cfg-base-lock-icon" style="display:none; color:#f59e0b; font-size:0.85rem; background:rgba(245,158,11,0.1); padding:2px 8px; border-radius:12px; border:1px solid rgba(245,158,11,0.3);" title="ثابتة ولا تتغير">🔒 غير قابلة للتغيير</span>
                        </label>
                        <div style="position:relative;">
                            <select class="form-select" id="cfg-currency" onchange="onCurrencySelectionChange()" style="padding-left:35px;">
                                <option value="SDG">الجنيه السوداني (SDG)</option>
                                <option value="USD">الدولار الأمريكي (USD)</option>
                                <option value="SAR">الريال السعودي (SAR)</option>
                                <option value="AED">الدرهم الإماراتي (AED)</option>
                            </select>
                            <span id="cfg-base-select-lock" style="display:none; position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none; font-size:1.1rem;">🔒</span>
                        </div>
                        <small style="color:var(--text-secondary); display:block; margin-top:5px;">العملة الأساسية لتقييم الحسابات والمخزون والتقارير المالية.</small>
                    </div>

                    <!-- Active Foreign Currency -->
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>العملة الأجنبية النشطة (Active Foreign Currency)</span>
                            <span id="cfg-foreign-lock-icon" style="display:none; color:#f59e0b; font-size:0.85rem; background:rgba(245,158,11,0.1); padding:2px 8px; border-radius:12px; border:1px solid rgba(245,158,11,0.3);" title="ثابتة ولا تتغير">🔒 غير قابلة للتغيير</span>
                        </label>
                        <div style="position:relative;">
                            <select class="form-select" id="cfg-foreign-currency" onchange="onCurrencySelectionChange()" style="padding-left:35px;">
                                <option value="USD" selected>الدولار الأمريكي (USD)</option>
                                <option value="SAR">الريال السعودي (SAR)</option>
                                <option value="AED">الدرهم الإماراتي (AED)</option>
                                <option value="SDG">الجنيه السوداني (SDG)</option>
                            </select>
                            <span id="cfg-foreign-select-lock" style="display:none; position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none; font-size:1.1rem;">🔒</span>
                        </div>
                        <small style="color:var(--text-secondary); display:block; margin-top:5px;">العملة الأجنبية المقترنة بالعملة المحلية في السندات والفواتير.</small>
                    </div>
                </div>

                <!-- Currency Immutability / Lock Notice -->
                <div id="currency-lock-notice" style="display:none; background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); border-radius:8px; padding:12px 15px; margin-bottom:20px; font-size:0.9rem; color:#f8fafc;">
                    💡 <strong>ملاحظة محاسبية:</strong> تم اعتماد العملة المحلية والعملة الأجنبية وتثبيتهما بحسابك لحماية الحركة المالية ودفتر الأستاذ. للتعديل يرجى التواصل مع <a href="javascript:void(0)" onclick="openSupportCurrencyModal()" style="color:#38bdf8; text-decoration:underline; font-weight:bold;">خدمات المشتركين والدعم الفني</a>.
                </div>

                <!-- Exchange Rates Sub-Panel -->
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed rgba(255,255,255,0.15);">
                    <div class="setting-title" id="exchange-rates-title" style="font-size: 1.05rem; color: #f59e0b; margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
                        <span>💱 سعر صرف العملة الأجنبية مقابل العملة المحلية</span>
                        <span id="cfg-mode-badge" style="font-size: 0.85rem; padding: 4px 12px; border-radius: 20px; font-weight: bold; transition: all 0.3s ease; background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; color: #38bdf8;">
                            🔒 السعر المركزي معتمد آلياً
                        </span>
                    </div>
                    
                    <div id="exchange-rates-single-row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(8, 20, 45, 0.98)); border: 2px solid #38bdf8; box-shadow: 0 0 25px rgba(56, 189, 248, 0.35), inset 0 1px 2px rgba(255,255,255,0.1); padding: 14px 20px; border-radius: 14px; transition: all 0.4s ease;">
                        <!-- Source Dropdown -->
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <label class="form-label" style="margin: 0; font-size: 0.95rem; font-weight: bold; color: #cbd5e1; white-space: nowrap;">مصدر تسعير العملة الأجنبية:</label>
                            <select class="form-select" id="cfg-exchange-mode" style="width: auto; min-width: 275px; padding: 8px 14px; background: #090d16; border: 1px solid #334155; color: #f8fafc; font-family: 'Tajawal'; font-size: 0.95rem; border-radius: 8px; cursor: pointer; font-weight: bold;" onchange="if(typeof handleExchangeModeChange === 'function') handleExchangeModeChange(this)">
                                <option value="global" selected>الاعتماد على السعر المركزي (لوحة المشتركين)</option>
                                <option value="manual">إدخال يدوي مخصص</option>
                            </select>
                        </div>

                        <div style="width: 1px; height: 35px; background: rgba(255,255,255,0.15); margin: 0 5px;"></div>

                        <!-- Rate Label & Input & Currency Suffix -->
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <label class="form-label" id="cfg-foreign-rate-label" style="margin: 0; font-size: 0.95rem; font-weight: bold; color: #38bdf8; text-shadow: 0 0 10px rgba(56,189,248,0.5); white-space: nowrap;">سعر الدولار الأمريكي (USD):</label>
                            <input type="text" inputmode="decimal" class="form-input rate-input" id="cfg-rate-usd" placeholder="سعر الصرف..." style="width: 140px; text-align: center; background: rgba(15, 23, 42, 0.9) !important; border: 2px solid #38bdf8 !important; color: #38bdf8 !important; font-size: 1.25rem !important; font-weight: 900 !important; font-family: 'JetBrains Mono', 'Outfit', sans-serif; padding: 6px 12px; border-radius: 10px; box-shadow: 0 0 15px rgba(56,189,248,0.3); transition: all 0.3s ease;" readOnly>
                            <span id="cfg-base-currency-suffix" style="color: #f59e0b; font-weight: 900; font-size: 1.05rem; font-family: 'Outfit', sans-serif; white-space: nowrap; text-shadow: 0 0 8px rgba(245, 158, 11, 0.4);">مقابل SDG</span>
                        </div>
                    </div>

                    <div id="exchange-rate-warning" style="display:none; color: #ef4444; font-size:0.9rem; margin-top:10px; font-weight:bold;">
                        ⚠️ ليس لديك الصلاحية لتعديل أسعار الصرف (للقراءة فقط).
                    </div>
                </div>
            </div>

                            <!-- Exchange Rate History Panel -->
                <div class="glass-panel" id="panel-exchange-history" style="margin-bottom: 20px;">
                    <div class="setting-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <span>📜 تقرير سجل تعديلات أسعار الصرف</span>
                        <small style="display:block; color:#94a3b8; font-size:0.85rem; margin-top:5px;">
                            💡 هذا التقرير يوثق فقط السجلات التاريخية التي تم حفظها سابقاً، ولا يقوم بالاتصال بمنصة ساس لجلب السعر اللحظي الحالي للوحة المشتركين هنا.
                        </small>
                    </div>
                    <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <label style="color: #94a3b8; font-size: 0.9rem;">فرز التقرير حسب:</label>
                        <select id="cfg-history-filter" class="form-select" style="width: auto; padding: 5px 15px; background: rgba(0,0,0,0.2);" onchange="renderExchangeRateHistory()">
                            <option value="all">الكل (All)</option>
                            <option value="manual">الأسعار المدخلة يدوياً فقط</option>
                            <option value="saas">الأسعار المسحوبة من منصة ساس فقط</option>
                        </select>
                        <button class="btn btn-primary" onclick="renderExchangeRateHistory()" style="padding: 5px 15px; font-size: 0.9rem; width: auto; flex: none;">تحديث</button>
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="erp-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:right; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1);">سعر الصرف (USD)</th>
                                    <th style="text-align:right; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1);">التاريخ</th>
                                    <th style="text-align:right; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1);">الزمن</th>
                                    <th style="text-align:right; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1);">المستخدم</th>
                                    <th style="text-align:right; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1);">مصدر التعديل</th>
                                </tr>
                            </thead>
                            <tbody id="cfg-history-tbody">
                                <tr><td colspan="5" style="text-align:center; padding:15px; color:#94a3b8;">لا توجد سجلات بعد.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- Panel 2: Standalone VAT Tax & Precision Panel -->
            <div class="glass-panel" style="margin-bottom: 20px;">
                <div class="setting-title" style="font-size: 1.05rem; color: #10b981; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <span>📊 الضرائب (VAT)، التقريب العشري، وسقف الخصم المسموح</span>
                    <span style="font-size:0.78rem; color:#34d399; background:rgba(16,185,129,0.12); padding:3px 10px; border-radius:12px; border:1px solid rgba(16,185,129,0.3);">مربوط آلياً بالفواتير والسندات والتقارير</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:18px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">نسبة ضريبة القيمة المضافة المطبقة (%)</label>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="number" class="form-input" id="cfg-vat" value="0" min="0" max="100" step="0.5" style="font-size: 1.1rem; font-weight: bold; width: 140px;">
                            <span style="font-size:1.1rem; font-weight:bold; color:var(--text-secondary);">%</span>
                        </div>
                        <small style="color:var(--text-secondary); display:block; margin-top:6px;">النسبة المئوية للضريبة المحسوبة على فواتير المبيعات والمشتريات.</small>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">عدد الخانات العشرية المعتمدة (Decimal Precision)</label>
                        <select class="form-select" id="cfg-decimal-places">
                            <option value="0">بدون كسور عشرية (0 - أرقام صحيحة)</option>
                            <option value="2" selected>خانتان عشريتان (0.00 - معيار محاسبي)</option>
                            <option value="3">3 خانات عشرية (0.000)</option>
                        </select>
                        <small style="color:var(--text-secondary); display:block; margin-top:6px;">توحيد التقريب المحاسبي في ميزان المراجعة وكشوفات الحساب.</small>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">أقصى نسبة خصم مسموحة بدون رمز المدير (%)</label>
                        <input type="number" class="form-input" id="cfg-max-discount-pct" value="10" min="0" max="100" step="1" style="font-size: 1.05rem; font-weight: bold;">
                        <small style="color:var(--text-secondary); display:block; margin-top:6px;">أي خصم يتجاوز هذه النسبة يطلب رمز اعتماد المدير (PIN) فوراً.</small>
                    </div>
                </div>
            </div>

            <!-- Panel 3: Fiscal Year & Accounting Period Lock Panel -->
            <div class="glass-panel">
                <div class="setting-title" style="font-size: 1.05rem; color: #38bdf8; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <span>📅 السنة المالية وقفل الفترة المحاسبية (منع التعديل بأثر رجعي)</span>
                    <span style="font-size:0.78rem; color:#f59e0b; background:rgba(245,158,11,0.12); padding:3px 10px; border-radius:12px; border:1px solid rgba(245,158,11,0.3);">🔒 حماية القيود التاريخية</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:18px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">تاريخ بداية السنة المالية للمؤسسة</label>
                        <select class="form-select" id="cfg-fiscal-year" style="font-size: 1rem;">
                            <option value="1">1 يناير (السنة التقويمية - افتراضي)</option>
                            <option value="4">1 أبريل (السنة الربيعية)</option>
                            <option value="7">1 يوليو (السنة الصيفية)</option>
                            <option value="10">1 أكتوبر (السنة الخريفية)</option>
                        </select>
                        <small style="color:var(--text-secondary); display:block; margin-top:6px;">الشهر الفعلي لبدء السنة المالية واستخراج القوائم الختامية.</small>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="color:#fbbf24;">🔒 تاريخ قفل الفترة المحاسبية (Accounting Lock Date)</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="date" class="form-input" id="cfg-accounting-lock-date" style="border-color:rgba(245,158,11,0.45); color:#fde68a; font-weight:800;">
                            <button type="button" onclick="document.getElementById('cfg-accounting-lock-date').value=''; saveSettings();" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.35); padding:10px 12px; border-radius:10px; cursor:pointer; font-weight:800; white-space:nowrap;" title="إلغاء قفل الفترة">فك القفل</button>
                        </div>
                        <small style="color:var(--text-secondary); display:block; margin-top:6px;">يمنع الموظفين من إضافة أو تعديل أو حذف أي فاتورة أو سند بتاريخ يسبق أو يساوي هذا التاريخ إلا بتفويض المدير.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Inventory -->
        <div class="tab-content" id="tab-inventory">
            <div class="glass-panel" id="panel-inventory-rules">
                <div class="setting-title">📦 قواعد المخزون والعمليات</div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">السماح بالبيع بالسالب (تجاوز رصيد المخزون)</span>
                    <label class="switch"><input type="checkbox" id="cfg-allow-negative-stock"><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل دورة البيع الثنائية (فاتورة ثم إذن صرف)</span>
                    <label class="switch"><input type="checkbox" id="cfg-two-step-sale" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">إلزامية تحديد اسم العميل في كل فاتورة</span>
                    <label class="switch"><input type="checkbox" id="cfg-require-customer"><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">منع الموظفين من البيع بأقل من سعر التكلفة</span>
                    <label class="switch"><input type="checkbox" id="cfg-prevent-below-cost"><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل تنبيهات انخفاض المخزون (الحد الأدنى)</span>
                    <label class="switch"><input type="checkbox" id="cfg-low-stock-alerts" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">اعتماد سياسة الصرف: الوارد أولاً يصرف أولاً (FIFO)</span>
                    <label class="switch"><input type="checkbox" id="cfg-fifo-policy" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">منع بيع أو صرف المواد منتهية الصلاحية تماماً</span>
                    <label class="switch"><input type="checkbox" id="cfg-prevent-expired-sale" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل استخدام قارئ الباركود في شاشات المبيعات والمشتريات</span>
                    <label class="switch"><input type="checkbox" id="cfg-enable-barcode" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row" style="flex-direction:column; align-items:stretch; gap:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="form-label" style="margin:0;">الترحيل التلقائي للفواتير والحركات إلى الحسابات العامة</span>
                        <label class="switch"><input type="checkbox" id="cfg-auto-post-gl" checked><span class="slider"></span></label>
                    </div>
                    <small style="color:#94a3b8; line-height:1.6; display:block;">
                        <strong style="color:#10b981;">✅ مفعّل (ترحيل تلقائي):</strong> عند تأكيد الفاتورة تُرحّل فوراً وتؤثر في الأرصدة والمخزون.<br>
                        <strong style="color:#fbbf24;">⏸️ معطّل (ترحيل يدوي):</strong> عند التأكيد تُحفظ الفاتورة لكن لا تؤثر في الأرصدة حتى يتم ترحيلها يدوياً.
                    </small>
                </div>
            </div>
        </div>

        <!-- Tab: Printing -->
        <div class="tab-content" id="tab-printing">
            <div class="glass-panel" id="panel-printing">
                <div class="setting-title">🖨️ الطباعة والفواتير</div>
                
                <div class="sub-tabs-container">
                    <button class="sub-tab-btn active" onclick="switchPrintTab('print-sales', this)">المبيعات</button>
                    <button class="sub-tab-btn" onclick="switchPrintTab('print-purchases', this)">المشتريات</button>
                    <button class="sub-tab-btn" onclick="switchPrintTab('print-quotes', this)">عروض الأسعار</button>
                    <button class="sub-tab-btn" onclick="switchPrintTab('print-reports', this)">التقارير</button>
                </div>

                <!-- Sales Printing -->
                <div class="sub-tab-content active" id="print-sales">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">مقاس ورق الفاتورة</label>
                            <select class="form-select" id="cfg-print-sales-size">
                                <option value="A4">ورق عادي (A4)</option>
                                <option value="A5">ورق نصف عادي (A5)</option>
                                <option value="80mm">طابعة إيصالات حرارية (80mm)</option>
                                <option value="58mm">طابعة إيصالات حرارية (58mm)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">طباعة رمز الاستجابة السريعة (QR Code)</label>
                            <select class="form-select" id="cfg-print-sales-qr">
                                <option value="true">تفعيل (ZATCA / الفوترة الإلكترونية)</option>
                                <option value="false">تعطيل</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="setting-row" style="margin-top: 15px;">
                        <span class="form-label" style="margin:0;">طباعة شعار الشركة (Logo) أعلى الفاتورة</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-sales-logo" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة مربعات التوقيعات (توقيع البائع - توقيع المشتري)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-sales-signatures"><span class="slider"></span></label>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label">تذييل فاتورة المبيعات (نص أسفل الفاتورة)</label>
                        <textarea class="form-input" id="cfg-print-sales-footer" rows="2" placeholder="مثال: البضاعة المباعة لا ترد ولا تستبدل إلا خلال 3 أيام..."></textarea>
                    </div>
                </div>

                <!-- Purchases Printing -->
                <div class="sub-tab-content" id="print-purchases">
                    <div class="form-group">
                        <label class="form-label">مقاس ورق فاتورة المشتريات / أمر الشراء</label>
                        <select class="form-select" id="cfg-print-purchases-size">
                            <option value="A4">ورق عادي (A4)</option>
                            <option value="A5">ورق نصف عادي (A5)</option>
                            <option value="80mm">طابعة إيصالات حرارية (80mm)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة شعار الشركة (Logo) أعلى الفاتورة</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-purchases-logo" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة مربعات التوقيعات (أمين المستودع - المورد)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-purchases-signatures" checked><span class="slider"></span></label>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label">تذييل أمر الشراء / فاتورة المشتريات</label>
                        <textarea class="form-input" id="cfg-print-purchases-footer" rows="2" placeholder="ملاحظات مطبوعة للمورد..."></textarea>
                    </div>
                </div>

                <!-- Quotes Printing -->
                <div class="sub-tab-content" id="print-quotes">
                    <div class="form-group">
                        <label class="form-label">مقاس ورق عرض السعر</label>
                        <select class="form-select" id="cfg-print-quotes-size">
                            <option value="A4">ورق عادي (A4)</option>
                            <option value="A5">ورق نصف عادي (A5)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة شعار الشركة (Logo) أعلى العرض</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-quotes-logo" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة مربع التوقيع (توقيع وختم الشركة)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-quotes-signatures" checked><span class="slider"></span></label>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label">الشروط والأحكام (Terms & Conditions)</label>
                        <textarea class="form-input" id="cfg-print-quotes-terms" rows="3" placeholder="الشروط والأحكام لعروض الأسعار والفواتير المبدئية (مثال: مدة صلاحية العرض 30 يوماً)..."></textarea>
                    </div>
                </div>

                <!-- Reports Printing -->
                <div class="sub-tab-content" id="print-reports">
                    <div class="form-group">
                        <label class="form-label">مقاس ورق التقارير (كشوفات الحساب، المخزون، إلخ)</label>
                        <select class="form-select" id="cfg-print-reports-size">
                            <option value="A4">ورق عادي (A4)</option>
                            <option value="A5">ورق نصف عادي (A5)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">طباعة الترويسة الرسمية (اللوجو، اسم الشركة، الأرقام الضريبية)</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-reports-header" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row">
                        <span class="form-label" style="margin:0;">إظهار وقت وتاريخ الطباعة واسم المستخدم</span>
                        <label class="switch"><input type="checkbox" id="cfg-print-reports-timestamp" checked><span class="slider"></span></label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Email/SMTP -->
        <div class="tab-content" id="tab-email">
            <div class="glass-panel" id="panel-email">
                <div class="setting-title">📧 إعدادات البريد الإلكتروني (SMTP)</div>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">تستخدم لإرسال الفواتير وكشوفات الحساب للعملاء مباشرة عبر البريد الإلكتروني.</p>
                <div class="form-group"><label class="form-label">خادم البريد (SMTP Server)</label><input type="text" class="form-input" id="cfg-smtp-server" placeholder="مثال: smtp.gmail.com"></div>
                <div class="form-group"><label class="form-label">المنفذ (Port)</label><input type="number" class="form-input" id="cfg-smtp-port" placeholder="مثال: 587 أو 465"></div>
                <div class="form-group"><label class="form-label">بريد المرسل (Email Username)</label><input type="email" class="form-input" id="cfg-smtp-user" placeholder="billing@yourcompany.com"></div>
                <div class="form-group"><label class="form-label">كلمة المرور (App Password)</label><input type="password" class="form-input" id="cfg-smtp-pass" placeholder="كلمة المرور أو App Password"></div>
                
                <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 20px 0;">
                <div class="setting-title">🛡️ النسخ الاحتياطي السحابي (Cloud Backups)</div>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">أدخل البريد الإلكتروني الذي سيستقبل النسخة الاحتياطية لقاعدة البيانات يومياً (مضغوطة بصيغة ZIP).</p>
                <div class="form-group"><label class="form-label">إيميل النسخ الاحتياطي (Backup Email)</label><input type="email" class="form-input" id="cfg-backup-email" placeholder="backup@yourcompany.com"></div>
            </div>
        </div>

        <!-- Tab: Users -->
        <div class="tab-content" id="tab-users">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px;">
                
                <div class="glass-panel" id="panel-users" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">🛡️ إدارة المستخدمين الشاملة</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">إدارة حسابات الموظفين، تحديد نوع المستخدم (مدير نظام أو عادي)، وتخصيص ظهور كل شاشة وأزرارها وتقاريرها بدقة.</p>
                    <button class="action-btn btn-users" onclick="window.location.href='users-roles.php'" style="margin-top: 15px;">⚙️ فتح شاشة المستخدمين المستقلة</button>
                </div>

                <div class="glass-panel" id="panel-whatsapp" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">💬 ربط الواتساب</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">إدارة جلسة الواتساب ومسح الـ QR لإرسال الفواتير والتنبيهات للعملاء مباشرة.</p>
                    <button class="action-btn" style="background: rgba(37,211,102,0.1); color: #25d366; border: 1px solid rgba(37,211,102,0.3); margin-top: 15px;" onclick="openWAModal()">📱 إعداد الواتساب</button>
                </div>

                <div class="glass-panel" id="panel-audit" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">🛡️ سجل الرقابة والتدقيق وتتبع العمليات</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">سجل شامل لجميع حركات النظام لتتبع ومراقبة العمليات والتدقيق الأمني.</p>
                    <button class="action-btn" style="background: rgba(15,23,42,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.2); margin-top: 15px;" onclick="window.location.href='audit-log.php'">🛡️ فتح سجل الرقابة والتدقيق الشامل</button>
                </div>
            </div>

            <!-- EMBEDDED GRANULAR USERS & PERMISSIONS STUDIO -->
            <div class="glass-panel" style="margin:0; padding:25px; border:1px solid rgba(56,189,248,0.3); border-radius:18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:15px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="font-size:1.6rem;">🛡️</span>
                        <div>
                            <h3 style="margin:0; font-size:1.3rem; color:#38bdf8; font-weight:800;">التحكم الدقيق في صلاحيات المستخدمين والشاشات</h3>
                            <p style="margin:3px 0 0 0; color:#94a3b8; font-size:0.85rem;">اختر الموظف للتحكم في ظهور الشاشات، وصلاحيات الأزرار (حفظ، جديد، تعديل، حذف، طباعة، مشاركة)، والتقارير</p>
                        </div>
                    </div>
                    
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <label style="color:#e2e8f0; font-weight:700; font-size:0.9rem;">المستخدم:</label>
                        <select id="settings-user-select" onchange="window.loadUserSettingsStudio(this.value)" style="padding:10px 16px; border-radius:10px; background:#0f172a; border:1px solid #38bdf8; color:#fff; font-size:0.95rem; font-weight:700; outline:none; min-width:200px;">
                            <!-- Populated dynamically -->
                        </select>
                        <button type="button" onclick="window.openNewUserInSettings()" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#38bdf8; padding:10px 16px; border-radius:10px; font-weight:700; cursor:pointer;">
                            + مستخدم جديد
                        </button>
                        <button type="button" onclick="window.changePasswordInSettings()" style="background:rgba(245,158,11,0.15); border:1px solid #f59e0b; color:#fbbf24; padding:10px 16px; border-radius:10px; font-weight:700; cursor:pointer;" title="تغيير كلمة مرور المستخدم المحدد">
                            🔑 كلمة المرور
                        </button>
                        <button type="button" onclick="window.deleteUserInSettings()" style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#f87171; padding:10px 16px; border-radius:10px; font-weight:700; cursor:pointer;" title="حذف المستخدم المحدد">
                            🗑️ حذف
                        </button>
                        <button type="button" onclick="window.saveUserSettingsPermissions()" style="background:#10b981; border:none; color:#030712; padding:10px 22px; border-radius:10px; font-weight:800; cursor:pointer; font-size:0.95rem; box-shadow:0 0 15px rgba(16,185,129,0.4);">
                            💾 حفظ صلاحيات المستخدم
                        </button>
                    </div>

                    <!-- Quick Copy / Clone Permissions Bar -->
                    <div style="margin-top:14px; display:flex; align-items:center; gap:12px; background:linear-gradient(135deg, rgba(99,102,241,0.15), rgba(15,23,42,0.7)); border:1.5px solid rgba(99,102,241,0.4); padding:12px 18px; border-radius:14px; flex-wrap:wrap; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="width:34px; height:34px; border-radius:10px; background:rgba(99,102,241,0.25); border:1.5px solid #6366f1; color:#a5b4fc; display:flex; align-items:center; justify-content:center; font-size:1.05rem;">
                                <i class="fa-solid fa-copy"></i>
                            </span>
                            <div>
                                <strong style="color:#c7d2fe; font-size:0.95rem; font-weight:800;">نسخ ومطابقة الصلاحيات:</strong>
                                <span style="color:#94a3b8; font-size:0.82rem; margin-right:4px;">(نسخ كامل الشاشات والأزرار والورديات الليلية والـ GPS وسقوف المبالغ من موظف لآخر بضغطة واحدة)</span>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-right:auto;">
                            <label style="color:#e2e8f0; font-size:0.88rem; font-weight:700;">نسخ من:</label>
                            <select id="settings-copy-source-select" style="padding:8px 14px; border-radius:8px; background:#0f172a; border:1px solid #6366f1; color:#fff; font-size:0.88rem; font-weight:700; outline:none; min-width:220px;">
                                <option value="">-- اختر المستخدم المصدر --</option>
                            </select>
                            <button type="button" onclick="window.copyPermissionsToSelectedUser()" style="background:linear-gradient(135deg, #6366f1, #4f46e5); color:#fff; border:none; padding:8px 18px; border-radius:8px; font-weight:800; font-size:0.88rem; cursor:pointer; box-shadow:0 0 15px rgba(99,102,241,0.4); display:flex; align-items:center; gap:6px;">
                                📋 تطبيق ونسخ الصلاحيات فوراً
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal / Inline New User Form -->
                <div id="settings-new-user-modal" style="display:none; background:rgba(0,0,0,0.5); padding:15px; border-radius:14px; border:1px dashed #38bdf8; margin-bottom:20px;">
                    <h4 style="margin:0 0 12px 0; color:#38bdf8;">إضافة مستخدم جديد:</h4>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                        <div><label style="font-size:0.8rem; color:#94a3b8;">الاسم الكامل:</label><input type="text" id="new-u-fullname" placeholder="مثال: أحمد محمد" class="form-input"></div>
                        <div><label style="font-size:0.8rem; color:#94a3b8;">اسم الدخول (Username):</label><input type="text" id="new-u-username" placeholder="ahmed" class="form-input"></div>
                        <div><label style="font-size:0.8rem; color:#94a3b8;">كلمة المرور:</label><input type="password" id="new-u-password" placeholder="••••••" class="form-input"></div>
                    </div>
                    <div style="margin-top:12px; display:flex; gap:10px;">
                        <button type="button" onclick="window.confirmCreateNewUserInSettings()" style="background:#38bdf8; color:#030712; border:none; padding:8px 18px; border-radius:8px; font-weight:700; cursor:pointer;">إضافة وتعيين الصلاحيات</button>
                        <button type="button" onclick="document.getElementById('settings-new-user-modal').style.display='none'" style="background:transparent; color:#94a3b8; border:1px solid #64748b; padding:8px 16px; border-radius:8px; cursor:pointer;">إلغاء</button>
                    </div>
                </div>

                <!-- Dynamic Container for the Studio -->
                <div id="settings-permissions-studio-container">
                    <!-- Populated dynamically via MagaieesPermissions -->
                </div>

                <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                    <button type="button" onclick="window.saveUserSettingsPermissions()" style="background:#10b981; border:none; color:#030712; padding:12px 28px; border-radius:12px; font-weight:800; cursor:pointer; font-size:1rem; box-shadow:0 0 20px rgba(16,185,129,0.4);">
                        💾 حفظ وتطبيق الصلاحيات فوراً
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab: Cybersecurity, Governance & Psychological UX -->
        <div class="tab-content" id="tab-cybersecurity">
            <div class="glass-panel" style="margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.35);">
                <div class="setting-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; color:#34d399;">
                    <span>🔐 درع الأمن السيبراني وحماية الجلسات والسجل الجنائي (Zero-Trust Security)</span>
                    <button type="button" onclick="window.verifyCyberAuditChainLive()" style="background:rgba(16,185,129,0.18); color:#6ee7b7; border:1px solid #10b981; padding:6px 14px; border-radius:10px; font-weight:800; font-size:0.82rem; cursor:pointer;">
                        🔍 فحص سلامة السلسلة التشفيرية الآن (SHA-256)
                    </button>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل التحقق الصارم من الجلسات وبصمة المتصفح (Zero-Trust Session Guard)</span>
                    <label class="switch"><input type="checkbox" id="cfg-cyber-strict-session" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">حظر الدخول التلقائي المؤقت بعد 5 محاولات كلمة مرور خاطئة (Brute-Force Shield)</span>
                    <label class="switch"><input type="checkbox" id="cfg-cyber-bruteforce-lock" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تفعيل التوثيق التشفيري غير القابل للتلاعب لسجل الرقابة (Immutable Audit Hash Chain)</span>
                    <label class="switch"><input type="checkbox" id="cfg-cyber-hash-chain" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">إلزامية رمز المدير (PIN) عند حذف أي مستند مالي أو تعديل فاتورة مرحّلة</span>
                    <label class="switch"><input type="checkbox" id="cfg-cyber-pin-delete" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">منع فتح نفس حساب الكاشير في متصفحين مختلفين في آنٍ واحد</span>
                    <label class="switch"><input type="checkbox" id="cfg-cyber-single-session"><span class="slider"></span></label>
                </div>
            </div>

            <div class="glass-panel" style="margin-bottom: 20px; border: 1px solid rgba(168, 85, 247, 0.35);">
                <div class="setting-title" style="color:#c084fc;">
                    🧠 الهندسة النفسية وراحة المستخدم ومنع الأخطاء البشرية (Cognitive & Psychological UX)
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">وضع الراحة البصرية المعمّق لتقليل إجهاد العين في الورديات الطويلة (Eye-Comfort Contrast)</span>
                    <label class="switch"><input type="checkbox" id="cfg-psych-eye-comfort" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">درع الحماية من الأخطاء غير المقصودة (رسالة تأكيد ذكية قبل الحذف أو التصفير أو الخروج)</span>
                    <label class="switch"><input type="checkbox" id="cfg-psych-mistake-shield" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">التعزيز الإيجابي الصوتي والبصري عند إتمام الحفظ والترحيل بنجاح</span>
                    <label class="switch"><input type="checkbox" id="cfg-psych-sound-feedback" checked><span class="slider"></span></label>
                </div>
                <div class="setting-row">
                    <span class="form-label" style="margin:0;">تقليل العبء الذهني للكاشير (إخفاء الحقول المحاسبية المتقدمة تلقائياً للمستويات المبتدئة)</span>
                    <label class="switch"><input type="checkbox" id="cfg-psych-zen-cashier" checked><span class="slider"></span></label>
                </div>
            </div>
        </div>

        <!-- Tab: Screen Settings (إعدادات الشاشة مستقلة) -->
        <div class="tab-content" id="tab-screen">
            <style>
                .ergo-prof-card{position:relative;padding:14px 16px;border-radius:14px;background:rgba(15,23,42,0.78);border:1.8px solid rgba(148,163,184,0.28);color:#cbd5e1;font-weight:900;cursor:pointer;text-align:right;transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;}
                .ergo-prof-card:hover{transform:translateY(-2px);border-color:#38bdf8;background:rgba(30,41,59,0.95);box-shadow:0 6px 18px rgba(56,189,248,0.2);}
                .ergo-prof-card.is-active{background:linear-gradient(135deg,rgba(14,165,233,0.28),rgba(16,185,129,0.22))!important;border:2.5px solid #38bdf8!important;color:#ffffff!important;box-shadow:0 0 22px rgba(56,189,248,0.42),inset 0 0 12px rgba(56,189,248,0.18)!important;}
                .ergo-prof-card .ergo-act-pill{display:none;background:#10b981;color:#022c22;padding:2px 9px;border-radius:999px;font-size:0.72rem;font-weight:900;}
                .ergo-prof-card.is-active .ergo-act-pill{display:inline-block;}
                .ergo-vb-chip{padding:7px 13px;border-radius:9px;background:rgba(15,23,42,0.8);border:1.5px solid rgba(148,163,184,0.3);color:#cbd5e1;font-weight:900;font-size:0.82rem;cursor:pointer;transition: transform 0.18s, opacity 0.18s, background-color 0.18s, border-color 0.18s, color 0.18s;}
                .ergo-vb-chip:hover{border-color:#38bdf8;color:#fff;}
                .ergo-vb-chip.is-active{background:linear-gradient(135deg,#0284c7,#059669)!important;border-color:#6ee7b7!important;color:#fff!important;box-shadow:0 0 14px rgba(16,185,129,0.4);}
            </style>
            <div class="glass-panel" style="margin-bottom: 18px; padding: 20px; border-radius: 20px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.98)); border: 2px solid #38bdf8;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                    <div>
                        <h3 style="margin:0;color:#38bdf8;font-size:1.24rem;font-weight:900;">🖥️ تصغير وتكبير الشاشة</h3>
                        <p style="margin:4px 0 0;color:#cbd5e1;font-size:0.85rem;font-weight:700;">تحكم سلس لتصغير أو تكبير الشاشة من المنتصف بتناسق تام وموحد عبر كافة شاشات البرنامج والنوافذ المنبثقة.</p>
                    </div>
                    <span id="cfg-screen-step-badge" style="background:rgba(56,189,248,0.18);color:#fde047;border:1.5px solid #38bdf8;padding:6px 14px;border-radius:999px;font-weight:900;font-size:0.88rem;">0 (الوضع الطبيعي 100%)</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <button type="button" onclick="window.setMagaiesScreenZoomStep((parseInt(localStorage.getItem('magaies_screen_zoom_step_v1')||'0',10)||0)-1)" style="padding:8px 16px;border-radius:10px;background:#e11d48;color:#fff;border:none;font-weight:900;cursor:pointer;">➖ تصغير درجة</button>
                    <input type="range" id="cfg-screen-step-range" min="-15" max="10" step="1" value="0" oninput="window.setMagaiesScreenZoomStep(this.value, true)" onchange="window.setMagaiesScreenZoomStep(this.value, false)" style="flex:1;min-width:220px;accent-color:#38bdf8;cursor:pointer;">
                    <button type="button" onclick="window.setMagaiesScreenZoomStep((parseInt(localStorage.getItem('magaies_screen_zoom_step_v1')||'0',10)||0)+1)" style="padding:8px 16px;border-radius:10px;background:#059669;color:#fff;border:none;font-weight:900;cursor:pointer;">➕ تكبير درجة</button>
                    <button type="button" onclick="window.setMagaiesScreenZoomStep(0)" style="padding:8px 14px;border-radius:10px;background:rgba(148,163,184,0.2);color:#f8fafc;border:1px solid rgba(148,163,184,0.4);font-weight:900;cursor:pointer;">↺ الوضع الطبيعي (0)</button>
                </div>
            </div>
            <div class="glass-panel" style="margin-bottom: 20px; padding: 22px; border-radius: 20px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.94), rgba(8, 25, 48, 0.96)); border: 2px solid rgba(56, 189, 248, 0.45);">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.1);">
                    <div>
                        <h3 style="margin:0;color:#38bdf8;font-size:1.25rem;font-weight:900;">📐 استوديو مقاسات الشاشات الموحد وراحة العين (يشمل كافة الأجهزة والشاشات)</h3>
                        <p style="margin:4px 0 0;color:#cbd5e1;font-size:0.86rem;font-weight:700;">انقر على أي خيار ليضيء ويتفعل فوراً على جميع شاشات البرنامج من الألف إلى الياء بنفس المقاس والتناسق.</p>
                    </div>
                    <span id="settings-ergo-live-badge" style="background:rgba(16,185,129,0.22);color:#6ee7b7;border:1.5px solid #10b981;padding:6px 14px;border-radius:999px;font-weight:900;font-size:0.84rem;">✓ نشط وموحد</span>
                </div>
                <div id="settings-ergo-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(225px,1fr));gap:12px;margin-bottom:18px;">
                    <button type="button" class="ergo-prof-card" data-ergo-card="auto" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('auto','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>🤖 تلقائي ذكي (موصى به)</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">يتعرف تلقائياً على اللابتوب والمكتبي والتابلت والطولي</div></button>
                    <button type="button" class="ergo-prof-card" data-ergo-card="laptop" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('laptop','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>💻 مقاس اللابتوب (1480px)</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">زاوية نظر بؤرية مركزة ومريحة بدون أي تمطيط</div></button>
                    <button type="button" class="ergo-prof-card" data-ergo-card="desktop_comfort" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('desktop_comfort','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>🖥️ كمبيوتر مكتبي مريح</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">يعالج ضيق الارتفاع ويوازن العرض على الشاشات المكتبية</div></button>
                    <button type="button" class="ergo-prof-card" data-ergo-card="tablet_touch" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('tablet_touch','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>📲 تابلت وشاشات لمس الكاشير</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">مسافات لمس مريحة وعرض متكامل لأجهزة الآيباد والكاشير</div></button>
                    <button type="button" class="ergo-prof-card" data-ergo-card="portrait_tall" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('portrait_tall','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>📱 شاشة طولية (Portrait)</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">أقصى ارتفاع رأسي للجداول المحاسبية والتقارير الطويلة</div></button>
                    <button type="button" class="ergo-prof-card" data-ergo-card="uhd_4k" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('uhd_4k','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>📺 شاشة كبيرة جداً / 4K</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">اتزان بصري فخم للشاشات العملاقة وعروض الإدارة</div></button>
                    <button type="button" class="ergo-prof-card" data-ergo-card="ultrawide_full" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.setProfile('ultrawide_full','global')"><div style="display:flex;justify-content:space-between;align-items:center;"><span>↔️ ملء العرض الكامل (100%)</span><span class="ergo-act-pill">✓ نشط الآن</span></div><div style="font-size:0.76rem;color:#94a3b8;margin-top:4px;font-weight:700;">امتداد أفقي كامل من الحافة للحافة في كل الشاشات</div></button>
                </div>
            </div>
            <div class="glass-panel" style="padding:18px 20px;border-radius:18px;background:linear-gradient(145deg,rgba(15,23,42,0.95),rgba(2,6,23,0.98));border:1.8px solid #f59e0b;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h3 style="margin:0;color:#fff;font-size:1.1rem;font-weight:900;">🔒 قفل اليومية التلقائي على حركات الأيام السابقة (بكلمة سر المالك)</h3>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <label style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:10px;background:rgba(16,185,129,0.18);border:1.5px solid #10b981;color:#6ee7b7;font-weight:900;cursor:pointer;">
                            <input type="checkbox" id="cfg-lock-prev-days" checked onchange="(function(c){const s=JSON.parse(localStorage.getItem('erp_settings_v1')||'{}');s.lockPreviousDays=c;localStorage.setItem('erp_settings_v1',JSON.stringify(s));if(window.showToast)window.showToast(c?'🔒 تم تفعيل قفل اليومية التلقائي':'🔓 تم إيقاف القفل التلقائي','success',2200);})(this.checked)" style="width:17px;height:17px;accent-color:#10b981;">
                            <span>مفعل تلقائياً ✅</span>
                        </label>
                        <input type="date" id="cfg-acc-lock-date" onchange="(function(v){const s=JSON.parse(localStorage.getItem('erp_settings_v1')||'{}');s.accountingLockDate=v;localStorage.setItem('erp_settings_v1',JSON.stringify(s));})(this.value)" style="padding:5px 10px;border-radius:8px;border:1px solid #f59e0b;background:#020617;color:#fff;font-weight:800;">
                    </div>
                </div>
            </div>
            <script>
                window.renderSettingsErgoStudio = function() {
                    try {
                        var mode = localStorage.getItem('magaies_ergo_global_mode') || 'auto';
                        var vb = parseInt(localStorage.getItem('magaies_ergo_vboost_px') || '0', 10) || 0;
                        document.querySelectorAll('[data-ergo-card]').forEach(function(btn) {
                            btn.classList.toggle('is-active', btn.getAttribute('data-ergo-card') === mode);
                        });
                        document.querySelectorAll('[data-vb-val]').forEach(function(ch) {
                            ch.classList.toggle('is-active', parseInt(ch.getAttribute('data-vb-val'), 10) === vb);
                        });
                        var rng = document.getElementById('set-vboost-range'), lbl = document.getElementById('set-vboost-val');
                        if (rng) rng.value = String(vb);
                        if (lbl) lbl.textContent = '+' + vb + 'px';
                        var s = parseInt(localStorage.getItem('magaies_screen_zoom_step_v1') || '0', 10) || 0;
                        var zRng = document.getElementById('cfg-screen-step-range'), zBdg = document.getElementById('cfg-screen-step-badge');
                        if (zRng) zRng.value = String(s);
                        if (zBdg) zBdg.textContent = (s > 0 ? ('+' + s + ' تكبير') : (s < 0 ? (s + ' تصغير') : '0 (الوضع الطبيعي 100%)')) + ' — ' + Math.round((1 + s * 0.02) * 100) + '%';
                    } catch (_) {}
                };
                window.setSettingsVerticalBoostUI = function(px) {
                    var v = Math.max(0, Math.min(24, parseInt(px, 10) || 0));
                    if (window.MagaiesScreenErgonomics) window.MagaiesScreenErgonomics.setVerticalBoost(v);
                    else localStorage.setItem('magaies_ergo_vboost_px', String(v));
                    window.renderSettingsErgoStudio();
                };
                (function(){try{const s=JSON.parse(localStorage.getItem('erp_settings_v1')||'{}');const c=document.getElementById('cfg-lock-prev-days'),d=document.getElementById('cfg-acc-lock-date');if(c)c.checked=(s.lockPreviousDays!==false);if(d&&s.accountingLockDate)d.value=s.accountingLockDate;window.renderSettingsErgoStudio();}catch(_){}})();
            </script>
        </div>

        <!-- Tab: Fonts Only (إعدادات الخطوط مستقلة) -->
        <div class="tab-content" id="tab-fonts">
            <!-- 1. 4 Fonts Selection Panel -->
            <div class="glass-panel" style="margin-bottom: 24px; padding: 28px; border-radius: 20px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(2, 6, 23, 0.96)); border-right: 4px solid #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); box-shadow: 0 12px 35px rgba(0,0,0,0.45);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.3)); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; border: 1.5px solid rgba(56, 189, 248, 0.45);">🔤</div>
                        <div>
                            <h3 style="margin: 0; color: #f1f5f9; font-size: 1.35rem; font-weight: 800;">الخطوط وحجم النص والعرض (Ophthalmic Typography Studio)</h3>
                            <p style="margin: 5px 0 0; color: #94a3b8; font-size: 0.92rem;">اختر من بين 4 خطوط معتمدة لراحة العين وتباين الأرقام المحاسبية على كافة الشاشات (يُحفظ تلقائياً في المتصفح)</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.MagaieesTypography && window.MagaieesTypography.resetDefaults()" style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.2), rgba(225, 29, 72, 0.3)); color: #fda4af; border: 1.5px solid rgba(244, 63, 94, 0.5); padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        🔄 إعادة الضبط للوضع الافتراضي
                    </button>
                </div>

                <!-- 4 Font Cards Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 18px;">
                    
                    <!-- Option 1: Default Current Font -->
                    <div data-magaiees-font-card="default" onclick="window.MagaieesTypography && window.MagaieesTypography.setFont('default')" style="cursor: pointer; padding: 20px; border-radius: 16px; background: rgba(15, 23, 42, 0.78); border: 2px solid #38bdf8; transition: all 0.25s ease; position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="font-radio-indicator" style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #38bdf8; background: #38bdf8; display: inline-block; flex-shrink: 0;"></span>
                                    <span style="font-size: 1.08rem; font-weight: 800; color: #f1f5f9;">1. الخط الافتراضي (الحالي)</span>
                                </div>
                                <span class="font-active-badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800;">✓ مفعل</span>
                            </div>
                            <p style="margin: 0 0 12px; color: #94a3b8; font-size: 0.85rem; line-height: 1.55;">
                                يترك البرنامج بوضعه وخطه وأوزانه الحالية كما هي تماماً دون أي تعديل.
                            </p>
                        </div>
                        <div class="font-preview-sample" style="font-family: 'Tajawal', sans-serif !important; background: rgba(2, 6, 23, 0.65); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); color: #f1f5f9;">
                            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 4px;">كشف حساب المبيعات والميزانية</div>
                            <div style="font-weight: 500; font-size: 0.9rem; color: #38bdf8; font-variant-numeric: tabular-nums;">1,485,920.50 SDG | 2,450.00 USD</div>
                        </div>
                    </div>

                    <!-- Option 2: IBM Plex Sans Arabic -->
                    <div data-magaiees-font-card="ibm_plex" onclick="window.MagaieesTypography && window.MagaieesTypography.setFont('ibm_plex')" style="cursor: pointer; padding: 20px; border-radius: 16px; background: rgba(15, 23, 42, 0.78); border: 2px solid rgba(255,255,255,0.12); transition: all 0.25s ease; position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="font-radio-indicator" style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #64748b; background: transparent; display: inline-block; flex-shrink: 0;"></span>
                                    <span style="font-size: 1.08rem; font-weight: 800; color: #f1f5f9;">2. خط IBM Plex Sans Arabic</span>
                                </div>
                                <span class="font-active-badge" style="display: none; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800;">✓ مفعل</span>
                            </div>
                            <p style="margin: 0 0 12px; color: #94a3b8; font-size: 0.85rem; line-height: 1.55;">
                                خط هندسي مؤسسي من Google Fonts بأوزانه الكاملة (300, 400, 500, 600, 700) — مثالي للجداول المحاسبية الكثيفة وراحة العين لساعات طويلة.
                            </p>
                        </div>
                        <div class="font-preview-sample" style="font-family: 'IBM Plex Sans Arabic', sans-serif !important; background: rgba(2, 6, 23, 0.65); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); color: #f1f5f9;">
                            <div style="font-family: 'IBM Plex Sans Arabic', sans-serif !important; font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;">كشف حساب المبيعات والميزانية</div>
                            <div style="font-family: 'IBM Plex Sans Arabic', sans-serif !important; font-weight: 500; font-size: 0.9rem; color: #38bdf8; font-variant-numeric: tabular-nums;">1,485,920.50 SDG | 2,450.00 USD</div>
                        </div>
                    </div>

                    <!-- Option 3: Readex Pro -->
                    <div data-magaiees-font-card="readex_pro" onclick="window.MagaieesTypography && window.MagaieesTypography.setFont('readex_pro')" style="cursor: pointer; padding: 20px; border-radius: 16px; background: rgba(15, 23, 42, 0.78); border: 2px solid rgba(255,255,255,0.12); transition: all 0.25s ease; position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="font-radio-indicator" style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #64748b; background: transparent; display: inline-block; flex-shrink: 0;"></span>
                                    <span style="font-size: 1.08rem; font-weight: 800; color: #f1f5f9;">3. خط Readex Pro</span>
                                </div>
                                <span class="font-active-badge" style="display: none; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800;">✓ مفعل</span>
                            </div>
                            <p style="margin: 0 0 12px; color: #94a3b8; font-size: 0.85rem; line-height: 1.55;">
                                مصمم بصرياً بتجويفات حروف مفتوحة بأوزانه الكاملة (300, 400, 500, 600, 700) — مثالي لمن يعمل بدون نظارة طبية وللشاشات الصغيرة.
                            </p>
                        </div>
                        <div class="font-preview-sample" style="font-family: 'Readex Pro', sans-serif !important; background: rgba(2, 6, 23, 0.65); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); color: #f1f5f9;">
                            <div style="font-family: 'Readex Pro', sans-serif !important; font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;">كشف حساب المبيعات والميزانية</div>
                            <div style="font-family: 'Readex Pro', sans-serif !important; font-weight: 500; font-size: 0.9rem; color: #38bdf8; font-variant-numeric: tabular-nums;">1,485,920.50 SDG | 2,450.00 USD</div>
                        </div>
                    </div>

                    <!-- Option 4: Almarai -->
                    <div data-magaiees-font-card="almarai" onclick="window.MagaieesTypography && window.MagaieesTypography.setFont('almarai')" style="cursor: pointer; padding: 20px; border-radius: 16px; background: rgba(15, 23, 42, 0.78); border: 2px solid rgba(255,255,255,0.12); transition: all 0.25s ease; position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="font-radio-indicator" style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid #64748b; background: transparent; display: inline-block; flex-shrink: 0;"></span>
                                    <span style="font-size: 1.08rem; font-weight: 800; color: #f1f5f9;">4. خط Almarai (المراعي)</span>
                                </div>
                                <span class="font-active-badge" style="display: none; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800;">✓ مفعل</span>
                            </div>
                            <p style="margin: 0 0 12px; color: #94a3b8; font-size: 0.85rem; line-height: 1.55;">
                                تباين حاد ووضوح فائق من مسافة بعيدة بأوزانه الكاملة (300, 400, 700, 800) — يعالج الشاشات التعبانة ذات الإضاءة أو الدقة الضعيفة.
                            </p>
                        </div>
                        <div class="font-preview-sample" style="font-family: 'Almarai', sans-serif !important; background: rgba(2, 6, 23, 0.65); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); color: #f1f5f9;">
                            <div style="font-family: 'Almarai', sans-serif !important; font-weight: 800; font-size: 0.95rem; margin-bottom: 4px;">كشف حساب المبيعات والميزانية</div>
                            <div style="font-family: 'Almarai', sans-serif !important; font-weight: 700; font-size: 0.9rem; color: #38bdf8; font-variant-numeric: tabular-nums;">1,485,920.50 SDG | 2,450.00 USD</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2. Font Scaling Tool (أداة تكبير وتصغير حجم الخط) -->
            <div class="glass-panel" style="margin-bottom: 24px; padding: 28px; border-radius: 20px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(2, 6, 23, 0.96)); border-right: 4px solid #10b981; border: 1px solid rgba(16, 185, 129, 0.3); box-shadow: 0 12px 35px rgba(0,0,0,0.45);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.18); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1px solid rgba(16, 185, 129, 0.4);">🔍</div>
                        <div>
                            <h3 style="margin: 0; color: #f1f5f9; font-size: 1.25rem; font-weight: 800;">أداة تكبير وتصغير حجم الخط العام (Font Scaling)</h3>
                            <p style="margin: 4px 0 0; color: #94a3b8; font-size: 0.9rem;">تحكم ديناميكي سلس بحجم النصوص والجداول والأزرار في كامل البرنامج دون تكسر الحدود أو اختفاء النصوص</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; background: rgba(2, 6, 23, 0.8); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.35);">
                        <span style="color: #94a3b8; font-size: 0.85rem;">المستوى الحالي:</span>
                        <span id="magaiees-font-scale-val" style="color: #38bdf8; font-weight: 900; font-size: 1.2rem; font-variant-numeric: tabular-nums;">100%</span>
                        <span style="color: #64748b;">|</span>
                        <span id="magaiees-font-scale-px" style="color: #34d399; font-weight: 800; font-size: 0.95rem; font-variant-numeric: tabular-nums;">16px</span>
                    </div>
                </div>

                <!-- Slider + Plus/Minus Buttons -->
                <div style="display: flex; align-items: center; gap: 14px; background: rgba(2, 6, 23, 0.6); padding: 16px 20px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 18px;">
                    <button type="button" onclick="window.MagaieesTypography && window.MagaieesTypography.stepScale(-5)" title="تصغير الخط (-5%)" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.18); color: #f1f5f9; font-size: 1.35rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center;">−</button>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 6px;">
                        <input type="range" id="magaiees-font-scale-slider" min="80" max="150" step="1" value="100" oninput="window.MagaieesTypography && window.MagaieesTypography.setScale(this.value, true)" onchange="window.MagaieesTypography && window.MagaieesTypography.setScale(this.value, false)" style="width: 100%; accent-color: #38bdf8; cursor: pointer; height: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: #94a3b8; font-variant-numeric: tabular-nums;">
                            <span>80%</span><span>90% (مدمج)</span><span>100% (قياسي)</span><span>115% (كبير)</span><span>130% (واضح جداً)</span><span>150% (فائق)</span>
                        </div>
                    </div>
                    <button type="button" onclick="window.MagaieesTypography && window.MagaieesTypography.stepScale(5)" title="تكبير الخط (+5%)" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.18); border: 1px solid rgba(56, 189, 248, 0.45); color: #38bdf8; font-size: 1.35rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
                </div>

                <!-- Master Weight Control (سماكة الخط العام: عادي أو تخين) -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: rgba(2, 6, 23, 0.55); padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 16px;">
                    <span style="color: #e2e8f0; font-weight: 800; font-size: 0.92rem;">⚖️ سماكة الخط العام (عادي / تخين لكافة الشاشات):</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button type="button" data-mg-master-weight-btn="auto" onclick="window.MagaieesTypography && window.MagaieesTypography.setMasterWeight('auto')" style="padding: 7px 14px; border-radius: 9px; border: 1px solid #38bdf8; background: linear-gradient(135deg, #0284c7, #2563eb); color: #fff; font-weight: 700; font-size: 0.84rem; cursor: pointer;">تلقائي متوازن</button>
                        <button type="button" data-mg-master-weight-btn="400" onclick="window.MagaieesTypography && window.MagaieesTypography.setMasterWeight('400')" style="padding: 7px 14px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.14); background: rgba(15,23,42,0.78); color: #cbd5e1; font-weight: 400; font-size: 0.84rem; cursor: pointer;">عادي (Regular 400)</button>
                        <button type="button" data-mg-master-weight-btn="500" onclick="window.MagaieesTypography && window.MagaieesTypography.setMasterWeight('500')" style="padding: 7px 14px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.14); background: rgba(15,23,42,0.78); color: #cbd5e1; font-weight: 500; font-size: 0.84rem; cursor: pointer;">متوسط طبي (Medium 500)</button>
                        <button type="button" data-mg-master-weight-btn="700" onclick="window.MagaieesTypography && window.MagaieesTypography.setMasterWeight('700')" style="padding: 7px 14px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.14); background: rgba(15,23,42,0.78); color: #cbd5e1; font-weight: 700; font-size: 0.84rem; cursor: pointer;">تخين (Bold 700)</button>
                        <button type="button" data-mg-master-weight-btn="800" onclick="window.MagaieesTypography && window.MagaieesTypography.setMasterWeight('800')" style="padding: 7px 14px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.14); background: rgba(15,23,42,0.78); color: #cbd5e1; font-weight: 800; font-size: 0.84rem; cursor: pointer;">تخين جداً (Extra-Bold 800)</button>
                    </div>
                </div>

                <!-- Quick Preset Levels (مستويات سريعة) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px;">
                    <button type="button" data-magaiees-scale-preset="90" onclick="window.MagaieesTypography && window.MagaieesTypography.setScale(90)" style="padding: 12px 16px; border-radius: 12px; background: rgba(15, 23, 42, 0.75); border: 1.5px solid rgba(255,255,255,0.15); color: #cbd5e1; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                        🔹 صغير (90%)
                    </button>
                    <button type="button" data-magaiees-scale-preset="100" onclick="window.MagaieesTypography && window.MagaieesTypography.setScale(100)" style="padding: 12px 16px; border-radius: 12px; background: linear-gradient(135deg, #0284c7, #2563eb); border: 1.5px solid #38bdf8; color: #ffffff; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                        ✅ قياسي (100%)
                    </button>
                    <button type="button" data-magaiees-scale-preset="115" onclick="window.MagaieesTypography && window.MagaieesTypography.setScale(115)" style="padding: 12px 16px; border-radius: 12px; background: rgba(15, 23, 42, 0.75); border: 1.5px solid rgba(255,255,255,0.15); color: #cbd5e1; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                        🔸 كبير (115%)
                    </button>
                    <button type="button" data-magaiees-scale-preset="130" onclick="window.MagaieesTypography && window.MagaieesTypography.setScale(130)" style="padding: 12px 16px; border-radius: 12px; background: rgba(15, 23, 42, 0.75); border: 1.5px solid rgba(255,255,255,0.15); color: #cbd5e1; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                        👓 كبير جداً لضعاف النظر (130%)
                    </button>
                </div>
            </div>

            <!-- 2.5 Professor's Clinical & Psychological Presets + 7-Level Architectural Typography Studio -->
            <?php
            $mgPresets = [
                ['id'=>'standard_golden','icon'=>'⚖️','title'=>'1. التوازن الذهبي القياسي (100%)','desc'=>'تدرج بصري متناغم من العناوين العلوية (105% تخين) إلى التفاصيل والجداول (100% متوسط) لراحة ذهنية فورية.'],
                ['id'=>'ophthalmic_comfort','icon'=>'🩺','title'=>'2. روشتة الطب البصري لعدم إجهاد العين','desc'=>'توزيع خط Readex Pro للتفاصيل والشريط مع IBM Plex للجداول بوزن متوسط مريح (500) يمنع إجهاد عضلة العين.'],
                ['id'=>'accounting_bold','icon'=>'👓','title'=>'3. التباين المحاسبي التخين (بدون نظارة)','desc'=>'خط المراعي Almarai العريض (Bold 700/800) للعناوين والشريط والمنبثق مع تكبير متدرج واضح للشاشات البعيدة.'],
                ['id'=>'laptop_compact','icon'=>'💻','title'=>'4. العرض البانورامي المدمج لشاشات اللابتوب','desc'=>'خط IBM Plex بهندسة رشيقة (96%) للشريط الجانبي والجداول لاحتواء أكبر قدر من الأعمدة المحاسبية دون ازدحام.']
            ];
            $mgLevels = [
                ['key'=>'sidebar','icon'=>'🧭','name'=>'المستوى 1: الشريط الجانبي الرئيسي (Sidebar)','base'=>15,'color'=>'#38bdf8','desc'=>'يتحكم في قوائم الشريط الجانبي الأيمن وشعار النظام في كل الشاشات.'],
                ['key'=>'flyout','icon'=>'🚀','name'=>'المستوى 2: الشريط المنبثق من الجانبي (Panoramic Flyout)','base'=>14,'color'=>'#a855f7','desc'=>'يتحكم في البطاقات والاختصارات المنبثقة عند المرور على الشريط الجانبي.'],
                ['key'=>'titles','icon'=>'👑','name'=>'المستوى 3: عناوين الشاشات والأقسام (كالمخازن والموردين واللوحة الرئيسية)','base'=>22,'color'=>'#f59e0b','desc'=>'يتحكم في العناوين العلوية، عناوين البطاقات (🏢 المخازن، 🏭 الموردين)، والتبويبات الرئيسية.'],
                ['key'=>'modals','icon'=>'🪟','name'=>'المستوى 4: الشاشات والنوافذ المنبثقة والفرعية (Modals & Popups)','base'=>16,'color'=>'#ec4899','desc'=>'يتحكم في جميع النوافذ المنبثقة، بطاقات الإضافة السريعة، وحوارات التأكيد.'],
                ['key'=>'details','icon'=>'📝','name'=>'المستوى 5: التفاصيل الداخلية وحقول البيانات والأزرار','base'=>15,'color'=>'#10b981','desc'=>'يتحكم في الحقول الداخلية للمخازن والموردين والأصناف والفواتير والتسميات والأزرار.'],
                ['key'=>'tables','icon'=>'📊','name'=>'المستوى 6: الجداول المحاسبية، الفواتير، والتقارير المالية','base'=>15,'color'=>'#06b6d4','desc'=>'يتحكم في رؤوس وخلايا الجداول، كشوفات الحساب، الأرقام المحاسبية، والإجماليات.'],
                ['key'=>'mobile','icon'=>'📱','name'=>'المستوى 7: شاشات الجوال والتابلت (Mobile Suite & Responsive)','base'=>16,'color'=>'#8b5cf6','desc'=>'يعمم الضبط ويتحكم في مقياس وسماكة ونوع الخط على كافة شاشات الجوال.']
            ];
            ?>
            <div class="glass-panel" style="margin-bottom: 24px; padding: 26px; border-radius: 20px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.94), rgba(2, 6, 23, 0.98)); border-right: 4px solid #a855f7; border: 1px solid rgba(168, 85, 247, 0.32);">
                <div style="margin-bottom: 18px;">
                    <h3 style="margin: 0; color: #f1f5f9; font-size: 1.25rem; font-weight: 800;">🩺 روشتات البروفيسور الطبية والنفسية + التحكم الهرمي على 7 مستويات (من الأعلى للأسفل ومن الأساس للجانبي)</h3>
                    <p style="margin: 4px 0 0; color: #94a3b8; font-size: 0.9rem;">اختر روشتة جاهزة بضغطة زر أو خصص كل مستوى في الشاشة (الشريط الجانبي، المنبثق، العناوين، النوافذ، التفاصيل، الجداول، والجوال) بنوع خط ومقاس وسماكة (عادي / تخين) مستقلة.</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; margin-bottom: 22px;">
                    <?php foreach ($mgPresets as $pr): ?>
                    <div data-mg-clinical-preset="<?= $pr['id'] ?>" onclick="window.MagaieesTypography && window.MagaieesTypography.applyClinicalPreset('<?= $pr['id'] ?>')" style="cursor: pointer; padding: 14px 16px; border-radius: 14px; background: rgba(15, 23, 42, 0.85); border: 1.5px solid rgba(255,255,255,0.12); transition: all 0.2s;">
                        <div style="font-weight: 800; color: #38bdf8; font-size: 0.95rem; margin-bottom: 5px;"><?= $pr['icon'] ?> <?= $pr['title'] ?></div>
                        <div style="font-size: 0.8rem; color: #cbd5e1; line-height: 1.5;"><?= $pr['desc'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 16px;">
                    <?php foreach ($mgLevels as $lv): $k = $lv['key']; ?>
                    <div style="padding: 16px; border-radius: 16px; background: rgba(2, 6, 23, 0.72); border: 1px solid rgba(255,255,255,0.1); border-right: 4px solid <?= $lv['color'] ?>; display: flex; flex-direction: column; gap: 11px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <div style="font-weight: 800; color: #f8fafc; font-size: 0.98rem;"><?= $lv['icon'] ?> <?= $lv['name'] ?></div>
                            <button type="button" onclick="window.MagaieesTypography && window.MagaieesTypography.resetLevel('<?= $k ?>')" title="إعادة ضبط هذا المستوى (100%)" style="padding: 3px 9px; border-radius: 8px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #94a3b8; font-size: 0.75rem; cursor: pointer;">↺ 100%</button>
                        </div>
                        <div style="font-size: 0.78rem; color: #94a3b8;"><?= $lv['desc'] ?></div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; background: rgba(15,23,42,0.75); padding: 7px 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.07);">
                            <span style="font-size: 0.8rem; color: #cbd5e1; font-weight: 700;">🔤 نوع الخط للمستوى:</span>
                            <select id="mg-lvl-font-<?= $k ?>" onchange="window.MagaieesTypography && window.MagaieesTypography.setLevelFont('<?= $k ?>', this.value)" style="background: #020617; color: #38bdf8; border: 1px solid rgba(56,189,248,0.4); border-radius: 8px; padding: 4px 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                                <option value="inherit">🔗 مرتبط بالخط العام (تلقائي)</option><option value="default">1. الخط الافتراضي (Tajawal)</option><option value="ibm_plex">2. خط IBM Plex Sans Arabic</option><option value="readex_pro">3. خط Readex Pro الطبي</option><option value="almarai">4. خط Almarai (المراعي)</option>
                            </select>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" onclick="window.MagaieesTypography && window.MagaieesTypography.stepLevelScale('<?= $k ?>', -4)" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16); color: #fff; font-weight: 900; cursor: pointer;">−</button>
                            <input type="range" id="mg-lvl-slider-<?= $k ?>" min="75" max="150" step="1" value="100" oninput="window.MagaieesTypography && window.MagaieesTypography.setLevelScale('<?= $k ?>', this.value, true)" onchange="window.MagaieesTypography && window.MagaieesTypography.setLevelScale('<?= $k ?>', this.value, false)" style="flex: 1; accent-color: <?= $lv['color'] ?>; cursor: pointer;">
                            <button type="button" onclick="window.MagaieesTypography && window.MagaieesTypography.stepLevelScale('<?= $k ?>', 4)" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56,189,248,0.18); border: 1px solid rgba(56,189,248,0.4); color: #38bdf8; font-weight: 900; cursor: pointer;">+</button>
                            <span id="mg-lvl-val-<?= $k ?>" style="min-width: 44px; text-align: center; font-weight: 900; color: #38bdf8; font-size: 0.88rem;">100%</span>
                            <span id="mg-lvl-px-<?= $k ?>" style="min-width: 44px; text-align: center; font-weight: 800; color: #34d399; font-size: 0.8rem;"><?= $lv['base'] ?>px</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; flex-wrap: wrap;">
                            <?php foreach ([['auto','تلقائي'],['400','عادي'],['500','متوسط'],['700','تخين Bold'],['800','تخين جداً']] as $wb): ?>
                            <button type="button" data-mg-lvl-weight-btn="<?= $k ?>" data-weight="<?= $wb[0] ?>" onclick="window.MagaieesTypography && window.MagaieesTypography.setLevelWeight('<?= $k ?>', '<?= $wb[0] ?>')" style="flex: 1; padding: 5px 6px; border-radius: 7px; border: 1px solid rgba(255,255,255,0.14); background: rgba(2,6,23,0.65); color: #94a3b8; font-size: 0.76rem; font-weight: <?= $wb[0]==='auto'?'700':$wb[0] ?>; cursor: pointer;"><?= $wb[1] ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Live 7-Level Screen Architecture Simulator -->
                <div id="mg-live-hierarchy-sim" style="margin-top: 20px; padding: 16px; border-radius: 14px; background: rgba(2, 6, 23, 0.85); border: 1px solid rgba(56, 189, 248, 0.3); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <div style="padding: 10px; border-radius: 10px; background: rgba(15,23,42,0.9); border-right: 3px solid #38bdf8;"><div style="font-size:0.72rem;color:#64748b;">🧭 معاينة الجانبي:</div><div data-mg-sim-zone="sidebar" style="color:#f1f5f9;">📦 البيانات الأساسية والمخازن</div></div>
                    <div style="padding: 10px; border-radius: 10px; background: rgba(15,23,42,0.9); border-right: 3px solid #a855f7;"><div style="font-size:0.72rem;color:#64748b;">🚀 معاينة المنبثق الجانبي:</div><div data-mg-sim-zone="flyout" style="color:#e9d5ff;">⚡ اختصار سريع: كشف الموردين</div></div>
                    <div style="padding: 10px; border-radius: 10px; background: rgba(15,23,42,0.9); border-right: 3px solid #f59e0b;"><div style="font-size:0.72rem;color:#64748b;">👑 معاينة عنوان الشاشة:</div><div data-mg-sim-zone="titles" style="color:#fde68a;">🏢 إدارة المخازن والموردين</div></div>
                    <div style="padding: 10px; border-radius: 10px; background: rgba(15,23,42,0.9); border-right: 3px solid #ec4899;"><div style="font-size:0.72rem;color:#64748b;">🪟 معاينة النافذة المنبثقة:</div><div data-mg-sim-zone="modals" style="color:#fbcfe8;">➕ نافذة إضافة مخزن / مورد</div></div>
                    <div style="padding: 10px; border-radius: 10px; background: rgba(15,23,42,0.9); border-right: 3px solid #10b981;"><div style="font-size:0.72rem;color:#64748b;">📝 معاينة التفاصيل والحقول:</div><div data-mg-sim-zone="details" style="color:#a7f3d0;">اسم المخزن: مستودع السيطرة الرئيسي</div></div>
                </div>
            </div>
            <!-- 3. Live Optical & Financial Statement Preview (معاينة حية للأوزان والأرقام المحاسبية) -->
            <div class="glass-panel" style="padding: 24px; border-radius: 20px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.12);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
                    <h4 style="margin: 0; color: #f1f5f9;">👁️ معاينة بصرية فورية لتوزيع الأوزان والمحاذاة المسطرية للأرقام (Tabular Nums)</h4>
                    <span style="font-size: 0.82rem; color: #34d399; background: rgba(16, 185, 129, 0.12); padding: 4px 12px; border-radius: 999px; border: 1px solid rgba(16, 185, 129, 0.3);">تباين مريح للعين #F1F5F9 + تنعيم الحواف Antialiased</span>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; background: rgba(2, 6, 23, 0.55); border-radius: 12px; overflow: hidden;">
                        <thead>
                            <tr style="background: rgba(56, 189, 248, 0.12); border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <th style="padding: 12px; text-align: right; color: #bae6fd;">البيان والحساب المحاسبي</th>
                                <th style="padding: 12px; text-align: right; color: #bae6fd;">رقم السند</th>
                                <th style="padding: 12px; text-align: left; color: #bae6fd;">مدين (Debit)</th>
                                <th style="padding: 12px; text-align: left; color: #bae6fd;">دائن (Credit)</th>
                                <th style="padding: 12px; text-align: left; color: #bae6fd;">الرصيد المتراكم</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <td style="padding: 10px 12px;">توريد بضاعة مبيعات نقدية — فرع الخرطوم الرئيسي</td>
                                <td style="padding: 10px 12px;" class="num">RV-2026-0841</td>
                                <td style="padding: 10px 12px; text-align: left; color: #34d399;" class="num">14,850,000.00</td>
                                <td style="padding: 10px 12px; text-align: left; color: #94a3b8;" class="num">0.00</td>
                                <td style="padding: 10px 12px; text-align: left; color: #f1f5f9;" class="num">14,850,000.00</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <td style="padding: 10px 12px;">سداد دفعة موردين عبر تطبيق بنكك (BOK)</td>
                                <td style="padding: 10px 12px;" class="num">PV-2026-0192</td>
                                <td style="padding: 10px 12px; text-align: left; color: #94a3b8;" class="num">0.00</td>
                                <td style="padding: 10px 12px; text-align: left; color: #f87171;" class="num">4,200,500.75</td>
                                <td style="padding: 10px 12px; text-align: left; color: #f1f5f9;" class="num">10,649,499.25</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background: rgba(16, 185, 129, 0.12); border-top: 2px solid rgba(16, 185, 129, 0.35);">
                                <td colspan="2" style="padding: 12px; color: #fde68a;">الإجمالي الصافي المطابق</td>
                                <td style="padding: 12px; text-align: left; color: #34d399;" class="num">14,850,000.00</td>
                                <td style="padding: 12px; text-align: left; color: #f87171;" class="num">4,200,500.75</td>
                                <td style="padding: 12px; text-align: left; color: #fde68a;" class="num">10,649,499.25</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Manager & Staff 12 WhatsApp Alerts -->
        <div class="tab-content" id="tab-manager-alerts">
            <div class="glass-panel" style="padding: 24px; border-radius: 20px; background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(2, 6, 23, 0.96)); border: 2px solid rgba(16, 185, 129, 0.45); box-shadow: 0 12px 35px rgba(0,0,0,0.4);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:14px;">
                    <div>
                        <h3 style="margin:0; color:#34d399; font-size:1.3rem; font-weight:900;">👔 أرقام واتساب الإدارة والموظفين (4 مدراء + 8 موظفين = 12 رقماً)</h3>
                        <p style="margin:5px 0 0; color:#cbd5e1; font-size:0.9rem; font-weight:700;">أدخل أرقام الواتساب مع مفتاح الدولة (مثل: <code style="color:#fde68a;">249912345678</code>). في شاشة التنبيهات ستظهر الأرقام من <b>1 إلى 12</b> وتحت كل رقم علامة صح ☑️ لاختيار المستلمين لكل تنبيه.</p>
                    </div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <a href="alarm.php" style="padding:10px 16px; border-radius:12px; background:rgba(244,63,94,0.18); border:1.5px solid rgba(244,63,94,0.5); color:#fda4af; font-weight:900; text-decoration:none; font-size:0.88rem;">🚨 فتح شاشة توزيع التنبيهات (1–12) ↗</a>
                        <button type="button" onclick="window.saveManagerAlerts12()" style="padding:10px 22px; border-radius:12px; background:linear-gradient(135deg,#10b981,#059669); border:none; color:#fff; font-weight:900; cursor:pointer; font-size:0.95rem; box-shadow:0 6px 18px rgba(16,185,129,0.35);">💾 حفظ الأرقام الـ 12</button>
                    </div>
                </div>
                <h4 style="margin:0 0 10px; color:#fde68a; font-size:1rem; font-weight:900;">👑 أولاً: أرقام المدراء الأربعة (1 – 4)</h4>
                <div id="mgr-slots-1-4" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:12px; margin-bottom:20px;"></div>
                <h4 style="margin:0 0 10px; color:#38bdf8; font-size:1rem; font-weight:900;">👥 ثانياً: أرقام الموظفين الثمانية (5 – 12)</h4>
                <div id="mgr-slots-5-12" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:12px;"></div>
            </div>
        </div>

        <!-- Tab: UI -->
        <div class="tab-content" id="tab-ui">
            <!-- Language Card -->
            <div class="glass-panel" style="margin-bottom: 25px; padding: 30px; border-radius: 20px; background: linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9)); border-left: 4px solid #3b82f6; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 1px solid rgba(59, 130, 246, 0.3);">🌐</div>
                    <div>
                        <h3 style="margin: 0; color: #fff; font-size: 1.3rem; font-weight: 800;" data-i18n="settings.langTitle">إعدادات اللغة (Language)</h3>
                        <p style="margin: 5px 0 0; color: #94a3b8; font-size: 0.95rem;" data-i18n="settings.selectLang">اختر لغة واجهة النظام المفضلة لديك</p>
                    </div>
                </div>
                <div style="display: flex; gap: 20px;">
                    <button type="button" id="lang-btn-ar" onclick="switchLanguage('ar')" class="action-btn" style="flex:1; padding: 18px; font-size: 1.2rem; font-weight: 800; border-radius: 16px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); transition: all 0.3s; cursor: pointer; color: #fff;">
                        🇸🇦 العربية
                    </button>
                    <button type="button" id="lang-btn-en" onclick="switchLanguage('en')" class="action-btn" style="flex:1; padding: 18px; font-size: 1.2rem; font-weight: 800; border-radius: 16px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); transition: all 0.3s; cursor: pointer; color: #fff;">
                        🇬🇧 English
                    </button>
                </div>
            </div>

            <!-- System Settings Card -->
            <div class="glass-panel" style="padding: 30px; border-radius: 20px; background: linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9)); border-left: 4px solid #10b981; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
                    <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">⚙️</div>
                    <div>
                        <h3 style="margin: 0; color: #fff; font-size: 1.3rem; font-weight: 800;" data-i18n="settings.uiTitle">ضبط النظام والمظهر (System Settings)</h3>
                        <p style="margin: 5px 0 0; color: #94a3b8; font-size: 0.95rem;">تخصيص الواجهة، أحجام الخطوط، وإعدادات الأمان</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" data-i18n="settings.fontSize" style="color: #cbd5e1; font-weight: 800; margin-bottom: 10px; display: block;">حجم الخط الرئيسي للنظام</label>
                        <select class="form-select" id="cfg-font-size" onchange="previewFontSize(this.value)" style="width: 100%; padding: 14px; border-radius: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 1rem; outline: none;">
                            <option value="14" data-i18n="settings.fontVerySmall">صغير جداً (14px)</option>
                            <option value="16" selected data-i18n="settings.fontMedium">متوسط (16px - افتراضي)</option>
                            <option value="18" data-i18n="settings.fontLarge">كبير (18px)</option>
                            <option value="20" data-i18n="settings.fontVeryLarge">كبير جداً (20px)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" data-i18n="settings.autoLogout" style="color: #cbd5e1; font-weight: 800; margin-bottom: 10px; display: block;">مدة الخروج التلقائي للأمان</label>
                        <select class="form-select" id="cfg-auto-logout" style="width: 100%; padding: 14px; border-radius: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: #fff; font-size: 1rem; outline: none;">
                            <option value="0" selected>بدون (معطل)</option>
                            <option value="30">30 ثانية</option>
                            <option value="60">1 دقيقة</option>
                            <option value="300">5 دقائق</option>
                            <option value="600">10 دقائق</option>
                            <option value="1800">30 دقيقة</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 30px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <div class="setting-row" style="background: rgba(255,255,255,0.03); padding: 18px 25px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.05);">
                        <span class="form-label" style="margin:0; font-size: 1.05rem; color: #fff; font-weight: bold;" data-i18n="settings.autoBackup">النسخ الاحتياطي التلقائي اليومي</span>
                        <label class="switch"><input type="checkbox" id="cfg-auto-backup" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row" style="background: rgba(255,255,255,0.03); padding: 18px 25px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.05);">
                        <span class="form-label" style="margin:0; font-size: 1.05rem; color: #fff; font-weight: bold;" data-i18n="settings.autoDark">تفعيل الوضع التلقائي الغامق</span>
                        <label class="switch"><input type="checkbox" id="cfg-auto-dark" checked><span class="slider"></span></label>
                    </div>
                    <div class="setting-row" style="background: rgba(255,255,255,0.03); padding: 18px 25px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.05);">
                        <span class="form-label" style="margin:0; font-size: 1.05rem; color: #fff; font-weight: bold;" data-i18n="settings.showShapes">إظهار الأشكال والزخارف في الترويسة العليا</span>
                        <label class="switch"><input type="checkbox" id="cfg-show-athkar" checked><span class="slider"></span></label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: System Backup -->
        <div class="tab-content" id="tab-system">
            <div class="glass-panel" id="panel-backup">
                <div class="setting-title">💾 النظام والنسخ الاحتياطي</div>
                <p style="color: #94a3b8; font-size: 0.9rem; margin: 0 0 20px; line-height: 1.6;">تنزيل نسخة احتياطية آمنة من جميع بيانات التخزين المحلي (JSON) لحمايتها من الفقدان.</p>
                <button class="action-btn btn-backup" onclick="backupData()">⬇️ تنزيل نسخة احتياطية للبيانات</button>
                <button class="action-btn btn-restore" onclick="document.getElementById('restore-file').click()">⬆️ استعادة من ملف احتياطي سابق</button>
                <input type="file" id="restore-file" accept=".json" style="display:none" onchange="restoreData(this)">
                
                <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
                <h4 style="color: #f87171; margin-bottom: 10px;">⚠️ منطقة الخطر</h4>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0;">القيام بتهيئة وإفراغ جميع جداول قواعد البيانات لمسح الحركات التجريبية والبدء من جديد.</p>
                <button class="action-btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3);" onclick="window.location.href='data-reset.php'">🗑️ تصفير بيانات النظام</button>
            </div>
        </div>

        <!-- Action Bar (Static Center) -->
        <div style="display: flex; justify-content: center; margin-top: 25px; margin-bottom: 35px; width: 100%;">
            <button id="save-settings-btn" class="save-btn-main" onclick="saveSettings()" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border-radius: 25px; padding: 12px 40px; font-size: 1.1rem; font-weight: bold; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); border: 1px solid rgba(255,255,255,0.2); cursor: pointer;">
                💾 حفظ التعديلات
            </button>
        </div>

    </div>

</div>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/leaflet.js"></script>
<script defer src="js/modern-alerts.js?v=<?php echo file_exists('js/modern-alerts.js') ? filemtime('js/modern-alerts.js') : '1'; ?>"></script>
<script defer src="js/permissions-manager.js?v=20261016_v28"></script>
<script defer src="js/app.js?v=20261016_v28"></script>
<script defer src="js/whatsapp-integration.js?v=20261029_fast"></script>
<script>
// Language switcher logic for settings page

function parseNum(str) {
    if (!str) return 0;
    const arabicNumbers = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    let engStr = String(str).replace(/[٠-٩]/g, w => arabicNumbers.indexOf(w));
    engStr = engStr.replace(/,/g, '');
    return parseFloat(engStr) || 0;
}

function switchLanguage(lang) {
    setLang(lang);
    highlightLangBtn(lang);
    setTimeout(() => window.location.reload(), 300);
}
function highlightLangBtn(lang) {
    const arBtn = document.getElementById('lang-btn-ar');
    const enBtn = document.getElementById('lang-btn-en');
    if (!arBtn || !enBtn) return;
    arBtn.style.background = 'rgba(255,255,255,0.05)';
    arBtn.style.borderColor = 'rgba(255,255,255,0.1)';
    arBtn.style.color = '#94a3b8';
    enBtn.style.background = 'rgba(255,255,255,0.05)';
    enBtn.style.borderColor = 'rgba(255,255,255,0.1)';
    enBtn.style.color = '#94a3b8';
    const activeBtn = lang === 'ar' ? arBtn : enBtn;
    activeBtn.style.background = 'linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2))';
    activeBtn.style.borderColor = 'rgba(99,102,241,0.6)';
    activeBtn.style.color = '#fff';
    activeBtn.style.boxShadow = '0 0 20px rgba(99,102,241,0.3)';
}

        function getCurrencyLabel(code) {
            const labels = {
                'SDG': 'الجنيه السوداني (SDG)',
                'USD': 'الدولار الأمريكي (USD)',
                'SAR': 'الريال السعودي (SAR)',
                'AED': 'الدرهم الإماراتي (AED)'
            };
            return labels[code] || code;
        }

        function onCurrencySelectionChange() {
            const baseCur = document.getElementById('cfg-currency')?.value || 'SDG';
            const foreignCurEl = document.getElementById('cfg-foreign-currency');
            if (!foreignCurEl) return;
            let foreignCur = foreignCurEl.value;

            if (foreignCur === baseCur) {
                const options = Array.from(foreignCurEl.options).map(o => o.value);
                const alt = options.find(o => o !== baseCur) || 'USD';
                foreignCurEl.value = alt;
                foreignCur = alt;
            }

            const titleEl = document.getElementById('exchange-rates-title');
            const labelEl = document.getElementById('cfg-foreign-rate-label');

            if (titleEl) {
                titleEl.innerHTML = `💱 سعر صرف [${getCurrencyLabel(foreignCur)}] مقابل [${getCurrencyLabel(baseCur)}]`;
            }
            if (labelEl) {
                labelEl.innerText = `سعر ${getCurrencyLabel(foreignCur)} مقابل ${baseCur}`;
            }
        }

        function openSupportCurrencyModal() {
            alert('📞 خدمات المشتركين والدعم الفني:\n\nتثبيت العملتين (المحلية والأجنبية) يهدف لحماية قيود دفتر الأستاذ العام والتراكميات المالية التاريخية.\n\nإذا كنت ترغب في تعديل العملة النشطة أو تغيير العملة المحلية بعد التأسيس، يرجى التواصل مع فريق الدعم الفني لمراجعة الأثر المحاسبي.');
        }

document.addEventListener('DOMContentLoaded', function() {
    highlightLangBtn(getLang());
});
</script>

<script src="js/screens/settings-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/settings-j1.js') ?: '2027' ?>"></script>

<!-- ==================================================================== -->
<!-- CUSTOM MAGAIEES PREMIUM GLASSMORPHISM MODALS (PIN & CONFIRMATION)      -->
<!-- ==================================================================== -->
<style>
.mg-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15, 23, 42, 0.82); /* gpu-opt */ /* gpu-opt */
    z-index: 999999; display: flex; align-items: center; justify-content: center;
    animation: mgFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1); direction: rtl; font-family: 'Tajawal', system-ui, sans-serif;
}
@keyframes mgFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes mgPopIn { from { opacity: 0; transform: scale(0.88) translateY(18px); } to { opacity: 1; transform: scale(1) translateY(0); } }

.mg-modal-card {
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65), 0 0 35px rgba(56, 189, 248, 0.18);
    padding: 32px 26px; text-align: center; max-width: 440px; width: 90%;
    animation: mgPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); color: #f8fafc; position: relative; overflow: hidden;
}
.mg-modal-card::before {
    content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
    background: radial-gradient(circle at center, rgba(56, 189, 248, 0.07) 0%, transparent 70%);
    pointer-events: none;
}
.mg-modal-badge {
    width: 64px; height: 64px; border-radius: 20px;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(99, 102, 241, 0.2));
    border: 1px solid rgba(56, 189, 248, 0.3);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 2.2rem; margin: 0 auto 16px auto; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}
.mg-modal-title { font-size: 1.35rem; font-weight: 800; color: #f8fafc; margin: 0 0 8px 0; }
.mg-modal-sub { font-size: 0.92rem; color: #94a3b8; margin: 0 0 20px 0; line-height: 1.6; }

/* 6 OTP Input Boxes */
.mg-otp-container { display: flex; gap: 8px; justify-content: center; margin: 20px 0; direction: ltr; }
.mg-otp-box {
    width: 48px; height: 56px; font-size: 1.6rem; font-weight: 800; text-align: center;
    border-radius: 14px; border: 2px solid #334155; background: #0f172a; color: #38bdf8;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s cubic-bezier(0.16, 1, 0.3, 1); outline: none; box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);
    -webkit-text-security: disc; text-security: disc;
}
.mg-otp-box:focus {
    border-color: #38bdf8; box-shadow: 0 0 18px rgba(56, 189, 248, 0.45), inset 0 2px 4px rgba(0,0,0,0.4);
    transform: translateY(-3px); background: #1e293b;
}

.mg-modal-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 22px; }
.mg-btn-primary {
    background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); color: #fff;
    border: none; border-radius: 14px; padding: 14px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.38);
    display: flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Tajawal';
}
.mg-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37, 99, 235, 0.55); filter: brightness(1.1); }

.mg-btn-secondary {
    background: rgba(51, 65, 85, 0.6); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 14px; padding: 12px; font-weight: 600; font-size: 0.95rem; cursor: pointer;
    transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; font-family: 'Tajawal';
}
.mg-btn-secondary:hover { background: rgba(51, 65, 85, 0.9); color: #fff; transform: translateY(-1px); }

.mg-btn-danger {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff;
    border: none; border-radius: 14px; padding: 14px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.38); font-family: 'Tajawal';
}
.mg-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(220, 38, 38, 0.55); }

.mg-btn-ghost { background: transparent; color: #94a3b8; border: none; font-size: 0.9rem; cursor: pointer; padding: 8px; font-family: 'Tajawal'; }
.mg-btn-ghost:hover { color: #f8fafc; }
</style>

<!-- Modal Container HTML -->
<div id="mgModalOverlay" class="mg-modal-overlay" style="display:none;">
    <div id="mgModalCard" class="mg-modal-card"></div>
</div>

<script>
// Custom UI Prompt for 6-Digit PIN
function promptCustomPin({ title, subtitle, refCode = "", showSkip = false }) {
    return new Promise((resolve) => {
        const overlay = document.getElementById('mgModalOverlay');
        const card = document.getElementById('mgModalCard');

        card.innerHTML = `
            <div class="mg-modal-badge">🔒</div>
            <h3 class="mg-modal-title">${title}</h3>
            <p class="mg-modal-sub">${subtitle}</p>
            ${refCode ? `<div style="margin:-10px 0 16px 0; padding:8px 12px; background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.2); border-radius:12px; font-size:0.85rem; color:#94a3b8;">
                رمز المرجعية للملف: <b style="color:#38bdf8; font-family:monospace; font-size:0.95rem;">${refCode}</b>
                <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">(في حال نسيان كلمة السر، تواصل مع إدارة المشتركين وزودهم بهذا الرمز)</div>
            </div>` : ''}
            
            <div class="mg-otp-container">
                <input type="text" value="" maxlength="1" class="mg-otp-box" data-idx="0" autocomplete="one-time-code" inputmode="numeric">
                <input type="text" value="" maxlength="1" class="mg-otp-box" data-idx="1" autocomplete="one-time-code" inputmode="numeric">
                <input type="text" value="" maxlength="1" class="mg-otp-box" data-idx="2" autocomplete="one-time-code" inputmode="numeric">
                <input type="text" value="" maxlength="1" class="mg-otp-box" data-idx="3" autocomplete="one-time-code" inputmode="numeric">
                <input type="text" value="" maxlength="1" class="mg-otp-box" data-idx="4" autocomplete="one-time-code" inputmode="numeric">
                <input type="text" value="" maxlength="1" class="mg-otp-box" data-idx="5" autocomplete="one-time-code" inputmode="numeric">
            </div>

            <div style="margin-bottom: 15px; font-size: 0.85rem; color: #64748b;">
                <label style="cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <input type="checkbox" id="chkShowPin"> إظهار الأرقام
                </label>
            </div>

            <div class="mg-modal-actions">
                <button id="btnConfirmPin" class="mg-btn-primary">تأكيد واستمرار 🚀</button>
                ${showSkip ? '<button id="btnSkipPin" class="mg-btn-secondary">تنزيل بدون رقم سري ⚡</button>' : ''}
                <button id="btnCancelPin" class="mg-btn-ghost">إلغاء</button>
            </div>
        `;

        overlay.style.display = 'flex';
        const boxes = Array.from(card.querySelectorAll('.mg-otp-box'));
        boxes.forEach(b => { b.value = ''; b.style.webkitTextSecurity = 'disc'; });
        setTimeout(() => {
            boxes.forEach(b => b.value = '');
            boxes[0].focus();
        }, 80);

        // Auto-advance & Auto-backspace logic
        boxes.forEach((box, i) => {
            box.addEventListener('input', (e) => {
                const val = e.target.value;
                if (val && i < 5) boxes[i + 1].focus();
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !box.value && i > 0) {
                    boxes[i - 1].focus();
                }
            });
        });

        // Toggle visibility
        const chkShow = card.querySelector('#chkShowPin');
        chkShow.addEventListener('change', (e) => {
            boxes.forEach(b => {
                b.style.webkitTextSecurity = e.target.checked ? 'none' : 'disc';
                b.style.textSecurity = e.target.checked ? 'none' : 'disc';
            });
        });

        const close = (val) => {
            overlay.style.display = 'none';
            card.innerHTML = '';
            resolve(val);
        };

        card.querySelector('#btnConfirmPin').onclick = () => {
            const pinStr = boxes.map(b => b.value).join('');
            if (pinStr.length !== 6 || !/^\d{6}$/.test(pinStr)) {
                alert("⚠️ يرجى إدخال 6 أرقام كاملة (مثال: 123456)");
                boxes[0].focus();
                return;
            }
            close(pinStr);
        };

        if (showSkip) {
            card.querySelector('#btnSkipPin').onclick = () => close('');
        }

        card.querySelector('#btnCancelPin').onclick = () => close(null);
    });
}

// Custom UI Confirm Modal
function confirmCustomModal({ title, subtitle, confirmText = "استمرار", icon = "⚠️", isDanger = true }) {
    return new Promise((resolve) => {
        const overlay = document.getElementById('mgModalOverlay');
        const card = document.getElementById('mgModalCard');

        card.innerHTML = `
            <div class="mg-modal-badge">${icon}</div>
            <h3 class="mg-modal-title">${title}</h3>
            <p class="mg-modal-sub">${subtitle}</p>

            <div class="mg-modal-actions">
                <button id="btnConfirmAction" class="${isDanger ? 'mg-btn-danger' : 'mg-btn-primary'}">${confirmText}</button>
                <button id="btnCancelAction" class="mg-btn-secondary">إلغاء</button>
            </div>
        `;

        overlay.style.display = 'flex';

        const close = (val) => {
            overlay.style.display = 'none';
            card.innerHTML = '';
            resolve(val);
        };

        card.querySelector('#btnConfirmAction').onclick = () => close(true);
        card.querySelector('#btnCancelAction').onclick = () => close(false);
    });
}
</script>

<!-- Centered Unsaved Changes Confirmation Modal -->
<div id="unsaved-confirm-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); z-index:9999999; justify-content:center; align-items:center;   font-family:'Tajawal', sans-serif;">
    <div style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid rgba(245, 158, 11, 0.4); box-shadow: 0 25px 60px rgba(0,0,0,0.85), 0 0 30px rgba(245, 158, 11, 0.25); border-radius: 20px; padding: 30px 35px; width: 90%; max-width: 480px; text-align: center; color: #f8fafc; animation: unsavedModalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="width: 65px; height: 65px; background: rgba(245, 158, 11, 0.15); border: 2px solid #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2rem;">
            ⚠️
        </div>
        <h3 style="margin: 0 0 10px; font-size: 1.3rem; font-weight: 800; color: #f59e0b;">تنبيه: توجد تعديلات غير محفوظة!</h3>
        <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px;">
            لقد قمت بتعديل بعض الإعدادات بالنظام ولم تقم بحفظها بعد. هل تريد حفظ التعديلات الآن قبل المغادرة أم التخلي عن التعديلات؟
        </p>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <button id="btn-unsaved-save-now" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 12px; border-radius: 12px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                💾 حفظ التعديلات والإستمرار
            </button>
            <button id="btn-unsaved-leave-no-save" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; padding: 11px; border-radius: 12px; font-size: 0.95rem; font-weight: bold; cursor: pointer; transition: all 0.2s ease;">
                🚪 المغادرة بدون حفظ
            </button>
            <button id="btn-unsaved-cancel" style="background: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); padding: 10px; border-radius: 12px; font-size: 0.9rem; cursor: pointer;">
                ❌ البقاء والتراجع
            </button>
        </div>
    </div>
</div>

<style>
@keyframes unsavedModalPop {
    0% { transform: scale(0.85); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.pulse-alert {
    animation: none; /* cpu-opt */
}
@keyframes savePulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>

<script src="js/screens/settings-j2.js?v=<?= @filemtime(__DIR__ . '/js/screens/settings-j2.js') ?: '2027' ?>"></script>

</body>
</html>
