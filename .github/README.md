# Workflows

Los pipelines de GitHub Actions vivirán en `.github/workflows/`, según DOC-05 y DOC-07 (PLT-004).

E0.1 no añade pipelines. Esta nota está fuera de esa carpeta: el remoto rechaza cualquier archivo bajo `.github/workflows/` si la credencial no tiene el ámbito `workflow`. Los archivos YAML se publicarán en E0.6, cuando esa tarea esté autorizada y la credencial permita subirlos.

En E0.6 se incorporarán las comprobaciones de pull request hacia `develop`:

- `backend.yml`: PHP-CS-Fixer, PHPStan, PHPUnit y validación OpenAPI.
- `flutter.yml`: análisis y pruebas Flutter.

`deploy.yml` queda fuera de la puerta mínima de E0. Las comprobaciones obligatorias de CI en la protección de `main` siguen pendientes hasta que existan estos pipelines.
