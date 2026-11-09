import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

/// A polished, production-grade error widget for admin panel screens
/// to replace raw text errors and provide one-click retry functionality.
class AdminErrorView extends StatefulWidget {
  final dynamic error;
  final StackTrace? stackTrace;
  final VoidCallback? onRetry;
  final String? customTitle;
  final String? customMessage;

  const AdminErrorView({
    super.key,
    required this.error,
    this.stackTrace,
    this.onRetry,
    this.customTitle,
    this.customMessage,
  });

  @override
  State<AdminErrorView> createState() => _AdminErrorViewState();
}

class _AdminErrorViewState extends State<AdminErrorView> {
  bool _showDetails = false;
  bool _isRetrying = false;

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final isSkew = isClockSkewError(widget.error);
    final isExpired = isJwtExpiredError(widget.error);

    String title = widget.customTitle ??
        (isSkew
            ? (isRtl ? 'جاري مزامنة توقيت الخادم' : 'Server Clock Synchronizing')
            : isExpired
                ? (isRtl ? 'انتهت صلاحية الجلسة' : 'Session Expired')
                : (isRtl ? 'تعذر تحميل البيانات' : 'Data Load Error'));

    String message = widget.customMessage ??
        (isSkew
            ? (isRtl
                ? 'حدث فارق توقيت طفيف مع خادم قاعدة البيانات، تمت موازنة التوقيت. يرجى الضغط على إعادة المحاولة.'
                : 'A minor clock skew occurred with the database server. Please click Retry to sync.')
            : isExpired
                ? (isRtl
                    ? 'انتهت صلاحية رمز الدخول، يرجى إعادة المحاولة أو تسجيل الدخول مجدداً.'
                    : 'Your session token has expired. Please retry or sign in again.')
                : (isRtl
                    ? 'تعذر الاتصال بقاعدة البيانات مؤقتاً، يرجى المحاولة مرة أخرى.'
                    : 'Temporary connection issue with the database. Please try again.'));

    final Color accentColor = isSkew
        ? const Color(0xFFD97706) // Amber / Warning
        : const Color(0xFFDC2626); // Red / Error

    return Center(
      child: Container(
        constraints: const BoxConstraints(maxWidth: 540),
        margin: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: accentColor.withOpacity(0.25), width: 1.5),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0F172A).withOpacity(0.06),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Icon
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: accentColor.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                isSkew ? Icons.sync_problem_rounded : Icons.cloud_off_rounded,
                color: accentColor,
                size: 36,
              ),
            ),
            const SizedBox(height: 18),

            // Title
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 8),

            // Description
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                color: Color(0xFF64748B),
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),

            // Retry Button
            if (widget.onRetry != null)
              SizedBox(
                height: 44,
                child: FilledButton.icon(
                  onPressed: _isRetrying
                      ? null
                      : () async {
                          setState(() => _isRetrying = true);
                          try {
                            widget.onRetry!();
                          } finally {
                            if (mounted) {
                              await Future.delayed(const Duration(milliseconds: 500));
                              if (mounted) setState(() => _isRetrying = false);
                            }
                          }
                        },
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF1E40AF),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: _isRetrying
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Icon(Icons.refresh_rounded, size: 18),
                  label: Text(
                    isRtl ? 'إعادة المحاولة' : 'Retry',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ),
              ),

            const SizedBox(height: 16),

            // Expandable technical details
            InkWell(
              onTap: () => setState(() => _showDetails = !_showDetails),
              borderRadius: BorderRadius.circular(8),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      _showDetails
                          ? (isRtl ? 'إخفاء التفاصيل التقنية' : 'Hide details')
                          : (isRtl ? 'عرض التفاصيل التقنية' : 'Technical details'),
                      style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                    ),
                    const SizedBox(width: 4),
                    Icon(
                      _showDetails ? Icons.expand_less : Icons.expand_more,
                      size: 16,
                      color: const Color(0xFF94A3B8),
                    ),
                  ],
                ),
              ),
            ),

            if (_showDetails) ...[
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Exception Log',
                          style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11, fontFamily: 'monospace'),
                        ),
                        InkWell(
                          onTap: () {
                            Clipboard.setData(ClipboardData(text: widget.error.toString()));
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'تم نسخ نص الخطأ' : 'Error copied to clipboard'),
                                duration: const Duration(seconds: 2),
                              ),
                            );
                          },
                          child: const Icon(Icons.copy_rounded, size: 14, color: Color(0xFF94A3B8)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    SelectableText(
                      widget.error.toString(),
                      style: const TextStyle(
                        color: Color(0xFFF87171),
                        fontSize: 11,
                        fontFamily: 'monospace',
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
