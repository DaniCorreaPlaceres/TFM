# E0.1 — Instrucciones y estructura del monorepositorio

Traza reconstruida el 18 de octubre de 2026. Este archivo no existía antes de la implementación.

Traza: PLT-001. Plan: [E0.1](../E0_Plan_de_trabajo.md).

## Objetivo

Alinear las instrucciones del monorepo con la entrega E0 y dejar la estructura de directorios y el índice de documentación.

## Fuera de alcance

- No editar, renombrar ni mover los Word DOC-01 a DOC-09.
- No iniciar Symfony ni Flutter en esta tarea.
- No escribir fichas de evidencia de pruebas ejecutadas. El índice de `docs/evidence/` queda creado y vacío.

## Criterios de aceptación

- Existen `AGENTS.md` en la raíz, `backend/`, `mobile/` y `docs/`.
- `docs/README.md` enlaza los nueve Word con sus nombres reales.
- Existen `README.md` general y los directorios `backend/`, `mobile/`, `infrastructure/`, `docs/` y `.github/`.
- Existen `docs/evidence/README.md` y `docs/evidence/index.csv` sin filas de pruebas.
- La nota de CI no vive en `.github/workflows/`, porque la credencial usada no tenía alcance `workflow`.

## Resultado

Integrado en `develop` mediante el pull request #2 (`d00343c`, `0b742c6`). Los Word no se modificaron. El índice de evidencias sigue sin filas.
