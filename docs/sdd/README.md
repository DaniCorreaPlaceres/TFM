# Especificaciones por tarea

Cada tarea o rama de trabajo tiene un Markdown en este directorio antes de implementar. El archivo describe esa tarea: objetivo, fuera de alcance, criterios de aceptación y traza. No sustituye a los Word DOC-01 a DOC-09.

## Cómo escribir el siguiente

1. Crear `docs/sdd/<tarea>.md` con el nombre de la rama, en minúsculas y con guiones.
2. Incluir objetivo, fuera de alcance, criterios de aceptación y traza al DOC, PLT o E0 que corresponda.
3. Si el contenido choca con DOC-04 o DOC-05, detenerse y pedir una decisión. No reescribir el Word.
4. Implementar después en una rama `feature/<tarea>`.
5. Al cerrar, anotar solo lo ejecutado. Una comprobación no hecha permanece como no ejecutada. Las fichas de evidencia van a [docs/evidence/](../evidence/README.md) en E0.7, no aquí.

## Tareas ya integradas

Estas fichas se reconstruyeron el 18 de octubre de 2026 a partir del plan y de los commits ya integrados en `develop`. No existían antes de la implementación.

- [E0.1 Instrucciones y estructura](e0-1-instrucciones-estructura.md)
- [E0.2 Git y protección](e0-2-git-proteccion.md)
- [E0.3 Entorno Symfony](e0-3-entorno-symfony.md)
- [E0.4 Esqueleto Flutter](e0-4-esqueleto-flutter.md)
- [Reglas visuales y bocetos](reglas-visuales-aselto.md)
- [Nombre, eslogan y dominio](nombre-eslogan-dominio.md)

## Tareas con especificación

- [E0.5 Contrato mínimo de API](e0-5-contrato-api.md)
- [E0.6 Integración continua](e0-6-integracion-continua.md)
- [E0.7 Evidencias](e0-7-evidencias.md)
- [E0.8 Almacenamiento local](e0-8-minio.md)
- [E1 Alta y verificación de correo](e1-registro.md)
- [E1 Inicio de sesión y recuperación](e1-sesion.md)
