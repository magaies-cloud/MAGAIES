import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';
import 'package:image_picker/image_picker.dart';

class AdminAdsScreen extends ConsumerStatefulWidget {
  const AdminAdsScreen({super.key});

  @override
  ConsumerState<AdminAdsScreen> createState() => _AdminAdsScreenState();
}

class _AdminAdsScreenState extends ConsumerState<AdminAdsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _pickDate(BuildContext context, TextEditingController controller) async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: now,
      firstDate: DateTime(2025),
      lastDate: DateTime(2030),
    );
    if (picked != null) {
      controller.text = DateFormat('yyyy-MM-dd').format(picked);
    }
  }

  void _showCreateOrEditAdDialog({AdModel? existingAd, required bool isRtl}) {
    final isEditing = existingAd != null;
    final titleArCtrl = TextEditingController(text: existingAd?.title ?? '');
    final advertiserCtrl = TextEditingController(text: existingAd?.company ?? '');
    final targetCtrl = TextEditingController(text: existingAd?.target ?? '');
    final startDateCtrl = TextEditingController(text: existingAd?.startDate ?? DateFormat('yyyy-MM-dd').format(DateTime.now()));
    final endDateCtrl = TextEditingController(text: existingAd?.endDate ?? DateFormat('yyyy-MM-dd').format(DateTime.now().add(const Duration(days: 30))));
    String imageUrl = existingAd?.imageUrl ?? 'https://images.unsplash.com/photo-1541888086225-b8296a297312?w=800';
    
    // For local instant preview
    Uint8List? localImageBytes; 
    String? localExt;
    bool isSaving = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setDlgState) {
            return Directionality(
              textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
              child: AlertDialog(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                title: Row(
                  children: [
                    Icon(isEditing ? Icons.edit : Icons.campaign, color: const Color(0xFF1E40AF)),
                    const SizedBox(width: 8),
                    Text(isEditing ? (isRtl ? 'تعديل الإعلان' : 'Edit Ad') : (isRtl ? 'إنشاء إعلان مدفوع جديد' : 'Create New Ad')),
                  ],
                ),
                content: SizedBox(
                  width: 520,
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        TextField(
                          controller: titleArCtrl,
                          decoration: InputDecoration(
                            labelText: isRtl ? 'عنوان الإعلان الرئيسي' : 'Ad Title',
                            border: const OutlineInputBorder(),
                            prefixIcon: const Icon(Icons.title),
                          ),
                        ),
                        const SizedBox(height: 14),
                        TextField(
                          controller: advertiserCtrl,
                          decoration: InputDecoration(
                            labelText: isRtl ? 'اسم المعلن / الشركة' : 'Advertiser / Company',
                            border: const OutlineInputBorder(),
                            prefixIcon: const Icon(Icons.business),
                          ),
                        ),
                        const SizedBox(height: 14),
                        TextField(
                          controller: targetCtrl,
                          decoration: InputDecoration(
                            labelText: isRtl ? 'الكلمة الترويجية (مثل: عرض خاص)' : 'Promotional Tag',
                            border: const OutlineInputBorder(),
                            prefixIcon: const Icon(Icons.local_offer),
                          ),
                        ),
                        const SizedBox(height: 14),
                        Row(
                          children: [
                            Expanded(
                              child: TextField(
                                controller: startDateCtrl,
                                readOnly: true,
                                onTap: () async {
                                  await _pickDate(ctx, startDateCtrl);
                                  setDlgState(() {});
                                },
                                decoration: InputDecoration(
                                  labelText: isRtl ? 'تاريخ البداية' : 'Start Date',
                                  border: const OutlineInputBorder(),
                                  suffixIcon: const Icon(Icons.calendar_today, size: 20),
                                ),
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: TextField(
                                controller: endDateCtrl,
                                readOnly: true,
                                onTap: () async {
                                  await _pickDate(ctx, endDateCtrl);
                                  setDlgState(() {});
                                },
                                decoration: InputDecoration(
                                  labelText: isRtl ? 'تاريخ النهاية' : 'End Date',
                                  border: const OutlineInputBorder(),
                                  suffixIcon: const Icon(Icons.calendar_today, size: 20),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        InkWell(
                          onTap: () async {
                            try {
                              final picker = ImagePicker();
                              final pickedFile = await picker.pickImage(source: ImageSource.gallery);
                              
                              if (pickedFile != null) {
                                final bytes = await pickedFile.readAsBytes();
                                final ext = pickedFile.name.split('.').last;
                                setDlgState(() {
                                  localImageBytes = bytes;
                                  localExt = ext;
                                });
                              }
                            } catch (e) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(isRtl ? 'فشل اختيار الصورة: $e' : 'Failed to pick image'),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
                          },
                          borderRadius: BorderRadius.circular(10),
                          child: Container(
                            width: double.infinity,
                            height: 120,
                            decoration: BoxDecoration(
                              color: const Color(0xFF1E40AF).withValues(alpha: 0.05),
                              border: Border.all(color: const Color(0xFF1E40AF).withValues(alpha: 0.3)),
                              borderRadius: BorderRadius.circular(10),
                              image: localImageBytes != null 
                                ? DecorationImage(
                                    image: MemoryImage(localImageBytes!),
                                    fit: BoxFit.cover,
                                  )
                                : (imageUrl.isNotEmpty && !imageUrl.contains('unsplash')
                                    ? DecorationImage(
                                        image: NetworkImage(imageUrl),
                                        fit: BoxFit.cover,
                                      )
                                    : null),
                            ),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.cloud_upload_outlined, size: 36, color: Color(0xFF1E40AF)),
                                const SizedBox(height: 6),
                                Text(
                                  isRtl ? 'انقر هنا لرفع أو تغيير صورة البانر' : 'Click to upload banner image',
                                  style: const TextStyle(color: Color(0xFF1E40AF), fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                actions: [
                  TextButton(
                    onPressed: isSaving ? null : () => Navigator.pop(ctx), 
                    child: Text(isRtl ? 'إلغاء' : 'Cancel')
                  ),
                  FilledButton.icon(
                    style: FilledButton.styleFrom(backgroundColor: const Color(0xFF1E40AF)),
                    onPressed: isSaving ? null : () async {
                      if (titleArCtrl.text.trim().isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('الرجاء إدخال عنوان الإعلان'), backgroundColor: Colors.red),
                        );
                        return;
                      }
                      
                      setDlgState(() => isSaving = true);

                      try {
                        // 1. Upload image if a new one was selected
                        if (localImageBytes != null && localExt != null) {
                          final fileName = 'ad_${DateTime.now().millisecondsSinceEpoch}.$localExt';
                          await Supabase.instance.client.storage.from('ads_bucket').uploadBinary(fileName, localImageBytes!);
                          imageUrl = Supabase.instance.client.storage.from('ads_bucket').getPublicUrl(fileName);
                        }

                        // 2. Save the ad
                        if (isEditing) {
                          await ref.read(adsProvider.notifier).updateAd(
                            existingAd.copyWith(
                              title: titleArCtrl.text.trim(),
                              company: advertiserCtrl.text.trim(),
                              target: targetCtrl.text.trim(),
                              startDate: startDateCtrl.text,
                              endDate: endDateCtrl.text,
                              imageUrl: imageUrl,
                            )
                          );
                        } else {
                          await ref.read(adsProvider.notifier).addAd(
                            AdModel(
                              id: 'ad_${DateTime.now().millisecondsSinceEpoch}',
                              title: titleArCtrl.text.trim(),
                              company: advertiserCtrl.text.trim(),
                              target: targetCtrl.text.trim().isNotEmpty ? targetCtrl.text.trim() : 'عرض خاص',
                              isActive: true,
                              startDate: startDateCtrl.text,
                              endDate: endDateCtrl.text,
                              imageUrl: imageUrl,
                            )
                          );
                        }
                        
                        if (ctx.mounted) Navigator.pop(ctx);
                      } catch (e) {
                        setDlgState(() => isSaving = false);
                        if (ctx.mounted) {
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            SnackBar(content: Text('خطأ أثناء الحفظ: $e'), backgroundColor: Colors.red),
                          );
                        }
                      }
                    },
                    icon: isSaving 
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) 
                      : const Icon(Icons.check),
                    label: Text(isRtl ? 'حفظ ونشر' : 'Save & Publish'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final ads = ref.watch(adsProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(isRtl ? 'إدارة الإعلانات والترويج' : 'Ads & Promotions'),
        bottom: TabBar(
          controller: _tabController,
          tabs: [
            Tab(text: isRtl ? 'الإعلانات الحالية' : 'Active Ads'),
            Tab(text: isRtl ? 'الإعلانات المنتهية' : 'Ended Ads'),
            Tab(text: isRtl ? 'الكل' : 'All'),
          ],
        ),
      ),
      body: ads.when(
        data: (adList) {
          final activeAds = adList.where((ad) => ad.isActive).toList();
          final endedAds = adList.where((ad) => !ad.isActive).toList();

          return TabBarView(
            controller: _tabController,
            children: [
              _buildAdsTable(context, isRtl, activeAds),
              _buildAdsTable(context, isRtl, endedAds),
              _buildAdsTable(context, isRtl, adList),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error: $err')),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showCreateOrEditAdDialog(isRtl: isRtl),
        icon: const Icon(Icons.add),
        label: Text(isRtl ? 'إضافة إعلان جديد' : 'Add New Ad'),
        backgroundColor: const Color(0xFF1E40AF),
        foregroundColor: Colors.white,
      ),
    );
  }

  Widget _buildAdsTable(BuildContext context, bool isRtl, List<AdModel> filteredAds) {
    if (filteredAds.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.campaign_outlined, size: 54, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              isRtl ? 'لا توجد إعلانات نشطة' : 'No active ads',
              style: TextStyle(color: Colors.grey.shade500, fontSize: 14),
            ),
          ],
        ),
      );
    }

    return Scrollbar(
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: SingleChildScrollView(
          child: ConstrainedBox(
            constraints: BoxConstraints(minWidth: MediaQuery.of(context).size.width - 140),
            child: DataTable(
              headingRowColor: WidgetStatePropertyAll(Colors.grey.shade50),
              headingTextStyle: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey.shade800, fontSize: 13),
              columnSpacing: 20,
              horizontalMargin: 16,
              columns: [
                DataColumn(label: Text(isRtl ? 'صورة البانر' : 'Image')),
                DataColumn(label: Text(isRtl ? 'عنوان الإعلان' : 'Ad Title')),
                DataColumn(label: Text(isRtl ? 'المعلن / المتجر' : 'Advertiser')),
                DataColumn(label: Text(isRtl ? 'الفترة الزمنية' : 'Duration')),
                DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                DataColumn(label: Text(isRtl ? 'الإجراءات' : 'Actions')),
              ],
              rows: filteredAds.map((ad) {
                return DataRow(cells: [
                  DataCell(
                    Container(
                      margin: const EdgeInsets.symmetric(vertical: 4),
                      width: 80,
                      height: 40,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(6),
                        image: DecorationImage(image: NetworkImage(ad.imageUrl), fit: BoxFit.cover),
                      ),
                    ),
                  ),
                  DataCell(ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 240),
                    child: Text(ad.title, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.bold)),
                  )),
                  DataCell(Text(ad.company)),
                  DataCell(Text('${ad.startDate} → ${ad.endDate}', style: TextStyle(color: Colors.grey.shade700, fontSize: 12))),
                  DataCell(
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: ad.isActive ? Colors.green.withValues(alpha: 0.12) : Colors.orange.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: ad.isActive ? Colors.green.withValues(alpha: 0.4) : Colors.orange.withValues(alpha: 0.4)),
                      ),
                      child: Text(
                        ad.isActive ? 'نشط (ظاهر)' : 'موقوف',
                        style: TextStyle(
                          color: ad.isActive ? Colors.green : Colors.orange.shade800,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                  DataCell(
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: const Icon(Icons.edit, color: Color(0xFF1E40AF), size: 18),
                          onPressed: () => _showCreateOrEditAdDialog(existingAd: ad, isRtl: isRtl),
                          tooltip: isRtl ? 'تعديل الإعلان' : 'Edit',
                        ),
                        IconButton(
                          icon: Icon(ad.isActive ? Icons.pause_circle_outline : Icons.play_circle_outline, color: Colors.orange, size: 20),
                          onPressed: () => ref.read(adsProvider.notifier).toggleStatus(ad.id),
                          tooltip: isRtl ? 'إيقاف / تنشيط' : 'Toggle',
                        ),
                        IconButton(
                          icon: const Icon(Icons.delete_outline, color: Colors.red, size: 18),
                          onPressed: () {
                             ref.read(adsProvider.notifier).deleteAd(ad.id);
                             ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم الحذف من المنصة'), backgroundColor: Colors.red));
                          },
                          tooltip: isRtl ? 'حذف الإعلان' : 'Delete',
                        ),
                      ],
                    ),
                  ),
                ]);
              }).toList(),
            ),
          ),
        ),
      ),
    );
  }
}
