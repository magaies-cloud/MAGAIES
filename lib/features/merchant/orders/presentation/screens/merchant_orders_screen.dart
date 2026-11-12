import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/call_screen.dart';
import 'package:magaiees_marketplace/features/merchant/inventory/presentation/screens/merchant_inventory_screen.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';
import 'package:magaiees_marketplace/core/widgets/product_image_preview_box.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';

// Store info provider for merchant orders screen
final merchantOrdersStoreProvider = FutureProvider.autoDispose<Map<String, dynamic>?>((ref) async {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return null;
  if (user.isStoreStaff && user.storeId != null) {
    return safeSupabaseQuery(
      () => Supabase.instance.client
          .from('stores').select('*').eq('id', user.storeId!).limit(1).maybeSingle(),
      fallback: () => null,
    );
  }
  final storeRes = await safeSupabaseQuery(
    () => Supabase.instance.client
        .from('stores').select('*').eq('owner_id', user.id).limit(1).maybeSingle(),
    fallback: () => null,
  );
  return storeRes;
});

// Platform commission settings provider for order breakdown
final merchantCommissionSettingsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  try {
    final settingsRes = await safeSupabaseQuery(
      () => Supabase.instance.client
          .from('gamification_settings')
          .select('enable_commission, commission_percentage')
          .eq('id', 1)
          .maybeSingle(),
      fallback: () => null,
    );
    if (settingsRes != null) {
      final bool enabled = settingsRes['enable_commission'] == true;
      final double percent = (num.tryParse(settingsRes['commission_percentage']?.toString() ?? '0') ?? 0.0).toDouble();
      return {
        'enable_commission': enabled,
        'commission_percentage': percent,
      };
    }
  } catch (_) {}
  return {'enable_commission': false, 'commission_percentage': 0.0};
});

// Real-time orders provider
final merchantOrdersProvider = StreamProvider.autoDispose<List<Map<String, dynamic>>>((ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) {
    return Stream.value([]);
  }

  final controller = StreamController<List<Map<String, dynamic>>>.broadcast();
  RealtimeChannel? channel;

  // Register onDispose synchronously at provider initialization before any async gaps
  ref.onDispose(() {
    final ch = channel;
    if (ch != null) {
      ch.unsubscribe();
      Supabase.instance.client.removeChannel(ch);
    }
    controller.close();
  });

  String? storeId;

  Future<void> fetchOrders() async {
    if (controller.isClosed) return;
    try {
      if (storeId == null) {
        if (user.isStoreStaff && user.storeId != null) {
          storeId = user.storeId;
        } else {
          final storeRes = await safeSupabaseQuery(
            () => Supabase.instance.client
                .from('stores')
                .select('id, name, name_ar')
                .eq('owner_id', user.id)
                .limit(1)
                .maybeSingle(),
            fallback: () => null,
          );
          storeId = storeRes?['id']?.toString();
        }
      }

      if (storeId == null) {
        if (!controller.isClosed) controller.add([]);
        return;
      }

      final ordersRes = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('orders')
            .select('*, order_items(*), buyer:users!buyer_id(id, name, name_ar, phone)')
            .eq('store_id', storeId!)
            .order('created_at', ascending: false),
        fallback: () => <dynamic>[],
      );

      final orders = List<Map<String, dynamic>>.from(ordersRes as Iterable);

      if (orders.isNotEmpty) {
        final orderIds = orders.map((o) => o['id'].toString()).toList();

        final paymentsRes = await safeSupabaseQuery(
          () => Supabase.instance.client
              .from('payments')
              .select('*')
              .inFilter('order_id', orderIds)
              .order('created_at', ascending: true),
          fallback: () => <dynamic>[],
        );

        final paymentsByOrderId = <String, List<dynamic>>{};
        for (var p in (paymentsRes as Iterable)) {
          final oId = p['order_id'].toString();
          paymentsByOrderId.putIfAbsent(oId, () => []).add(p);
        }

        for (int i = 0; i < orders.length; i++) {
          orders[i] = {
            ...orders[i],
            'payments': paymentsByOrderId[orders[i]['id'].toString()] ?? []
          };
        }
      }

      if (!controller.isClosed) controller.add(orders);
    } catch (e) {
      if (!controller.isClosed) controller.addError(e);
    }
  }

  void setupRealtime() async {
    if (storeId == null) {
      final s = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('stores')
            .select('id')
            .eq('owner_id', user.id)
            .limit(1)
            .maybeSingle(),
        fallback: () => null,
      );
      storeId = s?['id']?.toString();
    }
    if (storeId == null || controller.isClosed) return;

    channel = Supabase.instance.client
        .channel('merchant_orders_${storeId}_${DateTime.now().millisecondsSinceEpoch}')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'orders',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'store_id',
            value: storeId!,
          ),
          callback: (payload) {
            final newRec = payload.newRecord;
            final rowStoreId = newRec['store_id']?.toString();
            if (rowStoreId == storeId || rowStoreId == null) {
              fetchOrders();
              if (payload.eventType == PostgresChangeEvent.insert) {
                NotificationSoundService.play();
              }
            }
          },
        )
        .subscribe();
  }

  fetchOrders().then((_) {
    setupRealtime();
  });

  return controller.stream;
});

// Filter state providers
final _statusFilterProvider = StateProvider.autoDispose<String?>((ref) => null);
final _paymentFilterProvider = StateProvider.autoDispose<String?>((ref) => null);

class MerchantOrdersScreen extends ConsumerWidget {
  const MerchantOrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final ordersAsync = ref.watch(merchantOrdersProvider);
    final statusFilter = ref.watch(_statusFilterProvider);
    final paymentFilter = ref.watch(_paymentFilterProvider);

    int count = 0;
    if (ordersAsync.hasValue && ordersAsync.value != null) {
      final filteredOrders = ordersAsync.value!.where((o) {
        final matchStatus = statusFilter == null || o['status'] == statusFilter;
        bool matchPayment = true;
        if (paymentFilter == 'paid') {
          matchPayment = o['payment_status'] == 'paid';
        } else if (paymentFilter == 'debt') {
          final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
          final paid = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
          matchPayment = (total - paid) > 0;
        }
        return matchStatus && matchPayment;
      }).toList();
      count = filteredOrders.length;
    }

    return Scaffold(
      appBar: AppBar(
        title: Consumer(
          builder: (context, ref, _) {
            final storeAsync = ref.watch(merchantOrdersStoreProvider);
            final storeData = storeAsync.valueOrNull;
            final storeName = NameFormatter.formatName(
              name: storeData?['name']?.toString(),
              nameAr: storeData?['name_ar']?.toString(),
              isRtl: isRtl,
            );
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(isRtl ? 'الطلبات' : 'Orders'),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(color: Colors.blue.shade100, borderRadius: BorderRadius.circular(12)),
                      child: Text(
                        count.toString(), 
                        style: TextStyle(color: Colors.blue.shade900, fontSize: 13, fontWeight: FontWeight.bold)
                      ),
                    ),
                  ],
                ),
                if (storeName.isNotEmpty)
                  Text(
                    '${isRtl ? "متجر:" : "Store:"} $storeName',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.normal),
                  ),
              ],
            );
          },
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.invalidate(merchantOrdersStoreProvider);
              ref.invalidate(merchantOrdersProvider);
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Smart Filters Header (Professional Status & Payment Pill Indicators with Live Counts)
          Builder(
            builder: (context) {
              final allOrders = ordersAsync.valueOrNull ?? const <Map<String, dynamic>>[];
              int countStatus(String? s) => s == null ? allOrders.length : allOrders.where((o) => o['status'] == s).length;
              bool isOrderPaid(Map<String, dynamic> o) {
                if (o['status'] == 'cancelled') return false;
                if (o['payment_status'] == 'paid') return true;
                final total = double.tryParse(o['total_amount']?.toString() ?? '0') ?? 0;
                final paid = double.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0;
                return total > 0 && paid >= total;
              }
              bool isOrderUnderReview(Map<String, dynamic> o) =>
                  o['status'] != 'cancelled' && o['payment_status'] == 'under_review';
              bool isOrderDebt(Map<String, dynamic> o) {
                if (o['status'] == 'cancelled' || isOrderPaid(o)) return false;
                final total = double.tryParse(o['total_amount']?.toString() ?? '0') ?? 0;
                final paid = double.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0;
                return (total - paid) > 0;
              }

              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 6, offset: const Offset(0, 2))],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.tune_rounded, size: 17, color: Color(0xFF2563EB)),
                        const SizedBox(width: 6),
                        Text(isRtl ? 'حالة الطلب:' : 'Order Status:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip(context, ref, _statusFilterProvider, null, isRtl ? 'الكل' : 'All', count: countStatus(null), color: Colors.blueGrey),
                          _buildFilterChip(context, ref, _statusFilterProvider, 'pending', isRtl ? 'قيد الانتظار' : 'Pending', count: countStatus('pending'), color: Colors.orange),
                          _buildFilterChip(context, ref, _statusFilterProvider, 'processing', isRtl ? 'قيد المعالجة' : 'Processing', count: countStatus('processing'), color: Colors.blue),
                          _buildFilterChip(context, ref, _statusFilterProvider, 'shipped', isRtl ? 'تم الشحن' : 'Shipped', count: countStatus('shipped'), color: Colors.indigo),
                          _buildFilterChip(context, ref, _statusFilterProvider, 'delivered', isRtl ? 'تم التسليم' : 'Delivered', count: countStatus('delivered'), color: Colors.green),
                          _buildFilterChip(context, ref, _statusFilterProvider, 'cancelled', isRtl ? 'ملغي' : 'Cancelled', count: countStatus('cancelled'), color: Colors.red),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        const Icon(Icons.payments_outlined, size: 17, color: Color(0xFF059669)),
                        const SizedBox(width: 6),
                        Text(isRtl ? 'حالة الدفع:' : 'Payment Status:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildFilterChip(context, ref, _paymentFilterProvider, null, isRtl ? 'الكل' : 'All', count: allOrders.length, color: Colors.blueGrey),
                          _buildFilterChip(context, ref, _paymentFilterProvider, 'paid', isRtl ? 'مدفوع بالكامل' : 'Fully Paid', count: allOrders.where(isOrderPaid).length, color: Colors.green),
                          _buildFilterChip(context, ref, _paymentFilterProvider, 'under_review', isRtl ? 'قيد المراجعة' : 'Under Review', count: allOrders.where(isOrderUnderReview).length, color: Colors.purple),
                          _buildFilterChip(context, ref, _paymentFilterProvider, 'debt', isRtl ? 'عليه متبقي' : 'Has Debt', count: allOrders.where(isOrderDebt).length, color: Colors.red),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
          
          // Orders List
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                ref.invalidate(merchantOrdersProvider);
              },
              child: ordersAsync.when(
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (err, stack) => SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.cloud_off_rounded, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          Text('Error: $err', style: TextStyle(color: Colors.red.shade700)),
                          const SizedBox(height: 16),
                          FilledButton.icon(
                            onPressed: () => ref.invalidate(merchantOrdersProvider),
                            icon: const Icon(Icons.refresh),
                            label: Text(isRtl ? 'إعادة المحاولة' : 'Retry'),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                data: (orders) {
                  // Apply filters
                  final filteredOrders = orders.where((o) {
                    final matchStatus = statusFilter == null || o['status'] == statusFilter;
                    bool matchPayment = true;
                    final total = double.tryParse(o['total_amount']?.toString() ?? '0') ?? 0;
                    final paid = double.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0;
                    final isPaid = o['status'] != 'cancelled' &&
                        (o['payment_status'] == 'paid' || (total > 0 && paid >= total));
                    if (paymentFilter == 'paid') {
                      matchPayment = isPaid;
                    } else if (paymentFilter == 'under_review') {
                      matchPayment = o['status'] != 'cancelled' && o['payment_status'] == 'under_review';
                    } else if (paymentFilter == 'debt') {
                      matchPayment = o['status'] != 'cancelled' && !isPaid && (total - paid) > 0;
                    }
                    return matchStatus && matchPayment;
                  }).toList();

                  if (filteredOrders.isEmpty) {
                    return ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      children: [
                        SizedBox(height: MediaQuery.of(context).size.height * 0.25),
                        Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.receipt_long_outlined, size: 64, color: Colors.grey.shade400),
                              const SizedBox(height: 16),
                              Text(isRtl ? 'لا توجد طلبات تطابق الفلتر' : 'No orders match filters',
                                  style: TextStyle(fontSize: 16, color: Colors.grey.shade600)),
                            ],
                          ),
                        ),
                      ],
                    );
                  }
                  return ListView.builder(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: filteredOrders.length,
                    itemBuilder: (context, index) =>
                        _OrderCard(order: filteredOrders[index], ref: ref, isRtl: isRtl),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(
    BuildContext context,
    WidgetRef ref,
    AutoDisposeStateProvider<String?> provider,
    String? value,
    String label, {
    MaterialColor? color,
    int? count,
  }) {
    final selectedValue = ref.watch(provider);
    final isSelected = selectedValue == value;
    final themeColor = color ?? Colors.blueGrey;

    return Padding(
      padding: const EdgeInsetsDirectional.only(end: 8),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: () => ref.read(provider.notifier).state = value,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 180),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
            decoration: BoxDecoration(
              color: isSelected ? themeColor.shade600 : themeColor.shade50.withValues(alpha: 0.7),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: isSelected ? themeColor.shade700 : themeColor.shade200,
                width: isSelected ? 1.4 : 1.0,
              ),
              boxShadow: isSelected
                  ? [
                      BoxShadow(
                        color: themeColor.withValues(alpha: 0.28),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ]
                  : null,
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: isSelected ? Colors.white : themeColor.shade600,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                    color: isSelected ? Colors.white : themeColor.shade900,
                  ),
                ),
                if (count != null) ...[
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                    decoration: BoxDecoration(
                      color: isSelected ? Colors.white.withValues(alpha: 0.24) : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      count.toString(),
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w800,
                        color: isSelected ? Colors.white : themeColor.shade800,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  final Map<String, dynamic> order;
  final WidgetRef ref;
  final bool isRtl;
  const _OrderCard({required this.order, required this.ref, required this.isRtl});

  String _stampStaffInNotes(String currentNotes) {
    final currentUser = ref.read(authNotifierProvider).valueOrNull;
    if (currentUser != null && currentUser.isStoreStaff) {
      if (!currentNotes.contains('[staff:')) {
        final sName = (currentUser.nameAr != null && currentUser.nameAr!.trim().isNotEmpty)
            ? currentUser.nameAr!.trim()
            : (currentUser.name.isNotEmpty ? currentUser.name : 'موظف مبيعات');
        final staffStamp = '[staff: ${currentUser.id} | $sName]';
        return currentNotes.isEmpty ? staffStamp : '$currentNotes\n$staffStamp';
      }
    }
    return currentNotes;
  }

  @override
  Widget build(BuildContext context) {
    final orderId = OrderNumberFormatter.formatFromMap(order);
    final parsedCreated = DateTime.tryParse(order['created_at']?.toString() ?? '')?.toLocal();
    final date = parsedCreated != null ? DateFormat('yyyy-MM-dd HH:mm').format(parsedCreated) : '';
    final totalAmount = double.tryParse(order['total_amount']?.toString() ?? '0') ?? 0;
    final paidAmount = double.tryParse(order['paid_amount']?.toString() ?? '0') ?? 0;
    final transportFee = double.tryParse(order['transport_fee']?.toString() ?? '0') ?? 0;
    final offloadingFee = double.tryParse(order['offloading_fee']?.toString() ?? '0') ?? 0;
    final status = order['status'] ?? 'pending';
    final paymentStatus = order['payment_status'] ?? 'unpaid';
    final items = order['order_items'] as List<dynamic>? ?? [];
    final payments = order['payments'] as List<dynamic>? ?? [];
    final notes = order['notes']?.toString() ?? '';
    final shippingAddress = order['shipping_address']?.toString() ?? '';
    final isSelfPickup = notes.contains('استلام وترحيل ذاتي') || shippingAddress.contains('استلام وترحيل ذاتي');

    // Commission settings & net store share
    final commSettings = ref.watch(merchantCommissionSettingsProvider).valueOrNull ?? {'enable_commission': false, 'commission_percentage': 0.0};
    final bool commissionEnabled = commSettings['enable_commission'] == true;
    final double commissionPercent = (commSettings['commission_percentage'] as num?)?.toDouble() ?? 0.0;
    final double goodsAmount = (totalAmount - transportFee - offloadingFee).clamp(0.0, double.infinity);
    final double commissionAmount = commissionEnabled ? ((goodsAmount * commissionPercent) / 100) : 0.0;
    final double netMerchantShare = (totalAmount - commissionAmount).clamp(0.0, double.infinity);

    // Extract group_id for multi-store order coordination
    final groupIdMatch = RegExp(r'\[طلب مجمّع: (GRP-[^|]+)').firstMatch(notes);
    final String? groupId = groupIdMatch?.group(1)?.trim();

    final bool feesAwaitingApproval = !isSelfPickup && (order['buyer_approved_fees'] == null || order['buyer_approved_fees'] == false);

    double pendingReceiptAmt = 0.0;
    if (!feesAwaitingApproval && paymentStatus == 'under_review' && notes.contains('amount:')) {
      final aStr = notes.split('amount:')[1].split('|')[0].trim().split('\n')[0].trim();
      pendingReceiptAmt = double.tryParse(aStr) ?? 0.0;
    }
    if (!feesAwaitingApproval && pendingReceiptAmt == 0.0 && paidAmount > 0 && paymentStatus == 'under_review') {
      pendingReceiptAmt = paidAmount;
    }
    final effectivePaid = (!feesAwaitingApproval && paymentStatus == 'under_review')
        ? (pendingReceiptAmt > 0 ? pendingReceiptAmt : paidAmount)
        : paidAmount;
    final remaining = (totalAmount - effectivePaid).clamp(0.0, double.infinity);

    return Card(
      elevation: 2,
      shadowColor: Colors.black12,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: _statusColor(status).withOpacity(0.2), width: 1.5),
      ),
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: _statusColor(status).withOpacity(0.05),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('${isRtl ? 'طلب' : 'Order'} #$orderId',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.blue.shade900)),
                _buildStatusBadge(status),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Date & Buyer Info
                Text('${isRtl ? 'التاريخ' : 'Date'}: $date',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                const SizedBox(height: 6),
                // Buyer Info Banner
                Builder(builder: (_) {
                  final buyerData = order['buyer'] as Map<String, dynamic>?;
                  final buyerName = buyerData?['name']?.toString() ?? '';
                  final buyerPhone = buyerData?['phone']?.toString() ?? '';
                  if (buyerName.isEmpty && buyerPhone.isEmpty) return const SizedBox.shrink();
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    margin: const EdgeInsets.only(bottom: 2),
                    decoration: BoxDecoration(
                      color: Colors.purple.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.purple.shade100),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.person_outline, size: 16, color: Colors.purple.shade700),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Text(
                            '${isRtl ? 'المشتري' : 'Buyer'}: $buyerName${buyerPhone.isNotEmpty ? ' ($buyerPhone)' : ''}',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.purple.shade800),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  );
                }),
                // Staff Attribution Banner (if processed by a store staff member)
                Builder(builder: (_) {
                  final staffMatch = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes);
                  final staffName = staffMatch?.group(2)?.trim();
                  if (staffName == null || staffName.isEmpty) return const SizedBox.shrink();
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    margin: const EdgeInsets.only(top: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFCBD5E1)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.badge_outlined, size: 15, color: Color(0xFF475569)),
                        const SizedBox(width: 6),
                        Text(
                          '${isRtl ? 'الموظف المسؤول' : 'Handled by'}: $staffName',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                        ),
                      ],
                    ),
                  );
                }),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: isSelfPickup ? const Color(0xFFF0FDF4) : const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: isSelfPickup ? const Color(0xFFBBF7D0) : const Color(0xFFBFDBFE)),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        isSelfPickup ? Icons.warehouse_rounded : Icons.location_on_outlined,
                        size: 16,
                        color: isSelfPickup ? const Color(0xFF15803D) : const Color(0xFF1D4ED8),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          isSelfPickup
                              ? (isRtl ? 'استلام وترحيل ذاتي بواسطة المشتري' : 'Self-Pickup by Buyer')
                              : (shippingAddress.isNotEmpty ? shippingAddress : (isRtl ? 'توصيل بواسطة التاجر' : 'Merchant Delivery')),
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isSelfPickup ? const Color(0xFF166534) : const Color(0xFF1E40AF),
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),

                // Financial Summary
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    children: [
                      // Items listed directly at the top like an invoice
                      if (items.isNotEmpty) ...[
                        ...items.map((item) {
                          final pName = item['product_name']?.toString() ?? (isRtl ? 'منتج' : 'Product');
                          final qty = num.tryParse(item['quantity']?.toString() ?? '1') ?? 1;
                          double uPrice = double.tryParse((item['price_at_time'] ?? item['price_sdg'] ?? 0).toString()) ?? 0.0;
                          if (uPrice == 0 && items.length == 1 && totalAmount > 0) {
                            uPrice = (totalAmount - transportFee) / qty;
                          }
                          final lineTotal = uPrice * qty;
                          final label = qty > 1 
                              ? '$pName ($qty × ${NumberFormat('#,##0').format(uPrice)})'
                              : '$pName (1 × ${NumberFormat('#,##0').format(uPrice)})';
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 4),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    label,
                                    style: TextStyle(fontSize: 13, color: Colors.grey.shade900, fontWeight: FontWeight.bold),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                Text(
                                  '${NumberFormat('#,##0').format(lineTotal > 0 ? lineTotal : uPrice)} ${isRtl ? 'ج.س' : 'SDG'}',
                                  style: TextStyle(fontSize: 14, color: Colors.grey.shade900, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          );
                        }),
                      ] else ...[
                        _finRow(isRtl ? 'إجمالي الطلب' : 'Total', totalAmount, bold: true),
                      ],
                      if (isSelfPickup) ...[
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'رسوم الترحيل والتنزيل (استلام ذاتي)' : 'Transport & Offloading (Self-Pickup)',
                                style: const TextStyle(fontSize: 13, color: Color(0xFF0F766E), fontWeight: FontWeight.bold)),
                            Text('0.00 ${isRtl ? 'ج.س' : 'SDG'}',
                                style: const TextStyle(fontSize: 14, color: Color(0xFF0F766E), fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ] else ...[
                        if (transportFee > 0) ...[
                          const SizedBox(height: 4),
                          _finRow(isRtl ? 'رسوم الترحيل' : 'Transport Fee', transportFee, color: Colors.orange.shade700),
                        ],
                        if (order['offloading_fee'] != null && (double.tryParse(order['offloading_fee'].toString()) ?? 0) > 0) ...[
                          const SizedBox(height: 4),
                          _finRow(isRtl ? 'رسوم التنزيل' : 'Offloading Fee', double.tryParse(order['offloading_fee'].toString()) ?? 0, color: Colors.orange.shade700),
                        ],
                      ],
                      if (order['payment_method'] == 'partial_cod') ...[
                        const SizedBox(height: 4),
                        _finRow(
                          isRtl ? 'الدفعة المقدمة (عربون)' : 'Upfront Deposit',
                          double.tryParse(order['upfront_payment']?.toString() ?? '0') ?? 0,
                          color: Colors.blue.shade700,
                        ),
                      ],
                      if (items.length > 1 || (!isSelfPickup && (transportFee > 0 || (order['offloading_fee'] != null && (double.tryParse(order['offloading_fee'].toString()) ?? 0) > 0)))) ...[
                        const Divider(height: 12),
                        _finRow(isRtl ? 'إجمالي الطلب' : 'Total', totalAmount, bold: true),
                      ],
                      if (commissionEnabled && commissionAmount > 0) ...[
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFFBEB),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFFDE68A)),
                          ),
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.pie_chart_outline, size: 14, color: Color(0xFFB45309)),
                                      const SizedBox(width: 4),
                                      Text(
                                        '${isRtl ? 'خصم عمولة السيطرة' : 'Magaiees Commission'} (${commissionPercent.toStringAsFixed(commissionPercent.truncateToDouble() == commissionPercent ? 0 : 1)}%)',
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFFB45309)),
                                      ),
                                    ],
                                  ),
                                  Text(
                                    '-${NumberFormat('#,##0.##').format(commissionAmount)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    isRtl ? 'صافي استحقاق المتجر' : 'Net Store Revenue',
                                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                                  ),
                                  Text(
                                    '${NumberFormat('#,##0.##').format(netMerchantShare)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                      const Divider(height: 16),
                      if (paymentStatus != 'under_review') ...[
                        _finRow(isRtl ? 'المدفوع' : 'Paid', paidAmount, color: Colors.green.shade700),
                      ],
                      if (paymentStatus == 'under_review' && pendingReceiptAmt > 0) ...[
                        _finRow(
                          isRtl ? 'المبلغ المحول في الإشعار' : 'Transferred in Receipt',
                          pendingReceiptAmt,
                          color: const Color(0xFFD97706),
                          bold: true,
                        ),
                      ],
                      const SizedBox(height: 4),
                      _finRow(isRtl ? 'المتبقي' : 'Remaining', remaining,
                          color: remaining > 0 ? Colors.red.shade700 : Colors.green.shade700, bold: true),
                      const SizedBox(height: 8),
                      _buildPaymentBadge(paymentStatus, order['payment_method']?.toString()),
                    ],
                  ),
                ),

                // ── Grouped Shipment Products from Other Stores ──
                if (groupId != null && groupId.isNotEmpty)
                  FutureBuilder<List<Map<String, dynamic>>>(
                    future: _fetchGroupedOrderItems(order['id']?.toString() ?? '', groupId, order['buyer_id']?.toString() ?? ''),
                    builder: (context, snapshot) {
                      if (!snapshot.hasData || snapshot.data!.isEmpty) return const SizedBox.shrink();
                      final otherItems = snapshot.data!;
                      return Container(
                        margin: const EdgeInsets.only(top: 10),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(Icons.inventory_2_outlined, color: Colors.blueGrey.shade600, size: 18),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    isRtl ? '📦 منتجات أخرى في نفس الشحنة:' : '📦 Other products in same shipment:',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: Colors.blueGrey.shade800),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            ...otherItems.map((item) => Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Row(
                                children: [
                                  const SizedBox(width: 26),
                                  Icon(Icons.circle, size: 6, color: Colors.blueGrey.shade400),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      '${item['product_name']} — ${isRtl ? 'الكمية' : 'Qty'}: ${item['quantity']}',
                                      style: TextStyle(fontSize: 12, color: Colors.blueGrey.shade700),
                                    ),
                                  ),
                                ],
                              ),
                            )),
                            const SizedBox(height: 6),
                            Text(
                              isRtl
                                  ? 'هذه المنتجات من متاجر أخرى وسيتم شحنها معاً'
                                  : 'These products are from other stores and will be shipped together',
                              style: TextStyle(fontSize: 10.5, color: Colors.blueGrey.shade500, fontStyle: FontStyle.italic),
                            ),
                          ],
                        ),
                      );
                    },
                  ),

                // Buyer Uploaded Payment Receipt Banner
                if (!feesAwaitingApproval && paymentStatus == 'under_review' && order['notes']?.toString().contains(RegExp(r'(?<!\w)receipt_url:')) == true) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFF59E0B)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.receipt_rounded, color: Color(0xFFB45309)),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl ? '📑 أرفق المشتري إشعار تحويل بنكي لهذا الطلب' : 'Buyer uploaded a payment receipt',
                                style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF92400E), fontSize: 12.5),
                              ),
                              Text(
                                isRtl ? 'انقر للمعاينة والتحقق من صحة التحويل' : 'Click to inspect and verify receipt',
                                style: const TextStyle(color: Color(0xFFB45309), fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        FilledButton.tonalIcon(
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFFF59E0B),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          ),
                          icon: const Icon(Icons.visibility_rounded, size: 16),
                          label: Text(isRtl ? 'معاينة الإشعار' : 'View Receipt', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                          onPressed: () => _showMerchantReceiptDialog(context, order, payments, remaining, totalAmount, paidAmount),
                        ),
                      ],
                    ),
                  ),
                ],

                // Payment History
                if (payments.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Text(isRtl ? 'سجل المدفوعات:' : 'Payments:', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                  const SizedBox(height: 6),
                  ...payments.map((p) {
                    final parsedPDate = DateTime.tryParse(p['created_at']?.toString() ?? '')?.toLocal();
                    final pDate = parsedPDate != null ? DateFormat('MM-dd HH:mm').format(parsedPDate) : '';
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 3),
                      child: Row(
                        children: [
                          Icon(Icons.payments_outlined, size: 14, color: Colors.green.shade600),
                          const SizedBox(width: 6),
                          Expanded(child: Text('${p['amount']} ${isRtl ? 'ج.س' : 'SDG'} - ${p['method'] ?? ''}',
                              style: TextStyle(fontSize: 12, color: Colors.green.shade700))),
                          Text(pDate, style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                        ],
                      ),
                    );
                  }),
                ],

                // Actions
                const SizedBox(height: 16),
                _buildActions(context, status, remaining, totalAmount, paidAmount, order),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: _statusColor(status).withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _statusColor(status).withOpacity(0.3)),
      ),
      child: Text(_statusText(status), style: TextStyle(color: _statusColor(status), fontSize: 12, fontWeight: FontWeight.bold)),
    );
  }

  Widget _buildPaymentBadge(String ps, [String? method]) {
    Color c; String t; IconData ic;
    switch (ps) {
      case 'under_review': c = Colors.blue.shade700; t = isRtl ? 'قيد المراجعة' : 'Under Review'; ic = Icons.pending_actions; break;
      case 'partial': c = Colors.orange.shade700; t = isRtl ? 'دفع جزئي' : 'Partially Paid'; ic = Icons.timelapse; break;
      case 'paid': c = Colors.green.shade700; t = isRtl ? 'مكتمل الدفع' : 'Fully Paid'; ic = Icons.check_circle; break;
      default:
        if (method == 'partial_cod') {
          c = Colors.teal.shade700; t = isRtl ? 'بانتظار العربون (دفع جزئي)' : 'Awaiting Deposit'; ic = Icons.price_change_outlined;
        } else {
          c = Colors.red.shade700; t = isRtl ? 'لم يتم الدفع' : 'Unpaid'; ic = Icons.cancel;
        }
    }
    return Row(mainAxisAlignment: MainAxisAlignment.center, children: [
      Icon(ic, size: 16, color: c), const SizedBox(width: 4),
      Text(t, style: TextStyle(color: c, fontSize: 13, fontWeight: FontWeight.bold)),
    ]);
  }

  Widget _finRow(String label, double value, {Color? color, bool bold = false}) {
    return Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
      Text(label, style: TextStyle(fontSize: 13, color: color ?? Colors.grey.shade700, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
      Text('${NumberFormat('#,##0').format(value)} ${isRtl ? 'ج.س' : 'SDG'}',
          style: TextStyle(fontSize: 14, color: color ?? Colors.grey.shade900, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
    ]);
  }

  Widget _buildActions(BuildContext context, String status, double remaining, double total, double paid, Map<String, dynamic> order) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final bool buyerApprovedFees = order['buyer_approved_fees'] == true;
    final bool hasFeesSet = order['transport_fee'] != null || order['offloading_fee'] != null;

    if (status == 'cancelled') {
      return Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.red.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.red.shade200),
            ),
            child: Row(
              children: [
                Icon(Icons.cancel_rounded, color: Colors.red.shade700, size: 18),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    isRtl ? 'الطلب ملغي حالياً بواسطة المشتري' : 'Order currently cancelled by buyer',
                    style: TextStyle(color: Colors.red.shade900, fontSize: 12.5, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: _MerchantChatButton(order: order, isRtl: isRtl),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _MerchantCallButton(order: order, isRtl: isRtl),
              ),
            ],
          ),
        ],
      );
    }

    return Column(children: [
      // Visual indicator for fee approval
      if (hasFeesSet && buyerApprovedFees && status == 'pending') ...[
        Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: const Color(0xFFECFDF5),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: const Color(0xFFA7F3D0)),
          ),
          child: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 18),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  isRtl
                      ? '✅ وافق المشتري على رسوم التوصيل (جاهز للقبول والتأكيد)'
                      : '✅ Buyer approved delivery fees (Ready to accept)',
                  style: const TextStyle(color: Color(0xFF065F46), fontSize: 12.5, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      ],

      // Payment Record Button (if remaining balance exists)
      if (remaining > 0) ...[
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => _showPaymentDialog(context, remaining, total, paid),
            icon: const Icon(Icons.payments_outlined, size: 18),
            label: Text(
              isRtl ? 'تأكيد استلام دفعة' : 'Record Payment',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              overflow: TextOverflow.ellipsis,
            ),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.green.shade700,
              side: BorderSide(color: Colors.green.shade300),
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ),
        const SizedBox(height: 8),
      ],

      // In-App Communication Buttons Row (Chat + VoIP Call)
      Row(
        children: [
          Expanded(
            child: _MerchantChatButton(
              order: order,
              isRtl: isRtl,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _MerchantCallButton(
              order: order,
              isRtl: isRtl,
            ),
          ),
        ],
      ),
      if (status != 'delivered') const SizedBox(height: 8),

      // Status buttons
      if (status != 'delivered')
      Row(children: [
        if (status == 'pending') ...[
          Expanded(
            child: TextButton(
              onPressed: () => _confirmAction(context, 'cancelled', remaining,
                  isRtl ? 'تم رفض الطلب' : 'Order declined'),
              child: Text(isRtl ? 'رفض' : 'Decline', style: TextStyle(color: Colors.red.shade700)),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 2,
            child: hasFeesSet 
              ? (buyerApprovedFees
                  ? FilledButton.icon(
                      style: FilledButton.styleFrom(backgroundColor: Colors.green.shade600),
                      onPressed: () => _confirmAction(context, 'processing', remaining,
                          isRtl ? 'تم قبول الطلب!' : 'Order accepted!'),
                      icon: const Icon(Icons.check, size: 18),
                      label: Text(isRtl ? 'تأكيد وقبول الطلب' : 'Accept Order'),
                    )
                  : FilledButton.icon(
                      style: FilledButton.styleFrom(backgroundColor: Colors.orange.shade600),
                      onPressed: null, // Disabled, waiting for buyer
                      icon: const Icon(Icons.hourglass_empty, size: 18),
                      label: Text(isRtl ? 'بانتظار موافقة المشتري' : 'Awaiting Buyer'),
                    )
                )
              : FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: Colors.blue.shade600),
                  onPressed: () => _showSetFeesDialog(context, order),
                  icon: const Icon(Icons.local_shipping, size: 18),
                  label: Text(isRtl ? 'تحديد رسوم التوصيل' : 'Set Shipping Fees'),
                ),
          ),
        ],
        if (status == 'processing')
          Expanded(
            child: FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: Colors.indigo.shade600),
              onPressed: () => _confirmAction(context, 'shipped', remaining,
                  isRtl ? 'تم تأكيد الشحن!' : 'Marked as shipped!'),
              icon: const Icon(Icons.local_shipping, size: 18),
              label: Text(isRtl ? 'تم الشحن' : 'Mark Shipped'),
            ),
          ),
        if (status == 'shipped')
          Expanded(
            child: FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: Colors.green.shade700),
              onPressed: () => _confirmAction(context, 'delivered', remaining,
                  isRtl ? 'تم تأكيد التسليم!' : 'Delivered!'),
              icon: const Icon(Icons.check_circle, size: 18),
              label: Text(isRtl ? 'تأكيد التسليم' : 'Confirm Delivery'),
            ),
          ),
      ]),
    ]);
  }

  void _showSetFeesDialog(BuildContext context, Map<String, dynamic> order) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final transportController = TextEditingController();
    final offloadingController = TextEditingController();
    bool isSaving = false;

    // Check if another order in the same group already has approved transport
    final notes = order['notes']?.toString() ?? '';
    final groupMatch = RegExp(r'\[طلب مجمّع: (GRP-[^|]+)').firstMatch(notes);
    final gId = groupMatch?.group(1)?.trim();
    if (gId != null && gId.isNotEmpty) {
      try {
        final otherOrders = await Supabase.instance.client
            .from('orders')
            .select('id, transport_fee, buyer_approved_fees, notes')
            .eq('buyer_id', order['buyer_id'])
            .neq('id', order['id'])
            .not('status', 'eq', 'cancelled');
        final grouped = otherOrders.where((o) {
          final oNotes = o['notes']?.toString() ?? '';
          return oNotes.contains(gId);
        }).toList();
        final alreadyApproved = grouped.any((o) =>
            o['buyer_approved_fees'] == true && (double.tryParse(o['transport_fee']?.toString() ?? '0') ?? 0) > 0);
        if (alreadyApproved && context.mounted) {
          // Transport already approved in another order — auto-set to 0
          showDialog(
            context: context,
            builder: (ctx) => AlertDialog(
              icon: const Icon(Icons.check_circle_rounded, color: Color(0xFF16A34A), size: 48),
              title: Text(isRtl ? 'الترحيل مشمول' : 'Transport Covered'),
              content: Text(
                isRtl
                    ? 'تم اعتماد رسوم الترحيل في طلب آخر من نفس المجموعة. سيتم تعيين رسوم الترحيل = 0 لهذا الطلب تلقائياً.'
                    : 'Transport fees were already approved in another order from the same group. Transport fee will be set to 0 for this order.',
                textAlign: TextAlign.center,
              ),
              actions: [
                TextButton(onPressed: () => Navigator.pop(ctx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                FilledButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    final double oldTotal = double.tryParse(order['total_amount'].toString()) ?? 0.0;
                    await Supabase.instance.client.from('orders').update({
                      'transport_fee': 0,
                      'offloading_fee': 0,
                      'total_amount': oldTotal,
                      'buyer_approved_fees': true,
                      'notes': _stampStaffInNotes((order['notes'] ?? '') + '\n✅ الترحيل مشمول في طلب آخر من نفس المجموعة'),
                      if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
                      if (order['store_id'] != null) 'store_id': order['store_id'],
                    }).eq('id', order['id']).eq('store_id', order['store_id']);
                    NotificationSoundService.play();
                    if (context.mounted) {
                      ref.invalidate(merchantOrdersProvider);
                    }
                  },
                  child: Text(isRtl ? 'تأكيد (0 رسوم)' : 'Confirm (0 fees)'),
                ),
              ],
            ),
          );
          return;
        }
      } catch (_) {}
    }

    if (!context.mounted) return;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) => AlertDialog(
          title: Text(isRtl ? 'تحديد رسوم التوصيل' : 'Set Shipping Fees'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                isRtl ? 'بناءً على عنوان المشتري، قم بتحديد رسوم الترحيل والتنزيل:' : 'Based on the buyer address, set the transport and offloading fees:',
                style: const TextStyle(fontSize: 13),
              ),
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(8),
                width: double.infinity,
                decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(8)),
                child: Text(order['shipping_address'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: transportController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: isRtl ? 'رسوم الترحيل' : 'Transport Fee',
                  border: const OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: offloadingController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: InputDecoration(
                  labelText: isRtl ? 'رسوم التنزيل' : 'Offloading Fee',
                  border: const OutlineInputBorder(),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: isSaving ? null : () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              onPressed: isSaving ? null : () async {
                final tFee = double.tryParse(transportController.text) ?? 0.0;
                final oFee = double.tryParse(offloadingController.text) ?? 0.0;
                
                setState(() => isSaving = true);
                try {
                  final double oldTotal = double.tryParse(order['total_amount'].toString()) ?? 0.0;
                  final double prevTransport = double.tryParse(order['transport_fee']?.toString() ?? '0') ?? 0.0;
                  final double prevOffloading = double.tryParse(order['offloading_fee']?.toString() ?? '0') ?? 0.0;
                  final double baseTotal = oldTotal - prevTransport - prevOffloading;
                  final double newTotal = baseTotal + tFee + oFee;
                  
                  final String curNotes = order['notes']?.toString() ?? '';
                  final String stampedNotes = _stampStaffInNotes(curNotes);
                  await Supabase.instance.client.from('orders').update({
                    'transport_fee': tFee.round(),
                    'offloading_fee': oFee.round(),
                    'total_amount': newTotal,
                    'buyer_approved_fees': false,
                    if (stampedNotes != curNotes) 'notes': stampedNotes,
                    if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
                    if (order['store_id'] != null) 'store_id': order['store_id'],
                  }).eq('id', order['id']).eq('store_id', order['store_id']);
                  
                  if (ctx.mounted) {
                    NotificationSoundService.play();
                    Navigator.pop(ctx);
                    AppNotification.showSuccess(
                      context,
                      isRtl ? 'تم إرسال الرسوم للمشتري للموافقة' : 'Fees sent to buyer for approval',
                    );
                  }
                } catch (e) {
                  setState(() => isSaving = false);
                  if (ctx.mounted) {
                    AppNotification.showError(context, e.toString());
                  }
                }
              },
              child: isSaving ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : Text(isRtl ? 'حفظ وإرسال' : 'Save & Send'),
            ),
          ],
        ),
      ),
    );
  }

  Future<List<Map<String, dynamic>>> _fetchGroupedOrderItems(String currentOrderId, String groupId, String buyerId) async {
    try {
      // Fetch all orders from same buyer that contain the same group_id
      final allOrders = await Supabase.instance.client
          .from('orders')
          .select('id, notes, order_items(*)')
          .eq('buyer_id', buyerId)
          .neq('id', currentOrderId)
          .not('status', 'eq', 'cancelled');
      // Filter to those with matching group_id in notes
      final groupedOrders = allOrders.where((o) {
        final oNotes = o['notes']?.toString() ?? '';
        return oNotes.contains(groupId);
      }).toList();
      // Collect all items from grouped orders (name + quantity only)
      final List<Map<String, dynamic>> otherItems = [];
      for (var o in groupedOrders) {
        final oItems = o['order_items'] as List<dynamic>? ?? [];
        for (var item in oItems) {
          otherItems.add({
            'product_name': item['product_name']?.toString() ?? 'منتج',
            'quantity': item['quantity']?.toString() ?? '1',
          });
        }
      }
      return otherItems;
    } catch (e) {
      debugPrint('Error fetching grouped items: $e');
      return [];
    }
  }

  void _showMerchantReceiptDialog(BuildContext context, dynamic order, List<dynamic> payments, double remaining, double total, double paid) {
    List<Map<String, dynamic>> parsedReceipts = [];
    final notes = order['notes']?.toString() ?? '';
    
    if (notes.contains(RegExp(r'(?<!\w)receipt_url:'))) {
      final parts = notes.split(RegExp(r'(?<!\w)receipt_url:'));
      for (int i = 1; i < parts.length; i++) {
        final part = parts[i];
        final url = part.split('|')[0].trim().split(' ')[0].trim();
        
        String refNum = '';
        String bank = '';
        String amountStr = '';
        double amt = 0;
        
        if (part.contains('ref:')) refNum = part.split('ref:')[1].split('|')[0].trim().split('\n')[0].trim();
        if (part.contains('bank:')) bank = part.split('bank:')[1].split('|')[0].trim().split('\n')[0].trim();
        if (part.contains('amount:')) {
            amountStr = part.split('amount:')[1].split('|')[0].trim().split('\n')[0].trim();
            amt = double.tryParse(amountStr) ?? 0;
        }
        
        parsedReceipts.add({
          'url': url,
          'ref': refNum,
          'bank': bank,
          'amount': amt,
        });
      }
    } else {
      // Fallback for old single receipt method or empty
      String? fallbackUrl;
      for (var p in payments) {
        final acc = p['recipient_account']?.toString() ?? '';
        if (acc.startsWith('http')) {
          fallbackUrl = acc;
          break;
        }
      }
      if (fallbackUrl != null) {
        parsedReceipts.add({
          'url': fallbackUrl,
          'ref': '',
          'bank': 'Unknown',
          'amount': remaining,
        });
      }
    }

    double totalReceiptsAmount = 0;
    for (var r in parsedReceipts) {
      totalReceiptsAmount += (r['amount'] as num).toDouble();
    }
    
    // Fallback if no amounts parsed properly
    if (totalReceiptsAmount == 0) totalReceiptsAmount = remaining;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.verified_user_rounded, color: Color(0xFF0F766E)),
            const SizedBox(width: 8),
            Text(isRtl ? 'إشعارات الدفع المرفوعة' : 'Uploaded Payment Receipts'),
          ],
        ),
        content: SizedBox(
          width: 450,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (parsedReceipts.isEmpty)
                  const Text('No receipts found.'),
                  
                for (int i = 0; i < parsedReceipts.length; i++) ...[
                  if (i > 0) const Divider(height: 30),
                  Text(
                    isRtl ? 'الإشعار رقم ${i + 1}' : 'Receipt #${i + 1}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F766E)),
                  ),
                  const SizedBox(height: 8),
                  
                  if (parsedReceipts[i]['url'] != null && parsedReceipts[i]['url'].toString().isNotEmpty)
                    InkWell(
                      borderRadius: BorderRadius.circular(12),
                      onTap: () {
                        showInteractiveImageViewer(
                          context,
                          imageUrl: parsedReceipts[i]['url'],
                          title: isRtl ? 'إشعار التحويل رقم ${i + 1}' : 'Transfer Receipt #${i + 1}',
                        );
                      },
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Stack(
                          children: [
                            Container(
                              width: double.infinity,
                              constraints: const BoxConstraints(maxHeight: 280),
                              color: const Color(0xFF0F172A),
                              child: Image.network(
                                parsedReceipts[i]['url'],
                                fit: BoxFit.contain,
                                errorBuilder: (c, e, s) => const Center(
                                  child: Padding(
                                    padding: EdgeInsets.all(32),
                                    child: Icon(Icons.broken_image, size: 80, color: Colors.grey),
                                  ),
                                ),
                              ),
                            ),
                            Positioned(
                              bottom: 8,
                              right: isRtl ? 8 : null,
                              left: isRtl ? null : 8,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                decoration: BoxDecoration(
                                  color: Colors.black.withValues(alpha: 0.75),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: Colors.white24),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.zoom_in_rounded, color: Colors.white, size: 16),
                                    const SizedBox(width: 4),
                                    Text(
                                      isRtl ? 'انقر لتكبير وتدقيق الإشعار' : 'Tap to zoom & inspect',
                                      style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(8)),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'البنك:' : 'Bank:'),
                            Text('${parsedReceipts[i]['bank']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'رقم المرجع (Ref):' : 'Reference (Ref):'),
                            Text('${parsedReceipts[i]['ref']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'المبلغ المحول:' : 'Transferred Amount:'),
                            Text('${parsedReceipts[i]['amount']} ${isRtl ? 'ج.س' : 'SDG'}', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green)),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
                
                const SizedBox(height: 20),
                const Divider(),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(isRtl ? 'إجمالي الدفعات:' : 'Total Payments:', style: const TextStyle(fontWeight: FontWeight.bold)),
                    Text('$totalReceiptsAmount ${isRtl ? 'ج.س' : 'SDG'}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F766E))),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(isRtl ? 'المبلغ المطلوب:' : 'Required Amount:', style: const TextStyle(fontWeight: FontWeight.bold)),
                    Text('$remaining ${isRtl ? 'ج.س' : 'SDG'}', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.orange)),
                  ],
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              final reasonCtrl = TextEditingController();
              final quickReasons = isRtl
                  ? [
                      'صورة الإشعار غير واضحة',
                      'المبلغ غير مطابق لإجمالي الطلب',
                      'رقم المعاملة غير صحيح أو مكرر',
                      'التحويل لم يصل إلى الحساب البنكي',
                    ]
                  : [
                      'Receipt screenshot is not clear',
                      'Amount does not match order total',
                      'Invalid or duplicate transaction ref',
                      'Funds not received in bank account',
                    ];

              showDialog(
                context: context,
                builder: (rCtx) => StatefulBuilder(
                  builder: (rCtx, setDialogState) => AlertDialog(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    title: Row(
                      children: [
                        Icon(Icons.cancel_outlined, color: Colors.red.shade700, size: 26),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            isRtl ? 'رفض الإشعار وإخطار المشتري' : 'Reject Receipt & Notify Buyer',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.red.shade800),
                          ),
                        ),
                      ],
                    ),
                    content: SingleChildScrollView(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl
                                ? 'حدد أو اكتب سبب الرفض ليظهر للمشتري مع نغمة تنبيه فورية:'
                                : 'Select or write the rejection reason to notify the buyer:',
                            style: const TextStyle(fontSize: 13, color: Colors.black87),
                          ),
                          const SizedBox(height: 12),
                          // Quick Suggestion Chips
                          Wrap(
                            spacing: 6,
                            runSpacing: 6,
                            children: quickReasons.map((q) => ActionChip(
                              backgroundColor: reasonCtrl.text == q ? Colors.red.shade50 : const Color(0xFFF1F5F9),
                              side: BorderSide(color: reasonCtrl.text == q ? Colors.red.shade300 : const Color(0xFFCBD5E1)),
                              label: Text(
                                q,
                                style: TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: reasonCtrl.text == q ? FontWeight.bold : FontWeight.normal,
                                  color: reasonCtrl.text == q ? Colors.red.shade800 : Colors.blueGrey.shade800,
                                ),
                              ),
                              onPressed: () {
                                setDialogState(() {
                                  reasonCtrl.text = q;
                                });
                              },
                            )).toList(),
                          ),
                          const SizedBox(height: 12),
                          TextField(
                            controller: reasonCtrl,
                            maxLines: 3,
                            onChanged: (_) => setDialogState(() {}),
                            decoration: InputDecoration(
                              labelText: isRtl ? 'سبب الرفض المخصص' : 'Rejection Reason',
                              border: const OutlineInputBorder(),
                              hintText: isRtl ? 'اكتب سبب الرفض هنا...' : 'Write reason here...',
                            ),
                          ),
                        ],
                      ),
                    ),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(rCtx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                      FilledButton(
                        style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600),
                        onPressed: () async {
                          final reason = reasonCtrl.text.trim();
                          if (reason.isEmpty) {
                            AppNotification.showError(
                              context,
                              isRtl ? 'يرجى كتابة أو تحديد سبب الرفض أولاً' : 'Please enter a rejection reason first',
                            );
                            return;
                          }
                          Navigator.pop(rCtx);
                          try {
                            String updatedNotes = notes.replaceAll(RegExp(r'(?<!\w)receipt_url:'), 'rejected_receipt_url:');
                            if (updatedNotes.contains('rejection_reason:')) {
                              updatedNotes = updatedNotes.split('rejection_reason:')[0].trim();
                            }
                            updatedNotes = '$updatedNotes\nrejection_reason:$reason';

                            await Supabase.instance.client.from('orders').update({
                              'payment_status': 'unpaid',
                              'notes': _stampStaffInNotes(updatedNotes),
                              if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
                              if (order['store_id'] != null) 'store_id': order['store_id'],
                            }).eq('id', order['id'].toString()).eq('store_id', order['store_id'].toString());

                            if (context.mounted) {
                              ref.invalidate(merchantOrdersProvider);
                              AppNotification.showSuccess(
                                context,
                                isRtl ? 'تم رفض الإشعار وإشعار المشتري بالسبب بنجاح' : 'Receipt rejected and buyer notified',
                              );
                            }
                          } catch (e) {
                            if (context.mounted) {
                              AppNotification.showError(context, 'Error: $e');
                            }
                          }
                        },
                        child: Text(isRtl ? 'تأكيد الرفض وإشعار المشتري' : 'Confirm Reject & Notify'),
                      ),
                    ],
                  ),
                ),
              );
            },
            child: Text(isRtl ? 'رفض الإشعارات' : 'Reject Receipts', style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
          ),
          FilledButton.icon(
            icon: const Icon(Icons.check_circle_outline, size: 20),
            style: FilledButton.styleFrom(backgroundColor: Colors.green.shade700),
            label: Text(isRtl ? 'اعتماد الدفعات' : 'Approve Payments'),
            onPressed: () async {
              Navigator.pop(ctx);
              try {
                // Record payments for each uploaded receipt individually
                if (parsedReceipts.isNotEmpty) {
                  for (final r in parsedReceipts) {
                    final rAmt = (r['amount'] as num?)?.toDouble() ?? 0.0;
                    final bName = (r['bank']?.toString().isNotEmpty == true) ? r['bank'].toString() : 'bank_transfer';
                    await Supabase.instance.client.from('payments').insert({
                      'order_id': order['id'].toString(),
                      'amount': (rAmt > 0 ? rAmt : totalReceiptsAmount / parsedReceipts.length).round(),
                      'method': bName,
                      'reference_number': r['ref']?.toString().isNotEmpty == true ? r['ref'].toString() : null,
                      'bank_name': bName,
                      'recipient_account': r['url']?.toString().isNotEmpty == true ? r['url'].toString() : null,
                      'payment_destination': 'merchant_account',
                    });
                  }
                } else {
                  await Supabase.instance.client.from('payments').insert({
                    'order_id': order['id'].toString(),
                    'amount': totalReceiptsAmount.round(),
                    'method': 'bank_transfer',
                    'reference_number': null,
                    'bank_name': 'bank_transfer',
                    'recipient_account': null,
                    'payment_destination': 'merchant_account',
                  });
                }
                
                // If order was under_review with no recorded payments yet, basePaid is 0 to avoid duplication
                final double basePaid = (order['payment_status'] == 'under_review' && payments.isEmpty) ? 0.0 : paid;
                final newPaid = basePaid + totalReceiptsAmount;
                final newPaymentStatus = newPaid >= total ? 'paid' : 'partial';
                
                await Supabase.instance.client.from('orders').update({
                  'paid_amount': newPaid.round(),
                  'payment_status': newPaymentStatus,
                  'notes': _stampStaffInNotes(notes.replaceAll(RegExp(r'(?<!\w)receipt_url:'), 'approved_receipt_url:')),
                  if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
                  if (order['store_id'] != null) 'store_id': order['store_id'],
                }).eq('id', order['id'].toString()).eq('store_id', order['store_id'].toString());
                if (context.mounted) {
                  AppNotification.showSuccess(
                    context,
                    isRtl ? 'تم اعتماد الدفعات بنجاح!' : 'Payments approved!',
                  );
                }
              } catch (e) {
                if (context.mounted) {
                  AppNotification.showError(context, 'Error: $e');
                }
              }
            },
          ),
        ],
      ),
    );
  }

  void _confirmAction(BuildContext context, String newStatus, double remaining, String successMsg) {
    // Build warning message based on payment status
    String? warningMsg;
    if (remaining > 0) {
      final formatted = NumberFormat('#,##0').format(remaining);
      if (newStatus == 'processing') {
        warningMsg = isRtl
            ? '⚠️ تنبيه: المشتري لم يسدد المبلغ بعد!\nالمتبقي: $formatted ج.س\n\nهل تريد قبول الطلب رغم ذلك؟'
            : '⚠️ Warning: Buyer has NOT paid yet!\nRemaining: $formatted SDG\n\nAccept anyway?';
      } else if (newStatus == 'shipped') {
        warningMsg = isRtl
            ? '⚠️ تحذير: المشتري لم يسدد كامل المبلغ!\nالمتبقي: $formatted ج.س\n\nهل تريد شحن الطلب رغم وجود مبلغ متبقي؟'
            : '⚠️ Warning: Buyer has outstanding balance!\nRemaining: $formatted SDG\n\nShip anyway?';
      } else if (newStatus == 'delivered') {
        warningMsg = isRtl
            ? '🚨 تحذير مهم: المشتري لديه مبلغ متبقي!\nالمتبقي: $formatted ج.س\n\nتأكيد التسليم يعني أن البضاعة وصلت.\nهل أنت متأكد من تأكيد التسليم؟'
            : '🚨 Important: Buyer has unpaid balance!\nRemaining: $formatted SDG\n\nConfirming delivery means goods arrived.\nAre you sure?';
      } else if (newStatus == 'cancelled') {
        warningMsg = isRtl
            ? 'هل أنت متأكد من رفض هذا الطلب؟'
            : 'Are you sure you want to decline this order?';
      }
    } else if (newStatus == 'cancelled') {
      warningMsg = isRtl
          ? 'هل أنت متأكد من رفض هذا الطلب؟'
          : 'Are you sure you want to decline this order?';
    }

    if (warningMsg != null) {
      showDialog(
        context: context,
        builder: (dialogContext) => AlertDialog(
          title: Icon(
            remaining > 0 ? Icons.warning_amber_rounded : Icons.help_outline,
            size: 48,
            color: remaining > 0 ? Colors.orange.shade700 : Colors.blue.shade700,
          ),
          content: Text(warningMsg!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 15)),
          actionsAlignment: MainAxisAlignment.center,
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: newStatus == 'cancelled' ? Colors.red.shade600 : Colors.green.shade600,
              ),
              onPressed: () {
                Navigator.of(dialogContext).pop();
                _doUpdateStatus(context, newStatus, successMsg);
              },
              child: Text(isRtl ? 'نعم، متأكد' : 'Yes, Proceed'),
            ),
          ],
        ),
      );
    } else {
      _doUpdateStatus(context, newStatus, successMsg);
    }
  }

  Future<void> _doUpdateStatus(BuildContext context, String newStatus, String successMsg) async {
    try {
      final prevStatus = order['status']?.toString() ?? 'pending';
      final items = (order['order_items'] as List<dynamic>?) ?? [];

      // 1. Auto Stock Deduction on Order Acceptance (pending -> processing)
      if (newStatus == 'processing' && prevStatus == 'pending') {
        for (var item in items) {
          final productId = item['product_id']?.toString();
          final qty = (num.tryParse(item['quantity']?.toString() ?? '0') ?? 0).toInt();
          if (productId != null && qty > 0) {
            try {
              final prod = await Supabase.instance.client
                  .from('products')
                  .select('stock')
                  .eq('id', productId)
                  .maybeSingle();
              if (prod != null) {
                final currentStock = (num.tryParse(prod['stock']?.toString() ?? '0') ?? 0).toInt();
                final newStock = (currentStock - qty).clamp(0, 999999999);
                await Supabase.instance.client
                    .from('products')
                    .update({'stock': newStock})
                    .eq('id', productId);
              }
            } catch (err) {
              debugPrint('Error deducting stock for product $productId: $err');
            }
          }
        }
      } 
      // 2. Auto Stock Restoration on Order Cancellation (if was previously accepted or shipped)
      else if (newStatus == 'cancelled' && (prevStatus == 'processing' || prevStatus == 'shipped')) {
        for (var item in items) {
          final productId = item['product_id']?.toString();
          final qty = (num.tryParse(item['quantity']?.toString() ?? '0') ?? 0).toInt();
          if (productId != null && qty > 0) {
            try {
              final prod = await Supabase.instance.client
                  .from('products')
                  .select('stock')
                  .eq('id', productId)
                  .maybeSingle();
              if (prod != null) {
                final currentStock = (num.tryParse(prod['stock']?.toString() ?? '0') ?? 0).toInt();
                final newStock = currentStock + qty;
                await Supabase.instance.client
                    .from('products')
                    .update({'stock': newStock})
                    .eq('id', productId);
              }
            } catch (err) {
              debugPrint('Error restoring stock for product $productId: $err');
            }
          }
        }
      }

      final String curNotes = order['notes']?.toString() ?? '';
      final String stampedNotes = _stampStaffInNotes(curNotes);

      await Supabase.instance.client
          .from('orders')
          .update({
            'status': newStatus,
            if (stampedNotes != curNotes) 'notes': stampedNotes,
            if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
            if (order['store_id'] != null) 'store_id': order['store_id'],
          })
          .eq('id', order['id'].toString())
          .eq('store_id', order['store_id'].toString());

      if (context.mounted) {
        ref.invalidate(merchantOrdersProvider);
        ref.invalidate(merchantInventoryProvider);
        try { ref.invalidate(productsProvider); } catch (_) {}
        final orderId = order['id']?.toString();
        if (newStatus == 'shipped') {
          AppNotification.showShipped(
            context,
            orderId: orderId,
            message: successMsg,
          );
        } else if (newStatus == 'delivered') {
          AppNotification.showDelivered(
            context,
            orderId: orderId,
            message: successMsg,
          );
        } else if (newStatus == 'processing') {
          AppNotification.showProcessing(
            context,
            orderId: orderId,
            message: isRtl ? '$successMsg (تم خصم الكميات من المخزون تلقائياً)' : successMsg,
          );
        } else {
          AppNotification.showSuccess(context, successMsg);
        }
      }
    } catch (e) {
      if (context.mounted) {
        AppNotification.showError(context, 'Error: $e');
      }
    }
  }

  void _showPaymentDialog(BuildContext context, double remaining, double total, double paid) async {
    final amountController = TextEditingController(text: remaining > 0 ? remaining.toStringAsFixed(0) : '');
    final refNumberController = TextEditingController();
    final notesController = TextEditingController();
    
    // Load merchant's registered bank info
    String merchantBankName = '';
    String merchantAccountNumber = '';
    final user = Supabase.instance.client.auth.currentUser;
    if (user != null) {
      try {
        final st = await Supabase.instance.client.from('stores').select('bank_name, bank_account_number').eq('owner_id', user.id).limit(1).maybeSingle();
        merchantBankName = st?['bank_name']?.toString() ?? '';
        merchantAccountNumber = st?['bank_account_number']?.toString() ?? '';
        if (merchantBankName.isEmpty) {
          final us = await Supabase.instance.client.from('users').select('bank_name, bank_account_number').eq('id', user.id).maybeSingle();
          merchantBankName = us?['bank_name']?.toString() ?? '';
          merchantAccountNumber = us?['bank_account_number']?.toString() ?? '';
        }
      } catch (_) {}
    }

    String selectedDestination = 'merchant_account'; // 'merchant_account', 'platform_account', 'cash', 'other_bank'
    String selectedMethod = 'bankak'; // 'bankak', 'ocash', 'fawry', 'cash', 'bank_transfer'
    String customBankName = 'بنك الخرطوم (Bank of Khartoum - Bankak)';
    final formKey = GlobalKey<FormState>();

    final sudaneseBanks = [
      'بنك الخرطوم (Bank of Khartoum - Bankak)',
      'بنك أمدرمان الوطني (Omdurman National Bank - O-Cash)',
      'بنك فيصل الإسلامي (Faisal Islamic Bank - Fawry)',
      'بنك النيلين (Al Nilein Bank)',
      'البنك الأهلي السوداني (Al Ahli Bank of Sudan)',
      'بنك العمال الوطني (Workers National Bank)',
      'بنك الجزيرة السوداني الأردني (Al Jazeera Bank)',
      'بنك الثروة الحيوانية (Animal Resources Bank)',
      'بنك المال المتحد (United Capital Bank)',
      'بنك التضامن الإسلامي (Tadamon Islamic Bank)',
      'بنك تنمية الصادرات (Export Development Bank)',
      'بنك الشمال الإسلامي (Al Shamal Islamic Bank)',
      'بنك البركة السوداني (Al Baraka Bank Sudan)',
      'أخرى (Other Bank)'
    ];

    if (!context.mounted) return;

    showDialog(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(10)),
                child: const Icon(Icons.receipt_long_rounded, color: Colors.green, size: 24),
              ),
              const SizedBox(width: 10),
              Text(isRtl ? 'تأكيد استلام دفعة' : 'Confirm Payment', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
            ],
          ),
          content: SizedBox(
            width: 440,
            child: Form(
              key: formKey,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Amount Summary Box
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.red.shade200)),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(isRtl ? 'المبلغ المتبقي على العميل:' : 'Remaining Balance:', style: TextStyle(color: Colors.red.shade900, fontWeight: FontWeight.w600, fontSize: 13)),
                          Text('${NumberFormat('#,##0').format(remaining)} ${isRtl ? 'ج.س' : 'SDG'}', style: TextStyle(color: Colors.red.shade900, fontWeight: FontWeight.bold, fontSize: 15)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Amount Field
                    TextFormField(
                      controller: amountController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: isRtl ? 'المبلغ المستلم (ج.س) *' : 'Amount Received *',
                        prefixIcon: const Icon(Icons.monetization_on_outlined),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      validator: (v) {
                        if (v == null || v.trim().isEmpty) return isRtl ? 'الرجاء إدخال المبلغ' : 'Enter amount';
                        final amt = double.tryParse(v.trim()) ?? 0;
                        if (amt <= 0) return isRtl ? 'يجب إدخال مبلغ صالح' : 'Invalid amount';
                        return null;
                      },
                    ),
                    const SizedBox(height: 14),

                    // Destination Account (أين تم استلام المبلغ؟)
                    Text(isRtl ? 'الحساب / الجهة المستلمة للمبلغ *' : 'Received Into *', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 8),
                    DropdownButtonFormField<String>(
                      value: selectedDestination,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.account_balance),
                      ),
                      items: [
                        DropdownMenuItem(
                          value: 'merchant_account',
                          child: Text(
                            merchantBankName.isNotEmpty
                                ? (isRtl ? 'حسابي بالمتجر ($merchantBankName)' : 'My Merchant Account ($merchantBankName)')
                                : (isRtl ? 'حسابي البنكي كتاجر' : 'My Merchant Bank Account'),
                            style: const TextStyle(fontSize: 13),
                          ),
                        ),
                        DropdownMenuItem(
                          value: 'platform_account',
                          child: Text(isRtl ? 'حساب منصة السيطرة الرئيسي' : 'Magaiees Platform Account', style: const TextStyle(fontSize: 13)),
                        ),
                        DropdownMenuItem(
                          value: 'cash',
                          child: Text(isRtl ? 'نقداً (استلام باليد)' : 'Cash (In Person)', style: const TextStyle(fontSize: 13)),
                        ),
                        DropdownMenuItem(
                          value: 'other_bank',
                          child: Text(isRtl ? 'حساب بنكي آخر' : 'Other Bank Account', style: const TextStyle(fontSize: 13)),
                        ),
                      ],
                      onChanged: (v) => setDialogState(() {
                        selectedDestination = v ?? 'merchant_account';
                        if (selectedDestination == 'cash') selectedMethod = 'cash';
                      }),
                    ),
                    const SizedBox(height: 14),

                    // If Other Bank Selected
                    if (selectedDestination == 'other_bank') ...[
                      DropdownButtonFormField<String>(
                        value: customBankName,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'اسم البنك المستلم *' : 'Bank Name *',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.account_balance_outlined),
                        ),
                        items: sudaneseBanks.map((b) => DropdownMenuItem(value: b, child: Text(b, style: const TextStyle(fontSize: 12)))).toList(),
                        onChanged: (v) => setDialogState(() => customBankName = v ?? customBankName),
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Payment Method (طريقة الدفع)
                    if (selectedDestination != 'cash') ...[
                      DropdownButtonFormField<String>(
                        value: selectedMethod,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'طريقة التحويل / التطبيق *' : 'Payment Method / App *',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.phone_android),
                        ),
                        items: [
                          DropdownMenuItem(value: 'bankak', child: Text(isRtl ? 'تطبيق بنكك (بنك الخرطوم)' : 'Bankak (Bank of Khartoum)')),
                          DropdownMenuItem(value: 'ocash', child: Text(isRtl ? 'أوكاش (بنك أمدرمان الوطني)' : 'O-Cash (Omdurman Bank)')),
                          DropdownMenuItem(value: 'fawry', child: Text(isRtl ? 'فوري (بنك فيصل الإسلامي)' : 'Fawry (Faisal Bank)')),
                          DropdownMenuItem(value: 'bank_transfer', child: Text(isRtl ? 'تحويل بنكي مباشر' : 'Direct Bank Transfer')),
                          DropdownMenuItem(value: 'cheque', child: Text(isRtl ? 'شيك مصرفي' : 'Cheque')),
                        ],
                        onChanged: (v) => setDialogState(() => selectedMethod = v ?? 'bankak'),
                      ),
                      const SizedBox(height: 14),

                      // Reference Number (رقم الإشعار) ⭐ إلزامي ومهم جداً
                      TextFormField(
                        controller: refNumberController,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'رقم إشعار التحويل البنكي / الرقم المرجعي *' : 'Transaction / Reference No. *',
                          hintText: isRtl ? 'مثال: 123456789' : 'e.g. 123456789',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.tag_rounded, color: Colors.blue),
                        ),
                        validator: (v) {
                          if (selectedDestination != 'cash' && (v == null || v.trim().isEmpty)) {
                            return isRtl ? 'الرجاء إدخال رقم إشعار التحويل' : 'Please enter reference number';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Notes (ملاحظات)
                    TextFormField(
                      controller: notesController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'ملاحظات إضافية (اختياري)' : 'Notes (Optional)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.notes_rounded),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: Colors.green.shade700,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: () async {
                if (!formKey.currentState!.validate()) return;
                final amount = double.tryParse(amountController.text.trim()) ?? 0;
                if (amount <= 0) return;

                final refNo = refNumberController.text.trim();
                final notes = notesController.text.trim();
                
                String resolvedBank = merchantBankName;
                String resolvedAccount = merchantAccountNumber;
                if (selectedDestination == 'platform_account') {
                  resolvedBank = 'حساب منصة السيطرة';
                  resolvedAccount = 'Platform Account';
                } else if (selectedDestination == 'cash') {
                  resolvedBank = 'نقداً';
                  resolvedAccount = 'Cash';
                } else if (selectedDestination == 'other_bank') {
                  resolvedBank = customBankName;
                }

                try {
                  await Supabase.instance.client.from('payments').insert({
                    'order_id': order['id'].toString(),
                    'amount': amount,
                    'method': selectedMethod,
                    'reference_number': refNo.isNotEmpty ? refNo : null,
                    'bank_name': resolvedBank.isNotEmpty ? resolvedBank : null,
                    'recipient_account': resolvedAccount.isNotEmpty ? resolvedAccount : null,
                    'payment_destination': selectedDestination,
                    'note': [
                      if (refNo.isNotEmpty) 'إشعار: $refNo',
                      if (resolvedBank.isNotEmpty) 'البنك: $resolvedBank',
                      if (notes.isNotEmpty) notes,
                    ].join(' - '),
                  });

                  final newPaid = paid + amount;
                  final newPaymentStatus = newPaid >= total ? 'paid' : 'partial';

                  final String curNotes = order['notes']?.toString() ?? '';
                  final String stampedNotes = _stampStaffInNotes(curNotes);

                  await Supabase.instance.client.from('orders').update({
                    'paid_amount': newPaid.round(),
                    'payment_status': newPaymentStatus,
                    'payment_method': selectedMethod,
                    if (stampedNotes != curNotes) 'notes': stampedNotes,
                    if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
                    if (order['store_id'] != null) 'store_id': order['store_id'],
                  }).eq('id', order['id'].toString()).eq('store_id', order['store_id'].toString());

                  if (dialogContext.mounted) Navigator.of(dialogContext).pop();
                  if (context.mounted) {
                    ref.invalidate(merchantOrdersProvider);
                    AppNotification.showSuccess(
                      context,
                      isRtl ? 'تم تسجيل الدفعة ورقم الإشعار بنجاح!' : 'Payment & Reference recorded successfully!',
                    );
                  }
                } catch (e) {
                  if (context.mounted) {
                    AppNotification.showError(
                      context,
                      'Error: $e',
                    );
                  }
                }
              },
              child: Text(isRtl ? 'تأكيد واستلام' : 'Confirm Payment'),
            ),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String s) {
    switch (s) {
      case 'pending': return Colors.orange.shade700;
      case 'processing': return Colors.blue.shade700;
      case 'shipped': return Colors.indigo.shade700;
      case 'delivered': return Colors.green.shade700;
      case 'cancelled': return Colors.red.shade700;
      default: return Colors.grey.shade700;
    }
  }

  String _statusText(String s) {
    switch (s) {
      case 'pending': return isRtl ? 'قيد الانتظار' : 'Pending';
      case 'processing': return isRtl ? 'مقبول - قيد المعالجة' : 'Accepted';
      case 'shipped': return isRtl ? 'تم الشحن' : 'Shipped';
      case 'delivered': return isRtl ? 'تم التسليم' : 'Delivered';
      case 'cancelled': return isRtl ? 'ملغي' : 'Cancelled';
      default: return s;
    }
  }
}

class _MerchantChatButton extends ConsumerStatefulWidget {
  final Map<String, dynamic> order;
  final bool isRtl;

  const _MerchantChatButton({
    required this.order,
    required this.isRtl,
  });

  @override
  ConsumerState<_MerchantChatButton> createState() => _MerchantChatButtonState();
}

class _MerchantChatButtonState extends ConsumerState<_MerchantChatButton> {
  bool _isLoading = false;

  Future<void> _handleChat() async {
    if (_isLoading) return;

    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) {
      if (mounted) {
        AppNotification.showError(
          context,
          widget.isRtl ? 'يجب تسجيل الدخول أولاً' : 'Please log in first',
        );
      }
      return;
    }

    final buyerId = widget.order['buyer_id']?.toString() ??
        widget.order['buyer']?['id']?.toString();

    if (buyerId == null || buyerId.isEmpty || buyerId == 'guest_user') {
      if (mounted) {
        final buyerPhone = widget.order['buyer']?['phone']?.toString() ??
            widget.order['shipping_address']?.toString() ?? '';
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            icon: const Icon(Icons.person_off_outlined, color: Colors.orange, size: 40),
            title: Text(widget.isRtl ? 'طلب زائر بدون حساب' : 'Guest Order'),
            content: Text(
              widget.isRtl
                  ? 'هذا الطلب تم تنفيذه بواسطة زائر دون حساب مستخدم دائم.\nيمكنك التواصل مع المشتري هاتفياً:\n$buyerPhone'
                  : 'This order was placed by a guest without an account.\nYou can contact them by phone:\n$buyerPhone',
              textAlign: TextAlign.center,
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(widget.isRtl ? 'حسناً' : 'OK'),
              ),
            ],
          ),
        );
      }
      return;
    }

    if (buyerId == user.id) {
      if (mounted) {
        AppNotification.showInfo(
          context,
          widget.isRtl ? 'لا يمكنك بدء محادثة مع نفسك' : 'Cannot chat with yourself',
        );
      }
      return;
    }

    setState(() => _isLoading = true);

    try {
      final storeId = widget.order['store_id']?.toString();
      final roomId = await ChatService.getOrCreateRoom(
        buyerId: buyerId,
        merchantId: user.id,
        storeId: storeId,
      ).timeout(const Duration(seconds: 10));

      if (mounted) {
        await context.push('/merchant/chat/room/$roomId');
      }
    } catch (e) {
      debugPrint('Error opening chat with buyer: $e');
      if (mounted) {
        AppNotification.showError(
          context,
          widget.isRtl
              ? 'تعذر فتح المحادثة، يرجى المحاولة مرة أخرى'
              : 'Could not open chat, please try again',
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
    return OutlinedButton.icon(
      onPressed: _isLoading ? null : _handleChat,
      icon: _isLoading
          ? const SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF1E3A8A)),
            )
          : const Icon(Icons.chat_bubble_outline_rounded, size: 18),
      label: Text(
        _isLoading
            ? (widget.isRtl ? 'جاري الفتح...' : 'Opening...')
            : (widget.isRtl ? 'تواصل مع المشتري' : 'Chat Buyer'),
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
        overflow: TextOverflow.ellipsis,
      ),
      style: OutlinedButton.styleFrom(
        foregroundColor: const Color(0xFF1E3A8A),
        side: const BorderSide(color: Color(0xFF93C5FD)),
        backgroundColor: const Color(0xFFEFF6FF),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }
}

class _MerchantCallButton extends ConsumerStatefulWidget {
  final Map<String, dynamic> order;
  final bool isRtl;

  const _MerchantCallButton({
    required this.order,
    required this.isRtl,
  });

  @override
  ConsumerState<_MerchantCallButton> createState() => _MerchantCallButtonState();
}

class _MerchantCallButtonState extends ConsumerState<_MerchantCallButton> {
  bool _isLoading = false;

  Future<void> _handleCall() async {
    if (_isLoading) return;

    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) {
      if (mounted) {
        AppNotification.showError(
          context,
          widget.isRtl ? 'يجب تسجيل الدخول أولاً' : 'Please log in first',
        );
      }
      return;
    }

    final buyerId = widget.order['buyer_id']?.toString() ??
        widget.order['buyer']?['id']?.toString();

    if (buyerId == null || buyerId.isEmpty || buyerId == 'guest_user') {
      if (mounted) {
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            icon: const Icon(Icons.person_off_outlined, color: Colors.orange, size: 40),
            title: Text(widget.isRtl ? 'طلب زائر بدون حساب' : 'Guest Order'),
            content: Text(
              widget.isRtl
                  ? 'هذا الطلب تم تنفيذه بواسطة زائر دون حساب مستخدم دائم.\nالمكالمات داخل التطبيق متاحة للمستخدمين المسجلين فقط.'
                  : 'This order was placed by a guest without an account.\nIn-app calls are only available for registered users.',
              textAlign: TextAlign.center,
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(widget.isRtl ? 'حسناً' : 'OK'),
              ),
            ],
          ),
        );
      }
      return;
    }

    if (buyerId == user.id) {
      if (mounted) {
        AppNotification.showInfo(
          context,
          widget.isRtl ? 'لا يمكنك الاتصال بنفسك' : 'Cannot call yourself',
        );
      }
      return;
    }

    setState(() => _isLoading = true);

    try {
      final storeId = widget.order['store_id']?.toString();
      final roomId = await ChatService.getOrCreateRoom(
        buyerId: buyerId,
        merchantId: user.id,
        storeId: storeId,
      ).timeout(const Duration(seconds: 10));

      final buyerData = widget.order['buyer'] as Map<String, dynamic>?;
      final buyerName = NameFormatter.formatName(
        name: buyerData?['name']?.toString(),
        nameAr: buyerData?['name_ar']?.toString(),
        isRtl: widget.isRtl,
        fallback: widget.isRtl ? 'العميل' : 'Customer',
      );

      if (mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => CallScreen(
              callID: roomId,
              currentUser: user,
              callerName: buyerName,
              targetUserId: buyerId,
            ),
          ),
        );
      }
    } catch (e) {
      debugPrint('Error starting in-app call with buyer: $e');
      if (mounted) {
        AppNotification.showError(
          context,
          widget.isRtl
              ? 'تعذر بدء المكالمة، يرجى التأكد من الاتصال بالإنترنت'
              : 'Could not start call, please check connection',
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
    return OutlinedButton.icon(
      onPressed: _isLoading ? null : _handleCall,
      icon: _isLoading
          ? const SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF047857)),
            )
          : const Icon(Icons.call_rounded, size: 18),
      label: Text(
        _isLoading
            ? (widget.isRtl ? 'جاري الاتصال...' : 'Calling...')
            : (widget.isRtl ? 'اتصال بالتطبيق' : 'In-App Call'),
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
        overflow: TextOverflow.ellipsis,
      ),
      style: OutlinedButton.styleFrom(
        foregroundColor: const Color(0xFF047857),
        side: const BorderSide(color: Color(0xFFA7F3D0)),
        backgroundColor: const Color(0xFFECFDF5),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }
}

