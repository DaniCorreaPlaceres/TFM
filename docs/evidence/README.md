# Evidencias de IA

Índice operativo del protocolo descrito en DOC-09. Las fichas no sustituyen a ese Word.

## Estado

E0.7 registró EVI-0001 a EVI-0004 con resultados ya observados. E0.8 añadió EVI-0005 el mismo día, después de ejecutar la prueba del bucket. No se reconstruyen prompts ni tiempos que no se habían anotado.

- [EVI-0001](EVI-0001/record.md): entorno Symfony. Comprobado el 18 de octubre de 2026.
- [EVI-0002](EVI-0002/record.md): Flutter en el emulador. El dispositivo físico, iOS y el escritorio Windows no se ejecutaron.
- [EVI-0003](EVI-0003/record.md): contrato mínimo de API. La app no llama a la API.
- [EVI-0004](EVI-0004/record.md): workflows. La primera ejecución de backend falló; la siguiente quedó en verde.
- [EVI-0005](EVI-0005/record.md): MinIO local. El bucket `documents` rechaza la descarga anónima. En la pull request #14, PHPUnit compiló la imagen y pasó.
- [EVI-0006](EVI-0006/record.md): alta web y confirmación de correo. En la pull request #15, PHPUnit pasó.
- [EVI-0007](EVI-0007/record.md): inicio de sesión web y recuperación de contraseña. Comprobado en local, aceptado el 25 de octubre de 2026 y abierto en la pull request #17.

El índice está en [index.csv](index.csv).

## Revisión humana

El responsable del TFM aceptó EVI-0001 a EVI-0007 el 25 de octubre de 2026, incluidas las pruebas que siguen sin ejecutarse. Esa aceptación no promociona `develop` a `main` ni cierra E1. EVI-0007 está en la pull request #17, todavía sin integrar.

## Qué no se rellena

ChatGPT Plus se usa para ideación y documentación, y Cursor Pro para implementación y pruebas. Visual Studio Code y Android Studio no cuentan por sí solos como intervención de IA. Un prompt, un tiempo o una iteración que no se archivó permanece como no registrado.
