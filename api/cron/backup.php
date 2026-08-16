<?php
/**
 * MAGAIES Automated Database Backup Script
 * Usage: php api/cron/backup.php
 * Cron Job URL: https://example.com/api/cron/backup.php?token=magaies_backup_2026
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// 1. Security Check
$cronToken = 'magaies_backup_2026';
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['token']) || $_GET['token'] !== $cronToken) {
        http_response_code(403);
        die('Forbidden: Invalid Cron Token');
    }
}

set_time_limit(600); // 10 minutes max execution time

try {
    // 2. Fetch Backup Settings from DB
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('backup_email', 'smtp_server', 'smtp_port', 'smtp_user', 'smtp_pass')");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $backupEmail = $settings['backup_email'] ?? '';
    
    // 3. Dump Database
    $dateStr = date('Y-m-d_H-i-s');
    
    $backupDir = __DIR__ . '/../../backups/';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    
    $sqlFile = $backupDir . "db_backup_{$dateStr}.sql";
    $zipFile = $backupDir . "db_backup_{$dateStr}.zip";

    // Use pure PHP dump for maximum compatibility
    $tables = [];
    $stmt = $pdo->query('SHOW TABLES');
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sql = "-- MAGAIES Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $sql .= "-- Table structure for `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $row[1] . ";\n\n";

        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            $sql .= "-- Dumping data for `$table`\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vals = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = $pdo->quote($val);
                    }
                }
                $sql .= "INSERT INTO `$table` VALUES (" . implode(", ", $vals) . ");\n";
            }
            $sql .= "\n";
        }
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    file_put_contents($sqlFile, $sql);

    // 4. Zip the SQL file if extension exists, otherwise use raw SQL
    $finalAttachment = $sqlFile;
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($sqlFile, basename($sqlFile));
            $zip->close();
            $finalAttachment = $zipFile;
            
            // Delete raw SQL file to save space since we zipped it
            if (file_exists($sqlFile)) {
                unlink($sqlFile);
            }
        }
    }

    $responseMsg = "Backup created: " . basename($finalAttachment);

    // 5. Email the Backup
    if (!empty($backupEmail) && !empty($settings['smtp_server'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $settings['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtp_user'];
            $mail->Password   = $settings['smtp_pass'];
            
            $port = (int)($settings['smtp_port'] ?? 465);
            $mail->SMTPSecure = ($port === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($settings['smtp_user'], 'MAGAIES Backup System');
            $mail->addAddress($backupEmail);

            $mail->isHTML(true);
            $mail->Subject = 'النسخة الاحتياطية اليومية - MAGAIES';
            $mail->Body    = 'مرفق النسخة الاحتياطية لقاعدة البيانات الخاصة بنظام مقاييس لتاريخ: ' . date('Y-m-d');
            
            $mail->addAttachment($finalAttachment, basename($finalAttachment));

            $mail->send();
            $responseMsg .= " | Email sent to $backupEmail";
        } catch (PHPMailerException $e) {
            $responseMsg .= " | Email failed: {$mail->ErrorInfo}";
        }
    }

    // 6. Delete old backups (older than 7 days)
    $files = array_merge(glob($backupDir . '*.zip') ?: [], glob($backupDir . '*.sql') ?: []);
    $now   = time();
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { 
                unlink($file);
                $deletedCount++;
            }
        }
    }
    if ($deletedCount > 0) {
        $responseMsg .= " | Deleted $deletedCount old backups.";
    }

    echo json_encode(['success' => true, 'message' => $responseMsg], JSON_UNESCAPED_UNICODE);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
