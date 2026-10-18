# Infraestructura

Directorio reservado para Docker Compose, proxy, despliegue y observabilidad, según DOC-05.

E0.1 solo crea este directorio. Todavía no hay archivos de Compose ni de entorno.

- E0.3 añadirá Nginx, PHP-FPM 8.4, MySQL 8.4 y Redis, con healthchecks.
- E0.8 añadirá almacenamiento compatible con S3 mediante MinIO, privado en local.

Los secretos permanecen fuera de Git. La plantilla versionable será `.env.example`.
