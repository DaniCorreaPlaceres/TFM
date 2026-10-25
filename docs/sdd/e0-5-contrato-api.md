# E0.5 — Contrato mínimo de API

Traza: PLT-005. Plan: [E0.5](../E0_Plan_de_trabajo.md).

## Objetivo

Exponer `GET /api/v1/health` y un contrato OpenAPI mínimo, con errores `application/problem+json` y un `requestId` por solicitud de la API.

## Fuera de alcance

- No implementar el esquema fiscal de DOC-06 ni otros recursos de negocio.
- No añadir autenticación, refresh ni datos fiscales.
- No hacer que la app Flutter llame a la API en esta tarea. Dio sigue preparado y sin consumo.
- No crear los pipelines de CI. La validación automática en GitHub corresponde a E0.6.
- No elegir proveedor de IA ni añadir MinIO.

## Criterios de aceptación

- `GET /api/v1/health` responde 200, `Content-Type: application/json` y un cuerpo `{"status":"ok"}`.
- Cada respuesta bajo `/api/` incluye la cabecera `X-Request-Id` con un UUID generado por el servidor.
- Cada solicitud bajo `/api/` deja una línea de log con ese `requestId`.
- Una ruta o método de API inexistente responde `Content-Type: application/problem+json` con `type`, `title`, `status` y `requestId`. El `requestId` del cuerpo coincide con la cabecera. El detalle no incluye trazas ni mensajes internos.
- `GET /` no pasa a problem+json: sigue siendo la bienvenida de Symfony.
- `backend/openapi/openapi.yaml` describe ese contrato en OpenAPI 3.0 y una prueba comprueba que el documento contiene la ruta, la cabecera y el esquema de error.
- `GET /nginx-health` sigue comprobando Nginx, no la aplicación.

## Resultado

Comprobado el 18 de octubre de 2026 en `feature/e0-contrato-api`. `php vendor/bin/phpunit` superó 5 pruebas. `GET /api/v1/health` respondió 200 `{"status":"ok"}` con `X-Request-Id`, y el log de desarrollo registró esa solicitud. Una ruta desconocida respondió 404 `application/problem+json` con el mismo `requestId` en cuerpo y cabecera. `GET /nginx-health` sigue respondiendo 200 `ok`. La app Flutter no llama a la API. La ficha de evidencia queda para E0.7.
