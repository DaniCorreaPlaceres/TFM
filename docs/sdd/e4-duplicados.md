# E4 — Duplicados y recurrentes del gasto

Traza: EXP-004 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). El criterio pide alertas de duplicados sin supresión automática, y que los repetitivos creen propuestas no confirmadas. DOC-07 sigue siendo propuesta. [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) pide, en EXP-006, detectar posibles duplicados sin bloquear lo legítimo y mostrar una alerta con contexto. En EXP-007 pide una propuesta provisional que no quede confirmada sin revisión.

## Objetivo

Al registrar un gasto, el titular ve si en la misma entidad ya hay otro parecido. El aviso no borra ni impide guardar. Un gasto mensual puede generar una propuesta del mes siguiente, en borrador, que no es definitiva hasta que se revise.

## Fuera de alcance

- No borrar, fusionar ni rechazar un gasto por ser un posible duplicado.
- No programar la repetición sola. La propuesta se crea cuando el titular la pide.
- No abrir el CSV, el historial de ediciones ni el bloqueo del gasto por cierre de trimestre. Son EXP-005.
- No calcular los modelos 130, 303 y 420.
- No inferir el duplicado ni la propuesta con un modelo de IA.
- No crear la API ni hacer que la app Flutter la llame.
- No dejar que la asesoría consulte ni cambie el gasto.
- No promocionar `develop` a `main` ni desplegar. E1, E2, E3 y E4 no quedan cerrados.

## Criterios de aceptación

- Dos gastos de la misma entidad y del mismo proveedor se avisan si comparten el número de documento, o si comparten la fecha y el total.
- El aviso muestra el número, la fecha, el total y la revisión del otro gasto. Los dos gastos siguen guardados.
- Un gasto de otro proveedor, de otra entidad o con otra fecha y otro número no se avisa, aunque el total coincida.
- El titular puede marcar un gasto vigente como mensual y crear una propuesta del mes siguiente.
- La propuesta copia proveedor, categoría y líneas, nace en borrador, sin pagos y sin la deducibilidad decidida. No es definitiva.
- Pedir otra vez la misma propuesta no crea una segunda. El gasto de origen no cambia.
- Un gasto rechazado o anulado no se marca ni genera propuesta.
- Otra cuenta no ve el aviso ni crea la propuesta. Un formulario caducado no guarda.
- El segundo factor sigue siendo opcional.

## Decisiones

- DOC-04 no fija los campos del posible duplicado ni la cadencia. Este corte avisa por el mismo proveedor y el mismo número, o por el mismo proveedor, la misma fecha y el mismo total. La cadencia es mensual. El responsable puede cambiar esa regla.
- El día 31 pasa al último día del mes siguiente. El 31 de enero de 2026 cae el 28 de febrero.
- El número de la propuesta es el de origen con el sufijo `-P`, recortado a 40 caracteres.
- La fecha de la propuesta es un mes después de la fecha del gasto desde el que se pide, no de la última propuesta.
- Un gasto anulado o rechazado también puede aparecer en el aviso. El aviso no lo borra.
- El identificador sigue siendo un UUIDv7 en texto. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.

## Resultado

Comprobado el 2 de noviembre de 2026 en local, en `feature/e4-duplicados`. PHPUnit: 73 pruebas y 1241 aserciones, con PHP 8.4.26. PHPStan sin errores. PHP-CS-Fixer: 0 de 263. La migración `Version20261102233000` se aplicó en la base local: 1 migración y 3 consultas.

En el navegador, el gasto G-1 seguía revisado y definitivo, con base 200,00 €, cuota 42,00 €, total 242,00 €, 100,00 € anotados y saldo 142,00 €. Al marcarlo mensual se creó la propuesta G-1-P, del 2 de diciembre de 2026, en borrador y no definitiva, con los mismos importes, sin pagos y sin la deducibilidad decidida. Pedirla otra vez respondió que ya hay una propuesta para ese mes. Un segundo G-1, del 4 de noviembre y de 10,00 €, quedó guardado y avisó del primero. La otra cuenta no se recorrió en el navegador; la prueba web sí. La rama no está integrada. No hay pull request. El responsable aceptó EVI-0018 el 2 de noviembre de 2026. E1, E2, E3 y E4 no quedan cerrados.
