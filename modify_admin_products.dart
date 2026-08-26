import 'dart:io';

void main() {
  final file = File('lib/features/admin/presentation/screens/admin_products_screen.dart');
  String content = file.readAsStringSync();

  if (!content.contains('final String? initialSearchQuery;')) {
    content = content.replaceFirst('const AdminProductsScreen({super.key});', 'final String? initialSearchQuery;\n  const AdminProductsScreen({super.key, this.initialSearchQuery});');
    
    // now we need to assign it to _searchQuery or the text controller
    content = content.replaceFirst('String _searchQuery = \'\';', 'String _searchQuery = \'\';\n\n  @override\n  void initState() {\n    super.initState();\n    _searchQuery = widget.initialSearchQuery ?? \'\';\n    if (widget.initialSearchQuery != null) {\n      // if there is a search controller, we would set it here. \n    }\n  }');
    
    file.writeAsStringSync(content);
  }
  print('Done admin products');
}
