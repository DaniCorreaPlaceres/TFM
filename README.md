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

La protección de main es una política documentada: su aplicación efectiva requiere un remoto y configurar sus reglas. No hay despliegue de producción configurado.

## Arranque del trabajo

Tras el commit inicial, trabajar desde develop y crear una rama feature/<descripcion> para cada tarea. Consultar el AGENTS.md del componente antes de realizar cambios.
