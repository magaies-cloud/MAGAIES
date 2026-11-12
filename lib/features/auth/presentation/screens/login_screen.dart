import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/constants/app_sizes.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
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

  @override
  void initState() {
    super.initState();
    _checkBiometrics();
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
    bool isSending = false;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Text(
            isRtl ? 'استعادة كلمة المرور' : 'Reset Password',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                isRtl
                    ? 'أدخل بريدك الإلكتروني المسجل وسنرسل إليك رابط إعادة تعيين كلمة المرور.'
                    : 'Enter your registered email address and we will send you a password reset link.',
                style: const TextStyle(fontSize: 13.5, height: 1.4),
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
            ],
          ),
          actions: [
            TextButton(
              onPressed: isSending ? null : () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              onPressed: isSending
                  ? null
                  : () async {
                      final email = resetEmailCtrl.text.trim();
                      if (email.isEmpty || !email.contains('@')) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              isRtl ? 'يرجى إدخال بريد إلكتروني صحيح' : 'Please enter a valid email address',
                            ),
                          ),
                        );
                        return;
                      }
                      setDlgState(() => isSending = true);
                      try {
                        await Supabase.instance.client.auth.resetPasswordForEmail(email);
                        if (ctx.mounted) Navigator.pop(ctx);
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                isRtl
                                    ? 'تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني بنجاح'
                                    : 'Password reset link sent to your email',
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
                                    ? 'تعذر إرسال الرابط حالياً، يرجى التحقق من البريد والمحاولة لاحقاً'
                                    : 'Could not send reset link, please verify your email and try again',
                              ),
                            ),
                          );
                        }
                      }
                    },
              child: isSending
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(isRtl ? 'إرسال الرابط' : 'Send Link'),
            ),
          ],
        ),
      ),
    );
    resetEmailCtrl.dispose();
  }

  @override
  void dispose() {
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
    );
  }
}
