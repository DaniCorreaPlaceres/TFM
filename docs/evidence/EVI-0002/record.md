# EVI-0002 — Esqueleto Flutter

| Campo | Información |
| --- | --- |
| ID de evidencia | EVI-0002 |
| Fecha | 2026-10-18 |
| Entrega / historia / requisito | E0.4 / PLT-003 / N/A |
| Objetivo | Dejar el esqueleto Flutter con Riverpod, GoRouter y Dio, y una sesión provisional que proteja el inicio. |
| Herramienta | Cursor Pro, para el código y las pruebas. La ejecución en el emulador usó Android Studio y el SDK. Esos programas no cuentan por sí solos como intervención de IA. |
| Contexto aportado | AGENTS.md del móvil y el plan E0. Sin datos fiscales. |
| Prompt(s) | No archivados. Ver [prompt-redacted.md](prompt-redacted.md). |
| Respuesta utilizada | El proyecto `mobile/` integrado en `develop`, con los runners `ios/` y `windows/` preparados. |
| Decisión del desarrollador | Aceptada al integrar la pull request #5. Se mantuvo el escritorio Windows y se preparó iOS, sin darlos por ejecutados. El paquete sigue siendo `app_fiscal`. |
| Archivos | `mobile/`. |
| Pruebas ejecutadas | Ver [test-results.txt](test-results.txt). |
| Intervención humana | Integración de la pull request y la decisión de no desactivar el escritorio Windows. El responsable del TFM aceptó esta ficha el 25 de octubre de 2026. |
| Tiempo observado | No registrado. |
| Iteraciones de IA | No registradas. |
| Defectos | `flutter doctor` avisaba de la falta de Visual Studio para el escritorio Windows. Se instaló Visual Studio Build Tools 2026 18.11.0 en `D:\Microsoft\VisualStudio\18\BuildTools`, con Windows 10 SDK 10.0.26100.0. Después, `flutter doctor` no dejó incidencias. La app de escritorio no se lanzó. |
| Commit / PR / CI | Commit `e08e612`. Pull request #5, merge `7a47b58`. El workflow Flutter llega en EVI-0004. |
| Conclusión | El emulador mostró la sesión provisional y, tras Entrar, el inicio. El dispositivo físico, iOS y el escritorio Windows no se ejecutaron. Dio no llama a la API. |
| Responsable de aprobación | Responsable del TFM, 2026-10-25. |

Enlaces: [links.md](links.md).
