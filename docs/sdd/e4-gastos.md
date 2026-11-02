# E4 — Categorías y registro del gasto

Traza: CAT-001 y EXP-001 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) pide categorías de un nivel y el gasto manual con líneas. [DOC-06](../DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx) propone el esquema completo. DOC-06 y DOC-07 siguen siendo propuesta.

## Objetivo

El titular de la entidad activa clasifica gastos y registra una factura, un ticket u otro gasto, con varias líneas y un método de pago. La base y la cuota salen de las líneas y cuadran con el total.

## Fuera de alcance

- No marcar deducibilidad ni razones fiscales. Es EXP-002.
- No abrir estados de revisión, duplicados, recurrentes ni el CSV del gasto. Son EXP-003, EXP-004 y EXP-005.
- No bloquear el gasto por el cierre de trimestre de los ingresos.
- No crear el documento lógico ni calcular los modelos 130, 303 y 420.
- No crear la API ni hacer que la app Flutter la llame.
- No desvincular ni abrir la bandeja de la asesoría. La administradora no consulta todavía estos gastos.
- No promocionar `develop` a `main` ni desplegar. E1, E2, E3 y E4 no quedan cerrados.

## Criterios de aceptación

- Cada entidad tiene categorías de serie y puede crear otras. Un solo nivel. El nombre no se repite en esa entidad.
- Una categoría usada se desactiva y no se borra. El gasto ya guardado conserva el nombre.
- Una categoría desactivada no sirve para un gasto nuevo.
- El gasto es factura, ticket u otro. El proveedor es un contacto de la entidad marcado como proveedor. El método de pago es uno de la lista.
- Las líneas calculan base, cuota y retención sin coma flotante. El total cuadra con las líneas.
- Otra cuenta no ve el número, el proveedor ni la categoría. Un formulario caducado no guarda.
- El segundo factor sigue siendo opcional.

## Decisiones

- El identificador es un UUIDv7 en texto. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.
- Las categorías de serie son Suministros, Alquiler, Servicios profesionales, Material, Transporte y Otros. Se crean para la entidad la primera vez que se abre la lista.
- El método de pago es transferencia, tarjeta, efectivo, domiciliación u otro. DOC-04 no fija esta lista en el corte leído; esta es la de esta entrega.
- Los importes reutilizan el cálculo del ingreso: céntimos enteros, mitad hacia arriba, euro. El tipo y la retención son los de la factura externa.
- Al guardar se copian el nombre y el NIF del proveedor y el nombre de la categoría.
- No hay columna de estado de revisión ni de deducibilidad.

## Resultado

Comprobado el 2 de noviembre de 2026 en local, en `feature/e4-gastos`. PHPUnit: 67 pruebas y 991 aserciones, con PHP 8.4.26. Tras añadir los accesores que pedía el análisis estático, se repitió la prueba web del gasto: 1 prueba y 60 aserciones. PHPStan sin errores. PHP-CS-Fixer, repetido después: 0 de 249. La migración `Version20261102191000` se aplicó en la base local: 1 migración y 8 consultas.

En el navegador, sin entidad, `/gastos` pide elegir una. Con la entidad activa aparecen las categorías de serie. Se añadió Software. El gasto G-1 quedó en base 200,00 €, cuota 42,00 € y total 242,00 €. Material se desactivó, el gasto siguió mostrando ese nombre y el selector de un gasto nuevo ya no la ofrece. La otra cuenta no se recorrió en el navegador; la prueba web sí. Quedó integrada en `develop` con la pull request #27, merge `f13e0f9`. Los registros de GitHub de esa pull request no se copiaron. El responsable aceptó EVI-0015 el 2 de noviembre de 2026. E1, E2, E3 y E4 no quedan cerrados.
