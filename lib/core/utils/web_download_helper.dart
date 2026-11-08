// Cross-platform download/print helper.
//
// Uses conditional imports to select the correct implementation:
// - On web: uses dart:html for CSV download and browser print
// - On mobile: provides no-op stubs (mobile uses printing package instead)
export 'web_download_helper_stub.dart'
    if (dart.library.html) 'web_download_helper_web.dart';
