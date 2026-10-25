# EVI-0001 — Entorno Symfony

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0001 |
| Fecha | 2026-10-18 |
| Entrega / historia / requisito | E0.3 / PLT-002 / N/A |
| Objetivo | Dejar un entorno local reproducible con Symfony 7.4, PHP 8.4 en PHP-FPM, Doctrine, Nginx, MySQL 8.4 y Redis. |
| Herramienta | Cursor Pro, para la implementación. ChatGPT Plus no tiene una conversación archivada de esta tarea. |
| Contexto aportado | AGENTS.md, DOC-05 y el plan E0. Sin datos fiscales ni secretos. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El esqueleto Symfony y Compose que quedó en el repositorio. |
| Decisión del desarrollador | Aceptada al integrar la pull request #4 en `develop`. La pull request #3, dirigida a `main`, se cerró sin integrar. |
| Archivos | `backend/`, `infrastructure/compose.yaml` y la documentación de arranque. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | Integración de la pull request. El responsable del TFM aceptó esta ficha el 25 de octubre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La pull request #3 no entró porque apuntaba a `main`. El entorno se integró después hacia `develop`. |
| Commit / PR / CI | Commit `d38bf7c`. Pull request #4, merge `ed69869`. No hubo CI: los workflows llegan en E0.6. |
| Conclusión | El 18 de octubre de 2026 el entorno arrancó y la migración de ejemplo fue reversible. El esquema fiscal de DOC-06 y MinIO quedan fuera. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
