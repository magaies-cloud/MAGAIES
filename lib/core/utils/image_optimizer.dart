class ImageOptimizer {
  static String optimize(String? url, {int width = 400}) {
    if (url == null || url.isEmpty) return '';
    // Image transformations are disabled on this Supabase project (causing 400 Bad Request)
    // Fallback to original URL.
    return url;
  }
}
