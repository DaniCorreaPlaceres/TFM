# TFM — App Fiscal

Monorepositorio del TFM. Documentación y decisiones: [docs/README.md](docs/README.md). Instrucciones para agentes: [AGENTS.md](AGENTS.md).

## Estructura actual

- backend/: componente Symfony previsto; contiene instrucciones de trabajo.
- mobile/: aplicación Flutter prevista; contiene instrucciones de trabajo.
- docs/: documentos Word de referencia e índice Markdown.

Todavía no hay aplicación inicializada ni instrucciones de ejecución de Symfony o Flutter.

## Flujo Git

- main: línea de producción. No realizar commits de trabajo directamente; recibir cambios mediante PR revisada desde develop.
- develop: integración. Crear ramas feature/<descripcion> desde develop e integrarlas mediante PR.
- Revisar los cambios y ejecutar las comprobaciones correspondientes antes de integrar.
- No guardar secretos, credenciales, firmas Android ni datos fiscales personales reales.
- Conservar los archivos de bloqueo de dependencias cuando se inicialicen los proyectos.

Repositorio público: [DaniCorreaPlaceres/TFM](https://github.com/DaniCorreaPlaceres/TFM). Las ramas main y develop están publicadas. main tiene protección activa: exige PR con una aprobación de otra persona, descarta aprobaciones tras nuevos cambios y requiere resolver las conversaciones. Las reglas se aplican también a administradores; no se permiten pushes directos, pushes forzados ni borrados. Las comprobaciones obligatorias de CI quedan pendientes de crear los pipelines. No hay despliegue de producción configurado.

## Arranque del trabajo

Tras el commit inicial, trabajar desde develop y crear una rama feature/<descripcion> para cada tarea. Consultar el AGENTS.md del componente antes de realizar cambios.
