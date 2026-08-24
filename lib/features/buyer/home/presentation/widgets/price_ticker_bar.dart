import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/market_price_model.dart';
import 'package:intl/intl.dart' hide TextDirection;

class PriceTickerBar extends StatefulWidget {
  final List<MarketPriceModel> prices;

  const PriceTickerBar({super.key, required this.prices});

  @override
  State<PriceTickerBar> createState() => _PriceTickerBarState();
}

class _PriceTickerBarState extends State<PriceTickerBar> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;

  @override
  void initState() {
    super.initState();
    // 1. Use a single AnimationController with a long duration (30 seconds)
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 30),
    )..repeat();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.prices.isEmpty) return const SizedBox.shrink();

    // 2. Build the content as a Row of duplicated items (3x the list)
    final items = [...widget.prices, ...widget.prices, ...widget.prices];

    return Container(
      height: 40,
      color: Colors.black87,
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        physics: const NeverScrollableScrollPhysics(),
        // 3. Use an AnimatedBuilder to smoothly translate the content
        child: AnimatedBuilder(
          animation: _animationController,
          builder: (context, child) {
            final isRtl = Directionality.of(context) == TextDirection.rtl;
            // 4. When the scroll reaches the end of the first duplicate, reset to start seamlessly.
            // Since we have 3 copies, moving by 1/3 of the total width equals exactly one copy's width.
            // The jump back to 0 will be invisible.
            double dx = _animationController.value / 3.0;
            if (!isRtl) {
              dx = -dx;
            }
            return FractionalTranslation(
              translation: Offset(dx, 0),
              child: child,
            );
          },
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: items.map((item) {
              final format = NumberFormat.decimalPattern();
              final formattedPrice = format.format(item.currentPrice);
              final isUp = item.isUp;
              // 5. Keep the same dark background design, same arrow/color scheme
              final changeColor = isUp ? Colors.greenAccent : Colors.redAccent;
              final arrow = isUp ? '▲' : '▼';
              final changeStr = item.changePercent.toStringAsFixed(1);

              final isRtl = Directionality.of(context) == TextDirection.rtl;
              final name = isRtl ? item.materialNameAr : item.materialName;
              final unit = isRtl ? item.unitAr : item.unit;

              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$name  $formattedPrice $unit',
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      '$arrow ($changeStr%)',
                      style: TextStyle(color: changeColor, fontSize: 13, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(width: 16),
                    const Text('•', style: TextStyle(color: Colors.grey)),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ),
    );
  }
}
