import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import '../../application/subscribers_provider.dart';
import '../../domain/models/subscriber_model.dart';
import '../widgets/subscriber_detail_sheet.dart';

enum UiFilterType { all, buyers, merchants, drivers, pending, active, suspended }

class SubscribersManagementScreen extends ConsumerStatefulWidget {
  const SubscribersManagementScreen({super.key});

  @override
  ConsumerState<SubscribersManagementScreen> createState() => _SubscribersManagementScreenState();
}

class _SubscribersManagementScreenState extends ConsumerState<SubscribersManagementScreen> {
  final TextEditingController _searchController = TextEditingController();
  UiFilterType _selectedFilter = UiFilterType.all;
  bool _isLoading = false;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _runAsync(Future<void> Function() action) async {
    setState(() => _isLoading = true);
    try {
      await action();
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final asyncSubscribers = ref.watch(subscribersProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          isRtl ? 'إدارة المشتركين' : 'Subscribers Management',
          style: const TextStyle(
            color: Color(0xFF0F172A),
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.stars, color: Colors.amber),
            tooltip: isRtl ? 'إعدادات المنصة' : 'Platform Settings',
            onPressed: () => _showGamificationSettingsDialog(context, isRtl),
          ),
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF1E40AF)),
            onPressed: () => ref.read(subscribersProvider.notifier).loadSubscribers(),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Stack(
        children: [
          Column(
            children: [
              // Search & Filters Header
              Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 8,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                      child: TextField(
                        controller: _searchController,
                        onChanged: (v) => setState(() {}),
                        decoration: InputDecoration(
                          hintText: isRtl ? 'ابحث عن مشترك...' : 'Search subscribers...',
                          hintStyle: TextStyle(color: Colors.grey.shade400),
                          prefixIcon: const Icon(Icons.search, color: Color(0xFF1E40AF)),
                          filled: true,
                          fillColor: const Color(0xFFF1F5F9),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        ),
                      ),
                    ),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      child: Row(
                        children: UiFilterType.values.map((type) {
                          final isSelected = _selectedFilter == type;
                          return Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 4),
                            child: ChoiceChip(
                              label: Text(
                                _getUiFilterName(type, isRtl),
                                style: TextStyle(
                                  color: isSelected ? Colors.white : const Color(0xFF0F172A),
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                ),
                              ),
                              selected: isSelected,
                              onSelected: (selected) {
                                if (selected) setState(() => _selectedFilter = type);
                              },
                              backgroundColor: Colors.white,
                              selectedColor: const Color(0xFF1E40AF),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(20),
                                side: BorderSide(
                                  color: isSelected ? const Color(0xFF1E40AF) : Colors.grey.shade300,
                                ),
                              ),
                              showCheckmark: false,
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
                  loading: () => const Center(
                    child: CircularProgressIndicator(color: Color(0xFF1E40AF)),
                  ),
                  error: (err, stack) => Center(
                    child: Text('Error: $err', style: const TextStyle(color: Colors.red)),
                  ),
                  data: (subs) {
                    final filtered = _getFilteredSubscribers(subs);
                    if (filtered.isEmpty) {
                      return Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.people_outline, size: 80, color: Colors.grey.shade300),
                            const SizedBox(height: 16),
                            Text(
                              isRtl ? 'لا يوجد مشتركين' : 'No subscribers found',
                              style: TextStyle(
                                fontSize: 18,
                                color: Colors.grey.shade500,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      );
                    }

                    return ListView.builder(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      itemCount: filtered.length,
                      itemBuilder: (context, index) {
                        return _buildExpandableCard(filtered[index], isRtl);
                      },
                    );
                  },
                ),
              ),
            ],
          ),
          if (_isLoading)
            Container(
              color: Colors.black.withValues(alpha: 0.1),
              child: const Center(
                child: CircularProgressIndicator(color: Color(0xFF1E40AF)),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildExpandableCard(SubscriberModel sub, bool isRtl) {
    final statusColor = _getStatusColor(sub.status);
    final roleColor = _getRoleColor(sub.role);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          )
        ],
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.all(16),
          iconColor: const Color(0xFF1E40AF),
          collapsedIconColor: Colors.grey.shade400,
          title: Row(
            children: [
              Container(
                height: 48,
                width: 48,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF1E40AF), Color(0xFF3B82F6)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF1E40AF).withValues(alpha: 0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    sub.name.isNotEmpty ? sub.name[0].toUpperCase() : '?',
                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? sub.nameAr : sub.name,
                      style: const TextStyle(
                        color: Color(0xFF0F172A),
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      sub.email,
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 13,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
          subtitle: Padding(
            padding: const EdgeInsets.only(top: 12),
            child: Row(
              children: [
                _buildModernChip(
                  label: _getRoleName(sub.role, isRtl),
                  color: roleColor,
                  icon: Icons.badge_outlined,
                ),
                const SizedBox(width: 8),
                _buildModernChip(
                  label: _getStatusName(sub.status, isRtl),
                  color: statusColor,
                  icon: sub.status == SubscriberStatus.active
                      ? Icons.check_circle_outline
                      : sub.status == SubscriberStatus.suspended
                          ? Icons.block
                          : Icons.pending_actions,
                ),
              ],
            ),
          ),
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildStatCol(Icons.phone_outlined, isRtl ? 'الهاتف' : 'Phone', sub.phone.isEmpty ? '-' : sub.phone),
                      _buildStatCol(Icons.shopping_bag_outlined, isRtl ? 'الطلبات' : 'Orders', sub.totalOrders.toString()),
                      _buildStatCol(Icons.calendar_today_outlined, isRtl ? 'تاريخ الانضمام' : 'Joined', DateFormat('yyyy-MMM-dd').format(sub.joinedAt)),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      _buildActionButton(
                        icon: Icons.manage_accounts,
                        label: isRtl ? 'الصلاحية' : 'Role',
                        color: const Color(0xFF1E40AF),
                        onTap: () => _showChangeRoleDialog(sub),
                      ),
                      _buildActionButton(
                        icon: sub.status == SubscriberStatus.suspended ? Icons.check_circle : Icons.block,
                        label: sub.status == SubscriberStatus.suspended ? (isRtl ? 'إلغاء حظر' : 'Unblock') : (isRtl ? 'حظر' : 'Suspend'),
                        color: Colors.orange.shade700,
                        onTap: () async {
                          await _runAsync(() => ref.read(subscribersProvider.notifier).toggleSuspension(sub.id));
                        },
                      ),
                      _buildActionButton(
                        icon: Icons.visibility_outlined,
                        label: isRtl ? 'التفاصيل' : 'Details',
                        color: Colors.teal.shade600,
                        onTap: () => _showDetailSheet(context, sub),
                      ),
                      _buildActionButton(
                        icon: Icons.delete_outline,
                        label: isRtl ? 'حذف' : 'Delete',
                        color: Colors.red.shade600,
                        onTap: () => _showDeleteConfirm(sub),
                      ),
                    ],
                  ),
                ],
              ),
            )
          ],
        ),
      ),
    );
  }

  Widget _buildModernChip({required String label, required Color color, required IconData icon}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCol(IconData icon, String label, String value) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 20, color: Colors.grey.shade500),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
      ],
    );
  }

  Widget _buildActionButton({required IconData icon, required String label, required Color color, required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
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

  void _showChangeRoleDialog(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(isRtl ? 'تغيير صلاحية المستخدم' : 'Change Role', style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.bold)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: UserRole.values.map((role) {
              return RadioListTile<UserRole>(
                title: Text(_getRoleName(role, isRtl)),
                value: role,
                groupValue: subscriber.role,
                activeColor: const Color(0xFF1E40AF),
                onChanged: (newRole) async {
                  if (newRole != null) {
                    Navigator.pop(context);
                    await _runAsync(() => ref.read(subscribersProvider.notifier).changeRole(subscriber.id, newRole));
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(isRtl ? 'تم تغيير الصلاحية بنجاح' : 'Role updated successfully'),
                          backgroundColor: Colors.teal.shade600,
                        ),
                      );
                    }
                  }
                },
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
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.red.shade600),
            const SizedBox(width: 8),
            Text(isRtl ? 'تأكيد الحذف' : 'Confirm Deletion', style: const TextStyle(color: Color(0xFF0F172A))),
          ],
        ),
        content: Text(
          isRtl 
              ? 'هل أنت متأكد من حذف هذا المستخدم نهائياً؟ لا يمكن التراجع عن هذا الإجراء.'
              : 'Are you sure you want to permanently delete this user?',
          style: const TextStyle(fontSize: 15),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade700)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _runAsync(() => ref.read(subscribersProvider.notifier).removeSubscriber(subscriber.id));
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade600,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: Text(isRtl ? 'حذف' : 'Delete'),
          ),
        ],
      ),
    );
  }

  Future<void> _showGamificationSettingsDialog(BuildContext context, bool isRtl) async {
    final pointsPerOrderCtrl = TextEditingController();
    final salesThresholdCtrl = TextEditingController();
    final pointsPerSalesCtrl = TextEditingController();
    final penaltyCtrl = TextEditingController();
    final commissionCtrl = TextEditingController();
    bool enableCommission = false;
    bool enableCoupons = true;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(child: CircularProgressIndicator(color: Color(0xFF1E40AF))),
    );
    
    try {
      final res = await Supabase.instance.client.from('gamification_settings').select().eq('id', 1).maybeSingle();
      if (res != null) {
        pointsPerOrderCtrl.text = res['points_per_order']?.toString() ?? '10';
        salesThresholdCtrl.text = res['sales_threshold_sdg']?.toString() ?? '50000';
        pointsPerSalesCtrl.text = res['points_per_sales_threshold']?.toString() ?? '1';
        penaltyCtrl.text = res['penalty_cancel_order']?.toString() ?? '20';
        commissionCtrl.text = res['commission_percentage']?.toString() ?? '0.0';
        enableCommission = res['enable_commission'] == true;
        enableCoupons = res['enable_coupons'] ?? true;
      }
    } catch (_) {
      pointsPerOrderCtrl.text = '10';
      salesThresholdCtrl.text = '50000';
      pointsPerSalesCtrl.text = '1';
      penaltyCtrl.text = '20';
      commissionCtrl.text = '0.0';
    }

    if (context.mounted) Navigator.pop(context); 
    if (!context.mounted) return;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Row(
              children: [
                const Icon(Icons.stars_rounded, color: Colors.amber, size: 28),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    isRtl ? 'إعدادات المنصة والتجار' : 'Platform Settings',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                ),
              ],
            ),
            content: SizedBox(
              width: double.maxFinite,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildSettingsHeader(isRtl ? 'الكوبونات والعروض' : 'Coupons & Offers', Icons.local_offer_outlined, Colors.purple),
                    SwitchListTile(
                      title: Text(isRtl ? 'تفعيل نظام الكوبونات' : 'Enable Coupons System', style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: Text(
                        isRtl ? 'السماح بإنشاء أكواد خصم' : 'Allow promo codes creation',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      ),
                      value: enableCoupons,
                      activeColor: const Color(0xFF1E40AF),
                      onChanged: (v) => setState(() => enableCoupons = v),
                      contentPadding: EdgeInsets.zero,
                    ),
                    const Divider(height: 24),

                    _buildSettingsHeader(isRtl ? 'العمولات' : 'Commissions', Icons.percent_rounded, Colors.blue),
                    SwitchListTile(
                      title: Text(isRtl ? 'تفعيل نظام العمولات' : 'Enable Commission System', style: const TextStyle(fontWeight: FontWeight.w600)),
                      value: enableCommission,
                      activeColor: const Color(0xFF1E40AF),
                      onChanged: (v) => setState(() => enableCommission = v),
                      contentPadding: EdgeInsets.zero,
                    ),
                    if (enableCommission) ...[
                      const SizedBox(height: 12),
                      _buildSettingsTextField(
                        controller: commissionCtrl,
                        label: isRtl ? 'نسبة العمولة %' : 'Commission %',
                        icon: Icons.pie_chart_outline,
                        isNumber: true,
                      ),
                    ],
                    const Divider(height: 32),

                    _buildSettingsHeader(isRtl ? 'نقاط التدرج (Gamification)' : 'Gamification Rules', Icons.emoji_events_outlined, Colors.amber.shade700),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: pointsPerOrderCtrl,
                      label: isRtl ? 'النقاط لاكتمال الطلب' : 'Points per order',
                      icon: Icons.check_circle_outline,
                      isNumber: true,
                    ),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: salesThresholdCtrl,
                      label: isRtl ? 'حد المبيعات (ج.س)' : 'Sales Threshold (SDG)',
                      icon: Icons.monetization_on_outlined,
                      isNumber: true,
                    ),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: pointsPerSalesCtrl,
                      label: isRtl ? 'النقاط لكل حد مبيعات' : 'Points per threshold',
                      icon: Icons.star_outline,
                      isNumber: true,
                    ),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: penaltyCtrl,
                      label: isRtl ? 'خصم لإلغاء الطلب' : 'Cancel penalty',
                      icon: Icons.cancel_outlined,
                      isNumber: true,
                    ),
                  ],
                ),
              ),
            ),
            actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade700)),
              ),
              ElevatedButton(
                onPressed: () async {
                  Navigator.pop(ctx);
                  await _runAsync(() async {
                    try {
                      await Supabase.instance.client.from('gamification_settings').upsert({
                        'id': 1,
                        'points_per_order': int.tryParse(pointsPerOrderCtrl.text) ?? 10,
                        'sales_threshold_sdg': int.tryParse(salesThresholdCtrl.text) ?? 50000,
                        'points_per_sales_threshold': int.tryParse(pointsPerSalesCtrl.text) ?? 1,
                        'penalty_cancel_order': int.tryParse(penaltyCtrl.text) ?? 20,
                        'enable_commission': enableCommission,
                        'commission_percentage': double.tryParse(commissionCtrl.text) ?? 0.0,
                        'enable_coupons': enableCoupons,
                      });
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? 'تم الحفظ بنجاح' : 'Saved successfully'),
                            backgroundColor: Colors.teal.shade600,
                          ),
                        );
                      }
                    } catch (_) {}
                  });
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1E40AF),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                ),
                child: Text(isRtl ? 'حفظ التعديلات' : 'Save Changes'),
              ),
            ],
          );
        }
      ),
    );
  }

  Widget _buildSettingsHeader(String title, IconData icon, Color color) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 8),
        Text(title, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 15)),
      ],
    );
  }

  Widget _buildSettingsTextField({required TextEditingController controller, required String label, required IconData icon, bool isNumber = false}) {
    return TextField(
      controller: controller,
      keyboardType: isNumber ? const TextInputType.numberWithOptions(decimal: true) : TextInputType.text,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: Colors.grey.shade500, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFF1E40AF))),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        isDense: true,
      ),
    );
  }

  String _getUiFilterName(UiFilterType type, bool isRtl) {
    switch (type) {
      case UiFilterType.all: return isRtl ? 'الكل' : 'All';
      case UiFilterType.buyers: return isRtl ? 'المشترين' : 'Buyers';
      case UiFilterType.merchants: return isRtl ? 'التجار' : 'Merchants';
      case UiFilterType.drivers: return isRtl ? 'المناديب' : 'Drivers';
      case UiFilterType.pending: return isRtl ? 'قيد المراجعة' : 'Pending Verification';
      case UiFilterType.active: return isRtl ? 'نشط' : 'Active';
      case UiFilterType.suspended: return isRtl ? 'موقوف' : 'Suspended';
    }
  }

  List<SubscriberModel> _getFilteredSubscribers(List<SubscriberModel> subs) {
    final query = _searchController.text.toLowerCase();
    return subs.where((s) {
      bool matchF = true;
      switch (_selectedFilter) {
        case UiFilterType.all: matchF = true; break;
        case UiFilterType.buyers: matchF = s.role == UserRole.buyer; break;
        case UiFilterType.merchants: matchF = s.role == UserRole.merchant; break;
        case UiFilterType.drivers: matchF = s.role == UserRole.employee; break;
        case UiFilterType.pending: matchF = s.status == SubscriberStatus.pendingVerification; break;
        case UiFilterType.active: matchF = s.status == SubscriberStatus.active; break;
        case UiFilterType.suspended: matchF = s.status == SubscriberStatus.suspended; break;
      }
      bool matchQ = true;
      if (query.isNotEmpty) {
        matchQ = s.name.toLowerCase().contains(query) ||
                 s.nameAr.toLowerCase().contains(query) ||
                 s.email.toLowerCase().contains(query) ||
                 s.phone.contains(query);
      }
      return matchF && matchQ;
    }).toList();
  }

  String _getRoleName(UserRole role, bool isRtl) {
    switch (role) {
      case UserRole.admin: return isRtl ? 'ادارة' : 'Admin';
      case UserRole.merchant: return isRtl ? 'تاجر' : 'Merchant';
      case UserRole.buyer: return isRtl ? 'مشتري' : 'Buyer';
      case UserRole.guest: return isRtl ? 'زائر' : 'Guest';
      case UserRole.employee: return isRtl ? 'موظف' : 'Employee';
    }
  }

  Color _getRoleColor(UserRole role) {
    switch (role) {
      case UserRole.admin: return Colors.purple.shade700;
      case UserRole.merchant: return Colors.green.shade700;
      case UserRole.buyer: return const Color(0xFF1E40AF);
      case UserRole.guest: return Colors.grey.shade600;
      case UserRole.employee: return Colors.orange.shade700;
    }
  }

  String _getStatusName(SubscriberStatus status, bool isRtl) {
    switch (status) {
      case SubscriberStatus.active: return isRtl ? 'نشط' : 'Active';
      case SubscriberStatus.suspended: return isRtl ? 'موقوف' : 'Suspended';
      case SubscriberStatus.pendingVerification: return isRtl ? 'قيد المراجعة' : 'Pending';
    }
  }

  Color _getStatusColor(SubscriberStatus status) {
    switch (status) {
      case SubscriberStatus.active: return Colors.teal.shade600;
      case SubscriberStatus.suspended: return Colors.red.shade600;
      case SubscriberStatus.pendingVerification: return Colors.amber.shade700;
    }
  }
}

