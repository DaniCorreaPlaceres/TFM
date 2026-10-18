# E0.4 — Entorno Android y esqueleto Flutter

Traza reconstruida el 18 de octubre de 2026. Este archivo no existía antes de la implementación.

Traza: PLT-003. Plan: [E0.4](../E0_Plan_de_trabajo.md).

## Objetivo

Dejar el esqueleto Flutter con Riverpod, GoRouter y Dio, y una sesión provisional en memoria que proteja la pantalla de inicio.

## Fuera de alcance

- No llamar a la API. Dio queda preparado y `GET /api/v1/health` corresponde a E0.5.
- No implementar autenticación real ni datos fiscales.
- No validar iOS en Windows. El runner `ios/` queda preparado para abrirlo después en un Mac con Xcode.
- No dar por ejecutado el escritorio Windows ni un dispositivo Android físico si no se ha lanzado.

## Criterios de aceptación

- El proyecto móvil usa Flutter, Riverpod, GoRouter y Dio.
- Sin sesión, la app muestra la sesión provisional. Entrar abre Inicio. Salir vuelve a la sesión.
- `flutter analyze` no deja incidencias y las pruebas de la guarda de sesión y del cliente API pasan.
- Quedan registradas las versiones de Flutter, Android SDK, JDK y Gradle usadas en la comprobación.
- El emulador y el dispositivo físico se comprueban por separado. Lo no ejecutado permanece como no ejecutado.

## Resultado

Integrado en `develop` mediante el pull request #5 (`e08e612`). Comprobado el 18 de octubre de 2026 en el emulador `tfm_api_36`: la app mostró la sesión provisional y, tras Entrar, el inicio. `flutter analyze` no dejó incidencias y `flutter test` superó 2 pruebas. Flutter 3.47.7, Dart 3.13.5. El dispositivo Android físico no se ejecutó. Los runners de iOS y Windows quedaron preparados y sin ejecutar. La ficha de evidencia queda para E0.7.
