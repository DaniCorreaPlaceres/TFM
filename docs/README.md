# Documentación del TFM — App Fiscal

Índice de los documentos Word existentes. Los enlaces son relativos a este directorio y conservan sus nombres originales.

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
- [Evidencias de IA](evidence/README.md)
- [Infraestructura](../infrastructure/README.md)
- [Workflows previstos](../.github/README.md)

## Acuerdos de referencia

- Monorepo: backend/, mobile/, infrastructure/, docs/ y .github/workflows/. main protegida para producción y develop para integración.
- PHP 8.4, Symfony 7.4, MySQL 8.4 y Doctrine; Twig, Bootstrap, Stimulus y Turbo para la web.
- Flutter con Riverpod, GoRouter y Dio; Android primero con VS Code y Android Studio/SDK; iOS futuro con Mac/Xcode.
- ChatGPT Plus para ideación y Cursor Pro para código.
- Monolito modular con hexagonal selectiva, SDD/TDD y separación estricta por entidad fiscal.
- IA documental siempre sujeta a validación humana; reglas fiscales deterministas y soporte limitado a los modelos 130, 303 y 420 en el ámbito documentado.

Los Word se conservan sin cambios. E0.3 inicializa el esqueleto Symfony y E0.4 el esqueleto Flutter, con Android comprobado y los runners de iOS y Windows preparados. DOC-04 y DOC-05 forman la línea base aprobada; DOC-06, DOC-07 y DOC-08 conservan el estado indicado en cada documento. La protección de main ya está configurada en GitHub; su estado se describe en el [README general](../README.md). El índice de evidencias está creado y todavía no tiene filas.

## Seguimiento de E0

Consultar [Plan operativo E0.1–E0.8](E0_Plan_de_trabajo.md) para las subdivisiones, su correspondencia con PLT-001 a PLT-006 y las comprobaciones pendientes.
