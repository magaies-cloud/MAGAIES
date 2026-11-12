import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';

/// Helper to handle microphone permissions with a world-class,
/// international-grade user experience (matching WhatsApp, Telegram).
class CallPermissionHelper {
  /// Request microphone permission with high-end UI explanation dialog.
  /// Returns `true` if permission is granted and call can proceed.
  static Future<bool> requestMicrophonePermission(BuildContext context) async {
    // 1. Web-specific handling
    if (kIsWeb) {
      final bool isInsecureHttp = Uri.base.scheme == 'http' &&
          Uri.base.host != 'localhost' &&
          Uri.base.host != '127.0.0.1';

      if (isInsecureHttp) {
        if (context.mounted) {
          _showWebInsecureHttpDialog(context);
        }
        return false;
      }
      return true;
    }

    // 2. Mobile (Android & iOS) handling via permission_handler
    final status = await Permission.microphone.status;

    if (status.isGranted) {
      return true;
    }

    if (status.isPermanentlyDenied) {
      if (context.mounted) {
        return await _showPermanentlyDeniedDialog(context);
      }
      return false;
    }

    // If denied or not yet requested, show the luxury pre-permission dialog
    if (context.mounted) {
      final shouldRequest = await _showRationaleDialog(context);
      if (!shouldRequest) return false;

      final result = await Permission.microphone.request();
      if (result.isGranted) {
        return true;
      } else if (result.isPermanentlyDenied) {
        if (context.mounted) {
          return await _showPermanentlyDeniedDialog(context);
        }
      }
    }

    return false;
  }

  /// High-end rationale dialog explaining why microphone is required
  static Future<bool> _showRationaleDialog(BuildContext context) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 16),
        titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
        title: Column(
          children: [
            Container(
              width: 76,
              height: 76,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF2563EB).withValues(alpha: 0.35),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: const Icon(Icons.mic_rounded, size: 40, color: Colors.white),
            ),
            const SizedBox(height: 18),
            Text(
              isRtl ? 'السماح بالوصول إلى الميكروفون' : 'Allow Microphone Access',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 19,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              isRtl ? 'مطلوب لبدء المكالمة الصوتية المباشرة' : 'Required to start voice call',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  _FeatureRow(
                    icon: Icons.record_voice_over_rounded,
                    iconColor: const Color(0xFF2563EB),
                    title: isRtl ? 'محادثة صوتية واضحة' : 'Clear Voice Chat',
                    subtitle: isRtl
                        ? 'يتيح لك التحدث المباشر مع المتجر لمناقشة تفاصيل الطلبات والأسعار.'
                        : 'Enables direct voice talk with the store to discuss orders.',
                  ),
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 12),
                    child: Divider(height: 1, color: Color(0xFFE2E8F0)),
                  ),
                  _FeatureRow(
                    icon: Icons.shield_rounded,
                    iconColor: const Color(0xFF059669),
                    title: isRtl ? 'خصوصية وأمان 100%' : '100% Privacy & Security',
                    subtitle: isRtl
                        ? 'المكالمات مشفرة بالكامل من طرف لطرف، ولا يتم تسجيل أو حفظ صوتك أبداً.'
                        : 'Calls are end-to-end encrypted. Audio is never recorded.',
                  ),
                ],
              ),
            ),
          ],
        ),
        actionsPadding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
        actions: [
          Row(
            children: [
              Expanded(
                child: TextButton(
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () => Navigator.pop(ctx, false),
                  child: Text(
                    isRtl ? 'ليس الآن' : 'Not Now',
                    style: TextStyle(
                      color: Colors.grey.shade600,
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1E3A8A),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () => Navigator.pop(ctx, true),
                  child: Text(
                    isRtl ? 'متابعة وسماح' : 'Continue & Allow',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    return result ?? false;
  }

  /// Dialog shown when permission was permanently denied by the user
  static Future<bool> _showPermanentlyDeniedDialog(BuildContext context) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 16),
        titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
        title: Column(
          children: [
            Container(
              width: 76,
              height: 76,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFD97706), Color(0xFFF59E0B)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFFF59E0B).withValues(alpha: 0.35),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: const Icon(Icons.settings_voice_rounded, size: 40, color: Colors.white),
            ),
            const SizedBox(height: 18),
            Text(
              isRtl ? 'تفعيل إذن الميكروفون' : 'Enable Microphone',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 19,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              isRtl ? 'الإذن معطّل حالياً في إعدادات الهاتف' : 'Permission is currently disabled',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
        content: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFFFFFBEB),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFFDE68A)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                isRtl
                    ? 'لقد تم تعطيل إذن الميكروفون سابقاً. لإجراء المكالمات الصوتية، يرجى فتح إعدادات الهاتف وتفعيل خيار «الميكروفون» لتطبيق السيطرة.'
                    : 'Microphone permission was previously denied. Please open Settings and enable the Microphone toggle for Magaiees.',
                style: TextStyle(
                  fontSize: 13,
                  height: 1.5,
                  color: Colors.amber.shade900,
                ),
              ),
            ],
          ),
        ),
        actionsPadding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
        actions: [
          Row(
            children: [
              Expanded(
                child: TextButton(
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () => Navigator.pop(ctx, false),
                  child: Text(
                    isRtl ? 'إلغاء' : 'Cancel',
                    style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1E3A8A),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () async {
                    Navigator.pop(ctx, false);
                    await openAppSettings();
                  },
                  icon: const Icon(Icons.settings_rounded, size: 18),
                  label: Text(
                    isRtl ? 'فتح إعدادات الهاتف' : 'Open Settings',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    return result ?? false;
  }

  /// Informative dialog for Web over insecure HTTP
  static void _showWebInsecureHttpDialog(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        backgroundColor: Colors.white,
        title: Column(
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                shape: BoxShape.circle,
              ),
              child: Icon(Icons.lock_outline_rounded, size: 36, color: Colors.red.shade700),
            ),
            const SizedBox(height: 14),
            Text(
              isRtl ? 'تنبيه أمان المتصفح' : 'Browser Security Notice',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
          ],
        ),
        content: Text(
          isRtl
              ? 'متصفحات الهواتف (كروم وسفاري) تحظر تشغيل الميكروفون عبر روابط الـ HTTP غير المشفرة لحماية خصوصيتك.\n\nلتجربة الاتصال الصوتي، يرجى فتح التطبيق المثبت على هاتفك (APK) أو استخدام اتصال مشفر (HTTPS).'
              : 'Mobile browsers block microphone access over insecure HTTP connections. Please use the installed Android APK or HTTPS.',
          style: TextStyle(fontSize: 13, height: 1.5, color: Colors.grey.shade800),
        ),
        actions: [
          Center(
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1E3A8A),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: () => Navigator.pop(ctx),
              child: Text(isRtl ? 'حسناً، فهمت' : 'Got it'),
            ),
          ),
        ],
      ),
    );
  }
}

class _FeatureRow extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String subtitle;

  const _FeatureRow({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.subtitle,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: iconColor.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, size: 22, color: iconColor),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 13.5,
                  color: Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 3),
              Text(
                subtitle,
                style: TextStyle(
                  fontSize: 12,
                  height: 1.35,
                  color: Colors.grey.shade600,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
