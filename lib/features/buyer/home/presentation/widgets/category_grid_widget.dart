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

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14.0),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: crossAxisCount,
          mainAxisSpacing: 16,
          crossAxisSpacing: 10,
          childAspectRatio: 0.72,
        ),
        itemCount: categories.length + 1,
        itemBuilder: (context, index) {
          // 11th item: Service Providers & Tradesmen
          if (index == categories.length) {
            final name = isRtl ? 'مقدمو الخدمات' : 'Services';
            return InkWell(
              onTap: () => context.push('/buyer/home/services'),
              borderRadius: BorderRadius.circular(22),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.amber.withOpacity(0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.engineering,
                        color: Colors.white,
                        size: 40,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Expanded(
                    child: Text(
                      name,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.bold,
                        color: Colors.amber,
                        height: 1.2,
                      ),
                    ),
                  ),
                ],
              ),
            );
          }

          final category = categories[index];
          final name = isRtl ? category.nameAr : category.name;
          final style = _getCategoryStyle(category.iconName ?? '');

          return InkWell(
            onTap: () {
              context.push(
                '/buyer/home/category/${category.id}?name=${Uri.encodeComponent(name)}',
              );
            },
            borderRadius: BorderRadius.circular(22),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Icon Card with Gradient Background & Shadow
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    gradient: category.imageUrl != null ? null : LinearGradient(
                      colors: [
                        style.color.withOpacity(0.18),
                        style.color.withOpacity(0.08),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    color: category.imageUrl != null ? Colors.white : null,
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(
                      color: category.imageUrl != null ? Colors.grey.shade200 : style.color.withOpacity(0.25),
                      width: 1.2,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: category.imageUrl != null ? Colors.grey.withOpacity(0.1) : style.color.withOpacity(0.12),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: Center(
                    child: category.imageUrl != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(22),
                            child: Image.network(
                              category.imageUrl!.contains('unsplash') ? 'https://picsum.photos/seed/${category.id}/80/80' : category.imageUrl!,
                              width: 80,
                              height: 80,
                              fit: BoxFit.contain,
                              errorBuilder: (c, e, s) => Icon(style.icon, color: style.color, size: 28),
                            ),
                          )
                        : Icon(
                            style.icon,
                            color: style.color,
                            size: 40,
                          ),
                  ),
                ),
                const SizedBox(height: 6),
                // Category Title
                Expanded(
                  child: Text(
                    name,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      height: 1.2,
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