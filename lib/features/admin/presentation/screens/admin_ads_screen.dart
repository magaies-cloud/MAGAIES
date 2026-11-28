import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';

enum _AdSortOption {
  newest,
  highestPrice,
  endingSoonest,
  companyAZ,
}

class _CampaignTimingInfo {
  final int totalDays;
  final int elapsedDays;
  final int remainingDays;
  final double progress;
  final bool isExpired;
  final bool isExpiringSoon;
  final bool isNotStartedYet;

  const _CampaignTimingInfo({
    required this.totalDays,
    required this.elapsedDays,
    required this.remainingDays,
    required this.progress,
    required this.isExpired,
    required this.isExpiringSoon,
    required this.isNotStartedYet,
  });
}

_CampaignTimingInfo _analyzeAdTiming(AdModel ad) {
  final now = DateTime.now();
  final today = DateTime(now.year, now.month, now.day);
  final start = DateTime.tryParse(ad.startDate) ?? today;
  final end = DateTime.tryParse(ad.endDate) ?? today.add(const Duration(days: 30));

  final startDay = DateTime(start.year, start.month, start.day);
  final endDay = DateTime(end.year, end.month, end.day);

  final totalDays = endDay.difference(startDay).inDays.clamp(1, 36500);
  final elapsedDays = today.difference(startDay).inDays.clamp(0, totalDays);
  final remainingDays = endDay.difference(today).inDays;
  final isExpired = remainingDays < 0;
  final isNotStartedYet = today.isBefore(startDay);
  final isExpiringSoon = !isExpired && remainingDays <= 7;
  final progress = isExpired
      ? 1.0
      : (isNotStartedYet ? 0.0 : (elapsedDays / totalDays).clamp(0.0, 1.0));

  return _CampaignTimingInfo(
    totalDays: totalDays,
    elapsedDays: elapsedDays,
    remainingDays: remainingDays,
    progress: progress,
    isExpired: isExpired,
    isExpiringSoon: isExpiringSoon,
    isNotStartedYet: isNotStartedYet,
  );
}

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
  _AdSortOption _sortOption = _AdSortOption.newest;

  final Color _primaryColor = const Color(0xFF1E40AF);
  final Color _darkColor = const Color(0xFF0F172A);
  final Color _accentColor = const Color(0xFF3B82F6);

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _pickDate(
    BuildContext context,
    TextEditingController controller, {
    DateTime? firstAllowedDate,
  }) async {
    final now = DateTime.now();
    final existing = DateTime.tryParse(controller.text.trim()) ?? now;
    final firstDate = firstAllowedDate ?? DateTime(2024);
    final initialDate = existing.isBefore(firstDate) ? firstDate : existing;

    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: firstDate,
      lastDate: DateTime(2035),
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

  Future<void> _quickExtendCampaign(AdModel ad, int extraDays, bool isRtl) async {
    try {
      final now = DateTime.now();
      final today = DateTime(now.year, now.month, now.day);
      final currentEnd = DateTime.tryParse(ad.endDate) ?? today;
      final baseDate = currentEnd.isBefore(today) ? today : currentEnd;
      final newEnd = DateFormat('yyyy-MM-dd').format(baseDate.add(Duration(days: extraDays)));

      await ref.read(adsProvider.notifier).updateAd(
            ad.copyWith(
              endDate: newEnd,
              isActive: true,
            ),
          );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              isRtl
                  ? 'تم تمديد الحملة "${ad.title}" لمدة $extraDays يوم (حتى $newEnd)'
                  : 'Extended "${ad.title}" by $extraDays days (until $newEnd)',
            ),
            backgroundColor: const Color(0xFF059669),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'تعذر تمديد الحملة: $e' : 'Failed to extend campaign: $e'),
            backgroundColor: Colors.red.shade700,
          ),
        );
      }
    }
  }

  Future<void> _deactivateAllExpiredAds(List<AdModel> expiredActiveAds, bool isRtl) async {
    if (expiredActiveAds.isEmpty) return;
    try {
      for (final ad in expiredActiveAds) {
        await ref.read(adsProvider.notifier).updateAd(ad.copyWith(isActive: false));
      }
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              isRtl
                  ? 'تم إيقاف ${expiredActiveAds.length} حملة منتهية الصلاحية بنجاح'
                  : 'Paused ${expiredActiveAds.length} expired campaigns successfully',
            ),
            backgroundColor: const Color(0xFF059669),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'حدث خطأ أثناء تحديث الحالات: $e' : 'Error updating campaigns: $e'),
            backgroundColor: Colors.red.shade700,
          ),
        );
      }
    }
  }

  void _showCreateOrEditAdDialog({AdModel? existingAd, required bool isRtl}) {
    final isEditing = existingAd != null;
    final titleArCtrl = TextEditingController(text: existingAd?.title ?? '');
    final advertiserCtrl = TextEditingController(text: existingAd?.company ?? '');
    final targetCtrl = TextEditingController(text: existingAd?.target ?? (isRtl ? 'عرض خاص' : 'Special Offer'));
    final startDateCtrl = TextEditingController(
      text: existingAd?.startDate.isNotEmpty == true
          ? existingAd!.startDate
          : DateFormat('yyyy-MM-dd').format(DateTime.now()),
    );
    final endDateCtrl = TextEditingController(
      text: existingAd?.endDate.isNotEmpty == true
          ? existingAd!.endDate
          : DateFormat('yyyy-MM-dd').format(DateTime.now().add(const Duration(days: 30))),
    );
    final priceCtrl = TextEditingController(
      text: existingAd != null ? existingAd.price.toStringAsFixed(0) : '0',
    );
    String imageUrl = existingAd?.imageUrl ?? '';

    Uint8List? localImageBytes;
    String? localExt;
    bool isSaving = false;

    final presetTags = isRtl
        ? ['عرض خاص', 'خصم حصري', 'وصل حديثاً', 'وكيل معتمد', 'تصفية شاملة']
        : ['Special Offer', 'Exclusive Discount', 'New Arrival', 'Authorized Dealer', 'Clearance'];

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setDlgState) {
            void applyDurationDays(int days) {
              final start = DateTime.tryParse(startDateCtrl.text.trim()) ?? DateTime.now();
              endDateCtrl.text = DateFormat('yyyy-MM-dd').format(start.add(Duration(days: days)));
              setDlgState(() {});
            }

            final hasValidExistingImage =
                imageUrl.isNotEmpty && !imageUrl.contains('unsplash') && !imageUrl.contains('picsum');

            return Directionality(
              textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
              child: AlertDialog(
                backgroundColor: Colors.white,
                surfaceTintColor: Colors.transparent,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 16),
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
                          BoxShadow(
                            color: _primaryColor.withValues(alpha: 0.28),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Icon(
                        isEditing ? Icons.edit_note_rounded : Icons.campaign_rounded,
                        color: Colors.white,
                        size: 28,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isEditing
                                ? (isRtl ? 'تعديل بيانات الحملة الإعلانية' : 'Edit Advertising Campaign')
                                : (isRtl ? 'إطلاق حملة إعلانية جديدة' : 'Launch New Advertising Campaign'),
                            style: TextStyle(color: _darkColor, fontWeight: FontWeight.w900, fontSize: 20),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isRtl
                                ? 'أدخل بيانات المعلن، الفترة الزمنية، وقيمة التعاقد مع معاينة حية للبانر'
                                : 'Enter advertiser details, schedule, and contract value with live preview',
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 12.5, fontWeight: FontWeight.w500),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: isSaving ? null : () => Navigator.pop(ctx),
                      icon: Icon(Icons.close_rounded, color: Colors.grey.shade500),
                    ),
                  ],
                ),
                content: SizedBox(
                  width: 680,
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Divider(height: 1),
                        const SizedBox(height: 18),
                        Row(
                          children: [
                            Expanded(
                              flex: 3,
                              child: _buildDialogTextField(
                                controller: titleArCtrl,
                                label: isRtl ? 'عنوان الإعلان الرئيسي *' : 'Campaign Headline *',
                                icon: Icons.title_rounded,
                                onChanged: (_) => setDlgState(() {}),
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              flex: 2,
                              child: _buildDialogTextField(
                                controller: advertiserCtrl,
                                label: isRtl ? 'اسم المعلن / الشركة *' : 'Advertiser / Company *',
                                icon: Icons.business_rounded,
                                onChanged: (_) => setDlgState(() {}),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        Row(
                          children: [
                            Expanded(
                              flex: 3,
                              child: _buildDialogTextField(
                                controller: targetCtrl,
                                label: isRtl ? 'الوسم الترويجي (يظهر أعلى البانر)' : 'Promotional Badge Tag',
                                icon: Icons.local_offer_rounded,
                                onChanged: (_) => setDlgState(() {}),
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              flex: 2,
                              child: _buildDialogTextField(
                                controller: priceCtrl,
                                label: isRtl ? 'قيمة العقد المدفوعة (ج.س)' : 'Contract Amount (SDG)',
                                icon: Icons.payments_rounded,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                onChanged: (_) => setDlgState(() {}),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        // Quick promotional tags
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Text(
                              isRtl ? 'وسوم سريعة:' : 'Quick Tags:',
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.grey.shade600),
                            ),
                            ...presetTags.map(
                              (tag) => InkWell(
                                onTap: () {
                                  targetCtrl.text = tag;
                                  setDlgState(() {});
                                },
                                borderRadius: BorderRadius.circular(20),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: targetCtrl.text.trim() == tag
                                        ? _primaryColor.withValues(alpha: 0.12)
                                        : const Color(0xFFF1F5F9),
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(
                                      color: targetCtrl.text.trim() == tag
                                          ? _primaryColor
                                          : const Color(0xFFE2E8F0),
                                    ),
                                  ),
                                  child: Text(
                                    tag,
                                    style: TextStyle(
                                      fontSize: 11.5,
                                      fontWeight: FontWeight.w700,
                                      color: targetCtrl.text.trim() == tag ? _primaryColor : Colors.grey.shade700,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: _buildDialogTextField(
                                controller: startDateCtrl,
                                label: isRtl ? 'تاريخ بداية الحملة' : 'Start Date',
                                icon: Icons.calendar_today_rounded,
                                readOnly: true,
                                onTap: () async {
                                  await _pickDate(ctx, startDateCtrl);
                                  setDlgState(() {});
                                },
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: _buildDialogTextField(
                                controller: endDateCtrl,
                                label: isRtl ? 'تاريخ نهاية الحملة' : 'End Date',
                                icon: Icons.event_available_rounded,
                                readOnly: true,
                                onTap: () async {
                                  final start = DateTime.tryParse(startDateCtrl.text.trim());
                                  await _pickDate(ctx, endDateCtrl, firstAllowedDate: start);
                                  setDlgState(() {});
                                },
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        // Quick duration chips
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Text(
                              isRtl ? 'تحديد سريع للمدة:' : 'Quick Duration:',
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.grey.shade600),
                            ),
                            ...[
                              (7, isRtl ? 'أسبوع (7 أيام)' : '7 Days'),
                              (15, isRtl ? 'نصف شهر (15 يوم)' : '15 Days'),
                              (30, isRtl ? 'شهر كامل (30 يوم)' : '30 Days'),
                              (90, isRtl ? 'ربع سنوي (90 يوم)' : '90 Days'),
                            ].map(
                              (item) => ActionChip(
                                label: Text(item.$2, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                                avatar: Icon(Icons.timer_outlined, size: 14, color: _primaryColor),
                                backgroundColor: const Color(0xFFF8FAFC),
                                side: const BorderSide(color: Color(0xFFE2E8F0)),
                                onPressed: () => applyDurationDays(item.$1),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              isRtl ? 'تصميم البانر ومعاينة الظهور في التطبيق' : 'Banner Artwork & Live App Preview',
                              style: TextStyle(color: _darkColor, fontWeight: FontWeight.w800, fontSize: 14.5),
                            ),
                            TextButton.icon(
                              onPressed: isSaving
                                  ? null
                                  : () async {
                                      try {
                                        final picker = ImagePicker();
                                        final pickedFile = await picker.pickImage(
                                          source: ImageSource.gallery,
                                          imageQuality: 80,
                                          maxWidth: 1400,
                                        );
                                        if (pickedFile != null) {
                                          final bytes = await pickedFile.readAsBytes();
                                          final ext = pickedFile.name.split('.').last;
                                          setDlgState(() {
                                            localImageBytes = bytes;
                                            localExt = ext;
                                          });
                                        }
                                      } catch (e) {
                                        if (context.mounted) {
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            SnackBar(
                                              content: Text(isRtl ? 'فشل اختيار الصورة' : 'Failed to pick image'),
                                              backgroundColor: Colors.red,
                                            ),
                                          );
                                        }
                                      }
                                    },
                              icon: const Icon(Icons.upload_file_rounded, size: 18),
                              label: Text(isRtl ? 'اختيار صورة من الجهاز' : 'Choose Image'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        // Live Buyer Carousel Preview / Upload Box
                        InkWell(
                          onTap: isSaving
                              ? null
                              : () async {
                                  try {
                                    final picker = ImagePicker();
                                    final pickedFile = await picker.pickImage(
                                      source: ImageSource.gallery,
                                      imageQuality: 80,
                                      maxWidth: 1400,
                                    );
                                    if (pickedFile != null) {
                                      final bytes = await pickedFile.readAsBytes();
                                      final ext = pickedFile.name.split('.').last;
                                      setDlgState(() {
                                        localImageBytes = bytes;
                                        localExt = ext;
                                      });
                                    }
                                  } catch (_) {}
                                },
                          borderRadius: BorderRadius.circular(18),
                          child: Container(
                            width: double.infinity,
                            height: 195,
                            decoration: BoxDecoration(
                              color: const Color(0xFF0F172A),
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(
                                color: (localImageBytes != null || hasValidExistingImage)
                                    ? _primaryColor.withValues(alpha: 0.35)
                                    : _primaryColor.withValues(alpha: 0.3),
                                width: 1.8,
                              ),
                            ),
                            clipBehavior: Clip.antiAlias,
                            child: (localImageBytes == null && !hasValidExistingImage)
                                ? Container(
                                    color: const Color(0xFFF8FAFC),
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(12),
                                          decoration: BoxDecoration(
                                            color: Colors.white,
                                            shape: BoxShape.circle,
                                            boxShadow: [
                                              BoxShadow(
                                                color: Colors.black.withValues(alpha: 0.06),
                                                blurRadius: 12,
                                                offset: const Offset(0, 4),
                                              ),
                                            ],
                                          ),
                                          child: Icon(Icons.cloud_upload_rounded, size: 32, color: _accentColor),
                                        ),
                                        const SizedBox(height: 10),
                                        Text(
                                          isRtl
                                              ? 'اضغط هنا لرفع صورة البانر الإعلاني'
                                              : 'Click here to upload campaign banner',
                                          style: TextStyle(
                                            color: _primaryColor,
                                            fontWeight: FontWeight.w800,
                                            fontSize: 14,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          isRtl
                                              ? 'المقاس المثالي: 1200×500 بكسل (نسبة 16:7) • الصيغ: PNG, JPG, WebP'
                                              : 'Optimal Size: 1200×500 px (16:7) • PNG, JPG, WebP',
                                          style: TextStyle(
                                            color: Colors.grey.shade600,
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ],
                                    ),
                                  )
                                : Stack(
                                    fit: StackFit.expand,
                                    children: [
                                      if (localImageBytes != null)
                                        Image.memory(localImageBytes!, fit: BoxFit.cover)
                                      else
                                        Image.network(imageUrl, fit: BoxFit.cover),
                                      Container(
                                        decoration: BoxDecoration(
                                          gradient: LinearGradient(
                                            begin: Alignment.topCenter,
                                            end: Alignment.bottomCenter,
                                            colors: [
                                              Colors.black.withValues(alpha: 0.2),
                                              Colors.transparent,
                                              Colors.black.withValues(alpha: 0.72),
                                            ],
                                          ),
                                        ),
                                      ),
                                      // Tag preview
                                      Positioned(
                                        top: 12,
                                        right: isRtl ? 12 : null,
                                        left: isRtl ? null : 12,
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                                          decoration: BoxDecoration(
                                            color: _primaryColor.withValues(alpha: 0.92),
                                            borderRadius: BorderRadius.circular(20),
                                          ),
                                          child: Text(
                                            targetCtrl.text.trim().isEmpty
                                                ? (isRtl ? 'عرض خاص' : 'Special Offer')
                                                : targetCtrl.text.trim(),
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 11.5,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ),
                                      // Live Preview label
                                      Positioned(
                                        top: 12,
                                        left: isRtl ? 12 : null,
                                        right: isRtl ? null : 12,
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: Colors.black.withValues(alpha: 0.6),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              const Icon(Icons.visibility_rounded, color: Colors.white, size: 13),
                                              const SizedBox(width: 4),
                                              Text(
                                                isRtl ? 'معاينة حية لتطبيق المشتري' : 'Live App Preview',
                                                style: const TextStyle(
                                                  color: Colors.white,
                                                  fontSize: 10.5,
                                                  fontWeight: FontWeight.w700,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                      // Headline & Company preview
                                      Positioned(
                                        bottom: 14,
                                        right: isRtl ? 16 : 16,
                                        left: isRtl ? 16 : 16,
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Text(
                                              titleArCtrl.text.trim().isEmpty
                                                  ? (isRtl ? 'عنوان الإعلان سيظهر هنا...' : 'Campaign headline...')
                                                  : titleArCtrl.text.trim(),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontSize: 17,
                                                fontWeight: FontWeight.w900,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              advertiserCtrl.text.trim().isEmpty
                                                  ? (isRtl ? 'اسم الشركة المعلنة' : 'Advertiser Company')
                                                  : advertiserCtrl.text.trim(),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: TextStyle(
                                                color: Colors.white.withValues(alpha: 0.9),
                                                fontSize: 13,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                          ),
                        ),
                        const SizedBox(height: 12),
                      ],
                    ),
                  ),
                ),
                actionsPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                actions: [
                  TextButton(
                    onPressed: isSaving ? null : () => Navigator.pop(ctx),
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.grey.shade600,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                  FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: _primaryColor,
                      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 3,
                    ),
                    onPressed: isSaving
                        ? null
                        : () async {
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

                            final startDt = DateTime.tryParse(startDateCtrl.text.trim());
                            final endDt = DateTime.tryParse(endDateCtrl.text.trim());
                            if (startDt != null && endDt != null && endDt.isBefore(startDt)) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    isRtl
                                        ? '⚠️ تاريخ نهاية الحملة يجب أن يكون بعد أو يساوي تاريخ البداية'
                                        : 'End date must be on or after the start date',
                                  ),
                                  backgroundColor: Colors.red.shade700,
                                ),
                              );
                              return;
                            }

                            final parsedPrice = double.tryParse(priceCtrl.text.replaceAll(',', '').trim());
                            if (parsedPrice == null || parsedPrice < 0) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    isRtl
                                        ? '⚠️ يرجى إدخال مبلغ صحيح لقيمة الحملة'
                                        : 'Please enter a valid campaign amount',
                                  ),
                                  backgroundColor: Colors.red.shade700,
                                ),
                              );
                              return;
                            }

                            if (localImageBytes == null && !hasValidExistingImage) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    isRtl
                                        ? '⚠️ يرجى رفع تصميم وبانر حقيقي للإعلان'
                                        : 'Please upload a real banner image for the ad',
                                  ),
                                  backgroundColor: Colors.amber.shade900,
                                ),
                              );
                              return;
                            }

                            setDlgState(() => isSaving = true);

                            try {
                              if (localImageBytes != null && localExt != null) {
                                final fileName = 'ad_${DateTime.now().millisecondsSinceEpoch}.$localExt';
                                await Supabase.instance.client.storage
                                    .from('ads_bucket')
                                    .uploadBinary(fileName, localImageBytes!);
                                imageUrl = Supabase.instance.client.storage.from('ads_bucket').getPublicUrl(fileName);
                              }

                              if (isEditing) {
                                await ref.read(adsProvider.notifier).updateAd(
                                      existingAd.copyWith(
                                        title: titleArCtrl.text.trim(),
                                        company: advertiserCtrl.text.trim(),
                                        target: targetCtrl.text.trim().isNotEmpty ? targetCtrl.text.trim() : 'عرض خاص',
                                        startDate: startDateCtrl.text.trim(),
                                        endDate: endDateCtrl.text.trim(),
                                        imageUrl: imageUrl,
                                        price: parsedPrice,
                                      ),
                                    );
                              } else {
                                await ref.read(adsProvider.notifier).addAd(
                                      AdModel(
                                        id: 'ad_${DateTime.now().millisecondsSinceEpoch}',
                                        title: titleArCtrl.text.trim(),
                                        company: advertiserCtrl.text.trim(),
                                        target: targetCtrl.text.trim().isNotEmpty ? targetCtrl.text.trim() : 'عرض خاص',
                                        isActive: true,
                                        startDate: startDateCtrl.text.trim(),
                                        endDate: endDateCtrl.text.trim(),
                                        imageUrl: imageUrl,
                                        price: parsedPrice,
                                      ),
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
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                          )
                        : Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(isEditing ? Icons.check_circle_rounded : Icons.rocket_launch_rounded, size: 19),
                              const SizedBox(width: 8),
                              Text(
                                isEditing
                                    ? (isRtl ? 'حفظ التعديلات' : 'Save Changes')
                                    : (isRtl ? 'حفظ ونشر الحملة' : 'Launch Campaign'),
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5),
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
    ValueChanged<String>? onChanged,
    TextInputType? keyboardType,
  }) {
    return TextField(
      controller: controller,
      readOnly: readOnly,
      onTap: onTap,
      onChanged: onChanged,
      keyboardType: keyboardType,
      style: TextStyle(color: _darkColor, fontWeight: FontWeight.w700, fontSize: 13.5),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.w500, fontSize: 13),
        prefixIcon: Icon(icon, color: _primaryColor.withValues(alpha: 0.75), size: 20),
        filled: true,
        fillColor: const Color(0xFFF8FAFC),
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
          borderSide: BorderSide(color: _primaryColor, width: 1.8),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
    );
  }

  void _showBannerPreviewDialog(AdModel ad, bool isRtl) {
    final timing = _analyzeAdTiming(ad);
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 860),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.3), blurRadius: 30, offset: const Offset(0, 10)),
            ],
          ),
          clipBehavior: Clip.antiAlias,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [_darkColor, _primaryColor],
                    begin: Alignment.centerRight,
                    end: Alignment.centerLeft,
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.campaign_rounded, color: Colors.white, size: 26),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            ad.title,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16.5),
                          ),
                          Text(
                            '${ad.company} • ${ad.target}',
                            style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 12.5),
                          ),
                        ],
                      ),
                    ),
                    FilledButton.tonalIcon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _showCreateOrEditAdDialog(existingAd: ad, isRtl: isRtl);
                      },
                      icon: const Icon(Icons.edit_rounded, size: 16),
                      label: Text(isRtl ? 'تعديل' : 'Edit'),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: Colors.white),
                      onPressed: () => Navigator.pop(ctx),
                      tooltip: isRtl ? 'إغلاق' : 'Close',
                    ),
                  ],
                ),
              ),
              Container(
                color: const Color(0xFF0F172A),
                constraints: const BoxConstraints(maxHeight: 460),
                child: Center(
                  child: InteractiveViewer(
                    maxScale: 3.5,
                    child: Image.network(
                      ad.imageUrl,
                      fit: BoxFit.contain,
                      errorBuilder: (c, e, s) => Container(
                        height: 240,
                        color: Colors.grey.shade100,
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.broken_image_rounded, size: 48, color: Colors.grey.shade400),
                              const SizedBox(height: 8),
                              Text(
                                isRtl ? 'تعذر تحميل صورة البانر' : 'Image failed to load',
                                style: TextStyle(color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                color: const Color(0xFFF8FAFC),
                child: Wrap(
                  alignment: WrapAlignment.spaceBetween,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  spacing: 16,
                  runSpacing: 8,
                  children: [
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.calendar_today_rounded, size: 15, color: Colors.grey.shade600),
                        const SizedBox(width: 8),
                        Text(
                          '${ad.startDate}   ➜   ${ad.endDate}',
                          style: TextStyle(color: Colors.grey.shade800, fontWeight: FontWeight.w800, fontSize: 13),
                        ),
                        const SizedBox(width: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: timing.isExpired
                                ? const Color(0xFFFEE2E2)
                                : (timing.isExpiringSoon ? const Color(0xFFFEF3C7) : const Color(0xFFDCFCE7)),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            timing.isExpired
                                ? (isRtl ? 'منتهي منذ ${timing.remainingDays.abs()} يوم' : 'Expired')
                                : (isRtl ? 'متبقي ${timing.remainingDays} يوم' : '${timing.remainingDays} days left'),
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                              color: timing.isExpired
                                  ? const Color(0xFFB91C1C)
                                  : (timing.isExpiringSoon ? const Color(0xFFB45309) : const Color(0xFF15803D)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    Text(
                      '${NumberFormat('#,##0', 'en').format(ad.price)} ${isRtl ? 'جنيه سوداني' : 'SDG'}',
                      style: const TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.w900, fontSize: 15.5),
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
    bool isDeleting = false;
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) => AlertDialog(
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
                  color: Colors.red.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(Icons.warning_rounded, color: Colors.red.shade600, size: 28),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Text(
                  isRtl ? 'تأكيد حذف الحملة الإعلانية' : 'Delete Campaign',
                  style: TextStyle(color: _darkColor, fontWeight: FontWeight.w900, fontSize: 18),
                ),
              ),
            ],
          ),
          content: Text(
            isRtl
                ? 'هل أنت متأكد من حذف الإعلان "${ad.title}" الخاص بـ "${ad.company}"؟ لا يمكن التراجع عن هذا الإجراء.'
                : 'Are you sure you want to delete "${ad.title}"? This action cannot be undone.',
            style: TextStyle(color: Colors.grey.shade700, fontSize: 14.5, fontWeight: FontWeight.w500),
          ),
          actionsPadding: const EdgeInsets.all(24),
          actions: [
            TextButton(
              onPressed: isDeleting ? null : () => Navigator.pop(ctx),
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
              onPressed: isDeleting
                  ? null
                  : () async {
                      setDlgState(() => isDeleting = true);
                      try {
                        await ref.read(adsProvider.notifier).deleteAd(ad.id);
                        if (ctx.mounted) Navigator.pop(ctx);
                        if (mounted) {
                          ScaffoldMessenger.of(this.context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'تم حذف الحملة الإعلانية بنجاح' : 'Campaign deleted successfully'),
                              backgroundColor: const Color(0xFF0F172A),
                            ),
                          );
                        }
                      } catch (e) {
                        setDlgState(() => isDeleting = false);
                        if (ctx.mounted) {
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'فشل حذف الحملة: $e' : 'Failed to delete: $e'),
                              backgroundColor: Colors.red.shade700,
                            ),
                          );
                        }
                      }
                    },
              child: isDeleting
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : Text(isRtl ? 'تأكيد الحذف النهائي' : 'Confirm Delete', style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  List<AdModel> _filterAndSortAds(List<AdModel> list) {
    final query = _searchQuery.toLowerCase().trim();
    var filtered = query.isEmpty
        ? List<AdModel>.from(list)
        : list
            .where(
              (ad) =>
                  ad.title.toLowerCase().contains(query) ||
                  ad.company.toLowerCase().contains(query) ||
                  ad.target.toLowerCase().contains(query) ||
                  ad.startDate.contains(query) ||
                  ad.endDate.contains(query),
            )
            .toList();

    switch (_sortOption) {
      case _AdSortOption.newest:
        break;
      case _AdSortOption.highestPrice:
        filtered.sort((a, b) => b.price.compareTo(a.price));
        break;
      case _AdSortOption.endingSoonest:
        filtered.sort((a, b) {
          final endA = DateTime.tryParse(a.endDate) ?? DateTime(2099);
          final endB = DateTime.tryParse(b.endDate) ?? DateTime(2099);
          return endA.compareTo(endB);
        });
        break;
      case _AdSortOption.companyAZ:
        filtered.sort((a, b) => a.company.compareTo(b.company));
        break;
    }
    return filtered;
  }

  @override
  Widget build(BuildContext context) {
    final ads = ref.watch(adsProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat('#,##0', 'en');

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      body: ads.when(
        data: (adList) {
          final totalRevenue = adList.fold<double>(0.0, (sum, ad) => sum + ad.price);
          final activeValidList = adList.where((ad) => ad.isActive && !_analyzeAdTiming(ad).isExpired).toList();
          final expiredButActiveList = adList.where((ad) => ad.isActive && _analyzeAdTiming(ad).isExpired).toList();
          final expiringSoonList = adList.where((ad) {
            final t = _analyzeAdTiming(ad);
            return ad.isActive && (t.isExpiringSoon || t.isExpired);
          }).toList();
          final pausedList = adList.where((ad) => !ad.isActive).toList();

          final activeTabAds = _filterAndSortAds(adList.where((ad) => ad.isActive).toList());
          final expiringTabAds = _filterAndSortAds(expiringSoonList);
          final pausedTabAds = _filterAndSortAds(pausedList);
          final allTabAds = _filterAndSortAds(adList);

          return Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ==========================================
              // Executive Top Header & KPI Strip
              // ==========================================
              Container(
                padding: const EdgeInsets.fromLTRB(28, 22, 28, 16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Executive Banner Row
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF0F172A), Color(0xFF1E3A8A), Color(0xFF1D4ED8)],
                          begin: Alignment.centerRight,
                          end: Alignment.centerLeft,
                        ),
                        borderRadius: BorderRadius.circular(18),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF1E3A8A).withValues(alpha: 0.22),
                            blurRadius: 16,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: Wrap(
                        alignment: WrapAlignment.spaceBetween,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        spacing: 16,
                        runSpacing: 12,
                        children: [
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(11),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.14),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                                ),
                                child: const Icon(Icons.campaign_rounded, color: Colors.white, size: 28),
                              ),
                              const SizedBox(width: 14),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        isRtl ? 'مركز إدارة الحملات الإعلانية والبانرات' : 'Advertising Campaigns & Banners Hub',
                                        style: const TextStyle(
                                          fontSize: 20,
                                          fontWeight: FontWeight.w900,
                                          color: Colors.white,
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFF10B981).withValues(alpha: 0.25),
                                          borderRadius: BorderRadius.circular(20),
                                          border: Border.all(color: const Color(0xFF34D399)),
                                        ),
                                        child: Text(
                                          isRtl ? 'متزامن لحظياً' : 'Live Sync',
                                          style: const TextStyle(
                                            color: Color(0xFFA7F3D0),
                                            fontSize: 10.5,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 3),
                                  Text(
                                    isRtl
                                        ? 'إدارة عقود المعلنين، مراقبة صلاحية البانرات، والتحكم الفوري في الظهور على الشاشة الرئيسية للتطبيق'
                                        : 'Manage advertiser contracts, monitor banner expiration, and control home carousel visibility',
                                    style: TextStyle(
                                      color: Colors.white.withValues(alpha: 0.82),
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              OutlinedButton.icon(
                                onPressed: () => ref.invalidate(adsProvider),
                                icon: const Icon(Icons.refresh_rounded, size: 18, color: Colors.white),
                                label: Text(
                                  isRtl ? 'تحديث' : 'Refresh',
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                style: OutlinedButton.styleFrom(
                                  side: BorderSide(color: Colors.white.withValues(alpha: 0.35)),
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                              const SizedBox(width: 10),
                              FilledButton.icon(
                                onPressed: () => _showCreateOrEditAdDialog(isRtl: isRtl),
                                icon: const Icon(Icons.add_circle_outline_rounded, size: 20),
                                label: Text(
                                  isRtl ? 'إطلاق حملة جديدة' : 'Launch Campaign',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5),
                                ),
                                style: FilledButton.styleFrom(
                                  backgroundColor: const Color(0xFF10B981),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    // 5 Executive KPI Cards Row
                    LayoutBuilder(
                      builder: (context, constraints) {
                        final kpis = [
                          _buildExecutiveKpiCard(
                            label: isRtl ? 'إجمالي الحملات' : 'Total Campaigns',
                            value: '${adList.length}',
                            subtitle: isRtl ? 'كافة العقود المسجلة' : 'All registered contracts',
                            icon: Icons.layers_rounded,
                            color: const Color(0xFF4F46E5),
                          ),
                          _buildExecutiveKpiCard(
                            label: isRtl ? 'نشطة وسارية' : 'Active & Valid',
                            value: '${activeValidList.length}',
                            subtitle: isRtl ? 'تظهر حالياً للمشترين' : 'Currently live in app',
                            icon: Icons.play_circle_fill_rounded,
                            color: const Color(0xFF059669),
                          ),
                          _buildExecutiveKpiCard(
                            label: isRtl ? 'إجمالي عوائد الإعلانات' : 'Total Ad Revenue',
                            value: '${currencyFmt.format(totalRevenue)} ${isRtl ? 'ج.س' : 'SDG'}',
                            subtitle: adList.isEmpty
                                ? (isRtl ? 'لا توجد عقود' : 'No contracts')
                                : (isRtl
                                    ? 'متوسط الحملة: ${currencyFmt.format(totalRevenue / adList.length)} ج.س'
                                    : 'Avg: ${currencyFmt.format(totalRevenue / adList.length)} SDG'),
                            icon: Icons.payments_rounded,
                            color: const Color(0xFF0284C7),
                          ),
                          _buildExecutiveKpiCard(
                            label: isRtl ? 'تنتهي قريباً / منتهية' : 'Expiring / Expired',
                            value: '${expiringSoonList.length}',
                            subtitle: expiredButActiveList.isNotEmpty
                                ? (isRtl ? '${expiredButActiveList.length} تجاوزت تاريخ النهاية' : '${expiredButActiveList.length} past end date')
                                : (isRtl ? 'خلال 7 أيام قادمة' : 'Within next 7 days'),
                            icon: Icons.notification_important_rounded,
                            color: expiringSoonList.isNotEmpty ? const Color(0xFFD97706) : Colors.grey.shade600,
                          ),
                          _buildExecutiveKpiCard(
                            label: isRtl ? 'الحملات المتوقفة' : 'Paused Campaigns',
                            value: '${pausedList.length}',
                            subtitle: isRtl ? 'مؤرشفة أو موقوفة مؤقتاً' : 'Archived or paused',
                            icon: Icons.pause_circle_filled_rounded,
                            color: const Color(0xFF64748B),
                          ),
                        ];

                        if (constraints.maxWidth > 1050) {
                          return Row(
                            children: [
                              for (int i = 0; i < kpis.length; i++) ...[
                                Expanded(child: kpis[i]),
                                if (i < kpis.length - 1) const SizedBox(width: 12),
                              ],
                            ],
                          );
                        } else {
                          return Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            children: kpis
                                .map((card) => SizedBox(width: (constraints.maxWidth - 12) / 2, child: card))
                                .toList(),
                          );
                        }
                      },
                    ),

                    // Operational Alert Bar if any active campaign is expired
                    if (expiredButActiveList.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF2F2),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFFECACA)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.warning_amber_rounded, color: Color(0xFFDC2626), size: 20),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                isRtl
                                    ? 'تنبيه تشغيلي: يوجد (${expiredButActiveList.length}) حملة إعلانية انتهى تاريخ صلاحيتها ولكنها لا تزال مفعلة بالعرض.'
                                    : 'Alert: ${expiredButActiveList.length} campaign(s) have passed their end date but are still marked active.',
                                style: const TextStyle(
                                  color: Color(0xFF991B1B),
                                  fontWeight: FontWeight.w800,
                                  fontSize: 12.5,
                                ),
                              ),
                            ),
                            TextButton.icon(
                              onPressed: () => _deactivateAllExpiredAds(expiredButActiveList, isRtl),
                              icon: const Icon(Icons.stop_circle_outlined, size: 16, color: Color(0xFFDC2626)),
                              label: Text(
                                isRtl ? 'إيقاف المنتهية تلقائياً' : 'Pause Expired Now',
                                style: const TextStyle(
                                  color: Color(0xFFDC2626),
                                  fontWeight: FontWeight.w900,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],

                    const SizedBox(height: 14),

                    // Tabs, Search, Sort & View Switcher Toolbar
                    LayoutBuilder(
                      builder: (context, constraints) {
                        final isWide = constraints.maxWidth > 1050;

                        final tabsWidget = Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: TabBar(
                            controller: _tabController,
                            isScrollable: true,
                            dividerColor: Colors.transparent,
                            indicator: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(9),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.06),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            indicatorSize: TabBarIndicatorSize.tab,
                            labelColor: _primaryColor,
                            unselectedLabelColor: Colors.grey.shade600,
                            labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                            unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                            tabAlignment: TabAlignment.start,
                            tabs: [
                              Tab(
                                height: 34,
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 12),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.play_circle_filled_rounded, size: 16),
                                      const SizedBox(width: 6),
                                      Text(isRtl ? 'النشطة (${activeTabAds.length})' : 'Active (${activeTabAds.length})'),
                                    ],
                                  ),
                                ),
                              ),
                              Tab(
                                height: 34,
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 12),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        Icons.schedule_rounded,
                                        size: 16,
                                        color: expiringTabAds.isNotEmpty ? const Color(0xFFD97706) : null,
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        isRtl
                                            ? 'تحتاج تجديد / منتهية (${expiringTabAds.length})'
                                            : 'Expiring / Expired (${expiringTabAds.length})',
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              Tab(
                                height: 34,
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 12),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.stop_circle_rounded, size: 16),
                                      const SizedBox(width: 6),
                                      Text(isRtl ? 'المتوقفة (${pausedTabAds.length})' : 'Paused (${pausedTabAds.length})'),
                                    ],
                                  ),
                                ),
                              ),
                              Tab(
                                height: 34,
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 12),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.all_inclusive_rounded, size: 16),
                                      const SizedBox(width: 6),
                                      Text(isRtl ? 'الكل (${allTabAds.length})' : 'All (${allTabAds.length})'),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        );

                        final controlsRow = Wrap(
                          spacing: 10,
                          runSpacing: 8,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            // Search input
                            SizedBox(
                              width: 230,
                              height: 38,
                              child: TextField(
                                controller: _searchCtrl,
                                onChanged: (v) => setState(() => _searchQuery = v),
                                style: TextStyle(fontSize: 12.5, color: _darkColor, fontWeight: FontWeight.w600),
                                decoration: InputDecoration(
                                  hintText: isRtl ? 'بحث بالعنوان، الشركة، الوسم...' : 'Search title, company, tag...',
                                  hintStyle: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                                  prefixIcon: Icon(Icons.search_rounded, size: 17, color: Colors.grey.shade500),
                                  suffixIcon: _searchQuery.isNotEmpty
                                      ? IconButton(
                                          icon: const Icon(Icons.clear_rounded, size: 15),
                                          onPressed: () {
                                            _searchCtrl.clear();
                                            setState(() => _searchQuery = '');
                                          },
                                        )
                                      : null,
                                  filled: true,
                                  fillColor: const Color(0xFFF8FAFC),
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
                            ),

                            // Sort dropdown
                            Container(
                              height: 38,
                              padding: const EdgeInsets.symmetric(horizontal: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: DropdownButtonHideUnderline(
                                child: DropdownButton<_AdSortOption>(
                                  value: _sortOption,
                                  icon: Icon(Icons.sort_rounded, size: 17, color: _primaryColor),
                                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: _darkColor),
                                  onChanged: (val) {
                                    if (val != null) setState(() => _sortOption = val);
                                  },
                                  items: [
                                    DropdownMenuItem(
                                      value: _AdSortOption.newest,
                                      child: Text(isRtl ? 'الترتيب: الأحدث' : 'Sort: Newest'),
                                    ),
                                    DropdownMenuItem(
                                      value: _AdSortOption.highestPrice,
                                      child: Text(isRtl ? 'الأعلى قيمة عقد' : 'Highest Contract'),
                                    ),
                                    DropdownMenuItem(
                                      value: _AdSortOption.endingSoonest,
                                      child: Text(isRtl ? 'الأقرب انتهاءً' : 'Ending Soonest'),
                                    ),
                                    DropdownMenuItem(
                                      value: _AdSortOption.companyAZ,
                                      child: Text(isRtl ? 'اسم الشركة (أ-ي)' : 'Company (A-Z)'),
                                    ),
                                  ],
                                ),
                              ),
                            ),

                            // View Toggle (Table / Cards)
                            Container(
                              padding: const EdgeInsets.all(3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  InkWell(
                                    onTap: () => setState(() => _isTableView = true),
                                    borderRadius: BorderRadius.circular(7),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: _isTableView ? _primaryColor : Colors.transparent,
                                        borderRadius: BorderRadius.circular(7),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            Icons.table_rows_rounded,
                                            size: 15,
                                            color: _isTableView ? Colors.white : Colors.grey.shade700,
                                          ),
                                          const SizedBox(width: 5),
                                          Text(
                                            isRtl ? 'جدول' : 'Table',
                                            style: TextStyle(
                                              fontSize: 12,
                                              fontWeight: FontWeight.bold,
                                              color: _isTableView ? Colors.white : Colors.grey.shade700,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  InkWell(
                                    onTap: () => setState(() => _isTableView = false),
                                    borderRadius: BorderRadius.circular(7),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: !_isTableView ? _primaryColor : Colors.transparent,
                                        borderRadius: BorderRadius.circular(7),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            Icons.grid_view_rounded,
                                            size: 15,
                                            color: !_isTableView ? Colors.white : Colors.grey.shade700,
                                          ),
                                          const SizedBox(width: 5),
                                          Text(
                                            isRtl ? 'بطاقات' : 'Cards',
                                            style: TextStyle(
                                              fontSize: 12,
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
                            ),
                          ],
                        );

                        if (isWide) {
                          return Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Flexible(child: tabsWidget),
                              const SizedBox(width: 12),
                              controlsRow,
                            ],
                          );
                        } else {
                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              tabsWidget,
                              const SizedBox(height: 10),
                              controlsRow,
                            ],
                          );
                        }
                      },
                    ),
                  ],
                ),
              ),

              // ==========================================
              // Content Area (Full-Width Table or Cards Grid)
              // ==========================================
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    _isTableView ? _buildAdsTable(context, isRtl, activeTabAds) : _buildAdsGrid(context, isRtl, activeTabAds),
                    _isTableView ? _buildAdsTable(context, isRtl, expiringTabAds) : _buildAdsGrid(context, isRtl, expiringTabAds),
                    _isTableView ? _buildAdsTable(context, isRtl, pausedTabAds) : _buildAdsGrid(context, isRtl, pausedTabAds),
                    _isTableView ? _buildAdsTable(context, isRtl, allTabAds) : _buildAdsGrid(context, isRtl, allTabAds),
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

  Widget _buildExecutiveKpiCard({
    required String label,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Colors.grey.shade600),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: _darkColor, height: 1.15),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: color),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(bool isRtl) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(28),
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 24, offset: const Offset(0, 8)),
              ],
            ),
            child: Icon(Icons.campaign_outlined, size: 64, color: _primaryColor.withValues(alpha: 0.4)),
          ),
          const SizedBox(height: 20),
          Text(
            isRtl ? 'لا توجد حملات مطابقة في هذا القسم' : 'No matching campaigns in this tab',
            style: TextStyle(color: _darkColor, fontSize: 17, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 6),
          Text(
            isRtl
                ? 'يمكنك إطلاق حملة إعلانية جديدة أو تعديل معايير البحث أعلاه.'
                : 'Launch a new campaign or adjust your search filter above.',
            style: TextStyle(color: Colors.grey.shade500, fontSize: 13, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // 100% Full-Width Responsive Table View
  // ==========================================
  Widget _buildAdsTable(BuildContext context, bool isRtl, List<AdModel> filteredAds) {
    if (filteredAds.isEmpty) {
      return _buildEmptyState(isRtl);
    }

    final currencyFmt = NumberFormat('#,##0', 'en');

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 20),
      child: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 16,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: LayoutBuilder(
          builder: (context, constraints) {
            final tableContent = Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Table Header
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    border: Border(
                      bottom: BorderSide(color: Colors.grey.shade200, width: 1.5),
                    ),
                  ),
                  child: Row(
                    children: [
                      _buildTableHeaderCell(title: '#', width: 44, alignment: Alignment.center, isRtl: isRtl),
                      _buildTableHeaderCell(
                        title: isRtl ? 'البانر' : 'Banner',
                        width: 112,
                        alignment: Alignment.center,
                        isRtl: isRtl,
                      ),
                      _buildTableHeaderCell(
                        title: isRtl ? 'عنوان الإعلان والوسم الترويجي' : 'Campaign Title & Tag',
                        flex: 3,
                        isRtl: isRtl,
                      ),
                      _buildTableHeaderCell(
                        title: isRtl ? 'المعلن / الشركة' : 'Advertiser',
                        flex: 2,
                        isRtl: isRtl,
                      ),
                      _buildTableHeaderCell(
                        title: isRtl ? 'الفترة الزمنية والمدة المتبقية' : 'Schedule & Remaining',
                        flex: 3,
                        isRtl: isRtl,
                      ),
                      _buildTableHeaderCell(
                        title: isRtl ? 'قيمة العقد' : 'Contract Value',
                        flex: 2,
                        alignment: Alignment.center,
                        isRtl: isRtl,
                      ),
                      _buildTableHeaderCell(
                        title: isRtl ? 'الحالة' : 'Status',
                        flex: 2,
                        alignment: Alignment.center,
                        isRtl: isRtl,
                      ),
                      _buildTableHeaderCell(
                        title: isRtl ? 'الإجراءات والتحكم' : 'Actions',
                        width: 210,
                        alignment: Alignment.center,
                        isRtl: isRtl,
                      ),
                    ],
                  ),
                ),

                // Table Rows
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
                      onQuickExtend: () => _quickExtendCampaign(ad, 30, isRtl),
                      onToggleStatus: () => ref.read(adsProvider.notifier).toggleStatus(ad.id),
                      onDelete: () => _showDeleteConfirmation(ad, isRtl),
                    );
                  },
                ),
              ],
            );

            if (constraints.maxWidth >= 960) {
              return SizedBox(width: constraints.maxWidth, child: tableContent);
            }
            return SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: SizedBox(width: 960, child: tableContent),
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
      padding: const EdgeInsets.symmetric(horizontal: 6),
      child: Text(
        title,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
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
          padding: const EdgeInsets.all(28),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            childAspectRatio: 0.78,
            crossAxisSpacing: 22,
            mainAxisSpacing: 22,
          ),
          itemCount: filteredAds.length,
          itemBuilder: (context, index) {
            final ad = filteredAds[index];
            return _AdHoverCard(
              ad: ad,
              isRtl: isRtl,
              primaryColor: _primaryColor,
              darkColor: _darkColor,
              onPreview: () => _showBannerPreviewDialog(ad, isRtl),
              onEdit: () => _showCreateOrEditAdDialog(existingAd: ad, isRtl: isRtl),
              onQuickExtend: () => _quickExtendCampaign(ad, 30, isRtl),
              onToggleStatus: () => ref.read(adsProvider.notifier).toggleStatus(ad.id),
              onDelete: () => _showDeleteConfirmation(ad, isRtl),
            );
          },
        );
      },
    );
  }
}

// ==========================================
// Table Row Widget with Timing & Progress
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
  final VoidCallback onQuickExtend;
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
    required this.onQuickExtend,
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
    final timing = _analyzeAdTiming(ad);
    final defaultBg = widget.index.isEven ? const Color(0xFFF8FAFC).withValues(alpha: 0.6) : Colors.white;

    final Color progressColor = timing.isExpired
        ? const Color(0xFFDC2626)
        : (timing.isExpiringSoon ? const Color(0xFFD97706) : const Color(0xFF10B981));

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
              width: 44,
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

            // 2. Banner Thumbnail
            SizedBox(
              width: 112,
              child: Center(
                child: Tooltip(
                  message: isRtl ? 'اضغط لمعاينة وتكبير البانر' : 'Click to preview banner',
                  child: InkWell(
                    onTap: widget.onPreview,
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      width: 96,
                      height: 48,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade300),
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
                          Container(color: Colors.black.withValues(alpha: 0.14)),
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
                padding: const EdgeInsets.symmetric(horizontal: 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      ad.title,
                      style: TextStyle(
                        color: widget.darkColor,
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                        height: 1.25,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 5),
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
                            Flexible(
                              child: Text(
                                ad.target,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Color(0xFFB45309),
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
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
                        color: widget.primaryColor.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(7),
                      ),
                      child: Icon(Icons.business_rounded, size: 15, color: widget.primaryColor),
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
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // 5. Campaign Schedule & Remaining Days Progress
            Expanded(
              flex: 3,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.date_range_rounded, size: 13, color: Colors.grey.shade600),
                        const SizedBox(width: 5),
                        Flexible(
                          child: Text(
                            '${ad.startDate}  ➜  ${ad.endDate}',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(color: Colors.grey.shade800, fontSize: 12, fontWeight: FontWeight.w700),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: timing.progress,
                        minHeight: 5,
                        backgroundColor: const Color(0xFFE2E8F0),
                        valueColor: AlwaysStoppedAnimation<Color>(progressColor),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          timing.isExpired
                              ? (isRtl ? 'انتهت منذ ${timing.remainingDays.abs()} يوم' : 'Expired ${timing.remainingDays.abs()}d ago')
                              : (timing.remainingDays == 0
                                  ? (isRtl ? 'تنتهي اليوم!' : 'Ends today!')
                                  : (isRtl ? 'متبقي ${timing.remainingDays} يوم' : '${timing.remainingDays} days left')),
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: progressColor,
                          ),
                        ),
                        Text(
                          isRtl ? 'المدة: ${timing.totalDays} يوم' : '${timing.totalDays}d total',
                          style: TextStyle(fontSize: 10.5, color: Colors.grey.shade500, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            // 6. Contract Value
            Expanded(
              flex: 2,
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
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Color(0xFF065F46),
                      fontWeight: FontWeight.w900,
                      fontSize: 12.5,
                    ),
                  ),
                ),
              ),
            ),

            // 7. Smart Status Badge
            Expanded(
              flex: 2,
              child: Center(
                child: _buildSmartStatusBadge(ad, timing, isRtl),
              ),
            ),

            // 8. Action Icons Bar
            SizedBox(
              width: 210,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _buildActionButton(
                    icon: Icons.fullscreen_rounded,
                    tooltip: isRtl ? 'معاينة البانر بالحجم الكامل' : 'Full Preview',
                    color: const Color(0xFF0284C7),
                    bgColor: const Color(0xFFE0F2FE),
                    onTap: widget.onPreview,
                  ),
                  const SizedBox(width: 6),
                  _buildActionButton(
                    icon: Icons.update_rounded,
                    tooltip: isRtl ? 'تمديد سريع +30 يوم' : 'Quick Extend +30d',
                    color: const Color(0xFF7C3AED),
                    bgColor: const Color(0xFFEDE9FE),
                    onTap: widget.onQuickExtend,
                  ),
                  const SizedBox(width: 6),
                  _buildActionButton(
                    icon: ad.isActive ? Icons.pause_rounded : Icons.play_arrow_rounded,
                    tooltip: ad.isActive
                        ? (isRtl ? 'إيقاف مؤقت للحملة' : 'Pause Campaign')
                        : (isRtl ? 'تنشيط وإطلاق الحملة' : 'Activate Campaign'),
                    color: ad.isActive ? const Color(0xFFD97706) : const Color(0xFF059669),
                    bgColor: ad.isActive ? const Color(0xFFFEF3C7) : const Color(0xFFD1FAE5),
                    onTap: widget.onToggleStatus,
                  ),
                  const SizedBox(width: 6),
                  _buildActionButton(
                    icon: Icons.edit_note_rounded,
                    tooltip: isRtl ? 'تعديل بيانات الحملة' : 'Edit Campaign',
                    color: widget.primaryColor,
                    bgColor: widget.primaryColor.withValues(alpha: 0.08),
                    onTap: widget.onEdit,
                  ),
                  const SizedBox(width: 6),
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

  Widget _buildSmartStatusBadge(AdModel ad, _CampaignTimingInfo timing, bool isRtl) {
    if (!ad.isActive) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFCBD5E1)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 7,
              height: 7,
              decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.grey.shade600),
            ),
            const SizedBox(width: 5),
            Text(
              isRtl ? 'متوقف' : 'Paused',
              style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w800, fontSize: 11.5),
            ),
          ],
        ),
      );
    }

    if (timing.isExpired) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: const Color(0xFFFEE2E2),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFFCA5A5)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline_rounded, size: 13, color: Color(0xFFDC2626)),
            const SizedBox(width: 4),
            Text(
              isRtl ? 'انتهت المدة' : 'Expired',
              style: const TextStyle(color: Color(0xFFB91C1C), fontWeight: FontWeight.w800, fontSize: 11),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: const Color(0xFFDCFCE7),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFF86EFAC)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: const BoxDecoration(shape: BoxShape.circle, color: Color(0xFF16A34A)),
          ),
          const SizedBox(width: 5),
          Text(
            isRtl ? 'نشط وساري' : 'Active',
            style: const TextStyle(color: Color(0xFF15803D), fontWeight: FontWeight.w800, fontSize: 11.5),
          ),
        ],
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
            border: Border.all(color: color.withValues(alpha: 0.2)),
          ),
          child: Icon(icon, color: color, size: 17),
        ),
      ),
    );
  }
}

// ==========================================
// Hover Card Widget with Timing & Quick Renew
// ==========================================
class _AdHoverCard extends StatefulWidget {
  final AdModel ad;
  final bool isRtl;
  final Color primaryColor;
  final Color darkColor;
  final VoidCallback onPreview;
  final VoidCallback onEdit;
  final VoidCallback onQuickExtend;
  final VoidCallback onToggleStatus;
  final VoidCallback onDelete;

  const _AdHoverCard({
    required this.ad,
    required this.isRtl,
    required this.primaryColor,
    required this.darkColor,
    required this.onPreview,
    required this.onEdit,
    required this.onQuickExtend,
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
    final timing = _analyzeAdTiming(ad);

    return MouseRegion(
      onEnter: (_) => setState(() => _isHovered = true),
      onExit: (_) => setState(() => _isHovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOutCubic,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: _isHovered ? widget.primaryColor.withValues(alpha: 0.35) : Colors.grey.shade200,
            width: 1.5,
          ),
          boxShadow: [
            BoxShadow(
              color: _isHovered
                  ? widget.primaryColor.withValues(alpha: 0.12)
                  : Colors.black.withValues(alpha: 0.04),
              blurRadius: _isHovered ? 18 : 8,
              offset: Offset(0, _isHovered ? 8 : 4),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                flex: 4,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    InkWell(
                      onTap: widget.onPreview,
                      child: Container(
                        color: const Color(0xFF0F172A),
                        child: Image.network(
                          ad.imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (c, e, s) => Container(
                            color: Colors.grey.shade100,
                            child: const Icon(Icons.image_not_supported_rounded, color: Colors.grey, size: 40),
                          ),
                        ),
                      ),
                    ),
                    IgnorePointer(
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              Colors.black.withValues(alpha: 0.6),
                              Colors.transparent,
                              Colors.black.withValues(alpha: 0.35),
                            ],
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      top: 12,
                      left: isRtl ? null : 12,
                      right: isRtl ? 12 : null,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: !ad.isActive
                              ? const Color(0xFF64748B)
                              : (timing.isExpired ? const Color(0xFFDC2626) : const Color(0xFF10B981)),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          !ad.isActive
                              ? (isRtl ? 'متوقف' : 'Paused')
                              : (timing.isExpired
                                  ? (isRtl ? 'انتهت المدة' : 'Expired')
                                  : (isRtl ? 'نشط • متبقي ${timing.remainingDays} يوم' : 'Active • ${timing.remainingDays}d')),
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    if (ad.target.isNotEmpty)
                      Positioned(
                        bottom: 12,
                        right: isRtl ? null : 12,
                        left: isRtl ? 12 : null,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.65),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.sell_rounded, color: Colors.amber, size: 11),
                              const SizedBox(width: 4),
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
              Expanded(
                flex: 5,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        ad.title,
                        style: TextStyle(
                          color: widget.darkColor,
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          height: 1.25,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              ad.company,
                              style: TextStyle(
                                color: Colors.grey.shade800,
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
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
                                fontSize: 11.5,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.date_range_rounded, size: 13, color: Colors.grey.shade500),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                '${ad.startDate}  ➜  ${ad.endDate}',
                                style: TextStyle(
                                  color: Colors.grey.shade700,
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: FilledButton.icon(
                              onPressed: widget.onEdit,
                              icon: const Icon(Icons.edit_rounded, size: 14),
                              label: Text(isRtl ? 'تعديل' : 'Edit', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFFF1F5F9),
                                foregroundColor: widget.primaryColor,
                                elevation: 0,
                                padding: const EdgeInsets.symmetric(vertical: 8),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Tooltip(
                            message: isRtl ? 'تمديد +30 يوم' : 'Extend +30d',
                            child: InkWell(
                              onTap: widget.onQuickExtend,
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 34,
                                height: 34,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEDE9FE),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.update_rounded, color: Color(0xFF7C3AED), size: 17),
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Tooltip(
                            message: ad.isActive ? (isRtl ? 'إيقاف الحملة' : 'Pause') : (isRtl ? 'تنشيط' : 'Activate'),
                            child: InkWell(
                              onTap: widget.onToggleStatus,
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 34,
                                height: 34,
                                decoration: BoxDecoration(
                                  color: ad.isActive ? const Color(0xFFFEF3C7) : const Color(0xFFD1FAE5),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Icon(
                                  ad.isActive ? Icons.pause_rounded : Icons.play_arrow_rounded,
                                  color: ad.isActive ? const Color(0xFFD97706) : const Color(0xFF059669),
                                  size: 17,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Tooltip(
                            message: isRtl ? 'حذف الحملة' : 'Delete',
                            child: InkWell(
                              onTap: widget.onDelete,
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 34,
                                height: 34,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFEE2E2),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.delete_outline_rounded, color: Color(0xFFDC2626), size: 17),
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
