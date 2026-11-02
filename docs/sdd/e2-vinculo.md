# E2 — Vínculo entre entidad fiscal y asesoría

Traza: ADV-002 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-06](../DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx) propone `advisory_client_link` y una invitación con entidad fiscal. DOC-06 y DOC-07 siguen siendo propuesta.

## Objetivo

El titular de una entidad fiscal y la administradora de una asesoría pueden invitarse. Al aceptar queda un vínculo. Una entidad tiene como mucho una asesoría activa.

## Fuera de alcance

- No repartir permisos por empleado ni por cliente. Es ADV-003.
- No desvincular ni abrir la bandeja de trabajo. Son ADV-004 y ADV-005.
- No crear la API ni hacer que la app Flutter la llame.
- No promocionar `develop` a `main` ni desplegar. E1 y E2 no quedan cerrados.
- No hay ingresos, gastos ni documentos sobre los que operar.

## Criterios de aceptación

- Desde la entidad activa, el titular invita por correo a quien administra una asesoría. Desde la asesoría, la administradora invita por correo al titular de una entidad.
- El enlace caduca a los 7 días y solo se usa una vez. Una invitación nueva deja inservible la anterior del mismo correo y del mismo lado.
- Al aceptar, la entidad y la asesoría quedan vinculadas. Si la entidad ya tiene una asesoría activa, la invitación no se acepta.
- Quien no es titular de esa entidad no invita ni ve el vínculo. Quien no administra esa asesoría no invita ni ve las entidades vinculadas.
- Un formulario caducado no invita ni acepta.

## Decisiones

- El identificador es un UUIDv7 en texto. DOC-06 propone `BINARY(16)` en el esquema completo, que no se adopta aquí.
- La invitación de empleado sigue en su tabla. Esta ficha guarda el vínculo en `advisory_client_link` y la invitación en `advisory_client_invitation`, porque la de empleado no señala una entidad fiscal.
- No hay columna de estado ni fecha de baja. Desvincular es ADV-004. Mientras exista la fila, el vínculo está activo. La unicidad es por entidad fiscal.
- La invitación del titular guarda la entidad y el correo de la administradora. La de la administradora guarda la asesoría y el correo del titular. La otra parte se elige al aceptar, entre las asesorías que administra o las entidades de las que es titular.
- Para aceptar hay que haber entrado con el correo invitado. Quien no tiene asesoría o entidad la crea antes y vuelve a abrir el enlace. El segundo factor sigue siendo opcional para todos, por la decisión del 1 de noviembre de 2026.

## Resultado

Comprobado el 2 de noviembre de 2026 en `feature/e2-vinculo`. PHP 8.4.26. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios en 152 archivos. PHPUnit pasó 54 pruebas y 582 aserciones. La migración `Version20261102004000` se aplicó a la base local: 1 migración y 7 consultas.

En el navegador, el titular crea la entidad. El NIF introducido como `b-18 000018` queda como `B18000018`. Invita por correo a quien va a administrar la asesoría. Mailpit recibe el enlace y la página dice que caduca a los 7 días. Si lo abre otra sesión, responde que es para otro correo y no muestra el nombre. Quien confirma ese correo no queda vinculado: crea la asesoría, vuelve a abrir el enlace, la elige y acepta. La asesoría muestra la entidad y la sesión dice cuál es la asesoría activa. El formulario para invitar otra asesoría desaparece.

Desde la asesoría, la administradora invita al titular de otra entidad. Quien confirma ese correo no queda vinculado. Crea la entidad, vuelve a abrir el enlace, la elige y acepta. La sesión muestra la asesoría activa. Una misma asesoría muestra las dos entidades.

Una segunda asesoría invita al titular de la entidad que ya está vinculada. Al aceptar, la página dice que esa entidad ya tiene una asesoría activa y la sesión sigue mostrando la primera. Una empleada ve la asesoría y no ve el nombre de la entidad ni el formulario para invitar una entidad. Otra cuenta, al abrir la asesoría, recibe «Asesoría no encontrada.». En local esa respuesta es la página de depuración de Symfony y no muestra el nombre de la asesoría ni el de la entidad.

PHPUnit comprueba además que una invitación nueva deja inservible la anterior, que a los 8 días ya no sirve, que una empleada no invita a una entidad y que un formulario caducado no invita ni acepta. El segundo factor sigue siendo opcional para todos, por la decisión del 1 de noviembre de 2026. La app Flutter no se ejecutó y sigue sin llamar a la API. No hay permisos por empleado, desvinculación ni bandeja. La rama no está integrada en `develop`. El responsable aceptó EVI-0013 el 2 de noviembre de 2026. DOC-07 sigue siendo propuesta. E1 y E2 no quedan cerrados.
