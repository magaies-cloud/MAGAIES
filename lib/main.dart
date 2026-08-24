import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/bootstrap.dart';
import 'package:magaiees_marketplace/app.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Supabase
  await Supabase.initialize(
    url: 'https://ktxiqgajgvbbrbumtufe.supabase.co',
    anonKey: 'sb_publishable_n5bTkwOUGdVCEmApubP86Q_NoH0EsxD',
  );

  // Initialize core services and get provider overrides
  final overrides = await bootstrap();
  
  runApp(
    ProviderScope(
      overrides: overrides,
      child: const MagaieesApp(),
    ),
  );
}
