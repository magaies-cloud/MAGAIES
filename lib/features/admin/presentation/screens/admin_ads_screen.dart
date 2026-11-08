import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';

class AdminAdsScreen extends ConsumerStatefulWidget {
  const AdminAdsScreen({super.key});

  @override
  ConsumerState<AdminAdsScreen> createState() => _AdminAdsScreenState();
}

class _AdminAdsScreenState extends ConsumerState<AdminAdsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isTableView = true;
  final TextEditingController _searchCtrl = TextEditingController();
  String _searchQuery = '';

  final Color _primaryColor = const Color(0xFF1E40AF);
  final Color _darkColor = const Color(0xFF0F172A);
  final Color _accentColor = const Color(0xFF3B82F6);

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
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
    final priceCtrl = TextEditingController(text: existingAd?.price.toString() ?? '0.0');
    String imageUrl = existingAd?.imageUrl ?? '';
    
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
                backgroundColor: Theme.of(context).colorScheme.surface,
                surfaceTintColor: Colors.transparent,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                titlePadding: const EdgeInsets.all(24),
                contentPadding: const EdgeInsets.symmetric(horizontal: 24),
                title: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [_primaryColor, _accentColor],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(color: _primaryColor.withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 4)),
                        ],
                      ),
                      child: Icon(isEditing ? Icons.edit_note : Icons.campaign_rounded, color: Colors.white, size: 28),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isEditing ? (isRtl ? 'تعديل بيانات الإعلان' : 'Edit Advertisement') : (isRtl ? 'إطلاق إعلان جديد' : 'Launch New Campaign'),
                            style: TextStyle(color: _darkColor, fontWeight: FontWeight.w900, fontSize: 22),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isRtl ? 'أدخل تفاصيل الحملة الإعلانية' : 'Enter campaign details below',
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                content: SizedBox(
                  width: 600,
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildDialogTextField(
                          controller: titleArCtrl,
                          label: isRtl ? 'عنوان الإعلان الرئيسي' : 'Ad Title',
                          icon: Icons.title_rounded,
                        ),
                        const SizedBox(height: 16),
                        _buildDialogTextField(
                          controller: advertiserCtrl,
                          label: isRtl ? 'اسم المعلن / الشركة' : 'Advertiser / Company',
                          icon: Icons.business_rounded,
                        ),
                        const SizedBox(height: 16),
                        _buildDialogTextField(
                          controller: targetCtrl,
                          label: isRtl ? 'الكلمة الترويجية (مثل: عرض خاص)' : 'Promotional Tag',
                          icon: Icons.local_offer_rounded,
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: _buildDialogTextField(
                                controller: startDateCtrl,
                                label: isRtl ? 'تاريخ البداية' : 'Start Date',
                                icon: Icons.calendar_today_rounded,
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
                                icon: Icons.event_rounded,
                                readOnly: true,
                                onTap: () async {
                                  await _pickDate(ctx, endDateCtrl);
                                  setDlgState(() {});
                                },
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        _buildDialogTextField(
                          controller: priceCtrl,
                          label: isRtl ? 'المبلغ المدفوع (جنيه)' : 'Paid Amount (SDG)',
                          icon: Icons.attach_money_rounded,
                          keyboardType: TextInputType.number,
                        ),
                        const SizedBox(height: 24),
                        Text(
                          isRtl ? 'بانر الإعلان (الصورة)' : 'Campaign Banner Image',
                          style: TextStyle(color: _darkColor, fontWeight: FontWeight.w800, fontSize: 15),
                        ),
                        const SizedBox(height: 12),
                        InkWell(
                          onTap: isSaving ? null : () async {
                            try {
                              final picker = ImagePicker();
                              final pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 70, maxWidth: 1200);
                              
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
                          borderRadius: BorderRadius.circular(20),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 300),
                            width: double.infinity,
                            height: 210,
                            decoration: BoxDecoration(
                              color: _primaryColor.withValues(alpha: 0.04),
                              border: Border.all(
                                color: (localImageBytes != null || (imageUrl.isNotEmpty && !imageUrl.contains("unsplash") && !imageUrl.contains("picsum"))) 
                                    ? Colors.transparent 
                                    : _primaryColor.withValues(alpha: 0.3), 
                                width: 2,
                                style: BorderStyle.solid,
                              ),
                              borderRadius: BorderRadius.circular(20),
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
                                            BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 15, offset: const Offset(0, 5))
                                          ]
                                        ),
                                        child: Icon(Icons.cloud_upload_rounded, size: 32, color: _accentColor),
                                      ),
                                      const SizedBox(height: 10),
                                      Text(
                                        isRtl ? 'اضغط هنا لرفع تصميم البانر' : 'Click to upload banner artwork',
                                        style: TextStyle(color: _primaryColor, fontWeight: FontWeight.w800, fontSize: 14.5),
                                      ),
                                      const SizedBox(height: 6),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.aspect_ratio_rounded, size: 14, color: Colors.grey.shade600),
                                          const SizedBox(width: 4),
                                          Text(
                                            isRtl
                                                ? 'المقاس المثالي: 1200 × 500 بكسل (أو 1080 × 450) • نسبة 16:7'
                                                : 'Optimal Size: 1200 × 500 px (or 1080 × 450) • 16:7',
                                            style: TextStyle(
                                              color: Colors.grey.shade700,
                                              fontSize: 12,
                                              fontWeight: FontWeight.w600,
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: _primaryColor.withValues(alpha: 0.08),
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(
                                              isRtl ? 'الصيغ المدعومة: PNG, JPG, WebP' : 'Formats: PNG, JPG, WebP',
                                              style: TextStyle(
                                                color: _primaryColor,
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFF059669).withValues(alpha: 0.08),
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(
                                              isRtl ? 'أقصى حجم: 2 ميغابايت' : 'Max Size: 2 MB',
                                              style: const TextStyle(
                                                color: Color(0xFF059669),
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  )
                                : Container(
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(20),
                                      gradient: LinearGradient(
                                        colors: [Colors.black.withOpacity(0.5), Colors.transparent],
                                        begin: Alignment.bottomCenter,
                                        end: Alignment.center,
                                      ),
                                    ),
                                    alignment: Alignment.bottomCenter,
                                    padding: const EdgeInsets.all(12),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Icon(Icons.edit, color: Colors.white, size: 16),
                                        const SizedBox(width: 6),
                                        Text(
                                          isRtl ? 'تغيير الصورة' : 'Change Image',
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                  ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
                actionsPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                actions: [
                  TextButton(
                    onPressed: isSaving ? null : () => Navigator.pop(ctx),
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.grey.shade600,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: Text(isRtl ? 'إلغاء الأمر' : 'Cancel', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                  FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: _primaryColor,
                      padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 4,
                      shadowColor: _primaryColor.withOpacity(0.5),
                    ),
                    onPressed: isSaving ? null : () async {
                      final titleError = DataValidator.validateAdTitle(titleArCtrl.text, isRtl: isRtl);
                      if (titleError != null) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(titleError), backgroundColor: Colors.red),
                        );
                        return;
                      }

                      final companyError = DataValidator.validateName(
                        advertiserCtrl.text,
                        fieldName: isRtl ? 'اسم المعلن / الشركة' : 'Advertiser / Company',
                        isRtl: isRtl,
                        minLength: 2,
                      );
                      if (companyError != null) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(companyError), backgroundColor: Colors.red),
                        );
                        return;
                      }

                      if (localImageBytes == null && (imageUrl.isEmpty || imageUrl.contains('unsplash') || imageUrl.contains('picsum'))) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl
                                ? '⚠️ يرجى رفع تصميم وبانر حقيقي للإعلان'
                                : 'Please upload a real banner image for the ad'),
                            backgroundColor: Colors.amber.shade900,
                          ),
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
                              price: double.tryParse(priceCtrl.text) ?? 0.0,
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
                              price: double.tryParse(priceCtrl.text) ?? 0.0,
                            )
                          );
                        }
                        
                        if (ctx.mounted) Navigator.pop(ctx);
                      } catch (e) {
                        setDlgState(() => isSaving = false);
                        if (ctx.mounted) {
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
                          );
                        }
                      }
                    },
                    child: isSaving
                        ? const SizedBox(
                            width: 24,
                            height: 24,
                            child: CircularProgressIndicator(strokeWidth: 3, color: Colors.white),
                          )
                        : Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.rocket_launch_rounded, size: 20),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'حفظ ونشر الحملة' : 'Launch Campaign',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
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
    TextInputType? keyboardType,
  }) {
    return TextField(
      controller: controller,
      readOnly: readOnly,
      onTap: onTap,
      keyboardType: keyboardType,
      style: TextStyle(color: _darkColor, fontWeight: FontWeight.w600),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.normal),
        prefixIcon: Icon(icon, color: _primaryColor.withOpacity(0.7)),
        filled: true,
        fillColor: Colors.grey.shade50,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: _primaryColor, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      ),
    );
  }


  void _showBannerPreviewDialog(AdModel ad, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 850),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 30, offset: const Offset(0, 10)),
            ],
          ),
          clipBehavior: Clip.antiAlias,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header bar
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [_primaryColor, _accentColor],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.campaign_rounded, color: Colors.white, size: 24),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            ad.title,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                          Text(
                            '${ad.company} • ${ad.target}',
                            style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: Colors.white),
                      onPressed: () => Navigator.pop(ctx),
                      tooltip: isRtl ? 'إغلاق' : 'Close',
                    ),
                  ],
                ),
              ),
              // Image container with InteractiveViewer
              Container(
                color: const Color(0xFF0F172A),
                constraints: const BoxConstraints(maxHeight: 480),
                child: Center(
                  child: InteractiveViewer(
                    maxScale: 3.0,
                    child: Image.network(
                      ad.imageUrl,
                      fit: BoxFit.contain,
                      errorBuilder: (c, e, s) => Container(
                        height: 250,
                        color: Colors.grey.shade100,
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.broken_image_rounded, size: 48, color: Colors.grey.shade400),
                              const SizedBox(height: 8),
                              Text(isRtl ? 'تعذر تحميل الصورة' : 'Image failed to load', style: TextStyle(color: Colors.grey.shade600)),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              // Footer bar with metadata
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                color: const Color(0xFFF8FAFC),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.calendar_today_rounded, size: 15, color: Colors.grey.shade600),
                        const SizedBox(width: 8),
                        Text(
                          '${ad.startDate}   ➜   ${ad.endDate}',
                          style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ],
                    ),
                    Text(
                      '${NumberFormat('#,##0', 'en').format(ad.price)} ${isRtl ? 'جنيه سوداني' : 'SDG'}',
                      style: const TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.w900, fontSize: 15),
                    ),
                  ],
                ),
              ),
            ],
          ),
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
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        titlePadding: const EdgeInsets.all(24),
        contentPadding: const EdgeInsets.symmetric(horizontal: 24),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: Colors.red.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
              child: Icon(Icons.warning_rounded, color: Colors.red.shade600, size: 28),
            ),
            const SizedBox(width: 16),
            Text(isRtl ? 'حذف الحملة!' : 'Delete Campaign!', style: TextStyle(color: _darkColor, fontWeight: FontWeight.w900)),
          ],
        ),
        content: Text(
          isRtl ? 'هل أنت متأكد من حذف الإعلان "${ad.title}"؟ لا يمكن التراجع عن هذا الإجراء.' : 'Are you sure you want to delete "${ad.title}"? This cannot be undone.',
          style: TextStyle(color: Colors.grey.shade700, fontSize: 15, fontWeight: FontWeight.w500),
        ),
        actionsPadding: const EdgeInsets.all(24),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            style: TextButton.styleFrom(
              foregroundColor: Colors.grey.shade600,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            ),
            child: Text(isRtl ? 'تراجع' : 'Cancel', style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: Colors.red.shade600,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () async {
              try {
                await ref.read(adsProvider.notifier).deleteAd(ad.id);
                if (context.mounted) {
                  Navigator.pop(ctx);
                }
              } catch (e) {
                // handle
              }
            },
            child: Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete', style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final ads = ref.watch(adsProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: ads.when(
        data: (adList) {
          final query = _searchQuery.toLowerCase().trim();
          List<AdModel> filterList(List<AdModel> list) {
            if (query.isEmpty) return list;
            return list.where((ad) =>
              ad.title.toLowerCase().contains(query) ||
              ad.company.toLowerCase().contains(query) ||
              ad.target.toLowerCase().contains(query)
            ).toList();
          }

          final activeAds = filterList(adList.where((ad) => ad.isActive).toList());
          final endedAds = filterList(adList.where((ad) => !ad.isActive).toList());
          final allAds = filterList(adList);

          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Dashboard Header
              Container(
                padding: const EdgeInsets.fromLTRB(32, 36, 32, 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  colors: [_primaryColor, _accentColor],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                borderRadius: BorderRadius.circular(14),
                                boxShadow: [
                                  BoxShadow(color: _primaryColor.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4)),
                                ],
                              ),
                              child: const Icon(Icons.campaign_rounded, color: Colors.white, size: 28),
                            ),
                            const SizedBox(width: 16),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  isRtl ? 'إدارة الإعلانات والحملات' : 'Ads & Campaigns Manager',
                                  style: TextStyle(
                                    fontSize: 26,
                                    fontWeight: FontWeight.w900,
                                    color: _darkColor,
                                    letterSpacing: -0.5,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  isRtl 
                                      ? 'قم بإدارة وترويج الحملات التسويقية للشركات والتطبيق بكل سهولة.' 
                                      : 'Manage and promote marketing campaigns efficiently.',
                                  style: TextStyle(color: Colors.grey.shade600, fontSize: 14, fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                          ],
                        ),
                        FilledButton.icon(
                          onPressed: () => _showCreateOrEditAdDialog(isRtl: isRtl),
                          icon: const Icon(Icons.add_rounded, size: 22),
                          label: Text(
                            isRtl ? 'إنشاء إعلان جديد' : 'New Campaign',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          style: FilledButton.styleFrom(
                            backgroundColor: _primaryColor,
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            elevation: 4,
                            shadowColor: _primaryColor.withOpacity(0.4),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    // Quick KPI Row
                    Row(
                      children: [
                        _buildHeaderKpi(isRtl ? 'إجمالي الحملات' : 'Total Campaigns', adList.length.toString(), Icons.layers_rounded, Colors.purple),
                        const SizedBox(width: 24),
                        _buildHeaderKpi(isRtl ? 'الحملات النشطة' : 'Active Ads', adList.where((ad) => ad.isActive).length.toString(), Icons.local_fire_department_rounded, Colors.orange),
                        const SizedBox(width: 24),
                        _buildHeaderKpi(isRtl ? 'الحملات المتوقفة' : 'Ended Ads', adList.where((ad) => !ad.isActive).length.toString(), Icons.history_rounded, Colors.grey.shade600),
                      ],
                    ),
                    const SizedBox(height: 24),
                    // Tabs & View Switcher Toolbar
                    LayoutBuilder(
                      builder: (context, constraints) {
                        final isWide = constraints.maxWidth > 950;

                        final tabsWidget = Container(
                          padding: const EdgeInsets.all(5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: TabBar(
                            controller: _tabController,
                            isScrollable: true,
                            dividerColor: Colors.transparent,
                            indicator: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(10),
                              boxShadow: [
                                BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 8, offset: const Offset(0, 3)),
                              ],
                            ),
                            indicatorSize: TabBarIndicatorSize.tab,
                            labelColor: _primaryColor,
                            unselectedLabelColor: Colors.grey.shade600,
                            labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                            unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                            tabAlignment: TabAlignment.start,
                            tabs: [
                              Tab(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.play_circle_filled_rounded, size: 17),
                                      const SizedBox(width: 8),
                                      Text(isRtl ? 'الحملات النشطة (${activeAds.length})' : 'Active (${activeAds.length})'),
                                    ],
                                  ),
                                ),
                              ),
                              Tab(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.stop_circle_rounded, size: 17),
                                      const SizedBox(width: 8),
                                      Text(isRtl ? 'الحملات المتوقفة (${endedAds.length})' : 'Paused (${endedAds.length})'),
                                    ],
                                  ),
                                ),
                              ),
                              Tab(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.all_inclusive_rounded, size: 17),
                                      const SizedBox(width: 8),
                                      Text(isRtl ? 'جميع الإعلانات (${allAds.length})' : 'All Ads (${allAds.length})'),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        );

                        final searchWidget = SizedBox(
                          width: 240,
                          height: 40,
                          child: TextField(
                            controller: _searchCtrl,
                            onChanged: (v) => setState(() => _searchQuery = v),
                            style: TextStyle(fontSize: 13, color: _darkColor, fontWeight: FontWeight.w600),
                            decoration: InputDecoration(
                              hintText: isRtl ? 'بحث في الإعلانات...' : 'Search ads...',
                              hintStyle: TextStyle(fontSize: 12.5, color: Colors.grey.shade500),
                              prefixIcon: Icon(Icons.search_rounded, size: 18, color: Colors.grey.shade500),
                              suffixIcon: _searchQuery.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.clear_rounded, size: 16),
                                      onPressed: () {
                                        _searchCtrl.clear();
                                        setState(() => _searchQuery = '');
                                      },
                                    )
                                  : null,
                              filled: true,
                              fillColor: const Color(0xFFF1F5F9),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10),
                                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10),
                                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10),
                                borderSide: BorderSide(color: _primaryColor, width: 1.5),
                              ),
                            ),
                          ),
                        );

                        final viewToggleWidget = Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              // Table View Button
                              InkWell(
                                onTap: () => setState(() => _isTableView = true),
                                borderRadius: BorderRadius.circular(8),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 150),
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                                  decoration: BoxDecoration(
                                    color: _isTableView ? _primaryColor : Colors.transparent,
                                    borderRadius: BorderRadius.circular(8),
                                    boxShadow: _isTableView
                                        ? [BoxShadow(color: _primaryColor.withOpacity(0.3), blurRadius: 6, offset: const Offset(0, 2))]
                                        : null,
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        Icons.table_rows_rounded,
                                        size: 17,
                                        color: _isTableView ? Colors.white : Colors.grey.shade700,
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        isRtl ? 'عرض جدول' : 'Table View',
                                        style: TextStyle(
                                          fontSize: 12.5,
                                          fontWeight: FontWeight.bold,
                                          color: _isTableView ? Colors.white : Colors.grey.shade700,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(width: 4),
                              // Cards View Button
                              InkWell(
                                onTap: () => setState(() => _isTableView = false),
                                borderRadius: BorderRadius.circular(8),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 150),
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                                  decoration: BoxDecoration(
                                    color: !_isTableView ? _primaryColor : Colors.transparent,
                                    borderRadius: BorderRadius.circular(8),
                                    boxShadow: !_isTableView
                                        ? [BoxShadow(color: _primaryColor.withOpacity(0.3), blurRadius: 6, offset: const Offset(0, 2))]
                                        : null,
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        Icons.grid_view_rounded,
                                        size: 17,
                                        color: !_isTableView ? Colors.white : Colors.grey.shade700,
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        isRtl ? 'عرض بطاقات' : 'Cards View',
                                        style: TextStyle(
                                          fontSize: 12.5,
                                          fontWeight: FontWeight.bold,
                                          color: !_isTableView ? Colors.white : Colors.grey.shade700,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        );

                        if (isWide) {
                          return Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              tabsWidget,
                              Row(
                                children: [
                                  searchWidget,
                                  const SizedBox(width: 12),
                                  viewToggleWidget,
                                ],
                              ),
                            ],
                          );
                        } else {
                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              tabsWidget,
                              const SizedBox(height: 12),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(child: searchWidget),
                                  const SizedBox(width: 12),
                                  viewToggleWidget,
                                ],
                              ),
                            ],
                          );
                        }
                      },
                    ),
                  ],
                ),
              ),
              
              // Content Area (Table View or Grid View)
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    _isTableView ? _buildAdsTable(context, isRtl, activeAds) : _buildAdsGrid(context, isRtl, activeAds),
                    _isTableView ? _buildAdsTable(context, isRtl, endedAds) : _buildAdsGrid(context, isRtl, endedAds),
                    _isTableView ? _buildAdsTable(context, isRtl, allAds) : _buildAdsGrid(context, isRtl, allAds),
                  ],
                ),
              ),
            ],
          );
        },
        loading: () => Center(child: CircularProgressIndicator(color: _primaryColor)),
        error: (err, stack) => AdminErrorView(
          error: err,
          stackTrace: stack,
          onRetry: () => ref.refresh(adsProvider),
        ),
      ),
    );
  }

  Widget _buildHeaderKpi(String label, String value, IconData icon, Color color) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: _darkColor, height: 1.1)),
            Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey.shade600)),
          ],
        ),
      ],
    );
  }

  Widget _buildEmptyState(bool isRtl) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 30, offset: const Offset(0, 10)),
              ],
            ),
            child: Image.network(
              'https://cdn-icons-png.flaticon.com/512/7486/7486747.png',
              width: 90,
              height: 90,
              errorBuilder: (c, e, s) => Icon(Icons.campaign_outlined, size: 70, color: Colors.grey.shade300),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            isRtl ? 'لا توجد إعلانات لعرضها حالياً' : 'No campaigns to show',
            style: TextStyle(color: _darkColor, fontSize: 18, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 8),
          Text(
            isRtl ? 'قم بإنشاء حملتك الإعلانية الأولى لجذب المزيد من الزبائن.' : 'Create your first campaign to attract more customers.',
            style: TextStyle(color: Colors.grey.shade500, fontSize: 13, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // Table View (Unified Enterprise Rows)
  // ==========================================
  Widget _buildAdsTable(BuildContext context, bool isRtl, List<AdModel> filteredAds) {
    if (filteredAds.isEmpty) {
      return _buildEmptyState(isRtl);
    }

    final currencyFmt = NumberFormat('#,##0', 'en');

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 16,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: LayoutBuilder(
          builder: (context, constraints) {
            const double minWidth = 1150;
            final width = constraints.maxWidth > minWidth ? constraints.maxWidth : minWidth;

            return SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: SizedBox(
                width: width,
                child: Column(
                  children: [
                    // --- Table Header ---
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 15),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        border: Border(
                          bottom: BorderSide(color: Colors.grey.shade200, width: 1.5),
                        ),
                      ),
                      child: Row(
                        children: [
                          _buildTableHeaderCell(title: '#', width: 45, alignment: Alignment.center, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'البانر' : 'Banner', width: 110, alignment: Alignment.center, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'عنوان الإعلان والوسم' : 'Campaign Title & Tag', flex: 3, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'المعلن / الشركة' : 'Advertiser', flex: 2, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'فترة الحملة' : 'Period', flex: 2, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'المبلغ المدفوع' : 'Paid Amount', width: 130, alignment: Alignment.center, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'الحالة' : 'Status', width: 120, alignment: Alignment.center, isRtl: isRtl),
                          _buildTableHeaderCell(title: isRtl ? 'الإجراءات والأيقونات' : 'Actions', width: 180, alignment: Alignment.center, isRtl: isRtl),
                        ],
                      ),
                    ),

                    // --- Table Rows ---
                    ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: filteredAds.length,
                      separatorBuilder: (ctx, i) => Divider(height: 1, color: Colors.grey.shade200),
                      itemBuilder: (context, index) {
                        final ad = filteredAds[index];
                        return _AdTableRow(
                          index: index + 1,
                          ad: ad,
                          isRtl: isRtl,
                          primaryColor: _primaryColor,
                          darkColor: _darkColor,
                          currencyFmt: currencyFmt,
                          onPreview: () => _showBannerPreviewDialog(ad, isRtl),
                          onEdit: () => _showCreateOrEditAdDialog(existingAd: ad, isRtl: isRtl),
                          onToggleStatus: () => ref.read(adsProvider.notifier).toggleStatus(ad.id),
                          onDelete: () => _showDeleteConfirmation(ad, isRtl),
                        );
                      },
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildTableHeaderCell({
    required String title,
    double? width,
    int? flex,
    Alignment? alignment,
    required bool isRtl,
  }) {
    final defaultAlignment = isRtl ? Alignment.centerRight : Alignment.centerLeft;
    final cellAlignment = alignment ?? defaultAlignment;

    final content = Container(
      alignment: cellAlignment,
      child: Text(
        title,
        style: const TextStyle(
          color: Color(0xFF475569),
          fontSize: 12.5,
          fontWeight: FontWeight.w800,
        ),
      ),
    );

    if (width != null) {
      return SizedBox(width: width, child: content);
    }
    return Expanded(flex: flex ?? 1, child: content);
  }

  // ==========================================
  // Grid / Cards View
  // ==========================================
  Widget _buildAdsGrid(BuildContext context, bool isRtl, List<AdModel> filteredAds) {
    if (filteredAds.isEmpty) {
      return _buildEmptyState(isRtl);
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        int crossAxisCount = 1;
        if (constraints.maxWidth > 1400) {
          crossAxisCount = 4;
        } else if (constraints.maxWidth > 1000) {
          crossAxisCount = 3;
        } else if (constraints.maxWidth > 650) {
          crossAxisCount = 2;
        }

        return GridView.builder(
          padding: const EdgeInsets.all(32),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            childAspectRatio: 0.80,
            crossAxisSpacing: 24,
            mainAxisSpacing: 24,
          ),
          itemCount: filteredAds.length,
          itemBuilder: (context, index) {
            final ad = filteredAds[index];
            return TweenAnimationBuilder<double>(
              tween: Tween<double>(begin: 0, end: 1),
              duration: Duration(milliseconds: 400 + (index * 80).clamp(0, 800)),
              curve: Curves.easeOutCubic,
              builder: (context, val, child) {
                return Transform.translate(
                  offset: Offset(0, 30 * (1 - val)),
                  child: Opacity(
                    opacity: val,
                    child: child,
                  ),
                );
              },
              child: _AdHoverCard(
                ad: ad,
                isRtl: isRtl,
                primaryColor: _primaryColor,
                darkColor: _darkColor,
                onPreview: () => _showBannerPreviewDialog(ad, isRtl),
                onEdit: () => _showCreateOrEditAdDialog(existingAd: ad, isRtl: isRtl),
                onToggleStatus: () => ref.read(adsProvider.notifier).toggleStatus(ad.id),
                onDelete: () => _showDeleteConfirmation(ad, isRtl),
              ),
            );
          },
        );
      },
    );
  }
}

// ==========================================
// Table Row Widget with Micro-Interactions
// ==========================================
class _AdTableRow extends StatefulWidget {
  final int index;
  final AdModel ad;
  final bool isRtl;
  final Color primaryColor;
  final Color darkColor;
  final NumberFormat currencyFmt;
  final VoidCallback onPreview;
  final VoidCallback onEdit;
  final VoidCallback onToggleStatus;
  final VoidCallback onDelete;

  const _AdTableRow({
    required this.index,
    required this.ad,
    required this.isRtl,
    required this.primaryColor,
    required this.darkColor,
    required this.currencyFmt,
    required this.onPreview,
    required this.onEdit,
    required this.onToggleStatus,
    required this.onDelete,
  });

  @override
  State<_AdTableRow> createState() => _AdTableRowState();
}

class _AdTableRowState extends State<_AdTableRow> {
  bool _isHovered = false;

  @override
  Widget build(BuildContext context) {
    final ad = widget.ad;
    final isRtl = widget.isRtl;
    final defaultBg = widget.index.isEven ? const Color(0xFFF8FAFC).withOpacity(0.5) : Colors.white;

    return MouseRegion(
      onEnter: (_) => setState(() => _isHovered = true),
      onExit: (_) => setState(() => _isHovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        color: _isHovered ? const Color(0xFFEFF6FF) : defaultBg,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // 1. Index #
            SizedBox(
              width: 45,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '${widget.index}',
                    style: TextStyle(
                      color: Colors.grey.shade700,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
            ),

            // 2. Banner Thumbnail (with preview click)
            SizedBox(
              width: 110,
              child: Center(
                child: Tooltip(
                  message: isRtl ? 'اضغط لمعاينة وتكبير البانر' : 'Click to preview banner',
                  child: InkWell(
                    onTap: widget.onPreview,
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      width: 92,
                      height: 44,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade300),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 4, offset: const Offset(0, 2)),
                        ],
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          Image.network(
                            ad.imageUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (c, e, s) => Container(
                              color: Colors.grey.shade100,
                              child: const Icon(Icons.broken_image_rounded, size: 20, color: Colors.grey),
                            ),
                          ),
                          Container(
                            color: Colors.black.withOpacity(0.1),
                          ),
                          const Center(
                            child: Icon(Icons.zoom_in_rounded, color: Colors.white, size: 20),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),

            // 3. Campaign Title & Promotional Tag
            Expanded(
              flex: 3,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      ad.title,
                      style: TextStyle(
                        color: widget.darkColor,
                        fontWeight: FontWeight.w800,
                        fontSize: 14,
                        height: 1.2,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    if (ad.target.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: const Color(0xFFFDE68A)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.local_offer_rounded, size: 11, color: Color(0xFFB45309)),
                            const SizedBox(width: 4),
                            Text(
                              ad.target,
                              style: const TextStyle(
                                color: Color(0xFFB45309),
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            ),

            // 4. Advertiser / Company
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: widget.primaryColor.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Icon(Icons.business_rounded, size: 14, color: widget.primaryColor),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        ad.company,
                        style: TextStyle(
                          color: Colors.grey.shade900,
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // 5. Campaign Period
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.date_range_rounded, size: 14, color: Colors.grey.shade600),
                        const SizedBox(width: 6),
                        Text(
                          ad.startDate,
                          style: TextStyle(color: Colors.grey.shade800, fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Icon(Icons.arrow_downward_rounded, size: 12, color: Colors.grey.shade400),
                        const SizedBox(width: 8),
                        Text(
                          ad.endDate,
                          style: TextStyle(color: Colors.grey.shade800, fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            // 6. Paid Amount (Budget)
            SizedBox(
              width: 130,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Text(
                    '${widget.currencyFmt.format(ad.price)} ${isRtl ? 'ج.س' : 'SDG'}',
                    style: const TextStyle(
                      color: Color(0xFF065F46),
                      fontWeight: FontWeight.w900,
                      fontSize: 12.5,
                    ),
                  ),
                ),
              ),
            ),

            // 7. Status Badge
            SizedBox(
              width: 120,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: ad.isActive ? const Color(0xFFDCFCE7) : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: ad.isActive ? const Color(0xFF86EFAC) : const Color(0xFFCBD5E1),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 7,
                        height: 7,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: ad.isActive ? const Color(0xFF16A34A) : Colors.grey.shade600,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        ad.isActive ? (isRtl ? 'نشط' : 'Active') : (isRtl ? 'متوقف' : 'Paused'),
                        style: TextStyle(
                          color: ad.isActive ? const Color(0xFF15803D) : Colors.grey.shade700,
                          fontWeight: FontWeight.w800,
                          fontSize: 11.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // 8. Organized Action Icons Bar
            SizedBox(
              width: 180,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Action 1: Preview Banner
                  _buildActionButton(
                    icon: Icons.fullscreen_rounded,
                    tooltip: isRtl ? 'معاينة البانر بالحجم الكامل' : 'Full Preview',
                    color: const Color(0xFF0284C7),
                    bgColor: const Color(0xFFE0F2FE),
                    onTap: widget.onPreview,
                  ),
                  const SizedBox(width: 8),

                  // Action 2: Toggle Status (Play / Pause)
                  _buildActionButton(
                    icon: ad.isActive ? Icons.pause_rounded : Icons.play_arrow_rounded,
                    tooltip: ad.isActive
                        ? (isRtl ? 'إيقاف مؤقت للحملة' : 'Pause Campaign')
                        : (isRtl ? 'تنشيط وإطلاق الحملة' : 'Activate Campaign'),
                    color: ad.isActive ? const Color(0xFFD97706) : const Color(0xFF059669),
                    bgColor: ad.isActive ? const Color(0xFFFEF3C7) : const Color(0xFFD1FAE5),
                    onTap: widget.onToggleStatus,
                  ),
                  const SizedBox(width: 8),

                  // Action 3: Edit
                  _buildActionButton(
                    icon: Icons.edit_note_rounded,
                    tooltip: isRtl ? 'تعديل بيانات الحملة' : 'Edit Campaign',
                    color: widget.primaryColor,
                    bgColor: widget.primaryColor.withOpacity(0.08),
                    onTap: widget.onEdit,
                  ),
                  const SizedBox(width: 8),

                  // Action 4: Delete
                  _buildActionButton(
                    icon: Icons.delete_outline_rounded,
                    tooltip: isRtl ? 'حذف الحملة نهائياً' : 'Delete Campaign',
                    color: const Color(0xFFDC2626),
                    bgColor: const Color(0xFFFEE2E2),
                    onTap: widget.onDelete,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String tooltip,
    required Color color,
    required Color bgColor,
    required VoidCallback onTap,
  }) {
    return Tooltip(
      message: tooltip,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(8),
        child: Container(
          width: 34,
          height: 34,
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: color.withOpacity(0.2)),
          ),
          child: Icon(icon, color: color, size: 18),
        ),
      ),
    );
  }
}

// ==========================================
// Hover Card Widget with Micro-Interactions
// ==========================================
class _AdHoverCard extends StatefulWidget {
  final AdModel ad;
  final bool isRtl;
  final Color primaryColor;
  final Color darkColor;
  final VoidCallback onPreview;
  final VoidCallback onEdit;
  final VoidCallback onToggleStatus;
  final VoidCallback onDelete;

  const _AdHoverCard({
    required this.ad,
    required this.isRtl,
    required this.primaryColor,
    required this.darkColor,
    required this.onPreview,
    required this.onEdit,
    required this.onToggleStatus,
    required this.onDelete,
  });

  @override
  State<_AdHoverCard> createState() => _AdHoverCardState();
}

class _AdHoverCardState extends State<_AdHoverCard> {
  bool _isHovered = false;

  @override
  Widget build(BuildContext context) {
    final ad = widget.ad;
    final isRtl = widget.isRtl;

    return MouseRegion(
      onEnter: (_) => setState(() => _isHovered = true),
      onExit: (_) => setState(() => _isHovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOutCubic,
        transform: Matrix4.identity()..translate(0.0, _isHovered ? -6.0 : 0.0),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: _isHovered ? widget.primaryColor.withOpacity(0.3) : Colors.grey.shade200, width: 1.5),
          boxShadow: [
            BoxShadow(
              color: _isHovered ? widget.primaryColor.withOpacity(0.12) : Colors.black.withOpacity(0.04),
              blurRadius: _isHovered ? 20 : 8,
              offset: Offset(0, _isHovered ? 10 : 4),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ==================================
              // Image Section with Gradient Overlay
              // ==================================
              Expanded(
                flex: 4,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    InkWell(
                      onTap: widget.onPreview,
                      child: Container(
                        color: Colors.grey.shade50,
                        child: AnimatedScale(
                          scale: _isHovered ? 1.05 : 1.0,
                          duration: const Duration(milliseconds: 500),
                          curve: Curves.easeOutExpo,
                          child: Image.network(
                            ad.imageUrl,
                            fit: BoxFit.contain,
                            errorBuilder: (c, e, s) => Container(
                              color: Colors.grey.shade100,
                              child: const Icon(Icons.image_not_supported_rounded, color: Colors.grey, size: 40),
                            ),
                          ),
                        ),
                      ),
                    ),
                    // Gradient Overlay for Text Readability
                    IgnorePointer(
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              Colors.black.withOpacity(0.65),
                              Colors.transparent,
                              Colors.black.withOpacity(0.25),
                            ],
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                          ),
                        ),
                      ),
                    ),
                    // Status Badge (Top Left/Right)
                    Positioned(
                      top: 12,
                      left: isRtl ? null : 12,
                      right: isRtl ? 12 : null,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: ad.isActive ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 6, offset: const Offset(0, 2)),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              ad.isActive ? Icons.check_circle_rounded : Icons.pause_circle_filled_rounded,
                              color: Colors.white,
                              size: 13,
                            ),
                            const SizedBox(width: 5),
                            Text(
                              ad.isActive ? (isRtl ? 'نشط' : 'Active') : (isRtl ? 'متوقف' : 'Paused'),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11.5,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    // Target Tag (Bottom Left/Right)
                    if (ad.target.isNotEmpty)
                      Positioned(
                        bottom: 12,
                        right: isRtl ? null : 12,
                        left: isRtl ? 12 : null,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.65),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.white.withOpacity(0.2)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.sell_rounded, color: Colors.amber, size: 11),
                              const SizedBox(width: 5),
                              Text(
                                ad.target,
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              // ==================================
              // Details Section
              // ==================================
              Expanded(
                flex: 5,
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        ad.title,
                        style: TextStyle(
                          color: widget.darkColor,
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                          height: 1.25,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 10),
                      // Company and Price Row
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                Icon(Icons.business_center_rounded, size: 15, color: widget.primaryColor.withOpacity(0.7)),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    ad.company,
                                    style: TextStyle(
                                      color: Colors.grey.shade800,
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFECFDF5),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: const Color(0xFFA7F3D0)),
                            ),
                            child: Text(
                              '${NumberFormat('#,##0', 'en').format(ad.price)} ${isRtl ? 'ج.س' : 'SDG'}',
                              style: const TextStyle(
                                color: Color(0xFF065F46),
                                fontWeight: FontWeight.w900,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const Spacer(),
                      // Duration Box
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.date_range_rounded, size: 14, color: Colors.grey.shade500),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                '${ad.startDate}  ➜  ${ad.endDate}',
                                style: TextStyle(
                                  color: Colors.grey.shade700,
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),
                      // Actions Row
                      Row(
                        children: [
                          Expanded(
                            child: FilledButton.icon(
                              onPressed: widget.onEdit,
                              icon: const Icon(Icons.edit_rounded, size: 15),
                              label: Text(isRtl ? 'تعديل' : 'Edit', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFFF1F5F9),
                                foregroundColor: widget.primaryColor,
                                elevation: 0,
                                padding: const EdgeInsets.symmetric(vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Preview Button
                          Tooltip(
                            message: isRtl ? 'معاينة البانر' : 'Preview',
                            child: InkWell(
                              onTap: widget.onPreview,
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE0F2FE),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.fullscreen_rounded, color: Color(0xFF0284C7), size: 18),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Toggle Status Button
                          Tooltip(
                            message: ad.isActive ? (isRtl ? 'إيقاف الحملة' : 'Pause') : (isRtl ? 'تنشيط' : 'Activate'),
                            child: InkWell(
                              onTap: widget.onToggleStatus,
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: ad.isActive ? const Color(0xFFFEF3C7) : const Color(0xFFD1FAE5),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Icon(
                                  ad.isActive ? Icons.pause_rounded : Icons.play_arrow_rounded,
                                  color: ad.isActive ? const Color(0xFFD97706) : const Color(0xFF059669),
                                  size: 18,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Delete Button
                          Tooltip(
                            message: isRtl ? 'حذف الحملة' : 'Delete',
                            child: InkWell(
                              onTap: widget.onDelete,
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 36,
                                height: 36,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFEE2E2),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.delete_outline_rounded, color: Color(0xFFDC2626), size: 18),
                              ),
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
    );
  }
}

