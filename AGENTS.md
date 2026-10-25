# Instrucciones del proyecto TFM — Aselto

Estas instrucciones se aplican a todo el monorepo. Consultar también el AGENTS.md del directorio de trabajo y el índice [docs/README.md](docs/README.md).

El nombre de la aplicación es Aselto y el eslogan es «Tus finanzas bajo control». El dominio de la web será aselto.com. El identificador técnico del proyecto móvil sigue siendo `app_fiscal`.

## Organización y herramientas
- Monorepo con backend/, mobile/, infrastructure/, docs/ y .github/workflows/.
- main es la rama protegida de producción; develop es la rama de integración. Proponer cambios mediante ramas de trabajo y revisión antes de integrar; no publicar ni cambiar protecciones sin autorización.
- ChatGPT Plus para ideación, análisis y documentación; Cursor Pro para desarrollo de código.
- No introducir decisiones de producto o arquitectura incompatibles con los documentos sin señalar el conflicto y solicitar una decisión.

## Stack acordado
- Backend: PHP 8.4, Symfony 7.4, MySQL 8.4 y Doctrine.
- Interfaz web: Twig, Bootstrap, Stimulus y Turbo.
- Aplicación móvil: Flutter, Riverpod, GoRouter y Dio.
- Android primero, con VS Code y Android Studio/SDK. El proyecto ya incluye los runners de iOS y de escritorio Windows. iOS se compila en un Mac con Xcode. El escritorio Windows requiere Visual Studio con la carga de C++. Ninguno de esos dos destinos está validado en E0.

## Arquitectura y desarrollo
- Monolito modular con arquitectura hexagonal selectiva donde aporte aislamiento del dominio y facilidad de prueba; evitar complejidad sin necesidad.
- SDD: partir de especificaciones y criterios de aceptación antes de implementar. Cada tarea tiene su Markdown en [docs/sdd/](docs/sdd/README.md) antes de codificar. TDD: probar el comportamiento esperado, implementar y refactorizar.
- Mantener contratos de API, modelo de datos, pruebas y documentación coherentes.
- Separación estricta por entidad fiscal: toda consulta, operación, documento, cálculo y acceso debe comprobar su pertenencia y autorización; impedir cruces entre entidades, también en pruebas.
- Las reglas fiscales y los cálculos son deterministas, explícitos y verificables. Solo soportar los modelos 130, 303 y 420 dentro del alcance documentado; no inferir soporte para otros modelos ni aplicar modelos sin comprobar su ámbito fiscal.
- La IA documental produce propuestas pendientes de validación humana; no convertir extracciones o clasificaciones en datos fiscales definitivos sin revisión ni delegar cálculos fiscales a un modelo generativo.
- No incorporar secretos ni datos fiscales personales reales a ejemplos, registros o pruebas.

## Documentación y alcance actual
- Los Word DOC-01 a DOC-09 son referencias: no editarlos, renombrarlos, moverlos ni eliminarlos sin una petición expresa.
- Registrar decisiones, criterios y evidencias de uso de IA de forma trazable, sin inventar resultados. El índice de evidencias vive en docs/evidence/. Una prueba no ejecutada permanece como no ejecutada.
- La entrega E0, cimientos técnicos, está integrada en `develop`. E1 empieza por el alta web con confirmación de correo, según DOC-07. DOC-07 sigue siendo propuesta.
- E0.1 deja alineadas estas instrucciones con esa estructura. E0.3 deja el esqueleto Symfony y Compose. E0.4 deja el esqueleto Flutter Android, con los runners de iOS y Windows preparados y sin validar. E0.5 deja `GET /api/v1/health`, los errores problem+json y el OpenAPI mínimo. E0.6 deja los workflows de comprobación en `.github/workflows/`. E0.7 deja las fichas EVI-0001 a EVI-0004. E0.8 deja MinIO local, compatible con S3, con el bucket `documents` privado. E1 deja el alta en `/registro`, el enlace de 30 minutos y Mailpit local.
- Cada tarea posterior exige autorización explícita del responsable. La app no llama a la API, no hay inicio de sesión, no hay despliegue y no se selecciona proveedor de IA ni de correo de producción. `main` exige las comprobaciones PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter. No se cambian sin autorización.
- El runtime PHP del proyecto es 8.4, dentro de PHP-FPM. El esquema fiscal completo de DOC-06 y la elección de proveedor de IA del producto quedan fuera de E0.

## Seguimiento de E0

Consultar [docs/E0_Plan_de_trabajo.md](docs/E0_Plan_de_trabajo.md) para las subdivisiones, su correspondencia con PLT-001 a PLT-006 y las comprobaciones pendientes.
