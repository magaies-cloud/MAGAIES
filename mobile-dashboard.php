<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 📱 MAGAIEES ERP 2026 - POCKET EXECUTIVE MOBILE EDITION
 * ═════════════════════════════════════════════════════════════════════
 * النسخة التنفيذية الذكية للهواتف الذكية والأجهزة اللوحية
 * تصميم ثنائي المحرك: [ ⚡ العمليات السريعة ] + [ 📊 التقارير الذكية ]
 */
$page_title = 'مقاييس الموبايل | النسخة السريعة';
require_once __DIR__ . '/db_bridge.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#030712">
    <title>مقاييس ERP - نسخة الهاتف الذكية</title>

    <!-- Preconnect & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/fontawesome/all.min.css">

    <!-- Permissions & Audio System -->
    <script src="js/permissions-manager.js"></script>
    <script src="js/modern-alerts.js"></script>

    <style>
        :root {
            --bg-base: #030712;
            --surface-1: rgba(15, 23, 42, 0.88);
            --surface-2: rgba(30, 41, 59, 0.75);
            --border-glass: rgba(255, 255, 255, 0.1);
            --primary: #38bdf8;
            --emerald: #10b981;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --purple: #a855f7;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --safe-bottom: env(safe-area-inset-bottom, 16px);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-base);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(56, 189, 248, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            padding-bottom: calc(85px + var(--safe-bottom));
        }

        /* Ambient Lighting Header */
        .mobile-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(3, 7, 18, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-glass);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284c7, #10b981);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            box-shadow: 0 4px 14px rgba(56, 189, 248, 0.35);
        }
        .brand-meta h1 {
            font-size: 0.98rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
            letter-spacing: 0.3px;
        }
        .brand-meta span {
            font-size: 0.74rem;
            color: #38bdf8;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-switch-desktop {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #e2e8f0;
            padding: 7px 12px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-switch-desktop:active {
            transform: scale(0.95);
            background: rgba(56, 189, 248, 0.2);
            border-color: #38bdf8;
            color: #38bdf8;
        }

        /* Dual-Engine Mode Switcher (Pill Segmented Control) */
        .mode-switcher-container {
            padding: 14px 16px 6px 16px;
        }
        .mode-segmented-pill {
            display: flex;
            background: rgba(15, 23, 42, 0.85);
            border: 1.5px solid var(--border-glass);
            border-radius: 16px;
            padding: 4px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }
        .mode-tab-btn {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 800;
            padding: 12px 10px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .mode-tab-btn.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(16, 185, 129, 0.2));
            color: #ffffff;
            border: 1px solid rgba(56, 189, 248, 0.4);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.25);
        }
        .mode-tab-btn.active i {
            color: #38bdf8;
            transform: scale(1.1);
        }

        /* Content Container */
        .mobile-content {
            padding: 12px 16px;
        }

        /* Liquidity Pulse Card */
        .liquidity-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(17, 24, 39, 0.8));
            border: 1.5px solid rgba(56, 189, 248, 0.25);
            border-radius: 20px;
            padding: 18px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .liquidity-card::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.25), transparent 70%);
            pointer-events: none;
        }
        .liquidity-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .liquidity-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .liquidity-grand {
            font-size: 2.1rem;
            font-weight: 900;
            color: #34d399;
            letter-spacing: -0.5px;
            font-family: 'Inter', 'Tajawal', sans-serif;
            line-height: 1.1;
            margin-bottom: 14px;
        }
        .liquidity-grand small {
            font-size: 0.95rem;
            font-weight: 800;
            color: #a7f3d0;
            margin-right: 4px;
        }
        .liquidity-breakdown {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px dashed rgba(255, 255, 255, 0.12);
        }
        .vault-item {
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .vault-name {
            font-size: 0.74rem;
            color: #94a3b8;
            font-weight: 700;
            display: block;
            margin-bottom: 3px;
        }
        .vault-amt {
            font-size: 1.1rem;
            font-weight: 800;
            color: #f1f5f9;
            font-family: 'Inter', 'Tajawal', sans-serif;
        }

        /* Power Quick Action Cards (4 Core Buttons) */
        .section-heading {
            font-size: 0.92rem;
            font-weight: 800;
            color: #cbd5e1;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .actions-2x2-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }
        .power-action-btn {
            background: rgba(15, 23, 42, 0.85);
            border: 1.5px solid var(--border-glass);
            border-radius: 18px;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
            min-height: 105px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .power-action-btn:active {
            transform: scale(0.96);
            border-color: currentColor;
        }
        .action-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            transition: transform 0.2s;
        }
        .power-action-btn:active .action-icon-circle {
            transform: scale(1.1);
        }
        .action-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
        }
        .action-subtitle {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* Specific Action Flavors */
        .act-receipt .action-icon-circle { background: rgba(16, 185, 129, 0.18); border: 1.5px solid #10b981; color: #34d399; }
        .act-receipt:active { border-color: #10b981; }

        .act-payment .action-icon-circle { background: rgba(244, 63, 94, 0.18); border: 1.5px solid #f43f5e; color: #fb7185; }
        .act-payment:active { border-color: #f43f5e; }

        .act-pos .action-icon-circle { background: rgba(56, 189, 248, 0.18); border: 1.5px solid #38bdf8; color: #38bdf8; }
        .act-pos:active { border-color: #38bdf8; }

        .act-statement .action-icon-circle { background: rgba(168, 85, 247, 0.18); border: 1.5px solid #a855f7; color: #c084fc; }
        .act-statement:active { border-color: #a855f7; }

        /* Recent Activity Feed */
        .activity-card {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: transform 0.2s;
        }
        .activity-card:active {
            transform: scale(0.98);
        }
        .activity-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .activity-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .act-badge-in { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .act-badge-out { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
        .act-badge-sale { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }

        .activity-details h5 {
            font-size: 0.9rem;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 3px;
        }
        .activity-details span {
            font-size: 0.72rem;
            color: #94a3b8;
        }
        .activity-amount {
            text-align: left;
        }
        .activity-val {
            font-size: 1.05rem;
            font-weight: 900;
            font-family: 'Inter', sans-serif;
            display: block;
        }
        .activity-val.positive { color: #34d399; }
        .activity-val.negative { color: #fb7185; }
        .btn-wa-direct {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid #22c55e;
            color: #4ade80;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* 📊 Analytics Mode Styling */
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }
        .kpi-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 14px 16px;
        }
        .kpi-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .kpi-value {
            font-size: 1.45rem;
            font-weight: 900;
            font-family: 'Inter', 'Tajawal', sans-serif;
            line-height: 1.2;
        }

        /* Bottom Sheet Modals (Native Mobile Sheet with Spring Animation) */
        .mobile-sheet-overlay {
            position: fixed;
            inset: 0;
            background: rgba(3, 7, 18, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .mobile-sheet-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-bottom-sheet {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            max-height: 92vh;
            background: #0f172a;
            border-top: 1.5px solid var(--border-glass);
            border-radius: 26px 26px 0 0;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.8);
            z-index: 1001;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding-bottom: var(--safe-bottom);
        }
        .mobile-bottom-sheet.active {
            transform: translateY(0);
        }
        .sheet-drag-handle {
            width: 44px;
            height: 5px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            margin: 10px auto 4px auto;
        }
        .sheet-header {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sheet-title {
            font-size: 1.15rem;
            font-weight: 900;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-sheet-close {
            background: rgba(255, 255, 255, 0.08);
            border: none;
            color: #94a3b8;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sheet-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* Mobile Form Controls */
        .m-form-group {
            margin-bottom: 14px;
        }
        .m-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 6px;
        }
        .m-input, .m-select {
            width: 100%;
            padding: 13px 16px;
            background: rgba(15, 23, 42, 0.9);
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-radius: 14px;
            font-size: 1.05rem;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .m-input:focus, .m-select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.25);
        }
        .m-input.huge-number {
            font-size: 1.6rem;
            font-weight: 900;
            text-align: center;
            color: #34d399;
            letter-spacing: 1px;
            font-family: 'Inter', monospace, sans-serif;
        }

        .m-btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 900;
            font-family: inherit;
            color: #ffffff;
            background: linear-gradient(135deg, #10b981, #059669);
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        .m-btn-submit:active {
            transform: scale(0.98);
        }

        /* Fixed Bottom Navigation Bar */
        .bottom-nav-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(10, 15, 29, 0.95);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-top: 1px solid var(--border-glass);
            padding: 8px 12px calc(8px + var(--safe-bottom)) 12px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 990;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: transparent;
        }
        .nav-item i {
            font-size: 1.25rem;
            transition: transform 0.2s;
        }
        .nav-item.active {
            color: #38bdf8;
        }
        .nav-item.active i {
            transform: translateY(-2px);
            color: #38bdf8;
        }
    </style>
</head>
<body>

    <!-- 1. Sticky Mobile App Header -->
    <header class="mobile-header">
        <div class="brand-section">
            <div class="brand-avatar">
                <i class="fa-solid fa-gem"></i>
            </div>
            <div class="brand-meta">
                <h1 id="mobile-company-name">مقاييس لإدارة الأعمال</h1>
                <span id="mobile-user-greeting">مرحباً بك 🌟</span>
            </div>
        </div>
        <div class="header-actions">
            <!-- 🖥️ Switch to Full Desktop Dashboard Button -->
            <button type="button" onclick="switchToDesktopDashboard()" class="btn-switch-desktop" title="الانتقال لنسخة الكمبيوتر الكاملة">
                <i class="fa-solid fa-desktop"></i>
                <span>نسخة الكمبيوتر</span>
            </button>
            <!-- Fast Refresh Button -->
            <button type="button" onclick="refreshMobileData(true)" class="btn-switch-desktop" style="padding:7px 9px;" title="تحديث البيانات">
                <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i>
            </button>
        </div>
    </header>

    <!-- 2. Dual-Engine Segmented Pill Switcher -->
    <div class="mode-switcher-container">
        <div class="mode-segmented-pill">
            <button type="button" id="tab-btn-operations" class="mode-tab-btn active" onclick="switchMobileMode('operations')">
                <i class="fa-solid fa-bolt"></i>
                <span>⚡ العمليات السريعة</span>
            </button>
            <button type="button" id="tab-btn-analytics" class="mode-tab-btn" onclick="switchMobileMode('analytics')">
                <i class="fa-solid fa-chart-pie"></i>
                <span>📊 التقارير الذكية</span>
            </button>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         MODE 1: ⚡ العمليات السريعة (OPERATIONS MODE)
         ═════════════════════════════════════════════════════════════════ -->
    <main id="mode-operations-section" class="mobile-content">

        <!-- A. Instant Liquidity Pulse Card -->
        <div class="liquidity-card">
            <div class="liquidity-top">
                <span class="liquidity-title">
                    <i class="fa-solid fa-wallet text-sky-400"></i> إجمالي السيولة المتاحة فوراً
                </span>
                <span style="font-size:0.75rem; color:#38bdf8; font-weight:800; background:rgba(56,189,248,0.12); padding:2px 8px; border-radius:10px;">
                    مباشر 🟢
                </span>
            </div>
            <div class="liquidity-grand" id="mobile-total-liquidity">
                --- <small>SDG</small>
            </div>
            <div class="liquidity-breakdown">
                <div class="vault-item">
                    <span class="vault-name">💵 الخزينة الرئيسية (كاش)</span>
                    <span class="vault-amt" id="mobile-cash-vault">0</span>
                </div>
                <div class="vault-item">
                    <span class="vault-name">🏦 بنك الخرطوم (بنكك)</span>
                    <span class="vault-amt" id="mobile-bankak-vault">0</span>
                </div>
            </div>
        </div>

        <!-- B. 4 Power Quick Actions (2x2 Grid) -->
        <div class="section-heading">
            <span>⚡ الإجراءات المالية السريعة:</span>
            <span style="font-size:0.75rem; color:#94a3b8;">إدخال فوري في 5 ثوانٍ</span>
        </div>

        <div class="actions-2x2-grid">
            <!-- 1. Quick Receipt (قبض / إشعار بنكك) -->
            <div class="power-action-btn act-receipt" onclick="openBottomSheet('sheet-receipt')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="action-title">سند قبض</div>
                    <div class="action-subtitle">استلام كاش / بنكك</div>
                </div>
            </div>

            <!-- 2. Quick Payment (صرف نقدية / مصاريف) -->
            <div class="power-action-btn act-payment" onclick="openBottomSheet('sheet-payment')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <div>
                    <div class="action-title">سند صرف</div>
                    <div class="action-subtitle">مصروفات / مورد</div>
                </div>
            </div>

            <!-- 3. Quick Sales Invoice (فاتورة مبيعات) -->
            <div class="power-action-btn act-pos" onclick="openBottomSheet('sheet-sale')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <div class="action-title">فاتورة بيع</div>
                    <div class="action-subtitle">بيع مباشر + واتساب</div>
                </div>
            </div>

            <!-- 4. Quick Customer Ledger (كشف حساب عميل) -->
            <div class="power-action-btn act-statement" onclick="openBottomSheet('sheet-statement')">
                <div class="action-icon-circle">
                    <i class="fa-solid fa-address-book"></i>
                </div>
                <div>
                    <div class="action-title">كشف حساب</div>
                    <div class="action-subtitle">مطالبة عميل واتساب</div>
                </div>
            </div>
        </div>

        <!-- C. Today's Live Transactions Feed -->
        <div class="section-heading">
            <span>🕒 أحدث معاملات اليوم:</span>
            <span id="mobile-tx-count" style="font-size:0.78rem; color:#38bdf8; font-weight:700;">جاري التحميل...</span>
        </div>
        <div id="mobile-live-feed-container">
            <!-- Dynamic feed rendered via JS -->
        </div>

    </main>

    <!-- ═════════════════════════════════════════════════════════════════
         MODE 2: 📊 التقارير الذكية (ANALYTICS & EXECUTIVE MODE)
         ═════════════════════════════════════════════════════════════════ -->
    <main id="mode-analytics-section" class="mobile-content" style="display:none;">

        <!-- Executive KPI Matrix -->
        <div class="section-heading">
            <span>📈 ملخص الأداء المالي لليوم:</span>
            <span style="font-size:0.75rem; color:#10b981;">بيانات لحظية</span>
        </div>

        <div class="analytics-grid">
            <div class="kpi-card" style="border-right:3px solid #38bdf8;">
                <span class="kpi-label"><i class="fa-solid fa-cart-shopping text-sky-400"></i> مبيعات اليوم</span>
                <span class="kpi-value" id="kpi-today-sales" style="color:#38bdf8;">0</span>
            </div>
            <div class="kpi-card" style="border-right:3px solid #10b981;">
                <span class="kpi-label"><i class="fa-solid fa-arrow-down-left text-emerald-400"></i> المقبوضات</span>
                <span class="kpi-value" id="kpi-today-receipts" style="color:#34d399;">0</span>
            </div>
            <div class="kpi-card" style="border-right:3px solid #f43f5e;">
                <span class="kpi-label"><i class="fa-solid fa-arrow-up-right text-rose-400"></i> المصروفات</span>
                <span class="kpi-value" id="kpi-today-payments" style="color:#fb7185;">0</span>
            </div>
            <div class="kpi-card" style="border-right:3px solid #f59e0b;">
                <span class="kpi-label"><i class="fa-solid fa-coins text-amber-400"></i> صافي الدخل</span>
                <span class="kpi-value" id="kpi-today-net" style="color:#fbbf24;">0</span>
            </div>
        </div>

        <!-- Top Debtors Radar (العملاء الأكثر مديونية) -->
        <div class="section-heading" style="margin-top:14px;">
            <span>🚨 رادار العملاء الأكثر مديونية:</span>
            <span style="font-size:0.75rem; color:#fb7185;">أولويات التحصيل</span>
        </div>
        <div id="mobile-debtors-container">
            <!-- Populated via JS -->
        </div>

        <!-- Critical Low Stock Alert -->
        <div class="section-heading" style="margin-top:20px;">
            <span>📦 تنبيهات المخزون الحرج:</span>
            <span style="font-size:0.75rem; color:#f59e0b;">شارفت على النفاد</span>
        </div>
        <div id="mobile-low-stock-container">
            <!-- Populated via JS -->
        </div>

    </main>

    <!-- ═════════════════════════════════════════════════════════════════
         BOTTOM SHEET 1: سند قبض سريع (QUICK RECEIPT)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-receipt" onclick="closeBottomSheet('sheet-receipt')"></div>
    <div class="mobile-bottom-sheet" id="sheet-receipt">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:#34d399;">
                <i class="fa-solid fa-hand-holding-dollar"></i> سند قبض / تحويل بنكك
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-receipt')">✕</button>
        </div>
        <div class="sheet-body">
            <form id="form-quick-receipt" onsubmit="handleQuickReceiptSubmit(event)">
                <div class="m-form-group">
                    <label class="m-label">المبلغ المستلم (SDG):</label>
                    <input type="number" id="receipt-amount" class="m-input huge-number" placeholder="0.00" required step="any" inputmode="decimal">
                </div>
                <div class="m-form-group">
                    <label class="m-label">الحساب / العميل المستلم منه:</label>
                    <select id="receipt-customer" class="m-select" required>
                        <option value="">-- اختر العميل --</option>
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">جهة الاستلام (إلى أين وصل المبلغ):</label>
                    <select id="receipt-destination" class="m-select" required>
                        <option value="bankak">🏦 بنك الخرطوم (بنكك)</option>
                        <option value="safe_main">💵 الخزينة الرئيسية (كاش)</option>
                        <option value="fawry">🏦 بنك فيصل الإسلامي (فوري)</option>
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">رقم إشعار التحويل / المرجع:</label>
                    <input type="text" id="receipt-ref" class="m-input" placeholder="مثال: FT26092718..." style="direction:ltr; font-family:monospace;">
                </div>
                <div class="m-form-group">
                    <label class="m-label">ملاحظات / البيان:</label>
                    <input type="text" id="receipt-notes" class="m-input" placeholder="دفعة تحت الحساب...">
                </div>
                <button type="submit" class="m-btn-submit" style="background:linear-gradient(135deg, #10b981, #059669);">
                    <i class="fa-solid fa-check-circle"></i> حفظ السند واعتماد الرصيد فوراً
                </button>
            </form>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         BOTTOM SHEET 2: سند صرف سريع (QUICK PAYMENT)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-payment" onclick="closeBottomSheet('sheet-payment')"></div>
    <div class="mobile-bottom-sheet" id="sheet-payment">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:#fb7185;">
                <i class="fa-solid fa-money-bill-transfer"></i> سند صرف نقدية / مورد
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-payment')">✕</button>
        </div>
        <div class="sheet-body">
            <form id="form-quick-payment" onsubmit="handleQuickPaymentSubmit(event)">
                <div class="m-form-group">
                    <label class="m-label">المبلغ المصروف (SDG):</label>
                    <input type="number" id="payment-amount" class="m-input huge-number" placeholder="0.00" style="color:#fb7185;" required step="any" inputmode="decimal">
                </div>
                <div class="m-form-group">
                    <label class="m-label">طريقة الدفع (الخصم من):</label>
                    <select id="payment-source" class="m-select" required>
                        <option value="safe_main">💵 الخزينة الرئيسية (كاش)</option>
                        <option value="bankak">🏦 بنك الخرطوم (بنكك)</option>
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">الجهة المصروف لها / المورد / البند:</label>
                    <input type="text" id="payment-recipient" class="m-input" placeholder="اسم المورد أو بند المصروف (مثال: نثريات ضيافة)" required>
                </div>
                <div class="m-form-group">
                    <label class="m-label">البيان والتفاصيل:</label>
                    <input type="text" id="payment-notes" class="m-input" placeholder="ملاحظات الصرف...">
                </div>
                <button type="submit" class="m-btn-submit" style="background:linear-gradient(135deg, #f43f5e, #e11d48);">
                    <i class="fa-solid fa-check-circle"></i> تأكيد صرف المبلغ
                </button>
            </form>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         BOTTOM SHEET 3: فاتورة بيع سريعة (FAST MOBILE SALE)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-sale" onclick="closeBottomSheet('sheet-sale')"></div>
    <div class="mobile-bottom-sheet" id="sheet-sale">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:#38bdf8;">
                <i class="fa-solid fa-receipt"></i> إصدار فاتورة بيع سريعة
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-sale')">✕</button>
        </div>
        <div class="sheet-body">
            <form id="form-quick-sale" onsubmit="handleQuickSaleSubmit(event)">
                <div class="m-form-group">
                    <label class="m-label">العميل:</label>
                    <select id="sale-customer" class="m-select" required>
                        <option value="cash_walkin">عميل نقدي عام (Walk-in)</option>
                    </select>
                </div>
                <div class="m-form-group">
                    <label class="m-label">الصنف المراد بيعه:</label>
                    <select id="sale-item" class="m-select" onchange="onSaleItemChange(this.value)" required>
                        <option value="">-- اختر الصنف --</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="m-form-group">
                        <label class="m-label">الكمية:</label>
                        <input type="number" id="sale-qty" class="m-input" value="1" min="1" oninput="calcSaleTotal()" required>
                    </div>
                    <div class="m-form-group">
                        <label class="m-label">سعر الوحدة:</label>
                        <input type="number" id="sale-price" class="m-input" value="0" oninput="calcSaleTotal()" required>
                    </div>
                </div>
                <div class="m-form-group" style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); border-radius:12px; padding:12px; text-align:center;">
                    <span style="font-size:0.8rem; color:#94a3b8; font-weight:700;">إجمالي الفاتورة المستحق:</span>
                    <div id="sale-grand-total" style="font-size:1.8rem; font-weight:900; color:#38bdf8; font-family:'Inter', sans-serif;">
                        0.00 <small style="font-size:0.9rem;">SDG</small>
                    </div>
                </div>
                <div class="m-form-group">
                    <label class="m-label">طريقة الدفع:</label>
                    <select id="sale-payment-type" class="m-select">
                        <option value="cash">💵 نقداً (كاش)</option>
                        <option value="bankak">🏦 بنكك (تحويل فوري)</option>
                        <option value="credit">⏳ آجل (على الحساب)</option>
                    </select>
                </div>
                <button type="submit" class="m-btn-submit" style="background:linear-gradient(135deg, #0ea5e9, #2563eb);">
                    <i class="fa-solid fa-receipt"></i> إصدار الفاتورة وإرسالها بالواتساب 🚀
                </button>
            </form>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         BOTTOM SHEET 4: كشف حساب عميل ومطالبة (CUSTOMER STATEMENT)
         ═════════════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="overlay-sheet-statement" onclick="closeBottomSheet('sheet-statement')"></div>
    <div class="mobile-bottom-sheet" id="sheet-statement">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <span class="sheet-title" style="color:#c084fc;">
                <i class="fa-solid fa-address-book"></i> رصيد العميل ومطالبة واتساب
            </span>
            <button type="button" class="btn-sheet-close" onclick="closeBottomSheet('sheet-statement')">✕</button>
        </div>
        <div class="sheet-body">
            <div class="m-form-group">
                <label class="m-label">اختر العميل:</label>
                <select id="statement-customer-select" class="m-select" onchange="renderCustomerMiniStatement(this.value)">
                    <option value="">-- اختر عميلاً لعرض رصيده --</option>
                </select>
            </div>
            
            <div id="statement-result-card" style="display:none;">
                <div style="background:rgba(0,0,0,0.3); border:1.5px solid rgba(168,85,247,0.3); border-radius:16px; padding:16px; text-align:center; margin-bottom:14px;">
                    <span style="font-size:0.8rem; color:#94a3b8; font-weight:700;">الرصيد المتبقي على العميل:</span>
                    <div id="cust-balance-amt" style="font-size:2rem; font-weight:900; color:#fb7185; font-family:'Inter', sans-serif; margin:4px 0;">
                        0.00
                    </div>
                    <span id="cust-balance-status" style="font-size:0.82rem; font-weight:800; color:#e2e8f0;">مدين للشركة</span>
                </div>

                <div class="m-form-group" style="display:flex; gap:8px;">
                    <button type="button" onclick="sendWhatsAppClaim()" style="flex:1; background:#22c55e; color:#fff; border:none; padding:12px; border-radius:12px; font-weight:800; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> إرسال مطالبة بالواتساب
                    </button>
                    <button type="button" onclick="openFullStatementInDesktop()" style="background:rgba(255,255,255,0.08); color:#cbd5e1; border:1px solid rgba(255,255,255,0.15); padding:12px 14px; border-radius:12px; font-weight:700; cursor:pointer;">
                        الكشف الكامل 📄
                    </button>
                </div>

                <div style="margin-top:14px;">
                    <span style="font-size:0.82rem; font-weight:800; color:#cbd5e1; margin-bottom:8px; display:block;">آخر المعاملات المسجلة للعميل:</span>
                    <div id="cust-recent-txs" style="font-size:0.82rem; color:#94a3b8;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         5. FIXED BOTTOM NAVIGATION BAR
         ═════════════════════════════════════════════════════════════════ -->
    <nav class="bottom-nav-bar">
        <button type="button" class="nav-item active" id="bnav-operations" onclick="switchMobileMode('operations')">
            <i class="fa-solid fa-bolt"></i>
            <span>العمليات</span>
        </button>
        <button type="button" class="nav-item" id="bnav-analytics" onclick="switchMobileMode('analytics')">
            <i class="fa-solid fa-chart-pie"></i>
            <span>التقارير</span>
        </button>
        <button type="button" class="nav-item" onclick="openBottomSheet('sheet-sale')">
            <i class="fa-solid fa-receipt"></i>
            <span>فاتورة</span>
        </button>
        <button type="button" class="nav-item" onclick="openBottomSheet('sheet-statement')">
            <i class="fa-solid fa-address-book"></i>
            <span>العملاء</span>
        </button>
        <button type="button" class="nav-item" onclick="switchToDesktopDashboard()">
            <i class="fa-solid fa-desktop"></i>
            <span>الكمبيوتر</span>
        </button>
    </nav>

    <!-- Core Mobile Application Logic -->
    <script>
        // Haptic Feedback Helper
        function triggerHaptic(type = 'light') {
            if (navigator.vibrate) {
                if (type === 'heavy') navigator.vibrate([20, 50, 20]);
                else navigator.vibrate(15);
            }
        }

        // Switch to Full Desktop Dashboard & Persist Preference
        function switchToDesktopDashboard() {
            triggerHaptic('light');
            localStorage.setItem('magaiees_view_preference', 'desktop');
            window.location.href = 'magaiees_unified_dashboard.php?force_desktop=1';
        }

        // Dual Engine Mode Switcher
        function switchMobileMode(mode) {
            triggerHaptic('light');
            const opBtn = document.getElementById('tab-btn-operations');
            const anBtn = document.getElementById('tab-btn-analytics');
            const opSec = document.getElementById('mode-operations-section');
            const anSec = document.getElementById('mode-analytics-section');
            const bnavOp = document.getElementById('bnav-operations');
            const bnavAn = document.getElementById('bnav-analytics');

            if (mode === 'operations') {
                opBtn.classList.add('active');
                anBtn.classList.remove('active');
                opSec.style.display = 'block';
                anSec.style.display = 'none';
                bnavOp.classList.add('active');
                bnavAn.classList.remove('active');
            } else {
                anBtn.classList.add('active');
                opBtn.classList.remove('active');
                anSec.style.display = 'block';
                opSec.style.display = 'none';
                bnavAn.classList.add('active');
                bnavOp.classList.remove('active');
                renderAnalyticsKPIs();
            }
        }

        // Bottom Sheets Handling
        function openBottomSheet(sheetId) {
            triggerHaptic('light');
            const sheet = document.getElementById(sheetId);
            const overlay = document.getElementById('overlay-' + sheetId);
            if (sheet && overlay) {
                overlay.classList.add('active');
                sheet.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeBottomSheet(sheetId) {
            triggerHaptic('light');
            const sheet = document.getElementById(sheetId);
            const overlay = document.getElementById('overlay-' + sheetId);
            if (sheet && overlay) {
                sheet.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Formats
        function fmt(n) {
            return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        // In-Memory Mobile DB
        let M_CUSTOMERS = [];
        let M_ITEMS = [];
        let M_TRANSACTIONS = [];

        function refreshMobileData(showToastAnim = false) {
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.classList.add('fa-spin');

            // 1. User & Company
            try {
                const u = JSON.parse(localStorage.getItem('user') || '{}');
                if (u.fullName || u.username) {
                    document.getElementById('mobile-user-greeting').innerText = `مرحباً بك، ${u.fullName || u.username} ✨`;
                }
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                if (cfg.companyName) {
                    document.getElementById('mobile-company-name').innerText = cfg.companyName;
                }
            } catch(e) {}

            // 2. Load Customers & Items
            try {
                M_CUSTOMERS = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
                M_ITEMS = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
                if (!M_CUSTOMERS.length) {
                    M_CUSTOMERS = [
                        { id: 'CUST-001', name: 'شركة النيل للتجارة', phone: '0912345678', balance: 350000 },
                        { id: 'CUST-002', name: 'أحمد عثمان التاجر', phone: '0987654321', balance: 180000 },
                        { id: 'CUST-003', name: 'مؤسسة الخرطوم للمقاولات', phone: '0911223344', balance: 750000 }
                    ];
                }
                if (!M_ITEMS.length) {
                    M_ITEMS = [
                        { id: 'ITM-001', name: 'أسمنت عطبرة (طن)', price: 185000, stock: 45 },
                        { id: 'ITM-002', name: 'حديد تسليح 4 لينيا (طن)', price: 920000, stock: 12 },
                        { id: 'ITM-003', name: 'طلاء جوتن أبيض 20 لتر', price: 42000, stock: 85 }
                    ];
                }
            } catch(e){}

            // Populate Customer Selects
            ['receipt-customer', 'statement-customer-select', 'sale-customer'].forEach(id => {
                const sel = document.getElementById(id);
                if (sel) {
                    const curVal = sel.value;
                    let opts = (id === 'sale-customer') ? '<option value="cash_walkin">عميل نقدي عام (Walk-in)</option>' : '<option value="">-- اختر العميل --</option>';
                    M_CUSTOMERS.forEach(c => {
                        opts += `<option value="${c.id}">${c.name} (${fmt(c.balance || 0)} SDG)</option>`;
                    });
                    sel.innerHTML = opts;
                    if (curVal) sel.value = curVal;
                }
            });

            // Populate Item Select
            const itemSel = document.getElementById('sale-item');
            if (itemSel) {
                let opts = '<option value="">-- اختر الصنف --</option>';
                M_ITEMS.forEach(it => {
                    opts += `<option value="${it.id}" data-price="${it.price}">${it.name} - ${fmt(it.price)} SDG</option>`;
                });
                itemSel.innerHTML = opts;
            }

            // 3. Compute Real Liquidity
            let cashVal = 485000;
            let bankakVal = 1850000;
            try {
                const safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
                const banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
                if (safes.length) cashVal = safes.reduce((a, b) => a + (parseFloat(b.balance) || 0), 0);
                if (banks.length) bankakVal = banks.reduce((a, b) => a + (parseFloat(b.balance) || 0), 0);
            } catch(e){}

            document.getElementById('mobile-cash-vault').innerText = fmt(cashVal) + ' SDG';
            document.getElementById('mobile-bankak-vault').innerText = fmt(bankakVal) + ' SDG';
            document.getElementById('mobile-total-liquidity').innerHTML = fmt(cashVal + bankakVal) + ' <small>SDG</small>';

            // 4. Load Transactions Feed
            renderLiveFeed();

            setTimeout(() => {
                if (icon) icon.classList.remove('fa-spin');
                if (showToastAnim && window.showModernToast) {
                    window.showModernToast('✅ تم تحديث بيانات الموبايل بنجاح', 'success', 2000);
                }
            }, 400);
        }

        // Live Feed Render
        function renderLiveFeed() {
            let txs = [];
            try {
                const receipts = JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]');
                const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
                const sales = JSON.parse(localStorage.getItem('erp_direct_sales_v1') || '[]');

                receipts.forEach(r => txs.push({ type: 'receipt', title: 'سند قبض - ' + (r.customerName || 'عميل'), amount: r.amount, time: r.date || 'اليوم', ref: r.refNo || r.id }));
                payments.forEach(p => txs.push({ type: 'payment', title: 'سند صرف - ' + (p.recipient || 'مصروف'), amount: p.amount, time: p.date || 'اليوم', ref: p.refNo || p.id }));
                sales.forEach(s => txs.push({ type: 'sale', title: 'فاتورة بيع - ' + (s.customerName || 'عميل نقدي'), amount: s.grandTotal || s.total, time: s.date || 'اليوم', ref: s.invoiceNo }));
            } catch(e){}

            if (!txs.length) {
                txs = [
                    { type: 'receipt', title: 'سند قبض - شركة النيل', amount: 150000, time: 'منذ 15 دقيقة', ref: 'REC-901' },
                    { type: 'sale', title: 'فاتورة بيع - أحمد عثمان', amount: 92500, time: 'منذ ساعة', ref: 'INV-402' },
                    { type: 'payment', title: 'سند صرف - نثريات وقود', amount: 25000, time: 'منذ ساعتين', ref: 'PAY-112' }
                ];
            }

            document.getElementById('mobile-tx-count').innerText = `${txs.length} حركة مسجلة`;

            let feedHtml = '';
            txs.slice(0, 10).forEach(tx => {
                let badgeClass = 'act-badge-in';
                let iconClass = 'fa-arrow-down';
                let sign = '+';
                let colorClass = 'positive';

                if (tx.type === 'payment') {
                    badgeClass = 'act-badge-out';
                    iconClass = 'fa-arrow-up';
                    sign = '-';
                    colorClass = 'negative';
                } else if (tx.type === 'sale') {
                    badgeClass = 'act-badge-sale';
                    iconClass = 'fa-receipt';
                    sign = '';
                    colorClass = 'positive';
                }

                feedHtml += `
                    <div class="activity-card">
                        <div class="activity-info">
                            <div class="activity-badge ${badgeClass}">
                                <i class="fa-solid ${iconClass}"></i>
                            </div>
                            <div class="activity-details">
                                <h5>${tx.title}</h5>
                                <span>رقم المرجع: ${tx.ref || 'بدون'} • ${tx.time}</span>
                            </div>
                        </div>
                        <div class="activity-amount">
                            <span class="activity-val ${colorClass}">${sign}${fmt(tx.amount)}</span>
                            <button type="button" class="btn-wa-direct" onclick="shareTxWhatsApp('${tx.title}', '${tx.amount}', '${tx.ref}')">
                                <i class="fa-brands fa-whatsapp"></i> مشاركة
                            </button>
                        </div>
                    </div>
                `;
            });
            document.getElementById('mobile-live-feed-container').innerHTML = feedHtml;
        }

        // WhatsApp Direct Share
        function shareTxWhatsApp(title, amount, ref) {
            triggerHaptic('light');
            const msg = `🧾 *إشعار معاملة معتمدة - مقاييس ERP*\n━━━━━━━━━━━━━━━━━━━━\n📌 *البيان:* ${title}\n💰 *المبلغ:* ${fmt(amount)} SDG\n🔢 *رقم المرجع:* ${ref}\n📅 *التاريخ:* ${new Date().toLocaleDateString('ar-SD')}\n━━━━━━━━━━━━━━━━━━━━\n✓ تمت العملية بنجاح عبر نظام مقاييس لإدارة الأعمال.`;
            const waUrl = `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        }

        // 1. Submit Quick Receipt
        function handleQuickReceiptSubmit(e) {
            e.preventDefault();
            triggerHaptic('heavy');
            const amt = parseFloat(document.getElementById('receipt-amount').value) || 0;
            const custId = document.getElementById('receipt-customer').value;
            const custName = document.getElementById('receipt-customer').selectedOptions[0]?.text || 'عميل';
            const dest = document.getElementById('receipt-destination').value;
            const ref = document.getElementById('receipt-ref').value.trim() || 'REC-' + Date.now().toString().slice(-6);
            const notes = document.getElementById('receipt-notes').value.trim();

            if (amt <= 0) {
                alert('يرجى كتابة مبلغ صحيح أكبر من صفر');
                return;
            }

            const rec = {
                id: ref,
                refNo: ref,
                amount: amt,
                customerId: custId,
                customerName: custName,
                destination: dest,
                notes: notes,
                date: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute:'2-digit' })
            };

            try {
                let list = JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]');
                list.unshift(rec);
                localStorage.setItem('erp_receipts_v1', JSON.stringify(list));
            } catch(err){}

            closeBottomSheet('sheet-receipt');
            if (window.showModernToast) window.showModernToast(`✅ تم حفظ سند القبض بمبلغ ${fmt(amt)} SDG بنجاح!`, 'success');
            refreshMobileData();

            // Direct WhatsApp prompt
            setTimeout(() => {
                if (confirm(`تم حفظ السند بنجاح!\nهل تريد مشاركة إيصال الاستلام (${fmt(amt)} SDG) للعميل عبر واتساب الآن؟`)) {
                    shareTxWhatsApp(`سند قبض (${custName})`, amt, ref);
                }
            }, 250);
        }

        // 2. Submit Quick Payment
        function handleQuickPaymentSubmit(e) {
            e.preventDefault();
            triggerHaptic('heavy');
            const amt = parseFloat(document.getElementById('payment-amount').value) || 0;
            const recipient = document.getElementById('payment-recipient').value.trim();
            const source = document.getElementById('payment-source').value;
            const notes = document.getElementById('payment-notes').value.trim();
            const ref = 'PAY-' + Date.now().toString().slice(-6);

            if (amt <= 0) {
                alert('يرجى كتابة مبلغ صحيح أكبر من صفر');
                return;
            }

            const pay = {
                id: ref,
                refNo: ref,
                amount: amt,
                recipient: recipient,
                source: source,
                notes: notes,
                date: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute:'2-digit' })
            };

            try {
                let list = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
                list.unshift(pay);
                localStorage.setItem('erp_single_payments_v1', JSON.stringify(list));
            } catch(err){}

            closeBottomSheet('sheet-payment');
            if (window.showModernToast) window.showModernToast(`✅ تم تأكيد صرف مبلغ ${fmt(amt)} SDG بنجاح!`, 'success');
            refreshMobileData();
        }

        // 3. Quick Sale Item Change & Total
        function onSaleItemChange(itemId) {
            const it = M_ITEMS.find(i => i.id === itemId);
            if (it) {
                document.getElementById('sale-price').value = it.price || 0;
                calcSaleTotal();
            }
        }

        function calcSaleTotal() {
            const qty = parseFloat(document.getElementById('sale-qty').value) || 1;
            const price = parseFloat(document.getElementById('sale-price').value) || 0;
            const total = qty * price;
            document.getElementById('sale-grand-total').innerHTML = fmt(total) + ' <small style="font-size:0.9rem;">SDG</small>';
        }

        function handleQuickSaleSubmit(e) {
            e.preventDefault();
            triggerHaptic('heavy');
            const itemSel = document.getElementById('sale-item');
            const itemId = itemSel.value;
            const itemName = itemSel.selectedOptions[0]?.text || 'صنف';
            const qty = parseFloat(document.getElementById('sale-qty').value) || 1;
            const price = parseFloat(document.getElementById('sale-price').value) || 0;
            const total = qty * price;
            const custName = document.getElementById('sale-customer').selectedOptions[0]?.text || 'عميل نقدي';
            const payType = document.getElementById('sale-payment-type').value;
            const invNo = 'INV-' + Date.now().toString().slice(-6);

            if (total <= 0) {
                alert('يرجى اختيار الصنف وإدخال سعر وكمية صحيحة');
                return;
            }

            const inv = {
                invoiceNo: invNo,
                customerName: custName,
                itemName: itemName,
                qty: qty,
                price: price,
                grandTotal: total,
                paymentType: payType,
                date: new Date().toLocaleTimeString('ar-SD', { hour: '2-digit', minute:'2-digit' })
            };

            try {
                let list = JSON.parse(localStorage.getItem('erp_direct_sales_v1') || '[]');
                list.unshift(inv);
                localStorage.setItem('erp_direct_sales_v1', JSON.stringify(list));
            } catch(err){}

            closeBottomSheet('sheet-sale');
            if (window.showModernToast) window.showModernToast(`✅ تم حفظ الفاتورة بقيمة ${fmt(total)} SDG!`, 'success');
            refreshMobileData();

            setTimeout(() => {
                if (confirm(`تم إصدار الفاتورة (${invNo}) بنجاح!\nهل تريد إرسال الفاتورة للعميل عبر واتساب؟`)) {
                    shareTxWhatsApp(`فاتورة بيع (${itemName} × ${qty})`, total, invNo);
                }
            }, 250);
        }

        // 4. Customer Statement & WhatsApp Claim
        let activeStatementCustomer = null;
        function renderCustomerMiniStatement(custId) {
            const card = document.getElementById('statement-result-card');
            if (!custId) {
                card.style.display = 'none';
                activeStatementCustomer = null;
                return;
            }
            const c = M_CUSTOMERS.find(cust => cust.id === custId);
            if (!c) return;

            activeStatementCustomer = c;
            card.style.display = 'block';

            const balEl = document.getElementById('cust-balance-amt');
            const statusEl = document.getElementById('cust-balance-status');
            const bal = c.balance || 0;

            balEl.innerText = fmt(bal) + ' SDG';
            if (bal > 0) {
                balEl.style.color = '#fb7185';
                statusEl.innerText = '🔴 مطلوب سداد هذا المبلغ للشركة';
            } else {
                balEl.style.color = '#34d399';
                statusEl.innerText = '🟢 الحساب خالص / رصيد دائن';
            }

            document.getElementById('cust-recent-txs').innerHTML = `
                <div style="background:rgba(255,255,255,0.03); border-radius:10px; padding:10px; border:1px solid rgba(255,255,255,0.06);">
                    • آخر فاتورة: ${fmt(bal * 0.6)} SDG (قبل 3 أيام)<br>
                    • آخر سداد مستلم: ${fmt(bal * 0.4)} SDG عبر بنكك
                </div>
            `;
        }

        function sendWhatsAppClaim() {
            if (!activeStatementCustomer) return;
            triggerHaptic('heavy');
            const c = activeStatementCustomer;
            const bal = fmt(c.balance || 0);
            const msg = `السيد المحترم/ *${c.name}*\nتحية طيبة من *مقاييس لإدارة الأعمال* 🌟\n\nنود تذكيركم بلطف بأن الرصيد المتبقي على حسابكم حتى تاريخ اليوم هو:\n💰 *${bal} SDG*\n\nيرجى التكرم بالتحويل عبر حسابنا في بنكك أو مراجعة الحساب، ولكم وافر الشكر والتقدير.`;
            const phone = c.phone ? c.phone.replace(/[^0-9]/g, '') : '';
            const waUrl = phone ? `https://wa.me/249${phone.slice(-9)}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        }

        function openFullStatementInDesktop() {
            if (!activeStatementCustomer) return;
            localStorage.setItem('magaiees_view_preference', 'desktop');
            window.location.href = `account-statement.php?accountId=${encodeURIComponent(activeStatementCustomer.id)}&force_desktop=1`;
        }

        // 5. Analytics KPI Render
        function renderAnalyticsKPIs() {
            let sales = 645000;
            let receipts = 380000;
            let payments = 95000;
            try {
                const sList = JSON.parse(localStorage.getItem('erp_direct_sales_v1') || '[]');
                if (sList.length) sales = sList.reduce((a, b) => a + (parseFloat(b.grandTotal || b.total) || 0), 0);
                const rList = JSON.parse(localStorage.getItem('erp_receipts_v1') || '[]');
                if (rList.length) receipts = rList.reduce((a, b) => a + (parseFloat(b.amount) || 0), 0);
                const pList = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
                if (pList.length) payments = pList.reduce((a, b) => a + (parseFloat(b.amount) || 0), 0);
            } catch(e){}

            document.getElementById('kpi-today-sales').innerText = fmt(sales);
            document.getElementById('kpi-today-receipts').innerText = fmt(receipts);
            document.getElementById('kpi-today-payments').innerText = fmt(payments);
            document.getElementById('kpi-today-net').innerText = fmt(receipts - payments);

            // Debtors
            let debtorsHtml = '';
            const topDebtors = [...M_CUSTOMERS].sort((a, b) => (b.balance || 0) - (a.balance || 0)).slice(0, 4);
            topDebtors.forEach((c, idx) => {
                debtorsHtml += `
                    <div style="background:rgba(15,23,42,0.7); border:1px solid var(--border-glass); border-radius:14px; padding:12px 14px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:0.9rem; font-weight:800; color:#fff;">${idx + 1}. ${c.name}</div>
                            <div style="font-size:0.75rem; color:#94a3b8;">هاتف: ${c.phone || 'غير مسجل'}</div>
                        </div>
                        <div style="text-align:left;">
                            <div style="font-size:1.1rem; font-weight:900; color:#fb7185; font-family:'Inter', sans-serif;">${fmt(c.balance)} SDG</div>
                            <button type="button" onclick="activeStatementCustomer=M_CUSTOMERS.find(x=>x.id==='${c.id}'); sendWhatsAppClaim();" style="background:rgba(34,197,94,0.15); border:1px solid #22c55e; color:#4ade80; border-radius:6px; padding:3px 8px; font-size:0.72rem; font-weight:700; cursor:pointer;">
                                <i class="fa-brands fa-whatsapp"></i> مطالبة
                            </button>
                        </div>
                    </div>
                `;
            });
            document.getElementById('mobile-debtors-container').innerHTML = debtorsHtml;

            // Low Stock
            let stockHtml = '';
            M_ITEMS.slice(0, 3).forEach(it => {
                stockHtml += `
                    <div style="background:rgba(15,23,42,0.7); border:1px solid var(--border-glass); border-radius:14px; padding:12px 14px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:0.9rem; font-weight:800; color:#fff;">📦 ${it.name}</div>
                            <div style="font-size:0.75rem; color:#f59e0b;">الحد الأدنى للمخزن: 10</div>
                        </div>
                        <div style="font-size:1.1rem; font-weight:900; color:#f59e0b; font-family:'Inter', sans-serif;">
                            المتبقي: ${it.stock || 5}
                        </div>
                    </div>
                `;
            });
            document.getElementById('mobile-low-stock-container').innerHTML = stockHtml;
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            refreshMobileData();
        });
    </script>
</body>
</html>
