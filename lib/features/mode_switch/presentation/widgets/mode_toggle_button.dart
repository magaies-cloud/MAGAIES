import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/enums/app_mode.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';

import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';

/// A toggle button that switches between Buyer and Merchant modes.
///
/// Only visible to users who have an approved store.
/// Shows as a segmented button or a simple toggle.
class ModeToggleButton extends ConsumerWidget {
  const ModeToggleButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentMode = ref.watch(modeNotifierProvider);
    final userHasStore = ref.watch(hasStoreProvider);

    // Don't show toggle if user doesn't have a store
    if (!userHasStore) return const SizedBox.shrink();

    return SegmentedButton<AppMode>(
      segments: [
        ButtonSegment(
          value: AppMode.buyer,
          label: Text(context.l10n.buyerMode),
          icon: const Icon(Icons.shopping_cart_outlined),
        ),
        ButtonSegment(
          value: AppMode.merchant,
          label: Text(context.l10n.merchantMode),
          icon: const Icon(Icons.store_outlined),
        ),
      ],
      selected: {currentMode},
      onSelectionChanged: (selected) {
        final mode = selected.first;
        final success = ref.read(modeNotifierProvider.notifier).switchTo(mode);
        if (!success) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(context.l10n.noStoreAccess),
            ),
          );
        }
      },
    );
  }
}
