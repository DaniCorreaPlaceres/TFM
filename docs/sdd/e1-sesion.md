# E1 — Inicio de sesión y recuperación de contraseña

Traza: IDN-002 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx) y de [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx). La cookie, Redis, la inactividad de 120 minutos y el enlace de 30 minutos siguen [DOC-05](../DOC-05_Arquitectura_y_Especificacion_Tecnica_v1.1.docx), apartado 5.1. DOC-07 sigue siendo propuesta: esta ficha no cambia ese estado.

## Objetivo

Permitir entrar en la web con correo y contraseña, y recuperar la contraseña con un enlace de un solo uso. La sesión web caduca por inactividad. La recuperación no revela si el correo existe.

## Fuera de alcance

- No implementar el refresh rotatorio ni el JWT de la app. DOC-07 los nombra en IDN-002, pero la app Flutter sigue sin llamar a la API.
- No implementar 2FA, roles, entidades fiscales ni el listado de sesiones para revocación remota. Son IDN-003, IDN-004 e IDN-006.
- No elegir proveedor de correo de producción ni de hosting.
- No promocionar `develop` a `main` ni desplegar.
- No ejecutar el dispositivo Android físico, iOS ni el escritorio Windows.

## Criterios de aceptación

- Una cuenta confirmada entra con correo y contraseña. Una cuenta sin confirmar no entra.
- Un correo o una contraseña incorrectos reciben el mismo mensaje.
- La sesión web usa una cookie HttpOnly y SameSite. Dura 120 minutos de inactividad y se guarda en Redis.
- El enlace de recuperación caduca a los 30 minutos, deja de servir al usarse y no permite saber si el correo existe. Pedir otro enlace deja inservible el anterior si aún no se ha usado.
- La contraseña nueva sigue la regla de 12 caracteres y de contraseñas filtradas.
- Al cambiar la contraseña, la sesión anterior deja de autorizar el acceso.
- Los intentos de acceso y de recuperación están limitados.

## Decisiones

- Esta entrega es solo web. El refresh móvil queda para cuando la app llame a la API.
- La cookie lleva `Secure` cuando la petición es HTTPS. En Compose local la web se abre por HTTP, así que ahí la cookie no se marca `Secure`. SameSite es `Lax`.
- Una cuenta sin confirmar, con la contraseña correcta, ve el aviso de confirmar el correo. Si la contraseña no es correcta, el mensaje es el mismo que para un correo desconocido.
- DOC-05 pide limitar los intentos y no fija el número. Esta ficha usa 5 intentos fallidos de acceso y 5 solicitudes de recuperación por correo, cada 15 minutos. Si el responsable fija otro número, se cambia aquí y en la configuración.
- El correo de recuperación sale por Mailpit, igual que el de alta. Mailpit no es el correo de producción.
- Cambiar la contraseña cierra las sesiones web ya abiertas. El listado para revocarlas a distancia queda en IDN-006.

## Resultado

Comprobado el 25 de octubre de 2026 en `feature/e1-sesion`. PHPUnit pasó 33 pruebas y 216 aserciones. PHPStan no informó errores. En el navegador, una cuenta sin confirmar no entra y ve el aviso de confirmar el correo. Tras confirmar, entra en `/sesion`. La cookie real se llama `aselto_sesion`, es HttpOnly, SameSite es Lax, dura 7200 segundos y en HTTP local no lleva `Secure`. Redis guardó la sesión con una caducidad justo por debajo de esos 7200 segundos. Un correo desconocido y uno confirmado reciben la misma página de recuperación. Mailpit mostró el enlace solo para la cuenta confirmada. El enlace usado dejó de servir. Al guardar la contraseña nueva, la sesión anterior dejó de abrir `/sesion` y la contraseña nueva sí entró. Las pruebas no llaman a Have I Been Pwned. La app Flutter no llama a la API. La pull request #17, commit `0e3dfb2`, sigue sin integrar. Las comprobaciones de GitHub estaban en curso y su resultado no se anota todavía. DOC-07 sigue siendo propuesta. El responsable aceptó EVI-0007 el 25 de octubre de 2026.
