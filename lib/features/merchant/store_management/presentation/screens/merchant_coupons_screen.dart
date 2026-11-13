import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/merchant/store_management/application/merchant_coupons_provider.dart';

class MerchantCouponsScreen extends ConsumerStatefulWidget {
  const MerchantCouponsScreen({super.key});

  @override
  ConsumerState<MerchantCouponsScreen> createState() => _MerchantCouponsScreenState();
}

class _MerchantCouponsScreenState extends ConsumerState<MerchantCouponsScreen> {
  @override
  Widget build(BuildContext context) {
    final state = ref.watch(merchantCouponsProvider);

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('إدارة الكوبونات'),
          centerTitle: true,
        ),
        body: state.when(
          data: (data) {
            final bool enableCoupons = data['enableCoupons'] ?? false;
            final List<dynamic> coupons = data['coupons'] ?? [];

            if (!enableCoupons) {
              return Center(
                child: Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.lock_outline,
                        size: 80,
                        color: Colors.grey[400],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'نظام الكوبونات معطل حالياً من قبل الإدارة',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              color: Colors.grey[600],
                              fontWeight: FontWeight.bold,
                            ),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              );
            }

            if (coupons.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.local_offer_outlined,
                      size: 80,
                      color: Colors.grey[400],
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'لا توجد كوبونات حالياً',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            color: Colors.grey[600],
                          ),
                    ),
                  ],
                ),
              );
            }

            return RefreshIndicator(
              onRefresh: () async {
                // Force a reload of the provider by invalidating it
                ref.invalidate(merchantCouponsProvider);
              },
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: coupons.length,
                itemBuilder: (context, index) {
                  final coupon = coupons[index];
                  final bool isActive = coupon['is_active'] ?? false;
                  final DateTime expiry = DateTime.tryParse(coupon['expiry_date']?.toString() ?? '') ?? DateTime.now();
                  final bool isExpired = expiry.isBefore(DateTime.now());

                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    elevation: 2,
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Text(
                                    coupon['code'] ?? '',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 20,
                                      letterSpacing: 1.5,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: coupon['type'] == 'percentage'
                                          ? Colors.blue.withOpacity(0.1)
                                          : Colors.green.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      coupon['type'] == 'percentage' ? 'نسبة مئوية' : 'مبلغ ثابت',
                                      style: TextStyle(
                                        color: coupon['type'] == 'percentage' ? Colors.blue[700] : Colors.green[700],
                                        fontWeight: FontWeight.w600,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              Switch(
                                value: isActive,
                                activeColor: Theme.of(context).primaryColor,
                                onChanged: (value) async {
                                  try {
                                    await ref.read(merchantCouponsProvider.notifier).toggleCoupon(
                                      coupon['id'].toString(),
                                      value,
                                    );
                                  } catch (e) {
                                    if (mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text('حدث خطأ: $e')),
                                      );
                                    }
                                  }
                                },
                              ),
                            ],
                          ),
                          const Divider(height: 24),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'قيمة الخصم',
                                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${coupon['value']} ${coupon['type'] == 'percentage' ? '%' : 'ج.س'}',
                                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
                                  ),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'الحد الأدنى',
                                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${coupon['min_order_amount']} ج.س',
                                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
                                  ),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'تاريخ الانتهاء',
                                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    DateFormat('yyyy-MM-dd').format(expiry),
                                    style: TextStyle(
                                      fontWeight: FontWeight.w600,
                                      fontSize: 16,
                                      color: isExpired ? Colors.red : null,
                                      decoration: isExpired ? TextDecoration.lineThrough : null,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          if (isExpired)
                            Padding(
                              padding: const EdgeInsets.only(top: 12.0),
                              child: Row(
                                children: [
                                  const Icon(Icons.warning_amber_rounded, color: Colors.red, size: 16),
                                  const SizedBox(width: 4),
                                  Text(
                                    'هذا الكوبون منتهي الصلاحية',
                                    style: TextStyle(color: Colors.red[700], fontSize: 12, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, size: 48, color: Colors.red),
                const SizedBox(height: 16),
                Text(
                  'حدث خطأ في تحميل الكوبونات',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                TextButton(
                  onPressed: () => ref.invalidate(merchantCouponsProvider),
                  child: const Text('إعادة المحاولة'),
                )
              ],
            ),
          ),
        ),
        floatingActionButton: state.maybeWhen(
          data: (data) => data['enableCoupons'] == true
              ? FloatingActionButton.extended(
                  onPressed: () => _showAddCouponDialog(context),
                  icon: const Icon(Icons.add),
                  label: const Text('إضافة كوبون'),
                )
              : null,
          orElse: () => null,
        ),
      ),
    );
  }

  void _showAddCouponDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => const AddCouponDialog(),
    );
  }
}

class AddCouponDialog extends ConsumerStatefulWidget {
  const AddCouponDialog({super.key});

  @override
  ConsumerState<AddCouponDialog> createState() => _AddCouponDialogState();
}

class _AddCouponDialogState extends ConsumerState<AddCouponDialog> {
  final _formKey = GlobalKey<FormState>();
  final _codeController = TextEditingController();
  final _valueController = TextEditingController();
  final _minOrderController = TextEditingController();
  final _limitController = TextEditingController();

  String _selectedType = 'percentage';
  DateTime? _selectedExpiry;
  bool _isLoading = false;

  @override
  void dispose() {
    _codeController.dispose();
    _valueController.dispose();
    _minOrderController.dispose();
    _limitController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: AlertDialog(
        title: const Text('إضافة كوبون جديد', style: TextStyle(fontWeight: FontWeight.bold)),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        content: SingleChildScrollView(
          child: Form(
            key: _formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextFormField(
                  controller: _codeController,
                  decoration: InputDecoration(
                    labelText: 'كود الكوبون',
                    hintText: 'مثال: MEGA2024',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    prefixIcon: const Icon(Icons.qr_code),
                  ),
                  textCapitalization: TextCapitalization.characters,
                  validator: (value) => value == null || value.isEmpty ? 'هذا الحقل مطلوب' : null,
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  value: _selectedType,
                  decoration: InputDecoration(
                    labelText: 'نوع الخصم',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    prefixIcon: const Icon(Icons.discount_outlined),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'percentage', child: Text('نسبة مئوية (%)')),
                    DropdownMenuItem(value: 'fixed', child: Text('مبلغ ثابت (ج.س)')),
                  ],
                  onChanged: (value) {
                    if (value != null) setState(() => _selectedType = value);
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: TextFormField(
                        controller: _valueController,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                          labelText: 'قيمة الخصم',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        validator: (value) => value == null || value.isEmpty ? 'مطلوب' : null,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: TextFormField(
                        controller: _limitController,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                          labelText: 'الحد الأقصى للاستخدام',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        validator: (value) => value == null || value.isEmpty ? 'مطلوب' : null,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _minOrderController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'الحد الأدنى للطلب (ج.س)',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    prefixIcon: const Icon(Icons.shopping_bag_outlined),
                  ),
                  validator: (value) => value == null || value.isEmpty ? 'هذا الحقل مطلوب' : null,
                ),
                const SizedBox(height: 16),
                Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey.shade400),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: ListTile(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    title: const Text('تاريخ الانتهاء'),
                    subtitle: Text(
                      _selectedExpiry == null
                          ? 'لم يتم التحديد'
                          : DateFormat('yyyy-MM-dd').format(_selectedExpiry!),
                      style: TextStyle(
                        color: _selectedExpiry == null ? Colors.red : Colors.green[700],
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    trailing: const Icon(Icons.calendar_month),
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: DateTime.now().add(const Duration(days: 1)),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (picked != null) {
                        setState(() => _selectedExpiry = picked);
                      }
                    },
                  ),
                ),
              ],
            ),
          ),
        ),
        actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
            onPressed: _isLoading
                ? null
                : () async {
                    if (_formKey.currentState!.validate() && _selectedExpiry != null) {
                      setState(() => _isLoading = true);
                      try {
                        await ref.read(merchantCouponsProvider.notifier).createCoupon(
                          _codeController.text,
                          _selectedType,
                          double.tryParse(_valueController.text) ?? 0.0,
                          double.tryParse(_minOrderController.text) ?? 0.0,
                          int.tryParse(_limitController.text) ?? 1,
                          _selectedExpiry!,
                        );
                        if (mounted) Navigator.pop(context);
                      } catch (e) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('حدث خطأ: $e')),
                          );
                        }
                      } finally {
                        if (mounted) setState(() => _isLoading = false);
                      }
                    } else if (_selectedExpiry == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('الرجاء تحديد تاريخ الانتهاء')),
                      );
                    }
                  },
            child: _isLoading
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                : const Text('إضافة الكوبون'),
          ),
        ],
      ),
    );
  }
}
