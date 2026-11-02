# EVI-0014 — Permiso del empleado para ver entidades

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0014 |
| Fecha | 2026-11-02 |
| Entrega / historia / requisito | E2 / ADV-003 de DOC-07. |
| Objetivo | La administradora concede o retira a un empleado ver los nombres de las entidades vinculadas. Sin esa concesión, el empleado no las ve. El servidor decide el permiso efectivo. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e2-permisos.md`, DOC-07 ADV-003 y la propuesta de permisos de DOC-06. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Concesión en `advisory_employee_permission` con el código `ver_entidades`. Si no hay fila, el empleado no ve los nombres. La administradora los ve por su papel. Invitar sigue reservado a quien administra. |
| Decisión del desarrollador | El segundo factor sigue siendo opcional para todos los papeles, por la decisión del 1 de noviembre de 2026. DOC-06 propone un catálogo amplio y un alcance de clientes total por defecto; esta ficha no lo adopta. DOC-06 y DOC-07 siguen siendo propuesta. |
| Archivos | `backend/src/Controller/AdvisoryController.php`, `backend/src/Identity/SetEmployeeEntityView.php`, `backend/src/Entity/AdvisoryEmployeePermission.php`, `backend/migrations/Version20261102015000.php` y `docs/sdd/e2-permisos.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La primera carga tras conceder el permiso respondió 500 hasta borrar `var/cache/dev`. Después la misma página mostró el permiso guardado. El formulario caducado y la cuenta ajena no se repitieron en el navegador. La desvinculación y la bandeja no forman parte de esta ficha. La rama no está integrada. El mismo árbol contiene la presentación de `docs/sdd/front-aselto.md`, que esta ficha no cierra. |
| Commit / PR / CI | Sin commit. `feature/e2-permisos` no está integrada. GitHub no se ha ejecutado para esta ficha. |
| Conclusión | En local, la administradora concede y retira ver los nombres. Con la concesión, la empleada los ve y sigue sin invitar. Sin ella, no los ve. Un empleado no concede el permiso y un formulario caducado no lo cambia. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
