import 'package:flutter_test/flutter_test.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';

void main() {
  group('UnitFormatter Tests', () {
    test('Cleans currency prefixes properly', () {
      expect(UnitFormatter.clean('ج.س/حبة'), 'حبة');
      expect(UnitFormatter.clean('SDG/ton'), 'ton');
      expect(UnitFormatter.clean('\$/piece'), 'piece');
      expect(UnitFormatter.clean('USD / sack'), 'sack');
      expect(UnitFormatter.clean('دولار / متر'), 'متر');
    });

    test('Formats units in Arabic (RTL) cleanly without currency repetition', () {
      expect(UnitFormatter.format(null, 'ج.س/حبة', isRtl: true), 'حبة');
      expect(UnitFormatter.format('ج.س/طن', null, isRtl: true), 'طن');
      expect(UnitFormatter.format(null, 'meter', isRtl: true), 'متر');
      expect(UnitFormatter.format('كيس', null, isRtl: true), 'كيس');
      expect(UnitFormatter.format(null, 'piece', isRtl: true), 'قطعة');
      expect(UnitFormatter.format(null, '\$/piece', isRtl: true), 'قطعة');
    });

    test('Formats units in English (LTR) cleanly', () {
      expect(UnitFormatter.format(null, 'ج.س/حبة', isRtl: false), 'Piece');
      expect(UnitFormatter.format('ج.س/طن', null, isRtl: false), 'Ton');
      expect(UnitFormatter.format(null, 'meter', isRtl: false), 'Meter');
      expect(UnitFormatter.format('كيس', null, isRtl: false), 'Bag');
      expect(UnitFormatter.format(null, 'piece', isRtl: false), 'Piece');
    });
  });
}
