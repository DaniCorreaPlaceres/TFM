import 'package:app_fiscal/core/session/session_controller.dart';
import 'package:app_fiscal/features/home/home_page.dart';
import 'package:app_fiscal/features/session/session_page.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final refresh = ValueNotifier<int>(0);
  ref.onDispose(refresh.dispose);
  ref.listen(sessionProvider, (previous, next) {
    refresh.value++;
  });

  final router = GoRouter(
    refreshListenable: refresh,
    initialLocation: '/inicio',
    redirect: (context, state) {
      final authenticated = ref.read(sessionProvider);
      final onSession = state.matchedLocation == '/sesion';
      if (!authenticated && !onSession) {
        return '/sesion';
      }
      if (authenticated && onSession) {
        return '/inicio';
      }
      return null;
    },
    routes: [
      GoRoute(
        path: '/sesion',
        builder: (context, state) => const SessionPage(),
      ),
      GoRoute(
        path: '/inicio',
        builder: (context, state) => const HomePage(),
      ),
    ],
  );
  ref.onDispose(router.dispose);
  return router;
});
