# E3 — Ingresos y contactos

Traza: CNT-001, INC-001, INC-002, INC-003 e INC-004 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) define el directorio, la instantánea, el ingreso con líneas, el cobro parcial, el justificante, la anulación, el cierre y la importación. [DOC-06](../DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx) propone `contact`, `income`, `income_line` e `income_payment`. DOC-06 y DOC-07 siguen siendo propuesta.

## Objetivo

El titular de la entidad activa registra clientes y proveedores, ingresos con varias líneas e impuestos, cobros parciales y un justificante. El estado de cobro sale de los cobros. Un trimestre cerrado no se edita. La administradora de la asesoría vinculada puede consultarlos.

## Fuera de alcance

- No registrar gastos, categorías ni deducibilidad. Es E4.
- No crear el documento lógico, la bandeja ni el análisis con IA. Son E5 y E6. El justificante de esta ficha es un archivo privado ligado al ingreso.
- No calcular los modelos 130, 303 ni 420, ni abrir el expediente fiscal de territorio, actividades y regímenes.
- No emitir facturas ni presentar impuestos.
- No crear la API ni hacer que la app Flutter la llame.
- No desvincular, ni conceder permisos por empleado, ni abrir la bandeja de la asesoría. El permiso del empleado sigue en otra rama.
- No reabrir un trimestre. La reapertura controlada queda fuera.
- No promocionar `develop` a `main` ni desplegar. E1, E2 y E3 no quedan cerrados.

## Criterios de aceptación

- Un contacto puede ser cliente, proveedor o ambos. El NIF, el NIE o el CIF se comprueban con su dígito de control. Dos contactos de la misma entidad no comparten NIF.
- Una cuenta no ve ni cambia contactos, ingresos, cobros ni archivos de otra entidad.
- El ingreso admite varias líneas, con base, cuota y retención calculadas sin flotantes. El total cuadra con las líneas.
- Al guardar el ingreso se copia el nombre y el NIF del contacto. Un cambio posterior del contacto no cambia esa copia. Corregir el ingreso y mantener el mismo contacto tampoco la cambia.
- Un cobro no puede superar el saldo. El estado queda pendiente, parcial o cobrado según los cobros. Repetir el mismo envío no duplica el cobro.
- Anular no borra líneas, cobros ni el historial de acciones.
- El titular puede cerrar un trimestre. Mientras esté cerrado no se corrige, cobra, anula ni adjunta un ingreso de ese trimestre.
- El justificante se descarga solo con autorización. Quien no puede ver el ingreso no obtiene el archivo.
- La importación CSV informa de las filas admitidas y de las rechazadas. Un ingreso ya registrado con el mismo número, NIF y fecha se rechaza.
- La administradora de la asesoría vinculada ve los ingresos y puede descargar el justificante. No los modifica. Una empleada no ve el nombre de la entidad ni sus ingresos.

## Decisiones

- El identificador es un UUIDv7 en texto. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.
- El NIF del contacto sí lleva dígito de control. El de la entidad fiscal sigue sin él, como quedó en E1.
- Los importes se guardan con dos decimales. La cantidad y el precio unitario admiten hasta cuatro. Cada producto y cada porcentaje se redondean al céntimo, mitad hacia arriba, con enteros. No se usa coma flotante. La moneda es el euro.
- El tipo impositivo y la retención son datos de la factura externa, entre 0 y 100. Esta ficha no decide si el modelo fiscal los admite: eso es E7.
- El estado de cobro no se guarda en una columna. Se obtiene de los cobros y de la anulación, para que no pueda desviarse.
- La copia del contacto vive en el ingreso (`snapshot_name`, `snapshot_tax_id`). DOC-06 cita el snapshot sin fijar las columnas.
- El justificante se guarda en el bucket privado `documents`, con una clave que incluye la entidad y el ingreso. No se crea `document` ni `stored_file`: esas tablas son E5. En las pruebas el almacén se sustituye por uno en memoria.
- El cierre es un trimestre natural de la fecha del ingreso. No se copia aquí el ciclo abierto, en revisión y cerrado de `tax_period`.
- La empleada no ve la entidad. Sigue la decisión ya aceptada en el vínculo de E2. DOC-04 contempla un acceso inicial a toda la cartera; el permiso explícito está en otra rama y no se anticipa aquí.
- La vista de la asesoría es de solo lectura. Quien modifica el ingreso es el titular.
- El segundo factor sigue siendo opcional para todos, por la decisión del 1 de noviembre de 2026.
- La plantilla CSV usa las columnas `fecha`, `numero`, `contacto`, `nif`, `descripcion`, `cantidad`, `precio`, `iva` y `retencion`. DOC-04 deja la plantilla concreta para una especificación derivada; esta es la de esta entrega.

## Resultado

Comprobado el 2 de noviembre de 2026 en `feature/e3-ingresos`. PHP 8.4.26. PHPStan 2.3.2, nivel 6, no informó errores. PHP-CS-Fixer 3.95.27 no propuso cambios en 212 archivos. PHPUnit 13.4.3 pasó 59 pruebas y 763 aserciones. Una prueba quedó omitida: la del bucket privado, porque esa ejecución no definió `MINIO_ENDPOINT`. La migración `Version20261102030000` se aplicó a la base local: 1 migración y 20 consultas.

En el navegador, sobre esta rama en `http://127.0.0.1:8081`, una cuenta confirmada entra en `/sesion`. El alta de la entidad con el NIF `b-18 000018` la deja activa como `B18000018` y la sesión muestra Contactos e Ingresos y gastos. El contacto escrito como ` 12345678-z ` queda como Ana Ruiz (12345678Z) y como cliente. El ingreso F-1, del 2 de noviembre de 2026, con una línea de 2 × 100,00 € al 21 % y 15 % y otra de 1 × 50,00 € al 10 % y 0 %, muestra base 250,00 €, cuota 47,00 €, retención 30,00 € y total 267,00 €, pendiente. Un cobro de 100,00 € por transferencia deja el estado en parcial y el saldo en 167,00 €. Mailpit recibió el enlace de confirmación. Ese servidor temporal se detuvo; el de `http://127.0.0.1:8080` no se cambió.

PHPUnit comprueba además el NIF inválido, el contacto duplicado, que repetir el mismo cobro no lo duplica, que un cobro por encima del saldo se rechaza, el cobro que completa el total, la descarga del justificante, que anular conserva líneas y cobros, la importación CSV con filas admitidas y rechazadas, el trimestre cerrado y que otra cuenta y una empleada no ven el ingreso. La administradora de la asesoría vinculada lo consulta y descarga el justificante; un envío suyo para cobrar responde 404. La app Flutter no se ejecutó y sigue sin llamar a la API. No hay gastos, documento lógico ni modelos 130, 303 o 420. La rama no está integrada. DOC-07 sigue siendo propuesta. E1, E2 y E3 no quedan cerrados.
