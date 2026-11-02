# E1 — Segundo factor opcional en la web

Traza: IDN-003 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). En [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) el segundo factor es IDN-005, y el IDN-003 de ese documento son los roles coexistentes. [DOC-05](../DOC-05_Arquitectura_y_Especificacion_Tecnica_v1.1.docx), apartado 5.1, pide TOTP obligatorio para asesorías y superadministración, opcional para autónomos, y códigos de recuperación. DOC-07 sigue siendo propuesta: esta ficha no cambia ese estado.

## Objetivo

Permitir que la cuenta web que ya entra en `/entrar` active un segundo factor TOTP. Si está activo, el correo y la contraseña no bastan: hace falta un código de la aplicación o un código de recuperación de un solo uso.

## Fuera de alcance

- No crear los roles de asesoría ni de superadministración.
- No implementar la app Flutter, el refresh móvil, las entidades fiscales ni la revocación remota.
- No elegir proveedor de correo de producción ni de hosting.
- No promocionar `develop` a `main` ni desplegar.

## Criterios de aceptación

- Una cuenta sin segundo factor entra como hasta ahora, con correo y contraseña.
- Activar el segundo factor muestra un código QR del enlace de la aplicación y exige un código TOTP válido. Hasta entonces no queda activo.
- Con el segundo factor activo, el acceso pide un código después de la contraseña correcta. Un código incorrecto no abre la sesión.
- Cada código de recuperación sirve una sola vez. Un código ya usado no vuelve a servir.
- El secreto TOTP no se guarda en claro y los códigos de recuperación tampoco.

## Decisiones

- La cuenta actual no tiene rol. Se trata como el caso opcional del autónomo. El 1 de noviembre de 2026 el responsable decidió que el segundo factor es opcional para todos los papeles, también para la asesoría y para la superadministración si llega a existir. DOC-04 y DOC-05 apartado 5.1 piden que sea obligatorio en esos papeles; esa obligación no se aplica. DOC-04 y DOC-05 siguen siendo la línea base aprobada, con esta excepción.
- DOC-04, DOC-05 y DOC-07 no fijan cuántos códigos de recuperación se entregan. Esta ficha entrega 8. Si el responsable fija otro número, se cambia aquí y en el código.
- TOTP usa SHA-1, 6 cifras y un periodo de 30 segundos, que es lo que esperan las aplicaciones de códigos. Se acepta el intervalo actual y uno anterior y uno posterior.
- El código QR contiene ese mismo enlace. Es negro sobre blanco, en SVG, para que la aplicación pueda leerlo. El secreto sigue escrito en la página para quien no pueda escanear.
- El secreto se cifra en la base de datos con una clave derivada de `APP_SECRET`. Cambiar `APP_SECRET` deja inservibles los secretos ya guardados.
- Los códigos de recuperación se muestran una vez, al activar el segundo factor. En la base solo se guarda su resumen SHA-256.
- Los intentos del código siguen el límite ya usado en el acceso: 5 fallos por cuenta cada 15 minutos.
- Desactivar el segundo factor, desde la sesión ya abierta, exige un código TOTP o uno de recuperación todavía válido.

## Resultado

Comprobado el 1 de noviembre de 2026 en `feature/e1-segundo-factor`. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios. PHPUnit pasó 41 pruebas y 308 aserciones. En el navegador, una cuenta sin segundo factor entra en `/sesion`. Al activarlo, la página muestra el código QR, el secreto y un enlace `otpauth`. Un código TOTP válido deja el segundo factor activo y muestra 8 códigos de recuperación. Al recargar, esos códigos ya no aparecen. Con el segundo factor activo, la contraseña correcta lleva a `/entrar/codigo`. Un código incorrecto no abre la sesión. Un código de recuperación abre la sesión una vez; el mismo código, en el acceso siguiente, responde que no es válido. La base guarda el secreto cifrado y el resumen del código, no el código en claro. La app Flutter no se ejecutó en local: esta entrega no la modifica y sigue sin llamar a la API. No hay roles de asesoría ni de superadministración. El 1 de noviembre de 2026 quedó integrada en `develop` con la pull request #19, merge `7d7f29f`, commit `8597b15`. En GitHub, el run 41347891944 dejó PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI en verde. PHPUnit informó 41 pruebas y 308 aserciones. Flutter pasó en el run 41347891937. El responsable aceptó EVI-0008 ese mismo día. DOC-07 sigue siendo propuesta. La integración no cierra E1.
