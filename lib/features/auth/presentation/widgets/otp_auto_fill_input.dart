import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';

/// A clean 6-digit OTP input widget that supports:
/// 1. OS Keyboard & Notification Auto-Fill (`AutofillHints.oneTimeCode`) on Android & iOS.
/// 2. Silent Automatic Clipboard Detection when the user returns from their Email app
///    on the same device (`AppLifecycleState.resumed`), filling the 6 boxes automatically
///    without displaying any "Auto-Paste" button or extra clutter.
/// 3. Direct typing and pasting into the 6 boxes with auto-submit when all 6 digits are entered.
class OtpAutoFillInput extends StatefulWidget {
  final ValueChanged<String> onChanged;
  final ValueChanged<String>? onCompleted;
  final bool isRtl;
  final bool enabled;

  const OtpAutoFillInput({
    super.key,
    required this.onChanged,
    this.onCompleted,
    this.isRtl = true,
    this.enabled = true,
  });

  @override
  State<OtpAutoFillInput> createState() => _OtpAutoFillInputState();
}

class _OtpAutoFillInputState extends State<OtpAutoFillInput> with WidgetsBindingObserver {
  final TextEditingController _hiddenController = TextEditingController();
  final FocusNode _focusNode = FocusNode();
  String _currentCode = '';
  String _lastAutoFilledClipboard = '';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && widget.enabled) {
        _focusNode.requestFocus();
      }
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _hiddenController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // Silently auto-fill the 6-digit OTP when returning from the email app on the same device
    if (state == AppLifecycleState.resumed && widget.enabled) {
      _tryAutoFillFromClipboard(onlyIfFresh: true);
    }
  }

  Future<void> _tryAutoFillFromClipboard({required bool onlyIfFresh}) async {
    try {
      final data = await Clipboard.getData(Clipboard.kTextPlain);
      final raw = data?.text?.trim() ?? '';
      if (raw.isEmpty) return;

      final match = RegExp(r'(?<!\d)(\d{6})(?!\d)').firstMatch(raw);
      if (match != null) {
        final code = match.group(1)!;
        if (onlyIfFresh && code == _lastAutoFilledClipboard) return;
        if (_currentCode == code) return;

        _lastAutoFilledClipboard = code;
        _setCode(code);
      }
    } catch (_) {}
  }

  void _setCode(String code) {
    final digitsOnly = code.replaceAll(RegExp(r'[^0-9]'), '');
    final clamped = digitsOnly.length > 6 ? digitsOnly.substring(0, 6) : digitsOnly;

    if (_hiddenController.text != clamped) {
      _hiddenController.value = TextEditingValue(
        text: clamped,
        selection: TextSelection.collapsed(offset: clamped.length),
      );
    }

    setState(() {
      _currentCode = clamped;
    });

    widget.onChanged(clamped);
    if (clamped.length == 6) {
      widget.onCompleted?.call(clamped);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return AutofillGroup(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Hidden native TextField that captures OS AutofillHints.oneTimeCode, keyboard typing, and paste
          SizedBox(
            height: 1,
            width: 1,
            child: Opacity(
              opacity: 0.0,
              child: TextField(
                controller: _hiddenController,
                focusNode: _focusNode,
                enabled: widget.enabled,
                keyboardType: TextInputType.number,
                autofillHints: const [AutofillHints.oneTimeCode],
                maxLength: 6,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(6),
                ],
                onChanged: (val) => _setCode(val),
              ),
            ),
          ),

          // 6 Visual OTP Digit Boxes (Always LTR so digits read left-to-right)
          Directionality(
            textDirection: TextDirection.ltr,
            child: GestureDetector(
              onTap: () {
                if (!widget.enabled) return;
                if (_focusNode.hasFocus) {
                  _focusNode.unfocus();
                  Future.delayed(const Duration(milliseconds: 50), () {
                    if (mounted) _focusNode.requestFocus();
                  });
                } else {
                  _focusNode.requestFocus();
                }
              },
              onLongPress: () async {
                if (!widget.enabled) return;
                await _tryAutoFillFromClipboard(onlyIfFresh: false);
              },
              child: ListenableBuilder(
                listenable: _focusNode,
                builder: (context, _) {
                  final hasFocus = _focusNode.hasFocus;
                  return Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: List.generate(6, (index) {
                      final char = index < _currentCode.length ? _currentCode[index] : '';
                      final isActiveBox = hasFocus &&
                          (index == _currentCode.length ||
                              (index == 5 && _currentCode.length == 6));
                      final isFilled = char.isNotEmpty;

                      return AnimatedContainer(
                        duration: const Duration(milliseconds: 160),
                        width: 45,
                        height: 54,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: isFilled
                              ? AppColors.primary.withValues(alpha: isDark ? 0.22 : 0.08)
                              : (isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC)),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isActiveBox
                                ? AppColors.primary
                                : isFilled
                                    ? AppColors.primary.withValues(alpha: 0.65)
                                    : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                            width: isActiveBox || isFilled ? 2.0 : 1.2,
                          ),
                          boxShadow: isActiveBox
                              ? [
                                  BoxShadow(
                                    color: AppColors.primary.withValues(alpha: 0.18),
                                    blurRadius: 8,
                                    offset: const Offset(0, 2),
                                  ),
                                ]
                              : null,
                        ),
                        child: Text(
                          char,
                          style: TextStyle(
                            fontSize: 21,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                          ),
                        ),
                      );
                    }),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}
