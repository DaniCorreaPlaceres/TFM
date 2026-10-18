import 'package:app_fiscal/core/session/session_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class HomePage extends ConsumerWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Inicio', style: Theme.of(context).textTheme.headlineSmall),
            const SizedBox(height: 8),
            const Text('Esqueleto Android. Sin datos fiscales.'),
            const SizedBox(height: 24),
            OutlinedButton(
              onPressed: () => ref.read(sessionProvider.notifier).leave(),
              child: const Text('Salir'),
            ),
          ],
        ),
      ),
    );
  }
}
