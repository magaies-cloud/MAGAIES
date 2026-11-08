import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

/// Controller to allow external code to trigger image capture.
class ProductImagePreviewBoxController {
  _ProductImagePreviewBoxState? _state;
  void _attach(_ProductImagePreviewBoxState s) => _state = s;
  void _detach() => _state = null;

  /// Captures the visible framed image as PNG bytes (always in cover mode).
  Future<Uint8List?> captureFramedImage() => _state?.captureFramedImage() ?? Future.value(null);
}

class ProductImagePreviewBox extends StatefulWidget {
  final Uint8List? imageBytes;
  final String? imageUrl;
  final VoidCallback onPickImage;
  final VoidCallback? onRemoveImage;
  final double? height;
  final double aspectRatio;
  final String? label;
  final ProductImagePreviewBoxController? controller;
  final ValueChanged<Uint8List>? onCropConfirmed;

  const ProductImagePreviewBox({
    super.key,
    this.imageBytes,
    this.imageUrl,
    required this.onPickImage,
    this.onRemoveImage,
    this.height,
    this.aspectRatio = 4 / 3, // Matches standard 4:3 product image ratio
    this.label,
    this.controller,
    this.onCropConfirmed,
  });

  @override
  State<ProductImagePreviewBox> createState() => _ProductImagePreviewBoxState();
}

class _ProductImagePreviewBoxState extends State<ProductImagePreviewBox> {
  late final TransformationController _transformController;
  final GlobalKey _cropBoundaryKey = GlobalKey();

  bool get _hasImage => widget.imageBytes != null || (widget.imageUrl != null && widget.imageUrl!.isNotEmpty);

  @override
  void initState() {
    super.initState();
    _transformController = TransformationController();
    widget.controller?._attach(this);
  }

  @override
  void didUpdateWidget(covariant ProductImagePreviewBox oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.controller != widget.controller) {
      oldWidget.controller?._detach();
      widget.controller?._attach(this);
    }
    // Reset transform when a new image is picked
    if (oldWidget.imageBytes != widget.imageBytes && widget.imageBytes != null) {
      _transformController.value = Matrix4.identity();
    }
  }

  @override
  void dispose() {
    widget.controller?._detach();
    _transformController.dispose();
    super.dispose();
  }

  /// Captures the framed image as PNG. Uses cover mode to match buyer view.
  Future<Uint8List?> captureFramedImage() async {
    if (!_hasImage) return null;
    try {
      final boundary = _cropBoundaryKey.currentContext?.findRenderObject() as RenderRepaintBoundary?;
      if (boundary == null) return widget.imageBytes;

      final ui.Image image = await boundary.toImage(pixelRatio: 1.5).timeout(const Duration(seconds: 3));
      final ByteData? byteData = await image.toByteData(format: ui.ImageByteFormat.png).timeout(const Duration(seconds: 2));
      if (byteData != null) {
        return byteData.buffer.asUint8List();
      }
    } catch (e) {
      debugPrint('Error capturing framed image: $e');
    }
    return widget.imageBytes;
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    // ── Empty State ──────────────────────────────────────────────────────
    if (!_hasImage) {
      return Center(
        child: GestureDetector(
          onTap: widget.onPickImage,
          child: Container(
            width: double.infinity,
            constraints: const BoxConstraints(maxWidth: 480),
            child: AspectRatio(
              aspectRatio: widget.aspectRatio,
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFFFAFBFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFFD0D7DE),
                    width: 1.5,
                    strokeAlign: BorderSide.strokeAlignInside,
                  ),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        shape: BoxShape.circle,
                        border: Border.all(color: const Color(0xFFBFDBFE), width: 1.5),
                      ),
                      child: const Icon(
                        Icons.add_photo_alternate_rounded,
                        size: 28,
                        color: Color(0xFF3B82F6),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      isRtl ? 'إضافة صورة المنتج' : 'Add Product Image',
                      style: const TextStyle(
                        color: Color(0xFF1F2937),
                        fontWeight: FontWeight.w600,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      isRtl ? 'انقر لاختيار صورة' : 'Tap to choose an image',
                      style: const TextStyle(
                        color: Color(0xFF9CA3AF),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    // ── Image Loaded State ───────────────────────────────────────────────
    return Center(
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(maxWidth: 480),
        child: AspectRatio(
          aspectRatio: widget.aspectRatio,
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: const Color(0xFFD0D7DE),
                width: 1.5,
                strokeAlign: BorderSide.strokeAlignInside,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.06),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(13),
              child: Stack(
                children: [
                  // ── The capturable image frame ─────────────────────────
                  Positioned.fill(
                    child: RepaintBoundary(
                      key: _cropBoundaryKey,
                      child: Container(
                        color: Colors.white,
                        child: InteractiveViewer(
                          transformationController: _transformController,
                          minScale: 0.5,
                          maxScale: 4.0,
                          panEnabled: true,
                          scaleEnabled: true,
                          clipBehavior: Clip.antiAlias,
                          child: Center(
                            child: widget.imageBytes != null
                                ? Image.memory(
                                    widget.imageBytes!,
                                    fit: BoxFit.cover,
                                    width: double.infinity,
                                    height: double.infinity,
                                  )
                                : Image.network(
                                    widget.imageUrl!,
                                    fit: BoxFit.cover,
                                    width: double.infinity,
                                    height: double.infinity,
                                    errorBuilder: (context, error, stack) => const Center(
                                      child: Icon(Icons.broken_image_rounded, size: 48, color: Colors.grey),
                                    ),
                                  ),
                          ),
                        ),
                      ),
                    ),
                  ),

                  // ── Floating action buttons (bottom-right) ─────────────
                  Positioned(
                    bottom: 10,
                    right: isRtl ? null : 10,
                    left: isRtl ? 10 : null,
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Change image button
                        _FloatingActionBtn(
                          icon: Icons.photo_camera_rounded,
                          tooltip: isRtl ? 'تغيير الصورة' : 'Change Image',
                          onTap: widget.onPickImage,
                        ),
                        if (_hasImage) ...[
                          const SizedBox(width: 8),
                          _FloatingActionBtn(
                            icon: Icons.crop_rounded,
                            tooltip: isRtl ? 'تثبيت وحفظ مقاس وتأطير الصورة' : 'Lock & Apply Framing/Crop',
                            color: const Color(0xFF16A34A),
                            onTap: () async {
                              final cropped = await captureFramedImage();
                              if (cropped != null) {
                                widget.onCropConfirmed?.call(cropped);
                                if (context.mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Row(
                                        children: [
                                          const Icon(Icons.check_circle_rounded, color: Colors.white),
                                          const SizedBox(width: 8),
                                          Text(isRtl
                                              ? '✨ تم تثبيت وتأطير مقاس الصورة بنجاح!'
                                              : '✨ Image framing and size locked!'),
                                        ],
                                      ),
                                      backgroundColor: const Color(0xFF16A34A),
                                      duration: const Duration(seconds: 2),
                                      behavior: SnackBarBehavior.floating,
                                    ),
                                  );
                                }
                              }
                            },
                          ),
                        ],
                        if (widget.onRemoveImage != null) ...[
                          const SizedBox(width: 8),
                          _FloatingActionBtn(
                            icon: Icons.delete_outline_rounded,
                            tooltip: isRtl ? 'حذف الصورة' : 'Remove Image',
                            onTap: widget.onRemoveImage!,
                            isDestructive: true,
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Minimal floating action button for image controls.
class _FloatingActionBtn extends StatelessWidget {
  final IconData icon;
  final String tooltip;
  final VoidCallback onTap;
  final bool isDestructive;
  final Color? color;

  const _FloatingActionBtn({
    required this.icon,
    required this.tooltip,
    required this.onTap,
    this.isDestructive = false,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: color != null
                ? color!.withValues(alpha: 0.9)
                : Colors.black.withValues(alpha: 0.5),
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.15),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Icon(
            icon,
            size: 18,
            color: isDestructive ? const Color(0xFFFF6B6B) : Colors.white,
          ),
        ),
      ),
    );
  }
}

/// Opens an interactive image viewer in a modal dialog.
void showInteractiveImageViewer(
  BuildContext context, {
  Uint8List? imageBytes,
  String? imageUrl,
  String title = '',
}) {
  showDialog(
    context: context,
    barrierColor: Colors.black87,
    builder: (modalContext) {
      final isRtl = Directionality.of(modalContext) == TextDirection.rtl;
      final controller = TransformationController();

      return StatefulBuilder(
        builder: (ctx, setModalState) {
          return Dialog(
            backgroundColor: Colors.transparent,
            insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Container(
                width: double.infinity,
                constraints: const BoxConstraints(maxWidth: 800, maxHeight: 750),
                color: Colors.white,
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      decoration: const BoxDecoration(
                        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              title.isNotEmpty ? title : (isRtl ? 'معاينة الصورة' : 'Image Viewer'),
                              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close_rounded, color: Color(0xFF6B7280)),
                            onPressed: () => Navigator.pop(modalContext),
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: InteractiveViewer(
                        transformationController: controller,
                        minScale: 0.5,
                        maxScale: 6.0,
                        panEnabled: true,
                        scaleEnabled: true,
                        child: Center(
                          child: imageBytes != null
                              ? Image.memory(imageBytes, fit: BoxFit.contain)
                              : (imageUrl != null && imageUrl.isNotEmpty)
                                  ? Image.network(
                                      imageUrl,
                                      fit: BoxFit.contain,
                                      errorBuilder: (_, __, ___) => const Icon(Icons.broken_image_rounded, size: 64, color: Colors.grey),
                                    )
                                  : const Icon(Icons.image_not_supported_rounded, size: 64, color: Colors.grey),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      );
    },
  );
}
