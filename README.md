# Aselto

Tus finanzas bajo control.

Monorepositorio del TFM. Aselto es la aplicación fiscal y documental. El dominio de la web será aselto.com. Documentación y decisiones: [docs/README.md](docs/README.md). Instrucciones para agentes: [AGENTS.md](AGENTS.md).

## Estructura

- backend/: esqueleto Symfony 7.4 con Doctrine. El runtime es PHP 8.4 en PHP-FPM.
- mobile/: esqueleto Flutter con Riverpod, GoRouter y Dio. Android está comprobado; iOS y el escritorio Windows están preparados. Ver [mobile/README.md](mobile/README.md).
- infrastructure/: Compose local con Nginx, PHP-FPM, MySQL 8.4, Redis y MinIO. Ver [infrastructure/README.md](infrastructure/README.md).
- docs/: documentos Word de referencia, especificaciones por tarea en docs/sdd/ y registro de evidencias. Ver [docs/README.md](docs/README.md).
- .github/: workflows de comprobación de E0.6. Ver [.github/README.md](.github/README.md).

El detalle de versiones, healthchecks y la migración de ejemplo está en [infrastructure/README.md](infrastructure/README.md). El arranque de Flutter está en [mobile/README.md](mobile/README.md).

## Entorno local

Requisito: Docker con Compose. El runtime es el contenedor PHP 8.4; no hace falta instalar PHP ni Composer en el equipo.

Desde la raíz del repositorio:

```powershell
docker compose -f infrastructure/compose.yaml up -d --build --wait
```

El primer arranque construye la imagen PHP e instala las dependencias de `backend/` si falta `vendor`. Cuando los healthchecks terminan, la aplicación queda en http://127.0.0.1:8080. Esa página es la bienvenida de Symfony 7.4 y responde HTTP 404 porque el esqueleto aún no tiene ruta de inicio. `GET /api/v1/health` responde 200 `{"status":"ok"}`. `GET /nginx-health` solo comprueba Nginx.

Aplicar la migración de ejemplo:

```powershell
docker compose -f infrastructure/compose.yaml exec php php bin/console doctrine:migrations:migrate --no-interaction
```

MySQL solo es accesible dentro de Compose. Base `app`, usuario `app`, contraseña `app`. Son credenciales locales de desarrollo.

Parar los contenedores:

```powershell
docker compose -f infrastructure/compose.yaml stop
```

`docker compose -f infrastructure/compose.yaml down` elimina los contenedores y conserva los datos de MySQL. Añadir `-v` borra también ese volumen.

## Aplicación Android

Desde `mobile/`, con Flutter y el Android SDK en el `PATH`:

```powershell
flutter pub get
flutter analyze
flutter test
flutter run
```

La pantalla inicial es una sesión provisional. La guía de versiones y del emulador `tfm_api_36` está en [mobile/README.md](mobile/README.md).

## Entrega en curso

E0 — cimientos técnicos, según DOC-07 (PLT-001 a PLT-006). El detalle operativo está en [AGENTS.md](AGENTS.md).

- E0.1: alinear instrucciones y estructura del monorepositorio.
- E0.2: rama de trabajo y estado de la protección. El estado conocido de main está en Flujo Git.
- E0.3: entorno Symfony reproducible. El arranque está en Entorno local.
- E0.4: esqueleto Flutter. El arranque Android está en Aplicación Android. El dispositivo físico, iOS y el escritorio Windows siguen sin ejecutarse.
- E0.5: contrato mínimo `GET /api/v1/health`. La app Flutter no llama a la API.
- E0.6: workflows de comprobación. Pasaron en local y en la pull request #10 el 25 de octubre de 2026. `main` exige PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter.
- E0.7: fichas EVI-0001 a EVI-0004, aceptadas por el responsable el 25 de octubre de 2026.
- E0.8: MinIO local con el bucket `documents` privado. Comprobado el 25 de octubre de 2026, aceptado en EVI-0005 e integrado con la pull request #14.
- E1: alta web con confirmación de correo, integrada con la pull request #15. El inicio de sesión web y la recuperación están comprobados en local el 25 de octubre de 2026 y todavía no están integrados. La app Flutter no llama a la API.

## Flujo Git

- main: línea de producción. No realizar commits de trabajo directamente; recibir cambios mediante PR revisada desde develop.
- develop: integración. Crear ramas feature/<descripcion> desde develop e integrarlas mediante PR.
- Revisar los cambios y ejecutar las comprobaciones correspondientes antes de integrar.
- No guardar secretos, credenciales, firmas Android ni datos fiscales personales reales.
- Conservar los archivos de bloqueo de dependencias cuando se inicialicen los proyectos.

Repositorio público: [DaniCorreaPlaceres/TFM](https://github.com/DaniCorreaPlaceres/TFM). Las ramas main y develop están publicadas. main tiene protección activa: exige PR con una aprobación de otra persona, descarta aprobaciones tras nuevos cambios y requiere resolver las conversaciones. Las reglas se aplican también a administradores; no se permiten pushes directos, pushes forzados ni borrados. Los workflows de comprobación están en `.github/workflows/` y se ejecutaron en la pull request #10. `main` exige PHP-CS-Fixer, PHPStan, PHPUnit, OpenAPI y Flutter. No hay despliegue de producción configurado.

## Arranque del trabajo

Trabajar desde develop y crear una rama feature/<descripcion> para cada tarea. Consultar el AGENTS.md del componente antes de realizar cambios. El entorno Symfony se levanta con la sección Entorno local y Flutter con Aplicación Android.

## Seguimiento de E0

Consultar [Plan operativo E0.1–E0.8](docs/E0_Plan_de_trabajo.md) para las subdivisiones, su correspondencia con PLT-001 a PLT-006 y las comprobaciones pendientes.
