# E2 — Permiso del empleado para ver entidades

Traza: ADV-003 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-06](../DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx) propone permisos por empleado y un alcance de clientes total por defecto. DOC-06 y DOC-07 siguen siendo propuesta.

## Objetivo

La administradora concede o retira a un empleado la función de ver los nombres de las entidades vinculadas. Sin esa concesión, el empleado no las ve. El servidor decide el permiso efectivo.

## Fuera de alcance

- No desvincular ni abrir la bandeja de trabajo. Son ADV-004 y ADV-005.
- No crear la API ni hacer que la app Flutter la llame.
- No promocionar `develop` a `main` ni desplegar. E1 y E2 no quedan cerrados.
- No hay ingresos, gastos ni documentos sobre los que operar.
- No adoptar el catálogo de permisos de DOC-06 ni su alcance de clientes total por defecto. Esos recursos todavía no existen.

## Criterios de aceptación

- La administradora concede o retira, para cada empleado, ver los nombres de las entidades vinculadas.
- Sin esa concesión, el empleado no ve esos nombres y no puede invitar entidades.
- Con la concesión, ve los nombres. Invitar entidades sigue reservado a la administradora.
- Un empleado no concede ni retira permisos. Un formulario caducado no los cambia.
- Quien no pertenece a la asesoría no ve los nombres.

## Decisiones

- El identificador es un UUIDv7 en texto. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.
- La concesión se guarda en `advisory_employee_permission`, con el código `ver_entidades`. Si no hay fila, el empleado no tiene la función. DOC-06 propone un alcance de clientes total por defecto; esta ficha no lo adopta, porque ADV-003 pide una concesión explícita.
- La administradora ve las entidades por su papel y no necesita esa fila. Un empleado no invita entidades ni empleados, aunque pueda ver los nombres.
- El segundo factor sigue siendo opcional para todos, por la decisión del 1 de noviembre de 2026.

## Resultado

Comprobado el 2 de noviembre de 2026 en `feature/e2-permisos`. PHP 8.4.26. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios en 163 archivos. PHPUnit pasó 60 pruebas y 718 aserciones. Antes de las pruebas de la presentación web que comparte este árbol, la entrega del permiso había pasado con 56 pruebas y 648 aserciones. La migración `Version20261102015000` está aplicada en la base local. Al aplicarla fueron 1 migración y 2 consultas.

En el navegador, quien administra Norte Nav Asesores concede ver las entidades. La página dice que el permiso ha quedado guardado y la empleada figura con «ve las entidades». La empleada entra y ve Taller Norte Nav y Taller Sur Nav, sin el formulario para invitar una entidad ni el de conceder el permiso. Quien administra retira la concesión. La empleada vuelve a entrar y la página ya no muestra esos nombres.

La primera respuesta de `?permiso=1`, justo después de conceder, fue un error 500 de Twig: el bloque `body` figuraba como definido dos veces en `base.html.twig`. La plantilla de la asesoría tiene un solo bloque `body`. Se borró `var/cache/dev` y la misma dirección mostró el permiso guardado. PHPUnit cubre el formulario caducado y la cuenta que no pertenece; el navegador no repitió esos dos casos.

El segundo factor sigue siendo opcional para todos, por la decisión del 1 de noviembre de 2026. La app Flutter no se ejecutó y sigue sin llamar a la API. La desvinculación y la bandeja quedan fuera. El mismo árbol contiene la presentación de `docs/sdd/front-aselto.md`; esta ficha no la cierra. La prueba de alta de asesoría abre «Mi asesoría», que es el texto actual del menú. La rama no está integrada en `develop`. El responsable aceptó EVI-0014 el 2 de noviembre de 2026. DOC-07 sigue siendo propuesta. E1 y E2 no quedan cerrados.
