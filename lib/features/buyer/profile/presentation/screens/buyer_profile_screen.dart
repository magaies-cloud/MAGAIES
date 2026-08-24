import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';
import 'package:magaiees_marketplace/core/theme/theme_provider.dart';
import 'package:magaiees_marketplace/core/enums/app_mode.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:go_router/go_router.dart';

// Provider to mock the pending status of a merchant upgrade request
final merchantUpgradePendingProvider = StateProvider<bool>((ref) => false);

class BuyerProfileScreen extends ConsumerWidget {
  const BuyerProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authNotifierProvider).valueOrNull;
    final hasStore = ref.watch(hasStoreProvider);

    final isUpgradePending = ref.watch(merchantUpgradePendingProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(context.l10n.profile),
        centerTitle: true,
      ),
      body: ListView(
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
                      context.isRtl 
                          ? 'قم بتسجيل الدخول للاستفادة من كافة ميزات المنصة وتتبع طلباتك، أو الترقية كتاجر.'
                          : 'Log in to access all platform features, track orders, or upgrade to a merchant account.',
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
            // Merchant Access Section (Only for registered buyers)
            Text(
              context.isRtl ? 'حساب التاجر' : 'Merchant Account',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            
            if (hasStore)
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
              )
            else if (isUpgradePending)
              Card(
                color: Colors.amber.shade50,
                child: ListTile(
                  leading: const Icon(Icons.hourglass_empty, color: Colors.amber),
                  title: Text(context.isRtl ? 'طلب الترقية قيد المراجعة' : 'Upgrade Request Pending'),
                  subtitle: Text(context.isRtl ? 'يتم الآن مراجعة مستندات التوثيق الخاصة بك' : 'Your verification documents are under review'),
                ),
              )
            else
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
          ],
          
          const SizedBox(height: 24),
          
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
                    value: true,
                    onChanged: (val) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(context.isRtl ? 'تم تحديث إعدادات البصمة' : 'Biometric settings updated'),
                          duration: const Duration(seconds: 1),
                        ),
                      );
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
                    onTap: () => _showDeleteAccountDialog(context),
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
                if (hasStore) ...[
                  ListTile(
                    leading: const Icon(Icons.admin_panel_settings, color: Colors.purple),
                    title: Text(context.isRtl ? 'إدارة المشتركين' : 'Subscriber Management'),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                    onTap: () {
                      context.push('/admin/subscribers');
                    },
                  ),
                  const Divider(height: 1),
                ],
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
                  subtitle: const Text('+249 91 234 5678 / +966 50 123 4567'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(context.isRtl ? 'جاري فتح محادثة الدعم عبر واتساب...' : 'Opening WhatsApp Support...'),
                      ),
                    );
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.headset_mic_outlined, color: Colors.indigo),
                  title: Text(context.isRtl ? 'الاتصال بالرقم الموحد' : 'Customer Service Hotline'),
                  subtitle: const Text('800 123 4567'),
                  trailing: const Icon(Icons.call, size: 18, color: Colors.indigo),
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(context.isRtl ? 'الاتصال بخدمة العملاء: 8001234567' : 'Calling hotline: 8001234567'),
                      ),
                    );
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
                  subtitle: const Text('Version 1.0.0 (Build 100)'),
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
    );
  }

  // --- Dialogs & Sheets ---

  void _showMerchantUpgradeDialog(BuildContext context, WidgetRef ref) {
    final isRtl = context.isRtl;
    String selectedDocType = isRtl ? 'جواز السفر' : 'Passport';
    final docTypes = isRtl 
        ? ['جواز السفر', 'بطاقة الهوية', 'رخصة القيادة']
        : ['Passport', 'National ID', 'Driver\'s License'];

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
                      side: const BorderSide(color: Colors.blue),
                    ),
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(isRtl ? 'جاري فتح معرض الصور...' : 'Opening gallery...')),
                      );
                    },
                    icon: const Icon(Icons.upload_file),
                    label: Text(isRtl ? 'رفع صورة المستند' : 'Upload Document Image'),
                  ),
                  const SizedBox(height: 24),
                  FilledButton(
                    style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                    onPressed: () {
                      Navigator.pop(ctx);
                      ref.read(merchantUpgradePendingProvider.notifier).state = true;
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(isRtl 
                              ? 'تم إرسال طلب الترقية بنجاح. سيتم مراجعته من قبل الإدارة.'
                              : 'Upgrade request submitted successfully. It will be reviewed by admin.'),
                          backgroundColor: Colors.green,
                        ),
                      );
                    },
                    child: Text(
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
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isRtl ? 'تغيير كلمة المرور' : 'Change Password'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: isRtl ? 'كلمة المرور الحالية' : 'Current Password',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: isRtl ? 'كلمة المرور الجديدة' : 'New Password',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: isRtl ? 'تأكيد كلمة المرور الجديدة' : 'Confirm New Password',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(isRtl ? 'تم تحديث كلمة المرور بنجاح!' : 'Password updated successfully!')),
              );
            },
            child: Text(isRtl ? 'حفظ' : 'Save'),
          ),
        ],
      ),
    );
  }

  void _showDeleteAccountDialog(BuildContext context) {
    final isRtl = context.isRtl;
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
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
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'تراجع' : 'Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(isRtl ? 'تم تقديم طلب حذف الحساب وسيتم تنفيذه خلال 24 ساعة.' : 'Account deletion requested.'),
                  backgroundColor: Colors.red,
                ),
              );
            },
            child: Text(isRtl ? 'نعم، احذف الحساب' : 'Yes, Delete'),
          ),
        ],
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
              'Version 1.0.0 (Build 100)',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
            ),
            const SizedBox(height: 16),
            Text(
              isRtl
                  ? 'المنصة الرقمية الأولى المتخصصة في تجارة وتوريد مواد البناء والمعدات الإنشائية، تهدف إلى تسهيل عمليات الشراء والربط المباشر بين المصانع والتجار والمقاولين.'
                  : 'The premier digital marketplace for construction materials and equipment, streamlining procurement between manufacturers, merchants, and contractors.',
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