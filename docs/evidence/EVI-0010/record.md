# EVI-0010 — Aislamiento de la entidad fiscal

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0010 |
| Fecha | 2026-11-01 |
| Entrega / historia / requisito | E1 / SEC-001 de DOC-07. |
| Objetivo | Una cuenta no lee ni cambia la entidad fiscal de otra en las rutas web que ya existen. |
| Herramienta | Cursor Pro, para la prueba. |
| Contexto aportado | La especificación `docs/sdd/e1-aislamiento.md` y DOC-07 SEC-001. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Prueba web entre dos cuentas sobre `/sesion`, `/entidades` y la elección por identificador. |
| Decisión del desarrollador | El recurso comprobable hoy es la entidad fiscal. DOC-07 también pide la exportación; no existe esa ruta, así que esa parte queda no ejecutada. |
| Archivos | `backend/tests/Identity/FiscalEntityIsolationWebTest.php` y `docs/sdd/e1-aislamiento.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 1 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La exportación no se ejecutó. No hay perfil fiscal ni libros. |
| Commit / PR / CI | Pull request #21, commit `0f10edb`, merge `986501c`. El registro de las comprobaciones de GitHub no se copió en esta ficha. |
| Conclusión | En local, la segunda cuenta no ve el nombre ni el NIF de la entidad ajena. Elegir su identificador responde 404 y no modifica los datos guardados. Una sesión que contiene ese identificador no muestra la entidad ajena. La exportación no se ejecutó. |
| Responsable de aprobación | Responsable del TFM, 2026-11-01. |

Enlaces: [links.md](links.md).
