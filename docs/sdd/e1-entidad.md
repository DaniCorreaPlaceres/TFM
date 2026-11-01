# E1 — Crear y seleccionar la entidad fiscal

Traza: ORG-001 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) define la entidad fiscal como titular de la información, distinta de la cuenta, y exige un contexto de entidad válido en el servidor. [DOC-06](../DOC-06_Modelo_de_Datos_y_Contratos_OpenAPI_v1.1.docx) propone `fiscal_entity` y `fiscal_entity_membership`. DOC-06 y DOC-07 siguen siendo propuesta: esta ficha no cambia ese estado.

## Objetivo

Permitir que la cuenta web que ya entra cree una o varias entidades fiscales y elija cuál está activa en la sesión.

## Fuera de alcance

- No implementar el perfil fiscal de territorio, actividades y regímenes. Es TAX-001.
- No implementar asesorías, invitaciones ni roles distintos del titular.
- No implementar gastos, ingresos, documentos ni la API que consumiría la app. La app Flutter sigue sin llamar a la API.
- No ejecutar el aislamiento completo de lectura, cambio y exportación. Es SEC-001. Esta ficha solo impide elegir una entidad de la que la cuenta no es titular.
- No promocionar `develop` a `main` ni desplegar.

## Criterios de aceptación

- Una cuenta confirmada puede crear más de una entidad fiscal.
- La entidad creada queda vinculada solo a esa cuenta, como titular.
- La sesión guarda una entidad activa. Si la cuenta tiene exactamente una, queda elegida. Si tiene varias, elige una.
- No se puede dejar como activa una entidad de otra cuenta.
- Sin entidad activa, la sesión no muestra datos de una entidad.

## Decisiones

- La entidad guarda el nombre y el NIF. El perfil de territorio y régimen queda fuera.
- El NIF se guarda en mayúsculas, sin espacios. Esta ficha no aplica el dígito de control: DOC-07 no lo fija para ORG-001.
- El identificador es un UUIDv7 en texto, igual que la cuenta. DOC-06 propone `BINARY(16)` dentro del esquema completo, que sigue siendo propuesta y no se adopta aquí.
- La entidad activa vive en la sesión web, no en una columna de la cuenta.
- La pertenencia solo admite el papel de titular. Los demás papeles esperan a la historia de roles.

## Resultado

Comprobado el 1 de noviembre de 2026 en `feature/e1-entidad`. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios. PHPUnit pasó 45 pruebas y 348 aserciones. La migración `Version20261101210000` se aplicó a la base local. En el navegador, una cuenta confirmada entra en `/sesion` y ve que todavía no ha elegido entidad; la página sigue diciendo que la información fiscal no está disponible. Un nombre en blanco no crea la entidad. Crear una entidad la deja activa y guarda el NIF en mayúsculas, sin espacios ni guiones. Una segunda entidad pasa a ser la activa, y elegir la primera la vuelve a dejar activa. Sin sesión, `/entidades` vuelve a `/entrar`. PHPUnit comprueba que otra cuenta no puede elegir la entidad y que un identificador desconocido responde 404. La app Flutter no se ejecutó en local: esta entrega no la modifica y sigue sin llamar a la API. No hay perfil fiscal, libros ni exportación. La rama no está integrada en `develop`. El responsable aceptó EVI-0009 ese mismo día. DOC-07 sigue siendo propuesta.
