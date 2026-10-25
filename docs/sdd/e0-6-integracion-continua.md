# E0.6 — Integración continua

Traza: parte restante de PLT-004 y validación CI de PLT-005. Plan: [E0.6](../E0_Plan_de_trabajo.md).

## Objetivo

Dejar pipelines de GitHub Actions que comprueben el backend y Flutter en las pull requests hacia `develop` y hacia `main`.

## Fuera de alcance

- No crear `deploy.yml` ni desplegar a producción.
- No hacer que la app Flutter llame a la API.
- No elegir proveedor de IA ni añadir MinIO.
- No escribir las fichas de evidencia. Corresponden a E0.7.
- No exigir las comprobaciones en la protección de `main` hasta que existan ejecuciones reales con sus nombres.

## Criterios de aceptación

- `.github/workflows/backend.yml` ejecuta PHP-CS-Fixer, PHPStan, PHPUnit y la validación del OpenAPI en PHP 8.4.
- `.github/workflows/flutter.yml` ejecuta `flutter analyze` y `flutter test`.
- Los dos pipelines se disparan en pull requests y en pushes hacia `develop` y `main`.
- Esas mismas comprobaciones pasan en local antes de dar por cerrada la tarea.
- Las comprobaciones obligatorias de `main` usan los nombres reales de los jobs, y solo se configuran después de una ejecución publicada.

## Decisiones

- `.gitattributes` guarda `*.php`, `*.yml`, `*.yaml`, `*.xml`, `*.neon` y `*.md` con fin de línea LF. Así el dry-run de PHP-CS-Fixer ve el mismo texto en Windows y en Ubuntu.
- La regla `line_ending` de PHP-CS-Fixer queda desactivada. El fin de línea lo fija Git, no el formateador.
- `bin/validate-openapi.php` oculta los avisos deprecados de `cebe/php-openapi` 1.8 en PHP 8.4. El proceso sigue fallando si el contrato no es válido.
- `.env` no se versiona. En GitHub Actions el workflow copia `.env.example` a `.env` antes de `composer install`, porque el script de Symfony lee ese fichero al vaciar la caché. `APP_SECRET` de esa ejecución es un valor solo de CI.

## Resultado

Comprobado en local el 25 de octubre de 2026, en `feature/e0-ci`. El backend se ejecutó dentro del contenedor PHP 8.4.26:

- PHP-CS-Fixer 3.95.27, dry-run: 0 de 14 archivos por corregir.
- PHPStan 2.3.2, nivel 6: sin errores.
- `php bin/validate-openapi.php`: `OpenAPI válido.`, código 0.
- PHPUnit 13.4.3: 5 pruebas, 30 aserciones, OK.

Flutter 3.47.7 en el host: `flutter analyze` sin incidencias y `flutter test` con 2 pruebas OK.

La primera ejecución publicada, en la pull request #10, terminó el 25 de octubre de 2026: Flutter en verde y los cuatro jobs del backend en rojo. `composer install` falló al vaciar la caché porque `backend/.env` no está en el repositorio. Las comprobaciones obligatorias de `main` no están configuradas.
