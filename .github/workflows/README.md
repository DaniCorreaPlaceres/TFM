# Workflows

Directorio reservado para GitHub Actions, según DOC-05 y DOC-07 (PLT-004).

E0.1 no añade pipelines. Este archivo Markdown no es un workflow y Actions no lo ejecuta.

En E0.6, cuando esa tarea esté autorizada, se incorporarán las comprobaciones de pull request hacia `develop`:

- `backend.yml`: PHP-CS-Fixer, PHPStan, PHPUnit y validación OpenAPI.
- `flutter.yml`: análisis y pruebas Flutter.

`deploy.yml` queda fuera de la puerta mínima de E0. Las comprobaciones obligatorias de CI en la protección de `main` siguen pendientes hasta que existan estos pipelines.
