# E0.2 — Git, remoto y protección de ramas

Traza reconstruida el 18 de octubre de 2026. Este archivo no existía antes de la implementación.

Traza: PLT-001 y parte de PLT-004. Plan: [E0.2](../E0_Plan_de_trabajo.md).

## Objetivo

Dejar el repositorio público con `main` como producción y `develop` como integración, y proteger `main`.

## Fuera de alcance

- No crear los pipelines de CI. Corresponden a E0.6.
- No exigir todavía comprobaciones obligatorias de CI en la protección de `main`.
- No desplegar a producción.

## Criterios de aceptación

- El remoto es `https://github.com/DaniCorreaPlaceres/TFM.git`, con `main` y `develop` publicadas.
- El trabajo se hace en `feature/*` desde `develop` y se integra mediante pull request.
- `main` exige pull request, una aprobación de otra persona, descarta aprobaciones tras nuevos cambios y exige resolver las conversaciones.
- Las reglas se aplican también a administradores. No se permiten pushes directos, pushes forzados ni borrados.

## Resultado

Configuración integrada en `develop` (`8a9e773`, pull request #1). La promoción a `main` sigue requiriendo la aprobación de otra persona. Las comprobaciones obligatorias de CI quedan para E0.6.
