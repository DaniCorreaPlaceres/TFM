# EVI-0007 — Inicio de sesión y recuperación de contraseña

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0007 |
| Fecha | 2026-10-25 |
| Entrega / historia / requisito | E1 / IDN-002 |
| Objetivo | Inicio de sesión web con cookie de 120 minutos y recuperación por enlace de un solo uso y 30 minutos, sin revelar si el correo existe. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación de sesión, DOC-04 IDN-002 y DOC-05 apartado 5.1. Sin datos fiscales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El formulario `/entrar`, la cookie `aselto_sesion` en Redis, el enlace `/recuperar/clave/{token}` y Mailpit. |
| Decisión del desarrollador | Queda en `feature/e1-sesion`. El límite de 5 intentos cada 15 minutos no está en los documentos: DOC-05 pide limitar y no fija el número. El refresh móvil no entra en esta entrega. |
| Archivos | `backend/src/Security/`, `backend/src/Controller/SessionController.php`, `backend/src/Controller/PasswordResetController.php`, `backend/templates/sesion/` y `docs/sdd/e1-sesion.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 25 de octubre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | Ninguno pendiente en la comprobación local. |
| Commit / PR / CI | Sin pull request y sin resultado de GitHub. |
| Conclusión | En local, una cuenta confirmada entra, la cookie dura 7200 segundos y es HttpOnly, Redis guarda la sesión, el enlace de recuperación no distingue si el correo existe y la sesión anterior deja de servir al cambiar la contraseña. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
