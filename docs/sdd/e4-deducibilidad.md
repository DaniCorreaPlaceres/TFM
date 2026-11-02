# E4 — Deducibilidad del coste y del impuesto

Traza: EXP-002 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). El criterio es que el coste y el impuesto sean campos independientes, cada uno con su razón, y que una liquidación use reglas fiscales. DOC-07 sigue siendo propuesta.

## Objetivo

El titular decide, sobre un gasto ya registrado, si la base es deducible y si la cuota es deducible. Cada decisión lleva su motivo. Los importes del gasto no cambian.

## Fuera de alcance

- No calcular los modelos 130, 303 y 420 ni liquidar el trimestre. La decisión queda guardada para esa liquidación posterior.
- No inferir la deducibilidad con un modelo de IA.
- No abrir estados de revisión, pagos, duplicados ni el CSV del gasto. Son EXP-003, EXP-004 y EXP-005.
- No bloquear el gasto por el cierre de trimestre de los ingresos.
- No crear la API ni hacer que la app Flutter la llame.
- No dejar que la asesoría consulte ni cambie esta decisión.
- No promocionar `develop` a `main` ni desplegar. E1, E2, E3 y E4 no quedan cerrados.

## Criterios de aceptación

- Al registrar el gasto, la base y la cuota quedan sin decidir.
- El titular puede marcar la base sin marcar la cuota, y al revés. Cada marca es sí o no y exige un motivo.
- Guardar una decisión no cambia la base, la cuota ni el total del gasto, ni la otra decisión.
- Otra cuenta no ve el motivo. Un formulario caducado no guarda.
- El segundo factor sigue siendo opcional.

## Decisiones

- No hay catálogo de motivos. El titular escribe el texto, de 1 a 240 caracteres. DOC-04 no fija esa lista en el corte leído.
- La decisión se puede sustituir. El historial de ediciones es EXP-005 y no se abre aquí.
- El identificador sigue siendo un UUIDv7 en texto.

## Resultado

Comprobado el 2 de noviembre de 2026 en local, en `feature/e4-deducibilidad`. PHPUnit: 69 pruebas y 1055 aserciones, con PHP 8.4.26. PHPStan sin errores. PHP-CS-Fixer: 0 de 252. La migración `Version20261102220000` se aplicó en la base local: 1 migración y 1 consulta.

En el navegador, el gasto G-1 seguía en base 200,00 €, cuota 42,00 € y total 242,00 €. La base quedó deducible con el motivo «Papel del mostrador» mientras la cuota seguía sin decidir. Después la cuota quedó no deducible y la base no cambió. La otra cuenta no se recorrió en el navegador; la prueba web sí. La rama no está integrada. No hay pull request. El responsable aceptó EVI-0016 el 2 de noviembre de 2026. E1, E2, E3 y E4 no quedan cerrados.
