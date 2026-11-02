# EVI-0018 — Duplicados y recurrentes del gasto

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0018 |
| Fecha | 2026-11-02 |
| Entrega / historia / requisito | E4 / EXP-004 de DOC-07. |
| Objetivo | El titular ve un aviso si un gasto se parece a otro de la misma entidad, sin que el aviso borre ni impida guardar. Un gasto mensual puede generar una propuesta del mes siguiente, en borrador. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e4-duplicados.md` y EXP-004 de DOC-07. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El aviso sale con el mismo proveedor y el mismo número, o con el mismo proveedor, la misma fecha y el mismo total. La propuesta mensual nace en borrador, sin pagos y sin la deducibilidad decidida. Repetir el mismo mes no crea otra. |
| Decisión del desarrollador | El segundo factor sigue siendo opcional para todos los papeles, por la decisión del 1 de noviembre de 2026. DOC-04 no fija los campos del duplicado ni la cadencia. Este corte avisa con la regla anterior y solo repite cada mes. Los modelos 130, 303 y 420 no se calculan. DOC-07 sigue siendo propuesta. |
| Archivos | `backend/src/Controller/ExpenseController.php`, `backend/src/Expense/ExpenseDuplicates.php`, `backend/src/Expense/ProposeExpense.php`, `backend/src/Entity/Expense.php`, `backend/migrations/Version20261102233000.php` y `docs/sdd/e4-duplicados.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La otra cuenta no se recorrió en el navegador. La prueba web sí. El CSV, el historial, el bloqueo por cierre, la liquidación, la API y Flutter no forman parte de esta ficha. La rama no está integrada. |
| Commit / PR / CI | Sin commit. `feature/e4-duplicados` no está integrada. GitHub no se ha ejecutado para esta ficha. |
| Conclusión | En local, un segundo gasto con el mismo número queda guardado y avisa del primero. La propuesta del mes siguiente nace en borrador, con los importes copiados y sin los pagos ni la deducibilidad. Pedirla otra vez no crea una segunda. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
