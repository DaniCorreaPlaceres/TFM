# E1 — Aislamiento de la entidad fiscal

Traza: SEC-001 de [DOC-07](../DOC-07_Planificacion_Backlog_y_Criterios_de_Aceptacion_v1.1.docx). [DOC-04](../DOC-04_Especificacion_Funcional_Completa_v1.1.docx) exige un contexto de entidad válido en el servidor. DOC-07 sigue siendo propuesta: esta ficha no cambia ese estado.

## Objetivo

Demostrar, con las rutas web que ya existen, que una cuenta no lee ni cambia la entidad fiscal de otra.

## Fuera de alcance

- No ejecutar la exportación. No existe una ruta de exportación, así que esa parte del criterio de DOC-07 permanece no ejecutada.
- No implementar gastos, ingresos, documentos ni la API que consumiría la app. La app Flutter sigue sin llamar a la API.
- No implementar el perfil fiscal. Es TAX-001.
- No implementar asesorías, invitaciones ni roles distintos del titular.
- No promocionar `develop` a `main` ni desplegar. E1 no queda cerrado.

## Criterios de aceptación

- `/entidades` y `/sesion` de una cuenta no muestran el nombre ni el NIF de una entidad de otra cuenta.
- Elegir el identificador de una entidad ajena responde 404, no cambia la entidad activa de quien lo intenta y no modifica el nombre ni el NIF guardados.
- Si la sesión contiene el identificador de una entidad ajena, `/sesion` no muestra esa entidad.
- La exportación no se ejecuta.

## Decisiones

- El recurso que se puede leer o cambiar hoy es la propia entidad fiscal. Los libros y los documentos llegarán en entregas posteriores y tendrán que repetir esta comprobación.
- La respuesta 404 dice que la entidad no existe. No incluye el nombre ni el NIF de la otra cuenta.

## Resultado

Comprobado el 1 de noviembre de 2026 en `feature/e1-aislamiento`. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios. PHPUnit pasó 46 pruebas y 380 aserciones. La prueba web crea una entidad con una cuenta y, con otra, comprueba que `/sesion` y `/entidades` no muestran su nombre ni su NIF. Elegir ese identificador responde 404, no cambia la entidad activa y deja el nombre y el NIF guardados como estaban. Si la sesión de la segunda cuenta contiene ese identificador, `/sesion` no muestra la entidad ajena. La exportación no se ejecutó: no hay ruta de exportación. La app Flutter no se ejecutó en local y sigue sin llamar a la API. No hay perfil fiscal ni libros. La rama no está integrada en `develop`. El responsable aceptó EVI-0010 ese mismo día. DOC-07 sigue siendo propuesta. E1 no queda cerrado.
