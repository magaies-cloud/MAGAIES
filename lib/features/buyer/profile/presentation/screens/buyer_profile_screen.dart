import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/core/theme/theme_provider.dart';
import 'package:magaiees_marketplace/core/enums/app_mode.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/core/utils/image_picker_helper.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

// Provider to manage and persist biometric login preference
final biometricEnabledProvider = StateNotifierProvider<BiometricNotifier, bool>((ref) => BiometricNotifier());

class BiometricNotifier extends StateNotifier<bool> {
  BiometricNotifier() : super(true) {
    _load();
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    state = prefs.getBool('biometric_enabled') ?? true;
  }

  Future<void> toggle(bool value) async {
    state = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('biometric_enabled', value);
  }
}

// Provider to track the pending status of a merchant upgrade request
final merchantUpgradePendingProvider = StateProvider<bool>((ref) => false);

class BuyerProfileScreen extends ConsumerWidget {
  const BuyerProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authNotifierProvider).valueOrNull;
    final hasStore = ref.watch(hasStoreProvider);
    final isUpgradePending = ref.watch(merchantUpgradePendingProvider) ||
        (user?.permissions['merchant_upgrade_pending'] == true);
    final isUpgradeEnabled = ref.watch(merchantUpgradeSettingProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(context.l10n.profile),
        centerTitle: true,
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(merchantUpgradeSettingProvider);
          ref.invalidate(globalSettingsProvider);
          try {
            await ref.read(authNotifierProvider.notifier).refreshSession();
          } catch (_) {}
        },
        child: ListView(
        padding: const EdgeInsets.all(16.0),
        children: [
          // User Info Card
          Card(
            elevation: 0,
            color: Theme.of(context).colorScheme.surfaceContainerHighest,
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 30,
                    backgroundColor: Theme.of(context).colorScheme.primary,
                    child: const Icon(Icons.person, size: 30, color: Colors.white),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              context.isRtl ? (user?.nameAr ?? 'ضيف') : (user?.name ?? 'Guest'),
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                            ),
                            if (hasStore) ...[
                              const SizedBox(width: 6),
                              const Icon(Icons.verified, color: Colors.blue, size: 20),
                            ]
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          user?.email ?? '',
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
          
          if (user?.role.isGuest ?? true)
            // Guest Prompt
            Card(
              color: Theme.of(context).colorScheme.primaryContainer.withOpacity(0.4),
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    Icon(Icons.person_outline, size: 48, color: Theme.of(context).primaryColor),
                    const SizedBox(height: 12),
                    Text(
                      isUpgradeEnabled
                          ? (context.isRtl 
                              ? 'قم بتسجيل الدخول للاستفادة من كافة ميزات المنصة وتتبع طلباتك، أو الترقية كتاجر.'
                              : 'Log in to access all platform features, track orders, or upgrade to a merchant account.')
                          : (context.isRtl 
                              ? 'قم بتسجيل الدخول للاستفادة من كافة ميزات المنصة وتتبع طلباتك.'
                              : 'Log in to access all platform features and track orders.'),
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        ref.read(authNotifierProvider.notifier).logout(); // Logs out guest and redirects to login
                      },
                      child: Text(context.isRtl ? 'تسجيل الدخول / إنشاء حساب' : 'Log In / Register'),
                    ),
                  ],
                ),
              ),
            )
          else ...[
            // ── Admin & Staff Operations (For Admins and Authorized Employees) ──
            if (user?.role.isAdmin == true || (user?.role == UserRole.employee && user?.permissions['can_manage_subscribers'] == true)) ...[
              Text(
                context.isRtl ? 'لوحة الإدارة والتحكم' : 'Admin & Operations Control',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Card(
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(color: Colors.purple.shade100, width: 1.2),
                ),
                child: Column(
                  children: [
                    if (user?.role.isAdmin == true) ...[
                      ListTile(
                        leading: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.purple.shade50,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.admin_panel_settings, color: Colors.purple),
                        ),
                        title: Text(
                          context.isRtl ? 'لوحة تحكم المدير العام' : 'Admin Control Panel',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          context.isRtl ? 'إدارة النظام والأسعار والمبيعات والمالية' : 'Manage system, pricing, sales & finance',
                        ),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                        onTap: () => context.go(RouteNames.adminDashboard),
                      ),
                      const Divider(height: 1),
                    ],
                    ListTile(
                      leading: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.people_alt_rounded, color: Color(0xFF2563EB)),
                      ),
                      title: Text(
                        context.isRtl ? 'إدارة المشتركين وقاعدة العملاء' : 'Subscribers & Client Base',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      subtitle: Text(
                        context.isRtl
                            ? 'إدارة حسابات التجار والعملاء، الصلاحيات ونقاط الولاء'
                            : 'Manage merchants, buyers, roles & loyalty tiers',
                      ),
                      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                      onTap: () => context.push(RouteNames.adminSubscribers),
                    ),
                    if (user?.role == UserRole.employee) ...[
                      const Divider(height: 1),
                      ListTile(
                        leading: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.teal.shade50,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.badge_outlined, color: Colors.teal),
                        ),
                        title: Text(
                          context.isRtl ? 'بوابة الموظف' : 'Employee Portal',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          context.isRtl ? 'مهام الموظف والعمليات المصرحة' : 'Assigned tasks and operations',
                        ),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                        onTap: () => context.go(RouteNames.employeeDashboard),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 24),
            ],

            // ── Merchant Access Section ──
            if (hasStore || user?.role.isMerchant == true) ...[
              Text(
                context.isRtl ? 'حساب التاجر' : 'Merchant Account',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Card(
                child: ListTile(
                  leading: const Icon(Icons.store, color: Colors.blue),
                  title: Row(
                    children: [
                      Text(context.isRtl ? 'الدخول للوحة تحكم التاجر' : 'Enter Merchant Dashboard'),
                      const SizedBox(width: 6),
                      const Icon(Icons.verified, color: Colors.blue, size: 16),
                    ],
                  ),
                  subtitle: Text(
                    context.isRtl ? (user?.storeNameAr ?? '') : (user?.storeName ?? ''),
                  ),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    ref.read(modeNotifierProvider.notifier).switchTo(AppMode.merchant);
                    context.go('/merchant/dashboard');
                  },
                ),
              ),
              const SizedBox(height: 24),
            ] else if (isUpgradePending) ...[
              Text(
                context.isRtl ? 'حساب التاجر' : 'Merchant Account',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Card(
                color: Colors.amber.shade50,
                child: ListTile(
                  leading: const Icon(Icons.hourglass_empty, color: Colors.amber),
                  title: Text(context.isRtl ? 'طلب الترقية قيد المراجعة' : 'Upgrade Request Pending'),
                  subtitle: Text(context.isRtl ? 'يتم الآن مراجعة مستندات التوثيق الخاصة بك' : 'Your verification documents are under review'),
                ),
              ),
              const SizedBox(height: 24),
            ] else if (isUpgradeEnabled) ...[
              Text(
                context.isRtl ? 'حساب التاجر' : 'Merchant Account',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Card(
                child: ListTile(
                  leading: const Icon(Icons.storefront, color: Colors.grey),
                  title: Text(context.isRtl ? 'ترقية حسابك إلى تاجر' : 'Upgrade to Merchant'),
                  subtitle: Text(context.isRtl ? 'ابدأ بالبيع على منصة مقاييس اليوم' : 'Start selling on Magaiees Marketplace today'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    _showMerchantUpgradeDialog(context, ref);
                  },
                ),
              ),
              const SizedBox(height: 24),
            ],
          ],
          
          // Digital Broker (only for registered users)
          if (!(user?.role.isGuest ?? true)) ...[
            Text(
              context.isRtl ? 'الوسيط الرقمي' : 'Digital Broker',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Card(
              child: ListTile(
                leading: const Icon(Icons.handshake, color: Colors.green),
                title: Text(context.isRtl ? 'طلب تسعيرة كميات' : 'Request Bulk Quote'),
                subtitle: Text(context.isRtl ? 'للمشاريع والمقاولات الكبيرة' : 'For large projects & contractors'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () {
                  context.push('/buyer/digital-broker');
                },
              ),
            ),

            const SizedBox(height: 24),
          ],
          
          // Settings & Preferences (available to everyone including guests)
          Text(
            context.isRtl ? 'الإعدادات والتفضيلات' : 'Settings & Preferences',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                SwitchListTile(
                  secondary: Icon(
                    ref.watch(isDarkModeProvider) ? Icons.dark_mode : Icons.light_mode,
                    color: ref.watch(isDarkModeProvider) ? Colors.amber : Colors.orange,
                  ),
                  title: Text(context.isRtl ? 'الوضع الداكن' : 'Dark Mode'),
                  subtitle: Text(
                    context.isRtl
                        ? (ref.watch(isDarkModeProvider) ? 'مفعل' : 'معطل')
                        : (ref.watch(isDarkModeProvider) ? 'Enabled' : 'Disabled'),
                  ),
                  value: ref.watch(isDarkModeProvider),
                  onChanged: (val) {
                    ref.read(themeModeProvider.notifier).setThemeMode(
                          val ? ThemeMode.dark : ThemeMode.light,
                        );
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.language),
                  title: Text(context.isRtl ? 'اللغة (العربية)' : 'Language (English)'),
                  trailing: const Icon(Icons.swap_horiz, size: 20),
                  onTap: () {
                    ref.read(localeNotifierProvider.notifier).toggleLocale();
                  },
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          if (!(user?.role.isGuest ?? true)) ...[
            Text(
              context.isRtl ? 'عنوان التوصيل' : 'Delivery Address',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Card(
              child: ListTile(
                leading: const Icon(Icons.location_on, color: Colors.blue),
                title: Text(context.isRtl ? 'تعديل عنوان التوصيل' : 'Edit Delivery Address'),
                subtitle: Text(context.isRtl ? 'العنوان الافتراضي لطلباتك' : 'Default address for your orders'),
                trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                onTap: () => _showEditAddressDialog(context),
              ),
            ),
            const SizedBox(height: 24),
          ],

          // Security & Account (only for registered users, not guests)
          if (!(user?.role.isGuest ?? true)) ...[
            Text(
              context.isRtl ? 'الأمان والحساب' : 'Security & Account',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Card(
              child: Column(
                children: [
                  ListTile(
                    leading: const Icon(Icons.lock_outline, color: Colors.blue),
                    title: Text(context.isRtl ? 'تغيير كلمة المرور' : 'Change Password'),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                    onTap: () => _showChangePasswordDialog(context),
                  ),
                  const Divider(height: 1),
                  SwitchListTile(
                    secondary: const Icon(Icons.fingerprint, color: Colors.teal),
                    title: Text(context.isRtl ? 'تسجيل الدخول بالبصمة / Face ID' : 'Biometric / Face ID Login'),
                    subtitle: Text(context.isRtl ? 'دخول سريع وآمن للتطبيق' : 'Quick and secure app access'),
                    value: ref.watch(biometricEnabledProvider),
                    onChanged: (val) async {
                      await ref.read(biometricEnabledProvider.notifier).toggle(val);
                      if (context.mounted) {
                        context.showSnackBar(context.isRtl 
                            ? (val ? 'تم تفعيل تسجيل الدخول بالبصمة' : 'تم تعطيل تسجيل الدخول بالبصمة')
                            : (val ? 'Biometric login enabled' : 'Biometric login disabled'));
                      }
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.person_remove_outlined, color: Colors.red),
                    title: Text(
                      context.isRtl ? 'إلغاء أو حذف الحساب' : 'Delete / Deactivate Account',
                      style: const TextStyle(color: Colors.red),
                    ),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.red),
                    onTap: () => _showDeleteAccountDialog(context, ref),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),
          ],

          // Support & Communication
          Text(
            context.isRtl ? 'الدعم والتواصل السريع' : 'Support & Direct Contact',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.help_center_outlined, color: Colors.blue),
                  title: Text(context.isRtl ? 'مركز المساعدة ودليل الاستخدام' : 'Help & Knowledge Center'),
                  subtitle: Text(
                    context.isRtl
                        ? 'الحسابات، إدارة الإعلانات، الدفع، والأمان'
                        : 'Accounts, Ads, Payments & Security',
                  ),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    context.push('/buyer/profile/help');
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.chat_bubble_outline, color: Colors.green),
                  title: Text(context.isRtl ? 'محادثة الدعم الفني (واتساب)' : 'WhatsApp Technical Support'),
                  subtitle: const Text('0096899051925'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () async {
                    final uri = Uri.parse('https://wa.me/96899051925');
                    try {
                      final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
                      if (!launched && context.mounted) {
                        context.showSnackBar(context.isRtl ? 'جاري فتح محادثة الدعم عبر واتساب...' : 'Opening WhatsApp Support...');
                      }
                    } catch (_) {
                      if (context.mounted) {
                        context.showSnackBar(context.isRtl ? 'جاري فتح محادثة الدعم عبر واتساب...' : 'Opening WhatsApp Support...');
                      }
                    }
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.headset_mic_outlined, color: Colors.indigo),
                  title: Text(context.isRtl ? 'الاتصال بالرقم الموحد' : 'Customer Service Hotline'),
                  subtitle: const Text('0096899051925'),
                  trailing: const Icon(Icons.call, size: 18, color: Colors.indigo),
                  onTap: () async {
                    final uri = Uri.parse('tel:0096899051925');
                    try {
                      final launched = await launchUrl(uri);
                      if (!launched && context.mounted) {
                        context.showSnackBar(context.isRtl ? 'الاتصال بخدمة العملاء: 0096899051925' : 'Calling hotline: 0096899051925');
                      }
                    } catch (_) {
                      if (context.mounted) {
                        context.showSnackBar(context.isRtl ? 'الاتصال بخدمة العملاء: 0096899051925' : 'Calling hotline: 0096899051925');
                      }
                    }
                  },
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Terms, Policies & About
          Text(
            context.isRtl ? 'الشروط ومعلومات المنصة' : 'Terms & About Platform',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.description_outlined),
                  title: Text(context.isRtl ? 'الشروط والأحكام' : 'Terms & Conditions'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () => _showTermsDialog(context),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.policy_outlined),
                  title: Text(context.isRtl ? 'سياسة الخصوصية وإرجاع مواد البناء' : 'Privacy & Return Policy'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () => _showReturnPolicyDialog(context),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.info_outline, color: Colors.blue),
                  title: Text(context.isRtl ? 'عن منصة مقاييس' : 'About Magaiees Platform'),
                  subtitle: const Text('Version 1.0.9 (Build 10)'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () => _showAboutDialog(context),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 32),
          
          // Logout
          FilledButton.icon(
            style: FilledButton.styleFrom(
              backgroundColor: Colors.red.shade100,
              foregroundColor: Colors.red.shade900,
              padding: const EdgeInsets.symmetric(vertical: 14),
            ),
            onPressed: () {
              ref.read(modeNotifierProvider.notifier).resetToBuyer();
              ref.read(authNotifierProvider.notifier).logout();
            },
            icon: const Icon(Icons.logout),
            label: Text(context.l10n.logout, style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
          const SizedBox(height: 24),
        ],
      ),
      ),
    );
  }

  // --- Dialogs & Sheets ---

  void _showEditAddressDialog(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    final arController = TextEditingController(text: prefs.getString('buyer_delivery_address') ?? '');
    final enController = TextEditingController(text: prefs.getString('buyer_delivery_address_en') ?? '');
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    if (!context.mounted) return;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isRtl ? 'تعديل عنوان التوصيل' : 'Edit Delivery Address'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: arController,
                decoration: InputDecoration(
                  labelText: isRtl ? 'العنوان (عربي)' : 'Address (Arabic)',
                  hintText: isRtl ? 'مثال: الخرطوم، جبرة، مربع 15' : 'e.g. Khartoum, Jabra',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: enController,
                decoration: InputDecoration(
                  labelText: isRtl ? 'العنوان (إنجليزي)' : 'Address (English)',
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              await prefs.setString('buyer_delivery_address', arController.text.trim());
              await prefs.setString('buyer_delivery_address_en', enController.text.trim());
              if (ctx.mounted) {
                Navigator.pop(ctx);
                context.showSuccessSnackBar(isRtl ? 'تم تحديث العنوان بنجاح' : 'Address updated successfully');
              }
            },
            child: Text(isRtl ? 'حفظ' : 'Save'),
          ),
        ],
      ),
    );
  }

  void _showMerchantUpgradeDialog(BuildContext context, WidgetRef ref) {
    final isRtl = context.isRtl;
    final storeNameController = TextEditingController();
    String selectedDocType = isRtl ? 'جواز السفر' : 'Passport';
    final docTypes = isRtl 
        ? ['جواز السفر', 'بطاقة الهوية', 'رخصة القيادة']
        : ['Passport', 'National ID', 'Driver\'s License'];
    Uint8List? pickedDocBytes;
    String? pickedDocName;
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom,
                top: 20,
                left: 20,
                right: 20,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.verified, color: Colors.blue),
                      const SizedBox(width: 8),
                      Text(
                        isRtl ? 'طلب ترقية لحساب تاجر' : 'Merchant Upgrade Request',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    isRtl 
                        ? 'يرجى تقديم بيانات متجرك ورفع مستندات التوثيق الرسمية للحصول على شارة "مستخدم موثق".'
                        : 'Please provide your store details and upload official verification documents to get the "Verified" badge.',
                    style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                  ),
                  const SizedBox(height: 24),
                  TextField(
                    controller: storeNameController,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'اسم المتجر / الشركة' : 'Store / Company Name',
                      border: const OutlineInputBorder(),
                      prefixIcon: const Icon(Icons.store),
                    ),
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: selectedDocType,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'نوع المستند' : 'Document Type',
                      border: const OutlineInputBorder(),
                    ),
                    items: docTypes.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                    onChanged: (val) {
                      if (val != null) setState(() => selectedDocType = val);
                    },
                  ),
                  const SizedBox(height: 16),
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      side: BorderSide(color: pickedDocBytes != null ? Colors.green : Colors.blue),
                    ),
                    onPressed: () async {
                      final xFile = await ImagePickerHelper.pickImage(ctx, imageQuality: 80);
                      if (xFile != null) {
                        final bytes = await xFile.readAsBytes();
                        setState(() {
                          pickedDocBytes = bytes;
                          pickedDocName = xFile.name;
                        });
                      }
                    },
                    icon: Icon(
                      pickedDocBytes != null ? Icons.check_circle : Icons.upload_file,
                      color: pickedDocBytes != null ? Colors.green : null,
                    ),
                    label: Text(
                      pickedDocBytes != null
                          ? (isRtl ? 'تم إرفاق المستند بنجاح' : 'Document Attached')
                          : (isRtl ? 'رفع صورة المستند' : 'Upload Document Image'),
                    ),
                  ),
                  const SizedBox(height: 24),
                  FilledButton(
                    style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                    onPressed: isSubmitting
                        ? null
                        : () async {
                            final storeName = storeNameController.text.trim();
                            if (storeName.isEmpty) {
                              context.showErrorSnackBar(
                                isRtl ? 'يرجى إدخال اسم المتجر أو الشركة' : 'Please enter the store or company name',
                              );
                              return;
                            }
                            setState(() => isSubmitting = true);
                            try {
                              final currentUser = ref.read(authNotifierProvider).valueOrNull;
                              if (currentUser != null && currentUser.id.isNotEmpty) {
                                final updatedPerms = Map<String, dynamic>.from(currentUser.permissions);
                                updatedPerms['merchant_upgrade_pending'] = true;
                                updatedPerms['requested_store_name'] = storeName;
                                updatedPerms['verification_doc_type'] = selectedDocType;
                                updatedPerms['upgrade_requested_at'] = DateTime.now().toUtc().toIso8601String();

                                if (pickedDocBytes != null) {
                                  try {
                                    final ext = (pickedDocName ?? 'jpg').split('.').last.toLowerCase();
                                    final path = 'upgrade_docs/${currentUser.id}_${DateTime.now().millisecondsSinceEpoch}.$ext';
                                    await Supabase.instance.client.storage.from('products').uploadBinary(
                                      path,
                                      pickedDocBytes!,
                                      fileOptions: const FileOptions(upsert: true),
                                    );
                                    final publicUrl = Supabase.instance.client.storage.from('products').getPublicUrl(path);
                                    updatedPerms['verification_doc_url'] = publicUrl;
                                  } catch (_) {}
                                }

                                await Supabase.instance.client.from('users').update({
                                  'store_name': storeName,
                                  'store_name_ar': storeName,
                                  'permissions': updatedPerms,
                                }).eq('id', currentUser.id);
                                await ref.read(authNotifierProvider.notifier).refreshSession();
                              }
                            } catch (_) {}
                            if (ctx.mounted) {
                              Navigator.pop(ctx);
                            }
                            ref.read(merchantUpgradePendingProvider.notifier).state = true;
                            if (context.mounted) {
                              context.showSuccessSnackBar(
                                isRtl
                                    ? 'تم إرسال طلب الترقية بنجاح. سيتم مراجعته من قبل الإدارة.'
                                    : 'Upgrade request submitted successfully. It will be reviewed by admin.',
                              );
                            }
                          },
                    child: isSubmitting
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : Text(
                            isRtl ? 'إرسال طلب التوثيق' : 'Submit Verification Request',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            );
          }
        );
      },
    );
  }

  void _showChangePasswordDialog(BuildContext context) {
    final isRtl = context.isRtl;
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();
    bool isLoading = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) => AlertDialog(
          title: Text(isRtl ? 'تغيير كلمة المرور' : 'Change Password'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: newPasswordController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'كلمة المرور الجديدة' : 'New Password',
                    prefixIcon: const Icon(Icons.lock_outline),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: confirmPasswordController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'تأكيد كلمة المرور الجديدة' : 'Confirm New Password',
                    prefixIcon: const Icon(Icons.lock_reset),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            ElevatedButton(
              onPressed: isLoading ? null : () async {
                final newPass = newPasswordController.text.trim();
                final confPass = confirmPasswordController.text.trim();
                if (newPass.length < 6) {
                  context.showErrorSnackBar(isRtl ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' : 'Password must be at least 6 characters');
                  return;
                }
                if (newPass != confPass) {
                  context.showErrorSnackBar(isRtl ? 'كلمتا المرور غير متطابقتين' : 'Passwords do not match');
                  return;
                }
                setDlgState(() => isLoading = true);
                try {
                  await Supabase.instance.client.auth.updateUser(
                    UserAttributes(password: newPass),
                  );
                  if (ctx.mounted) {
                    Navigator.pop(ctx);
                    context.showSuccessSnackBar(isRtl ? 'تم تحديث كلمة المرور بنجاح!' : 'Password updated successfully!');
                  }
                } catch (e) {
                  if (ctx.mounted) {
                    setDlgState(() => isLoading = false);
                    context.showErrorSnackBar(isRtl ? 'فشل التحديث: $e' : 'Failed to update: $e');
                  }
                }
              },
              child: isLoading
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : Text(isRtl ? 'حفظ' : 'Save'),
            ),
          ],
        ),
      ),
    );
  }

  void _showDeleteAccountDialog(BuildContext context, WidgetRef ref) {
    final isRtl = context.isRtl;
    bool isDeleting = false;
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          title: Row(
            children: [
              const Icon(Icons.warning_amber_rounded, color: Colors.red),
              const SizedBox(width: 8),
              Text(isRtl ? 'حذف الحساب نهائياً' : 'Delete Account'),
            ],
          ),
          content: Text(
            isRtl
                ? 'هل أنت متأكد من رغبتك في حذف حسابك؟ سيؤدي هذا الإجراء إلى حذف كافة بياناتك وسجل الطلبات نهائياً ولا يمكن التراجع عنه.'
                : 'Are you sure you want to delete your account? This action will permanently remove all your data, quotes, and order history.',
          ),
          actions: [
            TextButton(
              onPressed: isDeleting ? null : () => Navigator.pop(ctx),
              child: Text(isRtl ? 'تراجع' : 'Cancel'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
              onPressed: isDeleting
                  ? null
                  : () async {
                      setDlgState(() => isDeleting = true);
                      try {
                        final currentUser = ref.read(authNotifierProvider).valueOrNull;
                        if (currentUser != null && currentUser.id.isNotEmpty && !currentUser.role.isGuest) {
                          final updatedPerms = Map<String, dynamic>.from(currentUser.permissions);
                          updatedPerms['deletion_requested'] = true;
                          updatedPerms['deleted_at'] = DateTime.now().toUtc().toIso8601String();
                          await Supabase.instance.client.from('users').update({
                            'is_suspended': true,
                            'permissions': updatedPerms,
                          }).eq('id', currentUser.id);
                        }
                      } catch (_) {}
                      if (ctx.mounted) {
                        Navigator.pop(ctx);
                      }
                      if (context.mounted) {
                        context.showErrorSnackBar(
                          isRtl
                              ? 'تم تقديم طلب حذف الحساب وتسجيل الخروج بنجاح.'
                              : 'Account deletion processed and signed out.',
                        );
                      }
                      ref.read(modeNotifierProvider.notifier).resetToBuyer();
                      await ref.read(authNotifierProvider.notifier).logout();
                    },
              child: isDeleting
                  ? const SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : Text(isRtl ? 'نعم، احذف الحساب' : 'Yes, Delete'),
            ),
          ],
        ),
      ),
    );
  }

  void _showTermsDialog(BuildContext context) {
    final isRtl = context.isRtl;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        expand: false,
        builder: (_, controller) => Padding(
          padding: const EdgeInsets.all(20.0),
          child: ListView(
            controller: controller,
            children: [
              Text(
                isRtl ? 'الشروط والأحكام - منصة مقاييس' : 'Terms & Conditions - Magaiees',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              Text(
                isRtl
                    ? '1. منصة مقاييس هي سوق رقمي يربط بين تجار ومصانع مواد البناء والعملاء والمقاولين.\n\n'
                      '2. الأسعار المعروضة قابلة للتحديث وفق حركة السوق اليومية، وتعتبر ملزمة فور تأكيد الطلب والدفع.\n\n'
                      '3. يتم تحديد رسوم النقل والتفريغ بحسب موقع المشروع ووزن الشحنة المتفق عليه.\n\n'
                      '4. يتحمل المشتري مسؤولية توفير مسار آمن لدخول الشاحنات وتفريغ المواد في الموقع المحدد.'
                    : '1. Magaiees is a digital marketplace connecting construction material merchants, suppliers, and contractors.\n\n'
                      '2. Displayed prices are subject to live market movements and become binding upon order confirmation.\n\n'
                      '3. Transportation and offloading fees are calculated based on job site location and agreed tonnage.\n\n'
                      '4. The buyer is responsible for ensuring clear and safe access for heavy trucks at the designated site.',
                style: const TextStyle(height: 1.6, fontSize: 14),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(isRtl ? 'فهمت وموافق' : 'I Understand & Agree'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showReturnPolicyDialog(BuildContext context) {
    final isRtl = context.isRtl;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        expand: false,
        builder: (_, controller) => Padding(
          padding: const EdgeInsets.all(20.0),
          child: ListView(
            controller: controller,
            children: [
              Text(
                isRtl ? 'سياسة الخصوصية وإرجاع مواد البناء' : 'Privacy & Building Materials Return Policy',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              Text(
                isRtl
                    ? '• الحديد والصلب: يتم الإرجاع خلال 48 ساعة فقط بشرط عدم تعرضه للرطوبة أو الصدأ وبنفس الحالة المسلمة.\n\n'
                      '• الأسمنت والمواد السائبة: لا يقبل إرجاع أكياس الأسمنت بعد فتحها أو تعرضها للهواء أو التلف المائي.\n\n'
                      '• الخرسانة الجاهزة: لا يمكن إلغاؤها أو إرجاعها بعد بدء خلط الشاحنة وتحركها نحو الموقع.\n\n'
                      '• الخصوصية: نلتزم بحماية بياناتك وسجلات مشاريعك وعدم مشاركتها مع أي أطراف ثالثة دون إذنك.'
                    : '• Rebar & Steel: Returns accepted within 48 hours provided the steel is dry, rust-free, and in original delivery state.\n\n'
                      '• Cement & Bulk: Opened bags or water-damaged materials cannot be returned.\n\n'
                      '• Ready-mix Concrete: Orders cannot be cancelled once the transit mixer is batched and en route.\n\n'
                      '• Privacy: We strictly protect your company project data and order history.',
                style: const TextStyle(height: 1.6, fontSize: 14),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(isRtl ? 'إغلاق' : 'Close'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAboutDialog(BuildContext context) {
    final isRtl = context.isRtl;
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset('assets/images/logo_final.png', height: 80),
            const SizedBox(height: 16),
            Text(
              isRtl ? 'منصة مقاييس' : 'Magaiees Marketplace',
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              'Version 1.0.9 (Build 10)',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
            ),
            const SizedBox(height: 16),
            Text(
              isRtl
                  ? 'منصة رقمية متخصصة في تجارة وتوريد مواد البناء والمعدات الإنشائية، تهدف إلى تسهيل عمليات الشراء والربط المباشر بين المصانع والتجار والمقاولين.'
                  : 'A digital marketplace for construction materials and equipment, streamlining procurement between manufacturers, merchants, and contractors.',
              textAlign: TextAlign.center,
              style: const TextStyle(height: 1.5, fontSize: 13),
            ),
          ],
        ),
        actions: [
          Center(
            child: ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(isRtl ? 'حسناً' : 'OK'),
            ),
          ),
        ],
      ),
    );
  }
}