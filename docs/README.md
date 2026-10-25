# Documentación del TFM — Aselto

Aselto. Eslogan: «Tus finanzas bajo control». Dominio web: aselto.com.

Índice de los documentos Word existentes. Los enlaces son relativos a este directorio y conservan sus nombres originales.

## Identidad y diseño

- Nombre: Aselto. Eslogan: «Tus finanzas bajo control». Dominio web: aselto.com.
- [Reglas visuales](reglas-visuales-diseno.md), versión 0.1.
- [Logo con eslogan](aselto-logo-eslogan-v2.png) e [icono](icono.png).
- Bocetos: [inicio](bocetos/aselto-inicio-web-movil-v1.png) y [documentos](bocetos/aselto-documentos-movil-v1.png).

## Documentos de referencia

- [DOC-01_Memoria_Toma_Decisiones_Producto_y_Arquitectura_v1.1](DOC-01_Memoria_Toma_Decisiones_Producto_y_Arquitectura_v1.1.docx)
- [DOC-02_Stack_Tecnologico_v1.1](DOC-02_Stack_Tecnologico_v1.1.docx)
- [DOC-03_Herramientas_IA_v1.1](DOC-03_Herramientas_IA_v1.1.docx)
- [DOC-04_Especificacion_Funcional_Completa_v1.1](DOC-04_Especificacion_Funcional_Completa_v1.1.docx)
- [DOC-05_Arquitectura_y_Especificacion_Tecnica_v1.1](DOC-05_Arquitectura_y_Especificacion_Tecnica_v1.1.docx)
- [DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1](DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx)
- [DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1](DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx)
- [DOC-08_Pruebas_TFM_v1.1](DOC-08_Pruebas_TFM_v1.1.docx)
- [DOC-09_Registro_Evidencias_IA_v1.1](DOC-09_Registro_Evidencias_IA_v1.1.docx)

## Guías de trabajo

- [Instrucciones generales](../AGENTS.md)
- [Backend](../backend/AGENTS.md)
- [Aplicación móvil](../mobile/AGENTS.md)
- [Documentación](AGENTS.md)
- [Memoria de trabajo](memoria.md)
- [Especificaciones por tarea](sdd/README.md)
- [Evidencias de IA](evidence/README.md)
- [Infraestructura](../infrastructure/README.md)
- [Workflows](../.github/README.md)

## Acuerdos de referencia

- Monorepo: backend/, mobile/, infrastructure/, docs/ y .github/workflows/. main protegida para producción y develop para integración.
- PHP 8.4, Symfony 7.4, MySQL 8.4 y Doctrine; Twig, Bootstrap, Stimulus y Turbo para la web.
- Flutter con Riverpod, GoRouter y Dio; Android primero con VS Code y Android Studio/SDK; iOS futuro con Mac/Xcode.
- ChatGPT Plus para ideación y Cursor Pro para código.
- Monolito modular con hexagonal selectiva, SDD/TDD y separación estricta por entidad fiscal.
- IA documental siempre sujeta a validación humana; reglas fiscales deterministas y soporte limitado a los modelos 130, 303 y 420 en el ámbito documentado.

Los Word se conservan sin cambios. E0.3 inicializa el esqueleto Symfony, E0.4 el esqueleto Flutter, E0.5 el contrato mínimo `GET /api/v1/health` y E0.6 los workflows de comprobación. DOC-04 y DOC-05 forman la línea base aprobada; DOC-06, DOC-07 y DOC-08 conservan el estado indicado en cada documento. La protección de main ya está configurada en GitHub; su estado se describe en el [README general](../README.md). Desde el 25 de octubre de 2026 esa protección exige PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter. El índice de evidencias tiene las fichas EVI-0001 a EVI-0007. El responsable aceptó EVI-0001 a EVI-0007 el 25 de octubre de 2026. EVI-0005 corresponde a MinIO y quedó integrada con la pull request #14. EVI-0006 corresponde al alta web, integrada con la pull request #15. EVI-0007 corresponde al inicio de sesión web, comprobada en local, aceptada el mismo día y abierta en la pull request #17.

## Seguimiento de E0

Consultar [Plan operativo E0.1–E0.8](E0_Plan_de_trabajo.md) para las subdivisiones, su correspondencia con PLT-001 a PLT-006 y las comprobaciones pendientes.
