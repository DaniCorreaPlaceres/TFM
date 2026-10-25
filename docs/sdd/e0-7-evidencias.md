# E0.7 — Revisión de pruebas y evidencias

Traza: transversal a E0, DOC-08 y DOC-09. Plan: [E0.7](../E0_Plan_de_trabajo.md).

## Objetivo

Registrar en `docs/evidence/` los resultados reales de entorno, API, móvil y CI disponibles al cerrar E0.6, y dejar identificadas las pruebas que no se han ejecutado.

## Fuera de alcance

- No reejecutar el entorno, la app ni los workflows para fabricar logs que no se conservaron.
- No reconstruir prompts, tiempos ni número de iteraciones que no quedaron anotados.
- No firmar la revisión humana. La ficha la solicita; la firma corresponde al responsable.
- No añadir MinIO ni cerrar E0.8.
- No promocionar `develop` a `main`.
- No editar los Word DOC-01 a DOC-09.

## Criterios de aceptación

- Existe una ficha `EVI-0001` a `EVI-0004` para el entorno Symfony, el esqueleto Flutter, el contrato de API y la integración continua.
- `docs/evidence/index.csv` enlaza cada ficha con su historia PLT, la herramienta y la pull request.
- Cada ficha separa lo comprobado de lo no ejecutado.
- Los prompts no archivados, los tiempos no observados y las iteraciones no contadas quedan como no registrados.
- La revisión humana del incremento queda solicitada y sin fecha de aprobación.

## Resultado

Registrado el 25 de octubre de 2026 en `feature/e0-evidencias`, a partir de las especificaciones y de las ejecuciones ya observadas. No se generaron logs nuevos.

- [EVI-0001](../evidence/EVI-0001/record.md): entorno Symfony, pull request #4.
- [EVI-0002](../evidence/EVI-0002/record.md): Flutter en emulador, pull request #5. El dispositivo físico, iOS y el escritorio Windows siguen sin ejecutarse.
- [EVI-0003](../evidence/EVI-0003/record.md): contrato mínimo de API, pull request #9.
- [EVI-0004](../evidence/EVI-0004/record.md): workflows, pull request #10. La primera ejecución de backend falló por la ausencia de `.env`; la siguiente quedó en verde.

El responsable del TFM aceptó el incremento el 25 de octubre de 2026.
