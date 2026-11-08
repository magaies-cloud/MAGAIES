import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';

/// Screen allowing the Store Owner (Merchant) to manage their store staff and team
/// with granular role-based access control (RBAC).
class MerchantTeamScreen extends ConsumerStatefulWidget {
  const MerchantTeamScreen({super.key});

  @override
  ConsumerState<MerchantTeamScreen> createState() => _MerchantTeamScreenState();
}

class _MerchantTeamScreenState extends ConsumerState<MerchantTeamScreen> {
  bool _isLoading = true;
  String? _storeId;
  String? _storeName;
  String? _storeNameAr;
  List<Map<String, dynamic>> _staffList = [];
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadStoreAndStaff();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadStoreAndStaff() async {
    setState(() => _isLoading = true);
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) {
      if (mounted) setState(() => _isLoading = false);
      return;
    }

    try {
      // 1. Resolve store info
      final storeRes = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('stores')
            .select('id, name, name_ar')
            .eq('owner_id', user.id)
            .limit(1)
            .maybeSingle(),
        fallback: () => null,
      );

      if (storeRes != null) {
        _storeId = storeRes['id']?.toString();
        _storeName = storeRes['name']?.toString();
        _storeNameAr = storeRes['name_ar']?.toString();
      } else if (user.storeId != null) {
        _storeId = user.storeId;
        _storeName = user.storeName;
        _storeNameAr = user.storeNameAr;
      }

      if (_storeId == null) {
        if (mounted) setState(() => _isLoading = false);
        return;
      }

      // 2. Query employees belonging to this store
      final usersRes = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('users')
            .select()
            .eq('role', 'employee'),
        fallback: () => <dynamic>[],
      );

      final List<Map<String, dynamic>> matchedStaff = [];
      for (final raw in usersRes) {
        if (raw is Map<String, dynamic>) {
          final perms = raw['permissions'];
          Map<String, dynamic> permsMap = {};
          if (perms is Map) {
            permsMap = Map<String, dynamic>.from(perms);
          }

          final staffStoreId = permsMap['store_id']?.toString() ?? raw['store_id']?.toString();
          final isStaff = permsMap['is_store_staff'] == true || staffStoreId == _storeId;

          if (isStaff && (staffStoreId == _storeId || raw['store_name'] == _storeName)) {
            matchedStaff.add(raw);
          }
        }
      }

      // Sort: active first, then by name
      matchedStaff.sort((a, b) {
        final aSusp = a['is_suspended'] == true;
        final bSusp = b['is_suspended'] == true;
        if (aSusp != bSusp) return aSusp ? 1 : -1;
        final aName = (a['name_ar'] ?? a['name'] ?? '').toString();
        final bName = (b['name_ar'] ?? b['name'] ?? '').toString();
        return aName.compareTo(bName);
      });

      if (mounted) {
        setState(() {
          _staffList = matchedStaff;
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Error loading merchant team: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<Map<String, dynamic>> get _filteredStaff {
    if (_searchQuery.trim().isEmpty) return _staffList;
    final query = _searchQuery.trim().toLowerCase();
    return _staffList.where((s) {
      final name = (s['name'] ?? '').toString().toLowerCase();
      final nameAr = (s['name_ar'] ?? '').toString().toLowerCase();
      final phone = (s['phone'] ?? '').toString().toLowerCase();
      final email = (s['email'] ?? '').toString().toLowerCase();
      final perms = s['permissions'] is Map ? s['permissions'] as Map : {};
      final job = (perms['job_title'] ?? '').toString().toLowerCase();
      return name.contains(query) ||
          nameAr.contains(query) ||
          phone.contains(query) ||
          email.contains(query) ||
          job.contains(query);
    }).toList();
  }

  Future<void> _toggleSuspension(Map<String, dynamic> staff) async {
    final staffId = staff['id']?.toString();
    if (staffId == null) return;
    final currentSuspended = staff['is_suspended'] == true;
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    try {
      await Supabase.instance.client
          .from('users')
          .update({'is_suspended': !currentSuspended})
          .eq('id', staffId);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              !currentSuspended
                  ? (isRtl ? 'تم تجميد حساب الموظف بنجاح' : 'Staff account suspended')
                  : (isRtl ? 'تم تنشيط حساب الموظف بنجاح' : 'Staff account activated'),
            ),
            backgroundColor: !currentSuspended ? Colors.orange.shade800 : Colors.green.shade700,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
      _loadStoreAndStaff();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'حدث خطأ أثناء تعديل الحالة: $e' : 'Error updating status: $e'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _confirmDeleteStaff(Map<String, dynamic> staff) async {
    final staffId = staff['id']?.toString();
    if (staffId == null) return;
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final staffName = (staff['name_ar'] ?? staff['name'] ?? '').toString();

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.warning_amber_rounded, color: Colors.red),
            const SizedBox(width: 8),
            Text(isRtl ? 'تأكيد حذف الموظف' : 'Confirm Staff Removal'),
          ],
        ),
        content: Text(
          isRtl
              ? 'هل أنت متأكد من رغبتك في حذف الموظف "$staffName" من فريق عمل المتجر؟ لن يتمكن من تسجيل الدخول أو استلام الطلبات بعد الحذف.'
              : 'Are you sure you want to remove "$staffName" from your store team? They will no longer have access to orders or chats.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(isRtl ? 'حذف نهائي' : 'Delete'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        await Supabase.instance.client.from('users').delete().eq('id', staffId);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(isRtl ? 'تم حذف الموظف بنجاح' : 'Staff deleted successfully'),
              backgroundColor: Colors.red.shade700,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
        _loadStoreAndStaff();
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(isRtl ? 'تعذر الحذف: $e' : 'Failed to delete: $e'),
              backgroundColor: Colors.red,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    }
  }

  void _showAddStaffDialog() {
    if (_storeId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تعذر تحديد المتجر الحالي')),
      );
      return;
    }

    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final nameCtrl = TextEditingController();
    final nameArCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final passCtrl = TextEditingController();
    final jobTitleCtrl = TextEditingController(text: isRtl ? 'مسؤول مبيعات واستلام طلبات' : 'Sales & Orders Manager');

    bool canManageOrders = true;
    bool canManageProducts = false;
    bool canChatCustomers = true;
    bool isSubmitting = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dlgCtx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.merchantAccent.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.person_add_alt_1_rounded, color: AppColors.merchantAccent),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    isRtl ? 'إضافة موظف جديد للمتجر' : 'Add Store Staff Member',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                ),
              ],
            ),
            content: SizedBox(
              width: 480,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl
                          ? 'أدخل بيانات الموظف والصلاحيات المسموح له بها لإدارة المتجر معك:'
                          : 'Enter staff credentials and assign permitted store capabilities:',
                      style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                    ),
                    const SizedBox(height: 16),

                    // Job Title
                    TextFormField(
                      controller: jobTitleCtrl,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'المسمى الوظيفي' : 'Job Title / Role',
                        hintText: isRtl ? 'مثال: مسؤول مبيعات، أمين مخزن' : 'e.g. Sales Manager, Warehouse Keeper',
                        prefixIcon: const Icon(Icons.badge_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Arabic Name
                    TextFormField(
                      controller: nameArCtrl,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'الاسم باللغة العربية *' : 'Name (Arabic) *',
                        prefixIcon: const Icon(Icons.person_outline),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // English Name
                    TextFormField(
                      controller: nameCtrl,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'الاسم باللغة الإنجليزية' : 'Name (English)',
                        prefixIcon: const Icon(Icons.person_outline),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Phone
                    TextFormField(
                      controller: phoneCtrl,
                      keyboardType: TextInputType.phone,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'رقم الهاتف *' : 'Phone Number *',
                        hintText: '0912345678',
                        prefixIcon: const Icon(Icons.phone_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Email
                    TextFormField(
                      controller: emailCtrl,
                      keyboardType: TextInputType.emailAddress,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'البريد الإلكتروني للدخول *' : 'Login Email *',
                        hintText: 'staff@example.com',
                        prefixIcon: const Icon(Icons.email_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Password
                    TextFormField(
                      controller: passCtrl,
                      obscureText: true,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'كلمة المرور / الرمز السري *' : 'Password / PIN *',
                        prefixIcon: const Icon(Icons.lock_outline),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Permissions Section
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50.withOpacity(0.5),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.blue.shade200),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.security, size: 18, color: Color(0xFF1E40AF)),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'صلاحيات الموظف في المتجر:' : 'Staff Store Permissions:',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E40AF)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),

                          // 1. Orders
                          SwitchListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(
                              isRtl ? '📦 استلام وإدارة الطلبات والردار' : '📦 Receive & Manage Orders',
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                            subtitle: Text(
                              isRtl ? 'استعراض ردار الطلبات، قبولها وتحديد رسوم التجهيز والترحيل' : 'View order radar, accept orders, and set dispatch fees',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                            ),
                            value: canManageOrders,
                            onChanged: (val) => setDlgState(() => canManageOrders = val),
                          ),
                          const Divider(height: 1),

                          // 2. Products / Inventory
                          SwitchListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(
                              isRtl ? '🏷️ إدارة وتعديل المنتجات والمخزون' : '🏷️ Inventory & Products',
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                            subtitle: Text(
                              isRtl ? 'إضافة منتجات، تعديل الأسعار والكميات المتاحة' : 'Add products, update stock and prices',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                            ),
                            value: canManageProducts,
                            onChanged: (val) => setDlgState(() => canManageProducts = val),
                          ),
                          const Divider(height: 1),

                          // 3. Customer Chat
                          SwitchListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(
                              isRtl ? '💬 محادثة وخدمة العملاء' : '💬 Customer Chat & Support',
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                            subtitle: Text(
                              isRtl ? 'الرد على استفسارات المشترين باسم وشعار المتجر' : 'Respond to buyer inquiries using Store branding',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                            ),
                            value: canChatCustomers,
                            onChanged: (val) => setDlgState(() => canChatCustomers = val),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Security Notice
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.amber.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.amber.shade300),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.lock_person, size: 16, color: Colors.amber.shade900),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              isRtl
                                  ? '🔒 حماية الخصوصية: لا يمكن للموظف بأي حال الإطلاع على أرباح المتجر أو رصيد المحفظة أو إعدادات الحساب البنكي لمالك المتجر.'
                                  : '🔒 Financial Privacy: Staff can never view store wallet, revenue, or modify owner banking settings.',
                              style: TextStyle(fontSize: 11, color: Colors.amber.shade900),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            actions: [
              TextButton(
                onPressed: isSubmitting ? null : () => Navigator.pop(dlgCtx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel'),
              ),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.merchantAccent,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                ),
                onPressed: isSubmitting
                    ? null
                    : () async {
                        final nameAr = nameArCtrl.text.trim();
                        final name = nameCtrl.text.trim();
                        final phone = phoneCtrl.text.trim();
                        final email = emailCtrl.text.trim();
                        final pass = passCtrl.text.trim();
                        final job = jobTitleCtrl.text.trim();

                        if ((nameAr.isEmpty && name.isEmpty) || phone.isEmpty || email.isEmpty || pass.isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'يرجى ملء جميع الحقول المطلوبة' : 'Please fill all required fields'),
                              backgroundColor: Colors.red,
                            ),
                          );
                          return;
                        }

                        setDlgState(() => isSubmitting = true);
                        try {
                          final user = ref.read(authNotifierProvider).valueOrNull;
                          final finalName = name.isNotEmpty ? name : nameAr;
                          final finalNameAr = nameAr.isNotEmpty ? nameAr : name;

                          final permissions = {
                            'is_store_staff': true,
                            'store_id': _storeId,
                            'store_name': _storeName,
                            'store_name_ar': _storeNameAr,
                            'job_title': job,
                            'can_manage_orders': canManageOrders,
                            'can_manage_products': canManageProducts,
                            'can_chat_customers': canChatCustomers,
                            'temporary_pin': pass,
                            'created_by': user?.id,
                            'created_at': DateTime.now().toIso8601String(),
                          };

                          await Supabase.instance.client.from('users').insert({
                            'name': finalName,
                            'name_ar': finalNameAr,
                            'email': email,
                            'phone': phone,
                            'role': 'employee',
                            'is_suspended': false,
                            'is_verified': true,
                            'store_name': _storeName,
                            'store_name_ar': _storeNameAr,
                            'permissions': permissions,
                          });

                          if (mounted) {
                            Navigator.pop(dlgCtx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? '✅ تم إضافة الموظف بنجاح إلى المتجر' : 'Staff member added successfully'),
                                backgroundColor: const Color(0xFF059669),
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                            _loadStoreAndStaff();

                            // Show credentials sheet
                            _showCreatedCredentialsSheet(
                              name: finalNameAr,
                              email: email,
                              password: pass,
                              jobTitle: job,
                              isRtl: isRtl,
                            );
                          }
                        } catch (e) {
                          setDlgState(() => isSubmitting = false);
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'فشل إضافة الموظف: $e' : 'Failed to add staff: $e'),
                                backgroundColor: Colors.red,
                              ),
                            );
                          }
                        }
                      },
                icon: isSubmitting
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.check, size: 18),
                label: Text(isRtl ? 'حفظ وإضافة' : 'Save & Add'),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showCreatedCredentialsSheet({
    required String name,
    required String email,
    required String password,
    required String jobTitle,
    required bool isRtl,
  }) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 28),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    isRtl ? 'بيانات دخول الموظف الجديد' : 'New Staff Credentials',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              isRtl
                  ? 'يمكنك الآن نسخ بيانات الدخول وإرسالها للموظف ليسجل دخوله إلى تطبيق ماغاييس:'
                  : 'You can copy these credentials and send them to your staff member:',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('👤 ${isRtl ? 'الموظف' : 'Staff'}: $name ($jobTitle)'),
                  const SizedBox(height: 6),
                  Text('🏪 ${isRtl ? 'المتجر' : 'Store'}: ${_storeNameAr ?? _storeName ?? ''}'),
                  const SizedBox(height: 6),
                  Text('📧 ${isRtl ? 'البريد الإلكتروني' : 'Email'}: $email'),
                  const SizedBox(height: 6),
                  Text('🔑 ${isRtl ? 'كلمة المرور' : 'Password'}: $password'),
                ],
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.merchantAccent,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () {
                  final text = 'مرحباً $name، تم إنشاء حسابك كموظف في متجر "${_storeNameAr ?? _storeName}":\n'
                      'البريد الإلكتروني: $email\n'
                      'كلمة المرور: $password\n'
                      'المسمى الوظيفي: $jobTitle';
                  Clipboard.setData(ClipboardData(text: text));
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(isRtl ? 'تم نسخ البيانات إلى الحافظة' : 'Copied to clipboard'),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                },
                icon: const Icon(Icons.copy, size: 20),
                label: Text(isRtl ? 'نسخ بيانات الدخول' : 'Copy Credentials'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditPermissionsDialog(Map<String, dynamic> staff) {
    final staffId = staff['id']?.toString();
    if (staffId == null) return;
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final staffName = (staff['name_ar'] ?? staff['name'] ?? '').toString();

    final perms = staff['permissions'] is Map ? Map<String, dynamic>.from(staff['permissions']) : <String, dynamic>{};
    final jobTitleCtrl = TextEditingController(text: perms['job_title']?.toString() ?? '');
    bool canManageOrders = perms['can_manage_orders'] == true;
    bool canManageProducts = perms['can_manage_products'] == true;
    bool canChatCustomers = perms['can_chat_customers'] == true;
    bool isSaving = false;

    showDialog(
      context: context,
      builder: (dlgCtx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                const Icon(Icons.tune_rounded, color: AppColors.merchantAccent),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    isRtl ? 'تعديل صلاحيات: $staffName' : 'Edit Permissions: $staffName',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                ),
              ],
            ),
            content: SizedBox(
              width: 440,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    TextFormField(
                      controller: jobTitleCtrl,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'المسمى الوظيفي' : 'Job Title',
                        prefixIcon: const Icon(Icons.badge_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 16),

                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        isRtl ? '📦 استلام وإدارة الطلبات والردار' : '📦 Manage Orders',
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                      value: canManageOrders,
                      onChanged: (val) => setDlgState(() => canManageOrders = val),
                    ),
                    const Divider(height: 1),

                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        isRtl ? '🏷️ إدارة المنتجات والمخزون' : '🏷️ Manage Products',
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                      value: canManageProducts,
                      onChanged: (val) => setDlgState(() => canManageProducts = val),
                    ),
                    const Divider(height: 1),

                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        isRtl ? '💬 محادثة وتواصل مع العملاء' : '💬 Customer Chat',
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                      value: canChatCustomers,
                      onChanged: (val) => setDlgState(() => canChatCustomers = val),
                    ),
                  ],
                ),
              ),
            ),
            actions: [
              TextButton(
                onPressed: isSaving ? null : () => Navigator.pop(dlgCtx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel'),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.merchantAccent,
                  foregroundColor: Colors.white,
                ),
                onPressed: isSaving
                    ? null
                    : () async {
                        setDlgState(() => isSaving = true);
                        try {
                          perms['job_title'] = jobTitleCtrl.text.trim();
                          perms['can_manage_orders'] = canManageOrders;
                          perms['can_manage_products'] = canManageProducts;
                          perms['can_chat_customers'] = canChatCustomers;
                          perms['updated_at'] = DateTime.now().toIso8601String();

                          await Supabase.instance.client
                              .from('users')
                              .update({'permissions': perms})
                              .eq('id', staffId);

                          if (mounted) {
                            Navigator.pop(dlgCtx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'تم تحديث الصلاحيات بنجاح' : 'Permissions updated successfully'),
                                backgroundColor: const Color(0xFF059669),
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                            _loadStoreAndStaff();
                          }
                        } catch (e) {
                          setDlgState(() => isSaving = false);
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'فشل التحديث: $e' : 'Failed to update: $e'),
                                backgroundColor: Colors.red,
                              ),
                            );
                          }
                        }
                      },
                child: Text(isRtl ? 'حفظ التعديلات' : 'Save Changes'),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSummaryCard(bool isRtl) {
    final total = _staffList.length;
    final active = _staffList.where((s) => s['is_suspended'] != true).length;
    final suspended = total - active;

    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.merchantAccent.withOpacity(0.12),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.groups_rounded, color: AppColors.merchantAccent, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _storeNameAr ?? _storeName ?? (isRtl ? 'متجري' : 'My Store'),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                      Text(
                        isRtl ? 'إدارة الموظفين وتوزيع الصلاحيات' : 'Store Staff & Access Delegation',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatPill(isRtl ? 'إجمالي الفريق' : 'Total Staff', total.toString(), Colors.blue),
                _buildStatPill(isRtl ? 'موظف نشط' : 'Active Staff', active.toString(), Colors.green),
                _buildStatPill(isRtl ? 'معلّق' : 'Suspended', suspended.toString(), Colors.orange),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline, size: 16, color: Color(0xFF1E40AF)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      isRtl
                          ? '💡 للمالك فقط: تحتفظ بكامل الصلاحيات، والموظفون لا يرون الأرباح أو الحساب البنكي.'
                          : '💡 Owner Only: You retain 100% full privileges. Staff cannot view wallet or revenue.',
                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF1E3A8A)),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: FilledButton.tonalIcon(
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF0F766E).withOpacity(0.12),
                  foregroundColor: const Color(0xFF0F766E),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () => context.push(RouteNames.merchantFinancialReport),
                icon: const Icon(Icons.table_chart_rounded, size: 20),
                label: Text(
                  isRtl ? 'عرض كشف مبيعات وإنتاجية الفريق' : 'View Team Sales & Productivity Statement',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatPill(String label, String value, Color color) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(fontSize: 11, color: Colors.grey),
        ),
      ],
    );
  }

  Widget _buildStaffCard(Map<String, dynamic> staff, bool isRtl) {
    final isSuspended = staff['is_suspended'] == true;
    final nameAr = staff['name_ar']?.toString() ?? '';
    final name = staff['name']?.toString() ?? '';
    final displayName = nameAr.isNotEmpty ? nameAr : name;
    final phone = staff['phone']?.toString() ?? '';
    final email = staff['email']?.toString() ?? '';

    final perms = staff['permissions'] is Map ? staff['permissions'] as Map : {};
    final jobTitle = perms['job_title']?.toString() ?? (isRtl ? 'موظف متجر' : 'Store Staff');
    final canOrders = perms['can_manage_orders'] == true;
    final canProducts = perms['can_manage_products'] == true;
    final canChat = perms['can_chat_customers'] == true;

    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Avatar, Name, Job title & Suspension status
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 24,
                  backgroundColor: isSuspended ? Colors.grey.shade300 : AppColors.merchantAccent.withOpacity(0.15),
                  child: Text(
                    displayName.isNotEmpty ? displayName[0].toUpperCase() : 'S',
                    style: TextStyle(
                      color: isSuspended ? Colors.grey.shade700 : AppColors.merchantAccent,
                      fontWeight: FontWeight.bold,
                      fontSize: 18,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              displayName,
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                                decoration: isSuspended ? TextDecoration.lineThrough : null,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: isSuspended ? Colors.red.shade50 : Colors.green.shade50,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: isSuspended ? Colors.red.shade300 : Colors.green.shade300,
                              ),
                            ),
                            child: Text(
                              isSuspended ? (isRtl ? 'معلّق' : 'Suspended') : (isRtl ? 'نشط' : 'Active'),
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isSuspended ? Colors.red.shade800 : Colors.green.shade800,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          jobTitle,
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.blue.shade900),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Contact info
            Row(
              children: [
                const Icon(Icons.phone, size: 14, color: Colors.grey),
                const SizedBox(width: 6),
                Text(phone, style: TextStyle(fontSize: 12, color: Colors.grey.shade800)),
                const SizedBox(width: 16),
                const Icon(Icons.email, size: 14, color: Colors.grey),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    email,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade800),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
            const Divider(height: 20),

            // Permission Chips
            Text(
              isRtl ? 'الصلاحيات الممنوحة:' : 'Assigned Permissions:',
              style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.black87),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                _buildPermissionChip(
                  icon: Icons.receipt_long,
                  label: isRtl ? 'إدارة الطلبات' : 'Orders',
                  isEnabled: canOrders,
                ),
                _buildPermissionChip(
                  icon: Icons.inventory_2,
                  label: isRtl ? 'إدارة المخزون' : 'Inventory',
                  isEnabled: canProducts,
                ),
                _buildPermissionChip(
                  icon: Icons.chat,
                  label: isRtl ? 'محادثة العملاء' : 'Customer Chat',
                  isEnabled: canChat,
                ),
              ],
            ),
            const Divider(height: 20),

            // Actions row
            Row(
              children: [
                // Edit Permissions
                OutlinedButton.icon(
                  onPressed: () => _showEditPermissionsDialog(staff),
                  icon: const Icon(Icons.edit, size: 16),
                  label: Text(isRtl ? 'تعديل الصلاحيات' : 'Edit Permissions'),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                ),
                const Spacer(),

                // Toggle Suspension
                IconButton(
                  tooltip: isSuspended ? (isRtl ? 'تنشيط الحساب' : 'Activate') : (isRtl ? 'تجميد الحساب' : 'Suspend'),
                  icon: Icon(
                    isSuspended ? Icons.play_circle_fill_rounded : Icons.pause_circle_filled_rounded,
                    color: isSuspended ? Colors.green : Colors.orange,
                  ),
                  onPressed: () => _toggleSuspension(staff),
                ),

                // Delete Staff
                IconButton(
                  tooltip: isRtl ? 'حذف الموظف' : 'Remove Staff',
                  icon: const Icon(Icons.delete_outline, color: Colors.red),
                  onPressed: () => _confirmDeleteStaff(staff),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPermissionChip({
    required IconData icon,
    required String label,
    required bool isEnabled,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: isEnabled ? Colors.blue.shade50 : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isEnabled ? Colors.blue.shade300 : Colors.grey.shade300,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 14,
            color: isEnabled ? Colors.blue.shade800 : Colors.grey.shade500,
          ),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: isEnabled ? FontWeight.bold : FontWeight.normal,
              color: isEnabled ? Colors.blue.shade900 : Colors.grey.shade600,
            ),
          ),
          const SizedBox(width: 4),
          Icon(
            isEnabled ? Icons.check_circle : Icons.cancel,
            size: 12,
            color: isEnabled ? Colors.green : Colors.grey.shade400,
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(bool isRtl) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.merchantAccent.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.people_outline_rounded,
                size: 64,
                color: AppColors.merchantAccent,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              isRtl ? 'لا يوجد موظفون مضافون حالياً' : 'No Staff Members Added Yet',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            const SizedBox(height: 8),
            Text(
              isRtl
                  ? 'يمكنك إضافة موظفين لمتجرك لمساعدتك في استلام الطلبات وإدارة المخزون والتواصل مع العملاء وفق صلاحيات محددة دون الإطلاع على الأرباح.'
                  : 'Add employees to help you fulfill orders, manage inventory, and chat with customers with dedicated roles.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.merchantAccent,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: _showAddStaffDialog,
              icon: const Icon(Icons.person_add),
              label: Text(isRtl ? '+ إضافة موظف جديد' : '+ Add First Staff Member'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final staffList = _filteredStaff;

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'فريق العمل وإدارة الموظفين' : 'Store Staff & Team'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 1,
        actions: [
          IconButton(
            tooltip: isRtl ? 'تحديث' : 'Refresh',
            icon: const Icon(Icons.refresh),
            onPressed: _loadStoreAndStaff,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.merchantAccent,
        foregroundColor: Colors.white,
        onPressed: _showAddStaffDialog,
        icon: const Icon(Icons.person_add_alt_1),
        label: Text(isRtl ? 'إضافة موظف' : 'Add Staff'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadStoreAndStaff,
              child: ListView(
                padding: const EdgeInsets.all(16.0),
                children: [
                  _buildSummaryCard(isRtl),

                  // Search field
                  if (_staffList.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: TextField(
                        controller: _searchController,
                        onChanged: (val) => setState(() => _searchQuery = val),
                        decoration: InputDecoration(
                          hintText: isRtl ? 'بحث بالاسم، الهاتف، أو المسمى الوظيفي...' : 'Search by name, phone, or title...',
                          prefixIcon: const Icon(Icons.search),
                          suffixIcon: _searchQuery.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear),
                                  onPressed: () {
                                    _searchController.clear();
                                    setState(() => _searchQuery = '');
                                  },
                                )
                              : null,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          filled: true,
                          fillColor: Colors.white,
                        ),
                      ),
                    ),

                  if (_staffList.isEmpty)
                    _buildEmptyState(isRtl)
                  else if (staffList.isEmpty)
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.all(32.0),
                        child: Text(
                          isRtl ? 'لا توجد نتائج مطابقة لبحثك' : 'No staff matched your search',
                          style: TextStyle(color: Colors.grey.shade600),
                        ),
                      ),
                    )
                  else
                    ...staffList.map((s) => _buildStaffCard(s, isRtl)),

                  const SizedBox(height: 80), // Padding for FAB
                ],
              ),
            ),
    );
  }
}
