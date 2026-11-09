import 'package:flutter/material.dart';

class FeaturedDealsWidget extends StatelessWidget {
  const FeaturedDealsWidget({super.key});

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final deals = [
      {
        'titleAr': 'خصم خاص على حديد سابك',
        'titleEn': 'Special Discount on SABIC Rebar',
        'descAr': 'وفر حتى 5% عند طلب أكثر من 25 طن',
        'descEn': 'Save up to 5% on 25+ ton orders',
        'tagAr': 'صفقة الأسبوع',
        'tagEn': 'Weekly Deal',
        'color': const Color(0xFF1E3A8A),
        'icon': Icons.local_fire_department,
      },
      {
        'titleAr': 'عروض الأسمنت البورتلاندي',
        'titleEn': 'Portland Cement Bulk Offers',
        'descAr': 'شحن مجاني للطلبات فوق 500 كيس',
        'descEn': 'Free delivery on 500+ bags',
        'tagAr': 'شحن مجاني',
        'tagEn': 'Free Delivery',
        'color': const Color(0xFF065F46),
        'icon': Icons.local_shipping,
      },
    ];

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
                  const Icon(Icons.flash_on, color: Colors.amber, size: 22),
                  const SizedBox(width: 6),
                  Text(
                    isRtl ? 'عروض وصفقات اليوم' : 'Daily Deals & Offers',
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              Text(
                isRtl ? 'عروض حصرية' : 'Exclusive',
                style: TextStyle(color: Colors.amber.shade800, fontSize: 12, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
        SizedBox(
          height: 125,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12.0),
            itemCount: deals.length,
            itemBuilder: (context, index) {
              final deal = deals[index];
              final title = isRtl ? deal['titleAr'] as String : deal['titleEn'] as String;
              final desc = isRtl ? deal['descAr'] as String : deal['descEn'] as String;
              final tag = isRtl ? deal['tagAr'] as String : deal['tagEn'] as String;
              final color = deal['color'] as Color;
              final icon = deal['icon'] as IconData;

              return Container(
                width: 280,
                margin: const EdgeInsets.symmetric(horizontal: 4.0, vertical: 4.0),
                padding: const EdgeInsets.all(14.0),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [color, color.withOpacity(0.85)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: color.withOpacity(0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            tag,
                            style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                        Icon(icon, color: Colors.amber, size: 20),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          desc,
                          style: TextStyle(color: Colors.white.withOpacity(0.9), fontSize: 11),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}