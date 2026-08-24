import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../cart/application/cart_provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  int _currentStep = 0;
  String _selectedPaymentMethod = 'ocash';
  String _deliveryAddress = 'سوق السجانة لمواد البناء، الخرطوم، السودان';
  String _deliveryAddressEn = 'El Sajana Construction Market, Khartoum, Sudan';

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final cart = ref.watch(cartNotifierProvider);
    final subtotal = ref.watch(cartSubtotalProvider);
    final transportTotal = ref.watch(cartTransportTotalProvider);
    final offloadingTotal = ref.watch(cartOffloadingTotalProvider);
    final vat = ref.watch(cartVatTotalProvider);
    final grandTotal = ref.watch(cartGrandTotalProvider);
    final currency = isRtl ? 'ج.س' : 'SDG';

    return Scaffold(
      appBar: AppBar(title: Text(isRtl ? 'إتمام الطلب والدفع' : 'Checkout')),
      body: Stepper(
        type: StepperType.vertical,
        currentStep: _currentStep,
        onStepContinue: () async {
          if (_currentStep < 2) {
            setState(() => _currentStep += 1);
          } else {
            // Place order in Supabase
            final user = ref.read(authNotifierProvider).valueOrNull;
            final cart = ref.read(cartNotifierProvider);
            
            if (user != null && cart.items.isNotEmpty) {
              try {
                // Group cart items by merchantId
                final Map<String, List<dynamic>> storeItems = {};
                for (var item in cart.items) {
                  final merchantId = item.product.merchantId;
                  storeItems.putIfAbsent(merchantId, () => []).add(item);
                }

                // Create an order for each store
                for (var entry in storeItems.entries) {
                  final storeId = entry.key;
                  final items = entry.value;
                  
                  // Calculate total for this store
                  double storeTotal = 0.0;
                  for (var item in items) {
                    storeTotal += item.product.pricePerTon * item.quantity;
                    storeTotal += item.product.transportFeePerTon * item.quantity;
                    storeTotal += item.product.offloadingFeePerTon * item.quantity;
                  }
                  
                  // 15% VAT
                  storeTotal = storeTotal * 1.15;
                  
                  // Check if storeId is valid UUID (very basic check)
                  final isUuid = RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false).hasMatch(storeId);

                  // Insert into public.orders
                  final List<dynamic> orderRes = await Supabase.instance.client.from('orders').insert({
                    'buyer_id': user.id,
                    'store_id': isUuid ? storeId : null,
                    'total_amount': storeTotal,
                    'status': 'pending',
                    'shipping_address': _deliveryAddress,
                  }).select('id');
                  
                  if (orderRes.isNotEmpty) {
                    final orderId = orderRes.first['id'];
                    
                    // Insert order_items
                    for (var item in items) {
                      final isProdUuid = RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false).hasMatch(item.product.id);
                      
                      await Supabase.instance.client.from('order_items').insert({
                        'order_id': orderId,
                        'product_id': isProdUuid ? item.product.id : null,
                        'product_name': item.product.nameAr.isNotEmpty ? item.product.nameAr : item.product.name,
                        'quantity': item.quantity,
                        'price_sdg': item.product.pricePerTon,
                        'price_usd': item.product.priceInUsd ?? 0.0,
                      });
                    }
                  }
                }
                
                if (mounted) {
                  _showSuccessDialog(isRtl);
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(isRtl ? 'حدث خطأ: $e' : 'Error: $e'), backgroundColor: Colors.red),
                  );
                }
              }
            } else if (cart.items.isEmpty && mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(isRtl ? 'السلة فارغة' : 'Cart is empty'), backgroundColor: Colors.orange),
                );
            }
          }
        },
        onStepCancel: () {
          if (_currentStep > 0) {
            setState(() => _currentStep -= 1);
          }
        },
        controlsBuilder: (context, details) {
          final isLastStep = _currentStep == 2;
          return Padding(
            padding: const EdgeInsets.only(top: 16.0),
            child: Row(
              children: [
                Expanded(
                  child: ElevatedButton(
                    onPressed: details.onStepContinue,
                    child: Text(
                      isLastStep
                          ? (isRtl ? 'تأكيد وإرسال الطلب' : 'Place Order')
                          : (isRtl ? 'متابعة' : 'Continue'),
                    ),
                  ),
                ),
                if (_currentStep > 0) ...[
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton(
                      onPressed: details.onStepCancel,
                      child: Text(isRtl ? 'رجوع' : 'Back'),
                    ),
                  ),
                ],
              ],
            ),
          );
        },
        steps: [
          Step(
            title: Text(isRtl ? 'عنوان التوصيل' : 'Delivery Address'),
            content: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.location_on, color: Colors.blue),
                      const SizedBox(width: 8),
                      Text(
                        isRtl ? 'العنوان المحدد' : 'Selected Address',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(isRtl ? _deliveryAddress : _deliveryAddressEn),
                  const SizedBox(height: 8),
                  TextButton.icon(
                    onPressed: () => _showChangeAddressDialog(context, isRtl),
                    icon: const Icon(Icons.edit, size: 16),
                    label: Text(isRtl ? 'تغيير العنوان' : 'Change Address'),
                  ),
                ],
              ),
            ),
            isActive: _currentStep >= 0,
            state: _currentStep > 0 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: Text(isRtl ? 'ملخص الطلب' : 'Order Summary'),
            content: Column(
              children: [
                ...cart.itemsByMerchant.entries.map((entry) {
                  final items = entry.value;
                  final storeName = isRtl
                      ? (items.first.product.storeNameAr.isNotEmpty ? items.first.product.storeNameAr : items.first.product.storeName)
                      : (items.first.product.storeName.isNotEmpty ? items.first.product.storeName : items.first.product.storeNameAr);
                  
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(storeName, style: const TextStyle(fontWeight: FontWeight.bold)),
                          const Divider(),
                          ...items.map((item) {
                            final itemName = isRtl
                                ? (item.product.nameAr.isNotEmpty ? item.product.nameAr : item.product.name)
                                : (item.product.name.isNotEmpty ? item.product.name : item.product.nameAr);
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 8.0),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Text('${item.quantity.toStringAsFixed(0)}x $itemName'),
                                  ),
                                  Text('${(item.product.pricePerTon * item.quantity).toStringAsFixed(2)} $currency'),
                                ],
                              ),
                            );
                          }),
                        ],
                      ),
                    ),
                  );
                }),
                const SizedBox(height: 8),
                _SummaryRow(isRtl ? 'المجموع الفرعي للمنتجات:' : 'Items Subtotal:', subtotal, currency: currency),
                _SummaryRow(isRtl ? 'رسوم النقل:' : 'Transport Fee:', transportTotal, currency: currency),
                _SummaryRow(isRtl ? 'رسوم التنزيل:' : 'Offloading Fee:', offloadingTotal, currency: currency),
                _SummaryRow(isRtl ? 'ضريبة القيمة المضافة (15%):' : 'VAT (15%):', vat, currency: currency),
                const Divider(),
                _SummaryRow(isRtl ? 'المجموع الكلي:' : 'Grand Total:', grandTotal, isTotal: true, currency: currency),
              ],
            ),
            isActive: _currentStep >= 1,
            state: _currentStep > 1 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: Text(isRtl ? 'طريقة الدفع' : 'Payment Method'),
            content: Column(
              children: [
                _PaymentRadio(
                  title: isRtl ? 'تطبيق أوكاش (O-Cash)' : 'O-Cash App Transfer',
                  value: 'ocash',
                  groupValue: _selectedPaymentMethod,
                  onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
                  icon: Icons.payments,
                ),
                _PaymentRadio(
                  title: isRtl ? 'تطبيق بنكك (Bankak)' : 'Bankak App Transfer',
                  value: 'bankak',
                  groupValue: _selectedPaymentMethod,
                  onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
                  icon: Icons.account_balance_wallet,
                ),
                _PaymentRadio(
                  title: isRtl ? 'تحويل بنكي مباشر' : 'Direct Bank Wire Transfer',
                  value: 'bank',
                  groupValue: _selectedPaymentMethod,
                  onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
                  icon: Icons.account_balance,
                ),
                _PaymentRadio(
                  title: isRtl ? 'آجل للشركات (B2B Credit)' : 'B2B Corporate Credit',
                  value: 'b2b',
                  groupValue: _selectedPaymentMethod,
                  onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
                  icon: Icons.business,
                ),
              ],
            ),
            isActive: _currentStep >= 2,
          ),
        ],
      ),
    );
  }

  void _showChangeAddressDialog(BuildContext context, bool isRtl) {
    final arController = TextEditingController(text: _deliveryAddress);
    final enController = TextEditingController(text: _deliveryAddressEn);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isRtl ? 'تحديث عنوان التوصيل' : 'Update Delivery Address'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: arController,
              decoration: const InputDecoration(labelText: 'العنوان (عربي)'),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: enController,
              decoration: const InputDecoration(labelText: 'Address (English)'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              setState(() {
                _deliveryAddress = arController.text;
                _deliveryAddressEn = enController.text;
              });
              Navigator.pop(context);
            },
            child: Text(isRtl ? 'تحديث' : 'Update'),
          ),
        ],
      ),
    );
  }

  void _showSuccessDialog(bool isRtl) {
    final parentContext = context;
    showDialog(
      context: parentContext,
      barrierDismissible: false,
      builder: (dialogContext) => AlertDialog(
        title: const Icon(Icons.check_circle, color: Colors.green, size: 48),
        content: Text(
          isRtl
              ? 'تم استلام وتأكيد طلبك بنجاح!\nسيتم التواصل معك لترتيب الشحن والتسليم.'
              : 'Your order has been placed successfully!\nYou will be contacted for delivery arrangements.',
          textAlign: TextAlign.center,
        ),
        actions: [
          Center(
            child: ElevatedButton(
              onPressed: () {
                ref.read(cartNotifierProvider.notifier).clearCart();
                Navigator.of(dialogContext).pop();
                GoRouter.of(parentContext).go('/buyer/home');
              },
              child: Text(isRtl ? 'العودة للرئيسية' : 'Back to Home'),
            ),
          ),
        ],
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final double value;
  final bool isTotal;
  final String currency;

  const _SummaryRow(this.label, this.value, {this.isTotal = false, this.currency = 'SDG'});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontWeight: isTotal ? FontWeight.bold : FontWeight.normal,
              fontSize: isTotal ? 16 : 14,
            ),
          ),
          Text(
            '${value.toStringAsFixed(2)} $currency',
            style: TextStyle(
              fontWeight: isTotal ? FontWeight.bold : FontWeight.normal,
              fontSize: isTotal ? 16 : 14,
              color: isTotal ? Colors.blue : null,
            ),
          ),
        ],
      ),
    );
  }
}

class _PaymentRadio extends StatelessWidget {
  final String title;
  final String value;
  final String groupValue;
  final ValueChanged<String?> onChanged;
  final IconData icon;

  const _PaymentRadio({
    required this.title,
    required this.value,
    required this.groupValue,
    required this.onChanged,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return RadioListTile<String>(
      title: Row(
        children: [
          Icon(icon, size: 24, color: Theme.of(context).primaryColor),
          const SizedBox(width: 12),
          Text(title),
        ],
      ),
      value: value,
      groupValue: groupValue,
      onChanged: onChanged,
      contentPadding: EdgeInsets.zero,
    );
  }
}
