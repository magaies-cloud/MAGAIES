import 'dart:io';

void main() {
  final file = File('lib/features/admin/presentation/screens/admin_products_screen.dart');
  String content = file.readAsStringSync();
  
  final badInit = '''  @override
  void initState() {
    super.initState();
    _searchQuery = widget.initialSearchQuery ?? '';
    if (widget.initialSearchQuery != null) {
      _searchCtrl.text = widget.initialSearchQuery!;
    }
  }''';

  content = content.replaceFirst(badInit, '');

  final goodInit = '''  @override
  void initState() {
    super.initState();
    final initialRate = ref.read(pricingProvider).usdExchangeRate;
    _usdRate = initialRate;
    _usdRateCtrl = TextEditingController(text: initialRate.toStringAsFixed(0));
  }''';

  final mergedInit = '''  @override
  void initState() {
    super.initState();
    _searchQuery = widget.initialSearchQuery ?? '';
    if (widget.initialSearchQuery != null) {
      _searchCtrl.text = widget.initialSearchQuery!;
    }
    final initialRate = ref.read(pricingProvider).usdExchangeRate;
    _usdRate = initialRate;
    _usdRateCtrl = TextEditingController(text: initialRate.toStringAsFixed(0));
  }''';

  content = content.replaceFirst(goodInit, mergedInit);
  file.writeAsStringSync(content);
}
