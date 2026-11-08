import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/merchant/inventory/presentation/screens/merchant_inventory_screen.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class MerchantTierWidget extends ConsumerWidget {
  final UserModel user;
  final String? storeName;

  const MerchantTierWidget({super.key, required this.user, this.storeName});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final points = user.merchantPoints;
    final tier = user.merchantTier.toLowerCase();
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    String effectiveStoreName = (storeName ?? '').trim();
    if (effectiveStoreName.isEmpty) {
      final storeData = ref.watch(merchantStoreInfoProvider).valueOrNull;
      final sName = storeData?['name']?.toString().trim() ?? '';
      final sNameAr = storeData?['name_ar']?.toString().trim() ?? '';
      if (isRtl) {
        effectiveStoreName = sNameAr.isNotEmpty ? sNameAr : sName;
      } else {
        effectiveStoreName = sName.isNotEmpty ? sName : sNameAr;
      }
      if (effectiveStoreName.isEmpty) {
        effectiveStoreName = (isRtl ? (user.storeNameAr ?? user.storeName) : (user.storeName ?? user.storeNameAr)) ?? '';
      }
    }

    String tierName;
    Color tierColor;
    IconData tierIcon;
    int nextTierPoints = 0;

    if (tier == 'gold') {
      tierName = isRtl ? 'ذهبي' : 'Gold';
      tierColor = const Color(0xFFFFD700);
      tierIcon = Icons.military_tech;
      nextTierPoints = points; // Maxed out
    } else if (tier == 'silver') {
      tierName = isRtl ? 'فضي' : 'Silver';
      tierColor = const Color(0xFFC0C0C0);
      tierIcon = Icons.stars;
      nextTierPoints = 5000;
    } else if (tier == 'bronze') {
      tierName = isRtl ? 'برونزي' : 'Bronze';
      tierColor = const Color(0xFFCD7F32);
      tierIcon = Icons.star_half;
      nextTierPoints = 2000;
    } else {
      tierName = isRtl ? 'مبتدئ' : 'Beginner';
      tierColor = Colors.blueGrey;
      tierIcon = Icons.star_border;
      nextTierPoints = 500;
    }

    final double progress = tier == 'gold' ? 1.0 : (points / nextTierPoints).clamp(0.0, 1.0);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [tierColor.withOpacity(0.2), Colors.white],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: tierColor.withOpacity(0.5)),
        boxShadow: [
          BoxShadow(
            color: tierColor.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Row(
                  children: [
                    Icon(tierIcon, color: tierColor, size: 32),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                isRtl ? 'تاجر $tierName' : '$tierName Merchant',
                                style: TextStyle(
                                  fontSize: 17,
                                  fontWeight: FontWeight.bold,
                                  color: tierColor.withOpacity(0.8).withAlpha(255),
                                ),
                              ),
                              if (effectiveStoreName.isNotEmpty) ...[
                                const SizedBox(width: 8),
                                Text(
                                  '•',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.grey.shade400,
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Icon(
                                  Icons.storefront_rounded,
                                  size: 18,
                                  color: Colors.blueGrey.shade700,
                                ),
                                const SizedBox(width: 4),
                                Expanded(
                                  child: Text(
                                    effectiveStoreName,
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.black87,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '$points ${isRtl ? 'نقطة' : 'Pts'}',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: Colors.black87,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                icon: const Icon(Icons.help_outline, color: Colors.black54),
                onPressed: () => _showPointsInfoDialog(context, isRtl),
                tooltip: isRtl ? 'كيف أكسب النقاط؟' : 'How to earn points?',
              )
            ],
          ),
          if (tier != 'gold') ...[
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(isRtl ? 'التقدم للمستوى التالي' : 'Progress to next tier', style: const TextStyle(fontSize: 12, color: Colors.black54)),
                Text('${(progress * 100).toInt()}%', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: tierColor)),
              ],
            ),
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: LinearProgressIndicator(
                value: progress,
                minHeight: 8,
                backgroundColor: Colors.grey.shade300,
                valueColor: AlwaysStoppedAnimation<Color>(tierColor),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              isRtl 
                ? 'باقي ${nextTierPoints - points} نقطة للترقية'
                : '${nextTierPoints - points} pts to upgrade',
              style: const TextStyle(fontSize: 12, color: Colors.black54),
            ),
          ] else ...[
            const SizedBox(height: 16),
            Text(
              isRtl ? 'أنت في أعلى مستوى! حافظ على تميزك.' : 'You are at the top tier! Keep it up.',
              style: TextStyle(color: tierColor, fontWeight: FontWeight.bold),
            )
          ]
        ],
      ),
    );
  }

  void _showPointsInfoDialog(BuildContext context, bool isRtl) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.info_outline, color: Colors.blue),
            const SizedBox(width: 8),
            Text(isRtl ? 'كيف أكسب النقاط؟' : 'How to earn points?'),
          ],
        ),
        content: FutureBuilder<dynamic>(
          future: Supabase.instance.client.from('gamification_settings').select().eq('id', 1).maybeSingle(),
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const SizedBox(height: 100, child: Center(child: CircularProgressIndicator()));
            }
            
            int ptsPerOrder = 10;
            int threshold = 50000;
            int ptsPerThreshold = 1;
            int penalty = 20;
            
            if (snapshot.hasData && snapshot.data != null) {
              final d = snapshot.data as Map;
              ptsPerOrder = d['points_per_order'] ?? 10;
              threshold = d['sales_threshold_sdg'] ?? 50000;
              ptsPerThreshold = d['points_per_sales_threshold'] ?? 1;
              penalty = d['penalty_cancel_order'] ?? 20;
            }

            return Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildRule(Icons.check_circle, Colors.green, isRtl ? '+$ptsPerOrder نقاط لكل طلب يتم تسليمه بنجاح' : '+$ptsPerOrder pts for delivered orders'),
                _buildRule(Icons.monetization_on, Colors.amber, isRtl ? '+$ptsPerThreshold نقطة لكل ${threshold/1000} ألف ج.س مبيعات' : '+$ptsPerThreshold pt per ${threshold/1000}K SDG sales'),
                _buildRule(Icons.star, Colors.blue, isRtl ? '+ مكافآت تميز من الإدارة' : '+ Admin Bonus for excellence'),
                const Divider(),
                _buildRule(Icons.cancel, Colors.red, isRtl ? '-$penalty نقطة لإلغاء الطلب بعد قبوله' : '-$penalty pts for cancelling orders'),
              ],
            );
          }
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(isRtl ? 'فهمت' : 'Got it'),
          )
        ],
      ),
    );
  }

  Widget _buildRule(IconData icon, Color color, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 8),
          Expanded(child: Text(text, style: const TextStyle(fontSize: 14))),
        ],
      ),
    );
  }
}
