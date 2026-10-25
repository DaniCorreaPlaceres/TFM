# E0.8 — Almacenamiento local privado

Traza: PLT-006. Plan: [E0.8](../E0_Plan_de_trabajo.md).

## Objetivo

Añadir MinIO al entorno local, compatible con S3, con un bucket privado. Comprobar que una descarga sin credenciales no obtiene el archivo.

## Fuera de alcance

- No guardar documentos de la aplicación ni crear una API de archivos.
- No publicar el puerto de MinIO fuera de localhost.
- No elegir almacenamiento de producción ni desplegar.
- No promocionar `develop` a `main`.
- No dar por cerrada la entrega E0 si siguen pruebas sin ejecutar.

## Criterios de aceptación

- Compose levanta MinIO junto a Nginx, PHP, MySQL y Redis.
- La versión queda fijada en `RELEASE.2025-10-15T17-29-55Z`, compilada desde el código fuente, porque Docker Hub y Quay ya no entregan la imagen comunitaria.
- El bucket `documents` no permite una descarga anónima. Una petición firmada sí lee el objeto.
- `.env.example` documenta el endpoint, el bucket y las credenciales locales, sin secretos de producción.
- La prueba se ejecuta en local y en el job PHPUnit de GitHub Actions.
- El cierre global de E0 deja escritas las comprobaciones que siguen sin ejecutarse.

## Resultado

Comprobado el 25 de octubre de 2026 en `feature/e0-minio`. MinIO informa `RELEASE.2025-10-15T17-29-55Z`, commit `9e49d5e7a648f00e26f2246f4dc28e6b07f8c84a`. PHPUnit, dentro del contenedor PHP 8.4.26, pasó 6 pruebas y 42 aserciones. La prueba del bucket privado subió un objeto, lo leyó con credenciales y recibió 403 sin ellas. Desde el equipo, `GET http://127.0.0.1:9000/documents/e0-private.txt` respondió 403. El puerto 9000 solo escucha en localhost. La consola no está publicada.

El responsable del TFM aceptó EVI-0005 el 25 de octubre de 2026. La entrega E0 no queda cerrada. Siguen sin ejecutarse el dispositivo Android físico, iOS, el escritorio Windows, la llamada de la app a la API y la promoción de `develop` a `main`. No hay despliegue. GitHub Actions todavía no ha construido esta imagen.
