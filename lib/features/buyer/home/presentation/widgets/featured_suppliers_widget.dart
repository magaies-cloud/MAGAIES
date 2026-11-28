import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/phone_formatter.dart';
import 'package:magaiees_marketplace/features/admin/suppliers/application/verified_suppliers_provider.dart';

class FeaturedSuppliersWidget extends ConsumerWidget {
  const FeaturedSuppliersWidget({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final allSuppliers = ref.watch(verifiedSuppliersProvider);
    final suppliers = allSuppliers.where((s) => s.isActive).toList();

    if (suppliers.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.verified, color: Colors.blue, size: 20),
                  const SizedBox(width: 6),
                  Text(
                    isRtl
                        ? 'المصانع والموردين المعتمدين'
                        : 'Verified Manufacturers & Suppliers',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              Text(
                isRtl ? 'موثق' : 'Verified',
                style: const TextStyle(
                  color: Colors.blue,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
        SizedBox(
          height: 85,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12.0),
            itemCount: suppliers.length,
            itemBuilder: (context, index) {
              final s = suppliers[index];
              final name = isRtl
                  ? s.nameAr
                  : (s.nameEn.isNotEmpty ? s.nameEn : s.nameAr);
              final type = isRtl
                  ? s.typeAr
                  : (s.typeEn.isNotEmpty ? s.typeEn : s.typeAr);

              return InkWell(
                borderRadius: BorderRadius.circular(12),
                onTap: () => _showSupplierDetails(context, s, isRtl),
                child: Container(
                  width: 185,
                  margin: const EdgeInsets.symmetric(
                    horizontal: 4.0,
                    vertical: 4.0,
                  ),
                  padding: const EdgeInsets.all(10.0),
                  decoration: BoxDecoration(
                    color: Theme.of(context)
                        .colorScheme
                        .surfaceContainerHighest
                        .withValues(alpha: 0.6),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: Colors.grey.withValues(alpha: 0.2),
                    ),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 18,
                        backgroundColor: Theme.of(context)
                            .colorScheme
                            .primary
                            .withValues(alpha: 0.15),
                        backgroundImage: (s.logoUrl != null && s.logoUrl!.trim().isNotEmpty)
                            ? NetworkImage(s.logoUrl!.trim())
                            : null,
                        child: (s.logoUrl == null || s.logoUrl!.trim().isEmpty)
                            ? Icon(
                                _getIconForType(s.typeAr),
                                size: 18,
                                color: Theme.of(context).colorScheme.primary,
                              )
                            : null,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    name,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                if (s.isVerified) ...[
                                  const SizedBox(width: 3),
                                  const Icon(
                                    Icons.verified_rounded,
                                    size: 13,
                                    color: Colors.blue,
                                  ),
                                ],
                              ],
                            ),
                            const SizedBox(height: 2),
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    type,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey,
                                    ),
                                  ),
                                ),
                                const Icon(
                                  Icons.star,
                                  size: 12,
                                  color: Colors.amber,
                                ),
                                Text(
                                  s.rating.toStringAsFixed(1),
                                  style: const TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  IconData _getIconForType(String typeAr) {
    if (typeAr.contains('مصنع')) return Icons.factory_rounded;
    if (typeAr.contains('مجمع')) return Icons.warehouse_rounded;
    if (typeAr.contains('وكيل')) return Icons.workspace_premium_rounded;
    if (typeAr.contains('مقاولات') || typeAr.contains('خرسان')) return Icons.domain_rounded;
    if (typeAr.contains('تشطيبات')) return Icons.format_paint_rounded;
    if (typeAr.contains('هندسية')) return Icons.architecture_rounded;
    return Icons.store;
  }

  void _showSupplierDetails(
    BuildContext context,
    VerifiedSupplierModel s,
    bool isRtl,
  ) {
    final displayName = isRtl ? s.nameAr : (s.nameEn.isNotEmpty ? s.nameEn : s.nameAr);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 42,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
              Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundColor: Colors.blue.withValues(alpha: 0.12),
                    backgroundImage: (s.logoUrl != null && s.logoUrl!.trim().isNotEmpty)
                        ? NetworkImage(s.logoUrl!.trim())
                        : null,
                    child: (s.logoUrl == null || s.logoUrl!.trim().isEmpty)
                        ? Icon(
                            _getIconForType(s.typeAr),
                            color: Colors.blue.shade700,
                            size: 25,
                          )
                        : null,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                displayName,
                                style: const TextStyle(
                                  fontSize: 17,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ),
                            if (s.isVerified) ...[
                              const SizedBox(width: 6),
                              const Icon(
                                Icons.verified_rounded,
                                color: Colors.blue,
                                size: 18,
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          '${isRtl ? s.typeAr : s.typeEn} • ${s.city}',
                          style: TextStyle(
                            fontSize: 12.5,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.amber.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.star_rounded, color: Colors.amber, size: 16),
                        const SizedBox(width: 4),
                        Text(
                          s.rating.toStringAsFixed(1),
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(height: 1),
              const SizedBox(height: 14),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.inventory_2_outlined, size: 19, color: Color(0xFF2563EB)),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? 'التخصص والتوريدات المعتمدة' : 'Certified Specialty & Supplies',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey.shade600,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            s.specialty,
                            style: const TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              if (s.phone.trim().isNotEmpty) ...[
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFBBF7D0)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.phone_in_talk_rounded, size: 17, color: Color(0xFF059669)),
                      const SizedBox(width: 8),
                      Text(
                        isRtl ? 'هاتف التواصل:' : 'Contact Phone:',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF065F46),
                        ),
                      ),
                      const Spacer(),
                      PhoneFormatter.text(
                        s.phone,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF064E3B),
                          fontFamily: 'monospace',
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 11),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(11),
                          ),
                        ),
                        onPressed: () async {
                          final cleanPhone = s.phone.replaceAll(RegExp(r'[^\d+]'), '');
                          final uri = Uri.parse('tel:$cleanPhone');
                          try {
                            final ok = await launchUrl(uri);
                            if (!ok && context.mounted) {
                              AppNotification.showInfo(
                                context,
                                '${isRtl ? 'الاتصال بـ' : 'Calling'} $displayName (${PhoneFormatter.format(s.phone)})',
                                icon: Icons.call,
                              );
                            }
                          } catch (_) {
                            if (context.mounted) {
                              AppNotification.showInfo(
                                context,
                                '${isRtl ? 'الاتصال بـ' : 'Calling'} $displayName (${PhoneFormatter.format(s.phone)})',
                                icon: Icons.call,
                              );
                            }
                          }
                        },
                        icon: const Icon(Icons.call_rounded, size: 18),
                        label: Text(isRtl ? 'اتصال مباشر' : 'Call Now'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: FilledButton.icon(
                        style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFF25D366),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 11),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(11),
                          ),
                        ),
                        onPressed: () async {
                          var digits = s.phone.replaceAll(RegExp(r'[^\d]'), '');
                          if (digits.startsWith('00')) digits = digits.substring(2);
                          final uri = Uri.parse('https://wa.me/$digits');
                          try {
                            final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
                            if (!ok && context.mounted) {
                              AppNotification.showInfo(
                                context,
                                '${isRtl ? 'تواصل عبر واتساب مع' : 'WhatsApp'} $displayName',
                                icon: Icons.chat_bubble_outline,
                              );
                            }
                          } catch (_) {
                            if (context.mounted) {
                              AppNotification.showInfo(
                                context,
                                '${isRtl ? 'تواصل عبر واتساب مع' : 'WhatsApp'} $displayName',
                                icon: Icons.chat_bubble_outline,
                              );
                            }
                          }
                        },
                        icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18),
                        label: Text(isRtl ? 'تواصل واتساب' : 'WhatsApp'),
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
