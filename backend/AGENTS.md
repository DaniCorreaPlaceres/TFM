# Instrucciones del backend

Aplicar también [las instrucciones raíz](../AGENTS.md). Ámbito: backend/.

- Stack: PHP 8.4, Symfony 7.4, MySQL 8.4 y Doctrine; web con Twig, Bootstrap, Stimulus y Turbo.
- Organizar un monolito modular. Usar puertos y adaptadores selectivamente para aislar lógica de dominio e integraciones; mantener controladores y plantillas libres de reglas fiscales.
- Especificar casos de uso y contratos antes de implementar; aplicar TDD a comportamientos y criterios de aceptación.
- Comprobar autorización y pertenencia a la entidad fiscal en cada operación. Las consultas y relaciones de Doctrine no deben permitir acceso cruzado; incluir pruebas de aislamiento.
- Implementar cálculos fiscales deterministas y comprobables solo para los modelos 130, 303 y 420 soportados y sus ámbitos documentados. No usar IA generativa para decidir importes o reglas.
- Conservar el estado pendiente de revisión de datos extraídos por IA hasta la validación humana explícita; mantener trazabilidad de la validación.
- Consultar DOC-04, DOC-05, DOC-06 y DOC-08 en [el índice](../docs/README.md) para funcionalidad, arquitectura, datos, contratos y pruebas.
- La inicialización de Symfony 7.4, PHP-FPM 8.4, MySQL 8.4, Redis y las dependencias corresponde a E0.3, cuando esa tarea esté autorizada. Hasta entonces backend/ solo contiene estas instrucciones.
- En E0 la migración permitida es un ejemplo reversible. El catálogo físico de DOC-06 sigue siendo propuesta hasta su implementación posterior.
