<?php
class EmailService {
    public static function sendOTP($email, $otp) {
        $brevo_api_key = getenv('BREVO_API_KEY');
        
        if (empty($brevo_api_key) || $brevo_api_key === 'YOUR_BREVO_API_KEY') {
            // Development Mode
            return ['success' => true, 'message' => 'تم إنشاء الرمز بنجاح (وضع التطوير)', 'dev_otp' => $otp];
        }
        
        // Production Mode (Brevo API)
        $mailData = [
            'sender' => ['name' => 'نظام مقاييس (MAGAIES)', 'email' => 'info@elsajana.com'],
            'to' => [['email' => $email]],
            'subject' => 'رمز التحقق (OTP) الخاص بك - MAGAIES',
            'htmlContent' => '
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
                </div>'
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
            return ['success' => true, 'message' => 'تم إرسال الرمز بنجاح إلى بريدك الإلكتروني'];
        } else {
            return ['success' => false, 'error' => 'فشل إرسال الإيميل، يرجى المحاولة لاحقاً'];
        }
    }
}
