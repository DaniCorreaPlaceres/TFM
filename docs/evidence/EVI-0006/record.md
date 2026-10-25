# EVI-0006 — Alta y verificación de correo

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0006 |
| Fecha | 2026-10-25 |
| Entrega / historia / requisito | E1 / IDN-001 / IDN-007 |
| Objetivo | Alta web con correo y contraseña, enlace de confirmación de un solo uso y 30 minutos, y rechazo de contraseñas filtradas. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación E1, DOC-04 IDN-001 e IDN-007, y DOC-05 apartado 5.1. Sin datos fiscales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El formulario `/registro`, Mailpit v1.32.0 y la consulta a Have I Been Pwned por rango de huella. |
| Decisión del desarrollador | Integrada en `develop` con la pull request #15. No hay inicio de sesión ni correo de producción. |
| Archivos | `backend/src/Identity/`, `backend/src/Controller/RegistrationController.php`, `backend/templates/registro/` e `infrastructure/compose.yaml`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable integró la pull request #15 y aceptó esta ficha el 25 de octubre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | Ninguno pendiente en la comprobación local. |
| Commit / PR / CI | Pull request #15, commit `0007981`, merge `b85068a`. Backend run 40216005170: PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI en verde. PHPUnit: 19 pruebas y 97 aserciones. Flutter run 40216005164 en verde. |
| Conclusión | En local y en el job PHPUnit de la pull request #15, el alta conserva el correo si la contraseña es corta, el enlace confirma la cuenta y el segundo uso se rechaza. Una cuenta sin confirmar no permite acceso fiscal. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
