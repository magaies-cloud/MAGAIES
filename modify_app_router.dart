import 'dart:io';

void main() {
  final file = File('lib/routing/app_router.dart');
  String content = file.readAsStringSync();

  if (!content.contains('sub_categories_screen.dart')) {
    content = content.replaceFirst(
        "import 'package:magaiees_marketplace/features/buyer/category_products/presentation/screens/category_products_screen.dart';",
        "import 'package:magaiees_marketplace/features/buyer/category_products/presentation/screens/category_products_screen.dart';\nimport 'package:magaiees_marketplace/features/buyer/home/presentation/screens/sub_categories_screen.dart';");
  }

  final oldRoute = '''                    GoRoute(
                      path: 'category/:id',
                      builder: (context, state) => CategoryProductsScreen(
                        categoryId: state.pathParameters['id']!,
                        categoryName: state.uri.queryParameters['name'],
                      ),
                    ),''';

  final newRoute = '''                    GoRoute(
                      path: 'category/:id',
                      builder: (context, state) => SubCategoriesScreen(
                        categoryId: state.pathParameters['id']!,
                        categoryName: state.uri.queryParameters['name'],
                      ),
                    ),
                    GoRoute(
                      path: 'category/:categoryId/subcategory/:subId',
                      builder: (context, state) => CategoryProductsScreen(
                        categoryId: state.pathParameters['categoryId']!,
                        subCategoryId: state.pathParameters['subId'],
                        categoryName: state.uri.queryParameters['name'],
                      ),
                    ),''';

  content = content.replaceFirst(oldRoute, newRoute);

  file.writeAsStringSync(content);
  print('Done app_router');
}
