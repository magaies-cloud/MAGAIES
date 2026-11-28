import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/constants/app_sizes.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/presentation/widgets/otp_auto_fill_input.dart';
import 'package:magaiees_marketplace/features/locale/presentation/widgets/language_switcher.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:go_router/go_router.dart';
import 'package:local_auth/local_auth.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  final LocalAuthentication _localAuth = LocalAuthentication();
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();
  bool _canCheckBiometrics = false;
  bool _hasFaceId = false;
  bool _isPasswordVisible = false;
  StreamSubscription<AuthState>? _authSub;
  bool _isRecoveryModalOpen = false;

  @override
  void initState() {
    super.initState();
    _checkBiometrics();
    _setupPasswordRecoveryListener();
  }

  void _setupPasswordRecoveryListener() {
    _authSub = Supabase.instance.client.auth.onAuthStateChange.listen((data) {
      if (data.event == AuthChangeEvent.passwordRecovery && mounted && !_isRecoveryModalOpen) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted && !_isRecoveryModalOpen) {
            _showDirectSetNewPasswordModal();
          }
        });
      }
    });

    if (kIsWeb) {
      final fullUri = Uri.base.toString();
      if (fullUri.contains('type=recovery') || fullUri.contains('access_token=')) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted && !_isRecoveryModalOpen) {
            _showDirectSetNewPasswordModal();
          }
        });
      }
    }
  }

  Future<void> _showDirectSetNewPasswordModal() async {
    if (_isRecoveryModalOpen || !mounted) return;
    _isRecoveryModalOpen = true;
    final isRtl = context.isRtl;
    final newPassCtrl = TextEditingController();
    final confirmPassCtrl = TextEditingController();
    bool isSaving = false;
    bool obscure1 = true;
    bool obscure2 = true;

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              const Icon(Icons.lock_reset_rounded, color: Color(0xFF2563EB), size: 28),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isRtl ? 'تعيين كلمة مرور جديدة' : 'Set New Password',
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 17),
                ),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  isRtl
                      ? 'أدخل كلمة المرور الجديدة لحسابك في منصة مقاييس:'
                      : 'Please enter the new password for your Magaiees account:',
                  style: const TextStyle(fontSize: 13.5, height: 1.4, color: Color(0xFF475569)),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: newPassCtrl,
                  obscureText: obscure1,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'كلمة المرور الجديدة' : 'New Password',
                    prefixIcon: const Icon(Icons.lock_outline),
                    suffixIcon: IconButton(
                      icon: Icon(obscure1 ? Icons.visibility_off : Icons.visibility),
                      onPressed: () => setDlgState(() => obscure1 = !obscure1),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: confirmPassCtrl,
                  obscureText: obscure2,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'تأكيد كلمة المرور' : 'Confirm Password',
                    prefixIcon: const Icon(Icons.lock_person_outlined),
                    suffixIcon: IconButton(
                      icon: Icon(obscure2 ? Icons.visibility_off : Icons.visibility),
                      onPressed: () => setDlgState(() => obscure2 = !obscure2),
                    ),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: isSaving ? null : () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              onPressed: isSaving
                  ? null
                  : () async {
                      final p1 = newPassCtrl.text.trim();
                      final p2 = confirmPassCtrl.text.trim();
                      if (p1.length < 6) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              isRtl
                                  ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'
                                  : 'Password must be at least 6 characters',
                            ),
                          ),
                        );
                        return;
                      }
                      if (p1 != p2) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              isRtl
                                  ? 'كلمتا المرور غير متطابقتين'
                                  : 'Passwords do not match',
                            ),
                          ),
                        );
                        return;
                      }
                      setDlgState(() => isSaving = true);
                      try {
                        await Supabase.instance.client.auth.updateUser(
                          UserAttributes(password: p1),
                        );
                        if (ctx.mounted) Navigator.pop(ctx);
                        if (mounted) {
                          _passwordController.text = p1;
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? '✅ تم تحديث كلمة المرور بنجاح!'
                                    : '✅ Password updated successfully!',
                              ),
                              backgroundColor: AppColors.success,
                            ),
                          );
                        }
                      } catch (e) {
                        if (ctx.mounted) setDlgState(() => isSaving = false);
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? 'تعذر تحديث كلمة المرور: $e'
                                    : 'Could not update password: $e',
                              ),
                            ),
                          );
                        }
                      }
                    },
              child: isSaving
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(isRtl ? 'حفظ كلمة المرور' : 'Save Password'),
            ),
          ],
        ),
      ),
    );
    newPassCtrl.dispose();
    confirmPassCtrl.dispose();
    _isRecoveryModalOpen = false;
  }

  Future<void> _checkBiometrics() async {
    try {
      if (kIsWeb) {
        if (mounted) setState(() => _canCheckBiometrics = false);
        return;
      }

      final prefs = await SharedPreferences.getInstance();
      final isEnabled = prefs.getBool('biometric_enabled') ?? true;
      if (!isEnabled) {
        if (mounted) {
          setState(() {
            _canCheckBiometrics = false;
          });
        }
        return;
      }

      final canCheck = await _localAuth.canCheckBiometrics;
      final isSupported = await _localAuth.isDeviceSupported();

      List<BiometricType> available = [];
      try {
        available = await _localAuth.getAvailableBiometrics();
      } catch (_) {}

      final hasFace = available.contains(BiometricType.face);

      if (mounted) {
        setState(() {
          _canCheckBiometrics = canCheck || isSupported;
          _hasFaceId = hasFace;
        });
      }
    } catch (_) {}
  }

  Future<void> _handleBiometricLogin() async {
    if (kIsWeb) return;

    final isRtl = mounted ? context.isRtl : true;
    final savedEmail = await _secureStorage.read(key: 'saved_email');
    final savedPassword = await _secureStorage.read(key: 'saved_password');

    if (!mounted) return;

    if (savedEmail == null || savedPassword == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            isRtl
                ? 'يرجى تسجيل الدخول بكلمة المرور لمرة واحدة أولاً لحفظ البصمة / Face ID'
                : 'Please log in with password once first to enable Biometrics / Face ID',
          ),
          backgroundColor: AppColors.primary,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
        ),
      );
      return;
    }

    try {
      final authenticated = await _localAuth.authenticate(
        localizedReason: isRtl
            ? 'يرجى تأكيد هويتك بالبصمة أو Face ID لتسجيل الدخول السريع'
            : 'Please authenticate with Biometrics or Face ID to log in',
        biometricOnly: false,
        persistAcrossBackgrounding: true,
      );

      if (authenticated && mounted) {
        _emailController.text = savedEmail;
        _passwordController.text = savedPassword;
        _handleLogin();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              isRtl
                  ? 'تعذر التحقق من البصمة / Face ID، يرجى إدخال كلمة المرور'
                  : 'Biometric verification failed, please enter password',
            ),
            backgroundColor: Colors.orange.shade800,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _showForgotPasswordDialog(bool isRtl) async {
    final resetEmailCtrl = TextEditingController(text: _emailController.text.trim());
    final tokenOrLinkCtrl = TextEditingController();
    final newPassCtrl = TextEditingController();
    final confirmPassCtrl = TextEditingController();
    bool isSending = false;
    bool isResending = false;
    bool emailSentStep = false;
    bool obscurePass = true;
    int resendSeconds = 60;
    Timer? countdownTimer;

    void startResendTimer(
      BuildContext dlgCtx,
      void Function(void Function()) setDlgState,
    ) {
      countdownTimer?.cancel();
      resendSeconds = 60;
      countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
        if (resendSeconds <= 1) {
          timer.cancel();
          if (dlgCtx.mounted) setDlgState(() => resendSeconds = 0);
        } else {
          if (dlgCtx.mounted) setDlgState(() => resendSeconds--);
        }
      });
    }

    Future<bool> sendSixDigitOtpToEmail(String email) async {
      final supabase = Supabase.instance.client;
      final cleanEmail = email.trim().toLowerCase();
      final tempPw = await supabase.rpc(
        'prepare_forgot_password_reauth',
        params: {'target_email': cleanEmail},
      );
      if (tempPw == null || tempPw.toString().trim().isEmpty) {
        return false;
      }
      await supabase.auth.signInWithPassword(
        email: cleanEmail,
        password: tempPw.toString().trim(),
      );
      await supabase.auth.reauthenticate();
      await supabase.auth.signOut();
      return true;
    }

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFF2563EB).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.lock_reset_rounded, color: Color(0xFF2563EB), size: 26),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  emailSentStep
                      ? (isRtl ? 'تعيين كلمة المرور الجديدة' : 'Reset Your Password')
                      : (isRtl ? 'استعادة كلمة المرور' : 'Forgot Password'),
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 17),
                ),
              ),
            ],
          ),
          content: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 440),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (!emailSentStep) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: Text(
                        isRtl
                            ? 'أدخل بريدك الإلكتروني المسجل وسنرسل إليك رمز التحقق (OTP) المكون من 6 أرقام لتعيين كلمة مرور جديدة.'
                            : 'Enter your registered email address and we will send you a 6-digit OTP verification code to set a new password.',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          height: 1.45,
                          color: Color(0xFF1E3A8A),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: resetEmailCtrl,
                      keyboardType: TextInputType.emailAddress,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'البريد الإلكتروني' : 'Email Address',
                        prefixIcon: const Icon(Icons.email_outlined),
                      ),
                    ),
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFBBF7D0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl
                                ? '✅ تم إرسال رمز التحقق (OTP) المكون من 6 أرقام إلى بريدك الإلكتروني:'
                                : '✅ A 6-digit OTP verification code has been sent to your email:',
                            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, height: 1.45, color: Color(0xFF166534)),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            resetEmailCtrl.text.trim(),
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF15803D)),
                            textDirection: TextDirection.ltr,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
                    OtpAutoFillInput(
                      isRtl: isRtl,
                      enabled: !isSending,
                      onChanged: (code) {
                        tokenOrLinkCtrl.text = code;
                      },
                    ),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (resendSeconds > 0)
                          Text(
                            isRtl
                                ? 'إعادة إرسال الرمز بعد ($resendSeconds ثانية)'
                                : 'Resend code in (${resendSeconds}s)',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                          )
                        else
                          TextButton.icon(
                            onPressed: isResending || isSending
                                ? null
                                : () async {
                                    setDlgState(() => isResending = true);
                                    try {
                                      await sendSixDigitOtpToEmail(resetEmailCtrl.text);
                                      if (ctx.mounted) {
                                        setDlgState(() => isResending = false);
                                        startResendTimer(ctx, setDlgState);
                                      }
                                      if (mounted) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text(
                                              isRtl
                                                  ? '✅ تم إرسال رمز تحقق جديد (6 أرقام) إلى بريدك الإلكتروني'
                                                  : '✅ A new 6-digit verification code has been sent to your email',
                                            ),
                                            backgroundColor: AppColors.success,
                                          ),
                                        );
                                      }
                                    } catch (_) {
                                      try {
                                        await Supabase.instance.client.auth.signOut();
                                      } catch (_) {}
                                      if (ctx.mounted) {
                                        setDlgState(() => isResending = false);
                                      }
                                    }
                                  },
                            icon: isResending
                                ? const SizedBox(
                                    width: 14,
                                    height: 14,
                                    child: CircularProgressIndicator(strokeWidth: 2),
                                  )
                                : const Icon(Icons.refresh_rounded, size: 16),
                            label: Text(
                              isRtl ? 'إعادة إرسال الرمز' : 'Resend Code',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: newPassCtrl,
                      obscureText: obscurePass,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'كلمة المرور الجديدة' : 'New Password',
                        prefixIcon: const Icon(Icons.lock_outline),
                        suffixIcon: IconButton(
                          icon: Icon(obscurePass ? Icons.visibility_off : Icons.visibility),
                          onPressed: () => setDlgState(() => obscurePass = !obscurePass),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: confirmPassCtrl,
                      obscureText: obscurePass,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'تأكيد كلمة المرور' : 'Confirm Password',
                        prefixIcon: const Icon(Icons.lock_person_outlined),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: isSending
                  ? null
                  : () async {
                      countdownTimer?.cancel();
                      if (emailSentStep) {
                        final email = resetEmailCtrl.text.trim().toLowerCase();
                        try {
                          await Supabase.instance.client.rpc(
                            'cancel_forgot_password_reauth',
                            params: {'target_email': email},
                          );
                        } catch (_) {}
                      }
                      if (ctx.mounted && Navigator.of(ctx).canPop()) {
                        Navigator.of(ctx).pop();
                      }
                    },
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            if (!emailSentStep)
              FilledButton(
                onPressed: isSending
                    ? null
                    : () async {
                        final email = resetEmailCtrl.text.trim().toLowerCase();
                        if (email.isEmpty || !email.contains('@')) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? 'يرجى إدخال بريد إلكتروني صحيح'
                                    : 'Please enter a valid email address',
                              ),
                            ),
                          );
                          return;
                        }
                        setDlgState(() => isSending = true);
                        try {
                          final found = await sendSixDigitOtpToEmail(email);
                          if (!found) {
                            if (ctx.mounted) setDlgState(() => isSending = false);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    isRtl
                                        ? 'لم يتم العثور على حساب مسجل بهذا البريد الإلكتروني.'
                                        : 'No account found with this email address.',
                                  ),
                                  backgroundColor: AppColors.error,
                                ),
                              );
                            }
                            return;
                          }
                          if (ctx.mounted) {
                            setDlgState(() {
                              isSending = false;
                              emailSentStep = true;
                            });
                            startResendTimer(ctx, setDlgState);
                          }
                        } catch (e) {
                          try {
                            await Supabase.instance.client.auth.signOut();
                          } catch (_) {}
                          if (ctx.mounted) setDlgState(() => isSending = false);
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  isRtl
                                      ? 'تعذر إرسال رمز التحقق حالياً، يرجى الانتظار دقيقة والمحاولة مرة أخرى.'
                                      : 'Could not send verification code. Please wait a minute and try again.',
                                ),
                                backgroundColor: AppColors.error,
                              ),
                            );
                          }
                        }
                      },
                child: isSending
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(isRtl ? 'إرسال رمز التحقق (OTP)' : 'Send OTP Code'),
              )
            else
              FilledButton(
                onPressed: isSending
                    ? null
                    : () async {
                        final email = resetEmailCtrl.text.trim().toLowerCase();
                        final cleanCode = tokenOrLinkCtrl.text.replaceAll(RegExp(r'[^0-9]'), '');
                        final newPass = newPassCtrl.text.trim();
                        final confirmPass = confirmPassCtrl.text.trim();

                        if (cleanCode.length != 6) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? 'يرجى إدخال رمز التحقق المكون من 6 أرقام المرسل لبريدك.'
                                    : 'Please enter the 6-digit verification code sent to your email.',
                              ),
                              backgroundColor: AppColors.error,
                            ),
                          );
                          return;
                        }
                        if (newPass.length < 6) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'
                                    : 'Password must be at least 6 characters',
                              ),
                            ),
                          );
                          return;
                        }
                        if (newPass != confirmPass) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? 'كلمتا المرور غير متطابقتين'
                                    : 'Passwords do not match',
                              ),
                            ),
                          );
                          return;
                        }

                        setDlgState(() => isSending = true);
                        try {
                          final verified = await Supabase.instance.client.rpc(
                            'verify_reauth_otp',
                            params: {
                              'target_email': email,
                              'input_code': cleanCode,
                              'new_password': newPass,
                            },
                          );

                          if (verified != true) {
                            throw Exception('Invalid OTP code');
                          }

                          countdownTimer?.cancel();
                          if (ctx.mounted && Navigator.of(ctx).canPop()) {
                            Navigator.of(ctx).pop();
                          }
                          if (mounted) {
                            _emailController.text = email;
                            _passwordController.text = newPass;
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  isRtl
                                      ? '✅ تم تعيين كلمة المرور الجديدة بنجاح! يمكنك تسجيل الدخول الآن'
                                      : '✅ Password updated successfully! You can sign in now',
                                ),
                                backgroundColor: AppColors.success,
                              ),
                            );
                          }
                        } catch (e) {
                          if (ctx.mounted) setDlgState(() => isSending = false);
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  isRtl
                                      ? 'رمز التحقق (OTP) غير صحيح أو منتهي الصلاحية، يرجى التأكد من الرمز المكون من 6 أرقام.'
                                      : 'Invalid or expired 6-digit OTP code. Please check your email.',
                                ),
                                backgroundColor: AppColors.error,
                              ),
                            );
                          }
                        }
                      },
                child: isSending
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(isRtl ? 'حفظ كلمة المرور الجديدة' : 'Save New Password'),
              ),
          ],
        ),
      ),
    );
    countdownTimer?.cancel();
    resetEmailCtrl.dispose();
    tokenOrLinkCtrl.dispose();
    newPassCtrl.dispose();
    confirmPassCtrl.dispose();
  }

  @override
  void dispose() {
    _authSub?.cancel();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    await ref.read(authNotifierProvider.notifier).login(
          email: _emailController.text.trim(),
          password: _passwordController.text,
        );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final isLoading = authState.isLoading;
    final isRtl = context.isRtl;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final screenWidth = MediaQuery.of(context).size.width;
    final cardPadding = screenWidth < 390 ? 22.0 : 34.0;

    // Listen for errors and successes
    ref.listen(authNotifierProvider, (prev, next) {
      if (next.hasError && !next.isLoading) {
        final errorStr = next.error.toString();
        String displayMsgAr = 'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً';
        String displayMsgEn = 'An unexpected error occurred, please try again';

        if (errorStr.contains('Account deleted or suspended')) {
          displayMsgAr = 'هذا الحساب محذوف أو موقوف عن الاستخدام.';
          displayMsgEn = 'This account has been deleted or suspended.';
        } else if (errorStr.contains('Invalid login credentials') || errorStr.contains('invalid_credentials')) {
          displayMsgAr = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
          displayMsgEn = 'Invalid email or password';
        }

        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Column(
              children: [
                const Icon(Icons.error_outline_rounded, color: Colors.redAccent, size: 52),
                const SizedBox(height: 12),
                Text(isRtl ? 'تنبيه' : 'Notice', style: const TextStyle(fontWeight: FontWeight.bold)),
              ],
            ),
            content: Text(
              isRtl ? displayMsgAr : displayMsgEn,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 15),
            ),
            actionsAlignment: MainAxisAlignment.center,
            actions: [
              FilledButton(
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.redAccent,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                ),
                onPressed: () => Navigator.of(ctx).pop(),
                child: Text(isRtl ? 'حسناً' : 'OK', style: const TextStyle(fontWeight: FontWeight.bold)),
              )
            ],
          ),
        );
      } else if (next.hasValue && next.value != null && !next.isLoading && !next.value!.role.isGuest) {
        TextInput.finishAutofillContext(shouldSave: true);
        if (_emailController.text.isNotEmpty && _passwordController.text.isNotEmpty) {
          _secureStorage.write(key: 'saved_email', value: _emailController.text.trim()).catchError((_) {});
          _secureStorage.write(key: 'saved_password', value: _passwordController.text).catchError((_) {});
        }
      }
    });

    return Scaffold(
      appBar: AppBar(
        title: Text(
          isRtl ? 'منصة مقاييس' : 'Magaiees Platform',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        actions: const [LanguageSwitcher()],
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        color: isDark ? const Color(0xFF050B14) : const Color(0xFFF1F5F9),
        child: Center(
          child: SingleChildScrollView(
            padding: EdgeInsets.symmetric(
              horizontal: screenWidth < 390 ? 16.0 : AppSizes.p24,
              vertical: AppSizes.p20,
            ),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Container(
                padding: EdgeInsets.all(cardPadding),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0F172A) : Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.05),
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    )
                  ],
                ),
                child: AutofillGroup(
                  child: Form(
                    key: _formKey,
                    child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Center(
                        child: Image.asset(
                          'assets/images/logo_final.png',
                          height: 100,
                          errorBuilder: (context, error, stackTrace) => const SizedBox(height: 100),
                          frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
                            if (wasSynchronouslyLoaded || frame != null) return child;
                            return const SizedBox(height: 100);
                          },
                        ),
                      ),
                      const SizedBox(height: 20),

                      Text(
                        isRtl ? 'تسجيل الدخول' : 'Welcome Back',
                        style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.w900,
                              color: isDark ? Colors.white : const Color(0xFF0F172A),
                            ),
                        textAlign: TextAlign.center,
                      ),
                      AppSizes.gapH8,
                      Text(
                        isRtl
                            ? 'أدخل بيانات الاعتماد للوصول إلى منصة مقاييس'
                            : 'Enter your credentials to access Magaiees Marketplace',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                              fontSize: 13.5,
                            ),
                        textAlign: TextAlign.center,
                      ),
                      AppSizes.gapH24,

                      // Email
                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        textInputAction: TextInputAction.next,
                        autofillHints: const [AutofillHints.username, AutofillHints.email],
                        style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A)),
                        decoration: InputDecoration(
                          labelText: isRtl ? 'البريد الإلكتروني' : 'Email Address',
                          prefixIcon: const Icon(Icons.email_outlined, color: Color(0xFF94A3B8)),
                          filled: true,
                          fillColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: isDark ? AppColors.primaryLight : AppColors.primary, width: 2),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return isRtl ? 'الرجاء إدخال البريد الإلكتروني' : 'Please enter your email';
                          }
                          return null;
                        },
                      ),
                      AppSizes.gapH16,

                      // Password with Show/Hide Toggle
                      TextFormField(
                        controller: _passwordController,
                        obscureText: !_isPasswordVisible,
                        textInputAction: TextInputAction.done,
                        autofillHints: const [AutofillHints.password],
                        onFieldSubmitted: (_) => _handleLogin(),
                        style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A)),
                        decoration: InputDecoration(
                          labelText: isRtl ? 'كلمة المرور' : 'Password',
                          prefixIcon: const Icon(Icons.lock_outlined, color: Color(0xFF94A3B8)),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _isPasswordVisible ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                              color: const Color(0xFF94A3B8),
                            ),
                            onPressed: () => setState(() => _isPasswordVisible = !_isPasswordVisible),
                            tooltip: isRtl
                                ? (_isPasswordVisible ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور')
                                : (_isPasswordVisible ? 'Hide password' : 'Show password'),
                          ),
                          filled: true,
                          fillColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: isDark ? AppColors.primaryLight : AppColors.primary, width: 2),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return isRtl ? 'الرجاء إدخال كلمة المرور' : 'Please enter your password';
                          }
                          return null;
                        },
                      ),

                      Align(
                        alignment: AlignmentDirectional.centerEnd,
                        child: TextButton(
                          onPressed: () => _showForgotPasswordDialog(isRtl),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            foregroundColor: isDark ? AppColors.primaryLight : AppColors.primary,
                          ),
                          child: Text(
                            isRtl ? 'نسيت كلمة المرور؟' : 'Forgot Password?',
                            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600),
                          ),
                        ),
                      ),
                      AppSizes.gapH16,

                      // Login Button
                      if (_canCheckBiometrics)
                        Row(
                          children: [
                            Expanded(
                              child: FilledButton(
                                onPressed: isLoading ? null : _handleLogin,
                                style: FilledButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(vertical: 15),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  backgroundColor: AppColors.primary,
                                  foregroundColor: Colors.white,
                                ),
                                child: isLoading
                                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                    : Text(isRtl ? 'تسجيل الدخول' : 'Sign In', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                              ),
                            ),
                            const SizedBox(width: 10),
                            IconButton.filledTonal(
                              onPressed: _handleBiometricLogin,
                              icon: Icon(_hasFaceId ? Icons.face_rounded : Icons.fingerprint_rounded, size: 26),
                              tooltip: isRtl
                                  ? (_hasFaceId ? 'تسجيل الدخول عبر Face ID' : 'تسجيل الدخول بالبصمة')
                                  : (_hasFaceId ? 'Sign in with Face ID' : 'Sign in with Biometrics'),
                              style: IconButton.styleFrom(
                                padding: const EdgeInsets.all(13),
                                backgroundColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFEFF6FF),
                                foregroundColor: isDark ? AppColors.primaryLight : AppColors.primary,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ],
                        )
                      else
                        FilledButton(
                          onPressed: isLoading ? null : _handleLogin,
                          style: FilledButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 15),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            backgroundColor: AppColors.primary,
                            foregroundColor: Colors.white,
                          ),
                          child: isLoading
                              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : Text(isRtl ? 'تسجيل الدخول' : 'Sign In', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        ),
                      AppSizes.gapH16,

                      // Guest Login Button
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          side: BorderSide(color: isDark ? AppColors.primaryLight : AppColors.primary),
                          foregroundColor: isDark ? AppColors.primaryLight : AppColors.primary,
                        ),
                        onPressed: isLoading
                            ? null
                            : () {
                                ref.read(authNotifierProvider.notifier).loginAsGuest();
                              },
                        icon: const Icon(Icons.explore_outlined, size: 20),
                        label: Text(
                          isRtl ? 'تصفح المنصة كزائر' : 'Browse as Guest',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                      ),
                      AppSizes.gapH12,

                      // Register link
                      TextButton(
                        style: TextButton.styleFrom(
                          foregroundColor: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                        ),
                        onPressed: () {
                          context.push('/register');
                        },
                        child: Text(
                          isRtl ? 'ليس لديك حساب؟ إنشاء حساب جديد' : "Don't have an account? Create Account",
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
