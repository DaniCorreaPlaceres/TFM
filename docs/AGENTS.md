# Instrucciones de documentación

Aplicar también [las instrucciones raíz](../AGENTS.md). Ámbito: docs/.

- DOC-01 a DOC-09 en formato Word son documentos de referencia: no modificar, renombrar, mover ni eliminar sin una petición expresa.
- Mantener [README.md](README.md) como índice Markdown con los nombres reales y rutas relativas a los documentos.
- El nombre de la aplicación es Aselto, el eslogan es «Tus finanzas bajo control» y el dominio de la web será aselto.com. Esa identidad vive en el índice y en [reglas-visuales-diseno.md](reglas-visuales-diseno.md). No renombrar los Word por este cambio.
- Conservar trazabilidad entre decisiones, especificaciones, contratos, backlog, criterios de aceptación, pruebas y evidencias de IA.
- Cada tarea tiene un Markdown en [sdd/](sdd/README.md) antes de implementarla. Ese archivo no sustituye a los Word. Las fichas de lo ejecutado siguen en [evidence/](evidence/README.md).
- DOC-04 y DOC-05 son línea base aprobada. DOC-06, DOC-07 y DOC-08 siguen identificados en su ficha como propuesta o diseño pendiente de implementación. No elevar ese estado desde el índice Markdown.
- No presentar propuestas como decisiones aprobadas ni pruebas no ejecutadas como superadas; señalar discrepancias para su resolución.
- Mantener el stack, las ramas main/develop, Android primero y el alcance fiscal acordado de los modelos 130, 303 y 420.
- Documentar el aislamiento estricto de entidad fiscal y la validación humana obligatoria de resultados de IA documental.
- [evidence/README.md](evidence/README.md) y [evidence/index.csv](evidence/index.csv) siguen el registro de DOC-09. E0.7 añade las fichas EVI-0001 a EVI-0004. Una prueba no ejecutada permanece como no ejecutada.
- E0.1 no modifica los Word ni inicia código Symfony o Flutter.
