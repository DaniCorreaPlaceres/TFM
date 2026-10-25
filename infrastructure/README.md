# Infraestructura

Docker Compose del entorno local de E0.3. MinIO queda para E0.8.

## Servicios

| Servicio | Imagen comprobada | Puerto local |
|---|---|---|
| nginx | nginx 1.28.3 | http://127.0.0.1:8080 |
| php | PHP 8.4.26 FPM, Symfony 7.4.21 | interno 9000 |
| mysql | MySQL 8.4.9 | solo red de Compose |
| redis | Redis 7.4.11 | solo red de Compose |

Las credenciales de MySQL son locales y solo valen dentro de Compose: base `app`, usuario `app`, contraseña `app`, root `root`. `APP_SECRET` del contenedor es `dev-only-not-for-production`. No reutilizar estos valores fuera del entorno de desarrollo.

## Arranque

Desde este directorio:

```powershell
docker compose up -d --build --wait
```

El primer arranque construye la imagen PHP e instala dependencias si falta `backend/vendor`. Los healthchecks de Nginx, PHP-FPM, MySQL y Redis deben quedar en estado healthy.

## Verificación

```powershell
docker compose ps
curl http://127.0.0.1:8080/nginx-health
curl http://127.0.0.1:8080/api/v1/health
curl http://127.0.0.1:8080/
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

- `GET /nginx-health` responde `200` y el texto `ok`. Comprueba Nginx, no la aplicación.
- `GET /api/v1/health` responde `200` y `{"status":"ok"}`. Es la comprobación de la aplicación.
- `GET /` muestra la página de bienvenida de Symfony 7.4. El esqueleto no tiene ruta de inicio, así que el código HTTP de esa página es `404`.
- La migración `Version20261018030000` crea `e0_example`. Para revertirla: `docker compose exec php php bin/console doctrine:migrations:migrate prev --no-interaction`. Vuelve a aplicarse con el comando `migrate` anterior.

Comprobado el 18 de octubre de 2026: los cuatro healthchecks pasaron, la migración subió, bajó y volvió a subir, y el navegador mostró la bienvenida de Symfony 7.4.

## Parada

```powershell
docker compose stop
```

`docker compose down` elimina los contenedores y conserva el volumen `mysql_data`. `docker compose down -v` borra también ese volumen.
