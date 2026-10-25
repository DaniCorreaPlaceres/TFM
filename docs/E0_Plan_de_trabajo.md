# E0 — Plan de trabajo operativo

Subdivisiones de trabajo utilizadas en el proyecto. Complementan el apartado 5 de [DOC-07](DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx), cuyo backlog conserva los identificadores PLT-001 a PLT-006. La numeración E0.x no sustituye esos identificadores ni modifica el estado de aprobación de los Word.

La secuencia y el reparto se recogen de las instrucciones locales existentes y del trabajo realizado. Los pasos pendientes se ejecutan cuando el responsable los solicite. No se considera superada ninguna comprobación no ejecutada.

## E0.1 — Instrucciones y estructura del monorepositorio

Traza: PLT-001.

- [x] Crear instrucciones AGENTS.md raíz, backend, mobile y docs.
- [x] Crear índice Markdown con los nueve Word y enlaces relativos.
- [x] Preparar README general y directorios backend/, mobile/, infrastructure/, docs/ y .github/.
- [x] Preparar docs/evidence/ e índice de evidencias.
- [x] Revisar e integrar los cambios locales adicionales de E0.1 que todavía no están confirmados en Git.

Estado: integrado en `develop` mediante el pull request #2. La especificación reconstruida está en [docs/sdd/e0-1-instrucciones-estructura.md](sdd/e0-1-instrucciones-estructura.md). La existencia del registro de evidencias no acredita pruebas ejecutadas.

## E0.2 — Git, remoto y protección de ramas

Traza: PLT-001 y parte de PLT-004.

- [x] Inicializar Git y configurar autoría local.
- [x] Crear el commit inicial y las ramas main/develop.
- [x] Crear y conectar el repositorio público DaniCorreaPlaceres/TFM.
- [x] Publicar ambas ramas y configurar su seguimiento remoto.
- [x] Proteger main: PR con una aprobación, descarte de aprobaciones antiguas y resolución de conversaciones.
- [x] Aplicar las reglas a administradores y bloquear pushes forzados y borrados.

Estado: configuración verificada en esta sesión. main es producción; develop es integración. Trabajar en feature/* y abrir PR hacia develop; promover a main mediante PR. Una persona distinta del autor debe aprobar las PR hacia main. Las comprobaciones obligatorias de CI se incorporarán en E0.6.

## E0.3 — Entorno Symfony reproducible

Traza: PLT-002.

- [x] Inicializar Symfony 7.4 con PHP 8.4 y Doctrine.
- [x] Preparar Docker Compose con Nginx, PHP-FPM 8.4, MySQL 8.4 y Redis.
- [x] Comprobar healthchecks y una migración de ejemplo reversible.
- [x] Documentar arranque, parada y verificación del entorno.

Estado: comprobado el 18 de octubre de 2026 en `feature/e0-entorno-symfony`. PHP 8.4.26, Symfony 7.4.21, MySQL 8.4.9, Redis 7.4.11 y Nginx 1.28.3 quedaron healthy. La migración `Version20261018030000` subió, bajó y volvió a subir. `GET /nginx-health` respondió 200. `GET /` mostró la bienvenida de Symfony 7.4. El modelo fiscal completo de DOC-06 queda fuera de E0. La ficha de evidencia de esta tarea se registra en E0.7.

## E0.4 — Entorno Android y esqueleto Flutter

Traza: PLT-003.

- [x] Preparar VS Code con Flutter/Dart y Android Studio/SDK, con cadena JDK/Gradle/AGP compatible.
- [x] Registrar versiones y comprobar flutter doctor -v, licencias, flutter devices y adb devices.
- [x] Crear el esqueleto Flutter con Riverpod, GoRouter y Dio y navegación protegida provisional.
- [ ] Ejecutar en emulador y dispositivo Android físico; comprobar análisis Dart y pruebas pertinentes.

Estado: comprobado el 18 de octubre de 2026 en `feature/e0-flutter-android`, salvo el dispositivo físico. Flutter 3.47.7, Dart 3.13.5, Android Studio 2026.2.1.8, OpenJDK 21.0.12.1, SDK 36, AGP 9.1.0 y Gradle 9.3.1. `flutter analyze` sin incidencias y `flutter test` con 2 pruebas superadas. El emulador `tfm_api_36` mostró la sesión provisional y, tras Entrar, el inicio. El dispositivo Android físico queda no ejecutado: `adb devices` solo listó el emulador. `flutter doctor` no dejó incidencias. Visual Studio Build Tools 2026 18.11.0 está en `D:\Microsoft\VisualStudio\18\BuildTools`, con Windows 10 SDK 10.0.26100.0. Los runners `ios/` y `windows/` siguen sin ejecutar: iOS requiere un Mac con Xcode. La ficha de evidencia se registra en E0.7.

## E0.5 — Contrato mínimo de API y logging

Traza: PLT-005.

- [x] Crear /api/v1/health.
- [x] Definir errores problem+json y requestId.
- [x] Preparar y validar el contrato OpenAPI mínimo, sin implementar el esquema fiscal completo.

Estado: comprobado el 18 de octubre de 2026 en `feature/e0-contrato-api`. PHPUnit superó 5 pruebas. `GET /api/v1/health` respondió 200 `{"status":"ok"}` con `X-Request-Id`. Una ruta desconocida respondió 404 `application/problem+json`. `GET /nginx-health` sigue respondiendo 200. El contrato está en `backend/openapi/openapi.yaml`. La validación en CI se conectará en E0.6. La app Flutter no llama a la API. La ficha de evidencia se registra en E0.7.

## E0.6 — Integración continua y comprobaciones obligatorias

Traza: parte restante de PLT-004 y validación CI de PLT-005.

- [x] Crear pipelines de PR hacia develop: formato PHP, PHPStan, PHPUnit y validación OpenAPI; análisis y pruebas Flutter.
- [x] Ejecutarlos y corregir fallos hasta obtener resultados verificables.
- [x] Asegurar que las comprobaciones requeridas también se ejecuten en PR hacia main antes de exigirlas en su protección.
- [x] Configurar las comprobaciones obligatorias de main utilizando sus nombres reales, una vez publicadas y ejecutadas.

Estado: los workflows se disparan hacia `develop` y hacia `main`. El 25 de octubre de 2026 pasaron en local y en la pull request #10. Desde ese día, `main` exige PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter. La rama no tiene que estar actualizada con `main` para que esas comprobaciones cuenten. Una pull request hacia `main` todavía no ha ejecutado estos workflows. No hay `deploy.yml`.

## E0.7 — Revisión de pruebas y evidencias

Traza: transversal a E0, DOC-08 y DOC-09.

- [x] Revisar los resultados reales de entorno, API, móvil y CI disponibles hasta este punto.
- [x] Registrar versiones, comandos, incidencias, resultados y referencias a commits/PR en fichas de evidencia sanitizadas.
- [x] Identificar pruebas no ejecutadas y solicitar revisión humana del incremento.

Estado: fichas EVI-0001 a EVI-0004 registradas y aceptadas por el responsable el 25 de octubre de 2026. Esta revisión precede a E0.8; el cierre completo de E0 requiere también comprobar almacenamiento y secretos.

## E0.8 — Almacenamiento local privado y secretos

Traza: PLT-006.

- [x] Añadir MinIO como almacenamiento local compatible con S3.
- [x] Mantener buckets privados y comprobar que no existe descarga pública directa.
- [x] Versionar .env.example sin secretos y mantener credenciales fuera de Git.
- [x] Añadir las pruebas y evidencias correspondientes y revisar el cierre global de E0.

Estado: comprobado el 25 de octubre de 2026. MinIO `RELEASE.2025-10-15T17-29-55Z` escucha solo en localhost y el bucket `documents` responde 403 a una descarga anónima. PHPUnit pasó 6 pruebas. El responsable aceptó EVI-0005 ese mismo día. E0 no se cierra: faltan el dispositivo físico, iOS, el escritorio Windows, la llamada de la app a la API, la promoción a `main` y el despliegue. La rama `feature/e0-minio` todavía no está integrada y GitHub Actions no ha construido esta imagen.

## Puerta de salida de E0

Entorno reproducible y pipelines verdes, con los criterios de PLT-001 a PLT-006 verificados, revisión humana y evidencias de lo realmente ejecutado. E0.2 completado no significa que la entrega E0 esté terminada.
