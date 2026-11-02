# E2 — Alta de asesoría e invitación de empleados

Traza: ADV-001 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-06](../DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx) propone `advisory_firm`, `advisory_membership` y `advisory_invitation`. DOC-06 y DOC-07 siguen siendo propuesta.

## Objetivo

La cuenta web crea una asesoría, invita a un empleado por correo y el servidor solo muestra esa asesoría a quien pertenece a ella.

## Fuera de alcance

- No vincular la asesoría con una entidad fiscal. Es ADV-002.
- No repartir permisos por empleado ni por cliente. Es ADV-003.
- No desvincular ni abrir la bandeja de trabajo. Son ADV-004 y ADV-005.
- No crear la API ni hacer que la app Flutter la llame.
- No promocionar `develop` a `main` ni desplegar. E1 no queda cerrado.

## Criterios de aceptación

- Una cuenta confirmada crea una asesoría y queda como administradora.
- La administradora invita a un correo. El enlace caduca a los 7 días y solo se usa una vez.
- Quien crea la cuenta con ese correo queda en la asesoría al confirmarlo. Quien ya tenía cuenta queda al entrar. No hace falta segundo factor.
- Quien no pertenece a la asesoría no la ve. Una empleada no invita a otra persona ni abre otra asesoría.
- Un formulario caducado no crea ni invita ni acepta.

## Decisiones

- El 1 de noviembre de 2026 el responsable decidió que el segundo factor es opcional para todos los papeles, también para la asesoría y para la superadministración si llega a existir. DOC-04, DOC-05 apartado 5.1 y DOC-07 piden que sea obligatorio para asesorías y superadministración. Esta ficha no aplica esa obligación. DOC-04 y DOC-05 siguen siendo la línea base aprobada, con esta excepción. DOC-07 sigue siendo propuesta.
- El identificador es un UUIDv7 en texto, como la cuenta. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.
- La asesoría guarda el nombre y el NIF, con la misma normalización que la entidad fiscal y sin dígito de control.
- La invitación guarda el correo y solo el resumen SHA-256 del enlace. DOC-06 fija 7 días para `advisory_invitation`. Una invitación nueva deja inservible la anterior del mismo correo y la misma asesoría.
- Quien no tiene cuenta la crea con el correo invitado. Al confirmar ese correo queda como empleada. Quien ya tenía cuenta queda como empleada al entrar. El enlace sigue sirviendo para abrir la asesoría.
- Los papeles de esta ficha son administradora y empleada. No hay estado de baja.

## Resultado

Comprobado el 1 de noviembre de 2026 en `feature/e2-asesoria`. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios en 135 archivos. PHPUnit pasó 51 pruebas y 470 aserciones. La migración `Version20261101223000` se aplicó a la base local.

En el navegador, una cuenta confirmada crea la asesoría. El NIF introducido como `b-12 000002` queda como `B12000002` y la cuenta aparece como administradora. La invitación dice que caduca a los 7 días y Mailpit recibe el enlace. Si la abre otra sesión, responde que es para otro correo y no muestra el nombre. Quien crea la cuenta con el correo invitado queda en la asesoría al confirmarlo, sin segundo factor. Al entrar ve esa asesoría como empleada y no puede abrir otra ni invitar. Al salir, la dirección de la asesoría vuelve a `/entrar` y no muestra el nombre.

PHPUnit comprueba además el nombre vacío, que una invitación nueva deja inservible la anterior, que a los 8 días ya no sirve, que confirmar el correo invitado deja a la persona como empleada, que una empleada no invita ni abre otra asesoría y que otra cuenta recibe 404 sin el nombre. Un formulario caducado no crea, no invita y no acepta. La app Flutter no se ejecutó en local y sigue sin llamar a la API. No hay vínculo con una entidad fiscal, permisos por empleado, desvinculación ni bandeja. La rama no está integrada en `develop`. El responsable aceptó EVI-0012 el 2 de noviembre de 2026. DOC-07 sigue siendo propuesta. E1 y E2 no quedan cerrados.
