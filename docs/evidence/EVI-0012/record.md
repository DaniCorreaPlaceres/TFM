# EVI-0012 — Alta de asesoría e invitación de empleados

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0012 |
| Fecha | 2026-11-01 |
| Entrega / historia / requisito | E2 / ADV-001 de DOC-07. |
| Objetivo | La cuenta web crea una asesoría, invita a un empleado por correo y el servidor solo muestra esa asesoría a quien pertenece a ella. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e2-asesoria.md`, DOC-07 ADV-001 y la propuesta de tablas de DOC-06. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Alta en `/asesorias` e invitación por correo con resumen SHA-256. Confirmar ese correo, o entrar si la cuenta ya existía, deja a la persona como empleada. |
| Decisión del desarrollador | El 1 de noviembre de 2026 el responsable decidió que el segundo factor es opcional para todos los papeles, también para la asesoría y para la superadministración si llega a existir. DOC-04 y DOC-05 apartado 5.1 piden que sea obligatorio en esos papeles; esa obligación no se aplica. DOC-04 y DOC-05 siguen siendo la línea base aprobada, con esta excepción. DOC-07 sigue siendo propuesta. |
| Archivos | `backend/src/Controller/AdvisoryController.php`, `backend/src/Identity/CreateAdvisoryFirm.php`, `backend/src/Identity/InviteAdvisoryEmployee.php`, `backend/src/Identity/AcceptAdvisoryInvitation.php`, `backend/migrations/Version20261101223000.php` y `docs/sdd/e2-asesoria.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | El vínculo con una entidad fiscal, los permisos por empleado, la desvinculación y la bandeja no formaban parte de esta ficha. |
| Commit / PR / CI | Commit `dd63f66` en `feature/e2-asesoria`. La ficha se aceptó antes de la pull request. Después quedó integrada en `develop` con la pull request #23, merge `e8c8786`. Los registros de GitHub de esa pull request no se copiaron aquí. |
| Conclusión | En local, la administradora crea la asesoría e invita. Quien confirma el correo invitado queda como empleada, sin segundo factor, y no abre otra asesoría. Otra cuenta no ve el nombre y una empleada no invita. Un formulario caducado no crea, no invita y no acepta. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
