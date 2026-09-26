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

            $stmtS = $pdo->prepare("SELECT data FROM settings WHERE tenant_id = ? ORDER BY id DESC LIMIT 1");
            $stmtS->execute([$GLOBAL_TENANT_ID]);
            $rawSettings = $stmtS->fetchColumn();
            $settingsData = $rawSettings ? json_decode($rawSettings, true) : null;

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
            
            // Sync full settings payload into settings table
            $jsonStr = json_encode($input, JSON_UNESCAPED_UNICODE);
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
    if (!empty($GLOBAL_TENANT_ID)) {
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
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مقاييس 2026 - الإعدادات</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/fontawesome/all.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script>
        window.SERVER_TENANT = <?php echo json_encode($serverTenant, JSON_UNESCAPED_UNICODE); ?>;
        window.SERVER_SETTINGS = <?php echo json_encode($serverSettings, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <style>
        :root {
            --bg-color: #050308;
            --surface: rgba(18, 15, 25, 0.65);
            --surface-hover: rgba(28, 24, 38, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.2);
            
            --primary-accent: #6366f1;
            --theme-pink: #ec4899;
            --theme-blue: #3b82f6;
            --theme-green: #10b981;
        }

        body {
            background-color: var(--bg-color);
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex; flex-direction: column;
            overflow-x: hidden;
            font-family: 'Tajawal', sans-serif;
            color: #fff;
        }

        /* Ambient Animated Background Orbs */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #0c0812 0%, #000000 100%);
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px); opacity: 0.15;
            animation: floatOrb 20s infinite alternate ease-in-out;
        }

        .orb-1 { width: 500px; height: 500px; background: var(--primary-accent); top: -5%; right: -5%; }
        .orb-2 { width: 450px; height: 450px; background: var(--theme-pink); bottom: -10%; left: -5%; animation-delay: -7s; }
        .orb-3 { width: 600px; height: 600px; background: var(--theme-blue); top: 30%; left: 30%; animation-duration: 25s; opacity: 0.1; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Layout Container */
        .settings-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 25px 25px 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 30px;
            flex: 1;
        }


        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #94a3b8; font-weight: 500; }

        .header-center { text-align: center; }
        .header-center h2 { margin: 0; font-size: 1.6rem; font-weight: 900; background: linear-gradient(to right, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 1px; }

        .header-controls { display: flex; align-items: center; gap: 15px; }
        .icon-btn {
            background: rgba(255,255,255,0.03); border: 1px solid var(--border); width: 46px; height: 46px;
            border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s ease;
        }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .save-btn-main {
            background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399;
            padding: 10px 24px; border-radius: 14px; font-size: 1rem; font-weight: 800; cursor: pointer;
            transition: all 0.3s; display: flex; align-items: center; gap: 8px; font-family: 'Tajawal', sans-serif;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.1);
        }
        .save-btn-main:hover { background: rgba(16, 185, 129, 0.25); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.5); color: #fff;}

        /* Save Badge */
        .save-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
            color: #34d399; padding: 10px 20px; border-radius: 20px;
            font-size: 0.95rem; font-weight: 800; opacity: 0; transition: opacity 0.4s;
            position: fixed; top: 110px; left: 50%; transform: translateX(-50%); z-index: 1000;
            backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.5); pointer-events: none;
        }
        .save-badge.show { opacity: 1; pointer-events: all; }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }

        /* Glass Panel Settings Card */
        .glass-panel {
            background: var(--surface); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border); border-radius: 24px; padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.03);
            transition: all 0.4s ease; display: flex; flex-direction: column;
            animation: fadeUpIn 0.6s ease-out both;
        }
        .glass-panel:nth-child(1) { animation-delay: 0.1s; }
        .glass-panel:nth-child(2) { animation-delay: 0.2s; }
        .glass-panel:nth-child(3) { animation-delay: 0.3s; }
        .glass-panel:nth-child(4) { animation-delay: 0.4s; }
        .glass-panel:nth-child(5) { animation-delay: 0.5s; }
        
        .glass-panel:hover { border-color: var(--border-highlight); box-shadow: 0 20px 45px rgba(0,0,0,0.4); transform: translateY(-5px); }

        .setting-title {
            font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 25px;
            padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex; align-items: center; gap: 10px;
        }

        /* Form Controls */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem; color: #94a3b8; }
        
        .form-input, .form-select {
            width: 100%; padding: 14px 16px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3);
            color: #fff; box-sizing: border-box; font-family: 'Tajawal', sans-serif;
            font-size: 1rem; transition: all 0.3s;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: var(--primary-accent); background: rgba(0,0,0,0.5);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        .form-select option { background: #0c0812; color: #fff; }

        .setting-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.05); }
        .setting-row:last-child { border-bottom: none; margin-bottom: 0; }

        /* Premium Toggle Switch */
        .switch { position: relative; display: inline-block; width: 54px; height: 28px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid rgba(255,255,255,0.05); }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 3px; background-color: #94a3b8; transition: .4s cubic-bezier(0.68, -0.55, 0.265, 1.55); border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        
        input:checked + .slider { background-color: #10b981; border-color: #059669; }
        input:checked + .slider:before { transform: translateX(26px); background-color: #ffffff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); }

        
        /* Sub-Tabs Navigation */
        .sub-tabs-container {
            display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; overflow-x: auto;
        }
        .sub-tab-btn {
            background: transparent; border: none; color: #94a3b8; font-family: 'Tajawal', sans-serif;
            font-size: 0.95rem; font-weight: 600; padding: 10px 20px; cursor: pointer; border-radius: 8px;
            transition: all 0.3s; white-space: nowrap;
        }
        .sub-tab-btn:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sub-tab-btn.active { background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
        .sub-tab-content { display: none; animation: fadeIn 0.3s ease-out; }
        .sub-tab-content.active { display: block; }

        /* Action Buttons */
        .action-btn {
            width: 100%; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 1rem;
            border: none; cursor: pointer; margin-top: 10px; transition: all 0.3s;
            font-family: 'Tajawal', sans-serif; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-users { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .btn-users:hover { background: rgba(56, 189, 248, 0.2); color: #fff; transform: translateY(-2px); }
        
        .btn-backup { background: rgba(168, 85, 247, 0.1); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); margin-bottom: 10px; }
        .btn-backup:hover { background: rgba(168, 85, 247, 0.2); color: #fff; transform: translateY(-2px); }
        
        .btn-restore { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .btn-restore:hover { background: rgba(245, 158, 11, 0.2); color: #fff; transform: translateY(-2px); }

        @keyframes fadeUpIn { from { opacity: 0; transform: translateY(40px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        @media (max-width: 768px) {
            .settings-grid { grid-template-columns: 1fr; }
        }
        
        /* Fix double sidebar spacing issue */
        body.has-sidebar { padding-right: 0 !important; }

        /* Light Theme Overrides for Typography */
        :root.light-theme .setting-title {
            color: #0f172a !important;
            border-bottom-color: rgba(0,0,0,0.1) !important;
        }
        :root.light-theme .form-label {
            color: #475569 !important;
        }
        :root.light-theme .setting-row {
            border-bottom-color: rgba(0,0,0,0.1) !important;
            color: #1e293b !important;
        }
        :root.light-theme .setting-row span,
        :root.light-theme .glass-panel p,
        :root.light-theme .setting-row label {
            color: #1e293b !important;
        }
        :root.light-theme .btn-users:hover,
        :root.light-theme .btn-backup:hover,
        :root.light-theme .btn-restore:hover {
            color: #0f172a !important;
        }
        :root.light-theme .form-input,
        :root.light-theme .form-select {
            color: #0f172a !important;
        }
        :root.light-theme .slider {
            background-color: rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        :root.light-theme .slider:before {
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
        }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .summary-card,
        :root.light-theme .filter-panel,
        :root.light-theme .chart-card,
        :root.light-theme .kpi-card,
        :root.light-theme .premium-header,
        :root.light-theme .top-header,
        :root.light-theme .header-area {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
        :root.light-theme .glass-panel *,
        :root.light-theme .neo-card *,
        :root.light-theme .summary-card * {
            text-shadow: none !important;
        }
        :root.light-theme table.pro-table th,
        :root.light-theme .pos-grid thead th,
        :root.light-theme .ledger-table th,
        :root.light-theme .report-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.02) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #334155 !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #0f172a !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .modern-btn {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #0f172a !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
\n
        /* Horizontal Tabs Styling */
        .settings-layout {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 5px;
        }
        .tabs-nav {
            display: flex;
            flex-wrap: wrap; /* Wrap tabs into multiple lines */
            justify-content: center; /* Center the wrapped tabs */
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px;
            gap: 10px;
            backdrop-filter: blur(10px);
            margin-top: 15px;
            margin-bottom: 15px;
        }
        
        :root.light-theme .tabs-nav {
            background: #e2e8f0 !important; /* Silver background in Light Mode */
            border-color: rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .tab-btn {
            background: transparent;
            color: #94a3b8;
            border: 1px solid transparent;
            padding: 10px 18px; /* Slightly adjusted padding */
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Smoother transition */
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px); /* Professional lift effect */
        }
        :root.light-theme .tab-btn { color: #64748b; }
        :root.light-theme .tab-btn:hover { 
            color: #0f172a; 
            background: rgba(0,0,0,0.03); 
            border-color: rgba(0,0,0,0.1);
        }

        .tab-btn.active {
            background: #38bdf8;
            color: #0f172a;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3);
        }
        :root.light-theme .tab-btn.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3);
        }
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease-out forwards;
        }
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes alertPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .pulse-alert {
            animation: alertPulse 1.5s infinite !important;
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: white !important;
            border: none !important;
        }

</style>
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

    <!-- Tabs Navigation -->
    <div class="tabs-nav">
        <button class="tab-btn active" onclick="switchTab('company', this)" data-i18n="settings.companyData">🏢 بيانات الشركة</button>
        <button class="tab-btn" onclick="switchTab('finance', this)" data-i18n="settings.financeTax">💰 المالية والضرائب</button>
        <button class="tab-btn" onclick="switchTab('inventory', this)" data-i18n="settings.warehouses">📦 المخازن</button>
        <button class="tab-btn" onclick="switchTab('printing', this)" data-i18n="settings.printing">🖨️ إعدادات الطباعة</button>
        <button class="tab-btn" onclick="switchTab('email', this)" data-i18n="settings.smtp">📧 إعدادات البريد SMTP</button>

        <button class="tab-btn" onclick="switchTab('users', this)" data-i18n="settings.users">🛡️ المستخدمين</button>
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
                <div id="subscription-summary-box" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(30, 41, 59, 0.65) 100%); border: 1.5px solid rgba(56, 189, 248, 0.35); border-radius: 18px; padding: 16px 20px; margin-top: 15px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; backdrop-filter: blur(10px);">
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
                        <input type="text" class="form-input" id="cfg-company-name" placeholder="مثال: مؤسسة مقاييس للتجارة والخدمات">
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

            <!-- Panel 2: Standalone VAT Tax Panel -->
            <div class="glass-panel" style="margin-bottom: 20px; display: none;">
                <div class="setting-title" style="font-size: 1.05rem; color: #10b981; display:flex; align-items:center; gap:8px;">
                    <span>📊 ضريبة القيمة المضافة (VAT)</span>
                </div>
                <div class="form-group" style="margin: 0; max-width: 450px;">
                    <label class="form-label">نسبة ضريبة القيمة المضافة المطبقة (%)</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="number" class="form-input" id="cfg-vat" value="0" min="0" max="100" step="0.5" style="font-size: 1.1rem; font-weight: bold; width: 140px;">
                        <span style="font-size:1.1rem; font-weight:bold; color:var(--text-secondary);">%</span>
                    </div>
                    <small style="color:var(--text-secondary); display:block; margin-top:6px;">النسبة المئوية للضريبة المعتمدة والمحسوبة على الفواتير والسندات والمشتريات والمبيعات.</small>
                </div>
            </div>

            <!-- Panel 3: Standalone Fiscal Year Panel -->
            <div class="glass-panel" style="display: none;">
                <div class="setting-title" style="font-size: 1.05rem; color: #38bdf8; display:flex; align-items:center; gap:8px;">
                    <span>📅 السنة المالية ودورة الإقفال المحاسبي</span>
                </div>
                <div class="form-group" style="margin: 0; max-width: 450px;">
                    <label class="form-label">تاريخ بداية السنة المالية للمؤسسة</label>
                    <select class="form-select" id="cfg-fiscal-year" style="font-size: 1rem;">
                        <option value="1">1 يناير (السنة التقويمية - افتراضي)</option>
                        <option value="4">1 أبريل (السنة الربيعية)</option>
                        <option value="7">1 يوليو (السنة الصيفية)</option>
                        <option value="10">1 أكتوبر (السنة الخريفية)</option>
                    </select>
                    <small style="color:var(--text-secondary); display:block; margin-top:6px;">الشهر الفعلي لبدء السنة المالية واستخراج القوائم الختامية وإقفال الأرباح والخسائر.</small>
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
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                
                <div class="glass-panel" id="panel-users" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">🛡️ إدارة المستخدمين</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">إدارة حسابات الموظفين، تحديد الأدوار (أدمن، محاسب، بائع)، وتخصيص الصلاحيات.</p>
                    <button class="action-btn btn-users" onclick="window.location.href='users-roles.php'" style="margin-top: 15px;">⚙️ فتح إدارة المستخدمين</button>
                </div>

                <div class="glass-panel" id="panel-whatsapp" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">💬 ربط الواتساب</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">إدارة جلسة الواتساب ومسح الـ QR لإرسال الفواتير والتنبيهات للعملاء مباشرة.</p>
                    <button class="action-btn" style="background: rgba(37,211,102,0.1); color: #25d366; border: 1px solid rgba(37,211,102,0.3); margin-top: 15px;" onclick="openWAModal()">📱 إعداد الواتساب</button>
                </div>

                <div class="glass-panel" id="panel-audit" style="margin: 0;">
                    <div class="setting-title" style="border: none; margin-bottom: 10px;">⬛ الصندوق الأسود</div>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0; line-height: 1.6;">سجل شامل لجميع حركات النظام لتتبع ومراقبة العمليات والتدقيق الأمني.</p>
                    <button class="action-btn" style="background: rgba(15,23,42,0.5); color: #fff; border: 1px solid rgba(255,255,255,0.2); margin-top: 15px;" onclick="window.location.href='audit-log.php'">🔍 فتح الصندوق الأسود</button>
                </div>
                
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
<script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
<script defer src="js/whatsapp-integration.js?v=<?php echo time(); ?>"></script>
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

<script>
    // ─── Load Settings on Page Open ───────────────────────────
    let _settingsPageInited = false;
    function initSettingsPage() {
        loadSettings();
        if (_settingsPageInited) return;
        _settingsPageInited = true;
        if (!localStorage.getItem('authToken')) {
            window.location.href = 'login.php';
            return;
        }

        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const perms = window.resolveUserPermissions ? window.resolveUserPermissions(user) : {};
        
        const role = String(user.role || '').toLowerCase();
        const username = String(user.username || '').toLowerCase();
        const roleId = String(user.roleId || '').toLowerCase();

        const isAdmin = !user.role || 
            username === 'admin' || 
            role.includes('admin') || 
            role.includes('مدير') || 
            role.includes('أدمن') || 
            role.includes('manager') || 
            roleId === 'admin_fallback' || 
            roleId === 'role_admin_default' ||
            (user.securityLevel && user.securityLevel >= 1);
        
        const hasSettings = isAdmin || !perms.settings || perms.settings.view !== false;
        const hasInvRules = isAdmin || !perms.inventory_rules || perms.inventory_rules.view !== false;
        
        if (!hasSettings && !hasInvRules) {
            window.location.href = 'dashboard-pro.php';
            return;
        }
        
        if (!hasSettings) {
            const tabsToHide = ['company', 'finance', 'ui', 'users', 'system', 'printing', 'email', 'whatsapp'];
            tabsToHide.forEach(t => {
                const btn = document.querySelector(`button[onclick*="'${t}'"]`);
                if (btn) btn.style.display = 'none';
            });
            const pTitle = document.querySelector('.page-title');
            if (pTitle) pTitle.innerText = 'قواعد المخزون والعمليات';
        }
        if (!hasInvRules && !hasSettings) {
            const invBtn = document.querySelector('button[onclick*="\'inventory\'"]');
            if (invBtn) invBtn.style.display = 'none';
        }

        const canEditSettings = isAdmin || !perms.settings || perms.settings.edit !== false;
        const canEditInvRules = isAdmin || !perms.inventory_rules || perms.inventory_rules.edit !== false;
        const canEditRates = isAdmin || !perms.exchange_rates || perms.exchange_rates.edit !== false;
        
        if (!canEditSettings && !canEditInvRules) {
            const mainSaveBtn = document.querySelector('.save-btn-main');
            if (mainSaveBtn) mainSaveBtn.style.display = 'none';
        }

        // Exchange Rate Logic setup
        const modeSelect = document.getElementById('cfg-exchange-mode');
        const rateInputs = document.querySelectorAll('.rate-input');
        const usdInput = document.getElementById('cfg-rate-usd');
        const sarInput = document.getElementById('cfg-rate-sar');
        const aedInput = document.getElementById('cfg-rate-aed');
        const rateWarning = document.getElementById('exchange-rate-warning');

        // Enforce exchange rate edit permissions
        rateInputs.forEach(i => {
            i.disabled = !canEditRates;
            i.readOnly = !canEditRates;
            i.style.opacity = canEditRates ? '1' : '0.5';
            i.style.pointerEvents = canEditRates ? 'auto' : 'none';
            i.style.cursor = canEditRates ? 'text' : 'not-allowed';
        });

        if (modeSelect) {
            modeSelect.disabled = !canEditRates;
            modeSelect.style.opacity = canEditRates ? '1' : '0.5';
            modeSelect.style.cursor = canEditRates ? 'pointer' : 'not-allowed';
            
            modeSelect.addEventListener('change', function() {
                if (!canEditRates) return;
                
                if (this.value === 'global') {
                    fetch('api_index.php/exchange-rate')
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.visible && data.rate) {
                                const usdRate = parseFloat(data.rate);
                                if (usdInput && (!usdInput.value || usdInput.value == 0)) {
                                    usdInput.value = usdRate;
                                }
                            }
                        }).catch(() => {});
                }
            });
        }

        if (usdInput) {

            const autoSaveRate = function() {
                const rawVal = usdInput.value;
                if (modeSelect && modeSelect.value === 'global') {
                    modeSelect.value = 'manual';
                }
                const usdRate = parseNum(rawVal);
                if (usdRate > 0) {
                    if (sarInput) sarInput.value = (usdRate / 3.75).toFixed(2);
                    if (aedInput) aedInput.value = (usdRate / 3.67).toFixed(2);

                    // Instant Persistence to dedicated and settings keys
                    const origSet = window.originalSetItem || Storage.prototype.setItem;
                    try { origSet.call(localStorage, 'erp_rate_usd', usdRate.toString()); } catch(e){}
                    try { localStorage.setItem('erp_rate_usd', usdRate.toString()); } catch(e){}
                    try { origSet.call(localStorage, 'erp_manual_rate_usd', usdRate.toString()); } catch(e){}
                    try { localStorage.setItem('erp_manual_rate_usd', usdRate.toString()); } catch(e){}

                    const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
                    try {
                        let currentCfg = JSON.parse(localStorage.getItem(settingsKey) || localStorage.getItem('erp_settings_v1') || '{}');
                        currentCfg.rateUSD = usdRate;
                        currentCfg.exchangeMode = 'manual';
                        const str = JSON.stringify(currentCfg);
                        try { origSet.call(localStorage, settingsKey, str); } catch(e){}
                        try { origSet.call(localStorage, 'erp_settings_v1', str); } catch(e){}
                        if (window.DB_STORE) {
                            window.DB_STORE[settingsKey] = str;
                            window.DB_STORE['erp_settings_v1'] = str;
                        }
                    } catch(e) {}
                }
            };

            usdInput.addEventListener('input', autoSaveRate);
            usdInput.addEventListener('change', autoSaveRate);
            usdInput.addEventListener('blur', autoSaveRate);
        }

        // Dynamic visibility of exchange rates based on selected active currencies
        const activeCurrenciesCbs = document.querySelectorAll('.active-currency-cb');
        function updateExchangeRateVisibility() {
            activeCurrenciesCbs.forEach(cb => {
                const group = document.querySelector('.rate-group-' + cb.value.toLowerCase());
                if (group) {
                    group.style.display = cb.checked ? 'block' : 'none';
                }
            });
        }
        activeCurrenciesCbs.forEach(cb => cb.addEventListener('change', updateExchangeRateVisibility));
        window.updateExchangeRateVisibility = updateExchangeRateVisibility; // Expose for loadSettings
        updateExchangeRateVisibility();

        loadSettings();

        // Show save button only when changes are made
        const settingsContainer = document.querySelector('.settings-container');
        if (settingsContainer) {
            function showSaveAlert(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
                    const btn = document.getElementById('save-settings-btn');
                    if (btn && btn.style.display !== 'block') {
                        btn.style.display = 'block';
                        btn.style.animation = 'none';
                        setTimeout(() => {
                            btn.style.animation = 'fadeIn 0.4s ease-out';
                        }, 10);
                        setTimeout(() => {
                            btn.classList.add('pulse-alert');
                        }, 400);
                    }
                }
            }

            // Auto open requested tab via URL query (?tab=system) or hash (#system)
            const urlParams = new URLSearchParams(window.location.search);
            const reqTab = urlParams.get('tab') || window.location.hash.replace('#', '');
            if (reqTab && document.getElementById('tab-' + reqTab)) {
                switchTab(reqTab);
            }

            if (settingsContainer) {
                settingsContainer.addEventListener('input', showSaveAlert);
                settingsContainer.addEventListener('change', showSaveAlert);
            }
        }
    }

    function switchTab(tabId, elem) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
        });
        
        let targetBtn = elem;
        if (!targetBtn && typeof event !== 'undefined' && event && event.currentTarget) {
            targetBtn = event.currentTarget;
        }
        if (!targetBtn) {
            targetBtn = document.querySelector(`.tab-btn[onclick*="'${tabId}'"]`);
        }
        
        if (targetBtn) targetBtn.classList.add('active');
        
        const targetTab = document.getElementById('tab-' + tabId);
        if (targetTab) {
            targetTab.classList.add('active');
            targetTab.style.display = 'block';
        }
    }

    function switchPrintTab(tabId, btn) {
        document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sub-tab-content').forEach(content => content.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
    window.switchTab = switchTab;
    window.switchPrintTab = switchPrintTab;

                window.handleExchangeModeChange = function(select) {
        const rateInput = document.getElementById('cfg-rate-usd');
        const container = document.getElementById('exchange-rates-single-row');
        const modeBadge = document.getElementById('cfg-mode-badge');
        const rateLabel = document.getElementById('cfg-foreign-rate-label');
        if (!rateInput) return;

        const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
        let currentCfg = {};
        try {
            currentCfg = JSON.parse(localStorage.getItem(settingsKey) || localStorage.getItem('erp_settings_v1') || '{}');
            currentCfg.exchangeMode = select.value;
            currentCfg._userExplicitManual = (select.value === 'manual');
            const str = JSON.stringify(currentCfg);
            localStorage.setItem(settingsKey, str);
            localStorage.setItem('erp_settings_v1', str);
            if (window.DB_STORE) {
                window.DB_STORE[settingsKey] = str;
                window.DB_STORE['erp_settings_v1'] = str;
            }
        } catch(e) {}

        const fallbackRate = currentCfg.rateUSD || parseFloat(localStorage.getItem('erp_rate_usd')) || 600;
        const lastManualRate = parseFloat(localStorage.getItem('erp_manual_rate_usd')) || fallbackRate;
        const lastSaasRate = parseFloat(localStorage.getItem('erp_saas_rate_usd')) || (window.getExchangeRateForCurrency ? window.getExchangeRateForCurrency('USD') : 5600) || fallbackRate;
        
        if (select.value === 'global' || select.value === 'saas') {
            // Cyan / Sky Blue High Glow for Central Rate
            if (container) {
                container.style.background = 'linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(8, 20, 45, 0.98))';
                container.style.borderColor = '#38bdf8';
                container.style.boxShadow = '0 0 25px rgba(56, 189, 248, 0.35), inset 0 1px 2px rgba(255,255,255,0.1)';
            }
            if (modeBadge) {
                modeBadge.style.background = 'rgba(56, 189, 248, 0.15)';
                modeBadge.style.border = '1px solid #38bdf8';
                modeBadge.style.color = '#38bdf8';
                modeBadge.innerHTML = '🔒 السعر المركزي معتمد آلياً';
            }
            if (rateLabel) {
                rateLabel.style.color = '#38bdf8';
                rateLabel.style.textShadow = '0 0 10px rgba(56,189,248,0.5)';
            }

            rateInput.readOnly = true;
            rateInput.style.opacity = '0.9';
            rateInput.style.background = 'rgba(15, 23, 42, 0.9)';
            rateInput.style.borderColor = '#38bdf8';
            rateInput.style.color = '#38bdf8';
            rateInput.style.boxShadow = '0 0 15px rgba(56,189,248,0.3)';
            rateInput.style.cursor = 'not-allowed';
            rateInput.value = lastSaasRate;

            // Fetch from SaaS
            const _base = (() => { const o = window.location.origin; if (o === 'https://elsajana.com' || o === 'https://www.elsajana.com') return 'https://c.elsajana.com/'; let d = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')); if (d.endsWith('/')) d = d.slice(0,-1); return o + (d||'') + '/'; })();
            const token = localStorage.getItem('authToken');
            if (token) {
                fetch(_base + 'api_index.php/tenant-exchange-rate', {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(r => r.json()).then(data => {
                    if (data && data.success && data.payload && data.payload.rateUSD && parseFloat(data.payload.rateUSD) > 0) {
                        rateInput.value = data.payload.rateUSD;
                        localStorage.setItem('erp_saas_rate_usd', data.payload.rateUSD);
                        if(typeof window.showToast === 'function') window.showToast('تم سحب السعر اللحظي من المنصة بنجاح', 'success');
                    } else {
                        rateInput.value = lastSaasRate;
                    }
                }).catch(e => {
                    rateInput.value = lastSaasRate;
                    console.error('Error fetching rate:', e);
                });
            } else {
                rateInput.value = lastSaasRate;
            }
        } else {
            // Emerald Green High Glow for Manual Mode
            if (container) {
                container.style.background = 'linear-gradient(135deg, rgba(6, 30, 20, 0.95), rgba(10, 25, 30, 0.98))';
                container.style.borderColor = '#10b981';
                container.style.boxShadow = '0 0 25px rgba(16, 185, 129, 0.35), inset 0 1px 2px rgba(255,255,255,0.1)';
            }
            if (modeBadge) {
                modeBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                modeBadge.style.border = '1px solid #10b981';
                modeBadge.style.color = '#34d399';
                modeBadge.innerHTML = '✏️ تسعير يدوي مخصص (متاح للتعديل)';
            }
            if (rateLabel) {
                rateLabel.style.color = '#34d399';
                rateLabel.style.textShadow = '0 0 10px rgba(16,185,129,0.5)';
            }

            rateInput.readOnly = false;
            rateInput.style.opacity = '1';
            rateInput.style.background = 'rgba(6, 20, 15, 0.95)';
            rateInput.style.borderColor = '#10b981';
            rateInput.style.color = '#34d399';
            rateInput.style.boxShadow = '0 0 20px rgba(16,185,129,0.4)';
            rateInput.style.cursor = 'text';
            rateInput.value = lastManualRate;
            rateInput.dispatchEvent(new Event('input'));
            rateInput.focus();
            rateInput.select();
        }
    };

    window.renderExchangeRateHistory = function() {
            const tbody = document.getElementById('cfg-history-tbody');
            if (!tbody) return;
            const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
            let hist = [];
            try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
            
            const filter = document.getElementById('cfg-history-filter') ? document.getElementById('cfg-history-filter').value : 'all';
            
            if (filter !== 'all') {
                hist = hist.filter(h => h.source === filter || (filter === 'saas' && (h.source === 'global' || h.source === 1 || h.source === '1')) || (filter === 'manual' && (h.source === 0 || h.source === '0')));
            }
            
            // Sort by most recent
            hist.reverse();
            
            if (hist.length === 0) {
                tbody.innerHTML = '<tr id="empty-history-row"><td colspan="5" style="text-align:center; padding:25px; color:#f87171; font-weight:bold; font-size: 1.1rem; background:rgba(239,68,68,0.05);">⚠️ لا توجد سجلات مطابقة للفلترة المختارة.</td></tr>';
                return;
            }
            
            let html = '';
            hist.forEach(h => {
                const sourceBadge = (h.source === 'saas' || h.source === 'global') 
                    ? '<span style="background:rgba(56,189,248,0.2); color:#38bdf8; padding:3px 8px; border-radius:12px; font-size:0.8rem;">سحب من منصة ساس</span>' 
                    : '<span style="background:rgba(245,158,11,0.2); color:#f59e0b; padding:3px 8px; border-radius:12px; font-size:0.8rem;">إدخال يدوي</span>';
                
                html += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background=''">
                        <td style="text-align:right; padding:10px; color:#10b981; font-weight:bold;">${parseFloat(h.rate).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                        <td style="text-align:right; padding:10px; color:#f8fafc;">${h.date || '-'}</td>
                        <td style="text-align:right; padding:10px; color:#94a3b8;">${h.time || '-'}</td>
                        <td style="text-align:right; padding:10px; color:#f8fafc;">${h.user || '-'}</td>
                        <td style="text-align:right; padding:10px;">${sourceBadge}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        };

        // Hook into initialization to load history
                document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('cfg-history-tbody')) {
                const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
                let hist = [];
                try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
                
                if (hist.length === 0) {
                    const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
                    const s = JSON.parse(localStorage.getItem(tk) || '{}');
                    if (s.rateUSD) {
                        const now = new Date();
                        hist.push({
                            id: 'HR-' + Date.now(),
                            rate: s.rateUSD,
                            date: now.toISOString().split('T')[0],
                            time: now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12:true}),
                            user: 'مدير النظام',
                            source: s.exchangeMode || 'manual'
                        });
                        localStorage.setItem(historyKey, JSON.stringify(hist));
                    }
                }
                renderExchangeRateHistory();
            }
        });

        function loadSettings() {
        console.log('=== loadSettings() CALLED (IRON-CLAD TENANT ISOLATION) ===');
        const activeTenantId = (window.SERVER_TENANT && window.SERVER_TENANT.id) || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) || '1';
        const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : ('tenant_' + activeTenantId + '_erp_settings_v1');
        console.log('Strict settingsKey =', settingsKey);
        
        // 🛡️ IRON-CLAD ISOLATION BARRIER:
        // Priority 1: Direct MySQL authoritative data for THIS specific tenant
        // Priority 2: In-Memory Proxy for THIS specific tenantKey
        // Priority 3: LocalStorage for THIS specific tenantKey
        // Priority 4: Scoped tenant cookie backup
        // Strictly prohibit reading from un-prefixed keys of other tenants!
        let cfgRaw = null;
        
        if (window.SERVER_SETTINGS && typeof window.SERVER_SETTINGS === 'object') {
            cfgRaw = window.SERVER_SETTINGS;
            console.log('loadSettings: READ from authoritative window.SERVER_SETTINGS (MySQL)');
        }
        
        if (!cfgRaw && window.DB_STORE && window.DB_STORE[settingsKey]) {
            cfgRaw = window.DB_STORE[settingsKey];
            console.log('loadSettings: READ from DB_STORE[settingsKey]');
        }
        
        if (!cfgRaw) {
            try { cfgRaw = localStorage.getItem(settingsKey); } catch(e) {}
            if (cfgRaw) console.log('loadSettings: READ from localStorage[settingsKey]');
        }
        
        // Scoped tenant cookie backup ONLY
        if (!cfgRaw) {
            const cookieMatch = document.cookie.match(new RegExp('erp_settings_backup_t' + activeTenantId + '=([^;]+)'));
            if (cookieMatch) {
                try {
                    cfgRaw = decodeURIComponent(cookieMatch[1]);
                    console.log('loadSettings: READ from scoped tenant cookie backup');
                } catch(e) {}
            }
        }
        
        if (!cfgRaw) cfgRaw = '{}';
        
        let cfg = {};
        try {
            cfg = typeof cfgRaw === 'string' ? JSON.parse(cfgRaw) : (cfgRaw || {});
        } catch(e) { cfg = {}; }
        
        console.log('loadSettings: cfg.rateUSD =', cfg.rateUSD, ', cfg.companyName =', cfg.companyName, ', cfg.savedAt =', cfg.savedAt);
        
        // Also check dedicated rate key
        let dedicatedRate = null;
        try { dedicatedRate = localStorage.getItem('erp_rate_usd'); } catch(e) {}
        console.log('loadSettings: dedicated erp_rate_usd =', dedicatedRate);
        
        if (cfg.logoBase64) {
            const img = document.getElementById('logo-preview');
            img.src = cfg.logoBase64;
            img.style.display = 'block';
        }
        
        document.getElementById('cfg-logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    window.pendingLogoBase64 = evt.target.result;
                    const img = document.getElementById('logo-preview');
                    img.src = window.pendingLogoBase64;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // -------------------------------------------------------------
        // Company & Subscription Data: Intelligent Multi-Tier Sync
        // -------------------------------------------------------------
        window.applyCompanyAndSubscriptionData = function(srvTenant, srvSettings, localCfg) {
            srvTenant = srvTenant || window.SERVER_TENANT || {};
            srvSettings = srvSettings || window.SERVER_SETTINGS || {};
            localCfg = localCfg || cfg || {};

            const safeSet = (id, val) => { const el = document.getElementById(id); if (el && val !== undefined && val !== null) el.value = val; };

            // Server is single source of truth; falls back to local cache, then tenant defaults
            const finalCompanyName = (srvSettings && srvSettings.companyName) || (srvTenant && srvTenant.company_name) || (localCfg && localCfg.companyName) || '';
            const finalRegNumber = (srvSettings && srvSettings.regNumber) || (localCfg && localCfg.regNumber) || '';
            const finalTaxNumber = (srvSettings && srvSettings.taxNumber) || (localCfg && localCfg.taxNumber) || '';
            const finalPhone = (srvSettings && srvSettings.phone) || (srvTenant && srvTenant.owner_phone) || (localCfg && localCfg.phone) || '';
            const finalWhatsapp = (srvSettings && srvSettings.whatsapp) || (srvTenant && srvTenant.phone2) || (localCfg && localCfg.whatsapp) || '';
            const finalAddress = (srvSettings && srvSettings.address) || (localCfg && localCfg.address) || '';
            const finalLocation = (srvSettings && srvSettings.location) || (localCfg && localCfg.location) || '';
            const finalLogo = (srvSettings && srvSettings.logoBase64) || (localCfg && localCfg.logoBase64) || '';

            safeSet('cfg-company-name', finalCompanyName);
            safeSet('cfg-reg-number', finalRegNumber);
            safeSet('cfg-tax-number', finalTaxNumber);
            safeSet('cfg-phone', finalPhone);
            safeSet('cfg-whatsapp', finalWhatsapp);
            safeSet('cfg-address', finalAddress);
            safeSet('cfg-location', finalLocation);

            if (finalLogo) {
                const img = document.getElementById('logo-preview');
                if (img) {
                    img.src = finalLogo;
                    img.style.display = 'block';
                }
            }

            // Subscription Status & Live Tracking
            const userObj = JSON.parse(localStorage.getItem('user') || '{}');
            const validUntilStr = (srvTenant && srvTenant.valid_until) || userObj.valid_until || localStorage.getItem('tenant_valid_until');
            const tenantStatus = (srvTenant && srvTenant.status) || userObj.status || 'active';
            const planName = (srvTenant && srvTenant.plan_name) || 'الباقة المؤسسية المعتمدة';

            // Keep local caches aligned
            if (validUntilStr) {
                localStorage.setItem('tenant_valid_until', validUntilStr);
                if (userObj.username && (!userObj.valid_until || userObj.valid_until !== validUntilStr || userObj.company_name !== finalCompanyName)) {
                    userObj.valid_until = validUntilStr;
                    userObj.company_name = finalCompanyName;
                    localStorage.setItem('user', JSON.stringify(userObj));
                }
            }

            const subStatusEl = document.getElementById('subscription-status');
            const subPlanEl = document.getElementById('sub-plan-name');
            const subAccEl = document.getElementById('sub-account-status');
            const subExpEl = document.getElementById('sub-expiry-date');
            const subDaysEl = document.getElementById('sub-days-remaining');

            if (subPlanEl) subPlanEl.textContent = planName;

            if (validUntilStr) {
                const validDate = new Date(validUntilStr);
                const now = new Date();
                const diffTime = validDate - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const dateStr = validDate.toISOString().split('T')[0];

                if (subExpEl) subExpEl.textContent = dateStr;

                if (diffDays > 0) {
                    let color = 'rgba(16, 185, 129, 0.2)';
                    let textColor = '#10b981';
                    if (diffDays <= 30) { color = 'rgba(245, 158, 11, 0.2)'; textColor = '#f59e0b'; }
                    if (diffDays <= 7) { color = 'rgba(239, 68, 68, 0.2)'; textColor = '#ef4444'; }

                    if (subStatusEl) {
                        subStatusEl.innerHTML = `⏳ تنتهي في: ${dateStr} (متبقي ${diffDays} يوم)`;
                        subStatusEl.style.background = color;
                        subStatusEl.style.color = textColor;
                        subStatusEl.style.border = `1px solid ${textColor}`;
                    }
                    if (subAccEl) {
                        subAccEl.innerHTML = `<span style="color:#10b981; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-circle-check"></i> ${tenantStatus === 'active' ? 'حساب نشط ومعتمد' : tenantStatus}</span>`;
                    }
                    if (subDaysEl) {
                        subDaysEl.innerHTML = `<span style="color:${textColor}; font-size:1.15rem; font-weight:900;">${diffDays} يوم</span>`;
                    }
                } else {
                    if (subStatusEl) {
                        subStatusEl.innerHTML = '⚠️ الاشتراك منتهي!';
                        subStatusEl.style.background = 'rgba(239, 68, 68, 0.2)';
                        subStatusEl.style.color = '#ef4444';
                        subStatusEl.style.border = '1px solid #ef4444';
                    }
                    if (subAccEl) {
                        subAccEl.innerHTML = '<span style="color:#ef4444; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-triangle-exclamation"></i> اشتراك منتهي الصلاحية</span>';
                    }
                    if (subDaysEl) {
                        subDaysEl.innerHTML = '<span style="color:#ef4444; font-size:1.15rem; font-weight:900;">منتهي منذ ' + Math.abs(diffDays) + ' يوم</span>';
                    }
                }
            } else {
                if (subStatusEl) {
                    subStatusEl.innerHTML = '✨ فترة تجريبية مفتوحة';
                    subStatusEl.style.background = 'rgba(56, 189, 248, 0.1)';
                    subStatusEl.style.color = '#38bdf8';
                    subStatusEl.style.border = '1px solid rgba(56, 189, 248, 0.3)';
                }
                if (subExpEl) subExpEl.textContent = 'غير محدد (فترة تجريبية)';
                if (subDaysEl) subDaysEl.textContent = 'مفتوح';
                if (subAccEl) {
                    subAccEl.innerHTML = '<span style="color:#38bdf8; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-circle-check"></i> فترة تجريبية نشطة</span>';
                }
            }
        };

        // Initial sync from server-injected state
        window.applyCompanyAndSubscriptionData(window.SERVER_TENANT, window.SERVER_SETTINGS, cfg);

        // Background live check to ensure up-to-the-minute freshness from MySQL
        try {
            fetch('settings.php?action=get_company_subscription_data')
                .then(r => r.json())
                .then(res => {
                    if (res && res.success && res.tenant) {
                        window.SERVER_TENANT = res.tenant;
                        if (res.settings) window.SERVER_SETTINGS = res.settings;
                        window.applyCompanyAndSubscriptionData(res.tenant, res.settings, cfg);
                        console.log('Live company & subscription data re-synced from MySQL.');
                    }
                }).catch(e => console.warn('Company subscription background fetch:', e));
        } catch(e) {}

        // Financial Currencies
        if (cfg.currency) {
            const baseEl = document.getElementById('cfg-currency');
            if (baseEl) baseEl.value = cfg.currency;
        }
        if (cfg.foreignCurrency) {
            const foreignEl = document.getElementById('cfg-foreign-currency');
            if (foreignEl) foreignEl.value = cfg.foreignCurrency;
        }

        // Safe DOM Setters
        const safeSetVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
        const safeSetCheck = (id, checked) => { const el = document.getElementById(id); if (el) el.checked = checked; };

        // Immutability Lock Verification
        const isLocked = cfg.initialSetupCompleted || localStorage.getItem('tenant_currencies_locked') === 'true';
        if (isLocked) {
            const baseSelect = document.getElementById('cfg-currency');
            const foreignSelect = document.getElementById('cfg-foreign-currency');
            if (baseSelect) { baseSelect.disabled = true; baseSelect.style.cursor = 'not-allowed'; baseSelect.style.opacity = '0.75'; }
            if (foreignSelect) { foreignSelect.disabled = true; foreignSelect.style.cursor = 'not-allowed'; foreignSelect.style.opacity = '0.75'; }

            ['cfg-lock-badge', 'cfg-base-lock-icon', 'cfg-foreign-lock-icon'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'inline-block';
            });
            ['currency-lock-notice', 'cfg-base-select-lock', 'cfg-foreign-select-lock'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'block';
            });
        }

        if (typeof onCurrencySelectionChange === 'function') {
            onCurrencySelectionChange();
        }
        if (cfg.vat !== undefined) safeSetVal('cfg-vat', cfg.vat);
        if (cfg.decimalPlaces !== undefined) safeSetVal('cfg-decimal-places', cfg.decimalPlaces);
        if (cfg.fiscalYear) safeSetVal('cfg-fiscal-year', cfg.fiscalYear);

        // Exchange Rates
        const usdEl = document.getElementById('cfg-rate-usd');
        if (usdEl) {
            let savedUsd = cfg.rateUSD || cfg.rate_usd || cfg.usdRate || dedicatedRate;
            if (!savedUsd && window.getExchangeRateForCurrency) {
                 savedUsd = window.getExchangeRateForCurrency('USD');
            }
            console.log('loadSettings: savedUsd candidates:', { 'cfg.rateUSD': cfg.rateUSD, 'cfg.rate_usd': cfg.rate_usd, 'cfg.usdRate': cfg.usdRate, 'dedicatedRate': dedicatedRate, 'final': savedUsd });
            const parsed = parseNum(savedUsd);
            console.log('loadSettings: parsed rate =', parsed);
            if (parsed > 0) {
                usdEl.value = parsed;
                console.log('loadSettings: SET usdEl.value =', parsed);
            } else {
                console.log('loadSettings: WARNING - no valid rate found, usdEl stays empty');
            }
        }
        const modeSelect = document.getElementById('cfg-exchange-mode');
        if (modeSelect) {
            let activeMode = cfg.exchangeMode;
            if (!activeMode) {
                activeMode = 'global';
            }
            modeSelect.value = activeMode;
            if (typeof handleExchangeModeChange === 'function') {
                handleExchangeModeChange(modeSelect);
            }
        }
        if (cfg.rateSAR != null) safeSetVal('cfg-rate-sar', cfg.rateSAR);
        if (cfg.rateAED != null) safeSetVal('cfg-rate-aed', cfg.rateAED);

        // Inventory Rules
        safeSetCheck('cfg-allow-negative-stock', cfg.allowNegativeStock || false);
        safeSetCheck('cfg-two-step-sale', cfg.twoStepSale !== false);
        safeSetCheck('cfg-require-customer', cfg.requireCustomer || false);
        safeSetCheck('cfg-prevent-below-cost', cfg.preventBelowCost || false);
        safeSetCheck('cfg-low-stock-alerts', cfg.lowStockAlerts !== false);
        safeSetCheck('cfg-fifo-policy', cfg.fifoPolicy !== false);
        safeSetCheck('cfg-prevent-expired-sale', cfg.preventExpiredSale !== false);
        safeSetCheck('cfg-enable-barcode', cfg.enableBarcode !== false);
        safeSetCheck('cfg-auto-post-gl', cfg.autoPostGl !== false);

        // Printing
        const printCfg = cfg.print || {
            sales: { size: cfg.printSize || 'A4', qr: 'true', logo: true, signatures: false, footer: cfg.invoiceFooter || '' },
            purchases: { size: 'A4', logo: true, signatures: true, footer: '' },
            quotes: { size: 'A4', logo: true, signatures: true, terms: cfg.invoiceTerms || '' },
            reports: { size: 'A4', header: true, timestamp: true }
        };
        
        // Sales
        safeSetVal('cfg-print-sales-size', printCfg.sales.size);
        safeSetVal('cfg-print-sales-qr', printCfg.sales.qr);
        safeSetCheck('cfg-print-sales-logo', printCfg.sales.logo);
        safeSetCheck('cfg-print-sales-signatures', printCfg.sales.signatures);
        safeSetVal('cfg-print-sales-footer', printCfg.sales.footer);
        
        // Purchases
        safeSetVal('cfg-print-purchases-size', printCfg.purchases.size);
        safeSetCheck('cfg-print-purchases-logo', printCfg.purchases.logo);
        safeSetCheck('cfg-print-purchases-signatures', printCfg.purchases.signatures);
        safeSetVal('cfg-print-purchases-footer', printCfg.purchases.footer);
        
        // Quotes
        safeSetVal('cfg-print-quotes-size', printCfg.quotes.size);
        safeSetCheck('cfg-print-quotes-logo', printCfg.quotes.logo);
        safeSetCheck('cfg-print-quotes-signatures', printCfg.quotes.signatures);
        safeSetVal('cfg-print-quotes-terms', printCfg.quotes.terms);
        
        // Reports
        safeSetVal('cfg-print-reports-size', printCfg.reports.size);
        safeSetCheck('cfg-print-reports-header', printCfg.reports.header);
        safeSetCheck('cfg-print-reports-timestamp', printCfg.reports.timestamp);
        
        // SMTP
        if (cfg.smtpServer) safeSetVal('cfg-smtp-server', cfg.smtpServer);
        if (cfg.smtpPort) safeSetVal('cfg-smtp-port', cfg.smtpPort);
        if (cfg.smtpUser) safeSetVal('cfg-smtp-user', cfg.smtpUser);
        if (cfg.smtpPass) safeSetVal('cfg-smtp-pass', cfg.smtpPass);
        if (cfg.backupEmail) safeSetVal('cfg-backup-email', cfg.backupEmail);

        // UI
        if (cfg.fontSize) safeSetVal('cfg-font-size', cfg.fontSize);
        safeSetCheck('cfg-auto-backup', cfg.autoBackup !== false);
        safeSetCheck('cfg-auto-dark', cfg.autoDark !== false);
        safeSetCheck('cfg-show-athkar', cfg.showAthkar !== false);
        if (cfg.autoLogoutTime !== undefined) safeSetVal('cfg-auto-logout', cfg.autoLogoutTime);
    }

        // ─── Save Settings ─────────────────────────────────────────
    function saveSettings() {
        try {
            const getVal = (id, def = '') => { const el = document.getElementById(id); return el ? el.value.trim() : def; };
            const getNum = (id, def = 0) => { const el = document.getElementById(id); return el ? (parseFloat(el.value) || def) : def; };
            const getBool = (id, def = false) => { const el = document.getElementById(id); return el ? !!el.checked : def; };

            const activeCurrencies = Array.from(document.querySelectorAll('#cfg-active-currencies input[type="checkbox"]:checked')).map(cb => cb.value);
            let oldCfg = {};
            const tk = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
            try { oldCfg = JSON.parse(localStorage.getItem(tk) || '{}'); } catch(e) { console.error('Corrupted old settings', e); }

            const baseCurrency = getVal('cfg-currency', oldCfg.currency || 'SDG');
            const foreignCurrency = getVal('cfg-foreign-currency', oldCfg.foreignCurrency || 'USD');

            const cfg = {
                // Company
                logoBase64:        window.pendingLogoBase64 || oldCfg.logoBase64 || '',
                companyName:       getVal('cfg-company-name', oldCfg.companyName || ''),
                regNumber:         getVal('cfg-reg-number', oldCfg.regNumber || ''),
                taxNumber:         getVal('cfg-tax-number', oldCfg.taxNumber || ''),
                phone:             getVal('cfg-phone', oldCfg.phone || ''),
                whatsapp:          getVal('cfg-whatsapp', oldCfg.whatsapp || ''),
                address:           getVal('cfg-address', oldCfg.address || ''),
                location:          getVal('cfg-location', oldCfg.location || ''),

                // Financial
                currency:              baseCurrency,
                foreignCurrency:       foreignCurrency,
                activeCurrencies:      [baseCurrency, foreignCurrency],
                initialSetupCompleted: true,
                vat:                   getNum('cfg-vat', 0),
                decimalPlaces:         parseInt(getVal('cfg-decimal-places', '2')) || 2,
                fiscalYear:            getVal('cfg-fiscal-year', '1'),

                // Exchange Rates
                exchangeMode:      getVal('cfg-exchange-mode', 'global'),
                rateUSD:           (document.getElementById('cfg-rate-usd') && parseNum(document.getElementById('cfg-rate-usd').value) > 0) ? parseNum(document.getElementById('cfg-rate-usd').value) : (oldCfg.rateUSD || 600),
                rateSAR:           (document.getElementById('cfg-rate-sar') && parseNum(document.getElementById('cfg-rate-sar').value) > 0) ? parseNum(document.getElementById('cfg-rate-sar').value) : (oldCfg.rateSAR || 160),
                rateAED:           (document.getElementById('cfg-rate-aed') && parseNum(document.getElementById('cfg-rate-aed').value) > 0) ? parseNum(document.getElementById('cfg-rate-aed').value) : (oldCfg.rateAED || 163),

                // Inventory
                allowNegativeStock: getBool('cfg-allow-negative-stock', false),
                twoStepSale:       getBool('cfg-two-step-sale', true),
                requireCustomer:   getBool('cfg-require-customer', false),
                preventBelowCost:  getBool('cfg-prevent-below-cost', false),
                lowStockAlerts:    getBool('cfg-low-stock-alerts', true),
                fifoPolicy:        getBool('cfg-fifo-policy', true),
                preventExpiredSale: getBool('cfg-prevent-expired-sale', true),
                enableBarcode:     getBool('cfg-enable-barcode', true),
                autoPostGl:        getBool('cfg-auto-post-gl', true),

                // Printing
                print: {
                    sales: {
                        size: getVal('cfg-print-sales-size', 'A4'),
                        qr: getVal('cfg-print-sales-qr', 'true'),
                        logo: getBool('cfg-print-sales-logo', true),
                        signatures: getBool('cfg-print-sales-signatures', false),
                        footer: getVal('cfg-print-sales-footer', '')
                    },
                    purchases: {
                        size: getVal('cfg-print-purchases-size', 'A4'),
                        logo: getBool('cfg-print-purchases-logo', true),
                        signatures: getBool('cfg-print-purchases-signatures', true),
                        footer: getVal('cfg-print-purchases-footer', '')
                    },
                    quotes: {
                        size: getVal('cfg-print-quotes-size', 'A4'),
                        logo: getBool('cfg-print-quotes-logo', true),
                        signatures: getBool('cfg-print-quotes-signatures', true),
                        terms: getVal('cfg-print-quotes-terms', '')
                    },
                    reports: {
                        size: getVal('cfg-print-reports-size', 'A4'),
                        header: getBool('cfg-print-reports-header', true),
                        timestamp: getBool('cfg-print-reports-timestamp', true)
                    }
                },

                // SMTP & Backups
                smtpServer:        getVal('cfg-smtp-server', ''),
                smtpPort:          getVal('cfg-smtp-port', ''),
                smtpUser:          getVal('cfg-smtp-user', ''),
                smtpPass:          getVal('cfg-smtp-pass', ''),
                backupEmail:       getVal('cfg-backup-email', ''),

                // UI
                fontSize:          getVal('cfg-font-size', '16'),
                autoBackup:        getBool('cfg-auto-backup', true),
                autoDark:          getBool('cfg-auto-dark', true),
                showAthkar:        getBool('cfg-show-athkar', true),
                autoLogoutTime:    getVal('cfg-auto-logout', '0'),

                savedAt: new Date().toISOString()
            };

            const activeTenantId = (window.SERVER_TENANT && window.SERVER_TENANT.id) || (JSON.parse(localStorage.getItem('user')||'{}').tenant_id) || '1';
            const settingsKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : ('tenant_' + activeTenantId + '_erp_settings_v1');
            const logoKey = window.getTenantKey ? window.getTenantKey('company_logo_base64') : ('tenant_' + activeTenantId + '_company_logo_base64');
            const strVal = JSON.stringify(cfg);
            
            console.log('=== saveSettings() STRICT TENANT ISOLATION ===');
            console.log('saveSettings: activeTenantId =', activeTenantId);
            console.log('saveSettings: settingsKey =', settingsKey);
            
            // 🛡️ Save EXCLUSIVELY to tenant-isolated storage
            try { localStorage.setItem(settingsKey, strVal); } catch(e) {}
            if (window.DB_STORE) {
                window.DB_STORE[settingsKey] = strVal;
            }
            if (typeof localforage !== 'undefined') {
                localforage.setItem(settingsKey, strVal).catch(() => {});
            }
            
            // Isolate dedicated logo key per tenant
            if (cfg.logoBase64) {
                try { localStorage.setItem(logoKey, cfg.logoBase64); } catch(e) {}
            } else {
                try { localStorage.removeItem(logoKey); } catch(e) {}
            }

            // Also save isolated dedicated rate key
            const rateKey = window.getTenantKey ? window.getTenantKey('erp_rate_usd') : ('tenant_' + activeTenantId + '_erp_rate_usd');
            try { localStorage.setItem(rateKey, String(cfg.rateUSD)); } catch(e) {}
            
            // Scoped tenant cookie backup (lightweight without heavy base64)
            try {
                const minCfg = JSON.stringify({ rateUSD: cfg.rateUSD, exchangeMode: cfg.exchangeMode, companyName: cfg.companyName, currency: cfg.currency, savedAt: cfg.savedAt });
                document.cookie = 'erp_settings_backup_t' + activeTenantId + '=' + encodeURIComponent(minCfg) + '; path=/; max-age=31536000; SameSite=Lax';
                document.cookie = 'erp_rate_usd_backup_t' + activeTenantId + '=' + cfg.rateUSD + '; path=/; max-age=31536000; SameSite=Lax';
            } catch(e) {}
            // --- Log Exchange Rate History ---
            try {
                if (cfg.rateUSD && (oldCfg.rateUSD !== cfg.rateUSD || oldCfg.exchangeMode !== cfg.exchangeMode)) {
                    const historyKey = typeof window.getTenantKey === 'function' ? window.getTenantKey('erp_exchange_rate_history') : 'erp_exchange_rate_history';
                    let hist = [];
                    try { hist = JSON.parse(localStorage.getItem(historyKey) || '[]'); } catch(e){}
                    
                    const usr = JSON.parse(localStorage.getItem('user')||'{}');
                    const now = new Date();
                    
                    hist.push({
                        id: 'HR-' + Date.now(),
                        rate: cfg.rateUSD,
                        date: now.toISOString().split('T')[0],
                        time: now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12:true}),
                        user: usr.name || usr.username || 'مدير النظام',
                        source: cfg.exchangeMode
                    });
                    localStorage.setItem(historyKey, JSON.stringify(hist));
                    if (typeof window.renderExchangeRateHistory === 'function') {
                        window.renderExchangeRateHistory();
                    }
                }
            } catch(e) { console.error('History log error:', e); }
            // --- End Log ---
            
            // Verify immediately after save
            try {
                const verifyVal = localStorage.getItem('erp_settings_v1');
                const verifyParsed = JSON.parse(verifyVal);
                console.log('saveSettings: VERIFY after save - rateUSD =', verifyParsed.rateUSD, ', savedAt =', verifyParsed.savedAt);
            } catch(e) { console.error('saveSettings: VERIFY FAILED:', e.message); }
            
            localStorage.setItem('tenant_currencies_locked', 'true');

            // Apply immutability lock immediately upon saving
            const baseSelect = document.getElementById('cfg-currency');
            const foreignSelect = document.getElementById('cfg-foreign-currency');
            if (baseSelect) baseSelect.disabled = true;
            if (foreignSelect) foreignSelect.disabled = true;

            const lockBadgeEl = document.getElementById('cfg-lock-badge');
            if (lockBadgeEl) lockBadgeEl.style.display = 'inline-block';

            const noticeEl = document.getElementById('currency-lock-notice');
            if (noticeEl) noticeEl.style.display = 'block';

            if (window.saveToDB) {
                window.saveToDB('erp_settings_v1', cfg);
            }

            // Persist full company & ERP settings to MySQL backend
            try {
                fetch('settings.php?action=save_company_settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(cfg)
                }).then(r => r.json()).then(res => {
                    if (res && res.success) {
                        console.log('Company and ERP settings synced to MySQL successfully.');
                        if (window.SERVER_TENANT) {
                            window.SERVER_TENANT.company_name = cfg.companyName;
                            window.SERVER_TENANT.owner_phone = cfg.phone;
                            window.SERVER_TENANT.phone2 = cfg.whatsapp;
                        }
                        if (window.SERVER_SETTINGS) {
                            Object.assign(window.SERVER_SETTINGS, cfg);
                        } else {
                            window.SERVER_SETTINGS = Object.assign({}, cfg);
                        }
                        try {
                            const u = JSON.parse(localStorage.getItem('user') || '{}');
                            u.company_name = cfg.companyName;
                            localStorage.setItem('user', JSON.stringify(u));
                            localStorage.setItem('companyName', cfg.companyName);
                        } catch(e) {}
                    }
                }).catch(e => console.warn('MySQL company settings sync warning:', e));
            } catch(e) {}
            
            // Send SMTP & Backup settings to backend
            try {
                fetch('api_index.php?r=smtp_settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ payload: { smtpServer: cfg.smtpServer, smtpPort: cfg.smtpPort, smtpUser: cfg.smtpUser, smtpPass: cfg.smtpPass, backupEmail: cfg.backupEmail } })
                }).catch(e => console.error('SMTP sync error:', e));
            } catch(e) {}

            if (cfg.fontSize) {
                document.documentElement.style.fontSize = cfg.fontSize + 'px';
            }

            const badge = document.getElementById('save-badge');
            if (badge) {
                badge.classList.add('show');
                setTimeout(() => badge.classList.remove('show'), 3000);
            }

            if (window.logSystemAction) {
                window.logSystemAction('Settings Update', 'settings', 'تم تحديث إعدادات النظام');
            }

            // Sync posting mode with the posting system
            localStorage.setItem('erp_posting_mode', cfg.autoPostGl ? 'auto' : 'manual');

            // Broadcast tenant exchange rate to server for multi-user sync
            if (cfg.exchangeMode) {
                const payload = {
                    exchangeMode: cfg.exchangeMode,
                    rateUSD: cfg.rateUSD,
                    rateSAR: cfg.rateSAR,
                    rateAED: cfg.rateAED
                };
                fetch('api_index.php/tenant-exchange-rate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('authToken')}` },
                    body: JSON.stringify({ payload: payload })
                }).catch(e => console.error('Error broadcasting rate:', e));
            }

            window.isSettingsDirty = false;
            const saveBtn = document.getElementById('save-settings-btn');
            if (saveBtn) {
                saveBtn.classList.remove('pulse-alert');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('✅ تم حفظ التعديلات والإعدادات بنجاح!', 'success');
            } else {
                alert('✅ تم حفظ التعديلات والإعدادات بنجاح!');
            }
        } catch(err) {
            console.error('Error saving settings:', err);
            alert('⚠️ حدث خطأ عند حفظ الإعدادات: ' + err.message);
        }
    }

    function previewFontSize(size) {
        document.documentElement.style.fontSize = size + 'px';
    }

    // ─── Backup Data ───────────────────────────────────────────
    async function backupData() {
        try {
            const completeStore = {};
            const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';

            // 1. Gather all keys from localStorage
            const globalKeys = ['saas_token', 'user', 'erp_lang', 'magaiees_global_theme', 'authToken'];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && (prefix === '' || k.startsWith(prefix) || globalKeys.includes(k))) {
                    const val = localStorage.getItem(k);
                    if (val !== null) completeStore[k] = val;
                }
            }

            // 2. Gather all keys from localforage (async)
            if (typeof localforage !== 'undefined') {
                try {
                    const lfKeys = await localforage.keys();
                    for (let k of lfKeys) {
                        if (prefix === '' || k.startsWith(prefix)) {
                            const val = await localforage.getItem(k);
                            if (val !== null) {
                                try { completeStore[k] = JSON.parse(val); } catch(e) { completeStore[k] = val; }
                            }
                        }
                    }
                } catch(e) { console.error("Error reading localforage:", e); }
            }

            // 3. Overlay DB_STORE memory proxy
            if (window.DB_STORE) {
                for (let [k, v] of Object.entries(window.DB_STORE)) {
                    if (prefix === '' || k.startsWith(prefix)) {
                        if (v !== null && v !== undefined) {
                            completeStore[k] = v;
                        }
                    }
                }
            }

            // Clean up empty data
            const cleanStore = {};
            let recordCount = 0;
            for (let [k, v] of Object.entries(completeStore)) {
                if (v && (Array.isArray(v) ? v.length > 0 : (typeof v === 'object' ? Object.keys(v).length > 0 : true))) {
                    cleanStore[k] = v;
                    if (Array.isArray(v)) recordCount += v.length;
                }
            }

            if (Object.keys(cleanStore).length === 0) {
                alert("⚠️ لا توجد بيانات مسجلة بالنظام للتصدير حالياً.");
                return;
            }

            // 4. Custom UI 6-Digit PIN Prompt
            const pin = await promptCustomPin({
                title: "حماية النسخة الاحتياطية",
                subtitle: "أدخل رقماً سرياً مكوناً من 6 أرقام لحماية ملف النسخة الاحتياطية، أو اختر التنزيل بدون رقم سري:",
                showSkip: true
            });

            if (pin === null) return; // User clicked Cancel

            let pinHash = null;
            if (pin && pin.trim() !== "") {
                const msgBuffer = new TextEncoder().encode(pin.trim());
                const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                pinHash = Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');
            }

            // 5. Custom UI Confirmation
            const confirmed = await confirmCustomModal({
                title: "تأكيد تنزيل النسخة الاحتياطية",
                subtitle: `سيتم تجميع كافة بيانات النظام الخاصة بالشركة الحالية وتنزيلها كملف احتياطي (يحتوي على ${recordCount} سجل)${pinHash ? ' 🔒 محمي برقم سري' : ''}. هل ترغب بالاستمرار؟`,
                confirmText: "تنزيل الآن 🚀",
                icon: "💾",
                isDanger: false
            });

            if (!confirmed) return;

            const refCode = 'REF-' + Math.random().toString(36).substr(2, 6).toUpperCase();

            // Dynamic Token System: We no longer store masterRecoveryHash.
            // Only the refCode and user's pinHash are saved.
            const exportPayload = {
                magaiees_backup_v3: true,
                refCode: refCode,
                isProtected: !!pinHash,
                pinHash: pinHash,
                store: cleanStore
            };

            const dateStr = new Date().toISOString().split('T')[0];
            const fileName = `MAGAIEES_ERP_Backup_${dateStr}.bak.json`;
            const backupDataStr = JSON.stringify(exportPayload, null, 2);

            const blob = new Blob([backupDataStr], { type: "application/json;charset=utf-8" });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction('Database Export', 'system', 'تصدير نسخة احتياطية للشركة الحالية');
            }
            alert(`✅ تم تنزيل النسخة الاحتياطية بنجاح! (تم تصدير ${recordCount} سجل خاص بهذه الشركة)`);
        } catch(e) {
            console.error(e);
            alert("❌ حدث خطأ أثناء تنزيل النسخة الاحتياطية: " + e.message);
        }
    }

    async function restoreData(elem) {
        const file = elem.files ? elem.files[0] : null;
        if (!file) return;

        const confirmed = await confirmCustomModal({
            title: "تحذير استعادة البيانات",
            subtitle: "استعادة النسخة الاحتياطية ستقوم بدمج وتحديث كافة البيانات الحالية ببيانات الملف المرفوع. هل أنت متأكد 100% أنك تريد الاستمرار؟",
            confirmText: "تأكيد الاستعادة ⚠️",
            icon: "⚠️",
            isDanger: true
        });

        if (!confirmed) {
            elem.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = async function(e) {
            let loader = null;
            try {
                const content = e.target.result;
                
                // Check if it's a SQL file or JSON file
                if (content.trim().startsWith('--') || content.toUpperCase().includes('INSERT INTO')) {
                    if (typeof restoreFromSqlDump === 'function') {
                        restoreFromSqlDump(content);
                        return;
                    }
                }

                const data = JSON.parse(content);
                if (typeof data !== 'object' || Array.isArray(data) || data === null) {
                    throw new Error("تنسيق الملف غير صالح");
                }

                let payloadStore = data;
                if (data.magaiees_backup_v2 || data.isProtected || data.pinHash) {
                    if (data.isProtected && data.pinHash) {
                        const refCode = data.refCode || '';
                        const enteredPin = await promptCustomPin({
                            title: "الملف محمي برقم سري",
                            subtitle: "هذه النسخة الاحتياطية محمية برقم سري. يرجى إدخال الرقم السري المكون من 6 أرقام (أو المفتاح الماستر للإدارة):",
                            refCode: refCode,
                            showSkip: false
                        });

                        if (!enteredPin) {
                            alert("❌ تم إلغاء عملية الاستعادة لحماية البيانات.");
                            elem.value = '';
                            return;
                        }

                        const cleanPin = enteredPin.trim();

                        // Compute hashes for entered PIN (User's personal PIN)
                        const msgBuffer = new TextEncoder().encode(cleanPin);
                        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                        const enteredHash = Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');

                        // Dynamic Master Token Generation
                        let expectedDynamicToken = "";
                        if (refCode) {
                            const salt = "Magaiees_ERP_2026_SaaS_Secr3t";
                            const dynBuffer = new TextEncoder().encode(refCode + salt);
                            const dynHash = await crypto.subtle.digest('SHA-256', dynBuffer);
                            const hashArray = Array.from(new Uint8Array(dynHash));
                            const num = (hashArray[0] << 24 | hashArray[1] << 16 | hashArray[2] << 8 | hashArray[3]) >>> 0;
                            expectedDynamicToken = (num % 1000000).toString().padStart(6, '0');
                        }

                        // Check 1: Direct PIN match (User's PIN)
                        // Check 2: Dynamic Master Token match (Admin generated)
                        const isPinMatch = (enteredHash === data.pinHash);
                        const isMasterMatch = (cleanPin === expectedDynamicToken && expectedDynamicToken !== "");

                        if (!isPinMatch && !isMasterMatch) {
                            alert(`❌ الرقم السري غير صحيح!\n\nرمز مرجعية الملف: [${refCode || 'N/A'}]\nيرجى التواصل مع إدارة المشتركين وتزويدهم بالرمز لاسترداد كلمة السر.`);
                            elem.value = '';
                            if (loader) loader.remove();
                            return;
                        }

                        if (isMasterMatch && !isPinMatch) {
                            alert("🔓 تم فك تشفير واستعادة النسخة الاحتياطية بنجاح باستخدام المفتاح الماستر للإدارة!");
                        }
                    }
                    payloadStore = data.store || {};
                }

                // Loader screen
                loader = document.createElement('div');
                loader.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.95); backdrop-filter:blur(15px); z-index:999999; display:flex; flex-direction:column; align-items:center; justify-content:center; color:white; font-family:Tajawal; text-align:center; padding:20px;';
                loader.innerHTML = `
                    <div style="font-size:3.5rem; margin-bottom:15px; animation:spin 2s linear infinite;">🔄</div>
                    <h2 style="font-size:1.8rem; font-weight:900; margin:0 0 10px 0; color:#38bdf8;">جاري استعادة البيانات والتهيأة...</h2>
                    <p style="color:#94a3b8; font-size:1rem; margin:0;">يرجى عدم إغلاق المتصفح حتى إكتمال العملية.</p>
                `;
                document.body.appendChild(loader);

                // 1. Preserve critical session tokens
                const sessionKeys = ['authToken', 'saas_token', 'user', 'erp_lang', 'magaiees_global_theme'];
                const preservedSession = {};
                sessionKeys.forEach(k => {
                    const val = localStorage.getItem(k);
                    if (val !== null) preservedSession[k] = val;
                });

                // 2. Clear current localforage
                const prefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                try {
                    if (typeof localforage !== 'undefined') {
                        const keys = await localforage.keys();
                        for (let k of keys) {
                            if (k.startsWith(prefix) || prefix === '') {
                                await localforage.removeItem(k);
                            }
                        }
                    }
                } catch(err) {}

                // 3. Clear current localStorage erp_ & tenant_ keys
                const lsKeys = [];
                for (let i = 0; i < localStorage.length; i++) {
                    lsKeys.push(localStorage.key(i));
                }
                for (let k of lsKeys) {
                    if (k && !sessionKeys.includes(k) && (k.startsWith('erp_') || k.startsWith('tenant_') || k.startsWith(prefix))) {
                        localStorage.removeItem(k);
                    }
                }

                // 4. Populate imported backup with Smart Deduplicated Merging
                let restoredCount = 0;
                const currentPrefix = typeof getTenantPrefix === 'function' ? getTenantPrefix() : '';
                const mergedStore = {};

                for (let [key, val] of Object.entries(payloadStore)) {
                    let targetKey = key;
                    if (currentPrefix && key.startsWith('tenant_')) {
                        targetKey = key.replace(/^tenant_[^_]+_/, currentPrefix);
                    } else if (currentPrefix && key.startsWith('erp_')) {
                        targetKey = currentPrefix + key;
                    }

                    // Parse double-stringified JSON if present
                    if (typeof val === 'string' && (val.startsWith('[') || val.startsWith('{'))) {
                        try { val = JSON.parse(val); } catch(e) {}
                    }

                    // Smart Merge to prevent smaller/empty company arrays from overwriting main company data
                    if (mergedStore[targetKey]) {
                        let existing = mergedStore[targetKey];
                        if (Array.isArray(val) && Array.isArray(existing)) {
                            const map = new Map();
                            existing.forEach(item => {
                                const id = item && (item.id || item.code || item.invoiceNo) ? (item.id || item.code || item.invoiceNo) : JSON.stringify(item);
                                map.set(id, item);
                            });
                            val.forEach(item => {
                                const id = item && (item.id || item.code || item.invoiceNo) ? (item.id || item.code || item.invoiceNo) : JSON.stringify(item);
                                map.set(id, item);
                            });
                            mergedStore[targetKey] = Array.from(map.values());
                        } else if (typeof val === 'object' && val !== null) {
                            mergedStore[targetKey] = Object.assign({}, existing, val);
                        } else {
                            if (val) mergedStore[targetKey] = val;
                        }
                    } else {
                        mergedStore[targetKey] = val;
                    }
                }

                // Write mergedStore to localStorage, localforage, and DB_STORE
                for (let [targetKey, val] of Object.entries(mergedStore)) {
                    const valStr = typeof val === 'string' ? val : JSON.stringify(val);
                    try { localStorage.setItem(targetKey, valStr); } catch(err) {}
                    try {
                        if (typeof localforage !== 'undefined') {
                            await localforage.setItem(targetKey, val); // Store as native Object/Array
                        }
                    } catch(err) {}
                    if (window.DB_STORE) {
                        window.DB_STORE[targetKey] = val; // Store as native Object/Array
                    }
                    restoredCount++;
                }

                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('Database Import', 'system', 'استعادة نسخة احتياطية ومعالجة البيانات بنجاح');
                }

                alert(`🎉 تم استعادة كافة البيانات بنجاح (${restoredCount} جدول/سجل معالَج بنجاح)! سيتم تحديث الصفحة الآن.`);
                window.location.reload();
            } catch(err) {
                console.error(err);
                alert("❌ خطأ: ملف النسخة الاحتياطية غير صالح أو تالف (" + err.message + ")");
                elem.value = '';
                if (loader) loader.remove();
            }
        };
        reader.readAsText(file);
    }

    document.addEventListener('DBReady', loadSettings);
    document.addEventListener('strictDataReady', loadSettings);
    document.addEventListener('cloudDataRestored', loadSettings);

    if (window.APP_READY) {
        initSettingsPage();
    } else {
        document.addEventListener('DBReady', initSettingsPage);
        document.addEventListener('DOMContentLoaded', initSettingsPage);
    }
</script>

<!-- ==================================================================== -->
<!-- CUSTOM MAGAIEES PREMIUM GLASSMORPHISM MODALS (PIN & CONFIRMATION)      -->
<!-- ==================================================================== -->
<style>
.mg-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
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
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); outline: none; box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);
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
    cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.38);
    display: flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Tajawal';
}
.mg-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37, 99, 235, 0.55); filter: brightness(1.1); }

.mg-btn-secondary {
    background: rgba(51, 65, 85, 0.6); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 14px; padding: 12px; font-weight: 600; font-size: 0.95rem; cursor: pointer;
    transition: all 0.2s ease; font-family: 'Tajawal';
}
.mg-btn-secondary:hover { background: rgba(51, 65, 85, 0.9); color: #fff; transform: translateY(-1px); }

.mg-btn-danger {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff;
    border: none; border-radius: 14px; padding: 14px; font-weight: 700; font-size: 1rem;
    cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.38); font-family: 'Tajawal';
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
<div id="unsaved-confirm-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); z-index:9999999; justify-content:center; align-items:center; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); font-family:'Tajawal', sans-serif;">
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
    animation: savePulse 1.5s infinite;
}
@keyframes savePulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>

<script>
(function() {
    window.isSettingsDirty = false;
    let pendingNavigationTarget = null;

    function initDirtyTracking() {
        window.isSettingsDirty = false;
        
        const container = document.body;
        container.addEventListener('change', function(e) {
            if (e.target.matches('input, select, textarea')) {
                window.isSettingsDirty = true;
                const saveBtn = document.getElementById('save-settings-btn');
                if (saveBtn) saveBtn.classList.add('pulse-alert');
            }
        });

        container.addEventListener('input', function(e) {
            if (e.target.matches('input, select, textarea')) {
                window.isSettingsDirty = true;
                const saveBtn = document.getElementById('save-settings-btn');
                if (saveBtn) saveBtn.classList.add('pulse-alert');
            }
        });
    }

    window.promptUnsavedChanges = function(targetUrl = null) {
        pendingNavigationTarget = targetUrl;
        const modal = document.getElementById('unsaved-confirm-modal');
        if (modal) modal.style.display = 'flex';
    };

    document.addEventListener('click', function(e) {
        if (!window.isSettingsDirty) return;

        if (e.target.closest('#save-settings-btn, .save-btn-main, #btn-unsaved-save-now, #btn-unsaved-leave-no-save, #btn-unsaved-cancel')) {
            return;
        }

        const navLink = e.target.closest('a[href], .sidebar-item, [data-href], button[onclick*="location"]');
        if (navLink) {
            let target = navLink.getAttribute('href') || navLink.getAttribute('data-href');
            if (!target && navLink.getAttribute('onclick')) {
                const match = navLink.getAttribute('onclick').match(/location\.href\s*=\s*['"]([^'"]+)['"]/);
                if (match) target = match[1];
            }

            if (target && !target.startsWith('#') && !target.startsWith('javascript:')) {
                e.preventDefault();
                e.stopPropagation();
                window.promptUnsavedChanges(target);
            }
        }
    }, true);

    // Unsaved changes tracking handled via custom in-page navigation checks


    document.addEventListener('DOMContentLoaded', function() {
        initDirtyTracking();

        const saveNowBtn = document.getElementById('btn-unsaved-save-now');
        const leaveBtn = document.getElementById('btn-unsaved-leave-no-save');
        const cancelBtn = document.getElementById('btn-unsaved-cancel');
        const modal = document.getElementById('unsaved-confirm-modal');

        if (saveNowBtn) {
            saveNowBtn.onclick = function() {
                if (typeof saveSettings === 'function') {
                    saveSettings();
                }
                window.isSettingsDirty = false;
                if (modal) modal.style.display = 'none';
                if (pendingNavigationTarget) {
                    window.location.href = pendingNavigationTarget;
                }
            };
        }

        if (leaveBtn) {
            leaveBtn.onclick = function() {
                window.isSettingsDirty = false;
                if (modal) modal.style.display = 'none';
                if (pendingNavigationTarget) {
                    window.location.href = pendingNavigationTarget;
                }
            };
        }

        if (cancelBtn) {
            cancelBtn.onclick = function() {
                if (modal) modal.style.display = 'none';
                pendingNavigationTarget = null;
            };
        }
    });
})();
</script>

</body>
</html>
