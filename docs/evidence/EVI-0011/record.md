# EVI-0011 — Cerrar las demás sesiones web

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0011 |
| Fecha | 2026-11-01 |
| Entrega / historia / requisito | E1 / IDN-004 de DOC-07. En DOC-04 la revocación es IDN-006. |
| Objetivo | Desde la sesión web abierta, cerrar las demás sesiones web de la misma cuenta. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e1-revocacion.md`, DOC-07 IDN-004 y DOC-04 IDN-006. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Acción en `/sesion/cerrar-demas` que avanza el marcador de sesión y lo escribe solo en la sesión actual. |
| Decisión del desarrollador | No hay listado de sesiones. El refresh, su reutilización y el cambio de permisos no existen todavía, así que quedan sin ejecutar. |
| Archivos | `backend/src/Identity/CloseOtherWebSessions.php`, `backend/src/Controller/SessionController.php`, `backend/templates/sesion/dentro.html.twig` y `docs/sdd/e1-revocacion.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 1 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | El refresh, su reutilización y el cambio de permisos no se ejecutaron. |
| Commit / PR / CI | Aceptada antes del commit. Después quedó integrada en `develop` con la pull request #22, commit `1a62c08`, merge `f37a227`. Los registros de GitHub de esa pull request no se copiaron a esta ficha. |
| Conclusión | En local, la sesión desde la que se cierra sigue abierta. Otra cookie de la misma cuenta vuelve a `/entrar`. Un formulario caducado no cierra la otra sesión. |
| Responsable de aprobación | Responsable del TFM, 2026-11-01. |

Enlaces: [links.md](links.md).
