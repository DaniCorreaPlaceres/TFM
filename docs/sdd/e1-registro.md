# E1 — Alta y verificación de correo

Traza: IDN-001 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx) y de [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx). La contraseña sigue [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) IDN-007 y [DOC-05](../DOC-05_Arquitectura_y_Especificacion_Tecnica_v1.1.docx), apartado 5.1. DOC-07 sigue siendo propuesta: esta ficha no cambia ese estado.

## Objetivo

Permitir un alta web con correo y contraseña. El enlace de confirmación es de un solo uso y caduca. Hasta verificar el correo, la cuenta no accede a información fiscal.

## Fuera de alcance

- No implementar inicio de sesión, recuperación, 2FA, roles ni entidades fiscales. Son IDN-002 a IDN-008 y el resto de E1.
- No hacer que la app Flutter llame a la API.
- No elegir proveedor de correo de producción ni de hosting.
- No promocionar `develop` a `main` ni desplegar.
- No ejecutar el dispositivo Android físico, iOS ni el escritorio Windows.
- No guardar documentos de negocio en MinIO.

## Criterios de aceptación

- Un alta con correo y contraseña crea una cuenta que todavía no está verificada.
- La contraseña de menos de 12 caracteres se rechaza.
- El enlace de confirmación caduca a los 30 minutos y deja de servir después de usarse. El responsable fijó esa caducidad el 25 de octubre de 2026. Los 30 minutos de recuperación de contraseña de DOC-04 no cambian.
- Una cuenta sin verificar no accede a información fiscal.
- El alta y la confirmación se prueban sin un buzón real.

## Decisiones

- Caducidad del enlace de alta: 30 minutos. Decidido por el responsable el 25 de octubre de 2026.
- Contraseñas comprometidas: Have I Been Pwned, por el rango de la huella. La contraseña completa no sale del servidor. Si la lista no responde, el alta se rechaza. Decidido por el responsable el 25 de octubre de 2026.
- Correo local: Mailpit. La interfaz solo escucha en localhost y el SMTP solo se ve dentro de Compose. No es el correo de producción. Decidido por el responsable el 25 de octubre de 2026.

## Resultado

Comprobado el 25 de octubre de 2026 en `feature/e1-registro`. PHPUnit pasó 19 pruebas y 97 aserciones. El alta web está en `/registro`. Una contraseña de menos de 12 caracteres se rechaza y el correo escrito se conserva. Con una contraseña aceptada, Mailpit v1.32.0 mostró el mensaje en http://127.0.0.1:8025. El enlace confirmó el correo y, al abrirse otra vez, dejó de servir. Hasta confirmar, la cuenta no permite acceso fiscal. Have I Been Pwned se consulta por el rango de la huella; las pruebas no llaman a internet. No hay inicio de sesión. La app Flutter no llama a la API. GitHub Actions todavía no ha ejecutado esta rama. DOC-07 sigue siendo propuesta.
