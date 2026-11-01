# EVI-0009 — Crear y elegir la entidad fiscal

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0009 |
| Fecha | 2026-11-01 |
| Entrega / historia / requisito | E1 / ORG-001 de DOC-07. |
| Objetivo | La cuenta web crea una o varias entidades fiscales y elige cuál está activa en la sesión. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e1-entidad.md`, DOC-07 ORG-001 y la definición de entidad fiscal de DOC-04. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Alta en `/entidades`, entidad activa en la sesión y rechazo de una entidad que la cuenta no tiene. |
| Decisión del desarrollador | El identificador es un UUIDv7 en texto, como la cuenta. DOC-06 propone `BINARY(16)` en el esquema completo, que sigue siendo propuesta y no se adopta. El NIF se guarda en mayúsculas, sin espacios ni guiones, y esta ficha no aplica el dígito de control. La entidad activa vive en la sesión, no en una columna de la cuenta. El único papel es titular. |
| Archivos | `backend/src/Entity/FiscalEntity.php`, `backend/src/Identity/CreateFiscalEntity.php`, `backend/src/Identity/ActiveFiscalEntity.php`, `backend/src/Controller/FiscalEntityController.php`, `backend/templates/entidad/lista.html.twig` y `docs/sdd/e1-entidad.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 1 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | Ninguno pendiente en la comprobación local. No hay perfil fiscal, libros ni exportación. |
| Commit / PR / CI | Pull request #20, commit `71af2c2`, merge `a6409cd`. Las comprobaciones de GitHub concluyeron en verde. El registro de PHPUnit de ese job no se copió en esta ficha. |
| Conclusión | En local, una cuenta confirmada crea varias entidades y elige la activa. Un nombre en blanco no crea nada. El NIF queda en mayúsculas, sin espacios ni guiones. Sin sesión, `/entidades` vuelve a `/entrar`. Otra cuenta no puede elegir la entidad. Un identificador desconocido responde 404 en PHPUnit. |
| Responsable de aprobación | Responsable del TFM, 2026-11-01. |

Enlaces: [links.md](links.md).
