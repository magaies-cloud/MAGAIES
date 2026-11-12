import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/constants/app_sizes.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';
import 'package:go_router/go_router.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();

  final _fullNameController = TextEditingController();
  final _emailController = TextEditingController();
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
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    final fullName = _fullNameController.text.trim();
    final email = _emailController.text.trim();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text;

    await ref.read(authNotifierProvider.notifier).register(
          name: fullName,
          nameAr: fullName,
          email: email,
          phone: phone,
          password: password,
        );

    if (!mounted) return;
    setState(() => _isLoading = false);

    final authState = ref.read(authNotifierProvider);
    if (authState.hasError) {
      final isRtl = context.isRtl;
      final errStr = authState.error.toString();
      String msg = isRtl
          ? 'تعذر إنشاء الحساب، يرجى التحقق من البيانات والمحاولة مرة أخرى.'
          : 'Could not create account. Please verify your details and try again.';
      if (errStr.contains('already registered') || errStr.contains('already exists') || errStr.contains('duplicate')) {
        msg = isRtl
            ? 'هذا البريد الإلكتروني مسجل مسبقاً، يرجى تسجيل الدخول بدلاً من ذلك.'
            : 'This email is already registered. Please sign in instead.';
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    if (authState.valueOrNull != null && !authState.valueOrNull!.role.isGuest) {
      context.go('/buyer/home');
    }
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
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
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
    );
  }
}
