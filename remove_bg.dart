import 'dart:io';
import 'package:image/image.dart' as img;

void main() {
  final file = File('assets/images/logo.png');
  if (!file.existsSync()) {
    print('File not found');
    return;
  }
  
  final image = img.decodeImage(file.readAsBytesSync());
  if (image == null) return;
  
  // Assuming top-left pixel is the background color
  final bgColor = image.getPixel(0, 0);
  final bgR = bgColor.r;
  final bgG = bgColor.g;
  final bgB = bgColor.b;
  
  for (int y = 0; y < image.height; y++) {
    for (int x = 0; x < image.width; x++) {
      final p = image.getPixel(x, y);
      
      // Calculate color distance
      final rDiff = (p.r - bgR).abs();
      final gDiff = (p.g - bgG).abs();
      final bDiff = (p.b - bgB).abs();
      
      // If close to gray background, make transparent
      if (rDiff < 20 && gDiff < 20 && bDiff < 20) {
        image.setPixelRgba(x, y, 0, 0, 0, 0);
      }
    }
  }
  
  File('assets/images/logo_clean.png').writeAsBytesSync(img.encodePng(image));
  print('Done');
}
