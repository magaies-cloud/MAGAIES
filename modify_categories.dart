import 'dart:io';

void main() {
  final file = File('lib/features/admin/presentation/screens/admin_categories_screen.dart');
  String content = file.readAsStringSync();

  if (!content.contains('admin_subcategories_screen.dart')) {
    content = content.replaceFirst(
        "import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';",
        "import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';\nimport 'package:magaiees_marketplace/features/admin/presentation/screens/admin_subcategories_screen.dart';");
  }

  final startStr = 'Widget _buildCategoryCard';
  final endStr = '  @override\n  Widget build(BuildContext context) {';

  if (content.contains(startStr) && content.contains(endStr)) {
    final idxStart = content.indexOf(startStr);
    final idxEnd = content.indexOf(endStr);
    
    final replacement = '''Widget _buildCategoryGridItem(BuildContext context, CategoryModel category, bool isRtl) {
    return InkWell(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (_) => AdminSubcategoriesScreen(category: category)));
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 16,
              offset: const Offset(0, 4),
            ),
          ],
          border: Border.all(color: Colors.grey.shade100),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: category.imageUrl != null
                    ? Image.network(category.imageUrl!, fit: BoxFit.contain, loadingBuilder: (ctx, child, progress) {
                        if (progress == null) return child;
                        return const Center(child: SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2)));
                      })
                    : Icon(Icons.category, color: const Color(0xFF1E40AF), size: 48),
              ),
            ),
            Container(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Text(
                    isRtl ? category.nameAr : category.name,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 14),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E40AF).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      isRtl ? '\ ????? ?????' : '\ Sub-categories',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF1E40AF), fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.edit, size: 18),
                        color: const Color(0xFF1E40AF),
                        onPressed: () => _showEditCategoryDialog(context, category, isRtl),
                        tooltip: isRtl ? '?????' : 'Edit',
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                      ),
                      const SizedBox(width: 16),
                      IconButton(
                        icon: const Icon(Icons.delete, size: 18),
                        color: Colors.red,
                        onPressed: () => _confirmDeleteCategory(context, category, isRtl),
                        tooltip: isRtl ? '???' : 'Delete',
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
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
  }

''';
    content = content.substring(0, idxStart) + replacement + content.substring(idxEnd);
  }

  final oldListview = '''return ListView.separated(
                    itemCount: filteredCategories.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 16),
                    itemBuilder: (context, index) {
                      final category = filteredCategories[index];
                      return _buildCategoryCard(context, category, isRtl);
                    },
                  );''';

  final newGridview = '''return GridView.builder(
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: MediaQuery.of(context).size.width > 1200 ? 5 : (MediaQuery.of(context).size.width > 800 ? 4 : 2),
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      childAspectRatio: 0.8,
                    ),
                    itemCount: filteredCategories.length,
                    itemBuilder: (context, index) {
                      return _buildCategoryGridItem(context, filteredCategories[index], isRtl);
                    },
                  );''';

  content = content.replaceAll(oldListview, newGridview);

  file.writeAsStringSync(content);
  print('Done!');
}
