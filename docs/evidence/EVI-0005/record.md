# EVI-0005 — MinIO privado

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0005 |
| Fecha | 2026-10-25 |
| Entrega / historia / requisito | E0.8 / PLT-006 / N/A |
| Objetivo | Añadir MinIO local, compatible con S3, y comprobar que un objeto no se descarga sin credenciales. |
| Herramienta | Cursor Pro, para la implementación y la prueba. |
| Contexto aportado | La especificación E0.8 y el Compose de E0.3. Sin datos fiscales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El servicio MinIO de Compose, la imagen compilada desde `RELEASE.2025-10-15T17-29-55Z` y la prueba `PrivateBucketTest`. |
| Decisión del desarrollador | Integrada en `develop` con la pull request #14. La aplicación todavía no guarda documentos. |
| Archivos | `infrastructure/docker/minio/Dockerfile`, `infrastructure/compose.yaml`, `backend/.env.example` y `backend/tests/Storage/`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | El responsable del TFM aceptó esta ficha el 25 de octubre de 2026 e integró la pull request #14 el mismo día. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | Docker Hub y Quay ya no entregan la imagen comunitaria `minio/minio`. El binario se compiló con `go install` de la etiqueta `RELEASE.2025-10-15T17-29-55Z`. |
| Commit / PR / CI | Pull request #14, commit `339d100`, merge `7ea57d3`. Backend run 40212630147: PHP-CS-Fixer, PHPStan, PHPUnit y OpenAPI en verde. Flutter run 40212630177 en verde. |
| Conclusión | En local y en el job PHPUnit de la pull request #14, el objeto de prueba se lee con credenciales y devuelve 403 sin ellas. El puerto 9000 solo escucha en localhost. E0 no queda cerrado. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
