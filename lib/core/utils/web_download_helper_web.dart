// ignore_for_file: avoid_web_libraries_in_flutter, deprecated_member_use
import 'dart:html' as html;

/// Web implementation: triggers a CSV file download via browser APIs.
void downloadCsvBytes(List<int> bytes, String fileName) {
  final blob = html.Blob([bytes], 'text/csv;charset=utf-8');
  final url = html.Url.createObjectUrlFromBlob(blob);
  html.AnchorElement(href: url)
    ..setAttribute('download', fileName)
    ..click();
  html.Url.revokeObjectUrl(url);
}

/// Web implementation: triggers the browser's native print dialog.
void triggerBrowserPrint() {
  html.window.print();
}
