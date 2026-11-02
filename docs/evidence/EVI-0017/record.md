# EVI-0017 — Pagos y estados de revisión del gasto

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0017 |
| Fecha | 2026-11-02 |
| Entrega / historia / requisito | E4 / EXP-003 de DOC-07. |
| Objetivo | El titular anota pagos sobre un gasto ya registrado y cambia su revisión solo por transiciones válidas. El estado de pago sale de esos pagos. Solo un gasto revisado es definitivo. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e4-revision.md` y EXP-003 de DOC-07. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Un gasto nuevo queda en borrador y pendiente. Un pago parcial o total actualiza el saldo. Desde borrador se pasa a revisado, rechazado o anulado. Desde revisado solo se anula. Rechazar y anular exigen motivo y dejan el gasto fuera de uso. |
| Decisión del desarrollador | El segundo factor sigue siendo opcional para todos los papeles, por la decisión del 1 de noviembre de 2026. No se añade el estado de revisión intermedio «pendiente» de DOC-04: pendiente queda reservado al pago. El pago cubierto se llama pagado. El historial de ediciones es EXP-005 y no se abre aquí. Los modelos 130, 303 y 420 no se calculan. DOC-07 sigue siendo propuesta. |
| Archivos | `backend/src/Controller/ExpenseController.php`, `backend/src/Expense/PayExpense.php`, `backend/src/Expense/ReviewExpense.php`, `backend/src/Entity/Expense.php`, `backend/migrations/Version20261102230000.php` y `docs/sdd/e4-revision.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable revisó esta entrega y aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La otra cuenta no se recorrió en el navegador. La prueba web sí. Los duplicados, los recurrentes, el CSV, el historial, la liquidación, la API y Flutter no forman parte de esta ficha. La rama no está integrada. |
| Commit / PR / CI | Sin commit. `feature/e4-revision` no está integrada. GitHub no se ha ejecutado para esta ficha. |
| Conclusión | En local, un pago parcial deja el gasto en parcial sin cambiar la base, la cuota ni la deducibilidad. Después puede quedar revisado y definitivo, y el pago no cambia. Otra cuenta no ve el gasto y un formulario caducado no guarda. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
