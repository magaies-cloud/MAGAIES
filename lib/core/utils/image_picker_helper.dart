import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';

class ImagePickerHelper {
  ImagePickerHelper._();

  /// Shows a bottom sheet allowing the user to choose between Camera and Gallery
  /// (or uses [source] directly if provided), handles runtime camera permission on mobile,
  /// and returns the selected [XFile].
  static Future<XFile?> pickImage(
    BuildContext context, {
    ImageSource? source,
    int imageQuality = 80,
    double? maxWidth = 1400,
    double? maxHeight,
  }) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final ImageSource? selectedSource = source ??
        await showModalBottomSheet<ImageSource>(
          context: context,
          backgroundColor: Colors.white,
          shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          builder: (sheetCtx) {
            return SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 44,
                      height: 4,
                      decoration: BoxDecoration(
                        color: const Color(0xFFCBD5E1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      isRtl ? 'إرفاق صورة' : 'Attach Image',
                      style: const TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      isRtl
                          ? 'اختر طريقة التقاط أو رفع الصورة'
                          : 'Choose how to capture or upload the image',
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF64748B),
                      ),
                    ),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: () => Navigator.pop(sheetCtx, ImageSource.camera),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 12),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEFF6FF),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFBFDBFE), width: 1.5),
                              ),
                              child: Column(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: const BoxDecoration(
                                      color: Color(0xFF2563EB),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.photo_camera_rounded,
                                      color: Colors.white,
                                      size: 28,
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    isRtl ? 'التقاط بالكاميرا' : 'Take Photo',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14,
                                      color: Color(0xFF1E3A8A),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    isRtl ? 'فتح كاميرا الهاتف' : 'Open phone camera',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: Color(0xFF3B82F6),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: () => Navigator.pop(sheetCtx, ImageSource.gallery),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 12),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0FDF4),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFBBF7D0), width: 1.5),
                              ),
                              child: Column(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: const BoxDecoration(
                                      color: Color(0xFF16A34A),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.photo_library_rounded,
                                      color: Colors.white,
                                      size: 28,
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    isRtl ? 'معرض الصور' : 'Photo Gallery',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14,
                                      color: Color(0xFF14532D),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    isRtl ? 'اختيار صورة محفوظة' : 'Choose saved photo',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: Color(0xFF16A34A),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        );

    if (selectedSource == null) return null;

    if (!kIsWeb && selectedSource == ImageSource.camera) {
      var status = await Permission.camera.status;
      if (!status.isGranted) {
        status = await Permission.camera.request();
      }
      if (!status.isGranted) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                isRtl
                    ? 'يرجى السماح بإذن الكاميرا لالتقاط الصورة'
                    : 'Please grant camera permission to take a photo',
              ),
              action: status.isPermanentlyDenied
                  ? SnackBarAction(
                      label: isRtl ? 'الإعدادات' : 'Settings',
                      textColor: Colors.amber,
                      onPressed: openAppSettings,
                    )
                  : null,
              backgroundColor: Colors.red.shade700,
            ),
          );
        }
        return null;
      }
    }

    final picker = ImagePicker();
    return picker.pickImage(
      source: selectedSource,
      imageQuality: imageQuality,
      maxWidth: maxWidth,
      maxHeight: maxHeight,
    );
  }
}
