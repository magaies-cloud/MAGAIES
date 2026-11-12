import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../cart/application/cart_provider.dart';
import '../../../cart/domain/models/cart_item_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/buyer/checkout/application/checkout_merchants_provider.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'dart:typed_data';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/core/utils/image_picker_helper.dart';
import 'package:magaiees_marketplace/features/buyer/orders/presentation/screens/buyer_orders_screen.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:magaiees_marketplace/features/employee/application/employee_orders_provider.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';
import 'package:magaiees_marketplace/routing/app_router.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> with SingleTickerProviderStateMixin {
  int _currentStep = 0;
  String _selectedPaymentMethod = 'ocash';
  String _deliveryOption = 'merchant_fee'; // 'merchant_fee' or 'self_pickup'
  final _upfrontPaymentController = TextEditingController();
  final _receiptRefController = TextEditingController();
  final _receiptAmountController = TextEditingController();
  Uint8List? _receiptBytes;
  String? _receiptExt;
  bool _isSubmittingOrder = false;
  bool _orderPlacedSuccessfully = false;
  String _deliveryAddress = '';
  String _deliveryAddressEn = '';
  String _contactPhone = '';
  bool _addressError = false;
  late AnimationController _shakeController;

  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(vsync: this, duration: const Duration(milliseconds: 500));
    _loadAddress();
  }

  Future<void> _loadAddress() async {
    final prefs = await SharedPreferences.getInstance();
    final user = Supabase.instance.client.auth.currentUser;
    setState(() {
      _deliveryAddress = prefs.getString('buyer_delivery_address') ?? '';
      _deliveryAddressEn = prefs.getString('buyer_delivery_address_en') ?? '';
      _contactPhone = prefs.getString('buyer_contact_phone') ?? user?.phone ?? '';
    });
  }

  void _shakeAddress() {
    setState(() => _addressError = true);
    _shakeController.forward(from: 0.0);
  }

  @override
  void dispose() {
    _shakeController.dispose();
    _upfrontPaymentController.dispose();
    _receiptRefController.dispose();
    _receiptAmountController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final cart = ref.watch(cartNotifierProvider);

    // If order was placed successfully, keep screen alive for dialog without executing postFrameCallback pop
    if (_orderPlacedSuccessfully) {
      return Scaffold(
        appBar: AppBar(title: Text(isRtl ? 'تم إرسال الطلب' : 'Order Submitted')),
        body: const SizedBox.shrink(),
      );
    }

    // If cart was cleared and not actively submitting, pop back to empty cart immediately
    if (cart.items.isEmpty && !_isSubmittingOrder) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted && !_orderPlacedSuccessfully) {
          if (Navigator.of(context).canPop()) {
            Navigator.of(context).pop();
          } else {
            context.go(RouteNames.buyerCart);
          }
        }
      });
      return Scaffold(
        appBar: AppBar(title: Text(isRtl ? 'إتمام الطلب والدفع' : 'Checkout')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final subtotal = ref.watch(cartSubtotalProvider);
    final vat = ref.watch(cartVatTotalProvider);
    final grandTotal = ref.watch(cartGrandTotalProvider);
    final merchantsAsync = ref.watch(checkoutMerchantsProvider);
    final buyerB2bAsync = ref.watch(buyerB2bEligibilityProvider);
    final currency = isRtl ? 'ج.س' : 'SDG';

    final settings = ref.watch(globalSettingsProvider);
    final merchants = merchantsAsync.valueOrNull ?? [];
    final directMerchants = merchants.where((m) => m['direct_payment_enabled'] == true).toList();
    final isDirectPaymentActive = directMerchants.isNotEmpty;
    final isPartialPaymentAllowed = isDirectPaymentActive
        ? (settings.merchantAllowPartialCod && directMerchants.every((m) => m['accept_partial_cod'] != false))
        : settings.exclusivePartialCod;

    final buyerB2b = buyerB2bAsync.valueOrNull ?? {'isEligible': false, 'scope': 'none'};
    final bool isBuyerB2bEnabled = buyerB2b['isEligible'] == true;
    final String b2bScope = buyerB2b['scope']?.toString() ?? 'all';
    final List b2bAllowedMerchants = (buyerB2b['allowedMerchants'] is List) ? buyerB2b['allowedMerchants'] as List : [];
    final double b2bCreditLimit = (buyerB2b['creditLimit'] is num) ? (buyerB2b['creditLimit'] as num).toDouble() : 0.0;

    final currentUserId = Supabase.instance.client.auth.currentUser?.id;
    final currentUserPhone = Supabase.instance.client.auth.currentUser?.phone;
    final bool isApprovedByMerchant = directMerchants.any((m) {
      final buyers = m['b2b_approved_buyers'];
      if (buyers is List) {
        if (currentUserId != null && buyers.contains(currentUserId)) return true;
        if (currentUserPhone != null && buyers.contains(currentUserPhone)) return true;
      }
      return false;
    });

    final bool isAllowedForMerchant = (isBuyerB2bEnabled &&
        (b2bScope == 'all' ||
            b2bScope == 'merchants_only' ||
            b2bAllowedMerchants.contains('all') ||
            directMerchants.any((m) => b2bAllowedMerchants.contains(m['store_id']) || b2bAllowedMerchants.contains(m['id'])))) ||
        isApprovedByMerchant;

    final bool isAllowedForMagaiees = isBuyerB2bEnabled &&
        (b2bScope == 'all' ||
            b2bScope == 'exclusive_only' ||
            b2bAllowedMerchants.contains('magaiees') ||
            b2bAllowedMerchants.contains('all') ||
            b2bAllowedMerchants.isEmpty);

    final bool isCurrentB2bValid = isDirectPaymentActive
        ? (settings.merchantAllowB2b && directMerchants.every((m) => m['accept_b2b'] != false) && isAllowedForMerchant)
        : (settings.exclusiveB2b && isAllowedForMagaiees);

    if (!isPartialPaymentAllowed && _selectedPaymentMethod == 'partial_cod') {
      _selectedPaymentMethod = isDirectPaymentActive
          ? (settings.merchantAllowBankak ? 'bankak' : 'bank')
          : (settings.exclusiveBankak ? 'bankak' : 'bank');
    }

    if (_selectedPaymentMethod == 'b2b' && !isCurrentB2bValid) {
      _selectedPaymentMethod = isDirectPaymentActive
          ? (settings.merchantAllowBankak ? 'bankak' : 'bank')
          : (settings.exclusiveBankak ? 'bankak' : 'bank');
    }
    return Scaffold(
      appBar: AppBar(title: Text(isRtl ? 'إتمام الطلب والدفع' : 'Checkout')),
      body: Stepper(
        type: StepperType.vertical,
        currentStep: _currentStep,
        onStepContinue: () async {
          if (_currentStep == 0) {
            final addrError = DataValidator.validateAddress(_deliveryAddress, isRtl: isRtl);
            if (addrError != null) {
              _shakeAddress();
              AppNotification.showError(context, addrError);
              return;
            }
            if (_contactPhone.isNotEmpty) {
              final phoneError = DataValidator.validatePhone(_contactPhone, isRtl: isRtl);
              if (phoneError != null) {
                _shakeAddress();
                AppNotification.showError(context, phoneError);
                return;
              }
            }
          }
          if (_currentStep < 2) {
            setState(() {
              _currentStep += 1;
              if (_currentStep == 2 && _receiptAmountController.text.trim().isEmpty) {
                final total = ref.read(cartGrandTotalProvider);
                if (total > 0) {
                  _receiptAmountController.text = total.toStringAsFixed(0);
                }
              }
            });
            return;
          }

          // ── Final Step (_currentStep == 2): Validations & Submission ──
          // 1. Validate B2B Corporate Credit eligibility
          if (_selectedPaymentMethod == 'b2b') {
            final buyerB2b = ref.read(buyerB2bEligibilityProvider).valueOrNull ?? {'isEligible': false};
            final isBuyerB2bEnabled = buyerB2b['isEligible'] == true;
            final b2bScope = buyerB2b['scope']?.toString() ?? 'all';
            final b2bAllowedMerchants = (buyerB2b['allowedMerchants'] is List) ? buyerB2b['allowedMerchants'] as List : [];
            final b2bCreditLimit = (buyerB2b['creditLimit'] is num) ? (buyerB2b['creditLimit'] as num).toDouble() : 0.0;

            final merchants = merchantsAsync.valueOrNull ?? [];
            final directMerchants = merchants.where((m) => m['direct_payment_enabled'] == true).toList();
            final isDirectPaymentActive = directMerchants.isNotEmpty;

            final currentUserId = Supabase.instance.client.auth.currentUser?.id;
            final currentUserPhone = Supabase.instance.client.auth.currentUser?.phone;
            final bool isApprovedByMerchant = directMerchants.any((m) {
              final buyers = m['b2b_approved_buyers'];
              if (buyers is List) {
                if (currentUserId != null && buyers.contains(currentUserId)) return true;
                if (currentUserPhone != null && buyers.contains(currentUserPhone)) return true;
              }
              return false;
            });

            final bool isAllowedForMerchant = (isBuyerB2bEnabled &&
                (b2bScope == 'all' ||
                    b2bScope == 'merchants_only' ||
                    b2bAllowedMerchants.contains('all') ||
                    directMerchants.any((m) => b2bAllowedMerchants.contains(m['store_id']) || b2bAllowedMerchants.contains(m['id'])))) ||
                isApprovedByMerchant;

            final bool isAllowedForMagaiees = isBuyerB2bEnabled &&
                (b2bScope == 'all' ||
                    b2bScope == 'exclusive_only' ||
                    b2bAllowedMerchants.contains('magaiees') ||
                    b2bAllowedMerchants.contains('all') ||
                    b2bAllowedMerchants.isEmpty);

            final bool isCurrentB2bValid = isDirectPaymentActive
                ? (settings.merchantAllowB2b && directMerchants.every((m) => m['accept_b2b'] != false) && isAllowedForMerchant)
                : (settings.exclusiveB2b && isAllowedForMagaiees);

            if (!isCurrentB2bValid) {
              AppNotification.showError(
                context,
                isRtl
                    ? 'عذراً، حسابك غير مصرّح له بالدفع الآجل للشركات (B2B Credit). يرجى اختيار وسيلة دفع أخرى.'
                    : 'Your account is not authorized for B2B Corporate Credit. Please select another payment method.',
              );
              return;
            }

            if (b2bCreditLimit > 0 && grandTotal > b2bCreditLimit) {
              AppNotification.showError(
                context,
                isRtl
                    ? 'عذراً، إجمالي الطلب (${grandTotal.toStringAsFixed(0)} $currency) يتجاوز سقف التسهيلات الائتمانية المحددة لحسابك (${b2bCreditLimit.toStringAsFixed(0)} $currency).'
                    : 'Order exceeds your assigned B2B credit limit (${b2bCreditLimit.toStringAsFixed(0)} $currency).',
              );
              return;
            }
          }

          // 2. Validate COD limit based on order type (Merchant vs Exclusive)
          final merchants = merchantsAsync.valueOrNull ?? [];
          final maxCod = merchants.isNotEmpty ? settings.merchantMaxCod : settings.exclusiveMaxCod;
          if (_selectedPaymentMethod == 'cod' && maxCod > 0 && grandTotal > maxCod) {
            AppNotification.showError(
              context,
              isRtl
                  ? 'عذراً، إجمالي الطلب يتجاوز الحد الأقصى المسموح به للدفع نقداً عند الاستلام (${maxCod.toStringAsFixed(0)} $currency). يرجى اختيار وسيلة دفع بنكية.'
                  : 'Order exceeds maximum Cash on Delivery limit. Please select a bank payment method.',
            );
            return;
          }

          // 3. Validate Fawry/electronic limit
          if (_selectedPaymentMethod == 'fawry' && settings.exclusiveMaxFawry > 0 && grandTotal > settings.exclusiveMaxFawry) {
            AppNotification.showError(
              context,
              isRtl
                  ? 'عذراً، إجمالي الطلب يتجاوز الحد الأقصى المسموح به للدفع الإلكتروني (${settings.exclusiveMaxFawry.toStringAsFixed(0)} $currency).'
                  : 'Order exceeds maximum electronic payment limit.',
            );
            return;
          }

          // 4. Validate partial payment (Deposit + COD)
          if (_selectedPaymentMethod == 'partial_cod') {
            if (!isPartialPaymentAllowed) {
              AppNotification.showError(
                context,
                isRtl
                    ? 'عذراً، خيار دفع جزء من المبلغ نقداً والباقي عند الاستلام غير متاح لهذا الطلب'
                    : 'Partial payment is not available for this order',
              );
              return;
            }
            final upfront = double.tryParse(_upfrontPaymentController.text.trim()) ?? 0.0;
            if (upfront <= 0) {
              AppNotification.showError(
                context,
                isRtl ? 'يرجى إدخال مبلغ الدفعة المقدمة (أكبر من صفر)' : 'Please enter the upfront payment amount (> 0)',
              );
              return;
            }
            if (upfront > grandTotal) {
              AppNotification.showError(
                context,
                isRtl ? 'مبلغ الدفعة المقدمة لا يمكن أن يتجاوز إجمالي الطلب' : 'Upfront amount cannot exceed order total',
              );
              return;
            }
          }

          // Place order in Supabase
          final user = ref.read(authNotifierProvider).valueOrNull;
          final cart = ref.read(cartNotifierProvider);
          if (user == null || user.role.isGuest) {
            AppNotification.showError(
              context,
              isRtl ? 'يرجى تسجيل الدخول لإتمام الطلب' : 'Please sign in to place your order',
            );
            context.push('/login');
            return;
          }
          
          if (cart.items.isNotEmpty) {
            final bool isSelfPickup = _deliveryOption == 'self_pickup';
            int createdOrdersCount = 0;
            final List<String> createdStoreNames = [];
            String? uploadedReceiptUrl;
            double? enteredReceiptAmount;

            // Group cart items by merchantId (exclusive items go strictly under 'magaiees')
            final Map<String, List<CartItemModel>> storeItems = {};
            for (final item in cart.items) {
              final merchantId = item.product.isMagaieesExclusive ? 'magaiees' : item.product.merchantId;
              storeItems.putIfAbsent(merchantId, () => []).add(item);
            }
            final totalStoresCount = storeItems.length;

            try {
              setState(() => _isSubmittingOrder = true);

              final bool isMultiStoreOrder = storeItems.length > 1;
              // Generate a group ID to link related orders from the same checkout
              final String groupId = isMultiStoreOrder
                  ? 'GRP-${user.id.length >= 8 ? user.id.substring(0, 8) : user.id}-${DateTime.now().millisecondsSinceEpoch}'
                  : '';

              // If payment is COD (Cash on Delivery): ensure no receipt data is attached at checkout
              final bool isPrepaid = _selectedPaymentMethod != 'cod';
              if (!isPrepaid) {
                _receiptAmountController.clear();
                _receiptRefController.clear();
                _receiptBytes = null;
                _receiptExt = null;
              }

              // If a receipt screenshot was picked, upload it to Supabase Storage (receipts bucket first)
              uploadedReceiptUrl = null;
              if (_receiptBytes != null && _receiptExt != null) {
                final fName = 'receipt_${user.id}_${DateTime.now().millisecondsSinceEpoch}.$_receiptExt';
                try {
                  await Supabase.instance.client.storage
                      .from('receipts')
                      .uploadBinary(
                        fName,
                        _receiptBytes!,
                        fileOptions: FileOptions(contentType: 'image/$_receiptExt'),
                      )
                      .timeout(const Duration(seconds: 30));
                  uploadedReceiptUrl = Supabase.instance.client.storage
                      .from('receipts')
                      .getPublicUrl(fName);
                } catch (uploadErr) {
                  debugPrint('Error uploading receipt to receipts: $uploadErr');
                  try {
                    await Supabase.instance.client.storage
                        .from('products_images')
                        .uploadBinary(
                          fName,
                          _receiptBytes!,
                          fileOptions: FileOptions(contentType: 'image/$_receiptExt'),
                        )
                        .timeout(const Duration(seconds: 30));
                    uploadedReceiptUrl = Supabase.instance.client.storage
                        .from('products_images')
                        .getPublicUrl(fName);
                  } catch (e2) {
                    debugPrint('Error uploading receipt to fallback products_images: $e2');
                  }
                }
              }

              enteredReceiptAmount = double.tryParse(_receiptAmountController.text.trim());

              // Create an order for each store
              for (var entry in storeItems.entries) {
                final storeId = entry.key;
                final items = entry.value;
                
                // Calculate total for this store
                double storeTotal = 0.0;
                for (var item in items) {
                  storeTotal += item.product.pricePerTon * item.quantity;
                }
                
                if (settings.isTaxAndCommissionEnabled) {
                  storeTotal += storeTotal * (settings.vatPercent / 100.0);
                }

                // Apply coupon discount proportionally
                if (cart.discountAmount > 0) {
                  // Calculate this store's share of the discount
                  final allStoreItems = storeItems.values.expand((items) => items).toList();
                  double grandSubtotal = 0;
                  for (var item in allStoreItems) {
                    grandSubtotal += item.product.pricePerTon * item.quantity;
                  }
                  if (settings.isTaxAndCommissionEnabled) {
                    grandSubtotal += grandSubtotal * (settings.vatPercent / 100.0);
                  }
                  if (grandSubtotal > 0) {
                    final discountShare = cart.discountAmount * (storeTotal / grandSubtotal);
                    storeTotal = (storeTotal - discountShare).clamp(0.0, double.infinity);
                  }
                }
                
                double upfrontPayment = 0.0;
                double deliveryPayment = 0.0;
                if (_selectedPaymentMethod == 'partial_cod') {
                  final enteredUpfront = double.tryParse(_upfrontPaymentController.text.trim()) ?? 0.0;
                  final ratio = grandTotal > 0 ? (storeTotal / grandTotal) : 1.0;
                  upfrontPayment = (enteredUpfront * ratio).clamp(0.0, storeTotal);
                  deliveryPayment = (storeTotal - upfrontPayment).clamp(0.0, double.infinity);
                } else if (_selectedPaymentMethod == 'cod' || _selectedPaymentMethod == 'b2b') {
                  upfrontPayment = 0.0;
                  deliveryPayment = storeTotal;
                } else {
                  // bankak, ocash, bank_transfer, wire - full payment methods (not a deposit)
                  upfrontPayment = 0.0;
                  deliveryPayment = 0.0;
                }
                  
                // Check if this is a Magaiees Exclusive order
                final bool isExclusiveOrder = storeId == 'magaiees' || items.any((it) => it.product.isMagaieesExclusive);
                final isUuid = RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false).hasMatch(storeId);
                String? resolvedStoreId;
                String? storeOwnerId;

                if (!isExclusiveOrder) {
                  if (isUuid) {
                    try {
                      final sRes = await Supabase.instance.client
                          .from('stores')
                          .select('id, owner_id')
                          .eq('id', storeId)
                          .maybeSingle()
                          .timeout(const Duration(seconds: 4));
                      if (sRes != null) {
                        resolvedStoreId = sRes['id']?.toString();
                        storeOwnerId = sRes['owner_id']?.toString();
                      } else {
                        final ownerRes = await Supabase.instance.client
                            .from('stores')
                            .select('id, owner_id')
                            .eq('owner_id', storeId)
                            .maybeSingle()
                            .timeout(const Duration(seconds: 4));
                        if (ownerRes != null) {
                          resolvedStoreId = ownerRes['id']?.toString();
                          storeOwnerId = ownerRes['owner_id']?.toString() ?? storeId;
                        }
                      }
                    } catch (_) {
                      resolvedStoreId = storeId;
                    }
                  }

                  // Extra safety: If storeId is not resolved yet, check items for product's store_id
                  if (resolvedStoreId == null && items.isNotEmpty) {
                    for (var it in items) {
                      final pId = it.product.id;
                      if (RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false).hasMatch(pId)) {
                        try {
                          final pRes = await Supabase.instance.client
                              .from('products')
                              .select('store_id')
                              .eq('id', pId)
                              .maybeSingle()
                              .timeout(const Duration(seconds: 4));
                          final pStore = pRes?['store_id']?.toString();
                          if (pStore != null && pStore.isNotEmpty && pStore != 'null') {
                            resolvedStoreId = pStore;
                            break;
                          }
                        } catch (_) {}
                      }
                    }
                  }

                  if (storeOwnerId == null && resolvedStoreId != null) {
                    try {
                      final sOwner = await Supabase.instance.client
                          .from('stores')
                          .select('owner_id')
                          .eq('id', resolvedStoreId)
                          .maybeSingle()
                          .timeout(const Duration(seconds: 4));
                      storeOwnerId = sOwner?['owner_id']?.toString();
                    } catch (_) {}
                  }
                }

                final shippingAddress = isSelfPickup
                    ? (_contactPhone.isNotEmpty
                        ? '$_deliveryAddress | هاتف: $_contactPhone [استلام وترحيل ذاتي]'
                        : '$_deliveryAddress [استلام وترحيل ذاتي]')
                    : (_contactPhone.isNotEmpty
                        ? '$_deliveryAddress | هاتف: $_contactPhone'
                        : _deliveryAddress);

                String orderNotes = isSelfPickup
                    ? 'استلام وترحيل ذاتي بواسطة المشتري (بدون رسوم نقل وتنزيل)'
                    : 'ترحيل وتنزيل بواسطة التاجر';

                // Track store name for confirmation dialog and order notes
                final storeNameForTracking = isExclusiveOrder
                    ? 'حصري على السيطرة'
                    : (items.isNotEmpty
                        ? (items.first.product.storeNameAr.isNotEmpty
                            ? items.first.product.storeNameAr
                            : items.first.product.storeName)
                        : 'متجر');

                // Embed store name in notes for instant retrieval without queries
                orderNotes += '\n[متجر: $storeNameForTracking]';
                if (isExclusiveOrder) {
                  orderNotes += '\n[طلب حصري السيطرة]';
                }

                // Add multi-store coordination info
                if (isMultiStoreOrder) {
                  orderNotes += '\n[طلب مجمّع: $groupId | عدد المتاجر: ${storeItems.length}]';
                  if (!isSelfPickup) {
                    orderNotes += '\n⚠️ يوجد طلبات أخرى من نفس المشتري - يرجى التنسيق لتوحيد الشحن إن أمكن';
                  }
                }

                final refNum = _receiptRefController.text.trim();
                // Receipt info applies ONLY if an actual receipt screenshot was uploaded or ref entered
                final bool hasReceiptInfo = (uploadedReceiptUrl != null || refNum.isNotEmpty);

                // Calculate paid amount for this store order ONLY when actual transfer proof is provided
                double paidAmountForOrder = 0.0;
                if (hasReceiptInfo) {
                  if (enteredReceiptAmount != null && enteredReceiptAmount > 0) {
                    final ratio = grandTotal > 0 ? (storeTotal / grandTotal) : 1.0;
                    paidAmountForOrder = (enteredReceiptAmount * ratio).clamp(0.0, storeTotal);
                  } else if (_selectedPaymentMethod == 'partial_cod') {
                    paidAmountForOrder = upfrontPayment;
                  } else {
                    paidAmountForOrder = storeTotal;
                  }

                  final recordedAmt = paidAmountForOrder > 0 ? paidAmountForOrder : storeTotal;
                  final receiptPrefix = uploadedReceiptUrl != null ? 'receipt_url:$uploadedReceiptUrl | ' : '';
                  orderNotes += '\n${receiptPrefix}ref:$refNum | bank:$_selectedPaymentMethod | amount:$recordedAmt';
                }

                final String paymentStatus;
                if (_selectedPaymentMethod == 'cod' || _selectedPaymentMethod == 'b2b') {
                  paymentStatus = 'unpaid';
                } else if (hasReceiptInfo) {
                  paymentStatus = 'under_review';
                } else {
                  paymentStatus = 'unpaid';
                }

                // Insert into public.orders with timeout
                final List<dynamic> orderRes = await Supabase.instance.client
                    .from('orders')
                    .insert({
                      'buyer_id': user.id,
                      'store_id': isExclusiveOrder ? null : (resolvedStoreId ?? (isUuid ? storeId : null)),
                      'total_amount': storeTotal.round(),
                      'status': 'pending',
                      'payment_status': paymentStatus,
                      'payment_method': _selectedPaymentMethod,
                      'shipping_address': shippingAddress,
                      'upfront_payment': upfrontPayment.round(),
                      'delivery_payment': deliveryPayment.round(),
                      'paid_amount': paidAmountForOrder.round(),
                      'transport_fee': isSelfPickup ? 0 : null,
                      'offloading_fee': isSelfPickup ? 0 : null,
                      'buyer_approved_fees': isSelfPickup ? true : null,
                      'receipt_url': uploadedReceiptUrl,
                      'notes': orderNotes,
                    })
                    .select('id')
                    .timeout(const Duration(seconds: 45));
                
                if (orderRes.isNotEmpty) {
                  final orderId = orderRes.first['id'];
                  
                  // Batch insert order_items in a single query with timeout
                  final List<Map<String, dynamic>> orderItemsToInsert = [];
                  for (var item in items) {
                    final isProdUuid = RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false).hasMatch(item.product.id);
                    final num qty = item.quantity > 0
                        ? (item.quantity % 1 == 0 ? item.quantity.toInt() : item.quantity)
                        : 1;
                    orderItemsToInsert.add({
                      'order_id': orderId,
                      'product_id': isProdUuid ? item.product.id : null,
                      'product_name': item.product.nameAr.isNotEmpty ? item.product.nameAr : item.product.name,
                      'quantity': qty,
                      'price_sdg': item.product.pricePerTon.round(),
                      'price_usd': (item.product.priceInUsd ?? 0.0).round(),
                    });
                  }
                  
                  if (orderItemsToInsert.isNotEmpty) {
                    await Supabase.instance.client
                        .from('order_items')
                        .insert(orderItemsToInsert)
                        .timeout(const Duration(seconds: 45));
                  }
                  createdOrdersCount++;
                  createdStoreNames.add(storeNameForTracking);

                  // Send instant notification to store owner in Supabase
                  if (storeOwnerId != null && storeOwnerId.isNotEmpty) {
                    try {
                      final buyerName = user.name.isNotEmpty ? user.name : 'أحد المشترين';
                      await Supabase.instance.client.from('notifications').insert({
                        'user_id': storeOwnerId,
                        'title': '🔔 طلب جديد وارد للمتجر!',
                        'title_ar': '🔔 طلب جديد وارد للمتجر!',
                        'body': 'قام المشتري ($buyerName) بإتمام طلبية جديدة بقيمة ${storeTotal.round()} ج.س',
                        'body_ar': 'قام المشتري ($buyerName) بإتمام طلبية جديدة بقيمة ${storeTotal.round()} ج.س',
                        'type': 'new_order',
                        'related_id': orderId.toString(),
                        'is_read': false,
                      }).timeout(const Duration(seconds: 8));
                    } catch (notifErr) {
                      debugPrint('Error inserting merchant notification: $notifErr');
                    }
                  }
                }
              }
              
              _orderPlacedSuccessfully = true;

              // Clear cart immediately upon successful placement
              ref.read(cartNotifierProvider.notifier).clearCart();

              // Invalidate buyer, merchant & employee orders providers
              ref.invalidate(buyerOrdersProvider);
              try {
                ref.invalidate(merchantOrdersProvider);
              } catch (_) {}
              try {
                ref.invalidate(employeeOrdersProvider);
              } catch (_) {}

              // Play success notification sound
              NotificationSoundService.play();

              if (mounted) {
                _showOrderUnderReviewDialog(
                  isRtl,
                  isSelfPickup,
                  receiptUploaded: uploadedReceiptUrl != null || (enteredReceiptAmount != null && enteredReceiptAmount > 0),
                  ordersCount: createdOrdersCount,
                  storeNames: createdStoreNames,
                );
              }
            } catch (e) {
              if (mounted) {
                if (createdOrdersCount > 0) {
                  _orderPlacedSuccessfully = true;
                  // ملاحظة: مسح السلة بالكامل هنا مقبول مؤقتاً لتجنب تكرار الطلبات الناجحة.
                  // الحل الأمثل مستقبلاً هو حذف عناصر المتاجر الناجحة فقط من السلة.
                  if (createdOrdersCount >= totalStoresCount) {
                    ref.read(cartNotifierProvider.notifier).clearCart();
                  }
                  ref.invalidate(buyerOrdersProvider);
                  try {
                    ref.invalidate(merchantOrdersProvider);
                  } catch (_) {}
                  NotificationSoundService.play();

                  _showOrderUnderReviewDialog(
                    isRtl,
                    isSelfPickup,
                    receiptUploaded: uploadedReceiptUrl != null || (enteredReceiptAmount != null && enteredReceiptAmount > 0),
                    ordersCount: createdOrdersCount,
                    storeNames: createdStoreNames,
                  );
                  if (createdOrdersCount < totalStoresCount) {
                    AppNotification.showInfo(
                      context,
                      isRtl
                          ? 'تم إرسال طلبات $createdOrdersCount من أصل $totalStoresCount متاجر بنجاح. تعذر إرسال باقي الطلبات، يرجى التحقق من شاشة الطلبات.'
                          : 'Orders submitted for $createdOrdersCount of $totalStoresCount stores successfully. Some orders could not be sent, please check your orders screen.',
                      icon: Icons.info_outline,
                    );
                  } else {
                    AppNotification.showInfo(
                      context,
                      isRtl
                          ? 'تم إنشاء الطلب بنجاح ولكن استغرق الاتصال وقتاً لإنهاء بعض العمليات.'
                          : 'Order created, but network connection was slow while finalizing.',
                      icon: Icons.check_circle_outline,
                    );
                  }
                } else {
                  final isTimeout = e.toString().contains('TimeoutException');
                  AppNotification.showError(
                    context,
                    isTimeout
                        ? (isRtl
                            ? 'استغرقت العملية وقتاً أطول من المتوقع بسبب بطء الاتصال بالإنترنت. يرجى التحقق من شاشة الطلبات قبل إعادة المحاولة.'
                            : 'Request timed out due to slow connection. Please check your Orders screen before retrying.')
                        : (isRtl ? 'حدث خطأ: $e' : 'Error: $e'),
                  );
                }
              }
            } finally {
              if (mounted) {
                setState(() => _isSubmittingOrder = false);
              }
            }
            } else if (cart.items.isEmpty && mounted) {
              AppNotification.showInfo(
                context,
                isRtl ? 'السلة فارغة' : 'Cart is empty',
              );
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
            padding: const EdgeInsets.only(top: 20.0, bottom: 8.0),
            child: Row(
              children: [
                Expanded(
                  flex: 3,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isLastStep ? const Color(0xFF047857) : const Color(0xFF1E3A8A),
                      foregroundColor: Colors.white,
                      disabledBackgroundColor: isLastStep
                          ? const Color(0xFF047857).withValues(alpha: 0.85)
                          : const Color(0xFF1E3A8A).withValues(alpha: 0.85),
                      disabledForegroundColor: Colors.white,
                      minimumSize: const Size(double.infinity, 50),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 3,
                    ),
                    onPressed: _isSubmittingOrder ? null : details.onStepContinue,
                    child: _isSubmittingOrder
                        ? Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.5,
                                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Text(
                                isRtl ? 'جاري إرسال وتأكيد الطلب...' : 'Submitting Order...',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14.5,
                                ),
                              ),
                            ],
                          )
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                isLastStep
                                    ? Icons.check_circle_outline_rounded
                                    : (isRtl ? Icons.arrow_back_rounded : Icons.arrow_forward_rounded),
                                size: 20,
                                color: Colors.white,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                isLastStep
                                    ? (isRtl ? 'تأكيد وإرسال الطلب' : 'Place Order')
                                    : (isRtl ? 'متابعة الخطوة التالية' : 'Continue'),
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 15,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
                if (_currentStep > 0) ...[
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF334155),
                        side: const BorderSide(color: Color(0xFFCBD5E1), width: 1.5),
                        minimumSize: const Size(0, 50),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: _isSubmittingOrder ? null : details.onStepCancel,
                      icon: Icon(isRtl ? Icons.arrow_forward_rounded : Icons.arrow_back_rounded, size: 18),
                      label: Text(
                        isRtl ? 'رجوع' : 'Back',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          );
        },
        steps: [
          Step(
            title: Text(isRtl ? 'عنوان التواصل' : 'Contact Address'),
            content: AnimatedBuilder(
              animation: _shakeController,
              builder: (context, child) {
                final shakeOffset = _shakeController.isAnimating
                    ? (_shakeController.value * 10 % 2 > 1 ? 5.0 : -5.0) * (1 - _shakeController.value)
                    : 0.0;
                    
                return Transform.translate(
                  offset: Offset(shakeOffset, 0),
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      border: Border.all(color: _addressError ? Colors.red : Colors.grey.shade300, width: _addressError ? 2 : 1),
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
                              isRtl ? 'العنوان المحدد للتوصيل' : 'Delivery Address',
                              style: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _deliveryAddress.isEmpty 
                              ? (isRtl ? 'لم يتم تحديد عنوان بعد' : 'No address selected')
                              : (isRtl ? _deliveryAddress : (_deliveryAddressEn.isNotEmpty ? _deliveryAddressEn : _deliveryAddress)),
                          style: TextStyle(
                            color: _deliveryAddress.isEmpty ? Colors.red : Theme.of(context).colorScheme.onSurface,
                          ),
                        ),
                        if (_contactPhone.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              const Icon(Icons.phone_android_rounded, size: 18, color: Colors.blueGrey),
                              const SizedBox(width: 8),
                              Text(
                                '${isRtl ? "هاتف التواصل:" : "Contact Phone:"} $_contactPhone',
                                style: const TextStyle(fontSize: 13, color: Colors.black87, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: 8),
                        TextButton.icon(
                          onPressed: () {
                            setState(() => _addressError = false);
                            _showChangeAddressDialog(context, isRtl);
                          },
                          icon: const Icon(Icons.edit, size: 16),
                          label: Text(isRtl ? 'تعديل عنوان وبيانات التواصل' : 'Edit Contact & Delivery Info'),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
            isActive: _currentStep >= 0,
            state: _currentStep > 0 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: Text(isRtl ? 'ملخص الطلب' : 'Order Summary'),
            content: Column(
              children: [
                // Multi-store notice in checkout
                if (cart.itemsByMerchant.length > 1)
                  Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFF93C5FD)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.layers_rounded, color: Color(0xFF2563EB), size: 18),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            isRtl
                                ? 'سيتم إنشاء ${cart.itemsByMerchant.length} طلبات منفصلة — طلب لكل متجر'
                                : '${cart.itemsByMerchant.length} separate orders will be created — one per store',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF1E40AF), fontWeight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ),
                  ),
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
                
                // ── Transport & Offloading Options Card ──
                Container(
                  margin: const EdgeInsets.symmetric(vertical: 10),
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
                          Icon(Icons.local_shipping_outlined, color: Colors.blue.shade800, size: 20),
                          const SizedBox(width: 8),
                          Text(
                            isRtl ? 'خيارات الترحيل والتنزيل:' : 'Transport & Offloading Options:',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      // Option 1: Merchant determines fees later
                      InkWell(
                        onTap: () => setState(() => _deliveryOption = 'merchant_fee'),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          decoration: BoxDecoration(
                            color: _deliveryOption == 'merchant_fee' ? const Color(0xFFFFF7ED) : Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: _deliveryOption == 'merchant_fee' ? const Color(0xFFEA580C) : Colors.grey.shade300,
                              width: _deliveryOption == 'merchant_fee' ? 1.5 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              Radio<String>(
                                value: 'merchant_fee',
                                groupValue: _deliveryOption,
                                activeColor: const Color(0xFFEA580C),
                                onChanged: (val) => setState(() => _deliveryOption = val!),
                              ),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      isRtl ? 'ترحيل وتنزيل بواسطة التاجر' : 'Delivery & Offloading by Merchant',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12.5,
                                        color: _deliveryOption == 'merchant_fee' ? const Color(0xFF9A3412) : Colors.black87,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      isRtl
                                          ? 'يحدد التاجر رسوم الترحيل والتنزيل لاحقاً بعد استلام الطلب ومراجعته'
                                          : 'Merchant sets delivery fees upon reviewing order and delivery address',
                                      style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFFEDD5),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  isRtl ? 'يحددها التاجر لاحقاً' : 'TBD by Merchant',
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF9A3412), fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      // Option 2: Self pickup (0 fees)
                      InkWell(
                        onTap: () => setState(() => _deliveryOption = 'self_pickup'),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          decoration: BoxDecoration(
                            color: _deliveryOption == 'self_pickup' ? const Color(0xFFF0FDF4) : Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: _deliveryOption == 'self_pickup' ? const Color(0xFF16A34A) : Colors.grey.shade300,
                              width: _deliveryOption == 'self_pickup' ? 1.5 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              Radio<String>(
                                value: 'self_pickup',
                                groupValue: _deliveryOption,
                                activeColor: const Color(0xFF16A34A),
                                onChanged: (val) => setState(() => _deliveryOption = val!),
                              ),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      isRtl ? 'بدون رسوم نقل وتنزيل (استلام وترحيل ذاتي)' : 'No Fees (Self-Pickup by Buyer)',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12.5,
                                        color: _deliveryOption == 'self_pickup' ? const Color(0xFF166534) : Colors.black87,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      isRtl
                                          ? 'يقوم المشتري باستلام ونقل المواد بنفسه من مخزن التاجر بدون أي رسوم إضافية'
                                          : 'Buyer handles transport & offloading directly from merchant warehouse (0 fees)',
                                      style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFDCFCE7),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  isRtl ? '0.00 ج.س (مجاناً)' : '0.00 SDG (Free)',
                                  style: const TextStyle(fontSize: 11, color: Color(0xFF166534), fontWeight: FontWeight.bold),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(isRtl ? 'رسوم النقل والتنزيل:' : 'Transport & Offloading:', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    _deliveryOption == 'self_pickup'
                        ? Text(
                            isRtl ? '0.00 $currency (ترحيل ذاتي - بدون رسوم)' : '0.00 $currency (Free - Self Pickup)',
                            style: const TextStyle(color: Color(0xFF16A34A), fontSize: 12, fontWeight: FontWeight.bold),
                          )
                        : Text(
                            isRtl ? 'يحددها التاجر لاحقاً' : 'TBD by Merchant',
                            style: TextStyle(color: Colors.orange.shade700, fontSize: 12, fontWeight: FontWeight.bold),
                          ),
                  ],
                ),
                const SizedBox(height: 8),

                if (settings.isTaxAndCommissionEnabled)
                  _SummaryRow(isRtl ? 'ضريبة القيمة المضافة (${settings.vatPercent}%):' : 'VAT (${settings.vatPercent}%):', vat, currency: currency),
                  

                  if (cart.discountAmount > 0) ...[
                    _SummaryRow(isRtl ? 'الخصم (كوبون):' : 'Discount (Coupon):', -cart.discountAmount, currency: currency),
                  ],
                  const Divider(),
                  
                  if (cart.appliedCouponCode == null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            decoration: InputDecoration(
                              labelText: isRtl ? 'هل لديك كود خصم؟' : 'Have a promo code?',
                              border: const OutlineInputBorder(),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                            ),
                            onSubmitted: (code) async {
                              // Simulate coupon fetch logic (we would fetch from Supabase here)
                              // For now, if code is not empty, give a fake discount of 5% of subtotal to test
                              if (code.trim().isNotEmpty) {
                                final res = await Supabase.instance.client
                                    .from('coupons')
                                    .select()
                                    .eq('code', code.trim())
                                    .eq('is_active', true)
                                    .maybeSingle();
                                
                                if (res != null) {
                                  double amount = double.tryParse(res['discount_value'].toString()) ?? 0.0;
                                  if (res['discount_type'] == 'percentage') {
                                    amount = (subtotal * amount) / 100;
                                    final maxDiscount = res['max_discount'] != null ? double.tryParse(res['max_discount'].toString()) : null;
                                    if (maxDiscount != null && amount > maxDiscount) {
                                      amount = maxDiscount;
                                    }
                                  }
                                  ref.read(cartNotifierProvider.notifier).applyCoupon(code.trim(), amount);
                                } else {
                                  if (context.mounted) {
                                    AppNotification.showError(
                                      context,
                                      isRtl ? 'الكود غير صحيح أو منتهي الصلاحية' : 'Invalid or expired code',
                                    );
                                  }
                                }
                              }
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                  ] else ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(8), border: Border.all(color: Colors.green)),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('🎟️ ${cart.appliedCouponCode} تم التطبيق!', style: const TextStyle(color: Colors.green, fontWeight: FontWeight.bold)),
                          InkWell(
                            onTap: () => ref.read(cartNotifierProvider.notifier).removeCoupon(),
                            child: const Icon(Icons.close, color: Colors.red, size: 20),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                  ],

                  const Divider(),
                  _SummaryRow(isRtl ? 'الإجمالي الكلي:' : 'Grand Total:', grandTotal, isTotal: true, currency: currency),
              ],
            ),
            isActive: _currentStep >= 1,
            state: _currentStep > 1 ? StepState.complete : StepState.indexed,
          ),
          Step(
            title: Text(isRtl ? 'طريقة الدفع' : 'Payment Method'),
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (merchantsAsync.isLoading && merchants.isEmpty)
                  const Padding(
                    padding: EdgeInsets.only(bottom: 12),
                    child: LinearProgressIndicator(minHeight: 3),
                  ),
                _buildPaymentOptions(
                  context: context,
                  isRtl: isRtl,
                  settings: settings,
                  merchants: merchants,
                  directMerchants: directMerchants,
                  isDirectPaymentActive: isDirectPaymentActive,
                  isPartialPaymentAllowed: isPartialPaymentAllowed,
                  b2bCreditLimit: b2bCreditLimit,
                  isB2bAllowedForMerchant: isAllowedForMerchant,
                  isB2bAllowedForMagaiees: isAllowedForMagaiees,
                  grandTotal: grandTotal,
                  currency: currency,
                ),
              ],
            ),
            isActive: _currentStep >= 2,
            state: _currentStep == 2 ? StepState.indexed : StepState.complete,
          ),
        ],
      ),
    );
  }

  void _showChangeAddressDialog(BuildContext context, bool isRtl) {
    final arController = TextEditingController(text: _deliveryAddress);
    final enController = TextEditingController(text: _deliveryAddressEn);
    final phoneController = TextEditingController(text: _contactPhone);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isRtl ? 'تحديث عنوان وبيانات التواصل' : 'Update Contact & Delivery Info'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: arController,
              decoration: InputDecoration(
                labelText: isRtl ? 'العنوان والموقع بالتفصيل' : 'Address / Location Details',
                prefixIcon: const Icon(Icons.location_on_outlined),
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: enController,
              decoration: InputDecoration(
                labelText: isRtl ? 'العنوان (إنجليزي - اختياري)' : 'Address (English - Optional)',
                prefixIcon: const Icon(Icons.language_outlined),
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: isRtl ? 'رقم هاتف التواصل' : 'Contact Phone Number',
                prefixIcon: const Icon(Icons.phone_outlined),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              final addrError = DataValidator.validateAddress(arController.text, isRtl: isRtl);
              if (addrError != null) {
                AppNotification.showError(context, addrError);
                return;
              }
              if (phoneController.text.trim().isNotEmpty) {
                final phoneError = DataValidator.validatePhone(phoneController.text.trim(), isRtl: isRtl);
                if (phoneError != null) {
                  AppNotification.showError(context, phoneError);
                  return;
                }
              }
              final prefs = await SharedPreferences.getInstance();
              await prefs.setString('buyer_delivery_address', arController.text.trim());
              await prefs.setString('buyer_delivery_address_en', enController.text.trim());
              await prefs.setString('buyer_contact_phone', phoneController.text.trim());
              setState(() {
                _deliveryAddress = arController.text.trim();
                _deliveryAddressEn = enController.text.trim();
                _contactPhone = phoneController.text.trim();
                _addressError = false;
              });
              if (context.mounted) Navigator.pop(context);
            },
            child: Text(isRtl ? 'تحديث' : 'Update'),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentOptions({
    required BuildContext context,
    required bool isRtl,
    required dynamic settings,
    required List<Map<String, dynamic>> merchants,
    required List<Map<String, dynamic>> directMerchants,
    required bool isDirectPaymentActive,
    required bool isPartialPaymentAllowed,
    required double b2bCreditLimit,
    required bool isB2bAllowedForMerchant,
    required bool isB2bAllowedForMagaiees,
    required double grandTotal,
    required String currency,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ── Case 1: Active Direct Payment for Verified Merchants ──
        if (isDirectPaymentActive) ...[
          // Merchant header badge
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFFAF5FF),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE9D5FF)),
            ),
            child: Row(
              children: [
                const Icon(Icons.verified_user_rounded, color: Color(0xFF7C3AED), size: 24),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl
                            ? 'الدفع المباشر لمتجر التاجر المعتمد: ${directMerchants.map((m) => m['name']).join('، ')}'
                            : 'Direct payment to verified merchant: ${directMerchants.map((m) => m['name']).join(', ')}',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF581C87)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isRtl
                            ? 'تم تفعيل الدفع المباشر للتاجر. يُرجى التحويل لحسابات التاجر أدناه ورفع الإشعار.'
                            : 'Direct merchant payment active. Please transfer to merchant accounts below.',
                        style: TextStyle(fontSize: 11, color: Colors.purple.shade700),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // 1. Bankak for Merchant
          if (settings.merchantAllowBankak && directMerchants.isNotEmpty) ...[
            _PaymentRadio(
              title: directMerchants.length == 1
                  ? (isRtl ? 'تطبيق بنكك (حساب التاجر: ${directMerchants.first['name']})' : 'Bankak App (Merchant: ${directMerchants.first['name']})')
                  : (isRtl ? 'تطبيق بنكك (حسابات التجار المباشرة)' : 'Bankak App (Direct Merchant Accounts)'),
              subtitle: directMerchants.map((m) {
                final mName = m['name']?.toString() ?? '';
                if (m['bankak_account'] != null && m['bankak_account'].toString().isNotEmpty) {
                  final accName = m['bankak_name']?.toString().isNotEmpty == true ? m['bankak_name'] : mName;
                  return '${isRtl ? "رقم الحساب:" : "Account:"} ${m['bankak_account']} | ${isRtl ? "الاسم:" : "Name:"} $accName';
                }
                if (m['bank_account_number'] != null && m['bank_account_number'].toString().isNotEmpty) {
                  final accName = m['bank_account_name']?.toString().isNotEmpty == true ? m['bank_account_name'] : mName;
                  return '${isRtl ? "رقم الحساب:" : "Account:"} ${m['bank_account_number']} | ${isRtl ? "الاسم:" : "Name:"} $accName';
                }
                return isRtl ? '$mName: يرجى تحويل المبلغ ورفع إشعار بنكك' : '$mName: Upload Bankak transfer receipt';
              }).join('\n'),
              value: 'bankak',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.account_balance_wallet,
            ),
          ],

          // 2. O-Cash for Merchant
          if (settings.merchantAllowOcash && directMerchants.isNotEmpty) ...[
            _PaymentRadio(
              title: directMerchants.length == 1
                  ? (isRtl ? 'تطبيق أوكاش (محفظة التاجر: ${directMerchants.first['name']})' : 'O-Cash Wallet (Merchant: ${directMerchants.first['name']})')
                  : (isRtl ? 'تطبيق أوكاش (محافظ التجار المباشرة)' : 'O-Cash Wallet (Direct Merchant Accounts)'),
              subtitle: directMerchants.map((m) {
                final mName = m['name']?.toString() ?? '';
                if (m['ocash_phone'] != null && m['ocash_phone'].toString().isNotEmpty) {
                  final accName = m['ocash_name']?.toString().isNotEmpty == true ? m['ocash_name'] : mName;
                  return '${isRtl ? "رقم المحفظة:" : "Phone:"} ${m['ocash_phone']} | ${isRtl ? "الاسم:" : "Name:"} $accName';
                }
                return isRtl ? '$mName: يرجى تحويل المبلغ ورفع إشعار أوكاش' : '$mName: Upload O-Cash transfer receipt';
              }).join('\n'),
              value: 'ocash',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.payments,
            ),
          ],

          // 3. Direct Wire to Merchant
          if (settings.merchantAllowWire && directMerchants.isNotEmpty) ...[
            _PaymentRadio(
              title: isRtl ? 'تحويل بنكي مباشر لحساب التاجر' : 'Direct Wire to Merchant Bank',
              subtitle: directMerchants.map((m) {
                return '${isRtl ? "البنك:" : "Bank:"} ${m['bank_name'] ?? "-"} | ${isRtl ? "رقم الحساب:" : "Account:"} ${m['bank_account_number'] ?? "-"} | ${isRtl ? "الاسم:" : "Name:"} ${m['bank_account_name'] ?? m['name']}';
              }).join('\n'),
              value: 'bank',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.account_balance,
            ),
          ],

          // 4. Cash on Delivery for Merchant
          if (settings.merchantAllowCod && directMerchants.every((m) => m['accept_cod'] != false)) ...[
            _PaymentRadio(
              title: isRtl ? 'الدفع نقداً عند الاستلام (COD)' : 'Cash on Delivery (COD)',
              subtitle: isRtl
                  ? 'الدفع نقداً عند استلام المواد من مندوب التاجر'
                  : 'Pay cash upon receiving material from merchant delivery',
              value: 'cod',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.local_shipping,
            ),
          ],

          // 5. Partial Payment (Deposit + COD) for Merchant
          if (isPartialPaymentAllowed) ...[
            _PaymentRadio(
              title: isRtl ? 'دفع جزء من المبلغ نقداً والباقي عند الاستلام' : 'Partial Payment (Deposit + Cash on Delivery)',
              subtitle: isRtl
                  ? 'سداد دفعة مقدمة (عربون) لحساب التاجر، ودفع المتبقي نقداً عند الاستلام'
                  : 'Pay an upfront deposit to merchant and remaining balance cash upon delivery',
              value: 'partial_cod',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) {
                setState(() {
                  _selectedPaymentMethod = val!;
                  if (_upfrontPaymentController.text.trim().isEmpty) {
                    final minPct = settings.exclusivePartialDepositPercent > 0 ? settings.exclusivePartialDepositPercent : 30.0;
                    final suggested = (grandTotal * (minPct / 100.0)).roundToDouble();
                    _upfrontPaymentController.text = suggested > 0 ? suggested.toStringAsFixed(0) : '';
                  }
                });
              },
              icon: Icons.price_change_outlined,
            ),
          ],

          // 6. B2B Corporate Credit for Merchant
          if (settings.merchantAllowB2b && directMerchants.every((m) => m['accept_b2b'] != false) && isB2bAllowedForMerchant) ...[
            _PaymentRadio(
              title: isRtl ? 'دفع آجل للشركات (B2B Credit)' : 'B2B Corporate Credit',
              subtitle: isRtl
                  ? (b2bCreditLimit > 0
                      ? 'سداد آجل معتمد (سقف الائتمان: ${b2bCreditLimit.toStringAsFixed(0)} $currency)'
                      : 'سداد آجل للمؤسسات والشركات المعتمدة')
                  : (b2bCreditLimit > 0
                      ? 'Approved corporate credit (Limit: ${b2bCreditLimit.toStringAsFixed(0)} $currency)'
                      : 'Deferred corporate credit payment'),
              value: 'b2b',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.business,
            ),
          ],
        ],

        // ── Case 2: Payment Received in Company Account ──
        if (!isDirectPaymentActive) ...[
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Row(
              children: [
                const Icon(Icons.shield_rounded, color: Color(0xFF2563EB), size: 24),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        merchants.isNotEmpty
                            ? (isRtl
                                ? 'الدفع الآمن عبر منصة السيطرة الرسمية (متجر: ${merchants.map((m) => m['name']).join('، ')})'
                                : 'Secure Payment via Magaiees (Store: ${merchants.map((m) => m['name']).join(', ')})')
                            : (isRtl ? 'منتجات حصرية ومباشرة عبر شركة السيطرة' : 'Exclusive Magaiees Products'),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E40AF)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isRtl
                            ? 'يتم سداد قيمة الطلب في حسابات المنصة الرسمية وحجزها كضمان حتى استلام المواد وتأكيدها.'
                            : 'Payments are held securely in Magaiees official escrow accounts until delivery confirmation.',
                        style: TextStyle(fontSize: 11, color: Colors.blue.shade900),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          if (settings.exclusiveBankak)
            _PaymentRadio(
              title: isRtl ? 'تطبيق بنكك (حساب شركة السيطرة)' : 'Bankak App (Magaiees Account)',
              subtitle: '${isRtl ? "رقم الحساب:" : "Account:"} ${settings.exclusiveBankakAccount} | ${settings.exclusiveBankakName}',
              value: 'bankak',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.account_balance_wallet,
            ),

          if (settings.exclusiveOcash)
            _PaymentRadio(
              title: isRtl ? 'تطبيق أوكاش (محفظة السيطرة)' : 'O-Cash App (Magaiees Wallet)',
              subtitle: '${isRtl ? "رقم المحفظة:" : "Phone:"} ${settings.exclusiveOcashAccount} | ${settings.exclusiveOcashName}',
              value: 'ocash',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.payments,
            ),

          if (settings.exclusiveWire)
            _PaymentRadio(
              title: isRtl ? 'تحويل بنكي مباشر (حساب شركة السيطرة)' : 'Direct Bank Wire (Magaiees)',
              subtitle: '${settings.exclusiveWireBank} | ${settings.exclusiveWireAccount} | ${settings.exclusiveWireName}',
              value: 'bank',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.account_balance,
            ),

          if (settings.exclusiveCod)
            _PaymentRadio(
              title: isRtl ? 'الدفع نقداً عند الاستلام (COD)' : 'Cash on Delivery (COD)',
              subtitle: isRtl ? 'سداد المبلغ كاش عند استلام الشحنة في الموقع' : 'Pay cash upon delivery',
              value: 'cod',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.local_shipping,
            ),

          if (isPartialPaymentAllowed)
            _PaymentRadio(
              title: isRtl ? 'دفع جزء من المبلغ نقداً والباقي عند الاستلام' : 'Partial Payment (Deposit + Cash on Delivery)',
              subtitle: isRtl
                  ? 'سداد دفعة مقدمة (عربون) لحساب شركة السيطرة، ودفع المتبقي نقداً عند الاستلام'
                  : 'Pay an upfront deposit to Magaiees and remaining balance cash upon delivery',
              value: 'partial_cod',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) {
                setState(() {
                  _selectedPaymentMethod = val!;
                  if (_upfrontPaymentController.text.trim().isEmpty) {
                    final minPct = settings.exclusivePartialDepositPercent > 0 ? settings.exclusivePartialDepositPercent : 30.0;
                    final suggested = (grandTotal * (minPct / 100.0)).roundToDouble();
                    _upfrontPaymentController.text = suggested > 0 ? suggested.toStringAsFixed(0) : '';
                  }
                });
              },
              icon: Icons.price_change_outlined,
            ),

          if (settings.exclusiveFawry)
            _PaymentRadio(
              title: isRtl ? 'دفع إلكتروني (فوري / فيزا)' : 'Fawry / Card Payment',
              subtitle: isRtl ? 'دفع إلكتروني سريع وآمن بالبطاقة المصرفية' : 'Fast and secure card payment',
              value: 'fawry',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.credit_card,
            ),

          if (settings.exclusiveB2b && isB2bAllowedForMagaiees)
            _PaymentRadio(
              title: isRtl ? 'دفع آجل للشركات (B2B Credit)' : 'B2B Corporate Credit',
              subtitle: isRtl
                  ? (b2bCreditLimit > 0
                      ? 'تسهيلات سداد آجل معتمدة (سقف الائتمان: ${b2bCreditLimit.toStringAsFixed(0)} $currency)'
                      : 'تسهيلات سداد آجل للمؤسسات والشركات المعتمدة')
                  : (b2bCreditLimit > 0
                      ? 'Approved corporate credit (Limit: ${b2bCreditLimit.toStringAsFixed(0)} $currency)'
                      : 'Deferred corporate credit payment'),
              value: 'b2b',
              groupValue: _selectedPaymentMethod,
              onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
              icon: Icons.business,
            ),
        ],

        // ── Partial Payment Deposit Section ──
        if (_selectedPaymentMethod == 'partial_cod') ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFCBD5E1)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.info_outline, color: Color(0xFF0F766E), size: 22),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        isRtl ? 'تفاصيل السداد الجزئي (عربون + كاش عند الاستلام)' : 'Partial Payment Breakdown',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F766E)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  isRtl
                      ? 'الحد الأدنى المقترح للدفعة الأولى: ${settings.exclusivePartialDepositPercent.toStringAsFixed(0)}% (حوالي ${(grandTotal * (settings.exclusivePartialDepositPercent / 100.0)).toStringAsFixed(0)} $currency).'
                      : 'Suggested minimum deposit: ${settings.exclusivePartialDepositPercent.toStringAsFixed(0)}%.',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _upfrontPaymentController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  onChanged: (_) => setState(() {}),
                  decoration: InputDecoration(
                    labelText: isRtl ? 'مبلغ الدفعة المقدمة (عربون) الآن (ج.س)' : 'Upfront Deposit Amount (SDG)',
                    hintText: isRtl ? 'أدخل الجزء المراد سداده مقدماً' : 'Enter upfront amount',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.payments_outlined),
                  ),
                ),
                const SizedBox(height: 12),
                Builder(
                  builder: (context) {
                    final entered = double.tryParse(_upfrontPaymentController.text.trim()) ?? 0.0;
                    final remaining = (grandTotal - entered).clamp(0.0, double.infinity);
                    return Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.blue.shade100),
                      ),
                      child: Column(
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                isRtl ? 'إجمالي الطلب:' : 'Total Order:',
                                style: const TextStyle(fontSize: 13, color: Colors.black87),
                              ),
                              Text(
                                '${grandTotal.toStringAsFixed(2)} $currency',
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          const Divider(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                isRtl ? 'الدفعة المقدمة (عربون يُحوّل الآن):' : 'Upfront Deposit (To Pay Now):',
                                style: TextStyle(fontSize: 13, color: Colors.blue.shade900, fontWeight: FontWeight.bold),
                              ),
                              Text(
                                '${entered.toStringAsFixed(2)} $currency',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.blue.shade900),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                isRtl ? 'المتبقي نقداً عند الاستلام (كاش):' : 'Remaining Cash on Delivery:',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.green.shade800),
                              ),
                              Text(
                                '${remaining.toStringAsFixed(2)} $currency',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.green.shade800),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  },
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFFDE68A)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.info_rounded, color: Color(0xFFB45309), size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          isDirectPaymentActive
                              ? (isRtl
                                  ? 'يُرجى تحويل مبلغ الدفعة المقدمة لحساب التاجر وإرفاق الإشعار، وسيتم سداد باقي المبلغ كاش عند استلام الشحنة في الموقع.'
                                  : 'Please transfer deposit to merchant bank/wallet and upload receipt; balance will be paid cash upon delivery.')
                              : (isRtl
                                  ? 'يُرجى تحويل مبلغ الدفعة المقدمة لحساب شركة السيطرة وإرفاق الإشعار، وسيتم سداد باقي المبلغ كاش عند استلام الشحنة في الموقع.'
                                  : 'Please transfer deposit to Magaiees bank/wallet and upload receipt; balance will be paid cash upon delivery.'),
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFF78350F), height: 1.3),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],

        // ── Receipt Upload for Electronic/Deposit Payment (Bankak, Ocash, Bank, Partial COD) ──
        if (['bankak', 'ocash', 'bank', 'partial_cod'].contains(_selectedPaymentMethod)) ...[
          const SizedBox(height: 18),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDF4),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFF86EFAC), width: 1.2),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.receipt_long_rounded, color: Color(0xFF15803D), size: 22),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        isRtl ? 'إرفاق إشعار السداد / التحويل (اختياري الآن)' : 'Attach Transfer Receipt (Optional now)',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF15803D)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  isRtl
                      ? 'بما أنك اخترت الاستلام والترحيل الذاتي (بدون رسوم نقل وتنزيل)، يمكنك رفع إشعار التحويل الآن لتسريع مراجعة وتجهيز طلبك، أو رفعه لاحقاً من شاشة (طلباتي).'
                      : 'Since you chose self-pickup (0 delivery fees), you can upload your payment receipt screenshot now to expedite processing, or upload it later from My Orders.',
                  style: TextStyle(fontSize: 11.5, color: Colors.green.shade900, height: 1.3),
                ),
                const SizedBox(height: 12),
                if (_receiptBytes != null) ...[
                  Container(
                    height: 150,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFF15803D)),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.memory(_receiptBytes!, fit: BoxFit.contain),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      TextButton.icon(
                        style: TextButton.styleFrom(foregroundColor: const Color(0xFF0F766E)),
                        onPressed: () async {
                          final picked = await ImagePickerHelper.pickImage(
                            context,
                            imageQuality: 70,
                            maxWidth: 1280,
                            maxHeight: 1280,
                          );
                          if (picked != null) {
                            final bytes = await picked.readAsBytes();
                            setState(() {
                              _receiptBytes = bytes;
                              _receiptExt = picked.name.split('.').last;
                            });
                          }
                        },
                        icon: const Icon(Icons.photo_camera_rounded, size: 16),
                        label: Text(isRtl ? 'تغيير / تصوير الإشعار' : 'Change / Capture Image'),
                      ),
                      const Spacer(),
                      TextButton.icon(
                        style: TextButton.styleFrom(foregroundColor: Colors.red),
                        onPressed: () {
                          setState(() {
                            _receiptBytes = null;
                            _receiptExt = null;
                          });
                        },
                        icon: const Icon(Icons.delete_outline, size: 16),
                        label: Text(isRtl ? 'حذف الصورة' : 'Remove'),
                      ),
                    ],
                  ),
                ] else ...[
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF15803D),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          onPressed: () async {
                            final picked = await ImagePickerHelper.pickImage(
                              context,
                              source: ImageSource.camera,
                              imageQuality: 70,
                              maxWidth: 1280,
                              maxHeight: 1280,
                            );
                            if (picked != null) {
                              final bytes = await picked.readAsBytes();
                              setState(() {
                                _receiptBytes = bytes;
                                _receiptExt = picked.name.split('.').last;
                              });
                            }
                          },
                          icon: const Icon(Icons.photo_camera_rounded, size: 18),
                          label: Text(
                            isRtl ? 'تصوير الإشعار بالكاميرا' : 'Take Receipt Photo',
                            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFF15803D),
                            side: const BorderSide(color: Color(0xFF15803D)),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          onPressed: () async {
                            final picked = await ImagePickerHelper.pickImage(
                              context,
                              source: ImageSource.gallery,
                              imageQuality: 70,
                              maxWidth: 1280,
                              maxHeight: 1280,
                            );
                            if (picked != null) {
                              final bytes = await picked.readAsBytes();
                              setState(() {
                                _receiptBytes = bytes;
                                _receiptExt = picked.name.split('.').last;
                              });
                            }
                          },
                          icon: const Icon(Icons.photo_library_rounded, size: 18),
                          label: Text(
                            isRtl ? 'اختيار من المعرض' : 'From Gallery',
                            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFBBF7D0)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.info_outline_rounded, size: 18, color: Color(0xFF15803D)),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          isRtl
                              ? 'إجمالي قيمة المواد المطلوب سدادها: ${grandTotal.toStringAsFixed(0)} $currency'
                              : 'Total Material Cost to Pay: ${grandTotal.toStringAsFixed(0)} $currency',
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
                        ),
                      ),
                    ],
                  ),
                ),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      flex: 6,
                      child: TextField(
                        controller: _receiptAmountController,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: InputDecoration(
                          isDense: true,
                          labelText: isRtl ? 'المبلغ المحول ($currency)' : 'Transferred Amount ($currency)',
                          hintText: isRtl ? 'أدخل المبلغ' : 'Enter amount',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.payments_outlined, size: 18, color: Color(0xFF15803D)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      flex: 6,
                      child: TextField(
                        controller: _receiptRefController,
                        decoration: InputDecoration(
                          isDense: true,
                          labelText: isRtl ? 'رقم الإشعار / المرجع' : 'Receipt / Ref No.',
                          hintText: isRtl ? 'مثال: 102938475' : 'e.g. 102938475',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.tag, size: 18),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  bool _leavingAfterSuccess = false;

  void _handleNavigateToOrders(BuildContext dialogContext) {
    _leaveAfterSuccess(dialogContext, RouteNames.buyerOrders, refreshOrders: true);
  }

  void _handleNavigateToHome(BuildContext dialogContext) {
    _leaveAfterSuccess(dialogContext, RouteNames.buyerHome);
  }

  /// Closes the success dialog once, then navigates with go_router.
  /// go() replaces the whole stack, so the checkout screen must NOT be popped
  /// manually (multiple pops in a row were freezing the screen).
  void _leaveAfterSuccess(BuildContext dialogContext, String route, {bool refreshOrders = false}) {
    if (_leavingAfterSuccess) return;
    _leavingAfterSuccess = true;

    final container = ProviderScope.containerOf(context, listen: false);
    final rootNav = Navigator.of(dialogContext, rootNavigator: true);
    if (rootNav.canPop()) rootNav.pop();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final navContext = rootNavigatorKey.currentContext ?? (mounted ? context : null);
      if (navContext != null) {
        try {
          GoRouter.of(navContext).go(route);
        } catch (e) {
          debugPrint('Navigation after order failed: $e');
        }
      }
      // Clear cart / refresh after navigation so the checkout screen
      // doesn't rebuild with an empty cart underneath the dialog.
      try {
        container.read(cartNotifierProvider.notifier).clearCart();
        if (refreshOrders) {
          container.invalidate(buyerOrdersProvider);
          container.invalidate(merchantOrdersProvider);
          container.invalidate(employeeOrdersProvider);
        }
      } catch (_) {}
    });
  }

  void _showOrderUnderReviewDialog(bool isRtl, bool isSelfPickup, {bool receiptUploaded = false, int ordersCount = 1, List<String> storeNames = const []}) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, result) {
          if (didPop) return;
          _handleNavigateToHome(dialogContext);
        },
        child: Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          elevation: 10,
          clipBehavior: Clip.antiAlias,
          child: SingleChildScrollView(
            child: Container(
              constraints: const BoxConstraints(maxWidth: 480),
              decoration: const BoxDecoration(
                color: Colors.white,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
              // Header Gradient Accent
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                decoration: BoxDecoration(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                  gradient: LinearGradient(
                    colors: isSelfPickup
                        ? [const Color(0xFF047857), const Color(0xFF059669), const Color(0xFF10B981)]
                        : [const Color(0xFF0F766E), const Color(0xFF0D9488), const Color(0xFF047857)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  children: [
                    Container(
                      width: 74,
                      height: 74,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.white.withValues(alpha: 0.18),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.35), width: 2),
                      ),
                      child: Center(
                        child: Container(
                          width: 56,
                          height: 56,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white,
                          ),
                          child: Icon(
                            isSelfPickup ? Icons.check_circle_rounded : Icons.assignment_turned_in_rounded,
                            color: isSelfPickup ? const Color(0xFF047857) : const Color(0xFF0F766E),
                            size: 34,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      isSelfPickup
                          ? (isRtl ? 'تم تأكيد طلبك بنجاح!' : 'Order Confirmed Successfully!')
                          : (isRtl ? 'تم إرسال واستلام طلبك بنجاح' : 'Order Placed Successfully'),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                      decoration: BoxDecoration(
                        color: isSelfPickup ? const Color(0xFFECFDF5) : const Color(0xFFFEF3C7),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: isSelfPickup ? const Color(0xFF34D399) : const Color(0xFFF59E0B)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.08),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            isSelfPickup ? Icons.warehouse_rounded : Icons.hourglass_top_rounded,
                            size: 16,
                            color: isSelfPickup ? const Color(0xFF065F46) : const Color(0xFFB45309),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            isSelfPickup
                                ? (isRtl ? 'استلام وترحيل ذاتي (0.00 رسوم نقل)' : 'Self Pickup (0 Delivery Fees)')
                                : (isRtl ? 'الطلب قيد المراجعة وحساب الرسوم' : 'Order Under Review & Fee Estimation'),
                            style: TextStyle(
                              color: isSelfPickup ? const Color(0xFF065F46) : const Color(0xFF92400E),
                              fontWeight: FontWeight.bold,
                              fontSize: 12.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    // Visual Workflow Timeline
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        children: [
                          _buildTimelineStep(
                            icon: Icons.check_circle_rounded,
                            title: isRtl ? 'تم الإرسال' : 'Submitted',
                            isDone: true,
                            isCurrent: false,
                          ),
                          Expanded(child: Container(height: 2, color: const Color(0xFF0F766E))),
                          _buildTimelineStep(
                            icon: isSelfPickup ? Icons.payment_rounded : Icons.manage_search_rounded,
                            title: isSelfPickup
                                ? (receiptUploaded
                                    ? (isRtl ? 'التحقق من السداد' : 'Verifying')
                                    : (isRtl ? 'إتمام السداد' : 'Payment'))
                                : (isRtl ? 'تحديد الرسوم' : 'Fee Review'),
                            isDone: isSelfPickup && receiptUploaded,
                            isCurrent: !isSelfPickup || !receiptUploaded,
                          ),
                          Expanded(
                            child: Container(
                              height: 2,
                              color: (isSelfPickup && receiptUploaded) ? const Color(0xFF0F766E) : Colors.grey.shade300,
                            ),
                          ),
                          _buildTimelineStep(
                            icon: isSelfPickup ? Icons.store_rounded : Icons.local_shipping_outlined,
                            title: isSelfPickup
                                ? (isRtl ? 'استلام المواد' : 'Pickup')
                                : (isRtl ? 'التسليم والتنزيل' : 'Delivery'),
                            isDone: false,
                            isCurrent: isSelfPickup && receiptUploaded,
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 14),

                    // Delivery & Next Steps Info Box
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: isSelfPickup ? const Color(0xFFF0FDF4) : const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: isSelfPickup ? const Color(0xFFBBF7D0) : const Color(0xFFFDE68A)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(
                                isSelfPickup ? Icons.info_outline_rounded : Icons.schedule_rounded,
                                color: isSelfPickup ? const Color(0xFF15803D) : const Color(0xFFD97706),
                                size: 19,
                              ),
                              const SizedBox(width: 8),
                              Builder(
                                builder: (context) {
                                  final bool hasExclusive = storeNames.any((s) => s.contains('حصري'));
                                  return Expanded(
                                    child: Text(
                                      isSelfPickup
                                          ? (isRtl ? 'تفاصيل استلام الطلب:' : 'Pickup Details:')
                                          : (isRtl
                                              ? (hasExclusive ? 'في انتظار مراجعة وتأكيد فريق السيطرة:' : 'في انتظار مراجعة وتأكيد التاجر:')
                                              : (hasExclusive ? 'Pending Magaiees Review:' : 'Pending Merchant Review:')),
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13,
                                        color: isSelfPickup ? const Color(0xFF166534) : const Color(0xFF92400E),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          if (!isSelfPickup) ...[
                            Builder(
                              builder: (context) {
                                final bool hasExclusive = storeNames.any((s) => s.contains('حصري'));
                                return Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      isRtl
                                          ? (hasExclusive
                                              ? 'بعد قيام فريق السيطرة بمراجعة تفاصيل طلبك الحصري وعنوانك وتحديد رسوم الترحيل والتنزيل، سيتم تأكيد طلبك وإشعارك بذلك.'
                                              : 'بعد قيام التاجر بمراجعة تفاصيل طلبك وعنوانك وتحديد رسوم الترحيل والتنزيل، سيتم تأكيد طلبك وإشعارك بذلك.')
                                          : (hasExclusive
                                              ? 'After Magaiees team reviews your exclusive order location and sets transport fees, your order will be confirmed.'
                                              : 'After the merchant reviews your delivery location and sets transport & offloading fees, your order will be confirmed and you will be notified.'),
                                      style: TextStyle(fontSize: 12, color: Colors.orange.shade900, height: 1.4),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      isRtl
                                          ? 'يمكنك بعدها مراجعة التكلفة الإجمالية من شاشة (طلباتي)، واعتمادها، ثم إتمام عملية الدفع ورفع إشعار التحويل مباشرة.'
                                          : 'You can then review total fees in My Orders, approve them, and complete payment by uploading the transfer receipt.',
                                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.brown.shade800, height: 1.4),
                                    ),
                                  ],
                                );
                              },
                            ),
                          ] else ...[
                            Text(
                              isRtl
                                  ? 'تم اعتماد الطلب بنظام الاستلام والترحيل الذاتي بواسطة المشتري. المبلغ الإجمالي نهائي ومحدد (0.00 رسوم نقل وتنزيل).'
                                  : 'Order placed with self-pickup. Total amount is final (0.00 transport and offloading fees).',
                              style: TextStyle(fontSize: 12, color: Colors.green.shade900, height: 1.4),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              receiptUploaded
                                  ? (isRtl
                                      ? 'تم إرفاق إشعار التحويل بنجاح! الطلب الآن قيد التحقق من السداد وتجهيز البضاعة للتسليم بالمستودع.'
                                      : 'Receipt uploaded! Order is being verified and materials prepared for warehouse pickup.')
                                  : (isRtl
                                      ? 'يمكنك إتمام التحويل وإرفاق إشعار السداد الآن عبر شاشة (طلباتي) ليتم تجهيز بضاعتك للاستلام فوراً.'
                                      : 'You can complete transfer and upload receipt now via My Orders to expedite preparation.'),
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.green.shade900, height: 1.4),
                            ),
                          ],
                        ],
                      ),
                    ),

                    const SizedBox(height: 18),

                    // Multi-store order info
                    if (ordersCount > 1) ...[
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFF93C5FD)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.layers_rounded, color: Color(0xFF1D4ED8), size: 18),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    isRtl
                                        ? 'تم إنشاء $ordersCount طلبات منفصلة:'
                                        : '$ordersCount separate orders created:',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: Color(0xFF1E40AF)),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            ...storeNames.asMap().entries.map((entry) => Padding(
                              padding: const EdgeInsets.only(top: 3),
                              child: Row(
                                children: [
                                  const SizedBox(width: 26),
                                  Icon(
                                    entry.value.contains('السيطرة') || entry.value.contains('حصري')
                                        ? Icons.verified_rounded
                                        : Icons.storefront_outlined,
                                    size: 14,
                                    color: const Color(0xFF3B82F6),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '${isRtl ? 'طلب' : 'Order'} ${entry.key + 1}: ${entry.value}',
                                    style: const TextStyle(fontSize: 12, color: Color(0xFF1E3A5F)),
                                  ),
                                ],
                              ),
                            )),
                            const SizedBox(height: 6),
                            Text(
                              isRtl
                                  ? 'يمكنك متابعة كل طلب من صفحة (طلباتي)'
                                  : 'Track each order from My Orders page',
                              style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontStyle: FontStyle.italic),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Action Buttons
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isSelfPickup ? const Color(0xFF047857) : const Color(0xFF0F766E),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          elevation: 2,
                          minimumSize: const Size(double.infinity, 48),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        ),
                        icon: const Icon(Icons.receipt_long_rounded, size: 20),
                        onPressed: () => _handleNavigateToOrders(dialogContext),
                        label: Text(
                          isSelfPickup
                              ? (receiptUploaded
                                  ? (isRtl ? 'متابعة تفاصيل الطلب' : 'Track Order Details')
                                  : (isRtl ? 'إتمام السداد ورفع الإشعار' : 'Complete Payment'))
                              : (isRtl ? 'متابعة حالة الطلب' : 'Track Order Status'),
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      child: TextButton.icon(
                        style: TextButton.styleFrom(
                          foregroundColor: Colors.grey.shade700,
                          minimumSize: const Size(double.infinity, 42),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        ),
                        icon: const Icon(Icons.home_outlined, size: 18),
                        onPressed: () => _handleNavigateToHome(dialogContext),
                        label: Text(
                          isRtl ? 'العودة للصفحة الرئيسية' : 'Return to Home',
                          style: const TextStyle(fontSize: 13.5),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  ),
);
  }

  Widget _buildTimelineStep({
    required IconData icon,
    required String title,
    required bool isDone,
    required bool isCurrent,
  }) {
    final color = isDone
        ? const Color(0xFF0F766E)
        : (isCurrent ? const Color(0xFFD97706) : Colors.grey.shade400);

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            color: isDone
                ? const Color(0xFF0F766E)
                : (isCurrent ? const Color(0xFFFEF3C7) : Colors.grey.shade100),
            shape: BoxShape.circle,
            border: Border.all(
              color: color,
              width: isCurrent ? 2 : 1.5,
            ),
          ),
          child: Icon(
            icon,
            size: 16,
            color: isDone ? Colors.white : color,
          ),
        ),
        const SizedBox(height: 5),
        Text(
          title,
          style: TextStyle(
            fontSize: 10,
            fontWeight: (isDone || isCurrent) ? FontWeight.bold : FontWeight.normal,
            color: (isDone || isCurrent) ? Colors.black87 : Colors.grey.shade600,
          ),
        ),
      ],
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
  final String? subtitle;
  final String value;
  final String groupValue;
  final ValueChanged<String?> onChanged;
  final IconData icon;

  const _PaymentRadio({
    required this.title,
    this.subtitle,
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
          Icon(icon, size: 22, color: Theme.of(context).primaryColor),
          const SizedBox(width: 10),
          Expanded(child: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14))),
        ],
      ),
      subtitle: subtitle != null
          ? Padding(
              padding: const EdgeInsets.only(top: 4, right: 32),
              child: Text(subtitle!, style: TextStyle(fontSize: 12, color: Colors.grey.shade700)),
            )
          : null,
      value: value,
      groupValue: groupValue,
      onChanged: onChanged,
      contentPadding: EdgeInsets.zero,
    );
  }
}
