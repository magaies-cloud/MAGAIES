import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import '../../domain/models/subscriber_model.dart';
import '../../application/subscribers_provider.dart';

class SubscriberDetailSheet extends ConsumerWidget {
  final SubscriberModel subscriber;

  const SubscriberDetailSheet({super.key, required this.subscriber});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = context.isRtl;
    final currencyFormatter = NumberFormat.currency(symbol: 'ج.س ', decimalDigits: 2);
    final dateFormatter = DateFormat('yyyy-MM-dd');

    return Container(
      padding: const EdgeInsets.all(24.0),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                CircleAvatar(
                  radius: 40,
                  backgroundColor: _getRoleColor(subscriber.role).withOpacity(0.2),
                  child: Text(
                    (isRtl ? subscriber.nameAr : subscriber.name).substring(0, 1).toUpperCase(),
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: _getRoleColor(subscriber.role),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? subscriber.nameAr : subscriber.name,
                        style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        subscriber.email,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          _buildChip(context, _getRoleName(subscriber.role, isRtl), _getRoleColor(subscriber.role)),
                          const SizedBox(width: 8),
                          if (subscriber.isVerified)
                            const Icon(Icons.verified, color: Colors.blue, size: 20),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
            const Divider(),
            const SizedBox(height: 16),
            _buildInfoRow(context, Icons.phone, isRtl ? 'رقم الهاتف' : 'Phone', subscriber.phone),
            _buildInfoRow(context, Icons.calendar_today, isRtl ? 'تاريخ الانضمام' : 'Join Date', dateFormatter.format(subscriber.joinedAt)),
            _buildInfoRow(
              context,
              Icons.info_outline,
              isRtl ? 'الحالة' : 'Status',
              _getStatusName(subscriber.status, isRtl),
              color: _getStatusColor(subscriber.status),
            ),
            
            if (subscriber.role == UserRole.merchant) ...[
                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 16),
                  Text(
                    isRtl ? 'معلومات المتجر' : 'Store Information',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  if (subscriber.storeName != null || subscriber.storeNameAr != null)
                    _buildInfoRow(context, Icons.store, isRtl ? 'اسم المتجر' : 'Store Name', (isRtl ? subscriber.storeNameAr : subscriber.storeName) ?? ''),
                  _buildInfoRow(context, Icons.shopping_bag, isRtl ? 'إجمالي الطلبات' : 'Total Orders', subscriber.totalOrders.toString()),
                  _buildInfoRow(context, Icons.attach_money, isRtl ? 'إجمالي المبيعات' : 'Total Revenue', currencyFormatter.format(subscriber.totalSpent)),
                  
                  // Gamification Admin View
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.amber.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.amber.shade200),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.stars, color: Colors.amber),
                            const SizedBox(width: 8),
                            Text(isRtl ? 'تدرج التاجر (Gamification)' : 'Merchant Gamification', style: const TextStyle(fontWeight: FontWeight.bold)),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _buildInfoRow(context, Icons.military_tech, isRtl ? 'المستوى الحالي' : 'Current Tier', subscriber.merchantTier.toUpperCase()),
                        _buildInfoRow(context, Icons.control_point_duplicate, isRtl ? 'النقاط المكتسبة' : 'Earned Points', '${subscriber.merchantPoints} نقطة'),
                        const SizedBox(height: 12),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            TextButton.icon(
                              icon: const Icon(Icons.remove, color: Colors.red),
                              label: Text(isRtl ? 'خصم 10' : 'Penalty -10', style: const TextStyle(color: Colors.red)),
                              onPressed: () => ref.read(subscribersProvider.notifier).adjustMerchantPoints(subscriber.id, -10),
                            ),
                            TextButton.icon(
                              icon: const Icon(Icons.add, color: Colors.green),
                              label: Text(isRtl ? 'منح 50' : 'Bonus +50', style: const TextStyle(color: Colors.green)),
                              onPressed: () => ref.read(subscribersProvider.notifier).adjustMerchantPoints(subscriber.id, 50),
                            ),
                          ],
                        )
                      ],
                    ),
                  ),
                ],

              if (subscriber.role == UserRole.employee) ...[
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 16),
                Text(
                  isRtl ? 'الصلاحيات مفصلا' : 'Detailed Permissions',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                _EmployeePermissionsEditor(subscriber: subscriber, isRtl: isRtl),
              ],

            if (subscriber.status == SubscriberStatus.pendingVerification && subscriber.documentType != null) ...[
              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 16),
              _buildInfoRow(context, Icons.description, isRtl ? 'نوع المستند' : 'Document Type', subscriber.documentType!),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: FilledButton.icon(
                      onPressed: () {
                        ref.read(subscribersProvider.notifier).approveMerchant(subscriber.id);
                        Navigator.pop(context);
                      },
                      icon: const Icon(Icons.check),
                      label: Text(isRtl ? 'موافقة وترقية' : 'Approve & Upgrade'),
                      style: FilledButton.styleFrom(backgroundColor: Colors.green),
                    ),
                  ),
                ],
              ),
            ],

            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      ref.read(subscribersProvider.notifier).toggleSuspension(subscriber.id);
                      Navigator.pop(context);
                    },
                    icon: Icon(subscriber.status == SubscriberStatus.suspended ? Icons.play_arrow : Icons.pause),
                    label: Text(subscriber.status == SubscriberStatus.suspended 
                        ? (isRtl ? 'تنشيط الحساب' : 'Activate Account') 
                        : (isRtl ? 'إيقاف الحساب' : 'Suspend Account')),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: subscriber.status == SubscriberStatus.suspended ? Colors.green : Colors.red,
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      ref.read(subscribersProvider.notifier).removeSubscriber(subscriber.id);
                      Navigator.pop(context);
                    },
                    icon: const Icon(Icons.delete),
                    label: Text(isRtl ? 'حذف الحساب' : 'Delete Account'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(BuildContext context, IconData icon, String label, String value, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey[600]),
          const SizedBox(width: 12),
          Text(
            label,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Colors.grey[600],
            ),
          ),
          const Spacer(),
          Text(
            value,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildChip(BuildContext context, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.5)),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }

  Color _getRoleColor(UserRole role) {
    switch (role) {
      case UserRole.admin:
        return Colors.purple;
      case UserRole.merchant:
        return Colors.green;
      case UserRole.buyer:
        return Colors.blue;
      case UserRole.guest:
        return Colors.grey;
      case UserRole.employee:
        return Colors.orange;
    }
  }

  String _getRoleName(UserRole role, bool isRtl) {
    switch (role) {
      case UserRole.admin:
        return isRtl ? 'مدير' : 'Admin';
      case UserRole.merchant:
        return isRtl ? 'تاجر' : 'Merchant';
      case UserRole.buyer:
        return isRtl ? 'مشتري' : 'Buyer';
      case UserRole.guest:
        return isRtl ? 'زائر' : 'Guest';
      case UserRole.employee:
        return isRtl ? 'موظف' : 'Employee';
    }
  }

  String _getStatusName(SubscriberStatus status, bool isRtl) {
    switch (status) {
      case SubscriberStatus.active:
        return isRtl ? 'نشط' : 'Active';
      case SubscriberStatus.suspended:
        return isRtl ? 'موقوف' : 'Suspended';
      case SubscriberStatus.pendingVerification:
        return isRtl ? 'قيد المراجعة' : 'Pending Verification';
    }
  }

  Color _getStatusColor(SubscriberStatus status) {
    switch (status) {
      case SubscriberStatus.active:
        return Colors.green;
      case SubscriberStatus.suspended:
        return Colors.red;
      case SubscriberStatus.pendingVerification:
        return Colors.amber;
    }
  }
}

class _EmployeePermissionsEditor extends ConsumerStatefulWidget {
  final SubscriberModel subscriber;
  final bool isRtl;

  const _EmployeePermissionsEditor({required this.subscriber, required this.isRtl});

  @override
  ConsumerState<_EmployeePermissionsEditor> createState() => _EmployeePermissionsEditorState();
}

class _EmployeePermissionsEditorState extends ConsumerState<_EmployeePermissionsEditor> {
  late Map<String, dynamic> _permissions;

  @override
  void initState() {
    super.initState();
    _permissions = Map<String, dynamic>.from(widget.subscriber.permissions);
  }

  void _updatePermission(String key, bool value) {
    setState(() {
      _permissions[key] = value;
    });
    ref.read(subscribersProvider.notifier).updatePermissions(widget.subscriber.id, _permissions);
  }

  @override
  Widget build(BuildContext context) {
    final permissionsList = [
      {'key': 'can_view_radar', 'titleAr': 'شاشة الرادار ومراقبة الطلبات', 'titleEn': 'Radar & Orders Monitoring'},
      {'key': 'can_manage_products', 'titleAr': 'إدارة الأصناف والأسعار', 'titleEn': 'Products & Pricing Management'},
      {'key': 'can_manage_categories', 'titleAr': 'إدارة الأقسام', 'titleEn': 'Categories Management'},
      {'key': 'can_manage_subscribers', 'titleAr': 'إدارة المشتركين والأدوار', 'titleEn': 'Subscribers & Roles Management'},
      {'key': 'can_manage_ads', 'titleAr': 'إدارة الإعلانات', 'titleEn': 'Advertisements Management'},
      {'key': 'can_chat_support', 'titleAr': 'الدعم الفني والمحادثات', 'titleEn': 'Customer Support Chat'},
    ];

    return Column(
      children: permissionsList.map((perm) {
        final key = perm['key']!;
        final val = _permissions[key] == true;
        return SwitchListTile(
          title: Text(widget.isRtl ? perm['titleAr']! : perm['titleEn']!),
          value: val,
          onChanged: (newValue) => _updatePermission(key, newValue),
          activeColor: Colors.blue,
          contentPadding: EdgeInsets.zero,
        );
      }).toList(),
    );
  }
}
