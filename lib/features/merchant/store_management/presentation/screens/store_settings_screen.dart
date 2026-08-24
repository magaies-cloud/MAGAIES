import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/presentation/screens/merchant_dashboard_screen.dart';

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
  
  bool _isLoading = true;
  bool _isOpen = true;
  bool _chatSoundsEnabled = true;
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
        final data = {
          'name': _nameController.text,
          'cr_number': _crController.text,
          'contact_phone': _phoneController.text,
          'address': _addressController.text,
          'working_hours': _workingHoursController.text,
          'return_policy': _returnPolicyController.text,
          'is_open': _isOpen,
        };

        if (_storeId != null) {
          await Supabase.instance.client
              .from('stores')
              .update(data)
              .eq('id', _storeId!);
        } else {
          data['owner_id'] = user.id;
          final inserted = await Supabase.instance.client
              .from('stores')
              .insert(data)
              .select()
              .single();
          _storeId = inserted['id'];
        }

        if (mounted) {
          final isRtl = Directionality.of(context) == TextDirection.rtl;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(isRtl ? 'تم حفظ إعدادات المتجر بنجاح!' : 'Store settings saved successfully!'),
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
    super.dispose();
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

                    Text(isRtl ? 'المعلومات الأساسية' : 'Basic Info', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _nameController,
                      decoration: InputDecoration(labelText: isRtl ? 'اسم المتجر' : 'Store Name', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.store)),
                      validator: (val) => val == null || val.isEmpty ? (isRtl ? 'مطلوب' : 'Required') : null,
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
                      validator: (val) => val == null || val.isEmpty ? (isRtl ? 'يجب إدخال رقم الهاتف لاستلام الطلبات' : 'Phone is required') : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _addressController,
                      decoration: InputDecoration(labelText: isRtl ? 'العنوان (إلزامي)' : 'Address (Required)', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.location_on)),
                      maxLines: 2,
                      validator: (val) => val == null || val.isEmpty ? (isRtl ? 'يجب إدخال العنوان لاستلام الطلبات' : 'Address is required') : null,
                    ),
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
