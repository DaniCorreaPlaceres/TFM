# E4 — Pagos y estados de revisión del gasto

Traza: EXP-003 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). El criterio pide pendiente, parcial o pagado; borrador, revisado, rechazado o anulado; y estados válidos. DOC-07 sigue siendo propuesta. [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) es la línea base y, en su EXP-003 y EXP-005, pide pagos que actualicen el saldo y una revisión con transición autorizada.

## Objetivo

El titular anota pagos sobre un gasto ya registrado y cambia su revisión solo por transiciones válidas. El estado de pago sale de esos pagos. Solo un gasto revisado es definitivo. Rechazarlo o anularlo lo deja fuera de uso.

## Fuera de alcance

- No calcular los modelos 130, 303 y 420 ni liquidar el trimestre.
- No abrir duplicados, recurrentes, el CSV ni el historial de ediciones. Son EXP-004 y EXP-005.
- No bloquear el gasto por el cierre de trimestre de los ingresos. Ese bloqueo del gasto es EXP-005.
- No inferir estados con un modelo de IA.
- No crear la API ni hacer que la app Flutter la llame.
- No dejar que la asesoría consulte ni cambie el gasto.
- No promocionar `develop` a `main` ni desplegar. E1, E2, E3 y E4 no quedan cerrados.

## Criterios de aceptación

- Un gasto nuevo queda en revisión borrador y en pago pendiente. No es definitivo.
- Un pago no supera el saldo. El estado pasa a parcial o a pagado según lo anotado. Repetir el mismo envío no duplica el pago.
- La base, la cuota, el total y la deducibilidad no cambian al anotar un pago ni al cambiar la revisión.
- Desde borrador se puede pasar a revisado, rechazado o anulado. Desde revisado solo se puede anular. Rechazar y anular exigen un motivo. El resto de transiciones no se guardan.
- Un gasto rechazado o anulado no admite otro pago, otra revisión ni un cambio de deducibilidad. Los pagos ya anotados se conservan.
- Solo la revisión revisado es definitiva.
- Otra cuenta no ve el gasto. Un formulario caducado no guarda.
- El segundo factor sigue siendo opcional.

## Decisiones

- DOC-04 describe la revisión como borrador, pendiente, revisado, rechazado o anulado, en ese orden. DOC-07, en el criterio de esta historia, no incluye pendiente como revisión. Este corte no añade ese estado intermedio: pendiente queda reservado al pago, que es como se aceptó al pedir la historia. El responsable puede pedir el estado intermedio si quiere seguir el literal de DOC-04.
- DOC-04 llama «completo» al pago cubierto. Este corte lo llama «pagado», que es la palabra del criterio de DOC-07.
- DOC-04 pide auditoría en la transición. El historial de ediciones es EXP-005 y no se abre aquí. La autorización de este corte es la del titular de la entidad.
- El identificador sigue siendo un UUIDv7 en texto. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.
- Cada pago guarda importe, fecha, método, referencia y un identificador de envío. El método usa la misma lista que el gasto. La referencia puede ir vacía y no pasa de 80 caracteres.
- Los importes reutilizan el cálculo del ingreso: céntimos enteros, sin coma flotante. Un importe vacío, cero o negativo no se guarda.
- El motivo de rechazo o anulación tiene de 1 a 240 caracteres, con los espacios colapsados.
- Un gasto rechazado o anulado puede conservar un pago parcial o total. Esas combinaciones se muestran, y el gasto deja de estar vigente.

## Resultado

Comprobado el 2 de noviembre de 2026 en local, en `feature/e4-revision`. PHPUnit: 71 pruebas y 1160 aserciones, con PHP 8.4.26. PHPStan sin errores. PHP-CS-Fixer: 0 de 257. La migración `Version20261102230000` se aplicó en la base local: 1 migración y 4 consultas.

En el navegador, el gasto G-1 seguía en base 200,00 €, cuota 42,00 € y total 242,00 €, en borrador y pendiente. Un pago de 100,00 € lo dejó en parcial, con 100,00 € anotados y 142,00 € de saldo. La revisión pasó a revisado y definitivo, y el pago no cambió. La base seguía deducible con «Papel del mostrador» y la cuota no deducible. La otra cuenta no se recorrió en el navegador; la prueba web sí. Quedó integrada en `develop` con la pull request #29, merge `b193fec`. Los registros de GitHub de esa pull request no se copiaron. El responsable aceptó EVI-0017 el 2 de noviembre de 2026. E1, E2, E3 y E4 no quedan cerrados.
