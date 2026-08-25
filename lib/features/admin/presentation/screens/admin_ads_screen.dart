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

  final Color _primaryColor = const Color(0xFF1E40AF);
  final Color _darkColor = const Color(0xFF0F172A);

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
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: _primaryColor,
              onPrimary: Colors.white,
              onSurface: _darkColor,
            ),
          ),
          child: child!,
        );
      },
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
    String imageUrl = existingAd?.imageUrl ?? 'https://picsum.photos/seed/ad1/800/400';
    
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
                backgroundColor: Colors.white,
                surfaceTintColor: Colors.transparent,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                titlePadding: const EdgeInsets.all(24),
                contentPadding: const EdgeInsets.symmetric(horizontal: 24),
                title: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: _primaryColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(isEditing ? Icons.edit_note : Icons.campaign, color: _primaryColor),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Text(
                        isEditing ? (isRtl ? 'تعديل الإعلان' : 'Edit Ad') : (isRtl ? 'إنشاء إعلان جديد' : 'Create New Ad'),
                        style: TextStyle(
                          color: _darkColor,
                          fontWeight: FontWeight.bold,
                          fontSize: 20,
                        ),
                      ),
                    ),
                  ],
                ),
                content: SizedBox(
                  width: 550,
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildDialogTextField(
                          controller: titleArCtrl,
                          label: isRtl ? 'عنوان الإعلان الرئيسي' : 'Ad Title',
                          icon: Icons.title,
                        ),
                        const SizedBox(height: 16),
                        _buildDialogTextField(
                          controller: advertiserCtrl,
                          label: isRtl ? 'اسم المعلن / الشركة' : 'Advertiser / Company',
                          icon: Icons.business,
                        ),
                        const SizedBox(height: 16),
                        _buildDialogTextField(
                          controller: targetCtrl,
                          label: isRtl ? 'الكلمة الترويجية (مثل: عرض خاص)' : 'Promotional Tag',
                          icon: Icons.local_offer,
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: _buildDialogTextField(
                                controller: startDateCtrl,
                                label: isRtl ? 'تاريخ البداية' : 'Start Date',
                                icon: Icons.calendar_today,
                                readOnly: true,
                                onTap: () async {
                                  await _pickDate(ctx, startDateCtrl);
                                  setDlgState(() {});
                                },
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: _buildDialogTextField(
                                controller: endDateCtrl,
                                label: isRtl ? 'تاريخ النهاية' : 'End Date',
                                icon: Icons.event,
                                readOnly: true,
                                onTap: () async {
                                  await _pickDate(ctx, endDateCtrl);
                                  setDlgState(() {});
                                },
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),
                        Text(
                          isRtl ? 'صورة الإعلان' : 'Ad Image',
                          style: TextStyle(
                            color: _darkColor,
                            fontWeight: FontWeight.w600,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 8),
                        InkWell(
                          onTap: isSaving ? null : () async {
                            try {
                              final picker = ImagePicker();
                              final pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                              
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
                                  content: Text(isRtl ? 'فشل اختيار الصورة' : 'Failed to pick image'),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
                          },
                          borderRadius: BorderRadius.circular(16),
                          child: Container(
                            width: double.infinity,
                            height: 160,
                            decoration: BoxDecoration(
                              color: _primaryColor.withOpacity(0.05),
                              border: Border.all(color: _primaryColor.withOpacity(0.2), width: 2),
                              borderRadius: BorderRadius.circular(16),
                              image: localImageBytes != null 
                                ? DecorationImage(
                                    image: MemoryImage(localImageBytes!),
                                    fit: BoxFit.cover,
                                  )
                                : (imageUrl.isNotEmpty && !imageUrl.contains('unsplash') && !imageUrl.contains('picsum')
                                    ? DecorationImage(
                                        image: NetworkImage(imageUrl),
                                        fit: BoxFit.cover,
                                      )
                                    : null),
                            ),
                            child: localImageBytes == null && (imageUrl.isEmpty || imageUrl.contains('unsplash') || imageUrl.contains('picsum'))
                                ? Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          shape: BoxShape.circle,
                                          boxShadow: [
                                            BoxShadow(
                                              color: Colors.black.withOpacity(0.05),
                                              blurRadius: 10,
                                            )
                                          ]
                                        ),
                                        child: Icon(Icons.cloud_upload_outlined, size: 32, color: _primaryColor),
                                      ),
                                      const SizedBox(height: 12),
                                      Text(
                                        isRtl ? 'انقر هنا لرفع صورة البانر' : 'Click to upload banner image',
                                        style: TextStyle(color: _primaryColor, fontWeight: FontWeight.bold, fontSize: 14),
                                      ),
                                    ],
                                  )
                                : null,
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
                actionsPadding: const EdgeInsets.all(24),
                actions: [
                  TextButton(
                    onPressed: isSaving ? null : () => Navigator.pop(ctx),
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.grey.shade600,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                    child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                  ),
                  FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: _primaryColor,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: isSaving ? null : () async {
                      if (titleArCtrl.text.trim().isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('الرجاء إدخال عنوان الإعلان'), backgroundColor: Colors.red),
                        );
                        return;
                      }
                      
                      setDlgState(() => isSaving = true);

                      try {
                        if (localImageBytes != null && localExt != null) {
                          final fileName = 'ad_${DateTime.now().millisecondsSinceEpoch}.$localExt';
                          await Supabase.instance.client.storage.from('ads_bucket').uploadBinary(fileName, localImageBytes!);
                          imageUrl = Supabase.instance.client.storage.from('ads_bucket').getPublicUrl(fileName);
                        }

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
                    child: isSaving
                        ? const SizedBox(
                            width: 24,
                            height: 24,
                            child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                          )
                        : Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.check, size: 20),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'حفظ ونشر' : 'Save & Publish',
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                            ],
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

  Widget _buildDialogTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    bool readOnly = false,
    VoidCallback? onTap,
  }) {
    return TextField(
      controller: controller,
      readOnly: readOnly,
      onTap: onTap,
      style: TextStyle(color: _darkColor),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.grey.shade600),
        prefixIcon: Icon(icon, color: Colors.grey.shade500),
        filled: true,
        fillColor: Colors.grey.shade50,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: _primaryColor, width: 2),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final ads = ref.watch(adsProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // slate-50
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        title: Text(
          isRtl ? 'إدارة الإعلانات' : 'Ads Management',
          style: TextStyle(color: _darkColor, fontWeight: FontWeight.bold),
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(60),
          child: Container(
            alignment: isRtl ? Alignment.centerRight : Alignment.centerLeft,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
              ),
              child: TabBar(
                controller: _tabController,
                isScrollable: true,
                dividerColor: Colors.transparent,
                indicator: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 4, offset: const Offset(0, 2)),
                  ],
                ),
                indicatorSize: TabBarIndicatorSize.tab,
                indicatorPadding: const EdgeInsets.all(4),
                labelColor: _primaryColor,
                unselectedLabelColor: Colors.grey.shade600,
                labelStyle: const TextStyle(fontWeight: FontWeight.bold),
                tabAlignment: TabAlignment.start,
                tabs: [
                  Tab(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text(isRtl ? 'الإعلانات الحالية' : 'Active Ads'),
                    ),
                  ),
                  Tab(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text(isRtl ? 'الإعلانات المنتهية' : 'Ended Ads'),
                    ),
                  ),
                  Tab(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text(isRtl ? 'الكل' : 'All'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      body: ads.when(
        data: (adList) {
          final activeAds = adList.where((ad) => ad.isActive).toList();
          final endedAds = adList.where((ad) => !ad.isActive).toList();

          return TabBarView(
            controller: _tabController,
            children: [
              _buildAdsGrid(context, isRtl, activeAds),
              _buildAdsGrid(context, isRtl, endedAds),
              _buildAdsGrid(context, isRtl, adList),
            ],
          );
        },
        loading: () => Center(child: CircularProgressIndicator(color: _primaryColor)),
        error: (err, stack) => Center(child: Text('Error: $err', style: const TextStyle(color: Colors.red))),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showCreateOrEditAdDialog(isRtl: isRtl),
        icon: const Icon(Icons.add),
        label: Text(
          isRtl ? 'إضافة إعلان جديد' : 'Add New Ad',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: _primaryColor,
        foregroundColor: Colors.white,
        elevation: 4,
      ),
    );
  }

  Widget _buildAdsGrid(BuildContext context, bool isRtl, List<AdModel> filteredAds) {
    if (filteredAds.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 20),
                ],
              ),
              child: Icon(Icons.campaign_outlined, size: 64, color: Colors.grey.shade300),
            ),
            const SizedBox(height: 24),
            Text(
              isRtl ? 'لا توجد إعلانات في هذا القسم' : 'No ads found in this section',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 16, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        int crossAxisCount = 1;
        if (constraints.maxWidth > 1200) {
          crossAxisCount = 4;
        } else if (constraints.maxWidth > 900) {
          crossAxisCount = 3;
        } else if (constraints.maxWidth > 600) {
          crossAxisCount = 2;
        }

        return GridView.builder(
          padding: const EdgeInsets.all(24),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            childAspectRatio: 0.85,
            crossAxisSpacing: 24,
            mainAxisSpacing: 24,
          ),
          itemCount: filteredAds.length,
          itemBuilder: (context, index) {
            final ad = filteredAds[index];
            return _buildAdWidgetCard(ad, isRtl);
          },
        );
      },
    );
  }

  Widget _buildAdWidgetCard(AdModel ad, bool isRtl) {
    return Card(
      elevation: 0,
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image Section
          Expanded(
            flex: 3,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Image.network(
                  ad.imageUrl,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => Container(
                    color: Colors.grey.shade100,
                    child: const Icon(Icons.image_not_supported, color: Colors.grey),
                  ),
                ),
                Positioned(
                  top: 12,
                  left: isRtl ? null : 12,
                  right: isRtl ? 12 : null,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: ad.isActive ? Colors.green.shade600 : Colors.orange.shade600,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 4),
                      ],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          ad.isActive ? Icons.check_circle : Icons.pause_circle_filled,
                          color: Colors.white,
                          size: 14,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          ad.isActive ? (isRtl ? 'نشط' : 'Active') : (isRtl ? 'موقوف' : 'Paused'),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          // Details Section
          Expanded(
            flex: 4,
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    ad.title,
                    style: TextStyle(
                      color: _darkColor,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Icon(Icons.storefront, size: 16, color: Colors.grey.shade600),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          ad.company,
                          style: TextStyle(
                            color: Colors.grey.shade700,
                            fontSize: 13,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.date_range, size: 14, color: Colors.grey.shade600),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Text(
                            '${ad.startDate} - ${ad.endDate}',
                            style: TextStyle(
                              color: Colors.grey.shade700,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Divider(height: 1),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _buildActionButton(
                        icon: Icons.edit_outlined,
                        label: isRtl ? 'تعديل' : 'Edit',
                        color: _primaryColor,
                        onTap: () => _showCreateOrEditAdDialog(existingAd: ad, isRtl: isRtl),
                      ),
                      _buildActionButton(
                        icon: ad.isActive ? Icons.pause_circle_outline : Icons.play_circle_outline,
                        label: ad.isActive ? (isRtl ? 'إيقاف' : 'Pause') : (isRtl ? 'تنشيط' : 'Activate'),
                        color: Colors.orange.shade700,
                        onTap: () async {
                          try {
                            await ref.read(adsProvider.notifier).toggleStatus(ad.id);
                          } catch (e) {
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Error: $e'),
                                  backgroundColor: Colors.red,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          }
                        },
                      ),
                      _buildActionButton(
                        icon: Icons.delete_outline,
                        label: isRtl ? 'حذف' : 'Delete',
                        color: Colors.red.shade600,
                        onTap: () => _showDeleteConfirmation(ad, isRtl),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showDeleteConfirmation(AdModel ad, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.red.shade600),
            const SizedBox(width: 8),
            Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete', style: TextStyle(color: _darkColor)),
          ],
        ),
        content: Text(
          isRtl ? 'هل أنت متأكد من حذف الإعلان "${ad.title}"؟' : 'Are you sure you want to delete "${ad.title}"?',
          style: TextStyle(color: Colors.grey.shade700),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade600)),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600),
            onPressed: () async {
              try {
                await ref.read(adsProvider.notifier).deleteAd(ad.id);
                if (context.mounted) {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(isRtl ? 'تم حذف الإعلان بنجاح' : 'Ad deleted successfully'),
                      backgroundColor: Colors.green.shade600,
                    ),
                  );
                }
              } catch (e) {
                if (context.mounted) {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Error: $e'),
                      backgroundColor: Colors.red,
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                }
              }
            },
            child: Text(isRtl ? 'حذف' : 'Delete'),
          ),
        ],
      ),
    );
  }
}
