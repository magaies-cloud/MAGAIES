import 'dart:io';

void main() {
  final file = File('lib/features/admin/presentation/screens/admin_products_screen.dart');
  String content = file.readAsStringSync();
  
  content = content.replaceFirst(
    'if (widget.initialSearchQuery != null) {\n      // if there is a search controller, we would set it here. \n    }',
    'if (widget.initialSearchQuery != null) {\n      _searchCtrl.text = widget.initialSearchQuery!;\n    }'
  );
  
  file.writeAsStringSync(content);
}
