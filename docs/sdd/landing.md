# Portada pública de Aselto

Traza: [documento introductorio](../Aselto_Documento_introductorio) y [reglas visuales](../reglas-visuales-diseno.md). [E0.5](e0-5-contrato-api.md) dejó `GET /` como HTML de Symfony para que no pasara a `problem+json`. Esta tarea sustituye esa bienvenida y mantiene el HTML. No cambia DOC-04 ni DOC-07. DOC-07 sigue siendo propuesta.

## Objetivo

Que `GET /` sea la página pública de Aselto y presente el producto a quien trabaja por cuenta propia y a quien lleva una asesoría.

## Fuera de alcance

- No implementar ingresos, gastos, documentos ni los modelos 130, 303 y 420.
- No conectar la aplicación Flutter con la API.
- No poner precios, testimonios, clientes ni importes de ejemplo.
- No prometer emisión de facturas, presentación de impuestos ni cambio de asesoría. La desvinculación sigue fuera.
- No registrar el dominio ni desplegar. aselto.com no está publicado.
- No declarar conformidad WCAG. El objetivo AA sigue siendo una meta de diseño.
- No promocionar `develop` a `main`.

## Criterios de aceptación

- `GET /` responde 200 con `text/html` y no usa `application/problem+json`.
- Sin sesión se ven el nombre, el eslogan, una parte para autónomos, otra para asesorías, y las acciones Crear cuenta y Entrar. Crear cuenta es la acción principal.
- Con sesión, la misma página ofrece ir a `/sesion`. El inicio de quien ha entrado sigue siendo `/sesion`, con su menú.
- No aparecen importes de ejemplo, «Todo claro» ni «Todo al día».
- Los modelos 130, 303 y 420 se nombran como el alcance previsto. La lectura de un documento no cuenta hasta que una persona la confirma.
- La página separa lo que ya se puede hacer de lo que llega después: ingresos, gastos, documentos, modelos y la aplicación del móvil.
- Entrar, crear cuenta y recuperar la contraseña conservan sus textos. Su marca vuelve a `/`.

## Decisiones

- El relato sale del documento introductorio: un espacio compartido para autónomos y asesorías, con documentos e ingresos en la misma actividad y con revisión humana de lo que proponga la lectura automática.
- Lo que las pantallas de producto dicen que todavía no existe se cuenta como entrega posterior, no como función abierta.
- El logo de los documentos pesa más de un megabyte. La portada dibuja la marca con `#006B70` y el texto «Aselto», igual que el resto de la web.
- La hoja de estilos sigue siendo propia. Bootstrap figura en el stack y no está cargado; esta tarea no lo incorpora.

## Resultado

Comprobado el 2 de noviembre de 2026 en `feature/portada-menu`. PHP 8.4.26. PHPStan no informó errores. PHP-CS-Fixer no propuso cambios en 223 archivos. PHPUnit pasó 65 pruebas y 913 aserciones. La rama no está integrada.

PHPUnit pasó `tests/Web/LandingWebTest.php` y `tests/Api/HealthTest.php`: 4 pruebas y 51 aserciones. También pasaron sesión, alta y asesoría: 11 pruebas y 196 aserciones. PHPStan no informó errores en `LandingController`. PHP-CS-Fixer no propuso cambios en esos tres archivos PHP. La suite completa no se ejecutó.

En el navegador, con sesión abierta, `/` muestra la portada y «Ir a mi espacio» abre `/sesion`. Sin sesión, la misma página ofrece «Crear cuenta» y «Entrar». A 320 px de ancho no hay desplazamiento horizontal. La app Flutter no se ejecutó.
