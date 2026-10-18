# Aplicación móvil

Esqueleto Flutter de E0.4. Android es la plataforma comprobada. El proyecto incluye también los runners de iOS (`ios/`, identificador `es.tfm.app_fiscal`) y de escritorio Windows (`windows/`). iOS se abre después en un Mac con Xcode. El escritorio Windows se compila cuando esté instalado Visual Studio con la carga de C++. La app no llama todavía a la API: Dio está preparado y el consumo de `GET /api/v1/health` corresponde a E0.5.

## Arranque

Hace falta Flutter en el `PATH`, el Android SDK y un dispositivo o emulador. En este equipo el SDK de Flutter está en `%USERPROFILE%\sdk\flutter` y el Android SDK en `%LOCALAPPDATA%\Android\Sdk`.

Desde `mobile/`:

```powershell
flutter pub get
flutter analyze
flutter test
flutter run
```

La primera pantalla es una sesión provisional en memoria. `Entrar` abre `Inicio`; `Salir` vuelve a la sesión. No hay autenticación ni datos fiscales.

El emulador creado para la comprobación se llama `tfm_api_36` (API 36, imagen Google APIs x86_64).

## Versiones comprobadas el 18 de octubre de 2026

- Flutter 3.47.7 stable, revisión `abaf9c5237`, Dart 3.13.5, DevTools 2.60.0.
- Android Studio 2026.2.1.8. El primer arranque del IDE no se ha usado: el SDK se instaló con las command-line tools.
- Visual Studio Code ya estaba instalado. Extensiones Dart y Flutter 3.144.0.
- Visual Studio Build Tools 2026 18.11.0, con la carga de C++ y Windows 10 SDK 10.0.26100.0.
- Microsoft OpenJDK 21.0.12.1. El módulo Android compila con objetivo Java 17.
- Android Gradle Plugin 9.1.0, Gradle 9.3.1, Kotlin 2.4.0.
- Android SDK 36, build-tools 36.0.0, NDK 28.2.13676358, ADB 1.0.41 (37.0.1), emulador 37.2.12.0.
- `flutter_riverpod` 3.4.3, `go_router` 18.1.0, `dio` 5.11.1.

`flutter doctor` no dejó incidencias: cadena Android correcta, licencias aceptadas y Visual Studio Build Tools 2026 18.11.0 en `D:\Microsoft\VisualStudio\18\BuildTools`, con Windows 10 SDK 10.0.26100.0. El escritorio Windows y iOS siguen sin ejecutarse; iOS requiere un Mac con Xcode.

## Comprobaciones

- `flutter analyze`: sin incidencias.
- `flutter test`: 2 pruebas superadas, incluida la guarda de la sesión provisional.
- Emulador `emulator-5554`: la app mostró «Sesión provisional» y, tras pulsar Entrar, «Inicio».
- Dispositivo Android físico: no ejecutado. `adb devices` solo mostró el emulador.
