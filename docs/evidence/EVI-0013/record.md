# EVI-0013 — Vínculo entre entidad fiscal y asesoría

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0013 |
| Fecha | 2026-11-02 |
| Entrega / historia / requisito | E2 / ADV-002 de DOC-07. |
| Objetivo | El titular de una entidad fiscal y la administradora de una asesoría pueden invitarse. Al aceptar queda un vínculo. Una entidad tiene como mucho una asesoría activa. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e2-vinculo.md`, DOC-07 ADV-002 y la propuesta de tablas de DOC-06. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Invitación en los dos sentidos, con resumen SHA-256 y caducidad de 7 días. Al aceptar se elige la otra parte. Si la entidad ya tiene una asesoría activa, la invitación no se acepta. |
| Decisión del desarrollador | El segundo factor sigue siendo opcional para todos los papeles, por la decisión del 1 de noviembre de 2026. DOC-04 y DOC-05 apartado 5.1 piden que sea obligatorio para la asesoría; esa obligación no se aplica. La invitación de empleado sigue en su tabla. El vínculo usa `advisory_client_link` y `advisory_client_invitation`. No hay columna de estado. DOC-06 y DOC-07 siguen siendo propuesta. |
| Archivos | `backend/src/Controller/AdvisoryClientController.php`, `backend/src/Identity/InviteAdvisoryForEntity.php`, `backend/src/Identity/InviteEntityForAdvisory.php`, `backend/src/Identity/AcceptAdvisoryClientInvitation.php`, `backend/migrations/Version20261102004000.php` y `docs/sdd/e2-vinculo.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | Los permisos por empleado, la desvinculación y la bandeja no se ejecutaron. La rama no está integrada. |
| Commit / PR / CI | Sin commit. `feature/e2-vinculo` no está integrada. GitHub no se ha ejecutado para esta ficha. |
| Conclusión | En local, las dos invitaciones vinculan la entidad y la asesoría. Una segunda asesoría no sustituye a la activa. Una empleada no ve la entidad y otra cuenta no ve el nombre. Un formulario caducado no invita ni acepta. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
