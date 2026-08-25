import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';
import 'package:magaiees_marketplace/features/admin/services/application/admin_services_provider.dart';

class AdminServicesScreen extends ConsumerStatefulWidget {
  const AdminServicesScreen({super.key});

  @override
  ConsumerState<AdminServicesScreen> createState() => _AdminServicesScreenState();
}

class _AdminServicesScreenState extends ConsumerState<AdminServicesScreen> {
  String _selectedCategory = 'All';
  
  final List<String> _categories = [
    'All',
    'engineering',
    'technicians',
    'labor',
    'logistics'
  ];

  final Color _primaryColor = const Color(0xFF0F172A);
  final Color _accentColor = const Color(0xFF1E40AF);

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(adminServicesProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final filteredProviders = _selectedCategory == 'All' 
        ? state.providers 
        : state.providers.where((p) {
            final json = p.toJson();
            return json['category_id'] == _selectedCategory;
          }).toList();

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          isRtl ? 'إدارة مقدمي الخدمات' : 'Manage Service Providers',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: _primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add_circle_outline, size: 28),
            onPressed: () => _showProviderDialog(context: context, isRtl: isRtl),
          ),
        ],
      ),
      body: state.isLoading && state.providers.isEmpty
        ? Center(child: CircularProgressIndicator(color: _accentColor))
        : Column(
            children: [
              _buildFilterSection(isRtl),
              if (state.error != null)
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Text(state.error!, style: const TextStyle(color: Colors.red)),
                ),
              if (state.isLoading && state.providers.isNotEmpty)
                LinearProgressIndicator(color: _accentColor),
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.all(16.0),
                  itemCount: filteredProviders.length,
                  itemBuilder: (context, index) {
                    final provider = filteredProviders[index];
                    return _buildProviderCard(context, provider, isRtl);
                  },
                ),
              ),
            ],
          ),
    );
  }

  Widget _buildFilterSection(bool isRtl) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 20.0),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            offset: const Offset(0, 4),
            blurRadius: 10,
          ),
        ],
      ),
      child: DropdownButtonFormField<String>(
        value: _selectedCategory,
        decoration: InputDecoration(
          labelText: isRtl ? 'تصفية حسب الفئة' : 'Filter by Category',
          prefixIcon: Icon(Icons.filter_list, color: _accentColor),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: _accentColor, width: 2),
          ),
          filled: true,
          fillColor: Colors.grey.shade50,
        ),
        items: _categories.map((cat) {
          return DropdownMenuItem(
            value: cat,
            child: Text(
              _getCategoryName(cat, isRtl),
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          );
        }).toList(),
        onChanged: (val) {
          if (val != null) {
            setState(() {
              _selectedCategory = val;
            });
          }
        },
      ),
    );
  }

  String _getCategoryName(String cat, bool isRtl) {
    if (!isRtl) return cat.toUpperCase();
    switch (cat) {
      case 'All': return 'الكل';
      case 'engineering': return 'هندسة';
      case 'technicians': return 'فنيين';
      case 'labor': return 'عمالة';
      case 'logistics': return 'لوجستيات';
      default: return cat;
    }
  }

  Widget _buildProviderCard(BuildContext context, ServiceProviderModel provider, bool isRtl) {
    final json = provider.toJson();
    final String? avatarUrl = json['avatar_url']?.toString();
    final String name = json['name']?.toString() ?? '';
    final String nameAr = json['name_ar']?.toString() ?? name;
    final String trade = json['trade']?.toString() ?? '';
    final String tradeAr = json['trade_ar']?.toString() ?? trade;
    final String categoryId = json['category_id']?.toString() ?? '';
    final double rating = double.tryParse(json['rating']?.toString() ?? '0') ?? 0.0;
    final int completedOrders = int.tryParse(json['completed_orders_count']?.toString() ?? '0') ?? 0;
    final String contractType = json['contract_type']?.toString() ?? 'none';

    return Card(
      elevation: 3,
      shadowColor: Colors.black12,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 35,
                  backgroundColor: _accentColor.withOpacity(0.1),
                  backgroundImage: avatarUrl != null && avatarUrl.isNotEmpty
                    ? NetworkImage(avatarUrl) 
                    : null,
                  child: (avatarUrl == null || avatarUrl.isEmpty)
                    ? Icon(Icons.person, size: 35, color: _accentColor) 
                    : null,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? nameAr : name,
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: _primaryColor,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${_getCategoryName(categoryId, isRtl)} | ${isRtl ? tradeAr : trade}',
                        style: TextStyle(color: Colors.grey.shade600),
                      ),
                      const SizedBox(height: 8),
                      _buildContractTypeBadge(contractType, isRtl),
                    ],
                  ),
                ),
                Column(
                  children: [
                    IconButton(
                      icon: Icon(Icons.edit, color: _accentColor),
                      onPressed: () => _showProviderDialog(
                        context: context, 
                        provider: provider, 
                        isRtl: isRtl
                      ),
                      constraints: const BoxConstraints(),
                      padding: EdgeInsets.zero,
                    ),
                    const SizedBox(height: 12),
                    IconButton(
                      icon: const Icon(Icons.delete_outline, color: Colors.red),
                      onPressed: () => _confirmDelete(context, provider, isRtl),
                      constraints: const BoxConstraints(),
                      padding: EdgeInsets.zero,
                    ),
                  ],
                )
              ],
            ),
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12.0),
              child: Divider(),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatItem(
                  icon: Icons.star_rounded, 
                  iconColor: Colors.amber, 
                  value: rating.toStringAsFixed(1), 
                  label: isRtl ? 'التقييم' : 'Rating'
                ),
                _buildStatItem(
                  icon: Icons.check_circle_outline, 
                  iconColor: Colors.green, 
                  value: completedOrders.toString(), 
                  label: isRtl ? 'طلبات منفذة' : 'Completed'
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContractTypeBadge(String type, bool isRtl) {
    String label = type;
    Color color = Colors.grey;
    if (type == 'none') {
      label = isRtl ? 'بدون عمولة' : 'No Commission';
      color = Colors.grey;
    } else if (type == 'percentage') {
      label = isRtl ? 'نسبة مئوية' : 'Percentage';
      color = Colors.purple;
    } else if (type == 'subscription') {
      label = isRtl ? 'اشتراك شهري' : 'Subscription';
      color = Colors.teal;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.5)),
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

  Widget _buildStatItem({required IconData icon, required Color iconColor, required String value, required String label}) {
    return Row(
      children: [
        Icon(icon, color: iconColor, size: 20),
        const SizedBox(width: 6),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            Text(label, style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
          ],
        ),
      ],
    );
  }

  Future<void> _showProviderDialog({
    required BuildContext context, 
    ServiceProviderModel? provider, 
    required bool isRtl,
  }) async {
    final json = provider?.toJson() ?? {};
    
    final nameController = TextEditingController(text: json['name']?.toString() ?? '');
    final nameArController = TextEditingController(text: json['name_ar']?.toString() ?? '');
    final tradeController = TextEditingController(text: json['trade']?.toString() ?? '');
    final tradeArController = TextEditingController(text: json['trade_ar']?.toString() ?? '');
    final ratingController = TextEditingController(text: json['rating']?.toString() ?? '0.0');
    final reviewsController = TextEditingController(text: json['reviews_count']?.toString() ?? '0');
    final experienceController = TextEditingController(text: json['experience_years']?.toString() ?? '0');
    final priceController = TextEditingController(text: json['price_estimate']?.toString() ?? '0.0');
    final phoneController = TextEditingController(text: json['phone']?.toString() ?? '');
    final locationController = TextEditingController(text: json['location']?.toString() ?? '');
    final locationArController = TextEditingController(text: json['location_ar']?.toString() ?? '');
    final contractValueController = TextEditingController(text: json['contract_value']?.toString() ?? '0.0');
    final completedOrdersController = TextEditingController(text: json['completed_orders_count']?.toString() ?? '0');
    
    String selectedCategory = json['category_id']?.toString() ?? 'engineering';
    if (!['engineering', 'technicians', 'labor', 'logistics'].contains(selectedCategory)) {
      selectedCategory = 'engineering';
    }
    String selectedContractType = json['contract_type']?.toString() ?? 'none';
    if (!['none', 'percentage', 'subscription'].contains(selectedContractType)) {
      selectedContractType = 'none';
    }
    
    Uint8List? avatarBytes;
    String? avatarExtension;
    final String? existingAvatarUrl = json['avatar_url']?.toString();
    final formKey = GlobalKey<FormState>();
    bool isSaving = false;

    await showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setStateDialog) {
            return Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              insetPadding: const EdgeInsets.all(16),
              child: Container(
                width: MediaQuery.of(context).size.width * 0.9,
                constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.85),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                      decoration: BoxDecoration(
                        color: _primaryColor,
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(20),
                          topRight: Radius.circular(20),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            provider == null 
                              ? (isRtl ? 'إضافة مقدم خدمة' : 'Add Service Provider')
                              : (isRtl ? 'تعديل بيانات مقدم الخدمة' : 'Edit Service Provider'),
                            style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close, color: Colors.white),
                            onPressed: () => Navigator.pop(context),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                          )
                        ],
                      ),
                    ),
                    Expanded(
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.all(24),
                        child: Form(
                          key: formKey,
                          child: Column(
                            children: [
                              GestureDetector(
                                onTap: () async {
                                  final picker = ImagePicker();
                                  final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                                  if (picked != null) {
                                    final bytes = await picked.readAsBytes();
                                    final ext = picked.name.split('.').last;
                                    setStateDialog(() {
                                      avatarBytes = bytes;
                                      avatarExtension = ext;
                                    });
                                  }
                                },
                                child: Stack(
                                  children: [
                                    CircleAvatar(
                                      radius: 50,
                                      backgroundColor: Colors.grey.shade200,
                                      backgroundImage: avatarBytes != null 
                                        ? MemoryImage(avatarBytes!) 
                                        : (existingAvatarUrl != null && existingAvatarUrl.isNotEmpty ? NetworkImage(existingAvatarUrl) : null) as ImageProvider?,
                                      child: avatarBytes == null && (existingAvatarUrl == null || existingAvatarUrl.isEmpty)
                                        ? Icon(Icons.add_a_photo, size: 40, color: Colors.grey.shade400) 
                                        : null,
                                    ),
                                    Positioned(
                                      bottom: 0,
                                      right: 0,
                                      child: Container(
                                        padding: const EdgeInsets.all(4),
                                        decoration: const BoxDecoration(
                                          color: Colors.white,
                                          shape: BoxShape.circle,
                                        ),
                                        child: Container(
                                          padding: const EdgeInsets.all(6),
                                          decoration: BoxDecoration(
                                            color: _accentColor,
                                            shape: BoxShape.circle,
                                          ),
                                          child: const Icon(Icons.camera_alt, size: 16, color: Colors.white),
                                        ),
                                      ),
                                    )
                                  ],
                                ),
                              ),
                              const SizedBox(height: 24),
                              
                              // Section: Basic Info
                              _buildSectionTitle(isRtl ? 'البيانات الأساسية' : 'Basic Info', Icons.info_outline),
                              Card(
                                elevation: 0,
                                color: Colors.grey.shade50,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  side: BorderSide(color: Colors.grey.shade200),
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    children: [
                                      Row(
                                        children: [
                                          Expanded(
                                            child: _buildTextField(
                                              controller: nameController,
                                              label: isRtl ? 'الاسم (إنجليزي)' : 'Name (English)',
                                              validator: (v) => v!.isEmpty ? 'Required' : null,
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: _buildTextField(
                                              controller: nameArController,
                                              label: isRtl ? 'الاسم (عربي)' : 'Name (Arabic)',
                                              validator: (v) => v!.isEmpty ? 'Required' : null,
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 12),
                                      Row(
                                        children: [
                                          Expanded(
                                            child: _buildTextField(
                                              controller: tradeController,
                                              label: isRtl ? 'المهنة (إنجليزي)' : 'Trade (English)',
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: _buildTextField(
                                              controller: tradeArController,
                                              label: isRtl ? 'المهنة (عربي)' : 'Trade (Arabic)',
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 12),
                                      DropdownButtonFormField<String>(
                                        value: selectedCategory,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'الفئة' : 'Category',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                          filled: true,
                                          fillColor: Colors.white,
                                        ),
                                        items: ['engineering', 'technicians', 'labor', 'logistics']
                                            .map((c) => DropdownMenuItem(value: c, child: Text(_getCategoryName(c, isRtl))))
                                            .toList(),
                                        onChanged: (v) {
                                          if (v != null) setStateDialog(() => selectedCategory = v);
                                        },
                                      ),
                                      const SizedBox(height: 12),
                                      _buildTextField(
                                        controller: phoneController,
                                        label: isRtl ? 'الهاتف' : 'Phone',
                                        keyboardType: TextInputType.phone,
                                      ),
                                      const SizedBox(height: 12),
                                      Row(
                                        children: [
                                          Expanded(
                                            child: _buildTextField(
                                              controller: locationController,
                                              label: isRtl ? 'الموقع (إنجليزي)' : 'Location (English)',
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: _buildTextField(
                                              controller: locationArController,
                                              label: isRtl ? 'الموقع (عربي)' : 'Location (Arabic)',
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ),

                              const SizedBox(height: 24),
                              
                              // Section: Finance & Commission
                              _buildSectionTitle(isRtl ? 'المالية والعمولة' : 'Finance & Commission', Icons.attach_money),
                              Card(
                                elevation: 0,
                                color: Colors.grey.shade50,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  side: BorderSide(color: Colors.grey.shade200),
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    children: [
                                      _buildTextField(
                                        controller: priceController,
                                        label: isRtl ? 'السعر التقديري للمتر/المقطوعية' : 'Price Estimate',
                                        keyboardType: TextInputType.number,
                                      ),
                                      const SizedBox(height: 12),
                                      DropdownButtonFormField<String>(
                                        value: selectedContractType,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'نوع التعاقد' : 'Contract Type',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                          filled: true,
                                          fillColor: Colors.white,
                                        ),
                                        items: [
                                          DropdownMenuItem(value: 'none', child: Text(isRtl ? 'بدون عمولة' : 'No Commission')),
                                          DropdownMenuItem(value: 'percentage', child: Text(isRtl ? 'نسبة مئوية' : 'Percentage')),
                                          DropdownMenuItem(value: 'subscription', child: Text(isRtl ? 'اشتراك شهري' : 'Subscription')),
                                        ],
                                        onChanged: (v) {
                                          if (v != null) setStateDialog(() => selectedContractType = v);
                                        },
                                      ),
                                      const SizedBox(height: 12),
                                      _buildTextField(
                                        controller: contractValueController,
                                        label: isRtl ? 'قيمة التعاقد / النسبة' : 'Contract Value / Percentage',
                                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                      ),
                                    ],
                                  ),
                                ),
                              ),

                              const SizedBox(height: 24),

                              // Section: Rating & Performance
                              _buildSectionTitle(isRtl ? 'التقييم والأداء' : 'Rating & Performance', Icons.trending_up),
                              Card(
                                elevation: 0,
                                color: Colors.grey.shade50,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  side: BorderSide(color: Colors.grey.shade200),
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    children: [
                                      Row(
                                        children: [
                                          Expanded(
                                            child: _buildTextField(
                                              controller: experienceController,
                                              label: isRtl ? 'سنوات الخبرة' : 'Experience Years',
                                              keyboardType: TextInputType.number,
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: _buildTextField(
                                              controller: completedOrdersController,
                                              label: isRtl ? 'الطلبات المنفذة' : 'Completed Orders',
                                              keyboardType: TextInputType.number,
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 12),
                                      Row(
                                        children: [
                                          Expanded(
                                            child: _buildTextField(
                                              controller: ratingController,
                                              label: isRtl ? 'التقييم (0-5)' : 'Rating (0-5)',
                                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: _buildTextField(
                                              controller: reviewsController,
                                              label: isRtl ? 'عدد المراجعات' : 'Reviews Count',
                                              keyboardType: TextInputType.number,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: const BorderRadius.only(
                          bottomLeft: Radius.circular(20),
                          bottomRight: Radius.circular(20),
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            offset: const Offset(0, -4),
                            blurRadius: 10,
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () => Navigator.pop(context),
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(fontSize: 16)),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: ElevatedButton(
                              onPressed: isSaving ? null : () async {
                                if (formKey.currentState!.validate()) {
                                  setStateDialog(() => isSaving = true);
                                  final newJson = Map<String, dynamic>.from(json);
                                  newJson['name'] = nameController.text;
                                  newJson['name_ar'] = nameArController.text;
                                  newJson['trade'] = tradeController.text;
                                  newJson['trade_ar'] = tradeArController.text;
                                  newJson['category_id'] = selectedCategory;
                                  newJson['phone'] = phoneController.text;
                                  newJson['location'] = locationController.text;
                                  newJson['location_ar'] = locationArController.text;
                                  newJson['experience_years'] = int.tryParse(experienceController.text) ?? 0;
                                  newJson['price_estimate'] = double.tryParse(priceController.text) ?? 0.0;
                                  newJson['rating'] = double.tryParse(ratingController.text) ?? 0.0;
                                  newJson['reviews_count'] = int.tryParse(reviewsController.text) ?? 0;
                                  newJson['contract_type'] = selectedContractType;
                                  newJson['contract_value'] = double.tryParse(contractValueController.text) ?? 0.0;
                                  newJson['completed_orders_count'] = int.tryParse(completedOrdersController.text) ?? 0;
                                  
                                  final newModel = ServiceProviderModel.fromJson(newJson);
            
                                  try {
                                    if (provider == null) {
                                      await ref.read(adminServicesProvider.notifier).addProvider(newModel, avatarBytes: avatarBytes, avatarExtension: avatarExtension);
                                    } else {
                                      await ref.read(adminServicesProvider.notifier).updateProvider(newModel, avatarBytes: avatarBytes, avatarExtension: avatarExtension);
                                    }
                                    if (context.mounted) Navigator.pop(context);
                                  } catch (e) {
                                    if (context.mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                        content: Text('Error: $e'),
                                        backgroundColor: Colors.red,
                                      ));
                                    }
                                    setStateDialog(() => isSaving = false);
                                  }
                                }
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: _accentColor,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                elevation: 0,
                              ),
                              child: isSaving 
                                ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) 
                                : Text(isRtl ? 'حفظ البيانات' : 'Save Changes', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      }
    );
  }

  Widget _buildSectionTitle(String title, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: Row(
        children: [
          Icon(icon, color: _accentColor, size: 20),
          const SizedBox(width: 8),
          Text(
            title,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: _primaryColor,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      keyboardType: keyboardType,
      validator: validator,
    );
  }

  void _confirmDelete(BuildContext context, ServiceProviderModel provider, bool isRtl) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              const Icon(Icons.warning_amber_rounded, color: Colors.red, size: 28),
              const SizedBox(width: 8),
              Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete', style: const TextStyle(fontWeight: FontWeight.bold)),
            ],
          ),
          content: Text(
            isRtl 
              ? 'هل أنت متأكد من حذف هذا المزود نهائياً؟ لا يمكن التراجع عن هذا الإجراء.' 
              : 'Are you sure you want to permanently delete this provider? This action cannot be undone.',
            style: const TextStyle(fontSize: 16),
          ),
          actionsPadding: const EdgeInsets.all(16),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(fontSize: 16)),
            ),
            ElevatedButton(
              onPressed: () async {
                Navigator.pop(context);
                try {
                  final id = provider.toJson()['id'];
                  if (id != null) {
                    await ref.read(adminServicesProvider.notifier).deleteProvider(id);
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                      content: Text('Error: $e'),
                      backgroundColor: Colors.red,
                    ));
                  }
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: Text(isRtl ? 'حذف' : 'Delete', style: const TextStyle(fontSize: 16)),
            ),
          ],
        );
      }
    );
  }
}
