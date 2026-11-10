import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
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
  
  final _nameEnController = TextEditingController();
  final _nameArController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isPasswordVisible = false;
  bool _isConfirmPasswordVisible = false;
  bool _isLoading = false;

  @override
  void dispose() {
    _nameEnController.dispose();
    _nameArController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isLoading = true);

    final nameEn = _nameEnController.text.trim();
    final nameAr = _nameArController.text.trim();
    final email = _emailController.text.trim();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text;

    try {
      // Assuming register accepts these named parameters or similar
      await ref.read(authNotifierProvider.notifier).register(
        name: nameEn,
        nameAr: nameAr,
        email: email,
        phone: phone,
        password: password,
      );
      
      if (mounted) {
        context.go('/buyer/home');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString())),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    // Assuming context.isRtl is a boolean extension
    final isRtl = context.isRtl; 

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'إنشاء حساب جديد' : 'Create New Account'),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSizes.p16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                TextFormField(
                  controller: _nameEnController,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'الاسم الكامل (إنجليزي)' : 'Full Name (English)',
                    prefixIcon: const Icon(Icons.person_outline),
                  ),
                  validator: (value) => DataValidator.validateName(
                    value,
                    fieldName: isRtl ? 'الاسم بالإنجليزية' : 'Full Name (English)',
                    isRtl: isRtl,
                  ),
                ),
                const SizedBox(height: AppSizes.p16),
                
                TextFormField(
                  controller: _nameArController,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'الاسم الكامل (عربي)' : 'Full Name (Arabic)',
                    prefixIcon: const Icon(Icons.person),
                  ),
                  validator: (value) => DataValidator.validateName(
                    value,
                    fieldName: isRtl ? 'الاسم بالعربية' : 'Full Name (Arabic)',
                    isRtl: isRtl,
                  ),
                ),
                const SizedBox(height: AppSizes.p16),

                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
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
                  decoration: InputDecoration(
                    labelText: isRtl ? 'كلمة المرور' : 'Password',
                    prefixIcon: const Icon(Icons.lock_outline),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _isPasswordVisible ? Icons.visibility : Icons.visibility_off,
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
                      return isRtl ? 'يجب أن تكون 6 أحرف على الأقل' : 'Min 6 characters';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: AppSizes.p16),

                TextFormField(
                  controller: _confirmPasswordController,
                  obscureText: !_isConfirmPasswordVisible,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'تأكيد كلمة المرور' : 'Confirm Password',
                    prefixIcon: const Icon(Icons.lock),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _isConfirmPasswordVisible ? Icons.visibility : Icons.visibility_off,
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
                const SizedBox(height: AppSizes.p32),

                FilledButton(
                  onPressed: _isLoading ? null : _submit,
                  child: _isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : Text(isRtl ? 'إنشاء حساب' : 'Register'),
                ),
                const SizedBox(height: AppSizes.p16),

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
                  child: Text(isRtl ? 'لديك حساب بالفعل؟ تسجيل الدخول' : 'Already have an account? Login'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
