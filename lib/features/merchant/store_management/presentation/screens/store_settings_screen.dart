import 'package:magaiees_marketplace/features/admin/finance/application/banks_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/presentation/screens/merchant_dashboard_screen.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/presentation/screens/merchant_wallet_screen.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/features/merchant/inventory/presentation/screens/merchant_inventory_screen.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/application/merchant_wallet_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';

class StoreSettingsScreen extends ConsumerStatefulWidget {
  const StoreSettingsScreen({super.key});

  @override
  ConsumerState<StoreSettingsScreen> createState() => _StoreSettingsScreenState();
}

class _StoreSettingsScreenState extends ConsumerState<StoreSettingsScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _crController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _workingHoursController = TextEditingController();
  final _returnPolicyController = TextEditingController();
  
  final _bankNameController = TextEditingController();
  final _bankAccountNameController = TextEditingController();
  final _bankAccountNumberController = TextEditingController();

  final _bankakAccountController = TextEditingController();
  final _bankakNameController = TextEditingController();
  final _ocashPhoneController = TextEditingController();
  final _ocashNameController = TextEditingController();
  bool _acceptCod = true;
  bool _acceptB2b = true;
  bool _acceptPartialCod = true;
  final _b2bApprovedBuyersController = TextEditingController();

  String _usdRateSource = 'platform';
  final _customUsdRateController = TextEditingController();
  
  bool _isLoading = true;
  bool _isOpen = true;
  bool _chatSoundsEnabled = true;
  bool _directPaymentEnabled = false;
  RealtimeChannel? _userRealtimeChannel;
  RealtimeChannel? _storeRealtimeChannel;
  String? _storeId;
  DateTime? _createdAt;

  @override
  void initState() {
    super.initState();
    _loadStoreData();
  }

  Future<void> _loadStoreData() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) {
      setState(() => _isLoading = false);
      return;
    }

    try {
      final res = await Supabase.instance.client
          .from('stores')
          .select()
          .eq('owner_id', user.id)
          .limit(1)
          .maybeSingle();
      
      if (res != null) {
        _storeId = res['id'];
        _nameController.text = res['name'] ?? '';
        _crController.text = res['cr_number'] ?? '';
        _phoneController.text = res['contact_phone'] ?? '';
        _addressController.text = res['address'] ?? '';
        _workingHoursController.text = res['working_hours'] ?? '';
        _returnPolicyController.text = res['return_policy'] ?? '';
        _isOpen = res['is_open'] ?? true;
        if (res['created_at'] != null) {
          _createdAt = DateTime.tryParse(res['created_at'].toString());
        }
        if (res['usd_exchange_rate'] != null) {
          final r = res['usd_exchange_rate'];
          _customUsdRateController.text = (r is num) ? r.toStringAsFixed(0) : r.toString();
        }
      }
      
      final userRes = await Supabase.instance.client
          .from('users')
          .select('bank_name, bank_account_name, bank_account_number, permissions, direct_payment_enabled')
          .eq('id', user.id)
          .maybeSingle();
          
      if (userRes != null) {
        _directPaymentEnabled = (userRes['direct_payment_enabled'] == true) ||
            (userRes['permissions'] is Map && (userRes['permissions'] as Map)['direct_payment_enabled'] == true);
        _bankNameController.text = userRes['bank_name'] ?? '';
        _bankAccountNameController.text = userRes['bank_account_name'] ?? '';
        _bankAccountNumberController.text = userRes['bank_account_number'] ?? '';
        if (userRes['permissions'] is Map) {
          final perms = userRes['permissions'] as Map<String, dynamic>;
          _usdRateSource = perms['usd_rate_source'] ?? _usdRateSource;
          final savedRate = perms['store_usd_rate']?.toString() ??
              (res != null && res['usd_exchange_rate'] != null ? res['usd_exchange_rate'].toString() : null) ??
              ref.read(globalSettingsProvider).platformUsdRate.toStringAsFixed(0);
          _customUsdRateController.text = savedRate;
          _bankakAccountController.text = perms['bankak_account']?.toString() ?? '';
          _bankakNameController.text = perms['bankak_name']?.toString() ?? '';
          _ocashPhoneController.text = perms['ocash_phone']?.toString() ?? '';
          _ocashNameController.text = perms['ocash_name']?.toString() ?? '';
          _acceptCod = perms['accept_cod'] ?? true;
          _acceptB2b = perms['accept_b2b'] ?? true;
          _acceptPartialCod = perms['accept_partial_cod'] ?? true;
          final rawBuyers = perms['b2b_approved_buyers'];
          if (rawBuyers is List) {
            _b2bApprovedBuyersController.text = rawBuyers.join(', ');
          } else {
            _b2bApprovedBuyersController.text = rawBuyers?.toString() ?? '';
          }
        }
      }

      _subscribeToUserRealtime(user.id);
      if (_storeId != null) {
        _subscribeToStoreRealtime(_storeId!);
      }
      
      final prefs = await SharedPreferences.getInstance();
      _chatSoundsEnabled = prefs.getBool('chat_sounds') ?? true;
    } catch (e) {
      debugPrint('Error loading store data: $e');
    }
    
    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _saveData() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);
    
    try {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user != null) {
        final platformRate = ref.read(globalSettingsProvider).platformUsdRate;
        final customRate = double.tryParse(_customUsdRateController.text.trim()) ?? platformRate;
        final effectiveRate = _usdRateSource == 'platform' ? platformRate : customRate;

        final data = {
          'name': _nameController.text,
          'cr_number': _crController.text,
          'contact_phone': _phoneController.text,
          'address': _addressController.text,
          'working_hours': _workingHoursController.text,
          'return_policy': _returnPolicyController.text,
          'is_open': _isOpen,
          'bank_name': _bankNameController.text,
          'bank_account_name': _bankAccountNameController.text,
          'bank_account_number': _bankAccountNumberController.text,
        };

        if (_storeId != null) {
          await Supabase.instance.client
              .from('stores')
              .update(data)
              .eq('id', _storeId!);

          try {
            await Supabase.instance.client
                .from('stores')
                .update({'usd_exchange_rate': effectiveRate})
                .eq('id', _storeId!);
          } catch (_) {}

          // Recalculate product prices based on effective USD rate
          try {
            final products = await Supabase.instance.client
                .from('products')
                .select('id, price_usd, price_sdg')
                .eq('store_id', _storeId!);
            for (var p in products) {
              double usd = double.tryParse(p['price_usd']?.toString() ?? '0') ?? 0;
              double sdg = double.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0;
              if (usd == 0 && sdg > 0 && effectiveRate > 0) {
                usd = sdg / effectiveRate;
              }
              if (usd > 0) {
                final newSdg = usd * effectiveRate;
                await Supabase.instance.client.from('products').update({
                  'price_sdg': newSdg,
                  'price_usd': usd,
                }).eq('id', p['id']).eq('store_id', _storeId!);
              }
            }
            ref.invalidate(merchantStoreInfoProvider);
            ref.invalidate(merchantInventoryProvider);
            ref.invalidate(productsProvider);
          } catch (_) {}
        } else {
          data['owner_id'] = user.id;
          final inserted = await Supabase.instance.client
              .from('stores')
              .insert(data)
              .select()
              .single();
          _storeId = inserted['id'];
        }
        
        final userRow = await Supabase.instance.client.from('users').select('permissions').eq('id', user.id).maybeSingle();
        final perms = (userRow != null && userRow['permissions'] is Map)
            ? Map<String, dynamic>.from(userRow['permissions'] as Map)
            : <String, dynamic>{};
        perms['usd_rate_source'] = _usdRateSource;
        perms['store_usd_rate'] = effectiveRate;
        perms['bankak_account'] = _bankakAccountController.text.trim();
        perms['bankak_name'] = _bankakNameController.text.trim();
        perms['ocash_phone'] = _ocashPhoneController.text.trim();
        perms['ocash_name'] = _ocashNameController.text.trim();
        perms['accept_cod'] = _acceptCod;
        perms['accept_b2b'] = _acceptB2b;
        perms['accept_partial_cod'] = _acceptPartialCod;
        final buyersText = _b2bApprovedBuyersController.text.trim();
        if (buyersText.isNotEmpty) {
          perms['b2b_approved_buyers'] = buyersText
              .split(RegExp(r'[,،\n]+'))
              .map((e) => e.trim())
              .where((e) => e.isNotEmpty)
              .toList();
        } else {
          perms['b2b_approved_buyers'] = [];
        }

        await Supabase.instance.client.from('users').update({
          'bank_name': _bankNameController.text.trim(),
          'bank_account_name': _bankAccountNameController.text.trim(),
          'bank_account_number': _bankAccountNumberController.text.trim(),
          'permissions': perms,
        }).eq('id', user.id);

        if (mounted) {
          final isRtl = Directionality.of(context) == TextDirection.rtl;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(isRtl ? 'تم حفظ إعدادات المتجر وأسعار الصرف بنجاح!' : 'Store settings and exchange rates saved successfully!'),
              backgroundColor: Colors.green,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _crController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _workingHoursController.dispose();
    _returnPolicyController.dispose();
    _bankNameController.dispose();
    _bankAccountNameController.dispose();
    _bankAccountNumberController.dispose();
    _bankakAccountController.dispose();
    _bankakNameController.dispose();
    _ocashPhoneController.dispose();
    _ocashNameController.dispose();
    _b2bApprovedBuyersController.dispose();
    _customUsdRateController.dispose();
    _userRealtimeChannel?.unsubscribe();
    _storeRealtimeChannel?.unsubscribe();
    super.dispose();
  }

  void _subscribeToUserRealtime(String userId) {
    _userRealtimeChannel?.unsubscribe();
    _userRealtimeChannel = Supabase.instance.client
        .channel('store_settings_user_$userId')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'users',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'id',
            value: userId,
          ),
          callback: (payload) {
            if (!mounted) return;
            final record = payload.newRecord;
            if (record.isNotEmpty) {
              setState(() {
                if (record.containsKey('direct_payment_enabled')) {
                  _directPaymentEnabled = record['direct_payment_enabled'] == true;
                }
                if (record['bank_name'] != null) {
                  _bankNameController.text = record['bank_name']?.toString() ?? '';
                }
                if (record['bank_account_name'] != null) {
                  _bankAccountNameController.text = record['bank_account_name']?.toString() ?? '';
                }
                if (record['bank_account_number'] != null) {
                  _bankAccountNumberController.text = record['bank_account_number']?.toString() ?? '';
                }
                if (record['permissions'] is Map) {
                  final perms = record['permissions'] as Map<String, dynamic>;
                  if (perms.containsKey('usd_rate_source') && perms['usd_rate_source'] != null) {
                    _usdRateSource = perms['usd_rate_source'].toString();
                  }
                  if (perms.containsKey('store_usd_rate') && perms['store_usd_rate'] != null) {
                    final r = perms['store_usd_rate'];
                    _customUsdRateController.text = (r is num) ? r.toStringAsFixed(0) : r.toString();
                  }
                  if (perms.containsKey('bankak_account')) {
                    _bankakAccountController.text = perms['bankak_account']?.toString() ?? '';
                  }
                  if (perms.containsKey('bankak_name')) {
                    _bankakNameController.text = perms['bankak_name']?.toString() ?? '';
                  }
                  if (perms.containsKey('ocash_phone')) {
                    _ocashPhoneController.text = perms['ocash_phone']?.toString() ?? '';
                  }
                  if (perms.containsKey('ocash_name')) {
                    _ocashNameController.text = perms['ocash_name']?.toString() ?? '';
                  }
                  if (perms.containsKey('accept_cod')) {
                    _acceptCod = perms['accept_cod'] ?? true;
                  }
                  if (perms.containsKey('accept_b2b')) {
                    _acceptB2b = perms['accept_b2b'] ?? true;
                  }
                  if (perms.containsKey('accept_partial_cod')) {
                    _acceptPartialCod = perms['accept_partial_cod'] ?? true;
                  }
                  if (perms.containsKey('b2b_approved_buyers')) {
                    final rawBuyers = perms['b2b_approved_buyers'];
                    if (rawBuyers is List) {
                      _b2bApprovedBuyersController.text = rawBuyers.join(', ');
                    }
                  }
                }
              });
              ref.invalidate(merchantStoreInfoProvider);
              ref.invalidate(merchantWalletProvider);
            }
          },
        )
        .subscribe();
  }

  void _subscribeToStoreRealtime(String storeId) {
    _storeRealtimeChannel?.unsubscribe();
    _storeRealtimeChannel = Supabase.instance.client
        .channel('store_settings_store_$storeId')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'stores',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'id',
            value: storeId,
          ),
          callback: (payload) {
            if (!mounted) return;
            final record = payload.newRecord;
            if (record.isNotEmpty) {
              setState(() {
                if (record.containsKey('name') && record['name'] != null) {
                  _nameController.text = record['name'].toString();
                }
                if (record.containsKey('usd_exchange_rate') && record['usd_exchange_rate'] != null) {
                  final r = record['usd_exchange_rate'];
                  _customUsdRateController.text = (r is num) ? r.toStringAsFixed(0) : r.toString();
                }
                if (record.containsKey('is_open') && record['is_open'] != null) {
                  _isOpen = record['is_open'] == true;
                }
              });
              ref.invalidate(merchantStoreInfoProvider);
              ref.invalidate(merchantInventoryProvider);
              ref.invalidate(productsProvider);
            }
          },
        )
        .subscribe();
  }

  Future<void> _saveExchangeRateOnly() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null || _storeId == null) return;

    final platformRate = ref.read(globalSettingsProvider).platformUsdRate;
    double effectiveRate = platformRate;
    if (_usdRateSource == 'custom') {
      final customRate = double.tryParse(_customUsdRateController.text.trim());
      if (customRate == null || customRate <= 0) {
        final isRtl = Directionality.of(context) == TextDirection.rtl;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'يرجى إدخال سعر صرف مخصص صحيح' : 'Please enter valid custom exchange rate'),
            backgroundColor: Colors.red,
          ),
        );
        return;
      }
      effectiveRate = customRate;
    }

    setState(() => _isLoading = true);

    try {
      final userRow = await Supabase.instance.client.from('users').select('permissions').eq('id', user.id).maybeSingle();
      final perms = (userRow != null && userRow['permissions'] is Map)
          ? Map<String, dynamic>.from(userRow['permissions'] as Map)
          : <String, dynamic>{};
      perms['usd_rate_source'] = _usdRateSource;
      perms['store_usd_rate'] = effectiveRate;

      await Supabase.instance.client.from('users').update({
        'permissions': perms,
      }).eq('id', user.id);

      await Supabase.instance.client.from('stores').update({
        'usd_exchange_rate': effectiveRate,
      }).eq('id', _storeId!);

      // Recalculate product prices based on effective USD rate
      final products = await Supabase.instance.client
          .from('products')
          .select('id, price_usd, price_sdg')
          .eq('store_id', _storeId!);
      for (var p in products) {
        double usd = double.tryParse(p['price_usd']?.toString() ?? '0') ?? 0;
        double sdg = double.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0;
        if (usd == 0 && sdg > 0 && effectiveRate > 0) {
          usd = sdg / effectiveRate;
        }
        if (usd > 0) {
          final newSdg = usd * effectiveRate;
          await Supabase.instance.client.from('products').update({
            'price_sdg': newSdg,
            'price_usd': usd,
          }).eq('id', p['id']).eq('store_id', _storeId!);
        }
      }

      ref.invalidate(merchantStoreInfoProvider);
      ref.invalidate(merchantInventoryProvider);
      ref.invalidate(productsProvider);

      if (mounted) {
        final isRtl = Directionality.of(context) == TextDirection.rtl;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'تم توحيد وتحديث سعر الصرف بنجاح في المتجر والمخزون!' : 'Exchange rate unified and synced across store and inventory!'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showMerchantBankAccountsModal(BuildContext context, bool isRtl) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return Container(
              height: MediaQuery.of(context).size.height * 0.85,
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      margin: const EdgeInsets.only(top: 12, bottom: 8),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FDF4),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.account_balance_wallet_rounded, color: Color(0xFF15803D), size: 24),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl ? 'تفاصيل الحساب البنكي واستقبال المبالغ' : 'Bank & Payment Accounts',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                              ),
                              Text(
                                isRtl ? 'حساباتك التي يتلقى عبرها متجرك مبالغ المبيعات من المشترين مباشرة' : 'Accounts where buyers will transfer funds directly',
                                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          onPressed: () => Navigator.pop(modalCtx),
                          icon: const Icon(Icons.close_rounded),
                        ),
                      ],
                    ),
                  ),
                  const Divider(height: 1),
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? '1. الحساب البنكي المعتمد (تحويل بنكي وسحب أرباح)' : '1. Official Bank Account',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E40AF)),
                          ),
                          const SizedBox(height: 10),
                          Consumer(
                            builder: (bCtx, bRef, _) {
                              final banksList = bRef.watch(banksProvider).valueOrNull?.where((b) => b.isActive).toList() ?? [];
                              final bankNames = banksList.map((b) => isRtl ? b.nameAr : b.nameEn).toList();
                              final currentVal = bankNames.contains(_bankNameController.text) ? _bankNameController.text : null;
                              
                              return DropdownButtonFormField<String>(
                                value: currentVal,
                                hint: Text(isRtl ? 'اختر البنك المعتمد' : 'Select Bank'),
                                decoration: InputDecoration(
                                  labelText: isRtl ? 'اسم البنك المعتمد *' : 'Bank Name *',
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                  prefixIcon: const Icon(Icons.account_balance),
                                ),
                                items: bankNames.map((b) => DropdownMenuItem(value: b, child: Text(b, style: const TextStyle(fontSize: 13)))).toList(),
                                onChanged: (val) {
                                  if (val != null) {
                                    setState(() => _bankNameController.text = val);
                                    setModalState(() {});
                                  }
                                },
                              );
                            },
                          ),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _bankAccountNameController,
                            decoration: InputDecoration(
                              labelText: isRtl ? 'اسم صاحب الحساب رباعي *' : 'Account Holder Name *',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              prefixIcon: const Icon(Icons.person),
                            ),
                          ),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _bankAccountNumberController,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              labelText: isRtl ? 'رقم الحساب البنكي *' : 'Bank Account Number *',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              prefixIcon: const Icon(Icons.numbers),
                            ),
                          ),
                          const SizedBox(height: 20),

                          Consumer(
                            builder: (bCtx, bRef, _) {
                              final settings = bRef.watch(globalSettingsProvider);
                              if (!settings.merchantAllowBankak) return const SizedBox.shrink();
                              return Container(
                                margin: const EdgeInsets.only(bottom: 16),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEFF6FF),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFBFDBFE)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.account_balance_wallet_rounded, color: Color(0xFF1E40AF), size: 18),
                                        const SizedBox(width: 8),
                                        Text(
                                          isRtl ? 'حساب تطبيق بنكك (خاص بالمتجر)' : 'Store Bankak Account',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E40AF)),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 10),
                                    TextFormField(
                                      controller: _bankakAccountController,
                                      keyboardType: TextInputType.number,
                                      decoration: InputDecoration(
                                        labelText: isRtl ? 'رقم حساب بنكك' : 'Bankak Account No',
                                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                        prefixIcon: const Icon(Icons.numbers),
                                        fillColor: Colors.white,
                                        filled: true,
                                      ),
                                    ),
                                    const SizedBox(height: 10),
                                    TextFormField(
                                      controller: _bankakNameController,
                                      decoration: InputDecoration(
                                        labelText: isRtl ? 'اسم صاحب الحساب في بنكك' : 'Bankak Name',
                                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                        prefixIcon: const Icon(Icons.badge),
                                        fillColor: Colors.white,
                                        filled: true,
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),

                          Consumer(
                            builder: (bCtx, bRef, _) {
                              final settings = bRef.watch(globalSettingsProvider);
                              if (!settings.merchantAllowOcash) return const SizedBox.shrink();
                              return Container(
                                margin: const EdgeInsets.only(bottom: 16),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFAF5FF),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFE9D5FF)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.payments_rounded, color: Color(0xFF6B21A8), size: 18),
                                        const SizedBox(width: 8),
                                        Text(
                                          isRtl ? 'محفظة أوكاش (خاصة بالمتجر)' : 'Store O-Cash Wallet',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF6B21A8)),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 10),
                                    TextFormField(
                                      controller: _ocashPhoneController,
                                      keyboardType: TextInputType.phone,
                                      decoration: InputDecoration(
                                        labelText: isRtl ? 'رقم المحفظة / الهاتف' : 'O-Cash Phone No',
                                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                        prefixIcon: const Icon(Icons.phone_android),
                                        fillColor: Colors.white,
                                        filled: true,
                                      ),
                                    ),
                                    const SizedBox(height: 10),
                                    TextFormField(
                                      controller: _ocashNameController,
                                      decoration: InputDecoration(
                                        labelText: isRtl ? 'اسم صاحب المحفظة' : 'Wallet Holder Name',
                                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                        prefixIcon: const Icon(Icons.person),
                                        fillColor: Colors.white,
                                        filled: true,
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),

                          Consumer(
                            builder: (bCtx, bRef, _) {
                              final settings = bRef.watch(globalSettingsProvider);
                              if (!settings.merchantAllowCod) return const SizedBox.shrink();
                              return SwitchListTile(
                                title: Text(isRtl ? 'قبول الدفع نقداً عند الاستلام لطلبات متجري' : 'Accept Cash on Delivery'),
                                subtitle: Text(isRtl ? 'السماح للزبائن بدفع قيمة طلبات هذا المتجر كاش عند الاستلام' : 'Allow customers to pay cash upon delivery'),
                                value: _acceptCod,
                                activeColor: Colors.green,
                                onChanged: (val) {
                                  setState(() => _acceptCod = val);
                                  setModalState(() {});
                                },
                                secondary: const Icon(Icons.local_shipping_rounded, color: Colors.green),
                              );
                            },
                          ),

                          Consumer(
                            builder: (bCtx, bRef, _) {
                              final settings = bRef.watch(globalSettingsProvider);
                              if (!settings.merchantAllowB2b) return const SizedBox.shrink();
                              return Container(
                                margin: const EdgeInsets.only(top: 10, bottom: 8),
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFAF5FF),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFE9D5FF)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    SwitchListTile(
                                      title: Text(
                                        isRtl ? 'قبول الدفع الآجل للشركات (B2B Credit)' : 'Accept B2B Corporate Credit',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF6B21A8)),
                                      ),
                                      subtitle: Text(
                                        isRtl
                                            ? 'السماح للشركات والعملاء المعتمدين بالشراء بالآجل من متجري'
                                            : 'Allow authorized corporate buyers to purchase on credit',
                                        style: const TextStyle(fontSize: 11),
                                      ),
                                      value: _acceptB2b,
                                      activeColor: const Color(0xFF7E22CE),
                                      onChanged: (val) {
                                        setState(() => _acceptB2b = val);
                                        setModalState(() {});
                                      },
                                      secondary: const Icon(Icons.business_rounded, color: Color(0xFF7E22CE)),
                                      contentPadding: EdgeInsets.zero,
                                    ),
                                    if (_acceptB2b) ...[
                                      const SizedBox(height: 8),
                                      TextFormField(
                                        controller: _b2bApprovedBuyersController,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'أرقام هواتف أو معرفات العملاء المعتمدين (B2B)' : 'Approved B2B Buyer Phones / IDs',
                                          hintText: isRtl ? 'اتركه فارغاً لجميع المعتمدين من الإدارة، أو أدخل أرقاماً مفصولة بفواصل' : 'Leave empty for all admin-approved buyers or enter phones',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                          prefixIcon: const Icon(Icons.people_alt_outlined, color: Color(0xFF7E22CE)),
                                          fillColor: Colors.white,
                                          filled: true,
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              );
                            },
                          ),

                          Consumer(
                            builder: (bCtx, bRef, _) {
                              final settings = bRef.watch(globalSettingsProvider);
                              if (!settings.merchantAllowPartialCod) return const SizedBox.shrink();
                              return SwitchListTile(
                                title: Text(isRtl ? 'قبول دفع جزء من المبلغ والباقي عند الاستلام' : 'Accept Partial Payment + COD'),
                                subtitle: Text(isRtl ? 'السماح للمشترين بدفع عربون أو دفعة مقدمة وسداد الباقي نقداً عند استلام الشحنة' : 'Allow buyers to pay deposit and remaining cash upon delivery'),
                                value: _acceptPartialCod,
                                activeColor: Colors.teal,
                                onChanged: (val) {
                                  setState(() => _acceptPartialCod = val);
                                  setModalState(() {});
                                },
                                secondary: const Icon(Icons.pie_chart_outline_rounded, color: Colors.teal),
                              );
                            },
                          ),
                          const SizedBox(height: 24),

                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: () async {
                                Navigator.pop(modalCtx);
                                await _saveData();
                              },
                              icon: const Icon(Icons.check_circle_rounded, size: 20),
                              label: Text(
                                isRtl ? 'حفظ وتحديث بيانات الحسابات' : 'Save & Update Accounts',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF15803D),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildBadge(int completedCount, bool isRtl) {
    String badgeName = isRtl ? 'تاجر مبتدئ (برونزي)' : 'Bronze Merchant';
    Color badgeColor = Colors.brown.shade400;
    IconData badgeIcon = Icons.military_tech;

    if (completedCount >= 100) {
      badgeName = isRtl ? 'تاجر معتمد ممتاز (ذهبي)' : 'Gold Merchant';
      badgeColor = Colors.amber.shade600;
      badgeIcon = Icons.workspace_premium;
    } else if (completedCount >= 20) {
      badgeName = isRtl ? 'تاجر موثوق (فضي)' : 'Silver Merchant';
      badgeColor = Colors.blueGrey.shade300;
      badgeIcon = Icons.military_tech;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: badgeColor.withOpacity(0.1),
        border: Border.all(color: badgeColor.withOpacity(0.5), width: 1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(badgeIcon, color: badgeColor, size: 32),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(badgeName, style: TextStyle(color: badgeColor, fontWeight: FontWeight.bold, fontSize: 16)),
              Text(isRtl ? 'شارة الاعتمادية' : 'Reliability Badge', style: TextStyle(color: badgeColor.withOpacity(0.8), fontSize: 12)),
            ],
          )
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<AsyncValue<Map<String, dynamic>?>>(merchantStoreInfoProvider, (previous, next) {
      next.whenData((info) {
        if (info != null && mounted) {
          setState(() {
            if (info.containsKey('usd_rate_source')) {
              _usdRateSource = info['usd_rate_source']?.toString() ?? _usdRateSource;
            }
            final customR = info['store_usd_rate'] ?? info['usd_exchange_rate'];
            if (customR != null) {
              _customUsdRateController.text = (customR is num) ? customR.toStringAsFixed(0) : customR.toString();
            }
          });
        }
      });
    });

    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final title = isRtl ? 'إعدادات المتجر' : 'Store Settings';
    final statsAsync = ref.watch(merchantDashboardStatsProvider);

    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    // Top Stats Card
                    statsAsync.when(
                      data: (stats) {
                        final allOrders = stats['allOrders'] as List<dynamic>? ?? [];
                        int deliveredCount = 0;
                        int cancelledCount = 0;
                        int pendingCount = 0;
                        for (var o in allOrders) {
                          if (o['status'] == 'delivered') deliveredCount++;
                          else if (o['status'] == 'cancelled') cancelledCount++;
                          else pendingCount++;
                        }
                        
                        return Card(
                          elevation: 2,
                          color: Colors.white,
                          margin: const EdgeInsets.only(bottom: 24),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(child: _buildBadge(deliveredCount, isRtl)),
                                  ],
                                ),
                                const Divider(height: 32),
                                if (_createdAt != null)
                                  Padding(
                                    padding: const EdgeInsets.only(bottom: 16),
                                    child: Row(
                                      children: [
                                        const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                                        const SizedBox(width: 8),
                                        Text('${isRtl ? 'تاريخ التسجيل:' : 'Joined:'} ${DateFormat('yyyy-MM-dd').format(_createdAt!)}', style: const TextStyle(fontWeight: FontWeight.bold)),
                                      ],
                                    ),
                                  ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                                  children: [
                                    _buildStat(isRtl ? 'مكتملة' : 'Completed', deliveredCount, Colors.green),
                                    _buildStat(isRtl ? 'معلقة' : 'Pending', pendingCount, Colors.orange),
                                    _buildStat(isRtl ? 'ملغية' : 'Cancelled', cancelledCount, Colors.red),
                                  ],
                                )
                              ],
                            ),
                          ),
                        );
                      },
                      loading: () => const SizedBox.shrink(),
                      error: (_, __) => const SizedBox.shrink(),
                    ),

                    // Wallet & Payouts Card
                    InkWell(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const MerchantWalletScreen()),
                        );
                      },
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.all(20),
                        margin: const EdgeInsets.only(bottom: 24),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [Colors.blue.shade900, Colors.blue.shade600],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.blue.withOpacity(0.3),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.2),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.account_balance_wallet, color: Colors.white, size: 28),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    isRtl ? 'المحفظة وسحب الأرباح' : 'Wallet & Payouts',
                                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    isRtl ? 'عرض الرصيد القابل للسحب وتقديم طلبات سحب الأرباح' : 'View withdrawable balance & request payouts',
                                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                                  ),
                                ],
                              ),
                            ),
                            const Icon(Icons.arrow_forward_ios, color: Colors.white70, size: 16),
                          ],
                        ),
                      ),
                    ),

                    // Store Team & Staff Management Card (فريق العمل والموظفين)
                    InkWell(
                      onTap: () {
                        context.push(RouteNames.merchantTeam);
                      },
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.all(20),
                        margin: const EdgeInsets.only(bottom: 24),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF0F766E), Color(0xFF0D9488)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF0D9488).withOpacity(0.3),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.2),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.groups_rounded, color: Colors.white, size: 28),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        isRtl ? 'فريق العمل والموظفين' : 'Store Staff & Team',
                                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                                      ),
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withOpacity(0.25),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          isRtl ? 'صلاحيات' : 'RBAC',
                                          style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    isRtl
                                        ? 'إضافة موظفين، وتحديد صلاحيات الطلبات والمخزون والمحادثات'
                                        : 'Add staff and assign roles (Orders, Stock, Chat)',
                                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                                  ),
                                ],
                              ),
                            ),
                            const Icon(Icons.arrow_forward_ios, color: Colors.white70, size: 16),
                          ],
                        ),
                      ),
                    ),

                    // Direct Payment Accounts Button (يظهر فقط إذا كان مفعلاً من الإدارة)
                    if (_directPaymentEnabled) ...[
                      Container(
                        margin: const EdgeInsets.only(bottom: 24),
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0FDF4),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFF86EFAC), width: 1.5),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: Colors.green.shade100,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(Icons.verified_rounded, color: Color(0xFF15803D), size: 26),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        isRtl ? 'الدفع المباشر لحسابك البنكي (مفعّل)' : 'Direct Bank Payment (Enabled)',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF14532D)),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        isRtl
                                            ? 'تم تمكين استلام المبالغ مباشرة لحسابك. اضغط أدناه لضبط وتحديث حساباتك'
                                            : 'Direct payments are enabled. Click below to view and edit receiving accounts',
                                        style: TextStyle(fontSize: 11.5, color: Colors.green.shade800),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: () => _showMerchantBankAccountsModal(context, isRtl),
                                icon: const Icon(Icons.account_balance_rounded, size: 20),
                                label: Text(
                                  isRtl ? 'تفاصيل الحساب البنكي واستقبال المبالغ' : 'Bank & Payment Receiving Details',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF15803D),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  elevation: 2,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],

                    Text(isRtl ? 'المعلومات الأساسية' : 'Basic Info', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _nameController,
                      decoration: InputDecoration(labelText: isRtl ? 'اسم المتجر' : 'Store Name', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.store)),
                      validator: (val) => DataValidator.validateName(val, fieldName: isRtl ? 'اسم المتجر' : 'Store Name', isRtl: isRtl, minLength: 3),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _crController,
                      decoration: InputDecoration(labelText: isRtl ? 'رقم السجل التجاري' : 'CR Number', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.description)),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _phoneController,
                      decoration: InputDecoration(labelText: isRtl ? 'رقم الهاتف (إلزامي)' : 'Phone (Required)', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.phone)),
                      keyboardType: TextInputType.phone,
                      validator: (val) => DataValidator.validatePhone(val, isRtl: isRtl),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _addressController,
                      decoration: InputDecoration(labelText: isRtl ? 'العنوان (إلزامي)' : 'Address (Required)', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.location_on)),
                      maxLines: 2,
                      validator: (val) => DataValidator.validateAddress(val, isRtl: isRtl),
                    ),
                    const SizedBox(height: 32),

                    Text(
                      isRtl ? 'سياسة تسعير العملات وسعر الصرف' : 'Currency & Exchange Rate Policy',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF1E40AF)),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      isRtl
                          ? 'حدد كيفية تسعير المنتجات واحتساب المقابل بالجنيه السوداني بناءً على سعر الدولار'
                          : 'Choose how product prices are calculated in SDG based on USD rate',
                      style: const TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                    const SizedBox(height: 12),
                    Consumer(
                      builder: (context, ref, _) {
                        final platformRate = ref.watch(globalSettingsProvider).platformUsdRate;
                        return Column(
                          children: [
                            // Option 1: Platform Rate
                            InkWell(
                              onTap: () => setState(() => _usdRateSource = 'platform'),
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: _usdRateSource == 'platform' ? const Color(0xFFF0FDF4) : Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: _usdRateSource == 'platform' ? const Color(0xFF16A34A) : Colors.grey.shade300,
                                    width: _usdRateSource == 'platform' ? 1.8 : 1.0,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Radio<String>(
                                      value: 'platform',
                                      groupValue: _usdRateSource,
                                      activeColor: const Color(0xFF16A34A),
                                      onChanged: (val) {
                                        if (val != null) setState(() => _usdRateSource = val);
                                      },
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Text(
                                                isRtl ? 'سعر صرف منصة مقاييس المعتمد' : 'Official Platform USD Rate',
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                              ),
                                              const SizedBox(width: 8),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: const Color(0xFFDCFCE7),
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  isRtl ? 'موصى به' : 'Recommended',
                                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            isRtl
                                                ? 'يتم تحديث أسعار منتجاتك بالجنيه تلقائياً وفق السعر المعتمد للمنصة (1\$ = ${NumberFormat('#,##0').format(platformRate)} ج.س)'
                                                : 'Prices automatically sync with platform rate (1\$ = ${NumberFormat('#,##0').format(platformRate)} SDG)',
                                            style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),

                            // Option 2: Custom Rate
                            InkWell(
                              onTap: () => setState(() => _usdRateSource = 'custom'),
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: _usdRateSource == 'custom' ? const Color(0xFFEFF6FF) : Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: _usdRateSource == 'custom' ? const Color(0xFF2563EB) : Colors.grey.shade300,
                                    width: _usdRateSource == 'custom' ? 1.8 : 1.0,
                                  ),
                                ),
                                child: Column(
                                  children: [
                                    Row(
                                      children: [
                                        Radio<String>(
                                          value: 'custom',
                                          groupValue: _usdRateSource,
                                          activeColor: const Color(0xFF2563EB),
                                          onChanged: (val) {
                                            if (val != null) setState(() => _usdRateSource = val);
                                          },
                                        ),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                isRtl ? 'سعر صرف مخصص لمتجري' : 'Custom Store USD Rate',
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                isRtl
                                                    ? 'أدخل سعر صرف الدولار الذي ترغب في اعتماده لمتجرك بشكل مستقل'
                                                    : 'Enter your own custom USD exchange rate independently',
                                                style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (_usdRateSource == 'custom') ...[
                                      const SizedBox(height: 12),
                                      TextFormField(
                                        controller: _customUsdRateController,
                                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'سعر الدولار المخصص (ج.س)' : 'Custom USD Rate (SDG)',
                                          suffixText: isRtl ? 'ج.س' : 'SDG',
                                          prefixIcon: const Icon(Icons.tune_rounded, color: Color(0xFF2563EB)),
                                          border: const OutlineInputBorder(),
                                        ),
                                        validator: (val) {
                                          if (_usdRateSource == 'custom') {
                                            final numVal = double.tryParse(val ?? '');
                                            if (numVal == null || numVal <= 0) {
                                              return isRtl ? 'أدخل سعر صرف صحيح' : 'Enter valid rate';
                                            }
                                          }
                                          return null;
                                        },
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 14),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: _isLoading ? null : _saveExchangeRateOnly,
                                icon: const Icon(Icons.sync_rounded, size: 20),
                                label: Text(
                                  isRtl ? 'تطبيق وتوحيد سعر الصرف الآن ⚡' : 'Apply & Sync Exchange Rate Now ⚡',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF1E40AF),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 13),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                    const SizedBox(height: 32),
                    const SizedBox(height: 32),
                    
                    Text(isRtl ? 'إعدادات إضافية' : 'Additional Settings', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                    const SizedBox(height: 16),
                    const SizedBox(height: 16),
                    SwitchListTile(
                      title: Text(isRtl ? 'أصوات المحادثة' : 'Chat Sounds'),
                      subtitle: Text(isRtl ? 'تشغيل تنبيه صوتي عند تلقي رسالة جديدة' : 'Play sound when receiving a new message'),
                      value: _chatSoundsEnabled,
                      onChanged: (val) {
                        setState(() => _chatSoundsEnabled = val);
                        SharedPreferences.getInstance().then((prefs) => prefs.setBool('chat_sounds', val));
                      },
                      secondary: const Icon(Icons.notifications_active),
                    ),
                    const SizedBox(height: 16),
                    SwitchListTile(
                      title: Text(isRtl ? 'حالة المتجر (مفتوح لاستقبال الطلبات)' : 'Store Status (Open)'),
                      subtitle: Text(isRtl ? 'إذا قمت بإغلاقه لن يتمكن العملاء من الطلب' : 'If closed, customers cannot order'),
                      value: _isOpen,
                      onChanged: (val) => setState(() => _isOpen = val),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _workingHoursController,
                      decoration: InputDecoration(labelText: isRtl ? 'أوقات العمل' : 'Working Hours', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.access_time)),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _returnPolicyController,
                      decoration: InputDecoration(labelText: isRtl ? 'سياسة الاسترجاع / ملاحظات' : 'Return Policy / Notes', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.policy)),
                      maxLines: 3,
                    ),

                    const SizedBox(height: 32),
                    ElevatedButton(
                      onPressed: _saveData,
                      style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(50)),
                      child: Text(isRtl ? 'حفظ الإعدادات' : 'Save Settings', style: const TextStyle(fontSize: 18)),
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildStat(String label, int count, Color color) {
    return Column(
      children: [
        Text(count.toString(), style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color)),
        Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
      ],
    );
  }
}
