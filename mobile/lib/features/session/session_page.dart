import 'package:app_fiscal/core/session/session_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class SessionPage extends ConsumerWidget {
  const SessionPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Sesión provisional',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 8),
            const Text('La autenticación llega en una entrega posterior.'),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () => ref.read(sessionProvider.notifier).enter(),
              child: const Text('Entrar'),
            ),
          ],
        ),
      ),
    );
  }
}
