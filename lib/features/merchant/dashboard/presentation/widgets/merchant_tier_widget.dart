import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';

class MerchantTierWidget extends StatelessWidget {
  final UserModel user;

  const MerchantTierWidget({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    final points = user.merchantPoints;
    final tier = user.merchantTier.toLowerCase();
    final isRtl = Directionality.of(context) == TextDirection.rtl;

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
              Row(
                children: [
                  Icon(tierIcon, color: tierColor, size: 32),
                  const SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'تاجر $tierName' : '$tierName Merchant',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: tierColor.withOpacity(0.8).withAlpha(255),
                        ),
                      ),
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
                ],
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
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildRule(Icons.check_circle, Colors.green, isRtl ? '+10 نقاط لكل طلب يتم تسليمه بنجاح' : '+10 pts for delivered orders'),
            _buildRule(Icons.monetization_on, Colors.amber, isRtl ? '+1 نقطة لكل 50,000 ج.س مبيعات' : '+1 pt per 50K SDG sales'),
            _buildRule(Icons.star, Colors.blue, isRtl ? '+50 نقطة مكافأة تميز من الإدارة' : '+50 pts Admin Bonus'),
            const Divider(),
            _buildRule(Icons.cancel, Colors.red, isRtl ? '-20 نقطة لإلغاء الطلب بعد قبوله' : '-20 pts for cancelling orders'),
          ],
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
