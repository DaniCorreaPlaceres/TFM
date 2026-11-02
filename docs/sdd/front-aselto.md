# Front web de Aselto

Traza: [reglas visuales](../reglas-visuales-diseno.md) y el boceto de inicio aceptado en [docs/bocetos/](../bocetos/). No cambia el alcance de DOC-04 ni de DOC-07. DOC-07 sigue siendo propuesta.

## Objetivo

Que la web ya hecha se reconozca como Aselto: misma paleta, una acción principal y el menú del producto. Las pantallas que todavía no existen se nombran y explican, sin cifras ni documentos inventados.

## Fuera de alcance

- No implementar ingresos, gastos, documentos ni los modelos 130, 303 y 420.
- No cambiar el permiso del empleado ni la ficha de la asesoría que está en curso en `feature/e2-permisos`.
- No hacer que la app Flutter llame a la API.
- No promocionar `develop` a `main` ni desplegar.
- No declarar conformidad WCAG. El objetivo AA sigue siendo una meta de diseño.

## Criterios de aceptación

- Quien ha entrado ve el menú: Inicio, Ingresos y gastos, Documentos, Mi asesoría y Ayuda.
- El inicio dice qué falta por hacer. Si no hay entidad, el paso siguiente es crearla. No aparece un importe de ejemplo.
- Ingresos, gastos y documentos dicen que todavía no hay datos.
- Entrar, crear cuenta y recuperar la contraseña se leen como páginas de Aselto, con el nombre y el eslogan.
- Los textos que ya comprueban las pruebas de identidad siguen en la página.

## Decisiones

- El menú usa los nombres de las reglas visuales. En el boceto el móvil dice «Movimientos»; aquí se mantiene «Ingresos y gastos».
- No se muestra «Todo claro» ni un total. Esas cifras del boceto son ficticias y el sistema no las calcula.
- El logo de los documentos pesa más de un megabyte. La web dibuja la marca con el color `#006B70` y el texto «Aselto», sin copiar ese archivo a `public/`.
- La hoja de estilos sigue siendo propia. Bootstrap figura en el stack y no está cargado en esta web; esta tarea no lo incorpora.

## Resultado

Comprobado el 2 de noviembre de 2026 en local. En el navegador, entrar y crear cuenta muestran la marca y el eslogan. Dentro, el menú es Inicio, Ingresos y gastos, Documentos, Mi asesoría y Ayuda. Sin entidad, el paso siguiente es crearla. En 390 px de ancho el menú se desplaza en horizontal y conserva los nombres. PHPUnit pasó `SessionWebTest`, `CloseOtherSessionsWebTest`, `FiscalEntityWebTest` y `SecondFactorWebTest`: 10 pruebas y 211 aserciones.

Al apoyarlo en `develop`, el enlace Ingresos y gastos abre la lista de ingresos que ya existe. Documentos y Ayuda siguen explicando lo que falta. Los gastos siguen fuera. Esta rama es `feature/portada-menu` y no está integrada.
