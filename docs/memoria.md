# Memoria de trabajo — Aselto

Versión de este relato: 25 de octubre de 2026.

Este documento cuenta lo que está hecho en el TFM hasta esa fecha. Sirve para la entrega y para retomar el trabajo dentro de meses sin reconstruir la historia desde los commits. No sustituye a los Word DOC-01 a DOC-09. Esos archivos siguen siendo la referencia y no se editan desde aquí. El detalle de cada tarea está en [docs/sdd/](sdd/README.md). El plan operativo está en [E0_Plan_de_trabajo.md](E0_Plan_de_trabajo.md).

Una comprobación que no se ha ejecutado sigue sin ejecutarse. Esta memoria no rellena huecos.

## Qué es el producto

Aselto es la aplicación fiscal y documental del TFM. El eslogan es «Tus finanzas bajo control». El dominio previsto de la web es aselto.com. El sitio no está publicado y el dominio no se ha registrado en este trabajo.

El nombre visible de la app es Aselto en Android, iOS y la ventana de Windows. El identificador técnico no cambió: el paquete Dart sigue siendo `app_fiscal` y el identificador `es.tfm.app_fiscal`. El repositorio de Git tampoco se renombró.

El alcance fiscal acordado, cuando llegue, son los modelos 130, 303 y 420. No hay otros modelos. Los cálculos fiscales tienen que ser deterministas y comprobables. La IA puede proponer lecturas de documentos, y una persona tiene que validarlas antes de que cuenten como dato fiscal. En esta fase no hay proveedor de IA elegido ni cálculos fiscales implementados.

## Cómo está organizado

El código vive en un monorepositorio público: [DaniCorreaPlaceres/TFM](https://github.com/DaniCorreaPlaceres/TFM).

- `backend/`: Symfony 7.4, Doctrine y el contrato OpenAPI.
- `mobile/`: Flutter, Riverpod, GoRouter y Dio.
- `infrastructure/`: Docker Compose con Nginx, PHP-FPM, MySQL 8.4 y Redis.
- `docs/`: Word de referencia, especificaciones, diseño y esta memoria.
- `.github/workflows/`: comprobaciones automáticas.

`develop` es la rama de integración. `main` es producción. El trabajo entra por una rama `feature/…`, una pull request hacia `develop` y, más adelante, otra pull request de `develop` hacia `main`. `main` exige la aprobación de otra persona, descarta aprobaciones viejas si hay cambios nuevos y obliga a resolver las conversaciones. Esas reglas valen también para administradores. No hay push directo, push forzado ni borrado de `main`.

A 25 de octubre de 2026, el producto está en `develop`. `main` sigue en el commit inicial del monorepositorio (`c1c0ed8`). Todavía no se ha promocionado nada a producción.

La entrega en curso es E0, los cimientos técnicos de DOC-07 (PLT-001 a PLT-006). El plan la parte en E0.1–E0.8. Esa numeración no cambia los identificadores PLT ni el estado de aprobación de los Word. DOC-04 y DOC-05 son la línea base aprobada. DOC-06, DOC-07 y DOC-08 conservan el estado que indica cada documento.

Cada tarea tiene un Markdown en `docs/sdd/` antes de codificar. Las fichas de E0.1 a E0.4, de las reglas visuales y del nombre se reconstruyeron el 18 de octubre de 2026, después del código, y lo dicen en el propio archivo. A partir de E0.5 el Markdown existió antes de implementar.

La ideación y la documentación de producto se apoyan en ChatGPT Plus. El código se escribe con Cursor Pro. Las fichas de evidencia de ese uso viven en `docs/evidence/`. El 25 de octubre de 2026, E0.7 añadió EVI-0001 a EVI-0004. Los prompts y los tiempos que no se habían anotado no se reconstruyeron. El responsable las aceptó ese mismo día.

## Qué quedó construido

### E0.1 — Instrucciones y carpetas

Pull request #2, integrada en `develop` el 18 de octubre de 2026.

Quedaron las instrucciones `AGENTS.md` de la raíz, del backend, de la app y de la documentación, el README general y el índice de los nueve Word. También la carpeta `docs/evidence/`, vacía de resultados.

### E0.2 — Git y protección de main

Pull request #1, el 18 de octubre de 2026.

El repositorio público quedó publicado con `main` y `develop`. La protección de `main` descrita arriba se comprobó en GitHub. En ese momento todavía no había pipelines. Las comprobaciones obligatorias llegaron en E0.6.

### E0.3 — Entorno Symfony

Pull request #4, el 18 de octubre de 2026. La pull request #3 apuntaba a `main` y se cerró sin integrar. El entorno entró por la #4, hacia `develop`.

Compose levanta Nginx, PHP 8.4 en PHP-FPM, MySQL 8.4 y Redis. El PHP del ordenador no es el runtime del proyecto. El 18 de octubre quedaron healthy PHP 8.4.26, Symfony 7.4.21, MySQL 8.4.9, Redis 7.4.11 y Nginx 1.28.3. La migración de ejemplo `Version20261018030000` subió, bajó y volvió a subir. `GET /nginx-health` respondió 200. `GET /` muestra la bienvenida de Symfony y responde 404 porque no hay página de inicio. MySQL solo se ve desde dentro de Compose. La base local se llama `app`, con usuario y contraseña `app`. Son datos de desarrollo, no de producción.

El esquema fiscal de DOC-06 no está creado. MinIO tampoco.

Arranque, desde la raíz:

```powershell
docker compose -f infrastructure/compose.yaml up -d --build --wait
```

La aplicación local queda en http://127.0.0.1:8080.

### E0.4 — App Flutter

Pull request #5, el 18 de octubre de 2026.

El esqueleto usa Flutter, Riverpod, GoRouter y Dio. Hay una sesión provisional en memoria: sin sesión se ve esa pantalla; Entrar abre Inicio; Salir vuelve a la sesión. Dio está preparado y no llama a la API.

Comprobado el 18 de octubre en el emulador `tfm_api_36`: se vio la sesión y, tras Entrar, el inicio. `flutter analyze` no dejó incidencias y `flutter test` pasó 2 pruebas. Versiones de esa comprobación: Flutter 3.47.7, Dart 3.13.5, Android Studio 2026.2.1.8, OpenJDK 21.0.12.1, SDK 36, AGP 9.1.0 y Gradle 9.3.1. El SDK de Flutter en este equipo está en `C:\Users\dierr\sdk\flutter`.

No se ejecutó en un móvil Android físico. `adb devices` solo vio el emulador.

El proyecto también tiene los runners de iOS y de escritorio Windows, porque más adelante puede hacer falta abrirlos. iOS no se ha compilado: hace falta un Mac con Xcode. El escritorio Windows no se ha lanzado. Para quitar el aviso de `flutter doctor` se instalaron Visual Studio Build Tools 2026 18.11.0 en `D:\Microsoft\VisualStudio\18\BuildTools`, con el Windows 10 SDK 10.0.26100.0. Después de eso, `flutter doctor` no dejó incidencias. Eso no equivale a haber ejecutado la app de escritorio.

### Identidad y diseño

Pull request #6, reglas visuales y bocetos. Pull request #7, nombre, eslogan y dominio. Las dos, el 18 de octubre de 2026.

Quedaron `docs/reglas-visuales-diseno.md` (versión 0.1), el logo con eslogan, el icono y los bocetos de inicio y de documentos. Los bocetos orientan; no son una especificación al píxel ni una prueba de accesibilidad. El objetivo AA está escrito como meta de diseño, no como conformidad declarada.

### E0.5 — Contrato mínimo de la API

Pull request #9, integrada el 25 de octubre de 2026. La comprobación local está fechada el 18 de octubre en `feature/e0-contrato-api`.

`GET /api/v1/health` responde 200 y `{"status":"ok"}`. Cada petición bajo `/api/` lleva un `X-Request-Id` (UUID) en la cabecera y una línea de log. Si la ruta o el método no existen, la API responde `application/problem+json` con `type`, `title`, `status` y `requestId`, sin trazas ni mensajes internos. `GET /` sigue siendo el HTML de Symfony. `GET /nginx-health` sigue comprobando solo Nginx.

El contrato está en `backend/openapi/openapi.yaml` (OpenAPI 3.0.3) y describe únicamente esa ruta de salud. PHPUnit pasó 5 pruebas y 30 aserciones. La app no llama a la API.

### E0.6 — Comprobaciones automáticas

Pull request #10, el 25 de octubre de 2026. La nota de la protección entró en la pull request #11, el mismo día.

Hay dos workflows. Se disparan en pull requests y en pushes hacia `develop` y hacia `main`.

- `backend.yml`, en PHP 8.4: PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI.
- `flutter.yml`, en Flutter 3.47.7: `flutter analyze` y `flutter test`.

No hay despliegue. No existe `deploy.yml`.

En local, dentro del contenedor PHP 8.4.26, pasaron PHP-CS-Fixer 3.95.27, PHPStan 2.3.2 en nivel 6, la validación del OpenAPI y PHPUnit 13.4.3 (5 pruebas, 30 aserciones). En el equipo, Flutter 3.47.7 pasó el análisis y las 2 pruebas.

La primera ejecución en GitHub, en la pull request #10, dejó Flutter en verde y los cuatro jobs del backend en rojo. `composer install` vacía la caché de Symfony y esa operación lee `backend/.env`. Ese archivo no está en Git, a propósito, porque mezcla configuración local. En el ordenador existe; en GitHub no. El workflow ahora copia `.env.example` a `.env` antes de instalar, con un `APP_SECRET` falso que solo vale para esa ejecución. El commit `4904366` dejó las cinco comprobaciones en verde.

Desde el 25 de octubre, `main` exige esos cinco nombres: PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter. La pull request no tiene que estar recién actualizada con `main` para que cuenten. El resto de la protección no cambió. Ninguna pull request hacia `main` ha ejecutado todavía estos workflows, porque el código sigue en `develop`.

Para poder subir la carpeta `.github/workflows/` hizo falta autorizar de nuevo la credencial de GitHub con el permiso `workflow`. Sin ese permiso, GitHub rechaza el push.

## Para qué sirve cada comprobación

PHP-CS-Fixer mira que el PHP esté escrito con el mismo estilo. No decide si la lógica es correcta.

PHPStan lee el PHP sin ejecutarlo y señala fallos de tipos, funciones que no existen y usos incoherentes.

PHPUnit ejecuta el backend. Hoy cubre la ruta de salud, el formato de error `problem+json` y que el contrato mencione esa ruta, la cabecera y el esquema de error.

OpenAPI comprueba que `openapi.yaml` sea un contrato válido.

Flutter analiza la app y ejecuta dos pruebas: el cliente HTTP queda preparado y no llama a la API, y la sesión provisional sigue protegiendo el inicio.

## Qué no está hecho

- E0.8: MinIO, buckets privados y el cierre de secretos.
- Promocionar `develop` a `main`.
- Ejecutar la app en un Android físico, en iOS y en el escritorio Windows.
- Conectar la app con `GET /api/v1/health`.
- Autenticación real, documentos, entidades fiscales y los modelos 130, 303 y 420.
- Elegir proveedor de IA.
- Desplegar. aselto.com no está publicado.
- Registrar el dominio.

## Cómo retomar

1. Partir de `develop` y crear `feature/<tarea>`.
2. Escribir antes `docs/sdd/<tarea>.md` con objetivo, fuera de alcance, criterios y traza.
3. Implementar y comprobar. Lo no ejecutado se deja escrito como no ejecutado.
4. Abrir la pull request hacia `develop`.
5. E0.8 no empieza sola: hace falta pedirla.

El siguiente paso del plan, cuando se autorice, es E0.8.
