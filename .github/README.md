# Workflows

Los pipelines de GitHub Actions están en `.github/workflows/`, según DOC-05 y DOC-07 (PLT-004). La especificación es [E0.6](../docs/sdd/e0-6-integracion-continua.md).

Se disparan en pull requests y en pushes hacia `develop` y `main`:

- `backend.yml`: jobs PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI, con PHP 8.4.
- `flutter.yml`: job Flutter, con `flutter analyze` y `flutter test` en Flutter 3.47.7.

No hay `deploy.yml`. Desde el 25 de octubre de 2026, `main` exige estas comprobaciones, con los nombres de la ejecución publicada en la pull request #10: PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter.

Publicar esta carpeta exige una credencial con ámbito `workflow`. En E0.1 el remoto la rechazó porque la credencial no lo tenía. Hasta que haya una ejecución en GitHub, el resultado de CI en el remoto permanece sin ejecutar.
