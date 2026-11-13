<?php
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService {
    
    private static function getSmtpSettings($pdo) {
        if (!$pdo) return null;
        $unquote = function($val) {
            if ($val === null || $val === '') return '';
            $s = trim((string)$val);
            $jd = json_decode($s, true);
            return is_string($jd) ? trim($jd) : trim($s, "\"' ");
        };

        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('smtp_config_json', 'smtp_server', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_name')");
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            if (!empty($settings['smtp_config_json'])) {
                $cfgJson = json_decode((string)$settings['smtp_config_json'], true);
                if (is_array($cfgJson) && !empty($cfgJson['smtp_server']) && !empty($cfgJson['smtp_user']) && !empty($cfgJson['smtp_pass'])) {
                    return [
                        'smtp_server' => trim((string)$cfgJson['smtp_server']),
                        'smtp_port'   => trim((string)($cfgJson['smtp_port'] ?? '587')),
                        'smtp_user'   => trim((string)$cfgJson['smtp_user']),
                        'smtp_pass'   => trim((string)$cfgJson['smtp_pass'])
                    ];
                }
            }
            $srv = $unquote($settings['smtp_server'] ?? '');
            $prt = $unquote($settings['smtp_port'] ?? '') ?: '587';
            $usr = $unquote($settings['smtp_user'] ?? '');
            $pas = $unquote($settings['smtp_pass'] ?? '');
            if (!empty($srv) && !empty($prt) && !empty($usr) && !empty($pas)) {
                return [
                    'smtp_server' => $srv,
                    'smtp_port'   => $prt,
                    'smtp_user'   => $usr,
                    'smtp_pass'   => $pas
                ];
            }
        } catch (\Throwable $e) {}

        // Check global_settings JSON blobs (erp_settings_v1 / tenant_*_erp_settings_v1)
        try {
            $stmtG = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key LIKE '%erp_settings_v1%' ORDER BY setting_key ASC");
            while ($val = $stmtG->fetchColumn()) {
                $dec = json_decode((string)$val, true);
                if (is_array($dec)) {
                    $srv = trim((string)($dec['smtpServer'] ?? ($dec['smtp_server'] ?? '')));
                    $prt = trim((string)($dec['smtpPort'] ?? ($dec['smtp_port'] ?? '587')));
                    $usr = trim((string)($dec['smtpUser'] ?? ($dec['smtp_user'] ?? '')));
                    $pas = trim((string)($dec['smtpPass'] ?? ($dec['smtp_pass'] ?? '')));
                    if (!empty($srv) && !empty($prt) && !empty($usr) && !empty($pas) && $pas !== '********') {
                        return [
                            'smtp_server' => $srv,
                            'smtp_port'   => $prt,
                            'smtp_user'   => $usr,
                            'smtp_pass'   => $pas
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Check tenant settings table (JSON column `data` or key-value columns)
        try {
            $stmtD = $pdo->query("SELECT data FROM settings WHERE data IS NOT NULL AND data != '' ORDER BY id DESC");
            while ($rawD = $stmtD->fetchColumn()) {
                $dec = json_decode((string)$rawD, true);
                if (is_array($dec)) {
                    $srv = trim((string)($dec['smtpServer'] ?? ($dec['smtp_server'] ?? '')));
                    $prt = trim((string)($dec['smtpPort'] ?? ($dec['smtp_port'] ?? '587')));
                    $usr = trim((string)($dec['smtpUser'] ?? ($dec['smtp_user'] ?? '')));
                    $pas = trim((string)($dec['smtpPass'] ?? ($dec['smtp_pass'] ?? '')));
                    if (!empty($srv) && !empty($prt) && !empty($usr) && !empty($pas) && $pas !== '********') {
                        return [
                            'smtp_server' => $srv,
                            'smtp_port'   => $prt,
                            'smtp_user'   => $usr,
                            'smtp_pass'   => $pas
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            $stmt2 = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('smtpServer', 'smtpPort', 'smtpUser', 'smtpPass', 'smtp_server', 'smtp_port', 'smtp_user', 'smtp_pass') ORDER BY id DESC");
            $tSettings = [];
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $k = $row['setting_key'];
                $v = $unquote($row['setting_value']);
                if ($v !== '' && !isset($tSettings[$k])) {
                    $tSettings[$k] = $v;
                }
            }
            $srv = $tSettings['smtp_server'] ?? ($tSettings['smtpServer'] ?? '');
            $prt = $tSettings['smtp_port'] ?? ($tSettings['smtpPort'] ?? '587');
            $usr = $tSettings['smtp_user'] ?? ($tSettings['smtpUser'] ?? '');
            $pas = $tSettings['smtp_pass'] ?? ($tSettings['smtpPass'] ?? '');
            if (!empty($srv) && !empty($prt) && !empty($usr) && !empty($pas) && $pas !== '********') {
                return [
                    'smtp_server' => $srv,
                    'smtp_port'   => $prt,
                    'smtp_user'   => $usr,
                    'smtp_pass'   => $pas
                ];
            }
        } catch (\Throwable $e) {}

        return null;
    }

    private static function sendViaLocalTransport($email, $subject, $htmlContent) {
        $fromEmail = 'info@elsajana.com';
        $fromName  = 'السيطرة MAGAIES';

        // 1. Try local MTA on 127.0.0.1:25 (Postfix / Exim) if listening
        $fp = @fsockopen('127.0.0.1', 25, $errno, $errstr, 1.5);
        if (is_resource($fp)) {
            @fclose($fp);
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host        = '127.0.0.1';
                $mail->Port        = 25;
                $mail->SMTPAuth    = false;
                $mail->SMTPAutoTLS = false;
                $mail->Hostname    = 'elsajana.com';
                $mail->CharSet     = 'UTF-8';
                $mail->Timeout     = 6;
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlContent;
                if ($mail->send()) {
                    return ['success' => true, 'message' => 'تم إرسال الإيميل بنجاح'];
                }
            } catch (\Throwable $e) {}
        }

        // 2. Try local Sendmail binary if present on Linux server
        if (stristr(PHP_OS, 'WIN') === false && (@is_executable('/usr/sbin/sendmail') || @is_executable('/usr/lib/sendmail'))) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSendmail();
                $mail->CharSet = 'UTF-8';
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlContent;
                if ($mail->send()) {
                    return ['success' => true, 'message' => 'تم إرسال الإيميل بنجاح'];
                }
            } catch (\Throwable $e) {}
        }

        // 3. Try Direct MX SMTP Delivery to recipient's mail server (Port 25)
        $atPos = strrpos($email, '@');
        if ($atPos !== false) {
            $domain = substr($email, $atPos + 1);
            $mxHosts = [];
            $mxWeights = [];
            if (@getmxrr($domain, $mxHosts, $mxWeights) && !empty($mxHosts)) {
                if (!empty($mxWeights) && count($mxWeights) === count($mxHosts)) {
                    array_multisort($mxWeights, SORT_ASC, $mxHosts);
                }
                foreach (array_slice($mxHosts, 0, 2) as $mxHost) {
                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host        = $mxHost;
                        $mail->Port        = 25;
                        $mail->SMTPAuth    = false;
                        $mail->SMTPAutoTLS = true;
                        $mail->Hostname    = 'elsajana.com';
                        $mail->CharSet     = 'UTF-8';
                        $mail->Timeout     = 6;
                        $mail->SMTPOptions = [
                            'ssl' => [
                                'verify_peer'       => false,
                                'verify_peer_name'  => false,
                                'allow_self_signed' => true
                            ]
                        ];
                        $mail->setFrom($fromEmail, $fromName);
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body    = $htmlContent;
                        if ($mail->send()) {
                            return ['success' => true, 'message' => 'تم إرسال الإيميل بنجاح'];
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        // 4. Try native PHP mail() with LF headers for Linux MTA compatibility
        $headers  = "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\n";
        $headers .= "From: MAGAIES <{$fromEmail}>\n";
        $headers .= "Reply-To: {$fromEmail}\n";
        $mailSent = @mail($email, $subject, $htmlContent, $headers, "-f{$fromEmail}");
        if ($mailSent) {
            return ['success' => true, 'message' => 'تم الإرسال بنجاح (mail)'];
        }

        return ['success' => false, 'error' => 'تعذر إرسال البريد الإلكتروني حالياً. يرجى التأكد من إعدادات البريد (SMTP).'];
    }

    private static function sendViaPHPMailer($smtp, $email, $subject, $htmlContent) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp['smtp_user'];
            $mail->Password   = $smtp['smtp_pass'];
            
            // Auto detect encryption
            $port = (int)$smtp['smtp_port'];
            if ($port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom($smtp['smtp_user'], 'السيطرة MAGAIES');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlContent;

            $mail->send();
            return ['success' => true, 'message' => 'تم إرسال الإيميل بنجاح'];
        } catch (PHPMailerException $e) {
            return ['success' => false, 'error' => "فشل إرسال الإيميل: {$mail->ErrorInfo}"];
        }
    }

    public static function sendOTP($email, $otp, $pdo = null) {
        $htmlContent = '
            <div style="font-family: Arial, sans-serif; direction: rtl; text-align: right; background-color: #f8fafc; padding: 20px;">
                <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <h2 style="color: #0ea5e9;">أهلاً بك في السيطرة 👋</h2>
                    <p style="color: #334155; font-size: 16px;">لقد تلقينا طلباً لإنشاء حساب جديد. يرجى استخدام رمز التحقق التالي لإكمال عملية التسجيل:</p>
                    <div style="background-color: #f1f5f9; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">
                        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #0f172a;">' . $otp . '</span>
                    </div>
                    <p style="color: #64748b; font-size: 14px;">هذا الرمز صالح لمدة 10 دقائق فقط. إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.</p>
                    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                    <p style="color: #94a3b8; font-size: 12px; text-align: center;">جميع الحقوق محفوظة © السيطرة MAGAIES</p>
                </div>
            </div>';

        return self::sendEmail($email, 'رمز التحقق (OTP) الخاص بك - السيطرة', $htmlContent, $pdo);
    }

    public static function sendPasswordResetOTP($email, $otp, $pdo = null) {
        $htmlContent = '
            <div style="font-family: Arial, sans-serif; direction: rtl; text-align: right; background-color: #f8fafc; padding: 20px;">
                <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <h2 style="color: #0ea5e9;">استرداد كلمة المرور - السيطرة 🔑</h2>
                    <p style="color: #334155; font-size: 16px;">لقد تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك. استخدم رمز التحقق التالي لإكمال العملية:</p>
                    <div style="background-color: #f1f5f9; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">
                        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #0f172a;">' . $otp . '</span>
                    </div>
                    <p style="color: #64748b; font-size: 14px;">هذا الرمز صالح لمدة 10 دقائق فقط. إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد وتأمين حسابك.</p>
                    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                    <p style="color: #94a3b8; font-size: 12px; text-align: center;">جميع الحقوق محفوظة © السيطرة MAGAIES</p>
                </div>
            </div>';

        return self::sendEmail($email, 'رمز استرداد كلمة المرور - السيطرة', $htmlContent, $pdo);
    }

    private static function sendEmail($email, $subject, $htmlContent, $pdo) {
        // 1. Try Custom SMTP Settings via PHPMailer
        $smtpSettings = self::getSmtpSettings($pdo);
        if ($smtpSettings) {
            $smtpRes = self::sendViaPHPMailer($smtpSettings, $email, $subject, $htmlContent);
            if (!empty($smtpRes['success'])) {
                return $smtpRes;
            }
        }

        // 2. Try Brevo API
        $brevo_api_key = getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? ($_SERVER['BREVO_API_KEY'] ?? ''));
        
        if (empty($brevo_api_key) && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key IN ('brevo_api_key', 'smtp_config_json')");
                $stmt->execute();
                while ($dbVal = $stmt->fetchColumn()) {
                    $s = trim((string)$dbVal);
                    $jd = json_decode($s, true);
                    if (is_array($jd) && !empty($jd['brevo_api_key'])) {
                        $brevo_api_key = trim((string)$jd['brevo_api_key']);
                        break;
                    } elseif (is_string($jd) && strpos($jd, 'xkeysib-') === 0) {
                        $brevo_api_key = trim($jd);
                        break;
                    } elseif (strpos($s, 'xkeysib-') === 0) {
                        $brevo_api_key = $s;
                        break;
                    }
                }
            } catch (\Throwable $e) {}
        }

        if (empty($brevo_api_key)) {
            $brevo_api_key = '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';
        }

        if (!empty($brevo_api_key) && $brevo_api_key !== 'YOUR_BREVO_API_KEY') {
            $mailData = [
                'sender' => ['name' => 'السيطرة', 'email' => 'magaiees00105@gmail.com'],
                'to' => [['email' => $email]],
                'subject' => $subject,
                'htmlContent' => $htmlContent
            ];

            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailData));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: ' . $brevo_api_key,
                'content-type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 201 || $httpCode === 200) {
                return ['success' => true, 'message' => 'تم الإرسال بنجاح'];
            }
        }

        // 3. Fallback to Local Transport (Sendmail / Local Postfix / PHP mail())
        $localRes = self::sendViaLocalTransport($email, $subject, $htmlContent);
        if (!empty($localRes['success'])) {
            return $localRes;
        }

        return [
            'success' => false,
            'error' => (!empty($smtpRes['error']) ? $smtpRes['error'] : ($localRes['error'] ?? 'تعذر إرسال البريد الإلكتروني حالياً. يرجى مراجعة إعدادات البريد (SMTP).'))
        ];
    }
}

