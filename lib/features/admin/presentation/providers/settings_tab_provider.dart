import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Controls which tab is initially selected when navigating to Settings.
/// Set this before navigating to open a specific tab (e.g. Employees tab = 6).
final settingsTabIndexProvider = StateProvider<int>((ref) => 0);
