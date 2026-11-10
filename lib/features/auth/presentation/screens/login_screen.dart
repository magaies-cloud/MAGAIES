import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/constants/app_sizes.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/locale/presentation/widgets/language_switcher.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:go_router/go_router.dart';
import 'package:local_auth/local_auth.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

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
      final savedEmail = await _secureStorage.read(key: 'saved_email');
      final hasSaved = savedEmail != null && savedEmail.isNotEmpty;

      List<BiometricType> available = [];
      try {
        available = await _localAuth.getAvailableBiometrics();
      } catch (_) {}

      final hasFace = available.contains(BiometricType.face);

      if (mounted) {
        setState(() {
          _canCheckBiometrics = canCheck || isSupported;
          _hasFaceId = hasFace;
          if (hasSaved && _emailController.text.isEmpty) {
            // Keep email input empty on screen open; only fill when biometric button is tapped.
          }
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
          backgroundColor: const Color(0xFF1E40AF),
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

    // Listen for errors and successes
    ref.listen(authNotifierProvider, (prev, next) {
      if (next.hasError && !next.isLoading) {
        final errorStr = next.error.toString();
        String displayMsgAr = 'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً';
        String displayMsgEn = 'An unexpected error occurred, please try again';
        
        if (errorStr.contains('Invalid login credentials') || errorStr.contains('invalid_credentials')) {
          displayMsgAr = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
          displayMsgEn = 'Invalid email or password';
        }

        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Column(
              children: [
                const Icon(Icons.error_outline, color: Colors.redAccent, size: 56),
                const SizedBox(height: 16),
                Text(isRtl ? 'عذراً' : 'Error', style: const TextStyle(fontWeight: FontWeight.bold)),
              ],
            ),
            content: Text(
              isRtl ? displayMsgAr : displayMsgEn,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 16),
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
      } else if (next.hasValue && next.value != null && !next.isLoading) {
        if (_emailController.text.isNotEmpty && _passwordController.text.isNotEmpty) {
          _secureStorage.write(key: 'saved_email', value: _emailController.text.trim()).catchError((_) {});
          _secureStorage.write(key: 'saved_password', value: _passwordController.text).catchError((_) {});
        }
      }
    });

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'منصة مقاييس' : 'Magaiees Platform'),
        actions: const [LanguageSwitcher()],
      ),
      body: Container(
        width: double.infinity,
        decoration: const BoxDecoration(
          color: Color(0xFFF1F5F9), // Subtle clean background
        ),
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppSizes.p24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 450), // Responsive Max Width for PCs
              child: Container(
                padding: const EdgeInsets.all(40),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
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
                      // Stunning Logo
                      Center(
                        child: Image.asset(
                          'assets/images/logo_final.png',
                          height: 120, // Clear and crisp height
                          errorBuilder: (context, error, stackTrace) => const SizedBox(height: 120),
                          frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
                            if (wasSynchronouslyLoaded || frame != null) return child;
                            return const SizedBox(height: 120);
                          },
                        ),
                      ),
                      AppSizes.gapH24,

                      // Title
                      Text(
                        isRtl ? 'تسجيل الدخول' : 'Welcome Back',
                        style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                              fontWeight: FontWeight.w900,
                              color: const Color(0xFF0F172A),
                            ),
                        textAlign: TextAlign.center,
                      ),
                      AppSizes.gapH8,
                      Text(
                        isRtl
                            ? 'أدخل بيانات الاعتماد للوصول إلى منصة مقاييس'
                            : 'Enter your credentials to access the platform',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              color: const Color(0xFF64748B),
                              fontSize: 14,
                            ),
                        textAlign: TextAlign.center,
                      ),
                      AppSizes.gapH32,

                      // Email
                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        textInputAction: TextInputAction.next,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'البريد الإلكتروني' : 'Email Address',
                          prefixIcon: const Icon(Icons.email_outlined, color: Color(0xFF94A3B8)),
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFF1E40AF), width: 2)),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return isRtl ? 'الرجاء إدخال البريد الإلكتروني' : 'Please enter your email';
                          }
                          return null;
                        },
                      ),
                      AppSizes.gapH16,

                      // Password
                      TextFormField(
                        controller: _passwordController,
                        obscureText: true,
                        textInputAction: TextInputAction.done,
                        onFieldSubmitted: (_) => _handleLogin(),
                        decoration: InputDecoration(
                          labelText: isRtl ? 'كلمة المرور' : 'Password',
                          prefixIcon: const Icon(Icons.lock_outlined, color: Color(0xFF94A3B8)),
                          filled: true,
                          fillColor: const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFF1E40AF), width: 2)),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return isRtl ? 'الرجاء إدخال كلمة المرور' : 'Please enter your password';
                          }
                          return null;
                        },
                      ),
                      AppSizes.gapH32,

                      // Login Button
                      if (_canCheckBiometrics)
                        Row(
                          children: [
                            Expanded(
                              child: FilledButton(
                                onPressed: isLoading ? null : _handleLogin,
                                style: FilledButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(vertical: 16),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  backgroundColor: const Color(0xFF1E40AF),
                                ),
                                child: isLoading
                                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                    : Text(isRtl ? 'تسجيل الدخول' : 'Login', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            IconButton.filledTonal(
                              onPressed: _handleBiometricLogin,
                              icon: Icon(_hasFaceId ? Icons.face_rounded : Icons.fingerprint_rounded, size: 28),
                              tooltip: isRtl
                                  ? (_hasFaceId ? 'تسجيل الدخول عبر Face ID' : 'تسجيل الدخول بالبصمة')
                                  : (_hasFaceId ? 'Login with Face ID' : 'Login with Biometrics'),
                              style: IconButton.styleFrom(
                                padding: const EdgeInsets.all(12),
                                backgroundColor: const Color(0xFFEFF6FF),
                                foregroundColor: const Color(0xFF1E40AF),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ],
                        )
                      else
                        FilledButton(
                          onPressed: isLoading ? null : _handleLogin,
                          style: FilledButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            backgroundColor: const Color(0xFF1E40AF),
                          ),
                          child: isLoading
                              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : Text(isRtl ? 'تسجيل الدخول' : 'Login', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        ),
                      AppSizes.gapH24,

                      // Guest Login Button
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          side: const BorderSide(color: Color(0xFF1E40AF)),
                          foregroundColor: const Color(0xFF1E40AF),
                        ),
                        onPressed: () {
                          ref.read(authNotifierProvider.notifier).loginAsGuest();
                        },
                        icon: const Icon(Icons.explore_outlined),
                        label: Text(isRtl ? 'تصفح المنصة كزائر' : 'Browse Platform as Guest', style: const TextStyle(fontWeight: FontWeight.bold)),
                      ),
                      AppSizes.gapH12,

                      // Register link
                      TextButton(
                        style: TextButton.styleFrom(foregroundColor: const Color(0xFF64748B)),
                        onPressed: () {
                          context.push('/register');
                        },
                        child: Text(isRtl
                            ? 'ليس لديك حساب؟ إنشاء حساب جديد'
                            : "Don't have an account? Register"),
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
