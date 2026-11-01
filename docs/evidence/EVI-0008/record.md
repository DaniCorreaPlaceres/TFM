# EVI-0008 — Segundo factor opcional en la web

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0008 |
| Fecha | 2026-11-01 |
| Entrega / historia / requisito | E1 / IDN-003 de DOC-07. En DOC-04 el segundo factor es IDN-005. |
| Objetivo | Segundo factor TOTP opcional para la cuenta web, con códigos de recuperación de un solo uso. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e1-segundo-factor.md`, DOC-07 IDN-003, DOC-04 IDN-005 y DOC-05 apartado 5.1. Sin datos fiscales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Activación en `/sesion/segundo-factor` con código QR del enlace, comprobación en `/entrar/codigo` y 8 códigos de recuperación mostrados una sola vez. |
| Decisión del desarrollador | La cuenta actual no tiene rol, así que el segundo factor es opcional. El obligatorio de asesoría y superadministración queda para cuando existan esos roles. Los documentos no fijan el número de códigos: esta entrega usa 8. |
| Archivos | `backend/src/Identity/SecondFactor.php`, `backend/src/Identity/Totp.php`, `backend/src/Controller/SecondFactorController.php`, `backend/templates/sesion/` y `docs/sdd/e1-segundo-factor.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 1 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | Ninguno pendiente en la comprobación local. No hay roles de asesoría ni de superadministración. |
| Commit / PR / CI | Pull request #19, commit `8597b15`, merge `7d7f29f`. Backend run 41347891944: PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI en verde. PHPUnit: 41 pruebas y 308 aserciones. Flutter run 41347891937 en verde. |
| Conclusión | En local y en el job PHPUnit de la pull request #19, una cuenta sin segundo factor entra como hasta ahora. Con el segundo factor activo, la contraseña no basta. Un código de recuperación abre la sesión una vez y el mismo código después no sirve. El secreto y los códigos no se guardan en claro. |
| Responsable de aprobación | Responsable del TFM, 2026-11-01. |

Enlaces: [links.md](links.md).
