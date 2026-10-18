# E0.3 — Entorno Symfony reproducible

Traza reconstruida el 18 de octubre de 2026. Este archivo no existía antes de la implementación.

Traza: PLT-002. Plan: [E0.3](../E0_Plan_de_trabajo.md).

## Objetivo

Dejar un entorno local reproducible con Symfony 7.4, PHP 8.4 en PHP-FPM, Doctrine, Nginx, MySQL 8.4 y Redis.

## Fuera de alcance

- No usar el PHP del equipo anfitrión como runtime del proyecto.
- No crear el esquema fiscal de DOC-06. La migración de esta tarea es un ejemplo reversible.
- No crear `GET /api/v1/health`. Corresponde a E0.5.
- No añadir MinIO. Corresponde a E0.8.

## Criterios de aceptación

- Compose define Nginx, PHP-FPM 8.4, MySQL 8.4 y Redis, con healthchecks.
- El arranque, la parada y la migración de ejemplo están documentados desde la raíz del repositorio.
- La migración de ejemplo sube, baja y vuelve a subir.
- `GET /nginx-health` responde 200.
- La página de bienvenida de Symfony puede responder 404 mientras no exista una ruta de aplicación.

## Resultado

Integrado en `develop` mediante el pull request #4 (`d38bf7c`). Comprobado el 18 de octubre de 2026: PHP 8.4.26, Symfony 7.4.21, MySQL 8.4.9, Redis 7.4.11 y Nginx 1.28.3. La migración `Version20261018030000` subió, bajó y volvió a subir. `GET /nginx-health` respondió 200. `GET /` mostró la bienvenida de Symfony 7.4. La ficha de evidencia queda para E0.7.
