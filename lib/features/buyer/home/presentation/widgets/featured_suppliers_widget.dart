import 'package:flutter/material.dart';

class FeaturedSuppliersWidget extends StatelessWidget {
  const FeaturedSuppliersWidget({super.key});

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final suppliers = [
      {'nameAr': 'شركة سابك للحديد', 'nameEn': 'SABIC Steel', 'typeAr': 'مصنع معتمد', 'typeEn': 'Certified Plant', 'rating': '4.9'},
      {'nameAr': 'حديد الراجحي', 'nameEn': 'Al-Rajhi Steel', 'typeAr': 'مورد رئيسي', 'typeEn': 'Main Supplier', 'rating': '4.8'},
      {'nameAr': 'أسمنت اليمامة', 'nameEn': 'Yamama Cement', 'typeAr': 'مصنع وطني', 'typeEn': 'National Plant', 'rating': '4.9'},
      {'nameAr': 'مستودعات السجانة', 'nameEn': 'El Sajana Hub', 'typeAr': 'مجمع مواد بناء', 'typeEn': 'Building Hub', 'rating': '5.0'},
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
                  const Icon(Icons.verified, color: Colors.blue, size: 20),
                  const SizedBox(width: 6),
                  Text(
                    isRtl ? 'المصانع والموردين المعتمدين' : 'Verified Manufacturers & Suppliers',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              Text(
                isRtl ? 'موثق' : 'Verified',
                style: const TextStyle(color: Colors.blue, fontSize: 12, fontWeight: FontWeight.bold),
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
              final name = isRtl ? s['nameAr']! : s['nameEn']!;
              final type = isRtl ? s['typeAr']! : s['typeEn']!;

              return Container(
                width: 175,
                margin: const EdgeInsets.symmetric(horizontal: 4.0, vertical: 4.0),
                padding: const EdgeInsets.all(10.0),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.6),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.withOpacity(0.2)),
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 18,
                      backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.15),
                      child: Icon(Icons.store, size: 18, color: Theme.of(context).colorScheme.primary),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                          ),
                          Row(
                            children: [
                              Text(type, style: const TextStyle(fontSize: 10, color: Colors.grey)),
                              const Spacer(),
                              const Icon(Icons.star, size: 12, color: Colors.amber),
                              Text(s['rating']!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ],
                      ),
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