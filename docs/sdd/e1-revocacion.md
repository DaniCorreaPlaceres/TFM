# E1 — Cerrar las demás sesiones web

Traza: IDN-004 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). En [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) la revocación es IDN-006. DOC-07 sigue siendo propuesta: esta ficha no cambia ese estado.

## Objetivo

Desde la sesión web abierta, cerrar las demás sesiones web de la misma cuenta. Esas cookies dejan de autorizar. La sesión desde la que se cierra sigue abierta.

## Fuera de alcance

- No invalidar un refresh ni detectar su reutilización. No hay refresh: la app Flutter sigue sin llamar a la API.
- No aplicar un cambio de permisos. No hay roles distintos del titular.
- No listar las sesiones abiertas ni elegir una concreta.
- No promocionar `develop` a `main` ni desplegar. E1 no queda cerrado.

## Criterios de aceptación

- Con la sesión abierta, cerrar las demás deja esa sesión en `/sesion`.
- Otra sesión web de la misma cuenta, al pedir `/sesion`, vuelve a `/entrar`.
- Sin sesión, la operación no se ejecuta.
- Un formulario caducado no cierra las demás sesiones.

## Decisiones

- No hay un listado de sesiones. La operación avanza el marcador que ya usa el cambio de contraseña y escribe el marcador nuevo solo en la sesión actual.
- El refresh, la reutilización y el cambio de permisos quedan sin ejecutar.

## Resultado

Comprobado el 1 de noviembre de 2026 en `feature/e1-revocacion`. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios. PHPUnit pasó 48 pruebas y 402 aserciones. En el navegador, la sesión abierta muestra «Cerrar las demás sesiones». Al usarlo, sigue en `/sesion` y dice que las demás sesiones se han cerrado. Al recargar, la sesión sigue abierta. PHPUnit comprueba además que otra cookie de la misma cuenta vuelve a `/entrar` y que un formulario caducado no la cierra. La invalidación del refresh, la reutilización y el cambio de permisos no se ejecutaron. La app Flutter no se ejecutó en local y sigue sin llamar a la API. La rama no está integrada en `develop`. El responsable aceptó EVI-0011 ese mismo día. DOC-07 sigue siendo propuesta. E1 no queda cerrado.
