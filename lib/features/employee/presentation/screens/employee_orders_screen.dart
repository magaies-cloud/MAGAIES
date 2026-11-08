import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/employee/application/employee_orders_provider.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';

const Color _kDarkNavy = Color(0xFF0F172A);
const Color _kRoyalBlue = Color(0xFF2563EB);
const Color _kEmerald = Color(0xFF10B981);
const Color _kAmber = Color(0xFFF59E0B);
const Color _kCrimson = Color(0xFFEF4444);

class EmployeeOrdersScreen extends ConsumerStatefulWidget {
  const EmployeeOrdersScreen({super.key});

  @override
  ConsumerState<EmployeeOrdersScreen> createState() => _EmployeeOrdersScreenState();
}

class _EmployeeOrdersScreenState extends ConsumerState<EmployeeOrdersScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: ref.read(employeeOrdersTabProvider).clamp(0, 1),
    );
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        ref.read(employeeOrdersTabProvider.notifier).state = _tabController.index;
      }
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  String _fmtSdg(double v) => NumberFormat.decimalPattern('ar').format(v);

  Future<void> _makePhoneCall(String phone) async {
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    if (cleanPhone.isEmpty) return;
    final Uri launchUri = Uri(scheme: 'tel', path: cleanPhone);
    try {
      if (await canLaunchUrl(launchUri)) {
        await launchUrl(launchUri);
      }
    } catch (_) {}
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'delivered':
        return _kEmerald;
      case 'shipped':
        return _kRoyalBlue;
      case 'processing':
        return _kAmber;
      case 'cancelled':
        return _kCrimson;
      default:
        return const Color(0xFF64748B);
    }
  }

  String _getStatusArabic(String status) {
    switch (status) {
      case 'delivered':
        return 'تم التسليم';
      case 'shipped':
        return 'تم الشحن';
      case 'processing':
        return 'قيد التجهيز';
      case 'cancelled':
        return 'ملغي';
      default:
        return 'قيد الانتظار';
    }
  }

  Widget _buildStatusChip({
    required String title,
    required bool isActive,
    required Color activeColor,
    required IconData icon,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 4),
        decoration: BoxDecoration(
          color: isActive ? activeColor.withValues(alpha: 0.15) : Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isActive ? activeColor : Colors.grey.shade300,
            width: isActive ? 1.5 : 1,
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isActive ? Icons.check_circle_rounded : icon,
              size: 13,
              color: isActive ? activeColor : Colors.grey.shade600,
            ),
            const SizedBox(width: 4),
            Flexible(
              child: Text(
                title,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: isActive ? FontWeight.bold : FontWeight.w600,
                  color: isActive ? activeColor : Colors.grey.shade700,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  List<Map<String, dynamic>> _parseAllReceipts(String notes, {String? fallbackUrl}) {
    final List<Map<String, dynamic>> receipts = [];
    if (notes.contains(RegExp(r'(?<!\w)receipt_url:'))) {
      final parts = notes.split(RegExp(r'(?<!\w)receipt_url:'));
      for (int i = 1; i < parts.length; i++) {
        final part = parts[i];
        final url = part.split('|')[0].trim().split(' ')[0].trim();
        String ref = '';
        String bank = 'bankak';
        double amount = 0;

        if (part.contains('ref:')) {
          ref = part.split('ref:')[1].split('|')[0].trim().split('\n')[0].trim();
        }
        if (part.contains('bank:')) {
          bank = part.split('bank:')[1].split('|')[0].trim().split('\n')[0].trim();
        }
        if (part.contains('amount:')) {
          final amtStr = part.split('amount:')[1].split('|')[0].trim().split('\n')[0].trim();
          amount = double.tryParse(amtStr) ?? 0;
        }

        receipts.add({
          'url': url,
          'ref': ref,
          'bank': bank,
          'amount': amount,
        });
      }
    } else if (fallbackUrl != null && fallbackUrl.isNotEmpty) {
      receipts.add({
        'url': fallbackUrl,
        'ref': '',
        'bank': 'bank_transfer',
        'amount': 0.0,
      });
    }
    return receipts;
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(employeeOrdersProvider);

    ref.listen<int>(employeeOrdersTabProvider, (prev, next) {
      if (_tabController.index != next && next >= 0 && next < 2) {
        _tabController.animateTo(next);
      }
    });

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: _kRoyalBlue.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.radar_rounded, color: _kRoyalBlue, size: 22),
              ),
              const SizedBox(width: 12),
              const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('مركز إدارة واستلام الطلبات', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: _kDarkNavy)),
                  Text('رادار مقاييس الحصري ومتابعة التوصيل', style: TextStyle(fontSize: 11, color: Colors.grey)),
                ],
              ),
            ],
          ),
          backgroundColor: Colors.white,
          foregroundColor: _kDarkNavy,
          elevation: 0,
          actions: [
            IconButton(
              icon: const Icon(Icons.volume_up_rounded, color: _kAmber),
              tooltip: 'اختبار نغمة إشعار مقاييس',
              onPressed: () {
                ref.read(employeeOrdersProvider.notifier).playOrderNotificationSound();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('🔔 تم تشغيل نغمة تنبيه مقاييس الرسمية.'), backgroundColor: _kRoyalBlue, duration: Duration(seconds: 2)),
                );
              },
            ),
            IconButton(
              icon: state.isLoading
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: _kRoyalBlue))
                  : const Icon(Icons.refresh_rounded, color: _kRoyalBlue),
              tooltip: 'تحديث فوري',
              onPressed: () => ref.read(employeeOrdersProvider.notifier).refreshAllOrders(),
            ),
            const SizedBox(width: 8),
          ],
          bottom: TabBar(
            controller: _tabController,
            labelColor: _kRoyalBlue,
            unselectedLabelColor: Colors.grey.shade600,
            indicatorColor: _kRoyalBlue,
            indicatorWeight: 3,
            tabs: [
              Tab(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.electric_bolt_rounded, size: 18),
                    const SizedBox(width: 8),
                    Text('طلبات جديدة متاحة (${state.pendingOrders.length})', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
              Tab(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.local_shipping_rounded, size: 18),
                    const SizedBox(width: 8),
                    Text('طلباتي قيد التوصيل (${state.myOrders.length})', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ],
          ),
        ),
        body: TabBarView(
          controller: _tabController,
          children: [
            // Tab 1: Pending Radar Orders
            RefreshIndicator(
              onRefresh: () => ref.read(employeeOrdersProvider.notifier).refreshAllOrders(),
              child: state.pendingOrders.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(24),
                            decoration: BoxDecoration(
                              color: _kRoyalBlue.withValues(alpha: 0.08),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.radar_rounded, size: 64, color: _kRoyalBlue),
                          ),
                          const SizedBox(height: 16),
                          const Text('الرادار يبحث عن طلبات حصرية جديدة...', style: TextStyle(color: _kDarkNavy, fontSize: 18, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 6),
                          Text('أي طلب جديد للمنتجات الحصرية على مقاييس سيصل هنا مصحوباً بنغمة التنبيه.', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                          const SizedBox(height: 20),
                          FilledButton.icon(
                            style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
                            onPressed: () => ref.read(employeeOrdersProvider.notifier).refreshAllOrders(),
                            icon: const Icon(Icons.refresh_rounded),
                            label: const Text('تحديث الرادار الآن'),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: state.pendingOrders.length,
                      itemBuilder: (ctx, index) {
                        final order = state.pendingOrders[index];
                        return _buildOrderCard(order, isPendingTab: true);
                      },
                    ),
            ),

            // Tab 2: My Assigned Orders
            RefreshIndicator(
              onRefresh: () => ref.read(employeeOrdersProvider.notifier).refreshAllOrders(),
              child: state.myOrders.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.inventory_2_outlined, size: 70, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          const Text('لا توجد طلبات جارية مستلمة حالياً', style: TextStyle(color: _kDarkNavy, fontSize: 18, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 6),
                          Text('يمكنك قبول واستلام الطلبات المتاحة من تبويب "طلبات جديدة".', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: state.myOrders.length,
                      itemBuilder: (ctx, index) {
                        final order = state.myOrders[index];
                        return _buildOrderCard(order, isPendingTab: false);
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrderCard(OrderModel order, {required bool isPendingTab}) {
    final isDelivered = order.status == 'delivered';
    final isShipped = order.status == 'shipped';
    final isProcessing = order.status == 'processing';
    final isCancelled = order.status == 'cancelled';
    final isPending = order.status == 'pending';

    final isSelfPickup = order.notes.contains('استلام وترحيل ذاتي') || order.address.contains('استلام وترحيل ذاتي');
    final bool hasDeliveryFeesSet = isSelfPickup || (order.transportFee != null || order.offloadingFee != null);
    final bool buyerApprovedFees = isSelfPickup || (order.buyerApprovedFees == true);
    final bool awaitingFeesApproval = !isSelfPickup && hasDeliveryFeesSet && (order.buyerApprovedFees != true);

    final double transport = order.transportFee ?? 0.0;
    final double offloading = order.offloadingFee ?? 0.0;
    final double remaining = (order.totalSdg - order.paidAmount).clamp(0.0, double.infinity);

    final parsedReceipts = _parseAllReceipts(order.notes, fallbackUrl: order.receiptUrl);
    final hasReceipts = order.paymentStatus == 'under_review' || parsedReceipts.isNotEmpty;

    return Container(
      margin: const EdgeInsets.only(bottom: 18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isPendingTab ? const Color(0xFFBFDBFE) : Colors.grey.shade200,
          width: isPendingTab ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: isPendingTab ? _kRoyalBlue.withValues(alpha: 0.08) : Colors.black.withValues(alpha: 0.04),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. Header: Order #, Tag, Status, and Date
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.verified_rounded, color: _kRoyalBlue, size: 14),
                          SizedBox(width: 4),
                          Text('حصري على مقاييس', style: TextStyle(color: _kRoyalBlue, fontSize: 11.5, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      '#${order.orderNumber}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: _kDarkNavy),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: _getStatusColor(order.status).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    _getStatusArabic(order.status),
                    style: TextStyle(color: _getStatusColor(order.status), fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              DateFormat('yyyy-MM-dd | hh:mm a', 'ar').format(order.createdAt),
              style: TextStyle(fontSize: 11.5, color: Colors.grey.shade500),
            ),
            const Divider(height: 20),

            // 2. Customer Info Card
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      const Icon(Icons.person_outline_rounded, color: _kRoyalBlue, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          order.buyerName.isNotEmpty ? order.buyerName : 'عميل مقاييس',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: _kDarkNavy),
                        ),
                      ),
                      if (order.buyerPhone.isNotEmpty) ...[
                        InkWell(
                          onTap: () => _makePhoneCall(order.buyerPhone),
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: _kEmerald.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.phone_in_talk_rounded, color: _kEmerald, size: 15),
                                const SizedBox(width: 4),
                                Text(order.buyerPhone, style: const TextStyle(color: _kEmerald, fontWeight: FontWeight.bold, fontSize: 12)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        isSelfPickup ? Icons.storefront_rounded : Icons.location_on_rounded,
                        color: isSelfPickup ? _kAmber : Colors.grey.shade600,
                        size: 18,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          isSelfPickup ? 'استلام وترحيل ذاتي بواسطة المشتري (بدون رسوم توصيل)' : order.address,
                          style: TextStyle(
                            fontSize: 13,
                            color: isSelfPickup ? const Color(0xFFB45309) : _kDarkNavy,
                            fontWeight: isSelfPickup ? FontWeight.bold : FontWeight.normal,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // 3. Order Items Breakdown (Invoice)
            if (order.orderItems.isNotEmpty) ...[
              const Text('تفاصيل المنتجات:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy)),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey.shade200),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  children: [
                    for (int i = 0; i < order.orderItems.length; i++) ...[
                      if (i > 0) Divider(height: 1, color: Colors.grey.shade200),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                order.orderItems[i]['product_name']?.toString() ?? 'منتج حصري',
                                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12.5),
                              ),
                            ),
                            Text(
                              '${order.orderItems[i]['quantity']} طن × ${_fmtSdg((order.orderItems[i]['price_sdg'] as num?)?.toDouble() ?? 0)} ج.س',
                              style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600),
                            ),
                            const SizedBox(width: 12),
                            Text(
                              '${_fmtSdg(((order.orderItems[i]['quantity'] as num?)?.toDouble() ?? 1) * ((order.orderItems[i]['price_sdg'] as num?)?.toDouble() ?? 0))} ج.س',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: _kDarkNavy),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 14),
            ],

            // 4. Detailed Financial Summary
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  if (!isSelfPickup) ...[
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('رسوم الترحيل:', style: TextStyle(fontSize: 12.5, color: Color(0xFF475569))),
                        Text(transport > 0 ? '${_fmtSdg(transport)} ج.س' : 'غير محددة', style: TextStyle(fontWeight: FontWeight.bold, color: transport > 0 ? _kAmber : Colors.grey)),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('رسوم التنزيل:', style: TextStyle(fontSize: 12.5, color: Color(0xFF475569))),
                        Text(offloading > 0 ? '${_fmtSdg(offloading)} ج.س' : 'غير محددة', style: TextStyle(fontWeight: FontWeight.bold, color: offloading > 0 ? _kAmber : Colors.grey)),
                      ],
                    ),
                    const Divider(height: 16),
                  ],
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('إجمالي الطلب:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: _kDarkNavy)),
                      Text('${_fmtSdg(order.totalSdg)} ج.س', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: _kDarkNavy)),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('المبلغ المدفوع:', style: TextStyle(fontSize: 12.5, color: _kEmerald)),
                      Text('${_fmtSdg(order.paidAmount)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: _kEmerald)),
                    ],
                  ),
                  if (remaining > 0) ...[
                    const SizedBox(height: 6),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('المتبقي للسداد:', style: TextStyle(fontSize: 12.5, color: _kCrimson)),
                        Text('${_fmtSdg(remaining)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kCrimson)),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 12),

            // 5. Delivery Fees Status / Banner
            if (!isSelfPickup) ...[
              if (!hasDeliveryFeesSet) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.info_outline_rounded, color: _kRoyalBlue, size: 18),
                      const SizedBox(width: 8),
                      const Expanded(
                        child: Text(
                          'يلزم تحديد رسوم التوصيل وإرسالها لموافقة المشتري للمتابعة.',
                          style: TextStyle(color: _kRoyalBlue, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                      TextButton.icon(
                        onPressed: () => _showSetFeesDialog(context, order),
                        icon: const Icon(Icons.edit_location_alt_rounded, size: 16),
                        label: const Text('تحديد الآن'),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 10),
              ] else if (awaitingFeesApproval) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFFDE68A)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.hourglass_top_rounded, color: _kAmber, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '⏳ تم إرسال رسوم التوصيل (${_fmtSdg(transport + offloading)} ج.س) وبانتظار موافقة المشتري.',
                          style: const TextStyle(color: Color(0xFFB45309), fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                      TextButton(
                        onPressed: () => _showSetFeesDialog(context, order),
                        child: const Text('تعديل'),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 10),
              ] else if (buyerApprovedFees && isPending) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.check_circle_rounded, color: _kEmerald, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          (transport > 0 || offloading > 0)
                              ? '✅ وافق المشتري على رسوم التوصيل (${_fmtSdg(transport + offloading)} ج.س) - جاهز للقبول والتجهيز.'
                              : '✅ تم تأكيد التوصيل وموافقة المشتري - جاهز للقبول والتجهيز.',
                          style: const TextStyle(color: Color(0xFF065F46), fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 10),
              ],
            ],

            // 6. Payment Receipt Inspection Banner
            if (hasReceipts) ...[
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF7ED),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFFDBA74)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.receipt_long_rounded, color: Color(0xFFC2410C), size: 20),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('📑 إشعار تحويل بنكي مرفوع من المشتري', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: Color(0xFF9A3412))),
                          Text('عدد الإشعارات: ${parsedReceipts.length}', style: const TextStyle(fontSize: 11, color: Color(0xFFC2410C))),
                        ],
                      ),
                    ),
                    FilledButton.tonal(
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFFFED7AA),
                        foregroundColor: const Color(0xFF9A3412),
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      ),
                      onPressed: () => _showEmployeeReceiptDialog(context, order, parsedReceipts, remaining),
                      child: const Text('معاينة الإشعار', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],

            // 7. Middle Row: Chat with Buyer & Add Payment
            Row(
              children: [
                if (remaining > 0 && !isDelivered && !isCancelled) ...[
                  Expanded(
                    flex: 3,
                    child: OutlinedButton.icon(
                      onPressed: () => _showPaymentDialog(context, order, remaining),
                      icon: const Icon(Icons.add_card_rounded, size: 16),
                      label: const Text('تأكيد استلام دفعة', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: _kEmerald,
                        side: const BorderSide(color: _kEmerald),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                ],
                Expanded(
                  flex: 2,
                  child: OutlinedButton.icon(
                    onPressed: () => _handleChat(context, order),
                    icon: const Icon(Icons.chat_bubble_outline_rounded, size: 16),
                    label: const Text('محادثة المشتري', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: _kRoyalBlue,
                      side: const BorderSide(color: _kRoyalBlue),
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // 8. Lifecycle Progression Action Buttons
            if (isCancelled) ...[
              Container(
                padding: const EdgeInsets.all(12),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.cancel_rounded, color: _kCrimson, size: 20),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'تم إلغاء هذا الطلب بواسطة المشتري أو الإدارة.',
                        style: TextStyle(color: _kCrimson, fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                    ),
                    TextButton(
                      onPressed: () async {
                        try {
                          await ref.read(employeeOrdersProvider.notifier).updateOrderStatus(order.id, 'pending');
                          if (mounted) AppNotification.showSuccess(context, 'تمت إعادة تفعيل الطلب بنجاح.');
                        } catch (e) {
                          if (mounted) AppNotification.showError(context, e.toString());
                        }
                      },
                      child: const Text('إعادة تفعيل'),
                    ),
                  ],
                ),
              ),
            ] else ...[
              // Primary Action based on current status:
              if (isPending) ...[
                Row(
                  children: [
                    if (isPendingTab) ...[
                      Expanded(
                        child: TextButton(
                          style: TextButton.styleFrom(foregroundColor: _kCrimson),
                          onPressed: () => _showDeclineDialog(context, order),
                          child: const Text('رفض / تخطي'),
                        ),
                      ),
                      const SizedBox(width: 8),
                    ],
                    Expanded(
                      flex: 2,
                      child: (!hasDeliveryFeesSet)
                          ? FilledButton.icon(
                              style: FilledButton.styleFrom(
                                backgroundColor: _kRoyalBlue,
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              onPressed: () => _showSetFeesDialog(context, order),
                              icon: const Icon(Icons.local_shipping_rounded, size: 18),
                              label: const Text('تحديد رسوم التوصيل', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5)),
                            )
                          : (awaitingFeesApproval)
                              ? Row(
                                  children: [
                                    Expanded(
                                      child: FilledButton.icon(
                                        style: FilledButton.styleFrom(
                                          backgroundColor: Colors.grey.shade400,
                                          padding: const EdgeInsets.symmetric(vertical: 12),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                        onPressed: null,
                                        icon: const Icon(Icons.hourglass_top_rounded, size: 18),
                                        label: const Text('بانتظار موافقة المشتري', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    OutlinedButton(
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      ),
                                      onPressed: () => _showSetFeesDialog(context, order),
                                      child: const Text('تعديل'),
                                    ),
                                  ],
                                )
                              : FilledButton.icon(
                                  style: FilledButton.styleFrom(
                                    backgroundColor: _kEmerald,
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                  onPressed: () async {
                                    final success = await ref.read(employeeOrdersProvider.notifier).acceptOrder(order.id);
                                    if (!mounted) return;
                                    if (success) {
                                      AppNotification.showSuccess(context, 'مبروك! تم قبول وتجهيز الطلب بنجاح.');
                                      if (isPendingTab) _tabController.animateTo(1);
                                    } else {
                                      AppNotification.showInfo(context, 'عذراً، قد يكون موظف آخر استلم هذا الطلب.');
                                    }
                                  },
                                  icon: const Icon(Icons.check_circle_rounded, size: 18),
                                  label: const Text('قبول وتجهيز الطلب (قيد التجهيز)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5)),
                                ),
                    ),
                  ],
                ),
              ] else if (isProcessing) ...[
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF4338CA), // Indigo
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: () async {
                      try {
                        await ref.read(employeeOrdersProvider.notifier).markShipped(order.id);
                        if (!mounted) return;
                        AppNotification.showSuccess(context, 'تم بدء الشحن والتوصيل للعميل.');
                      } catch (e) {
                        if (mounted) AppNotification.showError(context, e.toString());
                      }
                    },
                    icon: const Icon(Icons.local_shipping_rounded, size: 18),
                    label: const Text('بدء التوصيل / تم الشحن', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  ),
                ),
              ] else if (isShipped) ...[
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: _kEmerald,
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: () async {
                      try {
                        await ref.read(employeeOrdersProvider.notifier).markDelivered(order.id);
                        if (!mounted) return;
                        AppNotification.showSuccess(context, 'تم تأكيد التسليم النهائي بنجاح!');
                      } catch (e) {
                        if (mounted) AppNotification.showError(context, e.toString());
                      }
                    },
                    icon: const Icon(Icons.done_all_rounded, size: 18),
                    label: const Text('تأكيد التسليم النهائي للعميل (تم التسليم)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  ),
                ),
              ] else if (isDelivered) ...[
                Container(
                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.check_circle_rounded, color: _kEmerald, size: 20),
                      SizedBox(width: 8),
                      Text('تم إنجاز وتسليم هذا الطلب بالكامل بنجاح', style: TextStyle(color: Color(0xFF065F46), fontWeight: FontWeight.bold, fontSize: 13.5)),
                    ],
                  ),
                ),
              ],

              // Quick Action Status Bar: always accessible when fees are approved
              if (buyerApprovedFees) ...[
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Padding(
                        padding: EdgeInsets.only(bottom: 6, right: 2),
                        child: Row(
                          children: [
                            Icon(Icons.tune_rounded, size: 14, color: Color(0xFF64748B)),
                            SizedBox(width: 4),
                            Text(
                              'التحكم المباشر بحالة الطلب:',
                              style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                            ),
                          ],
                        ),
                      ),
                      Row(
                        children: [
                          // 1. قيد التجهيز
                          Expanded(
                            child: _buildStatusChip(
                              title: 'قيد التجهيز',
                              isActive: isProcessing,
                              activeColor: _kAmber,
                              icon: Icons.inventory_2_outlined,
                              onTap: isProcessing
                                  ? null
                                  : () async {
                                      try {
                                        await ref.read(employeeOrdersProvider.notifier).markProcessing(order.id);
                                        if (mounted) AppNotification.showSuccess(context, 'تم تغيير الحالة إلى قيد التجهيز.');
                                      } catch (e) {
                                        if (mounted) AppNotification.showError(context, e.toString());
                                      }
                                    },
                            ),
                          ),
                          const SizedBox(width: 6),
                          // 2. تم الشحن
                          Expanded(
                            child: _buildStatusChip(
                              title: 'تم الشحن',
                              isActive: isShipped,
                              activeColor: const Color(0xFF4338CA),
                              icon: Icons.local_shipping_outlined,
                              onTap: isShipped
                                  ? null
                                  : () async {
                                      try {
                                        await ref.read(employeeOrdersProvider.notifier).markShipped(order.id);
                                        if (mounted) AppNotification.showSuccess(context, 'تم تغيير الحالة إلى تم الشحن.');
                                      } catch (e) {
                                        if (mounted) AppNotification.showError(context, e.toString());
                                      }
                                    },
                            ),
                          ),
                          const SizedBox(width: 6),
                          // 3. تم التسليم
                          Expanded(
                            child: _buildStatusChip(
                              title: 'تم التسليم',
                              isActive: isDelivered,
                              activeColor: _kEmerald,
                              icon: Icons.check_circle_outline_rounded,
                              onTap: isDelivered
                                  ? null
                                  : () async {
                                      try {
                                        await ref.read(employeeOrdersProvider.notifier).markDelivered(order.id);
                                        if (mounted) AppNotification.showSuccess(context, 'تم تأكيد تسليم الطلب بنجاح.');
                                      } catch (e) {
                                        if (mounted) AppNotification.showError(context, e.toString());
                                      }
                                    },
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ],
        ),
      ),
    );
  }

  void _showSetFeesDialog(BuildContext context, OrderModel order) {
    final transportCtrl = TextEditingController(text: order.transportFee != null && order.transportFee! > 0 ? order.transportFee!.toInt().toString() : '');
    final offloadingCtrl = TextEditingController(text: order.offloadingFee != null && order.offloadingFee! > 0 ? order.offloadingFee!.toInt().toString() : '');
    bool isSaving = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: const Row(
            children: [
              Icon(Icons.local_shipping_rounded, color: _kRoyalBlue),
              SizedBox(width: 8),
              Text('تحديد رسوم التوصيل', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('بناءً على موقع وعنوان المشتري، حدد رسوم الترحيل والتنزيل:', style: TextStyle(fontSize: 13, color: Colors.black87)),
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.location_on_rounded, color: _kRoyalBlue, size: 16),
                    const SizedBox(width: 6),
                    Expanded(child: Text(order.address, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: _kDarkNavy))),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: transportCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'رسوم الترحيل',
                  suffixText: 'ج.س',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: offloadingCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'رسوم التنزيل',
                  suffixText: 'ج.س',
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: isSaving ? null : () => Navigator.pop(ctx),
              child: const Text('إلغاء'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
              onPressed: isSaving
                  ? null
                  : () async {
                      final tFee = double.tryParse(transportCtrl.text.trim()) ?? 0.0;
                      final oFee = double.tryParse(offloadingCtrl.text.trim()) ?? 0.0;

                      setDialogState(() => isSaving = true);
                      try {
                        await ref.read(employeeOrdersProvider.notifier).setDeliveryFees(order.id, tFee, oFee);
                        if (ctx.mounted) Navigator.pop(ctx);
                        if (context.mounted) {
                          AppNotification.showSuccess(context, 'تم إرسال رسوم التوصيل للمشتري بنجاح.');
                        }
                      } catch (e) {
                        setDialogState(() => isSaving = false);
                        if (context.mounted) AppNotification.showError(context, e.toString());
                      }
                    },
              child: isSaving
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('إرسال للمشتري للموافقة'),
            ),
          ],
        ),
      ),
    );
  }

  void _showEmployeeReceiptDialog(BuildContext context, OrderModel order, List<Map<String, dynamic>> parsedReceipts, double remaining) {
    double totalReceiptsAmount = 0;
    for (var r in parsedReceipts) {
      totalReceiptsAmount += (r['amount'] as num?)?.toDouble() ?? 0.0;
    }
    if (totalReceiptsAmount == 0) totalReceiptsAmount = remaining;

    showDialog(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: const Row(
            children: [
              Icon(Icons.verified_user_rounded, color: Color(0xFF0F766E)),
              SizedBox(width: 8),
              Text('إشعارات الدفع المرفوعة', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
            ],
          ),
          content: SizedBox(
            width: 460,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (parsedReceipts.isEmpty)
                    const Text('لا توجد إشعارات مرفوعة.'),
                  for (int i = 0; i < parsedReceipts.length; i++) ...[
                    if (i > 0) const Divider(height: 24),
                    Text(
                      'الإشعار رقم ${i + 1}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F766E)),
                    ),
                    const SizedBox(height: 8),
                    if (parsedReceipts[i]['url'] != null && parsedReceipts[i]['url'].toString().isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          // Full-screen image preview
                          showDialog(
                            context: context,
                            builder: (_) => Dialog(
                              child: InteractiveViewer(
                                child: Image.network(
                                  parsedReceipts[i]['url'],
                                  fit: BoxFit.contain,
                                  errorBuilder: (_, _, _) => const Icon(Icons.broken_image, size: 80),
                                ),
                              ),
                            ),
                          );
                        },
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Stack(
                            alignment: Alignment.bottomRight,
                            children: [
                              Image.network(
                                parsedReceipts[i]['url'],
                                height: 180,
                                width: double.infinity,
                                fit: BoxFit.cover,
                                errorBuilder: (_, _, _) => const Icon(Icons.broken_image, size: 80),
                              ),
                              Container(
                                margin: const EdgeInsets.all(8),
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.black.withValues(alpha: 0.6),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.zoom_in_rounded, color: Colors.white, size: 14),
                                    SizedBox(width: 4),
                                    Text('انقر للتكبير', style: TextStyle(color: Colors.white, fontSize: 11)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8)),
                      child: Column(
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('البنك / الطريقة:'),
                              Text('${parsedReceipts[i]['bank']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('رقم المرجع (Ref):'),
                              Text('${parsedReceipts[i]['ref']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('المبلغ المحول:'),
                              Text('${_fmtSdg((parsedReceipts[i]['amount'] as num?)?.toDouble() ?? 0)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, color: _kEmerald)),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                  const SizedBox(height: 16),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('إجمالي الدفعات بالإشعار:', style: TextStyle(fontWeight: FontWeight.bold)),
                      Text('${_fmtSdg(totalReceiptsAmount)} ج.س', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0F766E))),
                    ],
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              style: TextButton.styleFrom(foregroundColor: _kCrimson),
              onPressed: () {
                Navigator.pop(ctx);
                _showRejectReceiptDialog(context, order);
              },
              child: const Text('رفض الإشعار وإخطار المشتري', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: _kEmerald),
              onPressed: () async {
                try {
                  await ref.read(employeeOrdersProvider.notifier).approveReceiptPayments(
                    orderId: order.id,
                    receipts: parsedReceipts,
                    totalReceiptsAmount: totalReceiptsAmount,
                  );
                  if (ctx.mounted) Navigator.pop(ctx);
                  if (context.mounted) {
                    AppNotification.showSuccess(context, 'تم اعتماد إشعارات الدفع بنجاح!');
                  }
                } catch (e) {
                  if (context.mounted) AppNotification.showError(context, e.toString());
                }
              },
              icon: const Icon(Icons.check_circle_rounded, size: 18),
              label: const Text('اعتماد الدفعات فوراً', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showRejectReceiptDialog(BuildContext context, OrderModel order) {
    final reasonCtrl = TextEditingController();
    String selectedReason = 'صورة الإشعار غير واضحة';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('رفض إشعار التحويل', style: TextStyle(fontWeight: FontWeight.bold, color: _kCrimson)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('يرجى تحديد أو كتابة سبب الرفض ليتم إشعار المشتري فوراً:'),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: selectedReason,
                items: const [
                  DropdownMenuItem(value: 'صورة الإشعار غير واضحة', child: Text('صورة الإشعار غير واضحة')),
                  DropdownMenuItem(value: 'المبلغ المحول غير مطابق', child: Text('المبلغ المحول غير مطابق')),
                  DropdownMenuItem(value: 'رقم المرجع غير صالح أو مكرر', child: Text('رقم المرجع غير صالح أو مكرر')),
                  DropdownMenuItem(value: 'لم يتم استلام التحويل في الحساب', child: Text('لم يتم استلام التحويل في الحساب')),
                  DropdownMenuItem(value: 'أخرى', child: Text('سبب آخر...')),
                ],
                onChanged: (v) => setDialogState(() => selectedReason = v ?? 'صورة الإشعار غير واضحة'),
              ),
              if (selectedReason == 'أخرى') ...[
                const SizedBox(height: 10),
                TextField(
                  controller: reasonCtrl,
                  decoration: const InputDecoration(labelText: 'اكتب سبب الرفض', border: OutlineInputBorder()),
                ),
              ],
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: _kCrimson),
              onPressed: () async {
                final reason = selectedReason == 'أخرى' ? reasonCtrl.text.trim() : selectedReason;
                if (reason.isEmpty) return;
                try {
                  await ref.read(employeeOrdersProvider.notifier).rejectReceipt(orderId: order.id, reason: reason);
                  if (ctx.mounted) Navigator.pop(ctx);
                  if (context.mounted) {
                    AppNotification.showSuccess(context, 'تم رفض الإشعار وإشعار المشتري بالسبب.');
                  }
                } catch (e) {
                  if (context.mounted) AppNotification.showError(context, e.toString());
                }
              },
              child: const Text('تأكيد الرفض'),
            ),
          ],
        ),
      ),
    );
  }

  void _showPaymentDialog(BuildContext context, OrderModel order, double remaining) {
    final amountCtrl = TextEditingController(text: remaining.toInt().toString());
    String method = 'cash';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('تأكيد استلام دفعة نقدية/يدوية', style: TextStyle(fontWeight: FontWeight.bold)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('المتبقي للسداد: ${_fmtSdg(remaining)} ج.س', style: const TextStyle(color: _kCrimson, fontWeight: FontWeight.bold, fontSize: 15)),
              const SizedBox(height: 14),
              TextField(
                controller: amountCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'المبلغ المستلم', suffixText: 'ج.س', border: OutlineInputBorder()),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: method,
                items: const [
                  DropdownMenuItem(value: 'cash', child: Text('نقداً (عند التسليم)')),
                  DropdownMenuItem(value: 'bankak', child: Text('بنكك')),
                  DropdownMenuItem(value: 'ocash', child: Text('أوكاش')),
                  DropdownMenuItem(value: 'bank_transfer', child: Text('تحويل بنكي')),
                ],
                onChanged: (v) => setDialogState(() => method = v ?? 'cash'),
                decoration: const InputDecoration(labelText: 'طريقة الاستلام', border: OutlineInputBorder()),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: _kEmerald),
              onPressed: () async {
                final amt = double.tryParse(amountCtrl.text.trim()) ?? 0.0;
                if (amt <= 0) return;
                try {
                  await ref.read(employeeOrdersProvider.notifier).addPayment(order.id, amt, method);
                  if (ctx.mounted) Navigator.pop(ctx);
                  if (context.mounted) {
                    AppNotification.showSuccess(context, 'تم تسجيل الدفعة بنجاح.');
                  }
                } catch (e) {
                  if (context.mounted) AppNotification.showError(context, e.toString());
                }
              },
              child: const Text('تسجيل الدفعة'),
            ),
          ],
        ),
      ),
    );
  }

  void _showDeclineDialog(BuildContext context, OrderModel order) {
    final reasonCtrl = TextEditingController(text: 'تعذر التوصيل حالياً');
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('رفض الطلب', style: TextStyle(fontWeight: FontWeight.bold, color: _kCrimson)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('هل أنت متأكد من رفض هذا الطلب؟'),
            const SizedBox(height: 12),
            TextField(
              controller: reasonCtrl,
              decoration: const InputDecoration(labelText: 'سبب الرفض', border: OutlineInputBorder()),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('تراجع')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _kCrimson),
            onPressed: () async {
              try {
                await ref.read(employeeOrdersProvider.notifier).cancelOrder(order.id, reasonCtrl.text.trim());
                if (ctx.mounted) Navigator.pop(ctx);
                if (context.mounted) {
                  AppNotification.showSuccess(context, 'تم رفض الطلب وإلغاؤه.');
                }
              } catch (e) {
                if (context.mounted) AppNotification.showError(context, e.toString());
              }
            },
            child: const Text('تأكيد الرفض'),
          ),
        ],
      ),
    );
  }

  Future<void> _handleChat(BuildContext context, OrderModel order) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;

    try {
      final roomId = await ChatService.getOrCreateRoom(
        buyerId: order.customerId,
        merchantId: user.id,
      );
      if (context.mounted) {
        await context.push('/buyer/chat/room/$roomId');
      }
    } catch (e) {
      if (context.mounted) {
        AppNotification.showError(context, 'تعذر فتح المحادثة: $e');
      }
    }
  }
}
