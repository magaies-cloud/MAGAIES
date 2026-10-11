<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🛒 MAGAIEES ERP 2026 - POCKET SALES & POS MOBILE EDITION
 * ═════════════════════════════════════════════════════════════════════
 * شاشة المبيعات والفوترة المباشرة السريعة للهواتف الذكية والأجهزة اللوحية
 * متطابقة 100% مع نسخة اللابتوب ونظام المرآة الفوري (Mirror Sync)
 */

$_GET['legacy'] = '1';
$_GET['open'] = 'sales';
$_GET['sheet'] = 'sale';
$_GET['force_mobile'] = '1';
if (isset($_GET['tab'])) {
    $_GET['salestab'] = $_GET['tab'];
}

require_once __DIR__ . '/mobile-dashboard.php';
