import 'package:app_fiscal/app/app.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('la sesión provisional protege el inicio', (tester) async {
    await tester.pumpWidget(const ProviderScope(child: AppFiscal()));
    await tester.pumpAndSettle();

    expect(find.text('Sesión provisional'), findsOneWidget);
    expect(find.text('Inicio'), findsNothing);

    await tester.tap(find.text('Entrar'));
    await tester.pumpAndSettle();

    expect(find.text('Inicio'), findsOneWidget);
    expect(find.text('Sesión provisional'), findsNothing);

    await tester.tap(find.text('Salir'));
    await tester.pumpAndSettle();

    expect(find.text('Sesión provisional'), findsOneWidget);
    expect(find.text('Inicio'), findsNothing);
  });
}
