// Stub implementation for non-web platforms (Android/iOS).
// This file is imported via conditional import when dart:html is unavailable.

/// No-op on mobile: CSV download is handled via printing/share on mobile.
void downloadCsvBytes(List<int> bytes, String fileName) {
  // On mobile platforms, this is a no-op.
  // CSV export on mobile uses the printing package instead.
}

/// No-op on mobile: printing is handled via the printing package.
void triggerBrowserPrint() {
  // On mobile platforms, this is a no-op.
  // Printing on mobile uses the Printing.layoutPdf() API instead.
}
