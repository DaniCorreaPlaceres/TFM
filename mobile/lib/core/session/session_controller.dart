import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Sesión provisional en memoria. No autentica ni selecciona entidad fiscal.
final sessionProvider = NotifierProvider<SessionController, bool>(
  SessionController.new,
);

class SessionController extends Notifier<bool> {
  @override
  bool build() => false;

  void enter() => state = true;

  void leave() => state = false;
}
