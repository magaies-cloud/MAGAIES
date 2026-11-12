import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import '../../domain/models/subscriber_model.dart';
import '../../application/subscribers_provider.dart';

class SubscriberDetailSheet extends ConsumerWidget {
  final SubscriberModel subscriber;
  final bool isEmbedded;
  final VoidCallback? onOpenCategoryPermissions;
  final VoidCallback? onOpenChangeRole;
  final VoidCallback? onClose;

  const SubscriberDetailSheet({
    super.key,
    required this.subscriber,
    this.isEmbedded = false,
    this.onOpenCategoryPermissions,
    this.onOpenChangeRole,
    this.onClose,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = context.isRtl;
    final currencyFormatter = NumberFormat.currency(symbol: 'ج.س ', decimalDigits: 2);
    final dateFormatter = DateFormat('yyyy-MM-dd');

    // Watch provider for reactive updates
    final currentSubscriber = ref.watch(subscribersProvider).valueOrNull
        ?.cast<SubscriberModel?>()
        .firstWhere((s) => s?.id == subscriber.id, orElse: () => null) ?? subscriber;
    final sub = currentSubscriber;
    final roleColor = _getRoleColor(sub.role);
    final statusColor = _getStatusColor(sub.status);

    return Container(
      padding: isEmbedded ? const EdgeInsets.all(32.0) : const EdgeInsets.all(28.0),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: isEmbedded ? BorderRadius.zero : BorderRadius.circular(24),
        border: isEmbedded ? null : Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: isEmbedded ? null : [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 30,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Bar — only show when NOT embedded (embedded view has its own header)
          if (!isEmbedded) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.badge_rounded, color: Color(0xFF1E40AF), size: 20),
                    ),
                    const SizedBox(width: 10),
                    Text(
                      isRtl ? 'الملف التعريفي للمشترك' : 'Subscriber Profile Details',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B), size: 22),
                  tooltip: isRtl ? 'إغلاق' : 'Close',
                  onPressed: () => Navigator.pop(context),
                  splashRadius: 20,
                ),
              ],
            ),
            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 20),
          ],

          // Responsive Body: Unified single scroll view for the entire content
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.only(bottom: 24),
              child: LayoutBuilder(
                builder: (context, constraints) {
                  final bool isDesktop = constraints.maxWidth > 960;

                  final leftColumn = _buildLeftColumn(
                    context: context,
                    ref: ref,
                    sub: sub,
                    isRtl: isRtl,
                    roleColor: roleColor,
                    statusColor: statusColor,
                    dateFormatter: dateFormatter,
                  );

                  final rightColumn = _buildRightColumn(
                    context: context,
                    ref: ref,
                    sub: sub,
                    isRtl: isRtl,
                    currencyFormatter: currencyFormatter,
                  );

                  if (isDesktop) {
                    return Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        SizedBox(
                          width: 360,
                          child: leftColumn,
                        ),
                        const SizedBox(width: 24),
                        Expanded(
                          child: rightColumn,
                        ),
                      ],
                    );
                  } else {
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        leftColumn,
                        const SizedBox(height: 24),
                        rightColumn,
                      ],
                    );
                  }
                },
              ),
            ),
          ),
          
          // Bottom Action Buttons (Only if not embedded)
          if (!isEmbedded) ...[
            const SizedBox(height: 20),
            const Divider(height: 1),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      ref.read(subscribersProvider.notifier).toggleSuspension(sub.id);
                      Navigator.pop(context);
                    },
                    icon: Icon(
                      sub.status == SubscriberStatus.suspended ? Icons.play_arrow_rounded : Icons.pause_rounded,
                      size: 18,
                    ),
                    label: Text(
                      sub.status == SubscriberStatus.suspended
                          ? (isRtl ? 'تنشيط الحساب' : 'Activate User')
                          : (isRtl ? 'إيقاف الحساب' : 'Suspend User'),
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: sub.status == SubscriberStatus.suspended
                          ? const Color(0xFF059669)
                          : const Color(0xFFDC2626),
                      side: BorderSide(
                        color: sub.status == SubscriberStatus.suspended
                            ? const Color(0xFF059669)
                            : const Color(0xFFDC2626),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      ref.read(subscribersProvider.notifier).removeSubscriber(sub.id);
                      Navigator.pop(context);
                    },
                    icon: const Icon(Icons.delete_outline_rounded, color: Color(0xFFDC2626), size: 18),
                    label: Text(
                      isRtl ? 'حذف الحساب' : 'Delete User',
                      style: const TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.bold),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFDC2626)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          ]
        ],
      ),
    );
  }

  Widget _buildLeftColumn({
    required BuildContext context,
    required WidgetRef ref,
    required SubscriberModel sub,
    required bool isRtl,
    required Color roleColor,
    required Color statusColor,
    required DateFormat dateFormatter,
  }) {
    final displayName = isRtl ? (sub.nameAr.isNotEmpty ? sub.nameAr : sub.name) : sub.name;
    final otherName = isRtl ? sub.name : (sub.nameAr.isNotEmpty ? sub.nameAr : '');

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Avatar Card & Header
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [roleColor, roleColor.withValues(alpha: 0.7)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: roleColor.withValues(alpha: 0.25),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        displayName.trim().isNotEmpty ? displayName.trim()[0].toUpperCase() : 'U',
                        style: const TextStyle(
                          fontSize: 32,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 20),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                displayName,
                                style: const TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                            ),
                          ],
                        ),
                        if (otherName.isNotEmpty && otherName != displayName) ...[
                          const SizedBox(height: 2),
                          Text(
                            otherName,
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                        const SizedBox(height: 6),
                        Wrap(
                          spacing: 12,
                          runSpacing: 4,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.email_outlined, size: 14, color: Color(0xFF64748B)),
                                const SizedBox(width: 4),
                                Text(
                                  sub.email.isNotEmpty ? sub.email : '-',
                                  style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                                ),
                              ],
                            ),
                            if (sub.phone.isNotEmpty)
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.phone_outlined, size: 14, color: Color(0xFF64748B)),
                                  const SizedBox(width: 4),
                                  Text(
                                    sub.phone,
                                    textDirection: TextDirection.ltr,
                                    style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                                  ),
                                ],
                              ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            if (sub.isVerified)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEFF6FF),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: const Color(0xFFBFDBFE)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.verified_rounded, color: Color(0xFF2563EB), size: 14),
                                    const SizedBox(width: 4),
                                    Text(
                                      isRtl ? 'موثق' : 'Verified',
                                      style: const TextStyle(fontSize: 11, color: Color(0xFF1E40AF), fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                            _buildChip(_getRoleName(sub.role, isRtl), roleColor),
                            _buildChip(_getStatusName(sub.status, isRtl), statusColor),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              if (onOpenChangeRole != null || (sub.role == UserRole.merchant && onOpenCategoryPermissions != null)) ...[
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    if (onOpenChangeRole != null)
                      OutlinedButton.icon(
                        onPressed: () {
                          if (!isEmbedded) Navigator.pop(context);
                          onOpenChangeRole!();
                        },
                        icon: const Icon(Icons.manage_accounts_rounded, size: 16),
                        label: Text(isRtl ? 'تغيير الدور / الصلاحية' : 'Change Role'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF1E40AF),
                          side: const BorderSide(color: Color(0xFFBFDBFE)),
                          backgroundColor: const Color(0xFFEFF6FF),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    if (sub.role == UserRole.merchant && onOpenCategoryPermissions != null)
                      OutlinedButton.icon(
                        onPressed: () {
                          if (!isEmbedded) Navigator.pop(context);
                          onOpenCategoryPermissions!();
                        },
                        icon: const Icon(Icons.admin_panel_settings_outlined, size: 16),
                        label: Text(isRtl ? 'صلاحيات الأقسام والعمولات' : 'Category Permissions'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF059669),
                          side: const BorderSide(color: Color(0xFFA7F3D0)),
                          backgroundColor: const Color(0xFFECFDF5),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    if (sub.role == UserRole.merchant)
                      OutlinedButton.icon(
                        onPressed: () {
                          if (!isEmbedded) Navigator.pop(context);
                          context.push(RouteNames.merchantFinancialReport);
                        },
                        icon: const Icon(Icons.account_balance_wallet_outlined, size: 16),
                        label: Text(isRtl ? 'كشف الحساب والتقرير المالي' : 'Financial Statement'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF7C3AED),
                          side: const BorderSide(color: Color(0xFFDDD6FE)),
                          backgroundColor: const Color(0xFFF5F3FF),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 20),

        // General Info Section
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildSectionTitle(isRtl ? 'البيانات العامة والاتصال' : 'General & Contact Info', Icons.contact_mail_outlined),
              const SizedBox(height: 16),
              _buildInfoRow(Icons.tag_rounded, isRtl ? 'رقم المشترك ID' : 'Subscriber ID', sub.id),
              const Divider(height: 24),
              _buildInfoRow(Icons.calendar_today_outlined, isRtl ? 'تاريخ التسجيل' : 'Join Date', dateFormatter.format(sub.joinedAt)),
              if (sub.storeName != null || sub.storeNameAr != null) ...[
                const Divider(height: 24),
                _buildInfoRow(
                  Icons.store_rounded,
                  isRtl ? 'اسم المتجر' : 'Store Name',
                  (isRtl ? sub.storeNameAr : sub.storeName) ?? (sub.storeName ?? '-'),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Account Governance Card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildSectionTitle(isRtl ? 'إدارة وحالة الحساب' : 'Account Governance', Icons.admin_panel_settings_rounded),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => ref.read(subscribersProvider.notifier).toggleSuspension(sub.id),
                  icon: Icon(
                    sub.status == SubscriberStatus.suspended ? Icons.play_arrow_rounded : Icons.pause_rounded,
                    size: 18,
                  ),
                  label: Text(
                    sub.status == SubscriberStatus.suspended
                        ? (isRtl ? 'تنشيط الحساب' : 'Activate User')
                        : (isRtl ? 'إيقاف الحساب' : 'Suspend User'),
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: sub.status == SubscriberStatus.suspended
                        ? const Color(0xFF059669)
                        : const Color(0xFFDC2626),
                    side: BorderSide(
                      color: sub.status == SubscriberStatus.suspended
                          ? const Color(0xFF059669)
                          : const Color(0xFFDC2626),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () async {
                    final confirm = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        title: Text(isRtl ? 'تأكيد حذف الحساب' : 'Confirm Delete'),
                        content: Text(
                          isRtl
                              ? 'هل أنت متأكد من حذف حساب المشترك "${sub.name}"؟ لا يمكن التراجع عن هذا الإجراء.'
                              : 'Are you sure you want to delete "${sub.name}"? This action cannot be undone.',
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                          ),
                          FilledButton(
                            style: FilledButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
                            onPressed: () => Navigator.pop(ctx, true),
                            child: Text(isRtl ? 'حذف الحساب نهائياً' : 'Delete Permanently'),
                          ),
                        ],
                      ),
                    );
                    if (confirm == true) {
                      await ref.read(subscribersProvider.notifier).removeSubscriber(sub.id);
                      if (onClose != null) {
                        onClose!();
                      } else if (context.mounted && !isEmbedded) {
                        Navigator.pop(context);
                      }
                    }
                  },
                  icon: const Icon(Icons.delete_outline_rounded, color: Color(0xFFDC2626), size: 18),
                  label: Text(
                    isRtl ? 'حذف الحساب نهائياً' : 'Delete Account',
                    style: const TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.bold),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFFDC2626)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildRightColumn({
    required BuildContext context,
    required WidgetRef ref,
    required SubscriberModel sub,
    required bool isRtl,
    required NumberFormat currencyFormatter,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Top KPI Stats Summary Bar (4 Balanced Cards)
        LayoutBuilder(
          builder: (ctx, kpiConstraints) {
            final int crossAxisCount = kpiConstraints.maxWidth > 650 ? 4 : 2;
            final double childAspectRatio = kpiConstraints.maxWidth > 650 ? 1.75 : 2.2;

            return GridView.count(
              crossAxisCount: crossAxisCount,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 14,
              crossAxisSpacing: 14,
              childAspectRatio: childAspectRatio,
              children: [
                _buildKpiCard(
                  title: isRtl ? 'إجمالي الطلبات' : 'Total Orders',
                  value: sub.totalOrders.toString(),
                  icon: Icons.shopping_bag_rounded,
                  color: const Color(0xFF1E40AF),
                  bgColor: const Color(0xFFEFF6FF),
                ),
                _buildKpiCard(
                  title: isRtl
                      ? (sub.role == UserRole.merchant ? 'إجمالي المبيعات' : 'إجمالي المشتريات')
                      : 'Financial Volume',
                  value: currencyFormatter.format(sub.totalSpent),
                  icon: Icons.account_balance_wallet_rounded,
                  color: const Color(0xFF059669),
                  bgColor: const Color(0xFFECFDF5),
                ),
                _buildKpiCard(
                  title: isRtl ? 'عمر الحساب' : 'Account Age',
                  value: '${DateTime.now().difference(sub.joinedAt).inDays} ${isRtl ? "يوم" : "Days"}',
                  icon: Icons.calendar_month_rounded,
                  color: const Color(0xFFD97706),
                  bgColor: const Color(0xFFFFFBEB),
                ),
                _buildKpiCard(
                  title: isRtl
                      ? (sub.role == UserRole.merchant ? 'نقاط التاجر' : 'نقاط الولاء')
                      : 'Points',
                  value: sub.merchantPoints.toString(),
                  icon: Icons.stars_rounded,
                  color: const Color(0xFF7E22CE),
                  bgColor: const Color(0xFFFAF5FF),
                ),
              ],
            );
          },
        ),
        const SizedBox(height: 20),

        // B2B Corporate Credit Facilities Card
        _buildB2bCreditCard(context, ref, sub, isRtl),

        // Advertiser & Commercial Campaigns Card
        _buildAdvertiserCampaignsCard(context, ref, sub, isRtl, currencyFormatter),

        // Merchant Details
        if (sub.role == UserRole.merchant) ...[
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildSectionTitle(isRtl ? 'بيانات المتجر والتجارة' : 'Store & Commerce Info', Icons.storefront_rounded),
                
                // Gamification Info
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFFDE68A)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.stars_rounded, color: Color(0xFFD97706), size: 20),
                          const SizedBox(width: 8),
                          Text(
                            isRtl ? 'نقاط ورتبة التاجر (Gamification)' : 'Merchant Points & Tier',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF92400E)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      _buildInfoRow(Icons.military_tech_outlined, isRtl ? 'المستوى الحالي' : 'Current Tier', sub.merchantTier.toUpperCase()),
                      const Divider(height: 24, color: Color(0xFFFDE68A)),
                      _buildInfoRow(Icons.control_point_duplicate, isRtl ? 'النقاط المكتسبة' : 'Earned Points', '${sub.merchantPoints} ${isRtl ? "نقطة" : "pts"}'),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              icon: const Icon(Icons.remove_circle_outline, color: Color(0xFFDC2626), size: 16),
                              label: Text(isRtl ? 'خصم 10 نقاط' : 'Penalty -10', style: const TextStyle(color: Color(0xFFDC2626), fontSize: 12)),
                              onPressed: () => ref.read(subscribersProvider.notifier).adjustMerchantPoints(sub.id, -10),
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: Color(0xFFFCA5A5)),
                                backgroundColor: const Color(0xFFFEF2F2),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: OutlinedButton.icon(
                              icon: const Icon(Icons.add_circle_outline, color: Color(0xFF059669), size: 16),
                              label: Text(isRtl ? 'منح 50 نقطة' : 'Bonus +50', style: const TextStyle(color: Color(0xFF059669), fontSize: 12)),
                              onPressed: () => ref.read(subscribersProvider.notifier).adjustMerchantPoints(sub.id, 50),
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: Color(0xFFA7F3D0)),
                                backgroundColor: const Color(0xFFECFDF5),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                // Direct Payment Gateway Card
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: sub.directPaymentEnabled ? const Color(0xFFF0FDF4) : const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: sub.directPaymentEnabled ? const Color(0xFF86EFAC) : const Color(0xFFBFDBFE),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            sub.directPaymentEnabled ? Icons.check_circle_rounded : Icons.lock_outline_rounded,
                            color: sub.directPaymentEnabled ? Colors.green.shade700 : const Color(0xFF2563EB),
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              isRtl ? 'إعدادات دفع المشتري ومحفظة التاجر' : 'Merchant Payment & Wallet Settings',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                color: sub.directPaymentEnabled ? Colors.green.shade900 : const Color(0xFF1E40AF),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      SwitchListTile(
                        title: Text(
                          isRtl ? 'تمكين استلام الدفع المباشر للتاجر' : 'Enable Direct Payment to Merchant',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                        subtitle: Text(
                          sub.directPaymentEnabled
                              ? (isRtl ? 'مفعّل: تظهر حسابات التاجر للمشتري ويتم التحويل للتاجر مباشرة.' : 'Enabled: Merchant accounts are shown at checkout.')
                              : (isRtl ? 'مقفل: يتم استلام المدفوعات في حساب شركة السيطرة وتودع في محفظة التاجر.' : 'Locked: Payments are received in Magaiees company account.'),
                          style: TextStyle(
                            fontSize: 11,
                            color: sub.directPaymentEnabled ? Colors.green.shade800 : Colors.blue.shade900,
                          ),
                        ),
                        value: sub.directPaymentEnabled,
                        activeTrackColor: Colors.green,
                        onChanged: (val) {
                          ref.read(subscribersProvider.notifier).toggleDirectPayment(sub.id, val);
                        },
                        contentPadding: EdgeInsets.zero,
                      ),
                      if (sub.directPaymentEnabled) ...[
                        const Divider(height: 24),
                        Text(
                          isRtl ? 'طرق وبيانات الدفع لاستقبال مبيعات المتجر:' : 'Store Payment & Payout Accounts:',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                        const SizedBox(height: 12),
                        _buildInfoRow(Icons.account_balance_outlined, isRtl ? 'اسم البنك' : 'Bank', sub.bankName?.isNotEmpty == true ? sub.bankName! : '-'),
                        const SizedBox(height: 8),
                        _buildInfoRow(Icons.person_outline, isRtl ? 'اسم صاحب الحساب' : 'Account Name', sub.bankAccountName?.isNotEmpty == true ? sub.bankAccountName! : '-'),
                        const SizedBox(height: 8),
                        _buildInfoRow(Icons.numbers_outlined, isRtl ? 'رقم الحساب' : 'Bank Account No.', sub.bankAccountNumber?.isNotEmpty == true ? sub.bankAccountNumber! : '-'),
                        const SizedBox(height: 8),
                        _buildInfoRow(Icons.account_balance_wallet_outlined, isRtl ? 'حساب بنكك' : 'Bankak', (sub.permissions['bankak_account']?.toString().isNotEmpty == true) ? '${sub.permissions['bankak_account']} (${sub.permissions['bankak_name'] ?? ""})' : '-'),
                        const SizedBox(height: 8),
                        _buildInfoRow(Icons.phone_android_outlined, isRtl ? 'محفظة أوكاش' : 'O-Cash Phone', (sub.permissions['ocash_phone']?.toString().isNotEmpty == true) ? '${sub.permissions['ocash_phone']} (${sub.permissions['ocash_name'] ?? ""})' : '-'),
                        const SizedBox(height: 16),
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: () => _showEditMerchantWalletDialog(context, ref, sub, isRtl),
                            icon: const Icon(Icons.edit_note_rounded, size: 18),
                            label: Text(isRtl ? 'ضبط وتعديل بيانات محفظة التاجر وحساباته' : 'Edit Merchant Wallet & Accounts'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF1E40AF),
                              side: const BorderSide(color: Color(0xFF3B82F6)),
                              backgroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],

        // Employee Detailed Permissions
        if (sub.role == UserRole.employee) ...[
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildSectionTitle(isRtl ? 'صلاحيات الموظف التفصيلية' : 'Employee Permissions', Icons.security_rounded),
                const SizedBox(height: 16),
                _EmployeePermissionsEditor(subscriber: subscriber, isRtl: isRtl),
              ],
            ),
          ),
        ],

        // Pending Merchant Approval Action
        if (sub.status == SubscriberStatus.pendingVerification && sub.documentType != null) ...[
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow(Icons.description_outlined, isRtl ? 'نوع المستند المرفق' : 'Document Type', sub.documentType!),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: () {
                      ref.read(subscribersProvider.notifier).approveMerchant(sub.id);
                      if (!isEmbedded) Navigator.pop(context);
                    },
                    icon: const Icon(Icons.check_circle_outline_rounded, size: 18),
                    label: Text(isRtl ? 'موافقة وترقية إلى تاجر معتمد' : 'Approve & Upgrade to Merchant'),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildAdvertiserCampaignsCard(
    BuildContext context,
    WidgetRef ref,
    SubscriberModel sub,
    bool isRtl,
    NumberFormat currencyFormatter,
  ) {
    final hasAds = sub.isAdvertiser || sub.totalAdsCount > 0;

    return Container(
      margin: const EdgeInsets.only(top: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: hasAds ? const Color(0xFFF0ABFC) : const Color(0xFFE2E8F0),
          width: hasAds ? 1.5 : 1.0,
        ),
        boxShadow: [
          BoxShadow(
            color: (hasAds ? const Color(0xFFC026D3) : const Color(0xFF0F172A)).withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
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
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFDF4FF),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFF0ABFC)),
                    ),
                    child: const Icon(Icons.campaign_rounded, color: Color(0xFFC026D3), size: 20),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    isRtl ? 'الحملات الإعلانية ومصروفات التسويق' : 'Advertising & Campaign Spend',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14.5,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
              if (hasAds)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFDF4FF),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFE879F9)),
                  ),
                  child: Text(
                    isRtl ? 'معلن مسجّل' : 'Active Advertiser',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFFC026D3),
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),

          // Two stats: Number of Ads, Total Ad Spend
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF5FF),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE9D5FF)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'عدد الحملات المسجلة' : 'Registered Campaigns',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${sub.totalAdsCount}',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF7E22CE)),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFDF4FF),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFF0ABFC)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'إجمالي الإنفاق الإعلاني' : 'Total Ad Spend',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        currencyFormatter.format(sub.totalAdSpend),
                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFFC026D3)),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Action Button to Go to Ads Screen
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () {
                if (!isEmbedded) Navigator.pop(context);
                context.go(RouteNames.adminAds);
              },
              icon: const Icon(Icons.open_in_new_rounded, size: 16),
              label: Text(
                isRtl ? 'الانتقال إلى شاشة إدارة الإعلانات والحملات' : 'Manage Ads & Campaigns',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFFC026D3),
                side: const BorderSide(color: Color(0xFFF0ABFC)),
                backgroundColor: const Color(0xFFFDF4FF),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: color),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: color.withValues(alpha: 0.8),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w800,
              color: color,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 20, color: const Color(0xFF1E40AF)),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: Color(0xFF0F172A),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value, {Color? valueColor}) {
    return Row(
      children: [
        Icon(icon, size: 18, color: const Color(0xFF64748B)),
        const SizedBox(width: 10),
        Text(
          label,
          style: const TextStyle(
            color: Color(0xFF64748B),
            fontSize: 13,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 14,
              color: valueColor ?? const Color(0xFF0F172A),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildChip(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.25)),
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
      case UserRole.admin: return const Color(0xFF7E22CE);
      case UserRole.merchant: return const Color(0xFF059669);
      case UserRole.buyer: return const Color(0xFF1E40AF);
      case UserRole.guest: return const Color(0xFF64748B);
      case UserRole.employee: return const Color(0xFFD97706);
    }
  }

  String _getRoleName(UserRole role, bool isRtl) {
    switch (role) {
      case UserRole.admin: return isRtl ? 'مدير نظام' : 'Admin';
      case UserRole.merchant: return isRtl ? 'تاجر معتمد' : 'Merchant';
      case UserRole.buyer: return isRtl ? 'مشتري' : 'Buyer';
      case UserRole.guest: return isRtl ? 'زائر' : 'Guest';
      case UserRole.employee: return isRtl ? 'موظف منصة' : 'Employee';
    }
  }

  String _getStatusName(SubscriberStatus status, bool isRtl) {
    switch (status) {
      case SubscriberStatus.active: return isRtl ? 'نشط' : 'Active';
      case SubscriberStatus.suspended: return isRtl ? 'موقوف' : 'Suspended';
      case SubscriberStatus.pendingVerification: return isRtl ? 'قيد المراجعة' : 'Pending';
      case SubscriberStatus.rejectedVerification: return isRtl ? 'طلب مرفوض' : 'Rejected';
    }
  }

  Color _getStatusColor(SubscriberStatus status) {
    switch (status) {
      case SubscriberStatus.active: return const Color(0xFF10B981);
      case SubscriberStatus.suspended: return const Color(0xFFEF4444);
      case SubscriberStatus.pendingVerification: return const Color(0xFFF59E0B);
      case SubscriberStatus.rejectedVerification: return const Color(0xFFE11D48);
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
      {'key': 'can_manage_digital_broker', 'titleAr': 'الوسيط الرقمي وطلبات التسعير', 'titleEn': 'Digital Broker & Quote Requests'},
      {'key': 'can_chat_support', 'titleAr': 'الدعم الفني والمحادثات', 'titleEn': 'Customer Support Chat'},
    ];

    return Column(
      children: permissionsList.map((perm) {
        final key = perm['key']!;
        final val = _permissions[key] == true;
        return SwitchListTile(
          title: Text(widget.isRtl ? perm['titleAr']! : perm['titleEn']!, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          value: val,
          onChanged: (newValue) => _updatePermission(key, newValue),
          activeTrackColor: const Color(0xFF1E40AF),
          contentPadding: EdgeInsets.zero,
        );
      }).toList(),
    );
  }
}

void _showEditMerchantWalletDialog(BuildContext context, WidgetRef ref, SubscriberModel sub, bool isRtl) {
  final bankNameCtrl = TextEditingController(text: sub.bankName ?? '');
  final bankAccNameCtrl = TextEditingController(text: sub.bankAccountName ?? '');
  final bankAccNumCtrl = TextEditingController(text: sub.bankAccountNumber ?? '');
  final bankakAccCtrl = TextEditingController(text: sub.permissions['bankak_account']?.toString() ?? '');
  final bankakNameCtrl = TextEditingController(text: sub.permissions['bankak_name']?.toString() ?? '');
  final ocashPhoneCtrl = TextEditingController(text: sub.permissions['ocash_phone']?.toString() ?? '');
  final ocashNameCtrl = TextEditingController(text: sub.permissions['ocash_name']?.toString() ?? '');
  bool isDirectEnabled = sub.directPaymentEnabled;

  showDialog(
    context: context,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, setDialogState) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.account_balance_wallet_rounded, color: Color(0xFF1E40AF)),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                isRtl ? 'ضبط بيانات محفظة وحسابات التاجر' : 'Edit Merchant Accounts & Wallet',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: SizedBox(
            width: 480,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isDirectEnabled ? const Color(0xFFF0FDF4) : const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isDirectEnabled ? const Color(0xFF86EFAC) : const Color(0xFFFECACA),
                    ),
                  ),
                  child: SwitchListTile(
                    title: Text(
                      isRtl ? 'تمكين استلام الدفع المباشر للتاجر' : 'Direct Payment Enabled',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                    subtitle: Text(
                      isDirectEnabled
                          ? (isRtl ? 'مفعّل: يدفع المشتري لحسابات التاجر مباشرة' : 'Enabled: Buyer pays merchant directly')
                          : (isRtl ? 'مقفل: يتم الاستلام في حساب شركة السيطرة' : 'Locked: Received in company account'),
                      style: TextStyle(
                        fontSize: 11,
                        color: isDirectEnabled ? Colors.green.shade800 : Colors.red.shade800,
                      ),
                    ),
                    value: isDirectEnabled,
                    activeTrackColor: Colors.green,
                    onChanged: (val) => setDialogState(() => isDirectEnabled = val),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                const SizedBox(height: 16),
                Text(isRtl ? 'بيانات الحساب البنكي:' : 'Bank Details:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: bankNameCtrl,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'اسم البنك' : 'Bank Name',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.account_balance),
                  ),
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: bankAccNameCtrl,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'اسم صاحب الحساب رباعي' : 'Account Name',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.person),
                  ),
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: bankAccNumCtrl,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'رقم الحساب البنكي' : 'Account Number',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.numbers),
                  ),
                ),
                const SizedBox(height: 16),
                Text(isRtl ? 'بيانات تطبيق بنكك:' : 'Bankak Details:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextFormField(
                        controller: bankakAccCtrl,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'رقم حساب بنكك' : 'Bankak Acc',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.account_balance_wallet),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextFormField(
                        controller: bankakNameCtrl,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'اسم صاحب الحساب' : 'Holder Name',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.badge),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Text(isRtl ? 'بيانات محفظة أوكاش:' : 'O-Cash Details:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextFormField(
                        controller: ocashPhoneCtrl,
                        keyboardType: TextInputType.phone,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'رقم المحفظة / الهاتف' : 'O-Cash Phone',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.phone_android),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextFormField(
                        controller: ocashNameCtrl,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'اسم صاحب المحفظة' : 'Holder Name',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.person),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1E40AF),
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              await ref.read(subscribersProvider.notifier).updateMerchantWalletData(
                userId: sub.id,
                bankName: bankNameCtrl.text.trim(),
                bankAccountName: bankAccNameCtrl.text.trim(),
                bankAccountNumber: bankAccNumCtrl.text.trim(),
                bankakAccount: bankakAccCtrl.text.trim(),
                bankakName: bankakNameCtrl.text.trim(),
                ocashPhone: ocashPhoneCtrl.text.trim(),
                ocashName: ocashNameCtrl.text.trim(),
                directPaymentEnabled: isDirectEnabled,
              );
              if (ctx.mounted) Navigator.pop(ctx);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isRtl ? 'تم تحديث بيانات محفظة وحسابات التاجر بنجاح!' : 'Merchant wallet updated successfully!'),
                    backgroundColor: Colors.green,
                  ),
                );
              }
            },
            child: Text(isRtl ? 'حفظ التعديلات' : 'Save Changes'),
          ),
        ],
      ),
    ),
  );
}

Widget _buildB2bCreditCard(BuildContext context, WidgetRef ref, SubscriberModel sub, bool isRtl) {
  final bool isB2bEnabled = sub.permissions['b2b_credit_enabled'] == true;
  final String scope = sub.permissions['b2b_allowed_scope']?.toString() ?? 'all';
  final dynamic creditLimit = sub.permissions['b2b_credit_limit'];

  String scopeText;
  if (scope == 'exclusive_only') {
    scopeText = isRtl ? 'منتجات السيطرة الحصرية فقط' : 'Magaiees Exclusive only';
  } else if (scope == 'merchants_only') {
    scopeText = isRtl ? 'تجار السوق والموردين فقط' : 'Marketplace Merchants only';
  } else {
    scopeText = isRtl ? 'المنصة بالكامل (السيطرة + تجار السوق)' : 'All Platform (Magaiees + Merchants)';
  }

  return Container(
    padding: const EdgeInsets.all(20),
    decoration: BoxDecoration(
      color: isB2bEnabled ? const Color(0xFFFAF5FF) : Colors.white,
      borderRadius: BorderRadius.circular(16),
      border: Border.all(
        color: isB2bEnabled ? const Color(0xFFD8B4FE) : const Color(0xFFE2E8F0),
        width: isB2bEnabled ? 1.5 : 1,
      ),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              isB2bEnabled ? Icons.verified_user_rounded : Icons.business_outlined,
              color: isB2bEnabled ? const Color(0xFF7E22CE) : const Color(0xFF64748B),
              size: 24,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                isRtl ? 'تسهيلات الدفع الآجل للشركات (B2B Credit)' : 'Corporate Credit Facilities (B2B)',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 15,
                  color: isB2bEnabled ? const Color(0xFF581C87) : const Color(0xFF334155),
                ),
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: isB2bEnabled ? const Color(0xFFF3E8FF) : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: isB2bEnabled ? const Color(0xFFC084FC) : const Color(0xFFCBD5E1),
                ),
              ),
              child: Text(
                isB2bEnabled ? (isRtl ? 'مفعّل للعميل' : 'Active') : (isRtl ? 'غير مصرّح' : 'Disabled'),
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isB2bEnabled ? const Color(0xFF6B21A8) : const Color(0xFF64748B),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Text(
          isB2bEnabled
              ? (isRtl
                  ? 'يظهر خيار الدفع الآجل لهذا العميل في شاشة الدفع لإتمام الطلب بالآجل.'
                  : 'B2B Credit is enabled for this customer at checkout.')
              : (isRtl
                  ? 'هذا العميل غير مصرّح له بالدفع الآجل؛ الخيار مخفي عنه تماماً في شاشة الدفع.'
                  : 'B2B Credit is disabled for this customer and hidden at checkout.'),
          style: TextStyle(
            fontSize: 13,
            color: isB2bEnabled ? Colors.purple.shade900 : const Color(0xFF64748B),
          ),
        ),
        if (isB2bEnabled) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE9D5FF)),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    const Icon(Icons.domain_verification_rounded, size: 18, color: Color(0xFF64748B)),
                    const SizedBox(width: 10),
                    Text(
                      isRtl ? 'النطاق المعتمد' : 'Authorized Scope',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Text(
                        scopeText,
                        textAlign: TextAlign.end,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                      ),
                    ),
                  ],
                ),
                if (creditLimit != null && (num.tryParse(creditLimit.toString()) ?? 0) > 0) ...[
                  const Divider(height: 24),
                  Row(
                    children: [
                      const Icon(Icons.account_balance_wallet_outlined, size: 18, color: Color(0xFF64748B)),
                      const SizedBox(width: 10),
                      Text(
                        isRtl ? 'سقف الائتمان' : 'Credit Limit',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Text(
                          '${NumberFormat('#,##0').format(num.parse(creditLimit.toString()))} ${isRtl ? "ج.س" : "SDG"}',
                          textAlign: TextAlign.end,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                        ),
                      ),
                    ],
                  ),
                ]
              ],
            ),
          ),
        ],
        const SizedBox(height: 16),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => _showEditB2bCreditDialog(context, ref, sub, isRtl),
            icon: const Icon(Icons.tune_rounded, size: 16),
            label: Text(
              isRtl ? 'ضبط وتعديل تسهيلات الدفع الآجل (B2B Credit)' : 'Configure B2B Credit',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
            ),
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFF7E22CE),
              side: const BorderSide(color: Color(0xFFA855F7)),
              backgroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ),
      ],
    ),
  );
}

void _showEditB2bCreditDialog(BuildContext context, WidgetRef ref, SubscriberModel sub, bool isRtl) {
  bool isB2bEnabled = sub.permissions['b2b_credit_enabled'] == true;
  String selectedScope = sub.permissions['b2b_allowed_scope']?.toString() ?? 'all';
  final limitCtrl = TextEditingController(
    text: sub.permissions['b2b_credit_limit'] != null ? sub.permissions['b2b_credit_limit'].toString() : '',
  );

  showDialog(
    context: context,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, setDialogState) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.business_center_rounded, color: Color(0xFF7E22CE)),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                isRtl ? 'تسهيلات الدفع الآجل (B2B Credit)' : 'B2B Credit Facilities',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: SizedBox(
            width: 480,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isB2bEnabled ? const Color(0xFFFAF5FF) : const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isB2bEnabled ? const Color(0xFFD8B4FE) : const Color(0xFFCBD5E1),
                    ),
                  ),
                  child: SwitchListTile(
                    title: Text(
                      isRtl ? 'تمكين الدفع الآجل للشركات (B2B Credit)' : 'Enable B2B Credit',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                    subtitle: Text(
                      isB2bEnabled
                          ? (isRtl ? 'مصرّح: يظهر خيار B2B Credit لهذا العميل عند إتمام الطلب' : 'Allowed: B2B Credit appears for this buyer at checkout')
                          : (isRtl ? 'مخفي: لن يظهر خيار B2B Credit لهذا العميل نهائياً' : 'Hidden: B2B Credit option will not appear for this buyer'),
                      style: TextStyle(
                        fontSize: 11,
                        color: isB2bEnabled ? const Color(0xFF6B21A8) : const Color(0xFF64748B),
                      ),
                    ),
                    value: isB2bEnabled,
                    activeTrackColor: const Color(0xFF7E22CE),
                    onChanged: (val) => setDialogState(() => isB2bEnabled = val),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                if (isB2bEnabled) ...[
                  const SizedBox(height: 16),
                  Text(
                    isRtl ? 'نطاق المشتريات المسموح بها بالآجل:' : 'Authorized Scope for B2B:',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                  ),
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    initialValue: selectedScope,
                    decoration: InputDecoration(
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      prefixIcon: const Icon(Icons.domain_verification_rounded, color: Color(0xFF7E22CE)),
                    ),
                    items: [
                      DropdownMenuItem(
                        value: 'all',
                        child: Text(isRtl ? 'المنصة كاملة (منتجات السيطرة + تجار السوق)' : 'All Platform (Magaiees + Merchants)'),
                      ),
                      DropdownMenuItem(
                        value: 'exclusive_only',
                        child: Text(isRtl ? 'منتجات السيطرة الحصرية فقط' : 'Magaiees Exclusive Products only'),
                      ),
                      DropdownMenuItem(
                        value: 'merchants_only',
                        child: Text(isRtl ? 'تجار السوق والموردين فقط' : 'Marketplace Merchants only'),
                      ),
                    ],
                    onChanged: (val) {
                      if (val != null) setDialogState(() => selectedScope = val);
                    },
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: limitCtrl,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'سقف التسهيلات الائتمانية (ج.س) - اختياري' : 'Credit Limit (SDG) - Optional',
                      hintText: isRtl ? 'مثال: 5000000 أو اتركه فارغاً بدون سقف' : 'e.g. 5000000 or leave empty',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      prefixIcon: const Icon(Icons.credit_score_rounded, color: Color(0xFF7E22CE)),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF7E22CE),
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              final limitVal = double.tryParse(limitCtrl.text.trim());
              await ref.read(subscribersProvider.notifier).updateBuyerB2bCredit(
                userId: sub.id,
                enabled: isB2bEnabled,
                allowedScope: selectedScope,
                creditLimit: limitVal,
              );
              if (ctx.mounted) Navigator.pop(ctx);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isRtl ? 'تم حفظ واعتماد تسهيلات الدفع الآجل للعميل بنجاح!' : 'B2B Credit facilities updated successfully!'),
                    backgroundColor: const Color(0xFF7E22CE),
                  ),
                );
              }
            },
            child: Text(isRtl ? 'حفظ التعديلات' : 'Save Changes'),
          ),
        ],
      ),
    ),
  );
}
