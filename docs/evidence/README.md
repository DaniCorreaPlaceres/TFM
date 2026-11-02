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
- [EVI-0007](EVI-0007/record.md): inicio de sesión web y recuperación de contraseña. Comprobado en local y en la pull request #17. Aceptado el 25 de octubre de 2026.
- [EVI-0008](EVI-0008/record.md): segundo factor TOTP opcional en la web. Comprobado en local y en la pull request #19. Aceptado el 1 de noviembre de 2026.
- [EVI-0009](EVI-0009/record.md): crear y elegir la entidad fiscal en la web. Comprobado en local, aceptado el 1 de noviembre de 2026 e integrado con la pull request #20.
- [EVI-0010](EVI-0010/record.md): aislamiento de la entidad fiscal en la web. Comprobado en local, aceptado el 1 de noviembre de 2026 e integrado con la pull request #21. La exportación no se ejecutó.
- [EVI-0011](EVI-0011/record.md): cerrar las demás sesiones web. Comprobado en local y aceptado el 1 de noviembre de 2026. El refresh no se ejecutó. Quedó integrada con la pull request #22.
- [EVI-0012](EVI-0012/record.md): alta de una asesoría e invitación de un empleado. Comprobado en local el 1 de noviembre de 2026 y aceptado el 2 de noviembre de 2026. El segundo factor sigue siendo opcional para todos. Quedó integrada en `develop` con la pull request #23. Los registros de GitHub de esa pull request no se copiaron.
- [EVI-0013](EVI-0013/record.md): vínculo entre una entidad fiscal y una asesoría. Comprobado en local y aceptado el 2 de noviembre de 2026. Quedó integrado en `develop` con la pull request #24. Los registros de GitHub de esa pull request no se copiaron.
- [EVI-0014](EVI-0014/record.md): permiso del empleado para ver las entidades vinculadas. Comprobado en local y aceptado el 2 de noviembre de 2026. Quedó integrado en `develop` con la pull request #25. Los registros de GitHub de esa pull request no se copiaron.
- [EVI-0015](EVI-0015/record.md): categorías de un nivel y registro manual del gasto. Comprobado en local y aceptado el 2 de noviembre de 2026. Quedó integrado en `develop` con la pull request #27, merge `f13e0f9`. Los registros de GitHub de esa pull request no se copiaron.
- [EVI-0016](EVI-0016/record.md): deducibilidad independiente de la base y de la cuota. Comprobado en local y aceptado el 2 de noviembre de 2026. `feature/e4-deducibilidad` no está integrada.

El índice está en [index.csv](index.csv).

## Revisión humana

El responsable del TFM aceptó EVI-0001 a EVI-0007 el 25 de octubre de 2026, incluidas las pruebas que siguen sin ejecutarse, y EVI-0008, EVI-0009, EVI-0010 y EVI-0011 el 1 de noviembre de 2026. EVI-0012, EVI-0013, EVI-0014, EVI-0015 y EVI-0016 quedaron aceptadas el 2 de noviembre de 2026. Esa aceptación no promociona `develop` a `main` ni cierra E1, E2, E3 ni E4. Las pull requests #17, #19, #20, #21, #22, #23, #24, #25, #26 y #27 están integradas en `develop`. Los registros de GitHub de #25, #26 y #27 no se copiaron. `feature/e4-deducibilidad` no está integrada.

## Qué no se rellena

ChatGPT Plus se usa para ideación y documentación, y Cursor Pro para implementación y pruebas. Visual Studio Code y Android Studio no cuentan por sí solos como intervención de IA. Un prompt, un tiempo o una iteración que no se archivó permanece como no registrado.
