import 'package:app_fiscal/app/router.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AppFiscal extends ConsumerWidget {
  const AppFiscal({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'App Fiscal',
      routerConfig: router,
    );
  }
}
