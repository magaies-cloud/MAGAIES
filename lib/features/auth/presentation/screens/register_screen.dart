import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/constants/app_sizes.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/presentation/widgets/otp_auto_fill_input.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';
import 'package:go_router/go_router.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();

  final _fullNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _emailFocusNode = FocusNode();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isPasswordVisible = false;
  bool _isConfirmPasswordVisible = false;
  bool _isLoading = false;

  @override
  void dispose() {
    _fullNameController.dispose();
    _emailController.dispose();
    _emailFocusNode.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    final fullName = _fullNameController.text.trim();
    final cleanEmail = _emailController.text.trim().toLowerCase();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text;
    final isRtl = context.isRtl;

    try {
      final supabase = Supabase.instance.client;

      // 1. Clean up any previously deleted or unverified account with this email
      //    so re-registering a deleted email works cleanly as a brand-new user.
      try {
        await supabase.rpc(
          'cleanup_unverified_or_deleted_auth_user',
          params: {'target_email': cleanEmail},
        );
      } catch (_) {}

      // 2. Create user in Supabase Auth (with mailer_autoconfirm=true, this returns an active session without sending the link-only email)
      final response = await supabase.auth.signUp(
        email: cleanEmail,
        password: password,
        data: {
          'name': fullName,
          'name_ar': fullName,
          'phone': phone,
          'role': 'buyer',
          'locale': isRtl ? 'ar' : 'en',
        },
      );

      if (response.user == null) {
        throw Exception('Registration failed.');
      }

      if (response.user!.identities != null && response.user!.identities!.isEmpty) {
        throw Exception('User already registered');
      }

      final userId = response.user!.id;

      // 3. Save pending user record in public.users (marked otp_pending: true until OTP is verified)
      final newUser = {
        'id': userId,
        'name': fullName,
        'name_ar': fullName,
        'email': cleanEmail,
        'phone': phone,
        'role': 'buyer',
        'is_suspended': false,
        'is_verified': false,
        'permissions': {
          'password': password,
          'otp_pending': true,
        },
      };
      try {
        await safeSupabaseQuery(() => supabase.from('users').upsert(newUser));
      } catch (_) {}

      // 4. Send the 6-digit numeric OTP email via reauthenticate()
      //    (Subject: "123456 is your verification code", Body: "123456")
      try {
        if (supabase.auth.currentSession == null) {
          await supabase.auth.signInWithPassword(
            email: cleanEmail,
            password: password,
          );
        }
        await supabase.auth.reauthenticate();
      } catch (_) {}

      // 5. Sign out immediately so the user cannot enter the app before verifying the 6-digit OTP
      try {
        await supabase.auth.signOut();
      } catch (_) {}

      if (!mounted) return;
      setState(() => _isLoading = false);

      await _showRegistrationOtpDialog(
        email: cleanEmail,
        password: password,
        fullName: fullName,
        phone: phone,
        userId: userId,
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);

      final errStr = e.toString().toLowerCase();
      String msg = isRtl
          ? 'تعذر إنشاء الحساب، يرجى التحقق من البيانات والمحاولة مرة أخرى.'
          : 'Could not create account. Please verify your details and try again.';
      if (errStr.contains('already registered') ||
          errStr.contains('already exists') ||
          errStr.contains('duplicate')) {
        msg = isRtl
            ? 'هذا البريد الإلكتروني مسجل مسبقاً، يرجى تسجيل الدخول بدلاً من ذلك.'
            : 'This email is already registered. Please sign in instead.';
      } else if (errStr.contains('rate limit') || errStr.contains('security purposes')) {
        msg = isRtl
            ? 'يرجى الانتظار قليلاً قبل طلب رمز تحقق جديد.'
            : 'Please wait a moment before requesting a new verification code.';
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  Future<void> _showRegistrationOtpDialog({
    required String email,
    required String password,
    required String fullName,
    required String phone,
    required String userId,
  }) async {
    final isRtl = context.isRtl;
    String otpCode = '';
    bool isVerifying = false;
    bool isResending = false;
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

    Future<void> verifyCode(
      String codeToVerify,
      BuildContext dlgCtx,
      void Function(void Function()) setDlgState,
    ) async {
      final cleanCode = codeToVerify.replaceAll(RegExp(r'[^0-9]'), '');
      if (cleanCode.length != 6 || isVerifying) return;

      setDlgState(() => isVerifying = true);
      try {
        final supabase = Supabase.instance.client;

        // Verify the 6-digit OTP via our SHA-224 Postgres RPC (verify_reauth_otp)
        bool verified = false;
        try {
          final rpcRes = await supabase.rpc(
            'verify_reauth_otp',
            params: {
              'target_email': email,
              'input_code': cleanCode,
              'new_password': password,
            },
          );
          verified = rpcRes == true;
        } catch (_) {}

        // Fallback to standard GoTrue verifyOTP in case token was sent via standard signup/email
        if (!verified) {
          for (final otpType in [OtpType.signup, OtpType.email, OtpType.magiclink]) {
            try {
              await supabase.auth.verifyOTP(
                email: email,
                token: cleanCode,
                type: otpType,
              );
              verified = true;
              break;
            } catch (_) {}
          }
        }

        if (!verified) {
          throw Exception('Invalid OTP code');
        }

        // Mark user active & verified in public.users
        final activeUser = {
          'id': userId,
          'name': fullName,
          'name_ar': fullName,
          'email': email,
          'phone': phone,
          'role': 'buyer',
          'is_suspended': false,
          'is_verified': true,
          'permissions': {
            'password': password,
          },
        };
        try {
          await safeSupabaseQuery(() => supabase.from('users').upsert(activeUser));
        } catch (_) {}

        countdownTimer?.cancel();
        TextInput.finishAutofillContext(shouldSave: true);
        if (dlgCtx.mounted && Navigator.of(dlgCtx).canPop()) {
          Navigator.of(dlgCtx).pop();
        }

        // Sign in and navigate to buyer home
        await ref.read(authNotifierProvider.notifier).login(
              email: email,
              password: password,
            );

        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              isRtl
                  ? '✅ تم تفعيل حسابك بنجاح! أهلاً بك في منصة مقاييس'
                  : '✅ Account verified and activated! Welcome to Magaiees',
            ),
            backgroundColor: AppColors.success,
          ),
        );
        context.go('/buyer/home');
      } catch (e) {
        if (dlgCtx.mounted) {
          setDlgState(() => isVerifying = false);
        }
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                isRtl
                    ? 'رمز التحقق غير صحيح أو منتهي الصلاحية، يرجى التأكد من الرمز المكون من 6 أرقام المرسل إلى بريدك.'
                    : 'Invalid or expired 6-digit verification code. Please check your email.',
              ),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    }

    await showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (dlgCtx) {
        bool timerInitialized = false;
        return StatefulBuilder(
          builder: (ctx, setDlgState) {
            if (!timerInitialized) {
              timerInitialized = true;
              Future.microtask(() {
                if (ctx.mounted) startResendTimer(ctx, setDlgState);
              });
            }

            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.mark_email_read_rounded, color: AppColors.primary, size: 26),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      isRtl ? 'تفعيل الحساب برمز OTP' : 'Verify Account (OTP)',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 17),
                    ),
                  ),
                ],
              ),
              content: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFBFDBFE)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isRtl
                                  ? 'أرسلنا رمز التحقق المكون من 6 أرقام إلى بريدك الإلكتروني:'
                                  : 'We sent a 6-digit verification code to your email:',
                              style: const TextStyle(
                                fontSize: 13,
                                color: Color(0xFF1E3A8A),
                                height: 1.4,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              email,
                              style: const TextStyle(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF1E40AF),
                              ),
                              textDirection: TextDirection.ltr,
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Clean 6-digit Auto-Fill OTP Input boxes
                      OtpAutoFillInput(
                        isRtl: isRtl,
                        enabled: !isVerifying,
                        onChanged: (val) {
                          otpCode = val;
                        },
                        onCompleted: (completedCode) {
                          otpCode = completedCode;
                          verifyCode(completedCode, ctx, setDlgState);
                        },
                      ),

                      const SizedBox(height: 16),

                      // Resend OTP Row
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          if (resendSeconds > 0)
                            Text(
                              isRtl
                                  ? 'إعادة إرسال الرمز بعد ($resendSeconds ثانية)'
                                  : 'Resend code in (${resendSeconds}s)',
                              style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
                            )
                          else
                            TextButton.icon(
                              onPressed: isResending || isVerifying
                                  ? null
                                  : () async {
                                      setDlgState(() => isResending = true);
                                      try {
                                        final supabase = Supabase.instance.client;
                                        await supabase.auth.signInWithPassword(
                                          email: email,
                                          password: password,
                                        );
                                        await supabase.auth.reauthenticate();
                                        await supabase.auth.signOut();

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
                                      } catch (e) {
                                        try {
                                          await Supabase.instance.client.auth.signOut();
                                        } catch (_) {}
                                        if (ctx.mounted) {
                                          setDlgState(() => isResending = false);
                                        }
                                        if (mounted) {
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(
                                              content: Text(
                                                isRtl
                                                    ? 'يرجى الانتظار دقيقة قبل طلب رمز جديد.'
                                                    : 'Please wait a minute before requesting a new code.',
                                              ),
                                              backgroundColor: AppColors.error,
                                            ),
                                          );
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
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isVerifying
                      ? null
                      : () async {
                          countdownTimer?.cancel();
                          if (ctx.mounted && Navigator.of(ctx).canPop()) {
                            Navigator.of(ctx).pop();
                          }
                          // Clean up the unverified account so the user can enter a corrected email freely
                          try {
                            await Supabase.instance.client.rpc(
                              'cleanup_unverified_or_deleted_auth_user',
                              params: {'target_email': email},
                            );
                          } catch (_) {}
                          if (mounted) {
                            _emailFocusNode.requestFocus();
                          }
                        },
                  child: Text(isRtl ? 'تعديل البريد' : 'Change Email'),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 11),
                  ),
                  onPressed: isVerifying
                      ? null
                      : () => verifyCode(otpCode, ctx, setDlgState),
                  icon: isVerifying
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Icon(Icons.verified_user_rounded, size: 18),
                  label: Text(
                    isRtl ? 'تأكيد وتفعيل الحساب' : 'Verify & Activate',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
    countdownTimer?.cancel();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = context.isRtl;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final screenWidth = MediaQuery.of(context).size.width;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          isRtl ? 'إنشاء حساب جديد' : 'Create New Account',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        color: isDark ? const Color(0xFF050B14) : const Color(0xFFF1F5F9),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: EdgeInsets.symmetric(
                horizontal: screenWidth < 390 ? 16.0 : AppSizes.p24,
                vertical: AppSizes.p20,
              ),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 460),
                child: Container(
                  padding: EdgeInsets.all(screenWidth < 390 ? 22.0 : 32.0),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0F172A) : Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(
                      color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.05),
                        blurRadius: 20,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: AutofillGroup(
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Text(
                            isRtl ? 'أهلاً بك في منصة مقاييس' : 'Join Magaiees Platform',
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                  fontWeight: FontWeight.w900,
                                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                                ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 6),
                          Text(
                            isRtl
                                ? 'أنشئ حسابك خلال ثوانٍ للتسوق وطلب التسعيرات ومتابعة طلباتك'
                                : 'Create your account in seconds to shop, request quotes, and track orders',
                            style: TextStyle(
                              fontSize: 13,
                              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 24),

                          TextFormField(
                            controller: _fullNameController,
                            textInputAction: TextInputAction.next,
                            autofillHints: const [AutofillHints.name],
                            decoration: InputDecoration(
                              labelText: isRtl ? 'الاسم الكامل' : 'Full Name',
                              prefixIcon: const Icon(Icons.person_outline_rounded),
                            ),
                            validator: (value) => DataValidator.validateName(
                              value,
                              fieldName: isRtl ? 'الاسم الكامل' : 'Full Name',
                              isRtl: isRtl,
                            ),
                          ),
                          const SizedBox(height: AppSizes.p16),

                          TextFormField(
                            controller: _emailController,
                            focusNode: _emailFocusNode,
                            keyboardType: TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            autofillHints: const [AutofillHints.username, AutofillHints.email],
                            decoration: InputDecoration(
                              labelText: isRtl ? 'البريد الإلكتروني' : 'Email Address',
                              prefixIcon: const Icon(Icons.email_outlined),
                            ),
                            validator: (value) => DataValidator.validateEmail(value, isRtl: isRtl),
                          ),
                          const SizedBox(height: AppSizes.p16),

                          TextFormField(
                            controller: _phoneController,
                            keyboardType: TextInputType.phone,
                            textInputAction: TextInputAction.next,
                            autofillHints: const [AutofillHints.telephoneNumber],
                            decoration: InputDecoration(
                              labelText: isRtl ? 'رقم الهاتف' : 'Phone Number',
                              hintText: isRtl ? 'مثال: +968 9900 0000' : 'e.g. +968 9900 0000',
                              prefixIcon: const Icon(Icons.phone_outlined),
                            ),
                            validator: (value) => DataValidator.validatePhone(value, isRtl: isRtl),
                          ),
                          const SizedBox(height: AppSizes.p16),

                          TextFormField(
                            controller: _passwordController,
                            obscureText: !_isPasswordVisible,
                            textInputAction: TextInputAction.next,
                            autofillHints: const [AutofillHints.newPassword],
                            decoration: InputDecoration(
                              labelText: isRtl ? 'كلمة المرور' : 'Password',
                              prefixIcon: const Icon(Icons.lock_outline_rounded),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _isPasswordVisible ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                ),
                                onPressed: () {
                                  setState(() {
                                    _isPasswordVisible = !_isPasswordVisible;
                                  });
                                },
                              ),
                            ),
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return isRtl ? 'مطلوب' : 'Required';
                              }
                              if (value.length < 6) {
                                return isRtl ? 'يجب أن تكون 6 أحرف على الأقل' : 'Minimum 6 characters';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: AppSizes.p16),

                          TextFormField(
                            controller: _confirmPasswordController,
                            obscureText: !_isConfirmPasswordVisible,
                            textInputAction: TextInputAction.done,
                            autofillHints: const [AutofillHints.newPassword],
                            onFieldSubmitted: (_) => _submit(),
                            decoration: InputDecoration(
                              labelText: isRtl ? 'تأكيد كلمة المرور' : 'Confirm Password',
                              prefixIcon: const Icon(Icons.lock_reset_rounded),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _isConfirmPasswordVisible ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                ),
                                onPressed: () {
                                  setState(() {
                                    _isConfirmPasswordVisible = !_isConfirmPasswordVisible;
                                  });
                                },
                              ),
                            ),
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return isRtl ? 'مطلوب' : 'Required';
                              }
                              if (value != _passwordController.text) {
                                return isRtl ? 'كلمتا المرور غير متطابقتين' : 'Passwords do not match';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 28),

                          FilledButton(
                            onPressed: _isLoading ? null : _submit,
                            style: FilledButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 15),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            child: _isLoading
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                  )
                                : Text(
                                    isRtl ? 'إنشاء حساب' : 'Create Account',
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                          ),
                          const SizedBox(height: AppSizes.p12),

                          TextButton(
                            onPressed: _isLoading
                                ? null
                                : () {
                                    if (context.canPop()) {
                                      context.pop();
                                    } else {
                                      context.go('/login');
                                    }
                                  },
                            child: Text(
                              isRtl ? 'لديك حساب بالفعل؟ تسجيل الدخول' : 'Already have an account? Sign In',
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
      ),
    );
  }
}
