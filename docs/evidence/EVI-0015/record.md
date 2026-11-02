# EVI-0015 — Categorías y registro manual del gasto

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0015 |
| Fecha | 2026-11-02 |
| Entrega / historia / requisito | E4 / CAT-001 y EXP-001 de DOC-07. |
| Objetivo | El titular de la entidad activa tiene categorías de un nivel y registra un gasto manual. La base y la cuota salen de las líneas y cuadran con el total. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación `docs/sdd/e4-gastos.md`, DOC-07 CAT-001 y EXP-001, y el cálculo de líneas ya usado en los ingresos. Sin datos fiscales personales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | Categorías de serie y otras creadas por el titular. Una categoría se desactiva y no se borra. El gasto guarda la copia del proveedor y de la categoría. |
| Decisión del desarrollador | El segundo factor sigue siendo opcional para todos los papeles, por la decisión del 1 de noviembre de 2026. Las categorías de serie son Suministros, Alquiler, Servicios profesionales, Material, Transporte y Otros. El método de pago es transferencia, tarjeta, efectivo, domiciliación u otro. El cierre de trimestre de los ingresos no bloquea estos gastos. DOC-06 y DOC-07 siguen siendo propuesta. |
| Archivos | `backend/src/Controller/ExpenseController.php`, `backend/src/Expense/RegisterExpense.php`, `backend/src/Entity/Expense.php`, `backend/migrations/Version20261102191000.php` y `docs/sdd/e4-gastos.md`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable probó esta entrega y aceptó esta ficha el 2 de noviembre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | La otra cuenta no se recorrió en el navegador. La prueba web sí. La deducibilidad, los estados de revisión, el CSV, la asesoría, la API y Flutter no forman parte de esta ficha. La rama no está integrada. |
| Commit / PR / CI | Sin commit. `feature/e4-gastos` no está integrada. GitHub no se ha ejecutado para esta ficha. |
| Conclusión | En local, el titular crea una categoría, registra el gasto y desactiva una categoría usada. El gasto conserva el nombre y el total cuadra con las líneas. Otra cuenta no ve el gasto. Un formulario caducado no lo guarda. |
| Responsable de aprobación | Responsable del TFM, 2026-11-02. |

Enlaces: [links.md](links.md).
