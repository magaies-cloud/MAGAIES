import 'dart:io';
import 'package:image/image.dart' as img;

void main() {
  final file = File('assets/images/logo.png');
  final image = img.decodeImage(file.readAsBytesSync());
  if (image == null) return;
  
  final p0 = image.getPixel(0, 0);
  print('TopLeft: R:${p0.r} G:${p0.g} B:${p0.b} A:${p0.a}');
}
