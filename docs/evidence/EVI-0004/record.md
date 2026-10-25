# EVI-0004 — Integración continua

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0004 |
| Fecha | 2026-10-25 |
| Entrega / historia / requisito | E0.6 / PLT-004 y PLT-005 / N/A |
| Objetivo | Comprobar backend y Flutter en pull requests y pushes hacia `develop` y `main`. |
| Herramienta | Cursor Pro, para los workflows y la corrección. GitHub Actions ejecutó las comprobaciones. |
| Contexto aportado | La especificación E0.6 y el código ya integrado de E0.3 a E0.5. Sin secretos de producción. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | `.github/workflows/backend.yml`, `.github/workflows/flutter.yml` y la copia de `.env.example` antes de `composer install`. |
| Decisión del desarrollador | Aceptada al integrar la pull request #10. El mismo día se exigieron en `main` los cinco jobs, con los nombres de la ejecución verde. |
| Archivos | `.github/workflows/`, `.gitattributes`, `backend/.php-cs-fixer.dist.php`, `backend/phpstan.dist.neon` y `backend/bin/validate-openapi.php`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | Autorizar la credencial de GitHub con el ámbito `workflow`, integrar la pull request #10 y pedir las comprobaciones obligatorias de `main`. El responsable del TFM aceptó esta ficha el 25 de octubre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | El primer push de los workflows fue rechazado: la credencial no tenía el ámbito `workflow`. La primera ejecución de backend falló porque `composer install` no encontraba `backend/.env`. Se corrigió en `4904366`. |
| Commit / PR / CI | Pull request #10. Ejecución verde de backend: run 40205915132. Flutter: run 40205915073. Commit de la corrección: `4904366`. Merge `da6f8cd`. |
| Conclusión | Las cinco comprobaciones pasaron en GitHub en la pull request #10. No hay despliegue. Ninguna pull request hacia `main` ha ejecutado todavía estos workflows. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
