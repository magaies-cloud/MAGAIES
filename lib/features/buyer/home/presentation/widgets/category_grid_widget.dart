import 'package:magaiees_marketplace/core/utils/image_optimizer.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';

class CategoryGridWidget extends StatelessWidget {
  final List<CategoryModel> categories;

  const CategoryGridWidget({
    super.key,
    required this.categories,
  });

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final width = MediaQuery.of(context).size.width;
    final crossAxisCount = width > 800 ? 6 : 4;
    final horizontalPadding = 14.0 * 2;
    final totalSpacing = (crossAxisCount - 1) * 8.0;
    final columnWidth = (width - horizontalPadding - totalSpacing) / crossAxisCount;
    // Responsive card size: adapts fluidly to small and large phone screens
    final cardSize = (columnWidth * 0.86).clamp(56.0, 74.0);
    final iconSize = (cardSize * 0.44).clamp(24.0, 36.0);
    final cardRadius = (cardSize * 0.28).clamp(14.0, 20.0);
    final visibleCategories = categories.where((c) => c.isVisible).toList();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14.0),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: crossAxisCount,
          mainAxisSpacing: 14,
          crossAxisSpacing: 8,
          childAspectRatio: width > 800 ? 0.76 : (width < 380 ? 0.65 : 0.68),
        ),
        itemCount: visibleCategories.length + 1,
        itemBuilder: (context, index) {
          // 11th item: Service Providers & Tradesmen
          if (index == visibleCategories.length) {
            final name = isRtl ? 'مقدمو الخدمات' : 'Services';
            return InkWell(
              onTap: () => context.push('/buyer/home/services'),
              borderRadius: BorderRadius.circular(cardRadius),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: cardSize,
                    height: cardSize,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(cardRadius),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.amber.withValues(alpha: 0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Icon(
                        Icons.engineering,
                        color: Colors.white,
                        size: iconSize,
                      ),
                    ),
                  ),
                  const SizedBox(height: 5),
                  SizedBox(
                    height: 34,
                    child: Center(
                      child: Text(
                        name,
                        textAlign: TextAlign.center,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: width < 360 ? 10.0 : 11.2,
                          fontWeight: FontWeight.bold,
                          color: Colors.amber.shade900,
                          height: 1.15,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            );
          }

          final category = visibleCategories[index];
          final name = isRtl ? category.nameAr : category.name;
          final style = _getCategoryStyle(category.iconName ?? '');

          return InkWell(
            onTap: () {
              context.push(
                '/buyer/home/category/${category.id}?name=${Uri.encodeComponent(name)}',
              );
            },
            borderRadius: BorderRadius.circular(cardRadius),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Icon Card with Gradient Background, Shadow & Exclusive Badge
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: cardSize,
                      height: cardSize,
                      decoration: BoxDecoration(
                        gradient: category.imageUrl != null ? null : LinearGradient(
                          colors: [
                            style.color.withValues(alpha: 0.18),
                            style.color.withValues(alpha: 0.08),
                          ],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        color: category.imageUrl != null ? Colors.white : null,
                        borderRadius: BorderRadius.circular(cardRadius),
                        border: Border.all(
                          color: category.imageUrl != null ? Colors.grey.shade200 : style.color.withValues(alpha: 0.25),
                          width: 1.2,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: category.imageUrl != null ? Colors.grey.withValues(alpha: 0.1) : style.color.withValues(alpha: 0.12),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Center(
                        child: category.imageUrl != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(cardRadius),
                                child: Image.network(
                                  ImageOptimizer.optimize(category.imageUrl!, width: 150),
                                  width: cardSize - 2,
                                  height: cardSize - 2,
                                  fit: BoxFit.contain,
                                  errorBuilder: (c, e, s) => Icon(style.icon, color: style.color, size: iconSize),
                                ),
                              )
                            : Icon(
                                style.icon,
                                color: style.color,
                                size: iconSize,
                              ),
                      ),
                    ),
                    if (category.isExclusiveToMagaiees)
                      Positioned(
                        top: -3,
                        right: isRtl ? -3 : null,
                        left: isRtl ? null : -3,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF1E40AF), Color(0xFF2563EB)],
                            ),
                            borderRadius: BorderRadius.circular(6),
                            boxShadow: const [
                              BoxShadow(color: Colors.black26, blurRadius: 3, offset: Offset(0, 1)),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.verified_rounded, size: 9, color: Colors.amber),
                              const SizedBox(width: 2),
                              Text(
                                isRtl ? 'حصري' : 'Exclusive',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 8.5,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 5),
                // Category Title with fixed bounds and auto-scaling
                SizedBox(
                  height: 34,
                  child: Center(
                    child: Text(
                      name,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: width < 360 ? 10.0 : 11.2,
                        fontWeight: FontWeight.w600,
                        height: 1.15,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  _CategoryStyle _getCategoryStyle(String iconName) {
    switch (iconName) {
      case 'construction':
        return _CategoryStyle(Icons.construction, const Color(0xFF1E40AF));
      case 'foundation':
        return _CategoryStyle(Icons.layers, const Color(0xFF475569));
      case 'grid_on':
        return _CategoryStyle(Icons.grid_on, const Color(0xFFD97706));
      case 'window':
        return _CategoryStyle(Icons.window, const Color(0xFF0284C7));
      case 'format_paint':
        return _CategoryStyle(Icons.format_paint, const Color(0xFF0D9488));
      case 'plumbing':
        return _CategoryStyle(Icons.plumbing, const Color(0xFF2563EB));
      case 'brush':
        return _CategoryStyle(Icons.brush, const Color(0xFFBE185D));
      case 'bolt':
        return _CategoryStyle(Icons.bolt, const Color(0xFFEAB308));
      case 'terrain':
        return _CategoryStyle(Icons.terrain, const Color(0xFFEA580C));
      case 'engineering':
        return _CategoryStyle(Icons.engineering, const Color(0xFF059669));
      case 'handyman':
        return _CategoryStyle(Icons.handyman, const Color(0xFFDC2626));
      default:
        return _CategoryStyle(Icons.category, const Color(0xFF6366F1));
    }
  }
}

class _CategoryStyle {
  final IconData icon;
  final Color color;
  _CategoryStyle(this.icon, this.color);
}
