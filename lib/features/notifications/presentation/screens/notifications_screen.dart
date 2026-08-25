import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/notifications/application/notifications_provider.dart';

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  @override
  Widget build(BuildContext context) {
    final notificationsState = ref.watch(notificationsProvider);
    final unreadCount = ref.watch(unreadNotificationsCountProvider);

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('الإشعارات'),
          actions: [
            if (unreadCount > 0)
              TextButton(
                onPressed: () {
                  ref.read(notificationsProvider.notifier).markAllAsRead();
                },
                child: const Text('تحديد الكل كمقروء'),
              ),
          ],
        ),
        body: notificationsState.when(
          data: (notifications) {
            if (notifications.isEmpty) {
              return const Center(
                child: Text(
                  'لا توجد إشعارات',
                  style: TextStyle(fontSize: 18),
                ),
              );
            }

            return ListView.builder(
              itemCount: notifications.length,
              itemBuilder: (context, index) {
                final notification = notifications[index];
                final isRead = notification['is_read'] == true;
                final type = notification['type'] as String?;
                final title = notification['title'] as String? ?? 'إشعار';
                final body = notification['body'] as String? ?? '';
                final createdAtStr = notification['created_at'] as String?;
                final id = notification['id'].toString();

                DateTime? createdAt;
                if (createdAtStr != null) {
                  createdAt = DateTime.tryParse(createdAtStr);
                }
                
                String dateText = '';
                if (createdAt != null) {
                  dateText = DateFormat('yyyy/MM/dd hh:mm a').format(createdAt.toLocal());
                }

                IconData iconData;
                switch (type) {
                  case 'new_order':
                    iconData = Icons.shopping_cart;
                    break;
                  case 'payout_approved':
                    iconData = Icons.account_balance_wallet;
                    break;
                  default:
                    iconData = Icons.notifications;
                }

                return InkWell(
                  onTap: () {
                    if (!isRead) {
                      ref.read(notificationsProvider.notifier).markAsRead(id);
                    }
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('تم النقر على الإشعار')),
                    );
                  },
                  child: Container(
                    color: isRead ? Colors.transparent : Colors.blue.withOpacity(0.05),
                    padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        CircleAvatar(
                          backgroundColor: Colors.grey.withOpacity(0.1),
                          child: Icon(iconData, color: Colors.blue),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                title,
                                style: TextStyle(
                                  fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                                  fontSize: 16,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                body,
                                style: TextStyle(
                                  color: Colors.grey[700],
                                  fontSize: 14,
                                ),
                              ),
                              if (dateText.isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Text(
                                  dateText,
                                  style: TextStyle(
                                    color: Colors.grey[500],
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => Center(child: Text('حدث خطأ أثناء تحميل الإشعارات')),
        ),
      ),
    );
  }
}
