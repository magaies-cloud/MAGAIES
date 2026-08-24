import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/constants/app_sizes.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/locale/presentation/widgets/language_switcher.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:go_router/go_router.dart';
import 'package:local_auth/local_auth.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

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

  @override
  void initState() {
    super.initState();
    _checkBiometrics();
  }

  Future<void> _checkBiometrics() async {
    try {
      final canCheck = await _localAuth.canCheckBiometrics;
      final isSupported = await _localAuth.isDeviceSupported();
      final hasSavedCreds = await _secureStorage.read(key: 'saved_email') != null;
      
      if (mounted) {
        setState(() {
          _canCheckBiometrics = (canCheck || isSupported) && hasSavedCreds;
        });
      }
    } catch (_) {}
  }

  Future<void> _handleBiometricLogin() async {
    try {
      final authenticated = await _localAuth.authenticate(
        localizedReason: context.isRtl ? 'الرجاء المصادقة لتسجيل الدخول السريع' : 'Please authenticate to login',
      );

      if (authenticated) {
        final savedEmail = await _secureStorage.read(key: 'saved_email');
        final savedPassword = await _secureStorage.read(key: 'saved_password');
        if (savedEmail != null && savedPassword != null) {
          _emailController.text = savedEmail;
          _passwordController.text = savedPassword;
          _handleLogin();
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Biometric Error: $e')));
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
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl
                ? 'فشل تسجيل الدخول: ${next.error}'
                : 'Login failed: ${next.error}'),
          ),
        );
      } else if (next.hasValue && next.value != null && !next.isLoading) {
        // Successfully logged in, save credentials securely for biometric login
        if (_emailController.text.isNotEmpty && _passwordController.text.isNotEmpty) {
          _secureStorage.write(key: 'saved_email', value: _emailController.text.trim());
          _secureStorage.write(key: 'saved_password', value: _passwordController.text);
        }
      }
    });

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'منصة مقاييس' : 'Magaiees Platform'),
        actions: const [LanguageSwitcher()],
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppSizes.p24),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Image.asset(
                    'assets/images/logo_final.png',
                    height: 100,
                    errorBuilder: (context, error, stackTrace) => const SizedBox(height: 100),
                    frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
                      if (wasSynchronouslyLoaded || frame != null) return child;
                      return const SizedBox(height: 100);
                    },
                  ),
                  AppSizes.gapH24,

                  // Title
                  Text(
                    isRtl ? 'منصة مقاييس' : 'Magaiees Marketplace',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                    textAlign: TextAlign.center,
                  ),
                  AppSizes.gapH8,
                  Text(
                    isRtl
                        ? 'سوق مواد البناء والمعدات الرقمي'
                        : 'Digital Marketplace for Building Materials & Equipment',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          color: Theme.of(context).colorScheme.onSurfaceVariant,
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
                      prefixIcon: const Icon(Icons.email_outlined),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return isRtl
                            ? 'الرجاء إدخال البريد الإلكتروني'
                            : 'Please enter your email';
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
                      prefixIcon: const Icon(Icons.lock_outlined),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return isRtl
                            ? 'الرجاء إدخال كلمة المرور'
                            : 'Please enter your password';
                      }
                      return null;
                    },
                  ),
                  AppSizes.gapH24,

                  // Login Button
                  if (_canCheckBiometrics)
                    Row(
                      children: [
                        Expanded(
                          child: FilledButton(
                            onPressed: isLoading ? null : _handleLogin,
                            child: isLoading
                                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                : Text(isRtl ? 'تسجيل الدخول' : 'Login'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        IconButton.filledTonal(
                          onPressed: _handleBiometricLogin,
                          icon: const Icon(Icons.fingerprint, size: 28),
                          tooltip: isRtl ? 'تسجيل الدخول بالبصمة / Face ID' : 'Login with Biometrics',
                          style: IconButton.styleFrom(
                            padding: const EdgeInsets.all(12),
                          ),
                        ),
                      ],
                    )
                  else
                    FilledButton(
                      onPressed: isLoading ? null : _handleLogin,
                      child: isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : Text(isRtl ? 'تسجيل الدخول' : 'Login'),
                    ),
                  AppSizes.gapH12,

                  // Quick Guest Access
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: AppSizes.p16),
                      side: BorderSide(color: Theme.of(context).primaryColor),
                    ),
                    onPressed: () {
                      ref.read(authNotifierProvider.notifier).loginAsGuest();
                    },
                    icon: const Icon(Icons.explore_outlined),
                    label: Text(isRtl ? 'تصفح المنصة كزائر (بدون تسجيل)' : 'Browse Platform as Guest'),
                  ),
                  AppSizes.gapH12,

                  // Register link
                  TextButton(
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
    );
  }
}
