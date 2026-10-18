# TFM — App Fiscal

Monorepositorio del TFM. Documentación y decisiones: [docs/README.md](docs/README.md). Instrucciones para agentes: [AGENTS.md](AGENTS.md).

## Estructura

- backend/: Symfony 7.4 previsto. Hoy solo contiene instrucciones. Se inicializa en E0.3, con PHP 8.4 en PHP-FPM.
- mobile/: Flutter previsto. Hoy solo contiene instrucciones. Se inicializa en E0.4.
- infrastructure/: Docker, proxy y despliegue. El directorio está preparado; Compose llega en E0.3 y MinIO en E0.8. Ver [infrastructure/README.md](infrastructure/README.md).
- docs/: documentos Word de referencia, índice Markdown y registro de evidencias. Ver [docs/README.md](docs/README.md).
- .github/workflows/: pipelines previstos en E0.6. Todavía no hay workflows. Ver [.github/workflows/README.md](.github/workflows/README.md).

Todavía no hay aplicación inicializada ni instrucciones de ejecución de Symfony o Flutter.

## Entrega en curso

E0 — cimientos técnicos, según DOC-07 (PLT-001 a PLT-006). El detalle operativo está en [AGENTS.md](AGENTS.md).

- E0.1: alinear instrucciones y estructura del monorepositorio.
- E0.2: rama de trabajo y estado de la protección. El estado conocido de main está en Flujo Git.
- E0.3 a E0.8: pendientes de autorización explícita.

## Flujo Git

- main: línea de producción. No realizar commits de trabajo directamente; recibir cambios mediante PR revisada desde develop.
- develop: integración. Crear ramas feature/<descripcion> desde develop e integrarlas mediante PR.
- Revisar los cambios y ejecutar las comprobaciones correspondientes antes de integrar.
- No guardar secretos, credenciales, firmas Android ni datos fiscales personales reales.
- Conservar los archivos de bloqueo de dependencias cuando se inicialicen los proyectos.

Repositorio público: [DaniCorreaPlaceres/TFM](https://github.com/DaniCorreaPlaceres/TFM). Las ramas main y develop están publicadas. main tiene protección activa: exige PR con una aprobación de otra persona, descarta aprobaciones tras nuevos cambios y requiere resolver las conversaciones. Las reglas se aplican también a administradores; no se permiten pushes directos, pushes forzados ni borrados. Las comprobaciones obligatorias de CI quedan pendientes de crear los pipelines. No hay despliegue de producción configurado.

## Arranque del trabajo

Trabajar desde develop y crear una rama feature/<descripcion> para cada tarea. Consultar el AGENTS.md del componente antes de realizar cambios. Las instrucciones de arranque de Symfony y Flutter se añadirán cuando E0.3 y E0.4 estén implementadas.

## Seguimiento de E0

Consultar [Plan operativo E0.1–E0.8](docs/E0_Plan_de_trabajo.md) para las subdivisiones, su correspondencia con PLT-001 a PLT-006 y las comprobaciones pendientes.
