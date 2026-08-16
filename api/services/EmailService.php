<?php
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService {
    
    private static function getSmtpSettings($pdo) {
        if (!$pdo) return null;
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('smtp_server', 'smtp_port', 'smtp_user', 'smtp_pass')");
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            if (!empty($settings['smtp_server']) && !empty($settings['smtp_port']) && !empty($settings['smtp_user']) && !empty($settings['smtp_pass'])) {
                return $settings;
            }
        } catch (\Throwable $e) {}
        return null;
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

            $mail->setFrom($smtp['smtp_user'], 'مقاييس MAGAIES');
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
                    <h2 style="color: #0ea5e9;">أهلاً بك في مقاييس 👋</h2>
                    <p style="color: #334155; font-size: 16px;">لقد تلقينا طلباً لإنشاء حساب جديد. يرجى استخدام رمز التحقق التالي لإكمال عملية التسجيل:</p>
                    <div style="background-color: #f1f5f9; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">
                        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #0f172a;">' . $otp . '</span>
                    </div>
                    <p style="color: #64748b; font-size: 14px;">هذا الرمز صالح لمدة 10 دقائق فقط. إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.</p>
                    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                    <p style="color: #94a3b8; font-size: 12px; text-align: center;">جميع الحقوق محفوظة © مقاييس MAGAIES</p>
                </div>
            </div>';

        return self::sendEmail($email, 'رمز التحقق (OTP) الخاص بك - مقاييس', $htmlContent, $pdo);
    }

    public static function sendPasswordResetOTP($email, $otp, $pdo = null) {
        $htmlContent = '
            <div style="font-family: Arial, sans-serif; direction: rtl; text-align: right; background-color: #f8fafc; padding: 20px;">
                <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <h2 style="color: #0ea5e9;">استرداد كلمة المرور - مقاييس 🔑</h2>
                    <p style="color: #334155; font-size: 16px;">لقد تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك. استخدم رمز التحقق التالي لإكمال العملية:</p>
                    <div style="background-color: #f1f5f9; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">
                        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #0f172a;">' . $otp . '</span>
                    </div>
                    <p style="color: #64748b; font-size: 14px;">هذا الرمز صالح لمدة 10 دقائق فقط. إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد وتأمين حسابك.</p>
                    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                    <p style="color: #94a3b8; font-size: 12px; text-align: center;">جميع الحقوق محفوظة © مقاييس MAGAIES</p>
                </div>
            </div>';

        return self::sendEmail($email, 'رمز استرداد كلمة المرور - مقاييس', $htmlContent, $pdo);
    }

    private static function sendEmail($email, $subject, $htmlContent, $pdo) {
        // 1. Try Custom SMTP Settings via PHPMailer
        $smtpSettings = self::getSmtpSettings($pdo);
        if ($smtpSettings) {
            return self::sendViaPHPMailer($smtpSettings, $email, $subject, $htmlContent);
        }

        // 2. Fallback to Brevo
        $brevo_api_key = getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? ($_SERVER['BREVO_API_KEY'] ?? ''));
        
        if (empty($brevo_api_key) && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'brevo_api_key'");
                $stmt->execute();
                $dbKey = $stmt->fetchColumn();
                if (!empty($dbKey)) {
                    $brevo_api_key = trim($dbKey);
                }
            } catch (\Throwable $e) {}
        }
        
        if (empty($brevo_api_key)) {
            $brevo_api_key = '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';
        }

        if (empty($brevo_api_key) || $brevo_api_key === 'YOUR_BREVO_API_KEY') {
            // 3. Fallback to PHP mail()
            $headers = "MIME-Version: 1.0\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\n";
            $headers .= "From: MAGAIES <info@elsajana.com>\n";
            $headers .= "Reply-To: info@elsajana.com\n";
            
            $mailSent = @mail($email, $subject, $htmlContent, $headers, "-finfo@elsajana.com");
            if ($mailSent) {
                return ['success' => true, 'message' => 'تم الإرسال بنجاح (mail)'];
            } else {
                return ['success' => false, 'error' => 'تعذر إرسال الإيميل، يرجى التأكد من إعدادات البريد في الخادم.'];
            }
        }
        
        // Send via Brevo API
        $mailData = [
            'sender' => ['name' => 'مقاييس', 'email' => 'magaiees00105@gmail.com'],
            'to' => [['email' => $email]],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailData));
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
        } else {
            $resData = json_decode($response, true);
            $errMsg = $resData['message'] ?? 'فشل إرسال الإيميل';
            return ['success' => false, 'error' => 'Brevo: ' . $errMsg];
        }
    }
}

