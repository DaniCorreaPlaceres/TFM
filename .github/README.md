# Workflows

Los pipelines de GitHub Actions están en `.github/workflows/`, según DOC-05 y DOC-07 (PLT-004). La especificación es [E0.6](../docs/sdd/e0-6-integracion-continua.md).

Se disparan en pull requests y en pushes hacia `develop` y `main`:

- `backend.yml`: jobs PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI, con PHP 8.4.
- `flutter.yml`: job Flutter, con `flutter analyze` y `flutter test` en Flutter 3.47.7.

No hay `deploy.yml`. Las comprobaciones obligatorias de la protección de `main` siguen sin configurarse: hace falta una ejecución publicada para usar los nombres reales de los jobs.

Publicar esta carpeta exige una credencial con ámbito `workflow`. En E0.1 el remoto la rechazó porque la credencial no lo tenía. Hasta que haya una ejecución en GitHub, el resultado de CI en el remoto permanece sin ejecutar.
