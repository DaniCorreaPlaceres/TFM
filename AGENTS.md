# Instrucciones del proyecto TFM — App Fiscal

Estas instrucciones se aplican a todo el monorepo. Consultar también el AGENTS.md del directorio de trabajo y el índice [docs/README.md](docs/README.md).

## Organización y herramientas
- Monorepo con backend/, mobile/ y docs/.
- main es la rama protegida de producción; develop es la rama de integración. Proponer cambios mediante ramas de trabajo y revisión antes de integrar; no publicar ni cambiar protecciones sin autorización.
- ChatGPT Plus para ideación, análisis y documentación; Cursor Pro para desarrollo de código.
- No introducir decisiones de producto o arquitectura incompatibles con los documentos sin señalar el conflicto y solicitar una decisión.

## Stack acordado
- Backend: PHP 8.4, Symfony 7.4, MySQL 8.4 y Doctrine.
- Interfaz web: Twig, Bootstrap, Stimulus y Turbo.
- Aplicación móvil: Flutter, Riverpod, GoRouter y Dio.
- Android primero, con VS Code y Android Studio/SDK. iOS queda para una fase futura con Mac y Xcode.

## Arquitectura y desarrollo
- Monolito modular con arquitectura hexagonal selectiva donde aporte aislamiento del dominio y facilidad de prueba; evitar complejidad sin necesidad.
- SDD: partir de especificaciones y criterios de aceptación antes de implementar. TDD: probar el comportamiento esperado, implementar y refactorizar.
- Mantener contratos de API, modelo de datos, pruebas y documentación coherentes.
- Separación estricta por entidad fiscal: toda consulta, operación, documento, cálculo y acceso debe comprobar su pertenencia y autorización; impedir cruces entre entidades, también en pruebas.
- Las reglas fiscales y los cálculos son deterministas, explícitos y verificables. Solo soportar los modelos 130, 303 y 420 dentro del alcance documentado; no inferir soporte para otros modelos ni aplicar modelos sin comprobar su ámbito fiscal.
- La IA documental produce propuestas pendientes de validación humana; no convertir extracciones o clasificaciones en datos fiscales definitivos sin revisión ni delegar cálculos fiscales a un modelo generativo.
- No incorporar secretos ni datos fiscales personales reales a ejemplos, registros o pruebas.

## Documentación y alcance actual
- Los Word DOC-01 a DOC-09 son referencias: no editarlos, renombrarlos, moverlos ni eliminarlos sin una petición expresa.
- Registrar decisiones, criterios y evidencias de uso de IA de forma trazable, sin inventar resultados.
- La tarea actual se limita a los cuatro AGENTS.md y docs/README.md. No iniciar proyectos, instalar dependencias ni generar código Symfony o Flutter.
