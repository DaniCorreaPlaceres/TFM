# EVI-0016 — Deducibilidad del coste y del impuesto

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0016 |
| Fecha | 2026-11-02 |
| Entrega / historia / requisito | E4 / EXP-002 de DOC-07. |
| Objetivo | El titular decide, sobre un gasto ya registrado, si la base es deducible y si la cuota es deducible. Cada decisión lleva su motivo. Los importes no cambian. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e4-deducibilidad.md` y EXP-002 de DOC-07. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Dos decisiones independientes, cada una sí o no y con un motivo escrito. Al registrar el gasto quedan sin decidir. Guardar una no cambia la otra ni la base, la cuota o el total. |
| Decisión del desarrollador | El segundo factor sigue siendo opcional para todos los papeles, por la decisión del 1 de noviembre de 2026. No hay catálogo de motivos. Los modelos 130, 303 y 420 no se calculan. No se infiere la deducibilidad con un modelo de IA. DOC-07 sigue siendo propuesta. |
| Archivos | `backend/src/Controller/ExpenseController.php`, `backend/src/Expense/DecideExpenseDeduction.php`, `backend/src/Entity/Expense.php`, `backend/migrations/Version20261102220000.php` y `docs/sdd/e4-deducibilidad.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable probó esta entrega y aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La otra cuenta no se recorrió en el navegador. La prueba web sí. Los estados de revisión, los pagos, los duplicados, el CSV, la liquidación, la API y Flutter no forman parte de esta ficha. La rama no está integrada. |
| Commit / PR / CI | Sin commit. `feature/e4-deducibilidad` no está integrada. GitHub no se ha ejecutado para esta ficha. |
| Conclusión | En local, la base puede quedar deducible mientras la cuota sigue sin decidir. Después la cuota puede quedar no deducible sin cambiar la base ni los importes. Otra cuenta no ve el motivo y un formulario caducado no guarda. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
