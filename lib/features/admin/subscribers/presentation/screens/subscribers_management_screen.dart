import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import '../../application/subscribers_provider.dart';
import '../../domain/models/subscriber_model.dart';
import '../widgets/subscriber_detail_sheet.dart';

class SubscribersManagementScreen extends ConsumerStatefulWidget {
  const SubscribersManagementScreen({super.key});

  @override
  ConsumerState<SubscribersManagementScreen> createState() => _SubscribersManagementScreenState();
}

class _SubscribersManagementScreenState extends ConsumerState<SubscribersManagementScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final asyncSubscribers = ref.watch(filteredSubscribersProvider);

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(isRtl ? 'إدارة المشتركين' : 'Subscribers Management'),
        backgroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.read(subscribersProvider.notifier).loadSubscribers(),
          )
        ],
      ),
      body: Column(
        children: [
          // Search & Filters
          Container(
            color: Colors.white,
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: isRtl ? 'بحث...' : 'Search subscriber...',
                    prefixIcon: const Icon(Icons.search),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                  onChanged: (value) {
                    ref.read(subscriberSearchProvider.notifier).state = value;
                  },
                ),
                const SizedBox(height: 16),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: SubscriberFilterType.values.map((type) {
                      final isSelected = ref.watch(subscriberFilterProvider) == type;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: FilterChip(
                          label: Text(_getFilterName(type, isRtl)),
                          selected: isSelected,
                          onSelected: (selected) {
                            ref.read(subscriberFilterProvider.notifier).state = type;
                          },
                          backgroundColor: Colors.grey[100],
                          selectedColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.1),
                          checkmarkColor: Theme.of(context).colorScheme.primary,
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),
          
          // List
          Expanded(
            child: asyncSubscribers.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (err, stack) => Center(child: Text('Error: $err')),
              data: (subscribers) {
                if (subscribers.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.people_outline, size: 64, color: Colors.grey[300]),
                        const SizedBox(height: 16),
                        Text(
                          isRtl ? 'لا يوجد مشتركين' : 'No subscribers match your filters',
                          style: TextStyle(color: Colors.grey[500]),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: subscribers.length,
                  itemBuilder: (context, index) {
                    return _buildSubscriberCard(subscribers[index], isRtl);
                  },
                );
              }
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubscriberCard(SubscriberModel subscriber, bool isRtl) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 0,
      child: InkWell(
        onTap: () => _showDetailSheet(context, subscriber),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              CircleAvatar(
                radius: 24,
                backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.1),
                child: Text(
                  subscriber.name.isNotEmpty ? subscriber.name[0].toUpperCase() : '?',
                  style: TextStyle(
                    color: Theme.of(context).colorScheme.primary,
                    fontWeight: FontWeight.bold,
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
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subscriber.email,
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey[600]),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        _buildRoleChip(subscriber.role, isRtl),
                      ],
                    ),
                  ],
                ),
              ),
              PopupMenuButton<String>(
                icon: const Icon(Icons.more_vert),
                onSelected: (value) => _handleAction(value, subscriber),
                itemBuilder: (context) => [
                  PopupMenuItem(
                    value: 'change_role',
                    child: Row(
                      children: [
                        const Icon(Icons.manage_accounts, size: 20, color: Colors.blue),
                        const SizedBox(width: 8),
                        Text(isRtl ? 'تغيير الصلاحية (Role)' : 'Change Role'),
                      ],
                    ),
                  ),
                  PopupMenuItem(
                    value: 'suspend',
                    child: Row(
                      children: [
                        Icon(subscriber.status == SubscriberStatus.suspended ? Icons.check_circle : Icons.block, size: 20, color: Colors.orange),
                        const SizedBox(width: 8),
                        Text(subscriber.status == SubscriberStatus.suspended ? (isRtl ? 'إلغاء الحظر' : 'Unblock') : (isRtl ? 'حظر المستخدم' : 'Block User'), style: const TextStyle(color: Colors.orange)),
                      ],
                    ),
                  ),
                  PopupMenuItem(
                    value: 'delete',
                    child: Row(
                      children: [
                        const Icon(Icons.delete, size: 20, color: Colors.red),
                        const SizedBox(width: 8),
                        Text(isRtl ? 'حذف' : 'Delete', style: const TextStyle(color: Colors.red)),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRoleChip(UserRole role, bool isRtl) {
    final color = _getRoleColor(role);
    final label = _getRoleName(role, isRtl);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: color.withOpacity(0.5)),
      ),
      child: Text(label, style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.bold)),
    );
  }

  void _showDetailSheet(BuildContext context, SubscriberModel subscriber) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => SubscriberDetailSheet(subscriber: subscriber),
    );
  }

  void _handleAction(String action, SubscriberModel subscriber) {
    switch (action) {
      case 'change_role':
        _showChangeRoleDialog(subscriber);
        break;
      case 'suspend':
        ref.read(subscribersProvider.notifier).toggleSuspension(subscriber.id);
        break;
      case 'delete':
        _showDeleteConfirm(subscriber);
        break;
    }
  }

  void _showChangeRoleDialog(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(isRtl ? 'تغيير صلاحية المستخدم' : 'Change Role'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: UserRole.values.map((role) {
              return ListTile(
                title: Text(_getRoleName(role, isRtl)),
                leading: Radio<UserRole>(
                  value: role,
                  groupValue: subscriber.role,
                  onChanged: (newRole) {
                    if (newRole != null) {
                      ref.read(subscribersProvider.notifier).changeRole(subscriber.id, newRole);
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم تغيير الصلاحية بنجاح' : 'Role updated successfully')));
                    }
                  },
                ),
              );
            }).toList(),
          ),
        );
      }
    );
  }

  void _showDeleteConfirm(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isRtl ? 'تأكيد الحذف' : 'Confirm Deletion'),
        content: Text(isRtl 
            ? 'هل أنت متأكد من حذف هذا المستخدم نهائياً؟ لا يمكن التراجع عن هذا الإجراء.'
            : 'Are you sure you want to permanently delete this user?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          TextButton(
            onPressed: () {
              ref.read(subscribersProvider.notifier).removeSubscriber(subscriber.id);
              Navigator.pop(context);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: Text(isRtl ? 'حذف' : 'Delete'),
          ),
        ],
      ),
    );
  }

  Color _getRoleColor(UserRole role) {
    switch (role) {
      case UserRole.admin: return Colors.purple;
      case UserRole.merchant: return Colors.green;
      case UserRole.buyer: return Colors.blue;
      case UserRole.guest: return Colors.grey;
      case UserRole.employee: return Colors.orange;
    }
  }

  String _getRoleName(UserRole role, bool isRtl) {
    switch (role) {
      case UserRole.admin: return isRtl ? 'ادمن' : 'Admin';
      case UserRole.merchant: return isRtl ? 'تاجر' : 'Merchant';
      case UserRole.buyer: return isRtl ? 'مشتري' : 'Buyer';
      case UserRole.guest: return isRtl ? 'زائر' : 'Guest';
      case UserRole.employee: return isRtl ? 'موظف' : 'Employee';
    }
  }

  String _getFilterName(SubscriberFilterType type, bool isRtl) {
    switch (type) {
      case SubscriberFilterType.all: return isRtl ? 'الكل' : 'All';
      case SubscriberFilterType.buyers: return isRtl ? 'مشترين' : 'Buyers';
      case SubscriberFilterType.merchants: return isRtl ? 'تجار' : 'Merchants';
      case SubscriberFilterType.pending: return isRtl ? 'معلق' : 'Pending';
      case SubscriberFilterType.suspended: return isRtl ? 'موقوف' : 'Suspended';
    }
  }
}
