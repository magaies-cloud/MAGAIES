import 'dart:io';

void main() {
  final file = File('lib/features/buyer/category_products/presentation/screens/category_products_screen.dart');
  String content = file.readAsStringSync();

  // 1. Add subCategoryId to constructor
  content = content.replaceFirst(
    'final String categoryId;',
    'final String categoryId;\n  final String? subCategoryId;'
  );
  
  content = content.replaceFirst(
    'required this.categoryId,',
    'required this.categoryId,\n    this.subCategoryId,'
  );

  // 2. Init state
  content = content.replaceFirst(
    'String? _selectedSubCategoryId;',
    'String? _selectedSubCategoryId;\n\n  @override\n  void initState() {\n    super.initState();\n    _selectedSubCategoryId = widget.subCategoryId;\n  }'
  );

  file.writeAsStringSync(content);
  print('Done products screen');
}
