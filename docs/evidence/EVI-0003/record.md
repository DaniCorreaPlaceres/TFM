# EVI-0003 — Contrato mínimo de API

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0003 |
| Fecha | 2026-10-18, integrada el 2026-10-25 |
| Entrega / historia / requisito | E0.5 / PLT-005 / N/A |
| Objetivo | Exponer `GET /api/v1/health`, errores `application/problem+json` con `requestId` y un OpenAPI mínimo. |
| Herramienta | Cursor Pro, para la implementación y las pruebas. |
| Contexto aportado | AGENTS.md, la especificación E0.5 y el esqueleto de E0.3. Sin datos fiscales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El controlador de salud, los suscriptores de `requestId` y de errores, el OpenAPI y las pruebas PHPUnit. |
| Decisión del desarrollador | Aceptada al integrar la pull request #9. La app Flutter no quedó conectada a la API. |
| Archivos | `backend/src/Controller/HealthController.php`, `backend/src/Api/`, `backend/openapi/openapi.yaml` y `backend/tests/Api/`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | Integración de la pull request. El responsable del TFM aceptó esta ficha el 25 de octubre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La primera ejecución de PHPUnit falló porque el contenedor inyectaba `APP_ENV=dev` y el entorno de prueba no se imponía. Se forzó `APP_ENV=test` en `phpunit.dist.xml`. Después, las 5 pruebas pasaron. |
| Commit / PR / CI | Commit `e078b6c`. Pull request #9, merge `0c836a1`. La validación automática llega en EVI-0004. |
| Conclusión | La ruta de salud, el error `problem+json` y el contrato mínimo quedaron comprobados en local. La app no llama a la API. El esquema fiscal no forma parte del contrato. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
