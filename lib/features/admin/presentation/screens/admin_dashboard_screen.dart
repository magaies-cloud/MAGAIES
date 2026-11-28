import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:go_router/go_router.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/domain/models/subscriber_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/admin/orders/application/orders_provider.dart';
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';
import 'package:magaiees_marketplace/features/admin/finance/application/admin_finance_provider.dart';
import 'package:magaiees_marketplace/features/admin/digital_broker/application/admin_digital_broker_provider.dart';
import 'package:magaiees_marketplace/features/admin/services/application/admin_services_provider.dart';
import 'package:magaiees_marketplace/core/utils/phone_formatter.dart';

class _StoreSalesStat {
  final String storeName;
  final double totalSales;
  final int ordersCount;
  final bool isVerified;

  const _StoreSalesStat({
    required this.storeName,
    required this.totalSales,
    required this.ordersCount,
    this.isVerified = true,
  });
}

class _TableColumnSpec {
  final String label;
  final int flex;
  final bool alignEnd;

  const _TableColumnSpec({
    required this.label,
    required this.flex,
    this.alignEnd = false,
  });
}

class _AppMovementItem {
  final String id;
  final String category; // 'order', 'broker', 'subscriber', 'payout', 'ad'
  final String title;
  final String subtitle;
  final String amountText;
  final String statusText;
  final Color color;
  final IconData icon;
  final DateTime timestamp;
  final String targetRoute;
  final OrderModel? orderPayload;

  const _AppMovementItem({
    required this.id,
    required this.category,
    required this.title,
    required this.subtitle,
    required this.amountText,
    required this.statusText,
    required this.color,
    required this.icon,
    required this.timestamp,
    required this.targetRoute,
    this.orderPayload,
  });
}

class AdminDashboardScreen extends ConsumerStatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  ConsumerState<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends ConsumerState<AdminDashboardScreen> {
  String _selectedMovementFilter = 'all'; // all, order, broker, subscriber, payout, ad

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshAllData();
    });
  }

  void _refreshAllData() {
    ref.read(adminOrdersProvider.notifier).loadOrders();
    ref.read(adminFinanceProvider.notifier).loadFinanceData();
    ref.read(adminServicesProvider.notifier).loadProviders();
    ref.invalidate(subscribersProvider);
    ref.invalidate(productsProvider);
    ref.invalidate(categoriesProvider);
    ref.invalidate(adsProvider);
    ref.invalidate(adminDigitalBrokerProvider);
    ref.invalidate(adminServicesInsightsProvider);
  }

  Color _getStatusColor(String status) {
    final s = status.toLowerCase();
    if (s == 'delivered' || s == 'completed' || s == 'مكتمل' || s == 'closed') {
      return const Color(0xFF10B981);
    }
    if (s == 'cancelled' || s == 'ملغي' || s == 'rejected') {
      return const Color(0xFFEF4444);
    }
    if (s == 'shipped' || s == 'مشحون' || s == 'contacted') {
      return const Color(0xFF3B82F6);
    }
    return const Color(0xFFF59E0B);
  }

  String _getStatusText(String status, bool isRtl) {
    final s = status.toLowerCase();
    if (s == 'delivered' || s == 'completed' || s == 'مكتمل') {
      return isRtl ? 'مكتمل ومسلم' : 'Delivered';
    }
    if (s == 'cancelled' || s == 'ملغي') {
      return isRtl ? 'ملغي' : 'Cancelled';
    }
    if (s == 'shipped' || s == 'مشحون') {
      return isRtl ? 'مشحون' : 'Shipped';
    }
    return isRtl ? 'قيد المعالجة' : 'Processing';
  }

  void _showOrderDetails(BuildContext context, WidgetRef ref, OrderModel order, bool isRtl) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return Directionality(
            textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
            child: AlertDialog(
              backgroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.receipt_long, color: Color(0xFF1E40AF)),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      '${isRtl ? "تفاصيل حركة التوريد والشراء" : "Order Details"} #${order.formattedOrderNumber}',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 16),
                    ),
                  ),
                ],
              ),
              content: SizedBox(
                width: 520,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildDetailRow(isRtl ? 'العميل / المشتري' : 'Customer', order.buyerName ?? order.buyerId),
                    if (order.buyerPhone != null && order.buyerPhone!.isNotEmpty)
                      _buildDetailRow(isRtl ? 'هاتف العميل' : 'Phone', PhoneFormatter.format(order.buyerPhone!), isLtr: true),
                    _buildDetailRow(
                      isRtl ? 'المتجر / المورد' : 'Store / Supplier',
                      order.storeName ?? (isRtl ? 'متجر معتمد' : 'Verified Store'),
                    ),
                    if (order.staffAssignedName != null)
                      _buildDetailRow(isRtl ? 'الموظف المسؤول' : 'Handled by', order.staffAssignedName!),
                    if (order.transportFee > 0)
                      _buildDetailRow(
                        isRtl ? 'أجور النقل والترحيل' : 'Transport Fee',
                        '${currencyFmt.format(order.transportFee)} ${isRtl ? "ج.س" : "SDG"}',
                      ),
                    if (order.offloadingFee > 0)
                      _buildDetailRow(
                        isRtl ? 'أجور العتالة والتفريغ' : 'Offloading Fee',
                        '${currencyFmt.format(order.offloadingFee)} ${isRtl ? "ج.س" : "SDG"}',
                      ),
                    _buildDetailRow(
                      isRtl ? 'إجمالي قيمة التوريد' : 'Total Amount',
                      '${currencyFmt.format(order.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
                      isBold: true,
                    ),
                    if (order.commissionAmount != null && order.commissionAmount! > 0)
                      _buildDetailRow(
                        isRtl ? 'عمولة المنصة' : 'Platform Commission',
                        '${currencyFmt.format(order.commissionAmount!)} ${isRtl ? "ج.س" : "SDG"} (${order.commissionRateApplied ?? 0}%)',
                      ),
                    _buildDetailRow(
                      isRtl ? 'طريقة الدفع' : 'Payment Method',
                      order.paymentMethod ?? (isRtl ? 'تحويل بنكي' : 'Bank Transfer'),
                    ),
                    _buildDetailRow(
                      isRtl ? 'تاريخ الطلب' : 'Date',
                      DateFormat('yyyy-MM-dd HH:mm').format(order.createdAt.toLocal()),
                    ),
                    const SizedBox(height: 20),
                    const Divider(color: Color(0xFFE2E8F0)),
                    const SizedBox(height: 12),
                    Text(
                      isRtl ? 'تحديث حالة حركة التوريد:' : 'Update Order Status:',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 13),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildStatusOption(context, ctx, ref, order, 'pending', isRtl ? 'قيد المعالجة' : 'Pending', const Color(0xFFF59E0B), setDlgState),
                        _buildStatusOption(context, ctx, ref, order, 'shipped', isRtl ? 'مشحون' : 'Shipped', const Color(0xFF3B82F6), setDlgState),
                        _buildStatusOption(context, ctx, ref, order, 'delivered', isRtl ? 'مكتمل ومسلم' : 'Delivered', const Color(0xFF10B981), setDlgState),
                        _buildStatusOption(context, ctx, ref, order, 'cancelled', isRtl ? 'ملغي' : 'Cancelled', const Color(0xFFEF4444), setDlgState),
                      ],
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: Text(isRtl ? 'إغلاق' : 'Close'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isBold = false, bool isLtr = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 13)),
          Flexible(
            child: Directionality(
              textDirection: isLtr ? TextDirection.ltr : TextDirection.rtl,
              child: Text(
                value,
                textDirection: isLtr ? TextDirection.ltr : null,
                textAlign: isLtr ? TextAlign.left : TextAlign.end,
                style: TextStyle(
                  fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
                  color: isBold ? const Color(0xFF0F172A) : const Color(0xFF334155),
                  fontSize: isBold ? 15 : 13,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusOption(
    BuildContext context,
    BuildContext ctx,
    WidgetRef ref,
    OrderModel order,
    String statusKey,
    String label,
    Color color,
    StateSetter setDlgState,
  ) {
    final isSelected = order.status.toLowerCase() == statusKey.toLowerCase();
    return InkWell(
      onTap: () async {
        if (!isSelected) {
          try {
            await ref.read(adminOrdersProvider.notifier).updateOrderStatus(order.id, statusKey);
            if (context.mounted) {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('تم تحديث حالة الطلب إلى $label'),
                  backgroundColor: color,
                  behavior: SnackBarBehavior.floating,
                ),
              );
            }
          } catch (e) {
            if (context.mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('فشل التحديث: $e'),
                  backgroundColor: Colors.red,
                  behavior: SnackBarBehavior.floating,
                ),
              );
            }
          }
        }
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? color.withValues(alpha: 0.12) : Colors.transparent,
          border: Border.all(color: isSelected ? color : const Color(0xFFCBD5E1)),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? color : const Color(0xFF475569),
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            fontSize: 12,
          ),
        ),
      ),
    );
  }

  List<_AppMovementItem> _buildUnifiedMovementsList({
    required bool isRtl,
    required NumberFormat currencyFmt,
    required List<OrderModel> orders,
    required List<QuoteRequestModel> quotes,
    required List<SubscriberModel> subscribers,
    required List<Map<String, dynamic>> pendingPayouts,
    required List<Map<String, dynamic>> completedPayouts,
    required List<AdModel> ads,
  }) {
    final List<_AppMovementItem> items = [];

    // 1. Orders movements
    for (final o in orders.take(25)) {
      final statusColor = _getStatusColor(o.status);
      final statusLabel = _getStatusText(o.status, isRtl);
      final buyer = o.buyerName ?? (isRtl ? 'عميل مقاييس' : 'Buyer');
      final store = o.storeName ?? (isRtl ? 'متجر معتمد' : 'Store');
      items.add(
        _AppMovementItem(
          id: 'order_${o.id}',
          category: 'order',
          title: isRtl ? 'طلب توريد وشراء #${o.formattedOrderNumber}' : 'Supply Order #${o.formattedOrderNumber}',
          subtitle: '$buyer ← $store',
          amountText: '${currencyFmt.format(o.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
          statusText: statusLabel,
          color: statusColor,
          icon: Icons.shopping_bag_rounded,
          timestamp: o.createdAt.toLocal(),
          targetRoute: RouteNames.adminOrders,
          orderPayload: o,
        ),
      );
    }

    // 2. Digital Broker (B2B Quote Requests) movements
    for (final q in quotes.take(20)) {
      String statusAr = isRtl ? 'بانتظار التسعير' : 'Pending Quote';
      Color qColor = const Color(0xFFD97706);
      if (q.status == 'contacted') {
        statusAr = isRtl ? 'تم التواصل والتسعير' : 'Contacted';
        qColor = const Color(0xFF2563EB);
      } else if (q.status == 'closed') {
        statusAr = isRtl ? 'صفقة مكتملة' : 'Closed Deal';
        qColor = const Color(0xFF10B981);
      } else if (q.status == 'cancelled') {
        statusAr = isRtl ? 'ملغي' : 'Cancelled';
        qColor = const Color(0xFFEF4444);
      }

      items.add(
        _AppMovementItem(
          id: 'quote_${q.id}',
          category: 'broker',
          title: isRtl ? 'طلب تسعير وسيط رقمي (${q.ticketNumber})' : 'B2B Quote (${q.ticketNumber})',
          subtitle: '${q.buyerName} • ${q.materialType}',
          amountText: q.dealValue > 0
              ? '${currencyFmt.format(q.dealValue)} ${isRtl ? "ج.س" : "SDG"}'
              : (isRtl ? 'كمية: ${q.quantity}' : 'Qty: ${q.quantity}'),
          statusText: statusAr,
          color: qColor,
          icon: Icons.handshake_rounded,
          timestamp: q.createdAt ?? DateTime.now().subtract(const Duration(hours: 2)),
          targetRoute: RouteNames.adminDigitalBroker,
        ),
      );
    }

    // 3. Subscribers & Stores registrations
    for (final s in subscribers.take(20)) {
      final isMerchant = s.role == UserRole.merchant || (s.storeName != null && s.storeName!.isNotEmpty);
      final displayName = (isRtl && s.nameAr.isNotEmpty) ? s.nameAr : s.name;
      final storeLabel = (s.storeNameAr != null && s.storeNameAr!.isNotEmpty)
          ? s.storeNameAr!
          : (s.storeName ?? '');
      items.add(
        _AppMovementItem(
          id: 'sub_${s.id}',
          category: 'subscriber',
          title: isMerchant
              ? (isRtl ? 'تسجيل تاجر / متجر: ${storeLabel.isNotEmpty ? storeLabel : displayName}' : 'Merchant Joined: $displayName')
              : (isRtl ? 'انضمام مشترك جديد: $displayName' : 'New Subscriber: $displayName'),
          subtitle: '${s.phone.isNotEmpty ? PhoneFormatter.format(s.phone) : s.email} • ${s.membershipNumber}',
          amountText: s.totalSpent > 0
              ? '${currencyFmt.format(s.totalSpent)} ${isRtl ? "ج.س" : "SDG"}'
              : (isMerchant ? (isRtl ? 'حساب تاجر' : 'Merchant') : (isRtl ? 'حساب مشتري' : 'Buyer')),
          statusText: s.isVerified ? (isRtl ? 'موثق ✓' : 'Verified') : (isRtl ? 'قيد المراجعة' : 'Pending'),
          color: isMerchant ? const Color(0xFF7C3AED) : const Color(0xFF0284C7),
          icon: isMerchant ? Icons.storefront_rounded : Icons.person_add_alt_1_rounded,
          timestamp: s.joinedAt.toLocal(),
          targetRoute: RouteNames.adminSubscribers,
        ),
      );
    }

    // 4. Merchant Payout Requests
    for (final p in [...pendingPayouts, ...completedPayouts].take(15)) {
      final amount = (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
      final storeName = p['store_name']?.toString() ?? (isRtl ? 'متجر شريك' : 'Partner Store');
      final status = p['status']?.toString().toLowerCase() ?? 'pending';
      final isPaid = status == 'approved' || status == 'completed' || status == 'paid';
      final dt = DateTime.tryParse(p['created_at']?.toString() ?? '')?.toLocal() ??
          DateTime.now().subtract(const Duration(hours: 4));

      items.add(
        _AppMovementItem(
          id: 'payout_${p['id'] ?? dt.millisecondsSinceEpoch}',
          category: 'payout',
          title: isRtl ? 'طلب تسوية وسحب أرباح: $storeName' : 'Payout Request: $storeName',
          subtitle: p['bank_name']?.toString() ?? (isRtl ? 'تحويل بنكي للمتجر' : 'Bank Payout'),
          amountText: '${currencyFmt.format(amount)} ${isRtl ? "ج.س" : "SDG"}',
          statusText: isPaid ? (isRtl ? 'تم التحويل' : 'Paid') : (isRtl ? 'بانتظار الاعتماد' : 'Pending Approval'),
          color: isPaid ? const Color(0xFF059669) : const Color(0xFFE11D48),
          icon: Icons.account_balance_rounded,
          timestamp: dt,
          targetRoute: RouteNames.adminFinance,
        ),
      );
    }

    // 5. Advertiser Campaigns
    for (final a in ads.take(15)) {
      final dt = DateTime.tryParse(a.startDate)?.toLocal() ??
          DateTime.now().subtract(const Duration(days: 1));
      items.add(
        _AppMovementItem(
          id: 'ad_${a.id}',
          category: 'ad',
          title: isRtl ? 'حملة إعلانية: ${a.title}' : 'Ad Campaign: ${a.title}',
          subtitle: '${a.company.isNotEmpty ? a.company : (isRtl ? "معلن معتمد" : "Sponsor")} • ${a.target}',
          amountText: '${currencyFmt.format(a.price)} ${isRtl ? "ج.س" : "SDG"}',
          statusText: a.isActive ? (isRtl ? 'نشط الآن' : 'Active') : (isRtl ? 'منتهي' : 'Ended'),
          color: const Color(0xFFEA580C),
          icon: Icons.campaign_rounded,
          timestamp: dt,
          targetRoute: RouteNames.adminAds,
        ),
      );
    }

    items.sort((a, b) => b.timestamp.compareTo(a.timestamp));

    if (_selectedMovementFilter == 'all') {
      return items.take(20).toList();
    }
    return items.where((i) => i.category == _selectedMovementFilter).take(20).toList();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');

    // 1. Subscribers & Stores
    final allSubscribersAsync = ref.watch(subscribersProvider);
    final allSubscribers = allSubscribersAsync.valueOrNull ?? [];
    final merchantsCount = allSubscribers.where((s) => s.role == UserRole.merchant || (s.storeName != null && s.storeName!.isNotEmpty)).length;
    final buyersCount = allSubscribers.where((s) => s.role == UserRole.buyer).length;

    // 2. Products & Categories
    final productsAsync = ref.watch(productsProvider);
    final allProducts = productsAsync.valueOrNull ?? [];
    final totalAdvertisedProducts = allProducts.length;
    final categoriesAsync = ref.watch(categoriesProvider);
    final allCategories = categoriesAsync.valueOrNull ?? [];

    // 3. Supply & Purchase Orders
    final ordersAsync = ref.watch(adminOrdersProvider);
    final allOrders = ordersAsync.valueOrNull ?? [];
    final completedOrders = allOrders.where((o) =>
        o.status == 'مكتمل' ||
        o.status == 'تم التسليم' ||
        o.status.toLowerCase() == 'completed' ||
        o.status.toLowerCase() == 'delivered').toList();
    final cancelledOrders = allOrders.where((o) =>
        o.status == 'ملغي' ||
        o.status == 'ملغى' ||
        o.status.toLowerCase() == 'cancelled').toList();
    final inProgressOrders = allOrders.length - completedOrders.length - cancelledOrders.length;

    double totalDeliveredSales = 0.0;
    for (final o in completedOrders) {
      totalDeliveredSales += o.totalAmount;
    }
    double totalActiveOrderVolume = 0.0;
    double totalOrderTransportAndOffloading = 0.0;
    int ordersWithLogisticsCount = 0;
    for (final o in allOrders) {
      final s = o.status.toLowerCase();
      if (s != 'cancelled' && s != 'ملغي' && s != 'ملغى') {
        totalActiveOrderVolume += o.totalAmount;
        if (o.transportFee > 0 || o.offloadingFee > 0) {
          totalOrderTransportAndOffloading += (o.transportFee + o.offloadingFee);
          ordersWithLogisticsCount++;
        }
      }
    }
    final displayGmv = totalDeliveredSales > 0 ? totalDeliveredSales : totalActiveOrderVolume;

    final now = DateTime.now();
    double currentMonthRevenue = 0.0;
    for (final o in allOrders.where((o) {
      final s = o.status.toLowerCase();
      return s != 'cancelled' && s != 'ملغي' && o.createdAt.year == now.year && o.createdAt.month == now.month;
    })) {
      currentMonthRevenue += o.totalAmount;
    }

    // 4. Advertiser Campaigns
    final adsAsync = ref.watch(adsProvider);
    final allAds = adsAsync.valueOrNull ?? [];
    final activeAds = allAds.where((a) => a.isActive).toList();
    double totalAdsRevenue = 0.0;
    double currentMonthAdsRevenue = 0.0;
    for (final a in allAds) {
      if (a.price > 0) {
        totalAdsRevenue += a.price;
        final startDt = DateTime.tryParse(a.startDate);
        if (startDt != null && startDt.year == now.year && startDt.month == now.month) {
          currentMonthAdsRevenue += a.price;
        }
      }
    }

    // 5. Finance & Payouts
    final financeAsync = ref.watch(adminFinanceProvider);
    final financeData = financeAsync.valueOrNull;
    final totalPlatformCommission = (financeData?['totalPlatformCommission'] as num?)?.toDouble() ?? 0.0;
    final pendingPayouts = (financeData?['pendingPayoutRequests'] as List?)?.cast<Map<String, dynamic>>() ?? [];
    final completedPayouts = (financeData?['completedPayouts'] as List?)?.cast<Map<String, dynamic>>() ?? [];
    final totalApprovedPayouts = (financeData?['totalApprovedPayouts'] as num?)?.toDouble() ?? 0.0;

    // 6. Digital Broker (B2B Quote Requests)
    final brokerAsync = ref.watch(adminDigitalBrokerProvider);
    final brokerInsights = brokerAsync.valueOrNull;
    final allQuotes = brokerInsights?.requests ?? [];
    final totalBrokerRequests = brokerInsights?.totalRequests ?? 0;
    final pendingBrokerRequests = brokerInsights?.pendingRequests ?? 0;
    final totalBrokerDealVolume = brokerInsights?.totalEstimatedDealVolume ?? 0.0;
    final totalBrokerCommissions = brokerInsights?.totalBrokerageCommissions ?? 0.0;

    // 7. Service Providers & Fleet
    final servicesState = ref.watch(adminServicesProvider);
    final servicesInsightsAsync = ref.watch(adminServicesInsightsProvider);
    final servicesInsights = servicesInsightsAsync.valueOrNull;
    final totalServiceProviders = servicesInsights?.totalProvidersCount ?? servicesState.providers.length;
    final verifiedProvidersCount = servicesInsights?.verifiedCount ??
        servicesState.providers.where((p) => p.isVerified).length;
    final pendingProvidersCount = servicesInsights?.pendingApprovalCount ??
        servicesState.providers.where((p) => !p.isVerified).length;
    final totalLogisticsFees = (servicesInsights?.totalLogisticsFees ?? 0.0) > 0
        ? servicesInsights!.totalLogisticsFees
        : totalOrderTransportAndOffloading;

    // Combined Net Platform Profit (Sales Commissions + Ads Revenue + Digital Broker Commissions)
    final baseNetProfit = (financeData?['magaieesNetProfit'] as num?)?.toDouble() ??
        (totalPlatformCommission + totalAdsRevenue);
    final magaieesNetProfit = baseNetProfit + totalBrokerCommissions;

    // 8. 30-Day Growth
    final thirtyDaysAgo = now.subtract(const Duration(days: 30));
    final sixtyDaysAgo = now.subtract(const Duration(days: 60));
    final newSubscribersCount = allSubscribers.where((s) => s.joinedAt.isAfter(thirtyDaysAgo)).length;
    final previousSubscribersCount = allSubscribers
        .where((s) => s.joinedAt.isAfter(sixtyDaysAgo) && s.joinedAt.isBefore(thirtyDaysAgo))
        .length;
    double subscriberGrowth = 0.0;
    if (previousSubscribersCount > 0) {
      subscriberGrowth = ((newSubscribersCount - previousSubscribersCount) / previousSubscribersCount) * 100;
    } else if (newSubscribersCount > 0) {
      subscriberGrowth = 100.0;
    }

    final unifiedMovements = _buildUnifiedMovementsList(
      isRtl: isRtl,
      currencyFmt: currencyFmt,
      orders: allOrders,
      quotes: allQuotes,
      subscribers: allSubscribers,
      pendingPayouts: pendingPayouts,
      completedPayouts: completedPayouts,
      ads: allAds,
    );

    final isMobile = MediaQuery.of(context).size.width < 700;
    final contractingCount = servicesInsights?.contractingCount ??
        servicesState.providers.where((p) => p.categoryId == 'contracting').length;
    final logisticsCount = servicesInsights?.logisticsCount ??
        servicesState.providers.where((p) => p.categoryId == 'logistics').length;

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      body: SingleChildScrollView(
        padding: EdgeInsets.symmetric(
          horizontal: isMobile ? 12.0 : 24.0,
          vertical: isMobile ? 14.0 : 20.0,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ════════════════════════════════════════════════════════════════
            // 1. EXECUTIVE HERO COMMAND BANNER
            // ════════════════════════════════════════════════════════════════
            _buildExecutiveCommandHeader(
              context: context,
              isRtl: isRtl,
              currencyFmt: currencyFmt,
              totalSubscribers: allSubscribers.length,
              activeAdsCount: activeAds.length,
              magaieesNetProfit: magaieesNetProfit,
              totalBrokerRequests: totalBrokerRequests,
            ),

            if (ordersAsync.hasError ||
                allSubscribersAsync.hasError ||
                productsAsync.hasError ||
                adsAsync.hasError ||
                financeAsync.hasError) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFF59E0B)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.sync_problem_rounded, color: Color(0xFFD97706), size: 20),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        isRtl
                            ? 'تعذر تحميل بعض المؤشرات الفرعية مؤقتاً، اضغط تحديث لمزامنة كافة الجداول.'
                            : 'Some secondary metrics failed to sync. Click Refresh to reload all tables.',
                        style: const TextStyle(color: Color(0xFF92400E), fontSize: 12.5, fontWeight: FontWeight.w600),
                      ),
                    ),
                    TextButton.icon(
                      onPressed: _refreshAllData,
                      icon: const Icon(Icons.refresh_rounded, size: 17, color: Color(0xFF92400E)),
                      label: Text(
                        isRtl ? 'إعادة المحاولة' : 'Retry',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF92400E)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 14),

            // ════════════════════════════════════════════════════════════════
            // 2. 12 COMPACT EXECUTIVE KPI CARDS (40% HEIGHT & DISTINCT COLORS)
            // ════════════════════════════════════════════════════════════════
            LayoutBuilder(
              builder: (context, constraints) {
                const double spacing = 10.0;
                final int crossAxisCount = constraints.maxWidth > 1100
                    ? 4
                    : (constraints.maxWidth > 650 ? 2 : 1);
                final double cardWidth =
                    (constraints.maxWidth - (spacing * (crossAxisCount - 1))) / crossAxisCount;

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Row 1: Financial & Revenue Engine
                    Wrap(
                      spacing: spacing,
                      runSpacing: spacing,
                      children: [
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'إجمالي مبيعات وحركة السوق' : 'Total Marketplace GMV',
                          value: '${currencyFmt.format(displayGmv)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl
                              ? '${completedOrders.length} توريد مكتمل • ${allOrders.length} إجمالي'
                              : '${completedOrders.length} Delivered / ${allOrders.length} Total',
                          badgeText: isRtl ? 'المبيعات' : 'GMV',
                          icon: Icons.account_balance_wallet_rounded,
                          color: const Color(0xFF059669),
                          secondaryColor: const Color(0xFF047857),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'صافي عوائد منصة مقاييس' : 'Platform Net Revenue',
                          value: '${currencyFmt.format(magaieesNetProfit)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl
                              ? 'عمولات التوريد + الإعلانات + الوسيط'
                              : 'Commissions + Ads + Broker',
                          badgeText: isRtl ? 'صافي الربح' : 'Net Profit',
                          icon: Icons.monetization_on_rounded,
                          color: const Color(0xFF7C3AED),
                          secondaryColor: const Color(0xFF5B21B6),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'إيرادات وحملات المعلنين' : 'Advertiser Payments',
                          value: '${currencyFmt.format(totalAdsRevenue)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl
                              ? '${activeAds.length} حملة نشطة من أصل ${allAds.length}'
                              : '${activeAds.length} Active / ${allAds.length} Total',
                          badgeText: isRtl ? 'الإعلانات' : 'Ads',
                          icon: Icons.campaign_rounded,
                          color: const Color(0xFFEA580C),
                          secondaryColor: const Color(0xFFC2410C),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminAds),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'التدفق المالي للشهر الحالي' : 'Current Month Inflow',
                          value: '${currencyFmt.format(currentMonthRevenue + currentMonthAdsRevenue)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl
                              ? 'توريدات الشهر + إعلانات الشهر'
                              : 'Monthly Orders + Monthly Ads',
                          badgeText: isRtl ? 'هذا الشهر' : 'Monthly',
                          icon: Icons.trending_up_rounded,
                          color: const Color(0xFF0D9488),
                          secondaryColor: const Color(0xFF115E59),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                      ],
                    ),
                    const SizedBox(height: spacing),

                    // Row 2: Operations, B2B Digital Broker, Logistics & Payouts
                    Wrap(
                      spacing: spacing,
                      runSpacing: spacing,
                      children: [
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'حركة الطلبات والتوريدات' : 'Supply & Purchase Orders',
                          value: '${allOrders.length} ${isRtl ? "طلب" : "Orders"}',
                          subtitle: isRtl
                              ? '$inProgressOrders قيد التنفيذ • ${cancelledOrders.length} ملغي'
                              : '$inProgressOrders Active • ${cancelledOrders.length} Cancelled',
                          badgeText: isRtl ? 'التوريدات' : 'Orders',
                          icon: Icons.shopping_bag_rounded,
                          color: const Color(0xFF2563EB),
                          secondaryColor: const Color(0xFF1E40AF),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminOrders),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'الوسيط الرقمي وطلبات التسعير' : 'B2B Digital Broker',
                          value: '$totalBrokerRequests ${isRtl ? "طلب تسعير" : "Quotes"}',
                          subtitle: isRtl
                              ? 'حجم الصفقات: ${currencyFmt.format(totalBrokerDealVolume)} ج.س'
                              : 'Volume: ${currencyFmt.format(totalBrokerDealVolume)} SDG',
                          badgeText: isRtl ? '$pendingBrokerRequests بالانتظار' : '$pendingBrokerRequests Pending',
                          icon: Icons.handshake_rounded,
                          color: const Color(0xFFD97706),
                          secondaryColor: const Color(0xFF92400E),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminDigitalBroker),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'حركة النقل والعتالة اللوجستية' : 'Logistics & Fleet Fees',
                          value: '${currencyFmt.format(totalLogisticsFees)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl
                              ? '$ordersWithLogisticsCount عملية ترحيل وتفريغ'
                              : '$ordersWithLogisticsCount Fleet & Offloading Ops',
                          badgeText: isRtl ? 'اللوجستيات' : 'Logistics',
                          icon: Icons.local_shipping_rounded,
                          color: const Color(0xFF0284C7),
                          secondaryColor: const Color(0xFF075985),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminServices),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'تسويات وسحوبات التجار' : 'Merchant Payouts',
                          value: '${currencyFmt.format(totalApprovedPayouts)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl
                              ? '${pendingPayouts.length} طلب سحب بانتظار الاعتماد'
                              : '${pendingPayouts.length} Pending Payout Requests',
                          badgeText: isRtl ? 'التسويات' : 'Payouts',
                          icon: Icons.account_balance_rounded,
                          color: const Color(0xFFE11D48),
                          secondaryColor: const Color(0xFF9F1239),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                      ],
                    ),
                    const SizedBox(height: spacing),

                    // Row 3: Ecosystem (Subscribers, Stores, Products, Categories, Service Providers)
                    Wrap(
                      spacing: spacing,
                      runSpacing: spacing,
                      children: [
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'إجمالي المشتركين والمتاجر' : 'Subscribers & Stores',
                          value: '${allSubscribers.length} ${isRtl ? "مشترك" : "Users"}',
                          subtitle: isRtl
                              ? '$merchantsCount متجر وتاجر • $buyersCount مشتري'
                              : '$merchantsCount Merchants • $buyersCount Buyers',
                          badgeText: isRtl ? 'المجتمع' : 'Users',
                          icon: Icons.people_alt_rounded,
                          color: const Color(0xFF4F46E5),
                          secondaryColor: const Color(0xFF312E81),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminSubscribers),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'المنتجات والأقسام المعتمدة' : 'Products & Categories',
                          value: '$totalAdvertisedProducts ${isRtl ? "منتج" : "Items"}',
                          subtitle: isRtl
                              ? 'موزعة على ${allCategories.length} قسم رئيسي بالسوق'
                              : 'Across ${allCategories.length} Marketplace Categories',
                          badgeText: isRtl ? '${allCategories.length} أقسام' : '${allCategories.length} Cats',
                          icon: Icons.inventory_2_rounded,
                          color: const Color(0xFF0891B2),
                          secondaryColor: const Color(0xFF164E63),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminProducts),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'مقدمو الخدمات والأساطيل' : 'Service Providers & Fleet',
                          value: '$totalServiceProviders ${isRtl ? "مزود خدمة" : "Providers"}',
                          subtitle: isRtl
                              ? '$verifiedProvidersCount معتمد • $pendingProvidersCount قيد التوثيق'
                              : '$verifiedProvidersCount Verified • $pendingProvidersCount Pending',
                          badgeText: isRtl ? 'الخدمات' : 'Services',
                          icon: Icons.engineering_rounded,
                          color: const Color(0xFFC026D3),
                          secondaryColor: const Color(0xFF701A75),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminServices),
                        ),
                        _buildExecutiveKpiCard(
                          title: isRtl ? 'مؤشر النمو (آخر 30 يوماً)' : '30-Day Platform Growth',
                          value: '+$newSubscribersCount ${isRtl ? "مشترك" : "New"}',
                          subtitle: isRtl
                              ? 'نسبة النمو: ${subscriberGrowth >= 0 ? "+" : ""}${subscriberGrowth.toStringAsFixed(1)}%'
                              : 'Growth Rate: ${subscriberGrowth >= 0 ? "+" : ""}${subscriberGrowth.toStringAsFixed(1)}%',
                          badgeText: isRtl ? 'النمو' : 'Growth',
                          icon: Icons.auto_graph_rounded,
                          color: const Color(0xFF16A34A),
                          secondaryColor: const Color(0xFF14532D),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminSubscribers),
                        ),
                      ],
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 18),

            // ════════════════════════════════════════════════════════════════
            // 2B. DEDICATED LOGISTICS FLEET & CONTRACTING COMPANIES SPOTLIGHT
            // ════════════════════════════════════════════════════════════════
            _buildLogisticsAndContractingDashboardSection(
              context: context,
              isRtl: isRtl,
              isMobile: isMobile,
              contractingCount: contractingCount,
              logisticsCount: logisticsCount,
              pendingProvidersCount: pendingProvidersCount,
              totalLogisticsFeesFormatted:
                  '${currencyFmt.format(totalLogisticsFees)} ${isRtl ? "ج.س" : "SDG"}',
            ),
            const SizedBox(height: 20),

            // ════════════════════════════════════════════════════════════════
            // 3. FIRST: COMPREHENSIVE LINE-CHART ANALYTICS, SALES & TOP STORES
            // ════════════════════════════════════════════════════════════════
            _buildMergedAnalyticsCard(
              context,
              isRtl,
              allOrders.where((o) => o.status.toLowerCase() != 'cancelled' && o.status != 'ملغي').toList(),
              allSubscribers,
              allAds,
              allQuotes: allQuotes,
              displayGmv: displayGmv,
              magaieesNetProfit: magaieesNetProfit,
              totalAdsRevenue: totalAdsRevenue,
              totalBrokerDealVolume: totalBrokerDealVolume,
              totalLogisticsFees: totalLogisticsFees,
              completedOrdersCount: completedOrders.length,
              totalOrdersCount: allOrders.length,
            ),
            const SizedBox(height: 22),

            // ════════════════════════════════════════════════════════════════
            // 4. SECOND: UNIFIED LIVE APPLICATION MOVEMENTS STREAM (سجل حركات التطبيق الحية)
            // ════════════════════════════════════════════════════════════════
            _buildUnifiedMovementsSection(
              context: context,
              isRtl: isRtl,
              movements: unifiedMovements,
              counts: {
                'all': allOrders.length + allQuotes.length + allSubscribers.length + pendingPayouts.length + allAds.length,
                'order': allOrders.length,
                'broker': allQuotes.length,
                'subscriber': allSubscribers.length,
                'payout': pendingPayouts.length + completedPayouts.length,
                'ad': allAds.length,
              },
            ),
            const SizedBox(height: 22),

            // ════════════════════════════════════════════════════════════════
            // 5. DIGITAL BROKER & B2B QUOTE REQUESTS LIVE TABLE
            // ════════════════════════════════════════════════════════════════
            _buildDigitalBrokerQuotesCard(context, isRtl, allQuotes, totalBrokerDealVolume, totalBrokerCommissions),
            const SizedBox(height: 22),

            // ════════════════════════════════════════════════════════════════
            // 6. ADVERTISER PAYMENTS & CAMPAIGNS TABLE
            // ════════════════════════════════════════════════════════════════
            _buildAdvertiserCampaignsCard(context, isRtl, allAds),
            const SizedBox(height: 22),

            // ════════════════════════════════════════════════════════════════
            // 7. FULL-WIDTH RECENT SUPPLY & PURCHASE ORDERS TABLE
            // ════════════════════════════════════════════════════════════════
            _buildFullWidthLatestOrders(context, isRtl, allOrders),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // EXECUTIVE HERO COMMAND BANNER
  // ════════════════════════════════════════════════════════════════
  Widget _buildExecutiveCommandHeader({
    required BuildContext context,
    required bool isRtl,
    required NumberFormat currencyFmt,
    required int totalSubscribers,
    required int activeAdsCount,
    required double magaieesNetProfit,
    required int totalBrokerRequests,
  }) {
    final platformUsd = ref.watch(platformUsdRateSettingProvider);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [
            Color(0xFF0F172A),
            Color(0xFF1E3A8A),
            Color(0xFF1D4ED8),
          ],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A8A).withValues(alpha: 0.22),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        spacing: 16,
        runSpacing: 12,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFF10B981).withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: const Color(0xFF34D399).withValues(alpha: 0.5)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 7,
                          height: 7,
                          decoration: const BoxDecoration(
                            color: Color(0xFF34D399),
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          isRtl ? 'متصل مباشر • مراقبة حية لكافة حركات المنصة' : 'LIVE • Full Platform Telemetry',
                          style: const TextStyle(
                            color: Color(0xFFA7F3D0),
                            fontSize: 10.5,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                isRtl ? 'مركز القيادة ولوحة التحكم التنفيذية — مقاييس' : 'Magaiees Executive Command Center',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 21,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.4,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                isRtl
                    ? 'نظرة تنفيذية متكاملة لتحليل المبيعات، الإيرادات، أكثر المتاجر مبيعاً، وحركات التطبيق الحية'
                    : 'Executive intelligence for sales analytics, revenue growth, top stores & live movements',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.8),
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          Wrap(
            spacing: 10,
            runSpacing: 8,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              InkWell(
                onTap: () => _showUpdatePlatformUsdDialog(context, isRtl, platformUsd),
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF064E3B).withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF34D399).withValues(alpha: 0.6)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.currency_exchange_rounded, color: Color(0xFF34D399), size: 16),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            isRtl ? 'سعر الدولار المعتمد (تعديل)' : 'Official USD Rate',
                            style: const TextStyle(color: Color(0xFFA7F3D0), fontSize: 9.5, fontWeight: FontWeight.w600),
                          ),
                          Text(
                            '1 USD = ${NumberFormat('#,##0').format(platformUsd)} ${isRtl ? "ج.س" : "SDG"}',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 12),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              FilledButton.icon(
                onPressed: () {
                  _refreshAllData();
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        isRtl ? 'تم تحديث كافة بيانات وحركات المنصة بنجاح' : 'All platform data refreshed',
                      ),
                      backgroundColor: const Color(0xFF059669),
                      duration: const Duration(seconds: 2),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                },
                icon: const Icon(Icons.sync_rounded, size: 16),
                label: Text(isRtl ? 'تحديث فوري' : 'Sync Live', style: const TextStyle(fontSize: 12.5)),
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white.withValues(alpha: 0.15),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                    side: BorderSide(color: Colors.white.withValues(alpha: 0.25)),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // COMPACT EXECUTIVE KPI CARD (40% HEIGHT + DISTINCT COLOR THEME)
  // ════════════════════════════════════════════════════════════════
  Widget _buildExecutiveKpiCard({
    required String title,
    required String value,
    required String subtitle,
    required String badgeText,
    required IconData icon,
    required Color color,
    required Color secondaryColor,
    required double width,
    VoidCallback? onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          width: width,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [
                color,
                secondaryColor,
              ],
              begin: AlignmentDirectional.topStart,
              end: AlignmentDirectional.bottomEnd,
            ),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Colors.white.withValues(alpha: 0.22), width: 1),
            boxShadow: [
              BoxShadow(
                color: color.withValues(alpha: 0.24),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.18),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.28)),
                ),
                child: Icon(icon, color: Colors.white, size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            title,
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.92),
                              fontSize: 10.5,
                              fontWeight: FontWeight.w700,
                              height: 1.1,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            badgeText,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 3),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.baseline,
                      textBaseline: TextBaseline.alphabetic,
                      children: [
                        Flexible(
                          flex: 5,
                          child: Text(
                            value,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: -0.2,
                              height: 1.1,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Expanded(
                          flex: 6,
                          child: Text(
                            subtitle,
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.8),
                              fontSize: 9.5,
                              fontWeight: FontWeight.w500,
                              height: 1.1,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 4),
              Icon(Icons.arrow_forward_ios_rounded, size: 10, color: Colors.white.withValues(alpha: 0.7)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLogisticsAndContractingDashboardSection({
    required BuildContext context,
    required bool isRtl,
    required bool isMobile,
    required int contractingCount,
    required int logisticsCount,
    required int pendingProvidersCount,
    required String totalLogisticsFeesFormatted,
  }) {
    Widget buildSectorCard({
      required String title,
      required String subtitle,
      required String countBadge,
      required String subMetric,
      required IconData icon,
      required List<Color> gradientColors,
      required Color accentColor,
      required String buttonLabel,
    }) {
      return Container(
        padding: EdgeInsets.all(isMobile ? 15 : 18),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: gradientColors,
            begin: Alignment.centerRight,
            end: Alignment.centerLeft,
          ),
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: gradientColors.last.withValues(alpha: 0.2),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.16),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: accentColor, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: isMobile ? 14.5 : 15.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.84),
                          fontSize: 11.5,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.24),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    countBadge,
                    style: TextStyle(
                      color: accentColor,
                      fontWeight: FontWeight.w900,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              spacing: 10,
              runSpacing: 8,
              children: [
                Text(
                  subMetric,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.9),
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                FilledButton.icon(
                  onPressed: () => context.go(RouteNames.adminServices),
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: gradientColors.first,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  icon: const Icon(Icons.open_in_new_rounded, size: 15),
                  label: Text(
                    buttonLabel,
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12),
                  ),
                ),
              ],
            ),
          ],
        ),
      );
    }

    final logisticsCard = buildSectorCard(
      title: isRtl
          ? 'إدارة شركات الشحن ومقدمي الخدمات اللوجستية'
          : 'Logistics & Shipping Providers Hub',
      subtitle: isRtl
          ? 'أساطيل التريلات المسطحة، القلابات، الدفارات، وفرق التعتيل وعقود الشحن'
          : 'Flatbeds, dump trucks, medium cargo fleets & offloading crews',
      countBadge: isRtl ? '$logisticsCount أسطول وشركة' : '$logisticsCount Fleets',
      subMetric: isRtl
          ? 'عوائد الترحيل والتفريغ: $totalLogisticsFeesFormatted'
          : 'Logistics Volume: $totalLogisticsFeesFormatted',
      icon: Icons.local_shipping_rounded,
      gradientColors: const [Color(0xFF0F172A), Color(0xFF1E3A8A)],
      accentColor: const Color(0xFFFBBF24),
      buttonLabel: isRtl ? 'إدارة اللوجستيات والشحن' : 'Manage Logistics',
    );

    final contractingCard = buildSectorCard(
      title: isRtl
          ? 'قطاع شركات المقاولات والتشطيبات والهياكل الخرسانية'
          : 'Contracting, Concrete & Turnkey Finishing Co.',
      subtitle: isRtl
          ? 'الشركات المنفذة للهياكل الخرسانية المسلحة، الأبراج، الهناجر، وتشطيبات تسليم المفتاح'
          : 'Corporate reinforced concrete structures, buildings & turnkey finishing',
      countBadge: isRtl ? '$contractingCount شركة معتمدة' : '$contractingCount Firms',
      subMetric: pendingProvidersCount > 0
          ? (isRtl ? 'يوجد $pendingProvidersCount طلب انضمام بانتظار المراجعة' : '$pendingProvidersCount pending applications')
          : (isRtl ? 'كافة الشركات المعتمدة نشطة في دليل المقاولات' : 'All certified firms active in directory'),
      icon: Icons.domain_rounded,
      gradientColors: const [Color(0xFF0F292E), Color(0xFF0F766E)],
      accentColor: const Color(0xFF5EEAD4),
      buttonLabel: isRtl ? 'إدارة شركات المقاولات' : 'Manage Contracting',
    );

    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth < 900) {
          return Column(
            children: [
              logisticsCard,
              const SizedBox(height: 12),
              contractingCard,
            ],
          );
        }
        return Row(
          children: [
            Expanded(child: logisticsCard),
            const SizedBox(width: 14),
            Expanded(child: contractingCard),
          ],
        );
      },
    );
  }

  // ════════════════════════════════════════════════════════════════
  // UNIFIED LIVE APPLICATION MOVEMENTS FEED (سجل كافة حركات التطبيق)
  // ════════════════════════════════════════════════════════════════
  Widget _buildUnifiedMovementsSection({
    required BuildContext context,
    required bool isRtl,
    required List<_AppMovementItem> movements,
    required Map<String, int> counts,
  }) {
    final filters = [
      {'key': 'all', 'label': isRtl ? 'كافة الحركات' : 'All Movements', 'icon': Icons.bolt_rounded},
      {'key': 'order', 'label': isRtl ? 'طلبات التوريد' : 'Orders', 'icon': Icons.shopping_bag_rounded},
      {'key': 'broker', 'label': isRtl ? 'الوسيط الرقمي' : 'B2B Quotes', 'icon': Icons.handshake_rounded},
      {'key': 'subscriber', 'label': isRtl ? 'المشتركين والمتاجر' : 'Subscribers', 'icon': Icons.people_alt_rounded},
      {'key': 'payout', 'label': isRtl ? 'تسويات وسحوبات التجار' : 'Payouts', 'icon': Icons.account_balance_rounded},
      {'key': 'ad', 'label': isRtl ? 'حملات المعلنين' : 'Ads', 'icon': Icons.campaign_rounded},
    ];

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 12,
            runSpacing: 10,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.all(9),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.radar_rounded, color: Color(0xFF1E40AF), size: 22),
                  ),
                  const SizedBox(width: 12),
                  Flexible(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'سجل حركات التطبيق الحية الشامل' : 'Unified Live Application Movements',
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0F172A)),
                        ),
                        Text(
                          isRtl
                              ? 'رصد زمني مباشر لكل عملية شراء، تسعير، انضمام متجر أو مشترك، سحب أرباح، أو إعلان'
                              : 'Real-time chronological feed of every order, quote, user signup, payout, and ad',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFFBBF7D0)),
                ),
                child: Text(
                  isRtl ? '${movements.length} حركة معروضة' : '${movements.length} Movements',
                  style: const TextStyle(color: Color(0xFF15803D), fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Filter Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: filters.map((f) {
                final key = f['key'] as String;
                final label = f['label'] as String;
                final icon = f['icon'] as IconData;
                final isSelected = _selectedMovementFilter == key;
                final count = counts[key] ?? 0;

                return Padding(
                  padding: const EdgeInsetsDirectional.only(end: 8),
                  child: InkWell(
                    onTap: () => setState(() => _selectedMovementFilter = key),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: isSelected ? const Color(0xFF1E40AF) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isSelected ? const Color(0xFF1E40AF) : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            icon,
                            size: 15,
                            color: isSelected ? Colors.white : const Color(0xFF475569),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            '$label ($count)',
                            style: TextStyle(
                              color: isSelected ? Colors.white : const Color(0xFF334155),
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const Divider(height: 24),

          if (movements.isEmpty)
            Padding(
              padding: const EdgeInsets.all(28),
              child: Center(
                child: Text(
                  isRtl ? 'لا توجد حركات مسجلة في هذا التصنيف حالياً' : 'No movements in this category yet',
                  style: TextStyle(color: Colors.grey.shade500),
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: movements.take(10).length,
              separatorBuilder: (_, _) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
              itemBuilder: (context, index) {
                final item = movements[index];
                final timeStr = DateFormat('yyyy-MM-dd HH:mm').format(item.timestamp);

                return InkWell(
                  onTap: () {
                    if (item.orderPayload != null) {
                      _showOrderDetails(context, ref, item.orderPayload!, isRtl);
                    } else {
                      context.go(item.targetRoute);
                    }
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 6),
                    child: Row(
                      children: [
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: item.color.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(item.icon, color: item.color, size: 20),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item.title,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w800,
                                  fontSize: 13,
                                  color: Color(0xFF0F172A),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                item.subtitle,
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  color: Color(0xFF64748B),
                                  fontWeight: FontWeight.w500,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              item.amountText,
                              style: const TextStyle(
                                fontWeight: FontWeight.w900,
                                fontSize: 12.5,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 3),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: item.color.withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    item.statusText,
                                    style: TextStyle(
                                      color: item.color,
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  timeStr,
                                  style: const TextStyle(fontSize: 10.5, color: Color(0xFF94A3B8)),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // DIGITAL BROKER & B2B QUOTE REQUESTS CARD
  // ════════════════════════════════════════════════════════════════
  Widget _buildDigitalBrokerQuotesCard(
    BuildContext context,
    bool isRtl,
    List<QuoteRequestModel> quotes,
    double totalDealVolume,
    double totalBrokerCommissions,
  ) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    final recentQuotes = quotes.take(8).toList();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 12,
            runSpacing: 10,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFD97706).withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.handshake_rounded, color: Color(0xFFD97706), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Flexible(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'حركة الوسيط الرقمي وطلبات التسعير (B2B)' : 'B2B Digital Broker & Quote Requests',
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0F172A)),
                        ),
                        Text(
                          isRtl
                              ? 'إجمالي الصفقات المقدرة: ${currencyFmt.format(totalDealVolume)} ج.س • عمولات الوساطة: ${currencyFmt.format(totalBrokerCommissions)} ج.س'
                              : 'Est. Deal Volume: ${currencyFmt.format(totalDealVolume)} SDG • Brokerage Commission: ${currencyFmt.format(totalBrokerCommissions)} SDG',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              FilledButton.tonalIcon(
                onPressed: () => context.go(RouteNames.adminDigitalBroker),
                icon: Icon(isRtl ? Icons.arrow_back : Icons.arrow_forward, size: 16),
                label: Text(isRtl ? 'إدارة التسعير والصفقات' : 'Manage B2B Quotes'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFD97706).withValues(alpha: 0.1),
                  foregroundColor: const Color(0xFFB45309),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          recentQuotes.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(24),
                  child: Center(
                    child: Text(
                      isRtl ? 'لا توجد طلبات تسعير مسجلة حالياً' : 'No quote requests yet',
                      style: TextStyle(color: Colors.grey.shade500),
                    ),
                  ),
                )
              : _buildFullWidthResponsiveTable(
                  headers: [
                    _TableColumnSpec(label: isRtl ? 'رقم التذكرة' : 'Ticket', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'المشتري' : 'Buyer', flex: 3),
                    _TableColumnSpec(label: isRtl ? 'المواد المطلوبة' : 'Materials', flex: 4),
                    _TableColumnSpec(label: isRtl ? 'المورد المعتمد' : 'Assigned Supplier', flex: 3),
                    _TableColumnSpec(label: isRtl ? 'قيمة الصفقة' : 'Deal Value', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'عمولة المنصة' : 'Commission', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'الحالة' : 'Status', flex: 2),
                  ],
                  rows: recentQuotes.map((q) {
                    Color stColor = const Color(0xFFD97706);
                    String stText = isRtl ? 'قيد الانتظار' : 'Pending';
                    if (q.status == 'contacted') {
                      stColor = const Color(0xFF2563EB);
                      stText = isRtl ? 'تم التسعير' : 'Contacted';
                    } else if (q.status == 'closed') {
                      stColor = const Color(0xFF10B981);
                      stText = isRtl ? 'صفقة مكتملة' : 'Closed';
                    } else if (q.status == 'cancelled') {
                      stColor = const Color(0xFFEF4444);
                      stText = isRtl ? 'ملغي' : 'Cancelled';
                    }

                    return [
                      Text(
                        q.ticketNumber,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFFB45309)),
                      ),
                      Text(
                        q.buyerName,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        q.materialType,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 12),
                      ),
                      Text(
                        q.assignedSupplier ?? (isRtl ? 'لم يحدد بعد' : 'Unassigned'),
                        style: const TextStyle(fontSize: 12, color: Color(0xFF334155)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        q.dealValue > 0 ? '${currencyFmt.format(q.dealValue)} ${isRtl ? "ج.س" : "SDG"}' : '—',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                      Text(
                        q.commissionValue > 0 ? '${currencyFmt.format(q.commissionValue)} ${isRtl ? "ج.س" : "SDG"}' : '—',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF059669)),
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: stColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            stText,
                            style: TextStyle(color: stColor, fontWeight: FontWeight.bold, fontSize: 11),
                          ),
                        ),
                      ),
                    ];
                  }).toList(),
                ),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // COMPREHENSIVE EXECUTIVE ANALYTICS (LINE CHARTS + SALES + TOP STORES)
  // ════════════════════════════════════════════════════════════════
  Widget _buildMergedAnalyticsCard(
    BuildContext context,
    bool isRtl,
    List<OrderModel> validOrders,
    List<SubscriberModel> allSubscribers,
    List<AdModel> allAds, {
    required List<QuoteRequestModel> allQuotes,
    required double displayGmv,
    required double magaieesNetProfit,
    required double totalAdsRevenue,
    required double totalBrokerDealVolume,
    required double totalLogisticsFees,
    required int completedOrdersCount,
    required int totalOrdersCount,
  }) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    final now = DateTime.now();
    final List<String> months = [];
    final List<double> monthlySales = [];
    final List<double> monthlyPlatformAndAds = [];
    final List<int> monthlyNewUsers = [];

    double totalRevenue6M = 0;
    int totalNewUsers6M = 0;

    final arMonths = [
      'يناير',
      'فبراير',
      'مارس',
      'أبريل',
      'مايو',
      'يونيو',
      'يوليو',
      'أغسطس',
      'سبتمبر',
      'أكتوبر',
      'نوفمبر',
      'ديسمبر',
    ];

    for (int i = 5; i >= 0; i--) {
      final monthDate = DateTime(now.year, now.month - i, 1);
      final arMonth = arMonths[monthDate.month - 1];
      months.add(isRtl ? arMonth : DateFormat('MMM').format(monthDate));

      double mOrdersTotal = 0.0;
      double mCommissions = 0.0;
      for (final o in validOrders) {
        if (o.createdAt.year == monthDate.year && o.createdAt.month == monthDate.month) {
          mOrdersTotal += o.totalAmount;
          mCommissions += (o.commissionAmount ?? (o.totalAmount * 0.03));
        }
      }

      double mAdsAndBroker = mCommissions;
      for (final a in allAds) {
        if (a.price > 0) {
          final dt = DateTime.tryParse(a.startDate);
          if (dt != null && dt.year == monthDate.year && dt.month == monthDate.month) {
            mAdsAndBroker += a.price;
          }
        }
      }
      for (final q in allQuotes) {
        final qDt = q.createdAt;
        if (qDt != null && qDt.year == monthDate.year && qDt.month == monthDate.month) {
          if (q.dealValue > 0) {
            mOrdersTotal += q.dealValue;
          }
          if (q.commissionValue > 0) {
            mAdsAndBroker += q.commissionValue;
          }
        }
      }

      final combinedMonthRev = mOrdersTotal + mAdsAndBroker;
      monthlySales.add(combinedMonthRev);
      monthlyPlatformAndAds.add(mAdsAndBroker);
      totalRevenue6M += combinedMonthRev;

      int uCount = 0;
      for (final s in allSubscribers) {
        if (s.joinedAt.year == monthDate.year && s.joinedAt.month == monthDate.month) {
          uCount++;
        }
      }
      monthlyNewUsers.add(uCount);
      totalNewUsers6M += uCount;
    }

    final double maxSalesVal = monthlySales.isEmpty
        ? 1000.0
        : monthlySales.reduce((a, b) => a > b ? a : b);
    final int maxUsersVal = monthlyNewUsers.isEmpty
        ? 1
        : monthlyNewUsers.reduce((a, b) => a > b ? a : b);
    final double chartMaxY = (maxSalesVal > 0 ? maxSalesVal * 1.22 : 100000.0);

    // Compute Top-Selling Stores (أكثر المتاجر مبيعاً وتوريداً)
    final Map<String, _StoreSalesStat> storeStatsMap = {};
    for (final o in validOrders) {
      final rawName = (o.storeName ?? '').trim();
      final key = rawName.isNotEmpty
          ? rawName
          : (o.storeId.isNotEmpty ? o.storeId : (isRtl ? 'متجر مقاييس المعتمد' : 'Verified Store'));
      final displayName = rawName.isNotEmpty
          ? rawName
          : (isRtl ? 'متجر مقاييس المعتمد' : 'Verified Store');
      final prev = storeStatsMap[key];
      storeStatsMap[key] = _StoreSalesStat(
        storeName: displayName,
        totalSales: (prev?.totalSales ?? 0.0) + o.totalAmount,
        ordersCount: (prev?.ordersCount ?? 0) + 1,
        isVerified: true,
      );
    }

    // Merge merchant subscribers so all active stores appear in the leaderboard
    for (final s in allSubscribers) {
      final isMerchant = s.role == UserRole.merchant || (s.storeName != null && s.storeName!.isNotEmpty);
      if (!isMerchant) continue;
      final storeLabel = (isRtl && s.storeNameAr != null && s.storeNameAr!.trim().isNotEmpty)
          ? s.storeNameAr!.trim()
          : ((s.storeName != null && s.storeName!.trim().isNotEmpty)
              ? s.storeName!.trim()
              : (isRtl && s.nameAr.isNotEmpty ? s.nameAr : s.name));
      if (storeLabel.isEmpty) continue;
      if (!storeStatsMap.containsKey(storeLabel)) {
        storeStatsMap[storeLabel] = _StoreSalesStat(
          storeName: storeLabel,
          totalSales: s.totalSpent,
          ordersCount: s.totalOrders,
          isVerified: s.isVerified,
        );
      }
    }

    final List<_StoreSalesStat> topStores = storeStatsMap.values.toList()
      ..sort((a, b) {
        final cmp = b.totalSales.compareTo(a.totalSales);
        if (cmp != 0) return cmp;
        return b.ordersCount.compareTo(a.ordersCount);
      });
    final displayedTopStores = topStores.take(6).toList();
    final double totalTopStoresSales = displayedTopStores.fold(0.0, (sum, item) => sum + item.totalSales);
    final double maxStoreSale = displayedTopStores.isEmpty || displayedTopStores.first.totalSales <= 0
        ? 1.0
        : displayedTopStores.first.totalSales;

    // Overall Program Volume Breakdown
    final double totalProgramVolume =
        (displayGmv + totalBrokerDealVolume + totalAdsRevenue + totalLogisticsFees).clamp(1.0, double.infinity);
    final double avgOrderValue = validOrders.isEmpty ? 0.0 : (displayGmv / validOrders.length);
    final double completionRate = totalOrdersCount == 0
        ? 100.0
        : ((completedOrdersCount / totalOrdersCount) * 100.0);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top Header Row
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 12,
            runSpacing: 10,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF2563EB).withValues(alpha: 0.25),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: const Icon(Icons.show_chart_rounded, color: Colors.white, size: 22),
                  ),
                  const SizedBox(width: 12),
                  Flexible(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl
                              ? 'تحليل الإيرادات والمبيعات ونمو المشتركين وأكثر المتاجر مبيعاً'
                              : 'Executive Revenue, Sales & Subscriber Growth Line Analytics',
                          style: const TextStyle(
                            fontWeight: FontWeight.w900,
                            fontSize: 16.5,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          isRtl
                              ? 'مخطط خطوط بيانية احترافي لآخر 6 أشهر • نظرة تحليلية كاملة على قطاعات البرنامج وأداء المتاجر'
                              : '6-Month interactive multi-line telemetry • Complete program sector breakdown & top stores',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              Wrap(
                spacing: 8,
                runSpacing: 6,
                children: [
                  _chartLegendPill(const Color(0xFF2563EB), isRtl ? 'إجمالي المبيعات والتوريد' : 'Total Sales & GMV'),
                  _chartLegendPill(const Color(0xFF7C3AED), isRtl ? 'عوائد المنصة والإعلانات' : 'Platform & Ads'),
                  _chartLegendPill(const Color(0xFF10B981), isRtl ? 'نمو المشتركين والمتاجر' : 'Subscriber Growth'),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),

          // 4 Executive Analytical Summary Pills
          LayoutBuilder(
            builder: (context, stripConstraints) {
              final int pillCols = stripConstraints.maxWidth > 900 ? 4 : (stripConstraints.maxWidth > 520 ? 2 : 1);
              const double pSpace = 10.0;
              final double pWidth = (stripConstraints.maxWidth - (pSpace * (pillCols - 1))) / pillCols;

              return Wrap(
                spacing: pSpace,
                runSpacing: pSpace,
                children: [
                  _buildAnalyticsMiniStat(
                    width: pWidth,
                    label: isRtl ? 'إجمالي التداول التحليلي (6 أشهر)' : '6-Month Analytical Volume',
                    value: '${currencyFmt.format(totalRevenue6M > 0 ? totalRevenue6M : displayGmv)} ${isRtl ? "ج.س" : "SDG"}',
                    icon: Icons.stacked_line_chart_rounded,
                    color: const Color(0xFF2563EB),
                  ),
                  _buildAnalyticsMiniStat(
                    width: pWidth,
                    label: isRtl ? 'متوسط قيمة طلب التوريد' : 'Average Order Value (AOV)',
                    value: '${currencyFmt.format(avgOrderValue.round())} ${isRtl ? "ج.س" : "SDG"}',
                    icon: Icons.shopping_cart_checkout_rounded,
                    color: const Color(0xFF059669),
                  ),
                  _buildAnalyticsMiniStat(
                    width: pWidth,
                    label: isRtl ? 'نسبة إنجاز وتسليم الطلبات' : 'Order Fulfillment Rate',
                    value: '${completionRate.toStringAsFixed(1)}% ($completedOrdersCount/${totalOrdersCount == 0 ? 0 : totalOrdersCount})',
                    icon: Icons.verified_rounded,
                    color: const Color(0xFF7C3AED),
                  ),
                  _buildAnalyticsMiniStat(
                    width: pWidth,
                    label: isRtl ? 'المشتركين الجدد (آخر 6 أشهر)' : 'New Subscribers (6M)',
                    value: '+$totalNewUsers6M ${isRtl ? "مشترك وتاجر" : "Users & Stores"}',
                    icon: Icons.group_add_rounded,
                    color: const Color(0xFFD97706),
                  ),
                ],
              );
            },
          ),
          const Divider(height: 28, color: Color(0xFFE2E8F0)),

          // Main 2-Column Analytical Body: Line Chart + Sector Breakdown (Right/Start) | Top-Selling Stores (Left/End)
          LayoutBuilder(
            builder: (context, bodyConstraints) {
              final bool isWide = bodyConstraints.maxWidth > 940;

              final Widget lineChartAndSectorsWidget = Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Interactive Multi-Line Chart Box
                  Container(
                    padding: const EdgeInsets.fromLTRB(16, 18, 20, 12),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFF8FAFC), Color(0xFFFFFFFF)],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Wrap(
                          alignment: WrapAlignment.spaceBetween,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          spacing: 8,
                          runSpacing: 4,
                          children: [
                            Text(
                              isRtl
                                  ? 'المنحنى البياني لحركة المبيعات والإيرادات ونمو المشتركين'
                                  : 'Sales, Revenue & Subscriber Growth Curve',
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 13.5,
                                color: Color(0xFF1E293B),
                              ),
                            ),
                            Text(
                              isRtl ? 'مرر المؤشر على النقاط للتفاصيل' : 'Hover points for details',
                              style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 18),
                        SizedBox(
                          height: 220,
                          child: LineChart(
                            LineChartData(
                              minX: 0,
                              maxX: 5,
                              minY: 0,
                              maxY: chartMaxY,
                              gridData: FlGridData(
                                show: true,
                                drawVerticalLine: true,
                                horizontalInterval: (chartMaxY / 4).clamp(1.0, double.infinity),
                                getDrawingHorizontalLine: (value) => FlLine(
                                  color: const Color(0xFFE2E8F0),
                                  strokeWidth: 1,
                                  dashArray: [5, 4],
                                ),
                                getDrawingVerticalLine: (value) => FlLine(
                                  color: const Color(0xFFF1F5F9),
                                  strokeWidth: 1,
                                ),
                              ),
                              borderData: FlBorderData(
                                show: true,
                                border: const Border(
                                  bottom: BorderSide(color: Color(0xFFCBD5E1), width: 1.2),
                                  left: BorderSide(color: Color(0xFFE2E8F0), width: 1),
                                ),
                              ),
                              titlesData: FlTitlesData(
                                topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                                rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                                leftTitles: AxisTitles(
                                  sideTitles: SideTitles(
                                    showTitles: true,
                                    reservedSize: 48,
                                    interval: (chartMaxY / 4).clamp(1.0, double.infinity),
                                    getTitlesWidget: (value, meta) {
                                      if (value <= 0) return const SizedBox.shrink();
                                      String formatted;
                                      if (value >= 1000000) {
                                        formatted = '${(value / 1000000).toStringAsFixed(1)}M';
                                      } else if (value >= 1000) {
                                        formatted = '${(value / 1000).toStringAsFixed(0)}K';
                                      } else {
                                        formatted = value.toInt().toString();
                                      }
                                      return SideTitleWidget(
                                        meta: meta,
                                        child: Text(
                                          formatted,
                                          style: const TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.w700,
                                            color: Color(0xFF64748B),
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                ),
                                bottomTitles: AxisTitles(
                                  sideTitles: SideTitles(
                                    showTitles: true,
                                    reservedSize: 42,
                                    interval: 1,
                                    getTitlesWidget: (value, meta) {
                                      final idx = value.toInt();
                                      if (idx < 0 || idx >= months.length) {
                                        return const SizedBox.shrink();
                                      }
                                      return SideTitleWidget(
                                        meta: meta,
                                        child: Column(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Text(
                                              months[idx],
                                              style: const TextStyle(
                                                fontSize: 11,
                                                fontWeight: FontWeight.w800,
                                                color: Color(0xFF1E293B),
                                              ),
                                            ),
                                            Text(
                                              '+${monthlyNewUsers[idx]} ${isRtl ? "مشترك" : "usr"}',
                                              style: const TextStyle(
                                                fontSize: 9.5,
                                                fontWeight: FontWeight.w700,
                                                color: Color(0xFF059669),
                                              ),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),
                                ),
                              ),
                              lineTouchData: LineTouchData(
                                handleBuiltInTouches: true,
                                touchTooltipData: LineTouchTooltipData(
                                  getTooltipColor: (_) => const Color(0xFF0F172A).withValues(alpha: 0.94),
                                  getTooltipItems: (touchedSpots) {
                                    return touchedSpots.map((spot) {
                                      final idx = spot.x.toInt().clamp(0, months.length - 1);
                                      if (spot.barIndex == 0) {
                                        return LineTooltipItem(
                                          '${months[idx]}\n${isRtl ? "المبيعات: " : "Sales: "}${currencyFmt.format(monthlySales[idx])} ${isRtl ? "ج.س" : "SDG"}',
                                          const TextStyle(
                                            color: Color(0xFF60A5FA),
                                            fontWeight: FontWeight.w800,
                                            fontSize: 11.5,
                                          ),
                                        );
                                      } else if (spot.barIndex == 1) {
                                        return LineTooltipItem(
                                          '${isRtl ? "العوائد والإعلانات: " : "Platform Rev: "}${currencyFmt.format(monthlyPlatformAndAds[idx])} ${isRtl ? "ج.س" : "SDG"}',
                                          const TextStyle(
                                            color: Color(0xFFC084FC),
                                            fontWeight: FontWeight.w700,
                                            fontSize: 11,
                                          ),
                                        );
                                      } else {
                                        return LineTooltipItem(
                                          '${isRtl ? "المشتركين الجدد: " : "New Subscribers: "}+${monthlyNewUsers[idx]}',
                                          const TextStyle(
                                            color: Color(0xFF34D399),
                                            fontWeight: FontWeight.w800,
                                            fontSize: 11,
                                          ),
                                        );
                                      }
                                    }).toList();
                                  },
                                ),
                              ),
                              lineBarsData: [
                                // Series 1: Total Sales & GMV Curve (Royal Blue)
                                LineChartBarData(
                                  spots: List.generate(
                                    6,
                                    (i) => FlSpot(i.toDouble(), monthlySales[i]),
                                  ),
                                  isCurved: true,
                                  curveSmoothness: 0.32,
                                  color: const Color(0xFF2563EB),
                                  barWidth: 3.5,
                                  isStrokeCapRound: true,
                                  dotData: FlDotData(
                                    show: true,
                                    getDotPainter: (spot, percent, barData, index) =>
                                        FlDotCirclePainter(
                                      radius: 4.5,
                                      color: Colors.white,
                                      strokeWidth: 2.5,
                                      strokeColor: const Color(0xFF2563EB),
                                    ),
                                  ),
                                  belowBarData: BarAreaData(
                                    show: true,
                                    gradient: LinearGradient(
                                      colors: [
                                        const Color(0xFF2563EB).withValues(alpha: 0.24),
                                        const Color(0xFF2563EB).withValues(alpha: 0.01),
                                      ],
                                      begin: Alignment.topCenter,
                                      end: Alignment.bottomCenter,
                                    ),
                                  ),
                                ),
                                // Series 2: Platform Commissions & Ads Revenue Curve (Violet)
                                LineChartBarData(
                                  spots: List.generate(
                                    6,
                                    (i) => FlSpot(i.toDouble(), monthlyPlatformAndAds[i]),
                                  ),
                                  isCurved: true,
                                  curveSmoothness: 0.32,
                                  color: const Color(0xFF7C3AED),
                                  barWidth: 2.6,
                                  isStrokeCapRound: true,
                                  dotData: FlDotData(
                                    show: true,
                                    getDotPainter: (spot, percent, barData, index) =>
                                        FlDotCirclePainter(
                                      radius: 3.5,
                                      color: Colors.white,
                                      strokeWidth: 2,
                                      strokeColor: const Color(0xFF7C3AED),
                                    ),
                                  ),
                                  belowBarData: BarAreaData(
                                    show: true,
                                    gradient: LinearGradient(
                                      colors: [
                                        const Color(0xFF7C3AED).withValues(alpha: 0.14),
                                        const Color(0xFF7C3AED).withValues(alpha: 0.0),
                                      ],
                                      begin: Alignment.topCenter,
                                      end: Alignment.bottomCenter,
                                    ),
                                  ),
                                ),
                                // Series 3: Subscriber Growth Curve (Scaled to chart Y for visual correlation)
                                LineChartBarData(
                                  spots: List.generate(6, (i) {
                                    final double normalizedY = maxUsersVal > 0
                                        ? (monthlyNewUsers[i] / maxUsersVal) * (chartMaxY * 0.72)
                                        : 0.0;
                                    return FlSpot(i.toDouble(), normalizedY);
                                  }),
                                  isCurved: true,
                                  curveSmoothness: 0.35,
                                  color: const Color(0xFF10B981),
                                  barWidth: 2.8,
                                  dashArray: [6, 3],
                                  isStrokeCapRound: true,
                                  dotData: FlDotData(
                                    show: true,
                                    getDotPainter: (spot, percent, barData, index) =>
                                        FlDotCirclePainter(
                                      radius: 4,
                                      color: const Color(0xFF10B981),
                                      strokeWidth: 2,
                                      strokeColor: Colors.white,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Comprehensive Program Sales & Sector Distribution
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.pie_chart_rounded, size: 18, color: Color(0xFF1E40AF)),
                            const SizedBox(width: 8),
                            Text(
                              isRtl
                                  ? 'التحليل الشامل لتوزيع مبيعات وقطاعات البرنامج'
                                  : 'Complete Program Sales & Sector Breakdown',
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 13.5,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _buildSectorShareBar(
                          label: isRtl ? 'مبيعات التوريد المباشر بالمنصة' : 'Direct Supply Orders GMV',
                          amountText: '${currencyFmt.format(displayGmv)} ${isRtl ? "ج.س" : "SDG"}',
                          ratio: (displayGmv / totalProgramVolume).clamp(0.0, 1.0),
                          color: const Color(0xFF059669),
                        ),
                        const SizedBox(height: 8),
                        _buildSectorShareBar(
                          label: isRtl ? 'حجم صفقات الوسيط الرقمي (B2B)' : 'B2B Digital Broker Volume',
                          amountText: '${currencyFmt.format(totalBrokerDealVolume)} ${isRtl ? "ج.س" : "SDG"}',
                          ratio: (totalBrokerDealVolume / totalProgramVolume).clamp(0.0, 1.0),
                          color: const Color(0xFFD97706),
                        ),
                        const SizedBox(height: 8),
                        _buildSectorShareBar(
                          label: isRtl ? 'إيرادات وحملات الشركات والمعلنين' : 'Advertiser Campaigns Revenue',
                          amountText: '${currencyFmt.format(totalAdsRevenue)} ${isRtl ? "ج.س" : "SDG"}',
                          ratio: (totalAdsRevenue / totalProgramVolume).clamp(0.0, 1.0),
                          color: const Color(0xFFEA580C),
                        ),
                        const SizedBox(height: 8),
                        _buildSectorShareBar(
                          label: isRtl ? 'حركة النقل والعتالة والخدمات اللوجستية' : 'Logistics & Fleet Operations',
                          amountText: '${currencyFmt.format(totalLogisticsFees)} ${isRtl ? "ج.س" : "SDG"}',
                          ratio: (totalLogisticsFees / totalProgramVolume).clamp(0.0, 1.0),
                          color: const Color(0xFF0284C7),
                        ),
                      ],
                    ),
                  ),
                ],
              );

              // Top-Selling Stores Leaderboard Widget
              final Widget topStoresLeaderboardWidget = Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(7),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF59E0B).withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.emoji_events_rounded, color: Color(0xFFD97706), size: 20),
                            ),
                            const SizedBox(width: 8),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  isRtl ? 'أكثر المتاجر مبيعاً وتوريداً' : 'Top-Selling Stores',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w900,
                                    fontSize: 14.5,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                                Text(
                                  isRtl ? 'تصنيف المتاجر الأعلى أداءً وإيراداً' : 'Ranked by sales volume & orders',
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                ),
                              ],
                            ),
                          ],
                        ),
                        TextButton(
                          onPressed: () => context.go(RouteNames.adminSubscribers),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            minimumSize: Size.zero,
                          ),
                          child: Text(
                            isRtl ? 'كل المتاجر' : 'All Stores',
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF1E40AF)),
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 20, color: Color(0xFFE2E8F0)),
                    if (displayedTopStores.isEmpty)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 32),
                        child: Center(
                          child: Text(
                            isRtl ? 'لا توجد بيانات مبيعات متاجر مسجلة بعد' : 'No store sales recorded yet',
                            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                          ),
                        ),
                      )
                    else
                      ...List.generate(displayedTopStores.length, (idx) {
                        final st = displayedTopStores[idx];
                        final double progress = maxStoreSale > 0
                            ? (st.totalSales / maxStoreSale).clamp(0.08, 1.0)
                            : 0.15;
                        final double sharePct = totalTopStoresSales > 0
                            ? (st.totalSales / totalTopStoresSales) * 100
                            : 0.0;

                        final List<Color> rankColors = [
                          const Color(0xFFD97706), // #1 Gold
                          const Color(0xFF475569), // #2 Silver
                          const Color(0xFFB45309), // #3 Bronze
                          const Color(0xFF2563EB),
                          const Color(0xFF7C3AED),
                          const Color(0xFF0D9488),
                        ];
                        final Color badgeColor = rankColors[idx % rankColors.length];

                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: idx == 0
                                  ? const Color(0xFFF59E0B).withValues(alpha: 0.45)
                                  : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 24,
                                    height: 24,
                                    alignment: Alignment.center,
                                    decoration: BoxDecoration(
                                      color: badgeColor.withValues(alpha: 0.14),
                                      borderRadius: BorderRadius.circular(7),
                                    ),
                                    child: Text(
                                      '#${idx + 1}',
                                      style: TextStyle(
                                        fontWeight: FontWeight.w900,
                                        fontSize: 11,
                                        color: badgeColor,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Row(
                                      children: [
                                        Flexible(
                                          child: Text(
                                            st.storeName,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w800,
                                              fontSize: 12.5,
                                              color: Color(0xFF0F172A),
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        if (st.isVerified) ...[
                                          const SizedBox(width: 4),
                                          const Icon(Icons.verified_rounded, size: 13, color: Color(0xFF2563EB)),
                                        ],
                                      ],
                                    ),
                                  ),
                                  Text(
                                    '${currencyFmt.format(st.totalSales)} ${isRtl ? "ج.س" : "SDG"}',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w900,
                                      fontSize: 12,
                                      color: idx == 0 ? const Color(0xFFB45309) : const Color(0xFF0F172A),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  Expanded(
                                    child: ClipRRect(
                                      borderRadius: BorderRadius.circular(6),
                                      child: LinearProgressIndicator(
                                        value: progress,
                                        minHeight: 6,
                                        backgroundColor: const Color(0xFFF1F5F9),
                                        valueColor: AlwaysStoppedAnimation<Color>(badgeColor),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Text(
                                    '${st.ordersCount} ${isRtl ? "طلب" : "orders"} • ${sharePct.toStringAsFixed(0)}%',
                                    style: const TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF64748B),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      }),
                  ],
                ),
              );

              if (isWide) {
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(flex: 7, child: lineChartAndSectorsWidget),
                    const SizedBox(width: 18),
                    Expanded(flex: 5, child: topStoresLeaderboardWidget),
                  ],
                );
              }
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  lineChartAndSectorsWidget,
                  const SizedBox(height: 18),
                  topStoresLeaderboardWidget,
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildAnalyticsMiniStat({
    required double width,
    required String label,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(9),
            ),
            child: Icon(icon, size: 17, color: color),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: color),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectorShareBar({
    required String label,
    required String amountText,
    required double ratio,
    required Color color,
  }) {
    final pct = (ratio * 100).toStringAsFixed(1);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Text(
                label,
                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            Text(
              '$amountText ($pct%)',
              style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: color),
            ),
          ],
        ),
        const SizedBox(height: 4),
        ClipRRect(
          borderRadius: BorderRadius.circular(6),
          child: LinearProgressIndicator(
            value: ratio.clamp(0.04, 1.0),
            minHeight: 6,
            backgroundColor: const Color(0xFFE2E8F0),
            valueColor: AlwaysStoppedAnimation<Color>(color),
          ),
        ),
      ],
    );
  }

  Widget _chartLegendPill(Color c, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: c.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 8, height: 8, decoration: BoxDecoration(color: c, shape: BoxShape.circle)),
          const SizedBox(width: 6),
          Text(label, style: TextStyle(color: c, fontSize: 11, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // RECENT ADVERTISER CAMPAIGNS & PAYMENTS TABLE (دفعات وحملات المعلنين)
  // ════════════════════════════════════════════════════════════════
  Widget _buildAdvertiserCampaignsCard(BuildContext context, bool isRtl, List<AdModel> ads) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    final recentAds = ads.take(10).toList();
    final activeCount = ads.where((a) => a.isActive).length;
    double totalRevenue = 0.0;
    for (var a in ads) {
      if (a.price > 0) totalRevenue += a.price;
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 12,
            runSpacing: 10,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEA580C).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.campaign_rounded, color: Color(0xFFEA580C), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Flexible(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Wrap(
                          spacing: 10,
                          runSpacing: 4,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Text(
                              isRtl ? 'حملات ودفعات المعلنين' : 'Advertiser Campaigns & Payments',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0F172A)),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEA580C).withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                '$activeCount ${isRtl ? "نشط" : "Active"}',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFEA580C)),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          isRtl
                              ? 'إجمالي إيرادات المعلنين: ${currencyFmt.format(totalRevenue)} ${isRtl ? "ج.س" : "SDG"} • متابعة مباشرة لمدفوعات الحملات والشركات'
                              : 'Total Ad Revenue: ${currencyFmt.format(totalRevenue)} SDG • Live campaign payments and sponsorships',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              FilledButton.tonalIcon(
                onPressed: () => context.go(RouteNames.adminAds),
                icon: Icon(isRtl ? Icons.arrow_back : Icons.arrow_forward, size: 16),
                label: Text(isRtl ? 'إدارة كافة الإعلانات' : 'Manage All Ads'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFEA580C).withValues(alpha: 0.08),
                  foregroundColor: const Color(0xFFEA580C),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          recentAds.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.campaign_outlined, size: 40, color: Colors.grey.shade400),
                        const SizedBox(height: 8),
                        Text(
                          isRtl ? 'لا توجد حملات إعلانية مسجلة حتى الآن' : 'No ad campaigns registered yet',
                          style: TextStyle(color: Colors.grey.shade500),
                        ),
                      ],
                    ),
                  ),
                )
              : _buildFullWidthResponsiveTable(
                  headers: [
                    _TableColumnSpec(label: isRtl ? 'اسم المعلن / الشركة' : 'Advertiser / Company', flex: 3),
                    _TableColumnSpec(label: isRtl ? 'عنوان الحملة' : 'Campaign Title', flex: 4),
                    _TableColumnSpec(label: isRtl ? 'المبلغ المدفوع' : 'Paid Amount', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'الوسم الترويجي' : 'Target Tag', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'فترة الحملة' : 'Duration', flex: 3),
                    _TableColumnSpec(label: isRtl ? 'الحالة' : 'Status', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'إجراء' : 'Action', flex: 1, alignEnd: true),
                  ],
                  rows: recentAds.map((ad) {
                    final isActive = ad.isActive;
                    final company = ad.company.isNotEmpty ? ad.company : (isRtl ? 'معلن غير محدد' : 'Unknown');
                    final title = ad.title.isNotEmpty ? ad.title : '—';
                    final target = ad.target.isNotEmpty ? ad.target : '—';
                    final dates = '${ad.startDate.isNotEmpty ? ad.startDate : '—'} → ${ad.endDate.isNotEmpty ? ad.endDate : '—'}';

                    return [
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 14,
                            backgroundColor: const Color(0xFFEA580C).withValues(alpha: 0.12),
                            child: Text(
                              company.isNotEmpty ? company.characters.first : 'A',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFEA580C)),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              company,
                              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: Color(0xFF0F172A)),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        title,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12, color: Color(0xFF1E293B)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFF059669).withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '${currencyFmt.format(ad.price)} ${isRtl ? "ج.س" : "SDG"}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF059669)),
                          ),
                        ),
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Text(
                            target,
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ),
                      Text(
                        dates,
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isActive
                                ? const Color(0xFF10B981).withValues(alpha: 0.12)
                                : Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            isActive ? (isRtl ? 'نشط 🟢' : 'Active 🟢') : (isRtl ? 'متوقف ⚪' : 'Ended ⚪'),
                            style: TextStyle(
                              color: isActive ? const Color(0xFF10B981) : Colors.grey.shade700,
                              fontWeight: FontWeight.bold,
                              fontSize: 11,
                            ),
                          ),
                        ),
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerEnd,
                        child: IconButton(
                          icon: const Icon(Icons.open_in_new_rounded, size: 18, color: Color(0xFFEA580C)),
                          onPressed: () => context.go(RouteNames.adminAds),
                          tooltip: isRtl ? 'عرض الحملة وتعديلها في الإعلانات' : 'Open in Ads Manager',
                        ),
                      ),
                    ];
                  }).toList(),
                ),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // FULL-WIDTH RECENT SUPPLY & PURCHASE ORDERS TABLE
  // ════════════════════════════════════════════════════════════════
  Widget _buildFullWidthLatestOrders(BuildContext context, bool isRtl, List<OrderModel> orders) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    final recentOrders = orders.take(12).toList();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 12,
            runSpacing: 10,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF059669).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.shopping_bag_rounded, color: Color(0xFF059669), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Flexible(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'أحدث حركات التوريد والشراء بالسوق' : 'Recent Supply & Purchasing Orders',
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0F172A)),
                        ),
                        Text(
                          isRtl ? 'عرض مباشر لكافة الطلبات، التوريدات، الموردين، وحالات الدفع' : 'Live marketplace orders, suppliers, and payment status',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              FilledButton.tonalIcon(
                onPressed: () => context.go(RouteNames.adminOrders),
                icon: Icon(isRtl ? Icons.arrow_back : Icons.arrow_forward, size: 16),
                label: Text(isRtl ? 'عرض كافة الطلبات' : 'View All Orders'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF1E40AF).withValues(alpha: 0.08),
                  foregroundColor: const Color(0xFF1E40AF),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          recentOrders.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.shopping_cart_outlined, size: 40, color: Colors.grey.shade400),
                        const SizedBox(height: 8),
                        Text(
                          isRtl ? 'لا توجد طلبات توريد حالياً' : 'No orders found',
                          style: TextStyle(color: Colors.grey.shade500),
                        ),
                      ],
                    ),
                  ),
                )
              : _buildFullWidthResponsiveTable(
                  headers: [
                    _TableColumnSpec(label: isRtl ? 'رقم الطلب' : 'Order ID', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'المشتري / العميل' : 'Customer', flex: 3),
                    _TableColumnSpec(label: isRtl ? 'المتجر / المورد' : 'Supplier / Store', flex: 3),
                    _TableColumnSpec(label: isRtl ? 'إجمالي التوريد' : 'Total Amount', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'طريقة الدفع' : 'Payment Method', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'الحالة' : 'Status', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'التاريخ' : 'Date', flex: 2),
                    _TableColumnSpec(label: isRtl ? 'إجراء' : 'Action', flex: 1, alignEnd: true),
                  ],
                  rows: recentOrders.map((order) {
                    final shortId = order.formattedOrderNumber;
                    final statusColor = _getStatusColor(order.status);
                    final statusText = _getStatusText(order.status, isRtl);
                    final dateStr = DateFormat('yyyy-MM-dd HH:mm').format(order.createdAt.toLocal());
                    final buyer = order.buyerName ?? order.buyerId;
                    final store = order.storeName ?? (isRtl ? 'متجر معتمد' : 'Verified Store');
                    final method = order.paymentMethod ?? (isRtl ? 'تحويل بنكي' : 'Bank Transfer');

                    return [
                      Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E40AF).withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '#$shortId',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF1E40AF)),
                          ),
                        ),
                      ),
                      Text(
                        buyer,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12, color: Color(0xFF0F172A)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Row(
                        children: [
                          const Icon(Icons.storefront, size: 14, color: Colors.blueGrey),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              store,
                              style: const TextStyle(fontSize: 12, color: Color(0xFF334155)),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        '${currencyFmt.format(order.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        method,
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerStart,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: statusColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            statusText,
                            style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 11),
                          ),
                        ),
                      ),
                      Text(
                        dateStr,
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerEnd,
                        child: IconButton(
                          icon: const Icon(Icons.visibility_outlined, size: 18, color: Color(0xFF1E40AF)),
                          onPressed: () => _showOrderDetails(context, ref, order, isRtl),
                          tooltip: isRtl ? 'عرض التفاصيل وتعديل الحالة' : 'View Details',
                        ),
                      ),
                    ];
                  }).toList(),
                ),
        ],
      ),
    );
  }

  /// Builds a 100% full-width table that stretches proportionally across the entire screen width
  /// with zero empty space on the left side, while allowing horizontal scroll on narrow mobile screens.
  Widget _buildFullWidthResponsiveTable({
    required List<_TableColumnSpec> headers,
    required List<List<Widget>> rows,
  }) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final double tableWidth = constraints.maxWidth < 860 ? 860 : constraints.maxWidth;
        return SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: SizedBox(
            width: tableWidth,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Header Row
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: headers.map((h) {
                      return Expanded(
                        flex: h.flex,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 6),
                          child: Text(
                            h.label,
                            textAlign: h.alignEnd ? TextAlign.end : TextAlign.start,
                            style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 12,
                              color: Color(0xFF475569),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
                const SizedBox(height: 4),
                // Data Rows
                ...List.generate(rows.length, (rIdx) {
                  final cells = rows[rIdx];
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: rIdx.isEven ? Colors.white : const Color(0xFFFCFDFE),
                      border: const Border(
                        bottom: BorderSide(color: Color(0xFFF1F5F9), width: 1),
                      ),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: List.generate(headers.length, (cIdx) {
                        final spec = headers[cIdx];
                        final cellWidget = cIdx < cells.length ? cells[cIdx] : const SizedBox.shrink();
                        return Expanded(
                          flex: spec.flex,
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 6),
                            child: cellWidget,
                          ),
                        );
                      }),
                    ),
                  );
                }),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _showUpdatePlatformUsdDialog(BuildContext context, bool isRtl, double currentRate) async {
    final rateCtrl = TextEditingController(text: currentRate.toString());
    await showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.currency_exchange_rounded, color: Color(0xFF16A34A), size: 26),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                isRtl ? 'سعر صرف الدولار المعتمد للمنصة' : 'Official Platform USD Exchange Rate',
                style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isRtl
                  ? 'هذا السعر هو السعر المرجعي المعتمد لكافة المتاجر والمنتجات المرتبطة بسعر صرف منصة مقاييس. سيتم تطبيق التحديث وتعميمه فوراً.'
                  : 'This is the standard exchange rate for all stores synced with Magaiees Platform. Updates broadcast immediately.',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600, height: 1.4),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: rateCtrl,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              autofocus: true,
              decoration: InputDecoration(
                labelText: isRtl ? 'سعر الدولار المعتمد (ج.س)' : 'Platform USD Rate (SDG)',
                suffixText: isRtl ? 'ج.س' : 'SDG',
                prefixIcon: const Icon(Icons.attach_money_rounded, color: Color(0xFF16A34A)),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          FilledButton.icon(
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFF16A34A),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(Icons.check_circle_rounded, size: 18),
            label: Text(isRtl ? 'حفظ وتحديث للمنصة' : 'Save & Broadcast'),
            onPressed: () async {
              final newRate = double.tryParse(rateCtrl.text.trim());
              if (newRate != null && newRate > 0) {
                Navigator.pop(ctx);
                await ref.read(platformUsdRateSettingProvider.notifier).updateRate(newRate);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        isRtl
                            ? 'تم تحديث سعر صرف الدولار المعتمد للمنصة إلى: ${NumberFormat('#,##0').format(newRate)} ج.س'
                            : 'Platform USD exchange rate updated to: ${NumberFormat('#,##0').format(newRate)} SDG',
                      ),
                      backgroundColor: const Color(0xFF16A34A),
                      duration: const Duration(seconds: 2),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                }
              }
            },
          ),
        ],
      ),
    );
    rateCtrl.dispose();
  }
}
