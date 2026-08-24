import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
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
              content: Text(isRtl ? 'ØªÙ… Ø­Ù Ø¸ Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ø§Ù„Ù…ØªØ¬Ø± Ø¨Ù†Ø¬Ø§Ø­!' : 'Store settings saved successfully!'),
              backgroundColor: Colors.green,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
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
    String badgeName = isRtl ? 'ØªØ§Ø¬Ø± Ù…Ø¨ØªØ¯Ø¦ (Ø¨Ø±ÙˆÙ†Ø²ÙŠ)' : 'Bronze Merchant';
    Color badgeColor = Colors.brown.shade400;
    IconData badgeIcon = Icons.military_tech;

    if (completedCount >= 100) {
      badgeName = isRtl ? 'ØªØ§Ø¬Ø± Ù…Ø¹ØªÙ…Ø¯ Ù…Ù…ØªØ§Ø² (Ø°Ù‡Ø¨ÙŠ)' : 'Gold Merchant';
      badgeColor = Colors.amber.shade600;
      badgeIcon = Icons.workspace_premium;
    } else if (completedCount >= 20) {
      badgeName = isRtl ? 'ØªØ§Ø¬Ø± Ù…ÙˆØ«ÙˆÙ‚ (Ù Ø¶ÙŠ)' : 'Silver Merchant';
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
              Text(isRtl ? 'Ø´Ø§Ø±Ø© Ø§Ù„Ø§Ø¹ØªÙ…Ø§Ø¯ÙŠØ©' : 'Reliability Badge', style: TextStyle(color: badgeColor.withOpacity(0.8), fontSize: 12)),
            ],
          )
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final title = isRtl ? 'Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ø§Ù„Ù…ØªØ¬Ø±' : 'Store Settings';
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
                                        Text('${isRtl ? 'ØªØ§Ø±ÙŠØ® Ø§Ù„ØªØ³Ø¬ÙŠÙ„:' : 'Joined:'} ${DateFormat('yyyy-MM-dd').format(_createdAt!)}', style: const TextStyle(fontWeight: FontWeight.bold)),
                                      ],
                                    ),
                                  ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                                  children: [
                                    _buildStat(isRtl ? 'Ù…ÙƒØªÙ…Ù„Ø©' : 'Completed', deliveredCount, Colors.green),
                                    _buildStat(isRtl ? 'Ù…Ø¹Ù„Ù‚Ø©' : 'Pending', pendingCount, Colors.orange),
                                    _buildStat(isRtl ? 'Ù…Ù„ØºÙŠØ©' : 'Cancelled', cancelledCount, Colors.red),
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

                    Text(isRtl ? 'Ø§Ù„Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ©' : 'Basic Info', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _nameController,
                      decoration: InputDecoration(labelText: isRtl ? 'Ø§Ø³Ù… Ø§Ù„Ù…ØªØ¬Ø±' : 'Store Name', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.store)),
                      validator: (val) => val == null || val.isEmpty ? (isRtl ? 'Ù…Ø·Ù„ÙˆØ¨' : 'Required') : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _crController,
                      decoration: InputDecoration(labelText: isRtl ? 'Ø±Ù‚Ù… Ø§Ù„Ø³Ø¬Ù„ Ø§Ù„ØªØ¬Ø§Ø±ÙŠ' : 'CR Number', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.description)),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _phoneController,
                      decoration: InputDecoration(labelText: isRtl ? 'Ø±Ù‚Ù… Ø§Ù„Ù‡Ø§ØªÙ  (Ø¥Ù„Ø²Ø§Ù…ÙŠ)' : 'Phone (Required)', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.phone)),
                      keyboardType: TextInputType.phone,
                      validator: (val) => val == null || val.isEmpty ? (isRtl ? 'ÙŠØ¬Ø¨ Ø¥Ø¯Ø®Ø§Ù„ Ø±Ù‚Ù… Ø§Ù„Ù‡Ø§ØªÙ  Ù„Ø§Ø³ØªÙ„Ø§Ù… Ø§Ù„Ø·Ù„Ø¨Ø§Øª' : 'Phone is required') : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _addressController,
                      decoration: InputDecoration(labelText: isRtl ? 'Ø§Ù„Ø¹Ù†ÙˆØ§Ù† (Ø¥Ù„Ø²Ø§Ù…ÙŠ)' : 'Address (Required)', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.location_on)),
                      maxLines: 2,
                      validator: (val) => val == null || val.isEmpty ? (isRtl ? 'ÙŠØ¬Ø¨ Ø¥Ø¯Ø®Ø§Ù„ Ø§Ù„Ø¹Ù†ÙˆØ§Ù† Ù„Ø§Ø³ØªÙ„Ø§Ù… Ø§Ù„Ø·Ù„Ø¨Ø§Øª' : 'Address is required') : null,
                    ),
                    const SizedBox(height: 32),
                    
                    Text(isRtl ? 'Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ø¥Ø¶Ø§Ù ÙŠØ©' : 'Additional Settings', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                    const SizedBox(height: 16),
                    SwitchListTile(
                      title: Text(isRtl ? 'Ø­Ø§Ù„Ø© Ø§Ù„Ù…ØªØ¬Ø± (Ù…Ù ØªÙˆØ­ Ù„Ø§Ø³ØªÙ‚Ø¨Ø§Ù„ Ø§Ù„Ø·Ù„Ø¨Ø§Øª)' : 'Store Status (Open)'),
                      subtitle: Text(isRtl ? 'Ø¥Ø°Ø§ Ù‚Ù…Øª Ø¨Ø¥ØºÙ„Ø§Ù‚Ù‡ Ù„Ù† ÙŠØªÙ…ÙƒÙ† Ø§Ù„Ø¹Ù…Ù„Ø§Ø¡ Ù…Ù† Ø§Ù„Ø·Ù„Ø¨' : 'If closed, customers cannot order'),
                      value: _isOpen,
                      onChanged: (val) => setState(() => _isOpen = val),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _workingHoursController,
                      decoration: InputDecoration(labelText: isRtl ? 'Ø£ÙˆÙ‚Ø§Øª Ø§Ù„Ø¹Ù…Ù„' : 'Working Hours', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.access_time)),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _returnPolicyController,
                      decoration: InputDecoration(labelText: isRtl ? 'Ø³ÙŠØ§Ø³Ø© Ø§Ù„Ø§Ø³ØªØ±Ø¬Ø§Ø¹ / Ù…Ù„Ø§Ø­Ø¸Ø§Øª' : 'Return Policy / Notes', border: const OutlineInputBorder(), prefixIcon: const Icon(Icons.policy)),
                      maxLines: 3,
                    ),

                    const SizedBox(height: 32),
                    ElevatedButton(
                      onPressed: _saveData,
                      style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(50)),
                      child: Text(isRtl ? 'Ø­Ù Ø¸ Ø§Ù„Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª' : 'Save Settings', style: const TextStyle(fontSize: 18)),
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
